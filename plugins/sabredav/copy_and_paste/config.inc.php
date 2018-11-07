<?php
/***************************************************
 *
 * CONFIGURATION
 *
 ***************************************************/
/* SabreDAV base:
     it is highly recommended to run SabreDAV
     in a root folder.
*/
$baseUri = '/';

/* Database connection */
$dbtype = 'mysql';
$dbhost = 'localhost';
$dbname = 'sabredav';
$dbuser = 'root';
$dbpass = 'password';

/* Authentication 
   'imap' requires PHP imap module. If not installed it defaults to 'database' */
$authtype = 'imap'; // 'imap' or 'database'

/* IMAP authentication
   First argument of PHP function imap_open.
   Details: http://php.net/manual/en/function.imap-open.php */
$imap_open = '{localhost:143}';

/* Autoban */
/* If your IMAP server supports 'autoban', set values according to IMAP server configuration */
$autoban_db_table = 'autoban';
$autoban_attempts = 5;
$autoban_interval = 30; // minutes

/* cPanel */
$cpurl        = 'https://webmail.mydomain.tld:2096/login';
$cpuser       = 'myroundcube@mydomain.tld';
$cppass       = 'password';
$cpcookiefile = '/var/tmp/cookie.txt'; // must be writeable !!!

/* Password encrytion realm
   NOTE: It must be the same as configured in Roundcube
*/
$realm = 'SabreDAV';

/* Sharing */
$readonly_subdomain = 'dav-r';
$readwrite_subdomain = 'dav-rw';

/* Property positions */
/*
http://dav-r.mydomain.tld/calendars/user@mydomain.tld/events
pos 0                    /dav-pos 1/user-pos 2       /resource-pos 3
*/
$dav_pos      = 1;
$user_pos     = 2;
$resource_pos = 3;

/* Only this user has access to SabreDAV HTTP interface */
$admin = 'admin@mydomain.tld';

/* default Roundcube home (if user does not have a domain part) */
/* no trailing slash if Roundcube is in a root folder! Otherwise: http://webmail.mydomain.tld/roundcube/ */
$rcurl = 'http://webmail.mydomain.tld';

/* mapped Roundcube homes (if user has a domain part) */
$map = array(
  // domain part and home url
  'domain.tld' => 'http://webmail.mydomain.tld', // no trailing slash !!!
  'domain2.tld' => 'http://webmail.mydomain2.tld',
);

?>