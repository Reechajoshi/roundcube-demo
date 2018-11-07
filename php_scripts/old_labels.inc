<?php

/*
 +-----------------------------------------------------------------------+
 | localization/<lang>/labels.inc                                        |
 |                                                                       |
 | Localization file of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/labels/
*/

$temp_labels = array();

// login page
$temp_labels['welcome']   = 'Welcome to $product';
$temp_labels['username']  = 'Username';
$temp_labels['password']  = 'Password';
$temp_labels['server']    = 'Server';
$temp_labels['login']     = 'Login';

// taskbar
$temp_labels['logout']   = 'Logout';
$temp_labels['mail']     = 'Mail';
$temp_labels['settings'] = 'Settings';
$temp_labels['addressbook'] = 'Address Book';

// mailbox names
$temp_labels['inbox']  = 'Inbox';
$temp_labels['drafts'] = 'Drafts';
$temp_labels['sent']   = 'Sent';
$temp_labels['trash']  = 'Trash';
$temp_labels['junk']   = 'Junk';
$temp_labels['show_real_foldernames'] = 'Show real names for special folders';

// message listing
$temp_labels['subject'] = 'Subject';
$temp_labels['from']    = 'From';
$temp_labels['sender']  = 'Sender';
$temp_labels['to']      = 'To';
$temp_labels['cc']      = 'Cc';
$temp_labels['bcc']     = 'Bcc';
$temp_labels['replyto'] = 'Reply-To';
$temp_labels['followupto'] = 'Followup-To';
$temp_labels['date']    = 'Date';
$temp_labels['size']    = 'Size';
$temp_labels['priority'] = 'Priority';
$temp_labels['organization'] = 'Organization';
$temp_labels['readstatus'] = 'Read status';
$temp_labels['listoptions'] = 'List options...';

$temp_labels['mailboxlist'] = 'Folders';
$temp_labels['messagesfromto'] = 'Messages $from to $to of $count';
$temp_labels['threadsfromto'] = 'Threads $from to $to of $count';
$temp_labels['messagenrof'] = 'Message $nr of $count';
$temp_labels['fromtoshort'] = '$from – $to of $count';

$temp_labels['copy']     = 'Copy';
$temp_labels['move']     = 'Move';
$temp_labels['moveto']   = 'Move to...';
$temp_labels['copyto']   = 'Copy to...';
$temp_labels['download'] = 'Download';
$temp_labels['open']     = 'Open';
$temp_labels['showattachment'] = 'Show';
$temp_labels['showanyway'] = 'Show it anyway';

$temp_labels['filename'] = 'File name';
$temp_labels['filesize'] = 'File size';

$temp_labels['addtoaddressbook'] = 'Add to address book';

// weekdays short
$temp_labels['sun'] = 'Sun';
$temp_labels['mon'] = 'Mon';
$temp_labels['tue'] = 'Tue';
$temp_labels['wed'] = 'Wed';
$temp_labels['thu'] = 'Thu';
$temp_labels['fri'] = 'Fri';
$temp_labels['sat'] = 'Sat';

// weekdays long
$temp_labels['sunday']    = 'Sunday';
$temp_labels['monday']    = 'Monday';
$temp_labels['tuesday']   = 'Tuesday';
$temp_labels['wednesday'] = 'Wednesday';
$temp_labels['thursday']  = 'Thursday';
$temp_labels['friday']    = 'Friday';
$temp_labels['saturday']  = 'Saturday';

// months short
$temp_labels['jan']	= 'Jan';
$temp_labels['feb']	= 'Feb';
$temp_labels['mar']	= 'Mar';
$temp_labels['apr']	= 'Apr';
$temp_labels['may']	= 'May';
$temp_labels['jun']	= 'Jun';
$temp_labels['jul'] 	= 'Jul';
$temp_labels['aug']	= 'Aug';
$temp_labels['sep']	= 'Sep';
$temp_labels['oct']	= 'Oct';
$temp_labels['nov']	= 'Nov';
$temp_labels['dec']	= 'Dec';

