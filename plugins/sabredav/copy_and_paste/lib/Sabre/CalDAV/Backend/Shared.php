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

namespace Sabre\CalDAV\Backend;
use Sabre\VObject;
use Sabre\CalDAV;
use Sabre\DAV;
class Shared extends AbstractBackend{
const MAX_DATE='2038-01-01';
protected$pdo;
protected$calendarTableName;
protected$calendarObjectTableName;
var$propertyMap=array(
'{DAV:}displayname'=>'displayname',
'{urn:ietf:params:xml:ns:caldav}calendar-description'=>'description',
'{urn:ietf:params:xml:ns:caldav}calendar-timezone'=>'timezone',
'{http://apple.com/ns/ical/}calendar-order'=>'calendarorder',
'{http://apple.com/ns/ical/}calendar-color'=>'calendarcolor',
);
function __construct(\PDO$pdo,$calendarTableName='calendars',$calendarObjectTableName='calendarobjects'){
$this->pdo=$pdo;
$this->calendarTableName=$calendarTableName;
$this->calendarObjectTableName=$calendarObjectTableName;
}
function getCalendarsForUser($principalUri){
$fields=array_values($this->propertyMap);
$fields[]='id';
$fields[]='uri';
$fields[]='ctag';
$fields[]='components';
$fields[]='principaluri';
$fields[]='transparent';
$fields=implode(', ',$fields);
$stmt=$this->pdo->prepare("SELECT ".$fields." FROM ".$this->calendarTableName." WHERE principaluri = ? ORDER BY calendarorder ASC");
$stmt->execute(array($principalUri));
$calendars=array();
while($row=$stmt->fetch(\PDO::FETCH_ASSOC)){
$components=array();
if($row['components']){
$components=explode(',',$row['components']);
}
$calendar=array(
'id'=>$row['id'],
'uri'=>$row['uri'],
'principaluri'=>$row['principaluri'],
'{'.CalDAV\Plugin::NS_CALENDARSERVER.'}getctag'=>$row['ctag']?$row['ctag']:'0',
'{'.CalDAV\Plugin::NS_CALDAV.'}supported-calendar-component-set'=>new CalDAV\Property\SupportedCalendarComponentSet($components),
'{'.CalDAV\Plugin::NS_CALDAV.'}schedule-calendar-transp'=>new CalDAV\Property\ScheduleCalendarTransp($row['transparent']?'transparent':'opaque'),
);
foreach($this->propertyMap as$xmlName=>$dbName){
$calendar[$xmlName]=$row[$dbName];
}
$calendars[]=$calendar;
}
global$shared_resources;
$shares=array();
foreach($shared_resources as$resource=>$enabled){
$temp=explode('_',$resource);
$resource=$temp[count($temp)-1];
$shares[$resource]=1;
}
foreach($calendars as$idx=>$props){
if(!isset($shares[$props['uri']])){
unset($calendars[$idx]);
}
}
return$calendars;
}
function createCalendar($principalUri,$calendarUri,array$properties){
$fieldNames=array(
'principaluri',
'uri',
'ctag',
'transparent',
);
$values=array(
':principaluri'=>$principalUri,
':uri'=>$calendarUri,
':ctag'=>1,
':transparent'=>0,
);
$sccs='{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set';
$fieldNames[]='components';
if(!isset($properties[$sccs])){
$values[':components']='VEVENT,VTODO';
}else{
if(!($properties[$sccs]instanceof CalDAV\Property\SupportedCalendarComponentSet)){
throw new DAV\Exception('The '.$sccs.' property must be of type: \Sabre\CalDAV\Property\SupportedCalendarComponentSet');
}
$values[':components']=implode(',',$properties[$sccs]->getValue());
}
$transp='{'.CalDAV\Plugin::NS_CALDAV.'}schedule-calendar-transp';
if(isset($properties[$transp])){
$values[':transparent']=$properties[$transp]->getValue()==='transparent';
}
foreach($this->propertyMap as$xmlName=>$dbName){
if(isset($properties[$xmlName])){
$values[':'.$dbName]=$properties[$xmlName];
$fieldNames[]=$dbName;
}
}
$stmt=$this->pdo->prepare("INSERT INTO ".$this->calendarTableName." (".implode(', ',$fieldNames).") VALUES (".implode(', ',array_keys($values)).")");
$stmt->execute($values);
return$this->pdo->lastInsertId();
}
function updateCalendar($calendarId,array$mutations){
$newValues=array();
$result=array(
200=>array(),403=>array(),424=>array(),);
$hasError=false;
foreach($mutations as$propertyName=>$propertyValue){
switch($propertyName){
case'{'.CalDAV\Plugin::NS_CALDAV.'}schedule-calendar-transp':
$fieldName='transparent';
$newValues[$fieldName]=$propertyValue->getValue()==='transparent';
break;
default:
if(!isset($this->propertyMap[$propertyName])){
$hasError=true;
$result[403][$propertyName]=null;
unset($mutations[$propertyName]);
continue;
}
$fieldName=$this->propertyMap[$propertyName];
$newValues[$fieldName]=$propertyValue;
}
}
if($hasError){
foreach($mutations as$propertyName=>$propertyValue){
$result[424][$propertyName]=null;
}
foreach($result as$status=>$properties){
if(is_array($properties)&&count($properties)===0)unset($result[$status]);
}
return$result;
}
$valuesSql=array();
foreach($newValues as$fieldName=>$value){
$valuesSql[]=$fieldName.' = ?';
}
$valuesSql[]='ctag = ctag + 1';
$stmt=$this->pdo->prepare("UPDATE ".$this->calendarTableName." SET ".implode(', ',$valuesSql)." WHERE id = ?");
$newValues['id']=$calendarId;
$stmt->execute(array_values($newValues));
return true;
}
function deleteCalendar($calendarId){
$stmt=$this->pdo->prepare('DELETE FROM '.$this->calendarObjectTableName.' WHERE calendarid = ?');
$stmt->execute(array($calendarId));
$stmt=$this->pdo->prepare('DELETE FROM '.$this->calendarTableName.' WHERE id = ?');
$stmt->execute(array($calendarId));
}
function getCalendarObjects($calendarId){
$stmt=$this->pdo->prepare('SELECT id, uri, lastmodified, etag, calendarid, size FROM '.$this->calendarObjectTableName.' WHERE calendarid = ?');
$stmt->execute(array($calendarId));
$result=array();
foreach($stmt->fetchAll(\PDO::FETCH_ASSOC)as$row){
$result[]=array(
'id'=>$row['id'],
'uri'=>$row['uri'],
'lastmodified'=>$row['lastmodified'],
'etag'=>'"'.$row['etag'].'"',
'calendarid'=>$row['calendarid'],
'size'=>(int)$row['size'],
);
}
return$result;
}
function getCalendarObject($calendarId,$objectUri){
$stmt=$this->pdo->prepare('SELECT id, uri, lastmodified, etag, calendarid, size, calendardata FROM '.$this->calendarObjectTableName.' WHERE calendarid = ? AND uri = ?');
$stmt->execute(array($calendarId,$objectUri));
$row=$stmt->fetch(\PDO::FETCH_ASSOC);
if(!$row)return null;
return array(
'id'=>$row['id'],
'uri'=>$row['uri'],
'lastmodified'=>$row['lastmodified'],
'etag'=>'"'.$row['etag'].'"',
'calendarid'=>$row['calendarid'],
'size'=>(int)$row['size'],
'calendardata'=>$row['calendardata'],
);
}
function createCalendarObject($calendarId,$objectUri,$calendarData){
$extraData=$this->getDenormalizedData($calendarData);
$stmt=$this->pdo->prepare('INSERT INTO '.$this->calendarObjectTableName.' (calendarid, uri, calendardata, lastmodified, etag, size, componenttype, firstoccurence, lastoccurence) VALUES (?,?,?,?,?,?,?,?,?)');
$stmt->execute(array(
$calendarId,
$objectUri,
$calendarData,
time(),
$extraData['etag'],
$extraData['size'],
$extraData['componentType'],
$extraData['firstOccurence'],
$extraData['lastOccurence'],
));
$stmt=$this->pdo->prepare('UPDATE '.$this->calendarTableName.' SET ctag = ctag + 1 WHERE id = ?');
$stmt->execute(array($calendarId));
return'"'.$extraData['etag'].'"';
}
function updateCalendarObject($calendarId,$objectUri,$calendarData){
$extraData=$this->getDenormalizedData($calendarData);
$stmt=$this->pdo->prepare('UPDATE '.$this->calendarObjectTableName.' SET calendardata = ?, lastmodified = ?, etag = ?, size = ?, componenttype = ?, firstoccurence = ?, lastoccurence = ? WHERE calendarid = ? AND uri = ?');
$stmt->execute(array($calendarData,time(),$extraData['etag'],$extraData['size'],$extraData['componentType'],$extraData['firstOccurence'],$extraData['lastOccurence'],$calendarId,$objectUri));
$stmt=$this->pdo->prepare('UPDATE '.$this->calendarTableName.' SET ctag = ctag + 1 WHERE id = ?');
$stmt->execute(array($calendarId));
return'"'.$extraData['etag'].'"';
}
protected function getDenormalizedData($calendarData){
$vObject=VObject\Reader::read($calendarData);
$componentType=null;
$component=null;
$firstOccurence=null;
$lastOccurence=null;
foreach($vObject->getComponents()as$component){
if($component->name!=='VTIMEZONE'){
$componentType=$component->name;
break;
}
}
if(!$componentType){
throw new\Sabre\DAV\Exception\BadRequest('Calendar objects must have a VJOURNAL, VEVENT or VTODO component');
}
if($componentType==='VEVENT'){
$firstOccurence=$component->DTSTART->getDateTime()->getTimeStamp();
if(!isset($component->RRULE)){
if(isset($component->DTEND)){
$lastOccurence=$component->DTEND->getDateTime()->getTimeStamp();
}elseif(isset($component->DURATION)){
$endDate=clone$component->DTSTART->getDateTime();
$endDate->add(VObject\DateTimeParser::parse($component->DURATION->getValue()));
$lastOccurence=$endDate->getTimeStamp();
}elseif(!$component->DTSTART->hasTime()){
$endDate=clone$component->DTSTART->getDateTime();
$endDate->modify('+1 day');
$lastOccurence=$endDate->getTimeStamp();
}else{
$lastOccurence=$firstOccurence;
}
}else{
$it=new VObject\RecurrenceIterator($vObject,(string)$component->UID);
$maxDate=new\DateTime(self::MAX_DATE);
if($it->isInfinite()){
$lastOccurence=$maxDate->getTimeStamp();
}else{
$end=$it->getDtEnd();
while($it->valid()&&$end<$maxDate){
$end=$it->getDtEnd();
$it->next();
}
$lastOccurence=$end->getTimeStamp();
}
}
}
return array(
'etag'=>md5($calendarData),
'size'=>strlen($calendarData),
'componentType'=>$componentType,
'firstOccurence'=>$firstOccurence,
'lastOccurence'=>$lastOccurence,
);
}
function deleteCalendarObject($calendarId,$objectUri){
$stmt=$this->pdo->prepare('DELETE FROM '.$this->calendarObjectTableName.' WHERE calendarid = ? AND uri = ?');
$stmt->execute(array($calendarId,$objectUri));
$stmt=$this->pdo->prepare('UPDATE '.$this->calendarTableName.' SET ctag = ctag + 1 WHERE id = ?');
$stmt->execute(array($calendarId));
}
function calendarQuery($calendarId,array$filters){
$result=array();
$validator=new\Sabre\CalDAV\CalendarQueryValidator();
$componentType=null;
$requirePostFilter=true;
$timeRange=null;
if(!$filters['prop-filters']&&!$filters['comp-filters']){
$requirePostFilter=false;
}
if(count($filters['comp-filters'])>0&&!$filters['comp-filters'][0]['is-not-defined']){
$componentType=$filters['comp-filters'][0]['name'];
if(!$filters['prop-filters']&&!$filters['comp-filters'][0]['comp-filters']&&!$filters['comp-filters'][0]['time-range']&&!$filters['comp-filters'][0]['prop-filters']){
$requirePostFilter=false;
}
if($componentType=='VEVENT'&&isset($filters['comp-filters'][0]['time-range'])){
$timeRange=$filters['comp-filters'][0]['time-range'];
if(!$filters['prop-filters']&&!$filters['comp-filters'][0]['comp-filters']&&!$filters['comp-filters'][0]['prop-filters']&&(!$timeRange['start']||!$timeRange['end'])){
$requirePostFilter=false;
}
}
}
if($requirePostFilter){
$query="SELECT uri, calendardata FROM ".$this->calendarObjectTableName." WHERE calendarid = :calendarid";
}else{
$query="SELECT uri FROM ".$this->calendarObjectTableName." WHERE calendarid = :calendarid";
}
$values=array(
'calendarid'=>$calendarId,
);
if($componentType){
$query.=" AND componenttype = :componenttype";
$values['componenttype']=$componentType;
}
if($timeRange&&$timeRange['start']){
$query.=" AND lastoccurence > :startdate";
$values['startdate']=$timeRange['start']->getTimeStamp();
}
if($timeRange&&$timeRange['end']){
$query.=" AND firstoccurence < :enddate";
$values['enddate']=$timeRange['end']->getTimeStamp();
}
$stmt=$this->pdo->prepare($query);
$stmt->execute($values);
$result=array();
while($row=$stmt->fetch(\PDO::FETCH_ASSOC)){
if($requirePostFilter){
if(!$this->validateFilterForObject($row,$filters)){
continue;
}
}
$result[]=$row['uri'];
}
return$result;
}
}