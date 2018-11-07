<?php
/**
 * CardDAV
 *
 * @version 5.4.47 - 23.01.2013
 * @author Roland 'rosali' Liebl
 * @website http://myroundcube.googlecode.com
 *
 **/

/**
 * Based on:
 * Roundcube CardDAV implementation
 *
 * This is a CardDAV implementation for roundcube 0.6 or higher. It allows every user to add
 * multiple CardDAV server in their settings. The CardDAV contacts (vCards) will be synchronized
 * automatically.
 *
 * @author Christian Putzke <christian.putzke@graviox.de>
 * @copyright Christian Putzke @ Graviox Studios
 * @since 06.09.2011
 * @link http://www.graviox.de/
 * @link https://twitter.com/graviox/
 * @version 0.5.1
 * @license http://www.gnu.org/licenses/agpl.html GNU AGPL v3 or later
 *
 */

require_once INSTALL_PATH . 'plugins/carddav/carddav_backend.php';
require_once INSTALL_PATH . 'plugins/carddav/carddav_automatic_addressbook_backend.php';
require_once INSTALL_PATH . 'plugins/carddav/carddav_addressbook.php';

class carddav extends rcube_plugin{
  public $task = 'login|settings|addressbook|mail|dummy';
  
  protected $carddav_addressbook = 'carddav_addressbook';
  protected $automatic_addressbook = 'collected';
  private $moved = 0;

  /* unified plugin properties */
  static private $plugin = 'carddav';
  static private $author = 'myroundcube@mail4us.net';
  static private $authors_comments = '<font color="red">Since v4.x contact fields are limited to (name, firstname, surname, middlename, email, photo).</font> To support all available fields carddav_plus is required.<br />Since v3.x carddav_plus plugin is required for advanced features (f.e. automated Addressbook).<br /><a href="http://myroundcube.com/myroundcube-plugins/carddav-plugin" target="_new">Documentation</a><br /><a href="http://myroundcube.com/myroundcube-plugins/thunderbird-carddav" target="_new">Desktop Client Configuration</a><br /><a href="http://mirror.myroundcube.com/docs/carddav.html" target="_new"><font color="red">IMPORTANT</font></a>';
  static private $version = '5.4.47';
  static private $date = '23-01-2013';
  static private $licence = 'GPL';
  static private $requirements = array(
    'Roundcube' => '0.9',
    'PHP' => '5.2.1 + cURL',
    'required_plugins' => array(
      'settings' => 'require_plugin',
      'db_version' => 'require_plugin',
    ),
    'recommended_plugins' => array(
      'carddav_plus' => 'config',
      'google_oauth2' => 'config',
    ),
  );
  static private $tables = array(
    'carddav_contacts',
    'carddav_server',
    'carddav_contactgroups',
    'carddav_contactgroupmembers',
    'collected_contacts',
  );
  static private $db_version = array(
    'initial',
    '20130903',
    '20131110',
  );
  static private $prefs = array(
    'automatic_addressbook',
    'use_auto_abook',
    'use_auto_abook_for_completion',
    'carddav_done',
    'carddavs_removed',
  );
  static private $sqladmin = array('db_dsnw', 'carddav_contacts');
  static private $config_dist = 'config.inc.php.dist';

