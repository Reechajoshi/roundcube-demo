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
$CURR_SECTION = get_input_value('_manage', RCUBE_INPUT_POST);
global $RC_HELP, $RC_MAIL, $OUTPUT;

switch( $CURR_SECTION )
{
	case 'domain_aliases': // ACCOUNT DOMAIN
		if( isset( $_POST[ 'manage_domain_alias_flg' ] ) )
		{
			switch( $_POST[ 'manage_domain_alias_flg' ] )
			{
				case 'Add':
					$manage_domain_alias_opt = set_add_domain_alias();
					break;
					
				case 'Edit':
					$manage_domain_alias_opt = set_domain_alias_flag();
					break;
					
				case 'Cancel':
					$manage_domain_alias_opt = set_domain_alias_flag();
					break;
					
				case 'Back':
					$manage_domain_alias_opt = set_domain_alias_flag();
					break;
					
				case 'Add Record':
					$manage_domain_alias_opt = set_add_domain_record();
					break;
					
				case 'Delete Record':
					$manage_domain_alias_opt = set_delete_domain_record();
					break;
					
				case 'Delete':
					$manage_domain_alias_opt = set_delete_domain_alias();
					break;
			}
		}
		
		/* THIS METHOD WILL ADD DOMAIN ALIAS, DELETE DOMAIN ALIAS, ADD DOMAIN RECORD, DELETE DOMAIN RECORD */
		$RC_HELP->validate_manage_domain_aliases( $manage_domain_alias_opt );
		break;
	
	case 'accounts':
			error_log( 'case accounts' );
			$manage_accounts_opt = set_add_accouns();
			
			$RC_HELP->add_account_selection( $manage_accounts_opt );
		break;
		
	case 'users': // MANAGE USERS
		if( isset( $_POST[ "manage_user_flg" ] ) )
		{
			switch( $_POST[ "manage_user_flg" ] )
			{
				case "Add": // submit for adding usr
					$mange_usr_opt = set_add_user_fields();
					break;
				case "Delete": // submit for delete option of edit user
					$mange_usr_opt = set_delete_user_fields();
					break;
				case "Update": // submit for edit user
					$mange_usr_opt = set_update_user_fields();
					break;
				case "Edit": // submit for update option of edit user
					$mange_usr_opt = array();
					break;
				case "Cancel": // cancel button for update form
					$mange_usr_opt = array(); 
					break;
				case "YES": // submit button for delete form
					$mange_usr_opt = set_delete_email_fields(); 
					break;
				case "NO": // cancel button for delete form
					$mange_usr_opt = array(); 
					break;
			}
			$RC_HELP->validate_manage_users( $mange_usr_opt, $_POST[ "manage_user_flg" ] );			
		}
	break;
	
	
	case 'groups': // DISTRIBUTION LISTS
		if( isset( $_POST[ 'manage_list_flg' ] ) )
		{
			switch( $_POST[ 'manage_list_flg' ] )
			{
				case 'Add': // form 1
					$mange_lst_opt = set_add_list_fields();
					break;
				case 'Edit':
					$mange_lst_opt = $_POST;
					break;
				case 'Delete List': // form 1 to delete list
					$mange_lst_opt = set_delete_list_fields();
					break;
				case 'Save': // form 2 to add member to list
					$mange_lst_opt = set_add_list_member_fields();
					break;
				case 'Cancel': // form 2 to cancel form 2 and displayform 1
					$mange_lst_opt = array();
					break;
				case 'Back': // form 2 to cancel form 2 and displayform 1
					$mange_lst_opt = array();
					break;
				case 'Delete Members': // form 2 to delete member from list
					$mange_lst_opt = set_delete_list_member_fields();
					break;
			}
		}
		$RC_HELP->validate_manage_list( $mange_lst_opt, $_POST[ "manage_list_flg" ] );			
		break;
		
		case 'aliases': // USER ALIASES
			if( ( isset( $_POST[ 'manage_aliases_flg' ] ) ) && ( isset( $_POST[ 'manage_aliases_flg' ] ) ) )
			{
				switch( $_POST[ 'manage_aliases_flg' ] )
				{
					case 'Delete':
						$manage_alias_opt = set_delete_alias_fields();
						break;
					case 'Add':
						$manage_alias_opt = set_add_alias_fields();
						break;
				}
			}
			$RC_HELP->validate_manage_alias( $manage_alias_opt, $_POST[ 'manage_aliases_flg' ] );
			break;
			
		case 'user_rules': // USER RULES
			$manage_user_rule_opt = array();
			if( ( isset( $_POST[ 'manage_user_rules_save_flg' ] ) ) && ( $_POST[ 'manage_user_rules_save_flg' ] == 'Save' ) ) // if save button is hit
			{
				$rule_type = $_POST[ '_add_user_rule_name' ];
				$email = $_POST[ '_add_user_rule_email' ];
				switch( $rule_type )
				{
					case 'oof':
						$manage_user_rule_opt = $RC_HELP->set_oof_rules_arr( $_POST );
						$RC_HELP->validate_out_of_office( $manage_user_rule_opt, $email, true );
						break;
						
					case 'block_email':
						$manage_user_rule_opt = $RC_HELP->set_block_email_rules_arr( $_POST );
						$RC_HELP->validate_block_email( $manage_user_rule_opt, $email, true );
						break;
						
					case 'fwd_rule':
						$manage_user_rule_opt = $RC_HELP->set_fwd_rules_arr( $_POST );
						$RC_HELP->validate_forward_rule( $manage_user_rule_opt, $email, true );
						break;
						
					case 'custom_directive':
						$manage_user_rule_opt = $RC_HELP->set_custom_rule_arr( $_POST );
						$RC_HELP->validate_custom_rule( $manage_user_rule_opt, $email, true ); 
						break;
				}
			}
			break;
}


