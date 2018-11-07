<?php
	
	require_once 'program/include/iniset.php';
	
	echo( "users prefs fix called"."<br>" );
	$RCMAIL = rcmail::get_instance();
	$RCUBE = rcube::get_instance();
	
	$db_mailx = 'mysql://webmail:webmail@localhost/webmail';
	$db = rcube_db::factory($db_mailx, '', false);
	$db->db_connect('w');
	
	$host = $RCMAIL->config->get( 'default_host', '127.0.0.1' );
	
	$domain = 'mgtech.in';
	
	if (!($db_error_msg = $db->is_error())) {
		echo( "connected to webmail"."<br>" );
	}
	else {
		echo( "not connected to webmail"."<br>" );
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
		$serialized_prefs = $user_details[ 'preferences' ][ $i ];
		$prefs_array = unserialize( $serialized_prefs );
		if( isset( $prefs_array[ 'caldav_user' ] ) )
		{
			if( $prefs_array[ 'caldav_user' ] == '%u' )
			{
				echo( "%u present in caldav_user ".$user_details[ 'username' ][ $i ]."<br>" );
				$prefs_array[ 'caldav_user' ] = $user_details[ 'username' ][ $i ];
				echo( "after changing ".$prefs_array[ 'caldav_user' ] );
			}
			else
			{
				echo( "%u not present".$user_details[ 'username' ][ $i ]."<br>" );
			}
		}
		
		if( isset( $prefs_array[ 'caldav_url' ] ) )
		{
			if( strpos( $prefs_array[ 'caldav_url' ], "%u" ) != false )
			{
				echo( "%u present in caldav_url  ".$user_details[ 'username' ][ $i ]."<br>" );
				$prefs_array[ 'caldav_url' ] = str_replace( "%u", $user_details[ 'username' ][ $i ], $prefs_array[ 'caldav_url' ] );
				echo( "after changing caldav url : ".$prefs_array[ 'caldav_url' ] );
			}
			else	
				echo( "%u not present in caldav url ".$user_details[ 'username' ][ $i ]."<br>" );
				
			if( strpos( $prefs_array[ 'caldav_url' ], "events" ) === false )
			{
				$prefs_array[ 'caldav_url' ] = $prefs_array[ 'caldav_url' ]."/events";
				echo( "events not present in caldav url"."<br>" );
				echo( $prefs_array[ 'caldav_url' ]."<br>" );
			}
			else
			{
				echo( "events present in caldav url"."<br>" );
				echo( $prefs_array[ 'caldav_url' ]."<br>" );
			}
		}
		$serialized_prefs = serialize( $prefs_array );
		$RCUBE->user->set_specific_user_pref( $serialized_prefs, $user_details[ 'username' ][ $i ] );
	}
	echo( "<br>" );
	
	
?>