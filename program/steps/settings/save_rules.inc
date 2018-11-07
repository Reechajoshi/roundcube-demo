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
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/
$CURR_SECTION = get_input_value('_rules', RCUBE_INPUT_POST);

global $RC_HELP, $RC_MAIL;

$rules_options = array();
$err_msg = 'errorsaving';

switch( $CURR_SECTION )
{
	case 'outOfOffice':
		$rules_options = $RC_HELP->set_oof_rules_arr( $_POST );
		$RC_HELP->validate_out_of_office( $rules_options );
	break;
		
	case 'blockEmail':
		if( ( isset( $_POST[ '_block_email_header' ] ) ) && ( isset( $_POST[ '_block_email_filter' ] ) ) ) // when all field elements are removed, remove all elements from db
		{
			$rules_options = array(
				'_block_email_header' => isset( $_POST[ '_block_email_header' ] ) ? get_input_value('_block_email_header', RCUBE_INPUT_POST) : $CONFIG['blockEmail_header'],
				'_block_email_filter' => isset( $_POST[ '_block_email_filter' ] ) ? get_input_value('_block_email_filter', RCUBE_INPUT_POST) : $CONFIG['blockEmail_filter'],
			);
		}
		else
		{
			$rules_options = array();
		}
		$RC_HELP->validate_block_email( $rules_options );
	break;
	
	case 'forwardRule':
		if( ( isset( $_POST[ '_forward_rule_header' ] ) ) && ( isset( $_POST[ '_forward_rule_filter' ] ) ) && ( isset( $_POST[ '_forward_rule_email' ] ) ) ) // when all field elements are removed, remove all elements from db
		{
			$rules_options = array(
				'_forward_rule_header' => isset( $_POST[ '_forward_rule_header' ] ) ? get_input_value('_forward_rule_header', RCUBE_INPUT_POST) : $CONFIG['forwardRule_header'],
				'_forward_rule_filter' => isset( $_POST[ '_forward_rule_filter' ] ) ? get_input_value('_forward_rule_filter', RCUBE_INPUT_POST) : $CONFIG['forwardRule_filter'],
				'_forward_rule_email' => isset( $_POST[ '_forward_rule_email' ] ) ? get_input_value('_forward_rule_email', RCUBE_INPUT_POST) : $CONFIG['forwardRule_email'],
			);
		}
		else
		{
			$rules_options = array();
		}
		$RC_HELP->validate_forward_rule( $rules_options );
	break;
	
	case 'customRule':
		$rules_options = array(
			'_custom_rule_enable' => isset( $_POST[ '_custom_rule_enable' ] ) ? get_input_value('_custom_rule_enable', RCUBE_INPUT_POST) : $CONFIG['custom_rule_enable'],
			'_custom_rule_desc' => isset( $_POST[ '_custom_rule_desc' ] ) ? get_input_value('_custom_rule_desc', RCUBE_INPUT_POST) : $CONFIG['custom_rule_desc']
		);
		$RC_HELP->validate_custom_rule( $rules_options );
	break;
}

if( $RC_HELP->rules_ok )
{
	$RC_MAIL->publishSieve();
}
  
// display the form again
rcmail_overwrite_action('edit-rules');