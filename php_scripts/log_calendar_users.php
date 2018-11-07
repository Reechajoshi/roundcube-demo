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
    
    for( $i = 0; $i < count( $user_details[ 'user_id' ] ); $i++ )
	{
        $serialized_prefs = $user_details[ 'preferences' ][ $i ];
        
        $prefs_array = unserialize( $serialized_prefs );
        
    }
    
?>