// months long
$temp_labels['longjan']	= 'January';
$temp_labels['longfeb']	= 'February';
$temp_labels['longmar']	= 'March';
$temp_labels['longapr']	= 'April';
$temp_labels['longmay']	= 'May';
$temp_labels['longjun']	= 'June';
$temp_labels['longjul']	= 'July';
$temp_labels['longaug']	= 'August';
$temp_labels['longsep']	= 'September';
$temp_labels['longoct']	= 'October';
$temp_labels['longnov']	= 'November';
$temp_labels['longdec']	= 'December';

$temp_labels['today'] = 'Today';

// toolbar buttons
$temp_labels['refresh']          = 'Refresh';
$temp_labels['checkmail']        = 'Check for new messages';
$temp_labels['compose']          = 'Compose';
$temp_labels['writenewmessage']  = 'Create a new message';
$temp_labels['reply']            = 'Reply';
$temp_labels['replytomessage']   = 'Reply to sender';
$temp_labels['replytoallmessage'] = 'Reply to list or to sender and all recipients';
$temp_labels['replyall']         = 'Reply all';
$temp_labels['replylist']        = 'Reply list';
$temp_labels['forward']          = 'Forward';
$temp_labels['forwardinline']    = 'Forward inline';
$temp_labels['forwardattachment'] = 'Forward as attachment';
$temp_labels['forwardmessage']   = 'Forward the message';
$temp_labels['deletemessage']    = 'Delete message';
$temp_labels['movemessagetotrash'] = 'Move message to trash';
$temp_labels['printmessage']     = 'Print this message';
$temp_labels['previousmessage']  = 'Show previous message';
$temp_labels['firstmessage']     = 'Show first message';
$temp_labels['nextmessage']      = 'Show next message';
$temp_labels['lastmessage']      = 'Show last message';
$temp_labels['backtolist']       = 'Back to message list';
$temp_labels['viewsource']       = 'Show source';
$temp_labels['mark']             = 'Mark';
$temp_labels['markmessages']     = 'Mark messages';
$temp_labels['markread']         = 'As read';
$temp_labels['markunread']       = 'As unread';
$temp_labels['markflagged']      = 'As flagged';
$temp_labels['markunflagged']    = 'As unflagged';
$temp_labels['moreactions']      = 'More actions...';
$temp_labels['more']             = 'More';
$temp_labels['back']             = 'Back';
$temp_labels['options']          = 'Options';

$temp_labels['select'] = 'Select';
$temp_labels['all'] = 'All';
$temp_labels['none'] = 'None';
$temp_labels['currpage'] = 'Current page';
$temp_labels['unread'] = 'Unread';
$temp_labels['flagged'] = 'Flagged';
$temp_labels['unanswered'] = 'Unanswered';
$temp_labels['withattachment'] = 'With attachment';
$temp_labels['deleted'] = 'Deleted';
$temp_labels['undeleted'] = 'Not deleted';
$temp_labels['invert'] = 'Invert';
$temp_labels['filter'] = 'Filter';
$temp_labels['filtermatch'] = 'Filter Match';
$temp_labels['list'] = 'List';
$temp_labels['threads'] = 'Threads';
$temp_labels['expand-all'] = 'Expand All';
$temp_labels['expand-unread'] = 'Expand Unread';
$temp_labels['collapse-all'] = 'Collapse All';
$temp_labels['threaded'] = 'Threaded';

$temp_labels['autoexpand_threads'] = 'Expand message threads';
$temp_labels['do_expand'] = 'all threads';
$temp_labels['expand_only_unread'] = 'only with unread messages';
$temp_labels['fromto'] = 'From/To';
$temp_labels['flag'] = 'Flag';
$temp_labels['attachment'] = 'Attachment';
$temp_labels['nonesort'] = 'None';
$temp_labels['sentdate'] = 'Sent date';
$temp_labels['arrival'] = 'Arrival date';
$temp_labels['asc'] = 'ascending';
$temp_labels['desc'] = 'descending';
$temp_labels['listcolumns'] = 'List columns';
$temp_labels['listsorting'] = 'Sorting column';
$temp_labels['listorder'] = 'Sorting order';
$temp_labels['listmode'] = 'List view mode';

