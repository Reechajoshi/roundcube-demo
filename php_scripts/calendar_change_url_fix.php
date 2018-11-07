<?php

    echo( "rrr" );
	require_once '../program/include/iniset.php';

	echo( "users calendar change url fix called"."\n" );
	
	$RCMAIL = rcmail::get_instance();
	$RCUBE = rcube::get_instance();
	
	$db_mailx = 'mysql://webmail:webmail@localhost/webmail';
	$db = rcube_db::factory($db_mailx, '', false);
	$db->db_connect('w');
	
	$host = $RCMAIL->config->get( 'default_host', '127.0.0.1' );
	
	$domain = 'mgtech.in';
	
	if (!($db_error_msg = $db->is_error())) {
		echo( "connected to webmail"."\n" );
	}
	else {
		echo( "not connected to webmail"."\n" );
	}
	
	$_query = "select user_id, username, preferences from webmail.users;";
	$user_details = array();
	
	if( $query_result = $db->query( $_query ) )
	{
		while( $row = $db->fetch_array($query_result) )
		{
			$user_details[ 'user_id' ][] = $row[ 0 ];
			$user_details[ 'username' ][] = $row[ 1 ];
			$user_details[ 'preferences' ][] = $row[ 2 ];
		}
	}
	
	for( $i = 0; $i < count( $user_details[ 'user_id' ] ); $i++ )
	{
		echo( "\n" );
		echo( "Username: ======>".$user_details[ 'username' ][ $i ]."\n" );
		$serialized_prefs = $user_details[ 'preferences' ][ $i ];
		$prefs_array = unserialize( $serialized_prefs );
		
		if( isset( $prefs_array[ 'caldavs' ] ) )
		{
			echo( "Changing Caldavs url=====>"."\n" );
			foreach( $prefs_array[ 'caldavs' ] as $caldav_name => $caldav_details )
			{
				echo( "before changing: ".$prefs_array[ 'caldavs' ][ $caldav_name ][ 'url' ]."\n" );
				// $prefs_array[ 'caldavs' ][ $caldav_name ][ 'url' ] = str_replace( "http://cal.mgtech.in", "https://calendar.mgtech.in", $prefs_array[ 'caldavs' ][ $caldav_name ][ 'url' ] );
				// echo( "after changing: ".$prefs_array[ 'caldavs' ][ $caldav_name ][ 'url' ]."\n" );
				echo( "--------------------"."\n" );
			}
		}
		
		if( isset( $prefs_array[ 'caldavs_subscribed' ] ) )
		{
			echo( "Changing Caldavs_subscribed_url===========>"."\n" );
			foreach( $prefs_array[ 'caldavs_subscribed' ] as $shared_caldav_name => $shared_caldav_details )
			{
				echo( "Subscribed caldav url: ".$prefs_array[ 'caldavs_subscribed' ][ $shared_caldav_name ][ 'url' ]."\n" );
				
				// $prefs_array[ 'caldavs_subscribed' ][ $shared_caldav_name ][ 'url' ] = str_replace( "http://cal.mgtech.in", "https://calendar.mgtech.in", $prefs_array[ 'caldavs_subscribed' ][ $shared_caldav_name ][ 'url' ] );
				
				// echo( "After Changing: ".$prefs_array[ 'caldavs_subscribed' ][ $shared_caldav_name ][ 'url' ]."\n" );
				echo( "--------------------"."\n" );
			}
		}
		
		if( isset( $prefs_array[ 'caldav_url' ] ) )
		{
			echo( "Changing caldav_url===================>"."\n" );
			echo( "Before Changing: ".$prefs_array[ 'caldav_url' ]."\n" );
			// $prefs_array[ 'caldav_url' ] = str_replace( "http://cal.mgtech.in", "https://calendar.mgtech.in", $prefs_array[ 'caldav_url' ] );
			// echo( "After Changing: ".$prefs_array[ 'caldav_url' ]."\n" );
		}
		
		// $serialized_prefs = serialize( $prefs_array );
		// $RCUBE->user->set_specific_user_pref( $serialized_prefs, $user_details[ 'username' ][ $i ] );
		echo( "\n" );
	}
	
	$_query1 = "select event_id, url from events_caldav;";
	$events_caldav = array();
	
	if( $query_result1 = $db->query( $_query1 ) )
	{
		while( $row1 = $db->fetch_array($query_result1) )
		{
			$events_caldav[ 'event_id' ][] = $row1[ 0 ];
			$events_caldav[ 'url' ][] = $row1[ 1 ];
		}
	}
	
	for( $i = 0; $i < count( $events_caldav[ 'url' ] ); $i++ )
	{
		echo( "\n" );
		echo( "Changing Events Caldav URL====================>" );
		echo( "Before Changing: ".$events_caldav[ 'url' ][ $i ]."\n" );
		// $events_caldav[ 'url' ][ $i ] = str_replace( "http://cal.mgtech.in", "https://calendar.mgtech.in", $events_caldav[ 'url' ][ $i ] );
		echo( "after Changing: ".$events_caldav[ 'url' ][ $i ]."\n" );
		
		// $update_query = "update webmail.events_caldav set url='".$events_caldav[ 'url' ][ $i ]."' where  event_id='".$events_caldav[ 'event_id' ][ $i ]."';";
		// echo( $update_query."\n" );

		// $db->query( $update_query );
		// if($updated = $db->affected_rows())
			// echo( "changed"."\n" );
		// else	
			// echo( "update failed for event_id: ".$events_caldav[ 'event_id' ][ $i ]."\n" );
			
		echo( "\n" );
	}
?>