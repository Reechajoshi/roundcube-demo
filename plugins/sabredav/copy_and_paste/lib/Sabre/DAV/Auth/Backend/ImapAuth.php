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
namespace Sabre\DAV\Auth\Backend;
class ImapAuth extends\Sabre\DAV\Auth\Backend\AbstractBasic
{
private$imap;
private$pdo;
private$autoban;
private$attempts;
private$table;
function __construct($pdo,$imap,$autoban,$attempts,$table)
{
$this->imap=$imap;
$this->pdo=$pdo;
$this->autoban=$autoban;
$this->attempts=$attempts;
$this->table=$table;
}
protected function validateUserPass($username,$password)
{
$stmt=$this->pdo->prepare("DELETE FROM ".$this->table." WHERE ts < ?");
$stmt->execute(array(date('Y-m-d H:i:s',time()-(($this->autoban+1)*60))));
$stmt=$this->pdo->prepare("SELECT * FROM ".$this->table." WHERE username=? AND ip=?");
$stmt->execute(array($username,$_SERVER['REMOTE_ADDR']));
$count=0;
while($row=$stmt->fetch(\PDO::FETCH_ASSOC))
{
$count++;
}
if($count>=$this->attempts-1)
{
return false;
}
if(!$this->authenticateUser($username,$password))
{
$stmt=$this->pdo->prepare("INSERT INTO ".$this->table." (ip, username, ts) VALUES (?, ?, ?)");
$stmt->execute(array($_SERVER['REMOTE_ADDR'],$username,date('Y-m-d H:i:s',time())));
throw new\Sabre\DAV\Exception\NotAuthenticated('Username or password does not match');
}
return true;
}
private function authenticateUser($username,$password)
{
try
{
if($imap=@imap_open($this->imap,$username,$password,OP_HALFOPEN,0))
{
imap_close($imap);
return true;
}
else
{
return false;
}
}
catch(\Exception$e)
{
return false;
}
}
}