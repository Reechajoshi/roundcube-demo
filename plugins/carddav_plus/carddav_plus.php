<?php
# 
# This file is part of Roundcube "carddav_plus" plugin.
# 
# Your are not allowed to distribute this file or parts of it.
# 
# This file is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
# 
# Copyright (c) 2012 - 2014 Roland 'Rosali' Liebl - all rights reserved.
# dev-team [at] myroundcube [dot] net
# http://myroundcube.net
# 

class carddav_plus extends rcube_plugin
{
	static private $plugin='calendar_plus';
	static private$author='myroundcube@mail4us.net';
	static private$authors_comments='<font color="green"><u>GOOGLE CONTACTS</u>: Google CardDAV is back (requires <a href="#google_oauth2" class="anchorLink">google_oauth2</a> plugin).</font> Note: Setting up google_oauth2 requires deeper knowledge about Google APIs (<a href="https://cloud.google.com/console" target="_new">Google Develper Console</a>).<br /><font color="red"><u>WARNING</u>: We have implemented new methods to autodetect, create and delete CardDAV collections. Testings have passed our quality reviews successfully. Nevertheless, make sure to have a backup of your DAV resources to prevent any data loss.</font>';
	static private$download='http://myroundcube.googlecode.com';
	static private$version='3.0.17';
	static private$date='07-02-2014';
	static private$licence='All Rights reserved';
	static private$requirements=array(
		'Roundcube'=>'0.9',
		'PHP'=>'5.2.1',
		'required_plugins'=>array(
			'http_request'=>'require_plugin'
		),
	);
	static private$f;
	
	function init()
	{
		self::$f=$this;
		$this->require_plugin('http_request');
		if($_GET['_action']=='plugin.carddav_get_shares')
		{
			$this->startup();
		}
		$this->register_action('plugin.carddav_shareinvitation',array($this,'shareinvitation'));
		$this->add_hook('message_compose',array($this,'message_compose'));
		$this->add_hook('message_compose_body',array($this,'message_compose_body'));
	}
	
	static function carddav_coltypes()
	{
		return array(
			'name',
			'firstname',
			'surname',
			'middlename',
			'prefix',
			'suffix',
			'nickname',
			'jobtitle',
			'organization',
			'department',
			'assistant',
			'manager',
			'gender',
			'maidenname',
			'spouse',
			'email',
			'phone',
			'address',
			'birthday',
			'anniversary',
			'website',
			'im',
			'notes',
			'photo'
		);
	}
	
	static function about($DB=false)
	{
		$u=self::$requirements;
		foreach(array('required_','recommended_')as$w)
		{
			if(is_array($u[$w.'plugins']))
			{
				foreach($u[$w.'plugins']as$c=>$X)
				{
					if(class_exists($c)&&method_exists($c,'about'))
					{
						$vB=new$c(false);
						$u[$w.'plugins'][$c]=array(
							'method'=>$X,
							'plugin'=>$vB->about($DB),
						);
					}
					else
					{
						$u[$w.'plugins'][$c]=array(
							'method'=>$X,
							'plugin'=>$c,
						);
					}
				}
			}
		}
		$N=array(
			'plugin'=>self::$plugin,
			'version'=>self::$version,
			'date'=>self::$date,
			'author'=>self::$author,
			'comments'=>self::$authors_comments,
			'licence'=>self::$licence,
			'download'=>self::$download,
			'requirements'=>$u,
		);
		if(is_array($DB))
		{
			$r=array(
				'plugin'=>self::$plugin
			);
			foreach($DB as$F)
			{
				$r[$F]=$N[$F];
			}
			return$r;
		}
		else
		{
			return$N;
		}
	}
	
	static function carddav_ext($C,$uB)
	{
		if(stripos($C,'https://www.googleapis.com/carddav')!==false)
		{
			return'';
		}
		else
		{
			return'.'.$uB;
		}
	}
	
	static function carddav_add_collection($K,$o,$b,$C,$E)
	{
		$q=new carddav_backend($K);
		$q->set_auth($o,$b);
		$q->add_collection(slashify($C),$E);
	}
	
	static function carddav_delete_collection($K,$o,$b,$C)
	{
		$q=new carddav_backend($K);
		$q->set_auth($o,$b);
		$q->delete_collection(slashify($C));
	}
	
