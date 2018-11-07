<?php

	require_once 'program/include/iniset.php';
	
	$rc_mail = rcmail::get_instance();	
	$_continue = false;
	
	$db_mailx = 'mysql://mailx:mailx@localhost/mailx';
	
	$conn_db_mailx = rcube_db::factory($db_mailx, '', false);
	$conn_db_mailx->db_connect('w');
	
	if (!($db_error_msg = $conn_db_mailx->is_error())) {
		$_continue = true;
	}
	else {
		$_continue = false;
	}
	
	if( ( isset( $_GET[ 'main' ] ) ) && ( $_GET[ 'main' ] == 'dom_em' ) )
	{
		$selected_domain = $_GET[ 'sel_dom' ];
		$ret_array = get_users( $selected_domain );
		$emails = $ret_array[ 'email' ]; 
		
		$result_str = implode( "|", $emails );
		echo( $result_str ); // Value Echod because this echo is grabbed by javascript
	}
	
	// New Event invite attendees.
	if( isset( $_GET[ 'main' ] ) && ( $_GET[ 'main' ] == 'auto_complete' ) )
	{
		$address_book_type = array( "sql", "collected" );
		
		foreach( $address_book_type as $type )
		{
			$address_book = $rc_mail->get_address_book( $type ); // create object of addressbook
			$address_book->set_pagesize(9999); // set page size
			$address_records = $address_book->list_records(); // get all records..
			while( $contact = $address_records->next() ) // loop through records..
			{
				if( isset( $contact[ 'email:home' ] ) )
					$contacts_useremail = $contact[ 'email:home' ][ 0 ];
				if( isset( $contact[ 'email:work' ] ) )
					$contacts_useremail = $contact[ 'email:work' ][ 0 ];
				if( isset( $contact[ 'email:other' ] ) )
					$contacts_useremail = $contact[ 'email:other' ][ 0 ];
					
				$contacts_username = $contact[ 'name' ];
				$formatted_contact_detail = $contacts_username." &lt;".$contacts_useremail."&gt;";
				$formatted_contact_detail_arr[] = $formatted_contact_detail;
			}
			
			foreach( $formatted_contact_detail_arr as $contact )
			{
				echo( $contact."\n" );
			}
		}
	}
	
	if( ( isset( $_GET[ 'main' ] ) ) && ( $_GET[ 'main' ] == 'cal_share' ) )
	{
		$users_subscribed_arr = array();
		$caldav_url = $_GET[ 'url' ];
		$username = $_GET[ 'username' ];
		
		$username_arr = explode( "@", $username );
		$user_domain = $username_arr[ 1 ];
		
		$all_users_arr = get_users( $user_domain );
		$all_users_email = $all_users_arr[ 'email' ];
		
		for( $i = 0; $i < count( $all_users_email ); $i++ )
		{
			if( $all_users_email[ $i ] != $username ) // check for all users other than current user
			{
				$user_prefs = $rc_mail->user->get_user_prefs_by_username( $all_users_email[ $i ] );
				
				if( isset( $user_prefs[ 'caldav_url' ] ) ) // first check if user's default calendar.. 
				{
					$user_default_cal = str_replace( "%u", $all_users_email[ $i ], $user_prefs[ 'caldav_url' ] );
					
					if( $user_default_cal == $caldav_url )
					{
						// if selected calendar url is equal to user's default calendar url, set user and continue for next user
						$users_subscribed_arr[] = $all_users_email[ $i ];
						continue;
					}
				}
				
				$user_caldavs = $user_prefs[ 'caldavs' ]; // This retrives all the calendars of the specific user, other than default caldav
				
				foreach( $user_caldavs as $user_calendar ) // Looping through all the calendars
				{
					$url = $user_calendar[ 'url' ];
					if( $url == $caldav_url ) // ie. if the user is subscribed to the same calendar
					{
						$users_subscribed_arr[] = $all_users_email[ $i ];
					}
				}
			}
		}
		echo json_encode($users_subscribed_arr);
	}
	else if( ( isset( $_GET[ 'main' ] ) ) && ( $_GET[ 'main' ] == 'attendee_em' ) )
	{
		$useremail = $_SESSION[ 'username' ];
		$user_detail_arr = array();
		
		$explode_username = explode( "@", $useremail );
		$username = $explode_username[ 0 ];
		$domain = $explode_username[ 1 ];
		
		if( isset( $_GET[ 'all' ] ) ) // if all is set, then, retrive all emails
		{
			$all_users_details = get_users( $domain );
			$all_users_email = $all_users_details[ 'email' ];
			$all_username = $all_users_details[ 'userid' ];
			for( $i = 0; $i < count($all_username); $i++ )
			{
				$user_detail_arr[] = $all_users_email[ $i ]."|".$all_username[ $i ];
			}
		}
		
		echo json_encode( $user_detail_arr );
	}
	
	function get_users( $user_domain )
	{
		global $conn_db_mailx;
		$_query = "select email, userid from users where mydomain = '$user_domain';";
		
		if( $query_result = $conn_db_mailx->query( $_query ) )
		{
			while ($row = $conn_db_mailx->fetch_array($query_result)) {
				$ret_array['email'][] = $row[ 0 ];
				$ret_array['userid'][] = $row[ 1 ];
			}
		}
		
		return $ret_array;
	}
	
?>