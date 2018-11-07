CREATE TABLE IF NOT EXISTS autoban (
	id serial NOT NULL,
	username varchar(128) NOT NULL,
	ip varchar(15) NOT NULL,
	ts varchar(19) NOT NULL
	PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS addressbooks (
	id serial NOT NULL,
	principaluri varchar(255) DEFAULT NULL,
	displayname varchar(255) DEFAULT NULL,
	uri varchar(200) DEFAULT NULL,
	description text,
	ctag integer NOT NULL DEFAULT 1,
	PRIMARY KEY (id),
	UNIQUE (principaluri, uri)
);

CREATE TABLE IF NOT EXISTS calendars (
	id serial NOT NULL,
	principaluri varchar(100) DEFAULT NULL,
	displayname varchar(100) DEFAULT NULL,
	uri varchar(200) DEFAULT NULL,
	ctag integer NOT NULL DEFAULT 0,
	description text,
	calendarorder integer NOT NULL DEFAULT 0,
	calendarcolor varchar(10) DEFAULT NULL,
	timezone text,
	components varchar(20) DEFAULT NULL,
	transparent integer NOT NULL DEFAULT 0,
	PRIMARY KEY (id),
	UNIQUE (principaluri, uri)
);

CREATE TABLE IF NOT EXISTS calendarobjects (
	id serial NOT NULL,
	etag varchar(32) DEFAULT NULL,
	size integer NOT NULL,
	componenttype varchar(8) DEFAULT NULL,
	firstoccurence integer DEFAULT NULL,
	lastoccurence integer DEFAULT NULL,
	calendardata bytea,
	uri varchar(200) DEFAULT NULL,
	calendarid integer NOT NULL
		REFERENCES calendars (id) ON DELETE CASCADE ON UPDATE CASCADE,
	lastmodified integer DEFAULT NULL,
	PRIMARY KEY (id),
	UNIQUE (calendarid, uri)
);

CREATE TABLE IF NOT EXISTS cards (
	id serial NOT NULL,
	addressbookid integer NOT NULL,
	carddata bytea,
	uri varchar(200) DEFAULT NULL,
	lastmodified integer DEFAULT NULL,
	PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS groupmembers (
	id serial NOT NULL,
	principal_id integer NOT NULL,
	member_id integer NOT NULL,
	PRIMARY KEY (id),
	UNIQUE (principal_id, member_id)
);

CREATE TABLE IF NOT EXISTS locks (
	id serial NOT NULL,
	owner varchar(100) DEFAULT NULL,
	timeout integer DEFAULT NULL,
	created integer DEFAULT NULL,
	token varchar(100) DEFAULT NULL,
	scope smallint DEFAULT NULL,
	depth smallint DEFAULT NULL,
	uri varchar(1000) DEFAULT NULL,
	PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS principals (
	id serial NOT NULL,
	uri varchar(200) NOT NULL,
	email varchar(80) DEFAULT NULL,
	displayname varchar(80) DEFAULT NULL,
	vcardurl varchar(80) DEFAULT NULL,
	PRIMARY KEY (id),
	UNIQUE (uri)
);

CREATE TABLE IF NOT EXISTS users (
	id serial NOT NULL,
	rcube_id integer DEFAULT NULL,
	username varchar(50) DEFAULT NULL,
	digesta1 varchar(32) DEFAULT NULL,
	PRIMARY KEY (id),
	UNIQUE (username)
);

CREATE TABLE IF NOT EXISTS users_cal_rw (
	id serial NOT NULL,
	rcube_id integer DEFAULT NULL,
	username varchar(50) DEFAULT NULL,
	digesta1 varchar(32) DEFAULT NULL,
	PRIMARY KEY (id),
	UNIQUE (username)
);

CREATE TABLE IF NOT EXISTS users_cal_r (
	id serial NOT NULL,
	rcube_id integer DEFAULT NULL,
	username varchar(50) DEFAULT NULL,
	digesta1 varchar(32) DEFAULT NULL,
	PRIMARY KEY (id),
	UNIQUE (username)
);

CREATE TABLE IF NOT EXISTS users_abook_rw (
	id serial NOT NULL,
	rcube_id integer DEFAULT NULL,
	username varchar(50) DEFAULT NULL,
	digesta1 varchar(32) DEFAULT NULL,
	PRIMARY KEY (id),
	UNIQUE (username)
);

CREATE TABLE IF NOT EXISTS users_abook_r (
	id serial NOT NULL,
	rcube_id integer DEFAULT NULL,
	username varchar(50) DEFAULT NULL,
	digesta1 varchar(32) DEFAULT NULL,
	PRIMARY KEY (id),
	UNIQUE (username)
);

