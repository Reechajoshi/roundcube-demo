<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/func.inc                                       |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide functionality for user's settings & preferences             |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

if (!$OUTPUT->ajax_call) {
    $OUTPUT->set_pagetitle($RCMAIL->gettext('preferences'));
}

// register UI objects
// Macgregor Changes
$OUTPUT->add_handlers(array(
    'settingstabs'   => 'rcmail_settings_tabs',
    'prefsframe'     => 'rcmail_preferences_frame',
    'sectionslist'   => 'rcmail_sections_list',
    'identitieslist' => 'rcmail_identities_list',
    'adminlist' => 'rcmail_admin_list',
	'ruleslist' => 'rcmail_rules_list',
	'managelist' => 'rcmail_manage_list',
	'superadminlist' => 'rcmail_superadmin_list'
));

// register action aliases
$RCMAIL->register_action_map(array(
    'folders'       => 'folders.inc',
    'rename-folder' => 'folders.inc',
    'delete-folder' => 'folders.inc',
    'subscribe'     => 'folders.inc',
    'unsubscribe'   => 'folders.inc',
    'purge'         => 'folders.inc',
    'folder-size'   => 'folders.inc',
    'add-identity'  => 'edit_identity.inc',
    'add-response'  => 'edit_response.inc',
    'save-response' => 'edit_response.inc',
    'delete-response' => 'responses.inc',
    'delete-identity' => 'identities.inc',
    'upload-display'  => 'upload.inc',
));


// similar function as /steps/settings/identities.inc::rcmail_identity_frame()
function rcmail_preferences_frame($attrib)
{
    global $OUTPUT;

    if (!$attrib['id']) {
        $attrib['id'] = 'rcmprefsframe';
    }

    return $OUTPUT->frame($attrib, true);
}


function rcmail_sections_list($attrib)
{
    global $RCMAIL;

    // add id to message list table if not specified
    if (!strlen($attrib['id'])) {
        $attrib['id'] = 'rcmsectionslist';
    }

    list($list, $cols) = rcmail_user_prefs();

    // create XHTML table
    $out = $RCMAIL->table_output($attrib, $list, $cols, 'id');

    // set client env
    $RCMAIL->output->add_gui_object('sectionslist', $attrib['id']);
    $RCMAIL->output->include_script('list.js');

    return $out;
}
/**
 * Macgregor Changes
 * Shows the menu for admin
**/
function rcmail_admin_list($attrib)
{
	global $RCMAIL;

  // add id to message list table if not specified
  if (!strlen($attrib['id']))
    $attrib['id'] = 'rcmAdminList';

  list($list, $cols) = rcmail_admin_menu();

  // create XHTML table
  $out = $RCMAIL->table_output($attrib, $list, $cols, 'id');

  // set client env
  $RCMAIL->output->add_gui_object('adminlist', $attrib['id']);
  $RCMAIL->output->include_script('list.js');

  return $out;
}

/** 
 * Macgregor Changes
 * Creates a menu for the rules tab
**/
function rcmail_rules_list($attrib)
{
	global $RCMAIL;

  // add id to message list table if not specified
  if (!strlen($attrib['id']))
    $attrib['id'] = 'rcmRulesList';

  list($list, $cols) = rcmail_rules_menu();

  // create XHTML table
  $out = $RCMAIL->table_output($attrib, $list, $cols, 'id');

  // set client env
  $RCMAIL->output->add_gui_object('ruleslist', $attrib['id']);
  $RCMAIL->output->include_script('list.js');

  return $out;
}

/**
 * Macgregor Changes
 * Creates a menu for the manage users tab
**/
function rcmail_manage_list($attrib)
{
	global $RCMAIL;

  // add id to message list table if not specified
  if (!strlen($attrib['id']))
    $attrib['id'] = 'rcmManageList';

  list($list, $cols) = rcmail_manage_add();

  // create XHTML table
  $out = $RCMAIL->table_output($attrib, $list, $cols, 'id');

  // set client env
  $RCMAIL->output->add_gui_object('managelist', $attrib['id']);
  $RCMAIL->output->include_script('list.js');

  return $out;
}

/**
 * Macgregor Changes
 * Creates a menu for the superadmin tab
**/
function rcmail_superadmin_list($attrib)
{
	global $RCMAIL;
	
	if (!strlen($attrib['id']))
		$attrib['id'] = 'rcmSuperAdminList';
	
	list($list, $cols) = rcmail_superadmin_menu();
	$out = $RCMAIL->table_output($attrib, $list, $cols, 'id');
	
	$RCMAIL->output->add_gui_object('superadminlist', $attrib['id']);
	$RCMAIL->output->include_script('list.js');

	return $out;
}


function rcmail_identities_list($attrib)
{
    global $OUTPUT, $RCMAIL;

    // add id to message list table if not specified
    if (!strlen($attrib['id'])) {
        $attrib['id'] = 'rcmIdentitiesList';
    }

    // get identities list and define 'mail' column
    $list = $RCMAIL->user->list_emails();
    foreach ($list as $idx => $row) {
        $list[$idx]['mail'] = trim($row['name'] . ' <' . rcube_utils::idn_to_utf8($row['email']) . '>');
    }

    // get all identites from DB and define list of cols to be displayed
    $plugin = $RCMAIL->plugins->exec_hook('identities_list', array(
        'list' => $list,
        'cols' => array('mail')
    ));

    // @TODO: use <UL> instead of <TABLE> for identities list
    // create XHTML table
    $out = $RCMAIL->table_output($attrib, $plugin['list'], $plugin['cols'], 'identity_id');

    // set client env
    $OUTPUT->add_gui_object('identitieslist', $attrib['id']);

    return $out;
}


// similar function as in /steps/addressbook/edit.inc
function get_form_tags($attrib, $action, $id = null, $hidden = null)
{
    global $EDIT_FORM, $RCMAIL;

    $form_start = $form_end = '';

    if (empty($EDIT_FORM)) {
        $request_key = $action . (isset($id) ? '.'.$id : '');
        $form_start = $RCMAIL->output->request_form(array(
            'name'    => 'form',
            'method'  => 'post',
            'task'    => $RCMAIL->task,
            'action'  => $action,
            'request' => $request_key,
            'noclose' => true
        ) + $attrib);

        if (is_array($hidden)) {
            $hiddenfields = new html_hiddenfield($hidden);
            $form_start .= $hiddenfields->show();
        }

        $form_end = !strlen($attrib['form']) ? '</form>' : '';

        $EDIT_FORM = !empty($attrib['form']) ? $attrib['form'] : 'form';
        $RCMAIL->output->add_gui_object('editform', $EDIT_FORM);
    }

    return array($form_start, $form_end);
}

