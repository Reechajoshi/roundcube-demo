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

$labels=array();
$labels['pluginname']='Plugin Manager';
$labels['plugindescription']='Plugin Manager gives users the option to enable or disable plugins (at the user account level), while it helps admins to stay current with plugin updates, bug fixes, centralized downloads, documentation and recommendations. Plugin Manager does not modify your file system, nor it will install plugins automatically.';
$labels['plugin_manager_title']='Manage Plugins';
$labels['plugin_manager_center']='Plugin Manager Centre';
$labels['updatepm']='Plugin Manager update available.';
$labels['updatepmrequired']='Plugin Manager update required.';
$labels['downloadnow']='Download now';
$labels['homepage']='Home page';
$labels['calendaring']='Calendaring';
$labels['misc']='Miscellaneous';
$labels['downloads']='downloads';
$labels['issue']='Issue';
$labels['submitissue']='Report an issue.<br /><br />Please check your logs and provide relevant information in the ticket.<br /><br /><b>NOTE</b>: a Google Account is required.';
$labels['showall']='Show All Plugins';
$labels['hideuptodate']='Show Updates Only';
$labels['connectionerror']='The remote server did not respond to a connection attempt.';
$labels['trylater']='Please try later...';
$labels['translate']='Translate this plugin';
$labels['translationaccount']='Realtime translation account (username)';
$labels['translationserver']='Realtime translation account (IMAP server)';
$labels['whatsthis']='What\'s this?';
$labels['restoredefaults']='Restore Defaults';
$labels['checkall']='Check All';
$labels['uncheckall']='Uncheck All';
$labels['toggle']='Toggle Selection';
$labels['status']='Status';
$labels['globalplugins']='Global Plugins';
$labels['performance']='Performance';
$labels['backend']='Server Plugins';
$labels['messagesmanagement']='Message Management';
$labels['furtherconfig']='Do you want to configure this plugin now?';
$labels['uninstall']='You are about to disable this plugin. Do you want to remove all saved settings permanently?';
$labels['uninstallconfirm']='You are about to disable this plugin. WARNING: Select »DISABLE« to disable this plugin while keeping your current data and configuration stored in our server.  Select »REMOVE« if you want all data and configuration managed by this plugin to be removed permanently from our databases. NOTE: this action cannot be undone.';
$labels['areyousure']='You can\'t undo this action. Are you really sure?';
$labels['successfullydeleted']='Settings have been removed successfully.';
$labels['successfullysaved']='Successfully saved.';
$labels['errorsaving']='An error occured while saving.';
$labels['yes']='Yes';
$labels['no']='No';
$labels['disable']='Disable';
$labels['remove']='Remove';
$labels['savewarning']='Do you want to save your changes now?';
$labels['plugin_manager_update']='Update Plugins';
$labels['authors_comments']='Author\'s Comments (not translated)';
$labels['requiredby']='Plugin is required by';
$labels['requires']='Plugin requires';
$labels['recommended']='Recommended Plugins';
$labels['update_plugins']='Plugin Downloads';
$labels['ziparchive']='Download Zip Archive';
$labels['demoaccount']='Sorry, action is disabled (Demo Account)';
$labels['serverversion']='Server Version';
$labels['mirrorversion']='Mirror Version';
$labels['mirrorhost']='Mirror Server';
$labels['comments']='Requirements and Update Notes';
$labels['update_error']='Version mismatch';
$labels['update_ok']='Most recent has been detected. No update is necessary.';
$labels['update_edit']='Plugin must be re-configured or requires database adjustments. Please consider Update Notes.';
$labels['servernewer']='Registered plugin is newer than mirror version.';
$labels['orhigher']='or higher';
$labels['rc_ok']='Roundcube Core is up to date.';
$labels['update_update']='Plugin is outdated, please update! Consider update notes.';
$labels['noupdates']='No updates selected.';
$labels['rc_update']='Roundcube core is outdated, please update!';
$labels['rc_uptodate']='Roundcube core is up to date.';
$labels['rc_newer']='Roundcube core is newer than mirror version!';
$labels['nottested']='Please use plugins with care! We don\'t know if hosted plugins work with Roundcube v%s.';
$labels['justunzip']='Just unzip the archive over your existing installation.';
$labels['guide']='Read more...';
$labels['thirdparty']='This is not a MyRoundcube plugin.';
$labels['thirdpartywarning']='This is a third party plugin. We recommend not to download the plugin from this mirror server and instead download it from the developer\'s site, to ensure you get the latest version as well as notes and advice from its developer.';
$labels['develsite']='Download';
$labels['notinstalled']='not installed';
$labels['notregistered']='not registered';
$labels['roundcubeurl']='Download Roundcube';
$labels['languageupdate']='Localization update is available.';
$labels['localizationfilesonly']='Note: Download contains only localisation files';
$labels['donotregister']='Don\'t register this plugin. It is loaded automatically.';
$labels['register']='Register this plugin in Plugin Manager\'s configuration or in ./config/config.inc.php <small>[$config[\'plugins\'] = array("<i>foo</i>", "<i>bar</i>");]</small>.';
$labels['customer_account']='Customer Account';
$labels['customer_id']='Customer ID';
$labels['invalid_customer_id']='Invalid Customer ID';
$labels['invalid_credits']='Invalid credits';
$labels['successfully_transferred']='Credits successfully transferred';
$labels['noplugindescription']='No plugin description detected.';
$labels['markbuttons_pluginname']='Mark Message Buttons';
$labels['markbuttons_plugindescription']='Add control icons under message list separator to quickly mark messages.';
$labels['keyboard_shortcuts_pluginname']='Keyboard Shortcuts';
$labels['keyboard_shortcuts_plugindescription']='Enter commands using keyboard shortcuts.';
$labels['message_highlight_pluginname']='Highlight Messages';
$labels['message_highlight_plugindescription']='Highlight messages which matches your filter options.';
$labels['google_contacts_pluginname']='Google Contacts';
$labels['google_contacts_plugindescription']='Access your Google Contacts.';
$labels['contextmenu_pluginname']='Context Menu';
$labels['contextmenu_plugindescription']='Enable right click context menu functionality.';
$labels['newmail_notifier_pluginname']='New Mail Notifier';
$labels['newmail_notifier_plugindescription']='Notify on new messages.';
$labels['listcommands_pluginname']='Reply Mailing Lists';
$labels['listcommands_plugindescription']='Various options to reply to messages sent from mailing lists.';
$labels['copymessage_pluginname']='Copy Message(s)';
$labels['copymessage_plugindescription']='Copy message(s) into another IMAP folder.';
$labels['vcard_attachments_pluginname']='vCard Attachments';
$labels['vcard_attachments_plugindescription']='Add a box to messages which have a vcard attachment to import the contact directly into the addre...';
$labels['zipdownload_pluginname']='Export Messages';
$labels['zipdownload_plugindescription']='Export messages as a zip file.';
$labels['markasjunk2_pluginname']='Mark Messages as Spam';
$labels['markasjunk2_plugindescription']='Mark Messages as spam or not spam.';
$labels['markasjunk_pluginname']='Mark Messages as Spam';
$labels['markasjunk_plugindescription']='Mark Messages as spam or not spam.';
$labels['google_analytics_pluginname']='Google Analytics';
$labels['google_analytics_plugindescription']='Include Google Analytics.';
$labels['globaladdressbook_pluginname']='Global Addressbook';
$labels['globaladdressbook_plugindescription']='This is a readonly addressbook provided by your administrator.';
$labels['blockspamsending_pluginname']='Prevent Sending Spam';
$labels['blockspamsending_plugindescription']='Allow sending out only a specific amount of messages with same body during one session.';
$labels['global_config_pluginname']='Configuration Manager';
$labels['global_config_plugindescription']='This is a configuration manager plugin. It optimizes your server by centralizing plugin configura...';
$labels['jqueryui_pluginname']='Jqueryui';
$labels['jqueryui_plugindescription']='Jqueryui javascript library';
$labels['automatic_addressbook_pluginname']='Automatic Addressbook';
$labels['automatic_addressbook_plugindescription']='Creates an addressbook and automatically inserts the collected email addresses there. This eliminates the need to manually add each contact.';