	static function carddav_put($jB,$K,$eB,$CB,$b,$zB,$gB,$X,$_,$kB,$n,$OB,$TC)
	{
		$MB=time();
		if($CB=='***TOKEN***'&&$_SESSION['access_token'])
		{
			$v='Authorization: Bearer '.$_SESSION['access_token'];
		}
		else if($zB=='Digest')
		{
			if(!$R=fsockopen($jB.'://'.$K,$eB,$qB,$JC,15))
			{
				return false;
			}
			$M="$X $_ HTTP/1.1\r\n";
			$M.="Host: $K\r\n";$M.="User-Agent: ".$gB."\r\n";
			$M.="Connection: Close\r\n\r\n";
			fwrite($R,$M);
			while(!feof($R))
			{
				$nB=fgets($R,512);
				if(strpos($nB,"WWW-Authenticate:")!==false)
					$v=trim(substr($nB,18));
			}
			fclose($R);
			$MC=explode(",",$v);
			$BB=array();
			foreach($MC as$BC)
			{
				$JB=explode("=",$BC);
				$BB[trim($JB[0])]=substr($JB[1],1,strlen($JB[1])-2);
			}
			$fB=$BB['nonce'];
			$OC=$BB['opaque'];
			$hB=$BB['Digest realm'];
			$iB='sausages';
			$QC=$CB.':'.$hB.':'.$b;$PC=$X.':'.$_;
			$KC=md5($QC);
			$EC=md5($PC);
			$DC=$KC.':'.$fB.':00000001:'.$iB.':auth:'.$EC;
			$CC=md5($DC);
			$v="Authorization: Digest username=\"".$CB."\", realm=\"$hB\", qop=\"auth\", algorithm=\"MD5\", uri=\"$_\", nonce=\"$fB\", nc=00000001, cnonce=\"$iB\", opaque=\"$OC\", response=\"$CC\"";
		}
		else
		{
			$v="Authorization: Basic ".base64_encode($CB.':'.$b);
		}
		$M="$X $_ HTTP/1.1\r\n";
		$M.="Host: $K\r\n";
		$M.="Connection: Close\r\n";
		$M.="Content-Type: ".$kB."\r\n";
		$M.="Content-Length: ".strlen($n)."\r\n";
		$M.="User-Agent: ".$gB."\r\n";
		$M.=$v."\r\n\r\n";
		$M.=$n;
		if(!$R=fsockopen($jB.'://'.$K,$eB,$qB,$JC,15))
		{
			return false;
		}
		fwrite($R,$M);
		while(!feof($R))
		{
			$RB.=fgets($R,8192);
		}
		fclose($R);
		if(rcmail::get_instance()->config->get('carddav_debug',false))
		{
			write_log('CardDAV-timeline',"$X $C $kB $OB");
			write_log('CardDAV-timeline',time()-$MB);
			write_log('CardDAV-timeline',$RB);
		}
		$W=explode(' ',self::extractCustomHeader('HTTP\/1.1 ','\n',$RB));
		$W=$W[0];
		$g=explode("\r\n\r\n",$RB);
		if($g[1])
		{
			$r=$g[1];
		}
		else
		{
			$r='';
		}
		if($W==200||$W==207)
		{
			return($OB===true?true:$r);
		}
		else if($OB===true&&($W==201||$W==204))
		{
			return true;
		}
		else if($W==400||$W==401)
		{
			return false;
		}
		else
		{
			return false;
		}
		return true;
	}
	
	static function extractCustomHeader($MB,$FC,$GC)
	{
		$IC='/'.$MB.'(.*?)'.$FC.'/';
		if(preg_match($IC,$GC,$T))
		{
			return$T[1];
		}
		else
		{
			return false;
		}
	}
	
