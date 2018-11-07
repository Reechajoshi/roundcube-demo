<?php
/**
 * calendar
 *
 * @version 16.1.31 - 13.12.2013
 * @author Roland 'rosali'Liebl
 * @website http://myroundcube.com
 *
 **/

/**
 * Based on RoundCube Calendar
 *
 * Plugin to add a calendar to RoundCube.
 *
 * @version 0.2 BETA 2
 * @author Lazlo Westerhof
 * @url http://rc-calendar.lazlo.me
 * @licence GNU GPL
 * @copyright (c) 2010 Lazlo Westerhof - Netherlands
 */
 
// application constants
define('CALEOT', '2029-12-31 23:59:59');
// just a dummy for DAVical (don't log anything)
function dbg_error_log() {
 
}

// callback
function cmp_reminders($a, $b){
  return strcasecmp($a, $b);
}
 
class calendar extends rcube_plugin{
  public $backend = null;

  /** Some utility functions */
  public $utils = null;
  
  private $message;
  private $dbtable;
  private $dbtablesquence;
  private $ical_parts = array();
  private $myevents = null;
  private $userid = null;
  private $categories = array();
  private $ctags = array();
  private $notify = null;
  private $show_upcoming_cal = false;
  private $bd;
  private $search_fields = array(
    'summary' => 1,
    'description' => 1,
    'location' => 1,
    'categories' => 1,
    'all_day' => 1
  );
  
  /* unified plugin properties */
  static private $plugin = 'calendar';
  static private $author = 'myroundcube@mail4us.net';
  static private $authors_comments = 'News: Tasks implemented (requires calendar_plus 3.0 or newer).<br />Since v10.x you need calendar_plugs plugin to achieve advanced features (f.e. CalDAV).';
  static private $version = '16.1.31';
  static private $date = '13-12-2013';
  static private $db_version = array(
    'initial',
    '20130512',
    '20130804',
  );
  static private $sqladmin = array('db_dsnw', 'events');
  static private $licence = 'GPL';
  static private $requirements = array(
    'Roundcube' => '0.9',
    'PHP' => '5.2.1',
    'required_plugins' => array(
      'jqueryui' =>  'require_plugin',
      'jscolor' =>   'require_plugin',
      'qtip' =>      'require_plugin',
      'http_auth' => 'require_plugin',
      'http_request' => 'require_plugin',
      'timepicker' => 'require_plugin',
      'db_version' => 'require_plugin',
    ),
    'recommended_plugins' => array(
      'calendar_plus' => 'config',
    ),
  );
  static private $prefs = array(
    'backend',
    'caldav_user',
    'caldav_password',
    'caldav_url',
    'caldav_auth',
    'caldav_extr',
    'caldav_replicate_automatically',
    'caldav_replication_range',
    'caldav_reminders',
    'reminders_hash',
    'cal_searchset',
    'caldavs',
    'calfilter_allcalendars',
    'calfilter_mycalendar',
    'event_filters_allcalendars',
    'event_filters_mycalendar',
    'calendarfeeds',
    'caltokenreadonly',
    'caltoken_davreadonly',
    'caltoken',
    'upcoming_cal',
    'show_birthdays',
    'workdays',
    'default_duration',
    'default_view',
    'timeslots',
    'first_day',
    'default_calendar',
    'cal_notify',
    'cal_notify_to',
    'caldav_notify',
    'caldav_notify_to',
    'categories',
    'ctags',
    'caldavs_removed',
    'caldavs_subscribed',
    'caldavs_subscribed_prev',
    'public_categories_override',
    'default_caldav_subscribed',
    'feeds_subscribed',
    'feeds_subscribed_prev',
    'calendar_subscriptions_view',
    'collections_sync',
    'detected_caldavs',
    'sync_collections',
  );
  static private $config_dist = 'config.inc.php.dist';

 /****************************
  *
  * Initialization
  *
  *****************************/

  function version(){
    return self::$version;
  }
  
  function init() {
    $rcmail = rcmail::get_instance();
    if($rcmail->action == 'jappix.loadmini'){
      return;
    }
    if(!in_array('global_config', $rcmail->config->get('plugins', array()))){
      $this->require_plugin('jqueryui');
      $this->load_config();
    }
	
	// SELECT2 PLUGIN
	if(file_exists(INSTALL_PATH . 'plugins/calendar/skins/larry/select2.css')){
		$this->include_stylesheet("skins/larry/select2.css");
	}
	if(file_exists( INSTALL_PATH . 'plugins/calendar/program/js/select2.js' )){
		$this->include_script('program/js/select2.js');
	}
	
	// AUTOCOMPLETE PLUGIN
	if(file_exists(INSTALL_PATH . 'plugins/calendar/skins/larry/jquery.autocomplete.css')){
		$this->include_stylesheet("skins/larry/jquery.autocomplete.css");
	}
	if(file_exists( INSTALL_PATH . 'plugins/calendar/program/js/jquery.autocomplete.js' )){
		$this->include_script('program/js/jquery.autocomplete.js');
	}
	
    if(file_exists(INSTALL_PATH . 'plugins/calendar_plus/calendar_plus.php')){
      $this->require_plugin('calendar_plus');
      if(!get_input_value('_remote', RCUBE_INPUT_GPC)){
        $v = calendar_plus::about(array('version'));
        $t = explode('.', $v['version']);
        if($t[0] >= '3'){
          if($rcmail->action == 'plugin.calendar'){
            $rcmail->output->add_script('$("#taskstogglecontainer").show();', 'docready');
          }
        }
        else{
          $rcmail->output->add_script('rcmail.set_cookie("tasksvisible", 0);', 'head');
        }
      }
    }
    else{
      if(!get_input_value('_remote', RCUBE_INPUT_GPC)){
        $rcmail->output->add_script('rcmail.set_cookie("tasksvisible", 0);', 'head');
      }
    }
    
    /* DB versioning */
    if(is_dir(INSTALL_PATH . 'plugins/db_version')){
      $this->require_plugin('db_version');
      $tables = array();
      $tables[] = $rcmail->config->get('db_table_events', 'event');
      $tables[] = $rcmail->config->get('db_table_events_cache', 'events_cache');
      $default = array('database' => '', 'caldav' => '_caldav');
      $map = $rcmail->config->get('backend_db_table_map', $default);
      $tables[] = $tables[0] . $map['caldav'];
      $tables[0] = $tables[0] . $map['database'];
      $tables[] = $rcmail->config->get('db_table_reminders', 'reminders');
      if(!$load = db_version::exec(self::$plugin, $tables, self::$db_version)){
        return;
      }
    }
    
    $this->include_script('program/js/calendar.common.js');
    
    /* switch messagelist attachment icon and inject upcoming calendar */
    if(class_exists('calendar_plus') && !isset($_GET['_extwin'])){
       $this->show_upcoming_cal = $rcmail->config->get('upcoming_cal', false);
    }
    if($rcmail->task == 'mail') {
      if(class_exists('calendar_plus')){
        calendar_plus::load_ics_attachments();
      }
      $this->include_script('program/js/timezone.js');
      $this->include_script('program/js/detect_timezone.js');
      $this->include_script('program/js/date.js');
      $this->include_script('program/js/date.format.js');
      $this->include_script('program/js/calendar.replicate.js');
      if($this->show_upcoming_cal){
        if($rcmail->action != 'preview' &&
           $rcmail->action != 'compose' &&
           $rcmail->action != 'get' &&
           $rcmail->action != 'plugin.summary'
        ){
          calendar_plus::load_upcoming_cal();
          $skin = $rcmail->config->get('skin');
          if(!file_exists($this->home . '/skins/' . $skin . '/fullcalendar.css')) {
            $skin = "classic";
          }
          $this->include_stylesheet('skins/' . $skin . '/fullcalendar.css');
          $this->include_script('program/js/querystring.js');
          $this->include_script('program/js/fullcalendar.js');
          $this->include_script('program/js/calendar.jsonfeeds.js');
          $rcmail->output->set_env('caleot', CALEOT);
          $rcmail->output->set_env('caleot_unix', strtotime(CALEOT));
        }
      }
    }
         
    /* http requests */
    $this->require_plugin('http_request');
         
    /* compatibility functions */
    require_once('program/compat.php');
    
    /* localization */
    $flag = false;
    if(substr($rcmail->action,0,15) == 'plugin.calendar')
      $flag = true;
    $this->add_texts('localization/', $flag);
    
    /* restore calendar status */
    if($rcmail->action == 'plugin.calendar' || $rcmail->action == 'plugin.calendar_fetchalllayers'){
      $_SESSION['cal_initialized'] = true;
    }
    $_SESSION['calfilter'] = $rcmail->config->get('calfilter_allcalendars', array());
    $_SESSION['calfiltertasks'] = str_replace($this->gettext('calendar.events'), $this->gettext('calendar.tasks'), $_SESSION['calfilter']);
    $_SESSION['event_filters'] = $rcmail->config->get('event_filters_allcalendars', array());
    
    /* setup backend */
    $this->dbtable = $rcmail->config->get('db_table_events', 'events');
    $this->dbtablesquence = $rcmail->config->get('db_sequence_events', 'events_ids');
    $backend_type = $this->setupBackend();
    $rcmail->output->set_env('cal_backend', $backend_type);
    $GLOBALS['db_table_events'] = $rcmail->config->get('db_table_events', 'events');
    
    /* clear CalDAV cache */
    if($backend_type == 'caldav'){
      if($rcmail->action == 'plugin.getSettings'){
        if(get_input_value('_init', RCUBE_INPUT_POST)){
          if(!$_SESSION['caldav_allfetched'] && !$_SESSION['caldav_resume_replication']){
            $this->backend->truncateEvents(4);
            $_SESSION['caldav_truncate'] = true;
          }
        }
      }
    }
    else{
      if($rcmail->action == 'plugin.getSettings'){
        $this->backend->purgeEvents();
      }
    }

    if(empty($_SESSION['calfilter']))
      $_SESSION['calfilter'] = $this->gettext('allevents');
    
    /* calendar and print page */
    $this->register_action('plugin.calendar', array($this, 'startup'));
    $this->add_hook('template_object_cal_searchset', array($this, 'cal_searchset'));
    
    /* notify */
    $this->notify = $rcmail->config->get('cal_notify');
      
    /* http authentication */
    $this->require_plugin('http_auth');
    $this->add_hook('startup', array($this, 'check_auth'));
    
    /* settings */
    $this->register_action('plugin.getSettings', array($this, 'getSettings'));
    $this->register_action('plugin.calendar_uninstall', array($this, 'uninstall'));
    $this->add_hook('preferences_sections_list', array($this, 'calendarLink'));
    $this->add_hook('preferences_list', array($this, 'settingsTable'));
    $this->add_hook('preferences_save', array($this, 'saveSettings'));
    $this->add_hook('template_object_userprefs', array($this, 'caldav_dialog'));
    $this->register_action('plugin.calendar_getCalDAVs', array($this, 'getCalDAVs'));
    $this->register_action('plugin.calendar_saveCalDAV', array($this, 'saveCalDAV'));
    $this->register_action('plugin.calendar_removeCalDAV', array($this, 'removeCalDAV'));
    $this->register_action('plugin.calendar_subscribe', array($this, 'subscribe'));
    
    /* Calendar Layers and Feeds */
    $this->add_hook('login_after', array($this, 'clearCache'));
    $this->register_action('plugin.calendar_clearCache', array($this, 'clearCache'));
    $this->register_action('plugin.calendar_replicate', array($this, 'replicate'));
    $this->register_action('plugin.calendar_renew', array($this, 'renew'));
    $this->add_hook('startup', array($this, 'showLayer'));
    $this->register_action('plugin.calendar_fetchalllayers', array($this, 'fetchAllLayers'));
    $this->register_action('plugin.calendar_setfilters', array($this, 'setFilters'));
    $this->add_hook('template_object_event_dialog', array($this, 'event_dialog'));
    /* print */
    $this->register_action('plugin.calendar_print_events', array($this, 'calprintevents'));
    $this->register_action('plugin.calendar_print_tasks', array($this, 'calprinttasks'));
    $this->add_hook('template_object_datetime', array($this, 'datetime'));

    /* upload/download */
    $this->add_hook('startup', array($this, 'upload_file'));
    $this->register_action('plugin.calendar_single_export_as_file', array($this, 'exportEvent'));
    
    /* reminders */
    $this->add_hook('startup', array($this, 'reminders_cron'));
    $this->add_hook('template_object_reminders_mailto', array($this, 'reminders_mailto'));
	// SELECT2 PLUGIN
	$this->add_hook('template_object_users_select_box', array($this, 'users_select_box'));
	
    $this->register_action('plugin.calendar_delete_reminder', array($this, 'reminder_delete'));
    $this->register_action('plugin.calendar_delete_reminders', array($this, 'reminders_delete'));
    if(!get_input_value('_framed', RCUBE_INPUT_GPC) && $rcmail->action != 'compose' && $rcmail->action != 'plugin.plugin_manager_update'){
      $this->add_hook('render_page', array($this, 'reminders_html'));
      $this->register_action('plugin.calendar_get_reminders', array($this, 'reminders_get'));
    }
    
    /* sync Events */
    $this->register_action('plugin.calendar_syncEvents', array($this, 'syncEvents'));
    
    /* send invitation */
    $this->add_hook('message_compose', array($this, 'message_compose'));
    $this->add_hook('message_compose_body', array($this, 'message_compose_body'));
    $this->register_action('plugin.calendar_send_invitation', array($this, 'sendInvitation'));
    $this->add_hook('message_outgoing_headers', array($this, 'add_ics_header'));
    $this->add_hook('message_sent', array($this, 'unlink_tempfiles'));

    /* backend actions */
    $this->register_action('plugin.newEvent', array($this, 'newEvent'));
    $this->register_action('plugin.newTask', array($this, 'newTask'));
    $this->register_action('plugin.editEvent', array($this, 'editEvent'));
    $this->register_action('plugin.editTask', array($this, 'editTask'));
    $this->register_action('plugin.moveEvent', array($this, 'moveEvent'));
    $this->register_action('plugin.resizeEvent', array($this, 'resizeEvent'));
    $this->register_action('plugin.removeEvent', array($this, 'removeEvent'));
    $this->register_action('plugin.getEvents', array($this, 'getEvents'));
    $this->register_action('plugin.getTasks', array($this, 'getTasks'));
    $this->register_action('plugin.getTask', array($this, 'getTask'));
    $this->register_action('plugin.exportEventsZip', array($this, 'exportEventsZip'));
    $this->register_action('plugin.exportEvents', array($this, 'exportEvents'));
    $this->register_action('plugin.calendar_purge', array($this, 'purgeEvents'));
    $this->register_action('plugin.calendar_searchEvents', array($this, 'searchEvents'));
    $this->register_action('plugin.calendar_searchSet', array($this, 'searchSet'));
    $this->register_action('plugin.saveical', array($this, 'import_ics'));
    
    $skin = $rcmail->config->get('skin');
    $this->include_script('program/js/move_button.js');
    if(!file_exists($this->home . '/skins/' . $skin . '/calicon.css')) {
      $skin = "default";
    }
    if($_SESSION['user_id']){
      $this->include_stylesheet('skins/' . $skin . '/calicon.css');
    }

    /* add taskbar button */
    $token = '';
    if($rcmail->task != 'settings' && in_array('compressor', $rcmail->config->get('plugins', array()))){
      $token = '&_s=' . md5($_SESSION['language'] .
        session_id() . 
        md5($this->generateCSS()) . 
        md5(serialize($rcmail->user->list_identities())) .
        $rcmail->config->get('skin', 'classic') . 
        self::$version . 
        self::$date . 
        serialize($rcmail->config->get('plugins', array())) .
        serialize($rcmail->config->get('plugin_manager_active', array()))
      );
    }
    $this->add_button(array(
      'name'    => 'calendar',
      'class'   => 'button-calendar',
      'content' => html::tag('span', array('class' => 'button-inner'), $this->gettext('calendar.calendar')),
      'href'    => './?_task=dummy&_action=plugin.calendar' . $token,
      'id'      => 'calendar_button',
      ), 'taskbar');
      
    $this->add_hook('render_page', array($this, 'planner_drag_drop'));
  }
  
 /************************
  *
  * plugin_manager support
  *
  ************************/
  