/**
 * Macgregor Changes
 * skin dropdown commented
**/
function rcmail_user_prefs($current = null)
{
    global $RCMAIL;

    $sections['general']     = array('id' => 'general', 'section' => $RCMAIL->gettext('uisettings'));
    $sections['mailbox']     = array('id' => 'mailbox', 'section' => $RCMAIL->gettext('mailboxview'));
    $sections['mailview']    = array('id' => 'mailview','section' => $RCMAIL->gettext('messagesdisplaying'));
    $sections['compose']     = array('id' => 'compose', 'section' => $RCMAIL->gettext('messagescomposition'));
    $sections['addressbook'] = array('id' => 'addressbook','section' => $RCMAIL->gettext('addressbook'));
    $sections['folders']     = array('id' => 'folders', 'section' => $RCMAIL->gettext('specialfolders'));
    $sections['server']      = array('id' => 'server',  'section' => $RCMAIL->gettext('serversettings'));

    // hook + define list cols
    $plugin = $RCMAIL->plugins->exec_hook('preferences_sections_list',
        array('list' => $sections, 'cols' => array('section')));

    $sections    = $plugin['list'];
    $config      = $RCMAIL->config->all();
    $no_override = array_flip((array)$RCMAIL->config->get('dont_override'));

    foreach ($sections as $idx => $sect) {
        if ($current && $sect['id'] != $current) {
            continue;
        }

        $blocks = array();

        switch ($sect['id']) {

        // general
        // MG TODO: remove skin and check other options
        case 'general':
            $blocks = array(
                'main'    => array('name' => rcube::Q($RCMAIL->gettext('mainoptions'))),
                'skin'    => array('name' => rcube::Q($RCMAIL->gettext('skin'))),
                'browser' => array('name' => rcube::Q($RCMAIL->gettext('browseroptions'))),
                'advanced'=> array('name' => rcube::Q($RCMAIL->gettext('advancedoptions'))),
            );

            // language selection
            if (!isset($no_override['language'])) {
                if (!$current) {
                    continue 2;
                }

                $a_lang = $RCMAIL->list_languages();
                asort($a_lang);

                $field_id = 'rcmfd_lang';
                $select   = new html_select(array('name' => '_language', 'id' => $field_id));
                $select->add(array_values($a_lang), array_keys($a_lang));

                $blocks['main']['options']['language'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('language'))),
                    'content' => $select->show($RCMAIL->user->language),
                );
            }

            // timezone selection
            if (!isset($no_override['timezone'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_timezone';
                $select   = new html_select(array('name' => '_timezone', 'id' => $field_id));
                $select->add($RCMAIL->gettext('autodetect'), 'auto');

                $zones = array();
                foreach (DateTimeZone::listIdentifiers() as $i => $tzs) {
                    try {
                        $tz      = new DateTimeZone($tzs);
                        $date    = new DateTime(date('Y') . '-12-21', $tz);
                        $offset  = $date->format('Z') + 45000;
                        $sortkey = sprintf('%06d.%s', $offset, $tzs);
                        $zones[$sortkey] = array($tzs, $date->format('P'));
                    }
                    catch (Exception $e) {}
                }

                ksort($zones);

                foreach ($zones as $zone) {
                    list($tzs, $offset) = $zone;
                    $select->add('(GMT ' . $offset . ') ' . strtr($tzs, '_', ' '), $tzs);
                }

                $blocks['main']['options']['timezone'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('timezone'))),
                    'content' => $select->show((string)$config['timezone']),
                );
            }

            // date/time formatting
            if (!isset($no_override['time_format'])) {
                if (!$current) {
                    continue 2;
                }

                $reftime  = mktime(7,30,0);
                $defaults = array('G:i', 'H:i', 'g:i a', 'h:i A');
                $formats  = (array)$RCMAIL->config->get('time_formats', $defaults);
                $field_id = 'rcmfd_time_format';
                $select   = new html_select(array('name' => '_time_format', 'id' => $field_id));

                foreach ($formats as $choice) {
                    $select->add(date($choice, $reftime), $choice);
                }

                $blocks['main']['options']['time_format'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('timeformat'))),
                    'content' => $select->show($RCMAIL->config->get('time_format')),
                );
            }

            if (!isset($no_override['date_format'])) {
                if (!$current) {
                    continue 2;
                }

                $refdate  = mktime(12,30,0,7,24);
                $defaults = array('Y-m-d','d-m-Y','Y/m/d','m/d/Y','d/m/Y','d.m.Y','j.n.Y');
                $formats  = (array)$RCMAIL->config->get('date_formats', $defaults);
                $field_id = 'rcmfd_date_format';
                $select   = new html_select(array('name' => '_date_format', 'id' => $field_id));

                foreach ($formats as $choice) {
                    $select->add(date($choice, $refdate), $choice);
                }

                $blocks['main']['options']['date_format'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('dateformat'))),
                    'content' => $select->show($config['date_format']),
                );
            }

            // Show checkbox for toggling 'pretty dates'
            if (!isset($no_override['prettydate'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_prettydate';
                $input    = new html_checkbox(array('name' => '_pretty_date', 'id' => $field_id, 'value' => 1));

                $blocks['main']['options']['prettydate'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('prettydate'))),
                    'content' => $input->show($config['prettydate']?1:0),
                );
            }

            if (!isset($no_override['refresh_interval'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_refresh_interval';
                $select   = new html_select(array('name' => '_refresh_interval', 'id' => $field_id));

                $select->add($RCMAIL->gettext('never'), 0);
                foreach (array(1, 3, 5, 10, 15, 30, 60) as $min) {
                    if (!$config['min_refresh_interval'] || $config['min_refresh_interval'] <= $min * 60) {
                        $label = $RCMAIL->gettext(array('name' => 'everynminutes', 'vars' => array('n' => $min)));
                        $select->add($label, $min);
                    }
                }

                $blocks['main']['options']['refresh_interval'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('refreshinterval'))),
                    'content' => $select->show($config['refresh_interval']/60),
                );
            }
            // Skin dropdown commented

            /* // show drop-down for available skins
            if (!isset($no_override['skin'])) {
                if (!$current) {
                    continue 2;
                }

                $skins = rcmail_get_skins();

                if (count($skins) > 1) {
                    $field_id = 'rcmfd_skin';
                    $input    = new html_radiobutton(array('name'=>'_skin'));

                    foreach ($skins as $skin) {
                        $thumbnail   = "skins/$skin/thumbnail.png";
                        $skinname    = ucfirst($skin);
                        $author_link = $license_link = '';
                        $meta        = @json_decode(@file_get_contents(INSTALL_PATH . "skins/$skin/meta.json"), true);

                        if (is_array($meta) && $meta['name']) {
                            $skinname     = $meta['name'];
                            $author_link  = $meta['url'] ? html::a(array('href' => $meta['url'], 'target' => '_blank'), rcube::Q($meta['author'])) : rcube::Q($meta['author']);
                            $license_link = $meta['license-url'] ? html::a(array('href' => $meta['license-url'], 'target' => '_blank', 'tabindex' => '-1'), rcube::Q($meta['license'])) : rcube::Q($meta['license']);
                        }

                        $img = html::img(array(
                                'src'     => $thumbnail,
                                'class'   => 'skinthumbnail',
                                'alt'     => $skin,
                                'width'   => 64,
                                'height'  => 64,
                                'onerror' => "this.src = rcmail.assets_path('program/resources/blank.gif')",
                        ));

                        $skinnames[] = mb_strtolower($skinname);
                        $blocks['skin']['options'][$skin]['content'] = html::label(array('class' => 'skinselection'),
                            html::span('skinitem', $input->show($config['skin'], array('value' => $skin, 'id' => $field_id.$skin))) .
                            html::span('skinitem', $img) .
                            html::span('skinitem', html::span('skinname', rcube::Q($skinname)) . html::br() .
                                html::span('skinauthor', $author_link ? 'by ' . $author_link : '') . html::br() .
                                html::span('skinlicense', $license_link ? $RCMAIL->gettext('license').':&nbsp;' . $license_link : ''))
                        );
                    }
                    array_multisort($blocks['skin']['options'], SORT_ASC, SORT_STRING, $skinnames);
                }
            } */

            // standard_windows option decides if new windows should be
            // opened as popups or standard windows (which can be handled by browsers as tabs)
            if (!isset($no_override['standard_windows'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_standard_windows';
                $checkbox = new html_checkbox(array('name' => '_standard_windows', 'id' => $field_id, 'value' => 1));

                $blocks['browser']['options']['standard_windows'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('standardwindows'))),
                    'content' => $checkbox->show($config['standard_windows']?1:0),
                );
            }

            if ($current) {
                $product_name = $RCMAIL->config->get('product_name', 'Roundcube Webmail');
                $RCMAIL->output->add_script(sprintf("%s.check_protocol_handler('%s', '#mailtoprotohandler');",
                    rcmail_output::JS_OBJECT_NAME, rcube::JQ($product_name)), 'docready');
            }

            $blocks['browser']['options']['mailtoprotohandler'] = array(
                'content' => html::a(array(
                    'href'    => '#',
                    'id'      => 'mailtoprotohandler'
                ),
                rcube::Q($RCMAIL->gettext('mailtoprotohandler'))) .
                html::span('mailtoprotohandler-status', ''),
            );

        break;

        // Mailbox view (mail screen)
        case 'mailbox':
            $blocks = array(
                'main'        => array('name' => rcube::Q($RCMAIL->gettext('mainoptions'))),
                'new_message' => array('name' => rcube::Q($RCMAIL->gettext('newmessage'))),
                'advanced'    => array('name' => rcube::Q($RCMAIL->gettext('advancedoptions'))),
            );

            // show config parameter for preview pane
            if (!isset($no_override['preview_pane'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_preview';
                $input    = new html_checkbox(array('name' => '_preview_pane', 'id' => $field_id, 'value' => 1,
                    'onchange' => "$('#rcmfd_preview_pane_mark_read').prop('disabled', !this.checked)"));

                $blocks['main']['options']['preview_pane'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('previewpane'))),
                    'content' => $input->show($config['preview_pane']?1:0),
                );
            }

            // show config parameter for preview pane auto mark as read delay
            if (!isset($no_override['preview_pane_mark_read'])) {
                if (!$current) {
                    continue 2;
                }

                // apply default if config option is not set at all
                $config['preview_pane_mark_read'] = $RCMAIL->config->get('preview_pane_mark_read', 0);

                $field_id = 'rcmfd_preview_pane_mark_read';
                $select   = new html_select(array('name' => '_preview_pane_mark_read', 'id' => $field_id,
                    'disabled' => $config['preview_pane']?0:1));

                $select->add($RCMAIL->gettext('never'), '-1');
                $select->add($RCMAIL->gettext('immediately'), 0);

                foreach (array(5, 10, 20, 30) as $sec) {
                    $label = $RCMAIL->gettext(array('name' => 'afternseconds', 'vars' => array('n' => $sec)));
                    $select->add($label, $sec);
                }

                $blocks['main']['options']['preview_pane_mark_read'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('previewpanemarkread'))),
                    'content' => $select->show(intval($config['preview_pane_mark_read'])),
                );
            }

            if (!isset($no_override['mdn_requests'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_mdn_requests';
                $select   = new html_select(array('name' => '_mdn_requests', 'id' => $field_id));
                $select->add($RCMAIL->gettext('askuser'), 0);
                $select->add($RCMAIL->gettext('autosend'), 1);
                $select->add($RCMAIL->gettext('autosendknown'), 3);
                $select->add($RCMAIL->gettext('autosendknownignore'), 4);
                $select->add($RCMAIL->gettext('ignorerequest'), 2);

                $blocks['main']['options']['mdn_requests'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('mdnrequests'))),
                    'content' => $select->show($config['mdn_requests']),
                );
            }

            if (!isset($no_override['autoexpand_threads'])) {
                if (!$current) {
                    continue 2;
                }

                $storage   = $RCMAIL->get_storage();
                $supported = $storage->get_capability('THREAD');

                if ($supported) {
                    $field_id = 'rcmfd_autoexpand_threads';
                    $select   = new html_select(array('name' => '_autoexpand_threads', 'id' => $field_id));
                    $select->add($RCMAIL->gettext('never'), 0);
                    $select->add($RCMAIL->gettext('do_expand'), 1);
                    $select->add($RCMAIL->gettext('expand_only_unread'), 2);

                    $blocks['main']['options']['autoexpand_threads'] = array(
                        'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('autoexpand_threads'))),
                        'content' => $select->show($config['autoexpand_threads']),
                    );
                }
            }

            // show page size selection
            if (!isset($no_override['mail_pagesize'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_mail_pagesize';
                $input    = new html_inputfield(array('name' => '_mail_pagesize', 'id' => $field_id, 'size' => 5));
                $size     = intval($config['mail_pagesize'] ? $config['mail_pagesize'] : $config['pagesize']);

                $blocks['main']['options']['pagesize'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('pagesize'))),
                    'content' => $input->show($size ? $size : 50),
                );
            }

            if (!isset($no_override['check_all_folders'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_check_all_folders';
                $input    = new html_checkbox(array('name' => '_check_all_folders', 'id' => $field_id, 'value' => 1));

                $blocks['new_message']['options']['check_all_folders'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('checkallfolders'))),
                    'content' => $input->show($config['check_all_folders']?1:0),
                );
            }
        break;

        // Message viewing
        case 'mailview':
            $blocks = array(
                'main' => array('name' => rcube::Q($RCMAIL->gettext('mainoptions'))),
                'advanced'   => array('name' => rcube::Q($RCMAIL->gettext('advancedoptions'))),
            );

            // show checkbox to open message view in new window
            if (!isset($no_override['message_extwin'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_message_extwin';
                $input    = new html_checkbox(array('name' => '_message_extwin', 'id' => $field_id, 'value' => 1));

                $blocks['main']['options']['message_extwin'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('showinextwin'))),
                    'content' => $input->show($config['message_extwin']?1:0),
                );
            }

            // show checkbox to show email instead of name
            if (!isset($no_override['message_show_email'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_message_show_email';
                $input    = new html_checkbox(array('name' => '_message_show_email', 'id' => $field_id, 'value' => 1));

                $blocks['main']['options']['message_show_email'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('showemail'))),
                    'content' => $input->show($config['message_show_email']?1:0),
                );
            }

            // show checkbox for HTML/plaintext messages
            if (!isset($no_override['prefer_html'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_htmlmsg';
                $input    = new html_checkbox(array('name' => '_prefer_html', 'id' => $field_id, 'value' => 1,
                    'onchange' => "$('#rcmfd_show_images').prop('disabled', !this.checked).val(0)"));

                $blocks['main']['options']['prefer_html'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('preferhtml'))),
                    'content' => $input->show($config['prefer_html']?1:0),
                );
            }

            if (!isset($no_override['default_charset'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_default_charset';

                $blocks['advanced']['options']['default_charset'] = array(
                    'title' => html::label($field_id, rcube::Q($RCMAIL->gettext('defaultcharset'))),
                    'content' => $RCMAIL->output->charset_selector(array(
                        'id' => $field_id, 'name' => '_default_charset', 'selected' => $config['default_charset']
                )));
            }

            if (!isset($no_override['show_images'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_show_images';
                $input    = new html_select(array('name' => '_show_images', 'id' => $field_id,
                    'disabled' => !$config['prefer_html']));

                $input->add($RCMAIL->gettext('never'), 0);
                $input->add($RCMAIL->gettext('fromknownsenders'), 1);
                $input->add($RCMAIL->gettext('always'), 2);

                $blocks['main']['options']['show_images'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('showremoteimages'))),
                    'content' => $input->show($config['prefer_html'] ? $config['show_images'] : 0),
                );
            }

            if (!isset($no_override['inline_images'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_inline_images';
                $input    = new html_checkbox(array('name' => '_inline_images', 'id' => $field_id, 'value' => 1));

                $blocks['main']['options']['inline_images'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('showinlineimages'))),
                    'content' => $input->show($config['inline_images']?1:0),
                );
            }

            // "display after delete" checkbox
            if (!isset($no_override['display_next'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_displaynext';
                $input    = new html_checkbox(array('name' => '_display_next', 'id' => $field_id, 'value' => 1));

                $blocks['main']['options']['display_next'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('displaynext'))),
                    'content' => $input->show($config['display_next']?1:0),
                );
            }
        break;

        // Mail composition
        case 'compose':
            $blocks = array(
                'main'       => array('name' => rcube::Q($RCMAIL->gettext('mainoptions'))),
                'sig'        => array('name' => rcube::Q($RCMAIL->gettext('signatureoptions'))),
                'spellcheck' => array('name' => rcube::Q($RCMAIL->gettext('spellcheckoptions'))),
                'advanced'   => array('name' => rcube::Q($RCMAIL->gettext('advancedoptions'))),
            );

            // show checkbox to compose messages in a new window
            if (!isset($no_override['compose_extwin'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfdcompose_extwin';
                $input    = new html_checkbox(array('name' => '_compose_extwin', 'id' => $field_id, 'value' => 1));

                $blocks['main']['options']['compose_extwin'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('composeextwin'))),
                    'content' => $input->show($config['compose_extwin']?1:0),
                );
            }

            if (!isset($no_override['htmleditor'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_htmleditor';
                $select   = new html_select(array('name' => '_htmleditor', 'id' => $field_id));

                $select->add($RCMAIL->gettext('never'), 0);
                $select->add($RCMAIL->gettext('always'), 1);
                $select->add($RCMAIL->gettext('htmlonreply'), 2);
                $select->add($RCMAIL->gettext('htmlonreplyandforward'), 3);

                $blocks['main']['options']['htmleditor'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('htmleditor'))),
                    'content' => $select->show(intval($config['htmleditor'])),
                );
            }

            if (!isset($no_override['draft_autosave'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_autosave';
                $select   = new html_select(array('name' => '_draft_autosave', 'id' => $field_id, 'disabled' => empty($config['drafts_mbox'])));

                $select->add($RCMAIL->gettext('never'), 0);
                foreach (array(1, 3, 5, 10) as $i => $min) {
                    $label = $RCMAIL->gettext(array('name' => 'everynminutes', 'vars' => array('n' => $min)));
                    $select->add($label, $min*60);
                }

                $blocks['main']['options']['draft_autosave'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('autosavedraft'))),
                    'content' => $select->show($config['draft_autosave']),
                );
            }

            if (!isset($no_override['mime_param_folding'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_param_folding';
                $select   = new html_select(array('name' => '_mime_param_folding', 'id' => $field_id));

                $select->add($RCMAIL->gettext('2231folding'), 0);
                $select->add($RCMAIL->gettext('miscfolding'), 1);
                $select->add($RCMAIL->gettext('2047folding'), 2);

                $blocks['advanced']['options']['mime_param_folding'] = array(
                    'title'    => html::label($field_id, rcube::Q($RCMAIL->gettext('mimeparamfolding'))),
                    'content'  => $select->show($config['mime_param_folding']),
                );
            }

            if (!isset($no_override['force_7bit'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_force_7bit';
                $input    = new html_checkbox(array('name' => '_force_7bit', 'id' => $field_id, 'value' => 1));

                $blocks['advanced']['options']['force_7bit'] = array(
                    'title'    => html::label($field_id, rcube::Q($RCMAIL->gettext('force7bit'))),
                    'content'  => $input->show($config['force_7bit']?1:0),
                );
            }

            if (!isset($no_override['mdn_default'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_mdn_default';
                $input    = new html_checkbox(array('name' => '_mdn_default', 'id' => $field_id, 'value' => 1));

                $blocks['main']['options']['mdn_default'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('reqmdn'))),
                    'content' => $input->show($config['mdn_default']?1:0),
                );
            }

            if (!isset($no_override['dsn_default'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_dsn_default';
                $input    = new html_checkbox(array('name' => '_dsn_default', 'id' => $field_id, 'value' => 1));

                $blocks['main']['options']['dsn_default'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('reqdsn'))),
                    'content' => $input->show($config['dsn_default']?1:0),
                );
            }

            if (!isset($no_override['reply_same_folder'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_reply_same_folder';
                $input    = new html_checkbox(array('name' => '_reply_same_folder', 'id' => $field_id, 'value' => 1));

                $blocks['main']['options']['reply_same_folder'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('replysamefolder'))),
                    'content' => $input->show($config['reply_same_folder']?1:0),
                );
            }

            if (!isset($no_override['reply_mode'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_reply_mode';
                $select   = new html_select(array('name' => '_reply_mode', 'id' => $field_id));

                $select->add($RCMAIL->gettext('replyempty'), -1);
                $select->add($RCMAIL->gettext('replybottomposting'), 0);
                $select->add($RCMAIL->gettext('replytopposting'), 1);

                $blocks['main']['options']['reply_mode'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('whenreplying'))),
                    'content' => $select->show(intval($config['reply_mode'])),
                );
            }

            if (!isset($no_override['spellcheck_before_send']) && $config['enable_spellcheck']) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_spellcheck_before_send';
                $input    = new html_checkbox(array('name' => '_spellcheck_before_send', 'id' => $field_id, 'value' => 1));

                $blocks['spellcheck']['options']['spellcheck_before_send'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('spellcheckbeforesend'))),
                    'content' => $input->show($config['spellcheck_before_send']?1:0),
                );
            }

            if ($config['enable_spellcheck']) {
                if (!$current) {
                    continue 2;
                }

                foreach (array('syms', 'nums', 'caps') as $key) {
                    $key = 'spellcheck_ignore_'.$key;
                    if (!isset($no_override[$key])) {
                        $input = new html_checkbox(array('name' => '_'.$key, 'id' => 'rcmfd_'.$key, 'value' => 1));

                        $blocks['spellcheck']['options'][$key] = array(
                            'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext(str_replace('_', '', $key)))),
                            'content' => $input->show($config[$key]?1:0),
                        );
                    }
                }
            }

            if (!isset($no_override['show_sig'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_show_sig';
                $select   = new html_select(array('name' => '_show_sig', 'id' => $field_id));

                $select->add($RCMAIL->gettext('never'), 0);
                $select->add($RCMAIL->gettext('always'), 1);
                $select->add($RCMAIL->gettext('newmessageonly'), 2);
                $select->add($RCMAIL->gettext('replyandforwardonly'), 3);

                $blocks['sig']['options']['show_sig'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('autoaddsignature'))),
                    'content' => $select->show($RCMAIL->config->get('show_sig', 1)),
                );
            }

            if (!isset($no_override['strip_existing_sig'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_strip_existing_sig';
                $input    = new html_checkbox(array('name' => '_strip_existing_sig', 'id' => $field_id, 'value' => 1));

                $blocks['sig']['options']['strip_existing_sig'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('replyremovesignature'))),
                    'content' => $input->show($config['strip_existing_sig']?1:0),
                );
            }

            if (!isset($no_override['forward_attachment'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_forward_attachment';
                $select = new html_select(array('name' => '_forward_attachment', 'id' => $field_id));

                $select->add($RCMAIL->gettext('inline'), 0);
                $select->add($RCMAIL->gettext('asattachment'), 1);

                $blocks['main']['options']['forward_attachment'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('forwardmode'))),
                    'content' => $select->show(intval($config['forward_attachment'])),
                );
            }

            if (!isset($no_override['default_font']) || !isset($no_override['default_font_size'])) {
                if (!$current) {
                    continue 2;
                }

                // Default font size
                $field_id = 'rcmfd_default_font_size';
                $select_default_font_size = new html_select(array('name' => '_default_font_size', 'id' => $field_id));

                $fontsizes = array('', '8pt', '10pt', '12pt', '14pt', '18pt', '24pt', '36pt');
                foreach ($fontsizes as $size) {
                    $select_default_font_size->add($size, $size);
                }

                // Default font
                $field_id = 'rcmfd_default_font';
                $select_default_font = new html_select(array('name' => '_default_font', 'id' => $field_id));
                $select_default_font->add('', '');

                $fonts = rcmail::font_defs();
                foreach (array_keys($fonts) as $fname) {
                    $select_default_font->add($fname, $fname);
                }

                $blocks['main']['options']['default_font'] = array(
                    'title' => html::label($field_id, rcube::Q($RCMAIL->gettext('defaultfont'))),
                    'content' => $select_default_font->show($RCMAIL->config->get('default_font', 1)) .
                        $select_default_font_size->show($RCMAIL->config->get('default_font_size', 1))
                );
            }

            if (!isset($no_override['reply_all_mode'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_reply_all_mode';
                $select   = new html_select(array('name' => '_reply_all_mode', 'id' => $field_id));

                $select->add($RCMAIL->gettext('replyalldefault'), 0);
                $select->add($RCMAIL->gettext('replyalllist'), 1);

                $blocks['main']['options']['reply_all_mode'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('replyallmode'))),
                    'content' => $select->show(intval($config['reply_all_mode'])),
                );
            }

            if (!isset($no_override['compose_save_localstorage'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_compose_save_localstorage';
                $input    = new html_checkbox(array('name' => '_compose_save_localstorage', 'id' => $field_id, 'value' => 1));

                $blocks['advanced']['options']['compose_save_localstorage'] = array(
                    'title'    => html::label($field_id, rcube::Q($RCMAIL->gettext('savelocalstorage'))),
                    'content'  => $input->show($config['compose_save_localstorage']?1:0),
                );
            }

        break;

        // Addressbook config
        case 'addressbook':
            $blocks = array(
                'main'     => array('name' => rcube::Q($RCMAIL->gettext('mainoptions'))),
                'advanced' => array('name' => rcube::Q($RCMAIL->gettext('advancedoptions'))),
            );

            if (!isset($no_override['default_addressbook'])
                && (!$current || ($books = $RCMAIL->get_address_sources(true, true)))
            ) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_default_addressbook';
                $select   = new html_select(array('name' => '_default_addressbook', 'id' => $field_id));

                foreach ($books as $book) {
                    $select->add(html_entity_decode($book['name'], ENT_COMPAT, 'UTF-8'), $book['id']);
                }

                $blocks['main']['options']['default_addressbook'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('defaultabook'))),
                    'content' => $select->show($config['default_addressbook']),
                );
            }

            // show addressbook listing mode selection
            if (!isset($no_override['addressbook_name_listing'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_addressbook_name_listing';
                $select   = new html_select(array('name' => '_addressbook_name_listing', 'id' => $field_id));

                $select->add($RCMAIL->gettext('name'), 0);
                $select->add($RCMAIL->gettext('firstname') . ' '  . $RCMAIL->gettext('surname'), 1);
                $select->add($RCMAIL->gettext('surname')   . ' '  . $RCMAIL->gettext('firstname'), 2);
                $select->add($RCMAIL->gettext('surname')   . ', ' . $RCMAIL->gettext('firstname'), 3);

                $blocks['main']['options']['list_name_listing'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('listnamedisplay'))),
                    'content' => $select->show($config['addressbook_name_listing']),
                );
            }

            // show addressbook sort column
            if (!isset($no_override['addressbook_sort_col'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_addressbook_sort_col';
                $select   = new html_select(array('name' => '_addressbook_sort_col', 'id' => $field_id));

                $select->add($RCMAIL->gettext('name'), 'name');
                $select->add($RCMAIL->gettext('firstname'), 'firstname');
                $select->add($RCMAIL->gettext('surname'), 'surname');

                $blocks['main']['options']['sort_col'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('listsorting'))),
                    'content' => $select->show($config['addressbook_sort_col']),
                );
            }

            // show addressbook page size selection
            if (!isset($no_override['addressbook_pagesize'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_addressbook_pagesize';
                $input    = new html_inputfield(array('name' => '_addressbook_pagesize', 'id' => $field_id, 'size' => 5));
                $size     = intval($config['addressbook_pagesize'] ? $config['addressbook_pagesize'] : $config['pagesize']);

                $blocks['main']['options']['pagesize'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('pagesize'))),
                    'content' => $input->show($size ? $size : 50),
                );
            }

            if (!isset($no_override['autocomplete_single'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_autocomplete_single';
                $checkbox = new html_checkbox(array('name' => '_autocomplete_single', 'id' => $field_id, 'value' => 1));

                $blocks['main']['options']['autocomplete_single'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('autocompletesingle'))),
                    'content' => $checkbox->show($config['autocomplete_single']?1:0),
                );
            }
        break;

        // Special IMAP folders
        case 'folders':
            $blocks = array(
                'main'     => array('name' => rcube::Q($RCMAIL->gettext('mainoptions'))),
                'advanced' => array('name' => rcube::Q($RCMAIL->gettext('advancedoptions'))),
            );

            if (!isset($no_override['show_real_foldernames'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'show_real_foldernames';
                $input    = new html_checkbox(array('name' => '_show_real_foldernames', 'id' => $field_id, 'value' => 1));

                $blocks['main']['options']['show_real_foldernames'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('show_real_foldernames'))),
                    'content' => $input->show($config['show_real_foldernames']?1:0),
                );
            }

            // Configure special folders
            $set = array('drafts_mbox', 'sent_mbox', 'junk_mbox', 'trash_mbox');
            if ($current && count(array_intersect($no_override, $set)) < 4) {
                $select = $RCMAIL->folder_selector(array(
                    'noselection'   => '---',
                    'realnames'     => true,
                    'maxlength'     => 30,
                    'folder_filter' => 'mail',
                    'folder_rights' => 'w',
                ));

                // #1486114, #1488279, #1489219
                $onchange = "if ($(this).val() == 'INBOX') $(this).val('')";
            }

            if (!isset($no_override['drafts_mbox'])) {
                if (!$current) {
                    continue 2;
                }

                $blocks['main']['options']['drafts_mbox'] = array(
                    'title'   => rcube::Q($RCMAIL->gettext('drafts')),
                    'content' => $select->show($config['drafts_mbox'], array('name' => "_drafts_mbox", 'onchange' => $onchange)),
                );
            }

            if (!isset($no_override['sent_mbox'])) {
                if (!$current) {
                    continue 2;
                }

                $blocks['main']['options']['sent_mbox'] = array(
                    'title'   => rcube::Q($RCMAIL->gettext('sent')),
                    'content' => $select->show($config['sent_mbox'], array('name' => "_sent_mbox", 'onchange' => '')),
                );
            }

            if (!isset($no_override['junk_mbox'])) {
                if (!$current) {
                    continue 2;
                }

                $blocks['main']['options']['junk_mbox'] = array(
                    'title'   => rcube::Q($RCMAIL->gettext('junk')),
                    'content' => $select->show($config['junk_mbox'], array('name' => "_junk_mbox", 'onchange' => $onchange)),
                );
            }

            if (!isset($no_override['trash_mbox'])) {
                if (!$current) {
                    continue 2;
                }

                $blocks['main']['options']['trash_mbox'] = array(
                    'title'   => rcube::Q($RCMAIL->gettext('trash')),
                    'content' => $select->show($config['trash_mbox'], array('name' => "_trash_mbox", 'onchange' => $onchange)),
                );
            }
        break;

        // Server settings
        case 'server':
            $blocks = array(
                'main'        => array('name' => rcube::Q($RCMAIL->gettext('mainoptions'))),
                'maintenance' => array('name' => rcube::Q($RCMAIL->gettext('maintenance'))),
                'advanced'    => array('name' => rcube::Q($RCMAIL->gettext('advancedoptions'))),
            );

            if (!isset($no_override['read_when_deleted'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_read_deleted';
                $input    = new html_checkbox(array('name' => '_read_when_deleted', 'id' => $field_id, 'value' => 1));

                $blocks['main']['options']['read_when_deleted'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('readwhendeleted'))),
                    'content' => $input->show($config['read_when_deleted']?1:0),
                );
            }

            if (!isset($no_override['flag_for_deletion'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_flag_for_deletion';
                $input    = new html_checkbox(array('name' => '_flag_for_deletion', 'id' => $field_id, 'value' => 1));

                $blocks['main']['options']['flag_for_deletion'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('flagfordeletion'))),
                    'content' => $input->show($config['flag_for_deletion']?1:0),
                );
            }

            // don't show deleted messages
            if (!isset($no_override['skip_deleted'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_skip_deleted';
                $input    = new html_checkbox(array('name' => '_skip_deleted', 'id' => $field_id, 'value' => 1));

                $blocks['main']['options']['skip_deleted'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('skipdeleted'))),
                    'content' => $input->show($config['skip_deleted']?1:0),
                );
            }

            if (!isset($no_override['delete_always'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_delete_always';
                $input    = new html_checkbox(array('name' => '_delete_always', 'id' => $field_id, 'value' => 1));

                $blocks['main']['options']['delete_always'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('deletealways'))),
                    'content' => $input->show($config['delete_always']?1:0),
                );
            }

            if (!isset($no_override['delete_junk'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_delete_junk';
                $input    = new html_checkbox(array('name' => '_delete_junk', 'id' => $field_id, 'value' => 1));

                $blocks['main']['options']['delete_junk'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('deletejunk'))),
                    'content' => $input->show($config['delete_junk']?1:0),
                );
            }

            // Trash purging on logout
            if (!isset($no_override['logout_purge'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_logout_purge';
                $input    = new html_checkbox(array('name' => '_logout_purge', 'id' => $field_id, 'value' => 1));

                $blocks['maintenance']['options']['logout_purge'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('logoutclear'))),
                    'content' => $input->show($config['logout_purge']?1:0),
                );
            }

            // INBOX compacting on logout
            if (!isset($no_override['logout_expunge'])) {
                if (!$current) {
                    continue 2;
                }

                $field_id = 'rcmfd_logout_expunge';
                $input    = new html_checkbox(array('name' => '_logout_expunge', 'id' => $field_id, 'value' => 1));

                $blocks['maintenance']['options']['logout_expunge'] = array(
                    'title'   => html::label($field_id, rcube::Q($RCMAIL->gettext('logoutcompact'))),
                    'content' => $input->show($config['logout_expunge']?1:0),
                );
            }
        }

        $found = false;
        $data  = $RCMAIL->plugins->exec_hook('preferences_list',
            array('section' => $sect['id'], 'blocks' => $blocks, 'current' => $current));

        $advanced_prefs = (array) $RCMAIL->config->get('advanced_prefs');

        // create output
        foreach ($data['blocks'] as $key => $block) {
            if (!empty($block['content']) || !empty($block['options'])) {
                $found = true;
            }
            // move some options to the 'advanced' block as configured by admin
            if ($key != 'advanced') {
                foreach ($advanced_prefs as $opt) {
                    if ($block['options'][$opt]) {
                        $data['blocks']['advanced']['options'][$opt] = $block['options'][$opt];
                        unset($data['blocks'][$key]['options'][$opt]);
                    }
                }
            }
        }

        // move 'advanced' block to the end of the list
        if (!empty($data['blocks']['advanced'])) {
            $adv = $data['blocks']['advanced'];
            unset($data['blocks']['advanced']);
            $data['blocks']['advanced'] = $adv;
        }

        if (!$found)
            unset($sections[$idx]);
        else
            $sections[$idx]['blocks'] = $data['blocks'];
    }

    $data = $RCMAIL->plugins->exec_hook('preferences_section_header',
        array('section' => $sect['id'], 'header' => '', 'current' => $current));

    if(!empty($data['header'])) {
        $sections[$idx]['header'] = $data['header'];
    }

    return array($sections, $plugin['cols']);
}


