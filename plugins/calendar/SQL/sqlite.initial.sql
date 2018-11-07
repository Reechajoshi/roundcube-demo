CREATE TABLE 'events' (
  'event_id' INTEGER,
  'uid' TEXT,
  'recurrence_id' INTEGER,
  'exdates' TEXT,
  'user_id' INTEGER NOT NULL DEFAULT '0',
  'start' INTEGER NOT NULL DEFAULT '0',
  'end' INTEGER NOT NULL DEFAULT '0',
  'expires' INTEGER NOT NULL DEFAULT '0',
  'rr' TEXT default NULL,
  'recurring' TEXT NOT NULL,
  'occurrences' INTEGER DEFAULT '0',
  'byday' TEXT,
  'bymonth' TEXT, 
  'bymonthday' TEXT,
  'summary' TEXT NOT NULL,
  'description' TEXT NOT NULL,
  'location' TEXT NOT NULL DEFAULT '',
  'categories' TEXT NOT NULL DEFAULT '',
  'group' TEXT,
  'caldav' TEXT,
  'url' TEXT,
  'timestamp' timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  'del' INTEGER NOT NULL DEFAULT '0',
  'reminder' INTEGER DEFAULT NULL,
  'reminderservice' TEXT,
  'remindermailto' TEXT,
  'remindersent' INTEGER DEFAULT NULL,
  'notified' INTEGER NOT NULL default '0',
  'client' TEXT,
 PRIMARY KEY ('event_id' ASC),
 CONSTRAINT 'user_id_fk_events' FOREIGN KEY ('user_id')
   REFERENCES 'users'('user_id') ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE 'events_cache' (
  'event_id' INTEGER,
  'uid' TEXT,
  'recurrence_id' INTEGER,
  'exdates' TEXT,
  'user_id' INTEGER NOT NULL DEFAULT '0',
  'start' INTEGER NOT NULL DEFAULT '0',
  'end' INTEGER NOT NULL DEFAULT '0',
  'expires' INTEGER NOT NULL DEFAULT '0',
  'rr' TEXT default NULL,
  'recurring' TEXT NOT NULL,
  'occurrences' INTEGER DEFAULT '0',
  'byday' TEXT,
  'bymonth' TEXT, 
  'bymonthday' TEXT,
  'summary' TEXT NOT NULL,
  'description' TEXT NOT NULL,
  'location' TEXT NOT NULL DEFAULT '',
  'categories' TEXT NOT NULL DEFAULT '',
  'group' TEXT,
  'caldav' TEXT,
  'url' TEXT,
  'timestamp' timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  'del' INTEGER NOT NULL DEFAULT '0',
  'reminder' INTEGER DEFAULT NULL,
  'reminderservice' TEXT,
  'remindermailto' TEXT,
  'remindersent' INTEGER DEFAULT NULL,
  'notified' INTEGER NOT NULL default '0',
  'client' TEXT,
 PRIMARY KEY ('event_id' ASC),
 CONSTRAINT 'user_id_fk_events_cache' FOREIGN KEY ('user_id')
   REFERENCES 'users'('user_id') ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE 'events_caldav' (
  'event_id' INTEGER,
  'uid' TEXT,
  'recurrence_id' INTEGER,
  'exdates' TEXT,
  'user_id' INTEGER NOT NULL DEFAULT '0',
  'start' INTEGER NOT NULL DEFAULT '0',
  'end' INTEGER NOT NULL DEFAULT '0',
  'expires' INTEGER NOT NULL DEFAULT '0',
  'rr' TEXT default NULL,
  'recurring' TEXT NOT NULL,
  'occurrences' INTEGER DEFAULT '0',
  'byday' TEXT,
  'bymonth' TEXT, 
  'bymonthday' TEXT,
  'summary' TEXT NOT NULL,
  'description' TEXT NOT NULL,
  'location' TEXT NOT NULL DEFAULT '',
  'categories' TEXT NOT NULL DEFAULT '',
  'group' TEXT,
  'caldav' TEXT,
  'url' TEXT,
  'timestamp' timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  'del' INTEGER NOT NULL DEFAULT '0',
  'reminder' INTEGER DEFAULT NULL,
  'reminderservice' TEXT,
  'remindermailto' TEXT,
  'remindersent' INTEGER DEFAULT NULL,
  'notified' INTEGER NOT NULL default '0',
  'client' TEXT,
 PRIMARY KEY ('event_id' ASC),
 CONSTRAINT 'user_id_fk_events_caldav' FOREIGN KEY ('user_id')
   REFERENCES 'users'('user_id') ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE reminders (
    reminder_id integer NOT NULL PRIMARY KEY,
    user_id integer NOT NULL,
    events integer DEFAULT NULL,
    cache integer DEFAULT NULL,
    caldav integer DEFAULT NULL,
    "type" text,
    props text,
    runtime integer NOT NULL
);

CREATE TABLE IF NOT EXISTS 'system' (
  name varchar(64) NOT NULL PRIMARY KEY,
  value text NOT NULL
);

INSERT INTO system (name, value) VALUES ('myrc_calendar', 'initial');

