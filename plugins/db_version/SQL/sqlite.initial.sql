PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS myrc (
	id serial NOT NULL,
	user_id integer NOT NULL,
	PRIMARY KEY (id),
	FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE INDEX myrc_idx ON myrc (user_id);

DROP TABLE myrc;