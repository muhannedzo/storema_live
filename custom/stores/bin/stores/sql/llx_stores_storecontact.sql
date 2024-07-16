-- Copyright (C) 2023 SuperAdmin
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.


CREATE TABLE llx_stores_storecontact(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	ref varchar(128) NOT NULL, 
	fk_soc integer, 
	fk_project integer, 
	note_public text, 
	note_private text, 
	date_creation datetime NOT NULL, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	last_main_doc varchar(255), 
	import_key varchar(14), 
	status integer NOT NULL, 
	civility varchar(255), 
	lastname varchar(50), 
	firstname varchar(50), 
	poste varchar(80), 
	address varchar(255), 
	town text, 
	fk_departement integer, 
	fk_pays integer, 
	birthday date, 
	phone varchar(30), 
	phone_perso varchar(30), 
	phone_mobile varchar(30), 
	fax varchar(30), 
	email varchar(255), 
	socialnetworks text, 
	photo varchar(255), 
	priv smallint, 
	fk_stcommcontact integer, 
	fk_prospectlevel varchar(12), 
	no_email smallint, 
	default_lang varchar(6), 
	canvas varchar(32), 
	storeId integer, 
	zipcode varchar(25), 
	Entity integer DEFAULT 1 NOT NULL, 
	country_id integer, 
	country varchar(128), 
	state_id integer, 
	state varchar(128), 
	visibility varchar(128)
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
