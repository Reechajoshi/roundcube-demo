ALTER TABLE  `carddav_contacts`
  CHANGE  `etag`  `etag` VARCHAR( 255 ),
  CHANGE  `vcard_id`  `vcard_id` VARCHAR( 255 );
  
UPDATE `system` SET `value`='initial|20130903' WHERE `name`='myrc_carddav';