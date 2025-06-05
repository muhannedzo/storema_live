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

 
require "../../main.inc.php";
require_once DOL_DOCUMENT_ROOT."/custom/stores/class/branch.class.php";
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';


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



if (!empty($_GET['projectid'])) {
    $projectId = intval($_GET['projectid']);
    $sql = "SELECT rowid, description FROM llx_entrepot WHERE fk_project = " . $projectId;
    $resql = $db->query($sql);

    $warehouses = [];
    if ($resql) {
        while ($obj = $db->fetch_object($resql)) {
            $warehouses[] = [
                'rowid' => $obj->rowid,
                'description' => $obj->description,
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($warehouses);
    exit;
}else if (!empty($_GET['warehouseid'])) {
	
		$warehouseId = intval($_GET['warehouseid']);
		$sql = "SELECT p.rowid, p.label, p.description, ps.reel AS stock
				FROM llx_product_stock ps
				INNER JOIN llx_product p ON ps.fk_product = p.rowid
				WHERE ps.fk_entrepot = " . $warehouseId;
		$resql = $db->query($sql);
	
		$products = [];
		if ($resql) {
			while ($obj = $db->fetch_object($resql)) {
				$products[] = [
					'rowid' => $obj->rowid,
					'label' => $obj->label,
					'description' => $obj->description,
					'stock' => $obj->stock,
				];
			}
		}
	
		header('Content-Type: application/json');
		echo json_encode($products);
		exit;
	
}else if (!empty($thirdId)) {
	$form = new FormProjets($db);

	$return = array();

	$return['data']	= $form->select_project($thirdId, GETPOST('projectid', 'int'), 'projectid', 0, 0, 1, 0, 0, 0, 1, '', 1, 0, 'maxwidth500');
	// $return['success'] = $form->num;
	// $return['error']	= $form->error;
	echo json_encode($return);
	exit;
}else{
	echo "Not found";
	exit;
}