  public function init(){
    $rcmail = rcmail::get_instance();
    /* DB versioning */
    if(is_dir(INSTALL_PATH . 'plugins/db_version')){
      $this->require_plugin('db_version');
      if(!$load = db_version::exec(self::$plugin, self::$tables, self::$db_version)){
        return;
      }
    }
    
    // Find me: Bug fix ... Remove for Roundcube 1.0 and newer
    $v = explode('-', RCMAIL_VERSION);
    if(version_compare($v[0], '1.0', '<')){
      if(isset($_POST['_birthday'])){
        $date = date_create_from_format($rcmail->config->get('date_format', 'Y-m-d'), get_input_value('_birthday', RCUBE_INPUT_POST));
        if($date){
          if($date = date_format($date, 'Y-m-d')){
            $_POST['_birthday'] = $date;
          }
        }
      }
      if(isset($_POST['_anniverary'])){
        $date = date_create_from_format($rcmail->config->get('date_format', 'Y-m-d'), get_input_value('_anniversary', RCUBE_INPUT_POST));
        if($date){
          if($date = date_format($date, 'Y-m-d')){
            $_POST['_anniversary'] = $date;
          }
        }
      }
    }
    
    /* CardDAV plus */
    if(is_dir(INSTALL_PATH . 'plugins/carddav_plus')){
      $this->require_plugin('carddav_plus');
    }
    
    $skin_path = $this->local_skin_path();
    if(!is_dir($skin_path)){
      $skin_path = 'skins/classic';
    }
    $this->add_texts('localization/', true);
    $this->include_stylesheet($skin_path . '/carddav.css');
    if(!in_array('global_config', $rcmail->config->get('plugins', array()))){
      $this->load_config();
      $this->require_plugin('settings');
    }
    $this->add_hook('render_page', array($this, 'render_page'));
    $this->add_hook('user_create', array($this, 'user_create'));
    switch($rcmail->task){
      case 'settings':
        $this->register_action('plugin.carddav-server-save', array($this, 'carddav_server_save'));
        $this->register_action('plugin.carddav-label-save', array($this, 'carddav_label_save'));
        $this->register_action('plugin.carddav-autocomplete-save', array($this, 'carddav_autocomplete_save'));
        $this->register_action('plugin.carddav-readonly-save', array($this, 'carddav_readonly_save'));
        $this->register_action('plugin.carddav-idx-save', array($this, 'carddav_idx_save'));
        $this->register_action('plugin.carddav-server-delete', array($this, 'carddav_server_delete'));
        $this->register_action('plugin.carddav_uninstall', array($this, 'uninstall'));
        $this->add_hook('addressbooks_list', array($this, 'get_automatic_addressbook_source'));
        $this->add_hook('addressbooks_list', array($this, 'get_carddav_addressbook_sources'));
        $this->add_hook('addressbook_get', array($this, 'get_automatic_addressbook'));
        $this->add_hook('addressbook_get', array($this, 'get_carddav_addressbook'));
        $this->add_hook('preferences_save', array($this, 'save_prefs'));
        $this->add_hook('preferences_sections_list', array($this, 'carddav_link'));
        $this->add_hook('preferences_list', array($this, 'carddav_settings'));
        $this->include_script('carddav_settings.js');
        $this->include_script('jquery.base64.js');
        $sources = $rcmail->config->get('autocomplete_addressbooks', array('sql'));
        $servers = $this->get_carddav_server();
        foreach($servers as $server){
          if(!in_array($this->carddav_addressbook . $server['carddav_server_id'], $sources)){
            if($server['autocomplete'] == 1){
              $sources[] = $this->carddav_addressbook . $server['carddav_server_id'];
              $rcmail->config->set('autocomplete_addressbooks', $sources);
            }
          }
        }
        break;
      case 'addressbook':
        @set_time_limit(0);
        $this->add_hook('addressbooks_list', array($this, 'get_automatic_addressbook_source'));
        $this->add_hook('contact_copied', array($this, 'contact_copied'));
        $this->add_hook('addressbook_get', array($this, 'get_automatic_addressbook'));
        if($this->carddav_server_available()){
          $this->register_action('plugin.carddav-addressbook-sync', array($this, 'carddav_addressbook_sync'));
          $this->include_script('carddav_addressbook.js');
          $rcmail->output->set_env('sync_carddavs_interval', $rcmail->config->get('sync_carddavs_interval', 0));
          $this->add_hook('addressbooks_list', array($this, 'get_carddav_addressbook_sources'));
          $this->add_hook('addressbook_get', array($this, 'get_carddav_addressbook'));
          if($rcmail->config->get('skin') == 'larry'){
            $this->add_button(array(
              'command' => 'plugin.carddav-addressbook-sync',
              'id' => 'carddavsyncbut',
              'class' => 'button carddavsync',
              'href' => '#',
              'title' => 'carddav.sync',
              'label' => 'carddav.sync_short',
              'type' => 'link'),
              'toolbar'
            );
          }
          else{
            $this->add_button(array(
              'command' => 'plugin.carddav-addressbook-sync',
              'id' => 'carddavsyncbut',
              'class' => 'button carddavsync',
              'href' => '#',
              'title' => 'carddav.sync',
              'label' => 'carddav.blank',
              'type' => 'link'),
              'toolbar'
            );
          }
        }
        break;
      case 'mail':
      case 'dummy':
        $sources = $rcmail->config->get('autocomplete_addressbooks', array('sql'));
        if (!in_array($this->abook_id, $sources) && $rcmail->config->get('use_auto_abook', true) && $rcmail->config->get('use_auto_abook_for_completion', true)) {
            $sources[] = $this->automatic_addressbook;
            $rcmail->config->set('autocomplete_addressbooks', $sources);
        }
        $this->add_hook('addressbook_get', array($this, 'get_automatic_addressbook'));
        $this->add_hook('message_sent', array($this, 'register_recipients'));
        if($this->carddav_server_available()){
          $this->register_action('plugin.carddav-addressbook-sync', array($this, 'carddav_addressbook_sync'));
          if($rcmail->action != 'compose' && !class_exists('tabbed')){
            $this->include_script('carddav_addressbook.js');
            $rcmail->output->set_env('sync_carddavs_interval', $rcmail->config->get('sync_carddavs_interval', 0));
          }
          $this->add_hook('addressbooks_list', array($this, 'get_automatic_addressbook_source'));
          $this->add_hook('addressbooks_list', array($this, 'get_carddav_addressbook_sources'));
          $this->add_hook('addressbook_get', array($this, 'get_carddav_addressbook'));
          $sources = (array) $rcmail->config->get('autocomplete_addressbooks', array('sql'));
          $servers = $this->get_carddav_server();
          foreach($servers as $server){
            if(!in_array($this->carddav_addressbook . $server['carddav_server_id'], $sources)){
              if($server['autocomplete'] == 1){
                $sources[] = $this->carddav_addressbook . $server['carddav_server_id'];
                $rcmail->config->set('autocomplete_addressbooks', $sources);
              }
            }
          }
        }
        break;
      case 'login':
        $this->add_hook('login_after', array($this, 'login_after'));
    }
  }
  
  static public function about($keys = false){
    $requirements = self::$requirements;
    foreach(array('required_', 'recommended_') as $prefix){
      if(is_array($requirements[$prefix.'plugins'])){
        foreach($requirements[$prefix.'plugins'] as $plugin => $method){
          if(class_exists($plugin) && method_exists($plugin, 'about')){
            /* PHP 5.2.x workaround for $plugin::about() */
            $class = new $plugin(false);
            $requirements[$prefix.'plugins'][$plugin] = array(
              'method' => $method,
              'plugin' => $class->about($keys),
            );
          }
          else{
             $requirements[$prefix.'plugins'][$plugin] = array(
               'method' => $method,
               'plugin' => $plugin,
             );
          }
        }
      }
    }
    $rcmail_config = array();
    if(is_string(self::$config_dist)){
      if(is_file($file = INSTALL_PATH . 'plugins/' . self::$plugin . '/' . self::$config_dist))
        include $file;
      else
        write_log('errors', self::$plugin . ': ' . self::$config_dist . ' is missing!');
    }
    $ret = array(
      'plugin' => self::$plugin,
      'version' => self::$version,
      'date' => self::$date,
      'db_version' => self::$db_version,
      'author' => self::$author,
      'comments' => self::$authors_comments,
      'licence' => self::$licence,
      'sqladmin' => self::$sqladmin,
      'requirements' => $requirements,
    );
    if(is_array(self::$prefs))
      $ret['config'] = array_merge($rcmail_config, array_flip(self::$prefs));
    else
      $ret['config'] = $rcmail_config;
    if(is_array($keys)){
      $return = array('plugin' => self::$plugin);
      foreach($keys as $key){
        $return[$key] = $ret[$key];
      }
      return $return;
    }
    else{
      return $ret;
    }
  }
  
  public function uninstall(){
    $rcmail = rcmail::get_instance();
    if(!empty($rcmail->user->ID)){
      $query = $rcmail->db->query(
        "DELETE FROM " . get_table_name('carddav_server') . " WHERE user_id=?",
        $rcmail->user->ID
      );
      $query = $rcmail->db->query(
        "DELETE FROM " . get_table_name('carddav_contacts') . " WHERE user_id=?",
        $rcmail->user->ID
      );
    }
    $rcmail->output->command('plugin.plugin_manager_success', '');
  }
  
  function user_create($args){
    $_SESSION['carddav_newuser'] = true;
    return $args;
  }
  
