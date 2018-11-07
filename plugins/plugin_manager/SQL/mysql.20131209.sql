ALTER TABLE  `plugin_manager` DROP FOREIGN KEY  `user_id_fk_plugin_manager` ;
ALTER TABLE  `plugin_manager` DROP  `user_id`;
ALTER TABLE  `plugin_manager` ENGINE=MyISAM;
UPDATE `system` SET `value`='initial|20131209' WHERE `name`='myrc_plugin_manager';