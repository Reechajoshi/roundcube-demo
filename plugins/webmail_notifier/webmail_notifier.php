<?php

/**
 * Webmail Notifier
 *
 * @version 3.1.2 - 01.08.2013
 * @author Roland 'rosali' Liebl
 * @website http://myroundcube.com
 *
 **/
 
/** NOTICE
 *
 * #1- Designed for Webmail Notifier Firefox Addon 2.7.4 (http://webmailnotifier.mozdev.org)
 * #2- Due to restrictions in Webmail Notifier plugin will not work if Roundcube is running
 *     on a test environment on localhost! 
 *     ->Workaround for Windows: Fake hosts file
 *
 **/  
 
class webmail_notifier extends rcube_plugin
{
  public $task = "login|logout|mail|settings";
  private $flag = "UNSEEN";
  
  /* unified plugin properties */
  static private $plugin = 'webmail_notifier';
  static private $author = 'myroundcube@mail4us.net';
  static private $authors_comments = '<a href="http://myroundcube.com/myroundcube-plugins/webmail_notifier-plugin" target=_new>Documentation</a>';
  static private $download = 'http://myroundcube.googlecode.com';
  static private $version = '3.1.2';
  static private $date = '01-08-2013';
  static private $licence = 'GPL';
  static private $requirements = array(
    'Roundcube' => '0.8.1',
    'PHP' => '5.2.1'
  );
  static private $prefs = array('webmail_notifier_flag', 'webmail_notifier_folders');
  static private $config_dist = 'config.inc.php.dist';
  
