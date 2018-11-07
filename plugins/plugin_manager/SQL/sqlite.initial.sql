CREATE TABLE IF NOT EXISTS 'plugin_manager' ( 'id' integer NOT NULL PRIMARY KEY AUTOINCREMENT, 'user_id' INT(10) NOT NULL, 'conf' TEXT NOT NULL, 'value' TEXT, 'type' TEXT,
    CONSTRAINT 'plugin_manager_ibfk_1' FOREIGN KEY ('user_id') REFERENCES 'users'
      ('user_id') ON DELETE
      CASCADE ON UPDATE CASCADE );
      
CREATE TABLE IF NOT EXISTS 'system' (
  name varchar(64) NOT NULL PRIMARY KEY,
  value text NOT NULL
);

INSERT INTO system (name, value) VALUES ('myrc_plugin_manager', 'initial');