$temp_labels['folderactions'] = 'Folder actions...';
$temp_labels['compact'] = 'Compact';
$temp_labels['empty'] = 'Empty';
$temp_labels['importmessages'] = 'Import messages';

$temp_labels['quota'] = 'Disk usage';
$temp_labels['unknown']  = 'unknown';
$temp_labels['unlimited']  = 'unlimited';

$temp_labels['quicksearch']  = 'Quick search';
$temp_labels['resetsearch']  = 'Reset search';
$temp_labels['searchmod']  = 'Search modifiers';
$temp_labels['msgtext']  = 'Entire message';
$temp_labels['body']  = 'Body';
$temp_labels['type'] = 'Type';
$temp_labels['namex'] = 'Name';

$temp_labels['openinextwin'] = 'Open in new window';
$temp_labels['emlsave'] = 'Download (.eml)';
$temp_labels['changeformattext'] = 'Display in plain text format';
$temp_labels['changeformathtml'] = 'Display in HTML format';

// message compose
$temp_labels['editasnew']      = 'Edit as new';
$temp_labels['send']           = 'Send';
$temp_labels['sendmessage']    = 'Send message';
$temp_labels['savemessage']    = 'Save as draft';
$temp_labels['addattachment']  = 'Attach a file';
$temp_labels['charset']        = 'Charset';
$temp_labels['editortype']     = 'Editor type';
$temp_labels['returnreceipt']  = 'Return receipt';
$temp_labels['dsn']            = 'Delivery status notification';
$temp_labels['mailreplyintro'] = 'On $date, $sender wrote:';
$temp_labels['originalmessage'] = 'Original Message';

$temp_labels['editidents']    = 'Edit identities';
$temp_labels['spellcheck']    = 'Spell';
$temp_labels['checkspelling'] = 'Check spelling';
$temp_labels['resumeediting'] = 'Resume editing';
$temp_labels['revertto']      = 'Revert to';

$temp_labels['restore'] = 'Restore';
$temp_labels['restoremessage'] = 'Restore message?';

$temp_labels['responses'] = 'Responses';
$temp_labels['insertresponse'] = 'Insert a response';
$temp_labels['manageresponses'] = 'Manage responses';
$temp_labels['savenewresponse'] = 'Save new response';
$temp_labels['editresponses'] = 'Edit responses';
$temp_labels['editresponse'] = 'Edit response';
$temp_labels['responsename'] = 'Name';
$temp_labels['responsetext'] = 'Response Text';

$temp_labels['attach'] = 'Attach';
$temp_labels['attachments'] = 'Attachments';
$temp_labels['upload'] = 'Upload';
$temp_labels['uploadprogress'] = '$percent ($current from $total)';
$temp_labels['close']  = 'Close';
$temp_labels['messageoptions']  = 'Message options...';

$temp_labels['low']     = 'Low';
$temp_labels['lowest']  = 'Lowest';
$temp_labels['normal']  = 'Normal';
$temp_labels['high']    = 'High';
$temp_labels['highest'] = 'Highest';

$temp_labels['nosubject']  = '(no subject)';
$temp_labels['showimages'] = 'Display images';
$temp_labels['alwaysshow'] = 'Always show images from $sender';
$temp_labels['isdraft']    = 'This is a draft message.';
$temp_labels['andnmore']   = '$nr more...';
$temp_labels['togglemoreheaders'] = 'Show more message headers';
$temp_labels['togglefullheaders'] = 'Toggle raw message headers';

$temp_labels['htmltoggle'] = 'HTML';
$temp_labels['plaintoggle'] = 'Plain text';
$temp_labels['savesentmessagein'] = 'Save sent message in';
$temp_labels['dontsave'] = 'don\'t save';
$temp_labels['maxuploadsize'] = 'Maximum allowed file size is $size';

