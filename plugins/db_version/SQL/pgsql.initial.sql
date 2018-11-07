CREATE TABLE IF NOT EXISTS myrc (
	id serial NOT NULL,
	user_id integer NOT NULL,
	PRIMARY KEY (id)
);

ALTER TABLE myrc
	ADD CONSTRAINT myrc_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE INDEX myrc_idx ON myrc (user_id);

DROP TABLE myrc;