	static function carddav_settings($B,$oB=array(),$LC=false)
	{
		$A=rcmail::get_instance();
		if($B['section']=='addressbookcarddavs')
		{
			$B['blocks']['addressbookcarddavs']['options']['page']=array(
				'title'=>'',
				'content'=>$LC,
			);
			$A->output->add_script('$("td.title").remove();$("table.propform td").css("width", "auto");$(".mainaction").attr("onclick", "return rcmail.command(\'plugin.carddav-server-save\', \'\', this)");','docready');
		}
		else if($B['section']=='addressbook')
		{
			$mB=$A->config->get('use_auto_abook',true);
			$H='rcmfd_use_auto_abook';
			$l=new html_checkbox(array(
				'name'=>'_use_auto_abook',
				'id'=>$H,
				'value'=>1
			));
			$B['blocks']['automaticallycollected']['name']=self::$f->gettext('carddav.automaticallycollected');
			$B['blocks']['automaticallycollected']['options']['use_subscriptions']=array(
				'title'=>html::label($H,Q(self::$f->gettext('carddav.useautoabook'))),
				'content'=>$l->show($mB?1:0),
			);
			$lB=$A->config->get('use_auto_abook_for_completion',true);
			$dB='rcmfd_use_auto_abook_for_completion';
			$NC=new html_checkbox(array(
				'name'=>'_use_auto_abook_for_completion',
				'id'=>$dB,
				'value'=>1
			));
			$B['blocks']['automaticallycollected']['name']=self::$f->gettext('carddav.automaticallycollected');
			$B['blocks']['automaticallycollected']['options']['use_autocompletion']=array(
				'title'=>html::label($dB,Q(self::$f->gettext('carddav.useforcompletion'))),
				'content'=>$NC->show($lB?1:0),
			);
			$y=new html_select(array(
				'name'=>'_automatic_addressbook',
				'id'=>'rcmfd_automatic_addressbook_selector'
			));
			$Q='SELECT user_id FROM '.get_table_name('contacts').' WHERE user_id=? AND del<>?';
			$T=$A->db->query($Q,$A->user->ID,1);
			if($A->db->num_rows($T)>0||$A->config->get('show_empty_database_addressbooks',true))
			{
				$y->add(self::$f->gettext('carddav.defaultaddressbook').' ('.self::$f->gettext('carddav.local').')','default');
			}
			$Q='SELECT user_id FROM '.get_table_name('collected_contacts').' WHERE user_id=? AND del<>?';
			$T=$A->db->query($Q,$A->user->ID,1);
			if($A->db->num_rows($T)>0||$A->config->get('show_empty_database_addressbooks',true))
			{
				$y->add(self::$f->gettext('carddav.automaticallycollected_local'),'sql');
			}
			if(is_array($oB['sources']))
			{
				foreach($oB['sources']as$GB)
				{
					if($GB['readonly']!==true)
					{
						$y->add($GB['name'],$GB['id']);
					}
				}
				$B['blocks']['automaticallycollected']['options']['addressbooks']=array(
					'title'=>html::label($H,Q(self::$f->gettext('carddav.automatic_addressbook'))),
					'content'=>$y->show($A->config->get('automatic_addressbook','sql'),
						array(
							'name'=>"_automatic_addressbook"
						)
					),
				);
			}
		}
		else if($B['section']=='addressbooksharing')
		{
			if(class_exists('sabredav'))
			{
				self::$f->include_script('flashclipboard.js');
				$A->output->add_footer(html::tag('div',array(
					'id'=>'zclipdialog',
					'title'=>self::$f->gettext('carddav.copiedtoclipboard')
				)));
				$FB=sabredav::about(array('version'));
				$FB=$FB['version'];
				if($FB>='4.0.2')
				{
					$U=self::$f->local_skin_path();
					if($U=='skins/larry')
						$U='skins/classic';
					$EB='';
					$HB='./'.$U.'/images/icons/delete.png';
					if($A->config->get('skin')=='larry')
					{
						$EB='background: url(./skins/larry/images/buttons.png) -7px -377px no-repeat;';
						$HB='plugins/carddav/skins/larry/blank.gif';
					}
					$H='rcmfd_abook_token';
					$Y=$A->config->get('carddavtoken');
					$a='document.getElementById("'.$H.'").value="";';
					$t=html::tag('input',array(
						'type'=>'image',
						'style'=>$EB,
						'name'=>'_carddavtoken_submit',
						'value'=>'0',
						'src'=>$HB,
						'onclick'=>$a,
						'align'=>'absmiddle',
						'alt'=>self::$f->gettext('delete'),
						'title'=>self::$f->gettext('delete')
					));
					$m='readonly';
					$E=self::$f->gettext('carddav.isenabled');
					if(!$Y)
					{
						$a='document.getElementById("'.$H.'").disabled="";document.forms.form.submit();';
						$t="&nbsp;".html::tag('input',array(
							'name'=>'_carddavtoken_submit',
							'value'=>'1',
							'type'=>'checkbox',
							'onclick'=>$a
						));
						$E=self::$f->gettext('carddav.isdisabled');
					}
					$B['blocks']['addressbook_shares']['name']=self::$f->gettext('carddav.confidentialcarddavaccess');
					$O=new html_inputfield(array(
						'readonly'=>'readonly',
						'value'=>$_SESSION['username'],
						'size'=>strlen($_SESSION['username'])
					));
					$B['blocks']['addressbook_shares']['options']['username']=array(
						'title'=>html::label($H,Q(self::$f->gettext('username'))),
						'content'=>$O->show().'&nbsp; '.html::tag('img',array(
							'class'=>'zclip',
							'src'=>'plugins/carddav/'.$U.'/clipboard.png',
							'title'=>self::$f->gettext('carddav.copytoclipboard'),
							'alt'=>self::$f->gettext('carddav.copytoclipboard')
						))
					);
					$B['blocks']['addressbook_shares']['name']=html::tag('span',array(
						'class'=>'confidentialcarddavaccess'
					),
					sprintf(self::$f->gettext('carddav.confidentialcarddavaccess'),self::$f->gettext('carddav.readwrite').'&nbsp;'.$E));
					$O=new html_inputfield(array(
						'name'=>'_carddavtoken',
						$m=>$m,
						'id'=>$H,
						'value'=>$Y,
						'size'=>8,
						'maxlength'=>8
					));
					$B['blocks']['addressbook_shares']['options']['token']=array(
						'title'=>html::label($H,Q(self::$f->gettext('carddav.abooktoken'))),
						'content'=>$O->show($Y).'&nbsp; '.html::tag('img',array(
							'class'=>'zclip',
							'src'=>'plugins/carddav/'.$U.'/clipboard.png',
							'title'=>self::$f->gettext('carddav.copytoclipboard'),
							'alt'=>self::$f->gettext('carddav.copytoclipboard'))).'&nbsp;'.$t,
					);
					if(isset($_GET['_framed']))
					{
						$s='';
						if($A->config->get('carddavtoken'))
						{
							$s='&nbsp;'.html::tag('span',array(
								'class'=>'sharelink'
							),
							'['.html::tag('a',array(
								'href'=>'#',
								'onclick'=>'return carddav_share_dialog()'
							),
							Q(self::$f->gettext('carddav.shareinvitation'))).']');
						}
						$B['blocks']['addressbook_shares']['options']['shares']=array(
							'title'=>'&nbsp;'.html::tag('b',null,html::label($H,Q(self::$f->gettext('carddav.share')))).$s,
							'content'=>html::tag('b',null,Q(self::$f->gettext('carddav.resource')))
						);
						$Z='SELECT * from '.get_table_name('carddav_server').' WHERE user_id=?';
						$N=$A->db->query($Z,$A->user->ID);
						$e=array();
						while($G=$A->db->fetch_assoc($N))
						{
							$e[]=$G;
						}
						foreach($e as$F=>$G)
						{
							if($G['read_only'])
							{
								continue;
							}
							$C=str_replace('%u',$_SESSION['username'],$G['url']);
							if(strpos($C,'?')===false)
							{
								$C.='?issabredav=1';
							}
							else
							{
								$C.='&issabredav=1';
							}
							$N=carddav_plus::isSabreDAV($C);
							if($N)
							{
								$C=str_replace('%u',$_SESSION['username'],$G['url']);
								$D=parse_url($C);
								$D['host']=$A->config->get('sabredav_readwrite_host',$D[0]);
								$C=$D['scheme'].'://'.$D['host'].$D['path'];
								$D=explode('/',$G['url']);
								$J=$D[count($D)-1];
								$H='rcmfd_abook_shares_'.$J;
								$f=$A->config->get('carddav_shares_'.strtolower($J))?true:false;
								$l=new html_checkbox(array(
									'name'=>'_carddav_shares_'.strtolower($J),
									'value'=>1,
									'onclick'=>'document.forms.form.submit();'
								));
								$O=new html_inputfield(array(
									'readonly'=>'readonly',
									'value'=>$C,
									'size'=>80
								));
								$e[$F]['share_url']=$C;
								if(!isset($B['blocks']['addressbook_shares']['options']['resources_'.strtolower($J)]))
								{
									$B['blocks']['addressbook_shares']['options']['resources_'.strtolower($J)]=array(
										'title'=>$l->show($f).'&nbsp;'.$G['label'],
										'content'=>$O->show().'&nbsp; '.html::tag('img',array(
											'class'=>'zclip',
											'src'=>'plugins/carddav/'.$U.'/clipboard.png',
											'title'=>self::$f->gettext('carddav.copytoclipboard'),
											'alt'=>self::$f->gettext('carddav.copytoclipboard')
										))
									);
								}
							}
						}
						$B['blocks']['addressbook_shares']['options']['shareshint']=array(
							'title'=>'',
							'content'=>html::tag('small',null,html::tag('b',null,self::$f->gettext('carddav.sharehint')))
						);
						$H='rcmfd_abook_token_davreadonly';
						$Y=$A->config->get('carddavtoken_davreadonly','');
						$a='document.getElementById("'.$H.'").value="";';
						$t=html::tag('input',array(
							'type'=>'image',
							'style'=>$EB,
							'name'=>'_carddavtoken_davreadonly_submit',
							'value'=>'0',
							'src'=>$HB,
							'onclick'=>$a,
							'align'=>'absmiddle',
							'alt'=>self::$f->gettext('delete'),
							'title'=>self::$f->gettext('delete'))
						);
						$m='readonly';
						$E=self::$f->gettext('carddav.isenabled');
						if($Y=="")
						{
							$a='document.getElementById("'.$H.'").disabled="";document.forms.form.submit();';
							$t="&nbsp;".html::tag('input',array(
								'name'=>'_carddavtoken_davreadonly_submit',
								'value'=>'1',
								'type'=>'checkbox',
								'onclick'=>$a
							));
							$E=self::$f->gettext('carddav.isdisabled');
						}
						$B['blocks']['addressbook_shares_readonly']['name']=self::$f->gettext('carddav.confidentialcarddavaccess_readonly');
						$O=new html_inputfield(array(
							'readonly'=>'readonly',
							'value'=>$_SESSION['username'],
							'size'=>strlen($_SESSION['username'])
						));
						$B['blocks']['addressbook_shares_readonly']['options']['username']=array(
							'title'=>html::label($H,Q(self::$f->gettext('username'))),
							'content'=>$O->show().'&nbsp; '.html::tag('img',array(
								'class'=>'zclip',
								'src'=>'plugins/carddav/'.$U.'/clipboard.png',
								'title'=>self::$f->gettext('carddav.copytoclipboard'),
								'alt'=>self::$f->gettext('carddav.copytoclipboard')
							))
						);
						$B['blocks']['addressbook_shares_readonly']['name']=html::tag('span',array(
								'class'=>'confidentialcarddavaccess'
							),sprintf(self::$f->gettext('carddav.confidentialcarddavaccess'), self::$f->gettext('carddav.readonly').'&nbsp;'.$E));
						$O=new html_inputfield(array(
							'name'=>'_carddavtoken_davreadonly',
							$m=>$m,
							'id'=>$H,
							'value'=>$Y,
							'size'=>8,
							'maxlength'=>8
						));
						$B['blocks']['addressbook_shares_readonly']['options']['token']=array(
							'title'=>html::label($H,Q(self::$f->gettext('carddav.abooktoken'))),
							'content'=>$O->show($Y).'&nbsp; '.html::tag('img',array(
								'class'=>'zclip',
								'src'=>'plugins/carddav/'.$U.'/clipboard.png',
								'title'=>self::$f->gettext('carddav.copytoclipboard'),
								'alt'=>self::$f->gettext('carddav.copytoclipboard'))).'&nbsp;'.$t,
						);
						$s='';
						if($A->config->get('carddavtoken_davreadonly'))
						{
							$s='&nbsp;'.html::tag(
								'span',
								array('class'=>'sharelink'),
								'['.html::tag('a',array(
									'href'=>'#',
									'onclick'=>'return carddav_share_dialog()'
								),
								Q(self::$f->gettext('carddav.shareinvitation'))
								).']'
							);
						}
						$B['blocks']['addressbook_shares_readonly']['options']['shares']=array(
							'title'=>'&nbsp;'.html::tag('b',null,html::label($H,Q(self::$f->gettext('carddav.share')))).$s,
							'content'=>html::tag('b',null,Q(self::$f->gettext('carddav.resource')))
						);
						foreach($e as$F=>$G)
						{
							$C=str_replace('%u',$_SESSION['username'],$G['url']);
							if(strpos($C,'?')===false)
							{
								$C.='?issabredav=1';
							}
							else
							{
								$C.='&issabredav=1';
							}
							$N=carddav_plus::isSabreDAV($C);
							if($N)
							{
								$C=str_replace('%u',$_SESSION['username'],$G['url']);
								$D=parse_url($C);
								$D['host']=$A->config->get('sabredav_readonly_host',$D[0]);
								$C=$D['scheme'].'://'.$D['host'].$D['path'];
								$D=explode('/',$G['url']);
								$J=$D[count($D)-1];
								$H='rcmfd_abook_shares_readonly_'.$J;
								$f=$A->config->get('carddav_shares_readonly_'.strtolower($J))?true:false;
								$l=new html_checkbox(array(
									'name'=>'_carddav_shares_readonly_'.strtolower($J),
									'value'=>1,
									'onclick'=>'document.forms.form.submit();'
								));
								$O=new html_inputfield(array(
									'readonly'=>'readonly',
									'value'=>$C,
									'size'=>80
								));
								$e[$F]['share_url_readonly']=$C;
								if(!isset($B['blocks']['addressbook_shares_readonly']['options']['resources_readonly_'.strtolower($J)]))
								{
									$B['blocks']['addressbook_shares_readonly']['options']['resources_readonly_'.strtolower($J)]=array(
										'title'=>$l->show($f).'&nbsp;'.$G['label'],
										'content'=>$O->show().'&nbsp; '.html::tag('img',array(
											'class'=>'zclip',
											'src'=>'plugins/carddav/'.$U.'/clipboard.png',
											'title'=>self::$f->gettext('carddav.copytoclipboard'),
											'alt'=>self::$f->gettext('carddav.copytoclipboard')))
									);
								}
							}
						}
						$B['blocks']['addressbook_shares_readonly']['options']['shareshint']=array(
							'title'=>'',
							'content'=>html::tag('small',null,html::tag('b',null,self::$f->gettext('carddav.sharehint_readonly')))
						);
						$x='';
						foreach($e as$G)
						{
							if($G['share_url']||$G['share_url_readonly'])
							{
								$D=explode('/',$G['share_url']);
								$J=$D[count($D)-1];
								$f=$A->config->get('carddav_shares_'.strtolower($J))?true:false;
								$ZB=$A->config->get('carddav_shares_readonly_'.strtolower($J))?true:false;
								if($f||$ZB)
								{
									$WB='';
									if($f)
									{
										$WB=html::tag('td',null,html::tag('input',array(
											'type'=>'checkbox',
											'name'=>'_share[]',
											'value'=>$G['carddav_server_id'],
											'class'=>'shareinvitation'
										))).html::tag('td',null,$G['label']).html::tag('td',null,html::tag('small',null,'('.self::$f->gettext('carddav.readwrite').')'));$x.=html::tag('tr',null,$WB);
									}
									$XB='';
									if($ZB)
									{
										$XB=html::tag('td',null,html::tag('input',array(
											'type'=>'checkbox',
											'name'=>'_share_readonly[]',
											'value'=>$G['carddav_server_id'],
											'class'=>'shareinvitation'
										))).html::tag('td',null,$G['label']).html::tag('td',null,html::tag('small',null,'('.self::$f->gettext('carddav.readonly').')'));
										$x.=html::tag('tr',null,$XB);
									}
								}
							}
						}
						$d='';
						if($x)
						{
							$RC=html::tag('div',array(
								'id'=>'sharedialog',
								'style'=>'display: none'
							),
							html::tag('form',array(
								'action'=>'./',
								'method'=>'post',
								'id'=>'formshareinvitation',
								'name'=>'shareinvitation'
							),
							html::tag('table',null,$x)));
						}
						else
						{
							$d='$(".sharelink").hide();';
						}
						$A->output->add_label('carddav.checkresources','carddav.send','carddav.cancel','carddav.checkatleastoneresource');
						$A->output->add_footer($RC);
						$A->output->add_script('$(".propform").attr("action", "./?_framed=1");'.$d,'docready');
					}
				}
			}
		}
		return$B;
	}
	
