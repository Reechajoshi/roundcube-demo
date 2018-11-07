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

namespace Sabre\CardDAV\Backend;
use Sabre\CardDAV;
use Sabre\DAV;
class Shared extends AbstractBackend{
protected$pdo;
protected$addressBooksTableName;
protected$cardsTableName;
function __construct(\PDO$pdo,$addressBooksTableName='addressbooks',$cardsTableName='cards'){
$this->pdo=$pdo;
$this->addressBooksTableName=$addressBooksTableName;
$this->cardsTableName=$cardsTableName;
}
function getAddressBooksForUser($principalUri){
$stmt=$this->pdo->prepare('SELECT id, uri, displayname, principaluri, description, ctag FROM '.$this->addressBooksTableName.' WHERE principaluri = ?');
$stmt->execute(array($principalUri));
$addressBooks=array();
foreach($stmt->fetchAll()as$row){
$addressBooks[]=array(
'id'=>$row['id'],
'uri'=>$row['uri'],
'principaluri'=>$row['principaluri'],
'{DAV:}displayname'=>$row['displayname'],
'{'.CardDAV\Plugin::NS_CARDDAV.'}addressbook-description'=>$row['description'],
'{http://calendarserver.org/ns/}getctag'=>$row['ctag'],
'{'.CardDAV\Plugin::NS_CARDDAV.'}supported-address-data'=>
new CardDAV\Property\SupportedAddressData(),
);
}
global$shared_resources;
$shares=array();
foreach($shared_resources as$resource=>$enabled){
$temp=explode('_',$resource);
$resource=$temp[count($temp)-1];
$shares[$resource]=1;
}
foreach($addressBooks as$idx=>$props){
if(!isset($shares[$props['uri']])){
unset($addressBooks[$idx]);
}
}
return$addressBooks;
}
function updateAddressBook($addressBookId,array$mutations){
$updates=array();
foreach($mutations as$property=>$newValue){
switch($property){
case'{DAV:}displayname':
$updates['displayname']=$newValue;
break;
case'{'.CardDAV\Plugin::NS_CARDDAV.'}addressbook-description':
$updates['description']=$newValue;
break;
default:
return false;
}
}
if(!$updates){
return false;
}
$query='UPDATE '.$this->addressBooksTableName.' SET ctag = ctag + 1 ';
foreach($updates as$key=>$value){
$query.=', `'.$key.'` = :'.$key.' ';
}
$query.=' WHERE id = :addressbookid';
$stmt=$this->pdo->prepare($query);
$updates['addressbookid']=$addressBookId;
$stmt->execute($updates);
return true;
}
function createAddressBook($principalUri,$url,array$properties){
$values=array(
'displayname'=>null,
'description'=>null,
'principaluri'=>$principalUri,
'uri'=>$url,
);
foreach($properties as$property=>$newValue){
switch($property){
case'{DAV:}displayname':
$values['displayname']=$newValue;
break;
case'{'.CardDAV\Plugin::NS_CARDDAV.'}addressbook-description':
$values['description']=$newValue;
break;
default:
throw new DAV\Exception\BadRequest('Unknown property: '.$property);
}
}
$query='INSERT INTO '.$this->addressBooksTableName.' (uri, displayname, description, principaluri, ctag) VALUES (:uri, :displayname, :description, :principaluri, 1)';
$stmt=$this->pdo->prepare($query);
$stmt->execute($values);
}
function deleteAddressBook($addressBookId){
$stmt=$this->pdo->prepare('DELETE FROM '.$this->cardsTableName.' WHERE addressbookid = ?');
$stmt->execute(array($addressBookId));
$stmt=$this->pdo->prepare('DELETE FROM '.$this->addressBooksTableName.' WHERE id = ?');
$stmt->execute(array($addressBookId));
}
function getCards($addressbookId){
$stmt=$this->pdo->prepare('SELECT id, carddata, uri, lastmodified FROM '.$this->cardsTableName.' WHERE addressbookid = ?');
$stmt->execute(array($addressbookId));
return$stmt->fetchAll(\PDO::FETCH_ASSOC);
}
function getCard($addressBookId,$cardUri){
$stmt=$this->pdo->prepare('SELECT id, carddata, uri, lastmodified FROM '.$this->cardsTableName.' WHERE addressbookid = ? AND uri = ? LIMIT 1');
$stmt->execute(array($addressBookId,$cardUri));
$result=$stmt->fetchAll(\PDO::FETCH_ASSOC);
return(count($result)>0?$result[0]:false);
}
function createCard($addressBookId,$cardUri,$cardData){
$stmt=$this->pdo->prepare('INSERT INTO '.$this->cardsTableName.' (carddata, uri, lastmodified, addressbookid) VALUES (?, ?, ?, ?)');
$result=$stmt->execute(array($cardData,$cardUri,time(),$addressBookId));
$stmt2=$this->pdo->prepare('UPDATE '.$this->addressBooksTableName.' SET ctag = ctag + 1 WHERE id = ?');
$stmt2->execute(array($addressBookId));
return'"'.md5($cardData).'"';
}
function updateCard($addressBookId,$cardUri,$cardData){
$stmt=$this->pdo->prepare('UPDATE '.$this->cardsTableName.' SET carddata = ?, lastmodified = ? WHERE uri = ? AND addressbookid =?');
$stmt->execute(array($cardData,time(),$cardUri,$addressBookId));
$stmt2=$this->pdo->prepare('UPDATE '.$this->addressBooksTableName.' SET ctag = ctag + 1 WHERE id = ?');
$stmt2->execute(array($addressBookId));
return'"'.md5($cardData).'"';
}
function deleteCard($addressBookId,$cardUri){
$stmt=$this->pdo->prepare('DELETE FROM '.$this->cardsTableName.' WHERE addressbookid = ? AND uri = ?');
$stmt->execute(array($addressBookId,$cardUri));
$stmt2=$this->pdo->prepare('UPDATE '.$this->addressBooksTableName.' SET ctag = ctag + 1 WHERE id = ?');
$stmt2->execute(array($addressBookId));
return$stmt->rowCount()===1;
}
}