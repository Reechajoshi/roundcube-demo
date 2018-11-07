<?php
# 
# This file is part of Roundcube "settings" plugin.
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
class settings extends rcube_plugin
{
    var $task = 'settings';
    var $noajax = true;
    private $sections = array('general', 'mailbox', 'compose', 'mailview', 'addressbook', 'folders', 'server');
    static private $plugin = 'settings';
    static private $author = 'myroundcube@mail4us.net';
    static private $authors_comments = '<a href="http://myroundcube.com/myroundcube-plugins/helper-plugin?settings" target="_blank">Documentation</a>';
    static private $version = '4.5.1';
    static private $date = '05-10-2014';
    static private $licence = 'All Rights reserved';
    static private $requirements = array('Roundcube' => '1.0', 'PHP' => '5.3');
    static private $prefs = null;
    function init()
    {
        $D = rcmail::get_instance();
        $this->require_plugin('qtip');
        $this->register_handler('plugin.account_sections', array(
            $this,
            'account_sections'
        ));
        /* $this->add_hook('preferences_sections_list', array(
            $this,
            'account_link'
        )); */
        $this->add_hook('preferences_list', array(
            $this,
            'prefs_table'
        ));
        $this->add_hook('render_page', array(
            $this,
            'render_page'
        ));
        $J = $D->config->get('skin');
        $this->include_stylesheet('skins/' . $J . '/settings.css');
        $M = new rcube_browser();
        if ($M->ie) {
            if ($M->ver < 8)
                $this->include_stylesheet('skins/' . $J . '/iehacks.css');
            if ($M->ver < 7)
                $this->include_stylesheet('skins/' . $J . '/ie6hacks.css');
        }
        $this->add_texts('localization/');
        $D->output->add_label('settings.account');
    }
    static function about($Z = false)
    {
        $I = self::$requirements;
        foreach (array(
            'required_',
            'recommended_'
        ) as $L) {
            if (is_array($I[$L . 'plugins'])) {
                foreach ($I[$L . 'plugins'] as $B => $V) {
                    if (class_exists($B) && method_exists($B, 'about')) {
                        $b                     = new $B(false);
                        $I[$L . 'plugins'][$B] = array(
                            'method' => $V,
                            'plugin' => $b->about($Z)
                        );
                    } else {
                        $I[$L . 'plugins'][$B] = array(
                            'method' => $V,
                            'plugin' => $B
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
            'requirements' => $I
        );
    }
    function render_page($O)
    {
        if ($O['template'] == 'settings') {
            $D = rcmail::get_instance();
            $D->output->add_script('$("#rcmrowserver").remove();', 'docready');
            if (get_input_value('_accountsettings', RCUBE_INPUT_GET)) {
                $D->output->add_script('$("#rcmrowaccountlink").trigger("mousedown").trigger("mouseup");', 'docready');
            }
        }
        if ($O['template'] == 'settingsedit' && get_input_value('_section', RCUBE_INPUT_GPC) == 'server') {
            $D = rcmail::get_instance();
            $D->output->add_script('$(".boxtitle").html("<a id=\'accountlink\' href=\'./?_task=settings&_action=edit-prefs&_section=accountlink&_framed=1\'>" + rcmail.gettext("settings.account") + "</a>&nbsp;&raquo;&nbsp;" + $(".boxtitle").html());', 'docready');
        }
        return $O;
    }
    function account_link($A)
    {
        $D = rcmail::get_instance();
        $J = $D->config->get('skin');
        $G = array();
        foreach ($this->sections as $W => $K) {
            $G[$K] = $A['list'][$K];
            unset($A['list'][$K]);
        }
        $A['list']['general']             = $G['general'];
        $A['list']['mailbox']             = $G['mailbox'];
        $A['list']['compose']             = $G['compose'];
        $A['list']['mailview']            = $G['mailview'];
        $A['list']['mh_preferences']      = array();
        $A['list']['keyboard_shortcuts']  = array();
        $A['list']['identitieslink']      = array();
        $A['list']['addressbook']         = $G['addressbook'];
        $A['list']['addressbookcarddavs'] = array();
        $A['list']['addressbooksharing']  = array();
        $A['list']['jabber']              = array();
        $A['list']['folderslink']         = array();
        $A['list']['folders']             = $G['folders'];
        if ($J == 'classic') {
            $A['list']['folders']['section'] = $A['list']['folders']['section'];
        }
        $A['list']['calendarlink']            = array();
        $A['list']['calendarcategories']      = array();
        $A['list']['calendarfeeds']           = array();
        $A['list']['calendarsharing']         = array();
        $A['list']['nabblelink']              = array();
        $A['list']['plugin_manager']          = array();
        $A['list']['plugin_manager_settings'] = array();
        $A['list']['plugin_manager_admins']   = array();
        $A['list']['plugin_manager_customer'] = array();
        $A['list']['plugin_manager_update']   = array();
        $A['list']['accountslink']            = array();
        $A['list']['server']                  = $G['server'];
        $C                                    = (array) $GLOBALS['settingsnav'];
        $N                                    = array();
        foreach ($C as $B => $H) {
            if (class_exists($B)) {
                $N[$this->gettext($B . '.' . $H['label'])][$B] = $H;
            } else {
                unset($GLOBALS['settingsnav'][$B]);
            }
        }
        ksort($N);
        $C                      = $N;
        $GLOBALS['settingsnav'] = array();
        foreach ($C as $d => $H) {
            foreach ($H as $B => $Y) {
                if (class_exists($B)) {
                    $GLOBALS['settingsnav'][$B] = $Y;
                }
            }
        }
        $C = (array) $D->config->get('settingsnav', $GLOBALS['settingsnav']);
        foreach ($C as $B => $H) {
            if (!class_exists($B)) {
                unset($C[$B]);
            }
        }
        if ($T = $_SESSION['plugin_manager_defaults']) {
            $S = $D->config->get('plugin_manager_active', array());
            if (is_array($T)) {
                foreach ($T as $K => $c) {
                    foreach ($c as $B => $H) {
                        if ($H['active']) {
                            $S[$B] = 1;
                        }
                    }
                }
            }
            foreach ($C as $B => $H) {
                if ($S[$B] != 1) {
                    unset($C[$B]);
                }
            }
        }
        if (class_exists('mysqladmin') && strtolower($D->user->data['username']) == $D->config->get('mysql_admin')) {
            $a = array(
                'autoban',
                'autoresponder',
                'forwarding',
                'login',
                'accounts',
                'signature',
                'spamfilter'
            );
            $R = false;
            foreach ($a as $X) {
                $Q = $D->config->get('db_hmail_' . $X . '_dsn');
                if (is_string($Q)) {
                    $P = parse_url($Q);
                    if ($P['user'] && $P['pass']) {
                        $C = array_merge($C, array(
                            'mysqladmin' => array(
                                'part' => '',
                                'label' => 'pluginname',
                                'href' => './?_action=plugin.mysqladmin&pma_login=1&db=db_hmail_' . $X . '_dsn',
                                'onclick' => 'rcmail.set_cookie("PMA_referrer", document.location.href);',
                                'descr' => 'mysqladmin'
                            )
                        ));
                        $R = true;
                        break;
                    }
                }
            }
        }
        if (!$R) {
            $C = array_merge($C, array(
                'mysqladmin' => array(
                    'part' => '',
                    'label' => 'pluginname',
                    'href' => './?_action=plugin.mysqladmin&pma_login=1&db=db_dsnw&dbt=users',
                    'onclick' => 'rcmail.set_cookie("PMA_referrer", document.location.href);',
                    'descr' => 'mysqladmin'
                )
            ));
        }
        $C = array_merge($C, array(
            'settings' => array(
                'part' => '',
                'label' => 'serversettings',
                'href' => './?_task=settings&_action=edit-prefs&_section=server&_framed=1',
                'descr' => 'serversettings'
            )
        ));
        if (count($C) > 0) {
            $_SESSION['settingsnav']             = $C;
            $A['list']['accountlink']['id']      = 'accountlink';
            $A['list']['accountlink']['section'] = $this->gettext('account');
            if (strtolower($D->user->data['username']) != strtolower($_SESSION['username'])) {
                unset($A['list']['accountlink']);
            }
        }
        return $A;
    }
    function account_sections()
    {
        $D = rcmail::get_instance();
        if (isset($_GET['_msg'])) {
            $D->output->command('display_message', urldecode($_GET['_msg']), $_GET['_type']);
        }
        $C = (array) $_SESSION['settingsnav'];
        $E = "<div id=\"userprefs-accountblocks\">\n";
        foreach ($C as $W => $F) {
            if (!class_exists($W)) {
                continue;
            }
            if (!empty($F['descr'])) {
                $U++;
                $E .= "<div class=\"userprefs-accountblock\" id='accountsblock_$U'>\n";
                $E .= "<div class=\"userprefs-accountblock-border\">\n";
                $E .= "&raquo;&nbsp;<a class=\"plugin-description-link\" href=\"" . $F['href'] . "\" onclick='" . $F['onclick'] . "'>" . $this->gettext($F['descr'] . '.' . $F['label']) . "</a>\n";
                $E .= "</div>\n";
                $E .= "</div>\n";
                $E .= '
<script>
var element = $("#accountsblock_' . $U . '");
element.qtip({
  content: {title:\'' . addslashes($this->gettext($F['descr'] . '.' . $F['label'])) . '\', text: \'' . addslashes($this->gettext($F['descr'] . '.description')) . '\'},
  position: {
    my: "top left",
    at: "left bottom",
    target: element,
    viewport: $(window)
  },
  hide: {
    effect: function () { $(this).slideUp(5, function(){ $(this).dequeue(); }); }
  },
  style: {
    classes: "ui-tooltip-light"
  }
});
</script>
';
            }
        }
        $E .= "</div>\n<style>fieldset{border: none;}</style>\n";
        return $E;
    }
    function prefs_table($A)
    {
        if (!get_input_value('_framed', RCUBE_INPUT_GPC) && $A['section'] == 'accountlink') {
            $A['blocks'][$A['section']]['options'] = array(
                'title' => '',
                'content' => html::tag('div', array(
                    'id' => 'pm_dummy'
                ), '')
            );
            return $A;
        }
        if ($A['section'] == 'accountlink') {
            $A['blocks']['main']['options']['accountlink']['title']   = "";
            $A['blocks']['main']['options']['accountlink']['content'] = $this->account_sections("");
            $this->include_script('settings.js');
        }
        return $A;
    }
}