	static function save_prefs($B,$HC)
	{
		if($B['section']=='addressbook')
		{
			$A=rcmail::get_instance();
			$mB=$A->config->get('use_auto_abook');
			$B['prefs']['use_auto_abook']=isset($_POST['_use_auto_abook'])?true:false;
			$lB=$A->config->get('use_auto_abook_for_completion');
			$B['prefs']['use_auto_abook_for_completion']=isset($_POST['_use_auto_abook_for_completion'])?true:false;
			if($SB=get_input_value('_automatic_addressbook',RCUBE_INPUT_POST))
			{
				$B['prefs']['automatic_addressbook']=$SB;
				if($SB!='sql')
				{
					if(get_input_value('_use_auto_abook_for_completion',RCUBE_INPUT_POST)==1)
					{
						$Q="UPDATE ".get_table_name('carddav_server')." SET autocomplete=? WHERE carddav_server_id=? AND user_id=?";
						$A->db->query($Q,1,str_replace($HC,'',$SB),$A->user->data['user_id']);
					}
				}
			}
		}
		else if($B['section']=='addressbooksharing')
		{
			$A=rcmail::get_instance();
			$B['prefs']['carddavtoken']=get_input_value('_carddavtoken',RCUBE_INPUT_POST);
			$B['prefs']['carddavtoken_davreadonly']=get_input_value('_carddavtoken_davreadonly',RCUBE_INPUT_POST);
			if(isset($_POST['_carddavtoken_davreadonly_submit_x']))
			{
				$B['prefs']['carddavtoken_davreadonly']=false;
				carddav_plus::SabreDAVAuth('delete','users_abook_r');
			}
			if(isset($_POST['_carddavtoken_submit_x']))
			{
				$B['prefs']['carddavtoken']=false;
				carddav_plus::SabreDAVAuth('delete','users_abook_rw');
			}
			if($_POST['_carddavtoken_davreadonly_submit'])
			{
				$B['prefs']['carddavtoken_davreadonly']=carddav_plus::SabreDAVAuth('create','users_abook_r');
			}
			if($_POST['_carddavtoken_submit'])
			{
				$B['prefs']['carddavtoken']=carddav_plus::SabreDAVAuth('create','users_abook_rw');
			}
			$aB=$A->config->all();
			foreach($aB as$F=>$h)
			{
				if(substr($F,0,strlen('carddav_shares_'))=='carddav_shares_')
				{
					$B['prefs'][$F]=0;
				}
			}
			foreach($_POST as$F=>$h)
			{
				if(substr($F,0,strlen('_carddav_shares_'))=='_carddav_shares_')
				{
					$B['prefs'][substr($F,1)]=get_input_value($F,RCUBE_INPUT_POST);
				}
			}
			foreach($aB as$F=>$h)
			{
				if(substr($F,0,strlen('carddav_shares_readonly_'))=='carddav_shares_readonly_')
				{
					$B['prefs'][$F]=0;
				}
			}
			foreach($_POST as$F=>$h)
			{
				if(substr($F,0,strlen('_carddav_shares_readonly_'))=='_carddav_shares_readonly_')
				{
					$B['prefs'][substr($F,1)]=get_input_value($F,RCUBE_INPUT_POST);
				}
			}
		}
		return$B;
	}
	
