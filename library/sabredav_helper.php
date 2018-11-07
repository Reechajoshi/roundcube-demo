<?php

	class rc_sabre_help{
		
		var $rc = null;
		var $_db = null;
		var $_continue = null;
		var $config = array(); // to store config array 
		var $realm = null;
		var $public_cal_write_principal_id = null; // used in groupmembers table
		var $public_cal_username = null;
		
		function rc_sabre_help()
		{
			global $CONFIG, $OUTPUT;
			$this->rc = rcmail::get_instance($GLOBALS['env']);
			$this->config = $CONFIG;
			$this->_continue = $this->init_DB();
			$this->realm = $this->config[ 'sabredav_realm' ]; // SabreDAV
			
			// this method sets the main email of caldav server(admin@mgtech.in), and the principal of write proxy fo this email
			$this->initialise_main_email(); 
		}
		
		function initialise_main_email()
		{
			$this->public_cal_username = $this->config[ 'public_caldav_user' ]; // email of user whose calendar will be public
			$public_cal_principal_details = $this->get_principal_details( "principals/".$this->public_cal_username."/calendar-proxy-write" );
			$this->public_cal_write_principal_id = $public_cal_principal_details[ 'id' ];
			
		}
		
		function init_DB()
		{
			if( !empty( $this->config[ 'db_calendar' ] ) ) {
				$this->_db = rcube_db::factory($this->config['db_calendar'], '', false);
				$this->_db->db_connect('w');
				
				if (!($db_error_msg = $this->_db->is_error())) {
					return true;
				}
				else {
					return false;
				}
			}
		}
		
		function add_user( $username, $password, $user_id )
		{
			global $RC_HELP;
			$saved = false;
			if( $this->insert_user( $username, $password, $user_id ) ) // inserts user details in users table
			{
				if( $this->insert_principal( $username ) ) // inserts user details in principals table
				{
					// $this->rc->user->save_prefs( array( 'caldavs_subscribed' => 'my_calendar'  ) ); // to subscribe to the default calendar
					// Since new user doesnot have access to public calendar, this is commented
					/* if( $this->insert_groupmember( $username ) ) // inserts user details in groupmembers table
					{
						return true;
					}
					else
					{
						error_log( "error inserting user into gropmember table of sabredav database" );
						return false;
					} */
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
		
		// To Insert User Details in 3 Tables
		function insert_user( $username, $password, $rcube_id )
		{
			$encrypted_pwd = $this->encrypt_pwd( $username, $password ); // encrypts password in md5( 'username:Sabredav:password' )
			$insert_query = "insert into users( rcube_id, username, digesta1 ) values( $rcube_id, '$username', '$encrypted_pwd' );";
			
			$this->_db->query( $insert_query );
			$updated = $this->_db->affected_rows();
			
			if( $updated )
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		
		function insert_principal( $username )
		{
			global $RC_HELP;
			$user_mailx_details = $RC_HELP->get_user_det_by_email( $username ); // returns the details of users table of mailx databse
			$display_name = $user_mailx_details[ 'user_name' ]; // stores the username from mailx databse in display_name variable
			$uri = array( "principals/$username", "principals/$username/calendar-proxy-read", "principals/$username/calendar-proxy-write" ); // since 3 rows are used to insert into principals table, uri to be inserted are stored in array
			
			$insert_query = "INSERT INTO principals (uri,email,displayname) VALUES
							('".$uri[ 0 ]."', '$username','$display_name'),
							('".$uri[ 1 ]."', null, null),
							('".$uri[ 2 ]."', null, null);";
			
			$this->_db->query( $insert_query );
			$updated = $this->_db->affected_rows();
			
			if( $updated )
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		
		function insert_groupmember( $calendar_user, $calendar_owner )
		{
			$calendar_user_principal_det = $this->get_principal_details( 'principals/'.$calendar_user ); // member_id
			$calendar_owner_principal_det = $this->get_principal_details( 'principals/'.$calendar_owner.'/calendar-proxy-write' ); // principal_id
			
			$calendar_user_principal_id = $calendar_user_principal_det[ 'id' ]; 
			$calendar_owner_principal_id = $calendar_owner_principal_det[ 'id' ]; 
			
			$insert_query = "insert into groupmembers( principal_id, member_id ) values( $calendar_owner_principal_id, $calendar_user_principal_id ) ON DUPLICATE KEY UPDATE principal_id=$calendar_owner_principal_id , member_id=$calendar_user_principal_id;";
			
			$this->_db->query( $insert_query );
			$updated = $this->_db->affected_rows();
			
			if( $updated )
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		
		function delete_account( $username, &$err_msg )
		{
			// TO DELETE THE CALENDARS ASSOCIATED WITH THE USER
			$_continue = true; // set to true if calendar is present and deleted successfully or else set to false;
			
			$user_cal_details = $this->get_calendar_from_username( $username );
			
			if( !empty( $user_cal_details ) ) // if calendars are present
			{
				if( $this->delete_calendars( $username, $err_msg ) ) // if calendars deleted successfully
				{
					$_continue = true;
				}
				else
				{
					$err_msg = "Error Deleting Calendars";
					$_continue = false;
				}
			}
			
			$sabredav_user_details = $this->get_sabredav_user_details( $username );
			
			if( !empty( $sabredav_user_details ) ) // IF user is present in sabredav db, only then delete the user
			{
				// after all calendar and calendarobjects are deleted, if present
				if( $this->delete_groupmember( $username, $err_msg ) )
				{
					if( $this->delete_principal( $username, $err_msg ) )
					{
						if( $this->delete_user( $username, $err_msg ) )
						{
							return true;
						}
						else
						{
							$err_msg = "Error Deleting User from users table";
							return false;
						}	
					}
					else
					{
						$err_msg = "Error Deleting User from principals table";
						return false;
					}
				}
				else
				{
					$err_msg = "Error Deleting User from groupmembers table";
					$_continue = false;
				}
			}
			else // since user is not present in sabredav db, there is no need to delete it.
				return true;
		}
		
		function delete_user( $username, &$err_msg ) // working fine
		{
			$delete_query = "delete from users where username = '$username';";
			$this->_db->query( $delete_query );
			if( $updated = $this->_db->affected_rows() )
			{
				return true;
			}
			else
			{
				$err_msg = "Error While Deleting User from Users table of Sabredav database";
				return false;
			}
		}
		
		function delete_principal( $username, &$err_msg ) // working fine
		{
			$delete_query = "delete from principals where uri in ( 'principals/$username', 'principals/$username/calendar-proxy-read', 'principals/$username/calendar-proxy-write' );";
			
			$this->_db->query( $delete_query );
			if( $updated = $this->_db->affected_rows() )
			{
				return true;
			}
			else
			{
				$err_msg = "Error While Deleting User from Principals table of Sabredav database";
				return false;
			}
		}
		
		function delete_groupmember( $username, &$err_msg ) // working fine
		{
			/* $user_principal_details = $this->get_principal_details( "principals/$username" );
			$member_id = $user_principal_details[ 'id' ];
			$delete_query = "delete from groupmembers where principal_id=".$this->public_cal_write_principal_id." and member_id=$member_id;";
			$this->_db->query( $delete_query );
			if( $updated = $this->_db->affected_rows() )
			{
				return true;
			}
			else
			{
				$err_msg = "Error While Deleting User from Groupmembers table of Sabredav database";
				return false;
			} */
			$delete_query = "delete from groupmembers where principal_id in ( select id from principals where uri like '%$username%' ) or member_id in ( select id from principals where uri like '%$username%' );";
			
			if( $this->_db->query( $delete_query ) )
			{
				return true;
			}
			else
			{
				$err_msg = "Error While Deleting User from Groupmembers table of Sabredav database";
				return false;
			}
		}
		
		function delete_calendar_events( $username, $user_cal_details, &$err_msg ) // testing pending
		{
			$_continue = false;
			$user_calendar_count = count( $user_cal_details[ 'id' ] );
			for( $i = 0; $i < $user_calendar_count; $i++ )
			{
				$calendar_id = $user_cal_details[ 'id' ][ $i ];
				$delete_query = "delete from calendarobjects where calendarid=$calendar_id;";
				
				$_continue = true;
				$this->_db->query( $delete_query );
				if( $updated = $this->_db->affected_rows() )
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
				return true;
			}
			else
			{
				$err_msg = "Error Deleting Calendar Events From Sabredav Database";
				return false;
			}
		}
		
		function delete_calendars( $username, &$err_msg ) // testing pending
		{
			$delete_query = "delete from calendars where principaluri = 'principals/$username';";
			
			$this->_db->query( $delete_query );
			if( $updated = $this->_db->affected_rows() )
			{
				return true;
			}
			else
			{
				$err_msg = "Error Deleting Calendars From Sabredav Database";
				return false;
			}
		}
		
		function update_sabredav_password( $username, $password )
		{
			$encrypted_password = $this->encrypt_pwd( $username, $password );
			$user_sabredav_details = $this->get_sabredav_user_details( $username );
			$user_sabredav_id = $user_sabredav_details[ 'id' ];
			
			$update_query = "update users set digesta1='$encrypted_password' where id=$user_sabredav_id";
			$this->_db->query( $update_query );
			if( $updated = $this->_db->affected_rows() )
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		
		function update_shared_calendar_password( $user_email, $new_password )
		{
			global $RC_HELP, $RCMAIL;
			$explode_user_email = explode( "@", $user_email );
			$user_domain = $explode_user_email[ 1 ];
			// retrieve all calendars of the user
			$user_prefs = $RC_HELP->rc->user->get_user_prefs_by_username( $user_email );
			$user_calendars = array( $user_prefs[ 'caldav_url' ] );
			if( isset( $user_prefs[ 'caldavs' ] ) )
			{
				foreach( $user_prefs[ 'caldavs' ] as $caldav_name => $caldav_details )
				{
					if( $caldav_details[ 'is_caldav_owner' ] == 1 )
					{
						$user_calendars[] = $caldav_details[ 'url' ];
					}
				}
			}
			
			$all_users_details = $RC_HELP->get_user_details( $user_domain );
			$all_users_email = $all_users_details[ 'user_email' ];
			
			foreach( $all_users_email as $email )
			{
				if( $email != $user_email )
				{
					$user_prefs = $RC_HELP->rc->user->get_user_prefs_by_username( $email );
					$caldav_details = $user_prefs[ 'caldavs' ];
					foreach( $caldav_details as $caldav_name => $caldav_details )
					{
						if( in_array( $caldav_details[ 'url' ], $user_calendars ) )
						{
							$calendar_password = $RCMAIL->decrypt( $caldav_details[ 'pass' ] );
							$user_prefs[ 'caldavs' ][ $caldav_name ][ 'pass' ] = $RCMAIL->encrypt( $new_password );
							$serialized_prefs = serialize( $user_prefs );
							$RC_HELP->rc->user->set_specific_user_pref( $serialized_prefs, $email );
						}
					}
				}
			}
			
		}
		
		function unsubscribe_all_users( $calendar_owner, $caldav_url, $unsub_caldav_name, $is_calendar_owner = false )
		{
			global $RC_HELP, $RCMAIL;
			
			$explode_calendar_owner = explode( "@", $calendar_owner );
			$calendar_owner_domain = $explode_calendar_owner[ 1 ];
			$calendar_owner = ( $is_calendar_owner ) ? ( $_SESSION[ 'username' ] ) : ( $calendar_owner );
			
			$all_users_details = $RC_HELP->get_user_details( $calendar_owner_domain );
			$all_users_email = $all_users_details[ 'user_email' ];
			
			foreach( $all_users_email as $user_email )
			{
				if( $user_email != $calendar_owner ) // DONOT UNSUBSCRIBE CALENDAR OWNER
				{
					$user_prefs = $RC_HELP->rc->user->get_user_prefs_by_username( $user_email );
					
					$caldav_subscribed = $user_prefs[ 'caldavs' ];
					if( !empty( $caldav_subscribed ) ) // if user has some calendars subscribed
					{
						foreach( $caldav_subscribed as $caldav_name =>$caldav )
						{
							$url = $caldav[ 'url' ];
							if( $url == $caldav_url ) // if caldav url is present in user's prefs, ie if user is subscribed to the calendar
							{
								// unset the caldav
								unset( $user_prefs[ 'caldavs' ][ $caldav_name ] );
								unset( $user_prefs[ 'categories' ][ $caldav_name ] );
								unset( $user_prefs[ 'caldavs_subscribed' ][ $caldav_name ] );
								$this->delete_subscribed_cal_events( $unsub_caldav_name, $user_email );
							}
							$serialized_pref = serialize( $user_prefs );
							
							$RCMAIL->user->set_specific_user_pref( $serialized_pref, $user_email );
						}
					}
				}
			}
		}
		
		function unsubscribe_all_calendars( $username )
		{
			$explode_username = explode( "@", $username );
			$user_domain = $explode_username[ 1 ];
			$user_prefs = $this->rc->user->get_user_prefs_by_username( $username );
			
			if( isset( $user_prefs[ 'caldav_url' ] ) ) // if user has default calendar, then unsubscribe all users from the default calendar
			{
				$calendar_url = str_replace( "%u", $username, $user_prefs[ 'caldav_url' ] ); // since default caldav contains %u replace it with username
				$this->unsubscribe_all_users( $username, $calendar_url, $username );
			}
			
			if( isset( $user_prefs[ 'caldavs' ] ) ) // if user has calendars other than default calendar
			{
				foreach( $user_prefs[ 'caldavs' ] as $calendar_name => $calendar_details )
				{
					$this->unsubscribe_all_users( $username, $calendar_details[ 'url' ], $calendar_name );
				}
			}
			
			return true;
		}
		
		function delete_subscribed_cal_events( $unsub_caldav_name, $user_email )
		{
			global $RC_HELP;
			$user_id = $RC_HELP->get_webmail_usrid( $user_email );
			$delete_query = "delete from webmail.events_caldav where user_id = $user_id and categories = '$unsub_caldav_name'";
			
			return ( $RC_HELP->_DB->query( $delete_query ) );
		}
		
		function get_subscribed_users_from_caldavurl( $caldav_url, $caldav_owner )
		{	
			global $RC_HELP, $RCMAIL;
			
			$subscribed_users = array();
			
			$explode_caldav_owner = explode( "@", $caldav_owner ); // to get the domain of the user
			$user_domain = $explode_caldav_owner[ 1 ]; // domain of the owner of the calendar
			$all_users_details = $RC_HELP->get_user_details( $user_domain );
			
			$all_users_email = $all_users_details[ 'user_email' ];
			for( $i = 0; $i < count( $all_users_email ); $i++ ) // loop through all the users and get their preferences
			{
				if( $all_users_email[ $i ] != $caldav_owner ) // calendar owner should not come in subscribed list
				{
					$user_prefs = $RCMAIL->user->get_user_prefs_by_username( $all_users_email[ $i ] );
					
					if( ( isset( $user_prefs[ 'caldav_url' ] ) ) && ( $user_prefs[ 'caldav_url' ] == $caldav_url ) )
					{
						$subscribed_users[] = $all_users_email[ $i ];
					}
					else
					{
						if( !empty( $user_prefs[ 'caldavs' ] ) )
						{
							foreach( $user_prefs[ 'caldavs' ] as $caldav_name => $caldav_details )
							{
								if( $caldav_details[ 'url' ] == $caldav_url )
								{
									$subscribed_users[] = $all_users_email[ $i ];
								}
							}
						}
					}
				}
			}
			return $subscribed_users;
		}
		
		// Functions which return specific values
		function encrypt_pwd( $username, $password )
		{
			return md5( "$username:".$this->realm.":$password" );
		}
		
		function get_principal_details( $uri ) // return all details of principals table based on uri
		{
			$select_query = "select id, uri, email, displayname, vcardurl from principals where uri = '$uri';";
			if( $query_result = $this->_db->query( $select_query ) )
			{
				while ($row = $this->_db->fetch_array($query_result)) {
					$ret_array[ 'id' ] = $row[ 0 ];
					$ret_array[ 'uri' ] = $row[ 1 ];
					$ret_array[ 'email' ] = $row[ 2 ];
					$ret_array[ 'displayname' ] = $row[ 3 ];
				}
			}
			
			return $ret_array;
		}
		
		function get_sabredav_user_details( $username )
		{
			$select_query = "select id, rcube_id, username, digesta1 from users where username = '$username';";
			if( $query_result = $this->_db->query( $select_query ) )
			{
				while ($row = $this->_db->fetch_array($query_result)) {
					$ret_array[ 'id' ] = $row[ 0 ];
					$ret_array[ 'rcube_id' ] = $row[ 1 ];
					$ret_array[ 'username' ] = $row[ 2 ];
					$ret_array[ 'digesta1' ] = $row[ 3 ];
				}
			}
			
			return $ret_array;
		}
		
		// for attendees ics contents
		function get_calendar_from_username( $username )
		{
			$select_query = "select id, principaluri, displayname, uri, ctag, description, calendarorder, calendarcolor, timezone, components, transparent from calendars where principaluri = 'principals/$username';";
			
			if( $query_result = $this->_db->query( $select_query ) )
			{
				while ($row = $this->_db->fetch_array($query_result)) {
					$ret_array[ 'id' ][] = $row[ 0 ];
					$ret_array[ 'principaluri' ][] = $row[ 1 ];
					$ret_array[ 'displayname' ][] = $row[ 2 ];
					$ret_array[ 'uri' ][] = $row[ 3 ];
					$ret_array[ 'ctag' ][] = $row[ 4 ];
					$ret_array[ 'description' ][] = $row[ 5 ];
					$ret_array[ 'calendarorder' ][] = $row[ 6 ];
					$ret_array[ 'calendarcolor' ][] = $row[ 7 ];
					$ret_array[ 'timezone' ][] = $row[ 8 ];
					$ret_array[ 'components' ][] = $row[ 9 ];
					$ret_array[ 'transparent' ][] = $row[ 10 ];
				}
			}
			
			return $ret_array;
		}
		
		function get_calendar_events_from_username( $username )
		{
			$select_query = "select etag, size, componenttype, firstoccurence, lastoccurence, id, calendardata, uri, calendarid, lastmodified from calendarobjects where calendarid in ( select id from calendars where principaluri = 'principals/$username' );";
			
			if( $query_result = $this->_db->query( $select_query ) )
			{
				while ($row = $this->_db->fetch_array($query_result)) {
					$ret_array[ 'etag' ][] = $row[ 0 ];
					$ret_array[ 'size' ][] = $row[ 1 ];
					$ret_array[ 'componenttype' ][] = $row[ 2 ];
					$ret_array[ 'firstoccurence' ][] = $row[ 3 ];
					$ret_array[ 'lastoccurence' ][] = $row[ 4 ];
					$ret_array[ 'id' ][] = $row[ 5 ];
					$ret_array[ 'calendardata' ][] = $row[ 6 ];
					$ret_array[ 'uri' ][] = $row[ 7 ];
					$ret_array[ 'calendarid' ][] = $row[ 8 ];
					$ret_array[ 'lastmodified' ][] = $row[ 9 ];
				}
			}
			
			return $ret_array;
		}
	}

?>