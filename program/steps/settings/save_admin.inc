<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/edit_identity.inc                              |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2011, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Show edit form for a identity record or to add a new one            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Macgregor Technology    				                         |
 +-----------------------------------------------------------------------+
*/
$CURR_SECTION = get_input_value('_admin', RCUBE_INPUT_POST);

global $RC_HELP, $RC_MAIL;

$admin_options = array();

switch( $CURR_SECTION )
{
	case 'changepwd':
		$admin_options = array(
			'_admin_pwd' => isset( $_POST[ '_admin_pwd' ] ) ? get_input_value('_admin_pwd', RCUBE_INPUT_POST) : $CONFIG['admin_pass'],
			'_curr_admin_pwd' => isset( $_POST[ '_curr_admin_pwd' ] ) ? get_input_value('_curr_admin_pwd', RCUBE_INPUT_POST) : $CONFIG['confirm_admin_pass'],
			'_conf_admin_pwd' => isset( $_POST[ '_conf_admin_pwd' ] ) ? get_input_value('_conf_admin_pwd', RCUBE_INPUT_POST) : $CONFIG['current_admin_pass'],
		);
		
		$RC_HELP->validate_admin_pwd( $admin_options );
		break;
		
	case 'notification':
		$admin_options = array(
			'_email_notify_txtbox' => ( isset( $_POST[ '_email_notify_txtbox' ] ) ) ? ( get_input_value('_email_notify_txtbox', RCUBE_INPUT_POST) ) : ( $CONFIG['email_notification'] ),
		);
		$RC_HELP->validate_email_notification( $admin_options );
		break;
}

if( $RC_HELP->admin_ok )
	$RC_MAIL->publishSieve();
  
// display the form again
rcmail_overwrite_action('edit-admin');