/** 
 * Macgregor Changes
 * Create admin menu
**/
function rcmail_admin_menu( $current = null )
{
	global $RCMAIL , $OUTPUT, $RC_HELP;
	
	$sections['changepwd'] = array('id' => 'changepwd', 'admin' => $RCMAIL->gettext('changepwdTitle'));
	// Notification Commented
	/* $sections['notification'] = array('id' => 'notification', 'admin' => rcube_label('notification')); */
	
	// hook + define list cols
	$plugin = $RCMAIL->plugins->exec_hook('admin_sections_list',
		array('list' => $sections, 'cols' => array('admin')));
	$sections = $plugin['list'];

	$config = $RCMAIL->config->all();
	$no_override = array_flip($RCMAIL->config->get('dont_override', array()));
	
	foreach ($sections as $idx => $sect) {

		if ($current && $sect['id'] != $current) {
			continue;
		}
		
		$blocks = array();

		switch ($sect['id']) {
			// MG TODO: check whether advanced options are mandatory
			case 'changepwd':
			$blocks = array(
			  'main'    => array('name' =>rcube::Q($RCMAIL->gettext('manageadmin'))),
              'advanced'=> array('name' => rcube::Q($RCMAIL->gettext('advancedoptions'))),
			);
			
			$desc =  html::span('admin_opt_desc', 'You can change your password by Entering the details below.');
			
			$blocks['main']['options']['message'] = array(
					'content' => $desc
				);
			
			// txt box
			if (!isset($no_override['current_admin_pass'])) {
				if (!$current) {
                    continue 2;
                }
				
				$field_id = 'rcmfd_current_ap';
				$input_curr_pwd = new html_passwordfield( array( 'name' => '_curr_admin_pwd', 'id' => $field_id, 'size' => 20, 'value' => ( ( ( isset( $_POST[  '_curr_admin_pwd' ] ) ) && ( $RC_HELP->admin_ok != true ) ) ? ( $_POST[ '_curr_admin_pwd' ] ) :( "" ) ) ) );
				$curr_pwd = $config['current_admin_pass'];
				
				$blocks['main']['options']['curr_pwd'] = array(
					'title' => html::label($field_id, rcube::Q($RCMAIL->gettext('curradminpwd'))),
					'content' => $input_curr_pwd->show(),
				);
			}
			
			if (!isset($no_override['admin_pass'])) {
			  $field_id = 'rcmfd_admin_pwd';
			  $input_pwd = new html_passwordfield(array('name' => '_admin_pwd', 'id' => $field_id, 'size' => 20, 'value' => ( ( ( isset( $_POST[ '_admin_pwd' ] ) ) && ( $RC_HELP->admin_ok != true ) ) ? ( $_POST[ '_admin_pwd' ] ) : ( "" ) ) ) );
			  $pwd = $config['admin_pass'];

			  $blocks['main']['options']['pwd'] = array(
				'title' => html::label($field_id, rcube::Q($RCMAIL->gettext('adminpwd'))),
				'content' => $input_pwd->show(),
			  );
			}
			
			if (!isset($no_override['confirm_admin_pass'])) {
				$field_id = 'rcmfd_confirm_ap';
				$input_conf_pwd = new html_passwordfield(array('name' => '_conf_admin_pwd', 'id' => $field_id, 'size' => 20, 'value' => ( ( ( isset( $_POST[ '_conf_admin_pwd' ] ) ) && ( $RC_HELP->admin_ok != true ) ) ? ( $_POST[ '_conf_admin_pwd' ] ) : ( "" ) ) ) );
				
				$conf_pwd = $config['confirm_admin_pass'];
				
				$blocks['main']['options']['conf_pwd'] = array(
					'title' => html::label($field_id, rcube::Q($RCMAIL->gettext('confadminpwd'))),
					'content' => $input_conf_pwd->show(),
				);
			}
			
			break;
			
			case 'notification':
				$blocks = array(
					'main'    => array('name' => rcube::Q($RCMAIL->gettext('managenotification'))),
				);
				$desc =  html::span('admin_opt_desc', 'Notifications will be sent to entered Email.');
				$blocks['main']['options']['message'] = array(
					'content' => $desc
				);
				if (!isset($no_override['email_notification'])) {
					$field_id = 'rcmfd_current_ap';
					$input_email_notf = new html_inputfield( array( 'name' => '_email_notify_txtbox', 'id' => $field_id, 'size' => 20, 'value' => ( ( isset( $_POST[ '_email_notify_txtbox' ] ) ) ? ( $_POST[ '_email_notify_txtbox' ] ) : ( '' ) ) ) );
					
					$blocks['main']['options']['email_notify'] = array(
						'title' => html::label($field_id, rcube::Q($RCMAIL->gettext('email_notify_txt'))),
						'content' => $input_email_notf->show(),
					);
				}
				
				break;
		} // switch end
		
		// MG TODO: check both the lines
		$found = false;
		$data  = $RCMAIL->plugins->exec_hook('admin_list',
            array('section' => $sect['id'], 'blocks' => $blocks, 'current' => $current));
		
		$advanced_prefs = (array) $RCMAIL->config->get('advanced_prefs');
		
		// create output
        foreach ($data['blocks'] as $key => $block) {
			if (!empty($block['content']) || !empty($block['options'])) {
                $found = true;
            }
			// move some options to the 'advanced' block as configured by admin
            if ($key != 'advanced') {
				foreach ($advanced_prefs as $opt) {
                    if ($block['options'][$opt]) {
                        $data['blocks']['advanced']['options'][$opt] = $block['options'][$opt];
                        unset($data['blocks'][$key]['options'][$opt]);
                    }
                }
			}
		} // foreach 2 end
		
		if (!empty($data['blocks']['advanced'])) {
			$adv = $data['blocks']['advanced'];
			unset($data['blocks']['advanced']);
			$data['blocks']['advanced'] = $adv;
		}
		
		if (!$found)
            unset($sections[$idx]);
        else
            $sections[$idx]['blocks'] = $data['blocks'];
	} // foreach end 
	
	$data = $RCMAIL->plugins->exec_hook('admin_section_header',
        array('section' => $sect['id'], 'header' => '', 'current' => $current));
		
	if(!empty($data['header'])) {
        $sections[$idx]['header'] = $data['header'];
    }
		
	return array($sections, $plugin['cols']);
}

/**
 * Macgregor Changes
 * superadmin menu 
**/
function rcmail_superadmin_menu( $current = null )
{
	global $RCMAIL , $OUTPUT, $RC_HELP;
	
	$sections['new_account'] = array('id' => 'new_account', 'superadmin' => $RCMAIL->gettext('new_account_ttl'));
	$sections['new_user'] = array('id' => 'new_user', 'superadmin' => $RCMAIL->gettext('new_user'));
	$sections['new_admin'] = array('id' => 'new_admin', 'superadmin' => $RCMAIL->gettext('new_admin'));
	
	$plugin = $RCMAIL->plugins->exec_hook('superadmin_sections_list',
		array('list' => $sections, 'cols' => array('superadmin')));
	$sections = $plugin['list'];

	$config = $RCMAIL->config->all();
	$no_override = array_flip($RCMAIL->config->get('dont_override', array()));
	
	foreach ($sections as $idx => $sect) {

		if ($current && $sect['id'] != $current) {
			continue;
		}
		
		$blocks = array();

		switch ($sect['id']) {
			
			case 'new_account':
				
				$domain_name = ( ( $RC_HELP->super_admin_ok == true ) ? ( "" ) : ( $_POST[ '_add_superadmin_dom_nm' ] ) );
				$max_users = ( ( $RC_HELP->super_admin_ok == true ) ? ( "" ) : ( $_POST[ '_add_superadmin_max_users' ] ) );
				$inbox_size = ( ( $RC_HELP->super_admin_ok == true ) ? ( "" ) : ( $_POST[ '_add_superadmin_inbox_size' ] ) );
				
				$blocks = array(
				  'main'    => array('name' => rcube::Q($RCMAIL->gettext('new_account_ttl'))),
				);
				
				$desc =  html::span('admin_opt_desc', 'You can add a New Account by Entering the details below.');
				$blocks['main']['options']['message'] = array(
					'content' => $desc
				);
				
				$domain_name_txt = new html_inputfield( array( 'name' => '_add_superadmin_dom_nm', 'id' => '_add_superadmin_dom_nm', 'value' => $domain_name  ) );
				
				$blocks['main']['options']['add_superadmin_dom_nm'] = array(
					'title' => html::label( 'domain_name', 'Domain Name' ),
					'content' => $domain_name_txt->show()
				);
				
				$max_users_txt = new html_inputfield( array( 'name' => '_add_superadmin_max_users', 'id' => '_add_superadmin_max_users', 'size' => 20, 'value' => $max_users ) );
				
				$blocks['main']['options']['add_superadmin_max_users'] = array(
					'title' => html::label( 'max_users', 'Maximum No. of Users' ),
					'content' => $max_users_txt->show()
				);
				
				$inbox_size_txt = new html_inputfield( array( 'name' => '_add_superadmin_inbox_size', 'id' => '_add_superadmin_inbox_size', 'size' => 20, 'value' => $inbox_size ) );
				
				$blocks['main']['options']['add_superadmin_inbox_size'] = array(
					'title' => html::label( 'inbox_size', 'Inbox Size (GB)' ),
					'content' => $inbox_size_txt->show()
				);
				
				$submit_btn = new html_inputfield( array( 'type' => 'submit', 'value' => 'Save', 'class' => 'button mainaction manage_user_edt_del', 'name' => 'super_admin_flg' ) );
				
				$blocks['main']['options']['submit'] = array(
					'content' => $submit_btn->show()
				);
				
				break;
				
			case 'new_user': // for only users table
				
				$all_domains = $RC_HELP->get_all_domains();
				
				$user_name = ( ( $RC_HELP->super_admin_ok ) ? ( "" ) : ( $_POST[ '_add_user_name' ] ) );
				$user_email = ( ( $RC_HELP->super_admin_ok ) ? ( "" ) : ( $_POST[ '_add_user_email_nm' ] ) );
				$user_domain = ( ( $RC_HELP->super_admin_ok ) ? ( $all_domains[ 'dom_name' ][ 0 ] ) : ( $_POST[ '_add_user_email_dom' ] ) );
				
				if( $RC_HELP->super_admin_ok )
				{
					$user_quota = "1GB";
				}
				else if( ( isset( $_POST[ 'super_admin_flg' ] ) ) && ( $_POST[ 'super_admin_flg' ] == 'Save' ) )
				{
					$user_quota = $_POST[ '_add_user_quota' ];
				}
				else
				{
					$user_quota = "1GB";
				}
				
				$blocks = array(
				  'main'    => array('name' => rcube::Q($RCMAIL->gettext('new_user_ttl'))),
				);
				
				$desc =  html::span('admin_opt_desc', 'You can add a New User by Entering the details below.');
				$blocks['main']['options']['message'] = array(
					'content' => $desc
				);
				
				$input_user_name = new html_inputfield( array( 'name' => '_add_user_name', 'id' => '_add_user_name', 'size' => 20, 'value' => $user_name ) );
				
				$blocks['main']['options']['add_user_name'] = array(
					'title' => 'Name',
					'content' => $input_user_name->show()
				);
				
				$input_user_email_nm = new html_inputfield( array( 'name' => '_add_user_email_nm', 'id' => '_add_user_email_nm', 'size' => 20, 'value' => $user_email ) );
				$select_user_email_dom = new html_select( array( 'name' => '_add_user_email_dom', 'id' => '_add_user_email_dom', 'value' => '' ) );
				
				for( $i = 0; $i < count( $all_domains[ 'dom_name' ] ); $i++ )
				{
					$select_user_email_dom->add( $all_domains[ 'dom_name' ][ $i ], $all_domains[ 'dom_name' ][ $i ] );
				}
				
				$blocks['main']['options']['add_user_email_nm'] = array(
					'title' => 'Email',
					'content' => $input_user_email_nm->show().'@'.$select_user_email_dom->show( $user_domain )
				);
				
				$input_user_pwd = new html_inputfield( array( 'name' => '_add_user_pwd', 'id' => '_add_user_pwd', 'size' => 20, 'value' => '', 'autocomplete' => 'off' ) );
				$check_user_pwd_mask = new html_checkbox( array( 'id' => '_add_user_pwd_mask', 'name' => '_add_user_pwd_mask', 'value' => 1, 'onclick' => 'UI.toggle_pwd( \'_add_user_pwd_mask\', \'_add_user_pwd\' )' ) );
				$mask_title = html::label( '_add_user_pwd_mask', 'Mask' );
				$blocks['main']['options']['add_user_password'] = array(
					'title' => 'Password',
					'content' => $input_user_pwd->show().$check_user_pwd_mask->show().$mask_title
				);
				
				$select_add_user_quota = new html_select( array( 'name' => '_add_user_quota', 'id' => '_add_user_quota' ) );
				
				foreach( $RC_HELP->quota_array as $key => $value )
				{
					$select_add_user_quota->add( $value, $key );
				}
				
				$blocks['main']['options']['add_user_quota'] = array(
					'title' => 'Quota',
					'content' => $select_add_user_quota->show( $user_quota )
				);
				
				$submit_btn = new html_inputfield( array( 'type' => 'submit', 'value' => 'Save', 'class' => 'button mainaction manage_user_edt_del', 'name' => 'super_admin_flg' ) );
				
				$blocks['main']['options']['submit'] = array(
					'content' => $submit_btn->show()
				);
				
				break;
				
			case 'new_admin': // for user_admin table
				$RC_HELP->show_update_form = true;
				$all_domains = $RC_HELP->get_all_domains();
				
				$admin_domain = ( ( $RC_HELP->super_admin_ok ) ? ( $all_domains[ 0 ] ) : ( ( ( isset( $_POST[ 'super_admin_flg' ] ) ) && ( $_POST[ 'super_admin_flg' ] == 'Save' ) ) ? ( $_POST[ '_add_new_admin_domain' ] ) : ( $all_domains[ 0 ] ) ) );
				
				$admin_email = ( ( $RC_HELP->super_admin_ok ) ? ( 'default' ) : ( (  ( isset( $_POST[ 'super_admin_flg' ] ) ) && ( $_POST[ 'super_admin_flg' ] == 'Save' )  ) ? ( $_POST[ '_add_new_admin_useremail' ] ) : ( 'default' ) ) );
				
				$admin_domain_manage = ( ( $RC_HELP->super_admin_ok ) ? ( $all_domains[ 0 ] ) : ( (  ( isset( $_POST[ 'super_admin_flg' ] ) ) && ( $_POST[ 'super_admin_flg' ] == 'Save' )  ) ? ( $_POST[ '_add_new_admin_manage_dom' ] ) : ( $all_domains[ 0 ] ) ) );
				
				if( ( isset( $_POST[ 'super_admin_flg' ] ) ) && ( $_POST[ 'super_admin_flg' ] == 'Save' ) && ( $_POST[ '_add_new_admin_useremail' ] != 'default' ) && ( !( $RC_HELP->super_admin_ok ) ) )
				{
					$user_email_arr = $RC_HELP->get_user_details( $_POST[ '_add_new_admin_domain' ] );
				}
				
				$blocks = array(
				  'main'    => array('name' => rcube::Q($RCMAIL->gettext('new_admin_ttl'))),
				);
				
				$desc =  html::span('admin_opt_desc', 'You can add a New Admin by Entering the details below.');
				$blocks['main']['options']['message'] = array(
					'content' => $desc
				);
				
				$select_admin_domain = new html_select( array( 'id' => '_add_new_admin_domain', 'name' => '_add_new_admin_domain', 'onclick' => 'UI.add_user_by_domain(this)' ) );
				
				for( $i = 0; $i < count( $all_domains[ 'dom_name' ] ); $i++ )
				{
					$select_admin_domain->add( $all_domains[ 'dom_name' ][ $i ], $all_domains[ 'dom_name' ][ $i ] );
				}
				
				$blocks['main']['options']['add_admin_domain'] = array(
					'title' => 'User Domain',
					'content' => $select_admin_domain->show( $admin_domain )
				);
				
				$select_admin_username = new html_select( array( 'id' => '_add_new_admin_useremail', 'name' => '_add_new_admin_useremail', 'style' => 'width:275px;' ) );
				
				if( ( ( isset( $_POST[ 'super_admin_flg' ] ) ) && ( $_POST[ 'super_admin_flg' ] == 'Save' ) ) && ( $_POST[ '_add_new_admin_useremail' ] != 'default' ) && ( !( $RC_HELP->super_admin_ok ) ) )
				{
					for( $i = 0; $i < count( $user_email_arr[ 'user_email' ] ); $i++ )
						$select_admin_username->add( $user_email_arr[ 'user_email' ][ $i ], $user_email_arr[ 'user_email' ][ $i ] );
				}
				else
				{
					$select_admin_username->add( '--Please Select A Domain--', 'default' );
				}
				
				$blocks['main']['options']['add_admin_username'] = array(
					'title' => 'UserEmail',
					'content' => $select_admin_username->show( $admin_email )
				);
				
				$select_admin_domain_manage = new html_select( array( 'id' => '_add_new_admin_manage_dom', 'name' => '_add_new_admin_manage_dom' ) );
				
				for( $i = 0; $i < count( $all_domains[ 'dom_name' ] ); $i++ )
				{
					$select_admin_domain_manage->add( $all_domains[ 'dom_name' ][ $i ], $all_domains[ 'dom_name' ][ $i ] );
				}
				
				$blocks['main']['options']['add_admin_manage_dom'] = array(
					'title' => 'Domain to Manage',
					'content' => $select_admin_domain_manage->show( $admin_domain_manage )
				);
				
				$submit_btn = new html_inputfield( array( 'type' => 'submit', 'value' => 'Save', 'class' => 'button mainaction manage_user_edt_del', 'name' => 'super_admin_flg' ) );
				
				$blocks['main']['options']['submit'] = array(
					'content' => $submit_btn->show()
				);
				
				break;
				
		} // end of switch
		
		$data = $RCMAIL->plugins->exec_hook('superadmin_list', array('section' => $sect['id'], 'blocks' => $blocks, 'current' => $current));
		$advanced_prefs = (array) $RCMAIL->config->get('advanced_prefs');
		$found = false;
		
		// create output
		foreach ($data['blocks'] as $block) {
		  if (!empty($block['content']) || !empty($block['options'])) {
			$found = true;
		  }
		  
		  // move some options to the 'advanced' block as configured by admin
            if ($key != 'advanced') {
                foreach ($advanced_prefs as $opt) {
                    if ($block['options'][$opt]) {
                        $data['blocks']['advanced']['options'][$opt] = $block['options'][$opt];
                        unset($data['blocks'][$key]['options'][$opt]);
                    }
                }
            }
		  
		} // foreach 2 end
		
		 if (!empty($data['blocks']['advanced'])) {
            $adv = $data['blocks']['advanced'];
            unset($data['blocks']['advanced']);
            $data['blocks']['advanced'] = $adv;
        }

		if (!$found)
		  unset($sections[$idx]);
		else
		  $sections[$idx]['blocks'] = $data['blocks'];
	} // foreach end
	
	$data = $RCMAIL->plugins->exec_hook('superadmin_section_header',
        array('section' => $sect['id'], 'header' => '', 'current' => $current));

    if(!empty($data['header'])) {
        $sections[$idx]['header'] = $data['header'];
    }
	return array($sections, $plugin['cols']);
}