  public function render_page($p){
    if($p['template'] != 'addressbook' && $_SESSION['carddav_newuser']){
      $rcmail = rcmail::get_instance();
      $rcmail->output->add_footer(html::tag('iframe', array('src' => './?_task=settings&_action=edit-prefs&_section=addressbookcarddavs&_framed=1', 'height' => 0, 'width' => 0)));
      $rcmail->session->remove('carddav_newuser');
    }
    else if($p['template'] == 'compose'){
      $this->include_script('compose.js');
    }
    else if($p['template'] == 'addressbook'){
      if(class_exists('automatic_addressbook')){
        $error = html::tag('h3', null, 'ERROR - CardDAV (Roundcube v' . RCMAIL_VERSION . ')<hr />') .
          html::tag('p', null, 'Misconfiguration: Unregister <b>automatic_addressbook</b> in your configuration.') .
          html::tag('p', null, 'You can\'t use both (carddav and automatic_addressbook).<hr />');
        die($error);
      }
      $rcmail = rcmail::get_instance();
    }
    else if($p['template'] == 'contactedit'){
      $rcmail = rcmail::get_instance();
      $rcmail->output->add_footer(html::tag('div', array('id' => 'carddavoverlay')));
      $rcmail->output->add_script('
        $(".mainaction").click(function(){if($(this).attr("onclick").indexOf("save") > -1) {$("#carddavoverlay").show();}});
        if(typeof parent.rcmail.env.contactgroupmembership != "undefined" && parent.rcmail.env.contactgroupmembership != ""){
          $("#sourcename").html($("#sourcename").html() + "<span id=\"groupmembership\"> - ' . $this->gettext('group') . ': " + parent.rcmail.env.contactgroupmembership + "</span>");
        }
        else{
          $("#sourcename").html($("#sourcename").html() + "<span id=\"groupmembership\"></span>");
        }',
        'docready'
      );
    }
    else if($p['template'] == 'contact'){
      $rcmail = rcmail::get_instance();
      $rcmail->output->add_script('
        if(parent.$(".contactgroup.selected").get(0)) {
          var groupmembership = "";
          $(".groupmember").each(function(){
            if($(this).prop("checked")){
              groupmembership = groupmembership + $.trim($(this).parent().parent().text()) + ", ";
            }
          });
          groupmembership = groupmembership.substr(0, groupmembership.length - 2);
          parent.rcmail.env.contactgroupmembership = groupmembership;
          if(groupmembership != ""){
            $("#sourcename").html($("#sourcename").html() + "<span id=\"groupmembership\"> - ' . $this->gettext('group') . ': " + groupmembership + "</span>");
          }
          else{
            $("#sourcename").html($("#sourcename").html() + "<span id=\"groupmembership\"></span>");
          }
          $(".groupmember").click(function(){
            groupmembership = "";
            $(".groupmember").each(function(){
              if($(this).prop("checked")){
                groupmembership = groupmembership + $.trim($(this).parent().parent().text()) + ", ";
              }
            });
            groupmembership = groupmembership.substr(0, groupmembership.length - 2);
            if(groupmembership != ""){
              $("#groupmembership").html(" - ' . $this->gettext('groups') . ': " + groupmembership);
            }
            else{
              $("#groupmembership").html(groupmembership);
            }
            parent.rcmail.env.contactgroupmembership = groupmembership;
          });
        }',
        'docready'
      );
    }
    else{
      $rcmail = rcmail::get_instance();
      $about = $this->about();
      if(($p['template'] == 'mail') && $rcmail->config->get('carddav_done', false) !== $about['version'] && !get_input_value('_framed', RCUBE_INPUT_GET)){
        $p['content'] = str_replace('</html>', '<iframe src="./?_task=settings" height="0" width="0"></iframe></html>', $p['content']);
        $a_prefs['carddav_done'] = $about['version'];
        $rcmail->user->save_prefs($a_prefs);
      }
    }
    return $p;
  }
  
  public function contact_copied($args){
    $rcmail = rcmail::get_instance();
    $CONTACTS = $rcmail->get_address_book($args['source'], true);
    if(method_exists($CONTACTS, 'delete')){
      if($CONTACTS->delete(array($args['record']['ID']))){
        $this->moved ++;
        $message = $this->gettext('contact_moved_single');
        if($this->moved > 1){
          $message = $this->gettext('contact_moved_multiple');
        }
        $rcmail->output->command('plugin.carddav_addressbook_message_copied', array(
          'message' => $this->moved . ' ' . $message
          )
        );
      }
    }
    return $args;
  }
  
  public function register_recipients($p){
    $rcmail = rcmail::get_instance();
    if($rcmail->config->get('use_auto_abook', true)){
      $headers = $p['headers'];
      $all_recipients = array_merge(
        rcube_mime::decode_address_list($headers['To'], null, true, $headers['charset']),
        rcube_mime::decode_address_list($headers['Cc'], null, true, $headers['charset']),
        rcube_mime::decode_address_list($headers['Bcc'], null, true, $headers['charset'])
      );
      if($rcmail->config->get('automatic_addressbook', 'sql') == 'sql'){
        $CONTACTS = new carddav_automatic_addressbook_backend($rcmail->db, $rcmail->user->ID);
      }
      else if($rcmail->config->get('automatic_addressbook', 'sql') == 'default'){
        $CONTACTS = new rcube_contacts($rcmail->db, $rcmail->user->ID);
      }
      else{
        $CONTACTS = $this->get_carddav_addressbook(array('id' => $rcmail->config->get('automatic_addressbook', 'sql')));
        $CONTACTS = $CONTACTS['instance'];
      }
      if(is_object($CONTACTS) && method_exists($CONTACTS, 'insert')){
        foreach($all_recipients as $recipient){
          if($recipient['mailto'] != ''){
            $contact = array(
              'email' => $recipient['mailto'],
              'name' => $recipient['name']
            );
            if(empty($contact['name']) || $contact['name'] == $contact['email']){
              $contact['name'] = ucfirst(preg_replace('/[\.\-]/', ' ', substr($contact['email'], 0, strpos($contact['email'], '@'))));
            }
            $book_types = (array)$rcmail->config->get('autocomplete_addressbooks', 'sql');
            foreach($book_types as $id){
              $abook = $rcmail->get_address_book($id);
              $previous_entries = $abook->search('email', $contact['email'], 1, false);
              if($previous_entries->count){
                break;
              }
            }
             if(!$previous_entries->count){
              $plugin = $rcmail->plugins->exec_hook('contact_create', array('record' => $contact, 'source' => $this->abook_id));
              if(!$plugin['abort']){
                $CONTACTS->insert($contact, false);
              }
            }
          }
        }
      }
    }
  }

