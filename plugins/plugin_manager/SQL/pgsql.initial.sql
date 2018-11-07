CREATE TABLE plugin_manager (
  id serial NOT NULL,
  user_id integer NOT NULL
	REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
  conf text NOT NULL,
  value text,
  type text,
  PRIMARY KEY (id)
);
CREATE INDEX ix_plugin_manager_user_id ON users (user_id);

CREATE TABLE IF NOT EXISTS "system" (
    name varchar(64) NOT NULL PRIMARY KEY,
    value text
);

INSERT INTO "system" (name, value) VALUES ('myrc_plugin_manager', 'initial');