function set_add_user_fields() // on submit of add form
{
	$mange_usr_opt = array();
	$mange_usr_opt["_add_user_name"] = ( isset( $_POST[ "_add_user_name" ] ) ) ? ( get_input_value('_add_user_name', RCUBE_INPUT_POST) ) : ( "" ); // username
	$mange_usr_opt["_add_user_email_nm"] = ( isset( $_POST[ "_add_user_email_nm" ] ) ) ? ( get_input_value('_add_user_email_nm', RCUBE_INPUT_POST) ) : ( "" ); // email name
	$user_email_dom = get_input_value('_add_user_email_dom', RCUBE_INPUT_POST); // email domain
	$mange_usr_opt["_add_user_email"] = $mange_usr_opt["_add_user_email_nm"].'@'.$user_email_dom; // full email
	$mange_usr_opt["_add_user_pwd"] = ( isset( $_POST[ "_add_user_pwd" ] ) ) ? ( get_input_value('_add_user_pwd', RCUBE_INPUT_POST) ) : ( "" ); // password
	$mange_usr_opt["_add_user_quota"] = ( isset( $_POST[ "_add_user_quota" ] ) ) ? ( get_input_value('_add_user_quota', RCUBE_INPUT_POST) ) : ( "" ); // quota
	$mange_usr_opt["_add_user_priviledged"] = ( isset( $_POST[ "_add_user_priviledged" ] ) ) ? ( 1 ) : ( 0 ); // priviledge
	return $mange_usr_opt;
}

function set_delete_user_fields() // on submit of delete option of edit form
{
	$mange_usr_opt = array();
	$mange_usr_opt[ "_email_id" ] = $_POST[ "_email_id" ];
	return $mange_usr_opt;
}

function set_delete_email_fields() // on submit of delete form
{
	$mange_usr_opt = array();
	$mange_usr_opt[ 'del_email' ] = $_POST[ 'del_email' ];
	return $mange_usr_opt;
}

function set_update_user_fields() // on submit of update form
{
	$mange_usr_opt = array();
	$mange_usr_opt[ "_update_name" ] = ( isset($_POST[ "_update_name" ] ) ) ? ( get_input_value('_update_name', RCUBE_INPUT_POST) ) : ( "" );
	$mange_usr_opt[ "_updt_user_email" ] = ( isset($_POST[ "_updt_user_email" ] ) ) ? ( get_input_value('_updt_user_email', RCUBE_INPUT_POST) ) : ( "" );
	$mange_usr_opt[ "_updt_pwd" ] = ( isset($_POST[ "_updt_pwd" ] ) ) ? ( get_input_value('_updt_pwd', RCUBE_INPUT_POST) ) : ( "" );
	$mange_usr_opt[ "_updt_quota" ] = ( isset($_POST[ "_updt_quota" ] ) ) ? ( get_input_value('_updt_quota', RCUBE_INPUT_POST) ) : ( "" );
	$mange_usr_opt[ "_updt_priviledged" ] = ( isset($_POST[ "_updt_priviledged" ] ) ) ? ( 1 ) : ( 0 );
	return $mange_usr_opt;
}	

function set_add_list_fields()
{
	$mange_lst_opt[ '_add_list_name' ] = $_POST[ '_add_list_name' ];
	$mange_lst_opt[ '_add_list_dom' ] = $_POST[ '_add_list_dom' ];
	$mange_lst_opt[ '_add_list_private' ] = ( isset( $_POST[ '_add_list_private' ] ) ) ? ( "1" ) : ( "0" );
	return $mange_lst_opt;
}

