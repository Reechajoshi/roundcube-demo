<?php
	
	require_once 'program/include/iniset.php';

    $RCMAIL = rcmail::get_instance();
	$RCUBE = rcube::get_instance();
    
    $db_mailx = 'mysql://webmail:webmail@localhost/webmail';
	$db = rcube_db::factory($db_mailx, '', false);
	$db->db_connect('w');
    
    if (!($db_error_msg = $db->is_error())) {
		echo( "connected to webmail...."." \n" );
	}
	else {
		echo( "not connected to webmail...."." \n" );
	}
    
    $user_details = array();
    
    for( $i = 1; $i < count( $argv ); $i++ )
    {
        $query = "select user_id, username, preferences from users where username = '".$argv[ $i ]."';";        
        if( $query_result = $db->query( $query ) )
        {
            while( $row = $db->fetch_array($query_result) )
            {
                $user_details[ 'user_id' ][] = $row[ 0 ];
                $user_details[ 'username' ][] = $row[ 1 ];
                $user_details[ 'preferences' ][] = $row[ 2 ];
            }
        }
    }
    
    for( $i = 0; $i < count( $user_details[ 'user_id' ] ); $i++ )
	{
        echo( "Deleting calendars for user ".$user_details[ 'username' ][ $i ]."...."." \n" );
		$serialized_prefs = $user_details[ 'preferences' ][ $i ];
        echo( "Logging old Preferences in case of recovery...."." \n" );
        error_log( "logging old prefs for userid .... ".$user_details[ 'user_id' ][ $i ] );
        error_log( $serialized_prefs );
		$prefs_array = unserialize( $serialized_prefs );
        
        /* Unsetting Caldavs */
        if( isset( $prefs_array[ 'caldavs' ] ) )
        {
            echo( "Removing caldavs from preferences...."." \n" );
            unset( $prefs_array[ 'caldavs' ] );
        }
        
        /* Unsetting Categories */
        if( isset( $prefs_array[ 'categories' ] ) )
        {
            echo( "Removing categories from preferences...."." \n" );
            unset( $prefs_array[ 'categories' ] );
        }
        
        /* Unsetting Ctags */
        if( isset( $prefs_array[ 'ctags' ] ) )
        {
            echo( "Removing ctags from preferences...."." \n" );
            unset( $prefs_array[ 'ctags' ] );
        }
        
        /* Unsetting caldavs_subscribed */
        if( isset( $prefs_array[ 'caldavs_subscribed' ] ) )
        {
            echo( "Removing caldavs_subscribed from preferences...."." \n" );
            unset( $prefs_array[ 'caldavs_subscribed' ] );
        }
        
        /* Unsetting caldavs_removed */
        if( isset( $prefs_array[ 'caldavs_removed' ] ) )
        {
            echo( "Removing caldavs_removed from preferences...."." \n" );
            unset( $prefs_array[ 'caldavs_removed' ] );
        }
        
        /* Set calfilter_allcalendars to default calendar */
        echo( "Setting calfilter_allcalendars to default calendar.... "." \n" );
        $prefs_array[ 'calfilter_allcalendars' ] = array( $user_details[ 'username' ][ $i ] );
        
        /* Set event_filters_allcalendars to default calendar */
        echo( "Setting event_filters_allcalendars to default calendar...."." \n" );
        $prefs_array[ 'event_filters_allcalendars' ] = array( $user_details[ 'username' ][ $i ] );
        
        /* Unsetting caldavs_subscribed_prev */
        if( isset( $prefs_array[ 'caldavs_subscribed_prev' ] ) )
        {
            echo( "Removing caldavs_subscribed_prev from preferences..."." \n" );
            unset( $prefs_array[ 'caldavs_subscribed_prev' ] );
        }
        
        $serialized_prefs = serialize( $prefs_array );
        $RCUBE->user->set_specific_user_pref( $serialized_prefs, $user_details[ 'username' ][ $i ] );
        
    }
    
?>