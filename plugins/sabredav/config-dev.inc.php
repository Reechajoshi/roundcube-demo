<?php
 
// config-dev of sabredav
// sabredav database configuration
$config['db_sabredav_dsn'] = 'mysql://calendar:calendar@localhost/calendar?new_link=true';

// SabreDAV Realm (used as salt for Password hash digesta1)
$config['sabredav_realm'] = 'SabreDAV';

// Read/write host
$config['sabredav_readwrite_host'] = 'rw.calendar.maildev.mgtech.in';

// Readonly host
$config['sabredav_readonly_host'] = 'r.calendar.maildev.mgtech.in';  

// Create the following calendars additionally to default calendar
$config['sabredav_cals'] = array();

// Associate default CalDAV calendars
$config['caldavs'] = array();

// Remember defaults
$config['default_caldavs'] = $config['caldavs'];

// Create the following addressbooks additionally to 'default' addressbook
$config['sabredav_cards'] = array(
  'work',
  'personal'
);

// Associate default CardDAV addressbooks
$config['carddavs'] = array(
  'Work' => array(
                   'user' => '%u',
                   'pass' => '%p',
                   'url' => 'http://calendar.maildev.mgtech.in/dav/index.php/addressbooks/%u/work',
                   'readonly' => false,
                 ),
  'Personal' => array(
                   'user' => '%u',
                   'pass' => '%p',
                   'url' => 'http://calendar.maildev.mgtech.in/dav/index.php/addressbooks/%u/personal',
                   'readonly' => false,
                 )
);

// Remember defaults
$config['default_carddavs'] = $config['carddavs'];

?>