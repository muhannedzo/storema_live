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
print '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">';
print '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>';
print '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.5.0-beta4/html2canvas.min.css">';
print '<script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/2.3.1/purify.min.js"></script>';
dol_include_once('/ticket/class/ticket.class.php');
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/stores/class/branch.class.php';
dol_include_once('/ticket/class/ticket.class.php');
dol_include_once('/societe/class/societe.class.php');
dol_include_once('/projet/class/project.class.php');
dol_include_once('/stores/compress.php');


/*
 * View
 */

 $form = new Form($db);
 $formfile = new FormFile($db);
 $ticketId = GETPOST('id', 'int');
 
 $object = new Ticket($db);
 $object->fetch($ticketId);
 $socid = $object->socid;
 $storeid = $object->array_options["options_fk_store"];
 $company = new Societe($db);
 $company->fetch($socid);
 $store = new Branch($db);
 $store->fetch($storeid);
 $project = new Project($db);
 $project->fetch($object->fk_project);
 $compress = new Compress();

 llxHeader("", $langs->trans("Report"));
 
 print load_fiche_titre($langs->trans("TicketReport").$project->title, '', '');
 //////////////////////////////////////////////////////////////////////////////////////////
$existingReportSQL = "SELECT content, parameters, rowid FROM llx_tec_forms WHERE fk_ticket = ".$ticketId;
$existingReportRes = $db->query($existingReportSQL)->fetch_all();
if($existingReportRes){
$tecFormId = $existingReportRes[0][2];
$form = base64_decode($existingReportRes[0][0]);    
echo '<div id="report-form">'.$form.'</div>';
}else{
$reportSQL = "SELECT * FROM llx_reports WHERE projectid = ".$object->fk_project;
$reportRes = $db->query($reportSQL)->fetch_all();
$reportData = $reportRes[0];
$form = base64_decode($reportData[2]);
echo $form;
}

if(!$existingReportRes){

    echo 
    '<script>
    const form = document.getElementById("report-form");
    let dynamicDisplays = document.querySelectorAll([\'[data-content-type]\']);
    dynamicDisplays.forEach((element) => {
    if(element.dataset.contentType === "dynamic"){
    console.log(element.innerHTML.toLowerCase().substring(0, element.innerHTML.indexOf(" ")));
    switch(element.innerHTML.toLowerCase().substring(0, element.innerHTML.indexOf(" "))){
    case "filiale":
    element.innerHTML = "'.$store->b_number.'";
    break;
    case "ticketnummer":
    element.innerHTML = "'.$object->ref.'";
    break;
    case "kundennummer":
    element.innerHTML = "'.$company->id.'";
    break;
    case "kundenname":
    element.innerHTML = "'.$store->customer_name.'";
    break;
    case "projektname":
    element.innerHTML = "'.$project->title.'";
    break;
    case "datum":
    element.innerHTML = "'.date("d.m.y H:i", $object->datec).'";
    break;
    case "uhrzeit":
    element.innerHTML = "'.dol_print_date($object->date_creation, 'hour').'";
    break;
    case "dringlichkeit":
    element.innerHTML = "'.$object->severity_code.'";
    break;
    case "kategorie":
    element.innerHTML = "'.$object->category_label.'";
    break;
    case "beschreibung/auftrag":
    element.innerHTML = "'.$object->message.'";
    break;
    case "strasse":
    element.innerHTML = "'.$store->street.'";
    break;
    case "hausnummer":
    element.innerHTML = "'.$store->house_number.'";
    case "Stadt":
    element.innerHTML = "'.$store->city.'";
    break;
    case "plz":
    element.innerHTML = "'.$store->zip_code.'";
    break;
    case "ext.ticketnummeer":
    element.innerHTML = "'.$object->array_options["options_externalticketnumber"].'";
    break;
    case "telefonnummer":
    element.innerHTML = "'.$store->phone.'";
    break;
    default:
    element.innerHTML = "nothing";
    break;
    }
    }
    });
    
    </script>';
}else{
$parameters = base64_decode($existingReportRes[0][1]);
echo 
    '<script>
        const form = document.getElementById("report-form");
        // Fill the form with the existing data
        const elements = document.querySelectorAll(\'.report-element\');
        const params = '.$parameters.';
        for(let i = 0; i < params.length; i++){
            const element = document.getElementById(params[i].id);
            element.value = params[i].value;
        }
    </script>';
}

echo '<script>
console.log("test");
const saveFormButtonWrapper = document.getElementById("save-form-button-wrapper");
saveFormButtonWrapper.addEventListener("click", () => {
    const form = document.getElementById("report-form");
    let parameters = [];

    // 1. Capture serialized form data
    const elements = document.querySelectorAll(\'.report-element\');
    elements.forEach((element) => {
        const id = element.id;
        let  value = null;
        
        if(element.tagName === "INPUT" || element.tagName === "TEXTAREA"){
            value = element.value;
        }else{
        
            value = element.innerHTML;
        }
        parameters.push({ id: id, value: value });
    });
    // 2. Capture checked checkboxes
    // #TODO

    //Ajax to save the form
    const formData = new FormData();
    // Add the full HTML content
    formData.append("form", $("#report-form").html());
    formData.append("parameters", JSON.stringify(parameters));
    // Add other required fields
    formData.append("storeId", "'.$storeid.'");
    formData.append("userId", "'.$user->id.'");
    formData.append("ticketId", "'.$ticketId.'");
    formData.append("socId", "'.$object->fk_soc.'");
    console.log(formData);
    $.ajax({
                  url: "tecform.php",
                  type: "POST",
                  data: formData,
                  processData: false,
                  contentType: false,
                  success: function(response) {
                        console.log(response);
                        // if(redirect == 1){
                        //    window.location.href = "index.php";
                        // }
                        alert("Report saved successfully");
                  },
                  error: function(xhr, status, error) {
                        console.error("Request failed with status: " + xhr.status + ", Error: " + error);
                        // if(redirect == 1){
                        //    window.location.href = "index.php";
                        // }
                  }
               });

});

</script>';