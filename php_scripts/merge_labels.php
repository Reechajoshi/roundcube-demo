<?php
    
    include( '../program/localization/en_GB/labels.inc' );
    include( 'old_labels.inc' );
    
    $label_file = '../program/localization/en_GB/labels.inc';
    
    echo( "file included \n" );
    
    echo( "count of labels array: ".count( $labels )." \n" );
    echo( "count of temp labels array: ".count( $temp_labels )." \n" );
    
    foreach( $temp_labels as $key => $value )
    {
        if( !array_key_exists( $key, $labels ) && $key != 'savenewresponse' && $key != 'newcontactgroup' && $key != 'newitem' && $key != 'edititem' )
        {
            $append_label_array_str = '$labels[ \''.$key."'] = '".$value."'; \n";
            echo( $append_label_array_str );
        }
    }
    
    
?>