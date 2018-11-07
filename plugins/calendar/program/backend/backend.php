<?php

abstract class Backend
{
  /**
   * Search Events
   *
   * @param  string Search string
   * @param  string Label for default category
   * @access public
   */
  abstract public function searchEvents(
    $str,
    $label
  );
  
  /**
   * Get ctags
   *
   * @access public
   */
  abstract public function getCtags();

  /**
   * Revolve Reminder
   *
   * @param  integer Reminder's Identifier
   * @param  integer Event's Identifier
   * @param  integer Timestamp
   * @access public
   */
  abstract public function removeReminder(
    $id,
    $event_id,
    $ts
  );
  
  /**
   * Get Reminders
   *
   * @param  integer Unix timestamp
   * @param  string type
   * @access public
   */
  abstract function getReminders(
    $start,
    $type='popup'
  );
  
  /**
   * Schedule Reminders
   *
   * @param  array Event
   * @access public
   */
  abstract function scheduleReminders($event);
  
  /**
   * Export Events in ical format
   *
   * @param  string category
   * @access public
   */
  abstract function exportEvents($categories=false);
  
  /**
   * Get events from CalDAV
   *
   * @param  integer timerange start
   * @param  integer timerange end
   * @param  string  category
   * @param  string  type (event/alaram)
   */
  abstract public function replicateEvents(
    $estart,
    $eend,
    $category=false,
    $type='events'
  );
  
  /**
   * Get events from database
   *
   * @param  integer timerange start
   * @param  integer timerange end
   * @param  array   labels
   * @param  string  category
   * @param  mixed   filter (db field)
   * @param  mixed   client
   * @access public
   */
  abstract public function getEvents(
    $estart,
    $eend,
    $labels=array(),
    $category=false,
    $filter=false,
    $client=false
  );

  /**
   * Add a single event to the database
   *
   * @param  integer Event identifier
   * @param  integer Event's start
   * @param  integer Event's end
   * @param  string  Event's summary
   * @param  string  Event's description
   * @param  string  Event's location
   * @param  string  Event's category
   * @param  integer Event allDay state
   * @param  string  ToDo's status
   * @param  string  ToDo's priority
   * @param  integer ToDo's due date
   * @param  integer ToDo's completeness percent
   * @param  integer Event recur interval in seconds
   * @param  string  Event's expiration date
   * @param  string  Event's occurrences
   * @param  string  Event's BYDAY Recurring rule
   * @param  string  Event's BYMONTH Recurring rule
   * @param  string  Event's BYMONTHDAY Recurring rule
   * @param  string  Event's recurrence identifier
   * @param  string  Event's excluded dates
   * @param  integer Event's Remind before beginning,
   * @param  string  Event's Reminder type
   * @param  string  Event's Reminder recipient
   * @param  string  Event's unique identifier
   * @param  mixed   Client flag
   * @param  bool    Timezone adjustment
   * @access public
   */
  /* abstract public function newEvent(
    $start,
    $end,
    $summary,
    $description,
    $location,
    $categories,
    $allDay,
    $status,
    $priority,
    $due,
    $component,
    $recur,
    $expires,
    $occurrences,
    $byday=false,
    $bymonth=false,
    $bymonthday=false,
    $recurrence_id=false,
    $exdates=false,
    $reminderbefore=false,
    $remindertype=false,
    $remindermailto=false,
    $uid=false,
    $client=false,
    $adjust=true,
    $component='vevent',
	$invite_members_username,
	$invite_members_email
  ); */
  
  // ATTENDEES MODIFICATION
  abstract public function newEvent(
    $start,
    $end,
    $summary,
    $description,
    $location,
    $categories,
    $allDay,
    $status,
    $priority,
    $due,
    $component,
    $recur,
    $expires,
    $occurrences,
    $byday=false,
    $bymonth=false,
    $bymonthday=false,
    $recurrence_id=false,
    $exdates=false,
    $reminderbefore=false,
    $remindertype=false,
    $remindermailto=false,
    $uid=false,
    $client=false,
    $adjust=true,
    $component='vevent',
	$unselected_attendee_username,
    $unselected_attendee_email,
    $selected_attendee_username,
    $selected_attendee_email,
	$attendee_role_array,
	$all_day_event
  );
  
  /**
   * Add a new calendar
   *
   * @param  array Account
   * @param  string Displayname
   * @param  string Color
   */
  abstract public function newCalendar(
    $account,
    $displayname,
    $color
  );