/**
 * Macgregor Changes
 * superadmin update menu
**/
function rcmail_superadmin_update( $current = null )
{
	global $RCMAIL , $OUTPUT, $RC_HELP;
	
	$sections['new_admin'] = array('id' => 'new_admin', 'superadmin' => $RCMAIL->gettext('new_admin'));
	
	$plugin = $RCMAIL->plugins->exec_hook('superadmin_sections_list',
		array('list' => $sections, 'cols' => array('superadmin')));
	$sections = $plugin['list'];

	$config = $RCMAIL->config->all();
	$no_override = array_flip($RCMAIL->config->get('dont_override', array()));
	
	foreach ($sections as $idx => $sect) { // foreach
		if ($current && $sect['id'] != $current) {
			continue;
		}
		
		$blocks = array();
		
		switch ($sect['id']) {
			
			case 'new_admin':
				
				$blocks = array(
				  'main'    => array('name' => rcube::Q($RCMAIL->gettext('superadmin_delete'))),
				);
				
				$submit_btn = new html_inputfield( array( 'type' => 'submit', 'value' => 'Delete', 'class' => 'button mainaction manage_user_edt_del', 'name' => 'super_admin_flg', 'onclick' => 'return UI.show_delete_admin_confirm()' ) );
				
				$blocks['main']['options']['submit'] = array(
					'content' => $submit_btn->show()
				);
				// DISPLAY LIST OF ALL USRES
				$user_admins = $RC_HELP->get_user_admin();
				// $unique_admins = array_values( array_unique( $user_admins[ 'email' ] ) );
				
				$tbl_user_admin = new html_table( array( 'cols' => 4 ) );
				$tbl_user_admin->add_header( 'name', 'Name' );
				$tbl_user_admin->add_header( 'email', 'Email' );
				$tbl_user_admin->add_header( 'managing_domain', 'Managing Domain' );
				$tbl_user_admin->add_header( 'edit', '' );
				
				if( count( $user_admins[ 'email' ] ) )
				{
					for( $i = 0; $i < count( $user_admins[ 'email' ] ); $i++ )
					{
						$user_admin_delete = new html_checkbox( array( 'name' => '_delete_admin_user_chk[]', 'id' => '_delete_admin_user_chk', 'onclick' => 'UI.delete_admin( this, \''.$user_admins[ 'email' ][ $i ].'\', \''.$user_admins[ 'managed_domain' ][ $i ].'\' )', 'value' => 1 ) );
						$tbl_user_admin->add( 'admin_user_name', $user_admins[ 'username' ][ $i ] );
						$tbl_user_admin->add( 'admin_user_email', $user_admins[ 'email' ][ $i ] );
						$tbl_user_admin->add( 'admin_user_selected_domain', $user_admins[ 'managed_domain' ][ $i ] );
						$tbl_user_admin->add( 'admin_user_delete_chk', $user_admin_delete->show() );
					}
				}
				else
				{
					$tbl_user_admin->add( array( 'colspan' => '4' ), 'There are No Admins To Display' );
				}
				
				$blocks['main']['options']['admin_tbl'] = array(
					'content' => $tbl_user_admin->show()
				);
				
				$hidden_admin_email = new html_hiddenfield( array( 'name' => '_hidden_admin_email', 'id' => '_hidden_admin_email', 'value' => '' ) );
				
				$blocks['main']['options']['edit_admin_email_hidden_field'] = array(
					'content' => $hidden_admin_email->show()
				);
				
				$hidden_admin_manage_domain = new html_hiddenfield( array( 'name' => '_hidden_admin_manage_domain', 'id' => '_hidden_admin_manage_domain', 'value' => '' ) );
				
				$blocks['main']['options']['edit_admin_manage_domain_hidden_fld'] = array(
					'content' => $hidden_admin_manage_domain->show()
				);
				
				break;
		} // switch end
		
		$data = $RCMAIL->plugins->exec_hook('superadmin_list', array('section' => $sect['id'], 'blocks' => $blocks, 'current' => $current));
		$found = false;
		$advanced_prefs = (array) $RCMAIL->config->get('advanced_prefs');
		
		// create output
		foreach ($data['blocks'] as $block) {
		  if (!empty($block['content']) || !empty($block['options'])) {
			$found = true;
		  }
		   // move some options to the 'advanced' block as configured by admin
            if ($key != 'advanced') {
                foreach ($advanced_prefs as $opt) {
                    if ($block['options'][$opt]) {
                        $data['blocks']['advanced']['options'][$opt] = $block['options'][$opt];
                        unset($data['blocks'][$key]['options'][$opt]);
                    }
                }
            }
		} // foreach 2 end
		
		// move 'advanced' block to the end of the list
        if (!empty($data['blocks']['advanced'])) {
            $adv = $data['blocks']['advanced'];
            unset($data['blocks']['advanced']);
            $data['blocks']['advanced'] = $adv;
        }
		
		if (!$found)
		  unset($sections[$idx]);
		else
		  $sections[$idx]['blocks'] = $data['blocks'];
	} // foreach end
	
	$data = $RCMAIL->plugins->exec_hook('superadmin_section_header',
        array('section' => $sect['id'], 'header' => '', 'current' => $current));
		
	if(!empty($data['header'])) {
        $sections[$idx]['header'] = $data['header'];
    }
	
	return array($sections, $plugin['cols']);
}

