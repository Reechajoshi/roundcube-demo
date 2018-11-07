<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/rules.inc                                 |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2007, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide functionality of rules setting                                 |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

$CURR_SECTION = get_input_value('_rules', RCUBE_INPUT_GPC);

$OUTPUT->set_pagetitle(rcube_label('rules'));   // sets the page title to rules
$OUTPUT->include_script('list.js');

function rcmail_rules_frame($attrib)
{
	global $OUTPUT;

	if (!$attrib['id'])
		$attrib['id'] = 'rcmRulesFrame';

	return $OUTPUT->frame($attrib, true);
}

// register UI objects
$OUTPUT->add_handlers(array('rulesframe' => 'rcmail_rules_frame'));

$OUTPUT->send('rules');
?>