$temp_labels['addcc'] = 'Add Cc';
$temp_labels['addbcc'] = 'Add Bcc';
$temp_labels['addreplyto'] = 'Add Reply-To';
$temp_labels['addfollowupto'] = 'Add Followup-To';

// mdn
$temp_labels['mdnrequest'] = 'The sender of this message has asked to be notified when you read this message. Do you wish to notify the sender?';
$temp_labels['receiptread'] = 'Return Receipt (read)';
$temp_labels['yourmessage'] = 'This is a Return Receipt for your message';
$temp_labels['receiptnote'] = 'Note: This receipt only acknowledges that the message was displayed on the recipient\'s computer. There is no guarantee that the recipient has read or understood the message contents.';

// address boook
$temp_labels['name']         = 'Display Name';
$temp_labels['firstname']    = 'First Name';
$temp_labels['surname']      = 'Last Name';
$temp_labels['middlename']   = 'Middle Name';
$temp_labels['nameprefix']   = 'Prefix';
$temp_labels['namesuffix']   = 'Suffix';
$temp_labels['nickname']     = 'Nickname';
$temp_labels['jobtitle']     = 'Job Title';
$temp_labels['department']   = 'Department';
$temp_labels['gender']       = 'Gender';
$temp_labels['maidenname']   = 'Maiden Name';
$temp_labels['email']        = 'Email';
$temp_labels['phone']        = 'Phone';
$temp_labels['address']      = 'Address';
$temp_labels['street']       = 'Street';
$temp_labels['locality']     = 'City';
$temp_labels['zipcode']      = 'ZIP Code';
$temp_labels['region']       = 'State/Province';
$temp_labels['country']      = 'Country';
$temp_labels['birthday']     = 'Birthday';
$temp_labels['anniversary']  = 'Anniversary';
$temp_labels['website']      = 'Website';
$temp_labels['instantmessenger'] = 'IM';
$temp_labels['notes'] = 'Notes';
$temp_labels['male']   = 'male';
$temp_labels['female'] = 'female';
$temp_labels['manager'] = 'Manager';
$temp_labels['assistant'] = 'Assistant';
$temp_labels['spouse'] = 'Spouse';
$temp_labels['allfields'] = 'All fields';
$temp_labels['search'] = 'Search';
$temp_labels['advsearch'] = 'Advanced Search';
$temp_labels['advanced'] = 'Advanced';
$temp_labels['other'] = 'Other';

$temp_labels['typehome']   = 'Home';
$temp_labels['typework']   = 'Work';
$temp_labels['typeother']  = 'Other';
$temp_labels['typemobile']  = 'Mobile';
$temp_labels['typemain']  = 'Main';
$temp_labels['typehomefax']  = 'Home Fax';
$temp_labels['typeworkfax']  = 'Work Fax';
$temp_labels['typecar']  = 'Car';
$temp_labels['typepager']  = 'Pager';
$temp_labels['typevideo']  = 'Video';
$temp_labels['typeassistant']  = 'Assistant';
$temp_labels['typehomepage']  = 'Home Page';
$temp_labels['typeblog'] = 'Blog';
$temp_labels['typeprofile'] = 'Profile';

$temp_labels['addfield'] = 'Add field...';
$temp_labels['addcontact'] = 'Add new contact';
$temp_labels['editcontact'] = 'Edit contact';
$temp_labels['contacts'] = 'Contacts';
$temp_labels['contactproperties'] = 'Contact properties';
$temp_labels['personalinfo'] = 'Personal information';

$temp_labels['edit']   = 'Edit';
$temp_labels['cancel'] = 'Cancel';
$temp_labels['save']   = 'Save';
$temp_labels['delete'] = 'Delete';
$temp_labels['rename'] = 'Rename';
$temp_labels['addphoto'] = 'Add';
$temp_labels['replacephoto'] = 'Replace';
$temp_labels['uploadphoto'] = 'Upload photo';

