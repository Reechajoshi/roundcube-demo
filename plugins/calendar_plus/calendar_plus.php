<?php
# 
# This file is part of Roundcube "calendar_plus" plugin.
# 
# Your are not allowed to distribute this file or parts of it.
# 
# This file is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
# 
# Copyright (c) 2012 - 2013 Roland 'Rosali' Liebl - all rights reserved.
# dev-team [at] myroundcube [dot] com
# http://myroundcube.com
# 
class calendar_plus extends rcube_plugin
	{
		static private$plugin='calendar_plus';
		static private$author='myroundcube@mail4us.net';
		static private$authors_comments='<font color="red"><u>GOOGLE CALENDAR</u>: Google has announced to close CalDAV service for <a href="https://developers.google.com/google-apps/calendar/caldav" target="_new"><font color="red">public access</a></a> on 09/16/2013. Our plugin won\'t be able to sync Google events once public CalDAV access has been closed by Google.<br /><u>WARNING</u>: We have implemented new methods to autodetect, create and delete CardDAV collections. Testings have passed our quality reviews successfully. Nevertheless, make sure to have a backup of your DAV resources to prevent any data loss.</font>';
		static private$version='3.0.7';
		static private$date='07-09-2013';
		static private$licence='All Rights reserved';
		static private$requirements=array('Roundcube'=>'0.9','PHP'=>'5.2.1');
		static private$f;
		static private$calskin='classic';
		private$ical_parts=array();
		var$utils;
		function init()
		{
			self::$f=$this;
			$A=rcmail::get_instance();
			$YC=$A->config->get('skin','classic');
			if(!is_dir(INSTALL_PATH.'plugins/calendar/'.$YC))
			{
				self::$calskin='classic';
			}
			self::$calskin=$YC;
			require_once(INSTALL_PATH.'plugins/calendar/program/utils.php');
			$this->utils=new Utils($A);
			if($_GET['_action']=='plugin.calendar_get_shares')
			{
				$this->startup();
			}
			$this->add_hook('request_token',array($this,'switchUserToken'));
			$this->add_hook('template_object_ics_upload',array($this,'upload_field'));
			$this->add_hook('template_object_subscriptions_table',array($this,'subscriptions_table'));
			if($A->action=='show'||$A->action=='preview')
			{
				$this->add_hook('message_load',array($this,'message_load'));
				$this->add_hook('message_part_structure',array($this,'message_part_structure'));
				$this->add_hook('template_object_messagebody',array($this,'html_import'));
				$this->add_hook('render_page',array($this,'preview_events'));
			}
			$this->register_action('plugin.calendar_subscription_view',array($this,'subscription_view'));
		}
		static function about($sB=false)
		{
			$RB=self::$requirements;
			foreach(array('required_','recommended_')as$v)
			{
				if(is_array($RB[$v.'plugins']))
				{
					foreach($RB[$v.'plugins']as$OB=>$x)
					{
						if(class_exists($OB)&&method_exists($OB,'about'))
						{
							$jC=new$OB(false);
							$RB[$v.'plugins'][$OB]=array('method'=>$x,'plugin'=>$jC->about($sB),);
						}
						else
						{
							$RB[$v.'plugins'][$OB]=array('method'=>$x,'plugin'=>$OB,);
						}
					}
				}
			}
			$F=array('plugin'=>self::$plugin,'version'=>self::$version,'date'=>self::$date,'author'=>self::$author,'comments'=>self::$authors_comments,'licence'=>self::$licence,'requirements'=>$RB,);
			if(is_array($sB))
			{
				$HB=array('plugin'=>self::$plugin);
				foreach($sB as$C)
				{
					$HB[$C]=$F[$C];
				}
				return$HB;
			}
			else
			{
				return$F;
			}
		}
		function startup()
		{
			$A=rcmail::get_instance();
			if($A->action=='plugin.calendar_get_shares')
			{
				$cC=get_input_value('access',RCUBE_INPUT_GET);
				$uB='';
				if($cC==2)
				{
					$uB='readonly_';
				}
				$lB=get_input_value('rcuser',RCUBE_INPUT_GET);
				$YB='SELECT * from '.get_table_name('users').' WHERE username=? AND mail_host=? LIMIT 1';
				$iC=$A->db->query($YB,$lB,rcube_parse_host($A->config->get('default_host')));
				$w=$A->db->fetch_assoc($iC);
				$eC=$w;
				$w=unserialize($w['preferences']);
				$F=array();
				if(is_array($w))
				{
					foreach($w as$C=>$G)
					{
						if($cC==1)
						{
							$C=str_replace('_readonly_','_',$C);
						}
						if(substr($C,0,strlen('cal_shares_'.$uB))=='cal_shares_'.$uB)
						{
							if($G)
							{
								$F[$C]=$G;
							}
						}
					}
				}
				$x=get_input_value('method',RCUBE_INPUT_GET);
				if(count($F)>0&&$w['caldav_notify'])
				{
					if($x=='PUT'||$x=='DELETE'||$x=='MOVE')
					{
						$ID=get_input_value('rcuser',RCUBE_INPUT_GET);
						$EC=base64_decode(get_input_value('request',RCUBE_INPUT_GET));
						$LB=explode('/',$EC);
						$h=$LB[count($LB)-2];
						$LB=$LB[count($LB)-1];
						$BD='principals/'.$ID;
						$_C=$A->config->get('db_sabredav_dsn');
						$QB=new rcube_db($_C,'',FALSE);
						$QB->set_debug((bool)$A->config->get('sql_debug'));
						$QB->db_connect('r');
						$YB='SELECT id FROM calendars WHERE principaluri=? AND uri=?';
						$HB=$QB->query($YB,$BD,$h);
						$tB=$QB->fetch_assoc($HB);
						$tB=$tB['id'];
						$YB='SELECT calendardata FROM calendarobjects WHERE uri=? AND calendarid=?';
						$HB=$QB->query($YB,$LB,$tB);
						$mB=$QB->fetch_assoc($HB);
						$mB=$mB['calendardata'];
						if($mB)
						{
							include(INSTALL_PATH.'plugins/calendar/localization/en_US.inc');
							$CD=$T;
							if(file_exists(INSTALL_PATH.'plugins/calendar/localization/'.$eC['language'].'.inc'))
							{
								include(INSTALL_PATH.'plugins/calendar/localization/'.$eC['language'].'.inc');
								$T=array_merge($CD,$T);
							}
							$NB=$w['caldav_notify_to'];
							$DB=$T['calendar_modified']." (".ucwords($h).")";
							switch($x)
							{
								case'PUT':
									$BB=sprintf($T['notify_header'],$NB)."<br /><br />".sprintf($T['event_modified'],ucwords($h))."<br /><br /><a href='$EC' target='_new'>$EC</a><br /><br />".$T['regards']."<br />".$A->config->get('notify_sign',$T['administrator'])."<br /><hr />".$T['notify_footer']."<hr />".$T['notify_details'];
									break;
								case'DELETE':
									$BB=sprintf($T['notify_header'],$NB)."<br /><br />".sprintf($T['event_deleted'],ucwords($h))."<br /><br />".$T['regards']."<br />".$A->config->get('notify_sign',$T['administrator'])."<br /><hr />".$T['notify_footer']."<hr />".$T['notify_details'];
									break;
								default:
									$BB=false;
							}
							if($BB)
							{
								$this->notify($A->config->get('cron_sender','noreply'),$NB,$DB,$BB,$mB);
							}
						}
					}
				}
				echo serialize($F);
				if(!$_SESSION['user_id'])
				{
					$A->session->destroy(session_id());
				}
				exit;
			}
		}
		private function notify($kB,$NB,$DB,$BB,$GB)
		{
			$A=rcmail::get_instance();
			if(function_exists('mb_encode_mimeheader'))
			{
				mb_internal_encoding(RCMAIL_CHARSET);
				$DB=mb_encode_mimeheader($DB,RCMAIL_CHARSET,'Q',$A->config->header_delimiter(),8);
			}
			else
			{
				$DB='=?UTF-8?B?'.base64_encode($DB).'?=';
			}
			$jB=md5(rand().microtime());
			$n="Return-Path: $kB\r\n";
			$n.="MIME-Version: 1.0\r\n";
			$n.="X-RC-Attachment: ICS\r\n";
			$n.="Content-Type: multipart/mixed; boundary=\"=_$jB\"\r\n";
			$n.="Date: ".date('r',time())."\r\n";
			$n.="From: $kB\r\n";
			$n.="To: $NB\r\n";
			$n.="Subject: $DB\r\n";
			$n.="Reply-To: $kB\r\n";
			$b="--=_$jB";
			$b.="\r\n";
			$oB=md5(rand().microtime());
			$b.="Content-Type: multipart/alternative; boundary=\"=_$oB\"\r\n\r\n";
			$CB="--=_$oB";
			$CB.="\r\n";
			$CB.="Content-Transfer-Encoding: 7bit\r\n";
			$CB.="Content-Type: text/plain; charset=".RCMAIL_CHARSET."\r\n";
			$UC=$A->config->get('line_length',72);
			$ED=new html2text($BB,false,true,0);
			$vB=rc_wordwrap($ED->get_text(),$UC,"\r\n");
			$vB=wordwrap($vB,998,"\r\n",true);
			$CB.="$vB\r\n";
			$CB.="--=_$oB";
			$CB.="\r\n";
			$b.=$CB;
			$b.="Content-Transfer-Encoding: quoted-printable\r\n";
			$b.="Content-Type: text/html; charset=".RCMAIL_CHARSET."\r\n\r\n";
			$b.=str_replace("=","=3D",$BB);
			$b.="\r\n\r\n";
			$b.="--=_$oB--";
			$b.="\r\n\r\n";
			$JB="--=_$jB";
			$JB.="\r\n";
			$JB.="Content-Type: text/calendar; name=calendar.ics; charset=".RCMAIL_CHARSET."\r\n";
			$JB.="Content-Transfer-Encoding: base64\r\n\r\n";
			$JB.=chunk_split(base64_encode($GB),$UC,"\r\n");
			$JB.="--=_$jB--";
			$b.=$JB;
			if(!is_object($A->smtp))
				$A->smtp_init(true);
			$A->smtp->send_mail($kB,$NB,$n,$b);
		}
		static function load_settings($R=false,$K)
		{
			global $RC_HELP, $RC_SABRE_HELP;
			$A=rcmail::get_instance();
			if($R=='feeds')
			{
				$A->output->add_label('calendar.remove_feed');
				$ZC=(array)$A->config->get('public_categories',array());
				$Q=array_merge((array)$A->config->get('categories',array()),$ZC);
				/* CHANGE DEFAULT CALENDAR TEXT */
				$default_caldav_label = str_replace( "%u", $_SESSION[ 'username' ], $A->config->get('default_category_label',self::$f->gettext('calendar.defaultcategory')) );
				/* $Q=array_merge(array($A->config->get('default_category_label', self::$f->gettext('calendar.defaultcategory'))=>$A->config->get('default_category')),$Q); */
				$Q=array_merge(array($default_caldav_label=>$A->config->get('default_category')),$Q);
				$pC="var categories = ".json_encode($Q).';';
				$A->output->add_script($pC);
				self::$f->include_script('../calendar/program/js/settings.js');
				$K['blocks']['calendarfeeds']['name']=self::$f->gettext('calendar.feeds');
				$E='rcmfd_calendarfeeds';
				$TC=(array)$A->config->get('calendarfeeds',array());
				$N='';
				if(count($TC)<=$A->config->get('max_feeds',3))
					$N='<input type="button" value="+" title="'.self::$f->gettext('calendar.add_feed').'" onClick="addRowCalFeeds(60)">';
				$K['blocks']['calendarfeeds']['options']['calendarfeeds']=array('title'=>html::label($E,self::$f->gettext('calendar.calendar_feeds')),'content'=>$N,);
				foreach($TC as$C=>$G)
				{
					$E='rcmfd_calendarfeed_'.$C.'_'.$G;
					$oC=new html_inputfield(array('name'=>'_calendarfeeds[]','onclick'=>'this.select()','id'=>$E,'size'=>60,'title'=>$C));
					$VC=new html_select(array('name'=>'_feedscategories[]','id'=>$E));
					$hB=array_merge((array)$A->config->get('categories',array()),$ZC);
					/* CHANGE DEFAULT CALENDAR TEXT */
					$default_caldav_label = str_replace( "%u", $_SESSION[ 'username' ], $A->config->get('default_category_label',self::$f->gettext('calendar.defaultcategory')) );
					
					/* $hB=array_merge(array($A->config->get('default_category_label', self::$f->gettext('calendar.defaultcategory'))=>$A->config->get('default_category')),$hB); */
					$hB=array_merge(array($default_caldav_label=>$A->config->get('default_category')),$hB);
					$VC->add(array_flip($hB),$hB);
					$K['blocks']['calendarfeeds']['options']['calendarfeed_'.$C]=array('title'=>html::label($E,''),'content'=>'<input type="button" value="X" onclick="removeRow(this.parentNode.parentNode)" title="'.self::$f->gettext('calendar.remove_feed').'" />&nbsp;'.$oC->show($C)."&nbsp;".$VC->show($G),);
				}
			}
			else if($R=='sharing')
			{
				/* // HIDING ACTUAL CALENDAR MENUS
				self::$f->include_script('program/js/flashclipboard.js');
				$A->output->add_footer(html::tag('div',array('id'=>'zclipdialog','title'=>self::$f->gettext('calendar.copiedtoclipboard'))));
				$J=self::$f->local_skin_path();
				if($J=='skins/larry')
					$J='skins/classic';
				$iB='';
				$nB='./'.$J.'/images/icons/delete.png';
				if($A->config->get('skin')=='larry')
				{
					$iB='background: url(./skins/larry/images/buttons.png) -7px -377px no-repeat;';
					$nB='plugins/calendar/skins/larry/images/blank.gif';
				}
				if(class_exists('sabredav')&&$A->config->get('backend')=='caldav')
				{
					$FC=sabredav::about(array('version'));
					$FC=$FC['version'];
					if($FC>='4')
					{
						$E='rcmfd_cal_token';
						$S=$A->config->get('caltoken');
						$U='document.getElementById("'.$E.'").value="";';
						$o=html::tag('input',array('type'=>'image','style'=>$iB,'name'=>'_caltoken_submit','value'=>'0','src'=>$nB,'onclick'=>$U,'align'=>'absmiddle','alt'=>self::$f->gettext('delete'),'title'=>self::$f->gettext('delete')));
						$f='readonly';
						$m=self::$f->gettext('calendar.isenabled');
						if(!$S)
						{
							$U='document.getElementById("'.$E.'").disabled="";document.forms.form.submit();';
							$o="&nbsp;".html::tag('input',array('name'=>'_caltoken_submit','value'=>'1','type'=>'checkbox','onclick'=>$U));
							$m=self::$f->gettext('calendar.isdisabled');
						}
						if(isset($_GET['_framed']))
						{
							// USERNAME
							$K['blocks']['calendar_shares']['name']=self::$f->gettext('calendar.confidentialcaldavaccess');
							$O=new html_inputfield(array('readonly'=>'readonly','value'=>$_SESSION['username'],'size'=>strlen($_SESSION['username'])));
							$K['blocks']['calendar_shares']['options']['username']=array('title'=>html::label($E,Q(self::$f->gettext('username'))),'content'=>$O->show().'&nbsp; '.html::tag('img',array('class'=>'zclip','src'=>'plugins/calendar_plus/'.$J.'/images/clipboard.png','title'=>self::$f->gettext('calendar.copytoclipboard'),'alt'=>self::$f->gettext('calendar.copytoclipboard'))));
							$K['blocks']['calendar_shares']['name']=sprintf(self::$f->gettext('calendar.confidentialcaldavaccess'),self::$f->gettext('calendar.readwrite').'&nbsp;'.$m);
							$O=new html_inputfield(array('name'=>'_caltoken',$f=>$f,'id'=>$E,'value'=>$S,'size'=>8,'maxlength'=>8));
							// TOKEN
							$K['blocks']['calendar_shares']['options']['token']=array('title'=>html::label($E,Q(self::$f->gettext('calendar.caltoken'))),'content'=>$O->show($S).'&nbsp; '.html::tag('img',array('class'=>'zclip','src'=>'plugins/calendar_plus/'.$J.'/images/clipboard.png','title'=>self::$f->gettext('calendar.copytoclipboard'),'alt'=>self::$f->gettext('calendar.copytoclipboard'))).'&nbsp;'.$o,);
							// SHARE RESOURCE LABELS
							$K['blocks']['calendar_shares']['options']['shares']=array('title'=>'&nbsp;'.html::tag('b',null,html::label($E,Q(self::$f->gettext('calendar.share')))),'content'=>html::tag('b',null,Q(self::$f->gettext('calendar.resource'))));
							
							$D=$A->config->get('caldav_url');
							$D=str_replace('%u',$_SESSION['username'],$D);
							if(strpos($D,'?')===false)
							{
								$D.='?issabredav=1';
							}
							else
							{
								$D.='&issabredav=1';
							}
							// DISPLAY DEFAULT CALENDAR
							$F=calendar_plus::isSabreDAV($D);
							if($F)
							{
								$D=str_replace('%u',$_SESSION['username'],$A->config->get('caldav_url'));
								$I=parse_url($D);
								$I['host']=$A->config->get('sabredav_readwrite_host',$I[0]);
								$D=$I['scheme'].'://'.$I['host'].$I['path'];
								$E='rcmfd_cal_shares_default';
								$Y=$A->config->get('cal_shares_events')?true:false;
								$c=new html_checkbox(array('name'=>'_cal_shares_events','id'=>$E,'value'=>1,'onclick'=>'document.forms.form.submit();'));
								$O=new html_inputfield(array('readonly'=>'readonly','value'=>$D,'size'=>80));
								$K['blocks']['calendar_shares']['options']['resources_default']=array(
								'title'=>$c->show($Y).'&nbsp;'.$A->config->get('default_category_label',self::$f->gettext('calendar.defaultcategory')),'content'=>$O->show().'&nbsp;'.html::tag('img',array('class'=>'zclip','src'=>'plugins/calendar_plus/'.$J.'/images/clipboard.png','title'=>self::$f->gettext('calendar.copytoclipboard'),'alt'=>self::$f->gettext('calendar.copytoclipboard'))));
							}
							// GET REMAINING CALENDARS
							$CC=$A->config->get('caldavs',array());
							foreach($CC as$B=>$h)
							{
								$D=str_replace('%u',$_SESSION['username'],$h['url']);
								if(strpos($D,'?')===false)
								{
									$D.='?issabredav=1';
								}
								else
								{
									$D.='&issabredav=1';
								}
								$F=calendar_plus::isSabreDAV($D);
								if($F)
								{
									$D=str_replace('%u',$_SESSION['username'],$h['url']);
									$I=parse_url($D);
									$I['host']=$A->config->get('sabredav_readwrite_host',$I[0]);
									$D=$I['scheme'].'://'.$I['host'].$I['path'];
									$E='rcmfd_cal_shares_'.$B;
									$Y=$A->config->get('cal_shares_'.strtolower($B))?true:false;
									$c=new html_checkbox(array('name'=>'_cal_shares_'.strtolower($B),'value'=>1,'onclick'=>'document.forms.form.submit();'));
									$O=new html_inputfield(array('readonly'=>'readonly','value'=>$D,'size'=>80));
									$K['blocks']['calendar_shares']['options']['resources_'.$B]=array('title'=>$c->show($Y).'&nbsp;'.$B,'content'=>$O->show().'&nbsp;'.html::tag('img',array('class'=>'zclip','src'=>'plugins/calendar_plus/'.$J.'/images/clipboard.png','title'=>self::$f->gettext('calendar.copytoclipboard'),'alt'=>self::$f->gettext('calendar.copytoclipboard'))));
								}
							}
							// TO DISPLAY HINT
							$K['blocks']['calendar_shares']['options']['shareshint']=array('title'=>'','content'=>html::tag('small',null,html::tag('b',null,self::$f->gettext('calendar.sharehint'))));
							
							$E='rcmfd_cal_token_davreadonly';
							$S=$A->config->get('caltoken_davreadonly','');
							$U='document.getElementById("'.$E.'").value="";';
							$o=html::tag('input',array('type'=>'image','style'=>$iB,'name'=>'_caltoken_davreadonly_submit','value'=>'0','src'=>$nB,'onclick'=>$U,'align'=>'absmiddle','alt'=>self::$f->gettext('delete'),'title'=>self::$f->gettext('delete')));
							$f='readonly';
							$m=self::$f->gettext('calendar.isenabled');
							if($S=="")
							{
								$U='document.getElementById("'.$E.'").disabled="";document.forms.form.submit();';
								$o="&nbsp;".html::tag('input',array('name'=>'_caltoken_davreadonly_submit','value'=>'1','type'=>'checkbox','onclick'=>$U));
								$m=self::$f->gettext('carddav.isdisabled');
							}
							$K['blocks']['calendar_shares_readonly']['name']=self::$f->gettext('calendar.confidentialcaldavaccess_readonly');
							// USERNAME
							$O=new html_inputfield(array('readonly'=>'readonly','value'=>$_SESSION['username'],'size'=>strlen($_SESSION['username'])));
							$K['blocks']['calendar_shares_readonly']['options']['username']=array('title'=>html::label($E,Q(self::$f->gettext('username'))),'content'=>$O->show().'&nbsp; '.html::tag('img',array('class'=>'zclip','src'=>'plugins/calendar_plus/'.$J.'/images/clipboard.png','title'=>self::$f->gettext('calendar.copytoclipboard'),'alt'=>self::$f->gettext('calendar.copytoclipboard'))));
							// Confidential CalDAV access text
							$K['blocks']['calendar_shares_readonly']['name']=sprintf(self::$f->gettext('calendar.confidentialcaldavaccess'),self::$f->gettext('calendar.readonly').'&nbsp;'.$m);
							$O=new html_inputfield(array('name'=>'_caltoken_davreadonly',$f=>$f,'id'=>$E,'value'=>$S,'size'=>8,'maxlength'=>8));
							// TOKEN
							$K['blocks']['calendar_shares_readonly']['options']['token']=array('title'=>html::label($E,Q(self::$f->gettext('calendar.caltoken'))),'content'=>$O->show($S).'&nbsp; '.html::tag('img',array('class'=>'zclip','src'=>'plugins/calendar_plus/'.$J.'/images/clipboard.png','title'=>self::$f->gettext('calendar.copytoclipboard'),'alt'=>self::$f->gettext('calendar.copytoclipboard'))).'&nbsp;'.$o,);
							$K['blocks']['calendar_shares_readonly']['options']['shares_readonly']=array('title'=>'&nbsp;'.html::tag('b',null,html::label($E,Q(self::$f->gettext('calendar.share')))),'content'=>html::tag('b',null,Q(self::$f->gettext('calendar.resource'))));
							// DISPLAY DEFAULT CALENDAR
							$D=$A->config->get('caldav_url');
							if(strpos($D,'?')===false)
							{
								$D.='?issabredav=1';
							}
							else
							{
								$D.='&issabredav=1';
							}
							$F=calendar_plus::isSabreDAV($D);
							if($F)
							{
								$D=str_replace('%u',$_SESSION['username'],$A->config->get('caldav_url'));
								$I=parse_url($D);
								$I['host']=$A->config->get('sabredav_readonly_host',$I[0]);
								$D=$I['scheme'].'://'.$I['host'].$I['path'];
								$E='rcmfd_cal_shares_davreadonly_default';
								$Y=$A->config->get('cal_shares_readonly_events')?true:false;
								$c=new html_checkbox(array('name'=>'_cal_shares_readonly_events','id'=>$E,'value'=>1,'onclick'=>'document.forms.form.submit();'));
								$O=new html_inputfield(array('readonly'=>'readonly','value'=>$D,'size'=>80));
								$K['blocks']['calendar_shares_readonly']['options']['resources_default']=array('title'=>$c->show($Y).'&nbsp;'.$A->config->get('default_category_label',self::$f->gettext('calendar.defaultcategory')),'content'=>$O->show().'&nbsp; '.html::tag('img',array('class'=>'zclip','src'=>'plugins/calendar_plus/'.$J.'/images/clipboard.png','title'=>self::$f->gettext('calendar.copytoclipboard'),'alt'=>self::$f->gettext('calendar.copytoclipboard'))));
							}
							// DISPLAY OTHER CALENDARS
							$CC=$A->config->get('caldavs',array());
							foreach($CC as$B=>$h)
							{
								$D=str_replace('%u',$_SESSION['username'],$h['url']);
								if(strpos($D,'?')===false)
								{
									$D.='?issabredav=1';
								}
								else
								{
									$D.='&issabredav=1';
								}
								$F=calendar_plus::isSabreDAV($D);
								if($F)
								{
									$D=str_replace('%u',$_SESSION['username'],$h['url']);
									$I=parse_url($D);
									$I['host']=$A->config->get('sabredav_readonly_host',$I[0]);
									$D=$I['scheme'].'://'.$I['host'].$I['path'];
									$E='rcmfd_cal_shares_readonly_'.$B;
									$Y=$A->config->get('cal_shares_readonly_'.strtolower($B))?true:false;
									$c=new html_checkbox(array('name'=>'_cal_shares_readonly_'.strtolower($B),'value'=>1,'onclick'=>'document.forms.form.submit();'));
									$O=new html_inputfield(array('readonly'=>'readonly','value'=>$D,'size'=>80));
									$K['blocks']['calendar_shares_readonly']['options']['resources_readonly_'.$B]=array('title'=>$c->show($Y).'&nbsp;'.$B,'content'=>$O->show().'&nbsp; '.html::tag('img',array('class'=>'zclip','src'=>'plugins/calendar_plus/'.$J.'/images/clipboard.png','title'=>self::$f->gettext('calendar.copytoclipboard'),'alt'=>self::$f->gettext('calendar.copytoclipboard'))));
								}
							}
							// DESCRIPTION OF READONLY CALDAV
							$K['blocks']['calendar_shares_readonly']['options']['shareshint']=array('title'=>'','content'=>html::tag('small',null,html::tag('b',null,self::$f->gettext('calendar.sharehint_readonly'))));
							$A->output->add_script('$("form").attr("action", "./?_framed=1");','docready');
						}
					}  
				}
				
				$E='rcmfd_cal_token_readonly';
				$tC=md5($tC);
				$S=$A->config->get('caltokenreadonly','');
				$U='document.getElementById("'.$E.'").value="";';
				$o=html::tag('input',array('type'=>'image','style'=>$iB,'name'=>'_caltokenfeeds_submit','value'=>'0','src'=>$nB,'onclick'=>$U,'align'=>'absmiddle','alt'=>self::$f->gettext('delete'),'title'=>self::$f->gettext('delete')));
				$f='readonly';
				$m=self::$f->gettext('calendar.isenabled');
				$RC=true;
				$f='readonly';
				$m=self::$f->gettext('calendar.isenabled');
				if($S=="")
				{
					$S=md5($_SESSION['username'].session_id().time());
					$RC=false;
					$f='disabled';
					$U='document.getElementById("'.$E.'").disabled="";document.forms.form.submit();';
					$o="&nbsp;".html::tag('input',array('name'=>'_caltokenfeeds_submit','value'=>'1','type'=>'checkbox','onclick'=>$U));
					$m=self::$f->gettext('calendar.isdisabled');
				}
				$K['blocks']['calendar_readonly']['name']=sprintf(self::$f->gettext('calendar.calconfidentialurl'),self::$f->gettext('calendar.readonly').'&nbsp;'.$m);
				$O=new html_inputfield(array('name'=>'_caltokenreadonly',$f=>$f,'id'=>$E,'value'=>$S,'size'=>32,'maxlength'=>32));
				// PUBLIC FEED TOKEN
				$K['blocks']['calendar_readonly']['options']['token']=array('title'=>html::label($E,Q(self::$f->gettext('calendar.caltoken'))),'content'=>$O->show($S).'&nbsp; '.html::tag('img',array('class'=>'zclip','src'=>'plugins/calendar_plus/'.$J.'/images/clipboard.png','title'=>self::$f->gettext('calendar.copytoclipboard'),'alt'=>self::$f->gettext('calendar.copytoclipboard'))).'&nbsp;'.$o,);
				if($RC)
				{
					$E='rcmfd_cal_icsurl_readonly';
					if($A->config->get('cal_short_urls',false))
					{
						$z=calendar::getUrl()."ics/".$A->user->data['user_id']."/".$A->config->get('caltokenreadonly');
					}
					else
					{
						$z=calendar::getURL()."?_task=dummy&_action=plugin.calendar_showlayer&_userid=".$A->user->data['user_id']."&_ct=".$A->config->get('caltokenreadonly').'&_ics=1';
					}
					$O=new html_inputfield(array('name'=>'_calicsurl','readonly'=>'readonly','onclick'=>'this.select()','id'=>$E,'value'=>$z,'size'=>80));
					$K['blocks']['calendar_readonly']['options']['icsurl']=array('title'=>html::label($E,Q(self::$f->gettext('calendar.calicsurl'))),'content'=>$O->show($z).'&nbsp; '.html::tag('img',array('class'=>'zclip','src'=>'plugins/calendar_plus/'.$J.'/images/clipboard.png','title'=>self::$f->gettext('calendar.copytoclipboard'),'alt'=>self::$f->gettext('calendar.copytoclipboard'))),);
					$E='rcmfd_cal_feedurl_readonly';
					if($A->config->get('cal_short_urls',false))
					{
						$z=calendar::getUrl()."rc/".$A->user->data['user_id']."/".$A->config->get('caltokenreadonly');
					}
					else
					{
						$z=calendar::getURL()."?_task=dummy&_action=plugin.calendar_showlayer&_userid=".$A->user->data['user_id']."&_ct=".$A->config->get('caltokenreadonly');
					}
					$O=new html_inputfield(array('name'=>'_calfeedurl','readonly'=>'readonly','onclick'=>'this.select()','id'=>$E,'value'=>$z,'size'=>80));
					$K['blocks']['calendar_readonly']['options']['feedurl']=array('title'=>html::label($E,Q(self::$f->gettext('calendar.calfeedurl'))),'content'=>$O->show($z).'&nbsp; '.html::tag('img',array('class'=>'zclip','src'=>'plugins/calendar_plus/'.$J.'/images/clipboard.png','title'=>self::$f->gettext('calendar.copytoclipboard'),'alt'=>self::$f->gettext('calendar.copytoclipboard'))),);
					$K['blocks']['calendar_readonly']['options']['accesshint']=array('title'=>'','content'=>html::tag('small',null,html::tag('b',null,self::$f->gettext('calendar.icsaccesshint'))));
				} */
				
				$caldavs = array(); // contains name and url of all the caldav in order
				$user_domain = $A->user->get_username('domain');
				
				// COLLECT CALENDAR DETAILS
				/* CHANGE DEFAULT CALENDAR TEXT */
				/* $default_category = $A->config->get('default_category_label',self::$f->gettext('calendar.defaultcategory')); */
				$default_category = str_replace( "%u", $_SESSION[ 'username' ], $A->config->get('default_category_label',self::$f->gettext('calendar.defaultcategory')) );
				$caldav_categories = $A->config->get( 'categories', array() );
				
				$default_category_url = str_replace('%u',$_SESSION['username'],$A->config->get('caldav_url'));
				$other_categories = $CC=$A->config->get('caldavs',array());
				
				// ASSIGN DETAILS TO caldavs ARRAY
				$caldavs[ 'name' ][ 0 ] = $default_category;
				$caldavs[ 'url' ][ 0 ] = $default_category_url;
				
				foreach( $other_categories as $key => $value )
				{
					if( ( isset( $value[ 'is_caldav_owner' ] ) ) && ( $value[ 'is_caldav_owner' ] == 0 ) )
					{
						$hidden_shared_categories = new html_hiddenfield( "" );
						continue;
					}
					$caldavs[ 'name' ][] = $key;
					$caldavs[ 'url' ][] = $value[ 'url' ];
				}
				
				// KEEP THE CALENDAR SELECTED WHEN SAVE IS HIT
				$selected_calendar = 'null'; // if no calendar is selected, then show the default calendar.. in the format: calendarname|url
				
				if( ( isset( $_POST[ '_section' ] ) ) && ( $_POST[ '_section' ] == 'calendarsharing' ) )
				{
					if( isset( $_POST[ '_share_calendar_name' ] ) )
						$selected_calendar = $_POST[ '_share_calendar_name' ];
				}
				
				$exlode_selected_calendar = explode( "|", $selected_calendar );
				$selected_calendar_name = $exlode_selected_calendar[ 0 ]; // NOT USED ANYWHERE
				$selected_calendar_url = $exlode_selected_calendar[ 1 ];
				
				$users_subscribed = $RC_SABRE_HELP->get_subscribed_users_from_caldavurl( $selected_calendar_url, $A->user->get_username() );
				
				
				$users_subscribed_string = implode( "|", $users_subscribed ); // to set to the hidden field
				
				$select_calendars = new html_select( array( 'name' => '_share_calendar_name', 'onclick' => 'UI.set_caldav_url( this, \''.$A->user->get_username().'\' )', 'id' => '_share_calendar_name', 'value' => 1 ) );
				
				// Enter NULL VALUE ie. --Select-- (because, on loading page, instead of showing subscribed users for default cal, show --Select-- so user can select cal and subscribed users can be displayed)
				$select_calendars->add( "--Select--", "null" );
				
				for( $i = 0; $i < count( $caldavs[ 'name' ] ); $i++ )
				{
					$select_calendars->add( $caldavs[ 'name' ][ $i ], $caldavs[ 'name' ][ $i ].'|'.$caldavs[ 'url' ][ $i ] );
				}
				
				$input_url = new html_hiddenfield(array('name' => '_caldav_url', 'id' => '_caldav_url', 'value' => $selected_calendar_url, 'size' => 75));
				
				$K['blocks']['calendar_shares']['name']='Select The Calendar You Want To Share';
				
				$K['blocks']['calendar_shares']['options']['caldavs_select'] = array(
					'content' => "Calendar Name: ".$select_calendars->show( $selected_calendar ).$input_url->show( '', array( 'readonly' => 'true', 'style' => 'background:#FOFOFO;' ) )
				);
				
				$all_users = $RC_HELP->get_user_details( $user_domain );
				$total_users = $RC_HELP->get_user_count( $user_domain );
				
				$tbl_users = new html_table( array( 'cols' => 3, 'id' => 'cal_share' ) );
				$tbl_users->add_header( 'name', 'Name' );
				$tbl_users->add_header( 'email', 'Email' );
				$tbl_users->add_header( 'checkbox', 'Subscribed' );
				
				if( $total_users > 0 )
				{
					for( $i = 0; $i < $total_users; $i++ )
					{
						if( $all_users[ 'user_email' ][ $i ] == $A->user->get_username() ) // user cannot share calendar with itself
							continue;
						
						$chk_user_name = new html_checkbox( array( 'name' => '_share_cal_username', 'id' => '_share_cal_username', 'onclick' => 'UI.add_user_for_calendar_share( this, \''.$all_users[ 'user_email' ][ $i ].'\' )', 'value' => 1 ) );
						// $tbl_users->add_row();
						$tbl_users->add( 'name', $all_users[ 'user_name' ][ $i ] );
						$tbl_users->add( 'email', $all_users[ 'user_email' ][ $i ] );
						if( in_array( $all_users[ 'user_email' ][ $i ], $users_subscribed ) )
							$tbl_users->add( 'checkbox', $chk_user_name->show( 1, array( 'checked' => 'true' ) ) );
						else
							$tbl_users->add( 'checkbox', $chk_user_name->show() );
						
					}
				}
				else
				{
					$tbl_users->add_row();
					$tbl_users->add( array( 'colspan' => '4' ), 'There are No Users Added To Display' );
				}
				
				$K['blocks']['calendar_users']['name']='Select The Users With Whom You Want to Share Calendar';
				
				$K['blocks']['calendar_users']['options']['users_table'] = array(
					'content' => $tbl_users->show()
				);
				
				$hidden_user_name = new html_hiddenfield( array( 'name' => '_cal_share_hidden_username', 'value' => $users_subscribed_string, 'id' => '_cal_share_hidden_username' ) );
				
				$K['blocks']['calendar_users']['options']['hidden_username'] = array(
					'content' => $hidden_user_name->show()
				);
				
			}
			else if($R=='birthdays')
			{
				$E='rcmfd_show_birthdays';
				$Y=$A->config->get('show_birthdays');
				$c=new html_checkbox(array('name'=>'_show_birthdays','id'=>$E,'value'=>1));
				$K['blocks']['calendar']['options']['show_birthdays']=array('title'=>html::label($E,Q(self::$f->gettext('calendar.showbirthdays'))),'content'=>$c->show($Y?1:0),);
			}
			else if($R=='upcoming')
			{
				$E='rcmfd_upcoming_cal';
				$Y=$A->config->get('upcoming_cal');
				$c=new html_checkbox(array('name'=>'_upcoming_cal','id'=>$E,'value'=>1));
				$K['blocks']['calendar']['options']['upcoming_cal']=array('title'=>html::label($E,Q(self::$f->gettext('calendar.show_upcoming_cal'))),'content'=>$c->show($Y?1:0),);
			}
			else if($R=='tasks')
			{
				$E='rcmfd_caldav_replication_range_tasks';
				$Z=new html_select(array('name'=>'_caldav_replication_range_tasks','id'=>$E));
				$Z->add(self::$f->gettext('calendar.twoweeks'),14);
				$Z->add(self::$f->gettext('calendar.threeweeks'),21);
				$Z->add(self::$f->gettext('calendar.onemonth'),31);
				$Z->add(self::$f->gettext('calendar.twomonths'),62);
				$Z->add(self::$f->gettext('calendar.threemonths'),92);
				$Z->add(self::$f->gettext('calendar.sixmonths'),183);
				$Z->add(self::$f->gettext('calendar.oneyear'),366);
				$vC=$A->config->get('caldav_replication_range_tasks',92);
				$K['blocks']['calendar']['options']['caldav_caldav_replication_range_tasks']=array('title'=>html::label($E,Q(self::$f->gettext('calendar.caldavreplicationrangetasks'))),'content'=>$Z->show($vC),);
			}
			return$K;
		}
		static function load_backend($p='caldav')
		{
			$A=rcmail::get_instance();
			if($A->action=='plugin.calendar_showlayer'||get_input_value('_cron',RCUBE_INPUT_GPC))
			{
				if($A->action=='plugin.calendar_showlayer'||get_input_value('_userid',RCUBE_INPUT_GPC))
				{
					$A->set_task('dummy');
					$BC=get_input_value('_userid',RCUBE_INPUT_GPC);
				}
				else
				{
					$BC=$_SESSION['user_id'];
				}
				if($BC)
				{
					$pB=calendar::getUser($BC);
					$g=unserialize($pB['preferences']);
					if(!isset($g['backend']))
					{
						$g['backend']=$A->config->get('backend','caldav');
					}
					$p=$g['backend'];
					if($p=='caldav')
					{
						$A->config->set('caldav_user',$g['caldav_user']);
						$A->config->set('caldav_url',$g['caldav_url']);
						$mC=$A->decrypt($g['caldav_password']);
						if($mC=='%p')
						{
							if(!empty($pB['password']))
							{
								$g['caldav_password']=$pB['password'];
							}
							else
							{
								write_log('calendar','User "'.$pB['username'].'" has saved placeholder %p as password. Shared calendaring fails. Install "savepassword" plugin.');
							}
						}
						$A->config->set('caldav_password',$g['caldav_password']);
						$A->config->set('caldavs',$g['caldavs']);
						$A->config->set('categories',$g['categories']);
					}
				}
				else
				{
					$p='database';
				}
			}
			return$p;
		}
		static function load_birthdays($_=array())
		{
			$A=rcmail::get_instance();
			$DD=$A->config->get('autocomplete_addressbooks',array('sql'));
			$gB=get_input_value('_end',RCUBE_INPUT_GPC);
			foreach($DD as$zC)
			{
				$OC=$A->get_address_book($zC);
				$OC->set_pagesize(9999);
				$HD=$OC->list_records();
				while($P=$HD->next())
				{
					if(!empty($P['name'])&&!empty($P['birthday']))
					{
						list($aB,$EB,$SB)=explode('-',(string)$P['birthday'][0]);
						if(!$gB)
						{
							if($EB<date('m'))
							{
								$r=gmmktime(0,0,0,$EB,$SB,date('Y')+1);
							}
							else
							{
								$r=gmmktime(0,0,0,$EB,$SB);
							}
							$_['all'][]=array('timestamp'=>$r,'year'=>$aB,'text'=>$P['name'],'emails'=>array($P['email:home'],$P['email:other'],$P['email:work']));
						}
						else
						{
							$r=gmmktime(0,0,0,$EB,$SB,date('Y',$gB));
							$_['all'][]=array('timestamp'=>$r,'year'=>$aB,'text'=>$P['name'],'emails'=>array($P['email:home'],$P['email:other'],$P['email:work']));
							$r=gmmktime(0,0,0,$EB,$SB,date('Y',$gB)-1);
							$_['all'][]=array('timestamp'=>$r,'year'=>$aB,'text'=>$P['name'],'emails'=>array($P['email:home'],$P['email:other'],$P['email:work']));
							$r=gmmktime(0,0,0,$EB,$SB,date('Y',$gB)+1);
							$_['all'][]=array('timestamp'=>$r,'year'=>$aB,'text'=>$P['name'],'emails'=>array($P['email:home'],$P['email:other'],$P['email:work']));
						}
					}
				}
			}
			$_['birthdays']=$_['all'];
			return$_;
		}
		static function load_search($F,$eB)
		{
			$A=rcmail::get_instance();
			$a='';
			$PB=array();
			$M=array();
			if(!is_array($F))
			{
				$a.="<tr><td>".self::$f->gettext('calendar.calsearchnomatches')."</td></tr>";
				$PB=false;
			}
			else
			{
				$M=(array)$_SESSION['event_filters'];
				$M=array_flip($M);
				if(count($M)>0)
				{
					$V=array();
					foreach($F as$C=>$L)
					{
						if(isset($M[$L['categories']])||(isset($M['default'])&&$L['categories']==""))
						{
							$V[]=$L;
						}
					}
				}
				else
				{
					$V=$F;
				}
				foreach($V as$C=>$L)
				{
					$uC=$A->config->get('cal_searchset',array('summary'));
					foreach($uC as$C=>$VB)
					{
						$X=$L[$VB];
						preg_match('/'.str_replace(array('%','*','#'),'(.*)',$eB).'/i',$X,$GC);
						/* CHANGE DEFAULT CALENDAR TEXT */
						$default_caldav_label = str_replace( "%u", $_SESSION[ 'username' ], $A->config->get('default_category_label',self::$f->gettext('defaultcategory')) );
						
						/* if($VB=='categories'&&stripos($A->config->get('default_category_label',self::$f->gettext('defaultcategory')),$eB)!==false) */
						if($VB=='categories'&&stripos($default_caldav_label,$eB)!==false)
						{
							if($X=='')
							{
								$X=$eB;
								$GC[0]=$eB;
							}
						}
						if(count($GC)>0)
						{
							$wC='/'.implode('|',$GC).'/i';
							$X=preg_replace($wC,'<a href="#"><font id="rcmmatch'.$L['event_id'].'" class="calsearchmatch">$0</font></a>',$X);
							$fB=self::$f->gettext('calendar.'.$VB);
							if(strlen($fB)>7)
							{
								$fB=substr($fB,0,3)." ...";
							}
							$XC=0;
							if($_SESSION['tzname'])
							{
								$sC=date_default_timezone_get();
								date_default_timezone_set($_SESSION['tzname']);
								$XC=date('Z',$L['start'])-date('Z');
								date_default_timezone_set($sC);
							}
							if($L['component']=='vevent')
							{
								$U="onclick='calendar_commands.gotoDate(\"".($L['start']+$XC)."\", \"".$L['event_id']."\")";
							}
							else
							{
								if(!$L['clone'])
								{
									$L['clone']='false';
								}
								$U="onclick='calendar_gui.getTask(".$L['event_id'].", ".$L['start'].", ".$L['clone'].")";
							}
							$a.="<tr id='rcmrow".$L['event_id']."' ".$U."'><td><a href='#'>".$fB.'</a>: '.$X."<span id='calsearch_eid_".$L['event_id']."' style='display:none'>".$L['event_id']."</span></td></tr>";
							$PB[]=$L;
							break;
						}
					}
				}
			}
			return array('events'=>$PB,'filters'=>$M,'rows'=>$a);
		}
		static function load_boxtitle()
		{
			$HC=$_SESSION['tzname'];
			$rC=calendar::getClientTimezone();
			$nC=str_replace('_',' ',calendar::getClientTimezoneName($rC));
			$M=$_SESSION['calfilter'];
			if(is_array($M))
			{
				$M=implode(', ',array_flip($M));
			}
			if($HC)
				$HC='&nbsp;@&nbsp;'.$nC;
			$N='<div id="calusernamecontent">'.rcmail::get_instance()->user->data['username'].'</span>'.$HC."\n";
			$N.='<span>&nbsp;</span>'."\n";
			$N.='<small>('.self::$f->gettext('calendar.filter').':'."\n";
			$N.='<span id="calfiltercontent"><a href="#" onclick="calendar_commands.filterEvents()">'.$M.'</a></span>)</small>'."\n";
			return$N;
		}
		static function load_import($R=false)
		{
			if($R=='mainnav')
			{
				if(self::$calskin=='larry')
				{
					self::$f->add_button(array('command'=>'plugin.importEvents','id'=>'calimportbut','class'=>'button calimportbut','href'=>'#','title'=>'calendar.import','label'=>'calendar.import_short','type'=>'link'),'toolbar');
				}
				else
				{
					$d=getimagesize(INSTALL_PATH.'plugins/calendar_plus/skins/'.self::$calskin.'/images/import.png');
					self::$f->add_button(array('command'=>'plugin.importEvents','id'=>'calimportbut','width'=>$d[0],'height'=>$d[1],'href'=>'#','title'=>'calendar.import','imageact'=>'skins/'.self::$calskin.'/images/import.png'),'toolbar');
				}
			}
		}
		static function load_users($R=false)
		{
			if($R=='mainnav')
			{
				if(self::$calskin=='larry')
				{
					self::$f->add_button(array('command'=>'plugin.calendar_switchCalendar','id'=>'calswitchbut','class'=>'button calswitchbut','href'=>'#','title'=>'calendar.switch_calendar','label'=>'calendar.user','type'=>'link'),'toolbar');
				}
				else
				{
					$d=getimagesize(INSTALL_PATH.'plugins/calendar_plus/skins/'.self::$calskin.'/images/users.png');
					self::$f->add_button(array('command'=>'plugin.calendar_switchCalendar','id'=>'calswitchbut','width'=>$d[0],'height'=>$d[1],'href'=>'#','title'=>'calendar.switch_calendar','imageact'=>'skins/'.self::$calskin.'/images/users.png'),'toolbar');
				}
			}
			else if($R=='html')
			{
				$A=rcmail::get_instance();
				$W=$A->config->get('caldavs',array());
				$FB=$A->config->get('caldavs_subscribed',array());
				ksort($W);
				$M=array_flip($A->config->get('event_filters_allcalendars',array()));
				$Q=array_merge($A->config->get('public_categories',array()),$A->config->get('categories',array()));
				$q=html::tag('option',array('value'=>0),self::$f->gettext('calendar.allcalendars'));
				foreach($W as$B=>$u)
				{
					$aC='block';
					if(isset($W[$B])&&!isset($FB[$B]))
					{
						$aC='none';
					}
					$q.=html::tag('option',array('selected'=>(isset($M[$B])?true:false),'id'=>'user_'.asciiwords($B,true,''),'value'=>$B,'style'=>'display:'.$aC.';background-color:#'.$Q[$B]),$B.' ('.str_replace('%u',$_SESSION['username'],$u['user']).')');
				}
				return html::tag('select',array('name'=>'_caluser','id'=>'_caluser'),$q);
			}
		}
		static function load_filters($R=false,$PB=array())
		{
			// PB are all events for the user
			if($R=='filter')
			{
				$M=(array)$_SESSION['event_filters'];
				$M=array_flip($M);
				if(count($M)>0)
				{	
					$V=array();
					foreach($PB as$C=>$L)
					{
						// CALENDAR FILTER CHANGES
						/* if(isset($M[$L['classNameDisp']])||!isset($_SESSION['available_categories'][$L['classNameDisp']])||(isset($M['default'])&&$L['className']==""))
						{
							$V[]=$L;
						} */
						// !isset($_SESSION['available_categories'][$L['categories']])
						if( isset( $M[ $L[ 'categories' ] ] ) ) // M is selected category.. 
						{
							$V[]=$L;
						}
					}
				}
				else // if no category is selected
				{
					// CALENDAR FILTER CHANGES
					// $V=$PB;
					$V=array();
				}
				foreach($V as$C=>$L)
				{
					if($L['start_unix']<$qC&&$L['end_unix']<$qC)
						unset($V[$C]);
				}
				return array_values($V);
			}
			else if($R=='set')
			{
				$M=get_input_value('_filters',RCUBE_INPUT_POST);
				if(is_array($M))
				{
					$_SESSION['event_filters']=get_input_value('_filters',RCUBE_INPUT_POST);
					$s='';
					foreach($_SESSION['event_filters']as$C=>$G)
					{
						/* CHANGE DEFAULT CALENDAR TEXT */
						$default_caldav_label = str_replace( "%u", $_SESSION[ 'username' ], rcmail::get_instance()->config->get('default_category_label',self::$f->gettext('calendar.defaultcategory')) );
						if($G=="default")
							$G=$default_caldav_label;
							/* $G=rcmail::get_instance()->config->get( 'default_category_label',self::$f->gettext('calendar.defaultcategory')); */
						$s.="$G, ";
					}
					$s=substr($s,0,strlen($s)-2);
					if(strlen($s)>60)
					{
						$s=substr($s,0,60).' ...';
					}
					$_SESSION['calfilter']=$s;
				}
				else
				{
					$_SESSION['calfilter']=self::$f->gettext('calendar.allevents');
					$_SESSION['event_filters']=array();
				}
			}
		}
		static function load_print($R=false,$xC=null)
		{
			$J=self::$calskin;
			if($R=='js')
			{
				self::$f->include_script("program/js/$xC.js");
			}
			else if($R=='mainnav')
			{
				self::$f->add_button(array('command'=>'plugin.calendar_print','id'=>'calprintprevbut','class'=>'button calprintprevbut','href'=>'#','title'=>'print','label'=>'print','type'=>'link'),'toolbar');
			}
			else if($R=='popupnav')
			{
				$d=getimagesize(INSTALL_PATH.'plugins/calendar_plus/skins/'.$J.'/images/toggle_view.png');
				self::$f->add_button(array('command'=>'plugin.calendar_toggle_view','id'=>'caltoggleviewbut','width'=>$d[0],'height'=>$d[1],'href'=>'#','title'=>'calendar.toggle_view','imagepas'=>'skins/'.$J.'/images/spacer.gif','imageact'=>'skins/'.$J.'/images/toggle_view.png'),'toolbar');
				$d=getimagesize(INSTALL_PATH.'plugins/calendar_plus/skins/'.$J.'/images/print.png');
				self::$f->add_button(array('command'=>'plugin.calendar_do_print','id'=>'calprintbut','width'=>$d[0],'height'=>$d[1],'href'=>'#','title'=>'print','imagepas'=>'skins/'.$J.'/images/spacer.gif','imageact'=>'skins/'.$J.'/images/print.png'),'toolbar');
			}
		}
		static function load_upcoming_cal()
		{
			$A=rcmail::get_instance();
			self::$f->require_plugin('qtip');
			$J=self::$calskin;
			self::$f->include_stylesheet('skins/'.$J.'/upcoming.css');
			self::$f->include_script('program/js/upcoming.js');
			self::$f->add_hook('render_page',array(self::$f,'upcoming_calendar'));
			if($A->action=='preview'||$A->action=='show')
			{
				self::$f->include_stylesheet('skins/'.$J.'/preview.css');
			}
		}
		static function load_ics_attachments()
		{
			$A=rcmail::get_instance();
			$J=self::$calskin;
			$yC='plugins/calendar_plus/skins/'.$J.'/images/ics.png';
			$A->output->set_env('calskin',$J);
			$A->output->set_env('ics_icon',$yC);
			self::$f->include_script("program/js/calendar.ics_icon.js");
			self::$f->add_hook('messages_list',array(self::$f,'pass_ics_header'));
			self::$f->add_hook('storage_init',array(self::$f,'fetch_ics_header'));
		}
		function switchUserToken($S)
		{
			$A=rcmail::get_instance();
			if($_SESSION['user_id']!=$A->user->ID)
			{
				if($yB=rc_request_header('X-Roundcube-Request'))
				{
					$S['value']=$yB;
				}
				else if($yB=get_input_value('_token',RCUBE_INPUT_GPC))
				{
					$S['value']=$yB;
				}
			}
			return$S;
		}
		function upcoming_calendar($H)
		{
			if($H['template']=='mail'||$H['template']=='message')
			{
				$A=rcmail::get_instance();
				$A->output->add_label('calendar.unloadwarning','calendar.successfullyreplicated','calendar.replicationtimeout','calendar.resumereplication','calendar.replicationfailed','calendar.replicationincomplete');
				$A->output->set_env('rc_date_format',$A->config->get('date_format','m/d/Y'));
				$A->output->set_env('rc_time_format',$A->config->get('time_format','H:i'));
				$A->output->add_header(calendar::generateCSS());
				$N='<div id="upcoming-content">'."\n";
				$N.='  <div id="today-title" class="boxtitle">'."\n";
				$N.='    <span id="calback"><a href="#">&laquo;</a>&nbsp;</span>'."\n";
				$N.='    <label for "today"><span id="caltoday"><a href="#">'.$A->gettext('today').'</a></span></label>'."\n";
				$N.='    <span id="calforward"><a href="#">&raquo</a></span>'."\n";
				$N.='  </div>'."\n";
				$N.='  <div id="upcoming-container">'."\n";
				$N.='    <div id="upcoming"></div>'."\n";
				for($xB=1;$xB<$A->config->get('cal_previews',0);$xB++)
				{
					$N.='    <div id="upcoming_'.$xB.'"></div>'."\n";
				}
				$N.='  </div>'."\n";
				$N.='</div>'."\n";
				$N.='<div id="calendaroverlay"></div>'."\n";
				$A->output->add_footer($N);
			}
			return$H;
		}
		function pass_ics_header($H)
		{
			if(is_array($H['messages']))
			{
				foreach($H['messages']as$bC)
				{
					if($bC->others['x-rc-attachment'])
					{
						$bC->list_flags['extra_flags']['ics']=1;
					}
				}
			}
			return$H;
		}
		function fetch_ics_header($H)
		{
			$FD=array('X-RC-Attachment');
			$H['fetch_headers']=trim($H['fetch_headers'].' '.strtoupper(join(' ',$FD)));
			return$H;
		}
		function message_load($H)
		{
			$this->message=$H['object'];
			foreach((array)$this->message->attachments as$IB)
			{
				if($IB->mimetype=='text/calendar'||$IB->mimetype=='text/ical'||$IB->mimetype=='application/ics')
				{
					$this->ical_parts[$IB->mime_id]=$IB->mime_id;
					$_SESSION['ical_uid'][$IB->mime_id]=$H['object']->uid;
				}
			}
			foreach((array)$this->message->parts as$GD=>$j)
			{
				if($j->mimetype=='text/calendar'||$j->mimetype=='text/ical')
				{
					$this->ical_parts[$j->mime_id]=$j->mime_id;
					$_SESSION['ical_uid'][$j->mime_id]=$H['object']->uid;
				}
			}
		}
		function preview_events($H)
		{
			$A=rcmail::get_instance();
			$v='';
			if($H['template']=='messagepreview')
			{
				$v='parent.';
			}
			if(is_array($this->myevents))
			{
				if(!$LC=@json_encode($this->myevents))
				{
					$LC=json_encode(array());
				}
				$A->output->add_script($v."rcmail.set_env({myevents:".$LC."});");
			}
		}
		function message_part_structure($H)
		{
			if(is_array($H['structure']->parts))
			{
				foreach($H['structure']->parts as$C=>$G)
				{
					if($G->mimetype=='text/calendar'||$G->mimetype=='text/ical'||$G->mimetype=='application/ics'||strstr(strtolower($G->filename),'.ics'))
					{
						$this->ical_parts[$G->mime_id]=$G->mime_id;
						$_SESSION['ical_uid'][$G->mime_id]=$H['object']->uid;
					}
				}
			}
			return$H;
		}
		function html_import($H)
		{
			if(class_exists('calendar')&&is_array($this->ical_parts))
			{
				$A=rcmail::get_instance();
				$A->output->add_header(calendar::generateCSS());
				$A->output->add_label('calendar.importconfirmation');
				$J=self::$calskin;
				$MC=array();
				$zB=$A->config->get('caldavs_subscribed',array());
				foreach($zB as$B=>$e)
				{
					if($e['readonly'])
					{
						unset($zB[$B]);
					}
				}
				$Q=$A->config->get('categories',array());
				$Q=array_merge($Q,$zB);
				/* CHANGE DEFAULT CALENDAR TEXT */
				$default_caldav_label = str_replace( "%u", $_SESSION[ 'username' ], $A->config->get('default_category','d0d0d0') );
				
				/* $q=html::tag('option',array('style'=>'background-color:#'.$A->config->get( 'default_category','d0d0d0'),'value'=>0),$A->config->get('default_category_label',$this->gettext('calendar.defaultcategory'))); */
				$q=html::tag('option',array('style'=>'background-color:#'.$A->config->get('default_category','d0d0d0'),'value'=>0),$default_caldav_label);
				foreach($Q as$B=>$e)
				{
					$q.=html::tag('option',array('class'=>asciiwords($B,true,'').' select_##item##_'.asciiwords($B,true,''),'value'=>addslashes($B)),$B);
				}
				$Z=html::tag('select',array('class'=>'invimportcategory','id'=>'select_##item##'),$q);
				foreach($this->ical_parts as$GD=>$GB)
				{
					$NC=$_SESSION['ical_uid'][$GB];
					$PC=$GB;
					$j=$NC&&$PC?$A->imap->get_message_part($NC,$PC,NULL,NULL,NULL,true):null;
					if($j)
					{
						$V=(array)$this->utils->importEvents($j,false,true,'preview');
						$bB=false;
						if(count($V)==0)
						{
							$bB=true;
							$V=(array)$this->utils->importEvents($j,false,true,'preview',false,false,false,false,false,'todos');
						}
						$MB=str_replace("\n","",serialize($V));
						$MB=str_replace("\r","",$MB);
						$MB=md5(strtolower(str_replace(" ","",$MB)));
						if($MC[$MB])
							continue;
						$MC[$MB]=true;
						$k="<div id='upcoming_preview' style='padding: 0.5em;'><table style='border-collapse:collapse;' border='1' id='upcoming_preview_table'>\n";
						$AB=0;
						$QC=array();
						foreach($V as$F)
						{
							if(is_array($F))
							{
								// SEND MAIL TEST
								$AB++;
								// $k.="<tr><td>".ucfirst($bB?$this->gettext('calendar.task'):$this->gettext('calendar.event')).":</td><td> #".$AB."</td></tr>\n";
								
								// NEW CODE
								$calendar_details_array = array();
								if( $F[ 'title' ] )
								{
									$calendar_details_array[ 'summary' ] = $F[ 'title' ];
								}
								if( $F[ 'location' ] )
								{
									$calendar_details_array[ 'location' ] = $F[ 'location' ];
								}
								if( $F[ 'selected_invitee_email_str' ] )
								{
									$calendar_details_array[ 'selected_invitee_email_str' ] = str_replace( "|", ", ", $F[ 'selected_invitee_email_str' ] );
								}
								if( isset( $F[ 'start' ] ) || isset( $F[ 'end' ] ) || isset( $F[ 'due' ] ) || isset( $F[ 'expires' ] ) )
								{
									if($F['allDay'])
									{
										if(!is_numeric($F['allDay']))
											$G=strtotime($F['allDay']);
										$F['allDay'] = $G-$this->getClientTimezone()*3600;
										$calendar_details_array[ 'startdate' ] = $F['allDay'];
									}
									if($F['start']==0)
									{
										$calendar_details_array[ 'startdate' ] = $this->gettext('calendar.nodate');
									}
									else
									{
										$calendar_details_array[ 'startdate' ] = date($A->config->get('date_long','d.m.Y H:i'),$F['start_unix']);
										$start_date = date($A->config->get('date_long','d.m.Y H:i'),$F['start_unix']);
										$month = date( 'M', strtotime( $start_date ) );
										$short_day = date( 'j', strtotime( $start_date ) );
										$long_day = date( 'D', strtotime( $start_date ) );
										$full_date = date( "jS F, Y", strtotime( $start_date ) );
									}
									if( isset( $F['start'] ) )
									{
										if($A->config->get('upcoming_cal',false))
										{
											$calendar_details_array[ 'import' ] .= " <small>[<a href=\"javascript:void(0)\" onclick=\"calendar_icalattach.preview(".($F['start_unix']*1000).");\">".$this->gettext('calendar.preview')."</a>]</small>";
										}
										$calendar_details_array[ 'import' ] .= "&nbsp;<a style='font-weight:bold;' href=\"javascript:void(0)\" onclick=\"calendar_icalattach.save('".JQ($GB)."', ".$AB.", $('#select_".$AB."').val())\">Import Event</a>";
									}
									if( isset( $F['end'] ) )
									{
										if($F['end']==$F['start']||$F['end']==0)
										{
											unset($F[$C]);
											$dB=true;
										}
									}
									if( isset( $F['due'] ) )
									{
										if($F['due']<$F['start']||$F['due']==0)
										{
											unset($F[$C]);
											$dB=true;
										}
									}
									if( isset( $F[ 'allDay' ] ) )
									{
										if(!$F['end'])
										{
											$calendar_details_array[ 'allday' ] = $this->gettext('calendar.yes');
										}
										else if($F['end']-$F['start']>83699)
										{
											$calendar_details_array[ 'allday' ] = $this->gettext('calendar.yes');
										}
										else
										{
											$calendar_details_array[ 'allday' ] = $this->gettext('calendar.no');
										}
									}
									if( isset( $F[ 'description' ] ) )
									{
										$calendar_details_array[ 'description' ] = $F[ 'description' ];
									}
									if( isset( $F[ 'category' ] ) )
									{
										$QC[$AB] = asciiwords( $F[ 'category' ], true, '' );
										$calendar_details_array[ 'category' ] = str_replace('##item##',$AB,$Z);
									}
								}
								$k .= "<tr>
									
									<td>
										<div style='font-weight:bold;margin-left:10px;margin-top:5px;'>
											".$calendar_details_array[ 'description' ]."
										</div>
										<div style='margin-left:10px;margin-top:5px;'>
											Location:&nbsp;".$calendar_details_array[ 'location' ]."
										</div>
										<div style='margin-left:10px;margin-top:5px;'>
											Members Attending:&nbsp;".$calendar_details_array[ 'selected_invitee_email_str' ]."
										</div>
										<div style='margin-left:10px;margin-top:5px;'>
											".$calendar_details_array[ 'category' ]."
										</div>
										<div style='margin-left:10px;margin-top:5px;'>
											".$calendar_details_array[ 'import' ]."
										</div>
									</td>
								</tr>
								";
								
								/* foreach($F as$C=>$G)
								{
									$dB=false;
									if($C!='id')
									{
										error_log( "*****************KEY : ".$C );
										// display category select dropdown
										if($C=='category')
										{
											error_log( "1" );
											if($G)
											{
												$QC[$AB]=asciiwords($G,true,'');
											}
											$G=str_replace('##item##',$AB,$Z);
										}
										
										// dislay allday: yes/no
										if($C=='allDay'&&!$bB)
										{
											error_log( "2" );
											$C='all-day';
											if(!$F['end'])
											{
												error_log( "2.1" );
												$G=$this->gettext('calendar.yes');
											}
											else if($F['end']-$F['start']>83699)
											{
												error_log( "2.2" );
												$G=$this->gettext('calendar.yes');
											}
											else
											{
												error_log( "2.3" );
												$G=$this->gettext('calendar.no');
											}
										}
										
										// summary Label. not actual summary
										if($C=='title')
										{
											error_log( "3" );
											$C='summary';
											error_log( "C VALUE: ".$C );
										}
										
										if($C=='start'||$C=='end'||$C=='due'||$C=='expires')
										{
											error_log( "4" );
											// formatting the date..
											if($F['allDay'])
											{
												error_log( "4.1" );
												if(!is_numeric($G))
													$G=strtotime($G);
												$F[$C]=$G-$this->getClientTimezone()*3600;
												$G=$F[$C];
											}
											if($F['start']==0)
											{
												error_log( "4.2" );
												$G=$this->gettext('calendar.nodate');
											}
											else
											{
												error_log( "4.3" );
												$G=date($A->config->get('date_long','d.m.Y H:i'),$F[$C.'_unix']);
											}
											
											// import link
											if($C=='start')
											{
												error_log( "4.4" );
												if($A->config->get('upcoming_cal',false)&&!$bB)
												{
													$G.=" <small>[<a href=\"javascript:void(0)\" onclick=\"calendar_icalattach.preview(".($F['start_unix']*1000).");\">".$this->gettext('calendar.preview')."</a>]</small>";
												}
												$G.="&nbsp;<small>[<a href=\"javascript:void(0)\" onclick=\"calendar_icalattach.save('".JQ($GB)."', ".$AB.", $('#select_".$AB."').val())\">".$this->gettext('import')."</a>]</small>";
											}
											
											// setting value od DB
											if($C=='end')
											{
												error_log( "4.5" );
												if($F['end']==$F['start']||$F['end']==0)
												{
													unset($F[$C]);
													$dB=true;
												}
											}
											if($C=='due')
											{
												error_log( "4.6" );
												if($F['due']<$F['start']||$F['due']==0)
												{
													unset($F[$C]);
													$dB=true;
												}
											}
										}
										if($G=='')
										{
											$dB=true;
											unset($F[$C]);
										}
										
										if($C=='description')
										{
											error_log( "5" );
											$G='<pre>'.$G.'</pre>';
										}
										if($dB!=true&&$C!='className'&&$C!='classNameDisp'&&$C!='editable'&&$C!='start_unix'&&$C!='end_unix'&&$C!='due_unix'&&$C!='component')
										{
											$k.="<tr><td>".ucfirst($this->gettext('calendar.'.$C)).":</td><td>".$G."</td></tr>\n";
										}
										error_log( "**********************" );
									}
								} */
							}
							// $k.="<tr><td colspan=\"2\"><hr /></td></tr>\n";
							$this->myevents[]=$F;
						}
						$k.="</table></div><br />\n";
						foreach($QC as$AD=>$B)
						{
							$A->output->add_script("$('.select_".$AD."_".$B."').prop('selected', true);",'docready');
						}
						$H['content'].=html::tag('div',array('style'=>'clear: both; height: 25px;')).$k;
						if($AB>1)
						{
							$H['content'].=html::tag('div',array('style'=>'clear: both;'));
							$H['content'].=html::p(array('id'=>'upcoming_preview_import','style'=>"margin:1em; padding:0.5em; border:1px solid #999; border-radius:4px; -moz-border-radius:4px; -webkit-border-radius:4px; width: auto;"),html::a(array('href'=>"#",'onclick'=>"return calendar_icalattach.save('".JQ($GB)."', false, $('#select_all').val())",'title'=>$this->gettext('calendar.addicalmsg')),html::img(array('src'=>$this->url("skins/$J/images/ical_save.png"),'align'=>'middle','title'=>'','alt'=>''))).' '.html::span(null,Q($this->gettext('calendar.addicalmsg')).'&nbsp;'.str_replace('##item##','all',$Z)));
						}
						$H['content'].=html::tag('script',array('type'=>'text/javascript'),'$("#upcoming_preview").width($("#upcoming_preview_table").width()); $("#upcoming_preview_import").width($("#upcoming_preview").width());');
					}
					$this->include_script('program/js/calendar.icalattach.js');
					if($A->action=='preview'&&$this->show_upcoming_cal)
					{
						$A->output->add_script('try{parent.$("#caltoday").click()}catch(e){$("#caltoday").click()}','foot');
					}
				}
			}
			return$H;
		}
		static function isSabreDAV($D)
		{
			if(strpos($D,'access=')!==false)
			{
				return false;
			}
			$A=rcmail::get_instance();
			$XB=array('%u',);
			$WB=array($A->user->data['username'],);
			$D=str_replace($XB,$WB,$D);
			$t=new MyRCHttp;
			$JC['method']='GET';
			$JC['referrer']='http'.(rcube_https_check()?'s':'').'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
			$JC['target']=$D;
			$t->initialize($JC);
			$t->useCurl(true);
			if(ini_get('safe_mode')||ini_get('open_basedir'))
			{
				$t->useCurl(false);
			}
			$t->SetTimeout(5);
			$t->execute();
			$N=($t->error)?$t->error:$t->result;
			if(substr($N,0,strlen('SabreDAV'))=='SabreDAV')
			{
				$I=explode(':',$N);
				$TB=explode('.',$_SERVER['HTTP_HOST']);
				if(count($TB)>2)
				{
					unset($TB[0]);
				}
				$TB=implode('.',$TB);
				if($I[1]&&stripos($I[1],$TB)!==false)
				{
					return true;
				}
				else
				{
					if($_SERVER['SERVER_ADDR']==$I[1])
					{
						return true;
					}
					else
					{
						return false;
					}
				}
			}
			else
			{
				return false;
			}
		}
		function upload_field($H)
		{
			$A=rcmail::get_instance();
			$KB=array('id'=>'rcmUploadform','class'=>'uploadform');
			$UB=parse_bytes(ini_get('upload_max_filesize'));
			$IC=parse_bytes(ini_get('post_max_size'));
			if($IC&&$IC<$UB)
				$UB=$IC;
			$UB=show_bytes($UB);
			/* CHANGE DEFAULT CALENDAR TEXT */
			$default_caldav_label = str_replace( "%u", $_SESSION[ 'username' ], $A->config->get('default_category_label',rcube_label('calendar.defaultcategory')) );
			
			/* $q=html::tag('option',array('value'=>0,'style'=>'background-color:#'.$A->config->get( 'default_category','d0d0d0').';'),$A->config->get('default_category_label',rcube_label('calendar.defaultcategory'))); */
			$q=html::tag('option',array('value'=>0,'style'=>'background-color:#'.$A->config->get('default_category','d0d0d0').';'),$default_caldav_label);
			$W=$A->config->get('caldavs_subscribed',array());
			foreach($W as$u=>$e)
			{
				if($e['readonly'])
				{
					unset($W[$u]);
				}
			}
			$Q=$A->config->get('categories',array());
			$W=array_merge($Q,$W);
			foreach($W as$u=>$e)
			{
				$q.=html::tag('option',array('value'=>$u,'class'=>asciiwords($u,true,'')),$u);
			}
			$kC=html::div($KB,html::div(null,$this->upload_field_content(array('size'=>25))).html::div('hint',rcube_label(array('name'=>'maxuploadsize','vars'=>array('size'=>$UB)))).html::tag('div',null,rcube_label('calendar.category').': '.html::tag('select',array('name'=>'_category','id'=>'uploadcategory'),$q)));
			$H['content']=$kC;
			return$H;
		}
		function upload_field_content($KB)
		{
			$KB['type']='file';
			$KB['name']='calimport';
			$KB['id']='calimport';
			$VB=new html_inputfield($KB);
			return$VB->show();
		}
		function subscriptions_table($H)
		{
			$A=rcmail::get_instance();
			$qB=array();
			$p=$A->config->get('backend');
			if($p=='caldav')
			{
				$W=array_merge($A->config->get('caldavs',array()),$A->config->get('public_caldavs',array()));
				$FB=$A->config->get('caldavs_subscribed',false);
				if(!is_array($FB))
				{
					$FB=$W;
				}
				$qB['caldav']=array_merge($A->config->get('caldavs_unsubscribed',array()),$W);
			}
			$Q=$A->config->get('categories',array());
			$lB=strtolower($A->user->data['username']);
			$rB=explode('@',$lB);
			$rB=strtolower($rB[0]);
			$XB=array('%u','%su');
			$WB=array($lB,$rB);
			/* CHANGE DEFAULT CALENDAR TEXT */
			$default_caldav_label = str_replace( "%u", $_SESSION[ 'username' ], $A->config->get('default_category_label',$this->gettext('calendar.defaultcategory')) );
			
			/* $a=html::tag('tr',null,html::tag('td',null,html::tag('div',array('class'=>asciiwords($y,true,''),'style'=>'background-color:#'.$A->config->get('default_category','d0d0d0').';border: 1px solid #'.$this->utils->getBorderColor($A->config->get('default_category','d0d0d0'))),html::tag('input',array('id'=>'chbox_default','class'=>'subscriptionchbox','name'=>'_default_caldav_subscribed','type'=>'checkbox','checked'=>$A->config->get('default_caldav_subscribed',true),'value'=>1)))).html::tag('td',array('title'=>$p=='caldav'?str_replace($XB,$WB,$A->config->get('caldav_url')):''),'&nbsp;'.$A->config->get('default_category_label',$this->gettext('calendar.defaultcategory')).'&nbsp;'.html::tag('small',null,$p=='caldav'?html::tag('i',null,'('.$this->gettext('calendar.caldav').')'):''))); */
			$a=html::tag('tr',null,html::tag('td',null,html::tag('div',array('class'=>asciiwords($y,true,''),'style'=>'background-color:#'.$A->config->get('default_category','d0d0d0').';border: 1px solid #'.$this->utils->getBorderColor($A->config->get('default_category','d0d0d0'))),html::tag('input',array('id'=>'chbox_default','class'=>'subscriptionchbox','name'=>'_default_caldav_subscribed','type'=>'checkbox','checked'=>$A->config->get('default_caldav_subscribed',true),'value'=>1)))).html::tag('td',array('title'=>$p=='caldav'?str_replace($XB,$WB,$A->config->get('caldav_url')):''),'&nbsp;'.$default_caldav_label.'&nbsp;'.html::tag('small',null,$p=='caldav'?html::tag('i',null,'('.$this->gettext('calendar.caldav').')'):'')));
			$wB=$A->config->get('public_calendarfeeds',array());
			foreach($wB as$D=>$e)
			{
				$I=explode('|',$e);
				$wB[$D]=$I[0];
			}
			$_B=array_merge($A->config->get('calendarfeeds',array()),$wB);
			$l=$A->config->get('feeds_subscribed',false);
			if(!is_array($l))
			{
				$l=$_B;
			}
			$qB['feed']=$_B;
			if($A->config->get('calendar_subscriptions_view','filters')=='subscriptions')
			{
				$dC=' subscriptionlink-selected';
				$SC='';
				$gC='block';
				$fC='none';
			}
			else
			{
				$dC='';
				$SC=' subscriptionlink-selected';
				$gC='none';
				$fC='block';
			}
			$cB=$this->gettext('calendar.subscriptions');
			$ZB=$this->gettext('calendar.filter');
			if(strlen($cB)>13)
			{
				$cB=substr($cB,0,10).'...';
			}
			if(strlen($ZB)>13)
			{
				$ZB=substr($ZB,0,10).'...';
			}
			$H['content']=html::tag('span',array('class'=>'boxtitle','id'=>'subscriptiontoggle-content'),html::tag('input',array('id'=>'subscriptiontoggle','type'=>'checkbox','title'=>$this->gettext('calendar.checkall')))).html::tag('div',array('id'=>'subscriptionmenu','class'=>'boxtitle'),html::tag('center',null,html::tag('a',array('id'=>'filterslink','href'=>'#','class'=>'subscriptionlink'.$SC),html::tag('span',array('class'=>'subscriptionslinktext','title'=>$this->gettext('calendar.filter')),$ZB)).html::tag('div',array('style'=>'display: inline; min-width: 1px;'),' ').html::tag('a',array('id'=>'subscriptionlink','href'=>'#','class'=>'subscriptionlink'.$dC),html::tag('span',array('class'=>'subscriptionslinktext','title'=>$this->gettext('calendar.subscriptions')),$cB))));
			foreach($qB as$AC=>$WC)
			{
				ksort($WC);
				foreach($WC as$B=>$e)
				{
					if($AC=='caldav')
					{
						$X=$e['url'];
						$X=str_replace($XB,$WB,$X);
						$y=$B;
						if(isset($FB[$B]))
						{
							$i=true;
						}
						else
						{
							$i=false;
						}
					}
					else
					{
						$X=$B;
						$y=$e;
						if(isset($l[$B]))
						{
							$i=true;
						}
						else
						{
							$i=false;
						}
					}
					$_SESSION['available_categories'][$y]=true;
					$a.=html::tag('tr',null,html::tag('td',null,html::tag('div',array('class'=>asciiwords($y,true,''),'style'=>'background-color:#'.$Q[$y].';'),html::tag('input',array('id'=>'chbox_'.asciiwords($y,true,''),'type'=>'checkbox','name'=>'_'.$AC.'s[]','value'=>$B,'checked'=>$i,'class'=>'subscriptionchbox')))).html::tag('td',array('title'=>$X),'&nbsp;'.$y.'&nbsp;'.html::tag('small',null,html::tag('i',null,'('.$this->gettext('calendar.'.$AC).')'))));
				}
			}
			if($a)
			{
				$k=html::tag('table',null,$a);
				$KC=html::tag('form',array('id'=>'subscription_form','name'=>'subscription_form','action'=>'./?_task=dummy&_action="plugin.calendar_subscribe','method'=>'post'),html::tag('div',array('id'=>'subscription-table-content','style'=>'display:'.$gC),$k));
				$H['content'].=$KC;
			}
			$M=(array)$_SESSION['event_filters'];
			$M=array_flip($M);
			$i=false;
			// CALENDAR FILTER CHANGES
			// if(isset($M['default']))
			if(isset($M[$_SESSION[ 'username' ]]))
			{
				$i=true;
			}
			if($A->config->get('backend')=='caldav')
			{
				if($A->config->get('default_caldav_subscribed',true))
				{
					$DC=true;
				}
				else
				{
					$DC=false;
				}
			}
			else
			{
				$DC=true;
			}
			if($DC)
			{
				/* CHANGE DEFAULT CALENDAR TEXT */
				$default_caldav_label = str_replace( "%u", $_SESSION[ 'username' ], $A->config->get('default_category_label',$this->gettext('calendar.defaultcategory')) );
				
				/* $a=html::tag('tr',array('class'=>'filter_default'),html::tag('td',null,html::tag('div',array('id'=>'chbox_default','class'=>'default','style'=>'background-color:#'.$A->config->get('default_category','d0d0d0').';border: 1px solid #'.$this->utils->getBorderColor($A->config->get('default_category','d0d0d0'))),html::tag('input',array('class'=>'filterschbox','name'=>'_filters[]','type'=>'checkbox','checked'=>$i,'value'=>'default')))).html::tag('td',array('title'=>str_replace($XB,$WB,$A->config->get('caldav_url'))),'&nbsp;'.$A->config->get('default_category_label',$this->gettext('calendar.defaultcategory')))); */
				// CALENDAR FILTER CHANGES
				$a=html::tag('tr',array('class'=>'filter_default'),html::tag('td',null,html::tag('div',array('id'=>'chbox_default','class'=>'default','style'=>'background-color:#'.$A->config->get('default_category','d0d0d0').';border: 1px solid #'.$this->utils->getBorderColor($A->config->get('default_category','d0d0d0'))),html::tag('input',array('class'=>'filterschbox','name'=>'_filters[]','type'=>'checkbox','checked'=>$i,'value'=>$_SESSION[ 'username' ])))).html::tag('td',array('title'=>str_replace($XB,$WB,$A->config->get('caldav_url'))),'&nbsp;'.$default_caldav_label));
			}
			else
			{
				$a='';
			}
			$Q=array_merge($A->config->get('categories',array()),$A->config->get('public_categories',array()));
			$l=array_flip($l);
			foreach($l as$B=>$D)
			{
				$I=explode('|',$B);
				unset($l[$B]);
				$l[$I[0]]=$D;
			}
			foreach($Q as$B=>$e)
			{
				if(isset($W[$B])||isset($_B[$B]))
				{
					if(!isset($FB[$B])&&!isset($l[$B]))
					{
						unset($Q[$B]);
					}
				}
			}
			ksort($Q);
			foreach($Q as$B=>$lC)
			{
				$i=false;
				if(isset($M[$B]))
				{
					$i=true;
				}
				$a.=html::tag('tr',array('class'=>'filter_'.asciiwords($B,true,'')),html::tag('td',null,html::tag('div',array('class'=>asciiwords($B,true,''),'style'=>'background-color:#'.$lC.';'),html::tag('input',array('id'=>'chbox_'.asciiwords($B,true,''),'type'=>'checkbox','name'=>'_filters[]','value'=>$B,'checked'=>$i,'class'=>'filterschbox')))).html::tag('td',null,'&nbsp;'.$B));
			}
			$k=html::tag('table',null,$a);
			$KC=html::tag('form',array('id'=>'filters_form','name'=>'filters_form','action'=>'./?_task=dummy&_action=plugin.calendar_setfilters','method'=>'post'),html::tag('div',array('id'=>'filters-table-content','style'=>'display:'.$fC),$k));
			$H['content'].=$KC;
			return$H;
		}
		function subscription_view()
		{
			if($_SESSION['user_id'])
			{
				$hC=get_input_value('_view',RCUBE_INPUT_POST);
				rcmail::get_instance()->user->save_prefs(array('calendar_subscriptions_view'=>$hC));
			}
		}
	}