	function startup()
	{
		$A=rcmail::get_instance();
		if($A->action=='plugin.carddav_get_shares')
		{
			$cB=get_input_value('access',RCUBE_INPUT_GET);
			$d='';
			if($cB==2)
			{
				$d='readonly_';
			}
			$o=get_input_value('rcuser',RCUBE_INPUT_GET);
			$Z='SELECT * from '.get_table_name('users').' WHERE username=? AND mail_host=? LIMIT 1';
			$j=$A->db->query($Z,$o,rcube_parse_host($A->config->get('default_host','localhost')));
			$i=$A->db->fetch_assoc($j);
			$SC=$i;
			$i=unserialize($i['preferences']);
			$N=array();
			if(is_array($i))
			{
				foreach($i as$F=>$h)
				{
					if($cB==1)
					{
						$F=str_replace('_readonly_','_',$F);
					}
					if(substr($F,0,strlen('carddav_shares_'.$d))=='carddav_shares_'.$d)
					{
						if($h)
						{
							$N[$F]=$h;
						}
					}
				}
			}
			echo serialize($N);
			if(!$_SESSION['user_id'])
			{
				$A->session->destroy(session_id());
			}
			exit;
		}
	}
	
	function shareinvitation()
	{
		$A=rcmail::get_instance();
		$_SESSION['carddav_share_invitation']['share']=(array)get_input_value('_share',RCUBE_INPUT_POST);
		$_SESSION['carddav_share_invitation']['share_readonly']=(array)get_input_value('_share_readonly',RCUBE_INPUT_POST);
		$A->output->command('plugin.carddav_share_compose');
	}
	