$temp_labels['newcontact']     = 'Create new contact card';
$temp_labels['deletecontact']  = 'Delete selected contacts';
$temp_labels['composeto']      = 'Compose mail to';
$temp_labels['contactsfromto'] = 'Contacts $from to $to of $count';
$temp_labels['print']          = 'Print';
$temp_labels['export']         = 'Export';
$temp_labels['exportall']      = 'Export all';
$temp_labels['exportsel']      = 'Export selected';
$temp_labels['exportvcards']   = 'Export contacts in vCard format';
$temp_labels['newcontactgroup'] = 'Create new contact group';
$temp_labels['grouprename']    = 'Rename group';
$temp_labels['groupdelete']    = 'Delete group';
$temp_labels['groupremoveselected'] = 'Remove selected contacts from group';

$temp_labels['previouspage']   = 'Show previous page';
$temp_labels['firstpage']      = 'Show first page';
$temp_labels['nextpage']       = 'Show next page';
$temp_labels['lastpage']       = 'Show last page';

$temp_labels['group'] = 'Group';
$temp_labels['groups'] = 'Groups';
$temp_labels['listgroup'] = 'List group members';
$temp_labels['personaladrbook'] = 'Personal Addresses';

$temp_labels['searchsave'] = 'Save search';
$temp_labels['searchdelete'] = 'Delete search';

$temp_labels['import'] = 'Import';
$temp_labels['importcontacts'] = 'Import contacts';
$temp_labels['importfromfile'] = 'Import from file:';
$temp_labels['importtarget'] = 'Add contacts to';
$temp_labels['importreplace'] = 'Replace the entire address book';
$temp_labels['importgroups'] = 'Import group assignments';
$temp_labels['importgroupsall'] = 'All (create groups if necessary)';
$temp_labels['importgroupsexisting'] = 'Only for existing groups';
$temp_labels['importdesc'] = 'You can upload contacts from an existing address book.<br/>We currently support importing addresses from the <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> or CSV (comma-separated) data format.';
$temp_labels['done'] = 'Done';

// settings
$temp_labels['settingsfor'] = 'Settings for';
$temp_labels['about'] = 'About';
$temp_labels['preferences'] = 'Preferences';
$temp_labels['userpreferences'] = 'User preferences';
$temp_labels['editpreferences'] = 'Edit user preferences';

$temp_labels['identities'] = 'Identities';
$temp_labels['manageidentities'] = 'Manage identities for this account';
$temp_labels['newidentity'] = 'New identity';

$temp_labels['newitem'] = 'New item';
$temp_labels['edititem'] = 'Edit item';

