/**
 *
 * access-mysql.sql: A collection of SQL functions to help manage user access
 * Requires MySQL >= 5.0.10
 *
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details.
 * 
 * author: Hannes Forsgård <hannes.forsgard@fripost.org>
 *
 */
DELIMITER //


/**
 * Returns 1 if user (identified by uname and csv ugrps) is allowed to perfom
 * action given owner, group and permissions settings.
 *
 * Permissions are unix style permission bits, eg. 0777 or 0750
 * The permission fields should be definied as SMALLINT(2).
 *
 * Users named 'root' or beloning to grop 'root' are always allowed
 *
 * SELECT * FROM foo WHERE isAllowed('r', `owner`, `group`, `mode`, 'user', 'foo,bar');
 * UPDATE foo SET a='b' WHERE isAllowed('w', `owner`, `group`, `mode`, 'user', 'grpup1,group2');
 * DELETE FROM foo WHERE isAllowed('w', `owner`, `group`, `mode`, 'user', 'grpup1,group2');
 *
 * Since MySQL does not support error handling in stored procedures as of yet
 * it is not possible to now when a function returns false. For example may a
 * permissions bit not be set either because there is no row satisfying the
 * where clause, or because the user does not have permissions to change the bit.
 * This can not be circumvented at the moment.
 *
 * @param CHAR(1) action (r)ead, (w)rite or e(x)ecute
 * @param VARCHAR(10) owner
 * @param VARCHAR(10) owner_grp
 * @param CHAR(3) access_mode
 * @param VARCHAR(10) uname User name
 * @param VARCHAR(200) ugrps User groups, csv list
 * @return TINYINT(1) 1 if action is allowed, 0 otherwise.
 */
DROP FUNCTION IF EXISTS isAllowed//
CREATE FUNCTION isAllowed( action CHAR(1), owner VARCHAR(10), owner_grp VARCHAR(10), access_mode SMALLINT, uname VARCHAR(10), ugrps VARCHAR(200) ) RETURNS TINYINT(1)
	DETERMINISTIC
	COMMENT 'Returns 1 if action is allowed, 0 otherwise.'
	LANGUAGE SQL
	SQL SECURITY INVOKER
	BEGIN
		DECLARE mask SMALLINT(2) DEFAULT 7;

		-- Roots are always allowed
		IF uname = 'root' OR in_csv('root', ugrps) THEN
			RETURN 1;
		END IF;

		-- Set mask based on action
		IF action = 'r' THEN
			SET mask = 4;
		ELSEIF action = 'w' THEN
			SET mask = 2;
		ELSEIF action = 'x' THEN
			SET mask = 1;
		END IF;

		-- Shift mask based on owner and group ownership
		IF uname = owner THEN
			SET mask = mask << 6;
		ELSEIF in_csv(owner_grp, ugrps) THEN
			SET mask = mask << 3;
		END IF;

		-- Perform check
		IF access_mode & mask = mask THEN
			RETURN 1;
		ELSE
			RETURN 0;
		END IF;
	END//



/**
 * Returns 1 if needle is persent in haystack. Haystack is csv string
 * using , as delimiter.
 * @param VARCHAR(100) needle
 * @param TEXT haystack
 * @return TINYINT(1)
 */
DROP FUNCTION IF EXISTS in_csv//
CREATE FUNCTION in_csv( needle VARCHAR(100), haystack TEXT ) RETURNS TINYINT(1)
	DETERMINISTIC
	COMMENT 'True if needle exists in csv haystack'
	LANGUAGE SQL
	SQL SECURITY INVOKER
	BEGIN
		-- alter delimiter here if needed 
		DECLARE delim CHAR(1) DEFAULT ',';

		DECLARE pos_start INT DEFAULT 1;
		DECLARE pos_end INT;
		DECLARE len INT;
		DECLARE teststr VARCHAR(100);

		SET haystack = CONCAT(haystack, delim);

		haystackloop: LOOP
			SET pos_end = LOCATE(delim, haystack, pos_start);
			IF pos_end = 0 THEN
				RETURN 0;
			END IF;
			SET len = pos_end - pos_start;
			SET teststr = SUBSTR(haystack, pos_start, len);
			IF teststr = needle THEN
				RETURN 1;
			END IF;
			SET pos_start = pos_end + 1;
		END LOOP;
	END//


DELIMITER ;