  public function login_after($args){
    $rcmail = rcmail::get_instance();
    $def_carddavs = $rcmail->config->get('def_carddavs', array());
    $server = array();
    foreach($def_carddavs as $label => $carddav){
      if($carddav['user'] == '%u' && $carddav['pass'] == '%p'){
        $parsed = parse_url($carddav['url']);
        $server[$parsed['scheme'] . $parsed['host']] = $parsed['scheme'] . '://'. $parsed['host'];
      }
    }
    $detected = array();
    $carddavs_removed = $rcmail->config->get('carddavs_removed', array());
    foreach($server as $key => $host){
      $carddav_backend = new carddav_backend($host);
      $carddav_backend->set_auth($rcmail->user->data['username'], $rcmail->decrypt($_SESSION['password']));
      $collection = $carddav_backend->get_Collection();
      if(is_array($collection)){
        foreach($collection as $addressbook){
          if(isset($carddavs_removed[$host . unslashify(urldecode($addressbook))])){
            continue;
          }
          $addressbook = unslashify($addressbook);
          $temp = explode('/', $addressbook);
          $category = ucwords($temp[count($temp) - 1]);
          $detected[$category]['user'] = '%u';
          $detected[$category]['pass'] = '%p';
          $detected[$category]['url'] = $host . urldecode($addressbook);
          $detected[$category]['readonly'] = false;
        }
      }
    }
    $carddavs = array_merge($rcmail->config->get('carddavs', array()), $def_carddavs);
    $carddavs = array_merge($detected, $carddavs);
    $a_prefs = array();
    foreach($carddavs as $category => $carddav){
      $user = $carddav['user'];
      if($user == '%u'){
        $user = $rcmail->user->data['username'];
      }
      else if($user == '%su'){
        $user = explode('@', $rcmail->user->data['username']);
        $user = $user[0];
      }
      $readonly = 0;
      if($carddav['readonly']){
        $readonly = 1;
      }
      $query = "
        SELECT url FROM
        " . get_table_name('carddav_server') . "
        WHERE url=? AND user_id=?
      ";
      $rcmail->db->query($query, str_replace('%u', $user, str_replace('%su', $user, $carddav['url'])), $_SESSION['user_id']);
      $addressbooks = array();
      $url = str_replace('%u', $user, str_replace('%su', $user, $carddav['url']));
      while($addressbook = $rcmail->db->fetch_assoc($result)){
        $addressbooks[$url] = $addressbook;
      }
      if(!isset($addressbooks[$url])){
        $query = "
          INSERT INTO
          ". get_table_name('carddav_server') . " (user_id, url, username, password, label, read_only)
          VALUES
          (?, ?, ?, ?, ?, ?)
        ";
        $rcmail->db->query($query, $rcmail->user->ID, $url, $user, $rcmail->encrypt($carddav['pass']), $category, $readonly);
        if(strtolower($category) == 'personal'){
          $id = $rcmail->db->insert_id(get_table_name('carddav_server'));
          $a_prefs['default_addressbook'] = $this->carddav_addressbook . $id;
        }
        else if(strtolower($category) == 'collected'){
          $id = $rcmail->db->insert_id(get_table_name('carddav_server'));
          $a_prefs['automatic_addressbook'] = $this->carddav_addressbook . $id;
        }
      }
      else if($carddav['pass'] == '%p'){
        $query = "
          UPDATE
          ". get_table_name('carddav_server') . "
          SET password=?
            WHERE user_id=?
            AND url=?
        ";
        $rcmail->db->query($query, $rcmail->encrypt('%p'), $rcmail->user->ID, $url);
      }
    }
    if(count($a_prefs) > 0){
      $rcmail->user->save_prefs($a_prefs);
    }
    return $args;
  }

  public function local_skin_path($include_plugins_directory = false){
    $skin_path = parent::local_skin_path();
    if(!is_dir($skin_path)){
      $skin_path = 'skins/classic';
    }
    if($include_plugins_directory === true){
      $skin_path = 'plugins/carddav/' . $skin_path;
    }
    return $skin_path;
  }

  public function get_carddav_server($carddav_server_id = false){
    $servers = array();
    $rcmail = rcmail::get_instance();
    $user_id = $rcmail->user->data['user_id'];
    $query = "
      SELECT
        *
      FROM
        ".get_table_name('carddav_server')."
      WHERE
        user_id = ?
      ".($carddav_server_id !== false ? " AND carddav_server_id = ?" : null)."
    ";
    $result = $rcmail->db->query($query, $user_id, $carddav_server_id);
    while($server = $rcmail->db->fetch_assoc($result)){
      $servers[] = $server;
    }
	
    return $servers;
  }

