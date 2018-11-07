<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/admin.inc                                 |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2007, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide functionality of admin setting                                 |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Macgregor Technology    				                         |
 +-----------------------------------------------------------------------+
*/

$CURR_SECTION = get_input_value('_admin', RCUBE_INPUT_GPC);



/* 
function rcmail_admin_setting_form()
{
	return( 'form comes here' );
}

function rcmail_admin_setting_name()
{
	return( 'title here' );
}
 */
function rcmail_admin_frame($attrib)
{
	global $OUTPUT;

	if (!$attrib['id'])
		$attrib['id'] = 'rcmAdminFrame';

	return $OUTPUT->frame($attrib, true);
}

if ($OUTPUT->ajax_call) {
    $OUTPUT->send();
}

$OUTPUT->set_pagetitle(rcube_label('admin'));   // sets the page title to admin
$OUTPUT->include_script('list.js');

// register UI objects
$OUTPUT->add_handlers(array('adminframe' => 'rcmail_admin_frame'));

$OUTPUT->send('admin');