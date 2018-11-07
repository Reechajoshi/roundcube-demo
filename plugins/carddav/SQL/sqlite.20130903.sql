CREATE TABLE 'carddav_contacts_tmp' ( 
  'carddav_contact_id' INTEGER NOT NULL PRIMARY KEY ,
  'carddav_server_id' INT ( 10 ) NOT NULL ,
  'user_id' INT ( 10 ) NOT NULL ,
  'etag' VARCHAR ( 255 ) NOT NULL ,
  'last_modified' VARCHAR ( 128 ) NOT NULL ,
  'vcard_id' VARCHAR ( 255 ) NOT NULL DEFAULT "" ,
  'vcard' LONGTEXT NOT NULL ,
  'words' TEXT ,
  'firstname' VARCHAR ( 128 ) ,
  'surname' VARCHAR ( 128 ) ,
  'name' VARCHAR ( 255 ) ,
  'email' VARCHAR ( 255 ) ,
  CONSTRAINT 'carddav_contacts_ibfk_1' FOREIGN KEY ('carddav_server_id') REFERENCES 'carddav_server' ('carddav_server_id') ON DELETE CASCADE
) ;
INSERT INTO carddav_contacts_tmp (
  'carddav_contact_id',
  'carddav_server_id',
  'user_id',
  'etag',
  'last_modified',
  'vcard_id',
  'words',
  'firstname',
  'surname',
  'name',
  'email'
)
SELECT
  'carddav_contact_id',
  'carddav_server_id',
  'user_id',
  'etag',
  'last_modified',
  'vcard_id',
  'words',
  'firstname',
  'surname',
  'name',
  'email'
FROM carddav_contacts ;
DROP TABLE 'carddav_contacts' ;
CREATE TABLE 'carddav_contacts' ( 
  'carddav_contact_id' INTEGER NOT NULL PRIMARY KEY ,
  'carddav_server_id' INT ( 10 ) NOT NULL ,
  'user_id' INT ( 10 ) NOT NULL ,
  'etag' VARCHAR ( 255 ) NOT NULL ,
  'last_modified' VARCHAR ( 128 ) NOT NULL ,
  'vcard_id' VARCHAR ( 255 ) NOT NULL DEFAULT "" ,
  'vcard' LONGTEXT NOT NULL ,
  'words' TEXT ,
  'firstname' VARCHAR ( 128 ) ,
  'surname' VARCHAR ( 128 ) ,
  'name' VARCHAR ( 255 ) ,
  'email' VARCHAR ( 255 ) ,
  CONSTRAINT 'carddav_contacts_ibfk_1' FOREIGN KEY ('carddav_server_id') REFERENCES 'carddav_server' ('carddav_server_id') ON DELETE CASCADE
) ;
INSERT INTO carddav_contacts (
  'carddav_contact_id',
  'carddav_server_id',
  'user_id',
  'etag',
  'last_modified',
  'vcard_id',
  'words',
  'firstname',
  'surname',
  'name',
  'email'
)
SELECT
  'carddav_contact_id',
  'carddav_server_id',
  'user_id',
  'etag',
  'last_modified',
  'vcard_id',
  'words',
  'firstname',
  'surname',
  'name',
  'email'
FROM carddav_contacts_tmp ;
DROP TABLE 'carddav_contacts_tmp' ;
UPDATE 'system' SET value='initial|20130903' WHERE name='myrc_carddav';