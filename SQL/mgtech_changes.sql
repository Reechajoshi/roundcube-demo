create table out_of_office (
	emailaddr varchar(50) not null,
	enabled int(1) default 0,
	subject varchar(100) default '',
	message text default '',
	PRIMARY KEY(emailaddr),
	INDEX(enabled)
);

create table blocked_emails (
	emailaddr varchar(50) not null,
	block_count int(3) not null,
	header varchar(10) default '',
	filter varchar(50) default '',
	PRIMARY KEY(emailaddr,block_count)
);


create table forward_rules ( /* created on 2/9/13 */
	emailaddr varchar(50) not null,
	fw_rules_count int(3) not null,
	header varchar(10) default '',
	filter varchar(50) default '',
	forward_to_email varchar(50) not null,
	PRIMARY KEY( emailaddr, fw_rules_count)
); 

create table custom_directive (
	emailaddr varchar(50) not null,
	enabled int(1) default 0,
	description text default '',
	INDEX(enabled),
	PRIMARY KEY(emailaddr)
);

create table folder_rule(
	emailaddr varchar(50) not null,
	folder_name varchar(50) not null,
	enabled int(1) default 0,
	filter varchar(50) default '',
	filter_match varchar(100) default '',
	INDEX(enabled),
	PRIMARY KEY(emailaddr, folder_name)
);

rename table deleted_users TO user_deleted;

create table user_admin(
	username varchar(200) not null,
	mydomain varchar(50) not null,
	email varchar(200) not null,
	PRIMARY KEY( email )
);


/* for manage admin */
alter table user_admin
drop primary key

alter table user_admin 
add column managed_domain varchar(200) not null;
alter table user_admin 
add column manged_domain_is_selected int(1) not null default 0;

alter table user_admin
add primary key ( email, managed_domain );


alter table folder_rule modify filter int(1) default 0;


