<?php
# 
# This file is part of Roundcube "plugin_manager" plugin.
# 
# Your are not allowed to distribute this file or parts of it.
# 
# This file is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
# 
# Copyright (c) 2012 - 2014 Roland 'Rosali' Liebl - all rights reserved.
# dev-team [at] myroundcube [dot] net
# http://myroundcube.com
# 
class Http{var$target;var$host;var$port;var$path;var$schema;var$method;var$params;var$cookies;var$_cookies;var$timeout;var$useCurl;var$referrer;var$userAgent;var$cookiePath;var$useCookie;var$saveCookie;var$username;var$password;var$result;var$headers;var$status;var$redirect;var$maxRedirect;var$curRedirect;var$error;var$nextToken;var$debug;var$debugMsg;function Http(){$this->clear();}function initialize($_=array()){$this->clear();foreach($_ as$K=>$p){if(isset($this->$K)){$I='set'.ucfirst(str_replace('_','',$K));if(method_exists($this,$I)){$this->$I($p);}else{$this->$K=$p;}}}}function clear(){$this->host='';$this->port=0;$this->path='';$this->target='';$this->method='GET';$this->schema='http';$this->params=array();$this->headers=array();$this->cookies=array();$this->_cookies=array();$this->debug=FALSE;$this->error='';$this->status=0;$this->timeout='25';$this->useCurl=TRUE;$this->referrer='';$this->username='';$this->password='';$this->redirect=TRUE;$this->nextToken='';$this->useCookie=TRUE;$this->saveCookie=TRUE;$this->maxRedirect=3;$this->cookiePath='cookie.txt';$this->userAgent='Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.9';}function setTarget($r){if($r){$this->target=$r;}}function setMethod($I){if($I=='GET'||$I=='POST'){$this->method=$I;}}function setReferrer($S){if($S){$this->referrer=$S;}}function setUseragent($h){if($h){$this->userAgent=$h;}}function setTimeout($i){if($i>0){$this->timeout=$i;}}function setCookiepath($H){if($H){$this->cookiePath=$H;}}function setParams($l){if(is_array($l)){$this->params=array_merge($this->params,$l);}}function setAuth($u,$q){if(!empty($u)&&!empty($q)){$this->username=$u;$this->password=$q;}}function setMaxredirect($A){if(!empty($A)){$this->maxRedirect=$A;}}function addParam($C,$A){if(!empty($C)&&!empty($A)){$this->params[$C]=$A;}}function addCookie($C,$A){if(!empty($C)&&!empty($A)){$this->cookies[$C]=$A;}}function useCurl($A=TRUE){if(is_bool($A)){$this->useCurl=$A;}}function useCookie($A=TRUE){if(is_bool($A)){$this->useCookie=$A;}}function saveCookie($A=TRUE){if(is_bool($A)){$this->saveCookie=$A;}}function followRedirects($A=TRUE){if(is_bool($A)){$this->redirect=$A;}}function getResult(){return$this->result;}function getHeaders(){return$this->headers;}function getStatus(){return$this->status;}function getError(){return$this->error;}function execute($o='',$S='',$I='',$d=array()){$this->target=($o)?$o:$this->target;$this->method=($I)?$I:$this->method;$this->referrer=($S)?$S:$this->referrer;if(is_array($d)&&count($d)>0){$this->params=array_merge($this->params,$d);}if(is_array($this->params)&&count($this->params)>0){$T=array();foreach($this->params as$K=>$A){if(strlen(trim($A))>0){$T[]=$K."=".urlencode($A);}}$L=join('&',$T);}$this->useCurl=$this->useCurl&&in_array('curl',get_loaded_extensions());if($this->method=='GET'){if(isset($L)){$this->target=$this->target."?".$L;}}$D=parse_url($this->target);if($D['scheme']=='https'){$this->host='ssl://'.$D['host'];$this->port=($this->port!=0)?$this->port:443;}else{$this->host=$D['host'];$this->port=($this->port!=0)?$this->port:80;}$this->path=(isset($D['path'])?$D['path']:'/').(isset($D['query'])?'?'.$D['query']:'');$this->schema=$D['scheme'];$this->_passCookies();if(is_array($this->cookies)&&count($this->cookies)>0){$T=array();foreach($this->cookies as$K=>$A){if(strlen(trim($A))>0){$T[]=$K."=".urlencode($A);}}$W=join('&',$T);}if($this->useCurl){$B=curl_init();if($this->method=='GET'){curl_setopt($B,CURLOPT_HTTPGET,TRUE);curl_setopt($B,CURLOPT_POST,FALSE);}else{if(isset($L)){curl_setopt($B,CURLOPT_POSTFIELDS,$L);}curl_setopt($B,CURLOPT_POST,TRUE);curl_setopt($B,CURLOPT_HTTPGET,FALSE);}if($this->username&&$this->password){curl_setopt($B,CURLOPT_USERPWD,$this->username.':'.$this->password);}if($this->useCookie&&isset($W)){curl_setopt($B,CURLOPT_COOKIE,$W);}curl_setopt($B,CURLOPT_HEADER,TRUE);curl_setopt($B,CURLOPT_NOBODY,FALSE);curl_setopt($B,CURLOPT_COOKIEJAR,$this->cookiePath);curl_setopt($B,CURLOPT_TIMEOUT,$this->timeout);curl_setopt($B,CURLOPT_USERAGENT,$this->userAgent);curl_setopt($B,CURLOPT_URL,$this->target);curl_setopt($B,CURLOPT_REFERER,$this->referrer);curl_setopt($B,CURLOPT_VERBOSE,FALSE);curl_setopt($B,CURLOPT_SSL_VERIFYPEER,FALSE);curl_setopt($B,CURLOPT_FOLLOWLOCATION,$this->redirect);curl_setopt($B,CURLOPT_MAXREDIRS,$this->maxRedirect);curl_setopt($B,CURLOPT_RETURNTRANSFER,TRUE);$y=curl_exec($B);$V=explode("\r\n\r\n",$y);$DB=curl_getinfo($B);$this->result=$V[count($V)-1];$this->_parseHeaders($V[count($V)-2]);$this->_setError(curl_error($B));curl_close($B);}else{$J=fsockopen($this->host,$this->port,$x,$w,$this->timeout);if(!$J){$this->_setError('Failed opening http socket connection: '.$w.' ('.$x.')');return FALSE;}$G=$this->method." ".$this->path."  HTTP/1.1\r\n";$G.="Host: ".$D['host']."\r\n";$G.="User-Agent: ".$this->userAgent."\r\n";$G.="Content-Type: application/x-www-form-urlencoded\r\n";if($this->useCookie&&$W!=''){$G.="Cookie: ".$W."\r\n";}if($this->method=="POST"){$G.="Content-Length: ".strlen($L)."\r\n";}if($this->referrer!=''){$G.="Referer: ".$this->referrer."\r\n";}if($this->username&&$this->password){$G.="Authorization: Basic ".base64_encode($this->username.':'.$this->password)."\r\n";}$G.="Connection: close\r\n\r\n";if($this->method=="POST"){$G.=$L;}fwrite($J,$G);$R='';$X='';do{$R.=fread($J,1);}while(!preg_match('/\\r\\n\\r\\n$/',$R));$this->_parseHeaders($R);if(($this->status=='301'||$this->status=='302')&&$this->redirect==TRUE){if($this->curRedirect<$this->maxRedirect){$CB=parse_url($this->headers['location']);if($CB['host']){$t=$this->headers['location'];}else{$t=$this->schema.'://'.$this->host.'/'.$this->headers['location'];}$this->port=0;$this->status=0;$this->params=array();$this->method='GET';$this->referrer=$this->target;$this->curRedirect++;$this->result=$this->execute($t);}else{$this->_setError('Too many redirects.');return FALSE;}}else{if($this->headers['transfer-encoding']!='chunked'){while(!feof($J)){$X.=fgets($J,128);}}else{while($m=hexdec(fgets($J))){$Z='';$Y=0;while($Y<$m){$Z.=fread($J,$m-$Y);$Y=strlen($Z);}$X.=$Z;fgets($J);}}$this->result=chop($X);}}return$this->result;}function _parseHeaders($R){$b=explode("\r\n",$R);$this->_clearHeaders();if($this->status==0){if(!preg_match("/^http\/[0-9]+\\.[0-9]+[ \t]+([0-9]+)[ \t]*(.*)\$/i",$b[0],$BB)){$this->_setError('Unexpected HTTP response status');return FALSE;}$this->status=$BB[1];array_shift($b);}foreach($b as$v){$O=strtolower($this->_tokenize($v,':'));$j=trim(chop($this->_tokenize("\r\n")));if(isset($this->headers[$O])){if(gettype($this->headers[$O])=="string"){$this->headers[$O]=array($this->headers[$O]);}$this->headers[$O][]=$j;}else{$this->headers[$O]=$j;}}if($this->saveCookie&&isset($this->headers['set-cookie'])){$this->_parseCookie();}}function _clearHeaders(){$this->headers=array();}function _parseCookie(){if(gettype($this->headers['set-cookie'])=="array"){$c=$this->headers['set-cookie'];}else{$c=array($this->headers['set-cookie']);}for($F=0;$F<count($c);$F++){$AB=trim($this->_tokenize($c[$F],"="));$z=$this->_tokenize(";");$D=parse_url($this->target);$E=$D['host'];$N='0';$H="/";$f="";while(($C=trim(urldecode($this->_tokenize("="))))!=""){$A=urldecode($this->_tokenize(";"));switch($C){case"path":$H=$A;break;case"domain":$E=$A;break;case"secure":$N=($A!='')?'1':'0';break;}}$this->_setCookie($AB,$z,$f,$H,$E,$N);}}function _setCookie($C,$A,$f="",$H="/",$E="",$N=0){if(strlen($C)==0){return($this->_setError("No valid cookie name was specified."));}if(strlen($H)==0||strcmp($H[0],"/")){return($this->_setError("$H is not a valid path for setting cookie $C."));}if($E==""||!strpos($E,".",$E[0]=="."?1:0)){return($this->_setError("$E is not a valid domain for setting cookie $C."));}$E=strtolower($E);if(!strcmp($E[0],".")){$E=substr($E,1);}$C=$this->_encodeCookie($C,true);$A=$this->_encodeCookie($A,false);$N=intval($N);$this->_cookies[]=array("name"=>$C,"value"=>$A,"domain"=>$E,"path"=>$H,"expires"=>$f,"secure"=>$N);}function _encodeCookie($A,$C){return($C?str_replace("=","%25",$A):str_replace(";","%3B",$A));}function _passCookies(){if(is_array($this->_cookies)&&count($this->_cookies)>0){$D=parse_url($this->target);$s=array();foreach($this->_cookies as$F){if($this->_domainMatch($D['host'],$F['domain'])&&(0===strpos($D['path'],$F['path']))&&(empty($F['secure'])||$D['protocol']=='https')){$s[$F['name']][strlen($F['path'])]=$F['value'];}}foreach($s as$C=>$g){krsort($g);foreach($g as$A){$this->addCookie($C,$A);}}}}function _domainMatch($k,$P){if('.'!=$P{0}){return$k==$P;}elseif(substr_count($P,'.')<2){return false;}else{return substr('.'.$k,-strlen($P))==$P;}}function _tokenize($M,$U=''){if(!strcmp($U,'')){$U=$M;$M=$this->nextToken;}for($e=0;$e<strlen($U);$e++){if(gettype($n=strpos($M,$U[$e]))=="integer"){$Q=(isset($Q)?min($Q,$n):$n);}}if(isset($Q)){$this->nextToken=substr($M,$Q+1);return(substr($M,0,$Q));}else{$this->nextToken='';return($M);}}function _setError($a){if($a!=''){$this->error=$a;return$a;}}}