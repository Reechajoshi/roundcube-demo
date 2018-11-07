<?php
	
	class rc_mail{
	
		var $rc_hlp = null;
		var $rc = null;
		var $config = null;
		var $email = null;
		var $domain = null;
		var $user = null;
		
		function rc_mail()
		{
			global $RC_HELP, $CONFIG;
			$this->rc = rcube::get_instance();
			$this->config = $CONFIG;
			$this->rc_hlp = $RC_HELP;
			$this->email = $this->rc->get_user_email();
			$email_array = explode( '@', $this->email );
			$this->domain = $email_array[ 1 ];
			$this->user = $email_array[ 0 ];  
		}
		
		function publishSieve( $email = null )
		{
			if( $email )
			{
				$email_arr = explode( "@", $email );
				$user_name = $email_arr[ 0 ];
				$domain = $email_arr[ 1 ];
			}
			else
			{
				$user_name = $this->user;
				$domain = $this->domain;
			}
			
			// collect blocked emails details
			$block_mail_details = $this->rc_hlp->get_BlockEmail_details( $email );
			$block_header_idx = $block_mail_details[ 'header' ]; // retrives the header index ie. 0 for From, 1 for Subject
			
			$block_filter = $block_mail_details[ 'filter' ];
			$total_blocked_emails = count( $block_filter ); // gives the total count of the blocked emails
			
			// collect out of office details
			/* $out_of_off_details = $this->rc_hlp->get_OutOfOffice_details( $email );
			$oof_enabled = $out_of_off_details[ 'enabled' ];
			$oof_subject = $out_of_off_details[ 'subject' ];
			$oof_message = $out_of_off_details[ 'message' ]; */
			$out_of_off_details = $this->rc_hlp->get_OutOfOffice_details( $email, "and enabled = 1" );
			
			$oof_enabled = $out_of_off_details[ 'enabled' ];
			$oof_subject = $out_of_off_details[ 'subject' ];
			$oof_message = $out_of_off_details[ 'message' ];
			$oof_header = $out_of_off_details[ 'header' ];
			$oof_filter = $out_of_off_details[ 'filter' ];
			
			$total_oof_rules = count( $oof_enabled );
			// rearrange the oof details
			for( $i = 0; $i < $total_oof_rules; $i++ )
			{
				$new_oof_details[] = array( 
					'enabled' => $oof_enabled[ $i ], 
					'subject' => $oof_subject[ $i ], 
					'message' => $oof_message[ $i ], 
					'header' => $oof_header[ $i ], 
					'filter' => $oof_filter[ $i ] 
				);
			}
			
			// collect custom directive details
			$custom_rule_details = $this->rc_hlp->get_CustomRule_details( $email );
			$custom_rule_enabled = $custom_rule_details[ 'enabled' ];
			$custom_rule_description = $custom_rule_details[ 'description' ];
			
			// collect folder rule details
			$folder_rule_details = $this->rc_hlp->get_FolderRule_details(); // always returns array
			
			// $folder_rule_folder_nm = $this->rc_hlp->get_full_folder_name( $folder_rule_details[ 'folder_name' ] );
			$folder_rule_folder_nm = $folder_rule_details[ 'folder_name' ];
			
			$folder_rule_enabled = $folder_rule_details[ 'enabled' ];
			$folder_rule_filter = $folder_rule_details[ 'filter' ];
			$folder_rule_filter_match = $folder_rule_details[ 'filter_match' ];
			$total_folder_rules = ( ( $email ) ? ( 0 ) : ( count( $folder_rule_folder_nm ) ) ); // for manage rules, email var is set. if it is set, no folder rules are created. if not, then send count
			
			// collect forward rule details
			$fwd_rule_details = $this->rc_hlp->get_FwdRule_details( $email ); // always return an array
			$fwd_rule_header = $fwd_rule_details[ "header" ];
			$fwd_rule_filter = $fwd_rule_details[ "filter" ];
			$fwd_rule_email = $fwd_rule_details[ "forward_to_email" ];
			$fwd_rule_cnt = count( $fwd_rule_header );
			
			// file paths for storing the sieve file
			$file_path = "/var/vmail/$domain/$user_name/"; // location of .dovecot.sieve file
			$file_name = ".dovecot.sieve";
			$temp_file_path = "/var/vmail/$domain/$user_name/Maildir/tmp/";
			
			// send all data to create_sieve_contents() function where the contents of sieve file are created and written in sieve file
			if( $this->create_sieve_contents( $block_header_idx, $block_filter, $total_blocked_emails, $new_oof_details, $total_oof_rules, $custom_rule_enabled, $custom_rule_description, $folder_rule_folder_nm, $folder_rule_enabled, $folder_rule_filter, $folder_rule_filter_match, $total_folder_rules, $fwd_rule_cnt, $fwd_rule_header, $fwd_rule_filter, $fwd_rule_email, $file_path, $file_name, $temp_file_path, $email ) )
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		
		function create_sieve_contents( $block_header_idx, $block_filter, $total_blocked_emails, $oof_details, $total_oof_rules, $custom_rule_enabled, $custom_rule_description, $folder_rule_folder_nm, $folder_rule_enabled, $folder_rule_filter, $folder_rule_filter_match, $total_folder_rules, $fwd_rule_cnt, $fwd_rule_header, $fwd_rule_filter, $fwd_rule_email, $file_path, $file_name, $temp_file_path, $email = null )
		{
			$custom_rule_sieve = ( ( $custom_rule_enabled == '1' ) ? ( $this->rc_hlp->get_custom_rule_sieve( $custom_rule_description ) ) : ( "" ) );
			/* $out_of_office_sieve = ( ( $oof_enabled == '1' ) ? ( $this->rc_hlp->get_vacation_sieve( $oof_subject, $oof_message, $oof_header, $oof_filter, $email ) ) : ( "" ) ); */
			$out_of_office_sieve = ( ( $total_oof_rules > 0 ) ? ( $this->rc_hlp->get_vacation_sieve( $oof_details, $total_oof_rules, $email ) ) : ( "" ) );
			
			$block_email_sieve = ( ( $total_blocked_emails >= 1 ) ? ( $this->rc_hlp->get_block_sieve( $block_header_idx, $block_filter, $total_blocked_emails, $email ) ) : ( "" ) );
			$folder_rule_sieve = ( ( count( $folder_rule_folder_nm ) > 0 ) ? ( $this->rc_hlp->get_folder_rule_sieve( $folder_rule_folder_nm, $folder_rule_enabled, $folder_rule_filter, $folder_rule_filter_match, $email ) ) : ( "" ) );
			$fwd_rule_sieve = ( ( $fwd_rule_cnt > 0 ) ? ( $this->rc_hlp->get_fwd_rule_sieve( $fwd_rule_cnt, $fwd_rule_header, $fwd_rule_filter, $fwd_rule_email, $email ) ) : ( "" ) );
			
			if( $total_blocked_emails >= 1 )
			{
				$sieve_condts = $block_email_sieve;
			}
			else
			{
				$sieve_condts = "";
			}
			
			if( $custom_rule_enabled == '1' )
			{
				$sieve_condts = ( ( $total_blocked_emails >= 1 ) ? ( str_replace( "#_CONTINUE_#", $custom_rule_sieve, $sieve_condts ) ) : ( $custom_rule_sieve ) );
			}
			else
			{
				$sieve_condts = ( ( $total_blocked_emails >= 1 ) ? ( $sieve_condts ) : ( "#_CONTINUE_#" ) );
			}
			
			if( $total_oof_rules > 0 )
			{
				$sieve_condts = str_replace( "#_CONTINUE_#", $out_of_office_sieve, $sieve_condts );
			}
			else
			{
				$sieve_condts = $sieve_condts;
			}
			
			if( $fwd_rule_cnt >= 1 )
			{
				$sieve_condts = str_replace( "#_CONTINUE_#", $fwd_rule_sieve, $sieve_condts );
			}
			else
			{
				$sieve_condts = $sieve_condts;
			}
			
			if( $total_folder_rules >= 1 ) // ie. if folder rule is set
			{
				$sieve_condts = str_replace( "#_CONTINUE_#", $folder_rule_sieve, $sieve_condts );
			}
			else
			{
				$sieve_condts = str_replace( "#_CONTINUE_#", "keep;", $sieve_condts );
			}
			
			$sieve_condts = str_replace( "'", '"', $sieve_condts );
			
			if( $this->write_file( $this->rc_hlp->set_init_sieve_cont().$sieve_condts, $file_path, $temp_file_path, $file_name ) )
				return true;
			else
				return false;
		}
		
		function write_file( $content, $path, $temp_file_path, $file_name )
		{
			$file_full_path = $path.$file_name;
			$tmp_file = tempnam( $temp_file_path, 'xyz' ); // if temp directory doesnot have permission, it create default temp directory. 
			$file_obj = fopen( $tmp_file, 'w' );
			
			if( $file_obj )
			{
				fwrite($file_obj, $content);
				fclose($file_obj);
				$command = "cat $tmp_file | sudo -u vmail tee ".$file_full_path." >/dev/null";
				
				exec( $command , $out , $ret );
				
				if( $ret == 0 )
				{
					unlink( $tmp_file );
					return true;
				}
				else
				{
					return false;
				}
			}
		}
		
		function publishDomain( $domain = null, $delete_account_domain = false )
		{
			$domain_record_details = $this->rc_hlp->get_domain_records( $domain );
			$count = count( $domain_record_details[ 'rec_type' ] );
				
			if( ( $delete_account_domain == true ) && ( $this->delete_BytemarkDNS_file( $domain ) == true ) ) //if delete and deleting file succeed
			{
				return true;
			}
			else if( ( $delete_account_domain == true ) && ( $this->delete_BytemarkDNS_file( $domain ) == false ) ) // if delete and deleting file fails
			{
				return false;
			}
			else if( ( $delete_account_domain == false ) && ( $this->create_BytemarkDNS_contents( $domain_record_details, $count, $domain ) == true ) ) // if not delete option, and creating file succeed
			{
				return true;
			}
			else // if not delete option, and creating file fails
			{
				return false;
			}
		}
		
		function create_BytemarkDNS_contents( $domain_record_details, $count, $domain = null )
		{
			$file_path = "/root/BytemarkDNS/data/";
			$temp_file_path = "/var/www/temp/";
			$file_name = $domain.".txt";
			
			$BytemarkDNS_main_content = $this->rc_hlp->get_BytemarkDNS_main_content( $domain );
			$BytemarkDNS_domain_record_content = "";
			$BytemarkDNS_contents = "";
			
			for( $i = 0; $i < $count; $i++ )
			{
				$rec_name = $domain_record_details[ 'rec_name' ][ $i ];
				$rec_type = $domain_record_details[ 'rec_type' ][ $i ];
				$rec_value = $domain_record_details[ 'rec_value' ][ $i ];
				
				$BytemarkDNS_domain_record_content .= $this->rc_hlp->get_BytemarkDNS_domain_record_content( $rec_name, $rec_type, $rec_value, $domain );
			}
			
			$BytemarkDNS_contents = $BytemarkDNS_main_content."\n".$BytemarkDNS_domain_record_content;
			
			if( $this->write_file( $BytemarkDNS_contents, $file_path, $temp_file_path, $file_name ) == true )
			{
				return true;
			}
			else
			{
				return false;
			}
			
			/* $bytemark_file_path = "/root/BytemarkDNS/data/";
			
			$file = fopen( $bytemark_file_path.$file_name, 'w' );
			
			if( $file )
			{
				fwrite( $file, $BytemarkDNS_contents );
				fclose( $file );
				return true;
			}
			else
			{
				return false;
			} */
		}
		
		function delete_BytemarkDNS_file( $domain )
		{
			$BytemarkDNS_file_loc = "/root/BytemarkDNS/data/";
			$file_name = $domain.".txt";
			
			unlink( $BytemarkDNS_file_loc.$file_name );
			return true;
		}
	}
	
?>