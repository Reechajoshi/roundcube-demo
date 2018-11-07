<?php

    // This script was used to remove Macgregor Holiday calendar from all users
    
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
    
    $query = "select user_id, username, preferences from users;";  
    
    if( $query_result = $db->query( $query ) )
    {
        while( $row = $db->fetch_array($query_result) )
        {
            $user_details[ 'user_id' ][] = $row[ 0 ];
            $user_details[ 'username' ][] = $row[ 1 ];
            $user_details[ 'preferences' ][] = $row[ 2 ];
        }
    }
    
    // $log_file = fopen("logs/calendar_fix_log.txt", "w") or die("Unable to open file!");
    
    for( $i = 0; $i < count( $user_details[ 'user_id' ] ); $i++ )
	{
        $serialized_prefs = $user_details[ 'preferences' ][ $i ];
        
        $prefs_array = unserialize( $serialized_prefs );
        
        if( array_key_exists( 'Macgregor Holiday', $prefs_array[ 'caldavs_subscribed' ] ) )
        {
            // remove calendar from these users
            echo( "Removing Prefs for User ".$user_details[ 'username' ][ $i ]." \n"    );
            
            // remove from caldavs
            if( isset( $prefs_array[ 'caldavs' ][ 'Macgregor Holiday' ] ) )
            {
                echo( "Removing Macgregor Holiday from caldavs"." \n" );
                unset( $prefs_array[ 'caldavs' ][ 'Macgregor Holiday' ] );
            }
            
            // remove from categories
            if( isset( $prefs_array[ 'categories' ][ 'Macgregor Holiday' ] ) )
            {
                echo( "Removing Macgregor Holiday from categories"." \n" );
                unset( $prefs_array[ 'categories' ][ 'Macgregor Holiday' ] );
            }
            
            // remove from caldavs_subscribed
            if( isset( $prefs_array[ 'caldavs_subscribed' ][ 'Macgregor Holiday' ] ) )
            {
                echo( "Removing Macgregor Holiday from caldavs_subscribed"." \n" );
                unset( $prefs_array[ 'caldavs_subscribed' ][ 'Macgregor Holiday' ] );
            }
            
            // calfilter_allcalendars
            if( strpos( $prefs_array[ 'calfilter_allcalendars' ], 'Macgregor Holiday' ) !== false )
            {
                echo( "Removing Macgregor Holiday from calfilter_allcalendars"." \n" );
                $calfilter_allcalendars_arr = explode( ",", $prefs_array[ 'calfilter_allcalendars' ] );
                for( $k = 0; $k < count( $calfilter_allcalendars_arr ); $k++ )
                {
                    if( trim( $calfilter_allcalendars_arr[ $k ] ) == 'Macgregor Holiday' )
                    {
                        unset( $calfilter_allcalendars_arr[ $k ] );
                    }
                }
                $calfilter_allcalendars_str = implode( ",", $calfilter_allcalendars_arr );
                
                $prefs_array[ 'calfilter_allcalendars' ] = $calfilter_allcalendars_str;
            }
            
            // unset event_filters_allcalendars 
            if( isset( $prefs_array[ 'event_filters_allcalendars' ] ) )
            {
                echo( "Removing Macgregor Holiday from event_filters_allcalendars"." \n" );
                for( $j = 0; $j < count( $prefs_array[ 'event_filters_allcalendars' ] ); $j++ )
                {
                    if( trim( $prefs_array[ 'event_filters_allcalendars' ][ $j ] ) == 'Macgregor Holiday' )
                        unset( $prefs_array[ 'event_filters_allcalendars' ][ $j ] );
                }
            }
            
            $serialized_prefs = serialize( $prefs_array );
            $RCUBE->user->set_specific_user_pref( $serialized_prefs, $user_details[ 'username' ][ $i ] );
        }
    }
    
?>