  protected function get_carddav_server_list(){
    $rcmail = rcmail::get_instance();
    $temp = (array) $this->get_carddav_server();
    if(count($temp) == 0){
      $this->login_after(array());
      $temp = (array) $this->get_carddav_server();
    }
    $servers = array();
    $autoabook = $rcmail->config->get('automatic_addressbook', 'sql');
    foreach($temp as $server){
      $servers[$server['label']] = $server;
    }
    ksort($servers);
    $skin_path = $this->local_skin_path(true);
    $table = new html_table(array(
      'cols' => 9,
      'class' => 'carddav_server_list',
      'cellpadding' => 0,
      'cellspacing' => 0
    ));
    if(!empty($servers)){
      $table->add_header(array('width' => '5%'), '&nbsp;');
      $table->add_header(array('width' => '12%'), $this->gettext('settings_label'));
      $table->add_header(array('width' => '1%'), '&nbsp');
      $table->add_header(array('width' => '30%'), $this->gettext('server'));
      $table->add_header(array('width' => '13%'), $this->gettext('username'));
      $table->add_header(array('width' => '13%'), $this->gettext('password'));
      $table->add_header(array('width' => '13%'), $this->gettext('settings_read_only'));
      $table->add_header(array('width' => '7%'), $this->gettext('autocomplete'));
      $table->add_header(array('width' => '6%'), '&nbsp');
      $user = explode('@', $_SESSION['username']);
      $user = $user[0];
      $addressbooks = array_merge($rcmail->config->get('carddavs', array()), $rcmail->config->get('def_carddavs', array()));
      $urls = array();
      foreach($addressbooks as $label => $addressbook){
        $urls[strtolower(str_replace('%u', $_SESSION['username'], str_replace('%su', $user, $addressbook['url'])))] = $addressbook;
      }
      $sorted = array();
      foreach($servers as $server){
        if($server['idx']){
          $sorted[$server['idx']] = $server;
        }
        else{
          $idx = 0;
          foreach($servers as $server){
            $idx ++;
            $_POST['_target_id'] = $server['carddav_server_id'];
            $_POST['_target_idx'] = $idx;
            $this->carddav_idx_save(true);
          }
          header('Location: ./?_task=settings');
          exit;
        }
      }
      ksort($sorted);
      foreach($sorted as $server){
        $sel = $server['idx'];
        $options = '';
        for($i = 1; $i <= count($sorted); $i++){
          $selected = '';
          if($i == $sel){
            $selected = 'selected';
          }
          $options .= html::tag('option', array('value' => $i, 'selected' => $selected), $i);
        }
        $table->add(array(), html::tag('select', array('onchange' => 'carddav_server_index(this)', 'id' => 's' . $server['carddav_server_id'], 'class' => 'c' . $sel), $options));
        $label = $this->gettext($server['label']);
        if($server['edt'] || (substr($label, 0, 1) == '[' && substr($label, strlen($label) - 1, 1) == ']')){
          $label = $server['label'];
        }
        if($label == Google){
          $merge = array('readonly' => 'readonly');
        }
        else{
          $merge = array('class' => 'carddav_edit_label');
        }
        $table->add(array(), html::tag('input', array_merge(array('type' => 'text', 'size' => '12', 'value' => $label, 'id' => $this->carddav_addressbook . $server['carddav_server_id']), $merge)));
        $table->add(array('class' => 'loadingsmall', 'style' => 'visibility: hidden;', 'id' => 'l' . $this->carddav_addressbook . $server['carddav_server_id']), html::tag('img', array('src' => './plugins/carddav/skins/' . $rcmail->config->get('skin', 'classic') . '/loadingsmall.gif')));
        $table->add(array(), html::tag('input', array('title' => $this->gettext('protected'), 'type' => 'text', 'size' => '45', 'readonly' => 'readonly', 'value' => $server['url'])));
        $table->add(array(), html::tag('input', array('title' => $this->gettext('protected'), 'type' => 'text', 'size' => '17', 'readonly' => 'readonly', 'value' => $server['username'])));
        $table->add(array(), html::tag('input', array('title' => $this->gettext('protected'), 'type' => 'text', 'size' => '12', 'readonly' => 'readonly', 'value' => '**********')));
        $title = $this->gettext('toggle');
        $onclick = 'carddav_server_readonly(this,"' . $server['carddav_server_id'] . '")';
        $img = $server['read_only'] ? 'checked.png' : 'blank.gif';
        if(isset($urls[$server['url']]) || ($this->carddav_addressbook . $server['carddav_server_id']) == $autoabook){
          $title = $this->gettext('protected');
          $onclick = '';
        }
        $table->add(array('align' => 'center'), html::tag('p', array('style' => 'width: 15px; height: 15px; border: 1px solid #B2B2B2; border-radius: 4px;'), html::tag('img', array('title' => $title, 'onclick' => $onclick, 'src' => $skin_path . '/' . $img))));
        $autocomplete = $server['autocomplete'];
        $onclick= 'carddav_server_autocomplete(this,"' . $server['carddav_server_id'] . '")';
        $title = $this->gettext('toggle');
        if($this->carddav_addressbook . $server['carddav_server_id'] == $autoabook){
          $autocomplete = $rcmail->config->get('use_auto_abook_for_completion', false);
          $onclick = '';
          $title = $this->gettext('protected');
        }
        $img = $autocomplete ? 'checked.png' : 'blank.gif';
        $table->add(array('align' => 'center'), html::tag('p', array('style' => 'width: 15px; height: 15px; border: 1px solid #B2B2B2; border-radius: 4px;'), html::tag('img', array('height' => '12', 'width' => '12', 'onclick' => $onclick, 'title' => $title, 'src' => $skin_path . '/' . $img))));
        $delete = html::tag('a', array('href' => '#del', 'class' => 'deletebutton', 'title' => $this->gettext('delete'), 'onclick' => "if(window.confirm('" . addslashes($this->gettext('settings_delete_warning')) . "', '" . addslashes($this->gettext('settings_delete_contacts_warning_html')) . "', 'rcmail.command(\'plugin.carddav-server-remove\', \'" . $server['carddav_server_id'] ."\', this)', 'rcmail.command(\'plugin.carddav-server-delete\', \'" . $server['carddav_server_id'] ."\', this)', true)) { if(window.confirm('" . addslashes($this->gettext('settings_delete_contacts_warning')) . "')) { rcmail.command('plugin.carddav-server-remove', '" . $server['carddav_server_id'] ."', this) } else { rcmail.command('plugin.carddav-server-delete', '" . $server['carddav_server_id'] ."', this)} }"), $this->gettext('delete'));
        if(isset($urls[$server['url']]) || ($this->carddav_addressbook . $server['carddav_server_id']) == $autoabook){
          unset($urls[$server['url']]);
          $delete = '&nbsp;';
        }
        $table->add(array(), $delete);
      }
    }
    else{
      $i = 1;
    }
    if(count($servers) < $rcmail->config->get('max_carddavs', 3)){
      $table->add(array('align' => 'center'), html::tag('select', array('name' =>'_idx', 'readonly' => 'readonly'), html::tag('option', array('value' => $i), $i)));
      $input_label = new html_inputfield(array('name' => '_label', 'id' => '_label', 'size' => '12', 'autocomplete' => 'off', 'placeholder' => $this->gettext('settings_label')));
      $input_server_url = new html_inputfield(array('name' => '_server_url', 'id' => '_server_url', 'size' => '45', 'autocomplete' =>'off', 'placeholder' => $this->gettext('server_url')));
      $input_username = new html_inputfield(array('name' => '_username', 'id' => '_username', 'size' => '17', 'autocomplete' => 'off', 'placeholder' => $this->gettext('username')));
      $append = '';
      if(class_exists('vkeyboard')){
        $size = 7;
        if(get_input_value('_remote', RCUBE_INPUT_POST)){
          $append = html::tag('img', array('src' => 'plugins/vkeyboard/skins/' . $rcmail->config->get('skin') . '/keyboard.png', 'alt' => $this->gettext('vkeyboard.vk00'), 'class' => 'keyboardInputInitiator', 'title' => $this->gettext('vkeyboard.vk01')));
        }
      }
      else{
        $size = 12;
      }
      $input_password = new html_inputfield(array('name' => '_password', 'id' => '_password', 'class' => 'keyboardInput', 'size' => $size, 'autocomplete' => 'off', 'placeholder' => $this->gettext('password')));
      $input_read_only = new html_checkbox(array('name' => '_read_only', 'id' => '_read_only', 'value' => 1));
      $table->add(array(), $input_label->show());
      $table->add(array(), '&nbsp;');
      $table->add(array(), $input_server_url->show());
      $table->add(array(), $input_username->show());
      $table->add(array('nowrap' => 'nowrap'), $input_password->show() . $append);
      $table->add(array('align' => 'center'), $input_read_only->show());
      $input_autocomplete = new html_checkbox(array('name' => '_autocomplete', 'value' => 1));
      $table->add(array('align' => 'center'), $input_autocomplete->show(true));
      $add = html::tag('a', array('href' => '#add', 'class' => 'addbutton', 'title' => $this->gettext('add'), 'onclick' => "return rcmail.command('plugin.carddav-server-save', '', this)"), $this->gettext('add'));
      $table->add(array(), $add);
    }
    $content .= html::div(array('class' => 'carddav_container'), $table->show());
    return $content;
  }