/**
 * Macgregor Changes
 * Add menu for manage users
**/
function rcmail_manage_add( $current = null )
{
	global $RCMAIL , $OUTPUT, $RC_HELP;
	
	if( $RCMAIL->config->get('is_admin_multiple') == true )
	{
		$sections['accounts'] = array('id' => 'accounts', 'manage' => $RCMAIL->gettext('accounts'));
	}
	
	if( $RC_HELP->admin_selected_domain )
	{
		$sections['domain_aliases'] = array('id' => 'domain_aliases', 'manage' => $RCMAIL->gettext('managedomain_aliases'));
		$sections['users'] = array('id' => 'users', 'manage' => $RCMAIL->gettext('manageusers'));
		$sections['groups'] = array('id' => 'groups', 'manage' => $RCMAIL->gettext('manageGroups'));
		$sections['aliases'] = array('id' => 'aliases', 'manage' => $RCMAIL->gettext('manageAliases'));
		$sections['user_rules'] = array('id' => 'user_rules', 'manage' => $RCMAIL->gettext('manageRules'));
	}
	
	// hook + define list cols
	$plugin = $RCMAIL->plugins->exec_hook('users_sections_list',
		array('list' => $sections, 'cols' => array('manage')));
	$sections = $plugin['list'];

	$config = $RCMAIL->config->all();
	// $no_override = array_flip($RCMAIL->config->get('dont_override', array()));
	
	foreach ($sections as $idx => $sect) {

		if ($current && $sect['id'] != $current) {
			continue;
		}
		
		$blocks = array();

		switch ($sect['id']) {
		
			case 'accounts':
					
					$all_domain_det = $RC_HELP->get_user_admin( "username='".$RC_HELP->user_name."' and mydomain='".$RC_HELP->domain."' and email='".$RC_HELP->email."'" ); // retrives all information of the selected admin domain
					$managed_domain = $all_domain_det[ "managed_domain" ]; // saves the domain of the admin
					
					$blocks = array(
					  'main' => array('name' => rcube::Q($RCMAIL->gettext('accounts'))),
					);
					
					$curr_domain_span = html::span( array( 'style' => 'font-weight:bold;' ), $RC_HELP->admin_selected_domain ); // will set to null if no account is managed
					$desc1 =  html::span('rules_opt_desc', 'Please select the account you want to administer. ' );
					if( $RC_HELP->admin_selected_domain )
					{
						$desc2 = html::span('rules_opt_desc', 'Currently Administering ' ); 
						$desc3 =  html::span('rules_opt_desc', ' Account' );
					}
					else
					{
						$desc2 = html::span( 'rules_opt_desc', 'Not Administering any account Currently' );
						$desc3 = "";
					}
					
					$blocks['main']['options']['desc_message1'] = array(
						'content' => $desc1
					);
					
					$blocks['main']['options']['desc_message2'] = array(
						'content' => $desc2.$curr_domain_span.$desc3
					);
					
					$field_id = '_account_selection_domain';
					$account_selection_domain_dd = new html_select( array( 'name' => '_account_selection_domain', 'id' => $field_id, 'value' => 1  ) );
					
					for( $i = 0; $i < count( $managed_domain ); $i++ )
					{
						$account_selection_domain_dd->add( $managed_domain[ $i ], $managed_domain[ $i ] );
					}
					
					$blocks['main']['options']['_account_selection_domain'] = array(
						'title' => html::label($field_id, rcube::Q($RCMAIL->gettext('accounts_domain'))),
						'content' => $account_selection_domain_dd->show( $RC_HELP->get_selected_domain() ),
					);
					
					$edit_submit = new html_inputfield( array( 'type' => 'submit', 'value' => 'Save', 'class' => 'button mainaction manage_user_edt_del', 'name' => 'manage_account_selection_flg' ) );
					
					$blocks['main']['options']['submit'] = array(
						'content' => $edit_submit->show(),
					);
					
					break;
					
			case 'domain_aliases':
	
				$blocks = array(
				  'main' => array('name' => rcube::Q($RCMAIL->gettext('accounts'))),
				);
				
				$edit_submit = new html_inputfield( array( 'type' => 'submit', 'value' => 'Edit', 'class' => 'button mainaction manage_user_edt_del', 'name' => 'manage_domain_alias_flg', 'onclick' => 'UI.show_domain_details(\'primary\', \''.$RC_HELP->admin_selected_domain.'\')' ) );
				
				$desc1 = html::span('rules_opt_desc', 'Primary Domain for This Account is ' );
				$domain = html::span( array( 'style' => 'font-weight:bold' ), $RC_HELP->admin_selected_domain );
				
				$blocks['main']['options']['desc1'] = array(
					'content' => $desc1.$domain."	".$edit_submit->show()
				);
				
				$input_add_domain_alias_name = new html_inputfield( array( 'name' => '_add_domain_alias_name', 'id' => '_add_domain_alias_name', 'size' => 20, 'value' => '' ) );
				
				$submit_button = new html_inputfield( array( 'type' => 'submit', 'value' => 'Add', 'class' => 'button mainaction manage_user_edt_del', 'name' => 'manage_domain_alias_flg' ) );
				
				$blocks['main']['options']['add_domain_name'] = array(
					'title' => 'New Domain Alias',
					'content' => $input_add_domain_alias_name->show()."	".$submit_button->show()
				);
				
				$hidden_domain_name = new html_hiddenfield( array( 'name' => '_hidden_primary_domain_name', 'value' => '', 'id' => '_hidden_primary_domain_name' ) );
				
				$blocks['main']['options']['add_hidden_primary_domain_name'] = array( 
					'content' => $hidden_domain_name->show()
				);
				
				break;
		
			case 'users':
			// MANAGE USERS
			$u_name = ( $RC_HELP->manage_add_ok ) ? ( "" ) : ( $_POST[ '_add_user_name' ] );
			$u_email = ( $RC_HELP->manage_add_ok ) ? ( "" ) : ( $_POST[ '_add_user_email_nm' ] );
			$u_pwd = ( $RC_HELP->manage_add_ok ) ? ( "" ) : ( $_POST[ '_add_user_pwd' ] );
			$u_priv = $_POST[ '_add_user_priviledged' ];
			$u_quota = ( $RC_HELP->manage_add_ok ) ? ( "1GB" ) : ( ( isset( $_POST[ '_add_user_quota' ] ) ) ? ( $_POST[ '_add_user_quota' ] ) : ( "1GB" ) );
			$blocks = array(
			  'main'    => array('name' => rcube::Q($RCMAIL->gettext('manageacc'))),
			  'manageshowusers'    => array('name' => rcube::Q($RCMAIL->gettext('manageshowusers')))
			);
			
			// txt box
			$field_id = 'rcmfd_user_name';
			$input_user_name = new html_inputfield( array( 'name' => '_add_user_name', 'id' => $field_id, 'size' => 20, 'value' => $u_name ) );
			
			$blocks['main']['options']['add_user_name'] = array(
				'title' => html::label($field_id, rcube::Q($RCMAIL->gettext('add_uname_title'))),
				'content' => $input_user_name->show(),
			);
			
			$field_id = 'rcmfd_user_email_nm';
			$input_user_email_nm = new html_inputfield( array( 'name' => '_add_user_email_nm', 'id' => $field_id, 'size' => 20, 'value' => $u_email ) );
			
			$field_id = 'rcmfd_user_email_dom';
			$input_user_email_dom = new html_inputfield( array( 'name' => '_add_user_email_dom', 'id' => $field_id, 'size' => 20 ) );
			
			$blocks['main']['options']['add_user_email'] = array(
				'title' => html::label($field_id, rcube::Q($RCMAIL->gettext('add_uemail_title'))),
				'content' => $input_user_email_nm->show().html::label('at_sym', '@').$input_user_email_dom->show( $RC_HELP->admin_selected_domain, array( "readonly" => "true", "style" => "background:#F0F0F0;" ) ),
			); // displays, label: Email, txt box for email name, @ symbol, readonly txtbox with domain
			
			$field_id = '_add_user_pwd';
			$input_user_pwd = new html_inputfield( array( 'name' => '_add_user_pwd', 'id' => $field_id, 'size' => 20, 'value' => "", 'autocomplete' => 'off') );
			
			$field_id = '_add_user_pwd_mask';
			$check_user_pwd_mask = new html_checkbox( array( 'name' => '_add_user_pwd_mask', 'id' => $field_id, 'value' => 1, 'onclick' => 'UI.toggle_pwd( \'_add_user_pwd_mask\', \'_add_user_pwd\' )' ) );
			$mask_title =  html::label($field_id, 'Mask');
			
			$blocks['main']['options']['add_user_pwd'] = array(
				'title' => html::label('rcmfd_user_pwd', rcube::Q($RCMAIL->gettext('add_upwd_title'))),
				'content' => $input_user_pwd->show().$check_user_pwd_mask->show().$mask_title,
			);
			
			$field_id = 'rcmfd_user_priviliged';
			$input_user_priviledged = new html_checkbox( array( 'name' => '_add_user_priviledged', 'id' => $field_id, 'value' => 1 ) );
			if( $RC_HELP->manage_add_ok ) // when user successfully added
			{
				$blocks['main']['options']['add_user_priviledged'] = array(
					'title' => html::label($field_id, rcube::Q($RCMAIL->gettext('add_upriviledge_title'))),
					'content' => $input_user_priviledged->show(),
				);
			}
			else if( $u_priv == '1' ) // if post parameter checked
			{
				$blocks['main']['options']['add_user_priviledged'] = array(
					'title' => html::label($field_id, rcube::Q($RCMAIL->gettext('add_upriviledge_title'))),
					'content' => $input_user_priviledged->show( 1, array( 'checked' => 'true' ) ),
				);
			}
			else // if post parameter not checked
			{
				$blocks['main']['options']['add_user_priviledged'] = array(
					'title' => html::label($field_id, rcube::Q($RCMAIL->gettext('add_upriviledge_title'))),
					'content' => $input_user_priviledged->show(),
				);
			}
			
			$field_id = 'rcmfd_user_quota';
			$select_user_quota = new html_select( array( 'name' => '_add_user_quota', 'id' => $field_id, 'value' => 1 ) );
			
			foreach( $RC_HELP->quota_array as $key => $value )
			{
				$select_user_quota->add( $value, $key );
			}	
			
			$blocks['main']['options']['add_user_quota'] = array(
				'title' => html::label($field_id, rcube::Q($RCMAIL->gettext('add_uquota_title'))),
				'content' => $select_user_quota->show( $u_quota ),
			);
			
			$submit_button = new html_inputfield( array( 'type' => 'submit', 'value' => 'Add', 'class' => 'button mainaction manage_user_edt_del', 'name' => 'manage_user_flg' ) );
			
			$blocks['main']['options']['add_user_submit'] = array(
				'content' => $submit_button->show(),
			);
			
			break;
			
			// distribution  list
			case 'groups':
			$l_name = ( $RC_HELP->manage_add_ok ) ? ( "" ) : ( $_POST[ '_add_list_name' ] );
			$l_domain = ( $RC_HELP->manage_add_ok ) ? ( "" ) : ( $_POST[ '_add_list_dom' ] );
			/* $l_priv = ( $RC_HELP->manage_add_ok ) ? ( "1" ) : ( ( ( $_POST[ 'manage_list_flg' ] == 'Add' ) && ( !( isset( $_POST[ '_add_list_private' ] ) ) ) ) ? ( "0" ) : ( "1" ) ); */
			$l_priv = ( $RC_HELP->manage_add_ok ) ? ( "0" ) : ( ( ( $_POST[ 'manage_list_flg' ] == 'Add' ) && ( ( isset( $_POST[ '_add_list_private' ] ) ) ) ) ? ( "1" ) : ( "0" ) );
			
			$blocks = array(
			  'main'    => array('name' => rcube::Q($RCMAIL->gettext('managedl')))
			);
			
			$field_id = 'rcmfd_list_name';
			$field_name = '_add_list_name';
			$input_list_nm = new html_inputfield( array( 'name' => $field_name, 'id' => $field_id, 'size' => 20, 'value' => $l_name ) );
			
			$user_domain_aliases = $RC_HELP->get_domain_aliases( $RC_HELP->admin_selected_domain );
			
			$field_id = 'rcmfd_list_dom';
			$select_dom_aliases = new html_select( array( 'name' => '_add_list_dom', 'id' => $field_id , 'value' =>$l_domain ) );
			
			foreach( $user_domain_aliases[ 'alias_domain' ] as $value )
			{
				$select_dom_aliases->add($value, $value);
			}	
				
			$blocks['main']['options']['add_list_name'] = array(
				'title' => html::label($field_id, rcube::Q($RCMAIL->gettext('add_listname_title'))),
				'content' => $input_list_nm->show().html::label('at_sym', '@').$select_dom_aliases->show($l_domain)
			);	
			
			$field_id = 'rcmfd_list_private';
			$input_list_private = new html_checkbox( array( 'name' => '_add_list_private' , 'id' => $field_id, 'value' => '1' ) );
			
			if( $l_priv == '0' )
			{
				$blocks['main']['options']['add_list_private'] = array(
					'title' => html::label($field_id, rcube::Q($RCMAIL->gettext('add_private_list'))),
					'content' => $input_list_private->show(),
				);
			}
			else
			{
				$blocks['main']['options']['add_list_private'] = array(
					'title' => html::label($field_id, rcube::Q($RCMAIL->gettext('add_private_list'))),
					'content' => $input_list_private->show( 1, array( 'checked' => 'true' ) ),
				);
			}
			
			$submit_button = new html_inputfield( array( 'type' => 'submit', 'value' => 'Add', 'class' => 'button mainaction manage_user_edt_del', 'name' => 'manage_list_flg' ) );
			
			$blocks['main']['options']['add_user_submit'] = array(
				'content' => $submit_button->show(),
			);
			
			break;
			
			case 'aliases':
				$alias_name = ( $RC_HELP->manage_add_ok ) ? ( "" ) : ( $_POST[ '_add_alias_nm' ] );
				$alias_dom = ( $RC_HELP->manage_add_ok ) ? ( "" ) : ( $_POST[ '_add_alias_dom' ] );
				$orig_email_name = ( $RC_HELP->manage_add_ok ) ? ( "" ) : ( $_POST[ '_add_alias_orig_nm' ] );
				
				$all_user_details = $RC_HELP->get_user_details( $RC_HELP->admin_selected_domain );
				$user_emails = $all_user_details[ 'user_email' ];
				
				$domain_aliases = $RC_HELP->get_domain_aliases( $RC_HELP->admin_selected_domain );
				
				$blocks = array(
					'main'    => array('name' => rcube::Q($RCMAIL->gettext('managealias')))
				);
				
				$input_add_alias = new html_inputfield( array( 'name' => '_add_alias_nm', 'id' => '_add_alias_nm', 'size' => 20, 'value' => $alias_name ) );
				
				$select_add_alias_domain = new html_select( array( 'name' => '_add_alias_dom', 'id' => '_add_alias_dom' ) );
			
				foreach( $domain_aliases[ 'alias_domain' ] as $value )
					$select_add_alias_domain->add($value, $value);
				
				$blocks['main']['options']['_add_alias_email'] = array(
					'title' => 'Alias',
					'content' => $input_add_alias->show().'@'.$select_add_alias_domain->show($alias_dom),
				);
				
				if( count( $user_emails ) > 0 )
				{
					$select_org_email = new html_select( array( 'name' => '_add_alias_orig_nm', 'id' => '_add_alias_orig_nm' ) );
					
					for( $i = 0; $i < count( $user_emails ); $i++ )
					{
						$email_split_arr = explode( '@', $user_emails[ $i ] );
						$user_name = $email_split_arr[ 0 ];
						$select_org_email->add( $user_name, $user_name );
					}
					
					$input_org_domain = new html_inputfield( array( 'name' => '_add_alias_orig_dom', 'id' => '_add_alias_orig_dom', 'size' => 20 ) );
				
					$blocks['main']['options']['_add_original_email'] = array(
						'title' => 'Original',
						'content' => $select_org_email->show( $orig_email_name ).'@'.$input_org_domain->show( $RC_HELP->admin_selected_domain, array( "readonly" => "true", "style" => "background:#F0F0F0;" ) ),
					);
				}
				else
				{
					$no_users = html::label( 'no_user', 'There are No Users that belong to this Domain' );
					$blocks['main']['options']['_add_original_email'] = array(
						'title' => 'Original',
						'content' => $no_users
					);
				}
				
				$add_button = new html_inputfield( array( 'type' => 'submit', 'value' => 'Add', 'class' => 'button mainaction manage_user_edt_del', 'name' => 'manage_aliases_flg' ) );
			
				$blocks['main']['options']['_add_alias_submit'] = array(
					'content' => $add_button->show(),
				);
				
				break;
				
			case 'user_rules':
				$rules_array = array( 'oof' => 'Out Of Office', 'block_email' => 'Block Emails', 'fwd_rule' => 'Forward Rule', 'custom_directive' => 'Custom Directive' );
				
				$user_email = $_POST[ '_add_user_rule_email' ];
				$rule = $_POST[ '_add_user_rule_name' ];
				$rule_name = $rules_array[ $rule ];
				$blocks = array(
					'main'    => array('name' => rcube::Q($RCMAIL->gettext('manageRules'))),
					'sect' => array( 'name' => 'Manage '.$rule_name.' for '.$user_email),
				);
				
				$all_users = $RC_HELP->get_user_details( $RC_HELP->admin_selected_domain );
				
				if( count( $all_users[ 'user_email' ] ) > 0 )
				{
					$select_all_users = new html_select( array( 'name' => '_add_user_rule_email', 'id' => '_add_user_rule_email', 'value' => 1 ) );
					
					for( $i = 0; $i < count( $all_users[ 'user_email' ] ); $i++ )
					{
						$select_all_users->add( $all_users[ 'user_email' ][ $i ], $all_users[ 'user_email' ][ $i ] );
					}
					
					$blocks['main']['options']['_add_user_rule_email'] = array(
						'title' => 'User Email',
						'content' => $select_all_users->show( $user_email ),
					);
					
					$select_rules = new html_select( array( 'name' => '_add_user_rule_name', 'id' => '_add_user_rule_name', 'value' => 1 ) );
					
					foreach( $rules_array as $key => $value )
					{
						$select_rules->add( $value, $key );
					}
					
					$blocks['main']['options']['_add_user_rule_name'] = array(
						'title' => 'User Rule',
						'content' => $select_rules->show( $rule ),
					);
					
					$show_btn = new html_inputfield( array( 'type' => 'submit', 'value' => 'Show Rules', 'class' => 'button mainaction manage_user_edt_del', 'name' => 'manage_user_rules_flg' ) );
					
					$blocks['main']['options']['show_user_rules_det'] = array(
						'content' => $show_btn->show(),
					);
					
					// for displaying the rules block
					if( ( isset( $_POST[ 'manage_user_rules_flg' ] ) ) || (  isset( $_POST[ 'manage_user_rules_save_flg' ] ) ) ) // if "show rules" or "save" button is clicked
					{
						if( isset( $_POST[ 'manage_user_rules_flg' ] ) ) // if show rules button is clicked, unset all post param of rules, so that, db values will be displayed
						{
							switch( $rule )
							{
								case 'oof':
									unset( $_POST[ '_out_of_office_enable' ] );
									unset( $_POST[ '_out_of_office_sub' ] );
									unset( $_POST[ '_out_of_office_message' ] );
									break;
								case 'block_email':
									unset( $_POST[ '_block_email_header' ] );
									unset( $_POST[ '_block_email_filter' ] );
									break;
								case 'fwd_rule':
									unset( $_POST[ '_forward_rule_header' ] );
									unset( $_POST[ '_forward_rule_filter' ] );
									unset( $_POST[ '_forward_rule_email' ] );
									break;
								case 'custom_directive':
									unset( $_POST[ '_custom_rule_enable' ] );
									unset( $_POST[ '_custom_rule_desc' ] );
									break;
							}
						}
						
						switch( $rule )
						{
							case 'oof':
								
								// first add button
								$blocks['sect']['options']['oof_add'] = $RC_HELP->display_oof_add_button();
								
								$OOF_details = $RC_HELP->get_OutOfOffice_details( $user_email );
								
								if( count( $OOF_details[ 'enabled' ] ) > 0 ) // if there are entries in db for this email
								{
									$enabled = $OOF_details[ 'enabled' ];
									$subject = $OOF_details[ 'subject' ];
									$message = $OOF_details[ 'message' ];
									$header = $OOF_details[ 'header' ];
									$filter = $OOF_details[ 'filter' ];
								}
								else // set default values
								{
									$enabled = $subject = $message = $header = $filter = array();
								}
								
								if( isset( $_POST[ 'manage_user_rules_save_flg' ] ) )
								{
									$enabled = $_POST[ 'hidden_oof_enabled' ];
									$subject = $_POST[ 'oof_subject' ];
									$message = $_POST[ 'oof_message' ];
									$header = $_POST[ 'hidden_oof_header' ];
									$filter = $_POST[ 'oof_header_match' ];
								}
								
								if( count( $enabled ) > 0 )
								{
									for( $i = 0; $i < count( $enabled ); $i++ )
									{
										$blocks['sect']['options']['oof_enable'.$i] = $RC_HELP->display_oof_enable( $enabled[ $i ] );
										$blocks['sect']['options']['oof_header'.$i] = $RC_HELP->display_oof_header( $header[ $i ], $enabled[ $i ] );
										$blocks['sect']['options']['oof_filter'.$i] = $RC_HELP->display_oof_filter( $filter[ $i ], $header[ $i ], $enabled[ $i ] );
										$blocks['sect']['options']['oof_subject'.$i] = $RC_HELP->display_oof_subject( $subject[ $i ], $enabled[ $i ] );
										$blocks['sect']['options']['oof_message'.$i] = $RC_HELP->display_oof_message( $message[ $i ], $enabled[ $i ] );
										$blocks['sect']['options']['oof_remove_btn'.$i] = $RC_HELP->display_oof_remove_btn();
										
										$blocks['sect']['options']['oof_hidden_header'.$i] = $RC_HELP->display_oof_hidden_field( $header[ $i ], 'hidden_oof_header', 'hidden_oof_header[]' );
										
										$blocks['sect']['options']['oof_hidden_enabled'.$i] = $RC_HELP->display_oof_hidden_field( $enabled[ $i ], 'hidden_oof_enabled', 'hidden_oof_enabled[]' );
									}
								}
								
								break;
								
							
							case 'block_email':
								$header_label = array( 'from', 'sub' );
								$block_email_details = $RC_HELP->get_BlockEmail_details( $user_email );
								$db_block_headers = $block_email_details[ 'header' ];
								$db_block_filters = $block_email_details[ 'filter' ];
								$blocks['sect']['options']['block_email_add'] = $RC_HELP->display_block_email_add_btn();
								
								if( isset( $_POST[ 'manage_user_rules_save_flg' ] ) ) // if save button is clicked
								{
									$block_email_count = count( $_POST[ '_block_email_header' ] );
									$block_email_header = $_POST[ '_block_email_header' ];
									$block_email_filter = $_POST[ '_block_email_filter' ];
								}
								else if( count( $db_block_headers ) > 0 ) // from database
								{
									$block_email_count = count( $db_block_headers );
									$block_email_header = $db_block_headers;
									$block_email_filter = $db_block_filters;
								}
								
								for( $i = 0; $i < $block_email_count; $i++ )
								{
									$blocks['sect']['options']['header'.$i] = $RC_HELP->display_block_email_header( $header_label, $i, $block_email_header );
									$blocks['sect']['options']['filter'.$i] = $RC_HELP->display_block_email_filter( $i, $block_email_filter );
									$blocks['sect']['options']['add'.$i] = $RC_HELP->display_block_email_remove( $i );
								}
								
								break;
								
							case 'fwd_rule':
								$header_label = array( 'all', 'from', 'sub', 'to', 'cc' );
								$fwd_rule_arr = $RC_HELP->get_FwdRule_details( $user_email );
								
								$db_fwd_rule_header = $fwd_rule_arr[ 'header' ];
								$db_fwd_rule_filter = $fwd_rule_arr[ 'filter' ];
								$db_fwd_to_email = $fwd_rule_arr[ 'forward_to_email' ];
								
								if( isset( $_POST[ 'manage_user_rules_save_flg' ] ) ) // if save button is clicked
								{
									$count = count( $_POST[ '_forward_rule_header' ] );
									$fwd_rule_header = $_POST[ '_forward_rule_header' ];
									$fwd_rule_filter = $_POST[ '_forward_rule_filter' ];
									$fwd_rule_email = $_POST[ '_forward_rule_email' ];
								}
								else if( count( $db_fwd_rule_header ) > 0 )
								{
									$count = count( $db_fwd_rule_header );
									$fwd_rule_header = $db_fwd_rule_header;
									$fwd_rule_filter = $db_fwd_rule_filter;
									$fwd_rule_email = $db_fwd_to_email;
								}
								
								$blocks['sect']['options']['add'] = $RC_HELP->display_fwd_rule_add_btn();
								
								for( $i = 0; $i < $count; $i++ )
								{
									$blocks['sect']['options']['header'.$i] = $RC_HELP->display_fwd_rule_header( $header_label, $fwd_rule_header, $i );
									$blocks['sect']['options']['filter'.$i] = $RC_HELP->display_fwd_rule_filter( $fwd_rule_header, $fwd_rule_filter, $i );
									$blocks['sect']['options']['email'.$i] = $RC_HELP->display_fwd_rule_email( $fwd_rule_email, $i );
									$blocks['sect']['options']['add'.$i] = $RC_HELP->display_fwd_rule_remove_btn( $i );
								}
								
								break;
								
							case 'custom_directive':
								$custom_rule_details = $RC_HELP->get_CustomRule_details( $user_email );
								
								if( empty( $custom_rule_details ) )
								{
									$custom_directive_enabled = '0';
									$custom_directive_desc = "";
								}
								else
								{
									$custom_directive_enabled = $custom_rule_details[ 'enabled' ];
									$custom_directive_desc =  $custom_rule_details[ 'description' ];
								}
								
								$blocks['sect']['options']['field_id'] = $RC_HELP->display_custom_rule_enable( $custom_directive_enabled );
								$blocks['sect']['options']['message'] = $RC_HELP->display_custom_rule_message( $custom_directive_desc, $custom_directive_enabled );
								break;
						}
						
						$submit_btn = new html_inputfield( array( 'type' => 'submit', 'value' => 'Save', 'class' => 'button mainaction manage_user_edt_del', 'name' => 'manage_user_rules_save_flg' ) );
				
						$blocks['sect']['options']['submit'] = array(
							'content' => $submit_btn->show()
						);
					}
				}
				else
				{
					$blocks['main']['options']['desc'] = $RC_HELP->display_description( 'There are Currently No Users to Display' );
				}
				
				break;
			
		} // end of switch
		$data = $RCMAIL->plugins->exec_hook('manage_list', array('section' => $sect['id'], 'blocks' => $blocks, 'current' => $current));
		$found = false;
		$advanced_prefs = (array) $RCMAIL->config->get('advanced_prefs');
		
		// create output
		foreach ($data['blocks'] as $block) {
		  if (!empty($block['content']) || !empty($block['options'])) {
			$found = true;
		  }
		  // move some options to the 'advanced' block as configured by admin
            if ($key != 'advanced') {
                foreach ($advanced_prefs as $opt) {
                    if ($block['options'][$opt]) {
                        $data['blocks']['advanced']['options'][$opt] = $block['options'][$opt];
                        unset($data['blocks'][$key]['options'][$opt]);
                    }
                }
            }
		} // foreach 2 end
		
		// move 'advanced' block to the end of the list
        if (!empty($data['blocks']['advanced'])) {
            $adv = $data['blocks']['advanced'];
            unset($data['blocks']['advanced']);
            $data['blocks']['advanced'] = $adv;
        }
		
		if (!$found)
		  unset($sections[$idx]);
		else
		  $sections[$idx]['blocks'] = $data['blocks'];
	} // foreach end
	
	$data = $RCMAIL->plugins->exec_hook('superadmin_section_header',
        array('section' => $sect['id'], 'header' => '', 'current' => $current));
		
	if(!empty($data['header'])) {
        $sections[$idx]['header'] = $data['header'];
    }
	
	return array($sections, $plugin['cols']);
}

