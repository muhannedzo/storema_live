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


CREATE TABLE llx_stores_branch(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	fk_soc integer, 
	date_creation datetime NOT NULL, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	status integer NOT NULL, 
	b_number varchar(64) NOT NULL, 
	street varchar(64), 
	house_number varchar(14), 
	country varchar(64), 
	zip_code varchar(14), 
	city varchar(64), 
	state varchar(128), 
	phone varchar(20), 
	images text, 
	ref varchar(128) NOT NULL, 
	state_id integer, 
	country_id integer, 
	store_manager varchar(50), 
	district_manager varchar(50), 
	days text, 
	opening date, 
	closing date, 
	cashers_desks integer, 
	store_size integer, 
	sales_area integer, 
	warehouse_area integer, 
	branch_height integer, 
	goods text, 
	import_key varchar(14), 
	fk_user_author integer, 
	excel_imported integer, 
	customer_name varchar(128)
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
