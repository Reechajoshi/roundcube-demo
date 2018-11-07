 <?php
/* automatic addressbook database table name */
$config['db_table_collected_contacts'] = 'collected_contacts';

/* Is automatic_addressbook enabled or disabled by default for users? */
$config['use_auto_abook'] = true;

/* Should automatic_addressbook be used for completion by default? */
$config['use_auto_abook_for_completion'] = true;

/* Show database addressbook even if they are empty */
$config['show_empty_database_addressbooks'] = false;

/* CardDAV database tables */
$config['db_table_carddav_server'] = 'carddav_server';
$config['db_table_carddav_contacts'] = 'carddav_contacts';
$config['db_table_carddav_contactgroups'] = 'carddav_contactgroups';
$config['db_table_carddav_contactgroupmembers'] = 'carddav_contactgroupmembers';

/* CardDAV database tables sequences (PostgreSQL) */
$config['db_sequence_carddav_server'] = 'carddav_server_carddav_server_id_seq';
$config['db_sequence_carddav_contacts'] = 'carddav_contacts_carddav_contact_id_seq';
$config['db_sequence_carddav_contactgroups'] = 'carddav_contactgroups_contactgroup_id_seq';

/* Debug (extended logging) */
$config['carddav_debug'] = false;

/* Prevent users from editing defaults */
$config['carddav_protect'] = false;

/* Max CardDAVs */
$config['max_carddavs'] = 5;

/* Sync CardDAVs every (x) minutes automatically (0 = on login only) */
$config['sync_carddavs_interval'] = 5;

/* Host where passwords are in sync with IMAP passwords */
$config['carddav_synced_passwords'] = array(
  'calendar.maildev.mgtech.in',
);

/* Default CardDAV addressbooks
   placeholders: %u  = $_SESSiON['username'],
                  %su = local part of $_SESSION['username'],
                 %p  = $_SESSION['password']
*/
$config['def_carddavs'] = array(
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

/* HINT
   By default %u is replaced by rcmail::get_instance()->user->data['username'] (eq. to $_SESSION['username']).
   If it does not fit your needs, this is the right place to replace %u by whatever you need.
   Here follows an example how to strip off the domain part of the user
*/

/*
if(function_exists('get_input_value')){
  $user = explode('@', get_input_value('_user', RCUBE_INPUT_POST), 2);
  $user = $user[0];
  foreach($config['def_carddavs'] as $key => $props){
    if($config['def_carddavs'][$key]['user'] == '%u'){
      $config['def_carddavs'][$key]['user'] = $user;
      $config['def_carddavs'][$key]['url'] = str_replace('%u', $user, $config['def_carddavs'][$key]['url']);
    }
  }
}
*/

?>