/**
 * Macgregor Changes
 * Manage users tab
**/
function rcmail_manage_users( $current = null )
{
	global $RCMAIL , $OUTPUT, $RC_HELP;
	
	$sections['domain_aliases'] = array('id' => 'domain_aliases', 'manage' => $RCMAIL->gettext('managedomain_aliases'));
	$sections['users'] = array('id' => 'users', 'manage' => $RCMAIL->gettext('manageusers'));
	$sections['groups'] = array('id' => 'groups', 'manage' => $RCMAIL->gettext('manageGroups'));
	$sections['aliases'] = array('id' => 'aliases', 'manage' => $RCMAIL->gettext('manageAliases'));
		
	// hook + define list cols
	$plugin = $RCMAIL->plugins->exec_hook('users_sections_list',
		array('list' => $sections, 'cols' => array('manage')));
	$sections = $plugin['list'];
	
	foreach ($sections as $idx => $sect) {

		if ($current && $sect['id'] != $current) {
			continue;
		}
		
		$blocks = array();
		
		switch ($sect['id']) {
			
			case 'domain_aliases':
				$blocks = array(
					'main' => array( 'name' => 'Current Domain Aliases' )
				);
				
				$delete_button = new html_inputfield( array( 'type' => 'submit', 'value' => 'Delete', 'class' => 'button mainaction manage_user_edt_del', 'onclick' => 'return UI.delete_domain_alias_confirm()', 'name' => 'manage_domain_alias_flg' ) );
				
				$blocks['main']['options']['delete_submit'] = array( 
					'content' => $delete_button->show()
				);
				
				$all_domain_aliases = $RC_HELP->get_domain_aliases( $RC_HELP->admin_selected_domain, false );
				
				$total_domain_aliases = count( $all_domain_aliases[ 'alias_domain' ] );
				
				// $total_domain_aliases = 3;
				
				$tbl_domain_aliases = new html_table( array( 'cols' => 4) );
				$tbl_domain_aliases->add_header( 'alias_name', 'Alias Name' );
				$tbl_domain_aliases->add_header( 'domain_status', 'Status' );
				$tbl_domain_aliases->add_header( 'edit', '' );
				$tbl_domain_aliases->add_header( 'delete', '' );
				
				if( $total_domain_aliases > 0 )
				{
					for( $i = 0; $i < $total_domain_aliases; $i++ )
					{
						$tbl_domain_aliases->add( 'domain_alias_name', $all_domain_aliases[ 'alias_domain' ][ $i ] );
						// $tbl_domain_aliases->add( 'domain_alias_status', $RC_HELP->domain_status[ $all_domain_aliases[ 'domain_status' ][ $i ] ] );
						$tbl_domain_aliases->add( 'domain_alias_status', 'Active' );
						
						$submit_edit = new html_inputfield( array( 'type' => 'submit', 'value' => 'Edit', 'class' => 'manage_user_edt_del', 'name' => 'manage_domain_alias_flg', 'onclick' => 'UI.show_domain_details(\'alias\',\''.$all_domain_aliases[ 'alias_domain' ][ $i ].'\')') );
						$tbl_domain_aliases->add( 'edit', $submit_edit->show() );
						
						$domain_delete = new html_checkbox( array( 'name' => '_delete_domain_alias_name[]', 'id' => '_delete_domain_alias_name', 'onclick' => 'UI.delete_domain_alias( this, \''.$all_domain_aliases[ 'alias_domain' ][ $i ].'\' )', 'value' => 1 ) );
						$tbl_domain_aliases->add( 'delete', $domain_delete->show() );
					}
				}
				else
				{
					$tbl_domain_aliases->add( array( 'colspan' => 4 ), 'There are no Domain Aliases to Display' );
				}
				
				$blocks['main']['options']['all_domain_aliases'] = array( 
					'content' => $tbl_domain_aliases->show()
				);
				
				$hidden_domain_name = new html_hiddenfield( array( 'name' => '_hidden_domain_name', 'value' => '', 'id' => '_hidden_domain_name' ) );
				
				$blocks['main']['options']['add_hidden_domain_name'] = array( 
					'content' => $hidden_domain_name->show()
				);
				
				break;
			
			case 'users':
			// MANAGE USERS
			$blocks = array(
			  'main'    => array('name' => Q(rcube_label('manageacc'))),
			  'manageshowusers'    => array('name' => rcube::Q($RCMAIL->gettext('manageshowusers')))
			);
			
			$all_users = $RC_HELP->get_user_details( $RC_HELP->admin_selected_domain );
			$total_users = $RC_HELP->get_user_count( $RC_HELP->admin_selected_domain );
			
			$delete_button = new html_inputfield( array( 'type' => 'submit', 'value' => 'Delete', 'class' => 'button mainaction manage_user_edt_del', 'onclick' => "$('#_hidden_flag').val( 'Delete' );") );
			
			$blocks['manageshowusers']['options']['delete_user_button'] = array(
				'content' => $delete_button->show(),
			);
			
			$field_id = 'rcmfd_user_det_tbl';
			$tbl_user_name = new html_table( array( 'cols' => 4) );
			$tbl_user_name->add_header( 'name', 'Name' );
			$tbl_user_name->add_header( 'email', 'Email' );
			$tbl_user_name->add_header( 'edt_btn', '' );
			$tbl_user_name->add_header( 'delete_chk', '' );
			if( $total_users > 0 )
			{
				for( $i = 0; $i < $total_users; $i++ )
				{
					
					// if( isset( $_POST['del_email'] ) )
					if( $RC_HELP->manage_add_ok ) // shows form on successfully deleting user
					{
						$user_delete = new html_checkbox( array( 'name' => 'manage_user_edt_del[]', 'id' => 'manage_user_edt_del', 'onclick' => 'UI.delete_users( this, \''.$all_users[ 'user_email' ][ $i ].'\' )', 'value' => 1 ) );
					}
					else if( !( empty( $_POST['del_email'] ) ) )
					{
						if( in_array( $all_users[ 'user_email' ][ $i ], $_POST['del_email'] ) )
						{
							$user_delete = new html_checkbox( array( 'name' => 'manage_user_edt_del[]', 'id' => 'manage_user_edt_del', 'onclick' => 'UI.delete_users( this, \''.$all_users[ 'user_email' ][ $i ].'\' )', 'checked' => 'true' ) );
						}
						else
						{
							$user_delete = new html_checkbox( array( 'name' => 'manage_user_edt_del[]', 'id' => 'manage_user_edt_del', 'onclick' => 'UI.delete_users( this, \''.$all_users[ 'user_email' ][ $i ].'\' )', 'value' => 1 ) );
						}
					}
					else
					{
						$user_delete = new html_checkbox( array( 'name' => 'manage_user_edt_del[]', 'id' => 'manage_user_edt_del', 'onclick' => 'UI.delete_users( this, \''.$all_users[ 'user_email' ][ $i ].'\' )', 'value' => 1 ) );
					}
					$user_edit = new html_inputfield( array( 'type' => 'submit', 'value' => 'Edit', 'class' => 'manage_user_edt_del', 'onclick' => 'UI.show_user_detail(\''.$all_users[ 'user_email' ][ $i ].'\');') );
					
					$tbl_user_name->add_row();
					$tbl_user_name->add( 'name', $all_users["user_name"][$i] );
					$tbl_user_name->add( 'email', $all_users["user_email"][$i] );
					$tbl_user_name->add( 'add_user', $user_edit->show() );
					$tbl_user_name->add( 'delete_user', $user_delete->show() );
				}
			}
			else
			{
				$tbl_user_name->add_row();
				$tbl_user_name->add( array( 'colspan' => '4' ), 'There are No Users Added To Display' );
			}
			
			$blocks['manageshowusers']['options']['add_user_name'] = array(
				'content' => $tbl_user_name->show(),
			);
			
			if( $RC_HELP->manage_add_ok )
			{
				$hidden_email = new html_hiddenfield( array( 'name' => '_email_id', 'value' => '', 'id' => '_hidden_email' ) );
			}
			else if( !( empty( $_POST['del_email'] ) ) )
			{
				$email_str = implode( "|", $_POST['del_email'] );
				$hidden_email = new html_hiddenfield( array( 'name' => '_email_id', 'value' => $email_str, 'id' => '_hidden_email' ) );
			}
			else
			{
				$hidden_email = new html_hiddenfield( array( 'name' => '_email_id', 'value' => '', 'id' => '_hidden_email' ) );
			}
			$hidden_flag = new html_hiddenfield( array( 'name' => 'manage_user_flg', 'value' => '', 'id' => '_hidden_flag' ) );
			
			
			$blocks['manageshowusers']['options']['add_hidden_email'] = array(
				'content' => $hidden_email->show().$hidden_flag->show(),
			);
			
			break;
			
			case 'groups':
			$list_det = $RC_HELP->get_list_details( $RC_HELP->admin_selected_domain );
			$total_list = $RC_HELP->get_list_count( $RC_HELP->admin_selected_domain );
			$blocks = array(
			  'main'    => array('name' => 'Current Lists'),
			);
			
			$delete_button = new html_inputfield( array( 'type' => 'submit', 'onclick' => 'return UI.show_delte_list_confirm();', 'value' => 'Delete List', 'class' => 'button mainaction manage_user_edt_del', 'name' => 'manage_list_flg') );
			
			$blocks['main']['options']['delete_list_button'] = array(
				'content' => $delete_button->show(),
			);
			/*
			code for htaccess
			Order allow,deny
			Deny from all
			*/
			$field_id = 'rcmfd_user_det_tbl';
			$tbl_d_list = new html_table( array( 'cols' => 3) );
			$tbl_d_list->add_header( 'email', 'Email' );
			$tbl_d_list->add_header( 'edit_user', '' );
			$tbl_d_list->add_header( 'delete_user', '' );
			
			if( $total_list > 0 )
			{
				for( $i = 0; $i < $total_list; $i++ )
				{
					$list_delete = new html_checkbox( array( 'name' => 'manage_list_edt_del[]', 'id' => 'manage_list_edt_del', 'onclick' => 'UI.delete_list( this, \''.$list_det[ 'email_list' ][ $i ].'\' )', 'value' => 1 ) );
				
					$list_edit = new html_inputfield( array( 'type' => 'submit', 'value' => 'Edit', 'class' => 'manage_user_edt_del', 'onclick' => 'UI.show_list_detail(\''.$list_det[ 'email_list' ][ $i ].'\');', 'name' => 'manage_list_flg') );
					$tbl_d_list->add_row();
					$tbl_d_list->add( 'list', $list_det[ 'email_list' ][ $i ] );
					$tbl_d_list->add( 'updt_list', $list_edit->show() );
					$tbl_d_list->add( 'del_list', $list_delete->show() );
				}
			}
			else
			{
				$tbl_d_list->add_row();
				$tbl_d_list->add(  array( 'colspan' => '3' ) , 'There Are Currenty No Distribution Lists' );
			}
			$blocks['main']['options']['modify_list'] = array(
				'content' => $tbl_d_list->show(),
			);
			$hidden_list = new html_hiddenfield( array( 'name' => '_hidden_edit_list', 'value' => '', 'id' => '_hidden_edit_list' ) );
			
			$blocks['main']['options']['add_user_hidden_email'] = array(
				'content' => $hidden_list->show(),
			);
			break;
			
			case 'aliases':
				
				$blocks = array(
					'main'    => array('name' => rcube::Q($RCMAIL->gettext('manage_current_alias')))
				);
				
				$delete_button = new html_inputfield( array( 'type' => 'submit', 'value' => 'Delete', 'class' => 'button mainaction manage_user_edt_del', 'name' => 'manage_aliases_flg', 'onclick' => 'return UI.confirm_delte_alias()' ) );
				
				$blocks['main']['options']['_delete_user_alias'] = array(
					'content' => $delete_button->show(),
				);
				
				$user_aliases = $RC_HELP->get_user_aliases( $RC_HELP->admin_selected_domain );
				$alias_count = $RC_HELP->get_aliases_count( $RC_HELP->admin_selected_domain );
				
				$tbl_user_alias = new html_table( array( 'cols' => 3 ) );
				$tbl_user_alias->add_header( 'user_alias', 'Alias' );
				$tbl_user_alias->add_header( 'orig_email', 'Original Email' );
				$tbl_user_alias->add_header( 'delete_alias', 'Delete' );
				
				if( $alias_count > 0 )
				{
					for( $i = 0; $i < $alias_count; $i++ )
					{
						$chk_delete_alias = new html_checkbox( array( 'name' => '_delete_alias' , 'id' => '_delete_alias', 'value' => '1', 'onclick' => 'UI.delete_user_alias( this, "'.$user_aliases[ 'email_src' ][ $i ].'", "'.$user_aliases[ 'email_dest' ][ $i ].'" );' ) );
						$tbl_user_alias->add( 'alias', $user_aliases[ 'email_src' ][ $i ] );
						$tbl_user_alias->add( 'orig_email', $user_aliases[ 'email_dest' ][ $i ] );
						$tbl_user_alias->add( 'delete_chkbox', $chk_delete_alias->show() );
					}
				}
				else
				{
					$tbl_user_alias->add( array( 'colspan' => '3' ), 'There Are Currently No Aliases' );
				}
				
				$blocks['main']['options']['_user_alias'] = array(
					'content' => $tbl_user_alias->show(),
				);
				
				$alias_src_hidden_field = new html_hiddenfield( array( 'name' => '_hidden_user_aliases_src', 'value' => '', 'id' => '_hidden_user_aliases_src' ) );
				
				$alias_dest_hidden_field = new html_hiddenfield( array( 'name' => '_hidden_user_aliases_dest', 'value' => '', 'id' => '_hidden_user_aliases_dest' ) );
				
				$blocks['main']['options']['_hidden_user_aliases'] = array(
					'content' => $alias_src_hidden_field->show().$alias_dest_hidden_field->show(),
				);
				
				break;
			
			} // switch end
		
		$data = $RCMAIL->plugins->exec_hook('manage_list', array('section' => $sect['id'], 'blocks' => $blocks, 'current' => $current));
		$found = false;
		$advanced_prefs = (array) $RCMAIL->config->get('advanced_prefs');
		
		// create output
		foreach ($data['blocks'] as $block) {
		  if (!empty($block['content']) || !empty($block['options'])) {
			$found = true;
			break;
		  }
		  // move some options to the 'advanced' block as configured by admin
            if ($key != 'advanced') {
                foreach ($advanced_prefs as $opt) {
                    if ($block['options'][$opt]) {
                        $data['blocks']['advanced']['options'][$opt] = $block['options'][$opt];
                        unset($data['blocks'][$key]['options'][$opt]);
                    }
                }
            }
		} // foreach 2 end
		
		// move 'advanced' block to the end of the list
        if (!empty($data['blocks']['advanced'])) {
            $adv = $data['blocks']['advanced'];
            unset($data['blocks']['advanced']);
            $data['blocks']['advanced'] = $adv;
        }
		
		if (!$found)
		  unset($sections[$idx]);
		else
		  $sections[$idx]['blocks'] = $data['blocks'];
	} // foreach end
	
	 $data = $RCMAIL->plugins->exec_hook('user_section_header',
        array('section' => $sect['id'], 'header' => '', 'current' => $current));
		
	if(!empty($data['header'])) {
        $sections[$idx]['header'] = $data['header'];
    }	
		
	return array($sections, $plugin['cols']);
}


