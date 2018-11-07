<?php
$config['plugin_manager_third_party_plugins'] = array(
  'keyboard_shortcuts' => 'https://github.com/corbosman/keyboard_shortcuts',
  'listcommands' => 'https://github.com/corbosman/listcommands',
  'contextmenu' => 'https://github.com/JohnDoh/Roundcube-Plugin-Context-Menu',
  'copymessage' => 'https://github.com/JohnDoh/Roundcube-Plugin-Copy-Message/',
  'importmessages' => 'http://www.tehinterweb.co.uk/roundcube/plugins/old/importmessages.zip',
  'markasjunk2' => 'https://github.com/JohnDoh/Roundcube-Plugin-Mark-as-Junk-2/',
  'jqueryui' => 'https://github.com/roundcube/roundcubemail/tree/release-1.0/plugins/jqueryui',
  'database_attachments' => 'https://github.com/roundcube/roundcubemail/tree/release-1.0/plugins/database_attachments',
  'globaladdressbook' => 'https://github.com/JohnDoh/Roundcube-Plugin-Global-Address-Book/',
  'markbuttons' => 'https://github.com/xrxca/markbuttons/downloads',
  'database_attachments' => 'https://github.com/roundcube/roundcubemail/tree/release-0.9/plugins/database_attachments',
  'newmail_notifier' => 'https://github.com/roundcube/roundcubemail/tree/release-0.9/plugins/newmail_notifier',
  'new_user_dialog' => 'https://github.com/roundcube/roundcubemail/tree/release-0.9/plugins/new_user_dialog',
  'vcard_attachments' => 'https://github.com/roundcube/roundcubemail/tree/release-0.9/plugins/vcard_attachments',
  'jqueryui' => 'https://github.com/roundcube/roundcubemail/tree/release-0.9/plugins/jqueryui',
  'zipdownload' => 'https://github.com/roundcube/roundcubemail/tree/release-0.9/plugins/zipdownload',
  'hide_blockquote' => 'https://github.com/roundcube/roundcubemail/tree/release-0.9/plugins/hide_blockquote',
  'rcguard' => 'https://github.com/dennylin93/rcguard',
);

$config['plugin_manager_unauth'] = array(
  'vkeyboard' => true,
  'pwtools' => true,
  'nabble' => true,
  'webmail_notifier' => true,
  'checked_identities' => true,
  'detach_attachments' => true,
  'calendar' => true,
  'summary' => true,
  'jappix4roundcube' => true,
);

/* Full featured example */
//    'myplugin' => array( /* the plugin name */
//      'label_name' => 'markbuttons.pluginname', /* label for the plugin */
//      'label_description' => 'markbuttons.plugindescription', // label for the plugin description
//      'label_inject' => false, // see idle_timeout for a valid expample; possible sources: eval, string, config or session
//      'unset' => array(), /* an array of configs which have to be wiped out of preferences,
//                              if plugin is set to inactive by the user */
//      'localization' => 'localization', /* localization folder relative to plugin root folder */
//      'buttons' => false, /* false or an array with valid jquery selector -> inactive: $('validselector').show */
//      'domains' => false, /* array with email domains, true or false */
//      'hosts' => false, /* an array with hosts, true or false */
//      'protected' => true, /* an array of domains where users are not allowed to overwrite or
//                                true | false or
//                                an associated config key */
//      'config' => false, /* See archivefolder plugin for a valid example */
//      'section' => false, /* See archivefolder plugin for a valid example */
//      'reload' => false, /* Reload after saving */
//      'browser' => false, /* See webmail_notifier config (below) for a valid example */
//      'eval' => false, /* see summary config (below) for a valid example */
//      'uninstall' => false, /* give the user the choice to remove prefs from server permanently
//                                false or missing: keep prefs
//                                true: autodetect prefs if supported by plugin or
//                                unindexed array with pref keys */
//      'uninstall_request' => false, /* hmail_autoresponder for a valid example */
//      'uninstall_force' => false, /* force the uninstall request */
//      'skins' => false, /* false or an array with skins where the plugin should be active array('classic', 'litecube-f') */ 
//      'active' => false /* default */
//    ),
/* End full featured example */

