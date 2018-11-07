<?php
	
	class rc_help{
	
		var $rc = null;  // global roundcube object
		var $rcmail = null; // global rcmail object
		var $config = null;  // global config object
		var $_continue = null;
		var $email = null;
		var $domain = null;
		var $user_name = null;
		var $admin_ok = null;
		var $rules_ok = null;
		var $manage_add_ok = null;
		var $super_admin_ok = null;
		var $quota_array = null;
		var $block_header = null;
		var $folder_rule_header = null;
		var $fwd_rule_header_val = null;
		var $domain_status = null;
		var $identities_list = null;
		var $storage = null;
		var $show_update_form = null; // manage users
		var $show_delete_form = null; // manage users
		var $max_inbox_size_bt = null;
		// var $used_inbox_size_bt = null;
		var $max_users = null;
		// var $total_users = null;
		var $is_admin = null;
		var $is_admin_multiple = null;
		var $admin_selected_domain = null;
		var $is_super_admin = null;
		var $rc_sabre_help = null;
		
		function rc_help()
		{
			GLOBAL $CONFIG, $RC_SABRE_HELP;
			$this->rc_sabre_help = $RC_SABRE_HELP;
			$this->admin_ok = false;
			$this->rules_ok = false;
			$this->manage_add_ok = false;
			$this->super_admin_ok = false;
			$this->rc = rcube::get_instance();
			$this->rcmail = rcmail::get_instance();
			$this->config = $CONFIG;
			$this->_continue = $this->init_DB();
			$this->email = $this->rc->get_user_email();
			$this->domain = $this->rc->user->get_username('domain');
			$this->user_name = $this->rc->user->get_username('local');
			$this->admin_selected_domain = $this->get_selected_domain();
			$this->block_header = array( 'From', 'Subject' );
			$this->fwd_rule_header_val = array( 'All', 'From', 'Subject', 'To', 'CC' );
			$this->folder_rule_header = array( 'From', 'To', 'CC', 'Subject' );
			$this->domain_status = array( 0 => 'Not Active', 1 => 'Active' );
			$this->identities_list = $this->rc->user->list_identities();
			$this->storage =  $this->rc->get_storage();
			$this->show_update_form = false;
			$this->show_delete_form = false;
			$this->max_inbox_size_bt = (int)$this->get_inbox_size_in_bytes();
			// $this->used_inbox_size_bt = (int)$this->get_inbox_usage_in_bytes();
			$this->max_users = (int)$this->get_max_users();
			// $this->total_users = (int)$this->get_total_users();
			$_DB  = null;
			$this->check_is_admin();
			$this->check_admin_multiple_domain();
			// $this->quota_array = array( "1M" => "1MB", "5M" => "5MB", "10M" => "10MB", "50M" => "50MB", "100M" => "100MB", "400M" => "400MB", "500M" => "500MB", "1024M" => "1GB", "2048M" => "2GB", "5120M" => "5GB", "10240M" => "10GB", "20480M" => "20GB" );
			$this->quota_array = array( "100M" => "100MB", "400M" => "400MB", "500M" => "500MB", "1024M" => "1GB", "2048M" => "2GB", "5120M" => "5GB", "10240M" => "10GB", "20480M" => "20GB", "25600M" => "25GB", "30720M" => "30GB", "35840M" => "35GB", "40960M" => "40GB", "46080M" => "45GB", "51200M" => "50GB", "102400M" => "100GB" );
			
			$this->check_is_super_admin();
			
		}
		
		function init_DB()
		{
			if( !empty( $this->config[ 'db_mailx' ] ) ) {
				$this->_DB = rcube_db::factory($this->config['db_mailx'], '', false);
				$this->_DB->db_connect('w');
				
				if (!($db_error_msg = $this->_DB->is_error())) {
					return true;
				}
				else {
					return false;
				}
			}
		}
		
		function check_is_admin( $email = null )
		{
			$is_admin = false;
			$users_admin = $this->get_user_admin();
			$admin_user_email = $users_admin[ 'email' ];
			$check_email = ( ( $email ) ? ( $email ) : ( $this->email ) );
			
			for( $i = 0; $i < count( $admin_user_email ); $i++ )
			{
				if( $check_email == $admin_user_email[ $i ] )
				{
					$this->rc->config->set( 'is_admin', true ); // set the config variable
					$this->is_admin = true; // set the helper variable also
					// is_admin can be checked either by config or the rc_help object
					$is_admin = true;
					break;
				}
				else
				{
					$this->rc->config->set( 'is_admin', false ); // set the config variable
					$this->is_admin = false; // set the helper variable
					$is_admin = false;
				}
			}
			return $is_admin;
		}
		
		function check_is_super_admin( $where = null )
		{
			$super_admins = $this->rc->config->get( 'super_admin' );
			
			for( $i = 0; $i < count( $super_admins ); $i++ )
			{
				if( $this->email == $super_admins[ $i ] )
				{
					$this->is_super_admin = true;
					$this->rc->config->set( 'is_super_admin', true );
					break;
				}
				else
				{
					$this->is_super_admin = false;
					$this->rc->config->set( 'is_super_admin', false );
				}
			}
		}
		
		function check_is_user_created( $username )
		{
			$user_prefs = $this->rc->user->get_user_prefs_by_username( $username );
			if( ( isset( $user_prefs[ 'is_user_created' ] ) ) && ( $user_prefs[ 'is_user_created' ] == 1 ) )
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		
		function remove_is_user_created( $username )
		{
			$user_prefs = $this->rc->user->get_user_prefs_by_username( $username );
			
			if( ( isset( $user_prefs[ 'is_user_created' ] ) ) && ( $user_prefs[ 'is_user_created' ] == 1 ) )
			{
				unset( $user_prefs[ 'is_user_created' ] );
			}
			
			$serialized_prefs = serialize( $user_prefs );
			
			$this->rc->user->set_specific_user_pref( $serialized_prefs, $username );
		}
		
		function check_admin_multiple_domain()
		{
			$admin_multiple_domain = $this->get_user_admin( "username='".$this->user_name."' and mydomain='".$this->domain."' and email='".$this->email."'" );
			
			if( count( $admin_multiple_domain[ "email" ] ) > 1 )
			{
				$this->is_admin_multiple = true;
				$this->rc->config->set( 'is_admin_multiple', true );
			}
			else
			{
				$this->is_admin_multiple = false;
				$this->rc->config->set( 'is_admin_multiple', false );
			}
		}
		
		function get_user_admin( $where = false )
		{
			$ret_array = array();
			if( $this->_continue )
			{
				if( $where != false )
				{
					$_query = "select username, mydomain, email, managed_domain, manged_domain_is_selected from user_admin where ".$where.";";
				}
				else
				{
					$_query = "select username, mydomain, email, managed_domain, manged_domain_is_selected from user_admin";
				}
				
				if( $query_result = $this->_DB->query( $_query ) )
				{
					while ($row = $this->_DB->fetch_array($query_result)) {
						$ret_array[ 'username' ][] = $row[ 0 ];
						$ret_array[ 'mydomain' ][] = $row[ 1 ];
						$ret_array[ 'email' ][] = $row[ 2 ];
						$ret_array[ 'managed_domain' ][] = $row[ 3 ];
						$ret_array[ 'manged_domain_is_selected' ][] = $row[ 4 ];
					}
				}
			}
			return $ret_array;
		}
		
		function get_OutOfOffice_details( $email = null, $where = null )
		{
			$ret_array = array();
			if( $this->_continue )
			{
				$select_query = "select emailaddr, enabled, subject, message, oof_rule_count, header, filter from out_of_office where emailaddr = '".( ( $email ) ? ( $email ) : ( $this->email ) )."' ".( ( $where ) ? ( $where ) : ( "" ) )."; ";
				
				if( $query_result = $this->_DB->query( $select_query ) )
				{
					while ($row = $this->_DB->fetch_array($query_result)) {
						$ret_array[ 'emailaddr' ][] = $row[ 0 ];
						$ret_array[ 'enabled' ][] = $row[ 1 ];
						$ret_array[ 'subject' ][] = $row[ 2 ];
						$ret_array[ 'message' ][] = $row[ 3 ];
						$ret_array[ 'oof_rule_count' ][] = $row[ 4 ];
						$ret_array[ 'header' ][] = $row[ 5 ];
						$ret_array[ 'filter' ][] = $row[ 6 ];
					}
					return $ret_array;
				}
				else
				{
					return false;
				}
			}
		}
		
		function update_identities_with_uname($email) // if name in identities is blank , update it with the name in users table
		{
			if( $this->_continue )
			{
				$select_query = "select userid from users where email='$email'; ";
				
				$query_result = $this->_DB->query( $select_query );
				
				$row = $this->_DB->fetch_assoc($query_result);
				
				$name = $row['userid'];
				
				$update_query = "update webmail.identities set name='$name' where  email='$email';";

				$this->_DB->query( $update_query );
				if($updated = $this->_DB->affected_rows())
					return $name ;

				
				return false;
			}
		}
		
		function get_CustomRule_details( $email = null ) // retrives Custom Rule from the database
		{
			if( $this->_continue )
			{
				$select_query = "select emailaddr, enabled, description from custom_directive where emailaddr = '".( ( $email ) ? ( $email ) : ( $this->email ) )."'; ";
				$query_result = $this->_DB->query( $select_query );
				$result_arr = $this->_DB->fetch_assoc($query_result);
				
				if (!empty($result_arr)) {
					return $result_arr;
				}
				return false;
			}
		}
		
		function get_BlockEmail_details( $email = null ) // retrives Blocked Emails from the database
		{
			$ret_array = array();
			if( $this->_continue )
			{
				$select_query = "select emailaddr, block_count, header, filter from blocked_emails where emailaddr = '".( ( $email ) ? ( $email ) : ( $this->email ) )."'; ";
				if( $query_result = $this->_DB->query( $select_query ) )
				{
					while ($row = $this->_DB->fetch_array($query_result)) {
						$ret_array[ 'block_count' ][] = $row[ 1 ];
						$ret_array[ 'header' ][] = $row[ 2 ];
						$ret_array[ 'filter' ][] = $row[ 3 ];
					}
					return $ret_array;
				}
				else
					return false;
			}
		}
		
		function get_ForwardRule_details() // retrives forward Rules from the database
		{
			$ret_array = array();
			if( $this->_continue )
			{
				$select_query = "select fw_rules_count , header , filter , forward_to_email from forward_rules where emailaddr = '$this->email'; ";
				if( $query_result = $this->_DB->query( $select_query ) )
				{
	
					
					while ($row = $this->_DB->fetch_array($query_result)) {
						$ret_array[ 'fw_rules_count' ][] = $row[ 0 ];
						$ret_array[ 'header' ][] = $row[ 1 ];
						$ret_array[ 'filter' ][] = $row[ 2 ];
						$ret_array[ 'fw_to_email' ][] = $row[ 3 ];
						
					}
					return $ret_array;
				}
				else
					return false;
			}
		}
		
		function get_FolderRule_details( $folder_name = "" )
		{
			
			$ret_array = array();
			if( $this->_continue )
			{
				// strlen( $folder_name ) == 0 retrive all enabled rule folder details
				$where_condt = ( ( strlen( $folder_name ) == 0 ) ? ( "where emailaddr = '$this->email' and enabled = 1;" ) : ( "where emailaddr = '$this->email' and folder_name = '$folder_name';" ) );
				
				$select_query = "select emailaddr, folder_name, enabled, filter, filter_match from folder_rule ".$where_condt;
			
				if( $query_result = $this->_DB->query( $select_query ) )
				{
					if( strlen( $folder_name ) == 0 ) // if all folder details are to be retrived.. RETURNS ARRAY
					{
						while ($row = $this->_DB->fetch_array($query_result)) {
							$ret_array[ 'folder_name' ][] = $row[ 1 ];
							$ret_array[ 'enabled' ][] = $row[ 2 ];
							$ret_array[ 'filter' ][] = $row[ 3 ];
							$ret_array[ 'filter_match' ][] = $row[ 4 ];
						}
						
						return $ret_array;
					}
					else // retrive only the details for specific folder.. NO ARRAY RETURNED
					{
						$result_arr = $this->_DB->fetch_assoc($query_result);
					
						if (!empty($result_arr)) {
							return $result_arr;
						}
					}
				}
				else
					return false;
			
			}
		}
		
		function get_FwdRule_details( $email = null )
		{
			$ret_array = array();
			if( $this->_continue )
			{
				$select_query = "select fw_rules_count, header, filter, forward_to_email from forward_rules where emailaddr='".( ( $email ) ? ( $email ) : ( $this->email ) )."';";
				
				if( $query_result = $this->_DB->query( $select_query ) )
				{
					while ($row = $this->_DB->fetch_array($query_result)) {
						$ret_array[ 'fw_rules_count' ][] = $row[ 0 ];
						$ret_array[ 'header' ][] = $row[ 1 ];
						$ret_array[ 'filter' ][] = $row[ 2 ];
						$ret_array[ 'forward_to_email' ][] = $row[ 3 ];
					}
					
					return $ret_array;
				}
				else
					return false;
			}
		}
		
		function get_user_details( $selected_domain = null )
		{
			$ret_array = array();
			if( $this->_continue )
			{
				if( $selected_domain )
				{
					$_query = "select userid, email, isprev, inbox_quota, deliver_quota from users where mydomain = '".$selected_domain."' order by userid;";
				}
				else
				{
					$_query = "select userid, email, isprev, inbox_quota, deliver_quota from users order by userid;";
				}
				
				if( $query_result = $this->_DB->query( $_query ) )
				{
					// order: 0 richa, 1 richa@mgtech.in, 2 pwd, 3 0priviledged, 4 500MB, 5 *:storage=512000
					while ($row = $this->_DB->fetch_array($query_result)) {
						$ret_array["user_name"][] = $row[0];
						$ret_array["user_email"][] = $row[1];
						$ret_array["user_prev"][] = $row[2];
						$ret_array["inbox_quota"][] = $row[3];
						$ret_array["deliver_quota"][] = $row[4];
					}
					return $ret_array;
				}
				else
					return false;
			
			}
		}
		
		function get_list_details( $selected_domain = null )
		{
			$ret_array = array();
			if( $this->_continue )
			{
				$_query = "select email_list, is_private, mydomain from admin_lists where mydomain = '".( ( $selected_domain ) ? ( $selected_domain ) : ( $this->domain ) )."' order by email_list;";
				if( $query_result = $this->_DB->query( $_query ) )
				{
					while ($row = $this->_DB->fetch_array($query_result)) {
						$ret_array["email_list"][] = $row[0];
						$ret_array["is_private"][] = $row[1];
						$ret_array["mydomain"][] = $row[2];
					}
					return $ret_array;
				}
				else
					return false;
			}
		}
		
		function get_list_member_details( $list_name )
		{
			$ret_array = array();
			if( $this->_continue )
			{
				$_query = "select email_src, email_dest, mydomain, isaliase from user_aliases where mydomain = '".$this->admin_selected_domain."' and isaliase=0 and email_src='".$list_name."' order by email_dest;";
				if( $query_result = $this->_DB->query( $_query ) )
				{
					while ($row = $this->_DB->fetch_array($query_result)) {
						$ret_array["email_src"][] = $row[0];
						$ret_array["email_dest"][] = $row[1];
						$ret_array["mydomain"][] = $row[2];
						$ret_array["isaliase"][] = $row[3];
					}
					return $ret_array;
				}
				else
					return false;
			}
		}
		
		function get_list_non_exis_member_det( $list_name )
		{
			$ret_array = array();
			if( $this->_continue )
			{
				$_query = "select email from users where email not in ( select email_dest from user_aliases where mydomain = '".$this->admin_selected_domain."' and isaliase=0 and email_src='".$list_name."' order by email_dest ) and mydomain = '".$this->admin_selected_domain."';";
				if( $query_result = $this->_DB->query( $_query ) )
				{
					while ($row = $this->_DB->fetch_array($query_result)) {
						$ret_array["email"][] = $row[0];
					}
					return $ret_array;
				}
				else
					return false;
			}
		}
		
		function get_user_aliases( $selected_domain = null )
		{
			$ret_array = array();
			if( $this->_continue )
			{
				$_query = "select email_src, email_dest, mydomain, isaliase from user_aliases where isaliase=1 and mydomain='".( ( $selected_domain ) ? ( $selected_domain ) : ( $this->domain ) )."' order by email_dest;";
				if( $query_result = $this->_DB->query( $_query ) )
				{
					while ($row = $this->_DB->fetch_array($query_result)) {
						$ret_array["email_src"][] = $row[0];
						$ret_array["email_dest"][] = $row[1];
						$ret_array["mydomain"][] = $row[2];
						$ret_array["isaliase"][] = $row[3];
					}
					return $ret_array;
				}
				else
					return false;
			}	
		}
		
		function get_user_det_by_email( $email )
		{
			$ret_array = array();
			if( $this->_continue )
			{
				$_query = "select userid, email, isprev, inbox_quota, deliver_quota from users where email = '$email';";
				if( $query_result = $this->_DB->query( $_query ) )
				{
					while ($row = $this->_DB->fetch_assoc($query_result)) {
						$ret_array["user_name"] = $row["userid"];
						$ret_array["user_email"] = $row["email"];
						$ret_array["user_prev"] = $row["isprev"];
						$ret_array["inbox_quota"] = $row["inbox_quota"];
						$ret_array["deliver_quota"] = $row["deliver_quota"];
					}
					return $ret_array;
				}
				else
					return false;
			}
		}
		
		function get_webmail_usrid( $email )
		{
			$_query = "select user_id from webmail.users where username = '$email';";
			$res = $this->_DB->query( $_query );
			$result_arr = $this->_DB->fetch_assoc($res);
			return $result_arr[ "user_id" ];
		}
		
		function get_user_count( $selected_domain = null )
		{
			$_query = "select count(*) as cnt from users where mydomain = '".( ( $selected_domain ) ? ( $selected_domain ) : ( $this->domain ) )."';";
			$res = $this->_DB->query( $_query );
			$result_arr = $this->_DB->fetch_assoc($res);
			return $result_arr[ "cnt" ];
		}
		
		function get_list_count( $selected_domain = null )
		{
			$_query = " select count(*) as cnt from admin_lists where mydomain = '".( ( $selected_domain ) ? ( $selected_domain ) : ( $this->domain ) )."' order by email_list;";
			$res = $this->_DB->query( $_query );
			$result_arr = $this->_DB->fetch_assoc($res);
			return $result_arr[ "cnt" ];
		} 
		
		function get_aliases_count( $selected_domain = null )
		{
			$_query = " select count(*) as cnt from user_aliases where mydomain = '".( ( $selected_domain ) ? ( $selected_domain ) : ( $this->domain ) )."' and isaliase=1;";
			$res = $this->_DB->query( $_query );
			$result_arr = $this->_DB->fetch_assoc($res);
			return $result_arr[ "cnt" ];
		}
		
		function get_inbox_size_in_bytes()
		{
			$_query = "select inbox_size_bt from domain_limits where dom_name='".$this->admin_selected_domain."'";
			
			$res = $this->_DB->query( $_query );
			$result_arr = $this->_DB->fetch_assoc( $res );
			return $result_arr[ 'inbox_size_bt' ];
		}
		
		/* function get_inbox_usage_in_bytes()
		{
			$_query = "select inbox_usage_bt from domain_usage where dom_name = '".$this->admin_selected_domain."'";
			$res = $this->_DB->query( $_query );
			$result_arr = $this->_DB->fetch_assoc( $res );
			return $result_arr[ 'inbox_usage_bt' ];
		} */
		
		function get_max_users()
		{
			$_query = "select max_users from domain_limits  where dom_name='".$this->admin_selected_domain."'";
			
			$res = $this->_DB->query( $_query );
			$result_arr = $this->_DB->fetch_assoc( $res );
			return $result_arr[ 'max_users' ];
		}
		
		function get_user_quota( $email )
		{
			$_query = "select inbox_quota from users where email = '".$email."' and mydomain='".$this->admin_selected_domain."'";
			
			$res = $this->_DB->query( $_query );
			$result_arr = $this->_DB->fetch_assoc( $res );
			
			return $this->get_bytes( $result_arr[ 'inbox_quota' ] );
		}
		
		/* function get_total_users()
		{
			$_query = "select total_users from domain_usage where dom_name = '".$this->admin_selected_domain."';";
			$res = $this->_DB->query( $_query );
			$result_arr = $this->_DB->fetch_assoc( $res );
			return $result_arr[ 'total_users' ];
		} */
		
		function get_list_memeber_count( $list_name )
		{
			$_query = "select count(*) as cnt from user_aliases where mydomain = '".$this->admin_selected_domain."' and isaliase=0 and email_src='".$list_name."' order by email_dest;";
			$res = $this->_DB->query( $_query );
			$result_arr = $this->_DB->fetch_assoc($res);
			return $result_arr[ "cnt" ];
		}
		
		function get_list_non_exis_member( $list_name )
		{
			$_query = "select count(*) as cnt from users where email not in ( select email_dest from user_aliases where mydomain = '".$this->admin_selected_domain."' and isaliase=0 and email_src='".$list_name."' order by email_dest ) and mydomain = '".$this->admin_selected_domain."';";
			$res = $this->_DB->query( $_query );
			$result_arr = $this->_DB->fetch_assoc($res);
			return $result_arr[ "cnt" ];
		}
		
		function get_domain_aliases( $selected_domain = null, $include_main_domain = true )
		{
			$ret_array = array();
			if( $this->_continue )
			{
				// first value is always the actual domain name, which is not retrieved from select query
				if( $include_main_domain == true )
				{
					$ret_array["alias_domain"][] = ( ( $selected_domain ) ? ( $selected_domain ) : ( $this->domain ) );
				}
				
				$select_query = "select alias_domain, domain_status, publish_state from domain_aliases where org_domain='".( ( $selected_domain ) ? ( $selected_domain ) : ( $this->domain ) )."';";
				
				if( $query_result = $this->_DB->query( $select_query ) )
				{
					while ($row = $this->_DB->fetch_array($query_result)) {
						$ret_array["alias_domain"][] = $row[0];
						$ret_array["domain_status"][] = $row[1];
						$ret_array["publish_state"][] = $row[2];
					}
					return $ret_array;
				}
			}
			
			return false ;
		}
		
		/* function get_account_domains( $selected_domain = null )
		{
			$ret_array = array();
			if( $this->_continue )
			{
				$select_query = "select mydomain from admin_domains where mydomain='".( ( $selected_domain ) ? ( $selected_domain ) : ( $this->domain ) )."' union select alias_domain from domain_aliases where org_domain='".( ( $selected_domain ) ? ( $selected_domain ) : ( $this->domain ) )."'";
				
				if( $query_result = $this->_DB->query( $select_query ) )
				{
					while ($row = $this->_DB->fetch_array($query_result)) {
						$ret_array[] = $row[0];
					}
					return $ret_array;
				}
			}
		} */
		
		function get_domain_records( $selected_domain = null )
		{
			$ret_array = array();
			if( $this->_continue )
			{
				$select_query = "select dom_name, rec_name, rec_type, rec_value from domain_record where dom_name='".( ( $selected_domain ) ? ( $selected_domain ) : ( $this->domain ) )."';";
				
				if( $query_result = $this->_DB->query( $select_query ) )
				{
					while ($row = $this->_DB->fetch_array($query_result)) {
						$ret_array["dom_name"][] = $row[0];
						$ret_array["rec_name"][] = $row[1];
						$ret_array["rec_type"][] = $row[2];
						$ret_array["rec_value"][] = $row[3];
					}
					return $ret_array;
				}
			}
		}
		
		
		function get_all_domains()
		{
			$ret_array = array();
			if( $this->_continue )
			{
				// $select_query = "select distinct org_domain from domain_aliases;";
				$select_query = "select dom_name from domain_limits;";
				if( $query_result = $this->_DB->query( $select_query ) )
				{
					while ($row = $this->_DB->fetch_array($query_result)) {
						$ret_array[ 'dom_name' ][] = $row[ 0 ];
					}
					return $ret_array;
				}
				else
					return false;
			}
		}
		
		function get_selected_domain( $email = null )
		{
			$em = ( ( $email ) ? ( $email ) : ( $this->email ) );
			$exploded_email = explode( "@", $em );
			$user_name = $exploded_email[ 0 ];
			$domain = $exploded_email[ 1 ];
			
			$query = "select managed_domain from user_admin where username = '".$user_name."' and mydomain = '".$domain."' and email = '".$em."' and manged_domain_is_selected = 1;";
			$res = $this->_DB->query( $query );
			$result_arr = $this->_DB->fetch_assoc($res);
			if( empty( $result_arr ) )
			{
				return null;
			}
			else
			{
				return $result_arr[ "managed_domain" ];
			}
		}
		
		function reset_admin_pwd( $admin_options, &$err_msg ) // Code to reset admin password
		{
			if( $this->_continue )
			{
				$user_password = $this->rc->get_user_password(); // old password
				$curr_password = $admin_options[ '_curr_admin_pwd' ]; // current password
				$new_password = $admin_options[ '_conf_admin_pwd' ]; // new password
				
				if( $curr_password !== $user_password )
				{
					$err_msg = 'Invalid Current Password';
					return false;
				}
				else
				{
					$update_query = "update users set password=Encrypt('$new_password'), webpassword=AES_Encrypt('$new_password','cmail') where  email = '".$this->email."' and mydomain='".$this->domain."';";
					$ret_pwd_query = "select count(*) as cnt from users where email = '".$this->email."' and mydomain='".$this->domain."';";
					$sql_result = $this->_DB->query( $ret_pwd_query );
					$sql_arr = $this->_DB->fetch_assoc($sql_result);
					
					if (!empty($sql_arr)) {
						$count = $sql_arr[ 'cnt' ];
					}
					if( $count == 1 )
					{
						$this->_DB->query( $update_query );
						$updated = $this->_DB->affected_rows();
					}
					if( $updated )
					{
						// Macgregor Changes
						if( $this->rc_sabre_help->update_sabredav_password( $this->email, $new_password, $err_msg ) == true )
						{
							// set the session password as new password
							$_SESSION[ 'password' ] = $this->rcmail->encrypt( $new_password );
							$this->rc_sabre_help->update_shared_calendar_password( $this->email, $new_password );
							$this->admin_ok = true;
							return true;
						}
						else
						{
							$this->admin_ok = false;
							$err_msg = "Error Occured While Updating Password from Sabredav database";
							return false;
						}
					}
					else
					{
						$this->admin_ok = false;
						$err_msg = "Error Occured While Updating Password";
						return false;
					}
					
				}
			}
			else
			{
				$err_msg = "Error occured while connecting to the Database";
				return false;
			}
		}
		
		function save_email_notification( $admin_options, &$err_msg )
		{
			if( $this->_continue )
			{
				$email = $admin_options[ '_email_notify_txtbox' ];
				$query = "";
				return true;
			}
			else
			{
				$err_msg = "Error Occured While Connecting to Database.";
				return false;
			}
		}
		
		function save_out_of_office( $rules_options, $email = null, &$err_msg ) // Code to save Out Of Office credentials into database
		{
			$enable = $rules_options[ '_out_of_office_enable' ];
			$header = $rules_options[ '_out_of_office_header' ];
			$match = $rules_options[ '_out_of_office_match' ];
			$subject = $rules_options[ '_out_of_office_subject' ];
			$message = $rules_options[ '_out_of_office_message' ];
			
			$unsuccessful_insert = array(); // will contain the subject of the rules which weren't updated
			
			$oof_rules_count = count( $enable );
			
			if( $this->delete_rules( $email, 'out_of_office' ) )
			{
				for( $i = 0; $i < $oof_rules_count; $i++ )
				{
					if( $this->_continue )
					{
						$insert_query = "insert into out_of_office( emailaddr, oof_rule_count, enabled, header, filter, subject, message ) values( '".( ( $email ) ? ( $email ) : ( $this->email ) )."', $i, ".$enable[ $i ].", ".$header[ $i ].", '".$match[ $i ]."', '".$subject[ $i ]."', '".$message[ $i ]."' ) ON DUPLICATE KEY UPDATE emailaddr='".( ( $email ) ? ( $email ) : ( $this->email ) )."', oof_rule_count=$i, enabled=".$enable[ $i ].", header=".$header[ $i ].", filter='".$match[ $i ]."', subject='".$subject[ $i ]."', message='".$message[ $i ]."' ; ";
						
						$this->_DB->query( $insert_query );
						$updated = $this->_DB->affected_rows();
						if( $updated )
						{
							$this->rules_ok = true;
							$continue = true;
						}
						else
						{
							$unsuccessful_insert[] = $subject[ $i ];
							$this->rules_ok = false;
							$continue = false;
						}
					}				
				}
			}
			if( $continue )
			{
				return true;
			}
			else
			{
				$err_msg = "Couldnot Update Rule for User: ".implode( ", ", $unsuccessful_insert );
				return false;
			}
			
			
			/* $enable = ( $rules_options[ '_out_of_office_enable' ] == '1' ) ? ( 1 ) : ( 0 );
			$subject = $rules_options[ '_out_of_office_sub' ];
			$message = addslashes( $rules_options[ '_out_of_office_message' ] );
			
			
			if( $this->_continue )
			{
				$query = "insert into out_of_office( emailaddr, enabled, subject, message ) values( '".( ( $email ) ? ( $email ) : ( $this->email ) )."', $enable, '$subject', '$message' ) on duplicate key update emailaddr='".( ( $email ) ? ( $email ) : ( $this->email ) )."', enabled=$enable, subject='$subject', message='$message'; ";
				
				$this->_DB->query( $query );
				$updated = $this->_DB->affected_rows();
				
				if( $updated )
				{
					$this->rules_ok = true;
					return true;
				}
				else
				{
					$this->rules_ok = false;
					$err_msg = "Error Occured While Saving Details";
					return false;
				}
			}
			else
			{
				$err_msg = "Error Occured connecting to Database";
				return false;
			} */
		}
		
		// TODO: change the code to delete queries single function
		//delete_all_forward_rules
		function delete_all_blocked_emails( $email = null ) // before adding Blocked Email, the older blocked emails are deleted first and then new values are entered
		{
			$delete_query = "delete from blocked_emails where emailaddr='".( ( $email ) ? ( $email ) : ( $this->email ) )."';";
			return ( $this->_DB->query( $delete_query ) );
		}
		
		function delete_all_forward_rules( $email = null ) // before adding Blocked Email, the older blocked emails are deleted first and then new values are entered
		{
			$delete_query = "delete from forward_rules where emailaddr='".( ( $email ) ? ( $email ) : ( $this->email ) )."';";
			return ( $this->_DB->query( $delete_query ) );
		}
		
		function delete_all_oof_rules( $email )
		{
			$delete_query = "delete from out_of_office where emailaddr='".( ( $email ) ? ( $email ) : ( $this->email ) )."';";
			return ( $this->_DB->query( $delete_query ) );
		}
		
		function delete_rules( $email, $table_name )
		{
			$delete_query = "delete from $table_name where emailaddr='".( ( $email ) ? ( $email ) : ( $this->email ) )."';";
			return ( $this->_DB->query( $delete_query ) );
		}
		
		function save_block_emails( $rules_options, $email = null, &$err_msg ) // code to save Blocked emails. Delete Blocked Email is called first
		{
			$header_arr = $rules_options[ '_block_email_header' ];
			$filter_arr = $rules_options[ '_block_email_filter' ];
			$block_count = count( $filter_arr );
			
			$continue = false;
			
			if( $this->_continue )
			{
				if( $this->delete_all_blocked_emails( $email ) ) // delete all previous blocked emails then insert new values
				{
					for( $i = 0; $i < $block_count; $i++ )
					{
						$insert_query = "insert into blocked_emails( emailaddr, block_count, header, filter ) values( '".( ( $email ) ? ( $email ) : ( $this->email ) )."', $i, '".$header_arr[ $i ]."', '".$filter_arr[ $i ]."' );";
					
						$this->_DB->query( $insert_query );
						$updated = $this->_DB->affected_rows();
						
						if( $updated )
						{
							$this->rules_ok = true;
							$continue = true;
						}
						else
						{
							$this->rules_ok = false;
							$err_msg = "Error Occured While Saving Details";
							$continue = false;
						}
					}
				}
				
				if( $continue )
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}
		
		function save_forward_rules( $rules_options, $email = null, &$err_msg ) // code to save Blocked emails. Delete Blocked Email is called first
		{
			$header_arr = $rules_options[ '_forward_rule_header' ];
			$filter_arr = $rules_options[ '_forward_rule_filter' ];
			$fw_email_arr = $rules_options[ '_forward_rule_email' ];
			$block_count = count( $filter_arr );
			
			$continue = false;
			
			if( $this->_continue )
			{
				if( $this->delete_all_forward_rules( $email ) ) // delete all previous forward rules & then insert new values
				{
					for( $i = 0; $i < $block_count; $i++ )
					{
						//$insert_query = "insert into blocked_emails( emailaddr, block_count, header, filter ) values( '".$this->email."', $i, '".$header_arr[ $i ]."', '".$filter_arr[ $i ]."' );";
						
						$insert_query = "insert into forward_rules( emailaddr, fw_rules_count, header, filter , forward_to_email ) values( '".( ( $email ) ? ( $email ) : ( $this->email ) )."', $i, '".$header_arr[ $i ]."', '".$filter_arr[ $i ]."' , '".$fw_email_arr[ $i ]."');";
					
						$this->_DB->query( $insert_query );
						$updated = $this->_DB->affected_rows();
						
						if( $updated )
						{
							$this->rules_ok = true;
							$continue = true;
						}
						else
						{
							$this->rules_ok = false;
							$err_msg = "Error Occured While Saving Details";
							$continue = false;
						}
					}
				}
				
				if( $continue )
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}
		
		
		function save_custom_directive( $rules_options, $email = null, &$err_msg ) // Code to save Custom Rule Details
		{
			$enable = ( $rules_options[ '_custom_rule_enable' ] == '1' ) ? ( 1 ) : ( 0 );
			$description = addslashes( $rules_options[ '_custom_rule_desc' ] );
			
			if( $this->_continue )
			{
				$query = "insert into custom_directive( emailaddr, enabled, description ) values( '".( ( $email ) ? ( $email ) : ( $this->email ) )."', $enable, '$description' ) on duplicate key update emailaddr='".( ( $email ) ? ( $email ) : ( $this->email ) )."', enabled=$enable, description='$description'; ";
					
				$this->_DB->query( $query );
				$updated = $this->_DB->affected_rows();
				
				if( $updated )
				{
					$this->rules_ok = true;
					return true;
				}
				else
				{
					$this->rules_ok = false;
					$err_msg = "Error Occured While Saving Details";
					return false;
				}
			}
			else
			{
				$err_msg = "Error Occured Connecting to database";
				return false;
			}
		}
		
		function save_folder_rule( $folder_name, $rule_enabled, $rule_filter, $rule_filter_match, $folder_old_name, &$err_msg, $update = false )
		{
			$err_msg = "Error Saving Folder Rule in DB"; // default error message
			
			if( ( strpos($rule_filter_match, "'") !== false ) || ( strpos($rule_filter_match, '"') !== false ) )
			{
				$err_msg = "Filter To Header Should not contain Inverted Commas";
			}
			else
			{
				global $RC_MAIL;
				$update = ( ( strlen( $folder_old_name ) == 0 ) ? ( false ) : ( true ) );
				if( $this->_continue )
				{
					if( $update ) // if folder is being updated, check if db value is present for that folder
					{
						$folder_rule_details = $this->get_FolderRule_details( $folder_old_name ); // this retrives the folder_rule from db
						if( count( $folder_rule_details[ 'folder_name' ] ) != 0 )// i.e. if folder rule exists in db, update it
						{
							$query = "update folder_rule set folder_name='$folder_name', enabled='$rule_enabled', filter=$rule_filter, filter_match='$rule_filter_match' where folder_name='$folder_old_name';";
						}
						else // insert record
						{
							$query = "insert into folder_rule( emailaddr, folder_name, enabled, filter, filter_match ) values( '".$this->email."', '$folder_name', $rule_enabled, '$rule_filter', '$rule_filter_match' ) ON DUPLICATE KEY UPDATE emailaddr = '".$this->email."', folder_name='$folder_name', enabled = $rule_enabled, filter = $rule_filter, filter_match = '$rule_filter_match'; ";
						}
					}
					else // while creating new folder
					{
						$query = "insert into folder_rule( emailaddr, folder_name, enabled, filter, filter_match ) values( '".$this->email."', '$folder_name', $rule_enabled, '$rule_filter', '$rule_filter_match' ) ON DUPLICATE KEY UPDATE emailaddr = '".$this->email."', folder_name='$folder_name', enabled = $rule_enabled, filter = $rule_filter, filter_match = '$rule_filter_match'; ";
					}
					
					$this->_DB->query( $query );
					$updated = $this->_DB->affected_rows();
					
					if( $updated )
					{
						if( $RC_MAIL->publishSieve() )
						{
							return true;
						}
						else
						{
							return false;
						}
					}
					else
					{
						return false;
					}
				}
				else
					return false;
			}
		}
        
        function share_folder( $share_with_user, $folder_old_name, $folder_name )
        {
            $is_new = ( ( strlen( $folder_old_name ) == 0 ) && ( strlen( $hidden_folder_id ) == 0 ) ) ? ( true ) : ( false );
            $user_id = $_SESSION[ 'user_id' ]; 
            
            if( ( $this->folder_sharing_exists( $folder_old_name ) ) && !$is_new ) // if folder is editted and there is entry for folder sharing, then execute update query.
            {
                $_query = "update folder_sharing set folder_name = '$folder_name' , shared_with_email='$share_with_user' where folder_name = '$folder_old_name';";
            }
            else // if editted folder and folder sharing does not exist, or if $is_new is true, add query
            {
                $_query = "insert into folder_sharing(user_id, folder_name, shared_with_email) values( '$user_id', '$folder_name', '$share_with_user' );";
            }
            
            $this->_DB->query( $_query );
			
			if( $updated = $this->_DB->affected_rows() )
			{
                return true;
            }
            else
                return false;
        }
        
        function folder_sharing_exists( $folder_name )
        {
            $_query = "select count(*) as cnt from folder_sharing where folder_name ='$folder_name';";
            if( $query_result = $this->_DB->query( $_query ) )
            {
                $row = $this->_DB->fetch_assoc($query_result);
                $cnt = intval($row['cnt']);
                
                return ( $cnt == 0 ) ? ( false ) : ( true );
            }
        }
        
        function get_shared_with_user( $folder_name )
        {
            $_query = "select shared_with_email from folder_sharing where folder_name ='$folder_name';";
            if( $query_result = $this->_DB->query( $_query ) )
            {
                $row = $this->_DB->fetch_assoc($query_result);
                
                return $row[ 'shared_with_email' ];
            }
        }
        
        function delete_folder_sharing( $folder_name )
        {
            $_query = "delete from folder_sharing where folder_name = '$folder_name';";
            
            return ( $this->_DB->query( $_query ) ) ? ( true ) : ( false );
            
        }
		
		function delete_folder_rule( $folder_list, $folder_name )
		{
			global $RC_MAIL;
			$root_folder_name = $this->extract_folder_name( $folder_name );
			$folder_list_cnt = count( $folder_list );
			$folder_name_arr = array();
			
			// first element of folder_list arr is alway INBOX. so loop thru the folder list. if inbox present, take the index and remove it.
			for( $i = 0; $i < $folder_list_cnt; $i++ )
			{
				if( $folder_list[ $i ] == 'INBOX' )
				{
					$key = array_search( 'INBOX', $folder_list );
					unset( $folder_list[ 0 ] );
				}
			}
			
			$folder_list = array_values( $folder_list );
			
			//deleting the root folder
			$delete_query = "delete from folder_rule where folder_name = '$root_folder_name';";
			
			if( $this->_DB->query( $delete_query ) )
			{
				if( $RC_MAIL->publishSieve() )
				{
					$continue = true;
				}
				else
				{
					$continue = false;
				}
			}
			else
			{
				$continue = false;
			}
			
			// deleting sub folders
			foreach( $folder_list as $folder_name )
			{
				$delete_query = "delete from folder_rule where folder_name = '$folder_name';";
				
				if( $this->_DB->query( $delete_query ) )
				{
					if( $RC_MAIL->publishSieve() )
					{
						$continue = true;
					}
					else
					{
						$continue = false;
						break;
					}
				}
				else
				{
					$continue = false;
					break;
				}
			}
			
			return $continue;
		}
		
		function add_account_selection( $manage_accounts_opt )
		{
			GLOBAL $OUTPUT;
			$domain = $manage_accounts_opt[ "_account_selection_domain" ];
			
			$update_query = "update user_admin set manged_domain_is_selected = 1 where username = '".$this->user_name."' and mydomain = '".$this->domain."' and email = '".$this->email."' and managed_domain = '".$domain."';";
			
			if( $this->_continue )
			{
				$this->_DB->query( $update_query );
				if( $updated = $this->_DB->affected_rows() )
				{
					$update_query1 = "update user_admin set manged_domain_is_selected = 0 where username = '".$this->user_name."' and mydomain = '".$this->domain."' and email = '".$this->email."' and managed_domain != '".$domain."';";
					
					$this->_DB->query( $update_query1 );
					if( $updated = $this->_DB->affected_rows() )
					{
						$this->admin_selected_domain = $domain;
						$OUTPUT->show_message( "Account Selected Successfully", 'confirmation' );
					}
					else
					{
						$OUTPUT->show_message( "Error Occured While Changing Selected Account", 'error' );
					}
				}
			}
		}
		
		function add_domain_alias( $manage_domain_alias_opt, &$saved, &$err_msg )
		{
			/* ADDS ENTRY TO DOMAIN_ALIASES TABLE */
			global $RC_MAIL;
			$alias_domain = $manage_domain_alias_opt[ 'domain_alias_name' ];
			//publish state set to 0 by default, because domain is published, while saving itself.
			$insert_query = "insert into domain_aliases( org_domain, alias_domain, domain_status, publish_state ) values( '".$this->admin_selected_domain."', '".$alias_domain."', 0, 0 ) ON DUPLICATE KEY UPDATE org_domain='".$this->admin_selected_domain."', alias_domain='".$alias_domain."', domain_status=0, publish_state=0;";
			
			$this->_DB->query( $insert_query );
			
			if( $updated = $this->_DB->affected_rows() )
			{
				$insert_query2 = "insert into virtual_domains( virtual ) values( '$alias_domain' ) ON DUPLICATE KEY UPDATE virtual='$alias_domain';";
				
				$this->_DB->query( $insert_query2 );
				if( $updated = $this->_DB->affected_rows() )
				{
					if( $RC_MAIL->publishDomain( $alias_domain ) == true )
					{
						$saved = true;
						$this->manage_add_ok = true; 
					}
					else
					{
						$saved = false;
						$err_msg = "Account Domain Added to Database, But Couldnot create BytemarkDNS file.";
					}
				}
				else
				{
					$err_msg = "Error Adding Values to the Virtual Domains Table";
					$saved = false;
				}
			}
			else
			{
				$err_msg = "Error Adding Values to the Database";
				$saved = false;
			}
		}
		
		function add_domain_record( $manage_domain_alias_opt, &$saved, &$err_msg )
		{
			/* INSERT DATA INTO DOMAIN_RECORD TABLE */
			global $RC_MAIL;
			$continue = false;
			$original_domain = $manage_domain_alias_opt[ 'original_domain' ];
			$domain_record_name = $manage_domain_alias_opt[ 'domain_record_name' ];
			$domain_record_type = $manage_domain_alias_opt[ 'domain_record_type' ];
			$domain_record_value = $manage_domain_alias_opt[ 'domain_record_value' ];
			$count = count( $domain_record_type );
			
			for( $i = 0; $i < $count; $i++ )
			{
				// if the user doesnot specifies the domain record name, add @ symbol
				if( $domain_record_name[ $i ] == "" )
				{
					$domain_record_name[ $i ] = "@";
				}
				$insert_query = "insert into domain_record( dom_name, rec_name, rec_type, rec_value ) values( '".$original_domain."', '".$domain_record_name[ $i ]."', '".$domain_record_type[ $i ]."', '".$domain_record_value[ $i ]."' ) ON DUPLICATE KEY UPDATE dom_name='".$original_domain."', rec_name='".$domain_record_name[ $i ]."', rec_type='".$domain_record_type[ $i ]."', rec_value='".$domain_record_value[ $i ]."';";
				$this->_DB->query( $insert_query );
				$updated = $this->_DB->affected_rows();
				
				if( $updated )
				{
					$this->show_update_form = true;
					$continue = true;
				}
				else
				{
					$this->manage_add_ok = false;
					$continue = false;
				}
			}
			
			if( $continue )
			{
				if( $RC_MAIL->publishDomain( $original_domain ) == true )
				{
					$this->manage_add_ok = true;
					$saved = true;
				}
				else
				{
					$saved = false;
					$err_msg = "Domain Record Added in Database, but couldnot Modify BytemarDNS File";
				}
			}
			else
			{
				$saved = false;
				$err_msg = "Error Adding Domain Record into Database";
			}
		}
		
		function add_account( $super_admin_opt, &$saved, &$err_msg )
		{
			// changes made 19-th-feb .. insert table changed from domain_limits to virtual_domain
			$insert_query1 = "insert into domain_limits( dom_name, max_emails_sent, max_byte_sent, max_users, inbox_size, inbox_size_bt, enable_sms, enable_google ) values( '".$super_admin_opt[ "domain" ]."', 0, 0, ".$super_admin_opt[ "max_users" ].", '".$super_admin_opt[ "inbox_size" ]."GB', ".$this->get_bytes( $super_admin_opt[ "inbox_size" ]."GB" ).", 0, 0 );";
			$this->_DB->query( $insert_query1 );
			
			if( $updated1 = $this->_DB->affected_rows() )
			{
				$insert_query2 = "insert into virtual_domains( virtual ) values( '".$super_admin_opt[ "domain" ]."' )";
				
				$this->_DB->query( $insert_query2 );
				if( $updated2 = $this->_DB->affected_rows() )
				{
					$saved = true;
					$this->super_admin_ok = true;
				}
				else
				{
					$err_msg = "Error Adding Values to the Virtual Domains";
					$saved = false;
				}
			}
			else
			{
				$err_msg = "Error Adding Values to the Domain Limits";
				$saved = false;
			}
		}
		
		function add_user( $mange_usr_opt, &$err_msg, $admin_domain = null )
		{
			$u_name = $mange_usr_opt[ "_add_user_name" ];
			$u_email = $mange_usr_opt[ "_add_user_email" ];
			$u_pwd = $mange_usr_opt[ "_add_user_pwd" ];
			$u_quota = $mange_usr_opt[ "_add_user_quota" ];
			$u_priviledged = $mange_usr_opt[ "_add_user_priviledged" ];
			$userid_domanin_arr = explode( "@", $u_email );
			$u_domain = $userid_domanin_arr[ 1 ];
			$u_deliver_quota = $this->get_deliver_quota( $u_quota );
			
			/* if( $this->check_users_deleted( $u_email ) ) // returns tru if user is already deleted else return false
			{
				$err_msg = "User $u_email has been deleted. Cannot Add the User";
				$this->manage_add_ok = true;
				return false;
			} */
			// else
			// {
				$insert_query = "insert into users( email, userid, isprev, mydomain, password, webpassword, inbox_quota, deliver_quota ) values( '$u_email', '$u_name', $u_priviledged, '$u_domain', Encrypt('$u_pwd'), AES_Encrypt('$u_pwd','cmail'), '$u_quota', '$u_deliver_quota' ) ON DUPLICATE KEY UPDATE email='$u_email', userid='$u_name', isprev=$u_priviledged, mydomain='$u_domain', password=Encrypt('$u_pwd'), webpassword=AES_Encrypt('$u_pwd','cmail'), inbox_quota='$u_quota', deliver_quota='$u_deliver_quota';";
			
				if( $this->_continue )
				{
					$this->_DB->query( $insert_query );
					
					if( $updated = $this->_DB->affected_rows() )
					{
						//Without following query the user can't send the mails
						
						$insert_u_mail_from_query = "insert into user_mail_from values ('$u_email','$u_email' ,'$u_domain');";
						
						$this->_DB->query( $insert_u_mail_from_query );
						
						if( $updated_from_query = $this->_DB->affected_rows())
						{
							// NOW CREATE USER IN ROUNDCUBE
							$host = $this->config[ 'default_host' ];
							
							if ($created = rcube_user::create($u_email, $host, $u_pwd)) 
							{
								// NEW USER DIALOG WORKING
								if( $this->save_initial_prefs( $u_email ) )// is_user_created is set as preferences for displaying new user dialog
								{
									// create an inbox in /var/vmail/<domain>/<user> directory
									if( $this->create_mailbox( $u_email ) == true )
									{
										$this->manage_add_ok = true;
										return true;
									}
									else
									{
										$err_msg = "Error Creating Mailbox";
										return false;
									}
								}
								else
								{
									$err_msg = "Error Setting Initial Setting Of User";
									return false;
								}
							}
							else {
								$err_msg = "Error creating Webmail User";
								return false;
							}
							
							/* if( $this->update_domain_users( 1, $admin_domain ) )
							{
								if( $this->update_domain_inbox( $this->get_bytes( $u_quota ), $admin_domain ) )
								{
									$this->manage_add_ok = true;
									return true;
								}
								else
								{
									$err_msg = "Error Updating Inbox Value";
									return false;
								}
							}
							else
							{
								$err_msg = "Error Updating Total Users";
								return false;
							} */
						}
						else
						{
							$err_msg = "Error While Updating User Mail From";
							return false;
						}
						
					}
					else
					{
						$err_msg = "Error Inserting New User";
						return false;
					}
				}
				else
				{
					$err_msg = "Error establishing database Connection";
					return false;
				}
			// }
		}
		
		function save_initial_prefs( $username )
		{
			$initial_prefs = array( "is_user_created" => 1 );
			$serialized_prefs = serialize( $initial_prefs );
			if( $this->rc->user->set_specific_user_pref( $serialized_prefs, $username ) )
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		
		function create_mailbox( $useremail )
		{
			$explode_username = explode( "@", $useremail );
			$username = $explode_username[ 0 ];
			$user_domain = $explode_username[ 1 ];
			
			$create_user_file = "/var/working/src/createuser.sh";
			$command = "sudo $create_user_file $username $user_domain";
			
			exec( $command, $out, $ret );
			
			if( $ret == 0 )
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		
		function add_superadmin_user( $super_admin_opt, &$saved, &$err_msg )
		{
			$super_admin_opt[ 'user_email' ] = $super_admin_opt[ 'user_email_nm' ]."@".$super_admin_opt[ 'user_email_dom' ];
			// to add this user in database, new array is set with the required key values and this array is sent to add_user() method to avoid redundancy
			$add_usr_opt = array();
			$add_usr_opt[ '_add_user_name' ] = $super_admin_opt[ 'user_name' ];
			$add_usr_opt[ '_add_user_email' ] = $super_admin_opt[ 'user_email' ];
			$add_usr_opt[ '_add_user_pwd' ] = $super_admin_opt[ 'user_pwd' ];
			$add_usr_opt[ '_add_user_quota' ] = $super_admin_opt[ 'user_quota' ];
			$add_usr_opt[ '_add_user_priviledged' ] = 1;
			
			if( $this->add_user( $add_usr_opt, $err_msg, $super_admin_opt[ 'user_admin_email_dom' ] ) )
			{
				$saved = true;
				$this->super_admin_ok = true;
			}
			else
			{
				$saved = false;
			}
		}
		
		function add_admin( $super_admin_opt, &$saved, &$err_msg )
		{
			$admin_domain = $super_admin_opt[ 'admin_domain' ];
			$admin_useremail = $super_admin_opt[ 'admin_useremail' ];
			$admin_manage_domain = $super_admin_opt[ 'admin_manage_domain' ];
			$admin_email_arr = explode( "@", $admin_useremail );
			$admin_username = $admin_email_arr[ 0 ];
			
			$insert_query = "insert into user_admin( username, mydomain, email, managed_domain, manged_domain_is_selected ) values( '".$admin_username."', '".$admin_domain."', '".$admin_useremail."', '".$admin_manage_domain."', ( if( ( select count(*) from user_admin as tmp where email='".$admin_useremail."' and managed_domain!='".$admin_manage_domain."' and manged_domain_is_selected=1 ) > 0, 0, 1 ) ) ) ON DUPLICATE KEY UPDATE username='".$admin_username."', mydomain='".$admin_domain."', email='".$admin_useremail."', managed_domain='".$admin_manage_domain."', manged_domain_is_selected=( if( ( select count(*) from user_admin as tmp where email='".$admin_useremail."' and managed_domain!='".$admin_manage_domain."' and manged_domain_is_selected=1 ) > 0, 0, 1 ) );";
			
			$this->_DB->query( $insert_query );
			if( $updated = $this->_DB->affected_rows() )
			{
				$saved = true;
				$this->super_admin_ok = true;
			}
			else
			{
				$err_msg = "Error Adding New Admin to the Database";
				$saved = false;
			}
		}
		
		function check_users_deleted( $user_email, $selected_domain = null )
		{
			$deleted_users = $this->get_deleted_users( $user_email, $selected_domain );
			$continue = false;
			for( $i = 0; $i < count( $deleted_users[ 'userid' ] ); $i++ )
			{
				$deleted_email = $deleted_users[ 'userid' ][ $i ].'@'.$deleted_users[ 'mydomain' ][ $i ];
				if( $user_email == $deleted_email )
				{
					$continue = true;
				}
			}
			return $continue;
		}
		
		function check_user_exists( $user_email, $selected_domain = null )
		{
			$all_users = $this->get_user_details();
			$user_exists = false;
			if( in_array( $user_email, $all_users[ 'email' ] ) )
			{
				$user_exists = true;
			}
			else
			{
				$user_exists = false;
			}
			return $user_exists;
		}
		
		function check_domain_record_exists( $domain_record_name, $original_domain )
		{
			$domain_record_name = ( $domain_record_name == "" ) ? ( "@" ) : ( $domain_record_name );
			$domain_records = $this->get_domain_records( $original_domain );
			$domain_record_name_arr = $domain_records[ 'rec_name' ];
			if( ( !empty( $domain_record_name_arr ) ) && ( in_array( $domain_record_name, $domain_record_name_arr ) ) ) // if there are no domain records, parameter 2 is null for in_array
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		
		function get_deleted_users( $user_email, $selected_domain = null )
		{
			$ret_array = array();
			if( $this->_continue )
			{
				$_query = "select userid, mydomain from user_deleted where mydomain='".( ( $selected_domain ) ? ( $selected_domain ) : ( $this->admin_selected_domain ) )."';";
				if( $query_result = $this->_DB->query( $_query ) )
				{
					while ($row = $this->_DB->fetch_array($query_result)) {
						$ret_array[ 'userid' ][] = $row[ 0 ];
						$ret_array[ 'mydomain' ][] = $row[ 1 ];
					}
				}
			}
			return $ret_array;
		}
		
		/* function update_domain_users( $user_cnt, $admin_domain = null )
		{
			$updt_query = "update domain_usage set total_users =total_users + ".$user_cnt." where dom_name='".( ( $admin_domain ) ? ( $admin_domain ) : ( $this->admin_selected_domain ) )."';";
			
			if( $this->_continue )
			{
				$this->_DB->query( $updt_query );
				if( $updated = $this->_DB->affected_rows() )
				{
					return true;
				}
				else
				{
					return false;
				}	
			}
		}
		
		function update_domain_inbox( $user_quota, $admin_domain = null )
		{
			$updt_query = "update domain_usage set inbox_usage_bt = inbox_usage_bt+$user_quota where dom_name = '".( ( $admin_domain ) ? ( $admin_domain ) : ( $this->admin_selected_domain ) )."';";
			
			if( $this->_continue )
			{
				$this->_DB->query( $updt_query );
				if( $updated = $this->_DB->affected_rows() )
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			return true;
		} */
		
		function delete_user( $mange_usr_opt, &$err_msg, &$saved )
		{
			global $RC_SABRE_HELP;
			$_continue = true; // set to false if ther is error while deleteing user record from admin_users talbe
			$super_admin_opt = array(); // for sending value in delete admin method
			
			if( $this->_continue )
			{
				foreach( $mange_usr_opt[ 'del_email' ] as $email )
				{
					if( $RC_SABRE_HELP->unsubscribe_all_calendars( $email ) )
					{
						$user_quota = $this->get_user_quota( $email );
						// deleting based on only email because email is the primary key
						$del_query = "delete from users where email ='$email';";
						
						$this->_DB->query( $del_query );
						
						if( $updated1 = $this->_DB->affected_rows() )
						{
							$this->deleteRelays($email , $this->admin_selected_domain); //delete from tables  user_in_bcc , user_out_bcc
							
							$email_split_arr = explode( "@", $email );
								
							// FIRSTLY, UNSUBSCRIBE ALL USERS FROM USER'S CALENDARS
							// if( $RC_SABRE_HELP->unsubscribe_all_calendars( $email ) )
							// {
								$insert_query = "insert into user_deleted( userid, mydomain ) values( '".$email_split_arr[0]."', '".$email_split_arr[1]."' ) ON DUPLICATE KEY UPDATE userid='".$email_split_arr[0]."', mydomain='".$email_split_arr[1]."';";
								
								$this->_DB->query( $insert_query );
								if( $updated2 = $this->_DB->affected_rows() )
								{
									// if user is an admin, delete the record of the user from user_admin table
									if( ( $this->check_is_admin( $email ) ) == true ) 
									{
										$super_admin_opt[ 'delete_admin_email_str' ] = $email;
										$this->delete_admin_user( $super_admin_opt, $saved, $err_msg );
										if( $saved )
										{
											$_continue = true;
										}
										else
										{
											$_continue = false;
										}
									}
									
									if( $_continue )
									{
										// Macgregor Changes
										if( $this->rc_sabre_help->delete_account( $email, $err_msg ) ) // delete user from sabredav database
										{
											$this->removeUserInfo($email ); //delete frm tables user_usage  , user_in_bcc , user_out_bcc , user_mail_from 
											$this->manage_add_ok = true;
											
											$saved = $this->delete_user_inbox( $email_split_arr[0], $err_msg );
										}
										else
										{
											$saved = false;
										}
										
										// $this->removeUserInfo($email ); //delete frm tables user_usage  , user_in_bcc , user_out_bcc , user_mail_from 
										// $this->manage_add_ok = true;
										
										// $saved = $this->delete_user_inbox( $email_split_arr[0], $err_msg );
									}
									else
									{
										$err_msg = "Error Deleting Admin From User Table";
										$saved = false;
									}
								}
								else
								{
									$err_msg = "Error Deleting User From Database";
									$saved = false;
								}
							
						}
						else
						{
							$err_msg = "Error While Deleting User From Database1";
							$saved = false;
						}
					}
					else
					{
						$err_msg = "Error Unsubscribing users from all calendars of user";
						$saved = false;
					}
				}
				return true;
			}
			else
			{
				$err_msg = "Error establishing database Connection";
				return false;
			}  
		}
		
		function deleteRelays($email,$dom)
		{
			//$this->delete_record('user_in_bcc',array("user_email='$email'","mydomain='".$this->domain."'));
			// TODO: PRIMARY KEY IS USER_EMAIL, SO DELETED ON BASED OF ONLY 1 VALUE
			$bcc_in_del_query = "delete from user_in_bcc where user_email='$email' and mydomain='$dom';";
			$this->_DB->query( $bcc_in_del_query );
			
			//$this->delete_record('user_out_bcc',array("user_email='$email'","mydomain='$dom'"));
			
			$bcc_out_del_query = "delete from user_out_bcc where user_email='$email' and mydomain='$dom';";
			$this->_DB->query( $bcc_out_del_query );
			
			if($this->identityExists($email))
			{
				//$identities_del_query = "delete from webmail.identities where email='$email';"; //user_id = ''$use_id 
				$identities_del_query = "delete from webmail.identities where user_id='".$this->get_webmail_usrid( $email )."';"; //user_id = ''$use_id 
				
				$this->_DB->query( $identities_del_query );
			}
			
			if($this->webmailUserExists($email)) //delete only if exists in webmail.users table
			{
				$del_frm_webmail_user = "delete from webmail.users where username='$email';";
				$this->_DB->query( $del_frm_webmail_user );
			}
				
		}
		
		function identityExists( $email ) // this function will return user_id
		{
			if( $this->_continue )
			{
				$user_id = $this->get_webmail_usrid( $email );
				
				//$identity_cnt_query = "select count(*) cnt , user_id from webmail.identities where user_id='$user_id' and del=0;";
				$identity_cnt_query = "select count(*) cnt , user_id from webmail.identities where user_id='$user_id';";
				
				$query_result = $this->_DB->query( $identity_cnt_query );
				
				$row = $this->_DB->fetch_assoc($query_result);
				
				$cnt = intval($row['cnt']);
				
				return ( $cnt>0 );
			}
			else
			{
				return false;
			}
		}
		
		function webmailUserExists($email)
		{
			if( $this->_continue )
			{
				$web_user_query = "select count(*) cnt from webmail.users where username='$email';";
				
				$query_result = $this->_DB->query( $web_user_query );
				
				
				
				$row = $this->_DB->fetch_assoc($query_result);
				
				$cnt = intval($row['cnt']);
				
				return ($cnt>0) ;
				
			}
			return false;
		}
		
		function removeUserInfo($email) 
		{
			$query_del = "delete from user_usage where email='$email' and mydomain='".$this->admin_selected_domain."';";
			$this->_DB->query($query_del);
			
			$query_bcc_in_del = "delete from user_in_bcc where bcc_email='$email' and mydomain='".$this->admin_selected_domain."';";
			$this->_DB->query($query_bcc_in_del);
			
			$query_bcc_out_del = "delete from user_out_bcc where bcc_email='$email' and mydomain='".$this->admin_selected_domain."';";
			$this->_DB->query($query_bcc_out_del);
			
			$query_u_mail_from = "delete from user_mail_from where user_email='$email' and mydomain='".$this->admin_selected_domain."';";
			$this->_DB->query($query_u_mail_from);
			
		}
		
		
		function delete_user_inbox( $user_id, &$err_msg )
		{
			//$cmd = "sudo -u vmail /var/www/webmailb/library/scripts/move_inbox_trash.sh ".$this->domain." ".$user_id."";
			$cmd = "sudo -u vmail library/scripts/move_inbox_trash.sh ".$this->admin_selected_domain." ".$user_id."";
			
			if( $this->execute_cmd( $cmd ) )
			{
				return true;
			}
			else
			{
				$err_msg = "Error deleting the inbox";
				return false;
			}
			/* if( is_dir( "/var/vmail/_trash_/".$this->domain ) )
			{
				$cmd = "sudo -u vmail mv /var/vmail/".$this->domain."/".$user_id." /var/vmail/_trash_/".$this->domain."/";
				if( $this->execute_cmd( $cmd ) )
					return true;
				else
				{
					$err_msg = "Error while moving inbox to thrash";
					return false;
				}
			}
			else
			{
				// $cmd1 = "sudo -u vmail mkdir /var/vmail/_trash_/".$this->domain." && mv /var/vmail/".$this->domain."/".$user_id." /var/vmail/_trash_/".$this->domain."/";
				$cmd1 = "sudo -u vmail mkdir /var/vmail/_trash_/".$this->domain;
				if( $this->execute_cmd( $cmd1 ) )
				{
					$cmd1 = "mv /var/vmail/".$this->domain."/".$user_id." /var/vmail/_trash_/".$this->domain."/";
					if( $this->execute_cmd( $cmd1 ) )
						return true;
					else
					{
						$err_msg = "Error while moving inbox to thrash";
						return false;
					}
				}
				
			} */
		}
		
		function execute_cmd( $cmd )
		{
			exec( $cmd, $out, $ret );
			if( $ret == 0 )
				return true;
			else
				return false;
		}
		
		function update_user( $mange_usr_opt, &$err_msg )
		{
			$u_name = $mange_usr_opt[ "_update_name" ];
			$u_email = $mange_usr_opt[ "_updt_user_email" ];
			$email_dom_arr = explode( "@", $u_email );
			$u_domain = $email_dom_arr[ 1 ];
			$u_pwd = $mange_usr_opt[ "_updt_pwd" ];
			$u_quota = $mange_usr_opt[ "_updt_quota" ];
			$u_priv = $mange_usr_opt[ "_updt_priviledged" ];
			$u_deliver_quota = $this->get_deliver_quota( $u_quota );
			
			if( strlen( $u_pwd ) > 0 ) // if password is changed from admin
			{
				$updt_query = "update users set userid='$u_name', email='$u_email', isprev=$u_priv, mydomain='$u_domain', password=Encrypt('$u_pwd'), webpassword=AES_Encrypt('$u_pwd','cmail'), inbox_quota='$u_quota', deliver_quota='$u_deliver_quota' where email='$u_email';";
			}
			else
			{
				$updt_query = "update users set userid='$u_name', email='$u_email', isprev=$u_priv, mydomain='$u_domain', inbox_quota='$u_quota', deliver_quota='$u_deliver_quota' where email='$u_email';";
			}	
			
			if( $this->_continue )
			{
				$this->_DB->query( $updt_query );
				if( $updated = $this->_DB->affected_rows() )
				{
					if( strlen( $u_pwd ) > 0 ) // if password is changed from admin, change the password from sabredav also
					{
						// Macgregor Changes
						if( $this->rc_sabre_help->update_sabredav_password( $u_email, $u_pwd ) )
						{
							$this->rc_sabre_help->update_shared_calendar_password( $u_email, $u_pwd );
							return true;
						}
						else
						{
							$err_msg = "Error Updating Sabredav Password";
							return false;
						}
						// return true;
					}
					else
					{
						return true;
					}
				}
				else
				{
					$err_msg = "Error Inserting New User";
					return false;
				}
				return true;
			}
			else
			{
				$err_msg = "Error establishing database Connection";
				return false;
			}
		}
		
		function add_list( $mange_lst_opt, &$saved, &$err_msg )
		{ 
			$list_domain = $mange_lst_opt[ '_add_list_dom' ];
			$list_name = $mange_lst_opt[ '_add_list_name' ].'@'.$list_domain;
			$is_private = $mange_lst_opt[ '_add_list_private' ];
			
			$insert_query = "insert into admin_lists( email_list, is_private, mydomain ) values( '$list_name', '$is_private', '".$this->admin_selected_domain."' ) ON DUPLICATE KEY UPDATE email_list='$list_name', is_private = '$is_private', mydomain = '".$this->admin_selected_domain."' ";
			
			$this->_DB->query( $insert_query );
			if( $updated = $this->_DB->affected_rows() )
			{
				$saved = true;
				$this->manage_add_ok = true;
			}
			else
			{
				$err_msg = "Error Adding Values to the Database";
				$saved = false;
			}
			// show update form false
			// show delete form false
			
			//display error success messge
		}
		
		function delete_list( $mange_lst_opt, &$saved, &$err_msg )
		{
			$delete_list_name_str = $mange_lst_opt[ 'delete_list_names' ];
			$delete_list_name_arr = explode( "|", $delete_list_name_str );
			for( $i = 0; $i < count( $delete_list_name_arr ); $i++ )
			{
				$delete_query = "delete from admin_lists where email_list='".$delete_list_name_arr[ $i ]."' and mydomain='".$this->admin_selected_domain."';";
				if( $this->_DB->query( $delete_query ) )
				{
					$saved = true;
				}
				else
				{
					$err_msg = "Error while deleting List from the Database";
					$saved = false;
				}
			}
		}
		
		function save_list_member( $mange_lst_opt, &$saved, &$err_msg ) // ie. edit list
		{
			$member_type = $mange_lst_opt[ '_edit_list_type' ];
			$external_list_member = $mange_lst_opt[ '_edit_list_ext_email' ];
			$list_name = $mange_lst_opt[ '_list_name_hidden_field' ];
			$add_list_members = $mange_lst_opt[ '_edit_list_hidden_field' ];
			$list_members_arr = explode( '|', $add_list_members );
			
			if( $member_type == 'local' )
			{
				for( $i = 0; $i < count( $list_members_arr ); $i++ )
				{
					$insert_query = "insert into user_aliases( email_src, email_dest, mydomain, isaliase ) values( '$list_name', '".$list_members_arr[ $i ]."', '".$this->admin_selected_domain."', 0 );";
					
					$this->_DB->query( $insert_query );
					if( $updated = $this->_DB->affected_rows() )
					{
						$saved = true;
						$this->manage_add_ok = true;
					}	
					else
					{
						$saved = false;
						$err_msg = "Error Adding List Members to the Database";
					}	
				}
			}
			else // external
			{
				$insert_query = "insert into user_aliases( email_src, email_dest, mydomain, isaliase ) values( '$list_name', '".$external_list_member."', '".$this->admin_selected_domain."', 0 );";
				$this->_DB->query( $insert_query );
				if( $updated = $this->_DB->affected_rows() )
				{
					$saved = true;
					$this->manage_add_ok = true;
				}	
				else
				{
					$saved = false;
					$err_msg = "Error Adding List Members to the Database";
				}
			}
		}
		
		function delete_list_member( $mange_lst_opt, &$saved, &$err_msg ) // ie. edit list
		{
			$list_name = $mange_lst_opt[ 'list_name' ];
			$list_member_str = $mange_lst_opt[ 'delete_list_member_str' ];
			$list_member_arr = explode( "|", $list_member_str );
			
			for( $i = 0; $i < count( $list_member_arr ); $i++ )
			{
				$delete_query = "delete from user_aliases where email_dest='".$list_member_arr[ $i ]."' and email_src='".$list_name."' and mydomain = '".$this->admin_selected_domain."' and isaliase=0;";
				if( $this->_DB->query( $delete_query ) )
				{
					$saved = true;
				}
				else
				{
					$err_msg = "Error while deleting Member from the list";
					$saved = false;
				}
			}
			$this->show_update_form = true;
		}
		
		function add_alias( $manage_alias_opt, &$saved, &$err_msg )
		{
			$alias_nm = $manage_alias_opt[ '_add_alias_nm' ];
			$alias_dom = $manage_alias_opt[ '_add_alias_dom' ];
			$orig_email_nm = $manage_alias_opt[ '_add_alias_orig_nm' ];
			$orig_email_dom = $manage_alias_opt[ '_add_alias_orig_dom' ];
			
			$email_src = $alias_nm.'@'.$alias_dom; // alias
			$email_dest = $orig_email_nm.'@'.$orig_email_dom; // orig
			
			$insert_query = "insert into user_aliases( email_src, email_dest, mydomain, isaliase ) values( '$email_src', '$email_dest', '".$this->admin_selected_domain."', 1 ) ON DUPLICATE KEY UPDATE email_src='$email_src', email_dest='$email_dest', mydomain='".$this->admin_selected_domain."', isaliase=1;";
			
			$this->_DB->query( $insert_query );
			if( $updated = $this->_DB->affected_rows() )
			{
				$saved = true;
				$this->manage_add_ok = true;
			}
			else
			{
				$err_msg = "Error Adding Values to the Database";
				$saved = false;
			}
		}
		
		function validate_admin_pwd( $admin_options ) // Code to validate data before changing Password
		{
			// global $OUTPUT;
			// validation for change password
			if( ( strlen( $admin_options[ '_admin_pwd' ] ) == 0 ) || ( strlen( $admin_options[ '_curr_admin_pwd' ] ) == 0 ) || ( strlen( $admin_options[ '_conf_admin_pwd' ] ) == 0 ) )
			{
				$err_msg = 'Please Provide All The Values';
				$saved = false;
			}
			else if( $admin_options[ '_admin_pwd' ] !== $admin_options[ '_conf_admin_pwd' ] )
			{
				$err_msg = 'The New Password and Confirm Password should be the same';
				$saved = false;
			}
			else if( !( $this->isPassStrongOK( $admin_options[ '_admin_pwd' ], $pmsg ) ) )
			{
				$err_msg = $pmsg;
				$saved = false;
			}
			else
			{
				// $saved = $RCMAIL->user->reset_admin_pwd( $admin_options, $err_msg );
				$saved = $this->reset_admin_pwd( $admin_options, $err_msg );
			}

			//validation for out of office
			$this->display_message( $saved, 'successfullysaved', $err_msg );
		}
		
		function validate_email_notification( $admin_options )
		{
			// global $OUTPUT;
			$email = $admin_options[ '_email_notify_txtbox' ];
			if( !strlen( $email ) )
			{
				$err_msg = "Please Provide the Email-ID";
				$saved = false;
			}
			else
			{
				$saved = $this->save_email_notification( $admin_options, $err_msg );
			}
			
			$this->display_message( $saved, 'successfullysaved', $err_msg );
		}
		
		//function for validation
		function validateIpAddress($ip_addr)
		{
		  //first of all the format of the ip address is matched
		  if(preg_match("/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/",$ip_addr))
		  {
			//now all the intger values are separated
			$parts=explode(".",$ip_addr);
			//now we need to check each part can range from 0-255
			foreach($parts as $ip_parts)
			{
			  if(intval($ip_parts)>255 || intval($ip_parts)<0)
			  return false; //if number is not within range of 0-255
			}
			return true;
		  }
		  else
			return false; //if format of ip address doesn't matches
		}
		
		function isPassStrongOK($pwd,&$pmsg) // Special Validation for Admin Password
		{
			$_M_PASS_TO_SHORT = "Password Should Contain Minumum 8 Characters";
			$_M_PASS_ATL_NUMB = "Password Should Contain Atleast One Number";
			$_M_PASS_ATL_LETT = "Password Should Contain Atleast One Small Letter";
			$_M_PASS_ATL_CAPS = "Password Should Contain Atleast One Capital Letter";

			$pmsg = '';

			if( strlen($pwd) < 8 )
			$pmsg = $_M_PASS_TO_SHORT;
			else if( !preg_match("#[0-9]+#", $pwd) )
			$pmsg = $_M_PASS_ATL_NUMB;
			else if( !preg_match("#[a-z]+#", $pwd) )
			$pmsg = $_M_PASS_ATL_LETT;
			else if( !preg_match("#[A-Z]+#", $pwd) )
			$pmsg = $_M_PASS_ATL_CAPS;
			else
			return(true);

			return(false);
		}
		
		/* function isInboxLimitExcd( $user_quota )
		{
			$user_quota_in_bytes = $this->get_bytes( $user_quota );
			
			if( $this->max_inbox_size_bt > ( $this->used_inbox_size_bt + $user_quota_in_bytes ) )
			{
				return true;
			}
			else
			{
				return false;
			}	
		} */
		
		/* function isUserExcd()
		{
			if( $this->max_users > $this->total_users )
			{
				return true;
			}
			else
			{
				return false;
			}
		} */
		
		function email_exists( $email )
		{
			$select_query = "select email from users;";
			$continue = false;
			if( $query_result = $this->_DB->query( $select_query ) )
			{
				while ($row = $this->_DB->fetch_array($query_result)) {
					if( strcmp ( $row[0] , $email ) == 0 )
						$continue = true;
				}
			}
			return $continue;
		}
		
		function validate_out_of_office( $rules_options, $email = null, $manage_rules = false ) // Code to validate Out Of Office data
		{	
			global $OUTPUT;
			$enabled = $rules_options[ '_out_of_office_enable' ];
			$header = $rules_options[ '_out_of_office_header' ];
			$match = $rules_options[ '_out_of_office_match' ];
			$subject = $rules_options[ '_out_of_office_subject' ];
			$message = $rules_options[ '_out_of_office_message' ];
			
			$header_count = array_count_values($header);
			
			$oof_rules_count = count( $enabled );
			
			if( $oof_rules_count == 0 )
			{
				$saved = $this->delete_all_oof_rules( $email );
				$this->rules_ok = true;
			}
			else if( $header_count[ 0 ] > 1 )
			{
				$err_msg = 'Filter All may be set Only Once';
				$saved = false;
			}
			else
			{	
				for( $i = 0; $i < $oof_rules_count; $i++ )
				{
					// First Check if Enabled is not set, and other credentials are also not provided
					if( ( $enabled[ $i ] == '0' ) && ( $header[ $i ] == '0' ) && ( $match[ $i ] == '' ) && ( $subject[ $i ] == '' ) && ( $message[ $i ] == '' ) )
					{
						$err_msg = 'Please Provide All The Values';
						$saved = false;
						$continue = false; // continue if for the for loop
						break;
					}
					else if( ( $enabled[ $i ] == '1' ) && ( $header[ $i ] != '0' ) && ( $match[ $i ] == '' ) )
					{
						$err_msg = 'Please Provide Header Match';
						$saved = false;
						$continue = false;
						break;
					}
					else if( ( $enabled[ $i ] == '1' ) && ( $subject[ $i ] == '' ) )
					{
						$err_msg = 'Please Provide The Subject';
						$saved = false;
						$continue = false;
						break;
					}
					else if( ( $enabled[ $i ] == '1' ) && ( $message[ $i ] == '' ) )
					{
						$err_msg = 'Please Provide The Message';
						$saved = false;
						$continue = false;
						break;
					}
					else
					{
						$continue = true;
					}
				}
				
				
				if( $continue ) // if entried passes the validation
				{
					$saved = $this->save_out_of_office( $rules_options, $email, $err_msg );
				}
				// if continue is false, save is also false, so no need of else condt
			}
			
			if( ( $saved ) && ( $manage_rules == false ) )
			{
				$OUTPUT->show_message( 'successfullysaved', 'confirmation' );
			}
			else if( $saved == false )
			{
				$OUTPUT->show_message( $err_msg, 'error' );
			}
			
			/* $enabled = $rules_options[ '_out_of_office_enable' ];
			$subject = $rules_options[ '_out_of_office_sub' ];
			$message = $rules_options[ '_out_of_office_message' ];
			
			if( ( ( strlen( $subject ) == 0 ) || ( strlen( $message ) == 0 ) ) && ( $enabled == '1' ) )
			{
				$err_msg = 'Please Provide All The Values';
				$saved = false;
			}
			else if( ( strlen( $subject ) > 100 ) && ( $enabled == '1' ) )
			{
				$err_msg = 'The Subject cannot be more than 100 Characters';
				$saved = false;
			}
			else if( ( strlen( $message ) > 500 ) && ( $enabled == '1' ) )
			{
				$err_msg = 'The Message cannot be more than 500 Characters';
				$saved = false;
			}
			else
			{
				$saved = $this->save_out_of_office( $rules_options, $email, $err_msg );
			}
			
			if( ( $saved ) && ( $manage_rules == false ) )
			{
				$OUTPUT->show_message( 'successfullysaved', 'confirmation' );
			}
			else if( ( $saved == false ) && ( $manage_rules == false ) )
			{
				$OUTPUT->show_message( $err_msg, 'error' );
			} */
			
		}
		
		function validate_block_email( $rules_options, $email = null, $manage_rules = false ) // code to validate Block Email data
		{
			// global $OUTPUT;
			$header_array = $rules_options[ '_block_email_header' ];
			$filter_array = $rules_options[ '_block_email_filter' ];
			$count = count( $header_array );
			$continue = false;
			
			if( ( count( $rules_options[ '_block_email_header' ] ) == 0 ) && ( count( $rules_options[ '_block_email_filter' ] ) == 0 ) ) // if add button is not clicked
			{
				$saved = $this->delete_all_blocked_emails( $email );
				$this->rules_ok = true;
			}
			else // if add button is clicked then validate values
			{
				for( $i = 0; $i < $count; $i++ )
				{
					if( strlen( $filter_array[ $i ] ) == 0 ) 
					{
						$err_msg = 'Please Provide all the Values';
						$manage_rules = false; // manage rules set to false, so error msg will be displayed
						$saved = false;
						$continue = false;
						break;
					}
					else
					{
						$continue = true;
					}
				}
			}
			
			if( $continue )
			{
				$saved = $this->save_block_emails( $rules_options, $email, $err_msg );
			}
			
			if( $manage_rules == false )
			{
				$this->display_message( $saved, 'Block Emails Saved Successfully', $err_msg );
			}
		}
		
		function validate_forward_rule( $rules_options, $email = null, $manage_rules = false ) // code to validate Block Email data
		{
			// global $OUTPUT;
			$header_array = $rules_options[ '_forward_rule_header' ];
			$filter_array = $rules_options[ '_forward_rule_filter' ]; //_forward_rule_email
			$fw_rule_email_array = $rules_options[ '_forward_rule_email' ]; //_forward_rule_email
			$count = count( $header_array );
			$continue = false;
			
			if( ( count( $rules_options[ '_forward_rule_header' ] ) == 0 ) && ( count( $rules_options[ '_forward_rule_filter' ] ) == 0 ) && ( count( $rules_options[ '_forward_rule_email' ] ) == 0 ) ) // if add button is not clicked
			{
				$saved = $this->delete_all_forward_rules( $email );
				$this->rules_ok = true;
			}
			else // if add button is clicked then validate values
			{
				for( $i = 0; $i < $count; $i++ )
				{
					// if( ( strlen( $filter_array[ $i ] ) == 0 || strlen ($fw_rule_email_array[$i] ) == 0  ) && ( $header_array[ $i ] != "0" ) )
					if( ( strlen( $fw_rule_email_array[$i] ) == 0 ) || ( ( $header_array[ $i ] != "0" ) && ( strlen( $filter_array[ $i ] ) == 0 ) ) ) // new validation
					{
						$err_msg = 'Please Provide all the Values';
						$saved = false;
						$continue = false;
						$manage_rules = false; // manage rules is set to false to display error msg
						break;
					}
					else
					{
						$continue = true;
					}
				}
			}
			
			if( $continue )
			{
				$saved = $this->save_forward_rules( $rules_options, $email, $err_msg ); //save_block_emails  save_forward_rules
			}
			
			if( $manage_rules == false )
			{
				$this->display_message( $saved, 'Forward Rule Saved Successfully', $err_msg );
			}
		}
		
		function validate_custom_rule( $rules_options, $email = null, $manage_rules = false ) // code to validat Custom Rule
		{
			// global $OUTPUT;
			$enabled = $rules_options[ '_custom_rule_enable' ];
			$message = $rules_options[ '_custom_rule_desc' ];
			
			/* if( $enabled == '1' )
			{
				if( strlen( $message ) == 0 )
				{
					$err_msg = 'Please Enter The Description';
					$saved = false;
				}
				else
				{
					$saved = $this->save_custom_directive( $rules_options, $err_msg );
				}
			}
			else
				$saved = $this->save_custom_directive( $rules_options, $err_msg ); */
				
			if( ( strlen( $message ) == 0 ) && ( $enabled == '1' ) )
			{
				$manage_rules = false; // manage rules is set to false to display error msg
				$err_msg = 'Please Enter The Description';
				$saved = false;
			}
			else
			{
				$saved = $this->save_custom_directive( $rules_options, $email, $err_msg );
			}
				
			if( $manage_rules == false )
			{
				$this->display_message( $saved, 'Custom Directive Saved Successfully', $err_msg );
			}
		}
		
		function validate_manage_users( $mange_usr_opt, $flag )
		{
			global $OUTPUT;
			$success_msg = "";
			if( $flag == 'Edit' )
			{
				$this->show_update_form = true;
				$this->show_delete_form = false;
			}
			else if( $flag == 'Cancel' )
			{
				$this->show_update_form = false;
				$this->show_delete_form = false;
			}
			else if( $flag == 'NO' )
			{
				$this->show_update_form = false;
				$this->show_delete_form = false;
			}
			else if( $flag == 'Delete' )
			{
				$this->validate_delete_user( $mange_usr_opt, $err_msg, $continue ); // validate checks if atlease 1 email id is selected to delet or not
				if( !( $continue ) ) // if no email is selected is selected then show error message
					$OUTPUT->show_message( $err_msg, 'error' );
			}	
			else
			{
				switch( $flag )
				{
					case "Add":
						$this->validate_add_user( $mange_usr_opt, $err_msg, $saved );
						$success_msg = "User Added Successfully";
						break;
					case "Update":
						$this->validate_update_user( $mange_usr_opt, $err_msg, $saved );
						$success_msg = "User Updated Successfully";
						break;
					case "YES":
						$this->delete_user( $mange_usr_opt, $err_msg, $saved );
						$success_msg = "User Deleted Successfully";
						break;
				}
				
				$this->display_message( $saved, $success_msg, $err_msg );
			}
		}
		
		function validate_manage_domain_aliases( $manage_domain_alias_opt )
		{
			$flag = $manage_domain_alias_opt[ 'manage_domain_alias_flg' ];
			
			if( $flag == 'Edit' )
			{
				$this->show_update_form = true;
			}
			else if( ( $flag == 'Cancel' ) || ( $flag == 'Back' ) )
			{
				$this->show_update_form = false;
			}
			else
			{
				switch( $flag )
				{
					case 'Add':
						/* VALIDTE WHETHER THE DOMAIN ALIAS */
						$this->validate_add_domain_alias( $manage_domain_alias_opt, $saved, $err_msg );
						$success_msg = "Account Domain Added Successfully";
						break;
						
					case 'Delete':
						/* VALIDATE THE DELETION OF THE DOMAIN ALIAS */
						$this->delete_domain_alias( $manage_domain_alias_opt, $saved, $err_msg );
						$success_msg = "Account Domain Deleted Successfully";
						break;
						
					case 'Add Record':
						/* VALIDATE THE ADDING DOMAIN RECORD */
						$this->validate_add_domain_record( $manage_domain_alias_opt, $saved, $err_msg );
						$success_msg = "Domain Record Added Successfully";
						break;
						
					case 'Delete Record':
						/* VALIDATE THE DELETING DOMAIN RECORD */
						$this->delete_domain_record( $manage_domain_alias_opt, $saved, $err_msg );
						$success_msg = "Domain Record Deleted Successfully";
						break;
				}
				$this->display_message( $saved, $success_msg, $err_msg );
			}
		}
		
		function validate_super_admin_new_acc( $super_admin_opt )
		{
			$all_domains = $this->get_all_domains();
			if( strlen( $super_admin_opt[ 'domain' ] ) == 0 )
			{
				$saved = false;
				$err_msg = "Please Provide The Domain Name";
			}
			else if( strlen( $super_admin_opt[ 'max_users' ] ) == 0 )
			{
				$saved = false;
				$err_msg = "Please Provide Maximum No of Users";
			}
			else if( is_numeric( $super_admin_opt[ "max_users" ] ) == false )
			{
				$saved = false;
				$err_msg = "Maximum Users should be A Valid Number";
			}
			else if( strlen( $super_admin_opt[ 'inbox_size' ] ) == 0 )
			{
				$saved = false;
				$err_msg = "Please Provide Inbox Size";
			}
			else if( is_numeric( $super_admin_opt[ 'inbox_size' ] ) == false )
			{
				$saved = false;
				$err_msg = "Inbox Size Should Always Be In Number";
			}
			else if( in_array( $super_admin_opt[ "domain" ], $all_domains[ "dom_name" ] ) )
			{
				$saved = false;
				$err_msg = "This Domain is Already Present. Please provide some other Domain Name";
			}
			else
			{
				$this->add_account( $super_admin_opt, $saved, $err_msg );
			}
			
			$this->display_message( $saved, "Account Added Successfully", $err_msg );
			
		}
		
		function validate_add_domain_alias( $manage_domain_alias_opt, &$saved, &$err_msg )
		{
			if( strlen( $manage_domain_alias_opt[ 'domain_alias_name' ] ) == 0 )
			{
				$saved = false;
				$err_msg = "Please Provide A Domain Alias Name";
			}
			else
			{
				$this->add_domain_alias( $manage_domain_alias_opt, $saved, $err_msg );
			}
		}
		
		function validate_add_domain_record( $manage_domain_alias_opt, &$saved, &$err_msg )
		{
			/* FIRST CHECK IF DOMAIN RECORD EXISTS, IF NOT THEN  CHECK IF VALUES ENTERED PROPERLY, FOR VALUE A CHECK IF IP ADDRESS OTHERWISE DIRECT TEXT */
			$_continue = false;
			$domain_record_name = $manage_domain_alias_opt[ 'domain_record_name' ];
			$domain_record_type = $manage_domain_alias_opt[ 'domain_record_type' ];
			$domain_record_value = $manage_domain_alias_opt[ 'domain_record_value' ];
			$count = count( $domain_record_type );
			$original_domain = $manage_domain_alias_opt[ 'original_domain' ];
			
			if( $count > 0 )
			{
				for( $i = 0; $i < $count; $i++ )
				{
					if( $this->check_domain_record_exists( $domain_record_name[ $i ], $original_domain ) == true ) // returns true if domain record already exists else returnfalse
					{
						$saved = false;
						$err_msg = "Domain Record Already Exists";
						$_continue = false;
						$this->show_update_form = true;
						break;
					}
					if( strlen( $domain_record_value[ $i ] ) == 0 )
					{
						$saved = false;
						$err_msg = "Please Provide the Domain Record Value";
						$_continue = false;
						$this->show_update_form = true;
						break;
					}
					else if( ( $this->validateIpAddress( $domain_record_value[ $i ] ) == false ) && ( $domain_record_type[ $i ] == "A" ) )
					{
						$saved = false;
						$_continue = false;
						$this->show_update_form = true;
						$err_msg = "Please Provide Valid Domain Value";
						break;
					}
					else
					{
						$_continue = true;
					}
				}
			}
			else
			{
				$saved = false;
				$err_msg = "Please Provide All the Value";
				$_continue = false;
				$this->show_update_form = true;
			}
			
			if( $_continue )
			{
				$this->add_domain_record( $manage_domain_alias_opt, $saved, $err_msg );
			}
		}
		
		function validate_super_admin_new_user( $super_admin_opt )
		{
			// GLOBAL $OUTPUT;
			$all_users = $this->get_user_details( $super_admin_opt[ 'admin_email_dom' ] );
			
			if( strlen( $super_admin_opt[ 'user_name' ] ) == 0 )
			{
				$saved = false;
				$err_msg = "Please Provide the User Name";
			}
			else if( strlen( $super_admin_opt[ 'user_email_nm' ] ) == 0 )
			{
				$saved = false;
				$err_msg = "Please Provide User Email";
			}
			else if( strlen( $super_admin_opt[ 'user_pwd' ] ) == 0 )
			{
				$saved = false;
				$err_msg = "Please Provide User Password";
			}
			else if( !( $this->isPassStrongOK( $super_admin_opt[ 'user_pwd' ], $pmsg ) ) )
			{
				$err_msg = $pmsg;
				$saved = false;
			} 
			else if( $this->check_users_deleted( $super_admin_opt[ 'user_email_nm' ]."@".$super_admin_opt[ 'user_email_dom' ] ) )
			{
				$err_msg = "This User Is Deleted From Database. Please Provide Other Email";
				$saved = false;
			}
			else if( in_array( $super_admin_opt[ 'user_email_nm' ]."@".$super_admin_opt[ 'user_email_dom' ], $all_users[ 'user_email' ] ) )
			{
				$err_msg = "This User Is Already Present. Please Provide Other Email";
				$saved = false;
			}
			else if( ( preg_match('/[A-Z]/', $super_admin_opt[ 'user_email_nm' ] ) ) || ( preg_match('/[A-Z]/', $super_admin_opt[ 'user_email_dom' ] ) ) ) 
			{
				$err_msg = "User $user_email cannot contain Upper Case Characters.";
				$saved = false;
			}
			else 
			{
				$this->add_superadmin_user( $super_admin_opt, $saved, $err_msg );
			}
			
			$this->display_message( $saved, 'User Added Successfully', $err_msg );
			
		}
		
		function validate_super_admin_new_adm( $super_admin_opt, $superadmin_flag )
		{
			// global $OUTPUT;
			
			if( $super_admin_opt[ 'admin_useremail' ] == 'default' )
			{
				$saved = false;
				$err_msg = "Please Select a UserName";
			}
			else if( strlen( $super_admin_opt[ 'admin_domain' ] ) == 0 )
			{
				$saved = false;
				$err_msg = "Please Select a Domain";
			}
			else if( strlen( $super_admin_opt[ 'admin_manage_domain' ] ) == 0 )
			{
				$saved = false;
				$err_msg = "Please Select a Domain to Manage";
			}
			else
			{
				$this->add_admin( $super_admin_opt, $saved, $err_msg );
			}
			
			$this->display_message( $saved, 'Admin Added Successfully', $err_msg );
			
		}
		
		function validate_delete_admin_user( $super_admin_opt, $flag ) // flag not needed. kept for future use
		{
			// global $OUTPUT;
			// no validation required. directly deleting user
			$this->delete_admin_user( $super_admin_opt, $saved, $err_msg );
			
			$this->display_message( $saved, 'Admin Deleted Successfully', $err_msg );
			
		}
		
		function delete_admin_user( $super_admin_opt, &$saved, &$err_msg )
		{
			$delete_admin_string = $super_admin_opt[ 'delete_admin_email_str' ];
			$delete_admin_em_arr = explode( "|", $delete_admin_string );
			if( isset( $super_admin_opt[ 'delete_admin_m_dom_str' ] ) ) // if specific domain is set
			{
				$delete_admin_dom_string = $super_admin_opt[ 'delete_admin_m_dom_str' ];
				$delete_admin_dom_arr = explode( "|", $delete_admin_dom_string );
			}
			
			for( $i = 0; $i < count( $delete_admin_em_arr ); $i++ )
			{
				if( isset( $super_admin_opt[ 'delete_admin_m_dom_str' ] ) ) // if deleting from manage user admin
				{
					$delete_query = "delete from user_admin where email = '".$delete_admin_em_arr[ $i ]."' and managed_domain='".$delete_admin_dom_arr[ $i ]."';";
				}
				else // while deleting user
				{
					$delete_query = "delete from user_admin where email = '".$delete_admin_em_arr[ $i ]."';";
				}
				
				if( $this->_DB->query( $delete_query ) )
				{
					$saved = true;
				}
				else
				{
					$err_msg = "Error while deleting Admin from Database";
					$saved = false;
				}
			}
		}
		
		function delete_domain_alias( $manage_domain_alias_opt, &$saved, &$err_msg )
		{
			/* DELETES ENTRY FROM THE DOMAIN_ALIASES TABLE */
			global $RC_MAIL;
			$domain_alias_str = $manage_domain_alias_opt[ 'domain_alias_name_str' ];
			$domain_alias_arr = explode( "|", $domain_alias_str );
			
			foreach( $domain_alias_arr as $domain_alias )
			{
				// $delete_query = "delete from domain_aliases where org_domain='".$this->admin_selected_domain."' and alias_domain='".$domain_alias."'  and  0=(select count(email_src) from user_aliases where email_src like '%@".$domain_alias."');";
				// TODO: confirm deletion of domain alias by user alias check
				$delete_query = "delete from domain_aliases where org_domain='".$this->admin_selected_domain."' and alias_domain='".$domain_alias."';";
				
				$this->_DB->query( $delete_query );
				if( $updated = $this->_DB->affected_rows() )
				{
					$delete_query2 = "delete from virtual_domains where virtual='".$domain_alias."';";
					
					$this->_DB->query( $delete_query2 );
					if( $updated = $this->_DB->affected_rows() )
					{
						if( $RC_MAIL->publishDomain( $domain_alias, true ) == true ) // second parameter is, deletefile, which is set to true. by default it is false
						{
							$saved = true;
						}
						else
						{
							$saved = false;
							$err_msg = "Account Domain Deleted From Database, But couldnot Delete BytemarkDNS File";
						}
					}
					else
					{
						$saved = false;
						$err_msg = "Error While Deleting Domain from Virtual Domains Table";
						break;
					}
				}
				else // if first delete query fails, break
				{
					$saved = false;
					$err_msg = "Error While Deleting Account Domain From Database";
					break;
				}
			}
		}
		
		function delete_domain_record( $manage_domain_alias_opt, &$saved, &$err_msg )
		{
			/* DELETE ENTRY FROM THE DOMAIN_RECORD TABLE */
			global $RC_MAIL;
			$_continue = false;
			$original_domain = $manage_domain_alias_opt[ 'original_domain' ];
			$domain_record_str = $manage_domain_alias_opt[ 'domain_record_name' ];
			
			$domain_record_arr = explode( "|", $domain_record_str );
			
			for( $i = 0; $i < count( $domain_record_arr ); $i++ )
			{
				$delete_query = "delete from domain_record where dom_name='".$original_domain."' and rec_name='".$domain_record_arr[ $i ]."';";
				
				if( $this->_DB->query( $delete_query ) )
				{
					$_continue = true;
				}
				else
				{
					$_continue = false;
					break;
				}
			}
			
			if( $_continue )
			{
				if( $RC_MAIL->publishDomain( $original_domain ) == true )
				{
					$saved = true;
					$this->show_update_form = true;
				}
				else
				{
					$saved = false;
					$err_msg = "Domain Record Deleted from Database but couldnot Modify BytemarDNS File";
					$this->show_update_form = true;
				}
			}
			else
			{
				$saved = false;
				$err_msg = "Error while deleting List from the Database";
				$this->show_update_form = true;
			}	
		}
		
		function validate_edit_admin_user( $super_admin_opt, $flag )
		{
			if( $flag == 'Edit' )
			{
				$this->show_update_form = true;
			}
		}
		
		function validate_add_user( $mange_usr_opt, &$err_msg, &$saved )
		{
			$user_name = $mange_usr_opt[ "_add_user_name" ];
			$user_email_nm = $mange_usr_opt[ "_add_user_email_nm" ];
			$user_email = $mange_usr_opt[ "_add_user_email" ];
			$user_pwd = $mange_usr_opt[ "_add_user_pwd" ];
			$user_quota = $mange_usr_opt[ "_add_user_quota" ];
			$user_priv = $mange_usr_opt[ "_add_user_priviledged" ];
			
			if( strlen( $user_name ) == 0 )
			{
				$err_msg = "Please Enter The UserName";
				$saved = false;
			}
			else if( strlen( $user_name ) > 50 )
			{
				$err_msg = "The UserName cannot be more than 50 Characters";
				$saved = false;
			}
			else if( preg_match( '/\s/', $user_email_nm ) )
			{
				$err_msg = "Email cannot contain Spaces";
				$saved = false;
			}
			else if( strlen( $user_email_nm ) == 0 )
			{
				$err_msg = "Please Enter Email";
				$saved = false;
			}
			else if( strlen( $user_email_nm ) > 50 )
			{
				$err_msg = "Email cannot be more than 50 Characters";
				$saved = false;
			}			
			else if( strlen( $user_pwd ) == 0 )
			{
				$err_msg = "Please Enter Password";
				$saved = false;
			}
			/* else if( !( $this->isInboxLimitExcd( $user_quota ) ) )
			{
				$err_msg = "User Quota exceeds Maximum Inbox Size. Please select a smaller Quota Value";
				$saved = false;
			}
			else if( !( $this->isUserExcd() ) )
			{
				$err_msg = "Exceeded Maximum Number of Users. Please Contact Administrator";
				$saved = false;
			} */
			else if( !( $this->isPassStrongOK( $user_pwd, $pmsg ) ) )
			{
				$err_msg = $pmsg;
				$saved = false;
			}
			else if( $this->email_exists( $user_email ) )
			{
				$err_msg = "Email $user_email already exists. Please provide other Email ";
				$saved = false;
			}
			else if(preg_match('/[A-Z]/', $user_email))
			{
				$err_msg = "User $user_email cannot contain Upper Case Characters.";
				$saved = false;
			}
			else if( $this->check_users_deleted( $user_email ) ) // returns tru if user is already deleted else return false
			{
				$err_msg = "User $user_email has been deleted. Cannot Add the User";
				$this->manage_add_ok = true;
				$saved = false;
			}
			else
			{
				$saved = $this->add_user( $mange_usr_opt, $err_msg );
			}
		}
		
		function validate_delete_user( $mange_usr_opt, &$err_msg, &$continue )
		{
			$email_id = $mange_usr_opt[ "_email_id" ];
			if( empty( $mange_usr_opt[ "_email_id" ] ) )
			{
				// $this->show_delete_form = false; commented bcoz value is already false
				$err_msg = "Please select atleast 1 Email to Delete";
				$continue = false;
			}
			else
			{
				$this->show_delete_form = true;
				$continue = true;
				// $continue = $this->delete_user( $email_id, $err_msg );
			}
		}
		
		function validate_update_user( $mange_usr_opt, &$err_msg, &$saved )
		{
			$u_name = $mange_usr_opt[ "_update_name" ];
			$u_email = $mange_usr_opt[ "_updt_user_email" ];
			$u_pwd = $mange_usr_opt[ "_updt_pwd" ];
			$u_quota = $mange_usr_opt[ "_updt_quota" ];
			$u_priv = $mange_usr_opt[ "_updt_priviledged" ];
			if( strlen( $u_name ) == 0 )
			{
				$err_msg = "Please enter User Name";
				$saved = false;
				$this->show_update_form = true;
			}
			else if( strlen( $u_name ) > 50 )
			{
				$err_msg = "UserName cannot be more than 50 Characters";
				$saved = false;
				$this->show_update_form = true;
			}
			else if( strlen( $u_email ) == 0 )
			{
				$err_msg = "Please enter User Email";
				$saved = false;
				$this->show_update_form = true;
			}
			else if( ( strlen( $u_pwd ) != 0 ) && !( $this->isPassStrongOK( $u_pwd, $pmsg ) ) )
			{
				$err_msg = $pmsg;
				$saved = false;
				$this->show_update_form = true;
			}
			else
			{
				$saved = $this->update_user( $mange_usr_opt, $err_msg );
			}
		}
		
		function validate_manage_list( $mange_lst_opt, $manage_list_flg )
		{
			// GLOBAL $OUTPUT;
			if( ( $manage_list_flg == 'Cancel' ) || ( $manage_list_flg == 'Back' ) )
			{
				$this->show_update_form = false;
				// show update form false
			}
			else if( $manage_list_flg == 'Edit' )
			{
				$this->show_update_form = true;
			}				
			else
			{
				if( $manage_list_flg == 'Add' )
				{
					$saved_msg = "Directive List added Successfully";
					$this->validate_add_list( $mange_lst_opt, $saved, $err_msg );
				}
				if( $manage_list_flg == 'Delete List' )
				{
					$saved_msg = "Directive List deleted Successfully";
					$this->delete_list( $mange_lst_opt, $saved, $err_msg ); // validation already done in javascript .. hence directly delete list from db
				}
				if( $manage_list_flg == 'Save' )
				{
					$saved_msg = "List Member saved Successfully";
					$this->validate_save_lst_member( $mange_lst_opt, $saved, $err_msg );
				}
				if( $manage_list_flg == 'Delete Members' )
				{
					$saved_msg = "List Member deleted Successfully";
					$this->delete_list_member( $mange_lst_opt, $saved, $err_msg ); // validAtion already done in javascript.. hence directly delete list member from db
				}
				
				$this->display_message( $saved, $saved_msg, $err_msg );
			
			}
		}
		
		function validate_manage_alias( $manage_alias_opt, $manage_aliases_flg )
		{
			// GLOBAL $OUTPUT;
			switch ( $manage_aliases_flg )
			{
				case 'Delete':
					$success_msg = "Alias Deleted Successfully";
					$this->delete_alias( $manage_alias_opt, $saved, $err_msg );
					break;	
				case 'Add':
					$success_msg = "Alias Added Successfully";
					$this->validate_add_alias( $manage_alias_opt, $saved, $err_msg );
					break;	
			}
			
			$this->display_message( $saved, $success_msg, $err_msg );
			
		}
		
		function delete_alias( $manage_alias_opt, &$saved, &$err_msg )
		{
			$aliases_src_str = $manage_alias_opt[ 'aliases_src_str' ]; //  alias name
			$aliases_dest_str = $manage_alias_opt[ 'aliases_dest_str' ]; // original email
			
			$aliases_src_arr = explode( "|", $aliases_src_str ); // alias name array
			$aliases_dest_arr = explode( "|", $aliases_dest_str ); // orig email array
			
			for( $i = 0; $i < count( $aliases_src_arr ); $i++ )
			{
				$delete_query = "delete from user_aliases where email_src='".$aliases_src_arr[ $i ]."' and email_dest='".$aliases_dest_arr[ $i ]."' and mydomain='".$this->admin_selected_domain."' and isaliase=1;";
				
				if( $this->_DB->query( $delete_query ) )
				{
					$saved = true;
				}
				else
				{
					$err_msg = "Error while deleting Alias from Database";
					$saved = false;
					break;
				}
			}
			
		}
		
		function validate_add_alias( $manage_alias_opt, &$saved, &$err_msg )
		{
			$alias_name = $manage_alias_opt[ '_add_alias_nm' ];
			$original_name = $manage_alias_opt[ '_add_alias_orig_nm' ];
			if( strlen( $alias_name ) == 0 )
			{
				$err_msg = "Please Provide the Alias Name";
				$saved = false;
			}
			else if( strlen( $alias_name ) > 50 )
			{
				$err_msg = "Alias Name cannot be more than 50 Characters";
				$saved = false;
			}
			else if( strlen( $original_name ) == 0 )
			{
				$err_msg = "Please Provide the Original Email";
				$saved = false;
			}
			else if( strlen( $original_name ) > 50 )
			{
				$err_msg = "Original Email cannot be more than 50 Characters";
				$saved = false;
			}
			else if( preg_match( '/\s/', $alias_name ) )
			{
				$err_msg = "Alias Name cannot Contain Spaces";
				$saved = false;
			}
			else if( preg_match( '/\s/', $original_name ) )
			{
				$err_msg = "Original Email cannot contain Spaces";
				$saved = false;
			}
			else
			{
				$this->add_alias( $manage_alias_opt, $saved, $err_msg );
			}
		}
		
		function validate_add_list( $mange_lst_opt, &$saved, &$err_msg )
		{
			if( strlen( $mange_lst_opt[ '_add_list_name' ] ) == 0 )
			{
				$err_msg = "Please Provide List Name";
				$saved = false;
			}
			else if ( preg_match( '/\s/', $mange_lst_opt[ '_add_list_name' ] ) )
			{
				$err_msg = "List Name cannot contain Spaces";
				$saved = false;
			}
			else if( strlen( $mange_lst_opt[ '_add_list_name' ] ) > 50 )
			{
				$err_msg = "List Name cannot be more than 50 Characters";
				$saved = false;
			}
			else
			{
				$this->add_list( $mange_lst_opt, $saved, $err_msg );	
			}
		}
		
		// DISPLAY RULES FIELDS
		
		// DISPLAY DESCRIPTION
		function display_description( $desc )
		{
			$desc_lbl =  html::span('rules_opt_desc', $desc );
			$blocks['main']['options']['desc_message'] = array(
				'content' => $desc_lbl
			);
			
			return $blocks['main']['options']['desc_message'];
		}
		
		function display_message( $saved, $success_msg, $err_msg )
		{
			global $OUTPUT;
			
			if( $saved )
			{
				$OUTPUT->show_message( $success_msg, 'confirmation' );
			}
			else
			{
				$OUTPUT->show_message( $err_msg, 'error' );
			}
		}
		
		// OUT OF OFFICE
		
		// These methods will not be needed
		function display_oof_enable( $enabled )
		{
			$out_of_office_enable = new html_checkbox( array( 'name' => 'enable_oof_details[]', 'id' => 'enable_oof_details', 'value' => 1, 'onclick' => 'UI.toggle_oof_details($(this).parent().parent())' ) );
			
			if( $enabled == '1' )
			{
				$blocks['main']['options']['oof_enabled'] = array(
					'title' => 'Enable',
					'content' => $out_of_office_enable->show( 1, array( 'checked' => 'true' ) )
				);
			}
			else
			{
				$blocks['main']['options']['oof_enabled'] = array(
					'title' => 'Enable',
					'content' => $out_of_office_enable->show()
				);
			}
			
			return $blocks['main']['options']['oof_enabled'];
			
			/* $field_id = 'rcmfd_enable';
			$out_of_office_enable = new html_checkbox( array( 'name' => '_out_of_office_enable', 'id' => $field_id, 'value' => 1, 'onclick' => 'UI.enable_outofoffice_tb();' ) );
							
			if( ( $_POST[ '_out_of_office_enable' ] == '1' ) || ( $enabled == '1' ) )
			{
				$blocks['main']['options']['oof_enabled'] = array(
					'title' => html::label($field_id, Q(rcube_label('outofofficeenable'))),
					'content' => $out_of_office_enable->show( 1, array( 'checked' => 'true' ) ),
				);
			}
			else
			{
				$blocks['main']['options']['oof_enabled'] = array(
					'title' => html::label($field_id, Q(rcube_label('outofofficeenable'))),
					'content' => $out_of_office_enable->show(),
				);
			} */
			
		}
		
		function display_oof_header( $selected_header, $is_enabled )
		{
			$oof_header_array = array( 'All', 'From', 'Subject', 'To' );
			
			if( $is_enabled == '1' )
			{
				$oof_header = new html_select( array( 'name' => 'oof_header[]', 'id' => 'oof_header', 'onclick' => 'UI.set_oof_rule_header($(this).parent().parent())' ) );
			}
			else
			{
				$oof_header = new html_select( array( 'name' => 'oof_header[]', 'id' => 'oof_header', 'onclick' => 'UI.set_oof_rule_header($(this).parent().parent())', 'disabled' => 'disabled' ) );
			}
			
			for( $i = 0; $i < count( $oof_header_array ); $i++ )
			{
				$oof_header->add( $oof_header_array[ $i ], $i );
			}
			
			$blocks['main']['options']['oof_header'] = array(
				'title' => 'Header',
				'content' => $oof_header->show( $oof_header_array[ $selected_header ] )
			);
			
			return $blocks['main']['options']['oof_header'];
		}
		
		function display_oof_filter( $filter_text, $header_value, $is_enabled )
		{
			$filter_textbox = new html_inputfield( array( 'name' => 'oof_header_match[]', 'id' => 'oof_header_match' ) );
			
			if( ( $header_value == '0' ) || ( $is_enabled == '0' ) )
			{
				$blocks['main']['options']['oof_filter'] = array(
					'title' => 'Match',
					'content' => $filter_textbox->show( $filter_text, array( "readonly" => "true", "style" => "background:#F0F0F0;" ) )
				);
			}
			else
			{
				$blocks['main']['options']['oof_filter'] = array(
					'title' => 'Match',
					'content' => $filter_textbox->show( $filter_text )
				);
			}
			
			
			
			return $blocks['main']['options']['oof_filter'];
			
		}
		
		function display_oof_subject( $subject, $is_enabled )
		{
			$subject_textbox = new html_inputfield(array('name' => 'oof_subject[]', 'id' => 'oof_subject'));	 
			
			if( $is_enabled == '1' )
			{
				$blocks['main']['options']['oof_subject'] = array(
					'title' => 'Subject',
					'content' => $subject_textbox->show( $subject )
				);
			}
			else
			{
				$blocks['main']['options']['oof_subject'] = array(
					'title' => 'Subject',
					'content' => $subject_textbox->show( $subject, array( 'readonly' => 'true', 'style' => 'background:#F0F0F0;' ) )
				);
			}
			
			return $blocks['main']['options']['oof_subject'];
			
			/* $field_id = 'rcmfd_sub';
				$_out_of_office_sub = new html_inputfield(array('name' => '_out_of_office_sub', 'id' => $field_id, 'size' => 30));	 
				
				if( ( $_POST[ '_out_of_office_enable' ] == '1' ) )
				{
					$subject_value = $_POST[ '_out_of_office_sub' ];
					$subject_prop = array();
				}
				else if( $enabled == '1' )
				{
					$subject_value = $subject;
					$subject_prop = array();
				}
				else if( $enabled == '0' )
				{
					$subject_value = $subject;
					$subject_prop = array( 'readonly' => 'true', 'style' => 'background:#F0F0F0;' );
				}
				else
				{
					$subject_value = $_POST[ '_out_of_office_sub' ];
					$subject_prop = array( 'readonly' => 'true', 'style' => 'background:#F0F0F0;' );
				}
				
				$blocks['main']['options']['oof_sub'] = array(
					'title' => html::label($field_id, Q(rcube_label('outOfOfficeSub'))),
					'content' => $_out_of_office_sub->show( $subject_value, $subject_prop ),
				);
				
				return $blocks['main']['options']['oof_sub']; */
		}
		
		function display_oof_message( $message, $is_enabled )
		{
			$oof_subject_textbox = new html_textarea( array( 'id' => 'oof_message', 'name' => 'oof_message[]', 'rows' => 10, 'cols' => 60 ) );
			
			if( $is_enabled == '0' )
			{
				$blocks['main']['options']['oof_subject'] = array(
					'title' => 'Message',
					'content' => $oof_subject_textbox->show( $message, array( 'readonly' => 'true', 'style' => 'background:#F0F0F0;' ) )
				);
			}
			else
			{
				$blocks['main']['options']['oof_subject'] = array(
					'title' => 'Message',
					'content' => $oof_subject_textbox->show( $message )
				);
			}
			
			
			
			return $blocks['main']['options']['oof_subject'];
			
		
			/* $field_id = 'rcmfd_message';
			$_out_of_office_message = new html_textarea(array('name' => '_out_of_office_message', 'id' => field_id, 'rows' => 10, 'cols' => 60));	 
			
			if( $_POST[ '_out_of_office_enable' ] == '1' )
			{
				$message_value = $_POST[ '_out_of_office_message' ];
				$message_prop = array();
			}
			else if( $enabled == '1' )
			{	
				$message_value = $message;
				$message_prop = array();
			}
			else if( $enabled == '0' )
			{
				$message_value = $message;
				$message_prop = array( 'readonly' => 'true', 'style' => 'background:#F0F0F0;' );
			}
			else
			{
				$message_value = $_POST[ '_out_of_office_message' ];
				$message_prop = array( 'readonly' => 'true', 'style' => 'background:#F0F0F0;' );
			}
			
			$blocks['main']['options']['message'] = array(
				'title' => html::label($field_id, Q(rcube_label('outOfOfficeMessage'))),
				'content' => $_out_of_office_message->show( $message_value, $message_prop ),
			);
			
			return $blocks['main']['options']['message']; */
		}
		
		function display_oof_remove_btn()
		{
			$remove_btn = html::a(array(
				'class' => 'rules_block_email_remove',
				'onclick' => 'UI.remove_oof_details($(this).parent().parent())',
				'href' => '#',
				'title' => 'Remove Block Email'
			), 'Remove');
			$blocks['main']['options']['oof_remove_btn'] = array(
				'content' => $remove_btn
			);
			
			return $blocks['main']['options']['oof_remove_btn'];
		}
		
		function display_oof_hidden_field( $value, $id, $name )
		{
			$hidden_header_field = new html_hiddenfield( array( 'id' => $id, 'name' => $name, 'value' => $value ) );
			$blocks['main']['options']['oof_hidden_header'] = array(
				'content' => $hidden_header_field->show()
			);
			return $blocks['main']['options']['oof_hidden_header'];
		}
		
		// display oof add button
		function display_oof_add_button()
		{
			$add_field =  html::a(array(
				'href' => "#",
				'onclick' => "UI.add_oof_details()",
				'title' => 'Add Out Of Office Details',
				'class' => 'rules_block_email_add'
			),
			'Add');
					
			$blocks['main']['options']['oof_add'] = array(
				'content' => $add_field
			);
			
			return $blocks['main']['options']['oof_add'];
		}
		
		// BLOCK EMAILS
		function display_block_email_add_btn()
		{
			$add_field =  html::a(array(
				'href' => "#",
				'onclick' => "UI.add_block_email()",
				'title' => 'Add Block Emails',
				'class' => 'rules_block_email_add'
			),
			Q(rcube_label('outOfOfficeAdd')));
					
			$blocks['main']['options']['block_email_add'] = array(
				'content' => $add_field
			);
			
			return $blocks['main']['options']['block_email_add'];
		}
		
		function display_block_email_header( $header_label, $i, $headers )
		{
			$field_id = 'rcmfd_header';
			$select_header = new html_select(array('name' => '_block_email_header[]', 'id' => $field_id));
			
			foreach( $header_label as $key => $value )
				$select_header->add(rcube_label( $value ), $key);
			
			$blocks['main']['options']['header'.$i] = array(
				'title' => html::label($field_id, Q(rcube_label('header'))),
				'content' => $select_header->show((int)$headers[$i])
			);
			return $blocks['main']['options']['header'.$i];
		}
		
		function display_block_email_filter( $i, $filters )
		{
			$field_id = 'rcmfd_filter';
			$_block_email_filter = new html_inputfield(array('name' => '_block_email_filter[]', 'id' => $field_id, 'size' => 30, 'value' => $filters[ $i ]) );	 
			
			$blocks['main']['options']['filter'.$i] = array(
				'title' => html::label($field_id, Q(rcube_label('BlockFilter'))),
				'content' => $_block_email_filter->show(),
			);
			
			return $blocks['main']['options']['filter'.$i];
		}
		
		function display_block_email_remove( $i )
		{
			$remove_field = html::a(array(
				'href' => "#",
				'onclick' => "UI.remove_block_email($(this).parent().parent())",
				'title' => 'Remove Block Emails',
				'class' => 'rules_block_email_remove'
			),'Remove');
		
			$blocks['main']['options']['add'.$i] = array(
				'content' => $remove_field,
			);
			
			return $blocks['main']['options']['add'.$i];
		}
		
		// FORWARD RULE ADD BUTTON
		
		function display_fwd_rule_add_btn()
		{
			$add_field =  html::a(array(
				'href' => "#",
				'onclick' => "UI.add_forward_rule()",    //add_block_email -> add_forward_rule
				'title' => 'Add Block Emails',
				'class' => 'rules_block_email_add'
			),
			Q(rcube_label('outOfOfficeAdd')));
					
			$blocks['main']['options']['add'] = array(
				'content' => $add_field
			);
			
			return $blocks['main']['options']['add'];
		}
		
		function display_fwd_rule_header( $header_label, $headers, $i )
		{
			$field_id = 'rcmfwrule_header';    // Og -> rcmfd_header
			$select_header = new html_select(array('name' => '_forward_rule_header[]', 'id' => $field_id, 'onclick' => 'UI.set_fwd_rule_header();'));
			
			foreach( $header_label as $key => $value )
				$select_header->add(rcube_label( $value ), $key);
			
			$blocks['main']['options']['header'.$i] = array(
				'title' => html::label($field_id, Q(rcube_label('header'))),
				'content' => $select_header->show((int)$headers[$i])
			);
			
			return $blocks['main']['options']['header'.$i];
		}
		
		function display_fwd_rule_filter( $headers, $filters, $i )
		{
			$field_id = 'rcmfwrule_filter';
			$_forward_rule_filter = new html_inputfield(array('name' => '_forward_rule_filter[]', 'id' => $field_id, 'size' => 30) );
			if( $headers[ $i ] == "0" )
			{
				$blocks['main']['options']['filter'.$i] = array(
					'title' => html::label($field_id, "Match"),
					'content' => $_forward_rule_filter->show($filters[ $i ], array( 'readonly' => 'true', 'style' => 'background:#F0F0F0;' ) ),
				);
			}
			else
			{
				$blocks['main']['options']['filter'.$i] = array(
					'title' => html::label($field_id, "Match"),
					'content' => $_forward_rule_filter->show($filters[ $i ]),
				);
			}
			
			return $blocks['main']['options']['filter'.$i];
		}
		
		function display_fwd_rule_email( $post_forward_email, $i )
		{
			$field_id = 'rcmfwrule_email';
			$_forward_rule_email = new html_inputfield(array('name' => '_forward_rule_email[]', 'id' => $field_id, 'size' => 30, 'value' => $post_forward_email[ $i ]) );	 
			
			$blocks['main']['options']['email'.$i] = array(
				'title' => html::label($field_id, "Forward"), //email_notify_txt -> Email
				'content' => $_forward_rule_email->show(),
			);
			
			return $blocks['main']['options']['email'.$i];
		}
		
		function display_fwd_rule_remove_btn( $i )
		{
			$remove_field = html::a(array(
					'href' => "#",
					'onclick' => "UI.remove_row($(this).parent().parent())", //remove_row
					'title' => 'Remove Block Emails',
					'class' => 'rules_block_email_remove'
				),'Remove');
			
			$blocks['main']['options']['add'.$i] = array(
				'content' => $remove_field,
			);
			
			return $blocks['main']['options']['add'.$i];
		}
		
		// CUSTOM RULE
		
		function display_custom_rule_enable( $enabled )
		{
			$field_id = 'rcmfd_enable';
			$custom_rule_enable = new html_checkbox(array('name' => '_custom_rule_enable', 'id' => 'field_id', 'value' => 1, 'onclick' => 'UI.enable_custom_rule_tb()'));
							
			if( ( $_POST[ '_custom_rule_enable' ] == 1 ) || ( $enabled == '1' ) )
			{
				$blocks['main']['options']['field_id'] = array(
					'title' => html::label($field_id, Q(rcube_label('customruleenable'))),
					'content' => $custom_rule_enable->show( 1, array( 'checked' => 'true' ) ),
				);
			}
			else
			{
				$blocks['main']['options']['field_id'] = array(
					'title' => html::label($field_id, Q(rcube_label('customruleenable'))),
					'content' => $custom_rule_enable->show(),
				);
			}
			
			return $blocks['main']['options']['field_id'];
		}
		
		function display_custom_rule_message( $description, $enabled )
		{
			$field_id = 'rcmfd_message';
			$_custom_rule_desc = new html_textarea( array( 'name' => '_custom_rule_desc', 'id' => 'field_id', 'rows' => 10, 'cols' => 60) );	 
			
			if( $_POST[ '_custom_rule_enable' ] == 1 )
			{
				$desc_value = $_POST[ '_custom_rule_desc' ];
				$desc_prop = array();
			}
			else if( $enabled == '1' )
			{
				$desc_value = $description;
				$desc_prop = array();
			}
			else if( $enabled == '0' )
			{
				$desc_value = $description;
				$desc_prop = array( 'readonly' => 'true', 'style' => 'background:#F0F0F0;' );
			}
			else
			{
				$desc_value = $_POST[ '_custom_rule_desc' ];
				$desc_prop = array( 'readonly' => 'true', 'style' => 'background:#F0F0F0;' );
			}
			
			$blocks['main']['options']['message'] = array(
				'title' => html::label($field_id, Q(rcube_label('customRuleMessage'))),
				'content' => $_custom_rule_desc->show( $desc_value, $desc_prop ),
			);
			 
			return $blocks['main']['options']['message'];
		}
		
		// SET VALUES FOR RULES ARRAY
		
		// SET OOF RULES
		function set_oof_rules_arr( $post_vals )
		{
			/* $oof_rules[ '_out_of_office_enable' ] = ( ( isset( $post_vals[ '_out_of_office_enable' ] ) ) ? ( 1 ) : ( 0 ) );
			$oof_rules[ '_out_of_office_sub' ] = $post_vals[ '_out_of_office_sub' ];
			$oof_rules[ '_out_of_office_message' ] = $post_vals[ '_out_of_office_message' ]; */
			$oof_rules[ '_out_of_office_enable' ] = $post_vals[ 'hidden_oof_enabled' ];
			$oof_rules[ '_out_of_office_header' ] = $post_vals[ 'hidden_oof_header' ];
			$oof_rules[ '_out_of_office_match' ] = $post_vals[ 'oof_header_match' ];
			$oof_rules[ '_out_of_office_subject' ] = $post_vals[ 'oof_subject' ];
			$oof_rules[ '_out_of_office_message' ] = $post_vals[ 'oof_message' ];
			
			return $oof_rules;
		}
		
		function set_block_email_rules_arr( $post_vals )
		{
			$block_email_rules[ '_block_email_filter' ] = $post_vals[ '_block_email_filter' ];
			$block_email_rules[ '_block_email_header' ] = $post_vals[ '_block_email_header' ];
			return $block_email_rules;
		}
		
		function set_fwd_rules_arr( $post_vals )
		{
			$fwd_rules[ '_forward_rule_header' ] = $post_vals[ '_forward_rule_header' ];
			$fwd_rules[ '_forward_rule_filter' ] = $post_vals[ '_forward_rule_filter' ];
			$fwd_rules[ '_forward_rule_email' ] = $post_vals[ '_forward_rule_email' ];
			return $fwd_rules;
		}
		
		function set_custom_rule_arr( $post_vals )
		{
			$custom_rules[ '_custom_rule_enable' ] = ( ( isset( $post_vals[ '_custom_rule_enable' ] ) ) ? ( 1 ) : ( 0 ) );
			$custom_rules[ '_custom_rule_desc' ] = $post_vals[ '_custom_rule_desc' ];
			return $custom_rules;
		}
		
		// METHOD NOT NEEDED ANYMORE
		/* function validate_delete_list( $mange_lst_opt, &$saved, &$err_msg )
		{
			if( strlen( $mange_lst_opt[ 'delete_list_names' ] ) == 0 )
			{
				$err_msg = "Please select atleast 1 list to delete";
				$saved = false;
			}			
			else
			{	
				$this->delete_list( $mange_lst_opt, $saved, $err_msg );
			}
			$this->delete_list( $mange_lst_opt, $saved, $err_msg );
		} */
		
		function validate_save_lst_member( $mange_lst_opt, &$saved, &$err_msg )
		{
			// $this->show_update_form = true;
			$list_member_type = $mange_lst_opt[ '_edit_list_type' ];
			$external_member_email = $mange_lst_opt[ '_edit_list_ext_email' ];
			$local_member_email_str = $mange_lst_opt[ '_edit_list_hidden_field' ];
			switch( $list_member_type )
			{
				case 'local':
					if( strlen( $local_member_email_str ) == 0 )
					{
						$saved = false;
						$err_msg = "Please select atleast 1 email to add to the List";
					}
					else
					{
						$this->save_list_member( $mange_lst_opt, $saved, $err_msg );
					}
					break;
						
				case 'external':
					if( strlen( $external_member_email ) == 0 )
					{
						$saved = false;
						$err_msg = "Please enter the External Email ID";
					}
					else
					{
						$this->save_list_member( $mange_lst_opt, $saved, $err_msg );
					}
					break;
			}
			$this->show_update_form = true;
		}
		
		function extract_folder_name( $full_folder_name )
		{
			if( strpos( $full_folder_name, "." ) != false )
			{
				$folder_split_arr = explode( ".", $full_folder_name );
				$folder_split_cnt = count( $folder_split_arr );
				return $folder_split_arr[ $folder_split_cnt - 1 ];
			}
			else
				return $full_folder_name;
		}
		
		function get_full_folder_name( $folder_list )
		{
			$folder_list_cnt = count( $folder_list );
			$all_folder_list = $this->storage->list_folders_direct();
			$full_folder_name = array();
			for( $i = 0; $i < $folder_list_cnt; $i++ )
			{
				foreach( $all_folder_list as $folder_name )
				{
					if( $this->extract_folder_name( $folder_name ) == $folder_list[ $i ] )
					{
						$full_folder_name[ $i ] = $folder_name;
						break;
					}
					else
					{
						$full_folder_name[ $i ] = $folder_list[ $i ];
					}
				}
			}
			return $full_folder_name;
		}
		
		function get_deliver_quota( $quota )
		{
			$storage_space = intval( $quota );
			$bytes = 1024 * 1024 * $storage_space;
			return( "*:storage=".$bytes );
		}
		
		function get_identities( $email = null )
		{
			$identities = array();
			if( $email )
			{
				$user_id = $this->rc->user->get_webmail_user_id( $email );
				$identities = $this->rc->user->list_identities_from( $user_id );
			}
			else
			{
				$identities_arr = $this->identities_list;
				for( $i = 0; $i < count( $identities_arr ); $i++ )
				{
					$identities['email'][] = $identities_arr[ $i ][ 'email' ];
					$identities['name'][] = $identities_arr[ $i ][ 'name' ];
				}
			}
			
			return $identities;
		}
		
		function get_bytes( $val )
		{
			$val_int = (int)$val;
			$bytes = 1;
			if( ( strpos( $val, "G" ) !== false ) || ( strpos( $val, "GB" ) !== false ) )
			{
				$bytes = $val_int * ( 1024 * 1024 * 1024 );
			}
			else if( ( strpos( $val, "M" ) !== false ) || ( strpos( $val, "MB" ) !== false ) )
			{
				$bytes = $val_int * ( 1024 * 1024 );
			}
			else if( ( strpos( $val, "K" ) !== false ) || ( strpos( $val, "KB" ) !== false ) )
			{
				$bytes = $val_int * ( 1024 );
			}
			else
			{
				$bytes = $val_int;
			}
			return $bytes;
		}
		
		// SEND MAIL TEST
		function get_formatted_recepients( $invitee_sent_email_arr, $invitee_sent_username_arr )
		{
			$formatted_recipient_arr = array();
			for( $i = 0; $i < count( $invitee_sent_email_arr ); $i++ )
			{
				$formatted_recipient_arr[] = ucfirst( strtolower( $invitee_sent_username_arr[ $i ] ) )." <".$invitee_sent_email_arr[ $i ].">";
			}
			$formatted_recipient = implode( ",", $formatted_recipient_arr );
			return $formatted_recipient;
		}
		
		/* CREATING SIEVE FILE CODE */
		
		function set_init_sieve_cont() // returns first line of Sieve File
		{
			return "require [\"vacation\", \"fileinto\", \"copy\"];\n";
		}
		
		function get_block_sieve( $block_header_idx, $block_filter, $total_blocked_emails ) // retrives Block Email contents with #_CONTINUE_# in else block
		{
			$block_sieve_cont = "";
			for( $i = 0; $i < $total_blocked_emails; $i++ )
			{
				$block_sieve_cont .= ( ( $i == 0 ) ? ( "if " ) : ( "\n elsif " ) ).( ( $this->block_header[ $block_header_idx[ $i ] ] == "From" ) ? ( "address" ) : ( "header" ) )." :contains [\"".$this->block_header[ $block_header_idx[ $i ] ]."\"] [\"".$block_filter[ $i ]."\"] \n{ \n discard; \n}";
			}
			return $block_sieve_cont."\nelse\n{\n#_CONTINUE_#\n}";
		}
		
		function get_from_email( $email = null )
		{
			// $identity = $this->identities_list[ 0 ][ 'name' ];
			$identities = $this->get_identities( $email );
			
			$name = $identities[ 'name' ][ 0 ];
			$email = $identities[ 'email' ][ 0 ];
			return "From \"$name <$email>\"";
		}
		
		function get_alias_emails( $email = null )
		{
			$alias_emails = array();
			$identities = $this->get_identities( $email );
			
			$alias_count = count( $identities[ 'email' ] );
			for( $i = 0; $i < $alias_count; $i++ )
			{
				$alias_emails[] = "\"".$identities[ 'email' ][ $i ]."\"";
			}
			
			return ( implode( ",", $alias_emails ) );
		}
		
		function get_main_vacation_sieve_content( $message, $subject, $email )
		{
			$main_vacation_sieve = "";
			$from = $this->get_from_email( $email );
			
			if( $email )
				$alias_emails = $this->get_alias_emails( $email );
			else
				$alias_emails = $this->get_alias_emails();
			
			// Actual Vacation Sieve
			$main_vacation_sieve .= "\nvacation";
			
			// From
			$main_vacation_sieve .= "\n	:$from"; // :From "username <username@domain.com>"
			
			//Addresses
			$main_vacation_sieve .= "\n	:addresses[$alias_emails]";
			
			// Subject
			$main_vacation_sieve .= "\n	:subject \"".$subject."\" ";
			
			// Message
			$main_vacation_sieve .= "\n	\"".$message."\";";
			
			return $main_vacation_sieve;
		}
		
		function get_vacation_sieve( $oof_details, $total_oof_rules, $email = null ) // retrives just Out Of Office contents
		{
			for( $i = 0; $i < $total_oof_rules; $i++ )
			{
				if( $oof_details[ $i ][ 'header' ] == '0' )
				{
					$all_filter_details = array_splice( $oof_details, $i, 1 );
					array_splice( $oof_details, $total_oof_rules, 0, $all_filter_details );
					break;
				}
			}
			
			$closing_bracket = ""; // used for header condts. if, elsif 
			$if_condt = false; // used for if else statement check
			$out_of_office_cont = "";
			
			if( ( $total_oof_rules == 1 ) && ( $oof_details[ $i ][ 'header' ] == '0' ) ) // if only 1 rule is selected and that is all, then directly apply rule
			{
				$out_of_office_cont .= $this->get_main_vacation_sieve_content( $oof_details[ $i ][ 'message' ], $oof_details[ $i ][ 'subject' ], $email );
			}
			else
			{
				for( $i = 0; $i < $total_oof_rules; $i++ )
				{
					// 1st Line: if header :contains "<oof header>" "<oof filter>"
					if( $oof_details[ $i ][ 'header' ] == '0' ) // if all is selected
					{
						$out_of_office_cont .= "\nelse";
					}
					else if( $if_condt == false )
					{
						$out_of_office_cont .= "\nif header :contains \"".$this->fwd_rule_header_val[ $oof_details[ $i ][ 'header' ] ]."\" \"".$oof_details[ $i ][ 'filter' ]."\"";
						$if_condt = true;
					}
					else
					{
						$out_of_office_cont .= "\nelsif header :contains \"".$this->fwd_rule_header_val[ $oof_details[ $i ][ 'header' ] ]."\" \"".$oof_details[ $i ][ 'filter' ]."\"";
					}
					
					// Opening bracket
					$out_of_office_cont .= "\n{";
					$out_of_office_cont .= $this->get_main_vacation_sieve_content( $oof_details[ $i ][ 'message' ], $oof_details[ $i ][ 'subject' ], $email );
					$out_of_office_cont .= "\n}";
				}
			}
			$out_of_office_cont .= "\n#_CONTINUE_#\n";
			return $out_of_office_cont;
			
			/* $closing_bracket = ""; // used for header condts. if, elsif 
			$if_condt = false; // used for if else statement check
			$out_of_office_cont = "";
			
			// first check for all filter.. if array contains all, then display it first and then remove it form array
			for( $i = 0; $i < $total_oof_rules; $i++ )
			{
				if( $oof_header[ $i ] == '0' ) // if all filter is set
				{
					$out_of_office_cont .= $this->get_main_vacation_sieve_content( $oof_message[ $i ], $oof_subject[ $i ], $email );
					
					unset( $oof_subject[ $i ] );
					unset( $oof_message[ $i ] );
					unset( $oof_header[ $i ] );
					unset( $oof_filter[ $i ] );
					// reinitialize keys of array
					$oof_subject = array_values( $oof_subject );
					$oof_message = array_values( $oof_message );
					$oof_header = array_values( $oof_header );
					$oof_filter = array_values( $oof_filter );
					$total_oof_rules = $total_oof_rules - 1;
					break;
				}
			}
			
			for( $i = 0; $i < $total_oof_rules; $i++ )
			{
				// 1st Line: if header :contains "<oof header>" "<oof filter>"
				if( $if_condt == false )
				{
					$out_of_office_cont .= "\nif header :contains \"".$this->fwd_rule_header_val[ $oof_header[ $i ] ]."\" \"".$oof_filter[ $i ]."\"";
					$if_condt = true;
				}
				else
				{
					$out_of_office_cont .= "\nelsif header :contains \"".$this->fwd_rule_header_val[ $oof_header[ $i ] ]."\" \"".$oof_filter[ $i ]."\"";
				}
				
				// Opening bracket
				$out_of_office_cont .= "\n{";
				
				$out_of_office_cont .= $this->get_main_vacation_sieve_content( $oof_message[ $i ], $oof_subject[ $i ], $email );
				
				$out_of_office_cont .= "\n}";
			}
			
			error_log( $out_of_office_cont );
			
			$out_of_office_cont .= "\n#_CONTINUE_#\n";
			return ''; */
		}
		
		function get_custom_rule_sieve( $custom_rule_description ) // retrives Custom Rule contents with #_CONTINUE_# in else block
		{
			return $custom_rule_description;
			/* if( strpos( $custom_rule_description, "#_CONTINUE_#" ) == false )
			{
				return $custom_rule_description."\nelse\n{\n\t#_CONTINUE_#\n}";
			}
			else
			{
				return $custom_rule_description;
			} */
		}
		
		function get_fwd_rule_sieve( $fwd_rule_cnt, $fwd_rule_header, $fwd_rule_filter, $fwd_rule_email )
		{
			$fwd_rule_sieve = "";
			for( $i = 0; $i < $fwd_rule_cnt; $i++ )
			{
				if( $fwd_rule_header[ $i ] == "0" )
				{
					$fwd_rule_sieve .= "\nredirect \"".$fwd_rule_email[ $i ]."\";" ;
				}
				else
				{
					$fwd_rule_sieve .= "\nif header :contains \"".$this->fwd_rule_header_val[ $fwd_rule_header[ $i ] ]."\" \"".$fwd_rule_filter[ $i ]."\" \n{\n   redirect \"".$fwd_rule_email[ $i ]."\"; \n} " ;
				}
			}
			$fwd_rule_sieve = $fwd_rule_sieve."\n#_CONTINUE_#";
			return $fwd_rule_sieve;
		}
		
		function get_folder_rule_sieve( $folder_rule_folder_nm, $folder_rule_enabled, $folder_rule_filter, $folder_rule_filter_match )
		{
			$folder_rule_sieve = "";
			$folder_count = count( $folder_rule_folder_nm );
			$end_condt = "\nkeep;\n";
			for( $i = 0; $i < $folder_count; $i++ )
			{
				if( $folder_rule_enabled[ $i ] == '1' )
				{
					if( $i == 0 )
						$folder_rule_sieve = "\nif header :contains \"".$this->folder_rule_header[ $folder_rule_filter[ $i ] ]."\" \"".$folder_rule_filter_match[ $i ]."\" \n{ \n\tfileinto \"".$folder_rule_folder_nm[ $i ]."\"; \n\tstop; \n}";
					else
						$folder_rule_sieve .= "\nelsif header :contains \"".$this->folder_rule_header[ $folder_rule_filter[ $i ] ]."\" \"".$folder_rule_filter_match[ $i ]."\" \n{ \n\tfileinto \"".$folder_rule_folder_nm[ $i ]."\"; \n\tstop; \n}";
				}
			}
			return $folder_rule_sieve.$end_condt;
		}
		
		// BytemarkDNS file Contents
		function get_BytemarkDNS_main_content( $domain = null )
		{
			$BytemarkDNS_main_content_str = "";
			$BytemarkDNS_main_content = array();
			
			$BytemarkDNS_main_content[] = ".$domain::a.ns.bytemark.co.uk";
			$BytemarkDNS_main_content[] = ".$domain::b.ns.bytemark.co.uk";
			$BytemarkDNS_main_content[] = ".$domain::c.ns.bytemark.co.uk\n";
			
			$BytemarkDNS_main_content[] = "# MX";
			$BytemarkDNS_main_content[] = "@$domain:mgmailx.default.myindian.uk0.bigv.io:a:5:::nospam";
			$BytemarkDNS_main_content[] = "@$domain::a.nospam.bytemark.co.uk:5:::world";
			$BytemarkDNS_main_content[] = "@$domain::b.nospam.bytemark.co.uk:10:::world\n";
			
			$BytemarkDNS_main_content[] = "# TXT record for SPF";
			$BytemarkDNS_main_content[] = "'$domain:v=spf1 redirect=spf.mgtech.in:3600";
			
			for( $i = 0; $i < count( $BytemarkDNS_main_content ); $i++ )
			{
				$BytemarkDNS_main_content_str = $BytemarkDNS_main_content_str.$BytemarkDNS_main_content[ $i ]."\n";
			}
			
			return $BytemarkDNS_main_content_str;
		}
		
		function get_BytemarkDNS_domain_record_content( $rec_name, $rec_type, $rec_value, $domain = null )
		{
			if( $rec_type == 'A' )
			{
				if( $rec_name == '@' )
				{
					$BytemarkDNS_domain_record_content = "=$domain:$rec_value:86400";
				}
				else
				{
					$BytemarkDNS_domain_record_content = "+$rec_name.$domain:$rec_value:86400";
				}
			}
			else if( $rec_type == 'CNAME' )
			{
				$BytemarkDNS_domain_record_content = "C$rec_name.$domain:$rec_value";
			}
			
			return $BytemarkDNS_domain_record_content."\n";
		}
	}
	
?>