  /**
   * Edit a single event
   *
   * @param  integer Event identifier
   * @param  integer Event's start
   * @param  integer Event's end
   * @param  string  ToDo's status
   * @param  integer ToDo's priority
   * @param  integer ToDo's due data
   * @param  integer ToDo's completeness percentage
   * @param  string  Event's summary
   * @param  string  Event's location
   * @param  string  Event's category
   * @param  integer Event recur interval in seconds
   * @param  string  Event's expiration date
   * @param  string  Event's occurrences
   * @param  string  Event's BYDAY Recurring rule
   * @param  string  Event's BYMONTH Recurring rule
   * @param  string  Event's BYMONTHDAY Recurring rule
   * @param  integer Event's Remind before beginning,
   * @param  string  Event's Reminder type
   * @param  string  Event's Reminder recipient
   * @param  integer Event allDay state
   * @param  string  Event's old category
   * @param  mixed   Event's CalDAV properties (href, etag, uid)
   * @access public
   */
   abstract public function editEvent(
    $id,
    $start,
    $end,
    $status,
    $priority,
    $due,
    $complete,
    $summary,
    $description,
    $location,
    $categories,
    $recur, $expires,
    $occurrences,
    $byday=false,
    $bymonth=false,
    $bymonthday=false,
	$recurrence_id=false, // new
	$exdates=false, // new
    $reminderbefore=false,
    $remindertype=false,
    $remindermailto=false,
    $allDay=false,
    $old_categories=false,
    $caldav = false,
	$adjust = false, // new
	$component, // new
	$unselected_attendee_username, // new
	$unselected_attendee_email, // new
	$selected_attendee_username, // new
	$selected_attendee_email,	// new
	$attendee_role_array,
	$all_day_event
  );
  /* abstract public function editEvent(
    $id,
    $start,
    $end,
    $status,
    $priority,
    $due,
    $complete,
    $summary,
    $description,
    $location,
    $categories,
    $recur, $expires,
    $occurrences,
    $byday=false,
    $bymonth=false,
    $bymonthday=false,
    $reminderbefore=false,
    $remindertype=false,
    $remindermailto=false,
    $allDay=false,
    $old_categories=false,
    $caldav = false,
  ); */

  /**
   * Move a single event
   *
   * @param  integer Event identifier
   * @param  integer Event's new start
   * @param  integer Event's new end
   * @param  integer Event allDay state
   * @param  integer Event's reminder
   * @access public
   */
  abstract public function moveEvent(
    $id,
    $start,
    $end,
    $allDay,
    $reminder
  );

  /**
   * Resize a single event
   *
   * @param  integer Event identifier
   * @param  integer Event's new start
   * @param  integer Event's new end
   * @param  integer Event's reminder
   * @access public
   */
  abstract public function resizeEvent(
    $id,
    $start,
    $end,
    $reminder
  );
  
  /**
   * Remove a single event from the database
   * 
   * @param  integer Event identifier
   * @param  string  Event's category
   * @access public
   */
  abstract public function removeEvent(
    $id,
    $categories=false
  );
  
  /**
   * Delete a calendar
   *
   * @param  array Account
   */
  abstract public function removeCalendar(
    $account
  );
  
  /**
   * Remove all events from the database
   * 
   * @param  integer mode: 0 = truncate, 1 = set del flag, 2 = restore
   * @access public
   */
  abstract public function truncateEvents(
    $mode=0
  );
  
  /**
   * Remove all entries of a user from the databases
   * 
   * @access public
   */
  abstract public function uninstall(
  );
  
  /**
   * Delete events permanently from the database
   * 
   * @access public
   */
  abstract public function purgeEvents();
  
  /**
   * Remove duplicate entries
   *
   * @param  string Database table
   * @access public
   */
  abstract public function removeDuplicates(
    $table = 'events'
  );

  /**
   * Remove Timestamps
   *
   * @access public
   */
  abstract public function removeTimestamps();
  
  /**
   * Get single event form the database
   *
   * @param  integer Event identifier
   * @access public
   */  
  abstract public function getEvent(
    $eventid
  );
  
  /**
   * Get single event form the database
   *
   * @param  string uid
   * @param  integer Event recurrence identifier
   * @access public
   */  
  abstract public function getEventByUID(
    $uid,
    $recurrence_id=0
  );
  
  /**
   * Get events form the database by UID
   *
   * @param  string uid
   * @access public
   */  
  abstract public function getEventsByUID(
    $uid
  );
}
?>