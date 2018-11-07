<?php
# 
# This file is part of MyRoundcube "db_version" plugin.
# 
# This file is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
# 
# Copyright (c) 2014 Roland 'Rosali' Liebl
# dev-team [at] myroundcube [dot] com
# http://myroundcube.com
# 

class db_version extends rcube_plugin{

  static private $done = false;
  static private $map = array('mysql' => 'MySQL', 'pgsql' => 'PostGreSQL', 'sqlite' => 'Sqlite v.3', 'mssql' => 'MSSQL');
  
  /* unified plugin properties */
  static private $plugin = 'db_version';
  static private $author = 'myroundcube@mail4us.net';
  static private $authors_comments = '<a href="http://myroundcube.com/myroundcube-plugins/helper-plugin?db_version" target="_blank">Documentation</a>';
  static private $download = 'http://myroundcube.googlecode.com';
  static private $version = '3.1.8';
  static private $date = '08-12-2014';
  static private $licence = 'All Rights reserved';
  static private $requirements = array(
    'Roundcube' => '1.0',
    'PHP' => '5.3',
    'required_plugins' => array(
      'codemirror_ui' => 'require_plugin',
    ),
  );
  static private $prefs = array(
  );
  static private $config_dist = null;
  static private $db_map = array(
    'sqlite'     => 'sqlite',
    'sqlite2'    => 'sqlite',
    'sqlite3'    => 'sqlite',
    'sybase'     => 'mssql',
    'dblib'      => 'mssql',
    'sqlsrv'     => 'mssql',
    'mssql'      => 'mssql',
    'mysql'      => 'mysql',
    'mysqli'     => 'mysql',
    'pgsql'      => 'pgsql',
    'postgresql' => 'pgsql',
  );
  static private $default_tables = array(
    'users',
    'attachments',
    'cache',
    'cache_index',
    'cache_thread',
    'cache_tables',
    'cache_messages',
    'cache_shared',
    'contactgroupmembers',
    'contactgroups',
    'contacts',
    'dictionary',
    'identities',
    'searches',
    'system'
  );
  static private $f;
  
