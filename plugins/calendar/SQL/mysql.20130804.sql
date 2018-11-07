ALTER TABLE  `events` ADD  `component` VARCHAR( 6 ) NOT NULL DEFAULT  'vevent' AFTER  `event_id`;
ALTER TABLE  `events` ADD  `organizer` VARCHAR( 255 ) DEFAULT NULL AFTER `url`;
ALTER TABLE  `events` ADD  `attendees` VARCHAR( 255 ) DEFAULT NULL AFTER `organizer`;
ALTER TABLE  `events` ADD  `status` VARCHAR( 25 ) DEFAULT NULL AFTER `url`;
ALTER TABLE  `events` ADD  `due` INT( 11 ) NOT NULL DEFAULT '0' AFTER `end`;
ALTER TABLE  `events` ADD  `complete` INT( 3 ) NOT NULL DEFAULT '0' AFTER `due`;
ALTER TABLE  `events` ADD  `priority` INT( 1 ) NOT NULL DEFAULT '0' AFTER `uid`;
ALTER TABLE  `events` DROP  `group`;
ALTER TABLE  `events_cache` ADD  `component` VARCHAR( 6 ) NOT NULL DEFAULT  'vevent' AFTER  `event_id`;
ALTER TABLE  `events_cache` ADD  `organizer` VARCHAR( 255 ) DEFAULT NULL AFTER `url`;
ALTER TABLE  `events_cache` ADD  `attendees` VARCHAR( 255 ) DEFAULT NULL AFTER `organizer`;
ALTER TABLE  `events_cache` ADD  `status` VARCHAR( 25 ) DEFAULT NULL AFTER `url`;
ALTER TABLE  `events_cache` ADD  `due` INT( 11 ) NOT NULL DEFAULT '0' AFTER `end`;
ALTER TABLE  `events_cache` ADD  `complete` INT( 3 ) NOT NULL DEFAULT '0' AFTER `due`;
ALTER TABLE  `events_cache` ADD  `priority` INT( 1 ) NOT NULL DEFAULT '0' AFTER `uid`;
ALTER TABLE  `events_cache` DROP  `group`;
ALTER TABLE  `events_caldav` ADD  `component` VARCHAR( 6 ) NOT NULL DEFAULT  'vevent' AFTER  `event_id`;
ALTER TABLE  `events_caldav` ADD  `organizer` VARCHAR( 255 ) DEFAULT NULL AFTER `url`;
ALTER TABLE  `events_caldav` ADD  `attendees` VARCHAR( 255 ) DEFAULT NULL AFTER `organizer`;
ALTER TABLE  `events_caldav` ADD  `status` VARCHAR( 25 ) DEFAULT NULL AFTER `url`;
ALTER TABLE  `events_caldav` ADD  `due` INT( 11 ) NOT NULL DEFAULT '0' AFTER `end`;
ALTER TABLE  `events_caldav` ADD  `complete` INT( 3 ) NOT NULL DEFAULT '0' AFTER `due`;
ALTER TABLE  `events_caldav` ADD  `priority` INT( 1 ) NOT NULL DEFAULT '0' AFTER `uid`;
ALTER TABLE  `events_caldav` DROP  `group`;
UPDATE `system` SET `value`='initial|20130512|20130804' WHERE `name`='myrc_calendar';