$temp_labels['preferhtml'] = 'Display HTML';
$temp_labels['defaultcharset'] = 'Default Character Set';
$temp_labels['htmlmessage'] = 'HTML Message';
$temp_labels['messagepart'] = 'Part';
$temp_labels['digitalsig'] = 'Digital Signature';
$temp_labels['dateformat'] = 'Date format';
$temp_labels['timeformat'] = 'Time format';
$temp_labels['prettydate'] = 'Pretty dates';
$temp_labels['setdefault']  = 'Set default';
$temp_labels['autodetect']  = 'Auto';
$temp_labels['language']  = 'Language';
$temp_labels['timezone']  = 'Time zone';
$temp_labels['pagesize']  = 'Rows per page';
$temp_labels['signature'] = 'Signature';
$temp_labels['dstactive']  = 'Daylight saving time';
$temp_labels['showinextwin'] = 'Open message in a new window';
$temp_labels['composeextwin'] = 'Compose in a new window';
$temp_labels['htmleditor'] = 'Compose HTML messages';
$temp_labels['htmlonreply'] = 'on reply to HTML message';
$temp_labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
$temp_labels['htmlsignature'] = 'HTML signature';
$temp_labels['showemail'] = 'Show email address with display name';
$temp_labels['previewpane'] = 'Show preview pane';
$temp_labels['skin'] = 'Interface skin';
$temp_labels['logoutclear'] = 'Clear Trash on logout';
$temp_labels['logoutcompact'] = 'Compact Inbox on logout';
$temp_labels['uisettings'] = 'User Interface';
$temp_labels['serversettings'] = 'Server Settings';
$temp_labels['mailboxview'] = 'Mailbox View';
$temp_labels['mdnrequests'] = 'On request for return receipt';
$temp_labels['askuser'] = 'ask me';
$temp_labels['autosend'] = 'send receipt';
$temp_labels['autosendknown'] = 'send receipt to my contacts, otherwise ask me';
$temp_labels['autosendknownignore'] = 'send receipt to my contacts, otherwise ignore';
$temp_labels['ignore'] = 'ignore';
$temp_labels['readwhendeleted'] = 'Mark the message as read on delete';
$temp_labels['flagfordeletion'] = 'Flag the message for deletion instead of delete';
$temp_labels['skipdeleted'] = 'Do not show deleted messages';
$temp_labels['deletealways'] = 'If moving messages to Trash fails, delete them';
$temp_labels['deletejunk'] = 'Directly delete messages in Junk';
$temp_labels['showremoteimages'] = 'Display remote inline images';
$temp_labels['fromknownsenders'] = 'from known senders';
$temp_labels['always'] = 'always';
$temp_labels['showinlineimages'] = 'Display attached images below the message';
$temp_labels['autosavedraft']  = 'Automatically save draft';
$temp_labels['everynminutes']  = 'every $n minute(s)';
$temp_labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
$temp_labels['never']  = 'never';
$temp_labels['immediately']  = 'immediately';
$temp_labels['messagesdisplaying'] = 'Displaying Messages';
$temp_labels['messagescomposition'] = 'Composing Messages';
$temp_labels['mimeparamfolding'] = 'Attachment names';
$temp_labels['2231folding'] = 'Full RFC 2231 (Thunderbird)';
$temp_labels['miscfolding'] = 'RFC 2047/2231 (MS Outlook)';
$temp_labels['2047folding'] = 'Full RFC 2047 (other)';
$temp_labels['force7bit'] = 'Use MIME encoding for 8-bit characters';
$temp_labels['advancedoptions'] = 'Advanced options';
$temp_labels['focusonnewmessage'] = 'Focus browser window on new message';
$temp_labels['checkallfolders'] = 'Check all folders for new messages';
$temp_labels['displaynext'] = 'After message delete/move display the next message';
$temp_labels['defaultfont'] = 'Default font of HTML message';
$temp_labels['mainoptions'] = 'Main Options';
$temp_labels['browseroptions'] = 'Browser Options';
$temp_labels['section'] = 'Section';
$temp_labels['maintenance'] = 'Maintenance';
$temp_labels['newmessage'] = 'New Message';
$temp_labels['signatureoptions'] = 'Signature Options';
$temp_labels['whenreplying'] = 'When replying';
$temp_labels['replyempty'] = 'do not quote the original message';
$temp_labels['replytopposting'] = 'start new message above the quote';
$temp_labels['replybottomposting'] = 'start new message below the quote';
$temp_labels['replyremovesignature'] = 'When replying remove original signature from message';
$temp_labels['autoaddsignature'] = 'Automatically add signature';
$temp_labels['newmessageonly'] = 'new message only';
$temp_labels['replyandforwardonly'] = 'replies and forwards only';
$temp_labels['insertsignature'] = 'Insert signature';
$temp_labels['previewpanemarkread']  = 'Mark previewed messages as read';
$temp_labels['afternseconds']  = 'after $n seconds';
$temp_labels['reqmdn'] = 'Always request a return receipt';
$temp_labels['reqdsn'] = 'Always request a delivery status notification';
$temp_labels['replysamefolder'] = 'Place replies in the folder of the message being replied to';
$temp_labels['defaultabook'] = 'Default address book';
$temp_labels['autocompletesingle'] = 'Skip alternative email addresses in autocompletion';
$temp_labels['listnamedisplay'] = 'List contacts as';
$temp_labels['spellcheckbeforesend'] = 'Check spelling before sending a message';
$temp_labels['spellcheckoptions'] = 'Spellcheck Options';
$temp_labels['spellcheckignoresyms'] = 'Ignore words with symbols';
$temp_labels['spellcheckignorenums'] = 'Ignore words with numbers';
$temp_labels['spellcheckignorecaps'] = 'Ignore words with all letters capitalized';
$temp_labels['addtodict'] = 'Add to dictionary';
$temp_labels['mailtoprotohandler'] = 'Register protocol handler for mailto: links';
$temp_labels['standardwindows'] = 'Handle popups as standard windows';
$temp_labels['forwardmode'] = 'Messages forwarding';
$temp_labels['inline'] = 'inline';
$temp_labels['asattachment'] = 'as attachment';
$temp_labels['replyallmode'] = 'Default action of [Reply all] button';
$temp_labels['replyalldefault'] = 'reply to all';
$temp_labels['replyalllist'] = 'reply to mailing list only (if found)';

