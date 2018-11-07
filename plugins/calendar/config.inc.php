<?php

// caldav debug
$rcmail_config['caldav_debug'] = false;

// backend type (caldav, database)
$rcmail_config['backend'] = 'caldav';

/* If you don't want that users are able to overwrite defaults, then add
   'backend' in main configuration file (./config/main.inc.php) to
   'dont_override' array:
   // don't allow these settings to be overriden by the user
   $rcmail_config['dont_override'] = array(
     'backend',
     //other protected values ...
   );
*/

/* Dont't allow users to overwrite defautl CalDAV settings */
$rcmail_config['caldav_protect'] = false;

/* Max CalDavs */
$rcmail_config['max_caldavs'] = 10;

/* Max Layers */
$rcmail_config['max_feeds'] = 3;

/* mod_rewrite short urls
User friendly URLs for calendar sharing
---------------------------------------
NOTE: You need mod_rewrite
Edit .htaccess in Roundcube Root Folder as follows:
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule ^favicon.ico$ skins/classic/images/favicon.ico
# security rules
RewriteRule .git/ - [F]
RewriteRule ^README|INSTALL|LICENSE|SQL|bin|CHANGELOG$ - [F]
# calendar
RewriteRule ^ics/([0-9]+)/([a-z0-9]+)$ index.php?_task=dummy&_action=plugin.calendar_showlayer&_userid=$1&_ct=$2&_ics=1
RewriteRule ^rc/([0-9]+)/([a-z0-9]+)$ index.php?_task=dummy&_action=plugin.calendar_showlayer&_userid=$1&_ct=$2
</IfModule>
*/
$rcmail_config['cal_short_urls'] = false;

/* default CalDAV backend (null = no default settings)
   %u  will be replaced by $_SESSION['username']
   %su will be replaced by the user part (string before @ from $_SESSION['username'])
   %p  will be replaced by Roundcube Login Password
   %c  will be replaced by the category a CalDAV is associated with
*/
/* SabreDAV example */
$rcmail_config['default_caldav_backend'] = array(
  'user' => '%u',
  'pass' => '%p',
  'principals' => '',
  'url' => 'https://calendar.mgtech.in/dav/index.php/calendars/%u/',
  'cat' => 'https://calendar.mgtech.in/dav/index.php/calendars/%u/%c',
  'auth' => 'detect', //basic or detect
  'extr' => false,
);

// SEND MAIL TEST
// $rcmail_config[ 'cal_notify' ] = true;

/* Associate default CaldDAV calendars */
/*
$rcmail_config['caldavs'] = array(
  'Work' => array(
                   'user' => '%u',
                   'pass' => '%p',
                   'url' => 'http://dav.mydomain.tld/calendars/%u/work',
                   'auth' => 'detect',
                   'readonly' => false,
                   'extr' => false, // external reminder service
                 ),
  'Holidays' => array(
                   'user' => '%u',
                   'pass' => '%p',
                   'url' => 'http://dav.mydomain.tld/calendars/%u/holidays',
                   'auth' => 'detect',
                   'readonly' => false,
                   'extr' => false, // external reminder service
                 ),
  'Personal' => array(
                   'user' => '%u',
                   'pass' => '%p',
                   'url' => 'http://dav.mydomain.tld/calendars/%u/personal',
                   'auth' => 'detect',
                   'readonly' => false,
                   'extr' => false, // external reminder service
                 ),
  'Family' => array(
                   'user' => '%u',
                   'pass' => '%p',
                   'url' => 'http://dav.mydomain.tld/calendars/%u/family',
                   'auth' => 'detect',
                   'readonly' => false,
                   'extr' => false, // external reminder service
                 ),
);
*/

/* Caldav calendar auto-detection is executed on login.
   Do not run auto-detection if last login is younger than ... (seconds)
*/
$rcmail_config['sync_collections'] = 3600; // 0 = always on login

/* Do not autodetect the following collections.
   If your collection resides on https://dav.mydomain.tld/calendars/users/roundcube/tasks/
   then add to the array '/calendars/tasks'.
*/
$rcmail_config['caldav_hidden_collections'] = array(
  '/calendars/tasks',
);

/* CalDAV Replication
   fetch events (x) years in past and (y) years in future
*/
$rcmail_config['caldav_replication_range'] = array(
  'past'   => 2, // (x)
  'future' => 2, // (y)
);

/* Tasks Replication */
$rcmail_config['caldav_replication_range_tasks'] = 92; // days

/* Replicate CalDAV automatically after (x) seconds
   Recommended: 1800
   Never replicate automatically: false
*/
$rcmail_config['caldav_replicate_automatically'] = 1800;

/* Don't save passwords */
$rcmail_config['cal_dont_save_passwords'] = false;

/* Database table mapping */
// notice: leading underscore
$rcmail_config['backend_db_table_map'] = array(
  'database' => '', // default db table
  'caldav' => '_caldav', // caldav db table (= default db table) extended by _caldav
);

/* database table name (main table) */
$rcmail_config['db_table_events'] = 'events';
$rcmail_config['db_sequence_events'] = 'events_ids';

/* database table name (cache) */
$rcmail_config['db_table_events_cache'] = 'events_cache';
$rcmail_config['db_sequence_events_cache'] = 'events_cache_ids';

/* database table name reminders */
$rcmail_config['db_table_events_reminders'] = 'reminders';

