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
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

$CURR_SECTION = get_input_value('_manage', RCUBE_INPUT_GPC);

$OUTPUT->set_pagetitle(rcube_label('manage'));   // sets the page title to manage
$OUTPUT->include_script('list.js');

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
function rcmail_manage_frame($attrib)
{
	global $OUTPUT;

	if (!$attrib['id'])
		$attrib['id'] = 'rcmManageFrame';

	return $OUTPUT->frame($attrib, true);
}

// register UI objects
$OUTPUT->add_handlers(array('manageframe' => 'rcmail_manage_frame'));

$OUTPUT->send('manage');