$temp_labels['folder']  = 'Folder';
$temp_labels['folders']  = 'Folders';
$temp_labels['foldername']  = 'Folder name';
$temp_labels['subscribed']  = 'Subscribed';
$temp_labels['messagecount'] = 'Messages';
$temp_labels['create']  = 'Create';
$temp_labels['createfolder']  = 'Create new folder';
$temp_labels['managefolders']  = 'Manage folders';
$temp_labels['specialfolders'] = 'Special Folders';
$temp_labels['properties'] = 'Properties';
$temp_labels['folderproperties'] = 'Folder properties';
$temp_labels['parentfolder'] = 'Parent folder';
$temp_labels['location'] = 'Location';
$temp_labels['info'] = 'Information';
$temp_labels['getfoldersize'] = 'Click to get folder size';
$temp_labels['changesubscription'] = 'Click to change subscription';
$temp_labels['foldertype'] = 'Folder Type';
$temp_labels['personalfolder']  = 'Private Folder';
$temp_labels['otherfolder']  = 'Other User\'s Folder';
$temp_labels['sharedfolder']  = 'Public Folder';
$temp_labels['folderrule'] = 'Rule';
$temp_labels['folderruleenable'] = 'Enable';
$temp_labels['outofofficeenable'] = 'Enable';
$temp_labels['foldersharing'] = 'Sharing';

$temp_labels['sortby'] = 'Sort by';
$temp_labels['sortasc']  = 'Sort ascending';
$temp_labels['sortdesc'] = 'Sort descending';
$temp_labels['undo'] = 'Undo';

$temp_labels['installedplugins'] = 'Installed plugins';
$temp_labels['plugin'] = 'Plugin';
$temp_labels['version'] = 'Version';
$temp_labels['source'] = 'Source';
$temp_labels['license'] = 'License';
$temp_labels['support'] = 'Get support';

// units
$temp_labels['B'] = 'B';
$temp_labels['KB'] = 'KB';
$temp_labels['MB'] = 'MB';
$temp_labels['GB'] = 'GB';

// character sets
$temp_labels['unicode'] = 'Unicode';
$temp_labels['english'] = 'English';
$temp_labels['westerneuropean'] = 'Western European';
$temp_labels['easterneuropean'] = 'Eastern European';
$temp_labels['southeasterneuropean'] = 'South-Eastern European';
$temp_labels['baltic'] = 'Baltic';
$temp_labels['cyrillic'] = 'Cyrillic';
$temp_labels['arabic'] = 'Arabic';
$temp_labels['greek'] = 'Greek';
$temp_labels['hebrew'] = 'Hebrew';
$temp_labels['turkish'] = 'Turkish';
$temp_labels['nordic'] = 'Nordic';
$temp_labels['thai'] = 'Thai';
$temp_labels['celtic'] = 'Celtic';
$temp_labels['vietnamese'] = 'Vietnamese';
$temp_labels['japanese'] = 'Japanese';
$temp_labels['korean'] = 'Korean';
$temp_labels['chinese'] = 'Chinese';