	function message_compose($B)
	{
		if(get_input_value('_carddav_share_invitation',RCUBE_INPUT_GET))
		{
			$A=rcmail::get_instance();
			$Q='SELECT * FROM '.get_table_name('identities').' WHERE user_id=? AND del=? and standard=? LIMIT 1';
			$N=$A->db->query($Q,$A->user->ID,0,1);
			$T=$A->db->fetch_assoc($N);
			if($T['name'])
			{
				$PB=$T['name'].' ('.$T['email'].')';
			}
			else
			{
				$PB=$A->user->data['username'];
			}
			$B['param']['subject']=sprintf($this->gettext('carddav.invitationsubject'),$PB);
			$L=sprintf($this->gettext('carddav.invitationsubject'),$PB).".\r\n\r\n";
			$g=array(
				'carddav.label',
				'carddav.access',
				'carddav.url',
				'carddav.username',
				'password'
			);
			$k=0;
			foreach($g as$E)
			{
				$k=max($k,strlen($this->gettext($E)));
			}
			$IB='';
			for($I=0;$I<$k;$I++)
			{
				$IB.=' ';
			}
			$KB='';
			for($I=0;$I<73;$I++)
			{
				$KB.='_';
			}
			$I=0;
			foreach($_SESSION['carddav_share_invitation']['share']as$QB)
			{
				$Q='SELECT * FROM '.get_table_name('carddav_server').' WHERE carddav_server_id=? AND user_id=? LIMIT 1';
				$j=$A->db->query($Q,$QB,$A->user->ID);
				$S=$A->db->fetch_assoc($j);
				if(is_array($S))
				{
					$I++;
					$L.='#'.$I."-\r\n";
					foreach($g as$E)
					{
						if($E=='carddav.username')
						{
							$P=$S['username'];
						}
						else if($E=='password')
						{
							$P=$A->config->get('carddavtoken');
						}
						else if($E=='carddav.label')
						{
							$P=$S['label'];
						}
						else if($E=='carddav.url')
						{
							$P=$S['url'];
						}
						else if($E=='carddav.access')
						{
							$P=$this->gettext('carddav.readwrite');
						}
						$LB=substr($IB,0,$k-strlen($this->gettext($E)));
						$L.=$this->gettext($E).': '.$LB.$P."\r\n";
					}
					$L.=$KB."\r\n\r\n";
				}
			}
			foreach($_SESSION['carddav_share_invitation']['share_readonly']as$QB)
			{
				$Q='SELECT * FROM '.get_table_name('carddav_server').' WHERE carddav_server_id=? AND user_id=? LIMIT 1';
				$j=$A->db->query($Q,$QB,$A->user->ID);
				$S=$A->db->fetch_assoc($j);
				if(is_array($S))
				{
					$I++;
					$L.='#'.$I."-\r\n";
					foreach($g as$E)
					{
						if($E=='carddav.username')
						{
							$P=$S['username'];
						}
						else if($E=='password')
						{
							$P=$A->config->get('carddavtoken_davreadonly');
						}
						else if($E=='carddav.label')
						{
							$P=$S['label'];
						}
						else if($E=='carddav.url')
						{
							$P=$S['url'];
						}
						else if($E=='carddav.access')
						{
							$P=$this->gettext('carddav.readonly');
						}
						$LB=substr($IB,0,$k-strlen($this->gettext($E)));
						$L.=$this->gettext($E).': '.$LB.$P."\r\n";
					}
					$L.=$KB."\r\n\r\n";
				}
			}
			$bB='';
			for($I=0;$I<strlen($this->gettext('carddav.tutorials').':');$I++)
			{
				$bB.='=';
			}
			$L.=$this->gettext('carddav.tutorials').':'."\r\n";
			$L.=$bB."\r\n";
			$L.='#1- Thunderbird: '.$A->config->get('carddav_thunderbird','http://myroundcube.com/myroundcube-plugins/thunderbird-carddav')."\r\n";
			$L.='#2- Android:     '.$A->config->get('carddav_android','http://myroundcube.com/myroundcube-plugins/android-carddav')."\r\n";
			$L.='#3- iPhone:      '.$A->config->get('carddav_iphone','http://myroundcube.com/myroundcube-plugins/iphone-carddav')."\r\n";
			$L.="\r\n";
			$B['param']['body']=array('carddav_invitation'=>$L);
		}
		return$B;
	}
	
