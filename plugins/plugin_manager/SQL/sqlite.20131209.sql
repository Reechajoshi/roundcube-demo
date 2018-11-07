CREATE TABLE 'plugin_manager_tmp' (
  'id' INTEGER NOT NULL PRIMARY KEY,
  'user_id' INT(10) NOT NULL,
  'conf' TEXT NOT NULL,
  'value' TEXT,
  'type' TEXT
) ;

INSERT INTO plugin_manager_tmp (
  'id',
  'user_id',
  'conf',
  'value',
  'type'
)
SELECT
  'id',
  'user_id',
  'conf',
  'value',
  'type'
FROM plugin_manager;

DROP TABLE plugin_manager;

CREATE TABLE 'plugin_manager' (
  'id' INTEGER NOT NULL PRIMARY KEY,
  'conf' TEXT NOT NULL,
  'value' TEXT,
  'type' TEXT
) ;

INSERT INTO plugin_manager (
  'id',
  'conf',
  'value',
  'type'
)
SELECT
  'id',
  'conf',
  'value',
  'type'
FROM plugin_manager_tmp;

DROP TABLE plugin_manager_tmp;

UPDATE 'system' SET value='initial|20131209' WHERE name='myrc_plugin_manager';