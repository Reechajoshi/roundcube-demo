<?php
# 
# This file is part of Roundcube "plugin_manager" plugin.
# 
# Your are not allowed to distribute this file or parts of it.
# 
# This file is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
# 
# Copyright (c) 2012 - 2014 Roland 'Rosali' Liebl - all rights reserved.
# dev-team [at] myroundcube [dot] net
# http://myroundcube.com
# 
class plugin_manager extends rcube_plugin
{
    private $debug = false;
    static private $version = '26.0.10';
    static private $date = '11-11-2014';
    private $core_patches = array('version' => 'Roundcube 1.0.3', 'count' => 0, 'date' => '2014-10-13 09:00:00', 'comments' => 'Download required core files PATCHES for Roundcube 1.0.3 from <a href="https://mirror.myroundcube.com/dl/1.0.3/roundcube.zip">here</a>. Unzip the package over your Roundcube installation. For DETAILS about the additions these files provide, download a revision copy (*.PATCH) from <a href="https://mirror.myroundcube.com/dl/1.0.3/roundcube.patch" target="_blank">here</a>.', 'PHP' => '5.3', 'lc' => false);
    var $allowed_prefs = array('plugin_manager_hmail');
    private $rcmail;
    private $template;
    private $admins = array();
    private $noremote = false;
    private $host;
    private $domain;
    private $config;
    private $lables;
    private $plugins;
    private $mirror = 'http://mirror.myroundcube.com';
    private $svn = 'http://dev.myroundcube.com';
    private $stable = '0.9.5';
    private $dev = '1.0.3';
    private $rcurl = 'http://roundcube.net';
    private $guide = 'http://myroundcube.com/myroundcube-plugins/plugins-installation';
    private $from = 'dev-team@myroundcube.com';
    private $vlength = 5;
    private $billingurl = 'http://billing.myroundcube.com/?_task=billing&_action=buycredits';
    private $dlurl = 'https://billing.myroundcube.com/pm/';
    private $delay = 8000;
    private $out;
    private $defaults = array();
    private $unauth = array();
    private $thirdparty = array();
    private $use_ssl = false;
    private $use_hmail = false;
    private $maintenance_mode = false;
    private $compress_html = false;
    private $file_based_config = false;
    private $config_permissions = false;
    private $jqueryui = array('jquery-ui-1.8.18.custom.css', 'jquery-ui-1.9.1.custom.css');
    private $skip = array('plugin_manager', 'plugin_server', 'companyaddressbook_plus', 'calendar_plus', 'carddav_plus', 'codemirror_ui', 'db_version', 'global_config', 'http_auth', 'http_request', 'jqueryui', 'qtip', 'filesystem_attachments', 'fancybox', 'package_xml', 'sabredav_migrate', 'savepassword', 'timepicker', 'jsdialogs', 'jappix4roundcube', 'db_config', 'tabbed');
    private $noselect = array('plugin_manager', 'plugin_server', 'companyaddressbook_plus', 'calendar_plus', 'carddav_plus', 'codemirror_ui', 'global_config', 'http_auth', 'http_request', 'jqueryui', 'qtip', 'filesystem_attachments', 'fancybox', 'package_xml', 'sabredav_migrate', 'savepassword', 'timepicker');
    private $rctasks = array('settings', 'mail', 'addressbook', 'settings', 'dummy', 'logout', 'login');
    private $db_map = array('sqlite' => 'sqlite', 'sqlite2' => 'sqlite', 'sqlite3' => 'sqlite', 'sybase' => 'mssql', 'dblib' => 'mssql', 'sqlsrv' => 'mssql', 'mssql' => 'mssql', 'mysql' => 'mysql', 'mysqli' => 'mysql', 'pgsql' => 'pgsql', 'postgresql' => 'pgsql');
    private $nodocs = array('newuser', 'persistent_login', 'summary', 'markasjunk2', 'newmail_notifier', 'zipdownload', 'plaxo_contacts', 'hmail_spamfilter', 'nabble', 'rss_feeds', 'tinymce', 'wrapper');
    private $docsmap = array();
    static private $plugin = 'plugin_manager';
    static private $author = 'myroundcube@mail4us.net';
    static private $authors_comments = '<a onclick="alert(\'Roundcube Core Patches are recommended in order to show all installed plugins in <i>About</i> popup.\')" href="#pmu_Roundcube_Core_Patches"><font color="red">IMPORTANT</font></a><br /><a href="http://trac.roundcube.net/ticket/1488871" target="_blank">Related Ticket</a><br /><a href="http://myroundcube.com/myroundcube-plugins/plugin-manager" target="_blank">Documentation</a>';
    static private $download = 'http://myroundcube.com';
    static private $licence = 'All Rights reserved';
    static private $requirements = array('Roundcube' => '1.0', 'PHP' => '5.3', 'extra' => 'PHP cURL and OpenSSL are recommended', 'required_plugins' => array('qtip' => 'require_plugin', 'jqueryui' => 'require_plugin', 'settings' => 'require_plugin', 'http_request' => 'require_plugin', 'codemirror_ui' => 'require_plugin', 'db_version' => 'require_plugin'));
    static private $prefs = array('plugin_manager_active', 'plugin_manager_hash');
    static private $tables = array('plugin_manager');
    static private $db_version = array('initial', '20131209');
    function init()
    {
        if (version_compare(PHP_VERSION, '5.4.0', '>=') && strpos($_SERVER['HTTP_HOST'], 'myroundcube.com') === false) {
            if ((E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING & ~E_USER_WARNING) != filter_var(ini_get('error_reporting'), FILTER_VALIDATE_INT)) {
                @ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING & ~E_USER_WARNING);
                if ((E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING & ~E_USER_WARNING) != filter_var(ini_get('error_reporting'), FILTER_VALIDATE_INT)) {
                    write_log('errors', 'MyRoundcube Plugin Manager: Please set error_reporting to E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING & ~E_USER_WARNING (php.ini or .htaccess).');
                }
            }
        }
        if (is_dir(INSTALL_PATH . 'plugins/db_version')) {
            $this->require_plugin('db_version');
            if (!$load = db_version::exec(self::$plugin, self::$tables, self::$db_version)) {
                return;
            }
        }
        $this->rcmail = rcmail::get_instance();
        $required     = self::$requirements['required_plugins'];
        foreach ($required as $plugin => $load) {
            if (!file_exists(INSTALL_PATH . 'plugins/' . $plugin . '/' . $plugin . '.php')) {
                $this->out = html::tag('div', array(
                    'style' => 'font-size: 12px; text-align: justify; position: absolute; margin-left: auto; left: 50%; margin-left: -200px; width: 400px;'
                ), html::tag('h3', null, 'Missing plugin: "' . html::tag('a', array(
                    'href' => 'http://myroundcube.com/myroundcube-plugins#' . $plugin,
                    'target' => '_blank'
                ), $plugin) . '"<br />' . html::tag('span', array(
                    'style' => 'font-weight: normal; font-size: 9px'
                ), '(' . INSTALL_PATH . $plugin . ')')) . html::tag('span', null, 'Please ' . html::tag('a', array(
                    'href' => $this->svn . '/?_action=plugin.plugin_server_get_pm'
                ), 'download') . ' Plugin Manager package again and upload the entire package to your Roundcube\'s plugin folder.') . html::tag('br') . html::tag('br') . html::tag('div', array(
                    'style' => 'display: inline; float: left'
                ), html::tag('a', array(
                    'href' => 'javascript:void(0)',
                    'onclick' => 'document.location.href=\'./\''
                ), $this->gettext('done'))));
                $this->register_handler('plugin.body', array(
                    $this,
                    'sqlerror'
                ));
                $this->rcmail->output->send('plugin');
            }
        }
        $rcversion = current(explode('-', RCMAIL_VERSION));
        $pmversion = self::$requirements['Roundcube'];
        if (version_compare($rcversion, $pmversion, '<')) {
            $this->out = html::tag('div', array(
                'style' => 'font-size: 12px; text-align: justify; position: absolute; margin-left: auto; left: 50%; margin-left: -200px; width: 400px;'
            ), html::tag('h3', null, 'Plugin Manager is incompatible with your Roundcube installation (' . RCMAIL_VERSION . ')') . html::tag('span', null, 'Please ' . html::tag('a', array(
                'href' => 'http://myroundcube.com/myroundcube-plugins/plugin-manager',
                'target' => '_blank'
            ), 'download') . ' Plugin Manager package again and upload the entire package to your Roundcube\'s plugin folder.') . html::tag('br') . html::tag('br') . html::tag('div', array(
                'style' => 'display: inline; float: left'
            ), html::tag('a', array(
                'href' => 'javascript:void(0)',
                'onclick' => 'document.location.href=\'./\''
            ), $this->gettext('done'))));
            $this->register_handler('plugin.body', array(
                $this,
                'sqlerror'
            ));
            $this->rcmail->output->send('plugin');
        }
        $this->add_hook('login_after', array(
            $this,
            'login_after'
        ));
        if ($this->rcmail->task == 'mail' && $this->rcmail->action == 'plugin.dla') {
            if (file_exists(INSTALL_PATH . 'plugins/detach_attachments/detach_attachments.php')) {
                $this->require_plugin('detach_attachments');
            }
        }
        if ($this->rcmail->task == 'settings' && $this->rcmail->action == 'save-pref' && get_input_value('_name', RCUBE_INPUT_POST)) {
            $sql = 'DELETE FROM ' . get_table_name('plugin_manager') . ' WHERE ' . $this->q('conf') . '=?';
            $this->rcmail->db->query($sql, '_plugin_manager_hmail');
            $save = get_input_value('_value', RCUBE_INPUT_POST);
            $sql  = 'INSERT INTO ' . get_table_name('plugin_manager') . ' (conf, value, type) VALUES (?, ?, ?)';
            $this->rcmail->db->query($sql, '_plugin_manager_hmail', $save ? 1 : 0, 'bool');
            $this->rcmail->session->remove('plugin_manager_settings');
        }
        if (!isset($_SESSION['plugin_manager_settings'])) {
            $sql = 'SELECT * FROM ' . get_table_name('system') . ' WHERE ' . $this->q('name') . '=?';
            $res = $this->rcmail->db->query($sql, 'myrc_plugin_manager');
            $res = $this->rcmail->db->fetch_assoc($res);
            if (is_array($res)) {
                $sql = 'SELECT * FROM ' . get_table_name('plugin_manager') . ' WHERE ' . $this->q('conf') . ' LIKE ?';
                $res = $this->rcmail->db->query($sql, '_plugin_manager_%');
                if ($res) {
                    while ($conf = $this->rcmail->db->fetch_assoc($res)) {
                        $this->rcmail->config->set(substr($conf['conf'], 1), $conf['value']);
                        $_SESSION['plugin_manager_settings'][substr($conf['conf'], 1)] = $conf['value'];
                    }
                }
            }
        } else {
            foreach ($_SESSION['plugin_manager_settings'] as $key => $value) {
                $this->rcmail->config->set($key, $value);
            }
        }
        $this->use_ssl           = $this->rcmail->config->get('plugin_manager_use_ssl');
        $this->use_hmail         = $this->rcmail->config->get('plugin_manager_hmail');
        $this->maintenance_mode  = $this->rcmail->config->get('plugin_manager_maintenance_mode');
        $this->compress_html     = $this->rcmail->config->get('plugin_manager_compress_html');
        $this->file_based_config = $this->rcmail->config->get('plugin_manager_file_based_config');
        $svn                     = parse_url($this->svn);
        if ($this->file_based_config || $_SERVER['HTTP_HOST'] == $svn['host']) {
            $this->file_based_config = true;
            if (!in_array('global_config', $this->rcmail->config->get('plugins'))) {
                $this->load_config();
                $this->require_plugin('settings');
            }
            $this->defaults   = $this->rcmail->config->get('plugin_manager_defaults', array());
            $this->unauth     = $this->rcmail->config->get('plugin_manager_unauth', array());
            $this->thirdparty = $this->rcmail->config->get('plugin_manager_third_party_plugins', array());
        } else {
            $this->build_defaults();
            if (!in_array('global_config', $this->rcmail->config->get('plugins'))) {
                $this->require_plugin('settings');
            }
        }
        if ($this->rcmail->config->get('plugin_manager_use_ssl', false) && defined('OPENSSL_VERSION_TEXT')) {
            $this->mirror     = str_replace('http://', 'https://', $this->mirror);
            $this->svn        = str_replace('http://', 'https://', $this->svn);
            $this->billingurl = str_replace('http://', 'https://', $this->billingurl);
        }
        if ($hash = $this->rcmail->config->get('plugin_manager_hash')) {
            if (file_exists(INSTALL_PATH . $hash . '.myrc')) {
                $this->config_permissions = true;
            }
        }
        $fileadmins = $this->rcmail->config->get('plugin_manager_admins');
        if ($this->rcmail->task == 'settings' && $_SESSION['plugin_manager_admins']) {
            $this->admins = $_SESSION['plugin_manager_admins'];
        } else if ($this->rcmail->task == 'settings' && !isset($_GET['_remote'])) {
            $this->admins = array();
            $sql          = 'SELECT * FROM ' . get_table_name('plugin_manager') . ' WHERE ' . $this->q('conf') . '=?';
            $res          = $this->rcmail->db->limitquery($sql, 0, 1, 'admins');
            if ($res) {
                $admins = $this->rcmail->db->fetch_assoc($res);
                if ($admins = unserialize($admins['value'])) {
                    $this->rcmail->config->set('plugin_manager_admins', $admins);
                    $this->admins                      = array_flip($admins);
                    $_SESSION['plugin_manager_admins'] = $this->admins;
                }
            }
            if (count($this->admins) < 1 && strtolower($this->get_demo($_SESSION['username'])) != strtolower(sprintf($this->rcmail->config->get('demo_user_account'), ""))) {
                if ($this->rcmail->task == 'settings' && !isset($_GET['_remote'])) {
                    if (!$hash = $this->rcmail->config->get('plugin_manager_hash')) {
                        $hash = session_id();
                        $this->rcmail->user->save_prefs(array(
                            'plugin_manager_hash' => $hash
                        ));
                    } else {
                        if (!file_exists(INSTALL_PATH . $hash . '.myrc') && !isset($_GET['_framed'])) {
                            $this->register_handler('plugin.body', array(
                                $this,
                                'authenticate'
                            ));
                            $this->rcmail->output->send('plugin');
                        } else {
                            $query = 'SELECT * FROM ' . get_table_name('plugin_manager') . ' WHERE ' . $this->q('conf') . '=?';
                            $res   = $this->rcmail->db->limitquery($query, 0, 1, 'admins');
                            $res   = $this->rcmail->db->fetch_assoc($res);
                            if (is_array($res)) {
                                $admins = unserialize($res['value']);
                            } else {
                                $query      = 'INSERT INTO ' . get_table_name('plugin_manager') . ' (' . $this->q('conf') . ', ' . $this->q('value') . ', ' . $this->q('type') . ') VALUES (?, ?, ?)';
                                $superadmin = array(
                                    $this->rcmail->user->data['username']
                                );
                                $this->rcmail->db->query($query, 'admins', serialize($superadmin), 'array');
                                $this->rcmail->config->set('plugin_manager_admins', $superadmin);
                                $this->admins                      = array_flip($superadmin);
                                $_SESSION['plugin_manager_admins'] = $this->admins;
                            }
                        }
                    }
                }
            } else if ($this->rcmail->task != 'logout' && strtolower($this->get_demo($_SESSION['username'])) != strtolower(sprintf($this->rcmail->config->get('demo_user_account'), ""))) {
                $admins = $this->rcmail->config->get('plugin_manager_admins', array());
                if ($admins[0] == strtolower($this->rcmail->user->data['username'])) {
                    if (!$hash = $this->rcmail->config->get('plugin_manager_hash')) {
                        $hash = session_id();
                        $this->rcmail->user->save_prefs(array(
                            'plugin_manager_hash' => $hash
                        ));
                    } else {
                        if (!file_exists(INSTALL_PATH . $hash . '.myrc') && !isset($_GET['_framed'])) {
                            $this->register_handler('plugin.body', array(
                                $this,
                                'authenticate'
                            ));
                            $this->rcmail->output->send('plugin');
                        }
                    }
                }
            }
            $this->require_plugin('qtip');
            $hash = $this->rcmail->config->get('plugin_manager_hash');
            if (is_array($fileadmins) && file_exists(INSTALL_PATH . $hash . '.myrc')) {
                $this->rcmail->session->remove('plugin_manager_admins');
                $sql = 'UPDATE ' . get_table_name('plugin_manager') . ' SET ' . $this->q('value') . '=? WHERE ' . $this->q('conf') . '=?';
                $this->rcmail->db->query($sql, serialize($fileadmins), 'admins');
                $this->out = html::tag('div', array(
                    'style' => 'font-size: 12px; text-align: justify; position: absolute; margin-left: auto; left: 50%; margin-left: -200px; width: 400px;'
                ), html::tag('h3', null, 'Plugin Manager detected a misconfiguration') . html::tag('span', null, 'Please remove ' . '$config[\'plugin_manager_admins\'] from your configuration file.') . html::tag('br') . html::tag('br') . html::tag('pre', null, print_r($fileadmins, true)) . html::tag('div', array(
                    'style' => 'display: inline; float: right'
                ), html::tag('a', array(
                    'href' => 'javascript:void(0)',
                    'onclick' => 'document.location.href=\'./?_task=settings\''
                ), $this->gettext('done'))));
                $this->register_handler('plugin.body', array(
                    $this,
                    'sqlerror'
                ));
                $this->rcmail->output->send('plugin');
            }
        }
        if ($this->rcmail->action != 'jappix.loadmini') {
            $this->add_texts('localization/', false);
            $this->add_hook('render_page', array(
                $this,
                'render_page'
            ));
            $this->add_hook('send_page', array(
                $this,
                'send_page'
            ));
            /* $this->add_hook('preferences_sections_list', array(
                $this,
                'settings_link'
            )); */
            $this->add_hook('preferences_list', array(
                $this,
                'settings'
            ));
            $this->add_hook('preferences_save', array(
                $this,
                'saveprefs'
            ));
            $this->add_hook('plugins_installed', array(
                $this,
                'plugins_installed'
            ));
            $this->register_action('plugin.plugin_manager', array(
                $this,
                'navigation'
            ));
            $this->register_action('plugin.plugin_manager_save', array(
                $this,
                'save'
            ));
            $this->register_action('plugin.plugin_manager_uninstall', array(
                $this,
                'uninstall'
            ));
            $this->register_action('plugin.plugin_manager_update', array(
                $this,
                'update'
            ));
            $this->register_action('plugin.plugin_manager_bind', array(
                $this,
                'bind'
            ));
            $this->register_action('plugin.plugin_manager_unbind', array(
                $this,
                'unbind'
            ));
            $this->register_action('plugin.plugin_manager_getnew', array(
                $this,
                'getnew'
            ));
            $this->register_action('plugin.plugin_manager_deny', array(
                $this,
                'deny'
            ));
            $this->register_action('plugin.plugin_manager_show_config', array(
                $this,
                'show'
            ));
            $this->register_action('plugin.plugin_manager_save_config', array(
                $this,
                'save_config'
            ));
            $this->register_action('plugin.plugin_manager_edit_config', array(
                $this,
                'edit_config'
            ));
            $this->register_action('plugin.plugin_manager_restore_config', array(
                $this,
                'restore_config'
            ));
            $this->register_action('plugin.plugin_manager_accept', array(
                $this,
                'accept'
            ));
            $this->register_action('plugin.plugin_manager_transfer', array(
                $this,
                'transfer'
            ));
            $this->register_action('plugin.plugin_manager_getcredits', array(
                $this,
                'getcredits'
            ));
            $this->register_action('plugin.plugin_manager_buycredits', array(
                $this,
                'buycredits'
            ));
            $this->register_action('plugin.plugin_manager_compress', array(
                $this,
                'compress'
            ));
            $this->register_action('plugin.plugin_manager_update_notifier', array(
                $this,
                'update_notifier'
            ));
            $this->include_script('plugin_manager_fixes.js');
            if (isset($this->admins[$this->rcmail->user->data['username']])) {
                $skin = $this->rcmail->config->get('skin');
                if (!file_exists($this->home . '/skins/' . $skin . '/plugin_manager_update.css')) {
                    $skin = 'classic';
                }
                $this->require_plugin('http_request');
                $httpConfig['method']     = 'GET';
                $httpConfig['target']     = $this->svn . '?_action=plugin.plugin_server_pmversion';
                $httpConfig['timeout']    = '30';
                $httpConfig['user_agent'] = 'MyRoundcube PHP/5.0';
                $http                     = new MyRCHttp();
                $http->initialize($httpConfig);
                if (ini_get('safe_mode') || ini_get('open_basedir')) {
                    $http->useCurl(false);
                }
                $http->execute();
                if ($http->error) {
                    $this->mirror         = str_replace('https://', 'http://', $this->mirror);
                    $this->svn            = str_replace('https://', 'http://', $this->svn);
                    $this->billingurl     = str_replace('https://', 'http://', $this->billingurl);
                    $httpConfig['target'] = $this->svn . '?_action=plugin.plugin_server_pmversion';
                    $http->initialize($httpConfig);
                    $http->execute();
                    if ($http->error) {
                        $this->mirror         = str_replace('.com', '.net', $this->mirror);
                        $this->svn            = str_replace('.com', '.net', $this->svn);
                        $this->billingurl     = str_replace('.com', '.net', $this->billingurl);
                        $this->guide          = str_replace('.com', '.net', $this->guide);
                        $this->dlurl          = str_replace('.com', '.net', $this->dlurl);
                        $this->dlurl          = str_replace('https://', 'http://', $this->dlurl);
                        $httpConfig['target'] = $this->svn . '?_action=plugin.plugin_server_pmversion';
                        $http->initialize($httpConfig);
                        $http->execute();
                    }
                }
                if (!preg_match('/^[0-9\.|]+$/', trim($http->result)) || count(explode('|', $http->result)) != 2) {
                    $this->mirror         = str_replace('https://', 'http://', $this->mirror);
                    $this->svn            = str_replace('https://', 'http://', $this->svn);
                    $this->billingurl     = str_replace('https://', 'http://', $this->billingurl);
                    $this->dlurl          = str_replace('https://', 'http://', $this->dlurl);
                    $this->mirror         = str_replace('.com', '.net', $this->mirror);
                    $this->svn            = str_replace('.com', '.net', $this->svn);
                    $this->billingurl     = str_replace('.com', '.net', $this->billingurl);
                    $this->guide          = str_replace('.com', '.net', $this->guide);
                    $this->dlurl          = str_replace('.com', '.net', $this->dlurl);
                    $httpConfig['target'] = $this->svn . '?_action=plugin.plugin_server_pmversion';
                    $http->initialize($httpConfig);
                    $http->execute();
                }
                if ($this->debug) {
                    write_log('plugin_manager', $httpConfig);
                    write_log('plugin_manager', $_SERVER['SERVER_ADDR']);
                    write_log('plugin_manager', self::$version);
                    write_log('plugin_manager', $http);
                }
                if (!preg_match('/^[0-9\.|]+$/', trim($http->result)) || count(explode('|', $http->result)) != 2) {
                    $this->noremote = true;
                    $http->result   = 'error';
                }
                if (!$http->error) {
                    $response = $http->result;
                    $temp     = explode('|', $response, 2);
                    if ($response == 'error') {
                        $this->noremote = true;
                        if (!get_input_value('_remote', RCUBE_INPUT_GPC) && !get_input_value('_framed', RCUBE_INPUT_GPC)) {
                            $this->include_stylesheet('skins/' . $skin . '/plugin_manager_update.css');
                            $this->api->output->add_footer(html::tag('div', array(
                                'class' => 'myrcerror myrcmessage'
                            ), html::tag('span', null, $this->gettext('myrcerror'))));
                        }
                    } else if (self::$version != $temp[0]) {
                        if (self::$version < $temp[1]) {
                            $this->rcmail->session->remove('pm_update_message');
                        }
                        if ((!$_SESSION['pm_update_message']) && !get_input_value('_framed', RCUBE_INPUT_GPC) && $this->rcmail->action != 'about') {
                            if (self::$version < $temp[1]) {
                                $this->noremote                = true;
                                $this->delay                   = 500000;
                                $_SESSION['pm_update_message'] = true;
                                if (!get_input_value('_remote', RCUBE_INPUT_GPC)) {
                                    $this->include_stylesheet('skins/' . $skin . '/plugin_manager_update.css');
                                    $this->api->output->add_footer(html::tag('div', array(
                                        'class' => 'updatepmrequired myrcmessage',
                                        'onclick' => 'document.location.href="' . slashify($this->svn) . '?_action=plugin.plugin_server_get_pm"; $(this).hide("slow");'
                                    ), html::tag('span', null, $this->gettext('updatepmrequired')) . ((strpos($skin, 'litecube') !== false) ? '&nbsp;' : html::tag('br')) . html::tag('span', array(
                                        'style' => 'text-decoration:underline;'
                                    ), $this->gettext('downloadnow'))));
                                }
                            } else {
                                $this->delay                   = 30000;
                                $_SESSION['pm_update_message'] = true;
                                if (!get_input_value('_remote', RCUBE_INPUT_GPC)) {
                                    $this->include_stylesheet('skins/' . $skin . '/plugin_manager_update.css');
                                    $this->api->output->add_footer(html::tag('div', array(
                                        'class' => 'updatepm myrcmessage',
                                        'onclick' => 'document.location.href="' . slashify($this->svn) . '?_action=plugin.plugin_server_get_pm"; $(this).hide("slow");'
                                    ), html::tag('span', null, $this->gettext('updatepm')) . ((strpos($skin, 'litecube') !== false) ? '&nbsp;' : html::tag('br')) . html::tag('span', array(
                                        'style' => 'text-decoration:underline;'
                                    ), $this->gettext('downloadnow'))));
                                }
                            }
                        }
                    }
                } else {
                    $this->delay    = 8000;
                    $this->noremote = true;
                    if (!get_input_value('_remote', RCUBE_INPUT_GPC) && !get_input_value('_framed', RCUBE_INPUT_GPC)) {
                        $this->include_stylesheet('skins/' . $skin . '/plugin_manager_update.css');
                        $this->api->output->add_footer(html::tag('div', array(
                            'class' => 'myrcerror myrcmessage'
                        ), html::tag('span', null, $this->gettext('myrcerror'))));
                    }
                }
                $httpConfig['target'] = $this->svn . '?_action=plugin.plugin_server_branches';
                $http->initialize($httpConfig);
                $http->execute();
                if ($http->error) {
                    $httpConfig['target'] = $this->svn . '?_action=plugin.plugin_server_branches';
                    $http->initialize($httpConfig);
                    $http->execute();
                }
                if (!$http->error) {
                    if ($branches = unserialize($http->result)) {
                        $this->dev    = $branches['dev'];
                        $this->stable = $branches['stable'];
                    }
                }
                if (!$_SESSION['pm_update_message'] && $_SESSION['user_id'] && $this->rcmail->task != 'logout' && !get_input_value('_framed', RCUBE_INPUT_GPC) && $this->rcmail->config->get('plugin_manager_show_myrc_messages', false)) {
                    $httpConfig['target'] = $this->svn . '?_action=plugin.plugin_server_motd';
                    $http->initialize($httpConfig);
                    $http->execute();
                    if ($http->error) {
                        $httpConfig['target'] = $this->svn . '?_action=plugin.plugin_server_motd';
                        $http->initialize($httpConfig);
                        $http->execute();
                    }
                    if (!$http->error) {
                        if ($http->result != '') {
                            $this->delay                   = 30000;
                            $_SESSION['pm_update_message'] = true;
                            if (!get_input_value('_remote', RCUBE_INPUT_GPC)) {
                                $this->include_stylesheet('skins/' . $skin . '/plugin_manager_update.css');
                                $this->api->output->add_footer(html::tag('div', array(
                                    'class' => 'motd myrcmessage'
                                ), html::tag('span', null, html::tag('div', array(
                                    'style' => 'float: right'
                                ), html::tag('small', null, '[' . html::tag('a', array(
                                    'href' => '#',
                                    'onclick' => '$(".myrcmessage").hide()',
                                    'title' => $this->gettext('close')
                                ), 'x') . ']')) . $http->result)));
                            }
                        }
                    }
                }
            }
            if (count($this->admins) == 0 && ($_SERVER['QUERY_STRING'] == '_task=settings&_action=edit-prefs&_section=plugin_manager_update&_framed=1' || $_SERVER['QUERY_STRING'] == '_task=settings&_action=edit-prefs&_section=plugin_manager_customer&_framed=1')) {
                $this->rcmail->output->add_script('parent.location.href="./?_task=settings"', 'docready');
            }
            $this->register_action('plugin.google_contacts_uninstall', array(
                $this,
                'google_contacts_uninstall'
            ));
            $this->register_action('plugin.automatic_addressbook_uninstall', array(
                $this,
                'automatic_addressbook_uninstall'
            ));
        }
        $this->plugins = $this->rcmail->config->get('plugins', array());
        $this->host    = strtolower($_SERVER['HTTP_HOST']);
        $temparr       = explode('@', $_SESSION['username']);
        $this->domain  = strtolower($temparr[1]);
        if ($this->domain == '') {
            $host = $this->rcmail->user->data['mail_host'];
            if ($host == 'localhost') {
                $host = $_SERVER['HTTP_HOST'];
            }
            $this->domain = $host;
        }
        $this->merge_config();
        $deferred = array();
        foreach ($this->config as $sections => $section) {
            foreach ($section as $plugin => $props) {
                if (isset($this->config[$sections][$plugin])) {
                    if ($props['active']) {
                        $load = true;
                        if (is_array($props['hosts']) && count($props['hosts'] > 0)) {
                            $load = false;
                            foreach ($props['hosts'] as $host) {
                                if ($this->host == strtolower($host)) {
                                    $load = true;
                                    break;
                                }
                            }
                        }
                        if ($this->domain) {
                            if ($props['domain'] === true) {
                                $load = true;
                            } else if (is_array($props['domains']) && count($props['domains'] > 0)) {
                                $load = false;
                                foreach ($props['domains'] as $domain) {
                                    if ($this->domain == strtolower($domain)) {
                                        $load = true;
                                        break;
                                    }
                                }
                            }
                        }
                        if (is_array($props['skins'])) {
                            $props['skins'] = array_flip($props['skins']);
                            if (!isset($props['skins'][$this->rcmail->config->get('skin', 'classic')])) {
                                $load = false;
                            }
                        }
                        if ($load) {
                            if ($file = @file_get_contents(INSTALL_PATH . '/plugins/' . $plugin . '/' . $plugin . '.php')) {
                                $file    = str_replace(' ', '', $file);
                                $file    = current(explode('functioninit(', $file, 2));
                                $task    = explode('$task', $file, 2);
                                $task    = explode('=', $task[1], 2);
                                $task    = explode(';', $task[1], 2);
                                $task    = str_replace(array(
                                    '"',
                                    "'"
                                ), array(
                                    '',
                                    ''
                                ), trim($task[0]));
                                $noajax  = explode('$noajax', $file, 2);
                                $noajax  = explode('=', $noajax[1], 2);
                                $noajax  = explode(';', $noajax[1], 2);
                                $noajax  = str_replace(array(
                                    '"',
                                    "'"
                                ), array(
                                    '',
                                    ''
                                ), trim($noajax[0]));
                                $noframe = explode('$noframe', $file, 2);
                                $noframe = explode('=', $noframe[1], 2);
                                $noframe = explode(';', $noframe[1], 2);
                                $noframe = str_replace(array(
                                    '"',
                                    "'"
                                ), array(
                                    '',
                                    ''
                                ), trim($noajax[0]));
                                if ($task) {
                                    if (!preg_match('/^(' . $task . ')$/i', $this->rcmail->task)) {
                                        $noajax  = false;
                                        $noframe = false;
                                        $load    = false;
                                    }
                                }
                                if ($noajax && ($noajax == 'true' || $noajax == '1')) {
                                    if (isset($_REQUEST['_remote'])) {
                                        $noframe = false;
                                        $load    = false;
                                    }
                                }
                                if ($noframe && ($noframe == 'true' || $noframe == '1')) {
                                    if (isset($_REQUEST['_framed'])) {
                                        $load = false;
                                    }
                                }
                            }
                        }
                        if ($load && !$this->maintenance_mode) {
                            if ($props['browser']) {
                                if (!$browser)
                                    $browser = new rcube_browser();
                                eval($props['browser']);
                                if ($test) {
                                    if ($props['defer']) {
                                        $deferred[] = $plugin;
                                    } else {
                                        $this->require_plugin($plugin);
                                    }
                                }
                            } else if ($props['defer']) {
                                $deferred[] = $plugin;
                            } else {
                                $this->require_plugin($plugin);
                            }
                        }
                    } else {
                        if ($props['eval']) {
                            if (!is_array($props['eval'])) {
                                $eval = array(
                                    $props['eval']
                                );
                            } else {
                                $eval = $props['eval'];
                            }
                            foreach ($eval as $code) {
                                eval($code);
                            }
                        }
                    }
                }
            }
        }
        if (!$this->maintenance_mode) {
            foreach ($deferred as $plugin) {
                $this->require_plugin($plugin);
            }
        }
    }
    static function about($keys = false)
    {
        $requirements = self::$requirements;
        foreach (array(
            'required_',
            'recommended_'
        ) as $prefix) {
            if (is_array($requirements[$prefix . 'plugins'])) {
                foreach ($requirements[$prefix . 'plugins'] as $plugin => $method) {
                    if (class_exists($plugin) && method_exists($plugin, 'about')) {
                        $class                                      = new $plugin(false);
                        $requirements[$prefix . 'plugins'][$plugin] = array(
                            'method' => $method,
                            'plugin' => $class->about($keys)
                        );
                    } else {
                        $requirements[$prefix . 'plugins'][$plugin] = array(
                            'method' => $method,
                            'plugin' => $plugin
                        );
                    }
                }
            }
        }
        $config = array();
        $ret    = array(
            'plugin' => self::$plugin,
            'version' => self::$version,
            'db_version' => self::$db_version,
            'date' => self::$date,
            'author' => self::$author,
            'comments' => self::$authors_comments,
            'licence' => self::$licence,
            'download' => self::$download,
            'requirements' => $requirements
        );
        if (is_array(self::$prefs))
            $ret['config'] = array_merge($config, array_flip(self::$prefs));
        else
            $ret['config'] = $config;
        if (is_array($keys)) {
            $return = array(
                'plugin' => self::$plugin
            );
            foreach ($keys as $key) {
                $return[$key] = $ret[$key];
            }
            return $return;
        } else {
            return $ret;
        }
    }
    function update_notifier()
    {
        $_SESSION['plugin_manager_update_notifier'] = true;
        $server                                     = array();
        $updates                                    = array();
        $dir                                        = scandir(INSTALL_PATH . 'plugins');
        foreach ($dir as $dirname) {
            if ($dirname != '.' && $dirname != '..' && is_dir(INSTALL_PATH . 'plugins/' . $dirname) && file_exists(INSTALL_PATH . 'plugins/' . $dirname . '/' . $dirname . '.php')) {
                $server[$dirname] = 1;
            }
        }
        if (!empty($server)) {
            $this->require_plugin('http_request');
            $params                   = array();
            $httpConfig['method']     = 'GET';
            $httpConfig['target']     = $this->svn . '?_action=plugin.plugin_server_update_notifier';
            $httpConfig['timeout']    = '30';
            $httpConfig['params']     = $params;
            $httpConfig['user_agent'] = 'MyRoundcube PHP/5.0';
            $http                     = new MyRCHttp();
            $http->initialize($httpConfig);
            if (ini_get('safe_mode') || ini_get('open_basedir')) {
                $http->useCurl(false);
            }
            $http->execute();
            if ($http->error) {
                $response = false;
            } else {
                $response = $http->result;
            }
            if ($mirror = json_decode($response, true)) {
                if (is_array($mirror)) {
                    $sql   = 'SELECT value FROM ' . get_table_name('system') . ' WHERE name=?';
                    $res   = $this->rcmail->db->limitquery($sql, 0, 1, 'myrc_plugin_manager_updates_hash');
                    $value = $this->rcmail->db->fetch_assoc($res);
                    if (is_array($value)) {
                        $value = current($value);
                    } else {
                        $value = '';
                    }
                    if ($value != md5($response)) {
                        $sql = 'DELETE FROM ' . get_table_name('system') . ' WHERE name=?';
                        $this->rcmail->db->query($sql, 'myrc_plugin_manager_updates_hash');
                        $sql = 'INSERT INTO ' . get_table_name('system') . '(name, value) VALUES (?, ?)';
                        $this->rcmail->db->query($sql, 'myrc_plugin_manager_updates_hash', md5($response));
                        $sql = 'DELETE FROM ' . get_table_name('system') . ' WHERE name=?';
                        $this->rcmail->db->query($sql, 'myrc_plugin_manager_updates_last');
                        $sql = 'INSERT INTO ' . get_table_name('system') . '(name, value) VALUES (?, ?)';
                        $this->rcmail->db->query($sql, 'myrc_plugin_manager_updates_last', date('Y-m-d H:i:s'));
                        foreach ($server as $plugin => $null) {
                            if (isset($mirror[$plugin])) {
                                if (file_exists(INSTALL_PATH . 'plugins/' . $plugin . '/' . $plugin . '.php')) {
                                    $this->require_plugin($plugin);
                                    if (class_exists($plugin) && method_exists($plugin, 'about')) {
                                        $class = new $plugin(false);
                                        $props = $class->about('version', 'date');
                                        if (version_compare($props['version'], $mirror[$plugin]['version'], '<')) {
                                            $updates[$plugin] = array(
                                                'server' => $props['version'],
                                                'mirror' => $mirror[$plugin]['version']
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $rows = html::tag('tr', null, html::tag('th', array(
                        'align' => 'left',
                        'style' => 'border: 1px solid grey'
                    ), 'Plugin') . html::tag('th', array(
                        'align' => 'right',
                        'style' => 'border: 1px solid grey'
                    ), 'Locale Version') . html::tag('th', array(
                        'align' => 'right',
                        'style' => 'border: 1px solid grey'
                    ), 'Remote Version') . html::tag('th', array(
                        'align' => 'right',
                        'style' => 'border: 1px solid grey'
                    ), '&nbsp;'));
                    if (!empty($updates)) {
                        foreach ($updates as $plugin => $props) {
                            $rows .= html::tag('tr', null, html::tag('td', array(
                                'style' => 'border: 1px solid grey'
                            ), $plugin) . html::tag('td', array(
                                'align' => 'right',
                                'style' => 'border: 1px solid grey'
                            ), $props['server']) . html::tag('td', array(
                                'align' => 'right',
                                'style' => 'border: 1px solid grey'
                            ), $props['mirror']) . html::tag('td', array(
                                'align' => 'right',
                                'style' => 'border: 1px solid grey'
                            ), html::tag('a', array(
                                'href' => 'https://myroundcube.com/myroundcube-plugins/show-changelog?_plugin=' . $plugin
                            ), 'CHANGELOG')));
                        }
                        $table = html::tag('table', array(
                            'cellpadding' => 4,
                            'cellspacing' => 0,
                            'style' => 'border: 1px solid grey'
                        ), $rows);
                        $body  = 'Hello,<br /><br />There are plugins updates available for your Roundcube installation at <i>' . $this->gethost() . '</i>, <i>' . INSTALL_PATH . '</i>:<br /><br />' . $table . '<br /><br />Greetings,<br />MyRoundcube Support<br />(c) ';
                        $body .= html::tag('a', array(
                            'href' => 'http://myroundcube.com'
                        ), 'MyRoundcube.com') . ' 2012-' . date('Y') . '<br /><br />';
                        $body .= html::tag('div', array(
                            'style' => 'text-align:justify;'
                        ), 'You are receiving this email notification from Plugin Manager. If you no longer wish to receive updates notifications, please disable the <i>Update Notifications</i> option in your Roundcube installation <i>(Settings&nbsp;&raquo;&nbsp;Manage&nbsp;Plugins&nbsp;&raquo;&nbsp;Settings)</i>.');
                        $body   = html::tag('div', array(
                            'style' => 'width: 580px'
                        ), $body);
                        $sql    = 'SELECT value FROM ' . get_table_name('plugin_manager') . ' WHERE conf=?';
                        $res    = $this->rcmail->db->limitquery($sql, 0, 1, 'admins');
                        $admins = $this->rcmail->db->fetch_assoc($res);
                        if (is_array($admins)) {
                            $admins = unserialize(current($admins));
                            if (is_array($admins)) {
                                $subject = 'MyRoundcube Plugins Updates available';
                                $cc      = $this->rcmail->config->get('plugin_manager_update_notifications_cc');
                                foreach ($admins as $admin) {
                                    $to = rcube_user::user2email($admin, false, true);
                                    if (!$to) {
                                        $to = $admin;
                                    }
                                    $this->sendmail($this->from, $to, $cc, $subject, $body);
                                }
                                if ($cc) {
                                    $this->sendmail($this->from, $cc, $cc, $subject, $body);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    function plugins_installed($plugins)
    {
        unset($plugins['abort']);
        $conf = $this->defaults;
        foreach ($conf as $section) {
            foreach ($section as $plugin => $props) {
                if ($props['protected']) {
                    if ($props['active']) {
                        $plugins[] = $plugin;
                    }
                } else {
                    $plugins[] = $plugin;
                }
            }
        }
        $plugs = $plugins;
        foreach ($plugins as $key => $plugin) {
            $this->require_plugin($plugin);
            if (method_exists($plugin, 'about')) {
                $class        = new $plugin(false);
                $about        = $class->about();
                $requirements = $about['requirements'];
                foreach ($requirements as $requirement => $props) {
                    if ($requirement == 'required_plugins') {
                        foreach ($props as $plugin => $method) {
                            if ($method['method'] == 'require_plugin') {
                                $plugs[] = $plugin;
                            }
                        }
                    }
                }
            }
        }
        return $plugs;
    }
    function build_defaults()
    {
        if ($_SESSION['plugin_manager_defaults'] && !isset($_SESSION['plugin_manager_admins'][strtolower($this->rcmail->user->data['username'])])) {
            $this->defaults = $_SESSION['plugin_manager_defaults'];
            $this->unauth   = $_SESSION['plugin_manager_unauth'];
            return;
        }
        include INSTALL_PATH . 'plugins/plugin_manager/defaults.inc.php';
        $defaults  = $config['plugin_manager_defaults'];
        $sql       = 'SELECT * FROM ' . get_table_name('plugin_manager') . ' WHERE ' . $this->q('conf') . '=?';
        $res       = $this->rcmail->db->limitquery($sql, 0, 1, 'defaults_overwrite');
        $overwrite = $this->rcmail->db->fetch_assoc($res);
        if (is_array($overwrite)) {
            if ($overwrite = unserialize($overwrite['value'])) {
                $depricate = false;
                foreach ($overwrite as $section => $plugins) {
                    foreach ($plugins as $plugin => $props) {
                        if (isset($defaults[$section][$plugin]['deprecated'])) {
                            if (RCMAIL_VERSION > $defaults[$section][$plugin]['deprecated']) {
                                unset($overwrite[$section][$plugin]);
                                $deprecate = true;
                            }
                        }
                        foreach ($props as $prop => $value) {
                            $defaults[$section][$plugin][$prop] = $value;
                        }
                    }
                }
                if ($deprecate) {
                    $sql = 'UPDATE ' . get_table_name('plugin_manager') . ' SET ' . $this->q('value') . '=? WHERE ' . $this->q('conf') . '=?';
                    $this->rcmail->db->query($sql, serialize($overwrite), 'defaults_overwrite');
                }
            }
        }
        foreach ($defaults as $section => $plugins) {
            foreach ($plugins as $plugin => $props) {
                if (!file_exists(INSTALL_PATH . 'plugins/' . $plugin . '/' . $plugin . '.php')) {
                    unset($defaults[$section][$plugin]);
                } else if (substr($plugin, 0, strlen('hmail_')) == 'hmail_' && !$this->use_hmail) {
                    unset($defaults[$section][$plugin]);
                } else {
                    if (isset($config['plugin_manager_unauth'][$plugin])) {
                        $this->unauth[$plugin] = true;
                    }
                }
            }
        }
        $sql       = 'SELECT ' . $this->q('value') . ' FROM ' . get_table_name('plugin_manager') . ' WHERE ' . $this->q('conf') . '=?';
        $res       = $this->rcmail->db->limitquery($sql, 0, 1, 'defaults');
        $overwrite = $this->rcmail->db->fetch_assoc($res);
        if (is_array($overwrite)) {
            if ($overwrite = unserialize($overwrite['value'])) {
                foreach ($overwrite as $section => $plugins) {
                    foreach ($plugins as $plugin => $props) {
                        foreach ($props as $prop => $value) {
                            if (isset($defaults[$section][$plugin])) {
                                $true = true;
                                if (is_bool($defaults[$section][$plugin][$prop])) {
                                    $defaults[$section][$plugin][$prop] = $value ? true : false;
                                }
                            }
                        }
                    }
                }
            }
        }
        foreach ($defaults as $section => $plugins) {
            foreach ($plugins as $plugin => $props) {
                if (isset($defaults[$section][$plugin]['deprecated'])) {
                    if (RCMAIL_VERSION > $defaults[$section][$plugin]['deprecated']) {
                        unset($defaults[$section][$plugin]);
                    }
                }
            }
        }
        $this->defaults                      = $defaults;
        $_SESSION['plugin_manager_defaults'] = $defaults;
        $_SESSION['plugin_manager_unauth']   = $this->unauth;
        $this->thirdparty                    = $config['plugin_manager_third_party_plugins'];
    }
    function sqlerror($p)
    {
        return $this->out;
    }
    function authenticate($p)
    {
        $rcmail = rcmail::get_instance();
        $this->add_texts('localization/');
        $hash = $this->rcmail->config->get('plugin_manager_hash');
        $out  = html::tag('div', array(
            'style' => 'font-size: 12px; text-align: justify; position: absolute; margin-left: auto; left: 50%; margin-left: -225px; width: 450px;'
        ), html::tag('h3', null, $this->gettext('welcome_to_plugin_manager')) . $this->gettext('about_to_create_account') . ' ' . $this->gettext('create_account_proceed') . ' ' . $this->gettext('please_create') . html::tag('br') . html::tag('br') . html::tag('b', null, html::tag('center', null, $hash . '.myrc')) . html::tag('br') . html::tag('br') . html::tag('div', array(
            'style' => 'display: block; float: left;'
        ), $this->gettext('thank_you')) . html::tag('div', array(
            'style' => 'display: block; float: right;'
        ), html::tag('a', array(
            'href' => './?_task=settings'
        ), $this->gettext('done'))) . html::tag('br') . html::tag('hr') . html::tag('div', array(
            'style' => 'font-size: 10px;'
        ), '&copy;&nbsp;2012 - ' . date('Y') . '&nbsp;MyRoundcube.com. All rights reserved.'));
        $this->rcmail->output->add_script('$(".button-settings").attr("onclick", "document.location.href=\'./?_task=settings\'")', 'docready');
        return $out;
    }
    function login_after($args)
    {
        $active = $this->rcmail->config->get('plugin_manager_active', array());
        if (!$active) {
            $this->build_defaults();
            foreach ($this->defaults as $section => $plugins) {
                foreach ($plugins as $plugin => $props) {
                    if ($props['active']) {
                        $active[$plugin] = 1;
                    } else {
                        $active[$plugin] = 0;
                    }
                }
            }
            $this->rcmail->user->save_prefs(array(
                'plugin_manager_active' => $active
            ));
        }
        return $args;
    }
    function render_page($p)
    {
        $this->template = $p['template'];
        if ($this->template == 'settings') {
            if ($next = get_input_value('_next', RCUBE_INPUT_GET)) {
                $this->rcmail->output->add_script('window.setTimeout(\'$("#rcmrow' . $next . '").trigger("mousedown").trigger("mouseup")\', 500);', 'docready');
            }
        } else if ($this->template == 'mail') {
            if ($this->rcmail->config->get('plugin_manager_update_notifications')) {
                if (!isset($_SESSION['plugin_manager_update_notifier'])) {
                    $sql  = 'SELECT value FROM ' . get_table_name('system') . ' WHERE name=?';
                    $res  = $this->rcmail->db->limitquery($sql, 0, 1, 'myrc_plugin_manager_updates_last');
                    $last = $this->rcmail->db->fetch_assoc($res);
                    if (is_array($last)) {
                        $last = strtotime(current($last));
                    } else {
                        $last = 0;
                    }
                    if ($last + 86400 < time()) {
                        $this->rcmail->output->add_script("rcmail.http_request('plugin.plugin_manager_update_notifier');", 'docready');
                    }
                }
            }
        }
        if (!get_input_value('_framed', RCUBE_INPUT_GET)) {
            if ($this->maintenance_mode) {
                $this->rcmail->output->show_message($this->gettext('running_in_maintenance_mode'), 'warning');
            }
            if (!$this->rcmail->config->get('plugin_manager_about_link', true)) {
                $this->rcmail->output->add_script('$(".about-link").hide();', 'foot');
            }
            if (!$this->rcmail->config->get('plugin_manager_support_link', true)) {
                if ($this->rcmail->action != 'jappix.loadmini') {
                    $this->rcmail->output->add_script('$(".support-link").hide();', 'docready');
                }
            }
            if ($section = get_input_value('_plugin_manager_settings_section', RCUBE_INPUT_GET)) {
                $this->rcmail->output->set_env('section', $section);
                $src = './?_task=settings&_action=edit-prefs&_section=plugin_manager_settings&_framed=1&_expand=' . get_input_value('_expand', RCUBE_INPUT_GET);
                if ($this->rcmail->config->get('skin', 'classic') != 'classic') {
                    $this->rcmail->output->add_script("$('#rcmrowplugin_manager_settings').addClass('selected focused'); $('#preferences-frame').attr('src', '" . $src . "');", 'docready');
                } else {
                    $this->rcmail->output->add_script("$('#rcmrowplugin_manager_settings').addClass('selected focused'); $('#prefs-frame').attr('src', '" . $src . "');", 'docready');
                }
            }
            if (!class_exists('tabbed')) {
                $this->rcmail->output->add_script('window.setTimeout("$(\'.myrcmessage\').hide(\'slow\');", ' . $this->delay . ');', 'docready');
            } else {
                $this->rcmail->output->set_env('pm_message_delay', $this->delay);
            }
            if ($this->rcmail->config->get('plugin_manager_myroundcube_watermark', true) || $this->rcmail->config->get('plugin_manager_remove_watermark', false)) {
                if (strtolower($this->rcmail->user->data['username']) != $this->rcmail->config->get('mysql_admin')) {
                    $repl = 'myroundcube.html';
                    if ($this->rcmail->config->get('plugin_manager_remove_watermark', false)) {
                        $repl = 'blank.html';
                    }
                    switch ($p['template']) {
                        case 'settings':
                        case 'addressbook':
                        case 'identities':
                        case 'folders':
                        case 'mail':
                            $p['content'] = str_replace('skins/' . $this->rcmail->config->get('skin', 'classic') . '/watermark.html', 'plugins/plugin_manager/skins/' . $this->rcmail->config->get('skin', 'classic') . '/' . $repl, $p['content']);
                            $this->rcmail->output->set_env('blankpage', 'plugins/plugin_manager/skins/' . $this->rcmail->config->get('skin', 'classic') . '/' . $repl);
                    }
                }
            }
        }
        return $p;
    }
    function send_page($p)
    {
        if ($this->template == 'settingsedit' && strpos($p['content'], '_plugin_manager_show_myrc_messages') !== false) {
            foreach ($this->jqueryui as $css) {
                $p['content'] = str_replace('plugins/jqueryui/themes/' . $this->rcmail->config->get('skin', 'classic') . '/' . $css, 'plugins/plugin_manager/skins/larry/jqueryui.css', $p['content']);
            }
        }
        if ($this->compress_html) {
            $p = $this->html_compress($p);
        } else {
            $temp   = explode('.', $this->template);
            $plugin = $temp[0];
            if (count($temp == 2)) {
                if (class_exists($plugin) && method_exists($plugin, 'about')) {
                    $class = new $plugin(false);
                    $about = $class->about(array(
                        'version',
                        'date'
                    ));
                    if ($temp[0] && $temp[1]) {
                        $comment      = '<!-- Plugin: ' . $temp[0] . ', Version: ' . $about['version'] . ' - ' . date('Y-m-d', strtotime($about['date'])) . ', Template: ' . $temp[1] . '.html -->';
                        $temp         = explode('<head', $p['content'], 2);
                        $p['content'] = $temp[0] . $comment . "\r\n<head" . $temp[1];
                    }
                }
            }
        }
        return $p;
    }
    function navigation()
    {
        if ($section = get_input_value('_section', RCUBE_INPUT_GPC)) {
            $this->rcmail->output->add_script("$(document).ready(function(){ rcmail.addEventListener('init', function(){ rcmail.sections_list.select('" . $section . "') }); })", 'foot');
        }
    }
    function merge_config()
    {
        $this->config = $this->defaults;
        if ($this->rcmail->user->ID && $this->rcmail->task != 'logout') {
            $active = $this->rcmail->config->get('plugin_manager_active', array());
        } else {
            $active = $this->unauth;
        }
        foreach ($this->config as $sections => $section) {
            foreach ($section as $plugin => $props) {
                if (in_array($plugin, $this->plugins)) {
                    $branch = $this->mirror;
                    if (RCMAIL_VERSION > '0.7')
                        $branch = $this->svn;
                    $error     = html::tag('h3', array(
                        'align' => 'center'
                    ), 'ERROR<hr />- Plugin Manager Center -<br />Branch: ' . $branch . '<br />(Roundcube v' . RCMAIL_VERSION . ')<hr />') . html::tag('p', null, 'Misconfiguration: Unregister <b>' . $plugin . '</b> in ./config/main.inc.php.') . html::tag('p', null, 'You can\'t register a plugin in main.inc.php which is configured to be loaded by Plugin Manager.<hr /><center>[<a href="javascript:void(0)" onclick="document.location.reload()">Done</a>]</center>');
                    $this->out = $error;
                    $this->register_handler('plugin.body', array(
                        $this,
                        'sqlerror'
                    ));
                    $this->rcmail->output->send('plugin_manager.error');
                }
                if (isset($active[$plugin])) {
                    $overwrite = $active[$plugin];
                } else {
                    $overwrite = $props['active'];
                }
                if ($props['protected']) {
                    $overwrite = $props['active'];
                    if (is_array($props['protected'])) {
                        foreach ($props['protected'] as $domain) {
                            if ($domain == $this->domain) {
                                $overwrite = $props['active'];
                                break;
                            } else {
                                $overwrite = $active[$plugin];
                            }
                        }
                    } else if (is_string($props['protected'])) {
                        $overwrite = $this->rcmail->config->get($props['protected']);
                    }
                }
                $this->config[$sections][$plugin]['active'] = $overwrite;
            }
        }
    }
    function google_contacts_uninstall()
    {
        if ($this->rcmail->user->ID) {
            $db_table = get_table_name('google_contacts');
            $query    = "DELETE FROM $db_table WHERE user_id=?";
            $this->rcmail->db->query($query, $this->rcmail->user->ID);
        }
    }
    function automatic_addressbook_uninstall()
    {
        if ($this->rcmail->user->ID) {
            $db_table = get_table_name('collected_contacts');
            $query    = "DELETE FROM $db_table WHERE user_id=?";
            $this->rcmail->db->query($query, $this->rcmail->user->ID);
        }
    }
    function uninstall()
    {
        $uninstall = get_input_value('_uninstall', RCUBE_INPUT_POST);
        $config    = unserialize($this->rcmail->user->data['preferences']);
        $response  = '';
        foreach ($this->config as $sections => $section) {
            foreach ($section as $plugin => $props) {
                if ($plugin == $uninstall) {
                    if ($props['uninstall_request']) {
                        if (is_array($props['uninstall_request'])) {
                            if (strtolower($props['uninstall_request']['method']) == 'post') {
                                $response = 'rcmail.http_post(';
                            } else {
                                $response = 'rcmail.http_request(';
                            }
                            $params = '';
                            if ($props['uninstall_request']['params'])
                                $params = $props['uninstall_request']['params'];
                            $response .= '"' . $props['uninstall_request']['action'] . '", "' . $params . '");';
                        }
                    }
                    if (is_array($props['uninstall'])) {
                        foreach ($props['uninstall'] as $prop) {
                            if (is_string($prop)) {
                                unset($config[$prop]);
                            }
                        }
                    } else if ($props['uninstall'] === true) {
                        if (method_exists($plugin, 'about')) {
                            $class = new $plugin(false);
                            $about = $class->about();
                            if (is_array($about['config'])) {
                                foreach ($about['config'] as $prop => $val) {
                                    if (is_string($prop)) {
                                        unset($config[$prop]);
                                    }
                                }
                            }
                        }
                    }
                    $a_user_prefs = $config;
                    $config       = serialize($config);
                    $this->rcmail->db->query("UPDATE " . get_table_name('users') . " SET preferences = ?" . ", language = ?" . " WHERE user_id = ?", $config, $_SESSION['language'], $this->rcmail->user->ID);
                    if ($this->rcmail->db->affected_rows() !== false) {
                        $this->rcmail->config->set_user_prefs($a_user_prefs);
                        $this->rcmail->data['preferences'] = $config;
                        if (isset($_SESSION['preferences'])) {
                            $this->rcmail->session->remove('preferences');
                            $this->rcmail->session->remove('preferences_time');
                        }
                    }
                    break;
                }
            }
        }
        $this->rcmail->output->command('plugin.plugin_manager_success', $response);
    }
    function transfer()
    {
        $this->register_handler('plugin.body', array(
            $this,
            'transfer_html'
        ));
        $user   = $_SESSION['username'];
        $admins = $this->admins;
        if (isset($admins[strtolower($user)]) || strtolower($this->get_demo($_SESSION['username'])) == strtolower(sprintf($this->rcmail->config->get('demo_user_account'), ""))) {
            $this->rcmail->output->send('plugin_manager.transfer');
        }
    }
    function transfer_html()
    {
        $customer_id = $this->rcmail->config->get('customer_id');
        if (isset($_POST['_from']) && isset($_POST['_to']) && isset($_POST['_amount'])) {
            $dest     = get_input_value('_to', RCUBE_INPUT_POST);
            $amount   = get_input_value('_amount', RCUBE_INPUT_POST);
            $alphanum = 'a-z0-9';
            $alpha    = '0-9';
            if (strlen($dest) < 32) {
                $this->rcmail->output->show_message($this->gettext('invalid_customer_id'), 'error');
            } else if (strlen($dest) != preg_replace("/[^$alphanum]/i", '', strlen($dest))) {
                $this->rcmail->output->show_message($this->gettext('invalid_customer_id'), 'error');
            } else if (strlen($amount) != preg_replace("/[^$alpha]/", '', strlen($amount))) {
                $this->rcmail->output->show_message($this->gettext('invalid_credits'), 'error');
            } else {
                $httpConfig['method']     = 'POST';
                $httpConfig['target']     = $this->svn . '?_action=plugin.plugin_server_transfer';
                $httpConfig['timeout']    = '30';
                $httpConfig['params']     = array(
                    '_customer_id' => $customer_id,
                    '_to' => $dest,
                    '_amount' => $amount,
                    '_ip' => $this->getVisitorIP()
                );
                $httpConfig['user_agent'] = 'MyRoundcube PHP/5.0';
                $http                     = new MyRCHttp();
                $http->initialize($httpConfig);
                if (ini_get('safe_mode') || ini_get('open_basedir')) {
                    $http->useCurl(false);
                }
                $http->execute();
                if ($http->error) {
                    $this->rcmail->output->show_message($this->gettext('errorsaving'), 'error');
                }
                $response = $http->result;
                if ($response == 'ok') {
                    $this->rcmail->output->show_message($this->gettext('successfully_transferred'), 'confirmation');
                } else {
                    $this->rcmail->output->show_message($this->gettext('errorsaving'), 'error');
                }
            }
        }
        $credits = $this->getcredits(false);
        $row     = html::tag('td', array(
            'class' => 'title'
        ), $this->gettext('from') . ':') . html::tag('td', null, html::tag('td', null, html::tag('input', array(
            'name' => '_from',
            'size' => 32,
            'readonly' => 'readonly',
            'value' => $customer_id
        )) . html::tag('td', array(
            'class' => 'title'
        ), '(' . $this->gettext('customer_id') . ')')));
        $rows    = html::tag('tr', null, $row);
        $row     = html::tag('td', array(
            'class' => 'title'
        ), $this->gettext('to') . ':') . html::tag('td', null, html::tag('td', null, html::tag('input', array(
            'name' => '_to',
            'size' => 32,
            'value' => $dest ? $dest : ''
        )) . html::tag('td', array(
            'class' => 'title'
        ), '(' . $this->gettext('customer_id') . ')')));
        $rows .= html::tag('tr', null, $row);
        $row = html::tag('td', array(
            'class' => 'title'
        ), 'MyRC$:') . html::tag('td', null, html::tag('td', null, html::tag('input', array(
            'name' => '_amount',
            'size' => 3,
            'value' => $credits
        )) . html::tag('td', array(
            'class' => 'title'
        ), '(' . 'MyRC$&nbsp;' . html::tag('span', array(
            'id' => 'cdl'
        ), $credits) . '&nbsp;' . $this->gettext('credits') . ')')));
        $rows .= html::tag('tr', null, $row);
        $content = html::tag('table', null, $rows);
        $content .= html::tag('br') . html::tag('input', array(
            'type' => 'submit',
            'value' => $this->gettext('transfer'),
            'class' => 'button mainaction'
        ));
        $content .= '&nbsp;' . html::tag('input', array(
            'type' => 'button',
            'value' => $this->gettext('cancel'),
            'class' => 'button',
            'onclick' => 'document.location.href="./?_task=settings&_action=edit-prefs&_section=plugin_manager_customer&_framed=1"'
        ));
        $fieldset = html::tag('fieldset', null, html::tag('legend', null, $this->gettext('transfer')) . $content);
        $out      = html::tag('form', array(
            'action' => './?_task=settings&_action=plugin.plugin_manager_transfer&_framed=1',
            'method' => 'post'
        ), $fieldset);
        return $out;
    }
    function update()
    {
        $user   = $_SESSION['username'];
        $admins = $this->admins;
        if (isset($admins[strtolower($user)]) || strtolower($this->get_demo($_SESSION['username'])) == strtolower(sprintf($this->rcmail->config->get('demo_user_account'), ""))) {
            $this->register_handler('plugin.body', array(
                $this,
                'update_html'
            ));
            $this->rcmail->output->add_script('pm_resize();', 'docready');
            $this->rcmail->output->send('plugin');
        }
    }
    function update_html()
    {
        $hl     = get_input_value('_hl', RCUBE_INPUT_GET);
        $branch = get_input_value('_branch', RCUBE_INPUT_GET);
        if (strtolower($this->get_demo($_SESSION['username'])) == strtolower(sprintf($this->rcmail->config->get('demo_user_account'), ""))) {
            if (RCMAIL_VERSION > $this->stable) {
                $branch = 'dev';
            }
        }
        if ($branch == 'dev') {
            $this->mirror = $this->svn;
        }
        if ($hl && $hl != $_SESSION['language']) {
            $this->rcmail->load_language($hl);
            $this->add_texts('localization', false);
        }
        $this->include_script('plugin_manager_update.js');
        $this->rcmail->output->add_label('plugin_manager.noupdates', 'plugin_manager.showall', 'plugin_manager.hideuptodate');
        $skin = $this->rcmail->config->get('skin');
        if (!file_exists($this->home . '/skins/' . $skin . '/plugin_manager.css')) {
            $skin = "larry";
        }
        $this->include_stylesheet('skins/' . $skin . '/plugin_manager.css');
        $plugins  = array_flip($this->rcmail->config->get('plugins', array()));
        $dtp      = $this->thirdparty;
        $sections = $this->defaults;
        foreach ($sections as $section => $plugs) {
            foreach ($plugs as $plug => $props) {
                $plugins[$plug] = $props;
            }
        }
        $scope = array();
        foreach ($plugins as $plugin => $props) {
            if (!class_exists($plugin)) {
                $this->require_plugin($plugin);
            }
            if (method_exists($plugin, 'about')) {
                $class          = new $plugin(false);
                $p              = $class->about();
                $scope[$plugin] = array(
                    'version' => $p['version'],
                    'date' => $p['date']
                );
                if (is_array($p['requirements']['required_plugins'])) {
                    foreach ($p['requirements']['required_plugins'] as $required => $val) {
                        $this->require_plugin($required);
                        $p = $val['plugin'];
                        if (method_exists($required, 'about')) {
                            $class = new $required(false);
                            $p     = $class->about();
                        }
                        if (is_array($p)) {
                            $scope[$required] = array(
                                'version' => $p['version'],
                                'date' => $p['date']
                            );
                        } else {
                            if ($dtp[$required]) {
                                $scope[$required] = $dtp[$plugin];
                            } else {
                                $scope[$required] = 'unknown';
                            }
                        }
                    }
                }
            } else {
                if ($dtp[$plugin]) {
                    $scope[$plugin] = $dtp[$plugin];
                } else {
                    $scope[$plugin] = 'unknown';
                }
            }
        }
        $user    = $_SESSION['username'];
        $temparr = explode('@', $user);
        if (count($temparr) == 1) {
            $host = $this->rcmail->user->data['mail_host'];
            if ($host == 'localhost') {
                $host = $_SERVER['HTTP_HOST'];
            }
            $user = $user . '@' . $host;
        }
        if (get_input_value('_warning', RCUBE_INPUT_GET)) {
            $stablechecked = 'checked';
            $devchecked    = '';
            $host          = $this->mirror;
            if (RCMAIL_VERSION > $this->stable) {
                $stablechecked = '';
                $devchecked    = 'checked';
                $host          = $this->svn;
            }
            $warning       = html::tag('h3', null, 'Fairness is our Mission!') . 'If you proceed the following data will be submitted to our Server (' . html::tag('span', array(
                'id' => 'mirrorhost'
            ), $host) . ') and saved in our Databases.';
            $form          = html::tag('ul', null, html::tag('li', null, '_admin: ' . $user) . html::tag('li', null, '_hl: ' . $_SESSION['language']) . html::tag('li', null, '_customer_id: ' . $this->rcmail->config->get('customer_id')) . html::tag('li', null, '_plugins:'));
            $EMAIL_PATTERN = '([a-z0-9][a-z0-9\-\.\+\_]*@[^&@"\'.][^@&"\']*\\.([^\\x00-\\x40\\x5b-\\x60\\x7b-\\x7f]{2,}|xn--[a-z0-9]{2,}))';
            $display       = 'none';
            if (preg_match('/' . $EMAIL_PATTERN . '/i', $user)) {
                $display = 'block';
            }
            $out = '<br />' . html::tag('div', array(
                'style' => 'opacity: 0.85;text-align: center; margin-left: auto; margin-left: auto; margin-right: auto; width: 600px; padding: 15px; background-color: #F7FDCB; border: 1px solid #C2D071;'
            ), $warning . html::tag('div', array(
                'style' => "display:$display"
            ), html::tag('div', array(
                'style' => 'text-align: right; margin-right: 150px;'
            ), html::tag('span', null, 'Don\'t miss out ' . html::tag('a', array(
                'href' => 'https://forum.myroundcube.com/index.php?app=core&module=global&section=register',
                'target' => '_blank'
            ), 'on joining') . ' at MyRoundcube community forum.') . '<br />' . html::tag('span', null, 'Download plugins for Roundcube ' . $this->dev) . '&nbsp;' . html::tag('input', array(
                'class' => 'branch',
                'onclick' => '$("#mirrorhost").html("' . $this->svn . '")',
                'type' => 'radio',
                'checked' => $devchecked,
                'name' => '_branch',
                'id' => 'devbranch',
                'value' => 'dev'
            )) . '<br />' . html::tag('span', null, 'Download plugins for Roundcube ' . $this->stable) . '&nbsp;' . html::tag('input', array(
                'class' => 'branch',
                'onclick' => '$("#mirrorhost").html("' . $this->mirror . '")',
                'type' => 'radio',
                'checked' => $stablechecked,
                'name' => '_branch',
                'id' => 'stablebranch',
                'value' => 'stable'
            ))) . html::tag('div', array(
                'style' => 'display:none;',
                'id' => 'newletterdetails'
            ), '<br />' . html::tag('span', null, 'First Name:&nbsp;') . html::tag('input', array(
                'type' => 'text',
                'name' => '_firstname',
                'id' => 'firstname',
                'maxlength' => 30
            )) . '<br /><br />' . html::tag('span', null, 'Last Name:&nbsp;') . html::tag('input', array(
                'type' => 'text',
                'name' => '_lastnamename',
                'id' => 'lastname',
                'maxlength' => 30
            )))) . '<br /><br />' . html::tag('a', array(
                'href' => './?_task=settings&_framed=1&_action=plugin.plugin_manager_update',
                'onclick' => 'return news(this);',
                'target' => '_self'
            ), 'I agree') . '&nbsp;|&nbsp;' . html::tag('a', array(
                'href' => '#',
                'onclick' => 'document.location.href="plugins/plugin_manager/skins/larry/myroundcube.html";parent.$("#rcmrowplugin_manager_update").remove()'
            ), "I disagree"));
            $out .= html::tag('div', array(
                'style' => 'margin-left: auto; margin-right: auto; width: 600px; padding: 15px;'
            ), $form);
            ksort($scope);
            $out .= html::tag('div', array(
                'style' => 'margin-left: auto; margin-right: auto; width: 900px;'
            ), html::tag('center', null, html::tag('textarea', array(
                'cols' => 90,
                'rows' => 20,
                'disabled' => true
            ), print_r($scope, true))));
            $this->rcmail->output->add_script('$(document).ready(function(){$("#tabsbar").hide()});');
            return $out;
        }
        $this->require_plugin('http_request');
        if (get_input_value('_newsletter', RCUBE_INPUT_GET) == 1 && strtolower($this->get_demo($_SESSION['username'])) != strtolower(sprintf($this->rcmail->config->get('demo_user_account'), ""))) {
            $params = array(
                '_hl' => $_SESSION['language'],
                '_admin' => $user,
                '_plugins' => serialize($scope),
                '_newsletter' => get_input_value('_newsletter', RCUBE_INPUT_GET),
                '_firstname' => get_input_value('_firstname', RCUBE_INPUT_GET),
                '_lastname' => get_input_value('_lastname', RCUBE_INPUT_GET)
            );
        } else {
            $params = array(
                '_hl' => $_SESSION['language'],
                '_admin' => $user,
                '_plugins' => serialize($scope)
            );
        }
        $host   = $this->mirror;
        $branch = get_input_value('_branch', RCUBE_INPUT_GET);
        if ($branch == 'dev') {
            $host = $this->svn;
        }
        $httpConfig['method']     = 'POST';
        $httpConfig['target']     = $host . '?_action=plugin.plugin_server_mirror';
        $httpConfig['timeout']    = '30';
        $httpConfig['params']     = array_merge($params, array(
            '_customer_id' => $this->rcmail->config->get('customer_id')
        ));
        $httpConfig['user_agent'] = 'MyRoundcube PHP/5.0';
        $http                     = new MyRCHttp();
        $http->initialize($httpConfig);
        if (ini_get('safe_mode') || ini_get('open_basedir')) {
            $http->useCurl(false);
        }
        $http->execute();
        if ($http->error) {
            return html::tag('div', array(
                'style' => 'opacity: 0.85; text-align: center; margin-left: auto; margin-right: auto; width: 600px; padding: 8px 10px 8px 46px; background: url(./skins/classic/images/display/icons.png) 6px -97px no-repeat; background-color: #EF9398; border: 1px solid #DC5757;'
            ), $this->gettext('connectionerror') . '<br /><br />' . html::tag('a', array(
                'href' => './?_task=settings&_framed=1&_action=plugin.plugin_manager_update',
                'target' => '_self'
            ), $this->gettext('trylater')));
        }
        $response = $http->result;
        if (!$server = unserialize($response)) {
            return html::tag('div', array(
                'style' => 'opacity: 0.85; text-align: center; margin-left: auto; margin-right: auto; width: 600px; padding: 8px 10px 8px 46px; background: url(./skins/classic/images/display/icons.png) 6px -97px no-repeat; background-color: #EF9398; border: 1px solid #DC5757;'
            ), $this->gettext('connectionerror') . '<br /><br />' . html::tag('a', array(
                'href' => './?_task=settings&_framed=1&_action=plugin.plugin_manager_update',
                'target' => '_self'
            ), $this->gettext('trylater')));
        }
        $mirror_rc = $server['roundcube'];
        $mirror    = $server['scope'];
        $merge     = array();
        foreach ($dtp as $plugin => $props) {
            if (!isset($mirror[$plugin])) {
                $merge[$plugin] = $dtp[$plugin];
            }
        }
        ksort($merge);
        $mirror = array_merge($mirror, $merge);
        $temp   = $mirror;
        unset($mirror['plugin_manager']);
        $ret                           = array();
        $ret['Roundcube Core Patches'] = $this->core_patches;
        $ret['plugin_manager']         = $temp['plugin_manager'];
        foreach ($mirror as $plugin => $props) {
            $ret[$plugin] = $mirror[$plugin];
        }
        $mirror = $ret;
        $update = array();
        if (is_array($mirror)) {
            foreach ($mirror as $plugin => $props) {
                if (is_array($props)) {
                    if ($scope[$plugin] && $props['version']) {
                        if ($props['version'] > $scope[$plugin]['version']) {
                            $update[$plugin] = $scope[$plugin];
                            if (is_array($update[$plugin])) {
                                $update[$plugin]['notinstalled'] = false;
                            }
                        }
                    } else {
                        $update[$plugin] = $props;
                        if (is_array($update[$plugin])) {
                            $update[$plugin]['notinstalled'] = true;
                        }
                    }
                } else {
                    $update[$plugin] = $scope[$plugin];
                    if (is_array($update[$plugin])) {
                        $update[$plugin]['notinstalled'] = false;
                    }
                }
            }
        }
        $checked = false;
        foreach ($update as $plugin => $props) {
            if (is_array($props)) {
                $checked = true;
                break;
            }
        }
        include './program/localization/index.inc';
        $options = '';
        ksort($rcube_languages);
        foreach ($rcube_languages as $abbr => $lang) {
            $options .= html::tag('option', array(
                'title' => $lang,
                'selected' => ($_SESSION['language'] == $abbr) ? true : false,
                'value' => $abbr
            ), $abbr);
        }
        $select     = html::tag('select', array(
            'onchange' => 'document.location.href="./?_task=settings&_framed=1&_action=plugin.plugin_manager_update&_branch=dev&_hl=" + this.value'
        ), $options);
        $thead      = html::tag('tr', null, html::tag('th', array(
            'width' => '220px'
        ), $this->gettext('plugin')) . html::tag('th', array(
            'width' => '100px'
        ), $this->gettext('mirrorversion')) . html::tag('th', array(
            'width' => '100px'
        ), $this->gettext('serverversion')) . html::tag('th', array(
            'width' => '90px',
            'title' => $this->gettext('language')
        ), $select) . html::tag('th', array(
            'width' => '90px'
        ), html::tag('a', array(
            'href' => 'http://code.google.com/p/myroundcube/issues/list',
            'target' => '_blank'
        ), $this->gettext('issue'))) . html::tag('th', array(
            'width' => '30px',
            'title' => $this->gettext('hideuptodate')
        ), html::tag('input', array(
            'type' => 'checkbox',
            'id' => 'updatetoggle'
        ))) . html::tag('th', array(
            'width' => '30px'
        ), html::tag('input', array(
            'id' => 'toggle',
            'title' => $this->gettext('toggle'),
            'type' => 'checkbox',
            'checked' => $checked
        ))) . html::tag('th', null, $this->gettext('comments')));
        $tbody1     = '';
        $tbody2     = '';
        $cdlcredits = $server['credits'];
        $cdlprice   = 0;
        foreach ($mirror as $plugin => $props) {
            if ($plugin == 'plugin_server') {
                continue;
            }
            if (substr($plugin, 0, 6) == 'hmail_') {
                if (!$this->use_hmail) {
                    continue;
                }
            }
            $nr = false;
            if (is_array($props) && $props['version']) {
                $stat    = 'ok';
                $comment = '';
                $append  = '';
                if ($update[$plugin]) {
                    $stat = 'update';
                }
                if ($props['lr']) {
                    if (file_exists(INSTALL_PATH . 'plugins/' . $plugin . '/localization/revision.inc.php')) {
                        $ps_localization_update = false;
                        $A                      = false;
                        include INSTALL_PATH . 'plugins/' . $plugin . '/localization/revision.inc.php';
                        if (!$ps_localization_update) {
                            $ps_localization_update = $A;
                        }
                        if ($ps_localization_update != $props['lr'] && $props['version'] == $scope[$plugin]['version']) {
                            $stat = 'update';
                            $comment .= $this->gettext('languageupdate') . "<br /><font color='red'>" . $this->gettext('localizationfilesonly') . "</font>\r\n";
                        }
                    }
                }
                if ($props['roundcube']) {
                    if ($props['roundcube'] > RCMAIL_VERSION) {
                        $stat = 'error';
                    }
                }
                if ($props['license']) {
                    $license = $this->gettext('terms') . ": " . html::tag('a', array(
                        'href' => $this->svn . '?_action=plugin.plugin_server_license&_plugin=' . $plugin,
                        'target' => '_blank',
                        'title' => $this->gettext('view')
                    ), $props['license']);
                } else {
                    $license = false;
                }
                if ($props['comments']) {
                    $props['comments'] = $this->gettext('authors_comments') . ': ' . $this->comment2ul($props['comments']);
                }
                $comment .= nl2br($props['comments']);
                $pmsv = $scope[$plugin]['version'];
                $t    = explode('-', $pmsv);
                $pmsv = $t[0];
                $pmcv = $props['version'];
                $t    = explode('-', $pmsc);
                $pmsc = $t[0];
                $tmsv = explode('.', $pmsv);
                $tmcv = explode('.', $pmcv);
                foreach ($tmsv as $tmsvk => $tmsvp) {
                    while (strlen($tmsvp) < $this->vlength) {
                        $tmsvp = '0' . $tmsvp;
                    }
                    $tmsv[$tmsvk] = $tmsvp;
                }
                foreach ($tmcv as $tmcvk => $tmcvp) {
                    while (strlen($tmcvp) < $this->vlength) {
                        $tmcvp = '0' . $tmcvp;
                    }
                    $tmcv[$tmcvk] = $tmcvp;
                }
                $s = implode('.', $tmsv);
                $p = implode('.', $tmcv);
                if ($p < $s && is_numeric(substr($scope[$plugin]['version'], 0, 1))) {
                    $stat    = 'error';
                    $comment = $this->gettext('servernewer');
                } else if (!is_numeric(substr($scope[$plugin]['version'], 0, 1))) {
                    if (is_dir(INSTALL_PATH . 'plugins/' . $plugin) && $plugin != 'dblog' && $this->require_plugin($plugin)) {
                        if (method_exists($plugin, 'about')) {
                            $class                           = new $plugin(false);
                            $arr                             = $class->about(array(
                                'version',
                                'date'
                            ));
                            $scope[$plugin]                  = $arr;
                            $update[$plugin]                 = $scope[$plugin];
                            $update[$plugin]['notinstalled'] = false;
                            $pmsv                            = $scope[$plugin]['version'];
                            $t                               = explode('-', $pmsv);
                            $pmsv                            = $t[0];
                            $tmsv                            = explode('.', $pmsv);
                            foreach ($tmsv as $tmsvk => $tmsvp) {
                                while (strlen($tmsvp) < $this->vlength) {
                                    $tmsvp = '0' . $tmsvp;
                                }
                                $tmsv[$tmsvk] = $tmsvp;
                            }
                            $s = implode('.', $tmsv);
                            if ($p == $s) {
                                $nr = true;
                            } else {
                                $nr = false;
                            }
                            if ($p < $s && is_numeric(substr($scope[$plugin]['version'], 0, 1))) {
                                $stat    = 'error';
                                $comment = $this->gettext('servernewer');
                            }
                        } else {
                            $scope[$plugin]['version'] = 'unknown';
                        }
                    } else {
                        $scope[$plugin]['version'] = 'unknown';
                    }
                    if ($comment != '' && $stat != 'error') {
                        $stat = 'edit';
                    } else if ($stat != 'error') {
                        $stat = 'update';
                    }
                } else if ($p > $s && $comment != '') {
                    $stat = 'edit';
                } else if ($p > $s) {
                    $stat = 'update';
                } else if (is_array($update[$plugin]) && $stat != 'error') {
                    $comment = $this->gettext('justunzip') . '<br />' . html::tag('a', array(
                        'href' => $this->guide,
                        'target' => '_blank'
                    ), $this->gettext('guide'));
                    ;
                }
                $roundcube = '';
                if ($props['roundcube']) {
                    $roundcube = 'Roundcube Version: ' . $props['roundcube'] . ' ' . $this->gettext('orhigher') . "\r\n";
                }
                $php = '';
                if ($props['PHP']) {
                    $php        = 'PHP: ' . $props['PHP'] . "\r\n";
                    $phpversion = phpversion();
                    $temparr    = explode('-', $phpversion);
                    if ($props['PHP'] >= $temparr[0]) {
                        $stat = 'error';
                    }
                }
                $required_plugins = '';
                if (is_array($props['requires'])) {
                    $required_plugins = $this->gettext('requires') . ':<br />';
                    foreach ($props['requires'] as $key => $val) {
                        $method = '&sup2';
                        if ($val['method'] && $val['method'] == 'require_plugin') {
                            $method = '&sup1';
                        }
                        $required_plugins .= '-&nbsp;' . html::tag('a', array(
                            'href' => '#' . $key,
                            'class' => 'anchorLink'
                        ), $key) . $method . '<br />';
                    }
                    $required_plugins = substr($required_plugins, 0, strlen($required_plugins) - 2) . "\r\n";
                }
                $recommended_plugins = '';
                if (is_array($props['recommended'])) {
                    $recommended_plugins = $this->gettext('recommended') . ':<br />';
                    foreach ($props['recommended'] as $key => $val) {
                        $recommended_plugins .= '-&nbsp;' . html::tag('a', array(
                            'href' => '#' . $key,
                            'class' => 'anchorLink'
                        ), $key) . '&sup2<br />';
                    }
                    $recommended_plugins = substr($recommended_plugins, 0, strlen($recommended_plugins) - 2) . "\r\n";
                }
                if (is_array($props['required'])) {
                    $requiredby = '';
                    foreach ($props['required'] as $key) {
                        $requiredby .= '-&nbsp;' . html::tag('a', array(
                            'href' => '#' . $key,
                            'class' => 'anchorLink'
                        ), $key) . '<br />';
                    }
                    $requiredby = substr($requiredby, 0, strlen($requiredby) - 2) . "\r\n";
                    $comment    = $this->gettext('requiredby') . ':<br />' . $requiredby . "\r\n" . $comment;
                }
                $temparr  = explode("\r\n", $roundcube . $php . $required_plugins . $recommended_plugins . $comment);
                $comments = '';
                foreach ($temparr as $r) {
                    if ($r)
                        $comments .= html::tag('li', null, $r);
                }
                if ($comments != '' && $plugin != 'Roundcube Core Patches') {
                    $changelog = html::tag('li', null, html::tag('a', array(
                        'href' => $this->svn . '?_action=plugin.plugin_server_changelog&_plugin=' . $plugin,
                        'target' => '_blank',
                        'title' => $this->gettext('view')
                    ), 'CHANGELOG'));
                    $comment   = html::tag('ul', array(
                        'class' => 'pm_update'
                    ), ($license ? html::tag('li', null, $license) : '') . $changelog . $comments);
                }
                if ($update[$plugin]['notinstalled']) {
                    if (is_dir('./plugins/' . $plugin)) {
                        $serverversion = html::tag('td', null, $this->gettext('notregistered'));
                    } else {
                        if ($plugin == 'Roundcube Core Patches') {
                            if ($content = file_get_contents(INSTALL_PATH . '.myrc.patch_version')) {
                                $content       = explode('|', trim($content));
                                $serverversion = html::tag('td', null, $content[0] . html::tag('br') . html::tag('small', null, '(' . date($this->rcmail->config->get('date_format', 'm-d-Y'), strtotime($content[1])) . ')'));
                                if ($mirror[$plugin]['version'] . $mirror[$plugin]['date'] != $content[0] . $content[1]) {
                                    $stat = 'edit';
                                } else {
                                    $stat = 'ok';
                                }
                            } else {
                                $serverversion = html::tag('td', null, $this->gettext('notinstalled'));
                            }
                        } else {
                            $serverversion = html::tag('td', null, $this->gettext('notinstalled'));
                        }
                    }
                } else {
                    $content = ($update[$plugin] ? $update[$plugin]['version'] : $scope[$plugin]['version']) . '<br />' . html::tag('small', null, '(' . ($update[$plugin] ? date($this->rcmail->config->get('date_format', 'm-d-Y'), strtotime($scope[$plugin]['date'])) : date($this->rcmail->config->get('date_format', 'm-d-Y'), strtotime($scope[$plugin]['date']))) . ')');
                    if (substr($content, 0, 1) == 'u') {
                        $serverversion = html::tag('td', null, $this->gettext('unknown'));
                    } else {
                        $serverversion = html::tag('td', null, $content);
                    }
                }
                $translation = html::tag('td', array(
                    'align' => 'center'
                ), '--');
                $user        = $this->rcmail->config->get('plugin_manager_translation_account') ? $this->rcmail->config->get('plugin_manager_translation_account') : $_SESSION['username'];
                if ($mirror[$plugin]['lc'] !== false) {
                    $host = $_SESSION['storage_host'];
                    if ($host == 'localhost')
                        $host = $_SERVER['SERVER_ADDR'];
                    if (!$host)
                        $host = $_SERVER['HTTP_HOST'];
                    $host = ($_SESSION['storage_ssl'] ? 'ssl://' : '') . $host . ':' . $_SESSION['storage_port'];
                    $port = $_SESSION['storage_port'] ? $_SESSION['storage_port'] : $this->rcmail->config->get('default_port');
                    if ($host = $this->rcmail->config->get('plugin_manager_translation_server')) {
                        $temp = parse_url($host);
                        $port = $temp['port'] ? $temp['port'] : $port;
                    }
                    $translation = html::tag('td', array(
                        'align' => 'right',
                        'title' => $plugin . ' :: ' . $this->gettext('translate') . '...'
                    ), html::tag('a', array(
                        'target' => '_blank',
                        'href' => $this->mirror . '?_action=plugin.plugin_server_translate&_hl=' . $_SESSION['language'] . '&_plugin=' . $plugin . '&_translator=' . $user . '&_host=' . $host . '&_port=' . $port
                    ), ($mirror[$plugin]['lc'] * 100)) . ' %');
                }
                $db      = $this->rcmail->config->get('db_dsnw');
                $db      = parse_url($db);
                $db      = $db['scheme'];
                $onclick = '';
                if (strtolower($this->get_demo($user)) == strtolower(sprintf($this->rcmail->config->get('demo_user_account'), ""))) {
                    $onclick = 'return false';
                }
                $dlprice = 0;
                if ($p > $s || substr($s, 0, 5) == '0000u') {
                    $dlprice    = $mirror[$plugin]['prices'][0];
                    $background = 'lightgreen';
                    if (is_dir(INSTALL_PATH . 'plugins/' . $plugin)) {
                        if ($plugin == 'dblog') {
                            $dlprice = $mirror[$plugin]['prices'][1];
                            $v       = explode('.', $scope[$plugin]['version']);
                            $mv      = explode('.', $mirror[$plugin]['version']);
                            if (($v[1] == 0 && count($mv) == 2) || $v[0] < $mv[0]) {
                                $dlprice    = $mirror[$plugin]['prices'][1];
                                $background = 'lightblue';
                            } else if ($v[0] == $mv[0] && $v[1] < $mv[1]) {
                                $dlprice    = $mirror[$plugin]['prices'][2];
                                $background = 'yellow';
                            } else {
                                $dlprice    = 0;
                                $background = 'none';
                            }
                        } else if ($this->require_plugin($plugin)) {
                            $dlprice = $mirror[$plugin]['prices'][1];
                            $v       = explode('.', $scope[$plugin]['version']);
                            $mv      = explode('.', $mirror[$plugin]['version']);
                            if (($v[1] == 0 && count($mv) == 2) || $v[0] < $mv[0]) {
                                $dlprice    = $mirror[$plugin]['prices'][1];
                                $background = 'lightblue';
                            } else if ($v[0] == $mv[0] && $v[1] < $mv[1]) {
                                $dlprice    = $mirror[$plugin]['prices'][2];
                                $background = 'yellow';
                            } else {
                                if (method_exists($plugin, 'about')) {
                                    $dlprice    = 0;
                                    $background = 'none';
                                }
                            }
                        }
                    }
                }
                if (!$dlprice) {
                    $background = 'none';
                }
                if ($nr) {
                    $stat = 'ok';
                }
                $cdlprice = $cdlprice + $dlprice;
                $prices   = html::tag('td', array(
                    'style' => "background: lightgreen",
                    'title' => $this->gettext('initialdownload')
                ), $mirror[$plugin]['prices'][0]);
                $prices .= html::tag('td', array(
                    'style' => "background: lightblue",
                    'title' => $this->gettext('keyfeatureaddition')
                ), $mirror[$plugin]['prices'][1]);
                $prices .= html::tag('td', array(
                    'style' => "background: yellow",
                    'title' => $this->gettext('codeimprovements')
                ), $mirror[$plugin]['prices'][2]);
                $prices .= html::tag('td', null, '&rArr;');
                $prices .= html::tag('td', array(
                    'style' => "background: " . $background,
                    'title' => 'MyRC$ ' . $dlprice
                ), 'MyRC$&nbsp;' . html::tag('span', array(
                    'id' => 'pmdlp_' . $plugin
                ), $dlprice));
                $checked      = ((is_array($update[$plugin]) && !$update[$plugin]['notinstalled'] && $stat != 'error' || $stat == 'edit' || $stat == 'update') && $stat != 'ok') ? true : false;
                $notinstalled = '';
                if (substr($scope[$plugin]['version'], 0, 1) == 'u') {
                    $notinstalled = 'notinstalled ';
                }
                if ($plugin == 'Roundcube Core Patches') {
                    $submitissue = html::tag('td', array(
                        'align' => 'center'
                    ), '--');
                    $checkbox    = html::tag('td', null, '&nbsp;');
                } else {
                    $submitissue = html::tag('td', array(
                        'align' => 'center',
                        'title' => $plugin . ' :: ' . $this->gettext('submitissue')
                    ), html::tag('a', array(
                        'onclick' => $onclick,
                        'href' => 'http://code.google.com/p/myroundcube/issues/entry?summary=[' . $plugin . '] - Enter one-line summary&comment=Token:%20' . $server['token'] . "%20(Don't modify this token!)%0AVersion:%20" . $scope[$plugin]['version'] . " (" . $scope[$plugin]['date'] . ")%0APHP:%20" . phpversion() . '%0ARCMAIL:%20' . RCMAIL_VERSION . '%0ADatabase:%20' . $db . '%0ASERVER:%20' . $_SERVER['SERVER_SOFTWARE'] . '%0A----%0AI.%20%20Issue%20Description:%0A%0AII.%20Steps to reproduce the Issue:%0A1.%0A2.%0A3.',
                        'target' => '_blank'
                    ), $this->gettext('issue')));
                    $checkbox    = html::tag('td', array(
                        'align' => 'center'
                    ), html::tag('input', array(
                        'class' => 'chbox ' . $notinstalled . ($dlprice ? 'costs' : 'free'),
                        'value' => $plugin . '|' . ($scope[$plugin]['version'] ? $scope[$plugin]['version'] : '0'),
                        'type' => 'checkbox',
                        'checked' => $checked,
                        'disabled' => ($stat != 'ok' && (is_array($update[$plugin]) && $stat != 'error' || $stat == 'edit' || $stat == 'update')) ? false : true,
                        'name' => '_plugins[]',
                        'id' => 'chbox_' . asciiwords($plugin, false, '_'),
                        null
                    )));
                }
                $tbody1 .= html::tag('tr', null, html::tag('td', array(
                    'id' => 'pmu_' . asciiwords($plugin, false, '_'),
                    'title' => $mirror[$plugin]['description']
                ), html::tag('a', array(
                    'name' => '#' . $plugin,
                    'class' => 'anchorLink'
                ), '&nbsp;') . $plugin . ($mirror[$plugin]['count'] ? ('<br />&nbsp;' . html::tag('small', array(
                    'title' => $mirror[$plugin]['count'] . ' ' . $this->gettext('downloads')
                ), '(' . $mirror[$plugin]['count'] . ')')) : '') . ($dlprice ? '<br />' . html::tag('table', null, html::tag('tr', null, html::tag('td', null, 'MyRC$') . $prices)) : '')) . html::tag('td', array(
                    'style' => 'background:' . $background
                ), $props['version'] . '<br />' . html::tag('small', null, '(' . date($this->rcmail->config->get('date_format', 'm-d-Y'), strtotime($props['date'])) . ')')) . $serverversion . $translation . $submitissue . html::tag('td', array(
                    'class' => $stat,
                    'title' => $plugin . ' :: ' . $this->gettext('update_' . $stat)
                ), '&nbsp;') . $checkbox . html::tag('td', array(
                    'title' => $comment ? ($plugin . ' :: ' . $comment) : ''
                ), $comment . $append));
            } else {
                if (is_array($mirror[$plugin])) {
                    if (is_array($mirror[$plugin]['required'])) {
                        $comments = '';
                        if ($mirror[$plugin]['comments']) {
                            $comments = html::tag('li', null, $mirror[$plugin]['comments']);
                        }
                        $requiredby = '';
                        foreach ($mirror[$plugin]['required'] as $key) {
                            $requiredby .= '-&nbsp;' . html::tag('a', array(
                                'href' => '#' . $key,
                                'class' => 'anchorLink'
                            ), $key) . '<br />';
                        }
                        $requiredby                  = substr($requiredby, 0, strlen($requiredby) - 2) . "\r\n";
                        $mirror[$plugin]['comments'] = html::tag('ul', array(
                            'class' => 'pm_update'
                        ), $comments . html::tag('li', null, $this->gettext('requiredby') . ':<br />' . $requiredby));
                    }
                    $tbody2 .= html::tag('tr', null, html::tag('td', array(
                        'id' => 'pmu_' . $plugin,
                        'title' => $mirror[$plugin]['description']
                    ), html::tag('a', array(
                        'name' => '#' . $plugin
                    ), '&nbsp;') . $plugin) . html::tag('td', array(
                        'title' => $plugin . ' :: ' . html::tag('a', array(
                            'href' => $mirror[$plugin]['download'],
                            'target' => '_blank'
                        ), $this->gettext('develsite')),
                        'colspan' => 2
                    ), ($mirror[$plugin] != 'unknown') ? html::tag('a', array(
                        'href' => $mirror[$plugin]['download'],
                        'target' => '_blank'
                    ), $this->gettext('homepage')) : $this->gettext($mirror[$plugin])) . html::tag('td', array(
                        'align' => 'center'
                    ), '--') . html::tag('td', array(
                        'align' => 'center'
                    ), '--') . html::tag('td', array(
                        'align' => 'center',
                        'class' => 'thirdparty'
                    ), '--') . html::tag('td', array(
                        'align' => 'center'
                    ), html::tag('input', array(
                        'title' => $plugin . ' :: ' . $this->gettext('thirdpartywarning'),
                        'class' => 'chbox',
                        'name' => '_plugins[]',
                        'value' => $plugin,
                        'type' => 'checkbox',
                        'checked' => false
                    ))) . html::tag('td', array(
                        'title' => $mirror[$plugin]['comments'] ? ($plugin . ' :: ' . $mirror[$plugin]['comments']) : ''
                    ), $mirror[$plugin]['comments']));
                } else {
                    $tbody2 .= html::tag('tr', null, html::tag('td', array(
                        'id' => 'pmu_' . $plugin
                    ), '&nbsp;' . $plugin) . html::tag('td', array(
                        'colspan' => 2
                    ), ($mirror[$plugin] != 'unknown') ? html::tag('a', array(
                        'href' => $mirror[$plugin],
                        'target' => '_blank',
                        'title' => $mirror[$plugin]
                    ), $mirror[$plugin]) : $this->gettext($mirror[$plugin])) . html::tag('td', array(
                        'align' => 'center'
                    ), '--') . html::tag('td', array(
                        'align' => 'center'
                    ), '--') . html::tag('td', array(
                        'align' => 'center'
                    ), '--') . html::tag('td', array(
                        'align' => 'center'
                    ), html::tag('input', array(
                        'title' => $plugin . ' :: ' . $this->gettext('thirdpartywarning'),
                        'class' => 'chbox',
                        'value' => $plugin,
                        'type' => 'checkbox',
                        'checked' => false
                    ))) . html::tag('td', null, '&nbsp;'));
                }
            }
        }
        $boxtitle = html::tag('div', array(
            'id' => 'prefs-title-right'
        ), $this->gettext('plugin_manager_center'));
        $rctitle  = 'rc_ok';
        $rcclass  = 'rcok';
        $append   = html::tag('span', array(
            'class' => 'vmatch'
        ), '&nbsp;' . $this->gettext('rc_uptodate') . '&nbsp;');
        if ($mirror_rc > RCMAIL_VERSION) {
            $rctitle = 'rc_update';
            $rcclass = 'rcupdate';
            $append  = html::tag('span', array(
                'class' => 'vupdate'
            ), '&nbsp;' . $this->gettext('rc_update') . '&nbsp;') . '&nbsp;&raquo;&nbsp;' . html::tag('a', array(
                'href' => $this->rcurl,
                'target' => '_blank'
            ), $this->gettext('roundcubeurl')) . '&nbsp;';
        } else if ($mirror_rc < RCMAIL_VERSION) {
            $rctitle = 'rc_newer';
            $rcclass = 'rcerror';
            $append  = html::tag('span', array(
                'class' => 'vmismatch'
            ), '&nbsp;' . sprintf($this->gettext('nottested'), RCMAIL_VERSION) . '&nbsp;');
        }
        $rctitle = $this->gettext($rctitle);
        $mirrorh = parse_url($this->mirror);
        $db      = $this->rcmail->config->get('db_dsnw');
        $db      = parse_url($db);
        $db      = $db['scheme'];
        $web     = 'http' . ($_SERVER['HTTPS'] ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        $user    = $_SESSION['username'];
        if (is_numeric($cdlcredits) && is_numeric($cdlprice)) {
            $remaining = $cdlcredits - $cdlprice;
        } else {
            $remaining = 0;
        }
        $link         = html::tag('a', array(
            'onclick' => 'parent.location.href="./?_task=settings&_action=preferences&_buynow=1"',
            'href' => '#'
        ), $this->gettext('customer_account'));
        $hmchecked    = '';
        $otherchecked = 'checked';
        if ($this->use_hmail) {
            $hmchecked    = 'checked';
            $otherchecked = '';
        }
        if ($this->admins[$this->rcmail->user->data['username']] == 0) {
            $hmail = html::tag('span', null, 'IMAP-Server:&nbsp;') . html::tag('input', array(
                'type' => 'radio',
                'name' => 'hmailbackend',
                'id' => 'yhmail',
                'checked' => $hmchecked,
                'onclick' => 'pm_hmail(false)'
            )) . html::tag('span', null, html::tag('a', array(
                'href' => 'http://www.hmailserver.com/',
                'target' => '_blank'
            ), 'hMailserver') . '&nbsp;') . html::tag('input', array(
                'type' => 'radio',
                'name' => 'hmailbackend',
                'id' => 'nhmail',
                'checked' => $otherchecked,
                'onclick' => 'pm_hmail(true)'
            )) . html::tag('span', null, 'other') . '&nbsp;&rArr;&nbsp;';
        } else {
            $hmail = '';
        }
        $credits   = html::tag('div', array(
            'style' => 'display:block; border:1px solid lightgrey; background:lightyellow; padding:2px 2px 2px 2px; width:99%;'
        ), '&nbsp;' . $hmail . 'MyRC$ ' . html::tag('span', array(
            'id' => 'cdlcredits'
        ), ($cdlcredits ? $cdlcredits : 0)) . ' (' . $this->gettext('credits') . ') &minus; MyRC$ ' . html::tag('span', array(
            'id' => 'cdlprice'
        ), $cdlprice) . '&nbsp(' . $this->gettext('forthisdownload') . ') = ' . 'MyRC$ ' . html::tag('span', array(
            'id' => 'cdlremaining'
        ), $remaining) . ($remaining > 0 ? '&nbsp;(' . $this->gettext('remainingcredits') . ')' : '&nbsp;(' . $link . ')'));
        $controls  = html::tag('div', array(
            'style' => 'display: inline; float: right; margin-right: 5px;'
        ), html::tag('a', array(
            'id' => 'buycreditslink',
            'href' => './?_task=settings&_action=plugin.plugin_manager_buycredits',
            'target' => '_blank'
        ), $this->gettext('buynow'))) . html::tag('div', array(
            'style' => 'display: inline; float: right;'
        ), html::tag('a', array(
            'href' => '#',
            'onclick' => 'pm_discard()'
        ), $this->gettext('discardliabletopaycosts')) . '&nbsp;|&nbsp;' . html::tag('a', array(
            'href' => '#',
            'onclick' => 'pm_notinstalled()'
        ), $this->gettext('unchecknotinstalledplugins')) . '&nbsp;|&nbsp;');
        $zipbutton = $credits . html::tag('br') . html::tag('input', array(
            'type' => 'submit',
            'class' => 'button mainaction',
            'value' => $this->gettext('ziparchive')
        )) . $controls;
        if (strtolower($this->get_demo($user)) == strtolower(sprintf($this->rcmail->config->get('demo_user_account'), ""))) {
            $zipbutton = $zipbutton = html::tag('input', array(
                'type' => 'button',
                'class' => 'button mainaction',
                'value' => $this->gettext('demoaccount')
            ));
            ;
        }
        $formcontent = html::tag('div', array(
            'id' => 'rcheader'
        ), '<br />Roundcube:&nbsp;' . $this->gettext('serverversion') . '&nbsp;' . RCMAIL_VERSION . '&nbsp;&raquo;&nbsp;' . $this->gettext('mirrorversion') . '&nbsp;' . $mirror_rc . '&nbsp;&raquo;&nbsp;' . $append . '<hr />');
        $formcontent .= html::tag('p', null);
        $formcontent .= html::tag('div', array(
            'id' => 'table-container',
            'style' => 'height:0px; overflow:auto; margin-right:10px;'
        ), html::tag('table', array(
            'id' => 'table',
            'border' => 0,
            'cellspacing' => 0,
            'cellpadding' => 0
        ), html::tag('thead', null, $thead) . html::tag('tbody', null, $tbody1 . $tbody2)));
        $formcontent .= html::tag('div', array(
            'id' => 'update_footer'
        ), html::tag('p', null, null) . $zipbutton . html::tag('input', array(
            'type' => 'button',
            'onclick' => 'document.location.href="./?_task=settings&_framed=1&_action=plugin.plugin_manager_update&_warning=1"',
            'class' => 'button',
            'value' => $this->gettext('cancel')
        )) . html::tag('br') . html::tag('div', array(
            'class' => 'asterix'
        ), '&sup1;' . $this->gettext('donotregister') . '<br />&sup2;' . $this->gettext('register')) . html::tag('input', array(
            'type' => 'hidden',
            'name' => '_pm_price',
            'id' => 'pm_price',
            'value' => '##placeholder##'
        )) . html::tag('input', array(
            'type' => 'hidden',
            'name' => '_pm_customer_id',
            'value' => $this->rcmail->config->get('customer_id')
        )) . html::tag('input', array(
            'type' => 'hidden',
            'name' => '_pm_admin',
            'value' => $user
        )) . html::tag('input', array(
            'type' => 'hidden',
            'name' => '_pm_serveradmin',
            'value' => $_SERVER['SERVER_ADMIN']
        )) . html::tag('input', array(
            'type' => 'hidden',
            'name' => '_pm_newsletter',
            'value' => get_input_value('_newsletter', RCUBE_INPUT_GPC)
        )) . html::tag('input', array(
            'type' => 'hidden',
            'name' => '_pm_branch',
            'value' => get_input_value('_branch', RCUBE_INPUT_GPC)
        )) . html::tag('input', array(
            'type' => 'hidden',
            'name' => '_pm_firstname',
            'value' => urldecode(get_input_value('_firstname', RCUBE_INPUT_GPC))
        )) . html::tag('input', array(
            'type' => 'hidden',
            'name' => '_pm_lastname',
            'value' => urldecode(get_input_value('_lastname', RCUBE_INPUT_GPC))
        )) . html::tag('input', array(
            'type' => 'hidden',
            'name' => '_pm_serverip',
            'value' => $server['ip']
        )) . html::tag('input', array(
            'type' => 'hidden',
            'name' => '_pm_serversoftware',
            'value' => $_SERVER['SERVER_SOFTWARE']
        )) . html::tag('input', array(
            'type' => 'hidden',
            'name' => '_pm_rcmail',
            'value' => RCMAIL_VERSION
        )) . html::tag('input', array(
            'type' => 'hidden',
            'name' => '_pm_version',
            'value' => self::$version
        )) . html::tag('input', array(
            'type' => 'hidden',
            'name' => '_pm_db',
            'value' => $db
        )) . html::tag('input', array(
            'type' => 'hidden',
            'name' => '_pm_php',
            'value' => phpversion()
        )) . html::tag('p', null, null));
        $formfooter = html::tag('div', array(
            'id' => 'formfooter'
        ), html::tag('div', array(
            'class' => 'footerleft'
        ), html::tag('form', array(
            'name' => 'form',
            'onsubmit' => 'return pmf();',
            'method' => 'post',
            'action' => str_ireplace('http:', 'https:', $this->mirror) . '?_action=plugin.plugin_server_request&_hl=' . $_SESSION['language']
        ), $formcontent)));
        $formfooter .= html::tag('script', array(
            'type' => 'text/javascript'
        ), '$("body").hide();');
        $this->rcmail->output->add_label('plugin_manager.creditsupdated');
        $paypalbutton = html::tag('a', array(
            'href' => 'http://myroundcube.com/#contact',
            'target' => '_blank'
        ), 'MyRoundcube ' . $this->gettext('support'));
        $this->rcmail->output->add_script('if(screen.width < 1300){$(".pm_update").html("..."); $("#settings-sections").hide();$("#pluginbody").css("left", "5px")}', 'docready');
        return html::tag('div', array(
            'id' => 'prefs-box',
            'style' => 'width: 100%; overflow: auto;'
        ), $boxtitle . $formfooter) . html::tag('div', array(
            'id' => 'paypalcontainer'
        ), html::tag('div', array(
            'id' => 'paypal'
        ), $paypalbutton));
    }
    function getcredits($ajax = true)
    {
        $this->require_plugin('http_request');
        $params                   = array(
            '_customer_id' => $this->rcmail->config->get('customer_id')
        );
        $httpConfig['method']     = 'POST';
        $httpConfig['target']     = str_replace('buycredits', 'getcredits', $this->billingurl);
        $httpConfig['timeout']    = '30';
        $httpConfig['params']     = $params;
        $httpConfig['user_agent'] = 'MyRoundcube PHP/5.0';
        $http                     = new MyRCHttp();
        $http->initialize($httpConfig);
        if (ini_get('safe_mode') || ini_get('open_basedir')) {
            $http->useCurl(false);
        }
        $http->execute();
        if ($http->error) {
            $response = false;
        } else {
            $response = $http->result;
        }
        if ($response == '-0') {
            unset($httpConfig['params']);
            $httpConfig['method'] = 'GET';
            $httpConfig['target'] .= '&_customer_id=' . $this->rcmail->config->get('customer_id');
            $http->initialize($httpConfig);
            if (ini_get('safe_mode') || ini_get('open_basedir')) {
                $http->useCurl(false);
            }
            $http->execute();
            if ($http->error) {
                $response = false;
            } else {
                $response = $http->result;
            }
        }
        if ($ajax)
            $this->rcmail->output->command('plugin.plugin_manager_getcredits', $response);
        else
            return $response;
    }
    function buycredits()
    {
        $this->require_plugin('http_request');
        $params                   = array(
            '_customer_id' => $this->rcmail->config->get('customer_id'),
            '_clientip' => $_SERVER['REMOTE_ADDR'],
            '_serverip' => $_SERVER['SERVER_ADDR']
        );
        $httpConfig['method']     = 'POST';
        $httpConfig['target']     = $this->billingurl;
        $httpConfig['timeout']    = '30';
        $httpConfig['params']     = $params;
        $httpConfig['user_agent'] = 'MyRoundcube PHP/5.0';
        $http                     = new MyRCHttp();
        $http->initialize($httpConfig);
        if (ini_get('safe_mode') || ini_get('open_basedir')) {
            $http->useCurl(false);
        }
        $http->execute();
        if ($http->error) {
            $this->rcmail->output->send('plugin_manager.error');
        } else {
            $url  = explode('?', $this->billingurl, 2);
            $url  = slashify($url[0]);
            $page = $http->result;
            $page = str_replace('href="plugins/', 'href="' . $url . 'plugins/', $page);
            $page = str_replace('src="skins/', 'src="' . $url . 'skins/', $page);
            $page = str_replace('<img src="plugins/', '<img src="' . $url . 'plugins/', $page);
            $page = str_replace('<script type="text/javascript" src="plugins/', '<script type="text/javascript" src="' . $url . 'plugins/', $page);
            send_nocacheing_headers();
            echo $page;
        }
        exit;
    }
    function getnew()
    {
        $this->require_plugin('http_request');
        $params                   = array(
            '_customer_id' => $this->rcmail->config->get('customer_id'),
            '_ip' => $this->getVisitorIP()
        );
        $httpConfig['method']     = 'POST';
        $httpConfig['target']     = $this->svn . '?_action=plugin.plugin_server_customer_id_new';
        $httpConfig['timeout']    = '30';
        $httpConfig['params']     = $params;
        $httpConfig['user_agent'] = 'MyRoundcube PHP/5.0';
        $http                     = new MyRCHttp();
        $http->initialize($httpConfig);
        if (ini_get('safe_mode') || ini_get('open_basedir')) {
            $http->useCurl(false);
        }
        $http->execute();
        if ($http->error) {
            $response = false;
        } else {
            $response = $http->result;
        }
        if (is_string($response) && strlen($response) == 32) {
            $a_prefs['customer_id']     = $response;
            $a_prefs['own_customer_id'] = $response;
            $this->rcmail->user->save_prefs($a_prefs);
            $sql    = 'SELECT * FROM ' . get_table_name('plugin_manager') . ' WHERE ' . $this->q('conf') . '=?';
            $res    = $this->rcmail->db->limitquery($sql, 0, 1, 'admins');
            $admins = $this->rcmail->db->fetch_assoc($res);
            if ($admins = unserialize($admins['value'])) {
                if ($admins[0] == $this->rcmail->user->data['username']) {
                    foreach ($admins as $idx => $admin) {
                        if ($idx == 0)
                            continue;
                        $sql   = 'SELECT ' . $this->q('preferences') . ' FROM ' . get_table_name('users') . ' WHERE ' . $this->q('username') . '=? AND ' . $this->q('mail_host') . '=?';
                        $res   = $this->rcmail->db->limitquery($sql, 0, 1, $admin, $_SESSION['storage_host']);
                        $prefs = $this->rcmail->db->fetch_assoc($res);
                        if ($prefs = unserialize($prefs['preferences'])) {
                            if ($prefs['customer_id'] == $prefs['shared_customer_id']) {
                                $prefs['customer_id'] = $reponse;
                            }
                            $prefs['shared_customer_id'] = $response;
                            $prefs                       = serialize($prefs);
                            $sql                         = 'UPDATE ' . get_table_name('users') . ' SET ' . $this->q('preferences') . '=? WHERE ' . $this->q('username') . '=? AND ' . $this->q('mail_host') . '=?';
                            $this->rcmail->db->query($sql, $prefs, $admin, $_SESSION['storage_host']);
                        }
                    }
                }
            }
        } else {
            unset($httpConfig['params']);
            $httpConfig['method'] = 'GET';
            $httpConfig['target'] .= '&_customer_id=' . $this->rcmail->config->get('customer_id') . '&_ip=' . $this->getVisitorIP();
            $http->initialize($httpConfig);
            if (ini_get('safe_mode') || ini_get('open_basedir')) {
                $http->useCurl(false);
            }
            $http->execute();
            if ($http->error) {
                $response = false;
            } else {
                $response = $http->result;
            }
            if (is_string($response) && strlen($response) == 32) {
                $a_prefs['customer_id']     = $response;
                $a_prefs['own_customer_id'] = $response;
                $this->rcmail->user->save_prefs($a_prefs);
                $sql    = 'SELECT * FROM ' . get_table_name('plugin_manager') . ' WHERE ' . $this->q('conf') . '=?';
                $res    = $this->rcmail->db->limitquery($sql, 0, 1, 'admins');
                $admins = $this->rcmail->db->fetch_assoc($res);
                if ($admins = unserialize($admins['value'])) {
                    if ($admins[0] == $this->rcmail->user->data['username']) {
                        foreach ($admins as $idx => $admin) {
                            if ($idx == 0)
                                continue;
                            $sql   = 'SELECT ' . $this->q('preferences') . ' FROM ' . get_table_name('users') . ' WHERE ' . $this->q('username') . '=? AND ' . $this->q('mail_host') . '=?';
                            $res   = $this->rcmail->db->limitquery($sql, 0, 1, $admin, $_SESSION['storage_host']);
                            $prefs = $this->rcmail->db->fetch_assoc($res);
                            if ($prefs = unserialize($prefs['preferences'])) {
                                if ($prefs['customer_id'] == $prefs['shared_customer_id']) {
                                    $prefs['customer_id'] = $reponse;
                                }
                                $prefs['shared_customer_id'] = $response;
                                $prefs                       = serialize($prefs);
                                $sql                         = 'UPDATE ' . get_table_name('users') . ' SET ' . $this->q('preferences') . '=? WHERE ' . $this->q('username') . '=? AND ' . $this->q('mail_host') . '=?';
                                $this->rcmail->db->query($sql, $prefs, $admin, $_SESSION['storage_host']);
                            }
                        }
                    }
                }
            }
        }
        header('Location: ./?_task=settings&_action=edit-prefs&_section=plugin_manager_customer&_framed=1');
        exit;
    }
    function restore_config()
    {
        if ($this->config_permissions) {
            $plugin = get_input_value('_plugin', RCUBE_INPUT_GPC);
            $this->show('./plugins/' . $plugin . '/config.inc.php.dist', 'restore');
        }
    }
    function edit_config()
    {
        if ($this->config_permissions) {
            if ($plugin = get_input_value('_plugin', RCUBE_INPUT_GPC)) {
                $admin = false;
                $sql   = 'SELECT * FROM ' . get_table_name('db_config') . ' WHERE ' . $this->q('env') . '=?';
                $res   = $this->rcmail->db->limitquery($sql, 0, 1, $plugin);
                $conf  = $this->rcmail->db->fetch_assoc($res);
                if ($conf['conf']) {
                    $conf['conf'] = str_replace('$rcmail_config', '$config', $conf['conf']);
                    $source       = 'database';
                    $admin        = $conf['admin'];
                    include INSTALL_PATH . 'plugins/' . $plugin . '/config.inc.php.dist';
                    if ($plugin == 'register') {
                        if (isset($conf['conf'])) {
                            eval($conf['conf']);
                        }
                        if ($driver = $config['register_driver']) {
                            include INSTALL_PATH . 'plugins/register/drivers/' . $driver . '/driver.php';
                        }
                    }
                    if (is_array($rcmail_config)) {
                        $config        = $rcmail_config;
                        $sconfig       = $rcmail_config;
                        $defaults      = $sconfig;
                        $scomments     = $rcmail_config_comments;
                        $rcmail_config = false;
                    } else {
                        $sconfig   = $config;
                        $defaults  = $sconfig;
                        $scomments = $config_comments;
                    }
                    $config = array();
                    eval($conf['conf']);
                    if (is_array($rcmail_config)) {
                        $config        = $rcmail_config;
                        $rcmail_config = false;
                    }
                    foreach ($config as $key => $val) {
                        unset($sconfig[$key]);
                    }
                    foreach ($config as $key => $val) {
                        if (!isset($defaults[$key])) {
                            if ($dist = @file_get_contents(INSTALL_PATH . 'plugins/' . $plugin . '/config.inc.php.dist')) {
                                if (strpos($dist, '$config[\'' . $key . '\']') === false) {
                                    if (strpos($conf['conf'], "/* The following configuration parameter is deprecated - please remove */\n" . '$config[\'' . $key . '\']') === false) {
                                        $conf['conf'] = str_replace('$config[\'' . $key . '\']', "/* The following configuration parameter is deprecated - please remove */\r\n" . '$config[\'' . $key . '\']', $conf['conf']);
                                    }
                                }
                            }
                        }
                    }
                    $prepend = '';
                    if (count($sconfig > 0)) {
                        $code = '';
                        foreach ($sconfig as $key => $val) {
                            if (strpos($conf['conf'], '$config[\'' . $key . '\']') === false) {
                                if ($comment = $scomments[$key]) {
                                    $code .= "\r\n" . '/* ' . $comment . ' */' . "\r\n";
                                }
                                $code .= '$config[\'' . $key . '\'] = ' . var_export($val, true) . ';' . "\r\n";
                            }
                        }
                        if ($code) {
                            $prepend = '/*---- New configuration pararmenters since last saving ----*/' . "\r\n\r\n" . $code . "\r\n" . '/*----------------------------------------------------------*/' . "\r\n\r\n" . '/* Begin currently saved parameters */' . "\r\n\r\n";
                        }
                    }
                    $_POST['_save_config'] = "<?php\r\n\r\n" . $prepend . $conf['conf'] . "\r\n\r\n?>";
                } else {
                    if (file_exists(INSTALL_PATH . 'plugins/global_config/config.inc.php')) {
                        include INSTALL_PATH . 'plugins/global_config/config.inc.php';
                        if (is_array($rcmail_config)) {
                            $global_config = $rcmail_config;
                            $rcmail_config = array();
                        } else {
                            $global_config = $config;
                            $config        = array();
                        }
                    }
                    $source = './plugins/' . $plugin . '/config.inc.php.dist';
                    $conf   = file_get_contents(INSTALL_PATH . 'plugins/' . $plugin . '/config.inc.php.dist');
                    if ($plugin == 'register') {
                        if ($driver = $this->rcmail->config->get('register_driver')) {
                            if (file_exists(INSTALL_PATH . 'plugins/register/drivers/' . $driver . '/config.inc.php.dist')) {
                                $source = ', ./plugins/' . $plugin . 'drivers/' . $driver . '/config.inc.php.dist';
                                $conf .= file_get_contents(INSTALL_PATH . 'plugins/register/drivers/' . $driver . '/config.inc.php.dist');
                                $conf = str_replace('?><?php', '/* ' . INSTALL_PATH . 'plugins/regster/drivers/' . $driver . '/config.inc.php */', $conf);
                            }
                        }
                    }
                    $conf = str_replace('$rcmail_config', '$config', $conf);
                    include INSTALL_PATH . 'plugins/' . $plugin . '/config.inc.php.dist';
                    if (is_array($rcmail_config)) {
                        $config = $rcmail_config;
                    }
                    $rcmail_config = array();
                    $config        = array();
                    if (file_exists(INSTALL_PATH . 'plugins/' . $plugin . '/config.inc.php')) {
                        $source = './plugins/' . $plugin . '/config.inc.php';
                        include INSTALL_PATH . 'plugins/' . $plugin . '/config.inc.php';
                    }
                    if ($plugins == 'register') {
                        if ($driver = $this->rcmail->config->get('register_driver')) {
                            if (file_exists(INSTALL_PATH . 'plugins/register/drivers/' . $driver . '/config.inc.php')) {
                                $source = ', ./plugins/' . $plugin . 'drivers/' . $driver . '/config.inc.php';
                                include INSTALL_PATH . 'plugins/register/drivers/' . $driver . '/config.inc.php';
                            }
                        }
                    }
                    if (file_exists(INSTALL_PATH . 'plugins/global_config/config.inc.php')) {
                        include INSTALL_PATH . 'plugins/global_config/config.inc.php';
                    }
                    foreach ($config as $key => $val) {
                        if (isset($global_config[$key])) {
                            $source = './plugins/global_config/config.inc.php';
                        }
                        $conf = preg_replace('/\n\$config\[\'' . $key . '\'\] = \$/s', "\n" . '//***$config[' . "'" . $key . "'" . '] = $', $conf);
                        if (isset($config[$key])) {
                            $conf = preg_replace('/\n\$config\[\'' . $key . '\'\](.+?);(\r|\n)/s', "\n" . '$config[' . "'" . $key . "'" . '] = ' . var_export($config[$key], true) . ';', $conf);
                        }
                    }
                    $conf = str_replace("\n" . '//***$config[', "\n" . '$config[', $conf);
                    if ($plugin == 'tinymce') {
                        $repl   = array(
                            " '",
                            "';\r",
                            "';\n",
                            "\'"
                        );
                        $replby = array(
                            ' "',
                            "\";\r",
                            "\";\n",
                            "'"
                        );
                        $conf   = str_replace($repl, $replby, $conf);
                    }
                    $_POST['_save_config'] = $conf;
                }
            }
            $this->show($source, 'edit', $admin);
        }
    }
    function check_syntax($conf = false)
    {
        $test = "if(0){\n" . $conf . "\n}";
        @ini_set('log_errors', false);
        @ini_set('display_errors', true);
        ob_start();
        eval($test);
        $error = str_replace(': eval()\'d code ', '', strip_tags(ob_get_clean()));
        ob_end_clean();
        return $error;
    }
    function save_config()
    {
        if ($this->config_permissions) {
            $plugin = get_input_value('_plugin', RCUBE_INPUT_POST);
            $source = get_input_value('_source', RCUBE_INPUT_POST);
            $conf   = trim($_POST['_save_config']);
            $conf   = str_replace('+', '__plus__', $conf);
            $conf   = urldecode($conf);
            $conf   = str_replace('__plus__', '+', $conf);
            $conf   = str_replace('/*---- New configuration pararmenters since last saving ----*/' . "\n\n", '', $conf);
            $conf   = str_replace('/*----------------------------------------------------------*/' . "\n\n", '', $conf);
            $conf   = str_replace('/* Begin currently saved parameters */' . "\n\n", '', $conf);
            $conf   = explode("\n", $conf);
            if (strtolower(trim($conf[count($conf) - 1])) == '?>') {
                unset($conf[count($conf) - 1]);
            }
            if (strtolower(trim($conf[0])) == '<?php') {
                unset($conf[0]);
            }
            $conf  = trim(stripslashes(implode("\n", $conf)));
            $error = $this->check_syntax($conf);
            if ($error) {
                $conf  = str_replace("\\'", '"', $conf);
                $error = $this->check_syntax($conf);
            }
            if (!$error) {
                $sql = 'DELETE FROM ' . get_table_name('db_config') . ' WHERE ' . $this->q('env') . '=?';
                $this->rcmail->db->query($sql, $plugin);
                $sql = 'INSERT INTO ' . get_table_name('db_config') . '(' . $this->q('env') . ', ' . $this->q('conf') . ', ' . $this->q('admin') . ') VALUES (?, ?, ?)';
                $this->rcmail->db->query($sql, $plugin, $conf, $this->rcmail->user->data['username']);
                $this->rcmail->output->command('plugin.plugin_manager_save_config', array(
                    $this->gettext('successfullysaved'),
                    'confirmation'
                ));
            } else {
                $this->rcmail->output->command('plugin.plugin_manager_save_config', array(
                    $error,
                    'error'
                ));
            }
        } else {
            $this->rcmail->output->command('plugin.plugin_manager_save_config', array(
                $this->gettext('errorsaving'),
                'error'
            ));
        }
    }
    function show($source = false, $mode = 'edit', $admin = false)
    {
        $plugin = get_input_value('_plugin', RCUBE_INPUT_GPC);
        if (!$source) {
            if (file_exists(INSTALL_PATH . 'plugins/' . $plugin . '/config.inc.php.dist')) {
                $source = './plugins/' . $plugin . '/config.inc.php.dist';
            }
        }
        if (strtolower($this->get_demo($_SESSION['username'])) == strtolower(sprintf($this->rcmail->config->get('demo_user_account'), ""))) {
            $this->out = html::tag('h3', null, '&nbsp;&nbsp;Access denied (Demo Account)') . html::tag('div', array(
                'style' => 'float: left;'
            ), '&nbsp;&nbsp;[' . html::tag('a', array(
                'href' => './?_task=settings&_action=edit-prefs&_section=plugin_manager_settings&_framed=1&_expand=' . $plugin
            ), $this->gettext('back')) . ']');
        } else if ($plugin) {
            $GLOBALS['codemirror'] = array(
                'mode' => 'PHP',
                'elem' => 'code',
                'readonly' => false,
                'buttons' => '["save", "undo", "redo", "jump", "search"]',
                'save' => 'function(){document.getElementById("code").value = editor.mirror.getValue(); rcmail.http_post("plugin.plugin_manager_save_config", "_plugin=' . $plugin . '&_save_config=" + urlencode($("#code").val()));}'
            );
            $this->require_plugin('codemirror_ui');
            if (isset($_POST['_save_config'])) {
                $content = trim($_POST['_save_config']);
            } else {
                $content = file_get_contents(INSTALL_PATH . 'plugins/' . $plugin . '/config.inc.php.dist');
                if ($plugin == 'register') {
                    if ($driver = $this->rcmail->config->get('register_driver')) {
                        if (file_exists(INSTALL_PATH . 'plugins/register/drivers/' . $driver . '/config.inc.php.dist')) {
                            $content .= file_get_contents(INSTALL_PATH . 'plugins/register/drivers/' . $driver . '/config.inc.php.dist');
                            $content = str_replace('?><?php', '', $content);
                        }
                    }
                }
            }
            if ($mode == 'edit') {
                $action = '&nbsp;[' . html::tag('a', array(
                    'href' => './?_task=settings&_action=plugin.plugin_manager_restore_config&_framed=1&_plugin=' . $plugin
                ), $this->gettext('restoredefaults')) . ']';
            } else {
                $action = '&nbsp;[' . html::tag('a', array(
                    'href' => './?_action=plugin.plugin_manager_edit_config&_framed=1&_plugin=' . $plugin
                ), $this->gettext('edit')) . ']';
            }
            if (in_array($plugin, $this->nodocs)) {
                $instructions = html::tag('div', array(
                    'style' => 'float: left;'
                ), '&nbsp;[' . html::tag('a', array(
                    'target' => '_blank',
                    'href' => 'http://myroundcube.com/myroundcube-plugins#' . $plugin
                ), $this->gettext('instructions')) . ']');
            } else if (isset($this->docsmap[$plugin])) {
                $instructions = html::tag('div', array(
                    'style' => 'float: left;'
                ), '&nbsp;[' . html::tag('a', array(
                    'target' => '_blank',
                    'href' => 'http://myroundcube.com/myroundcube-plugins/' . $this->docsmap[$plugin]
                ), $this->gettext('instructions')) . ']');
            } else {
                $instructions = html::tag('div', array(
                    'style' => 'float: left;'
                ), '&nbsp;[' . html::tag('a', array(
                    'target' => '_blank',
                    'href' => 'http://myroundcube.com/myroundcube-plugins/' . $plugin . '-plugin'
                ), $this->gettext('instructions')) . ']');
            }
            $this->include_script('plugin_manager.js');
            $this->out = html::tag('form', array(
                'method' => 'post',
                'name' => 'form',
                'action' => './?_task=settings&_action=plugin.plugin_manager_save_config&_framed=1'
            ), html::tag('div', array(
                'style' => 'position:absolute; top: 20px; bottom: 20px; left: 20px; right: 20px;'
            ), html::tag('div', array(
                'style' => 'float: left;'
            ), $this->gettext('plugin') . ':&nbsp;' . $plugin) . html::tag('div', array(
                'style' => 'float: right;',
                'id' => 'source'
            ), ($source ? ($this->gettext('source') . ':&nbsp;') : '') . ($source == 'database' ? $this->gettext('database') : $source) . ($admin ? html::tag('small', null, html::tag('span', null, '&nbsp;(' . $this->gettext('configuredby') . '&nbsp;' . $admin . ')')) : '')) . html::tag('input', array(
                'name' => '_plugin',
                'type' => 'hidden',
                'value' => $plugin
            )) . html::tag('input', array(
                'name' => '_source',
                'type' => 'hidden',
                'value' => $source
            )) . html::tag('br') . html::tag('textarea', array(
                'style' => 'width: 100%; height: 95%',
                'name' => '_save_config',
                'id' => 'code'
            ), $content) . html::tag('br') . html::tag('div', array(
                'style' => 'float: left;'
            ), $instructions . $action . '&nbsp;[' . html::tag('a', array(
                'href' => './?_task=settings&_action=edit-prefs&_section=plugin_manager_settings&_framed=1&_expand=' . $plugin
            ), $this->gettext('back')) . ']')));
        } else {
            $GLOBALS['codemirror'] = array(
                'mode' => 'PHP',
                'elem' => 'code',
                'readonly' => true,
                'buttons' => '["jump", "search"]',
                'save' => 'function(){}'
            );
            $this->require_plugin('codemirror_ui');
            $header    = str_replace('##YEAR##', date('Y'), file_get_contents(INSTALL_PATH . 'plugins/plugin_manager/CONFIGHEADER'));
            $example   = file_get_contents(INSTALL_PATH . 'plugins/plugin_manager/EXAMPLE');
            $this->out = html::tag('div', array(
                'style' => 'position:absolute; top: 20px; bottom: 20px; left: 20px; right: 20px;'
            ), html::tag('textarea', array(
                'readonly' => true,
                'style' => 'width: 100%; height: 95%',
                'id' => 'code'
            ), "<?php\r\n" . $header . "\r\n" . $example . "\r\n\r\n" . '/* Configuration */' . "\r\n\r\n" . '/* Plugins which have to be loaded in not authenticated state even if disabled by user */' . "\r\n" . '$config[\'plugin_manager_unauth\'] = ' . var_export($this->unauth, true) . ";\r\n\r\n" . '/* Defaults */' . "\r\n" . '$config[\'plugin_manager_defaults\'] = ' . var_export($this->defaults, true) . ";\r\n?>") . html::tag('div', array(
                'style' => 'float: right;'
            ), '[' . html::tag('a', array(
                'href' => './?_task=settings&_action=edit-prefs&_section=plugin_manager_settings&_framed=1'
            ), $this->gettext('back')) . ']'));
        }
        $this->register_handler('plugin.body', array(
            $this,
            'sqlerror'
        ));
        $this->rcmail->output->send('plugin_manager.config');
    }
    function deny()
    {
        $customer_id                   = $this->rcmail->config->get('customer_id');
        $own_customer_id               = $this->rcmail->config->get('own_customer_id');
        $a_prefs                       = array();
        $a_prefs['customer_id']        = $own_customer_id;
        $a_prefs['shared_customer_id'] = $customer_id;
        $this->rcmail->user->save_prefs($a_prefs);
        header('Location: ./?_task=settings&_action=edit-prefs&_section=plugin_manager_customer&_framed=1');
        exit;
    }
    function accept()
    {
        $customer_id            = $this->rcmail->config->get('shared_customer_id');
        $a_prefs                = array();
        $a_prefs['customer_id'] = $customer_id;
        $this->rcmail->user->save_prefs($a_prefs);
        header('Location: ./?_task=settings&_action=edit-prefs&_section=plugin_manager_customer&_framed=1');
        exit;
    }
    function settings_link($args)
    {
        $args['list']['plugin_manager'] = array(
            'id' => 'plugin_manager',
            'section' => $this->gettext('plugin_manager_title')
        );
        $user                           = $_SESSION['username'];
        $admins                         = $this->admins;
        if (strtolower($this->get_demo($_SESSION['username'])) == strtolower(sprintf($this->rcmail->config->get('demo_user_account'), ""))) {
            if ($this->rcmail->config->get('demo_target_group', 'admins') == 'admins') {
                $demo = true;
            }
        }
        if ((isset($admins[strtolower($user)]) && $admins[strtolower($user)] == 0 && file_exists(INSTALL_PATH . $this->rcmail->config->get('plugin_manager_hash') . '.myrc')) || $demo) {
            $args['list']['plugin_manager_admins'] = array(
                'id' => 'plugin_manager_admins',
                'section' => $this->gettext('submenuprefix') . $this->gettext('manage_admins')
            );
        }
        if ((isset($admins[strtolower($user)]) && file_exists(INSTALL_PATH . $this->rcmail->config->get('plugin_manager_hash') . '.myrc')) || $demo) {
            $args['list']['plugin_manager_settings'] = array(
                'id' => 'plugin_manager_settings',
                'section' => $this->gettext('submenuprefix') . $this->gettext('settings')
            );
        }
        if (!$this->noremote && isset($admins[strtolower($user)]) || $demo) {
            $args['list']['plugin_manager_update'] = array(
                'id' => 'plugin_manager_update',
                'section' => $this->gettext('submenuprefix') . $this->gettext('update_plugins')
            );
            if (!$demo) {
                $args['list']['plugin_manager_customer'] = array(
                    'id' => 'plugin_manager_customer',
                    'section' => $this->gettext('submenuprefix') . $this->gettext('customer_account')
                );
                if (!$this->rcmail->config->get('customer_id')) {
                    $customer_id = $this->getCustomerID();
                    if (is_string($customer_id) && strlen($customer_id) == 32) {
                        $a_prefs['customer_id'] = $customer_id;
                        $this->rcmail->user->save_prefs($a_prefs);
                    }
                }
                $customer_id = $this->rcmail->config->get('customer_id');
                if ($_GET['_buynow'] || !$customer_id) {
                    if (!$customer_id) {
                        $customer_id        = $this->getCustomerID();
                        $arr['customer_id'] = $customer_id;
                        $this->rcmail->user->save_prefs($arr);
                        $this->rcmail->output->add_script('rcmail.display_message("' . $this->gettext('getnew') . '", "notice");', 'docready');
                    }
                    $this->rcmail->output->add_script('rcmail.sections_list.select("plugin_manager_customer");', 'docready');
                }
            }
        }
        return $args;
    }
    function bind()
    {
        if (isset($this->admins[strtolower($_SESSION['username'])]) && file_exists(INSTALL_PATH . $this->rcmail->config->get('plugin_manager_hash') . '.myrc')) {
            $target  = get_input_value('_target', RCUBE_INPUT_GET);
            $section = get_input_value('_section', RCUBE_INPUT_GET);
            if ($section && $target) {
                $sql       = 'SELECT * FROM ' . get_table_name('plugin_manager') . ' WHERE ' . $this->q('conf') . '=?';
                $res       = $this->rcmail->db->limitquery($sql, 0, 1, 'defaults_overwrite');
                $overwrite = $this->rcmail->db->fetch_assoc($res);
                if (is_array($overwrite)) {
                    $defaults = unserialize($overwrite['value']);
                    if (is_array($defaults)) {
                        include INSTALL_PATH . 'plugins/plugin_manager/defaults.inc.php';
                        $defaults[$section][$target]['protected'] = $config['plugin_manager_defaults'][$section][$target]['protected'];
                        $sql                                      = 'UPDATE ' . get_table_name('plugin_manager') . ' SET ' . $this->q('value') . '=?, ' . $this->q('type') . '=? WHERE ' . $this->q('conf') . '=?';
                        $this->rcmail->db->query($sql, serialize($defaults), 'array', 'defaults_overwrite');
                        $this->rcmail->session->remove('plugin_manager_defaults');
                    }
                }
                header('Location: ./?_task=settings&_action=edit-prefs&_section=plugin_manager_settings&_framed=1');
                exit;
            }
        }
    }
    function unbind()
    {
        if (isset($this->admins[strtolower($_SESSION['username'])]) && file_exists(INSTALL_PATH . $this->rcmail->config->get('plugin_manager_hash') . '.myrc')) {
            $target  = get_input_value('_target', RCUBE_INPUT_GET);
            $section = get_input_value('_section', RCUBE_INPUT_GET);
            if ($section && $target) {
                $sql       = 'SELECT * FROM ' . get_table_name('plugin_manager') . ' WHERE ' . $this->q('conf') . '=?';
                $res       = $this->rcmail->db->limitquery($sql, 0, 1, 'defaults_overwrite');
                $overwrite = $this->rcmail->db->fetch_assoc($res);
                if (!is_array($overwrite)) {
                    include INSTALL_PATH . 'plugins/plugin_manager/defaults.inc.php';
                    $overwrite = $config['plugin_manager_defaults'];
                } else {
                    $overwrite = unserialize($overwrite['value']);
                    if (!is_array($overwrite)) {
                        include INSTALL_PATH . 'plugins/plugin_manager/defaults.inc.php';
                        $overwrite = $config['plugin_manager_defaults'];
                    }
                }
                $overwrite[$section][$target]['protected'] = false;
                $sql                                       = 'DELETE FROM ' . get_table_name('plugin_manager') . ' WHERE ' . $this->q('conf') . '=?';
                $this->rcmail->db->query($sql, 'defaults_overwrite');
                $sql = 'INSERT INTO ' . get_table_name('plugin_manager') . ' (' . $this->q('conf') . ', ' . $this->q('value') . ', ' . $this->q('type') . ') VALUES(?, ?, ?)';
                $this->rcmail->db->query($sql, 'defaults_overwrite', serialize($overwrite), 'array');
                $this->rcmail->session->remove('plugin_manager_defaults');
                header('Location: ./?_task=settings&_action=edit-prefs&_section=plugin_manager_settings&_framed=1');
                exit;
            }
        }
    }
    function settings($args)
    {
        if (!get_input_value('_framed', RCUBE_INPUT_GPC)) {
            if ($args['section'] == 'plugin_manager') {
                $skin = $this->rcmail->config->get('skin');
                if (!file_exists($this->home . '/skins/' . $skin . '/plugin_manager.css')) {
                    $skin = "classic";
                }
                $this->include_stylesheet('skins/' . $skin . '/plugin_manager.css');
            }
            if (substr($args['section'], 0, strlen('plugin_manager')) == 'plugin_manager') {
                if ($args['section'] != 'plugin_manager') {
                    $args['blocks'][$args['section']]['options'] = array(
                        'title' => '',
                        'content' => html::tag('div', array(
                            'id' => 'pm_dummy'
                        ), '')
                    );
                    return $args;
                }
            }
        }
        if ($args['section'] == 'plugin_manager') {
            $this->include_script('plugin_manager.js');
            $skin = $this->rcmail->config->get('skin');
            if (!file_exists($this->home . '/skins/' . $skin . '/plugin_manager.css')) {
                $skin = "classic";
            }
            $this->include_stylesheet('skins/' . $skin . '/plugin_manager.css');
            $args['blocks']['plugin_manager'] = array(
                'options' => array(),
                'name' => ''
            );
            $this->merge_config();
            $content         = '';
            $restore         = array();
            $display_section = array();
            foreach ($this->config as $section => $props) {
                if (count($props) > 0) {
                    $li = array();
                    foreach ($props as $plugin => $prop) {
                        $show = true;
                        if ($this->domain) {
                            if (is_array($prop['domains']) && count($prop['domains'] > 0)) {
                                $show = false;
                                foreach ($prop['domains'] as $domain) {
                                    if ($this->domain == $domain) {
                                        $show = true;
                                        break;
                                    }
                                }
                            }
                            if (is_array($prop['hosts']) && count($prop['hosts'] > 0)) {
                                $show = false;
                                foreach ($prop['hosts'] as $host) {
                                    if ($this->host == strtolower($host)) {
                                        $show = true;
                                        break;
                                    }
                                }
                            }
                            if ($prop['browser']) {
                                $show = false;
                                if (!$browser)
                                    $browser = new rcube_browser();
                                eval($prop['browser']);
                                if ($test) {
                                    if ($prop['active']) {
                                        $show = true;
                                    }
                                }
                            }
                            if ($prop['autoload']) {
                                $show = false;
                            }
                            if ($prop['protected']) {
                                if ($prop['protected'] === true) {
                                    $show = false;
                                } else if (is_string($prop['protected'])) {
                                    $show = false;
                                } else if (is_array($prop['protected']) && count($prop['protected']) > 0) {
                                    foreach ($prop['protected'] as $domain) {
                                        if ($this->domain == strtolower($domain)) {
                                            $show = false;
                                            break;
                                        }
                                    }
                                }
                            }
                            if (is_array($prop['skins'])) {
                                $prop['skins'] = array_flip($prop['skins']);
                                if (!isset($prop['skins'][$this->rcmail->config->get('skin', 'classic')])) {
                                    $show = false;
                                }
                            }
                        }
                        if ($show) {
                            $display_section[$section] = true;
                            $defaults                  = $this->defaults;
                            $restore[$plugin]          = array(
                                $plugin,
                                $defaults[$section][$plugin]['active']
                            );
                            if ($user[$section][$plugin]) {
                                $prop = $user[$section][$plugin];
                            }
                            if (is_array($prop['buttons'])) {
                                $this->rcmail->output->set_env('pm_buttons_' . $plugin, $prop['buttons']);
                                $this->rcmail->output->set_env('pm_plugin_' . $plugin, $prop['active']);
                            }
                            $fconfig = 'fsavedialog';
                            if ($prop['config']) {
                                $fconfig = 'fconfig';
                            }
                            $funinstall = '';
                            if ($prop['uninstall']) {
                                $funinstall = 'funinstall';
                            }
                            $frequest = '';
                            if ($prop['uninstall_request']) {
                                if ($prop['uninstall_force']) {
                                    $frequest = 'frequestforce';
                                } else {
                                    $frequest = 'frequest';
                                }
                            }
                            $input = new html_checkbox(array(
                                'style' => 'vertical-align: middle;',
                                'name' => '_plugin_manager_' . $plugin,
                                'class' => trim($fconfig . ' ' . $funinstall . ' ' . $frequest),
                                'value' => 1,
                                'id' => 'pm_chbox_' . $plugin
                            ));
                            if (substr($this->labels($prop['label_name']), 0, 1) == '[' && substr($this->labels($prop['label_name']), strlen($this->labels($prop['label_name'])) - 1) == ']') {
                                if (!is_dir('./plugins/' . $plugin)) {
                                    $li[$plugin] .= html::tag('li', array(
                                        'class' => '_plugin_manager_li',
                                        'id' => 'pmid_' . html::tag('i', null, $plugin)
                                    ), html::tag('input', array(
                                        'style' => 'vertical-align: middle;',
                                        'type' => 'checkbox',
                                        'disabled' => 'true'
                                    )) . html::tag('span', array(
                                        'style' => 'vertical-align: middle;'
                                    ), '&nbsp;' . html::tag('i', null, $plugin) . '&nbsp;' . html::tag('font', array(
                                        'color' => 'red'
                                    ), '(' . $this->gettext('notinstalled') . ')')));
                                }
                            } else {
                                $li[$this->labels($prop['label_name'])] .= html::tag('li', array(
                                    'class' => 'plugin_manager_li',
                                    'style' => 'white-space: nowrap',
                                    'id' => 'pmid_' . $plugin
                                ), $input->show($prop['active'] ? 1 : 0) . html::tag('span', array(
                                    'style' => 'vertical-align: middle;'
                                ), '&nbsp;' . str_replace(' ', '&nbsp;', $this->labels($prop['label_name']))));
                            }
                            if ($prop['label_name']) {
                                $this->rcmail->output->add_script('rcmail.add_label({"' . $plugin . '.pluginname":"' . $this->labels($prop['label_name']) . '"});');
                            }
                            if ($prop['label_description']) {
                                $s = '';
                                if (is_array($prop['label_inject'])) {
                                    switch ($prop['label_inject'][0]) {
                                        case 'string':
                                            $s = $prop['label_inject'][1];
                                            break;
                                        case 'config':
                                            $s = $this->rcmail->config->get($prop['label_inject'][1]);
                                            break;
                                        case 'session':
                                            $s = $_SESSION($prop['label_inject'][1]);
                                            break;
                                        case 'eval':
                                            eval($prop['label_inject'][1]);
                                            break;
                                    }
                                }
                                $this->rcmail->output->add_script('rcmail.add_label({"' . $prop['label_description'] . '":"' . $this->labels($prop['label_description'], $s) . '"});');
                            }
                        } else {
                            $input = new html_hiddenfield(array(
                                'name' => '_plugin_manager_' . $plugin,
                                'id' => 'pm_chbox_' . $plugin,
                                'value' => $prop['active'] ? 1 : 0
                            ));
                            $li[$this->labels($prop['label_name'])] .= $input->show();
                        }
                    }
                    if ($display_section[$section] && count($li) > 0 && $section != 'globalplugins' && $section != 'performance') {
                        ksort($li);
                        $li = implode('', $li);
                        $content .= html::tag('div', array(
                            'id' => 'pm_section_' . $section,
                            'class' => 'pm_section'
                        ), html::tag('fieldset', array(
                            'class' => 'pm_fieldset'
                        ), html::tag('legend', array(
                            'class' => 'title'
                        ), str_replace(' ', '&nbsp;', $this->labels($section))) . html::tag('ul', array(
                            'id' => 'pm_' . $section,
                            'class' => 'plugin_manager_ul'
                        ), $li)));
                    }
                }
            }
            if ($content != '') {
                $args['blocks']['plugin_manager']['options'][0] = array(
                    'title' => '',
                    'content' => html::tag('div', array(
                        'id' => 'pm_div'
                    ), $content)
                );
                $input_restore                                  = new html_checkbox(array(
                    'id' => 'pm_restore_defaults'
                ));
                $input_checkall                                 = new html_checkbox(array(
                    'id' => 'pm_check_all'
                ));
                $input_uncheckall                               = new html_checkbox(array(
                    'id' => 'pm_uncheck_all'
                ));
                $input_config                                   = new html_hiddenfield(array(
                    'name' => '_config_plugin',
                    'id' => 'plugin_manager_config_plugin'
                ));
                $update                                         = '';
                $admins                                         = $this->admins;
                $user                                           = $_SESSION['username'];
                if (isset($admins[strtolower($user)]) || strtolower($this->get_demo($_SESSION['username'])) == strtolower(sprintf($this->rcmail->config->get('demo_user_account'), ""))) {
                    $input_update = new html_checkbox(array(
                        'id' => 'pm_update_plugins'
                    ));
                    $update       = '&nbsp;&nbsp;' . $this->gettext('update_plugins') . '&nbsp;' . html::tag('span', array(
                        'class' => 'pm_control'
                    ), $input_update->show(0));
                }
                $args['blocks']['plugin_manager']['options'][2] = array(
                    'title' => '',
                    'content' => $this->gettext('restoredefaults') . '&nbsp;' . html::tag('span', array(
                        'class' => 'pm_control'
                    ), $input_restore->show(0)) . '&nbsp;&nbsp;' . $this->gettext('checkall') . '&nbsp;' . html::tag('span', array(
                        'class' => 'pm_control'
                    ), $input_checkall->show(0)) . '&nbsp;&nbsp;' . $this->gettext('uncheckall') . '&nbsp;' . html::tag('span', array(
                        'class' => 'pm_control'
                    ), $input_uncheckall->show(0)) . $input_config->show() . $update . html::tag('div', array(
                        'id' => 'jqdialog',
                        'style' => 'display: none;'
                    ))
                );
                $this->rcmail->output->set_env('pm_restore', $restore);
                $this->rcmail->output->add_label('plugin_manager.furtherconfig', 'plugin_manager.successfullydeleted', 'plugin_manager.successfullysaved', 'plugin_manager.errorsaving', 'plugin_manager.uninstall', 'plugin_manager.uninstallconfirm', 'plugin_manager.savewarning', 'plugin_manager.areyousure', 'plugin_manager.yes', 'plugin_manager.no', 'plugin_manager.disable', 'plugin_manager.remove');
            } else {
                $user   = $_SESSION['username'];
                $admins = $this->admins;
                if (isset($admins[strtolower($user)])) {
                    $input_update                                   = new html_checkbox(array(
                        'id' => 'pm_update_plugins'
                    ));
                    $args['blocks']['plugin_manager']['options'][1] = array(
                        'title' => '',
                        'content' => $this->gettext('update_plugins') . '&nbsp;' . $input_update->show(0)
                    );
                }
            }
        } else if ($args['section'] == 'plugin_manager_update') {
            $warning = '&_warning=1';
            if (strtolower($this->get_demo($_SESSION['username'])) == strtolower(sprintf($this->rcmail->config->get('demo_user_account'), ""))) {
                $warning = '';
            }
            $args['blocks']['plugin_manager_update']['options'][0] = array(
                'title' => html::tag('script', array(
                    'type' => 'text/javascript'
                ), '$("body").hide(); document.location.href="./?_task=settings&_framed=1&_action=plugin.plugin_manager_update' . $warning . '"'),
                'content' => ''
            );
        } else if ($args['section'] == 'plugin_manager_settings') {
            if ($label = get_input_value('_pmmsg', RCUBE_INPUT_GET)) {
                $this->rcmail->output->show_message($this->gettext($label), 'confirmation');
            }
            $args['blocks']['plugin_manager']['name'] = $this->gettext('settings');
            $checked                                  = false;
            $readonly                                 = true;
            if ($this->admins[$this->rcmail->user->data['username']] == 0) {
                $readonly = false;
            }
            if ($this->file_based_config) {
                $checked = true;
            }
            $args['blocks']['plugin_manager']['options'][] = array(
                'title' => $this->gettext('file_based_config'),
                'content' => ($readonly ? html::tag('input', array(
                    'type' => 'checkbox',
                    'disabled' => true,
                    'checked' => $checked
                )) : html::tag('input', array(
                    'onclick' => '$("#plugin_manager_overlay").show(); document.forms.form.submit()',
                    'type' => 'checkbox',
                    'checked' => $checked,
                    'id' => 'pm_file_based_config',
                    'name' => '_plugin_manager_file_based_config',
                    'value' => 1
                ))) . html::tag('small', null, '&nbsp;(' . $this->gettext('serverwide') . ($readonly ? '' : '&nbsp; - ' . html::tag('a', array(
                    'href' => 'http://myroundcube.com/myroundcube-plugins/plugin-manager/file-based-administration',
                    'target' => 'new'
                ), $this->gettext('advanced_admins'))) . '&nbsp;-&nbsp;' . html::tag('a', array(
                    'href' => './?_action=plugin.plugin_manager_show_config&_framed=1'
                ), $this->gettext('show_config')) . ')')
            );
            $checked                                       = false;
            if ($this->use_ssl) {
                $checked = true;
            }
            $args['blocks']['plugin_manager']['options'][] = array(
                'title' => $this->gettext('use_ssl'),
                'content' => ($readonly ? html::tag('input', array(
                    'type' => 'checkbox',
                    'disabled' => true,
                    'checked' => $checked
                )) : html::tag('input', array(
                    'onclick' => '$("#plugin_manager_overlay").show(); document.forms.form.submit()',
                    'type' => 'checkbox',
                    'checked' => $checked,
                    'id' => 'pm_use_ssl',
                    'name' => '_plugin_manager_use_ssl',
                    'value' => 1
                ))) . html::tag('small', null, '&nbsp;(' . $this->gettext('serverwide') . ')')
            );
            $checked                                       = false;
            if ($this->use_hmail) {
                $checked = true;
            }
            $args['blocks']['plugin_manager']['options'][] = array(
                'title' => $this->gettext('use_hmail'),
                'content' => ($readonly ? html::tag('input', array(
                    'type' => 'checkbox',
                    'disabled' => true,
                    'checked' => $checked
                )) : html::tag('input', array(
                    'onclick' => '$("#plugin_manager_overlay").show(); document.forms.form.submit()',
                    'type' => 'checkbox',
                    'checked' => $checked,
                    'id' => 'pm_use_hmail',
                    'name' => '_plugin_manager_hmail',
                    'value' => 1
                ))) . html::tag('small', null, '&nbsp;(' . $this->gettext('serverwide') . ')')
            );
            $checked                                       = false;
            if ($this->compress_html) {
                $checked = true;
            }
            $args['blocks']['plugin_manager']['options'][] = array(
                'title' => $this->gettext('compress_html'),
                'content' => ($readonly ? html::tag('input', array(
                    'type' => 'checkbox',
                    'disabled' => true,
                    'checked' => $checked
                )) : html::tag('input', array(
                    'onclick' => '$("#plugin_manager_overlay").show(); document.forms.form.submit()',
                    'type' => 'checkbox',
                    'checked' => $checked,
                    'id' => 'pm_compress_html',
                    'name' => '_plugin_manager_compress_html',
                    'value' => 1
                ))) . html::tag('small', null, '&nbsp;(' . $this->gettext('serverwide') . ')')
            );
            $checked                                       = false;
            if ($this->rcmail->config->get('plugin_manager_about_link', true)) {
                $checked = true;
            }
            $args['blocks']['plugin_manager']['options'][] = array(
                'title' => $this->gettext('show_about_link'),
                'content' => ($readonly ? html::tag('input', array(
                    'type' => 'checkbox',
                    'disabled' => true,
                    'checked' => $checked
                )) : html::tag('input', array(
                    'onclick' => '$("#plugin_manager_overlay").show(); if($(this).prop("checked")){parent.$(".about-link").show()}else{parent.$(".about-link").hide()}; document.forms.form.submit()',
                    'type' => 'checkbox',
                    'checked' => $checked,
                    'id' => 'pm_about_link',
                    'name' => '_plugin_manager_about_link',
                    'value' => 1
                ))) . html::tag('small', null, '&nbsp;(' . $this->gettext('serverwide') . ')')
            );
            if ($this->rcmail->config->get('support_url')) {
                $checked = false;
                if ($this->rcmail->config->get('plugin_manager_support_link', true)) {
                    $checked = true;
                }
                $args['blocks']['plugin_manager']['options'][] = array(
                    'title' => $this->gettext('show_support_link'),
                    'content' => ($readonly ? html::tag('input', array(
                        'type' => 'checkbox',
                        'disabled' => true,
                        'checked' => $checked
                    )) : html::tag('input', array(
                        'onclick' => '$("#plugin_manager_overlay").show(); if($(this).prop("checked")){parent.$(".support-link").show()}else{parent.$(".support-link").hide()}; document.forms.form.submit()',
                        'type' => 'checkbox',
                        'checked' => $checked,
                        'id' => 'pm_support_link',
                        'name' => '_plugin_manager_support_link',
                        'value' => 1
                    ))) . html::tag('small', null, '&nbsp;(' . $this->gettext('serverwide') . ')')
                );
            }
            $checked = false;
            if ($this->rcmail->config->get('plugin_manager_myroundcube_watermark', true)) {
                $checked = true;
            }
            $args['blocks']['plugin_manager']['options'][] = array(
                'title' => $this->gettext('use_myroundcube_watermark'),
                'content' => ($readonly ? html::tag('input', array(
                    'type' => 'checkbox',
                    'disabled' => true,
                    'checked' => $checked
                )) : html::tag('input', array(
                    'onclick' => '$("#plugin_manager_overlay").show(); document.forms.form.submit()',
                    'type' => 'checkbox',
                    'checked' => $checked,
                    'id' => 'pm_myroundcube_watermark',
                    'name' => '_plugin_manager_myroundcube_watermark',
                    'value' => 1
                ))) . html::tag('small', null, '&nbsp;(' . $this->gettext('serverwide') . ')')
            );
            $checked                                       = false;
            if ($this->rcmail->config->get('plugin_manager_remove_watermark', false)) {
                $checked = true;
            }
            $args['blocks']['plugin_manager']['options'][] = array(
                'title' => $this->gettext('remove_watermark'),
                'content' => ($readonly ? html::tag('input', array(
                    'type' => 'checkbox',
                    'disabled' => true,
                    'checked' => $checked
                )) : html::tag('input', array(
                    'onclick' => '$("#plugin_manager_overlay").show(); document.forms.form.submit()',
                    'type' => 'checkbox',
                    'checked' => $checked,
                    'id' => 'pm_remove_watermark',
                    'name' => '_plugin_manager_remove_watermark',
                    'value' => 1
                ))) . html::tag('small', null, '&nbsp;(' . $this->gettext('serverwide') . ')')
            );
            $checked                                       = false;
            if ($this->rcmail->config->get('plugin_manager_maintenance_mode')) {
                $checked = true;
            }
            $args['blocks']['plugin_manager']['options'][] = array(
                'title' => $this->gettext('maintenance_mode') . ':' . html::tag('br') . html::tag('small', null, $this->gettext('maintenance_mode_hint')),
                'content' => html::tag('input', array(
                    'onclick' => '$("#plugin_manager_overlay").show(); document.forms.form.submit()',
                    'type' => 'checkbox',
                    'checked' => $checked,
                    'id' => 'pm_maintenanceMode',
                    'name' => '_plugin_manager_maintenance_mode',
                    'value' => 1
                )) . html::tag('small', null, '&nbsp;(' . $this->gettext('serverwide') . ')')
            );
            $readonly                                      = true;
            if ($this->admins[$this->rcmail->user->data['username']] == 0) {
                $readonly = false;
            }
            $checked = false;
            if ($this->rcmail->config->get('plugin_manager_update_notifications')) {
                $checked = true;
            }
            $args['blocks']['plugin_manager']['options'][] = array(
                'title' => $this->gettext('enable_notifications') . ':' . html::tag('br') . html::tag('small', null, $this->gettext('enable_notifications_note')),
                'content' => ($readonly ? html::tag('input', array(
                    'type' => 'checkbox',
                    'disabled' => true,
                    'checked' => $checked
                )) : html::tag('input', array(
                    'onclick' => '$("#plugin_manager_overlay").show(); document.forms.form.submit()',
                    'type' => 'checkbox',
                    'checked' => $checked,
                    'id' => 'pm_update_notifications',
                    'name' => '_plugin_manager_update_notifications',
                    'value' => 1
                ))) . html::tag('small', null, '&nbsp;(' . $this->gettext('serverwide') . ')')
            );
            $readonly                                      = true;
            if ($this->admins[$this->rcmail->user->data['username']] == 0) {
                $readonly = false;
            }
            $args['blocks']['plugin_manager']['options'][] = array(
                'title' => $this->gettext('notifications_cc') . ':' . html::tag('br') . html::tag('small', null, $this->gettext('notifications_cc_note')),
                'content' => ($readonly ? html::tag('input', array(
                    'type' => 'text',
                    'disabled' => true,
                    'value' => $this->rcmail->config->get('plugin_manager_update_notifications_cc')
                )) : html::tag('input', array(
                    'placeholder' => 'john.doh@gmail.com',
                    'size' => '30',
                    'type' => 'text',
                    'id' => 'pm_update_notifications_cc',
                    'name' => '_plugin_manager_update_notifications_cc',
                    'value' => $this->rcmail->config->get('plugin_manager_update_notifications_cc')
                )) . '&nbsp;' . html::tag('small', null, '[' . html::tag('a', array(
                    'href' => '#',
                    'onclick' => '$("#plugin_manager_overlay").show(); document.forms.form.submit()'
                ), $this->gettext('save')) . ']')) . html::tag('small', null, '&nbsp;(' . $this->gettext('serverwide') . ')')
            );
            $checked                                       = false;
            if ($this->rcmail->config->get('plugin_manager_show_myrc_messages')) {
                $checked = true;
            }
            $args['blocks']['plugin_manager']['options'][] = array(
                'title' => $this->gettext('show_myrc_messages'),
                'content' => html::tag('input', array(
                    'onclick' => '$("#plugin_manager_overlay").show(); document.forms.form.submit()',
                    'type' => 'checkbox',
                    'checked' => $checked,
                    'id' => 'pm_show_myrc_messages',
                    'name' => '_plugin_manager_show_myrc_messages',
                    'value' => 1
                ))
            );
            $args['blocks']['plugin_manager']['options'][] = array(
                'title' => $this->gettext('translationaccount'),
                'content' => html::tag('input', array(
                    'type' => 'text',
                    'size' => 30,
                    'placeholder' => 'john.doh@gmail.com',
                    'id' => 'pm_translation_account',
                    'name' => '_plugin_manager_translation_account',
                    'value' => $this->rcmail->config->get('plugin_manager_translation_account')
                )) . '&nbsp;' . html::tag('small', null, '[' . html::tag('a', array(
                    'href' => '#',
                    'onclick' => '$("#plugin_manager_overlay").show(); document.forms.form.submit()'
                ), $this->gettext('save')) . ']' . '&nbsp;' . html::tag('a', array(
                    'href' => 'http://myroundcube.com/myroundcube-plugins/real-time-translation',
                    'target' => '_blank'
                ), $this->gettext('whatsthis')))
            );
            $args['blocks']['plugin_manager']['options'][] = array(
                'title' => $this->gettext('translationserver'),
                'content' => html::tag('input', array(
                    'type' => 'text',
                    'placeholder' => 'ssl://imap.gmail.com:993',
                    'size' => 30,
                    'id' => 'pm_translation_server',
                    'name' => '_plugin_manager_translation_server',
                    'value' => $this->rcmail->config->get('plugin_manager_translation_server')
                )) . '&nbsp;' . html::tag('small', null, '[' . html::tag('a', array(
                    'href' => '#',
                    'onclick' => '$("#plugin_manager_overlay").show(); document.forms.form.submit()'
                ), $this->gettext('save')) . ']')
            );
            if (!$this->file_based_config) {
                $options        = html::tag('option', null, '--');
                $plugins_sorted = array();
                foreach ($this->defaults as $section => $plugins) {
                    foreach ($plugins as $plugin => $props) {
                        $plugins_sorted[$plugin] = $section;
                    }
                }
                ksort($plugins_sorted);
                foreach ($plugins_sorted as $plugin => $section) {
                    if (!in_array($plugin, $this->noselect)) {
                        $options .= html::tag('option', array(
                            'value' => $section,
                            'id' => 'option_' . $plugin
                        ), $plugin);
                    }
                }
                $selector                                      = html::tag('select', array(
                    'id' => 'pluginselector',
                    'onchange' => '$(".row").attr("style", ""); if(!$("#" + $(this).val()).is(":visible")){ $("#tab" + $(this).val()).trigger("click") }; $("#row_" + ($(this).find(":selected").text())).attr("style", "border-left: 7px solid #000000;"); window.location.href="#row_" + $(this).find(":selected").text();'
                ), $options);
                $args['blocks']['plugin_manager']['options'][] = array(
                    'title' => $this->gettext('select_plugin'),
                    'content' => $selector
                );
                $tabs                                          = '';
                $divs                                          = '';
                include INSTALL_PATH . 'plugins/plugin_manager/defaults.inc.php';
                $defaults         = $config['plugin_manager_defaults'];
                $release_defaults = $defaults;
                $conf             = array();
                foreach ($defaults as $section => $plugins) {
                    foreach ($plugins as $plugin => $props) {
                        $conf[$plugin] = 1;
                    }
                }
                $options   = array();
                $sql       = 'SELECT * FROM ' . get_table_name('plugin_manager') . ' WHERE ' . $this->q('conf') . '=?';
                $res       = $this->rcmail->db->limitquery($sql, 0, 1, 'defaults');
                $overwrite = $this->rcmail->db->fetch_assoc($res);
                if ($overwrite['value']) {
                    $overwrite = unserialize($overwrite['value']);
                    if (is_array($overwrite)) {
                        $defaults = array_merge($defaults, $overwrite);
                    }
                }
                $sql       = 'SELECT * FROM ' . get_table_name('plugin_manager') . ' WHERE ' . $this->q('conf') . '=?';
                $res       = $this->rcmail->db->limitquery($sql, 0, 1, 'defaults_overwrite');
                $overwrite = $this->rcmail->db->fetch_assoc($res);
                if ($overwrite['value']) {
                    $overwrite = unserialize($overwrite['value']);
                    if (is_array($overwrite)) {
                        foreach ($overwrite as $section => $plugins) {
                            foreach ($plugins as $plugin => $props) {
                                foreach ($props as $key => $val) {
                                    $defaults[$section][$plugin][$key] = $val;
                                }
                            }
                        }
                    }
                }
                foreach ($defaults as $section => $plugins) {
                    foreach ($plugins as $plugin => $props) {
                        $options[$plugin] = 1;
                    }
                }
                foreach ($this->rcmail->config->get('plugins', array()) as $plugin) {
                    $options[$plugin] = 1;
                }
                $scope  = scandir(INSTALL_PATH . 'plugins');
                $select = array();
                foreach ($scope as $dir) {
                    if (file_exists(INSTALL_PATH . 'plugins/' . $dir . '/' . $dir . '.php')) {
                        if (!isset($options[$dir])) {
                            if (!in_array($dir, $this->skip)) {
                                $select[$dir] = 1;
                            }
                        } else if (!isset($conf[$dir])) {
                            if (!in_array($dir, $this->skip)) {
                                $select[$dir] = -1;
                            }
                        }
                    }
                }
                $sel_add = html::tag('option', null, '--');
                ksort($options);
                foreach ($select as $plugin => $available) {
                    if ($available == 1) {
                        $sel_add .= html::tag('option', array(
                            'value' => $plugin
                        ), $plugin);
                    }
                }
                foreach ($this->defaults as $section => $plugins) {
                    foreach ($select as $plugin => $available) {
                        if ($available == -1 && isset($plugins[$plugin])) {
                            $sel_remove[$section] .= html::tag('option', array(
                                'value' => $plugin
                            ), $plugin);
                        }
                    }
                    if (function_exists('mb_substr')) {
                        $truncate = mb_substr($this->gettext($section), 0, 9);
                    } else {
                        $truncate = substr($this->gettext($section), 0, 9);
                    }
                    $tabs .= html::tag('li', array(
                        'onclick' => 'window.location.href="#pm_translation_server"'
                    ), html::tag('a', array(
                        'href' => '#' . $section,
                        'onclick' => 'parent.rcmail.env.section="' . $section . '"',
                        'id' => 'tab' . $section,
                        'title' => $this->gettext($section)
                    ), strlen($this->gettext($section)) > 12 ? $truncate . '...' : $this->gettext($section)));
                    $legend = html::tag('div', array(
                        'style' => 'float: left; width: 200px;'
                    ), html::tag('span', array(
                        'style' => 'font-size: 11px; font-weight: normal;'
                    ), '&nbsp;' . $this->gettext('legend') . ':') . html::tag('table', null, html::tag('tr', null, html::tag(td, array(
                        'nowrap' => true
                    ), html::tag('input', array(
                        'type' => 'checkbox',
                        'disabled' => true
                    )) . html::tag('input', array(
                        'type' => 'checkbox',
                        'disabled' => true
                    ))) . html::tag('td', array(
                        'nowrap' => true,
                        'style' => 'font-size: 9px; font-weight: normal; color: #188c18;'
                    ), $this->gettext('plugindisabledbydefault'))) . html::tag('tr', null, html::tag(td, null, html::tag('input', array(
                        'type' => 'checkbox',
                        'disabled' => true,
                        'checked' => true
                    )) . html::tag('input', array(
                        'type' => 'checkbox',
                        'disabled' => true
                    ))) . html::tag('td', array(
                        'nowrap' => true,
                        'style' => 'font-size: 9px; font-weight: normal; color: #188c18;'
                    ), $this->gettext('pluginenabledbydefault'))) . html::tag('tr', null, html::tag(td, null, html::tag('input', array(
                        'type' => 'checkbox',
                        'disabled' => true
                    )) . html::tag('input', array(
                        'type' => 'checkbox',
                        'disabled' => true,
                        'checked' => true
                    ))) . html::tag('td', array(
                        'nowrap' => true,
                        'style' => 'font-size: 9px; font-weight: normal; color: #8a8a8a;'
                    ), $this->gettext('loads_never'))) . html::tag('tr', null, html::tag(td, null, html::tag('input', array(
                        'type' => 'checkbox',
                        'disabled' => true,
                        'checked' => true
                    )) . html::tag('input', array(
                        'type' => 'checkbox',
                        'disabled' => true,
                        'checked' => true
                    ))) . html::tag('td', array(
                        'nowrap' => true,
                        'style' => 'font-size: 9px; font-weight: normal; color: #ff1b1b;'
                    ), $this->gettext('pluginmandatory')))));
                    $rows   = array();
                    $skins  = array();
                    $files  = scandir(INSTALL_PATH . 'skins');
                    foreach ($files as $file) {
                        if (is_dir(INSTALL_PATH . 'skins/' . $file) && $file != '.' && $file != '..') {
                            $skins[] = $file;
                        }
                    }
                    $skinoptions  = html::tag('option', array(
                        'value' => 'all'
                    ), $this->gettext('all'));
                    $combinations = '';
                    foreach ($skins as $skin) {
                        if ($combinations) {
                            $combinations .= '|';
                        }
                        $combinations .= $skin;
                        if ($combinations != implode('|', $skins)) {
                            $skinoptions .= html::tag('option', array(
                                'value' => $combinations
                            ), $combinations);
                        }
                    }
                    foreach ($plugins as $plugin => $props) {
                        if ($props['autoload']) {
                            continue;
                        }
                        $name  = $this->gettext($plugin . '.pluginname');
                        $title = $this->gettext($plugin . '.plugindescription');
                        if (substr($name, 0, 1) == '[') {
                            $name  = $this->gettext($plugin . '_pluginname');
                            $title = $this->gettext($plugin . '_plugindescription');
                        }
                        if (substr($name, 0, 1) == '[') {
                            if (file_exists(INSTALL_PATH . 'plugins/' . $plugin . '/localization/en_US.inc')) {
                                include INSTALL_PATH . 'plugins/' . $plugin . '/localization/en_US.inc';
                                $en_us_labels = $labels;
                                if (file_exists(INSTALL_PATH . 'plugins/' . $plugin . '/localization/' . $_SESSION['language'] . '.inc')) {
                                    include INSTALL_PATH . 'plugins/' . $plugin . '/localization/' . $_SESSION['language'] . '.inc';
                                    $labels = array_merge($en_us_labels, $labels);
                                }
                                if (isset($labels['pluginname'])) {
                                    $name = $labels['pluginname'];
                                }
                                if (isset($labels['plugindescription'])) {
                                    $title = $labels['plugindescription'];
                                } else {
                                    $title = '';
                                }
                            } else {
                                $title = '';
                            }
                        }
                        $docs = false;
                        if (substr($name, 0, 1) == '[') {
                            $name = $plugin . '&nbsp;' . html::tag('small', null, html::tag('span', null, '[') . html::tag('a', array(
                                'href' => "mailto:dev-team@myroundcube.com?subject=Third party plugin localization (" . $plugin . ")&body=Please add localization labels to the next Plugin Manager localization update.%0D%0A%0D%0APlugin: " . $plugin . "%0D%0A%0D%0ALanguage: en_US (English please)%0D%0APlugin name:%0D%0APlugin description:"
                            ), html::tag('span', array(
                                'style' => 'color: #ff1b1b;'
                            ), $this->gettext('localizationmissing'))) . html::tag('span', null, ']'));
                        }
                        if (is_array($release_defaults[$section][$plugin])) {
                            $docs = true;
                        }
                        $isactive    = $props['active'] ? true : false;
                        $isprotected = $props['protected'] ? true : false;
                        if (is_string($props['protected'])) {
                            $isprotected = $props['protected'];
                        }
                        if ($disable = get_input_value('_plugin', RCUBE_INPUT_GET)) {
                            if ($disable == $plugin) {
                                $isactive    = false;
                                $isprotected = true;
                                if (is_array($props['protected'])) {
                                    $isprotected = $props['protected'];
                                }
                            }
                        }
                        $status = false;
                        $bind   = '';
                        $unbind = '';
                        $linked = '';
                        $error  = '';
                        if (class_exists('db_config') && $this->defaults['globalplugins']['db_config']['active'] && RCMAIL_VERSION > '0.8.6') {
                            $isconfigured = true;
                            if (file_exists(INSTALL_PATH . 'plugins/' . $plugin . '/config.inc.php.dist')) {
                                $sql = 'SELECT * FROM ' . get_table_name('db_config') . ' WHERE ' . $this->q('env') . '=?';
                                $res = $this->rcmail->db->limitquery($sql, 0, 1, $plugin);
                                if (!is_array($this->rcmail->db->fetch_assoc($res))) {
                                    if ($plugin != 'global_config' && file_exists(INSTALL_PATH . 'plugins/global_config/config.inc.php')) {
                                        if (!file_exists(INSTALL_PATH . 'plugins/' . $plugin . 'config.inc.php')) {
                                            $config        = array();
                                            $rcmail_config = array();
                                            include INSTALL_PATH . 'plugins/' . $plugin . '/config.inc.php.dist';
                                            if (is_array($rcmail_config)) {
                                                $config        = $rcmail_config;
                                                $rcmail_config = array();
                                            }
                                            $defconf = $config;
                                            $config  = array();
                                            include INSTALL_PATH . 'plugins/global_config/config.inc.php';
                                            if (file_exists(INSTALL_PATH . 'plugins/' . $plugin . '/config.inc.php')) {
                                                include INSTALL_PATH . 'plugins/' . $plugin . '/config.inc.php';
                                                if (is_array($rcmail_config)) {
                                                    $config = $rcmail_config;
                                                }
                                            }
                                            foreach ($defconf as $key => $value) {
                                                if (isset($config[$key])) {
                                                    $isconfigured = true;
                                                    break;
                                                } else {
                                                    $isconfigured = false;
                                                }
                                            }
                                        }
                                    }
                                }
                                $democlick = '';
                                if (strtolower($this->get_demo($_SESSION['username'])) == strtolower(sprintf($this->rcmail->config->get('demo_user_account'), ""))) {
                                    $democlick = 'rcmail.display_message("' . $this->gettext('demoaccount') . '", "error"); return false';
                                }
                                if (!$isconfigured) {
                                    $error .= '&nbsp;' . html::tag('small', array(
                                        'style' => 'color: #ff1b1b;'
                                    ), '[' . html::tag('a', array(
                                        'style' => 'color: #ff1b1b;',
                                        'onclick' => $democlick,
                                        'href' => './?_action=plugin.plugin_manager_show_config&_framed=1&_plugin=' . $plugin
                                    ), $this->gettext('notconfigured')) . ']');
                                } else if (file_exists(INSTALL_PATH . 'plugins/' . $plugin . '/config.inc.php.dist')) {
                                    $error .= '&nbsp;' . html::tag('small', array(
                                        'style' => 'color: #188c18;'
                                    ), '[' . html::tag('a', array(
                                        'style' => 'color: #188c18;',
                                        'onclick' => $democlick,
                                        'href' => './?_action=plugin.plugin_manager_edit_config&_framed=1&_plugin=' . $plugin
                                    ), $this->gettext('editconfig')) . ']');
                                }
                            }
                        }
                        if (class_exists('mysqladmin') && class_exists($plugin) && method_exists($plugin, 'about') && strtolower($this->rcmail->config->get('mysql_admin')) == strtolower($this->rcmail->user->data['username'])) {
                            $class    = new $plugin(false);
                            $sqladmin = $class->about(array(
                                'sqladmin'
                            ));
                            if ($sqladmin['sqladmin']) {
                                $sqladmin = $sqladmin['sqladmin'];
                                $error .= '&nbsp;' . html::tag('small', array(
                                    'style' => 'color: #188c18;'
                                ), '[' . html::tag('a', array(
                                    'style' => 'color: #188c18;',
                                    'onclick' => 'var temp = document.location.href.split(\'&_expand\'); rcmail.set_cookie(\'PMA_referrer\', temp[0] + \'&_expand=' . $plugin . '\');',
                                    'href' => './?_action=plugin.mysqladmin&pma_login=1&db=' . $sqladmin[0] . '&dbt=' . $sqladmin[1]
                                ), $this->gettext('PHPMyAdmin')) . ']');
                            }
                        }
                        if (class_exists($plugin) && !method_exists($plugin, 'about')) {
                            $error .= '&nbsp;' . html::tag('small', array(
                                'style' => 'color: #8a8a8a;'
                            ), '[' . html::tag('i', null, str_replace('.', '', $this->gettext('thirdparty'))) . ']');
                        }
                        $active = html::tag('td', array(
                            'align' => 'center'
                        ), html::tag('input', array(
                            'onclick' => '$("#plugin_manager_overlay").show(); document.forms.form.action = document.forms.form.action + "?_plugin=' . $plugin . '"; document.forms.form.submit()',
                            'name' => '_plugin_manager_defaults[' . $section . '][' . $plugin . '][active]',
                            'value' => 1,
                            'checked' => $isactive ? true : false,
                            'type' => 'checkbox'
                        )));
                        if (is_string($release_defaults[$section][$plugin]['protected']) && !is_string($props['protected'])) {
                            if (substr($this->gettext($release_defaults[$section][$plugin]['config_label']), 0, 1) != '[') {
                                $bind   = '&nbsp;' . html::tag('small', null, '[' . html::tag('a', array(
                                    'title' => $this->gettext('loads_linked') . ': ' . $this->gettext($release_defaults[$section][$plugin]['config_label']),
                                    'href' => './?_task=settings&_action=plugin.plugin_manager_bind&_section=' . $section . '&_target=' . $plugin
                                ), $this->gettext('bind')) . ']');
                                $active = html::tag('td');
                            }
                        }
                        if (is_string($props['protected'])) {
                            $linked = $this->gettext('loads_linked') . ':' . html::tag('br') . $this->gettext($section) . html::tag('br') . '-&nbsp';
                            $status = $props['config_label'] ? $props['config_label'] : $props['protected'];
                            if (substr($this->gettext($status), 0, 1) == '[') {
                                $this->rcmail->output->add_script('document.location.href="./?_task=settings&_action=plugin.plugin_manager_unbind&_section=' . $section . '&_target=' . $plugin . '";', 'docready');
                            }
                            $unbind = '&nbsp;' . html::tag('small', null, '[' . html::tag('a', array(
                                'href' => './?_task=settings&_action=plugin.plugin_manager_unbind&_section=' . $section . '&_target=' . $plugin
                            ), $this->gettext('unbind')) . ']');
                            $color  = '#188c18';
                            $active = html::tag('td', array(
                                'align' => 'center'
                            ), html::tag('input', array(
                                'name' => '_plugin_manager_defaults[' . $section . '][' . $plugin . '][active]',
                                'value' => 0,
                                'type' => 'hidden'
                            )));
                        } else if ($isactive && $isprotected) {
                            $status = 'loads_always';
                            $color  = '#ff1b1b';
                        } else if ((!$isactive && $isprotected) || (($section == 'globalplugins' || $section == 'performance') && !$isactiv && !$isprotected)) {
                            $status = 'loads_never';
                            $color  = '#8a8a8a';
                        } else {
                            $status = 'loads_by_user';
                            $color  = '#188c18';
                        }
                        if ($section == 'globalplugins' || $section == 'performance' || $bind) {
                            if ($bind) {
                                $protected = html::tag('td');
                            } else {
                                $protected = html::tag('td', array(
                                    'align' => 'center'
                                ), html::tag('input', array(
                                    'disabled' => true,
                                    'checked' => true,
                                    'type' => 'checkbox'
                                )));
                            }
                            $protected .= html::tag('input', array(
                                'name' => '_plugin_manager_defaults[' . $section . '][' . $plugin . '][protected]',
                                'value' => 1,
                                'type' => 'hidden'
                            ));
                        } else {
                            if (is_string($isprotected)) {
                                $protected = html::tag('td', array(
                                    'align' => 'center'
                                ), html::tag('input', array(
                                    'name' => '_plugin_manager_defaults[' . $section . '][' . $plugin . '][protected]',
                                    'value' => $isprotected,
                                    'type' => 'hidden'
                                )));
                            } else {
                                $protected = html::tag('td', array(
                                    'align' => 'center'
                                ), html::tag('input', array(
                                    'onclick' => '$("#plugin_manager_overlay").show(); document.forms.form.action = document.forms.form.action + "?_plugin=' . $plugin . '"; document.forms.form.submit()',
                                    'name' => '_plugin_manager_defaults[' . $section . '][' . $plugin . '][protected]',
                                    'value' => 1,
                                    'checked' => $isprotected ? true : false,
                                    'type' => 'checkbox'
                                )));
                            }
                        }
                        if (!class_exists($plugin)) {
                            $this->require_plugin($plugin);
                        }
                        if (method_exists($plugin, 'about')) {
                            $class        = new $plugin(false);
                            $requirements = $class->about();
                            $required     = $requirements['db_version'];
                            if (is_array($required)) {
                                $required = implode('|', $required);
                                $sql      = 'SELECT * FROM ' . get_table_name('system') . ' WHERE ' . $this->q('name') . '=?';
                                $res      = $this->rcmail->db->limitquery($sql, 0, 1, 'myrc_' . $plugin);
                                $db       = $this->rcmail->db->fetch_assoc($res);
                                $db       = $db['value'];
                                if ($db != $required && strtolower($this->get_demo($_SESSION['username'])) != strtolower(sprintf($this->rcmail->config->get('demo_user_account'), ""))) {
                                    if (!$isactive && !$isprotected) {
                                        $isprotected = true;
                                        if (is_string($isprotected)) {
                                            $protected = html::tag('td', array(
                                                'align' => 'center'
                                            ), html::tag('input', array(
                                                'name' => '_plugin_manager_defaults[' . $section . '][' . $plugin . '][protected]',
                                                'value' => 1,
                                                'type' => 'hidden'
                                            )));
                                        } else {
                                            $protected = html::tag('td', array(
                                                'align' => 'center'
                                            ), html::tag('input', array(
                                                'onclick' => '$("#plugin_manager_overlay").show(); document.forms.form.action = document.forms.form.action + "?_plugin=' . $plugin . '"; document.forms.form.submit()',
                                                'name' => '_plugin_manager_defaults[' . $section . '][' . $plugin . '][protected]',
                                                'value' => 1,
                                                'checked' => true,
                                                'type' => 'checkbox'
                                            )));
                                        }
                                        $this->rcmail->output->add_script('$("#plugin_manager_overlay").show(); document.forms.form.submit();', 'docready');
                                    }
                                    $status = 'errordb';
                                    $color  = '#ff1b1b';
                                    $temp   = explode('.', RCMAIL_VERSION);
                                    if (($temp[0] == 0 && $temp[1] > 8) || $temp[0] > 0) {
                                        if ($isactive && $isprotected) {
                                            if ($class->task) {
                                                foreach ($this->rctasks as $task) {
                                                    if (preg_match('/^(' . $class->task . ')$/i', $task)) {
                                                        $link = './?_task=' . $task . '&_plugin_manager_settings_section=' . $section;
                                                        break;
                                                    }
                                                }
                                            } else {
                                                $link = './?_task=settings&_plugin_manager_settings_section=' . $section;
                                            }
                                            $_SESSION['db_version_lock'] = true;
                                            $status                      = 'errordb8';
                                            $error                       = html::tag('br') . html::tag('a', array(
                                                'href' => 'javascript:void(0)',
                                                'onclick' => 'window.setTimeout(\'parent.location.href="' . $link . '"\', 500);'
                                            ), $this->gettext('dbautomatically'));
                                            $this->rcmail->output->show_message('plugin_manager.dbautomatically', 'notice');
                                        }
                                    } else {
                                        $status = 'errordb8';
                                        $error  = html::tag('br') . html::tag('a', array(
                                            'href' => 'http://myroundcube.com/myroundcube-plugins/faqs/myroundcube-plugins-database-versioning-support',
                                            'target' => '_blank'
                                        ), $this->gettext('dbmanually'));
                                    }
                                }
                            }
                            $required = $requirements['requirements']['required_plugins'];
                            if (is_array($required)) {
                                $missing = array();
                                foreach ($required as $key => $val) {
                                    if (!file_exists(INSTALL_PATH . 'plugins/' . $key . '/' . $key . '.php')) {
                                        $missing[] = $key;
                                    }
                                }
                                if (count($missing) > 0) {
                                    $active    = html::tag('td', null, '&nbsp;');
                                    $protected = html::tag('td', null, '&nbsp;');
                                    $status    = 'errorplugin';
                                    $color     = '#ff1b1b';
                                    if (RCMAIL_VERSION > $this->stable) {
                                        $branch = 'dev';
                                    } else {
                                        $branch = 'stable';
                                    }
                                    $error = '&nbsp;(' . html::tag('a', array(
                                        'href' => 'javascript:void(0)',
                                        'onclick' => 'document.location.href="./?_task=settings&_action=plugin.plugin_manager_update&_framed=1&_branch=' . $branch . '"'
                                    ), implode(', ', $missing)) . ')';
                                }
                            }
                            $required = $requirements['requirements']['Roundcube'];
                            if (isset($required)) {
                                if (RCMAIL_VERSION < $required) {
                                    $active    = html::tag('td', null, html::tag('input', array(
                                        'name' => '_plugin_manager_defaults[' . $section . '][' . $plugin . '][active]',
                                        'value' => 0,
                                        'type' => 'hidden'
                                    )));
                                    $protected = html::tag('td', null, html::tag('input', array(
                                        'name' => '_plugin_manager_defaults[' . $section . '][' . $plugin . '][protected]',
                                        'value' => 1,
                                        'type' => 'hidden'
                                    )));
                                    $status    = 'errorrcversion';
                                    $color     = '#ff1b1b';
                                    $error     = '&nbsp;v' . $required . '&nbsp;' . $this->gettext('ornewer') . html::tag('br') . html::tag('small', array(
                                        'style' => 'color: #000000;'
                                    ), '[' . html::tag('a', array(
                                        'href' => 'http://roundcube.net/download',
                                        'target' => '_blank'
                                    ), $this->gettext('official_releases')) . ']');
                                    if ($isactive || !$isprotected) {
                                        $this->rcmail->output->add_script('$("#plugin_manager_overlay").show(); document.forms.form.submit();', 'docready');
                                    }
                                }
                            }
                            $required = $requirements['requirements']['PHP'];
                            if (isset($required)) {
                                $temp   = explode('+', $required);
                                $module = trim($temp[1]);
                                if (strtolower($module) == 'curl') {
                                    if (!function_exists('curl_init')) {
                                        $active    = html::tag('td', null, html::tag('input', array(
                                            'name' => '_plugin_manager_defaults[' . $section . '][' . $plugin . '][active]',
                                            'value' => 0,
                                            'type' => 'hidden'
                                        )));
                                        $protected = html::tag('td', null, html::tag('input', array(
                                            'name' => '_plugin_manager_defaults[' . $section . '][' . $plugin . '][protected]',
                                            'value' => 1,
                                            'type' => 'hidden'
                                        )));
                                        $status    = 'errorphpcurl';
                                        $color     = '#ff1b1b';
                                        $error     = '&nbsp;' . html::tag('small', array(
                                            'style' => 'color: #000000;'
                                        ), '[' . html::tag('a', array(
                                            'href' => 'http://php.net/manual/en/book.curl.php',
                                            'target' => '_blank'
                                        ), 'PHP cURL') . ']');
                                        if ($isactive || !$isprotected) {
                                            $this->rcmail->output->add_script('$("#plugin_manager_overlay").show(); document.forms.form.submit();', 'docready');
                                        }
                                    }
                                }
                                if (strtolower($module) == 'finfo') {
                                    if (!function_exists('finfo_open')) {
                                        $active    = html::tag('td', null, html::tag('input', array(
                                            'name' => '_plugin_manager_defaults[' . $section . '][' . $plugin . '][active]',
                                            'value' => 0,
                                            'type' => 'hidden'
                                        )));
                                        $protected = html::tag('td', null, html::tag('input', array(
                                            'name' => '_plugin_manager_defaults[' . $section . '][' . $plugin . '][protected]',
                                            'value' => 1,
                                            'type' => 'hidden'
                                        )));
                                        $status    = 'errorphpcurl';
                                        $status    = 'errorphpfinfo';
                                        $color     = '#ff1b1b';
                                        $error     = '&nbsp;' . html::tag('small', array(
                                            'style' => 'color: #000000;'
                                        ), '[' . html::tag('a', array(
                                            'href' => 'http://php.net/manual/en/book.fileinfo.php',
                                            'target' => '_blank'
                                        ), 'PHP finfo') . ']');
                                        if ($isactive || !$isprotected) {
                                            $this->rcmail->output->add_script('$("#plugin_manager_overlay").show(); document.forms.form.submit();', 'docready');
                                        }
                                    }
                                }
                                $php = trim($temp[0]);
                                if (PHP_VERSION < $php) {
                                    $active    = html::tag('td', null, html::tag('input', array(
                                        'name' => '_plugin_manager_defaults[' . $section . '][' . $plugin . '][active]',
                                        'value' => 0,
                                        'type' => 'hidden'
                                    )));
                                    $protected = html::tag('td', null, html::tag('input', array(
                                        'name' => '_plugin_manager_defaults[' . $section . '][' . $plugin . '][protected]',
                                        'value' => 1,
                                        'type' => 'hidden'
                                    )));
                                    $status    = 'errorphpversion';
                                    $color     = '#ff1b1b';
                                    $error     = '&nbsp;v' . $required;
                                    if ($isactive || !$isprotected) {
                                        $this->rcmail->output->add_script('$("#plugin_manager_overlay").show(); document.forms.form.submit();', 'docready');
                                    }
                                }
                            }
                        }
                        $skins = html::tag('td', null, html::tag('select', array(
                            'id' => 'skin_sel_' . $plugin,
                            'name' => '_skins[' . $section . '][' . $plugin . ']',
                            'onchange' => 'document.forms.form.submit()'
                        ), $skinoptions));
                        if (is_array($props['skins'])) {
                            $skins .= html::tag('script', array(
                                'type' => 'text/javascript'
                            ), '$("#skin_sel_' . $plugin . '").val("' . implode('|', $props['skins']) . '");');
                        }
                        if ($plugin == 'db_config') {
                            $skey = '_03';
                            if ($this->admins[$this->rcmail->user->data['username']] != 0) {
                                $active    = html::tag('td', array(
                                    'align' => 'center',
                                    'colspan' => 3
                                ), html::tag('span', array(
                                    'style' => 'font-weight: normal; color: #8a8a8a;'
                                ), $this->gettext('systemadmin') . '&nbsp;(' . $this->gettext('serverwide') . ')') . html::tag('input', array(
                                    'name' => '_plugin_manager_defaults[' . $section . '][' . $plugin . '][active]',
                                    'value' => $this->defaults['globalplugins']['db_config']['active'],
                                    'type' => 'hidden'
                                )) . html::tag('input', array(
                                    'name' => '_plugin_manager_defaults[' . $section . '][' . $plugin . '][protected]',
                                    'value' => 1,
                                    'type' => 'hidden'
                                )));
                                $protected = '';
                                $skins     = '';
                                if ($isactive && $isprotected) {
                                    $status = 'loads_always';
                                    $color  = '#ff1b1b';
                                } else if ((!$isactive && $isprotected)) {
                                    $status = 'loads_never';
                                    $color  = '#8a8a8a';
                                }
                            }
                        } else if (substr($this->gettext($plugin . '.pluginname'), 0, 1) != '[') {
                            $skey = $this->gettext($plugin . '.pluginname');
                        } else if (substr($this->gettext('plugin_manager.' . $plugin . '_pluginname'), 0, 1) != '[') {
                            $skey = $this->gettext('plugin_manager.' . $plugin . '_pluginname');
                        } else {
                            $skey = $plugin;
                        }
                        $rows[strtolower($skey)] = html::tag('a', array(
                            'name' => $plugin
                        ), '') . html::tag('tr', array(
                            'id' => 'row_' . $plugin,
                            'class' => 'row'
                        ), html::tag('td', array(
                            'style' => 'font-weight: normal;'
                        ), html::tag('span', array(
                            'title' => $title
                        ), $name) . ' ' . ($docs ? html::tag('small', null, '[' . html::tag('a', array(
                            'href' => 'http://myroundcube.com/myroundcube-plugins#' . $plugin,
                            'target' => '_blank',
                            'title' => $this->gettext('documentation')
                        ), $plugin) . ']') : '')) . $active . $protected . $skins . html::tag('td', array(
                            'style' => 'font-weight: normal; color: ' . $color
                        ), ($status ? ($linked . $this->gettext($status) . $unbind . $bind . $error) : '&nbsp;')));
                    }
                    $rows['_0'] = html::tag('tr', array(
                        'id' => 'row_new_plugin_' . $section,
                        'class' => 'row'
                    ), html::tag('td', array(
                        'width' => '20%'
                    ), html::tag('select', array(
                        'onchange' => 'document.forms.form.submit()',
                        'name' => '_new_plugin_name[' . $section . ']'
                    ), $sel_add)) . html::tag('td', array(
                        'width' => '30%',
                        'colspan' => 3,
                        'align' => 'center',
                        'style' => 'font-weight: normal; color: #8a8a8a;'
                    ), $this->gettext('addplugin')) . html::tag('td', array(
                        'width' => '50%'
                    ), '&nbsp;'));
                    if ($sel_remove[$section]) {
                        $sel_remove[$section] = html::tag('option', null, '--') . $sel_remove[$section];
                        $rows['_1']           = html::tag('tr', array(
                            'id' => 'row_remove_plugin_' . $section,
                            'class' => 'row'
                        ), html::tag('td', array(
                            'width' => '20%'
                        ), html::tag('select', array(
                            'onchange' => 'document.forms.form.submit()',
                            'name' => '_remove_plugin_name[' . $section . ']'
                        ), $sel_remove[$section])) . html::tag('td', array(
                            'width' => '30%',
                            'colspan' => 3,
                            'align' => 'center',
                            'style' => 'font-weight: normal; color: #8a8a8a;'
                        ), $this->gettext('removeplugin')) . html::tag('td', array(
                            'width' => '50%'
                        ), '&nbsp;'));
                    }
                    ksort($rows);
                    $divs .= html::tag('div', array(
                        'id' => $section
                    ), html::tag('table', array(
                        'class' => 'propform',
                        'width' => '100%'
                    ), html::tag('tr', null, html::tag('th', array(
                        'width' => '20%',
                        'style' => 'font-weight: normal;'
                    ), $this->gettext('plugin')) . html::tag('th', array(
                        'width' => '10%',
                        'style' => 'font-weight: normal;'
                    ), $this->gettext('enabled')) . html::tag('th', array(
                        'width' => '10%',
                        'style' => 'font-weight: normal;'
                    ), $this->gettext('protected')) . html::tag('th', array(
                        'width' => '10%',
                        'align' => 'left',
                        'style' => 'font-weight: normal;'
                    ), '&nbsp;&nbsp;' . $this->gettext('skins')) . html::tag('th', array(
                        'width' => '50%',
                        'align' => 'left',
                        'style' => 'font-weight: normal;'
                    ), '&nbsp;&nbsp;' . $this->gettext('status'))) . implode('', $rows)) . $legend);
                }
                $html                                          = html::tag('div', array(
                    'id' => 'plugin_manager_defaults',
                    'style' => 'display: none;'
                ), html::tag('ul', null, html::tag('style', array(
                    'type' => 'text/css'
                ), '.ui-tabs .ui-tabs-nav li a { font-size: 11px; } table.propform td.title { white-space: normal; }') . $tabs . $divs));
                $args['blocks']['plugin_manager']['options'][] = array(
                    'title' => $html,
                    'content' => html::tag('span', array(
                        'id' => 'remove'
                    ))
                );
                $skin                                          = $this->rcmail->config->get('skin');
                if (!file_exists($this->home . '/skins/' . $skin . '/plugin_manager.css')) {
                    $skin = "classic";
                }
                $this->include_stylesheet('skins/' . $skin . '/plugin_manager.css');
                $this->api->output->add_footer(html::tag('div', array(
                    'id' => 'plugin_manager_overlay'
                )));
            }
            $admins      = array_flip($this->admins);
            $systemadmin = '$(".boxtitle").html($(".boxtitle").html() + "&nbsp;&raquo;&nbsp;' . $this->gettext('systemadmin') . ':&nbsp;' . $admins[0];
            if ($admins[0] != $this->rcmail->user->data['username']) {
                $systemadmin .= '&nbsp;&raquo;&nbsp;' . $this->gettext('admin') . ':&nbsp;' . $admins[0];
            }
            $systemadmin .= '&nbsp;&raquo;&nbsp;<a href=\'javascript:window.scrollTo(0, 0)\'>' . $this->gettext('serverconfiguration') . '</a>&nbsp;|&nbsp;<a onclick=\'return pluginsconfiguration()\' href=\'#pm_translation_server\'>' . $this->gettext('pluginsconfiguration') . '</a>';
            $systemadmin .= '")';
            $this->rcmail->output->add_script('function pluginsconfiguration(){
           if($("#plugin_manager_defaults").tabs("option", "selected") == -1){
             $("#tabglobalplugins").trigger("click");
             return true;
           }
         }
         $(".mainaction").hide();
         $("#remove").parent().remove();
         $("#plugin_manager_defaults").parent().attr("colspan", 2);
         $("#plugin_manager_defaults").tabs({ collapsible: true, active: false });
         if(parent.rcmail.env.section){
           $("#tab" + parent.rcmail.env.section).trigger("click");
         }
         $("#plugin_manager_defaults").show();
         $("td.title").css("width", "300px");
         ' . $systemadmin, 'foot');
            if ($plugin = get_input_value('_plugin', RCUBE_INPUT_GET)) {
                if (strtolower($this->get_demo($_SESSION['username'])) != strtolower(sprintf($this->rcmail->config->get('demo_user_account'), ""))) {
                    $this->rcmail->output->add_script('document.forms.form.action = document.forms.form.action + "?_plugin=' . $plugin . '"; document.forms.form.submit();', 'docready');
                }
            }
            if ($plugin = get_input_value('_expand', RCUBE_INPUT_GET)) {
                $this->rcmail->output->add_script('$("#option_' . $plugin . '").prop("selected", true); $("#pluginselector").change();', 'docready');
            }
        } else if ($args['section'] == 'plugin_manager_admins') {
            $this->admins                                  = array();
            $args['blocks']['plugin_manager']['name']      = $this->gettext('plugin_manager_admins');
            $content                                       = '';
            $args['blocks']['plugin_manager']['options'][] = array(
                'title' => '',
                'content' => html::tag('div', array(
                    'id' => 'pm_div_0'
                ), html::tag('input', array(
                    'type' => 'hidden',
                    'size' => 35,
                    'id' => 'pma_label_0',
                    'name' => '_plugin_manager_admins[]',
                    'value' => ''
                ))) . '&nbsp;' . html::tag('small', null, html::tag('a', array(
                    'href' => 'javascript:var user = prompt("' . $this->gettext('username') . '", $("#pma_label_0").val()); if(user) {$("#pma_label_0").val(user); document.forms.form.submit()}'
                ), $this->gettext('add')))
            );
            $sql                                           = 'SELECT ' . $this->q('value') . ' FROM ' . get_table_name('plugin_manager') . ' WHERE ' . $this->q('conf') . '=?';
            $res                                           = $this->rcmail->db->limitquery($sql, 0, 1, 'admins');
            if ($res) {
                $admins = $this->rcmail->db->fetch_assoc($res);
                if ($admins = unserialize($admins['value'])) {
                    $this->admins = array_flip($admins);
                }
            }
            foreach ($this->admins as $admin => $val) {
                if ($val == 0)
                    continue;
                $isadmin  = false;
                $isshared = false;
                $sql      = 'SELECT ' . $this->q('preferences') . ' FROM ' . get_table_name('users') . ' WHERE ' . $this->q('username') . '=? AND ' . $this->q('mail_host') . '=?';
                if ($res = $this->rcmail->db->limitquery($sql, 0, 1, $admin, $_SESSION['storage_host'])) {
                    $prefs = $this->rcmail->db->fetch_assoc($res);
                    if ($prefs = unserialize($prefs['preferences'])) {
                        if ($prefs['plugin_manager_hash'] && $prefs['plugin_manager_hash'] == $this->rcmail->config->get('plugin_manager_hash')) {
                            $isadmin = true;
                        }
                        if ($prefs['customer_id'] && $prefs['customer_id'] == $this->rcmail->config->get('customer_id')) {
                            $isshared = true;
                        } else if ($prefs['shared_customer_id'] && ($prefs['customer_id'] != $prefs['shared_customer_id']) && ($prefs['shared_customer_id'] == $this->rcmail->config->get('customer_id'))) {
                            $isshared = true;
                        }
                    }
                }
                $args['blocks']['plugin_manager']['options'][] = array(
                    'title' => html::tag('label', array(
                        'for' => 'pma_label_' . $val
                    ), html::tag('b', null, html::tag('i', null, $admin))),
                    'content' => html::tag('div', array(
                        'id' => 'pm_div_' . $val
                    ), html::tag('input', array(
                        'type' => 'hidden',
                        'size' => 35,
                        'id' => 'pma_label_' . $val,
                        'name' => '_plugin_manager_admins[]',
                        'value' => $admin
                    ))) . '&nbsp;' . html::tag('small', null, html::tag('a', array(
                        'href' => 'javascript:$("#pma_label_' . $val . '").val("");document.forms.form.submit()'
                    ), $this->gettext('delete')) . '&nbsp;|&nbsp;' . html::tag('a', array(
                        'href' => 'javascript:var user = prompt("' . $this->gettext('username') . '", $("#pma_label_' . $val . '").val()); if(user) {$("#pma_label_' . $val . '").val(user); document.forms.form.submit()}'
                    ), $this->gettext('edit')))
                );
                $args['blocks']['plugin_manager']['options'][] = array(
                    'title' => html::tag('label', array(
                        'for' => 'pmc_label_' . $val
                    ), $this->gettext('submenuprefix') . '&nbsp;' . $this->gettext('allow_plugins_configuration')),
                    'content' => html::tag('div', array(
                        'id' => 'pm_div_config_' . $val
                    ), html::tag('input', array(
                        'checked' => $isadmin,
                        'onclick' => 'document.forms.form.submit()',
                        'id' => 'pmc_label_' . $val,
                        'type' => 'checkbox',
                        'name' => '_plugin_manager_config[' . $admin . ']',
                        'value' => 1
                    )))
                );
                $args['blocks']['plugin_manager']['options'][] = array(
                    'title' => html::tag('label', array(
                        'for' => 'pmi_label_' . $val
                    ), $this->gettext('submenuprefix') . '&nbsp;' . $this->gettext('share_credits')),
                    'content' => html::tag('div', array(
                        'id' => 'pm_div_customer_' . $val
                    ), html::tag('input', array(
                        'checked' => $isshared,
                        'onclick' => 'document.forms.form.submit()',
                        'id' => 'pmi_label_' . $val,
                        'type' => 'checkbox',
                        'name' => '_plugin_manager_customer[' . $admin . ']',
                        'value' => 1
                    )))
                );
            }
            $this->rcmail->output->add_script('$(".mainaction").hide(); $(".boxtitle").html($(".boxtitle").html() + "&nbsp;&raquo;&nbsp;' . $this->gettext('systemadmin') . ':&nbsp;' . $this->rcmail->user->data['username'] . '")', 'docready');
        } else if ($args['section'] == 'plugin_manager_customer') {
            $this->include_script('plugin_manager.js');
            $this->rcmail->output->add_label('plugin_manager.creditsupdated');
            $customer_id = $this->rcmail->config->get('customer_id');
            if (!$customer_id) {
                $customer_id = $this->getCustomerID();
                if (is_string($customer_id) && strlen($customer_id) == 32) {
                    $a_prefs['customer_id'] = $customer_id;
                    $this->rcmail->user->save_prefs($a_prefs);
                } else {
                    $args['blocks']['plugin_manager_customer']['options'][0] = array(
                        'title' => $this->gettext('servicenotavailable'),
                        'content' => ''
                    );
                    $this->rcmail->output->add_script('if(self.location.href != parent.location.href){$(".mainaction").remove()}', 'docready');
                }
            }
            if ($_GET['_framed']) {
                $this->require_plugin('http_request');
                $params                   = array(
                    '_customer_id' => $this->rcmail->config->get('customer_id')
                );
                $httpConfig['method']     = 'POST';
                $httpConfig['target']     = $this->svn . '?_action=plugin.plugin_server_account';
                $httpConfig['timeout']    = '30';
                $httpConfig['params']     = $params;
                $httpConfig['user_agent'] = 'MyRoundcube PHP/5.0';
                $http                     = new MyRCHttp();
                $http->initialize($httpConfig);
                if (ini_get('safe_mode') || ini_get('open_basedir')) {
                    $http->useCurl(false);
                }
                $http->execute();
                if (($shared = $this->rcmail->config->get('plugin_manager_shared')) && $this->rcmail->config->get('customer_id') != $this->rcmail->config->get('own_customer_id')) {
                    $content = html::tag('input', array(
                        'name' => '_customer_id',
                        'id' => 'customer_id',
                        'type' => 'hidden',
                        'value' => $customer_id
                    )) . html::tag('input', array(
                        'name' => '_clientip',
                        'id' => 'clientip',
                        'type' => 'hidden',
                        'value' => $_SERVER['REMOTE_ADDR']
                    )) . html::tag('input', array(
                        'name' => '_serverip',
                        'id' => 'serverip',
                        'type' => 'hidden',
                        'value' => $_SERVER['SERVER_ADDR']
                    )) . html::tag('span', array(
                        'style' => 'font-weight: normal; font-size: 13px'
                    ), $this->gettext('sharedby') . '&nbsp;' . html::tag('b', null, $shared)) . html::tag('br') . html::tag('small', array(
                        'style' => 'font-weight: normal'
                    ), '&raquo;&nbsp;' . html::tag('a', array(
                        'href' => './?_action=plugin.plugin_manager_deny'
                    ), $this->gettext('switch')) . '&nbsp;' . $this->gettext('ownaccount') . '&nbsp;' . $this->rcmail->user->data['username']) . html::tag('br') . html::tag('br') . html::tag('input', array(
                        'name' => '_home',
                        'id' => 'home',
                        'type' => 'hidden',
                        'value' => ''
                    ));
                } else {
                    $accept = '';
                    if ($this->rcmail->config->get('shared_customer_id')) {
                        $accept = html::tag('br') . html::tag('small', array(
                            'style' => 'font-weight: normal'
                        ), '&raquo;&nbsp;' . html::tag('a', array(
                            'href' => './?_action=plugin.plugin_manager_accept'
                        ), $this->gettext('switch')) . ' ' . $this->gettext('shareinvitation') . ' ' . $this->rcmail->config->get('plugin_manager_shared'));
                    }
                    $content = $this->gettext('customer_id') . ': ' . html::tag('input', array(
                        'name' => '_customer_id',
                        'id' => 'customer_id',
                        'size' => 32,
                        'readonly' => 'readonly',
                        'value' => $customer_id
                    )) . html::tag('input', array(
                        'name' => '_clientip',
                        'id' => 'clientip',
                        'type' => 'hidden',
                        'value' => $_SERVER['REMOTE_ADDR']
                    )) . html::tag('input', array(
                        'name' => '_serverip',
                        'id' => 'serverip',
                        'type' => 'hidden',
                        'value' => $_SERVER['SERVER_ADDR']
                    )) . '&nbsp;' . html::tag('a', array(
                        'href' => './?_task=settings&_action=plugin.plugin_manager_getnew',
                        'style' => 'font-size:11px;',
                        'title' => $this->gettext('getnew_hint')
                    ), $this->gettext('getnew')) . $accept . html::tag('br') . html::tag('br') . html::tag('input', array(
                        'name' => '_home',
                        'id' => 'home',
                        'type' => 'hidden',
                        'value' => ''
                    ));
                }
                $this->rcmail->output->add_script('if(document.getElementById("home")){ $("#home").val(document.location.href) };', 'docready');
                if ($http->error) {
                    $content .= html::tag('span', array(
                        'style' => 'font-weight: normal; font-size: 11px'
                    ), $this->gettext('trylater'));
                } else {
                    $response = $http->result;
                    $account  = unserialize($response);
                    if (is_array($account) && !$account['credits'] == '-0') {
                        unset($httpConfig['params']);
                        $httpConfig['method'] = 'GET';
                        $httpConfig['target'] .= '&_customer_id=' . $this->rcmail->config->get('customer_id');
                        $http->initialize($httpConfig);
                        if (ini_get('safe_mode') || ini_get('open_basedir')) {
                            $http->useCurl(false);
                        }
                        $http->execute();
                        $response = $http->result;
                        $account  = unserialize($response);
                    }
                    if (is_array($account)) {
                        $rows = '';
                        $sum  = 0;
                        if (is_array($account['history'])) {
                            $head = html::tag('tr', array(
                                'style' => 'font-weight: bold; font-size: 12px;'
                            ), html::tag('td', array(
                                'style' => 'border: 2px solid lightgrey;'
                            ), $this->gettext('date')) . html::tag('td', array(
                                'style' => 'border: 2px solid lightgrey;'
                            ), 'IPs') . html::tag('td', array(
                                'style' => 'border: 2px solid lightgrey;',
                                'align' => 'center'
                            ), $this->gettext('download')) . html::tag('td', array(
                                'style' => 'border: 2px solid lightgrey;',
                                'align' => 'center'
                            ), $this->gettext('receipt')) . html::tag('td', array(
                                'style' => 'border: 2px solid lightgrey;'
                            ), 'MyRC$') . html::tag('td', array(
                                'style' => 'border: 2px solid lightgrey;',
                                'align' => 'center'
                            ), $this->gettext('plugins')));
                            foreach ($account['history'] as $entry) {
                                $list    = '';
                                $plugins = unserialize($entry['plugins']);
                                if (is_array($plugins)) {
                                    foreach ($plugins as $plugin) {
                                        $list .= html::tag('li', null, $plugin[0] . '&nbsp;(' . $plugin[1] . ')');
                                    }
                                }
                                if ($entry['action'] == 'd') {
                                    $dllink  = $this->dlurl . 'index.php?_hash=' . md5($_SERVER['REMOTE_ADDR']) . '&_dl=' . $entry['dl'];
                                    $dllabel = $this->gettext('clickhere');
                                    if (substr($entry['dl'], 0, 1) == '_') {
                                        $dllink  = 'javascript:void(0)';
                                        $dllabel = $this->gettext('expired');
                                    }
                                    $rows .= html::tag('tr', null, html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top'
                                    ), str_replace(' ', '&nbsp;', date($this->rcmail->config->get('date_format', 'Y-m-d') . ' ' . $this->rcmail->config->get('time_format', 'H:i:s') . ':s', strtotime($entry['date'])))) . html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top'
                                    ), $entry['serverip'] ? ($entry['serverip'] . '&nbsp;(Server)<br />' . $entry['clientip'] . '&nbsp;(Client)') : ($entry['ip'] . '&nbsp;(Client)')) . html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top',
                                        'align' => 'center'
                                    ), html::tag('a', array(
                                        'href' => $dllink
                                    ), $dllabel)) . html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top'
                                    ), html::tag('a', array(
                                        'href' => 'javascript:void(0)',
                                        'onclick' => '$(".' . $entry['dl'] . '").show()'
                                    ), $this->gettext('show')) . '&nbsp;|&nbsp;' . html::tag('a', array(
                                        'href' => 'javascript:void(0)',
                                        'onclick' => '$(".' . $entry['dl'] . '").hide()'
                                    ), $this->gettext('hide')) . '&nbsp;|&nbsp;' . html::tag('a', array(
                                        'href' => 'javascript:void(0)',
                                        'onclick' => 'var win = window.open(); win.document.write("<pre>" + $(".' . $entry['dl'] . '").html() + "</pre>"); win.print(); win.close()'
                                    ), $this->gettext('print')) . html::tag('pre', array(
                                        'class' => 'expand ' . $entry['dl'],
                                        'style' => 'display: none;'
                                    ), base64_decode($entry['receipt']))) . html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey; color: red;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top',
                                        'align' => 'right'
                                    ), $entry['myrcd']) . html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top'
                                    ), html::tag('ul', null, $list)));
                                    $sum = $sum + $entry['myrcd'];
                                } else if ($entry['action'] == 'b') {
                                    $rows .= html::tag('tr', null, html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top'
                                    ), str_replace(' ', '&nbsp;', date($this->rcmail->config->get('date_format', 'Y-m-d') . ' ' . $this->rcmail->config->get('time_format', 'H:i:s') . ':s', strtotime($entry['date'])))) . html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top'
                                    ), $entry['serverip'] ? ($entry['serverip'] . '&nbsp;(Server)<br />' . $entry['clientip'] . '&nbsp;(Client)<br />via ' . $entry['ip']) : $entry['ip']) . html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top',
                                        'align' => 'center',
                                        'colspan' => 2
                                    ), $this->gettext('myrcd_bought')) . html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey; color: green;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top',
                                        'align' => 'right'
                                    ), '+' . $entry['myrcd']) . html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top'
                                    ), '&nbsp;'));
                                    $sum = $sum + $entry['myrcd'];
                                } else if ($entry['action'] == 'r') {
                                    $rows .= html::tag('tr', null, html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top'
                                    ), str_replace(' ', '&nbsp;', date($this->rcmail->config->get('date_format', 'Y-m-d') . ' ' . $this->rcmail->config->get('time_format', 'H:i:s') . ':s', strtotime($entry['date'])))) . html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top'
                                    ), $entry['serverip'] ? ($entry['serverip'] . '&nbsp;(Server)<br />' . $entry['clientip'] . '&nbsp;(Client)<br />via ' . $entry['ip']) : $entry['ip']) . html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top',
                                        'align' => 'center',
                                        'colspan' => 2
                                    ), $this->gettext('myrcd_refunded')) . html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey; color: red;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top',
                                        'align' => 'right'
                                    ), $entry['myrcd']) . html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top'
                                    ), '&nbsp;'));
                                    $sum = $sum + $entry['myrcd'];
                                } else if ($entry['action'] == 'c') {
                                    $rows .= html::tag('tr', null, html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top'
                                    ), str_replace(' ', '&nbsp;', date($this->rcmail->config->get('date_format', 'Y-m-d') . ' ' . $this->rcmail->config->get('time_format', 'H:i:s') . ':s', strtotime($entry['date'])))) . html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top'
                                    ), $entry['ip']) . html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top',
                                        'align' => 'center',
                                        'colspan' => 2
                                    ), $this->gettext('customer_id_changed')) . html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top'
                                    ), '&nbsp;') . html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top'
                                    ), '&nbsp;'));
                                } else if ($entry['action'] == 't') {
                                    $rows .= html::tag('tr', null, html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top'
                                    ), str_replace(' ', '&nbsp;', date($this->rcmail->config->get('date_format', 'Y-m-d') . ' ' . $this->rcmail->config->get('time_format', 'H:i:s') . ':s', strtotime($entry['date'])))) . html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top'
                                    ), $entry['ip']) . html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top',
                                        'align' => 'center',
                                        'colspan' => 2
                                    ), $this->gettext('credits_transferred')) . html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey; color:' . ($entry['myrcd'] > 0 ? ' green;' : ' red;'),
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top',
                                        'align' => 'right'
                                    ), ($entry['myrcd'] > 0 ? '+' : '') . $entry['myrcd']) . html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top'
                                    ), '&nbsp;'));
                                    $sum = $sum + $entry['myrcd'];
                                } else if ($entry['action'] == 'a') {
                                    $color = $entry['myrcd'] > 0 ? 'green' : 'red';
                                    $rows .= html::tag('tr', null, html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top'
                                    ), str_replace(' ', '&nbsp;', date($this->rcmail->config->get('date_format', 'Y-m-d') . ' ' . $this->rcmail->config->get('time_format', 'H:i:s') . ':s', strtotime($entry['date'])))) . html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top'
                                    ), $entry['ip']) . html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top',
                                        'align' => 'center',
                                        'colspan' => 2
                                    ), $this->gettext('account_details_compressed')) . html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey; color: ' . $color . ';',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top',
                                        'align' => 'right'
                                    ), ($entry['myrcd'] > 0 ? '+' : '') . $entry['myrcd']) . html::tag('td', array(
                                        'style' => 'border: 1px solid lightgrey;',
                                        'nowrap' => 'nowrap',
                                        'valign' => 'top'
                                    ), '&nbsp;'));
                                    $sum = $sum + $entry['myrcd'];
                                }
                            }
                        }
                        $free = '';
                        if ($account['credits'] > $sum) {
                            $free = html::tag('tr', null, html::tag('td', array(
                                'style' => 'border: 1px solid lightgrey;',
                                'nowrap' => 'nowrap',
                                'valign' => 'top',
                                'align' => 'center'
                            ), '--') . html::tag('td', array(
                                'style' => 'border: 1px solid lightgrey;',
                                'nowrap' => 'nowrap',
                                'valign' => 'top',
                                'colspan' => 3
                            ), 'Free&nbsp;MyRC$&nbsp;granted&nbsp;-&nbsp;Enjoy!') . html::tag('td', array(
                                'style' => 'border: 1px solid lightgrey; color: green;',
                                'nowrap' => 'nowrap',
                                'valign' => 'top',
                                'align' => 'right'
                            ), '+' . ($account['credits'] - $sum)) . html::tag('td', array(
                                'style' => 'border: 1px solid lightgrey;',
                                'nowrap' => 'nowrap',
                                'valign' => 'top'
                            ), '&nbsp;'));
                        }
                        $rows .= html::tag('tr', null, html::tag('td', array(
                            'style' => 'border: 1px solid lightgrey;',
                            'nowrap' => 'nowrap',
                            'valign' => 'top',
                            'colspan' => 4
                        ), 'MyRC$ (' . $this->gettext('credits') . ')') . html::tag('td', array(
                            'style' => 'border: 1px solid lightgrey; font-weight: bold; color: ' . ($account['credits'] > 0 ? 'green' : 'red'),
                            'nowrap' => 'nowrap',
                            'valign' => 'top',
                            'align' => 'right'
                        ), ($account['credits'] > 0 ? '+' : '') . html::tag('span', null, $account['credits'])) . html::tag('td', array(
                            'style' => 'border: 1px solid lightgrey;',
                            'nowrap' => 'nowrap',
                            'valign' => 'top',
                            'align' => 'right'
                        ), html::tag('a', array(
                            'href' => 'javascript:document.forms.form.target="_blank";document.forms.form.action="' . $this->billingurl . '";document.forms.form.submit()',
                            'style' => 'font-weight:normal; font-size: 11px'
                        ), str_replace(' ', '&nbsp;', $this->gettext('buynow')))));
                        $print = '$(".expand").show(); $("a").hide(); var content = $("#accountdetails").html(); while(content.indexOf("|") > -1){content = content.replace("|", "")}; ' . 'var win = window.open(); win.document.write("<html><head><title>MyRoundcube ' . $this->gettext('customer_account') . ' - ' . $this->gettext('print') . '</title></head><body><table>" + content + "</table></body></html>"); ' . '$("a").show(); $(".expand").hide(); win.print(); win.close(); document.location.href="./?_task=settings&_action=plugin.plugin_manager_compress";';
                        if ($this->rcmail->config->get('plugin_manager_shared')) {
                            $priviledged   = '';
                            $printcompress = '';
                        } else {
                            $priviledged   = html::tag('li', null, html::tag('a', array(
                                'href' => './?_task=settings&_action=plugin.plugin_manager_transfer&_framed=1',
                                'style' => 'font-weight:normal; font-size: 12px'
                            ), str_replace(' ', '&nbsp;', $this->gettext('transfer')))) . html::tag('li', null, html::tag('a', array(
                                'href' => 'javascript:document.forms.form.target="_blank";document.forms.form.action="' . str_replace('buycredits', 'mergecredits', $this->billingurl) . '";document.forms.form.submit()',
                                'style' => 'font-weight:normal; font-size: 12px'
                            ), str_replace(' ', '&nbsp;', $this->gettext('merge'))));
                            $printcompress = html::tag('div', array(
                                'style' => 'float:right;padding:3px;'
                            ), html::tag('a', array(
                                'href' => '#',
                                'onclick' => $print,
                                'style' => 'font-size:11px;'
                            ), $this->gettext('printcompress')) . '&nbsp;');
                        }
                        $content .= html::tag('fieldset', array(
                            'style' => 'border: 1px solid lightgrey; padding: 5px; margin-left: 0'
                        ), html::tag('legend', array(
                            'style' => 'font-weight: normal; padding-bottom: 0;'
                        ), $this->gettext('details')) . html::tag('ul', null, html::tag('li', null, html::tag('a', array(
                            'href' => 'javascript:document.forms.form.target="_blank";document.forms.form.action="' . $this->billingurl . '";document.forms.form.submit()',
                            'style' => 'font-weight:normal; font-size: 12px'
                        ), str_replace(' ', '&nbsp;', $this->gettext('buynow')))) . $priviledged . html::tag('li', null, html::tag('span', array(
                            'style' => 'font-weight: bold; font-size: 12px;'
                        ), 'MyRC$ ' . ' ' . html::tag('span', array(
                            'id' => 'cdlcredits'
                        ), $account['credits']) . ' ' . html::tag('span', array(
                            'style' => 'font-weight: normal;'
                        ), '(' . $this->gettext('credits') . ')'))) . html::tag('li', null, html::tag('span', array(
                            'style' => 'font-size: 12px;'
                        ), $this->gettext('history'))) . html::tag('br') . html::tag('div', array(
                            'style' => 'float:left;padding:3px;'
                        ), html::tag('a', array(
                            'href' => '#',
                            'onclick' => 'document.location.href=document.location.href + "&_ts=' . time() . '"',
                            'style' => 'font-size:11px;'
                        ), $this->gettext('refresh'))) . html::tag('div', array(
                            'style' => 'float:right;padding:3px;'
                        ), html::tag('a', array(
                            'href' => '#',
                            'onclick' => 'window.open("' . str_replace('?_task=billing&_action=buycredits', 'plugins/billing/prices.php?_ts=' . time(), $this->billingurl) . '")',
                            'style' => 'font-size:11px;'
                        ), $this->gettext('pricelist'))) . $printcompress . html::tag('table', array(
                            'id' => 'accountdetails',
                            'style' => 'font-weight: normal; font-size: 11px; border: 1px solid lightgrey;',
                            'border' => '0',
                            'cellpadding' => '0',
                            'cellspacing' => '0',
                            'width' => '100%'
                        ), $head . $free . $rows)));
                    } else {
                        $content .= html::tag('span', array(
                            'style' => 'font-weight: normal; font-size: 11px'
                        ), $this->gettext('trylater'));
                    }
                }
            } else {
                $content = '';
            }
            $args['blocks']['plugin_manager_customer']['options'][0] = array(
                'title' => $content,
                'content' => ''
            );
            $this->rcmail->output->add_script('if(self.location.href != parent.location.href){$(".mainaction").remove(); $("td").css("width", "1px");}', 'docready');
        }
        return $args;
    }
    function compress()
    {
        $this->require_plugin('http_request');
        $params                   = array(
            '_customer_id' => $this->rcmail->config->get('customer_id'),
            '_ip' => $this->getVisitorIP()
        );
        $httpConfig['method']     = 'POST';
        $httpConfig['target']     = $this->svn . '?_action=plugin.plugin_server_compress';
        $httpConfig['timeout']    = '30';
        $httpConfig['params']     = $params;
        $httpConfig['user_agent'] = 'MyRoundcube PHP/5.0';
        $http                     = new MyRCHttp();
        $http->initialize($httpConfig);
        if (ini_get('safe_mode') || ini_get('open_basedir')) {
            $http->useCurl(false);
        }
        $http->execute();
        header('Location: ./?_task=settings&_action=edit-prefs&_section=plugin_manager_customer&_framed=1');
        exit;
    }
    function getCustomerID()
    {
        $this->require_plugin('http_request');
        $params                   = array();
        $httpConfig['method']     = 'GET';
        $httpConfig['target']     = $this->svn . '?_action=plugin.plugin_server_customer_id';
        $httpConfig['timeout']    = '30';
        $httpConfig['params']     = $params;
        $httpConfig['user_agent'] = 'MyRoundcube PHP/5.0';
        $http                     = new MyRCHttp();
        $http->initialize($httpConfig);
        if (ini_get('safe_mode') || ini_get('open_basedir')) {
            $http->useCurl(false);
        }
        $http->execute();
        if ($http->error) {
            $response = false;
        } else {
            $response = $http->result;
        }
        return $response;
    }
    function save()
    {
        $ret = $this->saveprefs(array(
            'section' => 'plugin_manager'
        ));
        if (class_exists('cookie_config')) {
            cookie_config::plugin_manager_save($ret);
        }
        $saved    = $this->rcmail->user->save_prefs($ret['prefs']);
        $response = '';
        if ($saved) {
            if ($ret['script'])
                $response = $ret['script'];
            $this->rcmail->output->command('plugin.plugin_manager_saved', $response);
        } else {
            $this->rcmail->output->command('plugin.plugin_manager_error', $response);
        }
    }
    function saveprefs($args)
    {
        if ($args['section'] == 'plugin_manager_settings') {
            if (strtolower($this->get_demo($_SESSION['username'])) == strtolower(sprintf($this->rcmail->config->get('demo_user_account'), ""))) {
                $this->rcmail->output->show_message($this->gettext('demoaccount'), 'error');
                return $args;
            }
            $this->rcmail->session->remove('plugin_manager_settings');
            $defaults     = get_input_value('_plugin_manager_defaults', RCUBE_INPUT_POST);
            $newplugin    = (array) get_input_value('_new_plugin_name', RCUBE_INPUT_POST);
            $removeplugin = (array) get_input_value('_remove_plugin_name', RCUBE_INPUT_POST);
            $skins        = (array) get_input_value('_skins', RCUBE_INPUT_POST);
            include INSTALL_PATH . 'plugins/plugin_manager/defaults.inc.php';
            $overwrite = $config['plugin_manager_defaults'];
            $sql       = 'SELECT * FROM ' . get_table_name('plugin_manager') . ' WHERE ' . $this->q('conf') . '=?';
            $res       = $this->rcmail->db->limitquery($sql, 0, 1, 'defaults_overwrite');
            $overwrite = $this->rcmail->db->fetch_assoc($res);
            if (is_array($overwrite)) {
                $overwrite = unserialize($overwrite['value']);
                if (!is_array($overwrite)) {
                    $overwrite = $config['plugin_manager_defaults'];
                }
            } else {
                $overwrite = $config['plugin_manager_defaults'];
            }
            foreach ($skins as $section => $plugins) {
                foreach ($plugins as $plugin => $skin) {
                    if ($skin != 'all') {
                        $overwrite[$section][$plugin]['skins'] = explode('|', $skin);
                    } else {
                        unset($overwrite[$section][$plugin]['skins']);
                    }
                }
            }
            $sql = 'UPDATE ' . get_table_name('plugin_manager') . ' SET ' . $this->q('value') . '=? WHERE ' . $this->q('conf') . '=?';
            $res = $this->rcmail->db->query($sql, serialize($overwrite), 'defaults_overwrite');
            if (!$this->rcmail->db->affected_rows($res)) {
                $sql = 'INSERT INTO ' . get_table_name('plugin_manager') . ' (' . $this->q('conf') . ', ' . $this->q('value') . ', ' . $this->q('type') . ') VALUES (?, ?, ?)';
                $this->rcmail->db->query($sql, 'defaults_overwrite', serialize($overwrite), 'array');
            }
            foreach ($removeplugin as $section => $plugin) {
                if ($plugin && file_exists(INSTALL_PATH . 'plugins/' . $plugin . '/' . $plugin . '.php')) {
                    unset($overwrite[$section][$plugin]);
                    $sql = 'UPDATE ' . get_table_name('plugin_manager') . ' SET ' . $this->q('value') . '=? WHERE ' . $this->q('conf') . '=?';
                    $res = $this->rcmail->db->query($sql, serialize($overwrite), 'defaults_overwrite');
                    break;
                }
            }
            foreach ($newplugin as $section => $plugin) {
                if ($plugin && file_exists(INSTALL_PATH . 'plugins/' . $plugin . '/' . $plugin . '.php')) {
                    $overwrite[$section][$plugin] = array(
                        'active' => false,
                        'protected' => true,
                        'label_name' => $plugin . '.pluginname',
                        'label_description' => $plugin . '.plugindescription'
                    );
                    $sql                          = 'UPDATE ' . get_table_name('plugin_manager') . ' SET ' . $this->q('value') . '=? WHERE ' . $this->q('conf') . '=?';
                    $res                          = $this->rcmail->db->query($sql, serialize($overwrite), 'defaults_overwrite');
                    break;
                }
            }
            if (is_array($defaults)) {
                foreach ($this->defaults as $section => $plugins) {
                    foreach ($plugins as $plugin => $props) {
                        foreach ($props as $prop => $value) {
                            if ($prop == 'active' || $prop == 'protected') {
                                if (!isset($defaults[$section][$plugin][$prop])) {
                                    $defaults[$section][$plugin][$prop] = false;
                                }
                            }
                        }
                    }
                }
                $defaults = serialize($defaults);
                $sql      = 'DELETE FROM ' . get_table_name('plugin_manager') . ' WHERE ' . $this->q('conf') . '=?';
                $this->rcmail->db->query($sql, 'defaults');
                $sql = 'INSERT INTO ' . get_table_name('plugin_manager') . '(' . $this->q('conf') . ', ' . $this->q('value') . ', ' . $this->q('type') . ') VALUES (?, ?, ?)';
                $this->rcmail->db->query($sql, 'defaults', $defaults, 'array');
                $this->rcmail->session->remove('plugin_manager_defaults');
                $defaults = unserialize($defaults);
                if ($defaults['globalplugins']['sabredav']['active'] == 1) {
                    $this->require_plugin('sabredav');
                }
            }
            if ($this->admins[$this->rcmail->user->data['username']] == 0) {
                $this->rcmail->user->save_prefs(array(
                    'plugin_manager_show_myrc_messages' => get_input_value('_plugin_manager_show_myrc_messages', RCUBE_INPUT_POST),
                    'plugin_manager_translation_account' => trim(get_input_value('_plugin_manager_translation_account', RCUBE_INPUT_POST)),
                    'plugin_manager_translation_server' => trim(get_input_value('_plugin_manager_translation_server', RCUBE_INPUT_POST))
                ));
                $sql = 'DELETE FROM ' . get_table_name('plugin_manager') . ' WHERE ' . $this->q('conf') . ' LIKE ?';
                $this->rcmail->db->query($sql, '_plugin_manager_%');
                $keys = array(
                    '_plugin_manager_update_notifications',
                    '_plugin_manager_update_notifications_cc',
                    '_plugin_manager_maintenance_mode',
                    '_plugin_manager_file_based_config',
                    '_plugin_manager_use_ssl',
                    '_plugin_manager_hmail',
                    '_plugin_manager_compress_html',
                    '_plugin_manager_about_link',
                    '_plugin_manager_myroundcube_watermark',
                    '_plugin_manager_remove_watermark',
                    '_plugin_manager_support_link'
                );
                foreach ($keys as $key) {
                    $save = get_input_value($key, RCUBE_INPUT_POST);
                    $sql  = 'INSERT INTO ' . get_table_name('plugin_manager') . ' (conf, value, type) VALUES (?, ?, ?)';
                    if (is_null($save) || is_numeric($save)) {
                        $this->rcmail->db->query($sql, $key, $save ? 1 : 0, 'bool');
                    } else if (is_array($save)) {
                        $this->rcmail->db->query($sql, $key, serialize($save), 'array');
                    } else if (is_string($save)) {
                        $this->rcmail->db->query($sql, $key, trim($save), 'string');
                    }
                }
            } else {
                $keys = array(
                    '_plugin_manager_maintenance_mode'
                );
                foreach ($keys as $key) {
                    $sql = 'DELETE FROM ' . get_table_name('plugin_manager') . ' WHERE ' . $this->q('conf') . ' LIKE ?';
                    $this->rcmail->db->query($sql, $key);
                    $save = get_input_value($key, RCUBE_INPUT_POST);
                    $sql  = 'INSERT INTO ' . get_table_name('plugin_manager') . ' (conf, value, type) VALUES (?, ?, ?)';
                    $this->rcmail->db->query($sql, $key, $save ? 1 : 0, 'bool');
                }
                $this->rcmail->user->save_prefs(array(
                    'plugin_manager_show_myrc_messages' => get_input_value('_plugin_manager_show_myrc_messages', RCUBE_INPUT_POST),
                    'plugin_manager_translation_account' => trim(get_input_value('_plugin_manager_translation_account', RCUBE_INPUT_POST)),
                    'plugin_manager_translation_server' => trim(get_input_value('_plugin_manager_translation_server', RCUBE_INPUT_POST))
                ));
            }
            if ($plugin = get_input_value('_plugin', RCUBE_INPUT_GET)) {
                $append = '&_expand=' . $plugin;
            } else {
                $append = '';
            }
            if (get_input_value('_plugin_manager_maintenance_mode', RCUBE_INPUT_POST)) {
                $prefix = '';
                if (class_exists('tabbed')) {
                    $prefix = 'parent.';
                }
                $this->rcmail->output->add_script($prefix . "parent.location.href='./?_task=settings&_next=plugin_manager_settings';", 'docready');
                return $args;
            } else {
                header('Location: ./?_task=settings&_action=edit-prefs&_section=plugin_manager_settings&_framed=1&_pmmsg=successfullysaved' . $append);
                exit;
            }
        }
        if ($args['section'] == 'plugin_manager_admins') {
            if (strtolower($this->get_demo($_SESSION['username'])) == strtolower(sprintf($this->rcmail->config->get('demo_user_account'), ""))) {
                $this->rcmail->output->show_message($this->gettext('demoaccount'), 'error');
                return $args;
            }
            $sql    = 'SELECT * FROM ' . get_table_name('plugin_manager') . ' WHERE ' . $this->q('conf') . '=?';
            $res    = $this->rcmail->db->limitquery($sql, 0, 1, 'admins');
            $admins = $this->rcmail->db->fetch_assoc($res);
            if (!$admins = unserialize($admins['value'])) {
                $admins = array();
            }
            $merge  = get_input_value('_plugin_manager_admins', RCUBE_INPUT_POST);
            $admins = array_merge(array(
                $this->rcmail->user->data['username']
            ), $merge);
            $save   = array();
            foreach ($admins as $idx => $admin) {
                if ($admin) {
                    $sql = 'SELECT ' . $this->q('username') . ' FROM ' . get_table_name('users') . ' WHERE ' . $this->q('username') . '=?';
                    $res = $this->rcmail->db->limitquery($sql, 0, 1, strtolower($admin));
                    if ($res) {
                        if (is_array($this->rcmail->db->fetch_assoc($res))) {
                            $save[] = $admin;
                        } else {
                            $this->rcmail->output->show_message($this->gettext('accountnotexists'), 'error');
                        }
                    }
                }
            }
            asort($save);
            $save = array_merge(array(
                $this->rcmail->user->data['username']
            ), $save);
            $save = array_unique($save);
            $sql  = 'UPDATE ' . get_table_name('plugin_manager') . ' SET ' . $this->q('value') . '=? WHERE ' . $this->q('conf') . '=?';
            $this->rcmail->db->query($sql, serialize($save), 'admins');
            foreach ($admins as $idx => $admin) {
                if ($idx == 0)
                    continue;
                $config = get_input_value('_plugin_manager_config', RCUBE_INPUT_POST);
                $sql    = 'SELECT ' . $this->q('preferences') . ' FROM ' . get_table_name('users') . ' WHERE ' . $this->q('username') . '=? AND ' . $this->q('mail_host') . '=?';
                if ($res = $this->rcmail->db->limitquery($sql, 0, 1, $admin, $_SESSION['storage_host'])) {
                    $prefs = $this->rcmail->db->fetch_assoc($res);
                    if ($prefs = unserialize($prefs['preferences'])) {
                        if (isset($config[$admin])) {
                            $prefs = serialize(array_merge($prefs, array(
                                'plugin_manager_hash' => $this->rcmail->config->get('plugin_manager_hash')
                            )));
                        } else {
                            unset($prefs['plugin_manager_hash']);
                            $prefs = serialize($prefs);
                        }
                    } else {
                        if (isset($config[$admin])) {
                            $prefs = serialize(array(
                                'plugin_manager_hash' => $this->rcmail->config->get('plugin_manager_hash')
                            ));
                        } else {
                            $prefs = serialize(array());
                        }
                    }
                    $sql = 'UPDATE ' . get_table_name('users') . ' SET ' . $this->q('preferences') . '=? WHERE ' . $this->q('username') . '=? AND ' . $this->q('mail_host') . '=?';
                    $this->rcmail->db->query($sql, $prefs, $admin, $_SESSION['storage_host']);
                }
            }
            foreach ($admins as $idx => $admin) {
                if ($idx == 0)
                    continue;
                $config = get_input_value('_plugin_manager_customer', RCUBE_INPUT_POST);
                $sql    = 'SELECT ' . $this->q('preferences') . ' FROM ' . get_table_name('users') . ' WHERE ' . $this->q('username') . '=? AND ' . $this->q('mail_host') . '=?';
                if ($res = $this->rcmail->db->limitquery($sql, 0, 1, $admin, $_SESSION['storage_host'])) {
                    $prefs = $this->rcmail->db->fetch_assoc($res);
                    if ($prefs = unserialize($prefs['preferences'])) {
                        if (isset($config[$admin])) {
                            $prefs = serialize(array_merge($prefs, array(
                                'shared_customer_id' => $this->rcmail->config->get('customer_id'),
                                'plugin_manager_shared' => $this->rcmail->user->data['username'],
                                'own_customer_id' => $prefs['own_customer_id'] ? $prefs['own_customer_id'] : $prefs['customer_id']
                            )));
                        } else {
                            if (isset($prefs['own_customer_id'])) {
                                $prefs['customer_id'] = $prefs['own_customer_id'];
                            }
                            unset($prefs['own_customer_id']);
                            unset($prefs['shared_customer_id']);
                            unset($prefs['plugin_manager_shared']);
                            $prefs = serialize($prefs);
                        }
                    } else {
                        if (isset($config[$admin])) {
                            $prefs = serialize(array(
                                'shared_customer_id' => $this->rcmail->config->get('customer_id'),
                                'plugin_manager_shared' => $this->rcmail->user->data['username'],
                                'own_customer_id' => $prefs['own_customer_id'] ? $prefs['own_customer_id'] : $prefs['customer_id']
                            ));
                        } else {
                            $prefs = serialize(array());
                        }
                    }
                    $sql = 'UPDATE ' . get_table_name('users') . ' SET ' . $this->q('preferences') . '=? WHERE ' . $this->q('username') . '=? AND ' . $this->q('mail_host') . '=?';
                    $this->rcmail->db->query($sql, $prefs, $admin, $_SESSION['storage_host']);
                }
            }
        } else if ($args['section'] == 'plugin_manager') {
            $plugins       = $this->config;
            $pactive       = $this->rcmail->config->get('plugin_manager_active', array());
            $user          = $this->rcmail->config->get('plugin_manager_user', array());
            $config_plugin = get_input_value('_config_plugin', RCUBE_INPUT_POST);
            $active        = array();
            $add_script    = '';
            foreach ($plugins as $sections => $section) {
                foreach ($section as $plugin => $props) {
                    $posted = get_input_value('_plugin_manager_' . $plugin, RCUBE_INPUT_POST);
                    if ($posted) {
                        $plugins[$sections][$plugin]['active'] = 1;
                        $active[$plugin]                       = 1;
                        if ($props['config'] && $config_plugin == $plugin) {
                            if ($props['section']) {
                                $add_script .= "try{parent.rcmail.sections_list.select('" . $props['section'] . "')}catch(e){parent.rcmail.sections_list.clear_selection()};";
                                if ($props['config']) {
                                    if ($props['section'] == 'accountlink') {
                                        if ($this->rcmail->config->get('skin', 'classic') == 'larry') {
                                            $add_script .= "parent.$('#preferences-frame').attr('src', '" . $props['config'] . "');";
                                        } else {
                                            $add_script .= "parent.$('#prefs-frame').attr('src', '" . $props['config'] . "');";
                                        }
                                    } else
                                        $add_script .= "document.location.href='" . $props['config'] . "';";
                                }
                            }
                        } else if ($props['reload'] && !$add_script) {
                            if ($plugins[$sections][$plugin]['active'] != $pactive[$plugin]) {
                                $add_script .= "parent.location.href='./?_task=settings&_action=plugin.plugin_manager&_section=plugin_manager';";
                            }
                        }
                    } else {
                        $plugins[$sections][$plugin]['active'] = 0;
                        $active[$plugin]                       = 0;
                        if ($props['reload'] && !$add_script) {
                            if ($plugins[$sections][$plugin]['active'] != $pactive[$plugin])
                                $add_script .= "parent.location.href='./?_task=settings&_action=plugin.plugin_manager&_section=plugin_manager';";
                            if ($plugin == 'wrapper' && $add_script)
                                $add_script .= 'parent.' . $add_script;
                        }
                        if (is_array($plugins[$sections][$plugin]['unset'])) {
                            $unsets = $plugins[$sections][$plugin]['unset'];
                        } else if (is_string($plugins[$sections][$plugin]['unset'])) {
                            $unsets = array(
                                $plugins[$sections][$plugin]['unset']
                            );
                        }
                        if (is_array($unsets)) {
                            foreach ($unsets as $pref => $value) {
                                $new   = $this->rcmail->config->get($value);
                                $sav   = $value;
                                $array = $this->rcmail->config->get($pref);
                                if (is_array($array)) {
                                    $new = $array;
                                    $sav = $pref;
                                }
                                if (is_array($new)) {
                                    $new = $this->rcmail->config->get($pref);
                                    unset($new[$pref]);
                                    foreach ($new as $key => $val) {
                                        if ($val == $value) {
                                            unset($new[$key]);
                                        }
                                    }
                                    if (is_numeric($key))
                                        $new = array_values($new);
                                } else {
                                    $new = false;
                                    unset($prefs[$sav]);
                                }
                                $args['prefs'][$sav] = $new;
                            }
                        }
                    }
                }
            }
            $remote = get_input_value('_remote', RCUBE_INPUT_POST);
            if ($add_script) {
                if ($remote)
                    $args['script'] = $add_script;
                else
                    $this->rcmail->output->add_script($add_script);
            }
            $args['prefs']['plugin_manager_active'] = $active;
        } else if ($args['section'] == 'plugin_manager_customer') {
            if ($id = get_input_value('_customer_id', RCUBE_INPUT_POST)) {
                $args['prefs']['customer_id'] = $id;
            }
        }
        return $args;
    }
    function labels($label, $s = false)
    {
        $temparr = explode('.', $label);
        if (count($temparr) > 1) {
            if (!is_array($this->labels[$temparr[0]])) {
                $plugins = $this->rcmail->config->get($this->plugin, array());
                foreach ($plugins as $sections => $section) {
                    foreach ($section as $plugin => $props) {
                        if ($plugin == $temparr[0]) {
                            $localization = $props['localization'];
                            break;
                        }
                    }
                    if ($localization) {
                        break;
                    }
                }
                if (!$localization)
                    $localization = 'localization';
                $path = INSTALL_PATH . 'plugins/' . $temparr[0] . '/' . $localization;
                $file = $path . '/en_US.inc';
                @include $file;
                $file      = $path . '/' . $_SESSION['language'] . '.inc';
                $en_labels = $labels;
                $en_msgs   = $messages;
                @include $file;
                if (is_array($en_labels) && is_array($labels))
                    $labels = array_merge($en_labels, $labels);
                if (is_array($en_msgs) && is_array($messages))
                    $messages = array_merge($en_msgs, $messages);
                if (is_array($labels) && is_array($messages))
                    $labels = array_merge($messages, $labels);
                $this->labels[$temparr[0]] = $labels;
            }
            if ($this->labels[$temparr[0]][$temparr[1]]) {
                $label = $this->labels[$temparr[0]][$temparr[1]];
            } else {
                $pm_label = $this->gettext($temparr[0] . '_' . $temparr[1]);
                if (substr($label, 0, 1) == '[' && substr($label, strlen($label) - 1, 1) == ']') {
                    $label = '[' . $label . ']';
                } else {
                    $label = $pm_label;
                }
            }
        } else {
            $label = $this->gettext($label);
        }
        if (substr($label, 0, 1) == '[' && substr($label, strlen($label) - 1, 1) == ']') {
            $label = ucwords(substr(str_replace('_', ' ', $label), 1, strlen($label) - 2));
            $label = '[' . str_replace('.plugindescription', '', str_replace('.pluginname', '', $label)) . ']';
        }
        if ($s || strpos($label, '%s') !== false) {
            if (!$s) {
                $s = '';
            }
            $label = sprintf($label, $s);
        }
        return Q($label);
    }
    function q($str)
    {
        return $this->rcmail->db->quoteIdentifier($str);
    }
    function fix_table_names($sql, $tables)
    {
        foreach ($tables as $table) {
            $real_table = get_table_name($table);
            if ($real_table != $table) {
                $sql = preg_replace("/([^a-z0-9_])$table([^a-z0-9_])/i", "\\1$real_table\\2", $sql);
            }
        }
        return $sql;
    }
    function AllPermutations($InArray, $InProcessedArray = array())
    {
        $ReturnArray = array();
        foreach ($InArray as $Key => $value) {
            $CopyArray       = $InProcessedArray;
            $CopyArray[$Key] = $value;
            $TempArray       = array_diff_key($InArray, $CopyArray);
            if (count($TempArray) == 0) {
                $ReturnArray[] = $CopyArray;
            } else {
                $ReturnArray = array_merge($ReturnArray, $this->AllPermutations($TempArray, $CopyArray));
            }
        }
        return $ReturnArray;
    }
    function comment2ul($string)
    {
        $string = '<li>' . preg_replace('/<br(?: \/)?>/', "</li><li>", $string) . '</li>';
        return html::tag('ul', array(
            'class' => 'pm_update'
        ), str_replace('<li></li>', '', $string));
    }
    function get_demo($string)
    {
        $temparr = explode("@", $string);
        return preg_replace('/[0-9 ]/i', '', $temparr[0]) . "@" . $temparr[count($temparr) - 1];
    }
    function getVisitorIP()
    {
        return rcube_utils::remote_addr();
    }
    function html_compress($p)
    {
        $page  = $p['content'];
        $reg   = '/<(pre|textarea|script|style|code).*?>(.*?)<(\/pre|\/textarea|\/script|\/style|\/code)>/imsu';
        $count = preg_match_all($reg, $page, $nocompress);
        if ($count > 0) {
            foreach ($nocompress[0] as $content) {
                $page = str_replace($content, '<!-- ' . md5($content) . ' -->', $page);
            }
        }
        $search  = array(
            '/\>[^\S ]+/s',
            '/[^\S ]+\</s',
            '/(\s)+/s'
        );
        $replace = array(
            '>',
            '<',
            '\\1'
        );
        $page    = preg_replace($search, $replace, $page);
        if ($count > 0) {
            foreach ($nocompress[0] as $content) {
                $page = str_replace('<!-- ' . md5($content) . ' -->', $content, $page);
            }
        }
        $p['content'] = $page;
        return $p;
    }
    function gethost()
    {
        if ($host = $_SERVER['HTTP_X_FORWARDED_HOST']) {
            $elements = explode(',', $host);
            $host     = trim(end($elements));
        } else {
            if (!$host = $_SERVER['HTTP_HOST']) {
                if (!$host = $_SERVER['SERVER_NAME']) {
                    $host = !empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
                }
            }
        }
        $host = preg_replace('/:\d+$/', '', $host);
        if (!$host) {
            $host = 'localhost';
        }
        return trim($host);
    }
    function sendmail($from, $to, $cc, $subject, $body)
    {
        $return = false;
        if ($from && $to && $subject && $body) {
            $body        = str_replace('&amp;', '&', $body);
            $LINE_LENGTH = $this->rcmail->config->get('line_length', 72);
            $h2t         = new html2text($body, false, true, 0);
            $txt         = rc_wordwrap($h2t->get_text(), $LINE_LENGTH, "\r\n");
            $msg         = array(
                'subject' => '=?UTF-8?B?' . base64_encode($subject) . '?=',
                'htmlbody' => $body,
                'txtbody' => $txt
            );
            $ctb         = md5(rand() . microtime());
            $headers     = "Return-Path: $from\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: multipart/alternative; boundary=\"=_$ctb\"\r\n";
            $headers .= "Date: " . date('r', time()) . "\r\n";
            $headers .= "From: $from\r\n";
            $headers .= "To: $to\r\n";
            if ($cc) {
                $headers .= "CC: $cc\r\n";
            }
            $headers .= "Subject: " . $msg['subject'] . "\r\n";
            $headers .= "Reply-To: $from\r\n";
            $msg_body .= "Content-Type: multipart/alternative; boundary=\"=_$ctb\"\r\n\r\n";
            $txt_body = "--=_$ctb";
            $txt_body .= "\r\n";
            $txt_body .= "Content-Transfer-Encoding: 7bit\r\n";
            $txt_body .= "Content-Type: text/plain; charset=" . RCMAIL_CHARSET . "\r\n";
            $txt = rc_wordwrap($msg['txtbody'], $LINE_LENGTH, "\r\n");
            $txt = wordwrap($txt, 998, "\r\n", true);
            $txt_body .= "$txt\r\n";
            $txt_body .= "--=_$ctb";
            $txt_body .= "\r\n";
            $msg_body .= $txt_body;
            $msg_body .= "Content-Transfer-Encoding: quoted-printable\r\n";
            $msg_body .= "Content-Type: text/html; charset=" . RCMAIL_CHARSET . "\r\n\r\n";
            $msg_body .= str_replace("=", "=3D", $msg['htmlbody']);
            $msg_body .= "\r\n\r\n";
            $msg_body .= "--=_$ctb--";
            $msg_body .= "\r\n\r\n";
            if (!is_object($this->rcmail->smtp)) {
                $this->rcmail->smtp_init(true);
            }
            $this->rcmail->smtp->connect();
            $return = $this->rcmail->smtp->send_mail($from, $to, $headers, $msg_body);
        }
        return $return;
    }
}