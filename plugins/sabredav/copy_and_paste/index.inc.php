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
function getCallBack($url){
$ch=curl_init();
curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_HEADER,FALSE);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
$doc=curl_exec($ch);
curl_close($ch);
return$doc;
}
function getDigest(){
$digest=false;
if(isset($_SERVER['PHP_AUTH_DIGEST'])){
$digest=$_SERVER['PHP_AUTH_DIGEST'];
}
else if(isset($_SERVER['HTTP_AUTHENTICATION'])){
if(strpos(strtolower($_SERVER['HTTP_AUTHENTICATION']),'digest')===0)
$digest=substr($_SERVER['HTTP_AUTHORIZATION'],7);
}
return$digest;
}
function digestParse($digest){
$needed_parts=array('nonce'=>1,'nc'=>1,'cnonce'=>1,'qop'=>1,'username'=>1,'uri'=>1,'response'=>1);
$data=array();
preg_match_all('@(\w+)=(?:(?:")([^"]+)"|([^\s,$]+))@',$digest,$matches,PREG_SET_ORDER);
foreach($matches as$m){
$data[$m[1]]=$m[2]?$m[2]:$m[3];
unset($needed_parts[$m[1]]);
}
return$needed_parts?false:$data;
}
function digestVerify($realm,$A1){
$nonce=uniqid();
$digest=getDigest();
if(!$digest){
header('WWW-Authenticate: Digest realm="'.$realm.'",qop="auth",nonce="'.$nonce.'",opaque="'.md5($realm).'"');
header('HTTP/1.1 401 Unauthorized');
echo'AUTHORIZATION FAILED';
die();
}
$digestParts=digestParse($digest);
$A2=md5("{$_SERVER['REQUEST_METHOD']}:{$digestParts['uri']}");
$validResponse=md5("{$A1}:{$digestParts['nonce']}:{$digestParts['nc']}:{$digestParts['cnonce']}:{$digestParts['qop']}:{$A2}");
return$digestParts['response']===$validResponse;
}
$user=false;
$resource=false;
$users_table='users';
$cb='';
$uri=preg_replace('/\/+$/','',$_SERVER['REQUEST_URI']);
$temp=explode('/',urldecode($uri));
if(isset($temp[$resource_pos])){
$resource=$temp[$resource_pos];
}
$digest=getDigest();
$digestParts=digestParse($digest);
if(is_array($digestParts)&&isset($digestParts['username'])){
$user=strtolower($digestParts['username']);
$temp=explode('@',$user,2);
}
else{
if(isset($temp[$user_pos])){
$user=strtolower($temp[$user_pos]);
$temp=$user;
$temp=explode('@',$temp,2);
}
else{
unset($temp);
}
}
if(isset($temp[1])){
$user_domain=$temp[1];
}
else{
$user_domain='default';
}
if(isset($map)&&is_array($map)){
foreach($map as$key=>$val){
if($key==$user_domain){
$rcurl=$val;
break;
}
}
}
if(count($_GET)>0){
$query=explode('?',$uri,2);
if(strpos($uri,'&')!==false){
$query=explode('&',$query[1]);
}
$uri=strtolower($query[0]);
$query=explode('=',$query[count($query)-1]);
if($query[0]=='issabredav'){
header("HTTP/1.1 200 OK");
$temp=parse_url($rcurl);
$host=$temp['host'];
die('SabreDAV:'.$host);
}
}
$query=explode('.',strtolower($_SERVER['HTTP_HOST']));
if($query[0]==$readonly_subdomain||$query[0]==$readwrite_subdomain){
if($query[0]==$readwrite_subdomain){
$accesslevel=1;
}
else{
$accesslevel=2;
}
$path=false;
$die=true;
$path=explode('/',$uri);
if(isset($path[$dav_pos])){
$path=$path[$dav_pos];
}
else{
header("HTTP/1.1 403 Forbidden");
die('FORBIDDEN');
}
if(isset($user)){
if($accesslevel==1){
$query="SELECT * FROM users_cal_rw WHERE username=? LIMIT 1";
$stmt=$pdo->prepare($query);
$stmt->execute(array($user));
$row=$stmt->fetch(\PDO::FETCH_ASSOC);
if(is_array($row)){
$A1=$row['digesta1'];
if(digestVerify($realm,$A1)===true){
$path='calendars';
}
}
else{
$query="SELECT * FROM users_abook_rw WHERE username=? LIMIT 1";
$stmt=$pdo->prepare($query);
$stmt->execute(array($user));
$row=$stmt->fetch(\PDO::FETCH_ASSOC);
if(is_array($row)){
$A1=$row['digesta1'];
if(digestVerify($realm,$A1)===true){
$path='addressbooks';
}
}
}
}
else{
$query="SELECT * FROM users_cal_r WHERE username=? LIMIT 1";
$stmt=$pdo->prepare($query);
$stmt->execute(array($user));
$row=$stmt->fetch(\PDO::FETCH_ASSOC);
if(is_array($row)){
$A1=$row['digesta1'];
if(digestVerify($realm,$A1)===true){
$path='calendars';
}
}
else{
$query="SELECT * FROM users_abook_r WHERE username=? LIMIT 1";
$stmt=$pdo->prepare($query);
$stmt->execute(array($user));
$row=$stmt->fetch(\PDO::FETCH_ASSOC);
if(is_array($row)){
$A1=$row['digesta1'];
if(digestVerify($realm,$A1)===true){
$path='addressbooks';
}
}
}
}
$home='http'.((!empty($_SERVER['HTTPS'])&&strtolower($_SERVER['HTTPS'])!='off')?'s':'').'://'.$_SERVER['HTTP_HOST'];
switch($path){
case'calendars':
$prefix='cal';
$cb=$rcurl.'/?_action=plugin.calendar_get_shares&rcuser='.$user.'&access='.$accesslevel;
if($_SERVER['REQUEST_METHOD']=='DELETE'){
$cb.='&request='.base64_encode($home.$uri).'&method='.$_SERVER['REQUEST_METHOD'];
}
$shared_resources=getCallBack($cb);
if($_SERVER['REQUEST_METHOD']=='DELETE'){
$cb='';
}
break;
case'addressbooks':
$prefix='carddav';
$cb=$rcurl.'/?_action=plugin.carddav_get_shares&rcuser='.$user.'&access='.$accesslevel;
if($_SERVER['REQUEST_METHOD']=='DELETE'){
$cb.='&request='.base64_encode($home.$uri).'&method='.$_SERVER['REQUEST_METHOD'];
}
$shared_resources=getCallBack($cb);
$cb='';
break;
default:
$prefix='';
$shared_resources=serialize(array());
}
$shared_resources=unserialize($shared_resources);
if(is_array($shared_resources)){
foreach($shared_resources as$shared_resource=>$shared){
if($shared==1){
$append='';
if($accesslevel==2){
$append='readonly_';
}
if(str_replace($prefix.'_shares_'.$append,'',$shared_resource)==$resource){
$die=false;
break;
}
}
}
}
if(!$resource){
$die=false;
}
if($die===true){
header("HTTP/1.1 401 Unauthorized");
die('AUTHORIZATION FAILED');
}
else if($accesslevel==1){
switch($_SERVER['REQUEST_METHOD']){
case'DELETE':
if(isset($uri)){
$temp=explode('.',$uri);
if(isset($temp[count($temp)-1])){
if(strtolower($temp[count($temp)-1])=='ics'||strtolower($temp[count($temp)-1])=='vcf'){
break;
}
else{
header("HTTP/1.1 403 Forbidden");
exit;
}
}
else{
header("HTTP/1.1 403 Forbidden");
exit;
}
}
else{
header("HTTP/1.1 403 Forbidden");
exit;
}
case'MKCOL':
case'MOVE':
case'PROPPATCH':
header("HTTP/1.1 403 Forbidden");
exit;
}
}
else if($accesslevel==2){
switch($_SERVER['REQUEST_METHOD']){
case'MKCOL':
case'DELETE':
case'MOVE':
case'PUT':
case'PROPPATCH':
header("HTTP/1.1 403 Forbidden");
exit;
}
}
}
else{
header("HTTP/1.1 403 Forbidden");
die('FORBIDDEN');
}
switch($path){
case'calendars':
if($accesslevel==1){
$users_table='users_cal_rw';
}
else{
$users_table='users_cal_r';
}
break;
case'addressbooks':
if($accesslevel==1){
$users_table='users_abook_rw';
}
else{
$users_table='users_abook_r';
}
break;
}
}