/**
 * Macgregor Changes
 * Show menu for the manage users tab
**/
function rcmail_manage_update()
{
	global $RCMAIL , $OUTPUT, $RC_HELP;
	
	$sections['domain_aliases'] = array('id' => 'domain_aliases', 'manage' => $RCMAIL->gettext('managedomain_aliases'));
	$sections['users'] = array('id' => 'users', 'manage' => $RCMAIL->gettext('manageusers'));
	$sections['groups'] = array('id' => 'groups', 'manage' => $RCMAIL->gettext('manageusers'));
	
	if( isset( $_POST[ '_email_id' ] ) )
		$email_id = $_POST[ '_email_id' ];
		
	$user_detail = $RC_HELP->get_user_det_by_email( $email_id );
	$user_name = ( isset( $_POST[ "_update_name" ] ) ) ? ( $_POST[ "_update_name" ] ) : ( $user_detail[ "user_name" ] );
	$user_email = ( isset( $_POST[ "_updt_user_email" ] ) ) ? ( $_POST[ "_updt_user_email" ] ) : ( $user_detail[ "user_email" ] );
	$user_prev = ( ( isset( $_POST[ "_updt_priviledged" ] ) ) || ( $user_detail[ "user_prev" ] ) ) ? ( '1' ) : ( '0' );
	$inbox_quota = ( isset( $_POST[ "_updt_quota" ] ) ) ? ( $_POST[ "_updt_quota" ] ) : ( $user_detail[ "inbox_quota" ] );
		
	// hook + define list cols
	/* $plugin = $RCMAIL->plugins->exec_hook('users_sections_list',
		array('list' => $sections, 'cols' => array('manage')));
	$sections = $plugin['list']; */

	foreach ($sections as $idx => $sect) {

		if ($current && $sect['id'] != $current) {
			continue;
		}
		
		$blocks = array();

		switch ($sect['id']) {
			
			case 'domain_aliases':
				
				if( isset( $_POST[ 'manage_domain_alias_flg' ] ) ) // ie. if add record button is clicked
				{
					$domain_record_count = count( $_POST[ '_add_domain_record_type' ] );
					$domain_record_name = $_POST[ '_add_domain_record_name' ];
					$domain_record_type = $_POST[ '_add_domain_record_type' ];
					$domain_record_value = $_POST[ '_add_domain_record_value' ];
				}
				
				if( ( isset( $_POST[ '_hidden_domain_name' ] ) ) && ( strlen( $_POST[ '_hidden_domain_name' ] ) > 0 ) ) // set domain hidden name from prev form
				{
					$domain_name = $_POST[ '_hidden_domain_name' ];
				}
				else if( ( isset( $_POST[ '_hidden_primary_domain_name' ] ) ) && ( strlen( $_POST[ '_hidden_primary_domain_name' ] ) > 0 ) )
				{
					$domain_name = $_POST[ '_hidden_primary_domain_name' ];
				}
				
				$blocks = array(
					'main'    => array( 'name' => 'Manage Record for '.$domain_name ),
					'dom_rec'    => array( 'name' => 'Current Records' ),
				);
				
				$back_button1 = new html_inputfield( array( 'type' => 'submit', 'value' => 'Back', 'class' => 'button mainaction manage_user_edt_del', 'name' => 'manage_domain_alias_flg' ) );
				
				$blocks['main']['options']['back_btn1'] = array(
					'content' => $back_button1->show()
				);
				
				$add_field =  html::a(array(
					'href' => "#",
					'onclick' => "UI.add_domain_record()",
					'title' => 'Add Record',
					'class' => 'rules_block_email_add'
				),
				rcube::Q($RCMAIL->gettext('outOfOfficeAdd')));
				
				$blocks['main']['options']['domain_record_add'] = array(
					'content' => $add_field
				);
				
				if( !( $RC_HELP->manage_add_ok ) ) // if record added successfully
				{
					for( $i = 0; $i < $domain_record_count; $i++ )
					{
						$domain_record_lbl = html::label(array(
							'for' => '_add_domain_record_name',
						), 'Record:	');
						
						$input_domain_record_name = new html_inputfield( array( 'id' => '_add_domain_record_name', 'name' => '_add_domain_record_name[]', 'size' => 20, 'value' => $domain_record_name[ $i ] ) );
						
						$select_domain_record_type = new html_select( array( 'name' => '_add_domain_record_type[]', 'id' => '_add_domain_record_type', 'value' => 1 ) );
						$select_domain_record_type->add( 'A', 'A' );
						$select_domain_record_type->add( 'CNAME', 'CNAME' );
						
						$input_domain_record_value = new html_inputfield( array( 'id' => '_add_domain_record_value', 'name' => '_add_domain_record_value[]', 'size' => 20, 'value' => $domain_record_value[ $i ] ) );
						
						$remove_field = html::a(array(
							'href' => "#",
							'onclick' => "UI.remove_row($(this).parent().parent())",
							'title' => 'Remove Domain Record',
							'class' => 'rules_block_email_remove'
						),'Remove');
					
						$blocks[ 'main' ][ 'options' ][ 'domain_record_name'.$i ] = array( 
							'content' => $domain_record_lbl.$input_domain_record_name->show()."   ".$select_domain_record_type->show($domain_record_type[ $i ])."   ".$input_domain_record_value->show().$remove_field
						);
						
					}
				}
				
				$submit_button = new html_inputfield( array( 'type' => 'submit', 'value' => 'Add Record', 'class' => 'button mainaction manage_user_edt_del', 'name' => 'manage_domain_alias_flg' ) );
			
				$cancel_button = new html_inputfield( array( 'type' => 'submit', 'value' => 'Cancel', 'class' => 'button mainaction manage_user_edt_del', 'onclick' => '', 'name' => 'manage_domain_alias_flg' ) );
				
				$blocks['main']['options']['submit'] = array(
					'content' => $submit_button->show().$cancel_button->show()
				);
				
				$org_domain_hidden_field = new html_hiddenfield( array( 'name' => '_hidden_domain_name', 'id' => '_hidden_domain_name', 'value' => $domain_name ) );
				
				$blocks[ 'main' ][ 'options' ][ 'hidden_org_domain' ] = array( 
					'content' => $org_domain_hidden_field->show()
				);
				
				// display all domain record 
				$delete_submit = new html_inputfield( array( 'type' => 'submit', 'value' => 'Delete Record', 'class' => 'button mainaction manage_user_edt_del', 'name' => 'manage_domain_alias_flg', 'onclick' => 'return UI.show_delete_domain_record_confirm();' ) );
				
				$blocks['dom_rec']['options']['delete_submit'] = array(
					'content' => $delete_submit->show()
				);
				
				$domain_record_details = $RC_HELP->get_domain_records( $domain_name );
				$total_domain_records = count( $domain_record_details[ 'dom_name' ] );
				
				$tbl_domain_records = new html_table( array( 'cols' => 4 ) );
				
				$tbl_domain_records->add_header( 'name', 'Name' );
				$tbl_domain_records->add_header( 'type', 'Type' );
				$tbl_domain_records->add_header( 'value', 'Value' );
				$tbl_domain_records->add_header( 'delete', 'Delete' );
				
				if( $total_domain_records > 0 )
				{
					for( $i = 0; $i < $total_domain_records; $i++ )
					{
						$check_delete = new html_checkbox( array( 'name' => '_delete_domain_record[]', 'id' => '_delete_domain_record', 'value' => '1', 'onclick' => 'UI.set_delete_domain_record(this,\''.$domain_record_details[ 'rec_name' ][ $i ].'\' )' ) );
						$tbl_domain_records->add( 'name', $domain_record_details[ 'rec_name' ][ $i ] );
						$tbl_domain_records->add( 'type', $domain_record_details[ 'rec_type' ][ $i ] );
						$tbl_domain_records->add( 'value', $domain_record_details[ 'rec_value' ][ $i ] );
						$tbl_domain_records->add( 'delete', $check_delete->show() );
					}
				}
				else
				{
					$tbl_domain_records->add( array( 'colspan' => '4' ), 'There are Currently No Domain Records' );
				}
				
				$blocks['dom_rec']['options']['domain_records_tbl'] = array(
					'content' => $tbl_domain_records->show()
				);
				
				$hidden_delete_domain_record_nm = new html_hiddenfield( array( 'name' => '_hidden_domain_record_nm', 'id' => '_hidden_domain_record_nm', 'value' => '' ) );
				
				$blocks['dom_rec']['options']['hidden_domain_rec'] = array(
					'content' => $hidden_delete_domain_record_nm->show()
				);
				
				$back_button2 = new html_inputfield( array( 'type' => 'submit', 'value' => 'Back', 'class' => 'button mainaction manage_user_edt_del', 'name' => 'manage_domain_alias_flg' ) );
				
				$blocks['dom_rec']['options']['back_btn2'] = array(
					'content' => $back_button1->show()
				);
				
				break;
		
			case 'users':
			// MANAGE USERS
			$blocks = array(
			  'main'    => array( 'name' => 'Update User Details for '.$user_email ) );
			
			$input_update_name = new html_inputfield( array( 'name' => '_update_name', 'id' => '_update_name', 'size' => 20, 'value' => $user_name ) );
			
			$blocks['main']['options']['updt_user_name'] = array(
				'title' => html::label($field_id, rcube::Q($RCMAIL->gettext('add_uname_title'))),
				'content' => $input_update_name->show(),
			);
			
			$input_update_email = new html_inputfield( array( 'name' => '_updt_user_email', 'id' => '_updt_user_email', 'size' => 20 ) );
			
			$blocks['main']['options']['_updt_user_email'] = array(
				'title' => html::label($field_id, rcube::Q($RCMAIL->gettext('add_uemail_title'))),
				'content' => $input_update_email->show( $user_email, array( 'readonly' => 'true', 'style' => 'background:#F0F0F0;' ) ),
			);
			
			$input_update_pwd = new html_inputfield( array( 'name' => '_updt_pwd', 'id' => '_updt_pwd', 'size' => 20, 'value' => "", 'autocomplete' => 'off' ) );
		
			$check_update_pwd_mask = new html_checkbox( array( 'name' => '_updt_pwd_mask', 'id' => '_updt_pwd_mask', 'value' => 1, 'onclick' => 'UI.toggle_pwd( \'_updt_pwd_mask\', \'_updt_pwd\' )' ) );
			$mask_title =  html::label('_updt_pwd_mask', 'Mask');
			
			$blocks['main']['options']['add_user_pwd'] = array(
				'title' => html::label('rcmfd_user_pwd', rcube::Q($RCMAIL->gettext('add_upwd_title'))),
				'content' => $input_update_pwd->show().$check_update_pwd_mask->show().$mask_title,
			);
			
			$input_update_priviledged = new html_checkbox( array( 'name' => '_updt_priviledged', 'id' => '_updt_priviledged', 'value' => 1 ) );
			
			if( $user_prev == '0' )
			{
				$blocks['main']['options']['_updt_priviledged'] = array(
					'title' => html::label($field_id, rcube::Q($RCMAIL->gettext('add_upriviledge_title'))),
					'content' => $input_update_priviledged->show(),
				);
			}
			else
			{
				$blocks['main']['options']['_updt_priviledged'] = array(
					'title' => html::label($field_id, rcube::Q($RCMAIL->gettext('add_upriviledge_title'))),
					'content' => $input_update_priviledged->show( 1, array( 'checked' => 'true' ) ),
				);
			}
			
			$select_update_quota = new html_select( array( 'name' => '_updt_quota', 'id' => '_updt_quota', 'value' => 1 ) );
			
			foreach( $RC_HELP->quota_array as $key => $value )
			{
				$select_update_quota->add( $value, $key );
			}	
			
			$blocks['main']['options']['_updt_quota'] = array(
				'title' => html::label($field_id, rcube::Q($RCMAIL->gettext('add_uquota_title'))),
				'content' => $select_update_quota->show( $inbox_quota ),
			);
			
			$submit_button = new html_inputfield( array( 'type' => 'submit', 'value' => 'Update', 'class' => 'button mainaction manage_user_edt_del', 'name' => 'manage_user_flg' ) );
			
			$cancel_button = new html_inputfield( array( 'type' => 'submit', 'value' => 'Cancel', 'class' => 'button mainaction manage_user_edt_del', 'name' => 'manage_user_flg' ) );
			
			$blocks['main']['options']['_update_submit'] = array(
				'content' => $submit_button->show().$cancel_button->show(),
			);
			
			break;
			
			case 'groups':
				
				$email_list_str = $_POST[ '_edit_list_hidden_field' ];
				
				if( ( isset( $_POST[ 'manage_list_flg' ] ) ) && ( $_POST[ 'manage_list_flg' ] == 'Save' ) )
				{
					$add_member_type = $_POST[ '_edit_list_type' ];
					$external_email = $_POST[ '_edit_list_ext_email' ];
					$email_list_str = $_POST[ '_edit_list_hidden_field' ];
				}
				else
				{
					$add_member_type = "local";
					$external_email = "";
					$email_list_str = "";
				}
				$list_name = ( !( empty( $_POST[ '_hidden_edit_list' ] ) ) ? ( $_POST[ '_hidden_edit_list' ] ) : ( $_POST[ '_list_name_hidden_field' ] ) );
				$add_member_count = $RC_HELP->get_list_non_exis_member( $list_name );
				$add_member_details = $RC_HELP->get_list_non_exis_member_det( $list_name );
				$blocks = array(
					'main'    => array( 'name' => 'Edit List '.$list_name ),
					'delete_block'    => array( 'name' => 'Current Members of '.$list_name ),
				);
				
				// EDIT BLOCK
				// $show_local_dropdown = ( isset( $_POST[ '_edit_list_local_chk' ] ) ) ? ( "block" ) : ( "none" );
				
				$back_btn = new html_inputfield( array( 'type' => 'submit', 'value' => 'Back', 'class' => 'button mainaction manage_user_edt_del', 'name' => 'manage_list_flg' ) );
				
				$blocks['main']['options']['_back_btn_1'] = array(
						'content' => $back_btn->show(),
				);
				
				$show_external_txt = ( isset( $_POST[ '_edit_list_external_chk' ] ) ) ? ( "block" ) : ( "none" );
				
				$local_label = html::label( '_edit_list_local', 'Local' );
				
				$radio_local = new html_radiobutton( array( 'name' => '_edit_list_type', 'id' => '_edit_list_local', 'onclick' => 'UI.display_local_list_drodown();', "value" => "local" ) );
				
				if( $add_member_type == 'local' )
				{
					$blocks['main']['options']['_edit_list_local_chk'] = array(
						'title' => 'Add Member From List',
						'content' => $radio_local->show( 'local', array( 'checked' => 'true' ) ).$local_label,
					);
				}
				else
				{
					$blocks['main']['options']['_edit_list_local_chk'] = array(
						'title' => 'Add Member From List',
						'content' => $radio_local->show().$local_label,
					);
				}
				
				$add_member_table = new html_table( array( 'cols' => 2 ) );
				/* $add_member_table->add_header( 'email', "Email" );
				$add_member_table->add_header( 'add_chkbox', "Add" ); */
				
				if( $add_member_count > 0 )
				{
					for( $i = 0; $i < $add_member_count; $i++ )
					{
						if( strpos( $email_list_str, $add_member_details[ 'email' ][ $i ] ) !== false )
						{
							$add_chkbox = new html_checkbox( array( 'name' => '_edit_list_add_member[]', 'id' => '_edit_list_add_member', 'onclick' => 'UI.set_edit_list_name( this, \''.$add_member_details[ 'email' ][ $i ].'\' )', 'checked' => 'true' ) );
						}
						else
						{
							$add_chkbox = new html_checkbox( array( 'name' => '_edit_list_add_member[]', 'id' => '_edit_list_add_member', 'onclick' => 'UI.set_edit_list_name( this, \''.$add_member_details[ 'email' ][ $i ].'\' )', 'value' => '1' ) );
						}
						$add_member_table->add( "email", $add_member_details[ 'email' ][ $i ] );
						$add_member_table->add( "addchkbox", $add_chkbox->show() );
					}
				}
				else
				{
					$add_member_table->add( 'no_member', 'There are No Users to Add' );
				}
				
				$add_member_div = html::div( array( 'id' => 'add_member_to_list_div' ), $add_member_table->show() );
				
				$blocks['main']['options']['_edit_list_add_mem_tbl'] = array(
					'content' => $add_member_div,
				);
				
				$external_label = html::label( '_edit_list_external', 'External' );
				
				$radio_external = new html_radiobutton( array( 'name' => '_edit_list_type', 'id' => '_edit_list_external', 'onclick' => 'UI.display_external_list_txt()', "value" => "external" ) );
				
				$txt_external = new html_inputfield( array( 'name' => '_edit_list_ext_email', 'id' => '_edit_list_ext_email', 'size' => 20, 'value' => '', "style" => "display: ".$show_external_txt ) );
				
				if( $add_member_type == 'external' )
				{
					$blocks['main']['options']['_edit_list_external_chk'] = array(
						'title' => 'Add Custom Member',
						'content' => $radio_external->show( 'external', array( 'checked' => 'true' ) ).$external_label.$txt_external->show(),
					);
				}
				else
				{
					$blocks['main']['options']['_edit_list_external_chk'] = array(
						'title' => 'Add Custom Member',
						'content' => $radio_external->show().$external_label.$txt_external->show(),
					);
				}
				
				
				$edit_submit = new html_inputfield( array( 'type' => 'submit', 'value' => 'Save', 'class' => 'button mainaction manage_user_edt_del', 'name' => 'manage_list_flg' ) );
				
				$cancel_submit = new html_inputfield( array( 'type' => 'submit', 'value' => 'Cancel', 'class' => 'button mainaction manage_user_edt_del', 'name' => 'manage_list_flg' ) );
				
				$blocks['main']['options']['_edit_submit'] = array(
					'content' => $edit_submit->show().$cancel_submit->show(),
				);
				
				$edit_list_hidden_field = new html_hiddenfield( array( 'name' => '_edit_list_hidden_field', 'id' => '_edit_list_hidden_field', 'value' => $email_list_str ) );
				
				$blocks['delete_block']['options']['_edit_list_hidden_field'] = array(
					'content' => $edit_list_hidden_field->show(),
				);
				
				// DELETE BLOCK
				
				$delete_submit = new html_inputfield( array( 'type' => 'submit', 'value' => 'Delete Members', 'class' => 'button mainaction manage_user_edt_del', 'name' => 'manage_list_flg', 'onclick' => 'return UI.confirm_delete_list_members();' ) );
				
				$total_curr_list_member = $RC_HELP->get_list_memeber_count( $list_name );
				$curr_list_member_det = $RC_HELP->get_list_member_details( $list_name );
				
				$blocks['delete_block']['options']['_delete_submit'] = array(
					'content' => $delete_submit->show(),
				);
				
				$delete_dl_list = new html_table( array( 'cols' => 2) );
				$delete_dl_list->add_header( "email", "Email" );
				$delete_dl_list->add_header( "delete_email", "Delete" );
				
				if( $total_curr_list_member == 0 )
				{
					$delete_dl_list->add( 'no_member', 'There are no Members in the List' );
				}
				else
				{
					for( $i = 0; $i < $total_curr_list_member; $i++ )
					{
						$list_member_del = new html_checkbox( array( 'name' => '_edit_list_del_mem[]', 'id' => '_edit_list_del_mem', 'value' => "1", "onclick" => "UI.set_edit_list_name_del( this, '".$curr_list_member_det[ 'email_dest' ][ $i ]."' )" ) );
						$delete_dl_list->add( "email", $curr_list_member_det[ 'email_dest' ][ $i ] );
						$delete_dl_list->add( "delete_member", $list_member_del->show() );
					}
				}
				
				$blocks['delete_block']['options']['_current_member'] = array(
					'content' => $delete_dl_list->show(),
				);
				
				$delete_list_mem_hidden_fld = new html_hiddenfield( array( 'name' => '_del_list_mem_hidden_field', 'id' => '_del_list_mem_hidden_field' ) );
				
				$blocks['delete_block']['options']['_del_list_mem_hidden_field'] = array(
					'content' => $delete_list_mem_hidden_fld->show(),
				);
				
				// Back BUtton
				$blocks['delete_block']['options']['_back_btn_2'] = array(
						'content' => $back_btn->show(),
				);
				
				$list_name_hidden_field = new html_hiddenfield( array( 'name' => '_list_name_hidden_field', 'id' => '_list_name_hidden_field', 'value' => $list_name ) );
				
				$blocks['delete_block']['options']['_list_name_hidden_field'] = array(
					'content' => $list_name_hidden_field->show(),
				);
				
				
				break;
		} // end of switch block
		$data = $RCMAIL->plugins->exec_hook('manage_list', array('section' => $sect['id'], 'blocks' => $blocks, 'current' => $current));
		$found = false;
		
		$advanced_prefs = (array) $RCMAIL->config->get('advanced_prefs');
		
		// create output
        foreach ($data['blocks'] as $key => $block) {
            if (!empty($block['content']) || !empty($block['options'])) {
                $found = true;
            }
            // move some options to the 'advanced' block as configured by admin
            if ($key != 'advanced') {
                foreach ($advanced_prefs as $opt) {
                    if ($block['options'][$opt]) {
                        $data['blocks']['advanced']['options'][$opt] = $block['options'][$opt];
                        unset($data['blocks'][$key]['options'][$opt]);
                    }
                }
            }
        } // foreach 2 end
		
		// move 'advanced' block to the end of the list
        if (!empty($data['blocks']['advanced'])) {
            $adv = $data['blocks']['advanced'];
            unset($data['blocks']['advanced']);
            $data['blocks']['advanced'] = $adv;
        }
		
		if (!$found)
            unset($sections[$idx]);
        else
            $sections[$idx]['blocks'] = $data['blocks'];
			
	}
	
	$data = $RCMAIL->plugins->exec_hook('manage_section_header',
        array('section' => $sect['id'], 'header' => '', 'current' => $current));
		
	// create output
	if(!empty($data['header'])) {
		$sections[$idx]['header'] = $data['header'];
	}

	return array($sections, $plugin['cols']);
}