/* for calendar plugin */
CREATE TABLE `events` (
	`event_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`uid` text,
	`recurrence_id` int(10) DEFAULT NULL,
	`exdates` text,
	`user_id` int(10) unsigned NOT NULL DEFAULT '0',
	`start` int(11) NOT NULL DEFAULT '0',
	`end` int(11) NOT NULL DEFAULT '0',
	`expires` int(11) NOT NULL DEFAULT '0',
	`rr` varchar(1) DEFAULT NULL,
	`recurring` text NOT NULL,
	`occurrences` int(11) DEFAULT '0',
	`byday` text,
	`bymonth` text,
	`bymonthday` text,
	`summary` varchar(255) NOT NULL,
	`description` text NOT NULL,
	`location` varchar(255) NOT NULL DEFAULT '',
	`categories` varchar(255) NOT NULL DEFAULT '',
	`group` text,
	`caldav` text,
	`url` text,
	`timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	`del` int(1) NOT NULL DEFAULT '0',
	`reminder` int(10) DEFAULT NULL,
	`reminderservice` text,
	`remindermailto` text,
	`remindersent` int(10) DEFAULT NULL,
	`notified` int(1) NOT NULL DEFAULT '0',
	`client` text,
	PRIMARY KEY (`event_id`),
	KEY `user_id_fk_events` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE events_cache (
	`event_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`uid` text CHARACTER SET utf8,
	`recurrence_id` int(10) DEFAULT NULL,
	`exdates` text CHARACTER SET utf8,
	`user_id` int(10) unsigned NOT NULL DEFAULT '0',
	`start` int(11) NOT NULL DEFAULT '0',
	`end` int(11) NOT NULL DEFAULT '0',
	`expires` int(11) NOT NULL DEFAULT '0',
	`rr` varchar(1) CHARACTER SET utf8 DEFAULT NULL,
	`recurring` text CHARACTER SET utf8 NOT NULL,
	`occurrences` int(11) DEFAULT '0',
	`byday` text CHARACTER SET utf8,
	`bymonth` text CHARACTER SET utf8,
	`bymonthday` text CHARACTER SET utf8,
	`summary` varchar(255) CHARACTER SET utf8 NOT NULL,
	`description` text CHARACTER SET utf8 NOT NULL,
	`location` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
	`categories` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
	`group` text CHARACTER SET utf8,
	`caldav` text CHARACTER SET utf8,
	`url` text COLLATE utf8_unicode_ci,
	`timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	`del` int(1) NOT NULL DEFAULT '0',
	`reminder` int(10) DEFAULT NULL,
	`reminderservice` text CHARACTER SET utf8,
	`remindermailto` text CHARACTER SET utf8,
	`remindersent` int(10) DEFAULT NULL,
	`notified` int(1) NOT NULL DEFAULT '0',
	`client` text CHARACTER SET utf8,
	PRIMARY KEY (`event_id`),
	KEY `user_id_fk_events_cache` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE `events_caldav` (
	`event_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`uid` text,
	`recurrence_id` int(10) DEFAULT NULL,
	`exdates` text,
	`user_id` int(10) unsigned NOT NULL DEFAULT '0',
	`start` int(11) DEFAULT '0',
	`end` int(11) DEFAULT '0',
	`expires` int(11) DEFAULT '0',
	`rr` varchar(1) DEFAULT NULL,
	`recurring` text NOT NULL,
	`occurrences` int(11) DEFAULT '0',
	`byday` text,
	`bymonth` text,
	`bymonthday` text,
	`summary` varchar(255) NOT NULL,
	`description` text NOT NULL,
	`location` varchar(255) NOT NULL DEFAULT '',
	`categories` varchar(255) NOT NULL DEFAULT '',
	`group` text,
	`caldav` text,
	`url` text,
	`timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
	`del` int(1) NOT NULL DEFAULT '0',
	`reminder` int(10) DEFAULT NULL,
	`reminderservice` text,
	`remindermailto` text,
	`remindersent` int(10) DEFAULT NULL,
	`notified` int(1) NOT NULL DEFAULT '0',
	`client` text,
	PRIMARY KEY (`event_id`),
	KEY `user_id_fk_events_caldav` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE `reminders` (
	`reminder_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`user_id` int(10) unsigned NOT NULL,
	`events` int(10) unsigned DEFAULT NULL,
	`cache` int(10) unsigned DEFAULT NULL,
	`caldav` int(10) unsigned DEFAULT NULL,
	`type` text,
	`props` text,
	`runtime` int(11) NOT NULL,
	PRIMARY KEY (`reminder_id`),
	KEY `reminders_ibfk_1` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2299 ;

INSERT INTO `system` (name, value) VALUES ('myrc_calendar', 'initial');

ALTER TABLE `events`
	ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `events_cache`
	ADD CONSTRAINT `events_cache_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
	
ALTER TABLE `events_caldav`
	ADD CONSTRAINT `events_caldav_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
	
ALTER TABLE `reminders`
	ADD CONSTRAINT `reminders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
	
ALTER TABLE  `events` ADD  `tzname` VARCHAR( 255 ) NULL DEFAULT  'UTC' AFTER  `expires` ;

ALTER TABLE  `events_cache` ADD  `tzname` VARCHAR( 255 ) NULL DEFAULT  'UTC' AFTER  `expires` ;

ALTER TABLE  `events_caldav` ADD  `tzname` VARCHAR( 255 ) NULL DEFAULT  'UTC' AFTER  `expires` ;

UPDATE `system` SET `value`='initial|20130512' WHERE `name`='myrc_calendar';ALTER TABLE  `events` ADD  `component` VARCHAR( 6 ) NOT NULL DEFAULT  'vevent' AFTER  `event_id`;

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



/* 26-December-13 */
/* Users for sabredav, webmail, mailx database created. And Privileges granted */
/* grant all on database.* to user@* identified by 'password' */
GRANT ALL ON mailx.* TO 'mailx'@'localhost' IDENTIFIED BY 'mailx';
GRANT ALL ON webmail.* TO 'webmail'@'localhost' IDENTIFIED BY 'webmail';
GRANT ALL ON calendar.* TO 'calendar'@'localhost' IDENTIFIED BY 'calendar';
GRANT ALL ON webmail.* TO 'mailx'@'localhost' IDENTIFIED BY 'mailx';

drop user 'mailx'@'localhost';
drop user 'webmail'@'localhost';
drop user 'calendar'@'localhost';


create user 'mailx'@'localhost' IDENTIFIED BY 'mailx';
create user 'webmail'@'localhost' IDENTIFIED BY 'webmail';
create user 'calendar'@'localhost' IDENTIFIED BY 'calendar';

flush privileges;


/* 22-01-14 Out Of Office Modification */
ALTER TABLE out_of_office 
ADD COLUMN oof_rule_count int(3) not null default 0,  
ADD COLUMN header int(1) default 0,
ADD COLUMN filter varchar(50) default '';

ALTER TABLE out_of_office
DROP PRIMARY KEY, ADD PRIMARY KEY (emailaddr, oof_rule_count);



/* 27-01-14 Invite Attendees */
create table events_caldav_invitees(
	event_id int(11) not null,
	username varchar(50) not null default '',
	email varchar(100) not null default '',
	invite_sent int(1) not null default 0,
	PRIMARY KEY ( event_id, username )
);

/* 14-02-13 Inviteee Attedees add Role */
ALTER TABLE events_caldav_invitees
ADD COLUMN role varchar(50) not null default '';



/* FOR ALL DAY EVENTS */
ALTER TABLE events_caldav 
ADD COLUMN all_day INT( 1 ) NOT NULL DEFAULT 0;


/* FOR FOLDER SHARING */
create table folder_sharing (
    user_id int(10) unsigned NOT NULL DEFAULT '0',
    folder_name varchar(50) NOT NULL DEFAULT '',
    shared_with_email varchar(128) NOT NULL DEFAULT ''
) ENGINE=InnoDB;