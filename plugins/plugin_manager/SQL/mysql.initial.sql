CREATE TABLE IF NOT EXISTS `plugin_manager` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `conf` text NOT NULL,
  `value` text,
  `type` text,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `user_id_index` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `system` (
 `name` varchar(64) NOT NULL,
 `value` mediumtext,
 PRIMARY KEY(`name`)
);

INSERT INTO `system` (name, value) VALUES ('myrc_plugin_manager', 'initial');

ALTER TABLE `plugin_manager`
  ADD CONSTRAINT `user_id_fk_plugin_manager` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