/* fields where search is performed */
$rcmail_config['cal_searchset'] = array(
  'summary',
  'description',
  'location',
  'categories'
);

/* display small basic agenda day in calendar view */
$rcmail_config['hide_agenda_day_basic'] = true;

/* display upcoming calendar in mailbox view
   If enabled it is resource consuming on the client side!
*/
$rcmail_config['upcoming_cal'] = false;

/* preview next x days */
$rcmail_config['cal_previews'] = 2;

/* cron */
$rcmail_config['cron_log'] = false;
$rcmail_config['cron_smtp_user'] = 'dummy@mydomain.tld'; //smtp user
$rcmail_config['cron_smtp_pass'] = 'pass'; //smtp password
$rcmail_config['cron_rc_url'] = 'http://where_is_roundcube/'; //trailing slash !!!
$rcmail_config['cron_sender'] = 'noreply@mydomain.tld';

/* link colors for jquery-ui accordions 
   set according to your css */
$rcmail_config['linkcolor'] = '#212121';
$rcmail_config['rgblinkcolor'] = 'rgb(33, 33, 33)';

// use jqueryui theme
$rcmail_config['ui_theme_main_cal'] = true; // true is recommended
$rcmail_config['ui_theme_upcoming_cal'] = false; // false is recommended

// default calendar view (agendaDay, agendaWeek, month)
$rcmail_config['default_view'] = "month";

// timeslots per hour (1, 2, 3, 4, 6)
$rcmail_config['timeslots'] = '4';

// first day of the week (0-6)
$rcmail_config['first_day'] = '1';

// first hour of the calendar (0-23)
// -1: scroll to current time
$rcmail_config['first_hour'] = -1;
//$rcmail_config['first_hour'] = '6';

// default category
$rcmail_config['default_category'] = '1B52C0';

// label for default category
// $rcmail_config['default_category_label'] = utf8_encode('Default');
$rcmail_config['default_category_label'] = '%u';

// default font color ('complementary' or 'blackwhite')
$rcmail_config['default_font_color'] = 'blackwhite';

// default event categories (additional to 'default_category' / can be modified by user)
$rcmail_config['categories'] = array();

// remember default categories
$rcmail_config['default_categories'] = $rcmail_config['categories'];

// event preview category
$rcmail_config['categories_preview'] = array(
  'preview' => '75FF42',
  'occupied' => 'FF0000',
  'schedule' => '75FF42',
);

// public calendar categories (can't be modified by user)
$rcmail_config['public_categories'] = array();

// associated CalDAVs (can't be modified by user)
/* You can use here the same placeholders as in
   'default_caldav_backend'.
   Make sure to use unique keys. This array will
   overwrite 'public_categories'.
*/
$rcmail_config['public_caldavs'] = array();
/*$rcmail_config['public_caldavs'] = array(
  'Public' => array(
                'user' => '%u',
                'pass' => '%p',
                'url' => 'https://mycaldav.mydomain.tld/%u/events/public',
                'auth' => 'detect', // detect or basic
                'readonly' => false,
                'extr' => false,
              ),
);*/

// work days (0 = Sunday)
$rcmail_config['workdays'] = array(1,2,3,4,5);

// default event duration in hours (e.g. 0.25, 0.50, 1.00, 1.50, 2.00 ...)
$rcmail_config['default_duration'] = '1.00'; 

// event feeds (can be deleted by user; use it for pre-settings)
$rcmail_config['calendarfeeds'] = array();

/* public feeds (can't be deleted by user; inject here feeds which all users should see)
   
   IMPORTANT: Do not link static feeds directly!
     (*) Reason: If you do so, it builds a cache on a per user level.
                 This makes no sense, if each user should see the same feed.
                 It is not only slow, it blows up your cache database table.
                 Conclusion: Link only dynamic feeds directly!
     (*) The better way:
         (**) Create a user who holds your static feeds, f.e. public_user@yourdomain.tld.
         (**) Login as public_user@yourdomain.tld.
         (**) Goto Settings -> Calendar Feeds and enable ...
              ... 'Confidential feed access [read only]'
         (**) Copy the 'Feed URL' to clipboard.
         (**) Add this URL below and associate it with a category ...
              (***) Add a separator '|' followed by 'cache' to the category ...
              (***) If you want to inherit colorizing of public_user@yourdomain.tld,
                    then add a further separator '|' followed by 'inherit' to the
                    category.
              (***) Now the config entry should be something like:
              
              './?_task=dummy&_action=plugin.calendar_showlayer&_userid=123&_ct=4a923b22b6d9ce51c5966a09fb6ad889' => 'Holiday|cache|inherit'
         
         (**) Now add your static feeds to 'public_user@yourdomain.tld'.
         (**) Navigate to calendar and wait until it is loaded. Notice: you have always
              to load the calendar before you logout 'public_user@yourdomain.tld' to be
              sure that the cache is built and up to date.
         (**) Now you are done!
         (**) If you make changes to your static feeds, login as 'public_user@yourdomain.tld'
              and wait until the calendar is loaded.
     (*) Notice: To be able to search events in feeds, the host of the webmail must be same as the host of the feed.
                 F.e.: http://www.mydomain.tld (webmail) is not the same as http://mydomain.tld/?_task=... (feed url).
                 Details: http://code.google.com/p/myroundcube/issues/detail?id=239
*/
$rcmail_config['public_calendarfeeds'] = array();
$rcmail_config['shared_calendar_default_color'] = "FF6229";


?>