  function uninstall(){
    $this->backend->uninstall();
    rcmail::get_instance()->output->command('plugin.plugin_manager_success', '');
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
  
 /****************************
  *
  * Backend
  *
  ****************************/
  
  function get_demo($string){
    $temparr = explode("@",$string);
    return preg_replace ('/[0-9 ]/i', '', $temparr[0]) . "@" . $temparr[count($temparr)-1];   
  }
  
  function setupBackend($type = false){
    $rcmail = rcmail::get_instance();
    $rcmail->config->set('db_table_events', $this->dbtable);
    $rcmail->config->set('db_sequence_events', $this->dbtablesquence);
    $backend_type = $rcmail->config->get('backend', 'database');
    if(class_exists('calendar_plus')){
      $backend_type = calendar_plus::load_backend($backend_type);
    }
    else{
      if($_SESSION['user_id'] && $backend_type == 'caldav'){
        $backend_type = 'database';
        $a_prefs['backend'] = 'database';
        $rcmail->user->save_prefs($a_prefs);
      }
    }
    if(strtolower($this->get_demo($_SESSION['username'])) == strtolower(sprintf($rcmail->config->get('demo_user_account'),""))){
      //$backend_type = 'database';
    }
    if($type){
      $backend_type = $type;
    }
    else if($type = get_input_value('_backend', RCUBE_INPUT_GPC)){
      $backend_type = $type;
    }
    if($backend_type == 'database' || $backend_type == 'caldav'){
      require_once('program/backend/caldav.php');
    }
    else{
      if(!@require_once('program/backend/' . $backend_type . '.php')){
        $backend_type = 'database';
        require_once('program/backend/' . $backend_type . '.php');
      }
    }
    $default = array(
      'database' => '', // default db table
      'caldav' => '_caldav', // caldav db table (= default db table) extended by _caldav
    );
    $map = $rcmail->config->get('backend_db_table_map', $default);
    $rcmail->config->set('db_table_events', $rcmail->config->get('db_table_events','events') . $map[$backend_type]);
    $rcmail->config->set('db_sequence_events', $rcmail->config->get('db_table_events','events') . $map[$backend_type] . '_ids');
    if($backend_type == "caldav"){
      $save = array();
      $default_caldav = $rcmail->config->get('default_caldav_backend');
      if(is_array($default_caldav) &&
        ((!$rcmail->config->get('caldav_user') && !$rcmail->config->get('caldav_home') && !$rcmail->config->get('caldav_principals')) || $rcmail->config->get('caldav_protect'))
      ){
		// CALENDAR CHANGES
		// since, %u was creating problem, while sharing, changing pwd etc. we are replacing %u with the value directly
		// when user logs in, this is called. for some reason, the username is not displayed. therefore, replace %u with username only if username is present.
		if( ( $default_caldav['user'] == '%u' ) && ( strlen( $rcmail->user->get_username() ) > 0 ) )
		{
			$default_caldav['user'] = $rcmail->user->get_username();
		}
		if( ( strpos( $default_caldav['url'], '%u' ) != false ) && ( strlen( $rcmail->user->get_username() ) > 0 ) )
		{
			$default_caldav['url'] = str_replace( "%u", $default_caldav['user'], $default_caldav['url'] );
		}
        $save = array(
                        'backend' => 'caldav',
                        'caldav_user' => $default_caldav['user'],
                        'caldav_password' => $default_caldav['pass'],
                        'caldav_url' => $default_caldav['url'],
                        'caldav_home' => $default_caldav['home'],
                        'caldav_principals' => $default_caldav['principals'],
                        'caldav_auth' => $default_caldav['auth'],
                        'caldav_reminders' => $default_caldav['extr']
                      );
      }
	  
      //update caldav subscriptions
      $public_caldavs = $rcmail->config->get('public_caldavs', array());
      $available = array_merge($rcmail->config->get('caldavs', array()), $public_caldavs);
      $subscribed = $rcmail->config->get('caldavs_subscribed', array());
      $adjust = false;
      foreach($subscribed as $category => $caldav){
        if(!isset($available[$category])){
          $adjust = true;
          unset($subscribed[$category]);
        }
      }
      if($adjust){
        $save = array_merge($save, array('backend' => 'caldav', 'caldavs_subscribed' => $subscribed, 'ctags' => array()));
      }
      if($_SESSION['user_id'] && count($save) > 0){
        $rcmail->user->save_prefs($save);
      }
      $this->backend = new calendar_caldav($rcmail, 'caldav');
      if($adjust){
        $this->clearCache();
      }
    }
    else{
      $backend_type = 'database';
      $this->backend = new calendar_caldav($rcmail, 'database');
    }
    
    require_once('program/utils.php');
    $this->utils = new Utils($rcmail, $this->backend);
    $this->backend->utils = $this->utils;
    
    require_once('program/rrule.class.php');
    $GLOBALS['ical_weekdays'] = $ical_weekdays;
    return $backend_type;
  }
  
 /****************************
  *
  * Output
  *
  ****************************/
  
  function event_dialog($args){
    if($content = @file_get_contents(INSTALL_PATH . 'plugins/calendar/skins/includes/event.html')){
      $args['content'] = rcmail::get_instance()->output->just_parse($content);
    }
    return $args;
  }
  
  function planner_drag_drop($p){
    if($p['template'] == 'planner.planner'){
      $this->include_script("program/js/planner_drag_events.js");
    }
  }
  
  function cal_searchset($p){
    $rcmail = rcmail::get_instance();
    $content = '<div id="calsearchset"><form id="cal_search_fields" name="cal_search_fields" action="./?_task=dummy&_action=plugin.calendar"><ul class="caltoolbarmenu">';
    $fields = array('summary','description','location','categories');
    $cal_searchset = $rcmail->config->get('cal_searchset',array('summary'));
    $cal_searchset_flip = array_flip($cal_searchset);
    $checked = '';
    foreach($this->search_fields as $field => $val){
      if(isset($cal_searchset_flip[$field])){
        $checked = 'checked="checked" ';
      }
      $content .= '<li><input ' . $checked . 'type="checkbox" name="_cal_search_field_' . $field . '" value="' . $field . '" id="cal_search_field_' . $field . '" onclick="calendar_commands.searchFields($(' . "'" . '#cal_search_fields' . "'" . ').serialize())" /><label for="cal_search_field_' . $field . '">' . $this->gettext(str_replace('_','-',$field)) . '</label></li>';
      $checked = '';
    }
    $content .= '</ul></form></div>';
    $p['content'] = $content;
    return $p;
  }
  
  function startup($template = 'calendar.calendar') {
    $rcmail = rcmail::get_instance();
    $rcmail->output->add_label(
      'calendar.unloadwarning',
      'calendar.successfullyreplicated',
      'calendar.replicationtimeout',
      'calendar.resumereplication',
      'calendar.replicationfailed',
      'calendar.replicationincomplete',
      'calendar.removereminders'
    );
    $temparr = explode(".", $template);
    $domain = $temparr[0];
    $template = $temparr[1];
    
    $rcmail->output->set_pagetitle($this->gettext('calendar'));
    $rcmail->output->set_env('linkcolor', $rcmail->config->get('linkcolor','#212121'));
    $rcmail->output->set_env('rgblinkcolor', $rcmail->config->get('rgblinkcolor','rgb(33, 33, 33)'));
    $rcmail->output->set_env('caleot', CALEOT);
    $rcmail->output->set_env('caleot_unix', strtotime(CALEOT));
    $rcmail->output->set_env('rc_date_format', $rcmail->config->get('date_format', 'm/d/Y'));
    $rcmail->output->set_env('rc_time_format', $rcmail->config->get('time_format', 'H:i'));
    
    $ctags_saved = $rcmail->config->get('ctags', array());
    $this->ctags = $this->backend->getCtags();
    if(count($ctags_saved) > 0 && serialize($this->ctags) === serialize($ctags_saved)){
      $rcmail->output->set_env('noreplication', true);
    }

    $skin = $rcmail->config->get('skin');
    if(!file_exists($this->home . '/skins/' . $skin . '/fullcalendar.css')) {
      $skin = "classic";
    }
    $this->include_stylesheet('skins/' . $skin . '/fullcalendar.css');
    if($template == 'print')
      $this->include_stylesheet('skins/' . $skin . '/fullcalendar.print.css');
    $this->include_stylesheet('skins/' . $skin . '/calendar.css');
    $this->add_hook('template_object_cal_category_css', array($this, 'generateCSS'));
    $this->include_script("program/js/calendar.commands.js");
    $this->include_script('program/js/date.format.js');
    $this->include_script('program/js/timezone.js');
    $this->include_script('program/js/detect_timezone.js');
    
    if($template == 'calendar'){
      $this->include_script('program/js/date.js');
      $this->require_plugin('timepicker');
      $this->include_script('program/js/calendar.replicate.js');
    }
    if(file_exists("plugins/calendar/program/js/$template.gui.js"))
      $this->include_script("program/js/$template.gui.js");
    if(file_exists("plugins/calendar/program/js/$template.callbacks.js"))
      $this->include_script("program/js/$template.callbacks.js");
    $this->include_script('program/js/colors.js');
    $this->include_script('program/js/querystring.js');
    $this->include_script('program/js/fullcalendar.js');
    $this->include_script('program/js/calendar.jsonfeeds.js');
    if($template != 'print'){
      $this->include_script("program/js/$template.js"); 
    }
    else{
      if(class_exists('calendar_plus')){
        calendar_plus::load_print('js', 'print');
      }
    }
    if($template == 'calendar') {
      if($rcmail->config->get('backend') == database){
        $title = 'calendar.reload';
        $img = 'reload.png';
      }
      else{
        $title = 'calendar.backgroundreplication';
        $img = 'loading.gif';
      }
      if($skin == 'larry'){
        $this->add_button(array(
          'command' => 'plugin.calendar_newevent',
          'id' => 'calneweventbut',
          'class' => 'button calneweventbut',
          'href' => '#',
          'title' => 'calendar.new_event',
          'label' => 'calendar.new_event_short',
          'type' => 'link'),
          'toolbar'
        );
        if(class_exists('calendar_plus')){
          calendar_plus::load_users('mainnav');
        }
        $this->add_button(array(
          'command' => 'plugin.exportEventsZip',
          'id' => 'calexportbut',
          'class' => 'button calexportbut',
          'href' => './?_task=dummy&_action=plugin.exportEventsZip',
          'title' => 'calendar.export',
          'label' => 'calendar.export_short',
          'type' => 'link'),
          'toolbar'
        );
        if(class_exists('calendar_plus')){
          calendar_plus::load_import('mainnav');
        }
        if(class_exists('calendar_plus')){
          calendar_plus::load_print('mainnav');
        }
        if($rcmail->config->get('backend') == 'caldav'){
          $this->add_button(array(
            'command' => 'plugin.calendar_reload',
            'id' => 'calreloadbut',
            'class' => 'button calloadingbut',
            'href' => '#',
            'title' => $title,
            'label' => 'calendar.sync',
            'type' => 'link'),
            'toolbar'
          );
        }
      }
      else{
        if(class_exists('calendar_plus')){
          calendar_plus::load_users('mainnav');
        }
        $temparr = getimagesize(INSTALL_PATH . 'plugins/calendar/skins/' . $skin . '/images/export.png');
        $this->add_button(array(
          'command' => 'plugin.exportEventsZip',
          'id' => 'calexportbut',
          'width' => $temparr[0],
          'height' => $temparr[1],
          'href' => './?_task=dummy&_action=plugin.exportEventsZip',
          'title' => 'calendar.export',
          'imageact' => 'skins/' . $skin . '/images/export.png'),
          'toolbar'
        );
        if(class_exists('calendar_plus')){
          calendar_plus::load_import('mainnav');
        }
        $temparr = getimagesize(INSTALL_PATH . 'plugins/calendar/skins/' . $skin . '/images/preview.png');
        $this->add_button(array(
          'command' => 'plugin.calendar_print',
          'id' =>'calprintprevbut',
          'width' => $temparr[0],
          'height' => $temparr[1],
          'href' => '#',
          'title' => 'print',
          'imagepas' => 'skins/' . $skin . '/images/preview.png',
          'imageact' => 'skins/' . $skin . '/images/preview.png'),
          'toolbar'
        );
        if($rcmail->config->get('backend') == 'caldav'){
          $temparr = getimagesize(INSTALL_PATH . 'plugins/calendar/skins/' . $skin . '/images/' . $img);
          $this->add_button(array(
            'command' => 'plugin.calendar_reload',
            'id' => 'calreloadbut',
            'width' => $temparr[0],
            'height' => $temparr[1],
            'href' => '#',
            'title' => $title,
            'imageact' => 'skins/' . $skin . '/images/' . $img),
            'toolbar'
          );
        }
      }
    }
    if($template == "print"){
      if(class_exists('calendar_plus')){
        calendar_plus::load_print('popupnav');
      }
    }
    if($template != 'print'){
      $this->require_plugin('qtip');
      if($rcmail->config->get('hide_agenda_day_basic', false)){
        $rcmail->output->add_script('$("#upcoming-maincontainer").hide();', 'docready');
      }
      if(!class_exists('calendar_plus')){
        $rcmail->output->add_script('$("#calquicksearchbar").hide();', 'docready');
      }
    }
    $rcmail->output->add_label(
      'calendar.sunday',
      'calendar.monday',
      'calendar.tuesday',
      'calendar.wednesday',
      'calendar.thursday',
      'calendar.friday',
      'calendar.saturday'
    );
    $rcmail->output->send("$domain.$template");
  }
  
  function calprintevents() {
    $this->startup('calendar.print');
    exit;
  }
  
  function calprinttasks() {
    $rcmail = rcmail::get_instance();
    $skin = $rcmail->config->get('skin', 'classic');
    $this->include_stylesheet('skins/' . $skin . '/tasks.css');
    $this->include_script('program/js/calendar.commands.js');
    $temparr = getimagesize(INSTALL_PATH . 'plugins/calendar_plus/skins/' . $skin . '/images/print.png');
    $this->add_button(array(
      'command' => 'plugin.calendar_do_print',
      'id' => 'calprintbut',
      'width' => $temparr[0],
      'height' => $temparr[1],
      'href' => '#',
      'title' => 'print',
      'imagepas' => 'skins/' . $skin . '/images/print.png',
      'imageact' => 'skins/' . $skin . '/images/print.png'),
      'toolbar'
    );
    $rcmail->output->send('calendar.printtasks');
    exit;
  }

  function generateCSS($p=false,$e=true) {
    $rcmail = rcmail::get_instance();
    $categories = array();
    $css = "<style type=\"text/css\">\n";
    if($e){
      $css .= ".fc-event-skin {\n";
      $css .= "background-color: #" . $rcmail->config->get('default_category', 'C0C0C0') . ";\n";
      $css .= "border: 1px solid #" . $this->utils->getBorderColor($color)  . ";\n";
      $css .= "color: #" . $this->utils->getFontColor($rcmail->config->get('default_category', 'C0C0C0')) . ";\n";
      $css .="}\n";
      $categories = $rcmail->config->get('categories_preview', array());
      foreach($categories as $category => $color){
        $css .= "." . $category . " .fc-event-skin {\n";
        $css .= "background-color: #" . $color. ";\n";
        $css .= "border: 1px solid #" . $this->utils->getBorderColor($color)  . ";\n";
        $css .= "color: #" . $this->utils->getFontColor($color) . ";\n";
        $css .="}\n";
      }
    }
    $categories = array_merge((array)$rcmail->config->get('categories',array()), (array)$rcmail->config->get('public_categories',array()));
    if(!empty($categories)) {
      foreach ($categories as $class => $color) {
        $rcmail->output->set_env('class_' . asciiwords($class, true, ''), $class);
        $class = asciiwords($class, true, '');
        $css .= "." . $class . "{\n";
        $css .= "background-color: #" . $color . ";\n";
        $css .= "border: 1px solid #" . $this->utils->getBorderColor($color)  . ";\n";
        $css .= "color: #" . $this->utils->getFontColor($color) . ";\n";
        $css .= "}\n";
      }
    }
    $css .= "</style>\n";
    if($p){
      $p['content'] = $css;
      $ret = $p;
    }
    else{
      $ret = $css;
    }
    return $ret;
  }

  function generateHTML() {
    $rcmail = rcmail::get_instance();
    $categories = $rcmail->config->get('categories', array());
    $public_categories = $rcmail->config->get('public_categories', array());
    $public_caldavs = $rcmail->config->get('public_caldavs', array());
    foreach($public_caldavs as $caldav => $props){
      if(isset($public_categories[$caldav]) && !$props['readonly']){
        $categories[$caldav] = $public_categories[$caldav];
      }
    }
    ksort($categories);
    $caldavs = $rcmail->config->get('caldavs', array());
    $caldavs_subscribed = $rcmail->config->get('caldavs_subscribed', false);
    if(!is_array($caldavs_subscribed)){
      $caldavs_subscribed = $rcmail->config->get('caldavs', array());
    }
    $caldavs_subscribed = array_merge($caldavs_subscribed, $rcmail->config->get('public_caldavs', array()));
    $merge = array();
    if(is_array($_SESSION['detected_caldavs'])){
      foreach($_SESSION['detected_caldavs'] as $category => $props){
        if(!$categories[$category]){
          $merge[$category] = '#' . $rcmail->config->get('default_category', 'c0c0c0');
        }
      }
    }
    $categories = array_merge($merge, $categories);
	
    $this->categories = $categories;
    $options = '';
    if(is_array($categories)){
	  // CHANGE DEFAULT CALENDAR TEXT
	  // $short = $rcmail->config->get('default_category_label', $this->gettext('defaultcategory'));
	  $default_calendar_label = str_replace( "%u", $_SESSION[ "username" ], $rcmail->config->get('default_category_label', $this->gettext('defaultcategory')) );
	  $short = $default_calendar_label; // short value is displayed in select box of new event
      if(strlen($short) > 30){
        $short = substr($short, 0, 15) . '...';
      }
	  
	  // CHANGE DEFAULT CALENDAR TEXT
      /* $default = html::tag('option', array('value' => $rcmail->config->get('default_category_label', $_SESSION[ 'username' ]), 'style' => 'background-color:#' . $rcmail->config->get('default_category', 'c0c0c0') . '; color:#' . $this->utils->getFontColor($rcmail->config->get('default_category', 'c0c0c0'))), $short); */
	  $default = html::tag('option', array('value' => $_SESSION[ "username" ], 'style' => 'background-color:#' . $rcmail->config->get('default_category', 'c0c0c0') . '; color:#' . $this->utils->getFontColor($rcmail->config->get('default_category', 'c0c0c0'))), $short);
      if($rcmail->config->get('backend') != 'caldav' || $rcmail->config->get('default_caldav_subscribed', true)){
        $options .= $default;
      }
	  
      foreach ($categories as $class => $color) {
        $display = 'block';
        if(isset($caldavs[$class]) && !isset($caldavs_subscribed[$class])){
          $display = 'none';
        }
        $short = $class;
        if(strlen($class) > 30){
          $short = substr($class, 0, 15) . '...';
        }
        $options .= html::tag('option', array('id' => 'option_' . asciiwords($class, true, ''), 'style' => "display:" . $display . "; background-color:#$color; color:#" . $this->utils->getFontColor($color), 'value' => $class), $short);
      }
    }
    return html::tag('select', array('id' => 'categories', 'name' => 'categories'), $options);
  }
  
 /****************************
  *
  * Reminders
  *
  ****************************/
  
  function reminders_cron($args){
    $rcmail = rcmail::get_instance();
    if(empty($_SESSION['user_id']) && !empty($_GET['_cron']) && $this->is_cron_host()){
      if(get_input_value('_import', RCUBE_INPUT_GPC)){
        $this->reminders_import();
      }
      else if(get_input_value('_schedule', RCUBE_INPUT_GPC)){	
        if($user_id = get_input_value('_userid', RCUBE_INPUT_GPC)){
          $rcmail->user->ID = $user_id;
          $event_id = get_input_value('_event_id', RCUBE_INPUT_GPC);
          $event = $this->backend->getEvent($event_id);
          if(count($event) > 0){
            $this->backend->scheduleReminders($event);
          }
        }
      }
      exit;
    }
    return $args;
  }

  function reminders_get(){
    $rcmail = rcmail::get_instance();
    $display = false;
    if($_SESSION['reminders'] && time() - $_SESSION['reminders']['ts'] < 180){
      $reminders = $_SESSION['reminders']['props'];
    }
    else{
      $reminders = (array) $this->backend->getReminders(time(), 'popup');
      if(count($reminders) > 0){
        $reminders_hash = '';
        foreach($reminders as $key => $reminder){
          $reminders_hash .= $reminder['uid'] . 
                            $reminder['start'] . 
                            $reminder['end'] . 
                            $reminder['due'] . 
                            $reminder['title'];
        }
        uksort($reminders, 'cmp_reminders');
        $reminders_hash = md5($reminders_hash);
        $old_reminders_hash = $rcmail->config->get('reminders_hash');
        if($old_reminders_hash != $reminders_hash){
          if($rcmail->user->ID == $_SESSION['user_id']){
            $rcmail->user->save_prefs(array('reminders_hash' => $reminders_hash));
            $display = true;
          }
        }
      }
      $_SESSION['reminders'] = array('ts' => time(), 'props' => $reminders);
    }
    $rcmail->output->command('plugin.calendar_displayReminders', array($display, $reminders));
  }
  
  function reminders_import(){
    $rcmail = rcmail::get_instance();
    $userid = get_input_value('_userid', RCUBE_INPUT_GPC);
    $res = $rcmail->db->query('SELECT * FROM ' . get_table_name('users') . ' WHERE user_id=? LIMIT 1', $userid);
    while($ret = $rcmail->db->fetch_assoc($res)){
      $user = $ret;
    }
    $rcmail->user->ID = $user['user_id'];
    if(!$_SESSION['password']){
      $_SESSION['password'] = $user['password'];
    }
    $preferences = unserialize($user['preferences']);
    if(is_array($preferences)){
      foreach($preferences as $key => $val){
        $rcmail->config->set($key, $val);
      }
    }
    if($preferences['backend'] == 'caldav'){
      $this->setupBackend('caldav');
      //$start = time();
      //$end = $start + 86400;
      $start = 0;
      $end = time() + 86400;
      $this->backend->replicateEvents($start, $end, false, 'alarms');
      $caldavs = $rcmail->config->get('caldavs', array());
      foreach($caldavs as $caldav => $props){
        $this->backend->replicateEvents($start, $end, $caldav, 'alarms');
      }
    }
    exit;
  }
  
  function reminders_html($p){
    if($_SESSION['user_id']){
      $rcmail = rcmail::get_instance();
      if($rcmail->action != 'plugin.calendar_tests'){
        $this->require_plugin('qtip');
        $this->include_script("program/js/calendar.reminders.js");
        $skin = $rcmail->config->get('skin');
        if(!file_exists($this->home . '/skins/' . $skin . '/fullcalendar.css')) {
          $skin = "classic";
        }
        $this->include_stylesheet('skins/' . $skin . '/reminders.css');
        $rcmail->output->add_footer(html::div(array('id' => 'remindersloading')));
        $rcmail->output->add_footer(html::div(array('id' => 'remindersoverlay')));
        $rcmail->output->add_footer(html::div(array('id' => 'remindersoverlaysmall')));
        $rcmail->output->add_label(
          'calendar.starts',
          'calendar.ends',
          'calendar.at',
          'calendar.reminders',
          'calendar.started',
          'calendar.terminated',
          'calendar.minimize',
          'calendar.maximize',
          'calendar.remindersloading'
        );
      }
    }
    return $p;
  }
  
  function reminders_delete(){
    $rcmail = rcmail::get_instance();
    if(isset($_SESSION['reminders']['ts'])){
      $now = (int) $_SESSION['reminders']['ts'];
    }
    else{
      $now = time();
    }
    $this->backend->removeReminder(false, false, $now);
    $rcmail->session->remove('reminders');
    $this->reminders_get();
  }
  
  function reminder_delete($id=false, $event_id=false){
    $rcmail = rcmail::get_instance();
    if(!$id)
      $id = get_input_value('_id', RCUBE_INPUT_POST);
    if(!$event_id)
      $event_id = get_input_value('_event_id', RCUBE_INPUT_POST);
    if($id && $event_id){
      $rcmail->session->remove('reminders');
      $this->backend->removeReminder($id, $event_id, time());
    }
    $this->reminders_get();
  }
  
  function reminders_mailto($p){
	global $RC_HELP;
	
    $rcmail = rcmail::get_instance();
	
	// to display only username in select box of remainder email 
    /* $list = $rcmail->user->list_identities();
    $options = '';
    foreach ($list as $idx => $row){
      $options .= '<option value="' . $row['email'] . '">' . trim($row['name'] . ' &lt;' . rcube_idn_to_utf8($row['email']) .'&gt;') . '</option>' . "\r\n";
    } */
	
	$username = $RC_HELP->user_name;
	$email = $RC_HELP->email;
	$options .= '<option value="' . $email . '">' . trim($username . ' &lt;' . rcube_idn_to_utf8($email) .'&gt;') . '</option>' . "\r\n";
	
    $select = '<select name="_remindermailto" id="remindermailto">' . $options . '</select>' . "\r\n";
    $p['content'] = $select;
    if(class_exists('scheduled_sending')){
      $rcmail->output->add_script("$('#custommail').show();", 'docready');
    }
    return $p;
  }
  
  /***************************
   *
   * Select2 Select box with users email
   *
   *****************************/
   
   function users_select_box($p) {
	$rcube = rcube::get_instance();
	$rc_help  = new rc_help();
	$user_domain = $rcube->user->get_username( 'domain' );
	$user_details = $rc_help->get_user_details( $user_domain );
	
	$user_email_arr = $user_details[ 'user_email' ];
	$user_name_arr = $user_details[ 'user_name' ];
	
	$select_open_tag = '<select style="width:250px;" id="invitees_details_dropdown" >';
	$options = "";
	
	for( $i = 0; $i < count( $user_email_arr ); $i++ )
	{
		$options .= '<option value="'.$user_email_arr[ $i ].'|'.$user_name_arr[ $i ].'">'.$user_email_arr[ $i ].'</option>';
	}
	$select_close_tag = '</select>';
	
	$p[ 'content' ] = $select_open_tag.$options.$select_close_tag;
	
	return $p;
   }
  
 /****************************
  *
  * Import ICS Attachments
  *
  ****************************/

  function upload_file() {
    $rcmail = rcmail::get_instance();
    if($rcmail->action == "plugin.calendar_upload"){
      if($_FILES['calimport']['error'] == 0){
        if($content = @file_get_contents($_FILES['calimport']['tmp_name'])){
          @unlink($_FILES['calimport']['tmp_name']);
          $content = rcube_charset_convert($content, $this->utils->detect_encoding($content));
          $content = str_replace("\r\n ", "", $content);
          $category = get_input_value('_category', RCUBE_INPUT_POST);
          $success = $this->utils->importEvents($content, false, false, false, false, false, $category);
        }
      }
      $error_msg = $this->gettext('icalsavefailed');
      if($success){
        $text = sprintf($this->gettext('importedsuccessfullycnt'), $success);
        $type = 'confirmation';
      }
      else{
        $text = $error_msg;
        $type = 'error';
      }
      $page = 
        '<!DOCTYPE html>' . 
        html::tag('html', null,
          html::tag('head', null,
            html::tag('script', array('type' => 'text/javascript'), 'parent.rcmail.display_message("' . $text . '", "' . $type . '"); parent.rcmail.http_post("plugin.getTasks", "");')
          ) . html::tag('body')
        );
      echo $page;
      exit;
    }
  }
  
  function import_ics() {
    $uid = get_input_value('_uid', RCUBE_INPUT_POST);
    $mbox = get_input_value('_mbox', RCUBE_INPUT_POST);
    $mime_id = get_input_value('_part', RCUBE_INPUT_POST);
    $items = get_input_value('_items', RCUBE_INPUT_POST);
    $category = get_input_value('_category', RCUBE_INPUT_POST);
    $rcmail = rcmail::get_instance();
    $part = $uid && $mime_id ? $rcmail->imap->get_message_part($uid, $mime_id, NULL, NULL, NULL, true) : null;
    $part = rcube_charset_convert($part, $this->utils->detect_encoding($part));
    $error_msg = $this->gettext('icalsavefailed');
    $part = rcube_charset_convert($part, $this->utils->detect_encoding($part));
    $success = $this->utils->importEvents($part, false, false, false, $items, false, $category);
    if($success){
      $rcmail->output->command('display_message', $this->gettext('importedsuccessfully'), 'confirmation');
    }
    else{
      $rcmail->output->command('display_message', $error_msg, 'error');
    }    
    $rcmail->output->send();
  }

 /****************************
  *
  * Email Tasks
  *
  ****************************/
  
  function sendInvitation() {
    $rcmail = rcmail::get_instance();  
    $id = get_input_value('_id', RCUBE_INPUT_GPC);
    $edit = get_input_value('_edit', RCUBE_INPUT_GPC);
    if($edit == 'false'){
      $edit = 0;
      $events_table = $rcmail->config->get('db_table_events', 'events');
      $rcmail->config->set('db_table_events',$rcmail->config->get('db_table_events_cache', 'events_cache'));
      $event = $this->utils->arrayEvent($id);
      $rcmail->config->set('db_table_events',$events_table);
    }
    else{
      $edit = 1;
      $event = $this->utils->arrayEvent($id);
    }
    $event['start'] = $event['start'];
    $event['end'] = $event['end'];
    $event['recur'] = $event['recurring'];
    $ical = $this->utils->exportEvents(0, 0, array($event));
    $temp_dir = slashify($rcmail->config->get('temp_dir','temp/'));
    $file = slashify($temp_dir) . md5($_SESSION['username'] . time()) . ".ics";
    $_SESSION['icalatt'] = $file;
    if(file_put_contents($file, $ical)){
      $event['timestamp'] = false;
      $body = $this->notifyEvents(array(0 => $event), true);
      $file = $temp_dir . md5($_SESSION['username'] . time()) . ".html";
      $_SESSION['htmlatt'] = $file;
      file_put_contents($file, str_replace('\\n', "\r\n", $body));
      $rcmail->output->redirect(array('_task'=> 'mail', '_action' => 'compose', '_attachics' => 1, '_eid' => $id, '_edit' => $edit));
    }
    else{
      $rcmail->output->redirect(array('_task'=> 'dummy', '_action' => 'plugin.calendar'));
    }
    exit;
  }
  
  function add_ics_header($h){
    if($_SESSION['icalatt'] && $_SESSION['htmlatt']){
      $rcmail = rcmail::get_instance();
      $h['headers']['X-RC-Attachment'] = 'ICS';
      $rcmail->session->remove('icalatt');
      $rcmail->session->remove('htmlatt');
    }
    return $h;
  }
  
  function unlink_tempfiles($args){
    $rcmail = rcmail::get_instance();  
    $rcmail->session->remove('icalatt');
    $rcmail->session->remove('htmlatt');
    if(is_array($_SESSION['compose_ids'])){
      foreach($_SESSION['compose_ids'] as $id => $val){
        if(is_array($_SESSION['compose_data_' . $id]['attachments'])){
          foreach($_SESSION['compose_data_' . $id]['attachments'] as $key => $props){
            if(($props['name'] == 'ical.html' || $props['name'] == 'ical.ics') && $props['path']){
              @unlink($props['path']);
              if(strtolower(substr($key, 0, strlen('db_attach'))) == 'db_attach'){
                $rcmail->db->query(
                  "DELETE FROM ".get_table_name('cache')."
                  WHERE  user_id=?
                  AND    cache_key=?",
                  $_SESSION['user_id'],
                  $key
                );
              }
            }
          }
        }
      }
    }
    
  }
  
  function message_compose_body($args){
    $rcmail = rcmail::get_instance();
    $_SESSION['compose_ids'][get_input_value('_id', RCUBE_INPUT_GET)] = 1;
    if(class_exists('compose_newwindow')){
      $rcmail->output->add_script("
        var mypopup;
        if(opener && opener.rcmail && opener.rcmail.env && opener.rcmail.env.history_back){
          opener.history.back(-1);
          mypopup =  this;
          window.setTimeout('mypopup.focus()',1000);
        }
      ");
    }
    return $args;
  }
  
  function message_compose($args){
    if(empty($args['param']['attachics']))
      return $args;
    $rcmail = rcmail::get_instance();
    $ics = $_SESSION['icalatt'];
    $html = $_SESSION['htmlatt'];
    if(file_exists($ics) && file_exists($html)){
      $edit = $args['param']['edit'];
      if($edit == 0){
        $events_table = $rcmail->config->get('db_table_events', 'events');
        $rcmail->config->set('db_table_events',$rcmail->config->get('db_table_events_cache', 'events_cache'));
        $event = $this->utils->arrayEvent($args['param']['eid']);
        $rcmail->config->set('db_table_events',$events_table);
      }
      else{
        $event = $this->utils->arrayEvent($args['param']['eid']);
      }
      $args['attachments'][] = array('path' => $html, 'name' => "ical.html", 'mimetype' => "text/html");
      $args['attachments'][] = array('path' => $ics, 'name' => "ical.ics", 'mimetype' => "text/calendar");
      $args['param']['subject'] = rcube_label('calendar.invitation_subject') . ": " . $event['summary'];
      $_SESSION['calendar_attachments'][] = $html;
      $_SESSION['calendar_attachments'][] = $ics;
    }
    else{
      $rcmail->output->redirect(array('_task'=> 'dummy', '_action' => 'plugin.calendar'));    
    }
    return $args; 
  }
  
  function notifyEvents($events = false, $getbody = false)  {
    if(!is_array($events))
      return;
    $rcmail = rcmail::get_instance();
    if($rcmail->config->get('cal_notify') || $getbody){
      $webmail_url = $this->getURL();
      $from = $rcmail->user->data['username'];
      if(!strstr($from,'@'))
        $from = $from . '@' . $rcmail->config->get('mail_domain');
      $to = $rcmail->config->get('cal_notify_to',$_SESSION['username']);
      if(!empty($this->userid) && $rcmail->task == "dummy"){
        $arr = $this->getUser($this->userid);
        $edited_by = $to;
        $to = $arr['username'];
      }
      if(!strstr($to,'@'))
        $to = $to . '@' . $rcmail->config->get('mail_domain');
      foreach($events as $key => $val){
        if($val['clone'])
          continue;
        if($val['summary'])
          $val['title'] = $val['summary'];
        if($val['all_day'])
          $val['allDay'] = $val['all_day'];
        if(is_numeric($val['start'])){
          $val['start_unix'] = $val['start'];
          $val['start'] = gmdate('Y-m-d\TH:i:s.000+00:00',$val['start']);
        }
        if(is_numeric($val['end'])){
          $val['end_unix'] = $val['end'];
          $val['end'] = gmdate('Y-m-d\TH:i:s.000+00:00',$val['end']);
        }
        if($val['rr'])
          $val['recur'] = $val['rr'];
        if($val['categories']){
          $val['classNameDisp'] = asciiwords($val['categories'],true,'');
        }
        if($val['timestamp'] != '0000-00-00 00:00:00'){
          if($val['title'])
            $subject = $val['title'] . " [" . $this->gettext('calendar') . "]";
          else
            $subject = "[" . $this->gettext('calendar') . "]";
          if(strlen($subject) > 72)
            $subject = substr($subject, 0, 69) . '...';
          $allDay = "";
          if($val['allDay']){
            $allDay = "(".$this->gettext('all-day').")";
          }
          $nl = "\r\n";
          $body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN">' . $nl;
          $body .= '<html><head>' . $nl;
          $body .= '<meta http-equiv="content-type" content="text/html; charset=' . RCMAIL_CHARSET . '" />' . $nl;
          $body .= '</head>' . $nl;
          $body .= '<body>' . $nl;
          if($val['del'] == 1){
            $body .= '<table><tr><td colspan="2"><b>' . $this->gettext('removed') . '</b></td></tr>'.$nl;
            $body .= '<tr><td colspan="2"><hr /></td></tr></table>'.$nl;
          }
          if($edited_by){
            $body .= '<table><tr><td>' . $this->gettext('edited_by') . ':</td><td>' . $edited_by . '</td></tr>'.$nl;
            $body .= '<tr><td colspan="2"><hr /></td></tr></table>'.$nl;
          }
           
          $body .= '<div id="rcical"><table>'.$nl;
          if($val['title'])
            $body .= '<tr><td>' . $this->gettext('summary') . ': </td><td>' . $val['title'] . '</td></tr>'.$nl;
          if($val['description'])  
            $body .= '<tr><td>' . $this->gettext('description') . ': </td><td>' . $val['description'] . '</td></tr>'.$nl;
          if($val['location'])  
            $body .= '<tr><td>' . $this->gettext('location') . ': </td><td>' . $val['location'] . '</td></tr>'.$nl;
          if($val['className'])  
            $body .= '<tr><td>' . $this->gettext('category') . ': </td><td>' . $val['classNameDisp'] . '</td></tr>'.$nl;
          if($val['start'] != 0)  
            $body .= '<tr><td>' . $this->gettext('start') . ': </td><td>' . gmdate($rcmail->config->get('date_long'), strtotime($val['start'])) . '</td></tr>'.$nl;
          if($val['due'] != 0)
            $body .= '<tr><td>' . $this->gettext('due') . ': </td><td>' . gmdate($rcmail->config->get('date_long'), $val['due']) . '</td></tr>'.$nl;
          if($val['end'] && $val['end'] > $val['start'])
            $body .= '<tr><td>' . $this->gettext('end') . ': </td><td>' . gmdate($rcmail->config->get('date_long'), strtotime($val['end'])) . '</td></tr>'.$nl;
          if($val['recur'] != 0){
            $body .= '<tr><td>' . $this->gettext('recur') . ': </td><td>';
            $a_weekdays = array(0=>'SU',1=>'MO',2=>'TU',3=>'WE',4=>'TH',5=>'FR',6=>'SA');
            $freq = "";
            $t = $val['expires'];
            if(!$t){
              $t = 2082758399;
            }
            $until = "UNTIL=" . date('Ymd',$t) . "T235959";
            switch($val['rr']){
              case 'd':
                if($val['recur'] == 86401){
                  $body .= $this->gettext('workday');
                  $freq = 'RRULE:FREQ=DAILY;' . $until . ';BYDAY=';
                  foreach($rcmail->config->get('workdays') as $key1 => $val1){
                    $freq .= $a_weekdays[$val1] . ",";
                  }
                  $freq = substr($freq,0,strlen($freq)-1);
                }
                else{
                  $intval = round($val['recur'] / 86400,0);
                  $body .= $this->gettext('day');
                  $freq = 'RRULE:FREQ=DAILY;' . $until . ';INTERVAL=' . $intval;
                }
                break;
              case 'w':
                $intval = round($val['recur'] / 604800,0);
                $body .= $this->gettext('week');
                $freq = 'RRULE:FREQ=WEEKLY;' . $until . ';INTERVAL=' . $intval;
                break;                         
              case 'm':
                $intval = round($val['recur'] / 2592000,0);
                $body .= $this->gettext('month');
                $freq = 'RRULE:FREQ=MONTHLY;' . $until . ';INTERVAL=' . $intval;
                break;
              case 'y':
                $intval = round($val['recur'] / 31536000,0);
                $body .= $this->gettext('year');
                $freq = 'RRULE:FREQ=YEARLY;' . $until . ';INTERVAL=' . $intval;
                break;
            }
            if($val['occurrences'] > 0)
              $freq .= ';COUNT=' . $val['occurrences'];
            if($val['byday'])
              $freq .= ';BYDAY=' . $val['byday'];
            if($val['bymonth'])
              $freq .= ';BYMONTH=' . $val['bymonth'];
            if($val['bymonthday'])
              $freq .= ';BYMONTHDAY=' . $val['bymonthday'];
            $body .= '</td></tr>'.$nl;
            $body .= '<tr><td>RRULE:</td><td>' . str_replace("RRULE:","",$freq) . '</td></tr>' . $nl;
            $body .= '<tr><td>' . $this->gettext('expires') . ': </td><td>' . substr(date($rcmail->config->get('date_long'),$t),0,10) . '</td></tr>'.$nl;            
          }
          if($val['del'] != 1 && $getbody == false)
            $body .= '<tr><td>URL: </td><td><a href="' . $webmail_url . '?_task=dummy&amp;_action=plugin.calendar&amp;_date=' . $val['start_unix'] . '" target="_new">' . $this->gettext('click_here') . '</a></td></tr>'.$nl;  
          $body .= '</table></div>'.$nl;
          $body .= '</body></html>'.$nl;
          if($getbody)
            return $body;
			
          if (function_exists('mb_encode_mimeheader')){
            mb_internal_encoding(RCMAIL_CHARSET);
            $subject= mb_encode_mimeheader($subject,
              RCMAIL_CHARSET, 'Q', $rcmail->config->header_delimiter(), 8);
          }
          else{
            $subject = '=?UTF-8?B?'.base64_encode($subject). '?=';
          }

          $ctb = md5(rand() . microtime());

          $headers  = "Return-Path: $from\r\n";
          $headers .= "MIME-Version: 1.0\r\n";
          $headers .= "X-RC-Attachment: ICS\r\n";
          $headers .= "Content-Type: multipart/mixed; boundary=\"=_$ctb\"\r\n";
          $headers .= "Date: " . date('r', time()) . "\r\n";
          $headers .= "From: $from\r\n";
          $headers .= "To: $to\r\n";
          $headers .= "Subject: $subject\r\n";
          $headers .= "Reply-To: $from\r\n";

          $msg_body  = "--=_$ctb";
          $msg_body .= "\r\n";
          $mpb = md5(rand() . microtime());
          $msg_body .= "Content-Type: multipart/alternative; boundary=\"=_$mpb\"\r\n\r\n";

          $txt_body  = "--=_$mpb";
          $txt_body .= "\r\n";
          $txt_body .= "Content-Transfer-Encoding: 7bit\r\n";
          $txt_body .= "Content-Type: text/plain; charset=" . RCMAIL_CHARSET . "\r\n";
          $LINE_LENGTH = $rcmail->config->get('line_length', 72);  
          $h2t = new html2text($body, false, true, 0);
          $txt = rc_wordwrap($h2t->get_text(), $LINE_LENGTH, "\r\n");
          $txt = wordwrap($txt, 998, "\r\n", true);
          $txt_body .= "$txt\r\n";            
          $txt_body .= "--=_$mpb";
          $txt_body .= "\r\n";
          
          $msg_body .= $txt_body;
          
          $msg_body .= "Content-Transfer-Encoding: quoted-printable\r\n";
          $msg_body .= "Content-Type: text/html; charset=" . RCMAIL_CHARSET . "\r\n\r\n";
          $msg_body .= str_replace("=","=3D",$body);
          $msg_body .= "\r\n\r\n";
          $msg_body .= "--=_$mpb--";
          $msg_body .= "\r\n\r\n";
          
          $ics  = "--=_$ctb";
          $ics .= "\r\n";
          $ics .= "Content-Type: text/calendar; name=calendar.ics; charset=" . RCMAIL_CHARSET . "\r\n";
          $ics .= "Content-Transfer-Encoding: base64\r\n\r\n";
          
          $val['start'] = $val['start_unix'];
          $val['end'] = $val['end_unix'];
          $ical = $this->utils->exportEvents($val['start_unix'],$val['end_unix'],array(0=>$val),true);

          $ics .= chunk_split(base64_encode($ical), $LINE_LENGTH, "\r\n");
          $ics .= "--=_$ctb--";
          
          $msg_body .= $ics;
          
          // send message
          if (!is_object($rcmail->smtp))
            $rcmail->smtp_init(true);
          $rcmail->smtp->send_mail($from, $to, $headers, $msg_body);
        }
      }
      $this->backend->removeTimestamps();
      $this->backend->purgeEvents();
    }
  }

 /****************************
  *
  * Event Handling
  *
  ****************************/
  
  function errorEvent($msgid){
    $rcmail = rcmail::get_instance();
    if($rcmail->config->get('backend') == 'caldav' && $_SESSION['user_id']){
      $save['ctags'] = array();
      $rcmail->user->save_prefs($save);
      $_GET['_year'] = date('Y', get_input_value('_start', RCUBE_INPUT_POST));
      $_GET['_errorgui'] = 1;
      $this->clearCache();
      $this->syncEvents(true);
    }
    $rcmail->output->command('plugin.calendar_errorGUI', $msgid);
  }
  
  function searchSet(){
    $rcmail = rcmail::get_instance();
    foreach($_POST as $key => $val){
      if($key != '_remote' && isset($this->search_fields[$val])){
        $arr_sav[] = $val;
      }
    }
    $arr_sav = array('cal_searchset' => $arr_sav);
    if($_SESSION['user_id'])
      $rcmail->user->save_prefs($arr_sav);
    $rcmail->output->command('plugin.calendar_triggerSearch', '');
  }
  
  function searchEvents() {
    $rcmail = rcmail::get_instance();
    $str = trim(get_input_value('_str', RCUBE_INPUT_POST));
    $events = array();
    $filters = array();
	/* CHANGE DEFAULT CALENDAR TEXT */
	$default_caldav_label = str_replace( "%u", $_SESSION[ 'username' ], $this->gettext('default_category_label') );
    // $ret = $this->backend->searchEvents($str, $this->gettext('default_category_label'));
    $ret = $this->backend->searchEvents($str, $default_caldav_label);
    if(class_exists('calendar_plus')){
      $ret = calendar_plus::load_search($ret, $str);
      $events = $ret['events'];
      $filters = $ret['filters'];
      $rows = str_replace("\\n", "<br />", $ret['rows']);
    }
    if(is_array($events)){
      $events = $this->utils->arrayEvents(0, strtotime(CALEOT), $category=false, $edit=true, $links=false, $returndel=false, $events);
    }
    $rcmail->output->command('plugin.calendar_searchEvents', array('rows'=>$rows,'events'=>$events, 'filters' => array_flip($filters)));
  }

  function setDates() {
    $allDay = get_input_value('_allDay', RCUBE_INPUT_POST);
    $stz = date_default_timezone_get();
    date_default_timezone_set($_SESSION['tzname']);
    if($start = trim(get_input_value('_planner_datetime', RCUBE_INPUT_POST))){
      $start = strtotime($start);
      $end = $start + (int) (60 * 60 * rcmail::get_instance()->config->get('default_duration',1));
    }
    else{
      $start = trim(get_input_value('_start', RCUBE_INPUT_POST));
      if(!$start)
        $start = strtotime(trim(get_input_value('startdate', RCUBE_INPUT_POST)) . " " . trim(get_input_value('starttime', RCUBE_INPUT_POST)) . ":00");
      $end = trim(get_input_value('_end', RCUBE_INPUT_POST));
      if(!$end)
        $end = strtotime(trim(get_input_value('enddate', RCUBE_INPUT_POST)) . " " . trim(get_input_value('endtime', RCUBE_INPUT_POST)) . ":00");
    }
    $gmtoffset = date('O',$start);
    $gmtoffset = (int) substr($gmtoffset,0,3) + (int) substr($gmtoffset, 3) / 60;
    $offset = 0;
    $cnfoffset = 0;
    /*$ctz = $this->getClientTimezone();
    if($ctz != $gmtoffset){
      $ctzname = $this->getClientTimezoneName($ctz);
      if($ctzname){
        $cnfoffset = ($gmtoffset - $ctz) * 3600;
        if(date('I',time()) < date('I', $start))
          $cnfoffset = $cnfoffset - 3600;
        $gmtoffset = $ctz;
        date_default_timezone_set($ctzname);
      }
    }*/
    $start = $start + $offset + $cnfoffset;
    $end = $end + $offset + $cnfoffset;
    if($expires = strtotime(trim(get_input_value('expires', RCUBE_INPUT_POST)))){
      $expires = $expires + $offset + 86400;
    }
    date_default_timezone_set($stz);
    return array(
      'start'   => $start,
      'end'     => $end,
      'expires' => $expires,
      'allDay'  => 0
    );
  }
  
  function newTask(){
    $rec = array();
    $rec['raw'] = get_input_value('_raw', RCUBE_INPUT_POST);
    $rec = $this->prepare_task($rec);
    $_POST['_summary'] = $rec['title'];
    $_POST['_start'] = $rec['date'];
    $this->newEvent('vtodo');
  }
  
  function prepare_task($rec){
    // try to be smart and extract date from raw input
    if($rec['raw']){
      foreach(array('today','tomorrow','sunday','monday','tuesday','wednesday','thursday','friday','saturday','sun','mon','tue','wed','thu','fri','sat') as $word){
        $locwords[] = '/^' . preg_quote(mb_strtolower($this->gettext($word))) . '\b/i';
        $normwords[] = $word;
        $datewords[] = $word;
      }
      foreach(array('jan','feb','mar','apr','may','jun','jul','aug','sep','oct','now','dec') as $month){
        $locwords[] = '/(' . preg_quote(mb_strtolower($this->gettext('long'.$month))) . '|' . preg_quote(mb_strtolower($this->gettext($month))) . ')\b/i';
        $normwords[] = $month;
        $datewords[] = $month;
      }
      foreach(array('on','this','next','at') as $word){
        $fillwords[] = preg_quote(mb_strtolower($this->gettext($word)));
        $fillwords[] = $word;
      }

      $raw = trim($rec['raw']);
      $date_str = '';

      // translate localized keywords
      $raw = preg_replace('/^(' . join('|', $fillwords) . ')\s*/i', '', $raw);
      $raw = preg_replace($locwords, $normwords, $raw);

      // find date pattern
      $date_pattern = '!^(\d+[./-]\s*)?((?:\d+[./-])|' . join('|', $datewords) . ')\.?(\s+\d{4})?[:;,]?\s+!i';
      if(preg_match($date_pattern, $raw, $m)){
        $date_str .= $m[1] . $m[2] . $m[3];
        $raw = preg_replace(array($date_pattern, '/^(' . join('|', $fillwords) . ')\s*/i'), '', $raw);
        // add year to date string
        if($m[1] && !$m[3])
          $date_str .= date('Y');
      }

      // find time pattern
      $time_pattern = '/^(\d+([:.]\d+)?(\s*[hapm.]+)?),?\s+/i';
      if(preg_match($time_pattern, $raw, $m)){
        $date_str .= ($date_str ? ' ' : 'today ') . $m[1];
        $raw = preg_replace($time_pattern, '', $raw);
      }

      // yes, raw input matched a (valid) date
      if(strlen($date_str) && strtotime($date_str)){
        $rec['date'] = strtotime($date_str);
        $rec['title'] = $raw;
      }
      else
        $rec['title'] = $rec['raw'];
    }
    return $rec;
  }
  
  function newEvent($component = 'vevent'){
    $rcmail = rcmail::get_instance();
    if($component == 'vevent'){
      $ret = $this->setDates();
      $allDay = $ret['allDay'];
      $start = $ret['start'];
      $end = $ret['end'];
      $expires = $ret['expires'];
    }
    else{
      $start = (int) get_input_value('_start', RCUBE_INPUT_POST);
      $end = 0;
    }
	
    $summary = trim(get_input_value('_summary', RCUBE_INPUT_POST));
    $priority = get_input_value('priority', RCUBE_INPUT_POST);
    $description = (string) trim(get_input_value('_description', RCUBE_INPUT_POST));
    $location = (string) trim(get_input_value('_location', RCUBE_INPUT_POST));
    $categories = (string) trim(get_input_value('_categories', RCUBE_INPUT_POST));
    $recur = (string) trim(get_input_value('_recur', RCUBE_INPUT_POST));
    $occurrences = (int) trim(get_input_value('_occurrences', RCUBE_INPUT_POST));
    $byday = (string) trim(get_input_value('_byday', RCUBE_INPUT_POST));
    $bymonth = (string) trim(get_input_value('_bymonth', RCUBE_INPUT_POST));
    $bymonthday = (string) trim(get_input_value('_bymonthday', RCUBE_INPUT_POST));
    $msgid = (string) trim(get_input_value('_msgid', RCUBE_INPUT_POST));
    $reminderselector= (int) get_input_value('_reminderselector', RCUBE_INPUT_POST);
    $duration = (array) get_input_value('_duration', RCUBE_INPUT_POST);
    $reminderbefore = $duration[$reminderselector] * $reminderselector;
    $remindermailto = (string) trim(get_input_value('_remindermailto', RCUBE_INPUT_POST));
    $remindertype = (string) trim(get_input_value('_remindertype', RCUBE_INPUT_POST));
    $reminderenable = (int) get_input_value('_reminderenable', RCUBE_INPUT_POST);
	
	// ATTENDEES MODIFICATION
	$unselected_attendee_username = get_input_value( 'hidden_unselected_invitee_username', RCUBE_INPUT_POST );
	$unselected_attendee_email = get_input_value( 'hidden_unselected_invitee_email', RCUBE_INPUT_POST );
	$selected_attendee_username = get_input_value( 'hidden_selected_invitee_username', RCUBE_INPUT_POST );
	$selected_attendee_email = get_input_value( 'hidden_selected_invitee_email', RCUBE_INPUT_POST );
	$attendee_role_array = get_input_value( 'event_attendee_role', RCUBE_INPUT_POST );
	// SEND MAIL TEST
	$sendmail = get_input_value( '_sendmail', RCUBE_INPUT_POST );
	// ALL DAY EVENT
	
	$all_day_event = get_input_value( 'event_allday', RCUBE_INPUT_POST );
	
    if(!$reminderenable){
      $remindertype = 0;
    }
	
	// ATTENDEES MODIFICATION
    /* $event = $this->backend->newEvent(
      $start,
      $end,
      $summary,
      $description,
      $location,
      $categories,
      $allDay,
      0,
      $priority,
      0,
      0,
      $recur,
      $expires,
      $occurrences,
      $byday,
      $bymonth,
      $bymonthday,
      false,
      false,
      $reminderbefore,
      $remindertype,
      $remindermailto,
      false,
      false,
      true,
      $component
    ); */
	$event = $this->backend->newEvent(
      $start,
      $end,
      $summary,
      $description,
      $location,
      $categories,
      $allDay,
      0,
      $priority,
      0,
      0,
      $recur,
      $expires,
      $occurrences,
      $byday,
      $bymonth,
      $bymonthday,
      false,
      false,
      $reminderbefore,
      $remindertype,
      $remindermailto,
      false,
      false,
      true,
      $component,
	  $unselected_attendee_username,
	  $unselected_attendee_email,
	  $selected_attendee_username,
	  $selected_attendee_email,
	  $attendee_role_array,
	  $all_day_event
    );
    $id = get_input_value('_id', RCUBE_INPUT_POST);
    if($id){
      $rcmail->output->command('plugin.planner_drop_success', $id);
    }
    else{
      if(get_input_value('_note', RCUBE_INPUT_POST)){
        $msgid = array();
        $msgid['date'] = $event['start'];
        $msgid['id'] = $event['event_id'];
      }
      if(is_array($event) && $event['sync']){
        $this->reminders_get();
        if($this->notify)
		{
          $this->notifyEvents(array(0=>$event));
		}
        $rcmail->output->command('plugin.reloadCalendar', $msgid);
      }
      else{
        $_POST['_event_id'] = $event['event_id'];
        $_POST['_start'] = $event['start'];
        $this->removeEvent(false);
        $this->errorEvent($msgid);
      }
    }
	
	// SEND MAIL TEST
	if( $sendmail == 'true' )
	{
		$this->backend->send_invitation_mail( $event );
	}
  }

  function editEvent() {
    $rcmail = rcmail::get_instance();
    $ret = $this->setDates();
    $allDay = $ret['allDay'];
    $start = $ret['start'];
    $end = $ret['end'];
    $expires = $ret['expires'];
    $id = get_input_value('_event_id', RCUBE_INPUT_POST);
    $uid = get_input_value('_uid', RCUBE_INPUT_POST);
    $recurrence_id = (int) trim(get_input_value('_recurrence_id', RCUBE_INPUT_POST));
    $summary = trim(get_input_value('_summary', RCUBE_INPUT_POST));
    $description = trim(get_input_value('_description', RCUBE_INPUT_POST));
    $location = trim(get_input_value('_location', RCUBE_INPUT_POST));
    $categories = trim(get_input_value('_categories', RCUBE_INPUT_POST));
    $old_categories = trim(get_input_value('_old_categories', RCUBE_INPUT_POST));
    $recur = trim(get_input_value('_recur', RCUBE_INPUT_POST));
    $occurrences = trim(get_input_value('_occurrences', RCUBE_INPUT_POST));
    $byday = trim(get_input_value('_byday', RCUBE_INPUT_POST));
    $bymonth = trim(get_input_value('_bymonth', RCUBE_INPUT_POST));
    $bymonthday = trim(get_input_value('_bymonthday', RCUBE_INPUT_POST));
    $reminderselector= (int) get_input_value('_reminderselector', RCUBE_INPUT_POST);
    $duration = (array) get_input_value('_duration', RCUBE_INPUT_POST);
    $reminderbefore = $duration[$reminderselector] * $reminderselector;
    $remindermailto = (string) trim(get_input_value('_remindermailto', RCUBE_INPUT_POST));
    $remindertype = (string) trim(get_input_value('_remindertype', RCUBE_INPUT_POST));
    $reminderenable = (int) get_input_value('_reminderenable', RCUBE_INPUT_POST);
	
	// ATTENDEES MODIFICATION
	$unselected_attendee_username = get_input_value( 'hidden_unselected_invitee_username', RCUBE_INPUT_POST );
	$unselected_attendee_email = get_input_value( 'hidden_unselected_invitee_email', RCUBE_INPUT_POST );
	$selected_attendee_username = get_input_value( 'hidden_selected_invitee_username', RCUBE_INPUT_POST );
	$selected_attendee_email = get_input_value( 'hidden_selected_invitee_email', RCUBE_INPUT_POST );
	$attendee_role_array = get_input_value( 'event_attendee_role', RCUBE_INPUT_POST );
	// SEND MAIL TEST
	$sendmail = get_input_value( '_sendmail', RCUBE_INPUT_POST );
	// ALL DAY EVENT
	$all_day_event = get_input_value( 'event_allday', RCUBE_INPUT_POST );
	
    if(!$reminderenable){
      $remindertype = 0;
    }
    $msgid = (string) trim(get_input_value('_msgid', RCUBE_INPUT_POST));
    $reload = (int) trim(get_input_value('_reload', RCUBE_INPUT_POST));
    $recurselnever = (int) trim(get_input_value('_recurselnever', RCUBE_INPUT_POST));
    $mode = (string) trim(get_input_value('_mode', RCUBE_INPUT_POST));
    $component = get_input_value('component', RCUBE_INPUT_POST);
    $priority = get_input_value('priority', RCUBE_INPUT_POST);
    if($component == 'vtodo'){
      if(!get_input_value('startactive', RCUBE_INPUT_POST)){
        $start = 0;
      }
      if(!get_input_value('endactive', RCUBE_INPUT_POST)){
        $end = 0;
      }
      if(!get_input_value('dueactive', RCUBE_INPUT_POST)){
        $due = 0;
      }
      else{
        $due = strtotime(trim(get_input_value('duedate', RCUBE_INPUT_POST)) . " " . trim(get_input_value('duetime', RCUBE_INPUT_POST)) . ":00");
      }
      $status = get_input_value('status', RCUBE_INPUT_POST);
      $complete = get_input_value('percentage', RCUBE_INPUT_POST);
    }
    else{
      $due = 0;
      $complete = 0;
      $status = null;
    }
    if(!$mode || $mode == 'initial'){
      $event = $this->backend->getEvent($id);
      $event = $this->backend->editEvent(
        $id,
        $start,
        $end,
        $status,
        $priority,
        $due,
        $complete,
        $summary,
        $description,
        $location,
        $categories,
        $recur,
        $expires,
        $occurrences,
        $byday,
        $bymonth,
        $bymonthday,
        $recurrence_id,
        $event['exdates'],
        $reminderbefore,
        $remindertype,
        $remindermailto,
        $allDay,
        $old_categories,
        false,
        true,
        $component,
		$unselected_attendee_username,
		$unselected_attendee_email,
		$selected_attendee_username,
		$selected_attendee_email,
		$attendee_role_array,
		$all_day_event
      );
    }
    else if($mode == 'single'){
      $event = $this->backend->newEvent(
        $start,
        $end,
        $summary,
        $description,
        $location,
        $categories,
        $allDay,
        $status,
        $priority,
        $due,
        $complete,
        0,
        $expires,
        0,
        false,
        false,
        false,
        $recurrence_id,
        false,
        $reminderbefore,
        $remindertype,
        $remindermailto,
        $uid,
        false,
        true,
        $component,
		$unselected_attendee_username,
		$unselected_attendee_email,
		$selected_attendee_username,
		$selected_attendee_email,
		$attendee_role_array,
		$all_day_event
      );
    }
    else if($mode == 'future'){
      $event = $this->backend->getEvent($id);
      $event = $this->backend->editEvent(
        $id,
        $event['start'],
        $event['end'],
        $status,
        $priority,
        $due,
        $complete,
        $event['summary'],
        $event['description'],
        $event['location'],
        $event['categories'],
        $event['rr'] . $event['recurring'],
        $start - 86400,
        0,
        $event['byday'],
        $event['bymonth'],
        $event['bymonthday'],
        $event['recurrence_id'],
        $event['exdates'],
        $event['reminderbefore'],
        $event['remindertype'],
        $event['remindermailto'],
        $event['all_day'],
        $event['categories'],
        false,
        true,
        $component,
		$unselected_attendee_username,
		$unselected_attendee_email,
		$selected_attendee_username,
		$selected_attendee_email,
		$attendee_role_array,
		$all_day_event
      );
      $event = $this->backend->newEvent(
        $start,
        $end,
        $summary,
        $description,
        $location,
        $categories,
        $allDay,
        $status,
        $priority,
        $due,
        $complete,
        $recur,
        $expires,
        $occurrences,
        $byday,
        $bymonth,
        $bymonthday,
        false,
        false,
        $reminderbefore,
        $remindertype,
        $remindermailto,
        false,
        false,
        true,
        $component,
		$unselected_attendee_username,
		$unselected_attendee_email,
		$selected_attendee_username,
		$selected_attendee_email,
		$attendee_role_array,
		$all_day_event
      );
    }
	// SEND MAIL TEST
	if( $sendmail == 'true' )
	{
		$this->backend->send_invitation_mail( $event );
	}
	
    if($event['sync']){
      if($this->notify)
        $this->notifyEvents(array(0=>$event));
      $rcmail->output->command('plugin.reloadCalendar', $msgid);
    }
    else{
      $_POST['_start'] = $event['start'];
      $this->removeEvent(false);
      $this->errorEvent($msgid);
    }
  }
  
  function editTask(){
    $rcmail = rcmail::get_instance();
    $event = $this->backend->getEvent(get_input_value('_event_id', RCUBE_INPUT_POST));
    $done = get_input_value('_done', RCUBE_INPUT_POST);
    if(is_array($event)){
      if($done == 'true'){
        $event['end'] = time();
        $event['complete'] = 100;
        $event['status'] = 'COMPLETED';
      }
      else{
        $event['end'] = 0;
        $event['complete'] = 0;
        $event['status'] = null;
      }
      $event = $this->backend->editEvent(
        $event['event_id'],
        $event['start'],
        $event['end'],
        $event['status'],
        $event['priority'],
        $evnet['due'],
        $event['complete'],
        $event['summary'],
        $event['description'],
        $event['location'],
        $event['categories'],
        $event['rr'] . $event['recurring'],
        $event['expires'],
        $event['occurrences'],
        $event['byday'],
        $event['bymonth'],
        $event['bymonthday'],
        $event['recurrence_id'],
        $event['exdates'],
        $event['reminder'],
        $event['reminderservice'],
        $event['remindermailto'],
        false,
        $event['categories'],
        $event['caldav'],
        true,
        'vtodo'
      );
    }
    if($event['sync']){
      if($this->notify)
        $this->notifyEvents(array(0=>$event));
    }
    $this->getTasks();
  }
  
  function moveEvent() {
    $rcmail = rcmail::get_instance();
    $ret = $this->setDates();
    $allDay = $ret['allDay'];
    $start = $ret['start'];
    $end = $ret['end'];
    $id = get_input_value('_event_id', RCUBE_INPUT_POST);
    $uid = get_input_value('_uid', RCUBE_INPUT_POST);
    $gap = get_input_value('_gap', RCUBE_INPUT_POST);
    $reminder = get_input_value('_reminder', RCUBE_INPUT_POST);
    $refetch = get_input_value('_refetch', RCUBE_INPUT_POST);
    $msgid = (string) trim(get_input_value('_msgid', RCUBE_INPUT_POST));
    $mode = (string) trim(get_input_value('_mode', RCUBE_INPUT_POST));
    if(!$mode || $mode == 'initial'){
      $event = $this->backend->moveEvent($id, $start, $end, $allDay, $reminder);
    }
    else if($mode == 'single'){
      $event = $this->backend->getEvent($id);
      $event = $this->backend->newEvent(
        $start,
        $end,
        $event['summary'],
        $event['description'],
        $event['location'],
        $event['categories'],
        $event['all_day'],
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        false,
        false,
        false,
        $event['start'] + $gap,
        false,
        $event['reminderbefore'],
        $event['remindertype'],
        $event['remindermailto'],
        $event['uid']
      );
    }
    else if($mode == 'future'){
      $event = $this->backend->getEvent($id);
      $this->backend->editEvent(
        $id,
        $event['start'],
        $event['end'],
        0,
        0,
        0,
        0,
        $event['summary'],
        $event['description'],
        $event['location'],
        $event['categories'],
        $event['rr'] . $event['recurring'],
        $event['start'] + $gap - 86400,
        $event['occurrences'],
        $event['byday'],
        $event['bymonth'],
        $event['bymonthday'],
        $event['recurrence_id'],
        $event['exdates'],
        $event['reminderbefore'],
        $event['remindertype'],
        $event['remindermailto'],
        $event['all_day'],
        $event['categories']
      );
      $event = $this->backend->newEvent(
        $start,
        $end,
        $event['summary'],
        $event['description'],
        $event['location'],
        $event['categories'],
        $event['all_day'],
        0,
        0,
        0,
        0,
        $event['rr'] . $event['recurring'],
        $event['expires'],
        $event['occurrences'],
        $event['byday'],
        $event['bymonth'],
        $event['bymonthday'],
        false,
        false,
        $event['reminderbefore'],
        $event['remindertype'],
        $event['remindermailto']
      );
    }
    if($event['sync']){
      if($this->notify)
        $this->notifyEvents(array(0=>$event));
      if($refetch == 1 || !$event['sync'] || get_input_value('_allDay', RCUBE_INPUT_POST) == 'true'){
        $rcmail->output->command('plugin.reloadCalendar', $msgid);
      }
      else{
        $rcmail->output->command('plugin.calendar_unlockGUI', $msgid);
      }
    }
    else{
      $this->removeEvent(false);
      $this->errorEvent($msgid);
    }
  }
  
  function resizeEvent() {
    $rcmail = rcmail::get_instance();
    $ret = $this->setDates();
    $start = $ret['start'];
    $end = $ret['end'];
    $id = get_input_value('_event_id', RCUBE_INPUT_POST);
    $uid = get_input_value('_uid', RCUBE_INPUT_POST);
    $start = get_input_value('_start', RCUBE_INPUT_POST);
    $end = get_input_value('_end', RCUBE_INPUT_POST);
    $reminder = get_input_value('_reminder', RCUBE_INPUT_POST);
    $refetch = get_input_value('_refetch', RCUBE_INPUT_POST);
    $msgid = (string) trim(get_input_value('_msgid', RCUBE_INPUT_POST));
    $mode = (string) trim(get_input_value('_mode', RCUBE_INPUT_POST));
    if(!$mode || $mode == 'initial'){
      $event = $this->backend->resizeEvent($id, $start, $end, $reminder);
    }
    else if($mode == 'single'){
      $event = $this->backend->getEvent($id);
      $event = $this->backend->newEvent(
        $start,
        $end,
        $event['summary'],
        $event['description'],
        $event['location'],
        $event['categories'],
        $event['all_day'],
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        false,
        false,
        false,
        $start,
        false,
        $event['reminderbefore'],
        $event['remindertype'],
        $event['remindermailto'],
        $event['uid']
      );
    }

    else if($mode == 'future'){
      $event = $this->backend->getEvent($id);
      $this->backend->editEvent(
        $id,
        $event['start'],
        $event['end'],
        0,
        0,
        0,
        0,
        $event['summary'],
        $event['description'],
        $event['location'],
        $event['categories'],
        $event['rr'] . $event['recurring'],
        $start - 86400,
        $event['occurrences'],
        $event['byday'],
        $event['bymonth'],
        $event['bymonthday'],
        $event['recurrence_id'],
        $event['exdates'],
        $event['reminderbefore'],
        $event['remindertype'],
        $event['remindermailto'],
        $event['all_day'],
        $event['categories']
      );
      $event = $this->backend->newEvent(
        $start,
        $end,
        $event['summary'],
        $event['description'],
        $event['location'],
        $event['categories'],
        $event['all_day'],
        0,
        0,
        0,
        0,
        $event['rr'] . $event['recurring'],
        $event['expires'],
        $event['occurrences'],
        $event['byday'],
        $event['bymonth'],
        $event['bymonthday'],
        false,
        false,
        $event['reminderbefore'],
        $event['remindertype'],
        $event['remindermailto']
      );
    }
    if($event['sync']){
      if($this->notify)
        $this->notifyEvents(array(0=>$event));
      if($event['rr'] == '0' && $event['sync']){
        $rcmail->output->command('plugin.calendar_unlockGUI', $msgid);
      }
      else{
        $rcmail->output->command('plugin.reloadCalendar', $msgid);
      }
    }
    else{
      $this->removeEvent(false);
      $this->errorEvent($msgid);
    }
  }
  
  function removeEvent($sync = true){
    $this->backend->sync = $sync;
    $id = get_input_value('_event_id', RCUBE_INPUT_POST);
    $uid = get_input_value('_uid', RCUBE_INPUT_POST);
    $msgid = (string) trim(get_input_value('_msgid', RCUBE_INPUT_POST));
    $start = (int) trim(get_input_value('_start', RCUBE_INPUT_POST));
    $mode = (string) trim(get_input_value('_mode', RCUBE_INPUT_POST));
    if(!$mode || $mode == 'initial'){
      $event = $this->backend->removeEvent($id);
    }
    else if($mode == 'single'){
      $event = $this->backend->getEvent($id);
      if($event['exdates']){
        $exdates = @unserialize($event['exdates']);
        $exdates[] = $start;
      }
      else{
        $exdates = array(0 => (int) $start);
      }
      $event = $this->backend->editEvent(
        $event['event_id'],
        $event['start'],
        $event['end'],
        0,
        0,
        0,
        0,
        $event['summary'],
        $event['description'],
        $event['location'],
        $event['categories'],
        $event['rr'] . $event['recurring'],
        $event['expires'],
        $event['occurrences'],
        $event['byday'],
        $event['bymonth'],
        $event['bymonthday'],
        $event['recurrence_id'],
        $exdates,
        $event['reminderbefore'],
        $event['remindertype'],
        $event['remindermailto'],
        $event['all_day'],
        $event['categories']
      );
    }
    else if($mode == 'future'){
      $event = $this->backend->getEvent($id);
      $event = $this->backend->editEvent(
        $id,
        $event['start'],
        $event['end'],
        0,
        0,
        0,
        0,
        $event['summary'],
        $event['description'],
        $event['location'],
        $event['categories'],
        $event['rr'] . $event['recurring'],
        $start - 86400,
        $event['occurrences'],
        $event['byday'],
        $event['bymonth'],
        $event['bymonthday'],
        $event['recurrence_id'],
        $event['exdates'],
        $event['reminderbefore'],
        $event['remindertype'],
        $event['remindermailto'],
        $event['all_day'],
        $event['categories']
      );
      $events = $this->backend->getEventsByUID($uid);
      foreach($events as $key => $event){
        if($event['recurrence_id'] >= $start){
          $sav = $rcmail->action;
          $rcmail->action = '';
          $event = $this->backend->removeEvent($event['event_id']);
        }
      }
      $rcmail->action = $sav;
    }
    if($event['sync'] && ($event['del'] == 1 || $mode != 'initial')){
      if($this->notify)
        $this->notifyEvents(array(0=>$event));
      $rcmail = rcmail::get_instance();
      if(!$mode || $mode == 'initial'){
        $this->getTasks();
        $rcmail->output->command('plugin.calendar_unlockGUI', $msgid);
      }
      else{
        $rcmail->output->command('plugin.reloadCalendar', $msgid);
      }
    }
    else{
      $this->errorEvent($msgid);
    }
  }
  
  function filterEvents($events = array()){
    if(class_exists('calendar_plus')){
      return calendar_plus::load_filters('filter', $events);
    }
    else{
      return $events;
    }
  }
  
  function getEvents(){
    $rcmail = rcmail::get_instance();
    // "start" and "end" are from fullcalendar, not RoundCube.
    $start = get_input_value('_start', RCUBE_INPUT_GPC);
    $end = get_input_value('_end', RCUBE_INPUT_GPC);
    $category = get_input_value('_category', RCUBE_INPUT_GPC);
    $events = $this->filterEvents($this->utils->arrayEvents($start, $end, $category));
    send_nocacheing_headers();
    header('Content-Type: text/plain; charset=' . $rcmail->output->get_charset());
    echo json_encode($events);
    exit;
  }
  
  function getBirthdays($type = 'all'){
    $rcmail = rcmail::get_instance();
    if($rcmail->config->get('show_birthdays') && !is_array($this->bd[$type])){
      $birthdays = array();
      if(class_exists('calendar_plus')){
        $birthdays = calendar_plus::load_birthdays($birthdays);
      }
      $this->bd = $birthdays;
    }
    if(!is_array($this->bd[$type]))
      $this->bd[$type] = array();
    return $this->bd[$type];
  }
  
  function syncEvents($force = false){
    $rcmail = rcmail::get_instance();
    if($rcmail->config->get('backend') == 'caldav'){
      if($period = $rcmail->config->get('caldav_replicate_automatically', 3600)){
        $last_fetched = $_SESSION['caldav_allfetched'] ? $_SESSION['caldav_allfetched'] : $period;	
        if($force || ($period > 0 && time() - $last_fetched >= $period)){
          if(!$_SESSION['caldav_truncate']){
            $this->backend->truncateEvents(4);
            $_SESSION['caldav_truncate'] = true;
          }
          $rcmail->session->remove('caldav_allfetched');
          $rcmail->session->remove('caldav_resume_replication');
          $this->replicate();
        }
      }
    }
    $rcmail->output->command('plugin.calendar_replicate_done', '');
  }
  
  function exportEvents() {
    $start = 0;
    $end = strtotime(CALEOT);
    send_nocacheing_headers();
    header("Content-Type: text/calendar");
    header("Content-Disposition: inline; filename=" . asciiwords(str_replace("@","_",$_SESSION['username'])) . ".ics");
    echo $this->utils->exportEvents($start, $end);
    exit;
  }
  
  function exportEventsZip() {
    $rcmail = rcmail::get_instance();
    $start = 0;
    $end = strtotime(CALEOT);
    $temp_dir = slashify($rcmail->config->get('temp_dir','temp/'));
    $tmpfname = tempnam($temp_dir, 'zip');
    $zip = new ZipArchive();
    $zip->open($tmpfname, ZIPARCHIVE::OVERWRITE);
    $ics = $this->utils->exportEvents($start, $end);
    $zip->addFromString('default.ics', $ics);
    $caldavs = $this->backend->caldavs;
    if(is_array($caldavs)){
      foreach($caldavs as $category => $caldav){
        $ics = $this->utils->exportEvents($start, $end, $events=true, $showdel=false, $showclone=false, $category);
        $zip->addFromString(strtolower($category) . '.ics', $ics);
      }
    }
    $zip->close();
    $browser = new rcube_browser;
    send_nocacheing_headers();
    // send download headers
    header("Content-Type: application/octet-stream");
    if($browser->ie)
      header("Content-Type: application/force-download");
    // don't kill the connection if download takes more than 30 sec.
    @set_time_limit(0);
    header("Content-Disposition: attachment; filename=\"". asciiwords(str_replace("@","_",$_SESSION['username'])) . ".ics.zip\"");
    header("Content-length: " . filesize($tmpfname));
    readfile($tmpfname);
    @unlink($tmpfname);
    exit;
  }
  
  function exportEvent() {
    $rcmail = rcmail::get_instance();
    $id = get_input_value('_id', RCUBE_INPUT_GPC);
    $edit = get_input_value('_edit', RCUBE_INPUT_GPC);
    if($edit == 'false'){
      $events_table = $rcmail->config->get('db_table_events', 'events');
      $rcmail->config->set('db_table_events',$rcmail->config->get('db_table_events_cache', 'events_cache'));
      $event = $this->utils->arrayEvent($id);
      $rcmail->config->set('db_table_events',$events_table);
    }
    else{
      $edit = 1;
      $event = $this->utils->arrayEvent($id);
    }
    $event['start'] = $event['start'];
    $event['end'] = $event['end'];
    $event['recur'] = $event['recurring'];
    send_nocacheing_headers();
    header("Content-Type: text/calendar");
    if($event['summary'] != '')
      $filename = asciiwords($event['summary']);
    else
      $filename = 'calendar';
    header("Content-Disposition: inline; filename=" . $filename . ".ics");
    echo $this->utils->exportEvents(0, 0, array($event), false, false, false, $event['component']);
    exit;
  }

  function purgeEvents() {
    $this->backend->purgeEvents();
    exit;
  }
  
 /****************************
  *
  * Settings Section
  *
  ****************************/
  
  function caldav_dialog($args){
    $rcmail = rcmail::get_instance();
    if(!$rcmail->config->get('caldav_protect')){
      $content = $args['content'];
      if(strpos($content,'addRowCategories')){
        if($append = @file_get_contents(INSTALL_PATH . 'plugins/calendar/skins/includes/caldav.html')){
          $append = $rcmail->output->just_parse($append);
          $content .= $append;
        }
      }
      $args['content'] = $content;
    }
    return $args;
  }
  
  function getCalDAVs($show=true){
	global $CONFIG;
    $rcmail = rcmail::get_instance();
    $category = get_input_value('_category', RCUBE_INPUT_POST);
	
	if( ( isset( $_POST[ 'onlyURL' ] ) ) && ( $_POST[ 'onlyURL' ] == 'true' ) )
		$onlyURL = true;
	else
		$onlyURL = false;
	
    $caldavs = $rcmail->config->get('caldavs', array());
	
	// when edit button of default calendar or the shared calendar is clicked.
	if( $onlyURL )
	{
		$default_category_label = str_replace( "%u", $_SESSION[ 'username' ], $rcmail->config->get('default_category_label', $this->gettext('defaultcategory')) );
		// if default calendar url is requested, it is not present in caldavs array. so url will be default caldav url.
		if( $category == $default_category_label )
		{
			$properties['url'] = $rcmail->config->get('caldav_url', $_SESSION[ 'username' ] );
		}
		else
		{
			$properties['url'] = $caldavs[$category]['url'];
		}
		
		$properties['onlyURL'] = true;
	}
	else
	{
		if(
		  !empty($caldavs[$category]) &&
		  !empty($caldavs[$category]['user']) &&
		  !empty($caldavs[$category]['url'])
		  )
		{
		  $properties = $caldavs[$category];
		  $properties['pass'] = 'ENCRYPTED';
		  $properties['saved'] = true;
		}
		else{
		  $properties = $rcmail->config->get('default_caldav_backend',array());
		  $properties['saved'] = false;
		}
		if(!$properties['cat'])
		  $properties['cat'] = $properties['url'];
		if(strpos($properties['url'], '%su') || $properties['user'] == '%su'){
		  list($u, $d) = explode('@', $rcmail->user->data['username']);
		  $properties['url'] = str_replace('%su', $u, $properties['cat']);
		  $properties['cat'] = str_replace('%su', $u, $properties['cat']);
		  $properties['user'] = str_replace('%su', $u, $properties['user']);
		  $properties['pass'] = 'SESSION';
		}
		else if(strpos($properties['url'], '%u') || $properties['user'] == '%u'){
		  $properties['url'] = str_replace('%u', $rcmail->user->data['username'], $properties['cat']);
		  $properties['cat'] = str_replace('%u', $rcmail->user->data['username'], $properties['cat']);
		  $properties['user'] = $rcmail->user->data['username'];
		  $properties['pass'] = 'SESSION';
		}
		else{
		  $properties['url'] = '';
		}
		if(strpos($properties['cat'], '%c')){
		  $properties['url'] = str_replace('%c', asciiwords(strtolower($category)), $properties['cat']);
		}
		$properties['category'] = $category;
		$properties['category_disp'] = asciiwords($category, true, '_');
		$properties['max_caldavs'] = $rcmail->config->get('max_caldavs',3);
		$properties['cal_dont_save_passwords'] = $rcmail->config->get('cal_dont_save_passwords', false);
		$properties['show'] = $show;
		if(class_exists('tabbed') && $rcmail->action == 'plugin.calendar_saveCalDAV'){
		  $properties['tabbed'] = true;
		}
	}
    $rcmail->output->command('plugin.calendar_getCalDAVs', $properties);
  }
  
  function saveCalDAV(){
    $rcmail = rcmail::get_instance();
    $caldavs = $rcmail->config->get('caldavs', array());
	$caldav_categories = $rcmail->config->get('categories', array());
	
    $caldavs_subscribed = $rcmail->config->get('caldavs_subscribed', array());
    $save = array();
    $save['caldavs'] = $caldavs;
	
    $save['caldavs_removed'] = $rcmail->config->get('caldavs_removed', array());
    $user = trim(get_input_value('_caldav_user', RCUBE_INPUT_POST));
    $pass = trim(get_input_value('_caldav_password', RCUBE_INPUT_POST));
    $url = trim(get_input_value('_caldav_url', RCUBE_INPUT_POST));
    $extr = trim(get_input_value('_caldav_extr', RCUBE_INPUT_POST));
    $auth = trim(get_input_value('_caldav_auth', RCUBE_INPUT_POST));
    $category = trim(get_input_value('_category', RCUBE_INPUT_POST));
    $save['caldavs'][$category] = array(
      'user'        => $user,
      'url'         => $url,
      'auth'        => $auth,
      'extr'        => $extr,
	  'is_caldav_owner' 			=> 1,
    );
    if($pass != 'ENCRYPTED'){
      if($pass == $rcmail->decrypt($_SESSION['password'])){
        $pass = '%p';
      }
      else if($pass == 'SESSION'){
        $pass = '%p';
      }
      $pass = $rcmail->encrypt($pass);
      $save['caldavs'][$category]['pass'] = $pass;
    }
    else{
      $save['caldavs'][$category]['pass'] = $caldavs[$category]['pass'];
    }
    if($_SESSION['user_id']){
      $categories = $rcmail->config->get('categories', array());
	  
      $this->backend->newCalendar($save['caldavs'][$category], $category, '#' . $categories[$category]);
      $subscribed[$category] = $save['caldavs'][$category];
      $save['caldavs_subscribed'] = array_merge($caldavs_subscribed, $subscribed);
	  
      $save['ctags'] = array();
      unset($save['caldavs_removed'][unslashify($url)]);
      $rcmail->user->save_prefs($save);
      $this->backend->truncateEvents(3);
      $rcmail->session->remove('caldav_allfetched');
      $rcmail->session->remove('caldav_resume_replication');
      $rcmail->session->remove('reminders');
      $this->reminders_get();
    }
    $this->getCalDAVs(false);
  }
  
  function removeCalDAV(){
	global $RC_SABRE_HELP;
    $rcmail = rcmail::get_instance();
	$rcube = rcube::get_instance();
    $category = get_input_value('_category', RCUBE_INPUT_POST);
    $remove = get_input_value('_caldav_remove', RCUBE_INPUT_POST);
	$caldav_url = get_input_value('_caldav_url', RCUBE_INPUT_POST);
    $caldavs = $rcmail->config->get('caldavs', array());
	
	$RC_SABRE_HELP->unsubscribe_all_users( $rcube->user->get_username(), $caldav_url, $category, true );
	
    if($_SESSION['detected_caldavs']){
      $caldavs = array_merge($_SESSION['detected_caldavs'], $caldavs);
    }
    $parsed = parse_url($caldavs[$category]['url']);
    if(!$parsed['query'] && $remove == 1){
      if($this->backend->removeCalendar($caldavs[$category])){
        unset($_SESSION['detected_caldavs'][$category]);
      }
    }
    else{
      unset($_SESSION['detected_caldavs'][$category]);
    }
    $removed = $rcmail->config->get('caldavs_removed', array());
    $removed = array_merge($removed, array(unslashify($caldavs[$category]['url']) => 1));
    foreach($caldavs as $cat => $props){
      if($props['url'] == $caldavs[$category]['url']){
        unset($caldavs[$cat]);
      }
    }
    $subscribed = $rcmail->config->get('caldavs_subscribed', array());
    foreach($subscribed as $cat => $props){
      if($props['url'] == $caldavs[$category]['url']){
        unset($subscribed[$cat]);
      }
    }
    if($_SESSION['user_id']){
      $rcmail->user->save_prefs(array('caldavs' => $caldavs, 'caldavs_removed' => $removed, 'caldavs_subscribed' => $subscribed));
      $this->backend->truncateEvents(1);
      $rcmail->session->remove('caldav_allfetched');
      $rcmail->session->remove('caldav_resume_replication');
      $rcmail->session->remove('reminders');
      $this->reminders_get();
    }
    $this->getCalDAVs(false);
  }
  
  function subscribe(){
    $rcmail = rcmail::get_instance();
    $command = 'reload';
    if($_SESSION['user_id']){
      $default_caldav_subscribed = get_input_value('_default_caldav_subscribed', RCUBE_INPUT_POST);
      $caldavs_subscribed = get_input_value('_caldavs', RCUBE_INPUT_POST);
      if(is_array($caldavs_subscribed)){
        $caldavs_subscribed = array_flip($caldavs_subscribed);
      }
      $feeds_subscribed = get_input_value('_feeds', RCUBE_INPUT_POST);
      if(is_array($feeds_subscribed)){
        $feeds_subscribed = array_flip($feeds_subscribed);
      }
      $caldavs = array_merge($rcmail->config->get('caldavs', array()), $rcmail->config->get('public_caldavs', array()));
      $feeds = array_merge($rcmail->config->get('calendarfeeds', array()), $rcmail->config->get('public_calendarfeeds', array()));
      $caldavs_subscribed_prev = $rcmail->config->get('caldavs_subscribed_prev', array());
      $categories = $rcmail->config->get('categories', array());
      $feeds_subscribed_prev = $rcmail->config->get('feeds_subscribed_prev', array());
      $filters_allcalendars = array_flip(explode(', ', $rcmail->config->get('calfilter_allcalendars', '')));
      $event_filters_allcalendars = array_flip($rcmail->config->get('event_filters_allcalendars', array()));
      if(!$default_caldav_subscribed){
		/* CHANGE DEFAULT CALENDAR TEXT */
		$default_caldav_label = str_replace( "%u", $_SESSION[ 'username' ], $rcmail->config->get('default_category_label', $this->gettext('defaultcategory')) );
        unset($filters_allcalendars[$default_caldav_label]);
        // unset($event_filters_allcalendars[$rcmail->config->get('default_category_label', $this->gettext('defaultcategory'))]);
        unset($event_filters_allcalendars[$default_caldav_label]);
        foreach($categories as $key => $val){
          if(!isset($caldavs[$key])){
            unset($filters_allcalendars[$key]);
            unset($event_filters_allcalendars[$key]);
          }
        }
      }
      foreach($caldavs as $caldav => $props){
        if(!isset($caldavs_subscribed[$caldav])){
          $sql = 'DELETE FROM '. get_table_name('events') . ' WHERE user_id=? AND url=?';
          $rcmail->db->query($sql, $rcmail->user->ID, $caldavs[$caldav]['url']);
          unset($caldavs[$caldav]);
          unset($filters_allcalendars[$caldav]);
          unset($event_filters_allcalendars[$caldav]);
        }
        else{
          if(!isset($caldavs_subscribed_prev[$caldav])){
            $command = 'sync';
          }
        }
      }
      foreach($feeds as $feed => $props){
        if(!isset($feeds_subscribed[$feed])){
          $this->clearCache();
          unset($feeds[$feed]);
        }
        else{
          if(!isset($feeds_subscribed_prev[$feed])){
            $this->clearCache();
            $command = 'sync';
          }
        }
      }
      if(is_array($event_filters_allcalendars)){
        $event_filters_allcalendars = array_flip($event_filters_allcalendars);
      }
      $_SESSION['event_filters'] = $event_filters_allcalendars;
      $filters_allcalendars_serialized = implode(', ', array_flip($filters_allcalendars));
      $_SESSION['calfilter'] = is_array($filters_allcalendars) ? $filters_allcalendars : $this->gettext('allevents');
      $_SESSION['calfiltertasks'] = is_array($filters_allcalendars) ? $filters_allcalendars : $this->gettext('alltasks');
      if($default_caldav_subscribed != $rcmail->config->get('default_caldav_subscribed')){
        $command = 'sync';
      }
      $save = array(
        'caldavs_subscribed' => $caldavs,
        'default_caldav_subscribed' => $default_caldav_subscribed ? 1 : 0,
        'feeds_subscribed' => $feeds,
        'caldavs_subscribed_prev' => $caldavs_subscribed,
        'feeds_subscribed_prev' => $feeds_subscribed,
        'calfilter_allcalendars' => $filters_allcalendars_serialized,
        'event_filters_allcalendars' => $event_filters_allcalendars,
      );
	  
      $rcmail->user->save_prefs($save);
    }
    if($command == 'reload'){
      $rcmail->output->command('plugin.calendar_refresh', array(0 => $this->boxTitle(array())));
    }
    else{
      $rcmail->output->command('plugin.syncCalendar', '');
    }
  }

  function calendarLink($args){
	/* DISPLAY CALENDAR TABS */
    $rcmail = rcmail::get_instance();
    $temp = $args['list']['server'];
    unset($args['list']['server']);
    $args['list']['calendarlink']['id'] = 'calendarlink';
    $args['list']['calendarlink']['section'] = $this->gettext('calendar');
    $args['list']['calendarcategories']['id'] = 'calendarcategories';
    if($rcmail->config->get('backend') == 'caldav'){
      $args['list']['calendarcategories']['section'] = $this->gettext('submenuprefix') . $this->gettext('caldavsettings');
    }
    else{
      $args['list']['calendarcategories']['section'] = $this->gettext('submenuprefix') . $this->gettext('categories');
    }
    $args['list']['calendarfeeds']['id'] = 'calendarfeeds';
    $args['list']['calendarfeeds']['section'] = $this->gettext('submenuprefix') . $this->gettext('feeds');
    $args['list']['calendarsharing']['id'] = 'calendarsharing';
    $args['list']['calendarsharing']['section'] = $this->gettext('submenuprefix') . $this->gettext('sharing');
    $args['list']['server'] = $temp;

    return $args;
  }
  
  function getTask(){
    $rcmail = rcmail::get_instance();
    $event = $this->backend->getEvent(get_input_value('_event_id', RCUBE_INPUT_POST));
    $start = get_input_value('_start', RCUBE_INPUT_POST);
    $due = get_input_value('_due', RCUBE_INPUT_POST);
    $clone = get_input_value('_clone', RCUBE_INPUT_POST);
    if(is_numeric($clone)){
      $event['clone'] = $clone;
    }
    if(is_numeric($start)){
      $event['start'] = $start;
    }
    if(is_numeric($due)){
      $event['due'] = $due;
    }
    $event['editable'] = true;
    $rcmail->output->command('plugin.getTask', $this->utils->eventArrayMap($event));
  }
  
  function getTasks(){
    $rcmail = rcmail::get_instance();
    $tasks = $this->backend->getEvents(
      0,
      time() + (86400 * ((int) $rcmail->config->get('caldav_replication_range_tasks', 92))),
      array(),
      false,
      false,
      false,
      'vtodo'
    );
    $tasks = $this->filterEvents($tasks);
    $mytasks = array();
    foreach($tasks as $key => $task){
      if(!$task['start']){
        $sort = '0000000000|';
      }
      else{
        if($task['recurrence_id']){
          $sort = $task['recurrence_id'] . '|';
        }
        else{
          $sort = $task['start'] . '|';
        }
      }
      $sort .= $task['uid'];
      $mytasks[$sort] = $task;
      $mytasks[$sort]['classNameDisp'] = $task['categories'];
      $mytasks[$sort]['className'] = asciiwords($task['categories'], true, '');
      $task['className'] = $task['categories'];
      $task['classNameDisp'] = asciiwords($task['categories'], true, '');
    }
    ksort($mytasks);
    $html = '';
    $script = '';
    foreach($mytasks as $key => $task){
      switch($task['priority']){
        case 1:
          $priority = 'high';
          $color = 'red';
          $sign = '!';
          break;
        case 5:
          $priority = 'normal';
          $color = 'green';
          $sign = '!';
          break;
        case 9:
          $priority = 'low';
          $color = 'blue';
          $sign = '&darr;';
          break;
        default:
          $priority = 'none';
          $color = 'white';
          $sign = '';
      }
      $done = '';
      if($task['end'] > 0){
        $done = 'done ';
      }
      $due = '';
      if($task['due'] > 0){
        if(!$task['end']){
          $due = $task['due'] - time();
          if(abs($due) / (86400 * 365) > 1){
            $due = round($due / (86400 * 365));
            $due .= ' ' . $this->gettext('years');
          }
          else if(abs($due) / (86400 * 30) > 1){
            $due = round($due / (86400 * 30));
            $due .= ' ' . $this->gettext('months');
          }
          else if(abs($due) / (86400 * 7) > 1){
            $due = round($due / (86400 * 7));
            $due .= ' ' . $this->gettext('weeks');
          }
          else if(abs($due) / 86400 > 1){
            $due = round($due / 86400);
            $due .= ' ' . $this->gettext('days');
          }
          else if(abs($due) / 3600 > 1){
            $due = round($due / 3600);
            $due .= ' ' . $this->gettext('hours');
          }
          else{
            $due = '< 1' . $this->gettext('hours');
          }
        }
        else{
          $due = date($rcmail->config->get('date_format', 'm/d/Y') . ' H:i:s', $task['due']);
        }
      }
      $class = 'all ';
      if(!$task['start']){
        $class .= 'nodate today tomorrow sevendays later ';
      }
      if($task['due'] > 0 && !$task['end'] && $task['due'] - time() < 0){
        $class .= 'overdue ';
        if(get_input_value('_init', RCUBE_INPUT_POST)){
          $script = '$("#overdue").trigger("click"); $("view").html("' . $this->gettext('overview') . '")';
        }
      }
      if($sign == '!' && $color == 'red'){
        $class .= 'high ';
      }
      if($sign == '&darr;'){
        $class .= 'low ';
      }
      if($task['start'] && date('Ymd', $task['start']) == date('Ymd', time())){
        $class .= 'today ';
      }
      if($task['due'] && date('Ymd', $task['due']) == date('Ymd', time())){
        $class .= 'today ';
      }
      $tomorrow = mktime(0,0,0,date('m'), date('d') + 1, date("Y"));
      if($task['start'] && date('Ymd', $task['start']) == date('Ymd', $tomorrow)){
        $class .= 'tomorrow ';
      }
      $week = mktime(0,0,0,date('m'), date('d') + 7, date("Y"));
      if($task['start'] && $task['start'] <= strtotime(date('Ymd', $week))){
        $class .= 'sevendays ';
      }
      else if($task['start']){
        $class .= 'later ';
      }
      if($done){
        $class = 'complete';
      }
      $class = trim($class);
      if(!$task['clone']){
        $task['clone'] = 'false';
      }
      $html .= html::tag('tr', array('class' => $class, 'style' => 'cursor: pointer;'),
        html::tag('td', array('title' => $this->gettext('done'), 'class' => 'donecol ' . asciiwords($task['categories'], true, ''), 'style' => ($task['categories'] ? '' : ' background: #' . $rcmail->config->get('default_category'))), html::tag('input', array('onclick' => 'if(typeof calendar_gui.editTask == "function"){ calendar_gui.editTask(' . $task['event_id'] . ', "done", $(this).prop("checked")) }', 'type' => 'checkbox', 'class' => 'donechbox', 'checked' => $done ? true : false))) .
        html::tag('td', array('onclick' => 'if(typeof calendar_gui.getTask == "function"){ calendar_gui.getTask(' . $task['event_id'] . ', ' . $task['start'] . ', ' . $task['due'] . ', ' . $task['clone'] . ') }', 'title' => $this->gettext($priority), 'class' => 'ui-widget-content', 'style' => 'width: 12px;'), '&nbsp;' . html::tag('font', array('color' => $color, 'size' => '3'), html::tag('b', null, $sign))) .
        html::tag('td', array('onclick' => 'if(typeof calendar_gui.getTask == "function"){ calendar_gui.getTask(' . $task['event_id'] . ', ' . $task['start'] . ', ' . $task['due'] . ', ' . $task['clone'] . ') }', 'title' => $task['summary'], 'class' => $done . 'ui-widget-content'), '&nbsp;' . $task['summary'] . '&nbsp;') .
        html::tag('td', array('onclick' => 'if(typeof calendar_gui.getTask == "function"){ calendar_gui.getTask(' . $task['event_id'] . ', ' . $task['start'] . ', ' . $task['due'] . ', ' . $task['clone'] . ') }', 'title' => ($task['start'] ? date($rcmail->config->get('date_format', 'm/d/Y') . ' ' . $rcmail->config->get('time_format', 'h:i A'), $task['start']) : ''), 'class' => 'ui-widget-content adjust'), ($task['start'] ? date($rcmail->config->get('date_format', 'm/d/Y') . ' ' . $rcmail->config->get('time_format', 'h:i A'), $task['start']) : '') . '&nbsp;') .
        html::tag('td', array('onclick' => 'if(typeof calendar_gui.getTask == "function"){ calendar_gui.getTask(' . $task['event_id'] . ', ' . $task['start'] . ', ' . $task['due'] . ', ' . $task['clone'] . ') }', 'title' => $due, 'class' => 'ui-widget-content adjust'), '&nbsp;' . str_replace(' ', '&nbsp;', $due) . '&nbsp;') .
        html::tag('td', array('onclick' => 'if(typeof calendar_gui.getTask == "function"){ calendar_gui.getTask(' . $task['event_id'] . ', ' . $task['start'] . ', ' . $task['due'] . ', ' . $task['clone'] . ') }', 'title' => ($task['end'] ? date($rcmail->config->get('date_format', 'm/d/Y'), $task['end']) : ''), 'class' => 'ui-widget-content adjust'), ($task['end'] ? date($rcmail->config->get('date_format', 'm/d/Y'), $task['end']) : '') . '&nbsp;') .
        html::tag('td', array('onclick' => 'if(typeof calendar_gui.getTask == "function"){ calendar_gui.getTask(' . $task['event_id'] . ', ' . $task['start'] . ', ' . $task['due'] . ', ' . $task['clone'] . ') }', 'title' => $task['complete'] . '%', 'class' => 'ui-widget-content', 'style' => 'width: 60px;', 'nowrap' => 'nowrap'),
          html::tag('center', null, 
            html::tag('table', array('cellspacing' => 0, 'cellpadding' => 0, 'width' => '90%'),
              html::tag('tr', array('class' => 'percentage'),
                html::tag('td', array('class' => 'normal ui-widget-content', 'style' => 'background: #00acd4;', 'width' => $task['complete'] . '%'), '&nbsp;') .
                html::tag('td', array('class' => 'normal ui-widget-content', 'width' => (100 - $task['complete']) . '%'), '&nbsp;')
              )
            )
          )
       ) .
       html::tag('td', array('onclick' => 'if(typeof calendar_gui.getTask == "function"){ calendar_gui.getTask(' . $task['event_id'] . ', ' . $task['start'] . ', ' . $task['due'] . ', ' . $task['clone'] . ') }', 'title' => ($task['status'] ? $this->gettext($task['status']) : ''), 'class' => 'ui-widget-content scrollbar'), ($task['status'] ? '&nbsp;' . str_replace(' ', '&nbsp;', $this->gettext($task['status'])) : '')) 
      );
    }
    $html .= html::tag('tr', array('class' => 'notasks', 'style' => 'display: none;'), html::tag('td', array('colspan' => 8, 'width' => '100%', 'class' => 'ui-widget-content'), html::tag('center', null, $this->gettext('notasks'))));
    $html = html::tag('table', array('cellpadding' => 0, 'cellspacing' => 0, 'width' => '100%'), $html);
    $rcmail->output->command('plugin.getTasks', array('html' => $html, 'script' => $script));
  }

  function getSettings(){
    $rcmail = rcmail::get_instance();
    if($rcmail->config->get('caldav_protect') && $_SESSION['user_id']){
      $caldavs = array_merge($rcmail->config->get('default_caldavs', array()), $rcmail->config->get('detected_caldavs', array()));
      $default_categories = $rcmail->config->get('default_categories', array());
      $categories = $rcmail->config->get('categories', array());
      foreach($default_categories as $category => $color){
        if(!isset($categories[$category])){
          $categories[$category] = $default_categories[$category];
        }
      }
      $rcmail->user->save_prefs(array('caldavs' => $caldavs, 'categories' => $categories));
    }
    $_SESSION['tzname'] = get_input_value('_tzname', RCUBE_INPUT_POST);
    $settings = array();
    $settings['max_execution_time'] = ini_get('max_execution_time');
    $settings['backend'] = $rcmail->config->get('backend', 'dummy');
    $settings['caldav_replication_range'] = $rcmail->config->get('caldav_replication_range',array(
      'past'   => 2, // (x)
      'future' => 2, // (y)
    ));
    $public_caldavs = $rcmail->config->get('public_caldavs', array());
    $caldavs = $rcmail->config->get('caldavs_subscribed', false);
    if(!is_array($caldavs)){
      $caldavs = array_merge($rcmail->config->get('caldavs', array()), $rcmail->config->get('public_caldavs', array()));
    }
    foreach($public_caldavs as $category => $caldav){
      if(!isset($caldavs[$category])){
        unset($public_caldavs[$category]);
      }
    }
    $caldavs = array_merge($caldavs, $public_caldavs);
    $noduplicates = array();
    foreach($caldavs as $category => $props){
      $noduplicates[$props['url']][$category] = $caldavs[$category];
    }
    foreach($noduplicates as $url => $caldav){
      foreach($caldav as $category => $props){
        $caldavs[$category] = $props;
      }
    }
    foreach($caldavs as $category => $caldav)
      $settings['caldavs'][] = $category;
    if(count($settings['caldavs']) == 0)
      $settings['caldavs'] = true;
    // template objects
    $settings['boxtitle'] = $this->boxTitle(array());
    $settings['usersselector'] = $this->usersSelector(array());
    $settings['categorieshtml'] = $this->generateHTML();
    // configuration
    $settings['default_view'] = (string)$rcmail->config->get('default_view', 'agendaWeek');
    $settings['timeslots'] = (int)$rcmail->config->get('timeslots', 2);
    $settings['first_day'] = (int)$rcmail->config->get('first_day', 1);
    $settings['first_hour'] = (int)$rcmail->config->get('first_hour', 6);
    $settings['duration'] = (int) (60 * 60 * $rcmail->config->get('default_duration',1));
    $settings['clienttimezone'] = (string)$rcmail->config->get('timezone', 'auto');
    $settings['cal_previews'] = (int)$rcmail->config->get('cal_previews', 0);
    //jquery ui theme
    $settings['ui_theme_main'] = $rcmail->config->get('ui_theme_main_cal', true);
    $settings['ui_theme_upcoming'] = $rcmail->config->get('ui_theme_upcoming_cal', true);
    // date formats
    switch($rcmail->config->get('date_format', 'm/d/Y')){
      case 'Y-m-d':
      case 'Y-m-d H:i':
        $ddatepart = 'yyyy-MM-dd';
        $wdatepart = 'MM-dd';
        break;
      case 'd-m-Y':
      case 'd-m-Y H:i':
        $ddatepart = 'dd-MM-yyyy';
        $wdatepart = 'dd-MM';
        break;
      case 'm-d-Y':
      case 'm-d-Y H:i':
        $ddatepart = 'MM-dd-yyyy';
        $wdatepart = 'MM-dd';
        break;
      case 'Y/m/d':
      case 'Y/m/d H:i':
        $ddatepart = 'yyyy/MM/dd';
        $wdatepart = 'MM/dd';
        break;
      case 'm/d/Y':
      case 'm/d/Y H:i':
        $ddatepart = 'MM/dd/yyyy';
        $wdatepart = 'MM/dd';
        break;
      case 'd/m/Y':
      case 'd/m/Y H:i':
        $ddatepart = 'dd/MM/yyyy';
        $wdatepart = 'dd/MM';
        break;
      case 'd.m.Y':
      case 'd.m.Y H:i':
        $ddatepart = ' dd.MM.yyyy';
        $wdatepart = 'dd.MM.';
        break;
      case 'j.n.Y':
      case 'j.n.Y H:i':
        $ddatepart = 'd.M.yyyy';
        $wdatepart = 'd.M';
     default:
        $ddatepart = ' dd.MM.yyyy';
        $wdatepart = 'dd.MM.';
        break;
    }
    $settings['titleFormatDay'] = $rcmail->gettext('calendar.titleFormatDay') . ' ' . $ddatepart;
    $settings['titleFormatWeek'] = $ddatepart . ' { \'&#8212;\' ' . $ddatepart . '}';
    $settings['titleFormatMonth'] = $rcmail->gettext('calendar.titleFormatMonth');
    $settings['columnFormatDay'] = $rcmail->gettext('calendar.columnFormatDay') . ' ' . $ddatepart;
    $settings['columnFormatWeek'] = $rcmail->gettext('calendar.columnFormatWeek') . ' ' . $wdatepart;
    $settings['columnFormatMonth'] = $rcmail->gettext('calendar.columnFormatMonth');
    //time formats
    switch($rcmail->config->get('time_format', 'h:i A')){
      case 'G:i':
        $timeformat = 'H:mm';
        break;
      case 'H:i':
        $timeformat = 'HH:mm';
        break;
      case 'g:i a':
        $timeformat = 'h:mm tt';
        break;
      case 'h:i A':
        $timeformat = 'hh:mm TT';
        break;
      default:
        $timeformat = 'h:mm tt';
        break;
    }
    $settings['FormatTime'] = $timeformat;
    // localisation
    $settings['days'] = array(
      rcube_label('sunday'),   rcube_label('monday'),
      rcube_label('tuesday'),  rcube_label('wednesday'),
      rcube_label('thursday'), rcube_label('friday'),
      rcube_label('saturday')
    );
    $settings['days_short'] = array(
      rcube_label('sun'), rcube_label('mon'),
      rcube_label('tue'), rcube_label('wed'),
      rcube_label('thu'), rcube_label('fri'),
      rcube_label('sat')
    );
    $settings['months'] = array(
      $rcmail->gettext('longjan'), $rcmail->gettext('longfeb'),
      $rcmail->gettext('longmar'), $rcmail->gettext('longapr'),
      $rcmail->gettext('longmay'), $rcmail->gettext('longjun'),
      $rcmail->gettext('longjul'), $rcmail->gettext('longaug'),
      $rcmail->gettext('longsep'), $rcmail->gettext('longoct'),
      $rcmail->gettext('longnov'), $rcmail->gettext('longdec')
    );
    $settings['months_short'] = array(
      $rcmail->gettext('jan'), $rcmail->gettext('feb'),
      $rcmail->gettext('mar'), $rcmail->gettext('apr'),
      $rcmail->gettext('may'), $rcmail->gettext('jun'),
      $rcmail->gettext('jul'), $rcmail->gettext('aug'),
      $rcmail->gettext('sep'), $rcmail->gettext('oct'),
      $rcmail->gettext('nov'), $rcmail->gettext('dec')
    );
    $settings['today'] = rcube_label('today');
    $settings['calendar_week'] = $rcmail->gettext('calendar.calendar_week');
    // goto Date
    if($date = get_input_value('_date', RCUBE_INPUT_POST)){
      $settings['date'] = $date;
      $settings['event_id'] = get_input_value('_event_id', RCUBE_INPUT_POST);
    }
    $rcmail->output->command('plugin.getSettings', array('settings' => $settings));
  }
  
  function settingsTable($args){
    global $RC_HELP;
    $rcmail = rcmail::get_instance();
    if(!get_input_value('_framed', RCUBE_INPUT_GPC) && substr($args['section'], 0, strlen('calendar')) == 'calendar' && class_exists('calendar_plus')){
      $args['blocks'][$args['section']]['options'] = array(
        'title'   => '',
        'content' => html::tag('div', array('id' => 'pm_dummy'), '')
      );
      return $args;
    }
    $no_override = array_flip($rcmail->config->get('dont_override', array()));
    if($args['section'] == 'calendarfeeds'){
      if(class_exists('calendar_plus')){
        $args = calendar_plus::load_settings('feeds', $args);
      }
    }
    if($args['section'] == 'calendarsharing'){
      if(class_exists('calendar_plus')){
        $this->include_script('program/js/share.js');
        $args = calendar_plus::load_settings('sharing', $args);
      }
    }
    if($args['section'] == 'calendarlink'){
      $this->require_plugin('jscolor');
      $args['blocks']['calendar']['name'] = $this->gettext('calendar');
      if(isset($no_override['backend']) || $rcmail->config->get('caldav_protect') || !class_exists('calendar_plus')){
        $protected = true;
      }
      else{
        $protected = false;
      }
      if(!$protected){
        $field_id = 'rcmfd_backend';
        $select = new html_select(array('name' => '_backend', 'id' => $field_id, 'onchange' => 'document.forms.form.submit()'));
        $select->add($rcmail->config->get('product_name'), "database");
        $select->add('CalDAV', "caldav");
        $args['blocks']['calendar']['options']['backend'] = array(
          'title' => html::label($field_id, Q($this->gettext('calendarprovider'))),
          'content' => $select->show($rcmail->config->get('backend')),
        );
      }
      
      if($rcmail->config->get('backend') == 'database'){
        if(class_exists('calendar_plus')){
          $args = calendar_plus::load_settings('tasks', $args);
        }
      }
      else if($rcmail->config->get('backend') == 'caldav' && !$protected){
        $default_caldav = $rcmail->config->get('default_caldav_backend');
        $caldav_user = $rcmail->config->get('caldav_user');
        if($caldav_user == '%su'){
          list($u, $d) = explode('@', $_SESSION['username']);
          $caldav_user = str_replace('%su', $u, $caldav_user);
        }
        else if($caldav_user == '%u'){
          $caldav_user = str_replace('%u', $_SESSION['username'], $caldav_user);
        }
        $caldav_password = $rcmail->config->get('caldav_password');
        $caldav_url = $rcmail->config->get('caldav_url');
        if(strpos($caldav_url, '%su')){
          list($u, $d) = explode('@', $_SESSION['username']);
          $caldav_url = str_replace('%su', $u, $caldav_url);
        }
        else if(strpos($caldav_url, '%u')){
          $caldav_url = str_replace('%u', $_SESSION['username'], $caldav_url);
        }
        $caldav_auth = $rcmail->config->get('caldav_auth', 'detect');
        if(is_array($default_caldav) && !$rcmail->config->get('caldav_user')){
          $default_caldav = $rcmail->config->get('default_caldav_backend');
          if(strpos($default_caldav['url'], '%su')){
            list($u, $d) = explode('@', $_SESSION['username']);
            $caldav_url = str_replace('%su', $u, $default_caldav['url']);
            $caldav_user = str_replace('%su', $u, $default_caldav['user']);
          }
          else if(strpos($default_caldav['url'], '%u')){
            $caldav_url = str_replace('%u', $_SESSION['username'], $default_caldav['url']);
            $caldav_user = $_SESSION['username'];
          }
          if($default_caldav['pass'] == '%p'){
            $caldav_password = '%p';
          }
        }
        if(!$caldav_url)
          $caldav_url = $rcmail->config->get('caldav_url','https://www.mydomain.tld/calendars/john.doe@mydomain.tld/events');
        
        $input = new html_inputfield(array('name' => '_caldav_user', 'id' => $field_id, 'value' => $caldav_user, 'size' => 28));
        $args['blocks']['calendar']['options']['caldav_user'] = array(
          'title' => html::label($field_id, Q($this->gettext('username'))),
          'content' => $input->show($caldav_user),
        );
      
        $field_id = 'rcmfd_caldav_password';
        $title = $this->gettext('passwordisnotset');
        $pass = '';
        if($rcmail->config->get('caldav_password', $caldav_password)){
          $title = $this->gettext('passwordisset');
          $pass = 'ENCRYPTED';
        }
        if($rcmail->config->get('cal_dont_save_passwords', false)){
          $input = new html_hiddenfield(array('title' => $title, 'name' => '_caldav_password', 'id' => $field_id, 'value' => $pass, 'size' => 21, 'autocomplete' => 'off'));
          $args['blocks']['calendar']['options']['caldav_password'] = array(
            'title' => '',
            'content' => $input->show() . html::tag('script', null, '$("#' . $field_id . '").parent().parent().hide()'),
          );
        }
        else{
          $input = new html_passwordfield(array('title' => $title, 'name' => '_caldav_password', 'id' => $field_id, 'value' => $pass, 'size' => 21, 'autocomplete' => 'off'));
          $args['blocks']['calendar']['options']['caldav_password'] = array(
            'title' => html::label($field_id, Q($this->gettext('password'))),
            'content' => $input->show(),
          );
        }

        $field_id = 'rcmfd_caldav_url';
        $input = new html_inputfield(array('name' => '_caldav_url', 'id' => $field_id, 'value' => $caldav_url, 'size' => 80));
        $args['blocks']['calendar']['options']['caldav_url'] = array(
          'title' => html::label($field_id, Q($this->gettext('caldavurl'))),
          'content' => $input->show($caldav_url) . "<br /><span class='title'><small>&nbsp;" . $this->gettext('forinstance') . ": https://www.mydomain.tld/calendars/john.doe@mydomain.tld/events</small></span>",
        );

        $field_id = 'rcmfd_caldav_auth';
        $select = new html_select(array('name' => '_caldav_auth', 'id' => $field_id));
        $select->add($this->gettext('basic'), 'basic');
        $select->add($this->gettext('detect'), 'detect');
        $args['blocks']['calendar']['options']['caldav_auth'] = array(
          'title' => html::label($field_id, Q($this->gettext('caldavauthentication'))),
          'content' => $select->show($rcmail->config->get('caldav_auth', 'detect')),
        );
        $field_id = 'rcmfd_caldav_reminders';
        $select = new html_select(array('name' => '_caldav_extr', 'id' => $field_id));
        $select->add($rcmail->config->get('product_name'), 'internal');
        /* $select->add($this->gettext('externalreminders'), 'external'); */
        $presel = 'internal';
        if(!$rcmail->config->get('caldav_extr')){
          if($default_caldav['extr'])
            $presel = 'external';
        }
        else{
          $presel = $rcmail->config->get('caldav_extr');
        }
        $args['blocks']['calendar']['options']['caldav_reminders'] = array(
          'title' => html::label($field_id, Q($this->gettext('reminders'))),
          'content' => $select->show($presel),
        );
        
        $field_id = 'rcmfd_caldav_replicate_automatically';
        $select = new html_select(array('name' => '_caldav_replicate_automatically', 'id' => $field_id));
        $select->add($this->gettext('never'), 0);
        for($i=1;$i<13;$i++){
          $select->add($i*5, $i*5*60);
        }
        $args['blocks']['calendar']['options']['caldav_replicate_automatically'] = array(
          'title' => html::label($field_id, Q($this->gettext('replicateautomatically'))),
          'content' => $select->show($rcmail->config->get('caldav_replicate_automatically', 1800)) . "&nbsp;" . $this->gettext('minutes'),
        );
        
        $field_id = 'rcmfd_caldav_replication_range';
        $select = new html_select(array('name' => '_caldav_replication_range', 'id' => $field_id));
        $cy = date('Y', time());
        $cmax = date('Y', strtotime(CALEOT));
        $cmin = date('Y', 0);
        $y = $cy; $z = $cy;
        while($y > $cmin || $z < $cmax){
          $y--; $z++;
          $y = max($y, $cmin); $z = min($z, $cmax);
          $select->add("$y - $z", "$y|$z");
        }
        $preset = $rcmail->config->get('caldav_replication_range', array('past'=>2,'future'=>2));
        $args['blocks']['calendar']['options']['caldav_caldav_replication_range'] = array(
          'title' => html::label($field_id, Q($this->gettext('caldavreplicationrange'))),
          'content' => $select->show(($cy - $preset['past']) . "|" . ($cy + $preset['future'])),
        );
        
        if(class_exists('calendar_plus')){
          $args = calendar_plus::load_settings('tasks', $args);
        }
      }
      else if(!$protected){
        $field_id = 'rcmfd_caldav_user';
        $input = new html_hiddenfield(array('name' => '_caldav_user', 'id' => $field_id, 'value' => $rcmail->config->get('caldav_user'), 'size' => 21));
        $args['blocks']['calendar']['options']['caldav_user'] = array(
          'title' => '',
          'content' => $input->show($rcmail->config->get('caldav_user')) . html::tag('script', null, '$("#' . $field_id . '").parent().parent().hide()'),
        );
      
        $field_id = 'rcmfd_caldav_url';
        $input = new html_hiddenfield(array('name' => '_caldav_url', 'id' => $field_id, 'value' => $rcmail->config->get('caldav_url')));
        $args['blocks']['calendar']['options']['caldav_url'] = array(
          'title' => '',
          'content' => $input->show($rcmail->config->get('caldav_url')) . html::tag('script', null, '$("#' . $field_id . '").parent().parent().hide()'),
        );
        
        $field_id = 'rcmfd_caldav_reminders';
        $input = new html_hiddenfield(array('name' => '_caldav_extr', 'id' => $field_id, 'value' => $rcmail->config->get('caldav_extr')));
        $args['blocks']['calendar']['options']['caldav_extr'] = array(
          'title' => '',
          'content' => $input->show($rcmail->config->get('caldav_extr')) . html::tag('script', null, '$("#' . $field_id . '").parent().parent().hide()'),
        );
        
        $field_id = 'rcmfd_caldav_auth';
        $input = new html_hiddenfield(array('name' => '_caldav_auth', 'id' => $field_id, 'value' => $rcmail->config->get('caldav_auth')));
        $args['blocks']['calendar']['options']['caldav_auth'] = array(
          'title' => '',
          'content' => $input->show($rcmail->config->get('caldav_auth')) . html::tag('script', null, '$("#' . $field_id . '").parent().parent().hide()'),
        );
        
        $field_id = 'rcmfd_caldav_replicate_automatically';
        $input = new html_hiddenfield(array('name' => '_caldav_replicate_automatically', 'id' => $field_id, 'value' => $rcmail->config->get('caldav_replicate_automatically')));
        $args['blocks']['calendar']['options']['caldav_replicate_automatically'] = array(
          'title' => '',
          'content' => $input->show($rcmail->config->get('caldav_replicate_automatically', 1800)) . html::tag('script', null, '$("#' . $field_id . '").parent().parent().hide()'),
        );
        
        $preset = $rcmail->config->get('caldav_replication_range', array('past'=>2,'future'=>2));
        $cy = date('Y', time());
        $field_id = 'caldav_replication_range';
        $input = new html_hiddenfield(array('name' => '_caldav_replication_range', 'id' => $field_id, 'value' => ($cy - $preset['past']) . "|" . ($cy + $preset['future'])));
        $args['blocks']['calendar']['options']['caldav_caldav_replication_range'] = array(
          'title' => '',
          'content' => $input->show(($cy - $preset['past']) . "|" . ($cy + $preset['future'])) . html::tag('script', null, '$("#' . $field_id . '").parent().parent().hide()'),
        );
      }
      
      $field_id = 'rcmfd_default_category_label';
	  /* CHANGE DEFAULT CALENDAR TEXT */
	  $default_category_label = str_replace( "%u", $_SESSION[ 'username' ], $rcmail->config->get('default_category_label', $this->gettext('defaultcategory')) );
	  
      /* $input = new html_inputfield(array('name' => '_default_category_label', 'id' => $field_id, 'value' => $rcmail->config->get('default_category_label', $this->gettext('defaultcategory')))); */
	  $input = new html_inputfield(array('name' => '_default_category_label', 'id' => $field_id, 'value' => $default_category_label));
      /* $args['blocks']['calendar']['options']['default_category_label'] = array(
        'title' => $this->gettext('defaultcategorylabel'),
        'content' => $input->show($rcmail->config->get('default_category_label', $this->gettext('defaultcategory'))),
      ); */
	  $args['blocks']['calendar']['options']['default_category_label'] = array(
        'title' => $this->gettext('defaultcategorylabel'),
        'content' => $input->show($default_category_label),
      );
      
      $field_id = 'rcmfd_default_category';
      $input = new html_inputfield(array('name' => '_default_category', 'id' => $field_id, 'value' => $rcmail->config->get('default_category', 'd0d0d0'), 'size' => 7, 'class' => 'color', 'readonly' => 'readonly'));
      $args['blocks']['calendar']['options']['default_category'] = array(
        'title' => '- ' . $this->gettext('backgroundcolor'),
        'content' => $input->show($rcmail->config->get('default_category', 'd0d0d0')),
      );
      
      $field_id = 'rcmfd_default_view';
      $select = new html_select(array('name' => '_default_view', 'id' => $field_id));
      $select->add($this->gettext('day'), "agendaDay");
      $select->add($this->gettext('week'), "agendaWeek");
      $select->add($this->gettext('month'), "month");
      $args['blocks']['calendar']['options']['default_view'] = array(
        'title' => html::label($field_id, Q($this->gettext('default_view'))),
        'content' => $select->show($rcmail->config->get('default_view')),
      );
      
      $field_id = 'rcmfd_timeslot';
      $choices = array('1', '2', '3', '4', '6', '12');
      $select = new html_select(array('name' => '_timeslots', 'id' => $field_id));
      $select->add($choices);      
      $args['blocks']['calendar']['options']['timeslots'] = array(
        'title' => html::label($field_id, Q($this->gettext('timeslots'))),
        'content' => $select->show((string)$rcmail->config->get('timeslots','4')),
      );

      $field_id = 'rcmfd_default_duration';
      $choices = array('0.25', '0.50', '0.75', '1.00', '1.50', '2.00');
      $select = new html_select(array('name' => '_default_duration', 'id' => $field_id));
      foreach($choices as $choice){
        $select->add($this->gettext((60 * $choice) . '_min'), $choice);
      }
      $args['blocks']['calendar']['options']['default_duration'] = array(
        'title' => html::label($field_id, Q($this->gettext('duration'))),
        'content' => $select->show((string)$rcmail->config->get('default_duration','1')),
      );
      
      $field_id = 'rcmfd_first_day';   
      $select = new html_select(array('name' => '_first_day', 'id' => $field_id));
      $select->add(rcube_label('sunday'), '0');
      $select->add(rcube_label('monday'), '1');
      $select->add(rcube_label('tuesday'), '2');
      $select->add(rcube_label('wednesday'), '3');
      $select->add(rcube_label('thursday'), '4');
      $select->add(rcube_label('friday'), '5');
      $select->add(rcube_label('saturday'), '6');
      $args['blocks']['calendar']['options']['first_day'] = array(
        'title' => html::label($field_id, Q($this->gettext('first_day'))),
        'content' => $select->show((string)$rcmail->config->get('first_day','1')),
      );

      $field_id = 'rcmfd_workdays';
      $args['blocks']['calendar']['options']['workdays'] = array(
        'title' => html::label($field_id, $this->gettext('workdays')),
        'content' => '',
      );

      $a_weekday = array(
                     'sunday' => 0,
                     'monday' => 1,
                     'tuesday' => 2,
                     'wednesday' => 3,
                     'thursday' => 4,
                     'friday' => 5,
                     'saturday' => 6
                   );

      $workdays = $rcmail->config->get('workdays',array(1,2,3,4,5));
      foreach($a_weekday as $day => $num){
        $field_id = 'rcmfd_work_' . $day;
        $enabled = in_array($num, $workdays);
        $checkbox = new html_checkbox(array('name' => '_workdays[]', 'id' => $field_id, 'value' => $num));
        $args['blocks']['calendar']['options']['work_' . $day] = array(
          'title' => html::label($field_id, Q('- ' . $this->gettext($day))),
          'content' => $checkbox->show($enabled?$num:false),
        );
      }
      $IDENTITIES = $rcmail->user->list_identities();
      $identities = array();
      $is_set = false;
      foreach($IDENTITIES as $key => $identity){
        $is_set = true;
        $identities[$identity['email']] = $identity['email'];
      }
      if($is_set){
        $field_id = 'rcmfd_cal_notify';
        $enabled = $rcmail->config->get('cal_notify');
        $checkbox = new html_checkbox(array('name' => '_cal_notify', 'id' => $field_id, 'value' => 1));
        $args['blocks']['calendar']['options']['notify'] = array(
          'title' => html::label($field_id, Q($this->gettext('cal_notify'))),
          'content' => $checkbox->show($enabled?1:0),
        );
             
        $field_id = 'rcmfd_cal_notify_to';
        $select = new html_select(array('name' => '_cal_notify_to', 'id' => $field_id));
        foreach($identities as $key => $val){
          $select->add($key, $val);
        }
    
        $args['blocks']['calendar']['options']['cal_notify_to'] = array(
          'title' => '- ' . html::label($field_id, Q($this->gettext('cal_notify_to'))),
          'content' => $select->show($rcmail->config->get('cal_notify_to')),
        );
       
        $field_id = 'rcmfd_caldav_notify';
        $enabled = $rcmail->config->get('caldav_notify');
        $checkbox = new html_checkbox(array('name' => '_caldav_notify', 'id' => $field_id, 'value' => 1));
        $args['blocks']['calendar']['options']['caldav_notify'] = array(
          'title' => html::label($field_id, Q($this->gettext('caldav_notify'))),
          'content' => $checkbox->show($enabled?1:0),
        );
             
        if(class_exists('sabredav') && method_exists('sabredav', 'about')){
          $v = sabredav::about(array('version'));
          $v = $v['version'];
          if($v > '3'){
            $field_id = 'rcmfd_caldav_notify_to';
            $select = new html_select(array('name' => '_caldav_notify_to', 'id' => $field_id));
            foreach($identities as $key => $val){
              $select->add($key, $val);
            }
    
            $args['blocks']['calendar']['options']['caldav_notify_to'] = array(
              'title' => '- ' . html::label($field_id, Q($this->gettext('cal_notify_to'))),
              'content' => $select->show($rcmail->config->get('caldav_notify_to')),
            );
          }
        }
      }
      if(!isset($no_override['upcoming_cal']) && class_exists('calendar_plus')){
        $args = calendar_plus::load_settings('upcoming', $args);
      }
      if(class_exists('calendar_plus')){
        $args = calendar_plus::load_settings('birthdays', $args);
      }
    }
    
    if($args['section'] == 'calendarcategories'){
      $this->include_script('program/js/settings.js');
      $this->require_plugin('jscolor');
      $rcmail->output->add_label(
        'calendar.remove_category',
        'calendar.unlink_caldav',
        'calendar.unlink_caldav_warning',
        'calendar.protected',
        'calendar.save',
        'calendar.cancel',
        'calendar.remove'
      );
      $args['blocks']['calendarcategories']['name'] = $this->gettext('categories');
      
      // PRINCIPALS URL
      $principal_url_label = html::span( array( 'style' => 'font-weight:bold;' ), "Principal URL: " ); // will set to null if no account is managed
      
      $parsed_caldav_url = parse_url( $rcmail->config->get('caldav_url') );
      $principal_url = $parsed_caldav_url['scheme']."://".$parsed_caldav_url['host']."/dav/calendarserver.php/principals/".$_SESSION[ 'username' ]."/";
      
      $principal_url_block =  html::span('principal_url_block', $principal_url );
      
      $args['blocks']['calendarcategories']['options']['desc_message'] = array(
        'content' => $principal_url_label.$principal_url_block
      );
      
      $field_id = 'rcmfd_categories';
      $args['blocks']['calendarcategories']['options']['categories'] = array(
        'title' => html::label($field_id, $this->gettext('categories')),
        'content' => '<input type="button" value="+" title="' . $this->gettext('add_category') . '" onClick="addRowCategories(30)">',
      );
      $caldavs = $rcmail->config->get('caldavs', array());
      $merge = array();
      if(is_array($_SESSION['detected_caldavs'])){
        foreach($_SESSION['detected_caldavs'] as $category => $props){
          if(!$categories[$category]){
            $merge[$category] = '#' . $rcmail->config->get('default_category', 'c0c0c0');
          }
        }
        $caldavs = array_merge($_SESSION['detected_caldavs'], $caldavs);
      }
	  /* CHANGE DEFAULT CALENDAR TEXT */
	  $default_caldav_label = str_replace( "%u", $_SESSION[ 'username' ], $rcmail->config->get('default_category_label', $this->gettext('defaultcategory')) );
      /* $public = array_merge((array)$rcmail->config->get('public_categories',array()), array($rcmail->config->get('default_category_label', $this->gettext('defaultcategory')) => $rcmail->config->get('default_category'))); */
      $public = array_merge((array)$rcmail->config->get('public_categories',array()), array($default_caldav_label => $rcmail->config->get('default_category')));
      $public_override = $rcmail->config->get('public_categories_override', array());
      foreach($public_override as $key => $val){
        if(isset($public[$key])){
          $public[$key] = $val;
        }
      }
	  
      $categories = (array)$rcmail->config->get('categories',array());
      $categories = array_merge($categories, $public);
      $categories = array_reverse(array_merge($merge, $categories));
      $skin = $rcmail->config->get('skin');
      if($skin == 'larry'){
        $temp = INSTALL_PATH . "plugins/calendar/skins/larry/images/rename.png";
        $icon = "./plugins/calendar/skins/larry/images/rename.png";
      }
      else{
        $temp = INSTALL_PATH . "skins/$skin/images/icons/rename.png";
        $icon = "./skins/$skin/images/icons/rename.png";
      }
      $temp = getimagesize($temp);
      $radio_disabled = '';
      if(count($caldavs) >= $rcmail->config->get('max_caldavs',3))
        $radio_disabled = 'disabled';
	  
      foreach($categories as $key => $val){
        $skey = str_replace(' ', '_', str_replace('"', '_', str_replace("'", '_', $key)));
        $field_id = asciiwords('rcmfd_category_' . $key, true, '_');
        $readonly = array();
        $name = '_categories[]';
        if(isset($public[$key])){
			/* CHANGE DEFAULT CALENDAR TEXT */
			$default_caldav_label = str_replace( "%u", $_SESSION[ 'username' ], $rcmail->config->get('default_category_label', $this->gettext('defaultcategory')) );
          /* if($key == $rcmail->config->get('default_category_label', $this->gettext('defaultcategory'))){ */
          if($key == $default_caldav_label){
            $readonly = array();
            $name = '_default_category_label';
          }
          else{
            $readonly = array('readonly' => true);
            $name = '_public_categories[]';
          }
        }
		
        if(isset($caldavs[$key])){
			// for shared calendars, is_caldav_owner is set to 0. check this value. if caldav owner, then dont display the calendar. 
			// Macgregor Changes
			if( ( isset( $caldavs[ $key ][ 'is_caldav_owner' ] ) ) && ( $caldavs[ $key ][ 'is_caldav_owner' ] == 0 ) )
			{
				$input_shared_calendar_name = new html_inputfield( array( 'id' => '_readonly_shared_calendar_name', 'name' => '_readonly_shared_calendar_name[]', 'size' => '30' ) );
				$input_shared_calendar_color = new html_inputfield( array( 'id' => '_readonly_shared_calendar_color', 'name' => '_readonly_shared_calendar_color[]', 'size' => '6', 'class' => 'color', 'value' => $val ) );
				
				// Edit Button for shared calendar: 
				$edit_button_html = '&nbsp;<span class="edit_caldav" id="edit_' . $skey . '" ><img onclick="show_default_shared_cal_url( this, \'' . addslashes($key) . '\', \'' . asciiwords($key, true, '_') . '\')" width="' . $temp[0] . '" height="' . $temp[1] . '" align="absmiddle" title="' . $this->gettext('edit') . '" src="' . $icon . '" /></span>';
				
				
				$args['blocks']['calendarcategories']['options']['_hidden_shared_caldav'.$key] = array(
					'title' => '',
					'content' => $input_shared_calendar_name->show( $key, array( "readonly" => "true", "style" => "background:#F0F0F0;margin-left:30px;" ) ) ."&nbsp;". $input_shared_calendar_color->show(). $edit_button_html
				);
				continue;
			}
          $readonly = array('readonly' => true);
        }
        $input_category = new html_inputfield(array_merge(array('name' => $name, 'id' => $field_id, 'size' => 30, 'title' => $key), $readonly));
        $disabled = '';
        $field_id = asciiwords('rcmfd_color_' . $key . '_' . $val);
        $name = '_colors[]';
        if(isset($public[$key])){
			/* CHANGE DEFAULT CALENDAR TEXT */
			$default_caldav_label = str_replace( "%u", $_SESSION[ 'username' ], $rcmail->config->get('default_category_label', $this->gettext('defaultcategory')) );
          /* if($key == $rcmail->config->get('default_category_label', $this->gettext('defaultcategory'))){ */
		  if($key == $default_caldav_label){
            $disabled = '';
            $name = '_default_category';
          }
          else{
            $disabled = '';
            $name = '_public_colors[]';
          }
        }
        $input_category_color = new html_inputfield(array('name' => $name, 'id' => $field_id, 'size' => 6, 'title' => $val, 'class' => 'color', 'disabled' => $disabled));
        $append = '';
        if(!isset($public[$key]) && $rcmail->config->get('backend') == 'caldav'){
          if($rcmail->config->get('caldav_protect')){
            $append = '';
          }
          else{
            $append = '<input style="display:none" id="dialog_handler_' . $skey . '"' . $radio_disabled . ' title="' . $this->gettext('add_caldav') . '" onclick="calendar_toggle_caldav(this, \'' . addslashes($key) . '\', \'' . asciiwords($key, true, '_') . '\')" name="dialog_handler" type="radio" ' . $disabled . ' />';
          }
		  
          $display = 'hidden';
          if(!empty($caldavs[$key])){
            $display = 'visible';
          }
		  
          if($rcmail->config->get('caldav_protect')){
            $append = '';
          }
          else if($display == 'hidden'){
			// if not a caldv, then display radiobutton
            $append = '<input id="dialog_handler_' . $skey . '"' . $radio_disabled . ' title="' . $this->gettext('add_caldav') . '" onclick="calendar_toggle_caldav(this, \'' . addslashes($key) . '\', \'' . asciiwords($key, true, '_') . '\')" name="dialog_handler" type="radio" ' . $disabled . ' />';
          }
          if($rcmail->config->get('caldav_protect')){
            $append = ' ';
          }
          /* else{
			// edit button at right hand side of category
            $append .= '&nbsp;<span class="edit_caldav" style="visibility:' . $display . '" id="edit_' . $skey . '" ><img onclick="calendar_toggle_caldav(this, \'' . addslashes($key) . '\', \'' . asciiwords($key, true, '_') . '\')" width="' . $temp[0] . '" height="' . $temp[1] . '" align="absmiddle" title="' . $this->gettext('edit') . '" src="' . $icon . '" /></span>';
          } */
        }
		
		if( $key == $default_caldav_label ) // if default calendar..
		{
			$append = '&nbsp;<span class="edit_caldav" id="edit_' . $skey . '" ><img onclick="show_default_shared_cal_url( this, \'' . addslashes($key) . '\', \'' . asciiwords($key, true, '_') . '\')" width="' . $temp[0] . '" height="' . $temp[1] . '" align="absmiddle" title="' . $this->gettext('edit') . '" src="' . $icon . '" /></span>';
		}
		else
		{
			$append .= '&nbsp;<span class="edit_caldav" style="visibility:' . $display . '" id="edit_' . $skey . '" ><img onclick="calendar_toggle_caldav(this, \'' . addslashes($key) . '\', \'' . asciiwords($key, true, '_') . '\')" width="' . $temp[0] . '" height="' . $temp[1] . '" align="absmiddle" title="' . $this->gettext('edit') . '" src="' . $icon . '" /></span>';
		}
		
		// Remove Button on left hand side of categories..
        $remove = '<input id="category_handler_' . $skey . '" type="button" value="X" onclick="removeRow(this.parentNode.parentNode)" title="' . $this->gettext('remove_category') . '" />';
        if(isset($public[$key]) || ($append && isset($caldavs[$key]))){
          $label = $this->gettext('protected');
          $onclick = '';
          if(isset($caldavs[$key])){
            if($rcmail->config->get('caldav_protect')){
              $label = $this->gettext('protected');
            }
            else{
              $label = $this->gettext('unlink_caldav');
            }
            $onclick = 'onclick="calendar_toggle_caldav(this, \'' . $skey . '\')"';
          }
          $remove = '<input id="category_handler_' . $skey . '" ' . $onclick . ' type="button" class="protected_category" value="X" title="' . $label . '" />';
        }
		
		$args['blocks']['calendarcategories']['options']['category_' . $key] = array(
		  'title' => html::label($field_id, ''),
		  'content' => $remove . '&nbsp;' . $input_category->show($key) . "&nbsp;" . $input_category_color->show($val) . $append,
		);
      }
    }
    return $args;
  }
  
  function saveSettings($args){
	global $RC_SABRE_HELP, $RC_HELP;
    $rcmail = rcmail::get_instance();
    if($_SESSION['tzname']){
      $args['prefs']['tzname'] = $_SESSION['tzname'];
    }
    if($tz = get_input_value('_timezone', RCUBE_INPUT_POST)){
      if($args['prefs']['tzname'] != $tz){
        $args['prefs']['ctags'] = array();
        $rcmail->session->remove('caldav_allfetched');
        $rcmail->session->remove('caldav_resume_replication');
      }
    }
    if($args['section'] == 'calendarfeeds'){
      $feeds = get_input_value('_calendarfeeds', RCUBE_INPUT_POST);
      $categories = get_input_value('_feedscategories', RCUBE_INPUT_POST);
      $feeds_prev = $rcmail->config->get('calendarfeeds', array());
      $feeds_subscribed = $rcmail->config->get('feeds_subscribed', array());
      if(is_array($feeds) && is_array($categories)){
        $feeds = array_combine($feeds, $categories);
      }
      else
        $feeds = array();
      $public = $rcmail->config->get('public_calendarfeeds', array());
      $pfeeds = array();
      foreach($public as $url => $cat){
        $pfeeds[$url] = $cat;
      }
      $this->clearCache();
      foreach($feeds as $key => $val){
        if(!empty($key)){
          $feedurl = $this->getURL() . "?_task=dummy&_action=plugin.calendar_showlayer&_userid=" . $rcmail->user->data['user_id'] . "&_ct=" . $rcmail->config->get('caltokenreadonly');
          if(strtolower($key) != strtolower($feedurl)){
            $cr = $this->utils->curlRequest($key);
            if(strtolower(substr($cr,0, 5)) == "<?xml"){
              //ok
            }
            else{
              if(is_array($this->utils->importEvents($cr, false, true))){
                //ok
              }
              else if(!is_array(json_decode($cr))){
                unset($feeds[$key]);
              }
            }
          }
          else{
            unset($feeds[$key]);
          }
        }
        else{
          unset($feeds[$key]);
        }
        if($feeds[$key] &&!$feeds_prev[$key]){
          $feeds_subscribed[$key] = $val;
        }
      }
      $args['prefs']['calendarfeeds'] = $feeds;
      foreach($feeds_subscribed as $key => $val){
        if(!$feeds[$key] && !$pfeeds[$key]){
          unset($feeds_subscribed[$key]);
        }
        else if($feeds[$key] && $feeds[$key] != $feeds_subscribed[$key]){
          $feeds_subscribed[$key] = $feeds[$key];
        }
        else if($pfeeds[$key] && $pfeeds[$key] != $feeds_subscribed[$key]){
          $feeds_subscribed[$key] = $pfeeds[$key];
        }
      }
      $args['prefs']['feeds_subscribed'] = $feeds_subscribed;
    }
    if($args['section'] == 'calendarsharing'){
		// Macgregor Changes
		// $args array is used as per the framework... 
		
		// DATA TO ADD CALENDAR IN WEBMAIL DATABASE
		$caldav_str = get_input_value('_share_calendar_name', RCUBE_INPUT_POST); // Name of the Calendar to be shared along with url
		if( $caldav_str != 'null' )
		{
			$caldav_name_url_arr = explode( "|", $caldav_str );
			
			$category = $caldav_name_url_arr[ 0 ];
			
			$calendar_owner = $_SESSION[ 'username' ]; // calendar owner for inserting groupmember
			$explode_calendar_owner = explode( "@", $calendar_owner );
			$calendar_owner_domain = $explode_calendar_owner[ 1 ];
			
			$hidden_username_string = get_input_value('_cal_share_hidden_username', RCUBE_INPUT_POST); // String of user with whom calendar is to be shared
			// $args['prefs'][ 'caldav_url' ] = $caldav_url  = get_input_value('_caldav_url', RCUBE_INPUT_POST);
			$caldav_url = get_input_value('_caldav_url', RCUBE_INPUT_POST);
			
			$pass = $rcmail->decrypt($_SESSION['password']);
			$url = $caldav_url;
			$extr = 'internal';
			$auth = 'detect';
			
			if( strlen( $hidden_username_string ) > 0 )
			{
				$username_array = explode( "|", $hidden_username_string ); // Array of users with whom calendar is to be shared
			}
			
			$RC_SABRE_HELP->unsubscribe_all_users( $calendar_owner, $caldav_url, $category, true );
			for( $i = 0; $i < count( $username_array ); $i++ )
			{
				// TODO: while sharing the calendar, the $user, $pass are saved as the credentials of the owners calendars. Should be changed to users calendar. Username and email can be used by rc_helper, but, password cannot be taken
				// $cal_user_details = $RC_HELP->get_user_det_by_email( $username_array[ $i ] );
				$calendar_user_prefs = $rcmail->user->get_user_prefs_by_username( $username_array[ $i ] );
				
				$calendar_user_prefs[ 'categories' ][ $category ] = $rcmail->config->get( 'shared_calendar_default_color'); // set category of the prefs array
				
				$subscribed_caldav_details = array(
				  'user'        	=> $calendar_owner,
				  'url'         	=> $url,
				  'auth'    	    => $auth,
				  'extr'	        => $extr,
				  'is_caldav_owner' => 0,
				);
				
				$calendar_user_prefs['caldavs'][$category] = $subscribed_caldav_details; // set caldav category
				$calendar_user_prefs['caldavs_subscribed'][$category] = $subscribed_caldav_details; // subscribe to this category
				$calendar_user_prefs['caldavs'][$category][ 'pass' ] = $rcmail->encrypt($pass);
				$calendar_user_prefs['ctags'] = array();
				
				$serialized_pref = serialize( $calendar_user_prefs );
				if( $RC_SABRE_HELP->insert_groupmember( $username_array[ $i ], $calendar_owner ) )
				{
					$rcmail->user->set_specific_user_pref( $serialized_pref, $username_array[ $i ] );
				}
			}
		}
		
      /* $rcmail = rcmail::get_instance();
      $args['prefs']['caltoken'] = get_input_value('_caltoken', RCUBE_INPUT_POST);
      $args['prefs']['caltokenreadonly'] = get_input_value('_caltokenreadonly', RCUBE_INPUT_POST);
      $args['prefs']['caltoken_davreadonly'] = get_input_value('_caltoken_davreadonly', RCUBE_INPUT_POST);
      if(isset($_POST['_caltoken_davreadonly_submit_x'])){
        $args['prefs']['caltoken_davreadonly'] = false;
        $this->SabreDAVAuth('delete', 'users_cal_r');
      }
      if(isset($_POST['_caltoken_submit_x'])){
        $args['prefs']['caltoken'] = false;
        $this->SabreDAVAuth('delete', 'users_cal_rw');
      }
      if($_POST['_caltoken_davreadonly_submit']){
        $args['prefs']['caltoken_davreadonly'] = $this->SabreDAVAuth('create', 'users_cal_r');
      }
      if($_POST['_caltoken_submit']){
        $args['prefs']['caltoken'] = $this->SabreDAVAuth('create', 'users_cal_rw');
      }
      $conf = $rcmail->config->all();
      foreach($conf as $key => $val){
        if(substr($key, 0, strlen('cal_shares_')) == 'cal_shares_'){
          $args['prefs'][$key] = 0;
        }
      }
      foreach($_POST as $key => $val){
        if(substr($key, 0, strlen('_cal_shares_')) == '_cal_shares_'){
          $args['prefs'][substr($key, 1)] = get_input_value($key, RCUBE_INPUT_POST);
        }
      }
      foreach($conf as $key => $val){
        if(substr($key, 0, strlen('cal_shares_readonly_')) == 'cal_shares_readonly_'){
          $args['prefs'][$key] = 0;
        }
      }
      foreach($_POST as $key => $val){
        if(substr($key, 0, strlen('_cal_shares_readonly_')) == '_cal_shares_readonly_'){
          $args['prefs'][substr($key, 1)] = get_input_value($key, RCUBE_INPUT_POST);
        }
      }  */
    }
    if($args['section'] == 'calendarlink'){
      $rcmail = rcmail::get_instance();
      $rcmail->session->remove('cal_initialized');
      $rcmail->session->remove('reminders');
      $rcmail->session->remove('caldav_allfetched');
      $rcmail->session->remove('caldav_resume_replication');
      $args['prefs']['ctags'] = array();
      if($rcmail->config->get('backend') == 'caldav'){
        $this->backend->truncateEvents(3);
      }
      $caldav_user = trim(get_input_value('_caldav_user', RCUBE_INPUT_POST));
      $caldav_url = trim(get_input_value('_caldav_url', RCUBE_INPUT_POST));
      $caldav_password = trim(get_input_value('_caldav_password', RCUBE_INPUT_POST));
      if($rcmail->decrypt($_SESSION['password']) == $caldav_password){
        $caldav_password = '%p';
      }
      if(!$rcmail->config->get('caldav_user') && is_array($rcmail->config->get('default_caldav_backend'))){
        $default_caldav = $rcmail->config->get('default_caldav_backend', array());
        if(strpos($default_caldav['url'], '%su')){
          list($u, $d) = explode('@', $_SESSION['username']);
          $caldav_url = str_replace('%su', $u, $default_caldav['url']);
          $caldav_user = str_replace('%su', $u, $default_caldav['user']);
        }
        else if(strpos($default_caldav['url'], '%u')){
          $caldav_url = str_replace('%u', $_SESSION['username'], $default_caldav['url']);
          $caldav_user = $_SESSION['username'];
        }
      }
      $args['prefs']['caldav_user'] = $caldav_user;
      if($caldav_password != 'ENCRYPTED' && $caldav_password != ''){
        $args['prefs']['caldav_password'] = $rcmail->encrypt($caldav_password);
      }
      else{
        $args['prefs']['caldav_password'] = $rcmail->config->get('caldav_password');
      }
      if($caldav_url != '')
        $args['prefs']['caldav_url'] = $caldav_url;
      $auth = trim(get_input_value('_caldav_auth', RCUBE_INPUT_POST));
      if($auth){
        $args['prefs']['caldav_auth'] = trim(get_input_value('_caldav_auth', RCUBE_INPUT_POST));
        $args['prefs']['caldav_extr'] = trim(get_input_value('_caldav_extr', RCUBE_INPUT_POST));
        $args['prefs']['caldav_replicate_automatically'] = (int) trim(get_input_value('_caldav_replicate_automatically', RCUBE_INPUT_POST));
        $args['prefs']['caldav_replication_range_tasks'] = (int) trim(get_input_value('_caldav_replication_range_tasks', RCUBE_INPUT_POST));
        $replication = trim(get_input_value('_caldav_replication_range', RCUBE_INPUT_POST));
        $temparr = explode('|',$replication);
        if(count($temparr) == 2){
          $past = $temparr[0];
          $future = $temparr[1];
          if(is_numeric($past) && is_numeric($future)){
            $cy = date('Y', time());
            $past = $cy - $past;
            $future = $future - $cy;
            $args['prefs']['caldav_replication_range'] = array('past'=>$past,'future'=>$future);
          }
        }
      }
      $args['prefs']['default_category'] = get_input_value('_default_category', RCUBE_INPUT_POST);
	  /* CHANGE DEFAULT CALENDAR TEXT */
      /* $args['prefs']['default_category_label'] = get_input_value('_default_category_label', RCUBE_INPUT_POST); */
	  $args['prefs']['default_category_label'] = str_replace( "%u", $_SESSION[ 'username' ], get_input_value('_default_category_label', RCUBE_INPUT_POST) );
      $args['prefs']['backend'] = get_input_value('_backend', RCUBE_INPUT_POST);
      $args['prefs']['upcoming_cal'] = isset($_POST['_upcoming_cal']) ? true : false;
      $args['prefs']['show_birthdays'] = isset($_POST['_show_birthdays']) ? true : false;
      $args['prefs']['workdays'] = get_input_value('_workdays', RCUBE_INPUT_POST);
      $args['prefs']['default_duration'] = get_input_value('_default_duration', RCUBE_INPUT_POST);
      $args['prefs']['default_view'] = get_input_value('_default_view', RCUBE_INPUT_POST);
      $args['prefs']['timeslots'] = get_input_value('_timeslots', RCUBE_INPUT_POST);
      $args['prefs']['first_day'] = get_input_value('_first_day', RCUBE_INPUT_POST);
      $args['prefs']['cal_notify'] = isset($_POST['_cal_notify']) ? true : false;
      $args['prefs']['cal_notify_to'] = get_input_value('_cal_notify_to', RCUBE_INPUT_POST);
      $args['prefs']['caldav_notify'] = isset($_POST['_caldav_notify']) ? true : false;
      $args['prefs']['caldav_notify_to'] = get_input_value('_caldav_notify_to', RCUBE_INPUT_POST);
    }
    
    if($args['section'] == 'calendarcategories'){
	  $rcmail = rcmail::get_instance();
      $this->clearCache();
      $categories = get_input_value('_categories', RCUBE_INPUT_POST);
      $colors = get_input_value('_colors', RCUBE_INPUT_POST);
      $default_category = get_input_value('_default_category', RCUBE_INPUT_POST);
      $default_category_label = get_input_value('_default_category_label', RCUBE_INPUT_POST);
      $public_categories = get_input_value('_public_categories', RCUBE_INPUT_POST);
      $public_colors = get_input_value('_public_colors', RCUBE_INPUT_POST);
	  $shared_calendar_name = get_input_value('_readonly_shared_calendar_name', RCUBE_INPUT_POST);
	  $shared_calendar_color = get_input_value('_readonly_shared_calendar_color', RCUBE_INPUT_POST);
	  
	  $subscribed_category_array = array(); // array to store subscribed calendar name and color
	  
	  for( $i = 0; $i < count( $shared_calendar_name ); $i++ )
	  {
			$calendar_name = $shared_calendar_name[ $i ];
			$calendar_color = $shared_calendar_color[ $i ];
			$subscribed_category_array[ $calendar_name ] = $calendar_color;
	  }
	  
      if(is_array($categories)){
        foreach($categories as $key => $val){
          if($val == ''){
            unset($categories[$key]);
            unset($colors[$key]);
          }
          else{
            $categories[$key] = $val;
          }
          if(substr($val, 0, 1) == '?'){
            $val = substr($val, 1);
          }
        }
        if(is_array($colors)){
          $categories = array_combine($categories, $colors);
        }
      }
	  
	  // Macgregor Changes
      // $args['prefs']['categories'] = (array)$categories;
      $args['prefs']['categories'] = array_merge( (array)$categories, $subscribed_category_array );
	  
      if(is_array($public_categories) && is_array($public_colors)){
        $public_categories = array_combine($public_categories, $public_colors);
        $public_categories = array_merge(rcmail::get_instance()->config->get('public_categories', array()), $public_categories);
      }
      $args['prefs']['public_categories_override'] = (array)$public_categories;
      $args['prefs']['default_category'] = $default_category;
      if($default_category_label){
        $args['prefs']['default_category_label'] = $default_category_label;
      }
    }
    return $args;
  }
  
  function SabreDAVAuth($action, $table){
    $rcmail = rcmail::get_instance();
    if($_SESSION['user_id']){
      $alphanum = 'abcdefghijklmnopqrstuvwxyz';
      $alphanum .= strtoupper($alphanum) . '0123456789';
      $raw = '';
      for($i = 0; $i < 8; $i++){
        $raw .= substr($alphanum, rand(0, strlen($alphanum) - 1), 1);
      }
      $chars = '+-?!';
      $char1 = substr($chars, rand(0, 3), 1);
      $char2 = substr($chars, rand(0, 3), 1);
      $pos1 = rand(1, 6);
      $pos2 = rand(1, 6);
      $stoken = $raw;
      $repl1 = substr($stoken, $pos1, 1);
      $repl2 = substr($stoken, $pos2, 1);
      $stoken = str_replace($repl1, $char1, $stoken);
      $pass = str_replace($repl2, $char2, $stoken);
      $sabredb = $rcmail->config->get('db_sabredav_dsn');
      $db = new rcube_db($sabredb, '', FALSE);
      $db->set_debug((bool)$rcmail->config->get('sql_debug'));
      $db->db_connect('r');
      if($action == 'delete'){
        $sql = 'DELETE FROM ' . $rcmail->db->quoteIdentifier(get_table_name($table)) . ' WHERE ' . $rcmail->db->quoteIdentifier('username') . '=?';
        $db->query($sql, $rcmail->user->data['username']);
      }
      else{
        $sql = 'INSERT INTO ' . $rcmail->db->quoteIdentifier(get_table_name($table)) . ' (' . $rcmail->db->quoteIdentifier('rcube_id') . ', ' . $rcmail->db->quoteIdentifier('username') . ', ' . $rcmail->db->quoteIdentifier('digesta1') . ') VALUES (?, ?, ?)';
        $digest = md5($rcmail->user->data['username'] . ':' . $rcmail->config->get('sabredav_realm') . ':' . $pass);
        $db->query($sql, $rcmail->user->ID, $rcmail->user->data['username'], $digest);
      }
    }
    return $pass;
  }

 /****************************
  *
  * Layers / Feeds / ICS
  *
  ****************************/
  
  function boxTitle($p){
    if(class_exists('calendar_plus')){
      $content = calendar_plus::load_boxtitle();
      $p['content'] = $content;
    }
    return $p;
  }

  function setFilters(){
    $rcmail = rcmail::get_instance();
    $prefs = array();
    if(class_exists('calendar_plus')){
      calendar_plus::load_filters('set');
    }
	
    $prefs['calfilter_allcalendars'] = $_SESSION['calfilter'];
    $prefs['event_filters_allcalendars'] = $_SESSION['event_filters'];
    if($_SESSION['user_id'] && $rcmail->user->ID == $_SESSION['user_id']){
      $rcmail->user->save_prefs($prefs);
    }
    $rcmail->output->command('plugin.calendar_refresh', array(0 => $this->boxTitle(array())));
  }
  
  function usersSelector($p) { 
    if(class_exists('calendar_plus')){
      $p['content'] = calendar_plus::load_users('html');
    }
    return $p;
  }
  
  function jsonEvents($start, $end, $className, $editable, $links, $rc_layers = array()){
    $rcmail = rcmail::get_instance();
    $events = array();
    $events_table = $rcmail->config->get('db_table_events', 'events');
    $rcmail->config->set('db_table_events',$rcmail->config->get('db_table_events_cache', 'events_cache'));
    $layers = $this->utils->arrayEvents($start, $end, $className, $editable, $links);
    $rcmail->config->set('db_table_events', $events_table);
    $ret = array_merge($events,$layers);
    if($rcmail->user->ID == $_SESSION['user_id']){
      $ret = array_merge($ret, $rc_layers);
    }
    $arr = $this->filterEvents($ret);
    $bdclass = '';
    $birthdays = (array) $this->getBirthdays('all');
    if(count($birthdays) > 0){
      $locs = scandir(INSTALL_PATH . 'plugins/calendar/localization');
      foreach($locs as $loc){
        if($loc != '.' && $loc != '..' && $loc != 'revision.inc.php'){
          $labels = array();
          include INSTALL_PATH . 'plugins/calendar/localization/' . $loc;
          if($labels['birthday']){
            $bdclass .= asciiwords($labels['birthday'], true, '') . ' ';
            if($this->utils->categories[$labels['birthday']]){
              $colors = $this->utils->categories[$labels['birthday']];
            }
          }
        }
      }
      $bdclass = trim($bdclass);
    }
    if(!$colors){
      $colors = $rcmail->config->get('default_category');
    }
    $fontcolor = '#' . $this->utils->getFontColor($colors);
    $colors = '#' . $colors;
    $stz = date_default_timezone_get();
    $tz = get_input_value('_tzname', RCUBE_INPUT_GPC);
    if($_SESSION['tzname']){
      $tz = $_SESSION['tzname'];
    }
    date_default_timezone_set($tz);
    foreach($birthdays as $birthday){
      $offset = date('O', $birthday['timestamp']);
      $email = '';
      if(is_array($birthday['emails'])){
        foreach($birthday['emails'] as $val){
          if(is_array($val)){
            $email = $val[0];
            break;
          }
        }
      }
      $description = '';
      if((int) date('Y', $birthday['timestamp']) - (int) $birthday['year'] > 0)
        $description = (int) date('Y', $birthday['timestamp']) - (int) $birthday['year'] . ". ";
      $dst_adjust = 0;
      if(date('I') < date('I', $birthday['timestamp']))
        $dst_adjust = -3600;
      $bdate = strtotime(date('m/d/Y 08:00:00', $birthday['timestamp']));
      $arr[] = array(
                      'start' => gmdate('Y-m-d\TH:i:s.000' . $offset, $birthday['timestamp']),
                      'start_unix' => $birthday['timestamp'] + $dst_adjust,
                      'title' => '* ' . $birthday['text'],
                      'description' => $description . $this->gettext('birthday') . ' ' . $birthday['text'] . " (*" . $birthday['year'] . ")",
                      'rr' => false,
                      'reminderservice' => false,
                      'className' => $bdclass,
                      'classNameDisp' => $this->gettext('birthday'),
                      'color' => $colors,
                      'backgroundColor' => $colors,
                      'borderColor' => $colors,
                      'textColor' => $fontcolor,
                      'onclick' => './?_task=mail&_action=compose&_to=' . $email . '&_subject=' . $this->gettext('happybirthday') . '&_date=' . $bdate,
                      'editable' => false,
                      'allDay' => true
                     );
    }
    date_default_timezone_set($stz);
    send_nocacheing_headers();
    header('Content-Type: text/plain; charset=' . $rcmail->output->get_charset());
    echo json_encode($arr);
    exit;
  }
  
  function renew(){
    $rcmail = rcmail::get_instance();
    $rcmail->session->remove('caldav_allfetched');
    $rcmail->session->remove('caldav_resume_replication');
    if($_SESSION['user_id'] && $rcmail->user->ID == $_SESSION['user_id']){
      $save['ctags'] = array();
      // force synce by deleting etags
      $rcmail->user->save_prefs($save);
    }
    $this->backend->truncateEvents(2);
    $date = get_input_value('_date', RCUBE_INPUT_GPC);
    if(!$date){
      $date = time();
    }
    $rcmail->output->redirect(array('_date' => $date, '_action' => 'plugin.calendar', '_task' => 'dummy'));
  }
  
  function replicate(){
    $rcmail = rcmail::get_instance();
    $current_year = date('Y', time());
    $year = get_input_value('_year', RCUBE_INPUT_GPC);
    $range = $rcmail->config->get('caldav_replication_range', array('past' => 2, 'future' => 2));
    $force = get_input_value('_errorgui', RCUBE_INPUT_GPC);
    if($force){
      $range = array('past'=>0,'future'=>0);
      $step = 0;
      $current_year = $year;
    }
    if(!$year)
      $year = date('Y', time()) + $range['future'];
    $year = min($year, $current_year + $range['future']);
    $step = get_input_value('_step', RCUBE_INPUT_GPC);
    if(!$step)
      $step = 1;
    if($_SESSION['caldav_resume_replication']){
      if($_SESSION['caldav_resume_replication'] < $year){
        $year = $_SESSION['caldav_resume_replication'];
      }
    }
    if($year - 1 >= max($current_year - $range['past'], 1970) || $force){
      $end = strtotime($year . "-12-31 23:59:59");
      $year = $year - $step;
      $year = max($current_year - $range['past'], $year);
      $start = max(0,strtotime($year . "-12-31 23:59:59"));
      $this->ctags = $this->backend->getCtags();
      $ctags_saved = $rcmail->config->get('ctags', array());
      $url = $rcmail->config->get('caldav_url');
      if($this->ctags[md5($url)] == false || $ctags_saved[md5($url)] == false || $this->ctags[md5($url)] !== $ctags_saved[md5($url)]){
        if($rcmail->config->get('default_caldav_subscribed', true)){
          $this->backend->replicateEvents($start, $end);
          $this->backend->replicateEvents($start, $end, false, 'todos');
        }
      }
      else{
        //write_log('skip', $url);
      }
      $caldavs = $rcmail->config->get('caldavs', array());
      $public_caldavs = $rcmail->config->get('public_caldavs', array());
      $caldavs = array_merge($caldavs, $public_caldavs);
      foreach($caldavs as $category => $caldav){
        $url = $caldav['url'];
        if($this->ctags[md5($url)] == false || $ctags_saved[md5($url)] == false || $this->ctags[md5($url)] !== $ctags_saved[md5($url)]){
          $this->backend->replicateEvents($start, $end, $category);
          $this->backend->replicateEvents($start, $end, $category, 'todos');
        }
        else{
          //write_log('skip', $url);
        }
      }
      $_SESSION['caldav_resume_replication'] = $year;
      $rcmail->output->command('plugin.calendar_replicate', $year);
    }
    else{
      $_SESSION['caldav_allfetched'] = time();
      $this->backend->purgeEvents();
      $rcmail->session->remove('caldav_truncate');
      $save['ctags'] = $this->backend->getCtags();
      if($_SESSION['user_id']){
        $rcmail->user->save_prefs($save);
      }
      $rcmail->output->show_message('calendar.successfullyreplicated', 'confirmation');
      $rcmail->output->command('plugin.calendar_replicate_done', $_SESSION['caldav_allfetched']);
    }
  }
  
  function clearCache(){
    $rcmail = rcmail::get_instance();
    $rcmail->session->remove('cal_cache');
    $rcmail->session->remove('cal_links');
    $events_table = $rcmail->config->get('db_table_events', 'events');
    $events_table_ids = $rcmail->config->get('db_sequence_events', 'events_ids');
    $rcmail->config->set('db_table_events',$rcmail->config->get('db_table_events_cache', 'events_cache'));
    $rcmail->config->set('db_sequence_events',$rcmail->config->get('db_sequence_events_cache', 'events_cache_ids'));
    $this->backend->truncateEvents(3);
    $rcmail->config->set('db_table_events',$events_table);
    $rcmail->config->set('db_sequence_events',$events_table_ids);
  }
  
  function fetchAllLayers(){
    $rcmail = rcmail::get_instance();
    if(!$btz = get_input_value('_tzname', RCUBE_INPUT_GET)){
      $btz = $_SESSION['tzname'];
    }
    $feeds = $rcmail->config->get('feeds_subscribed', false);
    if(!is_array($feeds)){
      $feeds = array_merge($rcmail->config->get('calendarfeeds', array()), $rcmail->config->get('public_calendarfeeds', array()));
    }
    $start = get_input_value('_start', RCUBE_INPUT_GPC);
    $end = get_input_value('_end', RCUBE_INPUT_GPC);
    $rc_layers = array();
    $comp = md5(
      serialize($rcmail->config->get('public_categories')) .
      serialize($rcmail->config->get('public_calendarfeeds'))
    );
    if($_SESSION['cal_cache_config'] != $comp){
      $_SESSION['cal_cache'] = false;
    }
    $max_execution_time = ini_get('max_execution_time');
    if(count($feeds) > 0)
      $max_execution_time = max(5, round(($max_execution_time - 20) / count($feeds)));
    if($_SESSION['cal_cache_events'][$start.$end] && time() - $_SESSION['cal_cache_events']['ts'] < 1800){
      $this->jsonEvents($start, $end, false, false, $_SESSION['cal_links'], $_SESSION['cal_cache_events'][$start.$end]);
    }
    else{
      $this->clearCache();
      $events_table = $rcmail->config->get('db_table_events', 'events');
      $events_table_ids = $rcmail->config->get('db_sequence_events', 'events_ids');
      $rcmail->config->set('db_table_events',$rcmail->config->get('db_table_events_cache', 'events_cache'));
      $rcmail->config->set('db_sequence_events',$rcmail->config->get('db_sequence_events_cache', 'events_cache_ids'));
      $links = array();
      foreach($feeds as $url => $className){
        $className = explode('|', $className);
        $className = $className[0];
        $feedurl = $url;
        preg_match('/rc\/[0-9]+\/[0-9a-z]+/', $feedurl, $matches);
        if($matches[0]){
          $temparr = explode('/', $matches[0]);
          $feedurl = str_replace($matches[0], '', $feedurl);
          $feedurl .= 'index.php?_task=dummy&_action=plugin.calendar_showlayer&_userid=' . $temparr[1] . '&_ct=' . $temparr[2];
        }
        $arr = parse_url($feedurl);
        if($arr['path'] == './'){
          if($_SERVER['HTTPS'])
            $https = 's';
          else
            $https = '';
          $feedurl = 'http' . $https . "://" . $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'] . substr($feedurl,1);
          $arr = parse_url($feedurl);
        }
        $con = '?';
        if(strstr($feedurl,'?'))
          $con = '&';
        if(stripos($arr['query'],'plugin.calendar_showlayer')){ // Roundcube calendar
          $className = $feeds[$url];
          if(strtolower($arr['host']) == strtolower($_SERVER['HTTP_HOST'])){
            if(strpos($feedurl, '&_userid=' . $rcmail->user->ID . '&')){
              continue;
            }
            $feedurl = $feedurl . $con . "start=$start&end=$end&_className=$className&_tz=" . $this->getClientTimezoneName($rcmail->config->get('timezone', 'auto')) . "&_btz=$btz&_from=".$rcmail->user->ID;
          }
          else{
            $feedurl = $feedurl . $con . "_tz=" . $this->getClientTimezoneName($rcmail->config->get('timezone', 'auto')) . "&_btz=$btz&_ics=1&_client=1";
            $source = 'ics';
          }
        }
        else if(stripos($arr['host'], 'google.') && strtolower(substr($feedurl, strlen($feedurl)-4)) != '.ics'){ // Google xml calendar
          $source = 'google';
          $feedurl = preg_replace('/\/basic$/', '/full', $feedurl) . $con . 'alt=json';
        }
        else{ // default to ics
          $source = 'ics';
        }
        if(!isset($urls[$feedurl])){
          $content = $this->utils->curlRequest($feedurl, false, $max_execution_time);
          if(stripos($arr['query'],'plugin.calendar_showlayer') && strtolower($arr['host']) == strtolower($_SERVER['HTTP_HOST'])){
            $rc_layer = json_decode($content, true);
            $urls[$feedurl] = true;
            if(is_array($rc_layer)){
              $rc_layers = array_merge($rc_layers, $rc_layer);
            }
          }
          if($source == 'ics'){
            $this->utils->importEvents($content, $userid=false, $echo=false, $idoverwrite=$url, $item=false, $client=false, $className);
          }
          if($source == 'google'){
            $events = json_decode($content, true);
            if(is_array($events)){
              $arr = array();
              $stz = date_default_timezone_get();
              date_default_timezone_set('UTC');
              $content = "BEGIN:VCALENDAR\n";
              $content .= "VERSION:2.0\n";
              foreach($events as $key => $feed){
                if(is_array($feed) && is_array($feed['entry'])){
                  $i = count($feed['entry']) - 1;
                  foreach($feed['entry'] as $key1 => $entry){
                    if(isset($entry['gd$recurrence']) && isset($entry['gd$recurrence']['$t'])){
                      $recur = $entry['gd$recurrence']['$t'];
                      $temparr = explode('BEGIN:VTIMEZONE', $recur);
                      $content .= "BEGIN:VEVENT\n";
                      if(!empty($entry['gCal$uid']['value']))
                        $content .= "UID:" . $entry['gCal$uid']['value'] . "\n";
                      if(!empty($entry['title']['$t']))
                        $content .= "SUMMARY:" . $entry['title']['$t'] . "\n";
                      if(!empty($entry['gd$where'][0]['valueString']))
                        $content .= "LOCATION:" . $entry['gd$where'][0]['valueString'] . "\n";
                      if(!empty($entry['content']['$t']))
                        $content .= "DESCRIPTION:" . $entry['content']['$t'] . "\n";
                      $content .= $temparr[0];
                      $content .= "END:VEVENT\n";
                    }
                    else if(isset($entry['gd$when'][0]['startTime'])){
                      $content .= "BEGIN:VEVENT\n";
                      if(!empty($entry['gCal$uid']['value']))
                        $content .= "UID:" . $entry['gCal$uid']['value'] . "\n";
                      if(strlen($entry['gd$when'][0]['startTime']) == 10){
                        $content .= "DTSTART;VALUE=DATE:" . str_replace('-','',$entry['gd$when'][0]['startTime']) . "\n";
                      }
                      else{
                        $content .= "DTSTART:" . date('Ymd\THis\Z',strtotime($entry['gd$when'][0]['startTime'])) . "\n";
                      }
                      if(!empty($entry['gd$when'][0]['endTime'])){
                        if(strlen($entry['gd$when'][0]['endTime']) == 10){
                          $content .= "DTEND;VALUE=DATE:" . str_replace('-','',$entry['gd$when'][0]['endTime']) . "\n";
                        }
                        else{
                          $content .= "DTEND:" . date('Ymd\THis\Z',strtotime($entry['gd$when'][0]['endTime'])) . "\n";
                        }
                      }
                      if(!empty($entry['title']['$t']))
                        $content .= "SUMMARY:" . $entry['title']['$t'] . "\n";
                      if(!empty($entry['gd$where'][0]['valueString']))
                        $content .= "LOCATION:" . $entry['gd$where'][0]['valueString'] . "\n";
                      if(!empty($entry['content']['$t']))
                        $content .= "DESCRIPTION:" . $entry['content']['$t'] . "\n";
                      $content .= "END:VEVENT\n";
                    }
                    if(isset($entry['link']) && is_array($entry['link'])){
                      foreach($entry['link'] as $key => $link){
                        if(isset($entry['gCal$uid']['value']) && isset($link['type']) && $link['type'] == 'text/html'){
                          $links[$entry['gCal$uid']['value']] = $link['href'];
                        }
                      }
                    }
                  }
                  $content .= "END:VCALENDAR\n";
                  $this->utils->importEvents($content,$userid=false,$echo=false,$idoverwrite=$url,$item=false,$client=false,$className);
                }
              }
              date_default_timezone_set($stz);
            }
          }
        }
      }
      $rcmail->config->set('db_table_events',$events_table);
      $rcmail->config->set('db_sequence_events',$events_table_ids);
    }
    $_SESSION['cal_cache'] = true;
    $_SESSION['cal_cache_events'][$start.$end] = $rc_layers;
    $_SESSION['cal_cache_events']['ts'] = time();
    $_SESSION['cal_links'] = $links;
    $_SESSION['cal_cache_config'] = md5(
      serialize($rcmail->config->get('public_categories')) .
      serialize($rcmail->config->get('public_calendarfeeds'))
    );
    $this->backend->removeDuplicates($rcmail->config->get('db_table_events_cache'));
    $this->jsonEvents($start, $end, false, false, $links, $rc_layers);
  }
 
  function showLayer() {
    $rcmail = rcmail::get_instance();
    if($rcmail->action != "plugin.calendar_showlayer")
      return;
    $userid = $rcmail->user->data['user_id'];
    $caluserid = get_input_value('_userid', RCUBE_INPUT_GPC);
    $arr = $this->getUser($caluserid);
    $client = get_input_value('_client', RCUBE_INPUT_GPC);
    $preferences = unserialize($arr['preferences']);
    $caltokenreadonly = $preferences['caltokenreadonly'];
    $token = get_input_value('_ct', RCUBE_INPUT_GPC);
    if($token == $caltokenreadonly && $token != ""){
      $root = $this->getURL();
      $url = $root.'?'.$_SERVER['QUERY_STRING'];
      $temparr = explode("&start=",$url);
      $url = $temparr[0];
      $className = get_input_value('_className', RCUBE_INPUT_GPC);
      if(!$className){
        $feeds = array_merge((array)$rcmail->config->get('public_calendarfeeds',array()),(array)$rcmail->config->get('calendarfeeds',array()));
        $className = $feeds[$url];
      }
      $temp = explode('|',$className);
      $className = $temp[0];
      if($temp[1] && strtolower($temp[1]) == 'cache')
        $include_cache = true;
      if($temp[2] && strtolower($temp[2]) == 'inherit')
        $className = false;
      $rcmail->user->ID = $caluserid;
      $start = get_input_value('start', RCUBE_INPUT_GPC);
      if(!$start){
        $start = get_input_value('_start', RCUBE_INPUT_GPC);
        if($start && !is_numeric($start)){
          $start = strtotime($start);
        }
      }
      if(!$start)
        $start = time() - 86400 * 366;
      if($client)
        $start = 0;
      $end = get_input_value('end', RCUBE_INPUT_GPC);
      if(!$end){
        $end = get_input_value('_end', RCUBE_INPUT_GPC);
        if($end && !is_numeric($end)){
          $end = strtotime($end);
        }
      }
      if(!$end)
        $end = time() + 86400 * 366;
      if($client)
        $end = strtotime(CALEOT);
      $this->backend->replicateEvents($start, $end);
      $events = $this->utils->arrayEvents($start, $end, $className, false, false, false, false, $client);
      if($include_cache){
        $events_table = $rcmail->config->get('db_table_events', 'events');
        $events_table_ids = $rcmail->config->get('db_sequence_events', 'events_ids');
        $rcmail->config->set('db_table_events',$rcmail->config->get('db_table_events_cache', 'events_cache'));
        $rcmail->config->set('db_sequence_events',$rcmail->config->get('db_sequence_events_cache', 'events_cache_ids'));
        $layer = $this->utils->arrayEvents($start, $end, $className, false, false, false, false, $client);
        $rcmail->config->set('db_table_events',$events_table);
        $rcmail->config->set('db_sequence_events',$events_table_ids);
        $events = array_merge($events,$layer);
      }
      foreach($events as $key => $event){
        $events[$key]['userid'] = $caluserid;
        $events[$key]['caltoken'] = $token;
      }
      $rcmail->user->ID = $userid;
      if($temparr[1]){
        send_nocacheing_headers();
        header('Content-Type: text/plain; charset=' . $rcmail->output->get_charset());
        echo json_encode($events);
      }
      else{
        if(get_input_value('_ics', RCUBE_INPUT_GPC)){
          $this->generateICS($events);
        }
        else{
          $this->generateFeeds($events);
        }
      }
    }
    else{
      if(get_input_value('_ics', RCUBE_INPUT_GPC)){
        $ical = "BEGIN:VCALENDAR\n";
        $ical .= "VERSION:2.0\n";
        $ical .= "PRODID:-//" . $rcmail->config->get('product_name') . "//NONSGML Calendar//EN\n";
        $ical .= "END:VCALENDAR";
        echo $ical;
      }
      else{
        echo json_encode(array());
      }
    }
    if(!$_SESSION['user_id']){
      $rcmail->session->destroy(session_id());
    }
    exit;
  }

  function generateICS($events) {
    $rcmail = rcmail::get_instance();
    if(is_array($events) && $rcmail->user->ID = get_input_value('_userid', RCUBE_INPUT_GPC)){
      foreach($events as $key => $event){
        $events[$key]['start'] = $event['start_unix'];
        $events[$key]['end'] = $event['end_unix'];
      }
      $ical = $this->utils->exportEvents(0, strtotime(CALEOT), $events);
    }
    else{
      $ical = "BEGIN:VCALENDAR\n";
      $ical .= "VERSION:2.0\n";
      $ical .= "PRODID:-//" . $rcmail->config->get('product_name') . "//NONSGML Calendar//EN\n";
      $ical .= "END:VCALENDAR";
    }
    if(@ob_get_level() > 0){
      @ob_end_clean();
    }
    
    @ob_start();
    header("Content-Type: text/calendar");
    header("Content-Disposition: inline; filename=calendar.ics");
    header('Content-Length: ' . (string) strlen($ical));
    header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
    header("Expires: " . gmdate("D, d M Y H:i:s") ." GMT");  // Date in past
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") ." GMT");
    echo $ical;
    @ob_end_flush();
    exit;
  }

  function generateFeeds($events){
    $rcmail = rcmail::get_instance();
    $webmail_url = $this->getURL();
    
    $arr = $this->getUser($caluserid);
    
    if(@ob_get_level() > 0){
      @ob_end_clean();
    }
    
    @ob_start();
    header('Content-type: text/xml');
    header("Expires: " . gmdate("D, d M Y H:i:s") ." GMT");  // Date in past
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
    header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1

    $head = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"
xmlns:dc="http://purl.org/dc/elements/1.1/"
xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
xmlns:admin="http://webns.net/mvcb/"
xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
xmlns:content="http://purl.org/rss/1.0/modules/content/">
<channel>
  <pubDate>'.date('r').'</pubDate>
  <lastBuildDate>'.date('r').'</lastBuildDate>
  <link>' . $this->rss_encode($webmail_url . '?_task=dummy&_action=plugin.calendar&_date=' . time()) . '</link>
  <title>' . $this->rss_encode($rcmail->config->get('product_name')) . '</title>
  <description>' . $this->rss_encode(ucwords($this->gettext('calendar'))) . '</description>
  <generator>' . $this->rss_encode($rcmail->config->get('useragent')) . '</generator>';

    $footer = "\r\n</channel>\r\n</rss>";

    echo trim($head);
    foreach($events as $key => $event){
      $item = "\r\n" . '  <item>
    <title>' . $this->rss_encode($event['title']) . '</title>
    <description><![CDATA['."\n".nl2br($this->rss_encode($event['description']))."\n".']]>
    </description>
    <link>' . $this->rss_encode($webmail_url . '?_task=dummy&_action=plugin.calendar&_date=' . $event['start']) . '</link>
    <author>' . $this->rss_encode($username).'</author>
    <pubDate>'.date('r', $event['start']).'</pubDate>
  </item>';

      echo $item;
    }
    echo $footer;
    @ob_end_flush();
    exit;
  }
 /**********************************************************
  *
  * HTTP Authentication to access calendar by external links
  *
  **********************************************************/

  function check_auth($args)
  {
    $rcmail = rcmail::get_instance();
    if(!$rcmail->user->data['user_id'] && $rcmail->action == 'plugin.calendar'){
      $args['action'] = 'login';
      $this->http_auth();
    }
    return $args;
  }

  function http_auth(){

    if(!empty($_POST['_user'])){
      $_SERVER['PHP_AUTH_USER'] = trim($_POST['_user']);
      $_SERVER['PHP_AUTH_PW'] = trim($_POST['_pass']);
    }
  
    if(!isset($_SERVER['PHP_AUTH_USER'])){  
      $this->http_unauthorized();
    }
  }
  
  function http_unauthorized(){
    $rcmail = rcmail::get_instance();
    header('WWW-Authenticate: Basic realm="' . $rcmail->config->get('useragent', 'Webmail') . '"');
    header('HTTP/1.0 401 Unauthorized');
    $js = '
    <html>
    <head>
    <title>' . $rcmail->config->get('useragent', 'Webmail') . '</title>
    <script type="text/javascript">
    <!--
      document.location.href="' . $_SERVER['PHP_SELF'] . '";
    //-->
    </script>
    </head>
    <body></body>
    </html>
    ';
    echo $js;
    exit;
  }
  
 /****************************
  *
  * Helpers
  *
  ****************************/
  
  function rss_encode($string) {
    return @htmlspecialchars($string,ENT_NOQUOTES,'UTF-8',false);
  }
  
  function q($str){
    $rcmail = rcmail::get_instance();
    return $rcmail->db->quoteIdentifier($str);
  }
  
  static public function getClientTimezoneName($ctz) {
    $rcmail = rcmail::get_instance();
    $rcmail->config->set('_timezone_value', null);
    $tz = $rcmail->config->get('timezone');
    if(is_numeric($tz)){
      $ctzname = false;
      $offset = gmdate('H:i', abs($ctz) * 3600);
      if($ctz < 0)
        $offset = '-' . $offset;
      else
        $offset = '+' . $offset;
      $tza = calendar::get_timezones();
      $tzn = $tza[$offset][1];
    }
    else if($tz === "auto"){
      $tzn = $_SESSION['tzname'];
    }
    else{
      $tzn = $tz;
    }
    return $tzn;
  }
  
  static public function getClientTimezone() {
    $rcmail = rcmail::get_instance();
    if ($rcmail->config->get('timezone') === "auto") {
      $tz = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : date('Z')/3600;
    }
    else {
      if(is_numeric($rcmail->config->get('timezone'))){
        $tz = $rcmail->config->get('timezone');
      }
      else{
        $stz = date_default_timezone_get();
        date_default_timezone_set($rcmail->config->get('timezone'));
        $tz = date('Z',time()) / 3600;
        date_default_timezone_set($stz);
      }
    }
    return $tz;
  }
  
  public function get_timezones() {
    $tza = array();
    $tza['-11:00'] = array( 'Midway Island, Samoa', 'Pacific/Pago_Pago' );
    $tza['-10:00'] = array( 'Hawaii', 'Pacific/Honolulu' );
    $tza['-09:30'] = array( 'Marquesas Islands', 'Pacific/Marquesas' );
    $tza['-09:00'] = array( 'Alaska', 'America/Anchorage' );
    $tza['-08:00'] = array( 'Pacific Time (US/Canada)', 'America/Los_Angeles' );
    $tza['-07:00'] = array( 'Mountain Time (US/Canada)', 'America/Denver' );
    $tza['-06:00'] = array( 'Central Time (US/Canada), Mexico City', 'America/Chicago' );
    $tza['-05:00'] = array( 'Eastern Time (US/Canada), Bogota, Lima', 'America/New_York' );
    $tza['-04:30'] = array( 'Caracas', 'America/Caracas' );
    $tza['-04:00'] = array( 'Atlantic Time (Canada), La Paz', 'America/Halifax' );
    $tza['-03:30'] = array( 'Nfld Time (Canada), Nfld, S. Labador', 'America/St_Johns' );
    $tza['-03:00'] = array( 'Brazil, Buenos Aires, Georgetown', 'America/Fortaleza' );
    $tza['-02:00'] = array( 'Mid-Atlantic', 'America/Noronha' );
    $tza['-01:00'] = array( 'Azores, Cape Verde Islands', 'Atlantic/Azores' );
    $tza['+00:00'] = array( '(GMT) Western Europe, London, Lisbon, Casablanca', 'Europe/London' );
    $tza['+01:00'] = array( 'Central European Time', 'Europe/Berlin' );
    $tza['+02:00'] = array( 'EET: Tallinn, Helsinki, Kaliningrad, South Africa', 'Africa/Johannesburg' );
    $tza['+03:00'] = array( 'Baghdad, Kuwait, Riyadh, Moscow, Nairobi', 'Europe/Moscow' );
    $tza['+03:30'] = array( 'Tehran', 'Asia/Tehran' );
    $tza['+04:00'] = array( 'Abu Dhabi, Muscat, Baku, Tbilisi', 'Asia/Dubai' );
    $tza['+04:30'] = array( 'Kabul', 'Asia/Kabul' );
    $tza['+05:00'] = array( 'Ekaterinburg, Islamabad, Karachi', 'Asia/Karachi' );
    $tza['+05:30'] = array( 'Chennai, Kolkata, Mumbai, New Delhi', 'Asia/Kolkata' );
    $tza['+05:45'] = array( 'Kathmandu', 'Asia/Kathmandu' );
    $tza['+06:00'] = array( 'Almaty, Dhaka, Colombo', 'Asia/Dhaka' );
    $tza['+06:30'] = array( 'Cocos Islands, Myanmar', 'Asia/Rangoon' );
    $tza['+07:00'] = array( 'Bangkok, Hanoi, Jakarta', 'Asia/Jakarta' );
    $tza['+08:00'] = array( 'Beijing, Perth, Singapore, Taipei', 'Asia/Shanghai' );
    $tza['+08:45'] = array( 'Caiguna, Eucla, Border Village', 'Australia/Eucla' );
    $tza['+09:00'] = array( 'Tokyo, Seoul, Yakutsk', 'Asia/Tokyo' );
    $tza['+09:30'] = array( 'Adelaide, Darwin', 'Australia/Adelaide' );
    $tza['+10:00'] = array( 'EAST/AEST: Sydney, Guam, Vladivostok', 'Australia/Sydney' );
    $tza['+10:30'] = array( 'New South Wales', 'Australia/Lord_Howe' );
    $tza['+11:00'] = array( 'Magadan, Solomon Islands', 'Pacific/Noumea' );
    $tza['+11:30'] = array( 'Norfolk Island', 'Pacific/Norfolk' );
    $tza['+12:00'] = array( 'Auckland, Wellington, Kamchatka', 'Pacific/Auckland' );
    $tza['+12:45'] = array( 'Chatham Islands', 'Pacific/Chatham' );
    $tza['+13:00'] = array( 'Tonga, Pheonix Islands', 'Pacific/Tongatapu' );
    $tza['+14:00'] = array( 'Kiribati', 'Pacific/Kiritimati' );  
    return $tza;
  }
  
  function datetime($args) {
    $rcmail = rcmail::get_instance();
    $args['content'] = date($rcmail->config->get("date_long"),time());
    return($args);
  }
  
  function week_start_date($wk_num, $yr, $first = 1, $format = 'Y-m-d') {
    $wk_ts  = strtotime('+' . $wk_num . ' weeks', strtotime($yr . '0101'));
    $mon_ts = strtotime('-' . date('w', $wk_ts) + $first . ' days', $wk_ts);
    return date($format, $mon_ts);
  }
  
  static public function getUser($id=null) {
    $rcmail = rcmail::get_instance();
    if(empty($id))
      return array();
    $sql_result = $rcmail->db->query("SELECT * FROM ".get_table_name('users')." WHERE user_id=?", $id);
    $sql_arr = $rcmail->db->fetch_assoc($sql_result);
    return $sql_arr;
  }
  
  function getURL() {
    $webmail_url = 'http';
    if (rcube_https_check())
      $webmail_url .= 's';
    $webmail_url .= '://'.$_SERVER['SERVER_NAME'];
    if ($_SERVER['SERVER_PORT'] != '80')
      $webmail_url .= ':'.$_SERVER['SERVER_PORT'];
    if (dirname($_SERVER['SCRIPT_NAME']) != '/')
      $webmail_url .= dirname($_SERVER['SCRIPT_NAME']) . '/';
    $webmail_url = str_replace("\\","",$webmail_url);
    return slashify($webmail_url); 
  }
  
  function is_cron_host() {
    $rcmail = rcmail::get_instance();
    return $_SERVER['REMOTE_ADDR'] == '::1' ||
           $_SERVER['REMOTE_ADDR'] == $rcmail->config->get('cron_ip','127.0.0.1') ||
           $_SERVER['REMOTE_ADDR'] == '127.0.0.1';
  }
}  

?>