$config['plugin_manager_defaults'] = array(
  'globalplugins' => array(
    'db_config' => array( 
      'protected' => true,
      'active' => false
    ),
    'google_oauth2' => array(
      'protected' => true,
      'active' => false
    ),
    'custom_login_logout' => array(
      'protected' => true,
      'active' => false
    ),
    'jsdialogs' => array( 
      'protected' => true,
      'active' => false
    ),
    'jscolor' => array( 
      'protected' => true,
      'active' => false
    ),
    'helpui' => array( 
      'protected' => true,
      'active' => false
    ),
    'summary' => array(
      'label_name' => 'summary.pluginname',
      'label_description' => 'summary.plugindescription',
      'unset' => 'nosummary',
      //'eval' => array('$this->register_action("plugin.summary", array($this, "plugin_manager_dummy"));'),
      //'uninstall' => true,
      'active' => false,
      'protected' => true
    ),
    'checkbox' => array( 
      'label_name' => 'checkbox.pluginname',
      'label_description' => 'checkbox.plugindescription',
      'active' => false,
      'protected' => true
    ),
    'disclaimer' => array( 
      'protected' => true,
      'active' => false
    ),
    'google_analytics' => array( 
      'label_name' => 'google_analytics.pluginname',
      'label_description' => 'google_analytics.plugindescription',
      'active' => false,
      'protected' => true
    ),
    'domain_check' => array( 
      'protected' => true,
      'active' => false
    ),
    'placeholder' => array( 
      'protected' => true,
      'active' => false
    ),
    'sabredav' => array( 
      'protected' => true,
      'active' => false
    ),
    'demologin' => array( 
      'protected' => true,
      'active' => false
    ),
    'terms' => array( 
      'protected' => true,
      'active' => false
    ),
    'register' => array( 
      'protected' => true,
      'active' => false
    ),
    'lang_sel' => array( 
      'protected' => true,
      'active' => false
    ),
    'limit_skins' => array( 
      'protected' => true,
      'active' => false
    ),
    'rcguard' => array( 
      'protected' => true,
      'active' => false
    ),
    'captcha' => array( 
      'protected' => true,
      'active' => false
    ),
    'newuser' => array( 
      'protected' => true,
      'active' => false
    ),
    'new_user_dialog' => array( 
      'protected' => true,
      'active' => false
    ),
    'dnsbl' => array( 
      'protected' => true,
      'active' => false
    ),
    'persistent_login' => array( 
      'protected' => true,
      'active' => false
    ),
    'taskbar' => array( 
      'protected' => true,
      'active' => false
    ),
    'impressum' => array( 
      'protected' => true,
      'active' => false
    ),
    'contactus' => array( 
      'protected' => true,
      'active' => false
    ),
    'crawler' => array( 
      'protected' => true,
      'active' => false
    ),
    'checked_identities' => array( 
      'protected' => true,
      'active' => false
    ),
    'identities_smtp' => array( 
      'protected' => true,
      'active' => false
    ),
    'impersonate' => array( 
      'defer' => true,
      'protected' => true,
      'active' => false
    ),
    'hmail_login' => array(
      'protected' => true,
      'active' => false
    ),
    'hmail_autoban' => array( 
      'protected' => true,
      'active' => false
    ),
    'hmail_publicfolder' => array( 
      'protected' => true,
      'active' => false
    ),
    'hmail_search' => array(
      'protected' => true,
      'active' => false
    ),
    'hmail_sabredav_sync' => array(
      'protected' => true,
      'active' => false,
    ),
    'hmail_roundcube_sync' => array(
      'protected' => true,
      'active' => false,
    ),
    'blockspamsending' => array( 
      'protected' => true,
      'active' => false
    ),
    'dblog' => array( 
      'protected' => true,
      'active' => false
    ),
    'mysqladmin' => array( 
      'protected' => true,
      'active' => false
    ),
  ),
  'performance' => array(
    'load_splitter' => array( 
      'protected' => true,
      'active' => false
    ),
    'tabbed' => array( 
      'protected' => true,
      'active' => false
    ),
  ),
  'uisettings' => array(
    'contextmenu' => array( 
      'label_name' => 'contextmenu.pluginname',
      'label_description' => 'contextmenu.plugindescription',
      'defer' => true,
      'active' => false,
      'protected' => true
    ),
    'markbuttons' => array(
      'label_name' => 'markbuttons.pluginname',
      'label_description' => 'markbuttons.plugindescription',
      'active' => false,
      'protected' => true
    ),
    'google_ads' => array( 
      'label_name' => 'google_ads.pluginname',
      'label_description' => 'google_ads.plugindescription',
      'reload' => true,
      'active' => false,
      'protected' => true
    ),
    'vkeyboard' => array( 
      'label_name' => 'vkeyboard.pluginname',
      'label_description' => 'vkeyboard.plugindescription',
      'active' => false,
      'protected' => true
    ),
    'keyboard_shortcuts' => array( 
      'label_name' => 'keyboard_shortcuts.keyboard_shortcuts',
      'label_description' => 'keyboard_shortcuts.plugindescription',
      'active' => false,
      'protected' => true
    ),
    'folderbuttons' => array( 
      'label_name' => 'folderbuttons.pluginname',
      'label_description' => 'folderbuttons.plugindescription',
      'active' => false,
      'protected' => true
    ),
  ),
  'messagescomposition' => array(
    'save_in_same_folder' => array( 
      'label_name' => 'save_in_same_folder.pluginname',
      'label_description' => 'save_in_same_folder.plugindescription',
      'active' => false,
      'config_label' => 'replysamefolder',
      'protected' => 'reply_same_folder'
    ),
    'compose_myroundcube' => array(
      'label_name' => 'compose_myroundcube.pluginname',
      'label_description' => 'compose_myroundcube.plugindescription',
      'active' => false,
      'config_label' => 'composeextwin',
      'protected' => 'compose_extwin'
    ),
    'compose_in_taskbar' => array( 
      'label_name' => 'compose_in_taskbar.pluginname',
      'label_description' => 'compose_in_taskbar.plugindescription',
      'reload' => true,
      'active' => false,
      'config_label' => 'composeextwin',
      'protected' => 'compose_extwin'
    ),
   'detach_attachments' => array( 
      'label_name' => 'detach_attachments.pluginname',
      'label_description' => 'detach_attachments.plugindescription',
      'active' => false,
      'protected' => true
    ),
   'listcommands' => array( 
      'label_name' => 'listcommands.pluginname',
      'label_description' => 'listcommands.plugindescription',
      'active' => false,
      'protected' => true
    ),
   'scheduled_sending' => array( 
      'label_name' => 'scheduled_sending.pluginname',
      'label_description' => 'scheduled_sending.plugindescription',
      'uninstall_request' => array( //Note: this will give the user the choice to remove all scheduled messages from sending queue.
        'action' => 'plugin.scheduled_sending_uninstall',
        'method' => 'post'
      ),
      'active' => false,
      'protected' => true
    ),
   'vcard_attach' => array( 
      'label_name' => 'vcard_attach.pluginname',
      'label_description' => 'vcard_attach.plugindescription',
      'unset' => 'attach_vcard',
      'active' => false,
      'protected' => true
    ),
   'vcard_send' => array( 
      'label_name' => 'vcard_send.pluginname',
      'label_description' => 'vcard_send.plugindescription',
      'active' => false,
      'protected' => true
    ),
    'embed_images' => array( 
      'label_name' => 'embed_images.pluginname',
      'label_description' => 'embed_images.plugindescription',
      'protected' => true,
      'active' => false,
      'protected' => true
    ),
  ),
  'messagesdisplaying' => array(
    'imap_threads' => array(
      'label_name' => 'imap_threads.pluginname',
      'label_description' => 'imap_threads.plugindescription',
      'active' => false,
      'protected' => true
    ),
    'message_highlight' => array( 
      'label_name' => 'message_highlight.mh_title',
      'label_description' => 'message_highlight.plugindescription',
      'config' => './?_task=settings&_action=edit-prefs&_section=mh_preferences&_framed=1',
      'section' => 'mh_preferences',
      'uninstall' => array('message_highlight'),
      'reload' => true,
      'active' => false,
      'protected' => true
    ),
    'hide_blockquote' => array( 
      'label_name' => 'hide_blockquote.pluginname',
      'label_description' => 'hide_blockquote.plugindescription',
      'config' => './?_task=settings&_action=edit-prefs&_section=mailview&_framed=1',
      'section' => 'mailview',
      'uninstall' => array('hide_blockquote_limit'),
      'active' => false,
      'protected' => true
    ),
    'vcard_attachments' => array( 
      'label_name' => 'vcard_attachments.pluginname',
      'label_description' => 'vcard_attachments.plugindescription',
      'active' => false,
      'protected' => true
    ),
  ),
  'messagesmanagement' => array(
    'remove_attachments' => array( 
      'label_name' => 'remove_attachments.pluginname',
      'label_description' => 'remove_attachments.plugindescription',
      'active' => false,
      'protected' => true
    ),
    'jappix4roundcube' => array(
      'label_name' => 'jappix4roundcube.pluginname',
      'label_description' => 'jappix4roundcube.plugindescription',
      'reload' => true,
      'active' => false,
      'protected' => true
    ),
    'hmail_autoresponder' => array( 
      'label_name' => 'hmail_autoresponder.pluginname',
      'label_description' => 'hmail_autoresponder.plugindescription',
      'config' => './?_task=settings&_action=plugin.hmail_autoresponder&_framed=1',
      'section' => 'accountlink',
      'uninstall_force' => true,
      'uninstall_request' => array(
        'action' => 'plugin.hmail_autoresponder-uninstall',
        'method' => 'post',
      ),
      'active' => false,
      'protected' => true
    ),
    'hmail_forwarding' => array( 
      'label_name' => 'hmail_forwarding.pluginname',
      'label_description' => 'hmail_forwarding.plugindescription',
      'config' => './?_task=settings&_action=plugin.hmail_forwarding&_framed=1',
      'section' => 'accountlink',
      'uninstall_force' => true,
      'uninstall_request' => array(
        'action' => 'plugin.hmail_forwarding-uninstall',
        'method' => 'post',
      ),
      'active' => false,
      'protected' => true
    ),
    'hmail_signature' => array( 
      'label_name' => 'hmail_signature.signature',
      'label_description' => 'hmail_signature.plugindescription',
      'config' => './?_task=settings&_action=plugin.hmail_signature&_framed=1',
      'section' => 'accountlink',
      'uninstall_force' => true,
      'uninstall_request' => array(
        'action' => 'plugin.hmail_signature-uninstall',
        'method' => 'post',
      ),
      'active' => false,
      'protected' => true
    ),
    'hmail_rules' => array(
      'label_name' => 'hmail_rules.pluginname',
      'label_description' => 'hmail_rules.plugindescription',
      'config' => './?_task=settings&_action=plugin.hmail_rules&_framed=1',
      'section' => 'accountlink',
      'active' => false,
      'protected' => true
    ),
    'identities_imap' => array( 
      'label_name' => 'identities_imap.pluginname',
      'label_description' => 'identities_imap.plugindescription',
      'uninstall_request' => array(
        'action' => 'plugin.identities_imap_uninstall',
        'method' => 'post',
      ),
      'active' => false,
      'protected' => true
    ),
    'hmail_pop3' => array(
      'label_name' => 'hmail_pop3.pluginname',
      'label_description' => 'hmail_pop3.plugindescription',
      'config' => './?_task=settings&_action=plugin.hmail_pop3&_framed=1',
      'section' => 'accountlink',
      'active' => false,
      'protected' => true
    ),
    'archivefolder' => array( 
      'label_name' => 'archivefolder.pluginname',
      'label_description' => 'archivefolder.plugindescription',
      'config' => './?_task=settings&_action=edit-prefs&_section=folders&_framed=1',
      'section' => 'folders',
      'uninstall' => true,
      'active' => false,
      'protected' => true
    ),
    'markasjunk2' => array( 
      'label_name' => 'markasjunk2.pluginname',
      'label_description' => 'markasjunk2.plugindescription',
      'active' => false,
      'protected' => true
    ),
    'zipdownload' => array( 
      'label_name' => 'zipdownload.pluginname',
      'label_description' => 'zipdownload.plugindescription',
      'active' => false,
      'protected' => true
    ),
    'newmail_notifier' => array( 
      'label_name' => 'newmail_notifier.pluginname',
      'label_description' => 'newmail_notifier.plugindescription',
      'active' => false,
      'protected' => true
    ),
    'webmail_notifier' => array( 
      'label_name' => 'webmail_notifier.pluginname',
      'label_description' => 'webmail_notifier.plugindescription',
      'browser' => '$test = $browser->mz || $browser->chrome;',
      'uninstall' => true,
      'active' => false,
      'protected' => true
    ),
  ),
  'addressbook' => array(
    'globaladdressbook' => array( 
      'label_name' => 'globaladdressbook.globaladdressbook',
      'label_description' => 'globaladdressbook.plugindescription',
      'protected' => true,
      'active' => true
    ),
    'carddav' => array( 
      'label_name' => 'carddav.pluginname',
      'label_description' => 'carddav.plugindescription',
      'reload' => true,
      'uninstall_request' => array(
        'action' => 'plugin.carddav_uninstall',
        'method' => 'post'
      ),
      'active' => false,
      'protected' => true
    ),
    'carddav_plus' => array(
      'active' => false,
      'protected' => true,
      'autoload' => true,
    ),
    'plaxo_contacts' => array( 
      'label_name' => 'plaxo_contacts.plaxocontacts',
      'label_description' => 'plaxo_contacts.plugindescription',
      'unset' => 'use_plaxo_abook',
      'config' => './?_task=settings&_action=edit-prefs&_section=addressbook&_framed=1',
      'section' => 'addressbook',
      'uninstall' => true,
      'uninstall_request' => array(
        'action' => 'plugin.plaxo_contacts_uninstall',
        'method' => 'post',
      ),
      'active' => false,
      'protected' => true
    ),
    'companyaddressbook' => array( 
      'label_name' => 'companyaddressbook.pluginname',
      'label_description' => 'companyaddressbook.plugindescription',
      'protected' => true,
      'active' => false
    ),
  ),
  'settings' => array(
    'moreuserinfo' => array(
      'label_name' => 'moreuserinfo.pluginname',
      'label_description' => 'moreuserinfo.plugindescription',
      'protected' => true,
      'active' => false
    ),
    'cookie_config' => array(
      'label_name' => 'cookie_config.pluginname',
      'label_description' => 'cookie_config.plugindescription',
      'protected' => true,
      'active' => false
    ),
  ),
  'calendaring' => array(
    'planner' => array( 
      'label_name' => 'planner.planner',
      'label_description' => 'planner.plugindescription',
      'buttons' => array('#planner_button'),
      'uninstall' => true,
      'uninstall_request' => array(
        'action' => 'plugin.planner_uninstall',
        'method' => 'post',
      ),
      'reload' => true,
      'active' => false,
      'protected' => true
    ),
    'sticky_notes' => array( 
      'label_name' => 'sticky_notes.pluginname',
      'label_description' => 'sticky_notes.plugindescription',
      'buttons' => array('#sticky_notes_button'),
      'reload' => true,
      'uninstall_request' => array(
        'action' => 'plugin.sticky_notes_unregister',
        'method' => 'post',
      ),
      'active' => false,
      'protected' => true
    ),
    'calendar' => array( 
      'label_name' => 'calendar.pluginname',
      'label_description' => 'calendar.plugindescription',
      'buttons' => array('#calendar_button', '#rcmrowcalendarlink', '#rcmrowcalendarcategories', '#rcmrowcalendarfeeds', '#rcmrowcalendarsharing'),
      'reload' => true,
      'config' => './?_task=settings&_action=edit-prefs&_section=calendarlink&_framed=1',
      'section' => 'calendarlink',
      'uninstall' => true,
      'uninstall_request' => array(
        'action' => 'plugin.calendar_uninstall',
        'method' => 'post',
      ),
      'active' => false,
      'protected' => true
    ),
    'calendar_plus' => array(
      'active' => false,
      'protected' => true,
      'autoload' => true,
    ),
  ),
  //'backend' => array(
  //),
  'misc' => array(
   'tinymce' => array( 
      'label_name' => 'tinymce.pluginname',
      'label_description' => 'tinymce.plugindescription',
      'protected' => true,
      'active' => false,
      'protected' => true
    ),
    'hmail_password' => array( 
      'label_name' => 'hmail_password.changepasswd',
      'label_description' => 'hmail_password.plugindescription',
      'protected' => false,
      'active' => false,
      'protected' => true
    ),
    'hmail_spamfilter' => array(
      'label_name' => 'hmail_spamfilter.spamfilter',
      'label_description' => 'hmail_spamfilter.plugindescription',
      'protected' => false,
      'config' => './?_task=settings&_action=plugin.hmail_spamfilter&_framed=1',
      'section' => 'accountlink',
      'uninstall' => true,
      'uninstall_force' => true,
      'uninstall_request' => array(
        'action' => 'plugin.hmail_spamfilter-uninstall',
        'method' => 'post',
      ),
      'active' => false,
      'protected' => true
    ),
    'pwtools' => array( 
      'label_name' => 'pwtools.passwordrecovery',
      'label_description' => 'pwtools.plugindescription',
      'unset' => array('pwtoolsaddress', 'pwtoolsquestion', 'pwtoolsanswer', 'pwtoolsenabled'),
      'config' => '.?_task=settings&_action=plugin.pwtools&_framed=1',
      'section' => 'accountlink',
      'uninstall' => true,
      'active' => false,
      'protected' => true
    ),
    'idle_timeout' => array( 
      'label_name' => 'idle_timeout.pluginname',
      'label_description' => 'idle_timeout.plugindescription',
      'label_inject' => array('eval', '$s = $this->rcmail->config->get("idle_timeout_warning", 18) + $this->rcmail->config->get("idle_timeout_logout", 2);'),
      'reload' => true,
      'active' => false,
      'protected' => true
    ),
    'nabble' => array( 
      'label_name' => 'nabble.nabbleapps',
      'label_description' => 'nabble.plugindescription',
      'buttons' => array('#rcmrownabblelink'),
      'unset' => 'use_nabble',
      'reload' => true,
      'config' => './?_task=settings&_action=edit-prefs&_section=nabblelink&_framed=1',
      'section' => 'nabblelink',
      'uninstall' => true,
      'active' => false,
      'protected' => true
    ),
    'rss_feeds' => array( 
      'label_name' => 'rss_feeds.rss_plugin_name',
      'label_description' => 'rss_feeds.plugindescription',
      'active' => false,
      'protected' => true
    ),
    'wrapper' => array(
      'label_name' => 'wrapper.pluginname',
      'label_description' => 'wrapper.plugindescription',
      'reload' => true,
      'uninstall' => true,
      'active' => false,
      'protected' => true
    ),
  ),
);
?>