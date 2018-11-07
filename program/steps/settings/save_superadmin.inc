<?php

$CURR_SECTION = get_input_value('_superadmin', RCUBE_INPUT_POST);

$super_admin_opt = array();

global $RC_HELP, $RC_MAIL;

switch( $CURR_SECTION )
{
	case 'new_account':
	
		$super_admin_opt[ 'domain' ] = $_POST[ '_add_superadmin_dom_nm' ];
		$super_admin_opt[ 'max_users' ] = $_POST[ '_add_superadmin_max_users' ];
		$super_admin_opt[ 'inbox_size' ] = $_POST[ '_add_superadmin_inbox_size' ];
		
		$RC_HELP->validate_super_admin_new_acc( $super_admin_opt );
		
		break;
		
	case 'new_user': // for inserting only in users table
	
		$super_admin_opt[ 'user_name' ] = $_POST[ '_add_user_name' ];
		$super_admin_opt[ 'user_email_nm' ] = $_POST[ '_add_user_email_nm' ];
		$super_admin_opt[ 'user_email_dom' ] = $_POST[ '_add_user_email_dom' ];
		$super_admin_opt[ 'user_pwd' ] = $_POST[ '_add_user_pwd' ];
		$super_admin_opt[ 'user_quota' ] = $_POST[ '_add_user_quota' ];
		
		$RC_HELP->validate_super_admin_new_user( $super_admin_opt );
		
		break;	
		
	case 'new_admin':

		if( $_POST[ 'super_admin_flg' ] == 'Save' )
		{
			$super_admin_opt[ 'admin_domain' ] = $_POST[ '_add_new_admin_domain' ];
			$super_admin_opt[ 'admin_useremail' ] = $_POST[ '_add_new_admin_useremail' ];
			$super_admin_opt[ 'admin_manage_domain' ] = $_POST[ '_add_new_admin_manage_dom' ];
			
			$RC_HELP->validate_super_admin_new_adm( $super_admin_opt, $_POST[ 'super_admin_flg' ] );
		}
		else if( $_POST[ 'super_admin_flg' ] == 'Delete' )
		{	
			$super_admin_opt[ 'delete_admin_email_str' ] = $_POST[ '_hidden_admin_email' ];
			$super_admin_opt[ 'delete_admin_m_dom_str' ] = $_POST[ '_hidden_admin_manage_domain' ];
			
			$RC_HELP->validate_delete_admin_user( $super_admin_opt, $_POST[ 'edit_admin_user_flg' ] );
		}
		
		break;
		
}

rcmail_overwrite_action('edit-superadmin');