/** 
 * Macgregor Changes
 * Show delete option for managing users
**/
function rcmail_manage_delete()
{
	global $RCMAIL , $OUTPUT, $RC_HELP;
	
	$sections['users'] = array('id' => 'users', 'manage' => $RCMAIL->gettext('manageusers'));
	if( isset( $_POST[ "_email_id" ] ) )
		$email_list = $_POST[ "_email_id" ];
		
	foreach ($sections as $idx => $sect) {

		if ($current && $sect['id'] != $current) {
			continue;
		}
		
		$blocks = array();

		switch ($sect['id']) {
		
			case 'users':
			$blocks = array(
				'main' => array( 'name' => 'Are you sure you want to delete Following Email(s)?' )
			);
			
			$email_list_arr = explode( "|", $email_list );
			
			foreach( $email_list_arr as $key => $email )
			{
				$email_lbl = html::label('del_email[]', $email);
				$email_ids = new html_inputfield( array( 'name' => 'del_email[]', 'id' => '_delete_user', 'size' => 20 ) );
				$blocks['main']['options']['delete_user_email'.$key] = array(
					'content' => $email_ids->show( $email, array( "readonly" => "true", "style" => "background:#F0F0F0;" ) )
				);
			}
			
			$delete_submit = new html_inputfield( array( 'type' => 'submit', 'value' => 'YES', 'class' => 'button mainaction manage_user_edt_del', 'name' => 'manage_user_flg') );
			
			$delete_cancel = new html_inputfield( array( 'type' => 'submit', 'value' => 'NO', 'class' => 'button mainaction manage_user_edt_del', 'name' => 'manage_user_flg' ) );
			
			$blocks['main']['options']['submit'] = array(
				'content' => $delete_submit->show().$delete_cancel->show()
			);
		} // switch end
		
		$data = $RCMAIL->plugins->exec_hook('manage_list', array('section' => $sect['id'], 'blocks' => $blocks, 'current' => $current));
		$found = false;
		
		$advanced_prefs = (array) $RCMAIL->config->get('advanced_prefs');

        // create output
        foreach ($data['blocks'] as $key => $block) {
            if (!empty($block['content']) || !empty($block['options'])) {
                $found = true;
            }
            // move some options to the 'advanced' block as configured by admin
            if ($key != 'advanced') {
                foreach ($advanced_prefs as $opt) {
                    if ($block['options'][$opt]) {
                        $data['blocks']['advanced']['options'][$opt] = $block['options'][$opt];
                        unset($data['blocks'][$key]['options'][$opt]);
                    }
                }
            }
        }

        // move 'advanced' block to the end of the list
        if (!empty($data['blocks']['advanced'])) {
            $adv = $data['blocks']['advanced'];
            unset($data['blocks']['advanced']);
            $data['blocks']['advanced'] = $adv;
        }

        if (!$found)
            unset($sections[$idx]);
        else
            $sections[$idx]['blocks'] = $data['blocks'];
	} // foreach end
	
	$data = $RCMAIL->plugins->exec_hook('manage_section_header',
        array('section' => $sect['id'], 'header' => '', 'current' => $current));

    if(!empty($data['header'])) {
        $sections[$idx]['header'] = $data['header'];
    }

    return array($sections, $plugin['cols']);
}

/**
 * Macgregor Changes
 * Show menu for rules
**/
function rcmail_rules_menu()
{
	global $RCMAIL , $OUTPUT, $RC_HELP;
	
	$sections['outOfOffice'] = array('id' => 'outOfOffice', 'rules' => $RCMAIL->gettext('outOfOffice'));
	$sections['blockEmail'] = array('id' => 'blockEmail', 'rules' => $RCMAIL->gettext('blockEmail'));
	$sections['forwardRule'] = array('id' => 'forwardRule', 'rules' => $RCMAIL->gettext('forwardRule'));
	$sections['customRule'] = array('id' => 'customRule', 'rules' => $RCMAIL->gettext('customRule'));

	// hook + define list cols
	$plugin = $RCMAIL->plugins->exec_hook('rules_sections_list',
		array('list' => $sections, 'cols' => array('rules')));
	$sections = $plugin['list'];

	$config = $RCMAIL->config->all();
	$no_override = array_flip($RCMAIL->config->get('dont_override', array()));
	
	foreach ($sections as $idx => $sect) {

		if ($current && $sect['id'] != $current) {
			continue;
		}
		
		$blocks = array();

		switch ($sect['id']) {
		
			case 'outOfOffice':
				$blocks = array(
				  'main' => array('name' => rcube::Q($RCMAIL->gettext('outOfOffice'))),
				);
				
				$blocks['main']['options']['desc_message'] = $RC_HELP->display_description( 'Automatically reply to a given sender once with the specified message. Often used for an out-of-office resoponse.' );
				
				$blocks['main']['options']['add_button'] = $RC_HELP->display_oof_add_button();
				
				$OOF_details = $RC_HELP->get_OutOfOffice_details();
				
				if( count( $OOF_details[ 'oof_rule_count' ] > 0 ) ) // if there are entries in db for this email
				{
					$enabled = $OOF_details[ 'enabled' ];
					$subject = $OOF_details[ 'subject' ];
					$message = $OOF_details[ 'message' ];
					$header = $OOF_details[ 'header' ];
					$filter = $OOF_details[ 'filter' ];
				}
				else // set default values
				{
					$enabled = $subject = $message = $header = $filter = array();
				}
				
				if( ( isset( $_POST[ '_action' ] ) ) && ( $_POST[ '_action' ] == 'save-rules' ) )
				{
					$enabled = $_POST[ 'hidden_oof_enabled' ];
					$subject = $_POST[ 'oof_subject' ];
					$message = $_POST[ 'oof_message' ];
					$header = $_POST[ 'hidden_oof_header' ];
					$filter = $_POST[ 'oof_header_match' ];
				}
				
				if( count( $enabled ) > 0 )
				{
					for( $i = 0; $i < count( $enabled ); $i++ )
					{
						$blocks['main']['options']['oof_enable'.$i] = $RC_HELP->display_oof_enable( $enabled[ $i ] );
						$blocks['main']['options']['oof_header'.$i] = $RC_HELP->display_oof_header( $header[ $i ], $enabled[ $i ] );
						$blocks['main']['options']['oof_filter'.$i] = $RC_HELP->display_oof_filter( $filter[ $i ], $header[ $i ], $enabled[ $i ] );
						$blocks['main']['options']['oof_subject'.$i] = $RC_HELP->display_oof_subject( $subject[ $i ], $enabled[ $i ] );
						$blocks['main']['options']['oof_message'.$i] = $RC_HELP->display_oof_message( $subject[ $i ], $enabled[ $i ] );
						$blocks['main']['options']['oof_remove_btn'.$i] = $RC_HELP->display_oof_remove_btn();
						
						$blocks['main']['options']['oof_hidden_header'.$i] = $RC_HELP->display_oof_hidden_field( $header[ $i ], 'hidden_oof_header', 'hidden_oof_header[]' );
						
						$blocks['main']['options']['oof_hidden_enabled'.$i] = $RC_HELP->display_oof_hidden_field( $enabled[ $i ], 'hidden_oof_enabled', 'hidden_oof_enabled[]' );
					}
				}
				
				break;
				
			case 'blockEmail':
				$blocks = array(
				  'main'    => array('name' => rcube::Q($RCMAIL->gettext('block'))),
				);
				$header_label = array( 'from', 'sub' );
				
				$BE_details = $RC_HELP->get_BlockEmail_details();
				$db_block_count = $BE_details[ 'block_count' ];
				$db_headers = $BE_details[ 'header' ];
				$db_filters = $BE_details[ 'filter' ];
				
				$db_row_count = count( $db_block_count );
				
				$blocks['main']['options']['desc_message'] = $RC_HELP->display_description( ' Block and do not deliver any email which contains the Matching text applied to the header selected.' );
				
				$blocks['main']['options']['block_email_add'] = $RC_HELP->display_block_email_add_btn();
				
				if( isset( $_POST[ '_block_email_header' ] ) )
				{
					$block_email_count = count( $_POST[ '_block_email_header' ] );
					$block_email_header = $_POST[ '_block_email_header' ];
					$block_email_filter = $_POST[ '_block_email_filter' ];
				}
				else if( count( $BE_details[ 'header' ] ) )
				{
					$block_email_count = count( $BE_details[ 'header' ] );
					$block_email_header = $BE_details[ 'header' ];
					$block_email_filter = $BE_details[ 'filter' ];
				}
				for( $i = 0; $i < $block_email_count; $i ++ )
				{
					$blocks['main']['options']['header'.$i] = $RC_HELP->display_block_email_header( $header_label, $i, $block_email_header );
					$blocks['main']['options']['filter'.$i] = $RC_HELP->display_block_email_filter( $i, $block_email_filter );
					$blocks['main']['options']['add'.$i] = $RC_HELP->display_block_email_remove( $i );
				}
				break;
			
			case 'forwardRule':
				$blocks = array(
				  'main'    => array('name' => rcube::Q($RCMAIL->gettext('forwardRule'))),
				);
				
				$header_label = array( 'all', 'from', 'sub', 'to', 'cc' );
				
				$FW_details = $RC_HELP->get_ForwardRule_details(); // get_BlockEmail_details	get_ForwardRule_details
				
				//BE_details FW_details
				$db_fw_count = $FW_details[ 'fw_rules_count' ]; //db_block_count db_fw_count  block_count fw_rules_count
				
				$db_headers = $FW_details[ 'header' ];
				
				$db_filters = $FW_details[ 'filter' ]; 
				
				$db_fw_emails = $FW_details[ 'fw_to_email' ]; //newly crfeated db_fw_emails 
				
				$db_row_count = count( $db_fw_count );
				
				$blocks['main']['options']['desc_message'] = $RC_HELP->display_description( 'Forward any incoming email which matches the text specified applied to the given header( or forward all emails ) to the given email address.' );
				
				$blocks['main']['options']['add'] = $RC_HELP->display_fwd_rule_add_btn();
				
				if( isset( $_POST[ '_forward_rule_header' ] ) ) // if post parameter is set for header, display the multiple table cells if present
				{
					$fwd_rule_count = count( $_POST[ '_forward_rule_header' ] );
					$fwd_rule_header = $_POST[ '_forward_rule_header' ];
					$fwd_rule_filter = $_POST[ '_forward_rule_filter' ];
					$fwd_rule_email = $_POST[ '_forward_rule_email' ];
				}
				else if( count( $FW_details[ 'header' ] ) > 0 )
				{
					$fwd_rule_count = count( $FW_details[ 'header' ] );
					$fwd_rule_header = $FW_details[ 'header' ];
					$fwd_rule_filter = $FW_details[ 'filter' ];
					$fwd_rule_email = $FW_details[ 'fw_to_email' ];
				}
				for( $i = 0; $i < $fwd_rule_count; $i++ )
				{
					$blocks['main']['options']['header'.$i] = $RC_HELP->display_fwd_rule_header( $header_label, $fwd_rule_header, $i );
					$blocks['main']['options']['filter'.$i] = $RC_HELP->display_fwd_rule_filter( $fwd_rule_header, $fwd_rule_filter, $i );
					$blocks['main']['options']['email'.$i] = $RC_HELP->display_fwd_rule_email( $fwd_rule_email, $i );
					$blocks['main']['options']['add'.$i] = $RC_HELP->display_fwd_rule_remove_btn( $i );
				}
				break;
			
			case 'customRule':
				$blocks = array(
				  'main'    => array('name' => Q(rcube_label('customRule'))),
				);
				
				$CR_details = $RC_HELP->get_CustomRule_details();
				$enabled = $CR_details[ 'enabled' ];
				$description = $CR_details[ 'description' ];
				$desc_value = '';
				$desc_prop = '';
				
				$blocks['main']['options']['desc_message'] = $RC_HELP->display_description( 'Highly specialised rules may be specified here; please only apply scripts if you are confident in what you are doing' );
				
				$blocks['main']['options']['field_id'] = $RC_HELP->display_custom_rule_enable( $enabled );
				
				$blocks['main']['options']['message'] = $RC_HELP->display_custom_rule_message( $description, $enabled );
				
				break;
		} // switch end
		
		$data = $RCMAIL->plugins->exec_hook('rules_list', array('section' => $sect['id'], 'blocks' => $blocks, 'current' => $current));
		$found = false;
		
		$advanced_prefs = (array) $RCMAIL->config->get('advanced_prefs');
		
		// create output
        foreach ($data['blocks'] as $key => $block) {
            if (!empty($block['content']) || !empty($block['options'])) {
                $found = true;
            }
            // move some options to the 'advanced' block as configured by admin
            if ($key != 'advanced') {
                foreach ($advanced_prefs as $opt) {
                    if ($block['options'][$opt]) {
                        $data['blocks']['advanced']['options'][$opt] = $block['options'][$opt];
                        unset($data['blocks'][$key]['options'][$opt]);
                    }
                }
            }
        }
		
		// move 'advanced' block to the end of the list
        if (!empty($data['blocks']['advanced'])) {
            $adv = $data['blocks']['advanced'];
            unset($data['blocks']['advanced']);
            $data['blocks']['advanced'] = $adv;
        }

        if (!$found)
            unset($sections[$idx]);
        else
            $sections[$idx]['blocks'] = $data['blocks'];
		
	} // foreach end
	
	$data = $RCMAIL->plugins->exec_hook('rules_section_header',
        array('section' => $sect['id'], 'header' => '', 'current' => $current));
		
	if(!empty($data['header'])) {
        $sections[$idx]['header'] = $data['header'];
    }
	
	return array($sections, $plugin['cols']);
}

function rcmail_get_skins()
{
    $path  = RCUBE_INSTALL_PATH . 'skins';
    $skins = array();
    $dir   = opendir($path);

    if (!$dir) {
        return false;
    }

    while (($file = readdir($dir)) !== false) {
        $filename = $path.'/'.$file;
        if (!preg_match('/^\./', $file) && is_dir($filename) && is_readable($filename)) {
            $skins[] = $file;
        }
    }

    closedir($dir);

    return $skins;
}


function rcmail_folder_options($mailbox)
{
    global $RCMAIL;

    $options = $RCMAIL->get_storage()->folder_info($mailbox);
    $options['protected'] = $options['is_root'] || ($options['special'] && $RCMAIL->config->get('protect_default_folders'));

    return $options;
}

/**
 * Updates (or creates) folder row in the subscriptions table
 *
 * @param string $name      Folder name
 * @param string $oldname   Old folder name (for update)
 * @param bool   $subscribe Checks subscription checkbox
 * @param string $class     CSS class name for folder row
 */
function rcmail_update_folder_row($name, $oldname=null, $subscribe=false, $class_name=null)
{
    global $RCMAIL, $OUTPUT;

    $protect_folders = $RCMAIL->config->get('protect_default_folders');
    $storage         = $RCMAIL->get_storage();
    $delimiter       = $storage->get_hierarchy_delimiter();

    $name_utf8    = rcube_charset::convert($name, 'UTF7-IMAP');
    $protected    = $protect_folders && $storage->is_special_folder($name);
    $foldersplit  = explode($delimiter, $storage->mod_folder($name));
    $level        = count($foldersplit) - 1;
    $display_name = $protected ? $RCMAIL->localize_foldername($name) : rcube_charset::convert($foldersplit[$level], 'UTF7-IMAP');
    $class_name   = trim($class_name . ' mailbox');

    if ($oldname === null) {
        $OUTPUT->command('add_folder_row', $name, $name_utf8, $display_name, $protected, $subscribe,
            $class_name);
    }
    else {
        $OUTPUT->command('replace_folder_row', $oldname, $name, $name_utf8, $display_name, $protected, $class_name);
    }
}

/**
 * Render the list of settings sections (AKA tabs)
 */
function rcmail_settings_tabs($attrib)
{
    global $RCMAIL, $OUTPUT;
    $is_superadmin = $RCMAIL->config->get( 'is_super_admin' );
	$is_admin = $RCMAIL->config->get( 'is_admin' );
    $global_archive = $RCMAIL->config->get( 'global_archive' );
	

    // add default attributes
    $attrib += array('tagname' => 'span', 'idprefix' => 'settingstab', 'selclass' => 'selected');

    $default_actions = array(
        array('command' => 'preferences', 'type' => 'link', 'label' => 'preferences', 'title' => 'editpreferences'),
        array('command' => 'folders',     'type' => 'link', 'label' => 'folders',     'title' => 'managefolders'),
        array('command' => 'identities',  'type' => 'link', 'label' => 'identities',  'title' => 'manageidentities'),
        array('command' => 'responses',   'type' => 'link', 'label' => 'responses',   'title' => 'manageresponses'),
    );
    
    
    if( $global_archive == 'mail' )
    {
        $default_actions[] = array('command' => 'admin',   	  'type' => 'link', 'label' => 'admin',        'title' => 'admin');
        $default_actions[] = array('command' => 'rules',   	  'type' => 'link', 'label' => 'rules',        'title' => 'rules');
        
        if( $is_admin )
        {
            $default_actions[] = array('command' => 'manage',   	  'type' => 'link', 'label' => 'manage',        'title' => 'manage');
        }
        
        if( $is_superadmin )
        {
            $default_actions[] = array('command' => 'superadmin',   	  'type' => 'link', 'label' => 'superadmin',        'title' => 'superadmin');
        }
    }
	

    // get all identites from DB and define list of cols to be displayed
    $plugin = $RCMAIL->plugins->exec_hook('settings_actions', array(
        'actions' => $default_actions,
        'attrib'  => $attrib,
    ));

    $attrib  = $plugin['attrib'];
    $tagname = $attrib['tagname'];
    $tabs    = array();

    foreach ($plugin['actions'] as $action) {
        if (!$action['command'] && !$action['href'] && $action['action']) {
            $action['href'] = $RCMAIL->url(array('_action' => $action['action']));
        }

        $button = $OUTPUT->button($action);
        $attr   = $attrib;

        $cmd = $action['action'] ? $action['action'] : $action['command'];
        $id  = $action['id'] ? $action['id'] : $cmd;

        if (!empty($id)) {
            $attr['id'] = preg_replace('/[^a-z0-9]/i', '', $attrib['idprefix'] . $id);
        }

        $classnames = array($attrib['class']);
        if (!empty($action['class'])) {
            $classnames[] = $action['class'];
        }
        else if (!empty($cmd)) {
            $classnames[] = $cmd;
        }
        if ($RCMAIL->action == $cmd) {
            $classnames[] = $attrib['selclass'];
        }

        $attr['class'] = join(' ', $classnames);
        $tabs[] = html::tag($tagname, $attr, $button, html::$common_attrib);
    }

    return join('', $tabs);
}
