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

date_default_timezone_set('UTC');
include'config.inc.php';
$request=explode('/',$_SERVER['REQUEST_URI']);
if($request[1])
$request=$request[1];
else
$request='/';
$pdo=new PDO($dbtype.':host='.$dbhost.';dbname='.$dbname,$dbuser,$dbpass);
$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
function exception_error_handler($errno,$errstr,$errfile,$errline){
	throw new ErrorException($errstr,0,$errno,$errfile,$errline);
}
set_error_handler("exception_error_handler");
require_once'vendor/autoload.php';
include'index.inc.php';
if($users_table=='users'){
if($authtype=='imap'){
$authBackend=new\Sabre\DAV\Auth\Backend\ImapAuth($pdo,$imap_open,$autoban_interval,$autoban_attempts,$autoban_db_table);
}
else{
$authBackend=new\Sabre\DAV\Auth\Backend\PDO($pdo,$users_table);
}
}
else{
$authBackend=new\Sabre\DAV\Auth\Backend\PDO($pdo,$users_table);
}
$principalBackend=new\Sabre\DAVACL\PrincipalBackend\PDO($pdo);
if($users_table=='users'){
$carddavBackend=new\Sabre\CardDAV\Backend\PDO($pdo);
}
else{
$carddavBackend=new\Sabre\CardDAV\Backend\Shared($pdo);
}
if($users_table=='users'){
$caldavBackend=new\Sabre\CalDAV\Backend\PDO($pdo);
}
else{
$caldavBackend=new\Sabre\CalDAV\Backend\Shared($pdo);
}
$nodes=array(
new\Sabre\CalDAV\Principal\Collection($principalBackend),
new\Sabre\CalDAV\CalendarRootNode($principalBackend,$caldavBackend),
new\Sabre\CardDAV\AddressBookRoot($principalBackend,$carddavBackend),
);
$server=new\Sabre\DAV\Server($nodes);
$server->setBaseUri($baseUri);
$server->addPlugin(new\Sabre\DAV\Auth\Plugin($authBackend,$realm));
if(isset($user)&&strtolower($user)==strtolower($admin)){
$browser=new\Sabre\DAV\Browser\Plugin();
$server->addPlugin($browser);
}
$server->addPlugin(new\Sabre\CalDAV\Plugin());
$server->addPlugin(new\Sabre\CardDAV\Plugin());
$server->addPlugin(new\Sabre\DAVACL\Plugin());
$server->addPlugin(new\Sabre\CalDAV\ICSExportPlugin());
$server->exec();
if($cb){
getCallBack($cb.'&request='.base64_encode($home.$_SERVER['REQUEST_URI']).'&method='.$_SERVER['REQUEST_METHOD']);
}