/** MACGREGOR CHANGES **/
/** ADMIN MENU **/
$temp_labels['admin'] = 'Security';
$temp_labels['changepwdTitle'] = 'Change Password';
$temp_labels['manageadmin'] = 'Manage Security';
$temp_labels['curradminpwd'] = 'Current Password';
$temp_labels['adminpwd'] = 'New Password';
$temp_labels['confadminpwd'] = 'Confirm Password';
$temp_labels[ 'changepwd' ] = 'Change Password';
$temp_labels['usersTitle'] = 'Users';
$temp_labels['outOfOfficeTitle'] = 'Out Of Office';
$temp_labels['outOfOfficeSub'] = 'Subject';

/* RULES MENU */
$temp_labels['rules'] = 'Rules';
$temp_labels[ 'outOfOffice' ] = 'Out Of Office';
$temp_labels[ 'blockEmail' ] = 'Block Emails';
$temp_labels['forwardRule'] = 'Forward Rule'; 
$temp_labels['customRule'] = 'Custom Directive';
$temp_labels['outOfOfficeAdd'] = 'Add';
$temp_labels['header'] = 'Header';
$temp_labels['sub'] = 'Subject';
$temp_labels['BlockFilter'] = 'Match';
$temp_labels['block'] = 'Block Emails';
$temp_labels['customruleenable'] = 'Enable';
$temp_labels['customRuleMessage'] = 'Description';
$temp_labels['outOfOfficeFilter'] = 'Filter';

/* MANAGE MENU */
$temp_labels['manage'] = 'Manage';
$temp_labels['managedomain_aliases'] = 'Account Domain';
$temp_labels['managemanage'] = 'Manage Users';
$temp_labels['accounts_domain'] = 'Select Account';
$temp_labels['manageusers'] = 'Users';
$temp_labels['manageGroups'] = 'Distribution lists';
$temp_labels['addGroup'] = 'Groups';
$temp_labels['editGroup'] = 'Edit Group';
$temp_labels['manageAliases'] = 'User Aliases';
$temp_labels['managerules'] = 'Manage Rules';
$temp_labels['domain_aliases'] = 'Manage Domain Aliases';
$temp_labels['accounts'] = 'Account Selection';
$temp_labels['users'] = 'Manage Users';
$temp_labels['manageacc'] = 'Add User';
$temp_labels['add_uname_title'] = 'Name';
$temp_labels['add_uemail_title'] = 'Email';
$temp_labels['add_upwd_title'] = 'Password';
$temp_labels['add_upriviledge_title'] = 'Priviledged';
$temp_labels['add_uquota_title'] = 'Quota';
$temp_labels['manageshowusers'] = 'Current Users';
$temp_labels['managedl'] = 'Add List';
$temp_labels['manageshowlist'] = 'Add User';
$temp_labels['add_listname_title'] = 'List Name';
$temp_labels['add_private_list'] = 'Private List';
$lables['atTheRate'] = '@';
$temp_labels['managealias'] = 'Add User Aliases';
$temp_labels['manage_current_alias'] = 'Current User Aliases';
$temp_labels['user_rules'] = 'Manage User Rules';
$temp_labels['manageRules'] = 'Manage User Rules';
$temp_labels['aliases'] = 'Manage User Aliases';

/* SUPERADMIN MENU */
$temp_labels['superadmin'] = 'Super Admin'; // Super Admin
$temp_labels['new_account_ttl'] = 'New Account';
$temp_labels['new_account'] = 'Manage Super Admin';
$temp_labels['new_user'] = 'New User';
$temp_labels['new_user_ttl'] = 'Add New User'; // users table
$temp_labels['new_admin'] = 'Manage User Admin'; // user_admin tbl
$temp_labels['new_admin_ttl'] = 'Add New User';
$temp_labels['superadmin_delete'] = 'Delete Admin';
$temp_labels['new_admin_manage_domain'] = 'Admin Managed Domain';
$temp_labels['new_admin_manage_domain_ttl'] = 'New Admin Managed Domain';
// $temp_labels['managesuperadmin'] = 'Manage Super Admin';
// $temp_labels['add_superadmin_ttl'] = 'Add Super Admin';

?>