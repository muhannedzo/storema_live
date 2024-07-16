-- Copyright (C) 2023 Mohanned <muhanned.zoubi.sw@gmail.com>
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


-- BEGIN MODULEBUILDER INDEXES
ALTER TABLE llx_pricematrix_metrices ADD INDEX idx_pricematrix_metrices_rowid (rowid);
ALTER TABLE llx_pricematrix_metrices ADD INDEX idx_pricematrix_metrices_ref (ref);
ALTER TABLE llx_pricematrix_metrices ADD INDEX idx_pricematrix_metrices_status (status);
-- END MODULEBUILDER INDEXES

--ALTER TABLE llx_pricematrix_metrices ADD UNIQUE INDEX uk_pricematrix_metrices_fieldxy(fieldx, fieldy);

--ALTER TABLE llx_pricematrix_metrices ADD CONSTRAINT llx_pricematrix_metrices_fk_field FOREIGN KEY (fk_field) REFERENCES llx_pricematrix_myotherobject(rowid);