  public function get_automatic_addressbook($args){
    $rcmail = rcmail::get_instance();
    if(($args['id'] === $this->automatic_addressbook) && $rcmail->config->get('use_auto_abook', true)){
      $args['instance'] = new carddav_automatic_addressbook_backend($rcmail->db, $rcmail->user->ID);
      $args['instance']->groups = false;
    }
    return $args;
  }

  public function get_carddav_addressbook($addressbook){
    $servers = $this->get_carddav_server();
    foreach($servers as $server){
      if($addressbook['id'] === $this->carddav_addressbook . $server['carddav_server_id']){
        $addressbook['instance'] = new carddav_addressbook($server['carddav_server_id'], $server['label'], ($server['read_only'] == 1 ? true : false), $addressbook['id']);
      }
    }
    return $addressbook;
  }
  
  public function get_automatic_addressbook_source($args){
    $rcmail = rcmail::get_instance();
    if($rcmail->config->get('use_auto_abook', true)){
      $show = true;
      if($rcmail->config->get('automatic_addressbook', 'sql') != 'sql'){
        $query = 'SELECT user_id FROM ' . get_table_name('collected_contacts') . ' WHERE user_id=? AND del<>?';
        $result = $rcmail->db->query($query, $rcmail->user->data['user_id'], 1);
        if($rcmail->db->num_rows($result) == 0 && !$rcmail->config->get('show_empty_database_addressbooks', true)){
          $show = false;
        }
      }
      if($show){
        $args['sources'][$this->automatic_addressbook] = array('id' => $this->automatic_addressbook, 'name' => Q($this->gettext('automaticallycollected_local')), 'readonly' => false, 'groups' => false);
      }
    }
    foreach($args['sources'] as $key => $source){
      if($source['id'] == 0){
        $query = 'SELECT user_id FROM ' . get_table_name('contacts') . ' WHERE user_id=? AND del<>?';
        $result = $rcmail->db->query($query, $rcmail->user->data['user_id'], 1);
        if($rcmail->db->num_rows($result) == 0 && $rcmail->config->get('automatic_addressbook', 'sql') != 'default' && $rcmail->config->get('default_addressbook', '0') != '0' && !$rcmail->config->get('show_empty_database_addressbooks', true)){
          unset($args['sources'][$key]);
        }
        else{
          if($key === 0){
            $args['sources'][$key]['name'] = $this->gettext('defaultaddressbook') . ' (' . $this->gettext('local') . ')';
          }
        }
      }
    }
    return $args;
  }

  public function get_carddav_addressbook_sources($addressbook){
    $servers = $this->get_carddav_server();
    $sorted = array();
    foreach($servers as $server){
      $sorted[$server['idx']] = $server;
    }
    ksort($sorted);
    foreach ($sorted as $server){
      $carddav_addressbook = new carddav_addressbook($server['carddav_server_id'], $server['label'], ($server['read_only'] == 1 ? true : false), $addressbook['id']);
      $label = $this->gettext($server['label']);
      if($server['edt'] || (substr($label, 0, 1) == '[' && substr($label, strlen($label) - 1, 1) == ']')){
        $label = $server['label'];
      }
      $addressbook['sources'][$this->carddav_addressbook . $server['carddav_server_id']] = array(
        'id' => $this->carddav_addressbook . $server['carddav_server_id'],
        'name' => $label,
        'readonly' => $carddav_addressbook->readonly,
        'groups' => $carddav_addressbook->groups
      );
    }
    return $addressbook;
  }

  private function check_curl_installed(){
    if(function_exists('curl_init')){
      return true;
    }
    else{
      return false;
    }
  }

  public function carddav_addressbook_sync($carddav_server_id = false, $ajax = true){
    $silent = get_input_value('_silent', RCUBE_INPUT_POST);
    $servers = $this->get_carddav_server();
    $failure = array();
    foreach ($servers as $server){
      if($carddav_server_id === false || $carddav_server_id == $server['carddav_server_id']){
        $carddav_addressbook = new carddav_addressbook($server['carddav_server_id'], $server['label'], ($server['read_only'] == 1 ? true : false), false);
        $result = $carddav_addressbook->carddav_addressbook_sync($server);
        if(!$result){
          $failure[] = $this->carddav_addressbook.$server['carddav_server_id'];
        }
      }
    }
	
    if($ajax === true){
      $rcmail = rcmail::get_instance();
      if(count($failure) == 0){
        if($silent){
          $rcmail->output->command('plugin.carddav_addressbook_message_silent', array(
            'message' => $this->gettext('addressbook_synced'),
            'check' => true
          ));
        }
        else{
          $rcmail->output->command('plugin.carddav_addressbook_message', array(
            'message' => $this->gettext('addressbook_synced'),
            'check' => true
          ));
        }
      }
      else{
        $rcmail->output->command('plugin.carddav_addressbook_message', array(
          'message' => $this->gettext('addressbook_sync_failed'),
          'failure' => $failure,
          'check' => false
        ));
      }
    }
  }

  protected function carddav_server_available(){
    $rcmail = rcmail::get_instance();
    $user_id = $rcmail->user->data['user_id'];
    $query = "
      SELECT
        *
      FROM
        ".get_table_name('carddav_server')."
      WHERE
        user_id = ?
    ";
    $result = $rcmail->db->query($query, $user_id);
    if($rcmail->db->num_rows($result)){
      return true;
    }
    else{
      return false;
    }
  }

  public function carddav_server_check_connection(){
    $rcmail = rcmail::get_instance();
    $url = trim(get_input_value('_server_url', RCUBE_INPUT_POST));
    $username = parse_input_value(base64_decode($_POST['_username']));
    $password = parse_input_value(base64_decode($_POST['_password']));
    if($password == '%p'){
      if($_SESSION['default_account_password']){
        $password = $_SESSION['default_account_password'];
      }
      else{
        $password = $_SESSION['password'];
      }
      $password = $rcmail->decrypt($password);
    }
    $carddav_backend = new carddav_backend($url);
    $carddav_backend->set_auth($username, $password);
    $return = $carddav_backend->check_connection();
    return $return;
  }

