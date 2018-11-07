CREATE TABLE collected_contacts (
  contact_id integer NOT NULL PRIMARY KEY,
  user_id integer NOT NULL,
  changed datetime NOT NULL default '0000-00-00 00:00:00',
  del tinyint NOT NULL default '0',
  name varchar(128) NOT NULL default '',
  email text NOT NULL default '',
  firstname varchar(128) NOT NULL default '',
  surname varchar(128) NOT NULL default '',
  vcard text NOT NULL default '',
  words text NOT NULL default ''
);

CREATE INDEX ix_collected_contacts_user_id ON collected_contacts(user_id, del);
