ALTER TABLE  carddav_contacts
  ALTER etag TYPE VARCHAR( 255 ),
  ALTER vcard_id TYPE VARCHAR( 255 ) ;
  
UPDATE "system" SET value='initial|20130903' WHERE name='myrc_carddav';