  public function carddav_link($args){
    if(class_exists('carddav_plus') && !rcmail::get_instance()->config->get('carddav_protect', false)){
      $args['list']['addressbookcarddavs']['section'] = $this->gettext('submenuprefix') . $this->gettext('settings');
      $args['list']['addressbookcarddavs']['id'] = 'addressbookcarddavs';
    }
	// Sharing Commented
    /* if(class_exists('carddav_plus') && class_exists('sabredav')){
      $args['list']['addressbooksharing']['id'] = 'addressbooksharing';
      $args['list']['addressbooksharing']['section'] = $this->gettext('submenuprefix') . $this->gettext('sharing');
    } */
    return $args;
  }

  public function carddav_settings($args){
    if(!$args['section']){
      return array();
    }
    if(class_exists('carddav_plus')){
      if(!get_input_value('_framed', RCUBE_INPUT_GPC) && substr($args['section'], 0, strlen('addressbook')) == 'addressbook'){
        $args['blocks'][$args['section']]['options'] = array(
          'title'   => '',
          'content' => html::tag('div', array('id' => 'pm_dummy'), '')
        );
        return $args;
      }
      $addressbooks = array();
      if($args['section'] == 'addressbook'){
        $addressbooks = (array) $this->get_carddav_addressbook_sources(false);
      }
      $list = false;
      if($args['section'] == 'addressbookcarddavs'){
        $list = $this->get_carddav_server_list();
      }
      $args = carddav_plus::carddav_settings($args, $addressbooks, $list);
    }
    return $args;
  }
  
  public function save_prefs($args){
    if(class_exists('carddav_plus')){
      $addressbook = $this->carddav_addressbook;
      $args = carddav_plus::save_prefs($args, $addressbook);
    }
    return $args;
  }

  public function carddav_server_save(){
    $rcmail = rcmail::get_instance();
    if($ret = $this->carddav_server_check_connection()){
      $user_id = $rcmail->user->data['user_id'];
      //https://code.google.com/p/myroundcube/issues/detail?id=411
      $url = trim(get_input_value('_server_url', RCUBE_INPUT_POST));
      $parsed = parse_url($url);
      $parsed['path'] = $this->sanitize(urldecode($parsed['path']));
      $url = $parsed['scheme'] . '://' . $parsed['host'] . ($parsed['port'] ? (':' . $parsed['port']) : '') . $parsed['path'] . ($parsed['query'] ? ('?' . $parsed['query']) : '');
      $username = parse_input_value(base64_decode($_POST['_username']));
      $password = parse_input_value(base64_decode($_POST['_password']));
      $label = parse_input_value(base64_decode($_POST['_label']));
      $read_only = (int) parse_input_value(base64_decode($_POST['_read_only']));
      $temp = explode('?', $url, 2);
      if(carddav_plus::isSabreDAV($temp[0] . '?issabredav=1') && strpos($url, '?access=2') !== false){
        $read_only = 1;
      }
      $autocomplete = (int) parse_input_value(base64_decode($_POST['_autocomplete']));
      $idx = (int) parse_input_value(base64_decode($_POST['_idx']));
      $pwsync = $rcmail->config->get('carddav_synced_passwords', array());
      $parsed = parse_url($url);
      if(!$parsed['query']){
        $host = $parsed['scheme'] . '://'. $parsed['host'];
        if(class_exists('carddav_plus') && method_exists('carddav_plus', 'carddav_add_collection')){
          carddav_plus::carddav_add_collection($host, $username, $password, $url, $label);
        }
      }
      $default_password = $_SESSION['default_account_password'] ? $_SESSION['default_account_password'] : $_SESSION['password'];
      if($password == $rcmail->decrypt($default_password)){
        $password = '%p';
      }
      if(isset($parsed['host']) && strpos($url, '?access=') === false){
        if(in_array($parsed['host'], $pwsync)){
          $password = '%p';
        }
      }
      $carddavs_removed = $rcmail->config->get('carddavs_removed', array());
      unset($carddavs_removed[unslashify(urldecode($url))]);
      $rcmail->user->save_prefs(array('carddavs_removed' => $carddavs_removed));
      $query = "
        INSERT INTO
          ".get_table_name('carddav_server')." (user_id, url, username, password, label, read_only, autocomplete, idx)
        VALUES
          (?, ?, ?, ?, ?, ?, ?, ?)
      ";
      $rcmail->db->query($query, $user_id, $url, $username, $rcmail->encrypt($password), $label, $read_only, $autocomplete, $idx);
      if($rcmail->db->affected_rows()){
        $sync = $this->carddav_addressbook_sync($rcmail->db->insert_id(), false);
        $rcmail->output->command('plugin.carddav_server_message', array(
          'server_list' => $this->get_carddav_server_list(),
          'message' => $this->gettext('settings_saved'),
          'check' => true,
          'tabbed' => true,
        ));
      }
      else{
        $rcmail->output->command('plugin.carddav_server_message', array(
          'message' => $this->gettext('settings_save_failed'),
          'check' => false,
          'tabbed' => true,
        ));
      }
    }
    else{
      $rcmail->output->command('plugin.carddav_server_message', array(
        'message' => $this->gettext('settings_no_connection'),
        'check' => false,
        'tabbed' => true,
      ));
    }
  }
  
  public function carddav_label_save(){
    $rcmail = rcmail::get_instance();
    $id = str_replace($this->carddav_addressbook, '', get_input_value('_id', RCUBE_INPUT_POST));
    $label = urldecode(get_input_value('_label', RCUBE_INPUT_POST));
    $query = "UPDATE 
      ".get_table_name('carddav_server') ." SET label=?, edt=? WHERE carddav_server_id=?";
    $rcmail->db->query($query, $label, 1, $id);
    if($rcmail->db->affected_rows()){
      $rcmail->output->command('plugin.carddav_server_success', array(
        'message' => $this->gettext('successfullysaved'),
        'tabbed' => true,
      ));
    }
  }
  
  public function carddav_autocomplete_save(){
    $rcmail = rcmail::get_instance();
    $id = get_input_value('_id', RCUBE_INPUT_POST);
    $autocomplete = get_input_value('_autocomplete', RCUBE_INPUT_POST);
    $query = "UPDATE 
      ".get_table_name('carddav_server') ." SET autocomplete=? WHERE carddav_server_id=?";
    $rcmail->db->query($query, $autocomplete, $id);
    if($rcmail->db->affected_rows()){
      $rcmail->output->command('plugin.carddav_server_success', array(
        'message' => $this->gettext('successfullysaved'),
        'tabbed' => true,
      ));
    }
  }
  