function set_delete_list_fields()
{
	$mange_lst_opt[ 'delete_list_names' ] = $_POST[ '_hidden_edit_list' ];
	return $mange_lst_opt;
}

function set_add_list_member_fields()
{
	$mange_lst_opt[ '_edit_list_type' ] = $_POST[ '_edit_list_type' ];
	$mange_lst_opt[ '_edit_list_ext_email' ] = $_POST[ '_edit_list_ext_email' ];
	$mange_lst_opt[ '_edit_list_hidden_field' ] = $_POST[ '_edit_list_hidden_field' ];
	$mange_lst_opt[ '_list_name_hidden_field' ] = $_POST[ '_list_name_hidden_field' ];
	return $mange_lst_opt;
}

function set_delete_list_member_fields()
{
	$mange_lst_opt[ 'delete_list_member_str' ] = $_POST[ '_del_list_mem_hidden_field' ];
	$mange_lst_opt[ 'list_name' ] = $_POST[ '_list_name_hidden_field' ];
	return $mange_lst_opt;
}

function set_delete_alias_fields()
{
	$mange_lst_opt[ 'aliases_src_str' ] = $_POST[ '_hidden_user_aliases_src' ];
	$mange_lst_opt[ 'aliases_dest_str' ] = $_POST[ '_hidden_user_aliases_dest' ];
	return $mange_lst_opt;
}

function set_add_alias_fields()
{
	$manage_alias_opt[ '_add_alias_nm' ] = $_POST[ '_add_alias_nm' ];
	$manage_alias_opt[ '_add_alias_dom' ] = $_POST[ '_add_alias_dom' ];
	$manage_alias_opt[ '_add_alias_orig_nm' ] = $_POST[ '_add_alias_orig_nm' ];
	$manage_alias_opt[ '_add_alias_orig_dom' ] = $_POST[ '_add_alias_orig_dom' ];
	return $manage_alias_opt;
}

function set_add_accouns()
{
	$manage_accounts_opt[ "manage_account_selection_flg" ] = $_POST[ "manage_account_selection_flg" ]; 
	$manage_accounts_opt[ "_account_selection_domain" ] = $_POST[ "_account_selection_domain" ];
	
	return $manage_accounts_opt;
}

function set_add_domain_alias()
{
	$manage_domain_alias_opt[ 'domain_alias_name' ] = $_POST[ '_add_domain_alias_name' ];
	$manage_domain_alias_opt[ 'manage_domain_alias_flg' ] = $_POST[ 'manage_domain_alias_flg' ];
	
	return $manage_domain_alias_opt;
}

function set_domain_alias_flag()
{
	$manage_domain_alias_opt[ 'manage_domain_alias_flg' ] = $_POST[ 'manage_domain_alias_flg' ];
	
	return $manage_domain_alias_opt;
}

function set_add_domain_record()
{
	$manage_domain_alias_opt[ 'manage_domain_alias_flg' ] = $_POST[ 'manage_domain_alias_flg' ];
	$manage_domain_alias_opt[ 'domain_record_name' ] = $_POST[ '_add_domain_record_name' ];
	$manage_domain_alias_opt[ 'domain_record_type' ] = $_POST[ '_add_domain_record_type' ];
	$manage_domain_alias_opt[ 'domain_record_value' ] = $_POST[ '_add_domain_record_value' ];
	$manage_domain_alias_opt[ 'original_domain' ] = $_POST[ '_hidden_domain_name' ];
	
	return $manage_domain_alias_opt;
}

function set_delete_domain_record()
{
	$manage_domain_alias_opt[ 'manage_domain_alias_flg' ] = $_POST[ 'manage_domain_alias_flg' ];
	$manage_domain_alias_opt[ 'original_domain' ] = $_POST[ '_hidden_domain_name' ];
	$manage_domain_alias_opt[ 'domain_record_name' ] = $_POST[ '_hidden_domain_record_nm' ];
	
	return $manage_domain_alias_opt;
}

function set_delete_domain_alias()
{
	$manage_domain_alias_opt[ 'manage_domain_alias_flg' ] = $_POST[ 'manage_domain_alias_flg' ];
	$manage_domain_alias_opt[ 'domain_alias_name_str' ] = $_POST[ '_hidden_domain_name' ];
	
	return $manage_domain_alias_opt;
}

if( $RC_HELP->rules_ok )
{
	$email = $_POST[ '_add_user_rule_email' ];
	if( $RC_MAIL->publishSieve( $email ) )
	{
		$OUTPUT->show_message( 'successfullysaved', 'confirmation' );
	}
	else
	{
		$OUTPUT->show_message( 'Rule Saved in Database. Inbox Not Present for This User', 'error' );
	}
}

// display the form again
rcmail_overwrite_action('edit-manage'); // calls the inc file