	function message_compose_body($B)
	{
		if(is_array($B['body'])&&isset($B['body']['carddav_invitation']))
		{
			$A=rcmail::get_instance();
			$A->output->set_env('top_posting',false);
			$B['body']=$B['body']['carddav_invitation'];
		}
		return$B;
	}
	
	static function SabreDAVAuth($_B,$YB)
	{
		$A=rcmail::get_instance();
		if($_SESSION['user_id'])
		{
			$AB='abcdefghijklmnopqrstuvwxyz';
			$AB.=strtoupper($AB).'0123456789';
			$TB='';
			for($I=0;$I<8;$I++)
			{
				$TB.=substr($AB,rand(0,strlen($AB)-1),1);
			}
			$UB='+-?!';
			$pB=substr($UB,rand(0,3),1);
			$rB=substr($UB,rand(0,3),1);
			$AC=rand(1,6);
			$yB=rand(1,6);
			$p=$TB;
			$sB=substr($p,$AC,1);
			$xB=substr($p,$yB,1);
			$p=str_replace($sB,$pB,$p);
			$VB=str_replace($xB,$rB,$p);
			$wB=$A->config->get('db_sabredav_dsn');
			$z=new rcube_db($wB,'',FALSE);
			$z->set_debug((bool)$A->config->get('sql_debug'));
			$z->db_connect('r');
			if($_B=='delete')
			{
				$Z='DELETE FROM '.$A->db->quoteIdentifier(get_table_name($YB)).' WHERE '.$A->db->quoteIdentifier('username').'=?';
				$z->query($Z,$A->user->data['username']);
			}
			else
			{
				$Z='INSERT INTO '.$A->db->quoteIdentifier(get_table_name($YB)).' ('.$A->db->quoteIdentifier('username').', '.$A->db->quoteIdentifier('digesta1').') VALUES (?, ?)';
				$tB=md5($A->user->data['username'].':'.$A->config->get('sabredav_realm').':'.$VB);
				$z->query($Z,$A->user->data['username'],$tB);
			}
		}
		return$VB;
	}
	
	static function isSabreDAV($C)
	{
		if(strpos($C,'access=')!==false)
		{
			return false;
		}
		$D=parse_url($C);
		if(!isset($D['scheme']))
		{
			return false;
		}
		$V=new MyRCHttp;
		$NB['method']='GET';
		$NB['referrer']='http'.(rcube_https_check()?'s':'').'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];$NB['target']=$C;
		$V->initialize($NB);
		$V->useCurl(true);
		if(ini_get('safe_mode')||ini_get('open_basedir'))
		{
			$V->useCurl(false);
		}
		$V->SetTimeout(5);
		$V->execute();
		$n=($V->error)?$V->error:$V->result;
		if(substr($n,0,strlen('SabreDAV'))=='SabreDAV')
		{
			$D=explode(':',$n);
			$K=explode('.',$_SERVER['HTTP_HOST']);
			if(count($K)>2)
			{
				unset($K[0]);
			}
			$K=implode('.',$K);
			if($D[1]&&stripos($D[1],$K)!==false)
			{
				return true;
			}
			else
			{
				if($_SERVER['SERVER_ADDR']==$D[1])
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
}