  public function carddav_readonly_save(){
    $rcmail = rcmail::get_instance();
    $id = get_input_value('_id', RCUBE_INPUT_POST);
    $readonly = get_input_value('_readonly', RCUBE_INPUT_POST);
    $query = "UPDATE 
      ".get_table_name('carddav_server') ." SET read_only=? WHERE carddav_server_id=?";
    $rcmail->db->query($query, $readonly, $id);
    if($rcmail->db->affected_rows()){
      $rcmail->output->command('plugin.carddav_server_success', array(
        'message' => $this->gettext('successfullysaved'),
        'tabbed' => true,
      ));
    }
  }
  
  public function carddav_idx_save($silent = false){
    $rcmail = rcmail::get_instance();
    $old_target_id = get_input_value('_old_target_id', RCUBE_INPUT_POST);
    $old_target_idx = get_input_value('_old_target_idx', RCUBE_INPUT_POST);
    $target_id = get_input_value('_target_id', RCUBE_INPUT_POST);
    $target_idx = get_input_value('_target_idx', RCUBE_INPUT_POST);
    $query = "
      UPDATE " . get_table_name('carddav_server') . "
        SET idx=? 
      WHERE carddav_server_id=?
        AND user_id=?";
    $rcmail->db->query($query, $target_idx, $target_id, $rcmail->user->data['user_id']);
    if($old_target_id && $old_target_idx){
      $query = "
        UPDATE " . get_table_name('carddav_server') . "
          SET idx=? 
        WHERE carddav_server_id=?
          AND user_id=?";
      $rcmail->db->query($query, $old_target_idx, $old_target_id, $rcmail->user->data['user_id']);
    }
    if(!$silent){
      if($rcmail->db->affected_rows()){
        $rcmail->output->command('plugin.carddav_server_message', array(
          'server_list' => $this->get_carddav_server_list(),
          'message' => $this->gettext('successfullysaved'),
          'check' => true,
          'type' => 'confirmation'
        ));
      }
      else{
        $rcmail->output->command('plugin.carddav_server_message', array(
          'server_list' => $this->get_carddav_server_list(),
          'message' => $this->gettext('errorsaving'),
          'check' => true,
          'type' => 'error'
        ));
      }
    }
  }

  public function carddav_server_delete(){
    $rcmail = rcmail::get_instance();
    $carddav_server_id = parse_input_value(base64_decode($_POST['_carddav_server_id']));
    $remove = get_input_value('_remove', RCUBE_INPUT_POST);
    $servers = (array) $this->get_carddav_server();
    $user_id = $rcmail->user->data['user_id'];
    $query = "
      SELECT * FROM
        ".get_table_name('carddav_server')."
      WHERE
        user_id = ?
      AND
        carddav_server_id = ?
    ";
    $res = $rcmail->db->query($query, $user_id, $carddav_server_id);
    $server = $rcmail->db->fetch_assoc($res);
    $parsed = parse_url($server['url']);
    if(!$parsed['query']){
      $host = $parsed['scheme'] . '://'. $parsed['host'];
      $user = $server['username'];
      $password = $rcmail->decrypt($server['password']);
      if($user == '%u'){ 
        $user = $rcmail->user->data['username'];
      }
      if($password == '%p'){
        if(isset($_SESSION['default_account_password'])){
          $password = $rcmail->decrypt($_SESSION['default_account_password']);
        }
        else{
          $password = $rcmail->decrypt($_SESSION['password']);
        }
      }
      if($remove && class_exists('carddav_plus') && method_exists('carddav_plus', 'carddav_delete_collection')){
        carddav_plus::carddav_delete_collection($host, $user, $password, $server['url']);
      }
      $carddavs_removed = $rcmail->config->get('carddavs_removed', array());
      $carddavs_removed[unslashify(urldecode($server['url']))] = 1;
      $rcmail->user->save_prefs(array('carddavs_removed' => $carddavs_removed));
    }
    $query = "
      DELETE FROM
        ".get_table_name('carddav_server')."
      WHERE
        user_id = ?
      AND
        carddav_server_id = ?
    ";
    $rcmail->db->query($query, $user_id, $carddav_server_id);
    $return = $rcmail->db->affected_rows();
    $idx = 0;
    $sorted = array();
    $servers = (array) $this->get_carddav_server();
    foreach($servers as $server){
      $sorted[$server['idx']] = $server;
    }
    ksort($sorted);
    foreach($sorted as $server){
      $idx ++;
      $_POST['_target_id'] = $server['carddav_server_id'];
      $_POST['_target_idx'] = $idx;
      $this->carddav_idx_save(true);
    }
    if($return){
      $rcmail->output->command('plugin.carddav_server_message', array(
        'server_list' => $this->get_carddav_server_list(),
        'message' => $this->gettext('settings_deleted'),
        'check' => true,
        'tabbed' => true,
      ));
    }
    else{
      $rcmail->output->command('plugin.carddav_server_message', array(
        'message' => $this->gettext('settings_delete_failed'),
        'check' => false,
        'tabbed' => true
      ));
    }
  }

  public function write_log($message){
    if(rcmail::get_instance()->config->get('carddav_debug', false)){
      write_log('CardDAV', 'v' . self::$version . ' | ' . $message);
    }
  }
  
  private function sanitize($unformatted){
    $url = trim($unformatted);
    $url = htmlentities($url, ENT_QUOTES, 'UTF-8');
    $url = preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', $url);
    $url = html_entity_decode($url, ENT_QUOTES, 'UTF-8');
    $url = trim($url, ' -');
    $search = array('�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', '�', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', '�', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', '?', '?', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', '?', '?', 'L', 'l', 'N', 'n', 'N', 'n', 'N', 'n', '?', 'O', 'o', 'O', 'o', 'O', 'o', '�', '�', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', '�', '�', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', '�', 'Z', 'z', 'Z', 'z', '�', '�', '?', '�', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', '?', '?', '?', '?', '?', '?'); 
    $replace = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o'); 
    $url = str_replace($search, $replace, $url);
    $search = array('&', '�', '$'); 
    $replace = array('and', 'pounds', 'dollars'); 
    $url = str_replace($search, $replace, $url);
    $find = array(' ', '&', '\r\n', '\n', '+', ',', '//');
    $url = str_replace($find, '-', $url);
    $find = array('/[^a-zA-Z0-9\-\+<>_\/@\.]/', '/[\-]+/', '/<[^>]*>/');
    $replace = array('', '-', '');
    $url = preg_replace($find, $replace, $url);
    return $url;
  }
}
?>