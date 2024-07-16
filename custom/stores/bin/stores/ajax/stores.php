<?php
/* Copyright (C) 2012 Regis Houssin       <regis.houssin@inodbox.com>
 * Copyright (C) 2020 Laurent Destailleur <eldy@users.sourceforge.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *       \file       htdocs/core/ajax/contacts.php
 *       \brief      File to load contacts combobox
 */

 
require "../../../main.inc.php";
require_once DOL_DOCUMENT_ROOT."/custom/stores/class/branch.class.php";
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';


if (!defined('NOTOKENRENEWAL')) {
	define('NOTOKENRENEWAL', '1'); // Disables token renewal
}
if (!defined('NOREQUIREMENU')) {
	define('NOREQUIREMENU', '1');
}
if (!defined('NOREQUIREAJAX')) {
	define('NOREQUIREAJAX', '1');
}


$thirdId = GETPOST('id', 'int'); // id of thirdparty
$storeId = GETPOST('storeId', 'int'); // id of thirdparty


/*
 * View
 */

top_httphead();


if (!empty($thirdId)) {
	$branch = new Branch($db);

	$return = array();

	$return['data']	= $branch->select_stores($thirdId, null);
	// $return['success'] = $form->num;
	// $return['error']	= $form->error;

	echo json_encode($return);
}