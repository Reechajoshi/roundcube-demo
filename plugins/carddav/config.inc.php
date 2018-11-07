<?php
/* automatic addressbook database table name */
$rcmail_config['db_table_collected_contacts'] = 'collected_contacts';

/* Is automatic_addressbook enabled or disabled by default for users? */
$rcmail_config['use_auto_abook'] = true;

/* Should automatic_addressbook be used for completion by default? */
$rcmail_config['use_auto_abook_for_completion'] = true;

/* Show database addressbook even if they are empty */
$rcmail_config['show_empty_database_addressbooks'] = false;

/* CardDAV database tables */
$rcmail_config['db_table_carddav_server'] = 'carddav_server';
$rcmail_config['db_table_carddav_contacts'] = 'carddav_contacts';
$rcmail_config['db_table_carddav_contactgroups'] = 'carddav_contactgroups';
$rcmail_config['db_table_carddav_contactgroupmembers'] = 'carddav_contactgroupmembers';

/* CardDAV database tables sequences (PostgreSQL) */
$rcmail_config['db_sequence_carddav_server'] = 'carddav_server_carddav_server_id_seq';
$rcmail_config['db_sequence_carddav_contacts'] = 'carddav_contacts_carddav_contact_id_seq';
$rcmail_config['db_sequence_carddav_contactgroups'] = 'carddav_contactgroups_contactgroup_id_seq';

/* Debug (extended logging) */
$rcmail_config['carddav_debug'] = false;

/* Prevent users from editing defaults */
$rcmail_config['carddav_protect'] = false;

/* Max CardDAVs */
$rcmail_config['max_carddavs'] = 5;

/* Sync CardDAVs every (x) minutes automatically (0 = on login only) */
$rcmail_config['sync_carddavs_interval'] = 5;

/* Host where passwords are in sync with IMAP passwords */
$rcmail_config['carddav_synced_passwords'] = array(
  'dav.dev.macgregor.tk',
);

/* Default CardDAV addressbooks
   placeholders: %u  = $_SESSiON['username'],
                 %su = local part of $_SESSION['username'],
                 %p  = $_SESSION['password']
*/
$rcmail_config['def_carddavs'] = array(
  'Work' => array(
                   'user' => '%u',
                   'pass' => '%p',
                   'url' => 'https://contacts.mgtech.in/dav/index.php/addressbooks/%u/work',
                   'readonly' => false,
                 ),
  'Personal' => array(
                   'user' => '%u',
                   'pass' => '%p',
                   'url' => 'https://contacts.mgtech.in/dav/index.php/addressbooks/%u/personal',
                   'readonly' => false,
                 )
);

$rcmail_config['carddavs'] = array(
  'Work' => array(
                   'user' => '%u',
                   'pass' => '%p',
                   'url' => 'https://contacts.mgtech.in/dav/index.php/addressbooks/%u/work',
                   'readonly' => false,
                 ),
  'Personal' => array(
                   'user' => '%u',
                   'pass' => '%p',
                   'url' => 'https://contacts.mgtech.in/dav/index.php/addressbooks/%u/personal',
                   'readonly' => false,
                 )
);

/* HINT
   By default %u is replaced by rcmail::get_instance()->user->data['username'] (eq. to $_SESSION['username']).
   If it does not fit your needs, this is the right place to replace %u by whatever you need.
   Here follows an example how to strip off the domain part of the user
*/

/*
if(function_exists('get_input_value')){
  $user = explode('@', get_input_value('_user', RCUBE_INPUT_POST), 2);
  $user = $user[0];
  foreach($rcmail_config['def_carddavs'] as $key => $props){
    if($rcmail_config['def_carddavs'][$key]['user'] == '%u'){
      $rcmail_config['def_carddavs'][$key]['user'] = $user;
      $rcmail_config['def_carddavs'][$key]['url'] = str_replace('%u', $user, $rcmail_config['def_carddavs'][$key]['url']);
    }
  }
}
*/

?>