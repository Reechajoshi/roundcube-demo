<?php
# 
# This file is part of Roundcube "sabredav" plugin.
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
class sabredav extends rcube_plugin
{
	var$task='login|settings';
	var$noajax=true;
	static private$plugin='sabredav';
	static private$author='myroundcube@mail4us.net';
	static private$authors_comments='<a href=" http://myroundcube.com/myroundcube-plugins/sabredav-plugin" target="_new">Documentation</a><br /><font color="red">Version 5.0 requires <a href="http://mirror.myroundcube.com/docs/sabredav.html" target="_new">database adjustments</a> of SabreDAV database!</font><br /><font color="red">Version 5.1 has a <a href="http://mirror.myroundcube.com/docs/sabredav.html" target="_new">new sharing</a> concept and requires re-configuration.</font><br /><font color="red">Version 5.1.8: Copy content of "copy_and_paste" folder (except .htaccess) over your SabreDAV installation.</font>';
	static private$download='http://myroundcube.googlecode.com';
	static private$version='5.1.8';
	static private$date='06-11-2013';
	static private$licence='All Rights reserved';
	static private$requirements=array(
		'Roundcube'=>'0.9',
		'PHP'=>'5.2.1 + cURL',
	);
	static private$incompat=null;
	static private$sql=null;
	static private$prefs=null;
	static private$config_dist='config.inc.php.dist';
	private$credentials;
	function init()
	{
		$B=rcmail::get_instance();
		$this->require_plugin('global_alias');
		if($B->task=='settings')
		{
			$this->add_texts('localization/');
		}
		if(!in_array('global_config',$B->config->get('plugins')))
		{
			$this->load_config();
		}
		$this->add_hook('authenticate',array($this,'credentials'));
		$this->add_hook('login_after',array($this,'setup'));
		$this->add_hook('password_change',array($this,'password'));
	}
	static function about($Y=false)
	{
		$M=self::$requirements;
		foreach(array('required_','recommended_')as$P)
		{
			if(is_array($M[$P.'plugins']))
			{
				foreach($M[$P.'plugins']as$I=>$h)
				{
					if(class_exists($I)&&method_exists($I,'about'))
					{
						$j=new$I(false);
						$M[$P.'plugins'][$I]=array(
							'method'=>$h,
							'plugin'=>$j->about($Y),
						);
					}
					else
					{
						$M[$P.'plugins'][$I]=array(
							'method'=>$h,
							'plugin'=>$I,
						);
					}
				}
			}
		}
		$d=array();
		if(is_string(self::$config_dist))
		{
			if(is_file($k=INSTALL_PATH.'plugins/'.self::$plugin.'/'.self::$config_dist))
				include$k;
			else 
				write_log('errors',self::$plugin.': '.self::$config_dist.' is missing!');
		}
		$Q=array(
			'plugin'=>self::$plugin,
			'version'=>self::$version,
			'date'=>self::$date,
			'author'=>self::$author,
			'comments'=>self::$authors_comments,
			'licence'=>self::$licence,
			'download'=>self::$download,
			'requirements'=>$M,
		);
		if(is_array(self::$prefs))
			$Q['config']=array_merge($d,array_flip(self::$prefs));
		else
			$Q['config']=$d;
		if(is_array($Y))
		{
			$g=array('plugin'=>self::$plugin);
			foreach($Y as$f)
			{
				$g[$f]=$Q[$f];
			}
			return$g;
		}
		else
		{
			return$Q;
		}
	}
	function credentials($E)
	{
		if(strtolower($this->get_demo($E['user']))==strtolower(sprintf(rcmail::get_instance()->config->get('demo_user_account'),"")))
		{
			$E['pass']=rcmail::get_instance()->config->get('demo_user_password');
		}
		if(strpos($E['user'],'@')===false)
		{
			$F=false;
			if($K=rcmail::get_instance()->config->get('username_domain'))
			{
				$F=$_SERVER['HTTP_HOST'];
				$e=parse_url($F);
				if($e['host'])
				{
					$F=$e['host'];
				}
			}
			if($F)
			{
				if(is_array($K&&isset($K[$F])))
				{
					$E['user'].='@'.rcube_parse_host($K[$F],$F);
				}
				else if(is_string($K))
				{
					$E['user'].='@'.rcube_parse_host($K,$F);
				}
			}
		}
		$this->credentials=$E;
	}
	function setup($E)
	{
		$B=rcmail::get_instance();
		$l=$B->config->get('impersonate_seperator','*');
		if(strpos($this->credentials['user'],$l)!==false)
		{
			return$E;
		}
		$this->changepw(strtolower(urldecode($_SESSION['username'])),$B->decrypt($_SESSION['password']));
		$N=$B->config->get('db_sabredav_dsn');
		$A=new rcube_db($N,'',FALSE);
		$A->set_debug((bool)$B->config->get('sql_debug'));
		$A->db_connect('r');
		$C=$A->query("SELECT * FROM ".$this->table('users')." WHERE ".$this->q('username')."=?",$this->credentials['user']);
		$O=array();
		while($C&&($D=$A->fetch_assoc($C)))
		{
			$O[]=$D;
		}
		if(count($O)>0)
		{
			$A->query("UPDATE ".$this->table('users')." 
			SET ".$this->q('digesta1')."=?, ".$this->q('rcube_id')."=?
			WHERE ".$this->q('username')."=?",md5($this->credentials['user'].':'.$B->config->get('sabredav_realm').':'.$this->credentials['pass']),$B->user->ID,$this->credentials['user']);
			$W=array_merge(array($B->config->get('sabredav_default_cal','events')),$B->config->get('sabredav_cals',array()));
			foreach($W as$V=>$H)
			{
				$C=$A->query("SELECT * FROM ".$this->table('calendars')." WHERE ".$this->q('principaluri')."=? AND ".$this->q('uri')."=?","principals/".$this->credentials['user'],$H);
				$L=array();
				while($C&&($D=$A->fetch_assoc($C)))
				{
					$L[]=$D;
				}
				if(count($L)<1)
				{
					$A->query("INSERT INTO ".$this->table('calendars')."
					(".$this->q('principaluri').", ".$this->q('uri').", ".$this->q('displayname').", ".$this->q('ctag').", ".$this->q('calendarorder').", ".$this->q('components').")
					VALUES (?, ?, ?, ?, ?, ?)","principals/".$this->credentials['user'],$H,ucwords($H),0,0,'VEVENT,VTODO');
				}
			}
			$T=$B->config->get('sabredav_cards',array());
			foreach($T as$V=>$G)
			{
				$C=$A->query("SELECT * FROM ".$this->table('addressbooks')." WHERE ".$this->q('principaluri')."=? AND ".$this->q('uri')."=?","principals/".$this->credentials['user'],$G);
				$J=array();
				while($C&&($D=$A->fetch_assoc($C)))
				{
					$J[]=$D;
				}
				if(count($J)<1)
				{
					$A->query("INSERT INTO ".$this->table('addressbooks')."
					(".$this->q('principaluri').", ".$this->q('uri').", ".$this->q('displayname').", ".$this->q('ctag').")
					VALUES (?, ?, ?, ?)","principals/".$this->credentials['user'],$G,ucwords($G),0);
				}
			}
		}
		else
		{
			$A->query("INSERT INTO ".$this->table('users')."
			(".$this->q('username').", ".$this->q('digesta1').", ".$this->q('rcube_id').")
			VALUES (?, ?, ?)",$this->credentials['user'],md5($this->credentials['user'].':'.$B->config->get('sabredav_realm').':'.$this->credentials['pass']),$B->user->ID);
			$C=$A->query("SELECT * FROM ".$this->table('principals')." WHERE ".$this->q('uri')."=?","principals/".$this->credentials['user']);
			$m=array();
			while($C&&($D=$A->fetch_assoc($C)))
			{
				$m[]=$D;
			}
			if(count($O)<1)
			{
				$A->query("INSERT INTO ".$this->table('principals')."
				(".$this->q('uri').", ".$this->q('email').", ".$this->q('displayname').")
				VALUES (?, ?, ?)","principals/".$this->credentials['user'],$this->credentials['user'],$this->credentials['user']);
			}
			$C=$A->query("SELECT * FROM ".$this->table('calendars')." WHERE ".$this->q('principaluri')."=? AND ".$this->q('uri')."=?","principals/".$this->credentials['user'],$B->config->get('sabredav_default_cal','events'));
			$L=array();
			while($C&&($D=$A->fetch_assoc($C)))
			{
				$L[]=$D;
			}
			if(count($L)<1)
			{
				$W=array_merge(array($B->config->get('sabredav_default_cal','events')),$B->config->get('sabredav_cals',array()));
				foreach($W as$V=>$H)
				{
					$A->query("INSERT INTO ".$this->table('calendars')."
					(".$this->q('principaluri').", ".$this->q('uri').", ".$this->q('displayname').", ".$this->q('ctag').", ".$this->q('calendarorder').", ".$this->q('components').")
					VALUES (?, ?, ?, ?, ?, ?)","principals/".$this->credentials['user'],$H,ucwords($H),0,0,'VEVENT,VTODO');
				}
			}
			$T=$B->config->get('sabredav_cards',array());
			foreach($T as$V=>$G)
			{
				$C=$A->query("SELECT * FROM ".$this->table('addressbooks')." WHERE ".$this->q('principaluri')."=? AND ".$this->q('uri')."=?","principals/".$this->credentials['user'],$G);
				$J=array();
				while($C&&($D=$A->fetch_assoc($C)))
				{
					$J[]=$D;
				}
				if(count($J)<1)
				{
					$A->query("INSERT INTO ".$this->table('addressbooks')."
					(".$this->q('principaluri').", ".$this->q('uri').", ".$this->q('displayname').", ".$this->q('ctag').")
					VALUES (?, ?, ?, ?)","principals/".$this->credentials['user'],$G,ucwords($G),0);
				}
			}
		}
	}
	function create($U,$b,$Z=false)
	{
		$B=rcmail::get_instance();
		$N=$B->config->get('db_sabredav_dsn');
		$A=new rcube_db($N,'',FALSE);
		$A->set_debug((bool)$B->config->get('sql_debug'));
		$A->db_connect('r');
		$C=$A->query("SELECT * FROM ".sabredav::table('users')." WHERE ".sabredav::q('username')."=?",$B->user->data['username']);
		$O=array();
		while($C&&($D=$A->fetch_assoc($C)))
		{
			$X=$D;
			break;
		}
		if(is_array($X))
		{
			$C=$A->query("SELECT * FROM ".sabredav::table($b)." WHERE ".sabredav::q('principaluri')."=? AND ".sabredav::q('uri')."=?","principals/".$X['username'],$U);
			$a=array();
			while($C&&($D=$A->fetch_assoc($C)))
			{
				$a[]=$D;
			}
			if(count($a)<1)
			{
				if(!$Z)
				{
					$Z=ucwords($U);
				}
				$A->query("INSERT INTO ".sabredav::table($b)."
				(".sabredav::q('principaluri').", ".sabredav::q('uri').", ".sabredav::q('displayname').", ".sabredav::q('ctag').")
				VALUES (?, ?, ?, ?)","principals/".$X['username'],$U,$Z,0);
			}
		}
	}
	function get_demo($n)
	{
		$S=explode("@",$n);
		return preg_replace('/[0-9 ]/i','',$S[0])."@".$S[count($S)-1];
	}
	function password($E)
	{
		sabredav::changepw(strtolower(urldecode($_SESSION['username'])),$E['new_pass']);
	}
	static function changepw($c='',$i='')
	{
		$B=rcmail::get_instance();
		$N=$B->config->get('db_sabredav_dsn');
		$A=new rcube_db($N,'',FALSE);
		$A->set_debug((bool)$B->config->get('sql_debug'));
		$A->db_connect('r');
		$A->query("UPDATE ".sabredav::table('users')." 
		SET ".sabredav::q('digesta1')."=?
		WHERE ".sabredav::q('username')."=?",md5($c.':'.$B->config->get('sabredav_realm').':'.$i),$c);
	}
	static function q($R)
	{
		return rcmail::get_instance()->db->quoteIdentifier($R);
	}
	static function table($R)
	{
		return rcmail::get_instance()->db->quoteIdentifier(get_table_name($R));
	}
}