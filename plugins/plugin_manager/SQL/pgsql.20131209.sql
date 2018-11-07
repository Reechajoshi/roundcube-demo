ALTER TABLE plugin_manager DROP CONSTRAINT plugin_manager_user_id_fkey;
ALTER TABLE plugin_manager DROP user_id;
UPDATE "system" SET value='initial|20131209' WHERE name='myrc_plugin_manager';