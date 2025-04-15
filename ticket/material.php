<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
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
 *	\file       stores/storesindex.php
 *	\ingroup    stores
 *	\brief      Home page of stores top menu
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/stores/class/branch.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formticket.class.php';
require_once DOL_DOCUMENT_ROOT.'/ticket/class/ticket.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/ticket.lib.php';
print '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">';
print '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>';
print '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.5.0-beta4/html2canvas.min.css">';
print '<script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/2.3.1/purify.min.js"></script>'; 

// Load translation files required by the page
$langs->loadLangs(array('companies', 'ticket'));

// Get parameters
$socid = GETPOST("socid", 'int');
$action = GETPOST("action", 'alpha');
$track_id = GETPOST("track_id", 'alpha');
$id = GETPOST("id", 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$type = GETPOST('type', 'alpha');
$source = GETPOST('source', 'alpha');

$ligne = GETPOST('ligne', 'int');
$lineid = GETPOST('lineid', 'int');

// Store current page url
$url_page_current = DOL_URL_ROOT.'/ticket/reportOverview.php';
$object = new Ticket($db);

$formticket = new FormTicket($db);

$permissiontoadd = $user->rights->ticket->write;

// Security check
$id = GETPOST("id", 'int');
if ($user->socid > 0) $socid = $user->socid;
$result = restrictedArea($user, 'ticket', $object->id, '');

// restrict access for externals users
if ($user->socid > 0 && ($object->fk_soc != $user->socid)) {
	accessforbidden();
}
// or for unauthorized internals users
if (!$user->socid && (!empty($conf->global->TICKET_LIMIT_VIEW_ASSIGNED_ONLY) && $object->fk_user_assign != $user->id) && !$user->rights->ticket->manage) {
	accessforbidden();
}

$object = new Ticket($db);
$object->fetch($id);
$socid = $object->socid;
$storeid = $object->array_options["options_fk_store"];
$company = new Societe($db);
$company->fetch($socid);
$store = new Branch($db);
$store->fetch($storeid);
$project = new Project($db);
$project->fetch($object->fk_project);
$techUser = new User($db);


llxHeader("", $langs->trans("Materialien"));
$head = ticket_prepare_head($object);

print dol_get_fiche_head($head, 'tabTicketMaterial', $langs->trans("Material"), -1, 'material');


print load_fiche_titre($langs->trans("Materialliste für ").$object->ref, '', '');


$sql = "SELECT 
            p.label AS product_label, 
            p.description AS product_description, 
            w.description AS warehouse_description,
            pt.qty,
            pt.rowid,
            w.rowid AS fk_warehouse,
            p.rowid AS fk_product
        FROM llx_product_ticket pt
        LEFT JOIN llx_product p ON pt.fk_product = p.rowid
        LEFT JOIN llx_entrepot w ON pt.fk_warehouse = w.rowid
        WHERE pt.fk_ticket = " . (int) $object->id."
        ORDER BY p.label ASC";

$resql = $db->query($sql);

if ($resql) {
    $products = array();
    while ($obj = $db->fetch_object($resql)) {
        $products[(int)$obj->rowid] = $obj;
    }
} else {
    dol_print_error($db);
}


if (GETPOST('action', 'alpha') == 'save_edit' && !empty($_POST['qty'])) {
    // #TODO: Min/Max qty
    // Loop over each submitted quantity, where the key is the rowid
    foreach ($_POST['qty'] as $rowid => $newQty) {
        $newQty = (int) $newQty;
        $rowid = (int) $rowid;  
        if ($newQty > 0 && $newQty != $products[$rowid]->qty) {
            // Update quantity
            $sqlUpdate = "UPDATE llx_product_ticket SET qty = " . $newQty . " WHERE rowid = " . $rowid;
            $db->query($sqlUpdate);
            // Decrease stock in warehouse if we added more
            if($products[$rowid]->qty < $newQty){
                $sqlUpdateWarehouseStock = "UPDATE llx_product_stock SET reel = reel - " . ($newQty - $products[$rowid]->qty) . " WHERE fk_product = " . $products[$rowid]->fk_product . " AND fk_entrepot = " . $products[$rowid]->fk_warehouse;
                $db->query($sqlUpdateWarehouseStock);
            }else if($products[$rowid]->qty > $newQty){ // Increase if we removed some from ticket
                $sqlUpdateWarehouseStock = "UPDATE llx_product_stock SET reel = reel + " . ($products[$rowid]->qty - $newQty) . " WHERE fk_product = " . $products[$rowid]->fk_product . " AND fk_entrepot = " . $products[$rowid]->fk_warehouse;
                $db->query($sqlUpdateWarehouseStock);
            }
        } else if($newQty == 0 && $products[$rowid]->qty > 0) {
            // Delete the entry if qty is set to 0
            $sqlDelete = "DELETE FROM llx_product_ticket WHERE rowid = " . $rowid;
            $db->query($sqlDelete);
            $sqlUpdateWarehouseStock = "UPDATE llx_product_stock SET reel = reel + " . $products[$rowid]->qty . " WHERE fk_product = " . $products[$rowid]->fk_product . " AND fk_entrepot = " . $products[$rowid]->fk_warehouse;
            $db->query($sqlUpdateWarehouseStock);
        }
        echo "<script>window.location.href = '" . $_SERVER['PHP_SELF'] . "?id=" . $_POST['id'] . "'; </script>";
    }

    // After saving, redirect back using the id parameter (keep the ticket id)
    //echo "<script>window.location.href='" . $_SERVER['PHP_SELF'] . "?id=" . $_POST['id'] . "';</script>";
}else if(GETPOST('action', 'alpha' == 'save_add') && !empty($_POST['selected_products'])){
    $selectedProducts = json_decode($_POST['selected_products'], true);
    $warehouseId = GETPOST('warehouseid', 'int');
   
    // Loop through selected products and insert them into the database
    foreach ($selectedProducts as $product) {
        $productId = (int) $product['id'];
        $qty = (int) $product['qty'];
         // Check if warehouse and product combination is already present in llx_product_ticket
        $sqlCheck = "SELECT * FROM llx_product_ticket WHERE fk_ticket = " . $_POST['id'] . " AND fk_warehouse = " . $warehouseId;
        $resqlCheck = $db->query($sqlCheck);
        $existingProducts = array();
        if (!empty($existingProducts)) {
            setEventMessage('Produkt aus dem Lager ist dem Ticket bereits zugewiesen' , 'errors');
            

        }else{
            if ($qty > 0) {
                // Insert into product_ticket table
                $sqlInsert = "INSERT INTO llx_product_ticket (fk_product, fk_ticket, fk_warehouse, qty) VALUES (" . $productId . ", " . $_POST['id'] . ", " . $warehouseId . ", " . $qty . ")";
                $db->query($sqlInsert);
                // Decrease stock in warehouse
                $sqlUpdateWarehouseStock = "UPDATE llx_product_stock SET reel = reel - " . $qty . " WHERE fk_product = " . $productId . " AND fk_entrepot = " . $warehouseId;
                $db->query($sqlUpdateWarehouseStock);
            }
        }
        
    }
    echo "<script>window.location.href='" . $_SERVER['PHP_SELF'] . "?id=" . $_POST['id'] . "';</script>";

}




if(GETPOST('action') != 'add'){
    if (GETPOST('action') != 'edit') {
        echo '<div class="d-flex justify-content-end mb-1 gap-1">';
        echo '<a href="' . $_SERVER['PHP_SELF'] . '?action=edit&id=' . (int) $object->id . '" class="btn btn-dark text-light">Bearbeiten</a>';
        echo '<a href="' . $_SERVER['PHP_SELF'] . '?action=add&id=' . (int) $object->id . '" class="btn btn-dark text-light">Hinzufügen</a>';
        echo '</div>';
    }
    
    
    if (!empty($products)) {
        echo '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
        if ($action == 'edit') {
            echo '<div class="text-end mt-1 d-flex justify-content-end mb-2">';
            echo '<button type="submit" class="btn btn-success">Speichern</button>';
            echo '</div>';
        }
        echo '<input type="hidden" name="action" value="save_edit">';
        echo '<div class="table-responsive">';
        echo '<table class="table table-striped table-bordered">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Produktname</th>';
        echo '<th>Beschreibung</th>';
        echo '<th>Menge</th>';
        if($action == 'edit') {
            echo '<th>Verfügbare Menge im Lager</th>';
        }
        echo '<th>Lager</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($products as $product) {
            echo '<tr>';
            echo '<td>' . dol_escape_htmltag($product->product_label) . '</td>';
            echo '<td>' . dol_escape_htmltag($product->product_description) . '</td>';
            echo '<td>';
            if ($action =="edit") {
                // Using product ticket's rowid as the key
                $sqlGetWarehouseStock = "SELECT reel FROM llx_product_stock WHERE fk_product = " . $product->fk_product . " AND fk_entrepot = " . $product->fk_warehouse;
                $resqlGetWarehouseStock = $db->query($sqlGetWarehouseStock);
                $stock = $resqlGetWarehouseStock ? $db->fetch_object($resqlGetWarehouseStock)->reel : 0;
                echo '<input type="number" name="qty[' . (int) $product->rowid . ']" value="' . (int) $product->qty . '" max='.$stock.' min="0" class="form-control" style="width:100px;">';
            } else {
                echo (int) $product->qty;
            }
            echo '</td>';
            if($action == 'edit') {
                echo '<td>' . (int) $stock . '</td>';
            }
            echo '<td>' . dol_escape_htmltag($product->warehouse_description) . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    
        
        echo '<input type="hidden" name="id" value="' . (int) $object->id . '">';
    
        echo '</form>';
    } else {
        echo '<p>Keine Produkte gefunden.</p>';
    }
}else{
    $sqlFetchWarhouse = "SELECT * FROM llx_entrepot WHERE fk_project = " . $object->fk_project;
    $resqlFetchWarhouse = $db->query($sqlFetchWarhouse);
    $warehouses = array();
    if ($resqlFetchWarhouse) {
        while ($obj = $db->fetch_object($resqlFetchWarhouse)) {
            $warehouses[] = $obj;
        }
    } else {
        dol_print_error($db);
    }
    $warehouseOptions = array();
    foreach ($warehouses as $warehouse) {
        $warehouseOptions[] = '<option value="' . $warehouse->rowid . '">' . $warehouse->description . '</option>';
    }
    $warehouseOptions = implode('', $warehouseOptions);

    // Check which products have already been assigned
    $sqlAssignedProducts = "SELECT fk_product, fk_warehouse FROM llx_product_ticket WHERE fk_ticket = " . (int) $object->id;
    $resqlAssignedProducts = $db->query($sqlAssignedProducts);

    $assignedProductIds = array();
    if ($resqlAssignedProducts) {
        while ($objAssigned = $db->fetch_object($resqlAssignedProducts)) {
            $entry = array();
            $entry['fk_product'] = $objAssigned->fk_product;
            $entry['fk_warehouse'] = $objAssigned->fk_warehouse;
            $assignedProductIds[] = $entry;  // Store the IDs of the assigned products
        }
    } else {
        dol_print_error($db);
    }
    var_dump(json_encode($assignedProductIds));

    print '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
    print '<input type="hidden" name="action" value="save_add">';
    print '
            <select id="warehouseid" name="warehouseid" class="flat minwidth200 p-2">
                <option value="" selected disabled>Lager auswählen</option>
                '.$warehouseOptions.'
            </select>';

            echo '
                    <div id="product-table-container" class="table-responsive">
                        <input type="text" id="product-search" placeholder="Search products..." class="form-control mb-3" />
                        <table id="product-table" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Auswählen</th>
                                    <th>Produktname</th>
                                    <th>Beschreibung</th>
                                    <th>Lagerbestand</th>
                                    <th>Menge</th>
                                </tr>
                            </thead>
                            <tbody id="product-table-body">
                                
                            </tbody>
                        </table>
                    </div>
                    <input type="hidden" id="selected-products" name="selected_products" value="">
                    <input type="hidden" name="id" value="' . (int) $object->id . '">
                    <div class="text-end mt-1 d-flex justify-content-end mb-2">
                        <button type="submit" class="btn btn-success">Speichern</button>
                    </div>
                </form>';
    
            echo '<script type="text/javascript">
                    $(document).ready(function () {

                        // Already assigned products
                        var assignedProductIds = ' . json_encode($assignedProductIds) . ';
                        console.log(assignedProductIds);
                        // Fetch products when a warehouse is selected
                        $("#warehouseid").change(function () {
                            var warehouseId = $(this).val();
                            if (warehouseId) {
                                $.ajax({
                                    url: "'. dol_buildpath("/ticket/ajax/tickets.php", 1).'",
                                    type: "GET",
                                    data: { warehouseid: warehouseId },
                                    dataType: "json",
                                    success: function (response) {
                                        var tbody = $("#product-table-body");
                                        tbody.empty();
                                        if (response && response.length > 0) {
                                            response.forEach(function (product) {
                                                var isAssigned = assignedProductIds.some(function (assigned) {
                                                    return assigned.fk_product === product.rowid && assigned.fk_warehouse === warehouseId;
                                                });
                                                tbody.append(`
                                                    <tr data-product-id="${product.rowid}">
                                                        <td>
                                                            ${isAssigned ? \'Produkt bereits hinzugefügt\' : \'<input type="checkbox" class="product-select">\'}
                                                        </td>
                                                        <td>${product.label}</td>
                                                        <td>${product.description}</td>
                                                        <td>${product.stock}</td>
                                                        <td>
                                                            <input type="number" class="product-qty form-control" placeholder="Stk" min="1" style="width:80px;" data-stock="${product.stock}" disabled>
                                                        </td>
                                                    </tr>
                                                `);
                                            });
                                        } else {
                                            tbody.append(\'<tr><td colspan="4">Keine Produkte gefunden</td></tr>\');
                                        }
                                    },
                                    error: function (xhr, status, error) {
                                        console.error("Error fetching products:", error);
                                    }
                                });
                            }
                        });
    
                        // Enable/disable quantity input based on checkbox selection and update hidden field
                        $(document).on("change", ".product-select", function () {
                            var row = $(this).closest("tr");
                            var qtyInput = row.find(".product-qty");
                            if ($(this).is(":checked")) {
                                qtyInput.prop("disabled", false).focus();
                            } else {
                                qtyInput.prop("disabled", true).val("");
                            }
                            updateSelectedProducts();
                        });
    
                        // Function to check quantity against stock
                        function checkQty(input) {
                            var qty = parseInt(input.value);
                            var stock = parseInt($(input).data("stock")); // Correctly retrieve stock from data attribute
    
                            if (isNaN(qty) || qty < 1) {
                                input.value = 1; // Set to minimum value if invalid
                                qty = 1;
                            }
    
                            if (qty > stock) {
                                alert("Die Menge darf den Lagerbestand nicht überschreiten.");
                                input.value = stock;
                            }
                        };
                    
    
                        // Update hidden input when quantity is changed
                        $(document).on("input change", ".product-qty", function () {
                            checkQty(this); // Call checkQty with the input element
                            updateSelectedProducts(); // Call updateSelectedProducts after quantity change
                        });
    
                        // Search filter for products
                        $("#product-search").on("input", function () {
                            var searchTerm = $(this).val().toLowerCase();
                            $("#product-table-body tr").each(function () {
                                var productName = $(this).find("td:nth-child(2)").text().toLowerCase();
                                $(this).toggle(productName.indexOf(searchTerm) > -1);
                            });
                        });
    
                        // Update the hidden input with selected products and their quantities
                        function updateSelectedProducts() {
                            var selectedProducts = [];
                            $("#product-table-body tr").each(function () {
                                var checkbox = $(this).find(".product-select");
                                if (checkbox.is(":checked")) {
                                    var productId = $(this).data("product-id");
                                    var qty = $(this).find(".product-qty").val();
                                    if (qty === "" || isNaN(qty) || qty <= 0) {
                                        qty = 1;
                                    }
                                    selectedProducts.push({ id: productId, qty: qty });
                                }
                            });
                            $("#selected-products").val(JSON.stringify(selectedProducts));
                        }
                    });
                </script>';
    
}