  function init(){
    self::$f = $this;
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
  
  function form($p){
    return self::$out;
  }
  
  static public function exec($plugin, $tables, $dbversion){
    $rcmail = rcmail::get_instance();
    /*
    if($rcmail->action == 'plugin.plugin_manager_update' || $rcmail->action == 'about' || $rcmail->action == 'alive' || $rcmail->action == 'refresh'){
      return true;
    }
    */
    if($rcmail->action == 'about'){ // Do not force database adjustments on about request
      return true;
    }
    if(get_input_value('_load_pm_settings', RCUBE_INPUT_GET)){
      if($plugin = get_input_value('_plugin', RCUBE_INPUT_GET)){
        $append = '&_plugin=' . $plugin;
      }
      else{
        $append = '';
      }
      if(!self::$done){
        self::$done = true;
        if($rcmail->config->get('skin', 'classic') == 'classic'){
          $frame = 'prefs-frame';
        }
        else{
          $frame = 'preferences-frame';
        }
        $rcmail->output->add_script('$("#'. $frame . '").attr("src", "./?_task=settings&_action=edit-prefs&_section=plugin_manager_settings&_framed=1' . $append . '");', 'docready');
      }
      return false;
    }
    $return = false;
    $dbversion = implode('|', $dbversion);
    $query = 'SELECT * FROM ' . get_table_name('system') . ' WHERE ' . $rcmail->db->quoteIdentifier('name') . '=?';
    $res = $rcmail->db->limitquery($query, 0, 1, 'myrc_' . $plugin);
    $version = $rcmail->db->fetch_assoc($res);
    if($error = $rcmail->db->is_error()){
      write_log('db_version', $error);
      return false;
    }
    if($version['value'] == $dbversion){
      $return = true;
    }
    else if($plugin == 'plugin_manager' || $rcmail->config->get('plugin_manager_hash')){
      $hash = $rcmail->config->get('plugin_manager_hash');
      if(($plugin == 'plugin_manager' && !is_array($version)) || ($rcmail->task != 'logout' && file_exists(INSTALL_PATH . $hash . '.myrc'))){
        if(get_input_value('_framed', RCUBE_INPUT_GPC)){
          return false;
        }
        $type = current(explode(':', $rcmail->config->get('db_dsnw'), 2));
        $type = self::$db_map[$type];
        if(!$type){
          if(!file_exists(slashify($rcmail->config->get('log_dir', 'logs/')) . 'dbtypefailure')){
            write_log('dbtypefailure', 'MyRoundcube Fatal Error');
            write_log('dbtypefailure', '-----------------------');
            write_log('dbtypefailure', 'Failed to detect database type.');
            write_log('dbtypefailure', 'Please contact MyRoundcube Dev Team (dev-team@myroundcube.com) and mail the following string (don\'t miss to mask password and to clear your Browser cache):');
            write_log('dbtypefailure', ' ');
            write_log('dbtypefailure', $rcmail->config->get('db_dsnw'));
          }
          $content = file_get_contents(slashify($rcmail->config->get('log_dir', 'logs/')) . 'dbtypefailure');
          $content = explode("\n", $content);
          $out = '';
          foreach($content as $nb => $line){
            $line = explode("]: ", $line);
            $out .= $line[1] . "\n";
          }
          $rcmail->output->nocacheing_headers();
          die('<html><head><title>MyRoundcube Fatal Error</title></head><body><pre>' . $out . '</pre></body></html>');
        }
        if(get_input_value('_remote', RCUBE_INPUT_GPC) && get_input_value('_action', RCUBE_INPUT_GPC) != 'plugin.plugin_manager_update_notifier' && !$_SESSION['db_version_lock']){
          $db_version = explode('|', $dbversion);
          $redirect = true;
          $reward = false;
          $missing = '';
          foreach($db_version as $script){
            if(!file_exists(INSTALL_PATH . 'plugins/' . $plugin . '/SQL/' . $type . '.' . $script . '.sql') && !file_exists(INSTALL_PATH . 'plugins/' . $plugin . '/sql/' . $type . '.' . $script . '.sql')){
              $rcmail->output->show_message('Plugin "' . $plugin . '": Can\'t load plugin. ' . self::$map[strtolower($type)] . ' database scripts are incomplete.', 'error');
              $redirect = false;
              $missing .= $plugin . ': plugins/' . $plugin . '/SQL/' . $type . '.' . $script . '.sql does not exist!';
              $reward = '--> REWARD MyRC$ 5: Create missing scripts and mail it to dev-team@myroundcube.com (include your customer ID)!';
            }
          }
          if($reward && !$_SESSION['db_version_missing_logged_' . $plugin]){
            $_SESSION['db_version_missing_logged_' . $plugin] = true;
            write_log('errors', $missing);
            write_log('errors', $reward);
          }
          if($redirect){
            echo json_encode(array('exec' => 'document.location.href="./"')); 
            exit;
          }
        }
        $return = db_version::db_versioning($plugin, array_merge(array('system'), $tables));
      }
    }
    else if($plugin == 'db_config'){
      $return = true;
    }
    return $return;
  }
  
  static public function test_permissions($type, $db){
    if($type == 'sqlite'){
      return true;
    }
    $sql = @file_get_contents(INSTALL_PATH . 'plugins/db_version/SQL/' . $type . '.initial.sql');
    if(!$sql){
      $sql = @file_get_contents(INSTALL_PATH . 'plugins/db_version/sql/' . $type . '.initial.sql');
    }
    if($sql){
      $sql = trim($sql) . "\r\n";
      $sql = self::fix_table_names($sql, array('myrc'));
      $sql = explode(';', $sql);
      foreach($sql as $line){
        if(trim($line)){
          if(!$db->query($line)){
            $command = explode(' ', $line);
            return $command[0];
          }
        }
      }
      return true;
    }
    else{
      return $type;
    }
  }
  
  static public function db_versioning($plugin, $tables){
    $rcmail = rcmail::get_instance();
    /* PHP 5.2.x workaround for $plugin::about() */
    $class = new $plugin(false);
    $db_version = $class->about(array('db_version'));
    $db_version = $db_version['db_version'];
    if(is_array($db_version)){
      $database = parse_url($rcmail->config->get('db_dsnw'));
      if(!$database){
        $database = parse_url(end(explode(':', $rcmail->config->get('db_dsnw'), 2)));
        $database['scheme'] = current(explode(':', $rcmail->config->get('db_dsnw'), 2));
      }
      $type = self::$db_map[$database['scheme']];
      $sql = 'SELECT * FROM ' . get_table_name('system') . ' WHERE ' . $rcmail->db->quote_identifier('name') . '=?';
      $res = $rcmail->db->limitquery($sql, 0, 1, 'myrc_' . $plugin);
      $system = $rcmail->db->fetch_assoc($res);
      if($system){
        $versions = explode('|', $system['value']);
      }
      if(implode('|', $db_version) == $system['value']){
        return true;
      }
      if(count($_POST) == 0){
        $sql = '';
        foreach($db_version as $script){
          if(!is_array($versions) || !in_array($script, $versions)){
            if(file_exists(INSTALL_PATH . 'plugins/' . $plugin . '/SQL/' . $type . '.' . $script . '.sql')){
              $sql .= trim(file_get_contents(INSTALL_PATH . 'plugins/' . $plugin . '/SQL/' . $type . '.' . $script . '.sql')) . "\r\n";
            }
            else if(file_exists(INSTALL_PATH . 'plugins/' . $plugin . '/sql/' . $type . '.' . $script . '.sql')){
              $sql .= trim(file_get_contents(INSTALL_PATH . 'plugins/' . $plugin . '/sql/' . $type . '.' . $script . '.sql')) . "\r\n";
            }
            else{
              return false;
            }
          }
        }
        if($sql){
          $disable = '';
          if($plugin != 'plugin_manager'){
            $res = $rcmail->db->limitquery('SELECT * FROM ' . get_table_name('plugin_manager') . ' WHERE ' . $rcmail->db->quote_identifier('conf') . '=?', 0, 1, '_plugin_manager_file_based_config');
            $fb = $rcmail->db->fetch_assoc($res);
            if($fb['value'] == 0){
              $disable = '&nbsp;' . html::tag('input', array('onclick' => 'document.location.href="./?_task=settings&_load_pm_settings=1&_plugin=' . $plugin . '"', 'type' => 'button', 'class' => 'button', 'value' => 'Disable Plugin'));
            }
          }
          if($database['user']){
            $dsnw = $database['scheme'] . '://' . $database['user'] . ':***masked***@' . $database['host'] . $database['path'];
          }
          else{
            $dsnw = $rcmail->config->get('db_dsnw');
          }
          $v = explode('-', RCMAIL_VERSION);
          $v = trim($v[0]);
          if(version_compare($v, '0.9', '>=')){
            $out = 
              html::tag('div', array('style' => 'font-size: 12px; text-align: justify; position: absolute; margin-left: auto; left: 50%; margin-left: -400px; width: 800px;'),
                html::tag('h3', null, 'Plugin Manager database versioning: Plugin "' . $plugin . '"') .
                'Plugin Manager has detected required missing adjustments in your database. The following commands will be executed against your database ' .
                html::tag('b', null, $dsnw . ':') . html::tag('br') . html::tag('br') . 
                html::tag('textarea', array('readonly' => true, 'rows' => '18', 'cols' => '95', 'id' => 'code'), self::fix_table_names($sql, $tables)) .
                html::tag('table', null,
                  html::tag('tr', null, html::tag('td', null, html::tag('input', array('type' => 'radio', 'name' => '_dbadjust_agreed', 'checked' => true, 'value' => 1, 'onclick' => '$("#submitbutton").show();$("#donebox").hide()')) . 
                    html::tag('td', null, 'I have a recent database backup. I understand the code and I agree to execute it. '))) .
                  html::tag('tr', null, html::tag('td', null, html::tag('input', array('type' => 'radio', 'name' => '_dbadjust_agreed', 'value' => 0, 'onclick' => '$("#submitbutton").hide();$("#donebox").show()')) . 
                    html::tag('td', null, 'I will take care of the necessary ' .
                    html::tag('a', array('href' => 'http://myroundcube.com/myroundcube-plugins/faqs/myroundcube-plugins-database-versioning-support', 'target' => '_new'), 'database adjustments') . ' by myself. ' .
                    'I\'m aware that MyRoundcube "' . $plugin . '" plugin does not work without these database adjustments. ')))
                ) .
                html::tag('center', null, html::tag('input', array('id' => 'submitbutton', 'type' => 'submit', 'value' => 'Submit')) . $disable) . html::tag('br') .
                html::tag('center', array('style' => 'display: none;', 'id' => 'donebox'), html::tag('input', array('type' => 'checkbox', 'onclick' => '$("#submitbutton").show();$("#donebox").hide();$(this).prop("checked", false)')) . html::tag('span', null, '&nbsp;' . 
                  html::tag('b', null, 'I have unregistered this plugin or I have already applied database adjustments manually.')))
            );
            $hidden = new html_hiddenfield(array('name' => '_token', 'value' => $rcmail->get_request_token()));
            $out .= "\n" . $hidden->show();
            $out .= html::tag('script', array('type' => 'text/javascript'), '$("#taskbar.topright").hide();');
            $section = '';
            if($section = get_input_value('_plugin_manager_settings_section', RCUBE_INPUT_GET)){
              $section = '&_plugin_manager_settings_section=' . $section . '&_expand=' . $plugin;
            }
            $form = html::tag('form', array('method' => 'post', 'action' => './?_task=' . $rcmail->task . $section), $out);
          }
          else{
            $out = 
              html::tag('div', array('style' => 'font-size: 12px; text-align: justify; position: absolute; margin-left: auto; left: 50%; margin-left: -400px; width: 800px;'),
                html::tag('h3', null, 'Plugin Manager database versioning: Plugin "' . $plugin . '"') .
                'Plugin Manager has detected required missing adjustments in your database. Please execute the following commands against your database ' .
                html::tag('b', null, $database['scheme'] . '://' . $database['user'] . ':***masked***@' . $database['host'] . $database['path'] . ':') . html::tag('br') . html::tag('br') . 
                html::tag('textarea', array('readonly' => true, 'rows' => '18', 'cols' => '95', 'id' => 'code'), self::fix_table_names($sql, $tables)) .
                html::tag('table', null,
                  html::tag('tr', null, html::tag('td', null,
                    html::tag('td', null, 'Please backup your database. ' .
                    'Please note, that MyRoundcube "' . $plugin . '" plugin does not work without these database adjustments.')))
                ) .
                html::tag('center', null, html::tag('input', array('id' => 'submitbutton', 'type' => 'submit', 'value' => 'Database has been backed up and commands have been executed.')))
            );
            $hidden = new html_hiddenfield(array('name' => '_token', 'value' => $rcmail->get_request_token()));
            $out .= "\n" . $hidden->show();
            $out .= html::tag('script', array('type' => 'text/javascript'), '$("#taskbar.topright").hide();');
            $section = '';
            if($section = get_input_value('_plugin_manager_settings_section', RCUBE_INPUT_GET)){
              $section = '&_plugin_manager_settings_section=' . $section . '&_expand=' . $plugin;
            }
            $form = html::tag('form', array('method' => 'post', 'action' => './?_task=' . $rcmail->task . $section), $out);
          }
          $out = str_replace('##FORM##', $form, @file_get_contents(INSTALL_PATH . 'plugins/db_version/skins/' . $rcmail->config->get('skin', 'classic') . '/sql.html'));
          if(method_exists($rcmail->output, 'just_parse')){
            send_nocacheing_headers();
            header('Content-Type: text/html; charset=' . RCMAIL_CHARSET);
            echo $rcmail->output->just_parse($out);
            exit;
          }
        }
      }
      else{
        if(get_input_value('_dbadjust_agreed', RCUBE_INPUT_POST)){
          unset($_POST);
          $rcmail->session->remove('db_version_lock');
          if($dsn = $rcmail->config->get('db_dsnw_superadmin')){
            $rcmail->db = rcube_db::factory($dsn . '?new_link=true', '', false);
            $rcmail->db = $rcmail->db->factory($dsn . '?new_link=true');
            $rcmail->db->set_debug((bool)$rcmail->config->get('sql_debug'));
            $rcmail->db->db_connect('r');
          }
          $ret = self::test_permissions($type, $rcmail->db);
          $sversions = is_array($system) ? $system['value'] : '';
          $sversions = explode('|', $sversions);
          if($ret !== true){
            $sql = '';
            foreach($db_version as $script){
              if(!in_array($script, $sversions)){
                if(file_exists(INSTALL_PATH . 'plugins/' . $plugin . '/SQL/' . $type . '.' . $script . '.sql')){
                  $sql .= self::fix_table_names(trim(file_get_contents(INSTALL_PATH . 'plugins/' . $plugin . '/SQL/' . $type . '.' . $script . '.sql')) . "\r\n", $tables);
                }
                else if(file_exists(INSTALL_PATH . 'plugins/' . $plugin . '/sql/' . $type . '.' . $script . '.sql')){
                  $sql .= self::fix_table_names(trim(file_get_contents(INSTALL_PATH . 'plugins/' . $plugin . '/sql/' . $type . '.' . $script . '.sql')) . "\r\n", $tables);
                }
              }
            }
            if($ret === $type){
              $rcmail->output->show_message('Plugin "db_version": ' . self::$map[strtolower($type)] . ' database is not supported!', 'error');
              return false;
            }
            self::html_output($sql, $ret);
          }
          foreach($db_version as $script){
            if(!in_array($script, $sversions)){
              if(file_exists(INSTALL_PATH . 'plugins/' . $plugin . '/SQL/' . $type . '.' . $script . '.sql')){
                $sql = self::fix_table_names(trim(file_get_contents(INSTALL_PATH . 'plugins/' . $plugin . '/SQL/' . $type . '.' . $script . '.sql')) . "\r\n", $tables);
                $lines = explode(';', $sql);
                foreach($lines as $line){
                  if(trim($line)){
                    $rcmail->db->query(trim($line));
                  }
                }
              }
              else if(file_exists(INSTALL_PATH . 'plugins/' . $plugin . '/sql/' . $type . '.' . $script . '.sql')){
                $sql = self::fix_table_names(trim(file_get_contents(INSTALL_PATH . 'plugins/' . $plugin . '/sql/' . $type . '.' . $script . '.sql')) . "\r\n", $tables);
                $lines = explode(';', $sql);
                foreach($lines as $line){
                  if(trim($line)){
                    $rcmail->db->query(trim($line));
                  }
                }
              }
            }
          }
          $sql = 'DELETE from ' . get_table_name('system') . ' WHERE ' . $rcmail->db->quoteIdentifier('name') . '=?';
          $rcmail->db->query($sql, 'myrc_' . $plugin);
          $sql = 'INSERT INTO ' . get_table_name('system') . ' (' . $rcmail->db->quote_identifier('name') . ', ' . $rcmail->db->quote_identifier('value') . ') VALUES(?, ?)';
          $res = $rcmail->db->query($sql, 'myrc_' . $plugin, implode('|', $db_version));
          if($rcmail->db->affected_rows($res)){
            return true;
          }
          else{
            return false;
          }
        }
      }
    }
  }
  
  static public function html_output($script, $command = false){
    $rcmail = rcmail::get_instance();
    $config_var = '$config';
    $config_file = 'config.inc.php';
    $v = current(explode('-', RCMAIL_VERSION));
    if(version_compare($v, '1.0', '<')){
      $config_var = '$rcmail_config';
      $config_file = 'db.inc.php';
    }
    $dbconfig = parse_url($rcmail->config->get('db_dsnw'));
    $out = html::tag('div', array('style' => 'font-size: 12px; text-align: justify; position: absolute; margin-left: auto; left: 50%; margin-left: -400px; width: 800px;'),
      html::tag('h3', null, sprintf('Database adjustment failed (Command: %s)', $command)) .
      html::tag('span', null, 'The user ' .
       html::tag('b', null, '"' . ($dbconfig['user'] ? $dbconfig['user'] : get_current_user()) . '"') .' does not seem to have sufficient permissions on the database ' .
       html::tag('b', null, '"' . ($dbconfig['path'] ? substr($dbconfig['path'], 1) : $rcmail->config->get('db_dsnw')) . '"') . ':' . html::tag('br') . html::tag('br') .
       ' ' . html::tag('center', null, html::tag('i', null, 'Required permissions: SELECT / INSERT / UPDATE / DELETE / CREATE / ALTER / INDEX / DROP')) . 
       html::tag('br') .
        'Please, grant the user sufficient permissions on the database or execute the following database script manually:') . html::tag('br') . html::tag('br') .
        html::tag('textarea', array('readonly' => true, 'rows' => '10', 'cols' => '95', 'id' => 'code'), self::fix_table_names($script, array('plugin_manager'))) .
        html::tag('br') . html::tag('br') .
        html::tag('div', array('style' => $display_superadmin ? 'block' : 'none'), 
          $dbconfig['user'] ?
          (
            html::tag('left', null, html::tag('b', null, 'Alternatively') . ' add a different database user with sufficient permissions to your configuration in ' . 
              html::tag('b', null, '"./config/' . $config_file . '"') . ':' . html::tag('br') . html::tag('br') .
              html::tag('textarea', array('readonly' => true, 'cols' => '95', 'rows' => '2'),
                $config_var . '[\'db_dsnw_superadmin\'] = \'' . $dbconfig['scheme'] . '://root:password@' . $dbconfig['host'] . $dbconfig['path'] . '\';')) .
              html::tag('br') . html::tag('br')
            ) : ''
          ) .
        html::tag('div', array('style' => 'display: inline; float: left'),
         html::tag('a', array('href' => 'javascript:void(0)', 'onclick' => 'document.location.href=\'./\''), 'Done')
        )
    );
    $out .= html::tag('script', array('type' => 'text/javascript'), '$("#taskbar.topright").hide();');
    $out = str_replace('##FORM##', $out, @file_get_contents(INSTALL_PATH . 'plugins/db_version/skins/' . $rcmail->config->get('skin', 'classic') . '/sql.html'));
    send_nocacheing_headers();
    header('Content-Type: text/html; charset=' . RCMAIL_CHARSET);
    echo $rcmail->output->just_parse($out);
    exit;
  }
  
  static public function fix_table_names($sql, $tables){
    $tables = array_merge(self::$default_tables, $tables);
    $DB = rcmail::get_instance()->db;
    if(is_array($tables)){
      foreach($tables as $table){
        $real_table = $DB->table_name($table);
        if($real_table != $table){
          $sql = preg_replace("/([^a-z0-9_])$table([^a-z0-9_])/i", "\\1$real_table\\2", $sql);
        }
      }
    }
    return $sql;
  }
}
?>