  function init(){
    $browser = new rcube_browser();
    if($browser->mz || $browser->chrome){
      $this->add_texts('localization/');
      $rcmail = rcmail::get_instance();
      if(!in_array('global_config', $rcmail->config->get('plugins'))){
        $this->load_config();
      }
      $this->add_hook('authenticate', array($this, 'authenticate'));
      $this->register_action('plugin.webmail_notifier', array($this, 'get_unread'));
      $this->register_action('plugin.webmail_notifier_script', array($this, 'get_script'));
      $this->add_hook('preferences_list', array($this, 'prefs_table'));
      $this->add_hook('preferences_save', array($this, 'save_prefs'));
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
      'author' => self::$author,
      'comments' => self::$authors_comments,
      'licence' => self::$licence,
      'download' => self::$download,
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
  
  function authenticate($args){
    if(!empty($_POST['_webmail_notifier']) || !empty($_GET['_webmail_notifier'])){
      $_SESSION['noidletimeout'] = true;
      $args['valid'] = true;
      $args['cookiecheck'] = false;
    }
    return $args;
  }
  
  function get_script(){
    $rcmail = rcmail::get_instance();
    $providername = $rcmail->config->get('providername','Roundcube Webmail');
    if($_SERVER['HTTPS'])
      $s = "s";
    $append = '';
    if(file_exists(INSTALL_PATH . 'plugins/summary/summary.php')){
      $append = '&_action=plugin.summary';
    }
    $providerurl = unslashify("http$s://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?wmn' . $append);

    $script = file_get_contents('./plugins/webmail_notifier/scripts/roundcube.js');
    $script = str_replace('$providername$',$providername,$script);
    $script = str_replace('$providerurl$',$providerurl,$script);

    header("Expires: 0"); 
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
    header("Content-Type: application/force-download"); 
    header("Content-Description: File Transfer"); 
    header("Content-Disposition: attachment; filename=" . asciiwords($providername,true,'_') . ".js"); 
    header("Content-Transfer-Encoding: binary"); 
    
    echo $script;
    
    exit;
  }
    
  function get_unread(){

    $rcmail = rcmail::get_instance();
    $this->flag = $rcmail->config->get('webmail_notifier_flag','UNSEEN');
  
    if($rcmail->config->get('webmail_notifier_folders',2) == 1){
      // all folders
      (array)$folders = $rcmail->imap->list_mailboxes();
      (int)$count = 0;
      foreach($folders as $key => $val){
        (int)$count = $count + $rcmail->imap->count($val, strtoupper($this->flag), TRUE);
      }
    }
    else if($rcmail->config->get('webmail_notifier_folders',2) == 3){
      // INBOX and Junk
      (int)$count = $rcmail->imap->count('INBOX', strtoupper($this->flag), TRUE);
      if($rcmail->config->get('junk_mbox')){
        (int)$count = $count + $rcmail->imap->count($rcmail->config->get('junk_mbox'), strtoupper($this->flag), TRUE);
      }
    }
    else{
      // INBOX only
      (int)$count = $rcmail->imap->count('INBOX', strtoupper($this->flag), TRUE);
    }
    $out = "<b>$count</b>";
    echo $out;
    exit;
  }

  function prefs_table($args){
    if ($args['section'] == 'mailbox') {
      $rcmail = rcmail::get_instance();
      $skin_path = $this->local_skin_path();
      $field_id = 'rcmfd_webmail_notifier_addon';
      $link = 'http://xnotifier.tobwithu.com/wp/';
      $browser = new rcube_browser();
      if($browser->mz){
        $name = 'Firefox';
      }
      else if($browser->chrome){
        $name = 'Chrome';
      }
      else{
        return $args;
      }
      $args['blocks']['webmail_notifier']['name'] = Q(sprintf($this->gettext('pluginname'), $name));
      $content = '<a href="' . $link . '" target="_new" title="' . $this->gettext('download') . '"><img src="' . './plugins/webmail_notifier/' . $skin_path . '/images/download.gif" /></a>';
      $args['blocks']['webmail_notifier']['options']['webmail_notifier_link'] = array( 
            'title' => html::label($field_id, Q(sprintf($this->gettext('webmailnotifieraddon'), $name))), 
            'content' => $content
      );
      $field_id = 'rcmfd_webmail_notifier_script';
      $content = '<a href="./?_task=settings&_action=plugin.webmail_notifier_script&_time=' . time() . '" target="_self" title="' . $this->gettext('download') . '"><img src="' . './plugins/webmail_notifier/' . $skin_path . '/images/download.gif" /></a>';
      $content .= '&nbsp;<a href="http://myroundcube.com/myroundcube-plugins/webmail_notifier-plugin" target=_new>' . $this->gettext('tutorial') . '</a>';
      $args['blocks']['webmail_notifier']['options']['webmail_notifier_script'] = array( 
            'title' => html::label($field_id, Q(sprintf($this->gettext('webmailnotifierscript'), $name))), 
            'content' => $content
      );
      $content = "";
      $field_id = 'rcmfd_webmail_notifier_flag';
      $select = new html_select(array('name' => '_webmail_notifier_flag', 'id' => $field_id));
      $select->add($this->gettext('unseen'),'UNSEEN');
      $select->add($this->gettext('recent'),'RECENT');        
      $content .= $select->show($rcmail->config->get('webmail_notifier_flag','UNSEEN'));
      $args['blocks']['webmail_notifier']['options']['webmail_notifier_flag'] = array( 
            'title' => html::label($field_id, Q($this->gettext('webmailnotifierflag'))), 
            'content' => $content
      );
      $content = "";    
      $field_id = 'rcmfd_webmail_notifier_folders';
      $select = new html_select(array('name' => '_webmail_notifier_folders', 'id' => $field_id));
      $select->add($this->gettext('all'),1);
      $select->add($this->gettext('inbox'),2);
      if($rcmail->config->get('junk_mbox'))
        $select->add($this->gettext('inbox') . " / " . $this->gettext('junk'),3);
      $content .= $select->show($rcmail->config->get('webmail_notifier_folders',2));
      $args['blocks']['webmail_notifier']['options']['webmail_notifier_folders'] = array( 
            'title' => html::label($field_id, Q($this->gettext('webmailnotifierfolders'))), 
            'content' => $content
      );
    }
    return $args;

  }

  function save_prefs($args){
    if($args['section'] == 'mailbox'){
      $args['prefs']['webmail_notifier_flag'] = get_input_value('_webmail_notifier_flag', RCUBE_INPUT_POST);
      $args['prefs']['webmail_notifier_folders'] = get_input_value('_webmail_notifier_folders', RCUBE_INPUT_POST);
      return $args;
    }
  }
}
?>