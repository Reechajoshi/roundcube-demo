<?php
/**
 * codemirror_ui
 *
 * @version 1.0.5 - 08.05.2014
 * @author Roland 'rosali' Liebl
 * @website http://myroundcube.com
 *
 **/

class codemirror_ui extends rcube_plugin{

  public $noajax = true;
  
  /* unified plugin properties */
  static private $plugin = 'codemirror_ui';
  static private $author = 'myroundcube@mail4us.net';
  static private $authors_comments = '<a href="http://myroundcube.com/myroundcube-plugins/helper-plugin?codemirror_ui" target="_blank">Documentation</a>';
  static private $version = '1.0.5';
  static private $date = '08-05-2014';
  static private $licence = 'GPL';
  static private $requirements = array(
    'Roundcube' => '1.0',
    'PHP' => '5.3'
  );
  
  private $rcmail;
  
  function init(){
    $this->rcmail = rcmail::get_instance();
    switch($GLOBALS['codemirror']['mode']){
      case 'PHP':
        $this->rcmail->output->add_header('<style type="text/css">.CodeMirror {height: 90%} .CodeMirror-scroll {height: 100%} </style>');
        break;
      case 'SQL':
        $this->rcmail->output->add_header('<style type="text/css">.CodeMirror {height: auto} .CodeMirror-scroll {overflow-y: hidden; overflow-x: auto;} </style>');
    }
    $this->include_stylesheet('lib/CodeMirror-2.3/lib/codemirror.css');
    $this->include_script('lib/CodeMirror-2.3/lib/codemirror.js');
    $this->include_script('lib/CodeMirror-2.3/lib/util/searchcursor.js');
    switch($GLOBALS['codemirror']['mode']){
      case 'PHP':
        $this->PHP($GLOBALS['codemirror']['elem']);
        break;
      case 'SQL':
        $this->SQL($GLOBALS['codemirror']['elem']);
    }
    $this->include_stylesheet('css/codemirror-ui.css');
    $this->include_script('js/codemirror-ui.js');
  }
  
  function PHP($elem){
    $this->include_script('lib/CodeMirror-2.3/mode/htmlmixed/htmlmixed.js');
    $this->include_script('lib/CodeMirror-2.3/mode/xml/xml.js');
    $this->include_script('lib/CodeMirror-2.3/mode/javascript/javascript.js');
    $this->include_script('lib/CodeMirror-2.3/mode/css/css.js');
    $this->include_script('lib/CodeMirror-2.3/mode/clike/clike.js');
    $this->include_script('lib/CodeMirror-2.3/mode/php/php.js');
    $this->rcmail->output->add_script('
      var textarea = document.getElementById("' . $elem . '");
      var uiOptions = {
        path : "js/",
        searchMode : "popup",
        mode: "php",
        imagePath : "plugins/codemirror_ui/images/silk",
        buttons : ' . $GLOBALS['codemirror']['buttons'] . ',
        saveCallback : ' . $GLOBALS['codemirror']['save'] . '
      }
      var codeMirrorOptions = {
        readOnly: ' . ($GLOBALS['codemirror']['readonly'] ? 'true' : 'false') . ',
        lineNumbers: true,
        matchBrackets: true,
        mode: "application/x-httpd-php",
        indentUnit: 2,
        indentWithTabs: true,
        enterMode: "keep",
        tabMode: "shift",
        tabSize: 2
      }
      var editor = new CodeMirrorUI(textarea, uiOptions, codeMirrorOptions);
    ', 'docready'
    );
  }
  
  function SQL($elem){
    $this->include_script('lib/CodeMirror-2.3/mode/htmlmixed/htmlmixed.js');
    $this->include_script('lib/CodeMirror-2.3/mode/xml/xml.js');
    $this->include_script('lib/CodeMirror-2.3/mode/javascript/javascript.js');
    $this->include_script('lib/CodeMirror-2.3/mode/css/css.js');
    $this->include_script('lib/CodeMirror-2.3/mode/clike/clike.js');
    $this->include_script('lib/CodeMirror-2.3/mode/mysql/mysql.js');
    $this->rcmail->output->add_script('
      var textarea = document.getElementById("' . $elem . '");
      var uiOptions = {
        path : "js/",
        searchMode : "popup",
        mode: "mysql",
        imagePath : "plugins/codemirror_ui/images/silk",
        buttons : ' . $GLOBALS['codemirror']['buttons'] . ',
        saveCallback : ' . $GLOBALS['codemirror']['save'] . '
      }
      var codeMirrorOptions = {
        readOnly: ' . ($GLOBALS['codemirror']['readonly'] ? 'true' : 'false') . ',
        lineNumbers: true,
        matchBrackets: true,
        mode: "text/x-mysql",
        indentUnit: 2,
        indentWithTabs: true,
        enterMode: "keep",
        tabMode: "shift",
        fixedGutter: true,
        tabSize: 2
      }
      var editor = new CodeMirrorUI(textarea, uiOptions, codeMirrorOptions);
    ', 'docready'
    );
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
    return array(
      'plugin' => self::$plugin,
      'version' => self::$version,
      'date' => self::$date,
      'author' => self::$author,
      'comments' => self::$authors_comments,
      'licence' => self::$licence,
      'requirements' => $requirements,
    );
  }
}

?>