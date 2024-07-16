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
 
 print load_fiche_titre($langs->trans("Ticket Report - ").$project->title, '', '');
 //////////////////////////////////////////////////////////////////////////////////////////

 //task message
 $sql = 'SELECT content, parameters, images FROM llx_tec_forms WHERE fk_ticket = '.$object->id.' AND fk_user = '.$user->id.' AND fk_store = '.$storeid.' AND fk_soc = '.$object->fk_soc.';';
 $result = $db->query($sql)->fetch_all()[0];
 
 print '<div id="report-body">';
   // if($result){
   //    var_dump(json_decode(base64_decode($result[1]), true));
   // }else{
      print '<div class="row">';
         print '<div class="col-6">';
            print '<label>Ticket Number*</label>';
            print '<br>';
            print '<input class="textfield" type="text" name="ticket-number" value="'.$object->ref.'" required>';
         print '</div>';
         print '<div class="col-6">';
            print '<label>Store*</label>';
            print '<br>';
            print '<input class="textfield" type="text" name="store-number" value="'.$store->b_number.'" required>';
         print '</div>';
      print '</div>';
      print '<br>';
      print '<div>';
         print '<label>Street*</label>';
         print '<br>';
         print '<input class="textfield" type="text" name="street" value="'.$store->street.','. $store->zip_code.' '. $store->city.'" required>';
      print '</div>';
      print '<br>';
      print '<div>';
         print '<label>Phonenumber*</label>';
         print '<br>';
         print '<input class="textfield" type="text" name="phonenumber" value="'.$store->phone.'" required>';
      print '</div>';
      print '<br>';
      print '<div class="task-message">';
         print 'Task Message:';
         print '<br>';
         print $object->message;
         print '<br>';
         print 'Date: '.date("d.m.y H:i", $object->datec);
      print '</div>';
      print '<br>';
      print '<div class="container">';
         print '<div class="row mb-3">';
            print '<div class="col-6 col-md-3 d-flex align-items-center">';
               print '<label for="input-time-departure" class="form-label mb-0">Time Departure: </label>';
            print '</div>';
            print '<div class="col-6 col-md-9">';
               print '<input type="time" id="input-time-departure" name="time-departure" class="form-control" value="">';
            print '</div>';
         print '</div>';
         print '<div class="row mb-3">';
            print '<div class="col-6 col-md-3 d-flex align-items-center">';
               print '<label for="input-time-arrival" class="form-label mb-0">Time Arrival: </label>';
            print '</div>';
            print '<div class="col-6 col-md-9">';
               print '<input type="time" id="input-time-arrival" name="time-arrival" class="form-control" value="">';
            print '</div>';
         print '</div>';
         print '<div class="row mb-3">';
            print '<div class="col-6 col-md-3 d-flex align-items-center">';
               print '<label class="form-label mb-0">Duration of Trip: </label>';
            print '</div>';
            print '<div class="col-6 col-md-9 d-flex">';
               print '<input type="number" id="input-duration-hours" name="trip-hours" class="form-control me-2" style="max-width: 70px;" placeholder="h" value="">';
               print '<span class="align-self-center me-2">h :</span>';
               print '<input type="number" id="input-duration-minutes" name="trip-minutes" class="form-control" style="max-width: 70px;" max="60" placeholder="m" value="">';
               print '<span class="align-self-center me-2">m</span>';
            print '</div>';
         print '</div>';
         print '<div class="row mb-3">';
            print '<div class="col-6 col-md-3 d-flex align-items-center">';
               print '<label for="input-km" class="form-label mb-0">KM: </label>';
            print '</div>';
            print '<div class="col-6 col-md-9">';
               print '<input type="number" id="input-km" class="form-control" name="km" value="">';
            print '</div>';
         print '</div>';
         print '<div class="row mb-3">';
            print '<div class="col-6 col-md-3 d-flex align-items-center">';
               print '<label for="input-work-start" class="form-label mb-0">Work Start: </label>';
            print '</div>';
            print '<div class="col-6 col-md-9">';
               print '<input type="time" id="input-work-start" name="work-start" class="form-control" value="">';
            print '</div>';
         print '</div>';
         print '<div class="row mb-3">';
            print '<div class="col-6 col-md-3 d-flex align-items-center">';
               print '<label for="input-work-end" class="form-label mb-0">Work End: </label>';
            print '</div>';
            print '<div class="col-6 col-md-9">';
               print '<input type="time" id="input-work-end" name="work-end" class="form-control" value="">';
            print '</div>';
         print '</div>';
      print '</div>';
      print '<br>';
      print '<div class="row">';
         print '<div class="col-lg-6 col-xs-12 div-table-responsive-no-min" id="pieces-table">';
            print '<table class="noborder centpercent">';
               print '<tr class="oddeven">';
                  print '<td colspan="1">Lancom Router</td>';
                  print '<td colspan="1"></td>';
                  print '<td colspan="1" class="center"><input type="checkbox" name="router-value"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">Palo Alto Firewall</td>';
                  print '<td colspan="1"></td>';
                  print '<td colspan="1" class="center"><input type="checkbox" name="firewall-value"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">Patchkabel</td>';
                  print '<td colspan="1">1 Meter</td>';
                  print '<td colspan="1" class="center"><input type="number" name="patchkabel-1meter-value" value="0"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">Patchkabel</td>';
                  print '<td colspan="1">3 Meter</td>';
                  print '<td colspan="1" class="center"><input type="number" name="patchkabel-3meter-value" value="0"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">Patchkabel</td>';
                  print '<td colspan="1">5 Meter</td>';
                  print '<td colspan="1" class="center"><input type="number" name="patchkabel-5meter-value" value="0"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">Kaltgeratekabel</td>';
                  print '<td colspan="1"></td>';
                  print '<td colspan="1" class="center"><input type="number" name="kaltgeratekabel-value" value="0"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">Steckdosenleiste</td>';
                  print '<td colspan="1"></td>';
                  print '<td colspan="1" class="center"><input type="number" name="steckdosenleiste-value" value="0"></td>';
               print '</tr>';
            print '</table>';
         print '</div>';
      print '</div>';
      print '<br>';
      print '<div class="row">';
         print '<div class="col-12 div-table-responsive-no-min">';
            print '<table id="questions-table" class="noborder centpercent">';
               print '<tr class="liste_titre">';
                  print '<th colspan="3"></th>';
                  print '<th colspan="2">VKST 4.0</th>';
                  print '<th colspan="3">NUR NACH RUCKBAUI VKST 3.0</th>';
               print '</tr>';
               print '<tr class="liste_titre">';
                  print '<td colspan="1">Test NR</td>';
                  print '<td colspan="1">Testfalle</td>';
                  print '<td colspan="1">Prio</td>';
                  print '<td colspan="1"><i class="fa fa-check" style="color:green"></i></td>';
                  print '<td colspan="1"><i class="ico-times" role="img" aria-label="Cancel"></i></td>';
                  print '<td colspan="1">NV</td>';
                  print '<td colspan="1"><i class="fa fa-check center" style="color:green"></i></td>';
                  print '<td colspan="1"><i class="ico-times center" role="img" aria-label="Cancel"></i></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">A1</td>';
                  print '<td colspan="1">Testartikel scannen (R)</td>';
                  print '<td colspan="1" class="prio">1</td>';
                  print '<td colspan="1"><input type="radio" name="question1vk" id="question1vk_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question1vk" id="question1vk_2" value="2"></td>';
                  print '<td colspan="1"><input type="checkbox" name="table1-check-1"></td>';
                  print '<td colspan="1"><input type="radio" name="question1nu" id="question1nu_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question1nu" id="question1nu_2" value="2"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">A2</td>';
                  print '<td colspan="1">Bon Druck und TSE (R)</td>';
                  print '<td colspan="1" class="prio">1</td>';
                  print '<td colspan="1"><input type="radio" name="question2vk" id="question2vk_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question2vk" id="question2vk_2" value="2"></td>';
                  print '<td colspan="1"><input type="checkbox" name="table1-check-2"></td>';
                  print '<td colspan="1"><input type="radio" name="question2nu" id="question2nu_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question2nu" id="question2nu_2" value="2"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">A3</td>';
                  print '<td colspan="1">EC-Zahlung (R)</td>';
                  print '<td colspan="1" class="prio">1</td>';
                  print '<td colspan="1"><input type="radio" name="question3vk" id="question3vk_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question3vk" id="question3vk_2" value="2"></td>';
                  print '<td colspan="1"><input type="checkbox" name="table1-check-3"></td>';
                  print '<td colspan="1"><input type="radio" name="question3nu" id="question3nu_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question3nu" id="question3nu_2" value="2"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">A4</td>';
                  print '<td colspan="1">EC-Diagnose (R)</td>';
                  print '<td colspan="1" class="prio">1</td>';
                  print '<td colspan="1"><input type="radio" name="question4vk" id="question4vk_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question4vk" id="question4vk_2" value="2"></td>';
                  print '<td colspan="1"><input type="checkbox" name="table1-check-4"></td>';
                  print '<td colspan="1"><input type="radio" name="question4nu" id="question4nu_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question4nu" id="question4nu_2" value="2"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">A5</td>';
                  print '<td colspan="1">Gutschein abfragen (R)</td>';
                  print '<td colspan="1" class="prio">1</td>';
                  print '<td colspan="1"><input type="radio" name="question5vk" id="question5vk_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question5vk" id="question5vk_2" value="2"></td>';
                  print '<td colspan="1"><input type="checkbox" name="table1-check-5"></td>';
                  print '<td colspan="1"><input type="radio" name="question5nu" id="question5nu_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question5nu" id="question5nu_2" value="2"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">A6</td>';
                  print '<td colspan="1">Bediener Abmelden (R)</td>';
                  print '<td colspan="1" class="prio">1</td>';
                  print '<td colspan="1"><input type="radio" name="question6vk" id="question6vk_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question6vk" id="question6vk_2" value="2"></td>';
                  print '<td colspan="1"><input type="checkbox" name="table1-check-6"></td>';
                  print '<td colspan="1"><input type="radio" name="question6nu" id="question6nu_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question6nu" id="question6nu_2" value="2"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">B1</td>';
                  print '<td colspan="1">Mit OBF einen Artikel scannen (R)</td>';
                  print '<td colspan="1" class="prio">1</td>';
                  print '<td colspan="1"><input type="radio" name="question7vk" id="question7vk_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question7vk" id="question7vk_2" value="2"></td>';
                  print '<td colspan="1"><input type="checkbox" name="table1-check-7"></td>';
                  print '<td colspan="1"><input type="radio" name="question7nu" id="question7nu_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question7nu" id="question7nu_2" value="2"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">B2</td>';
                  print '<td colspan="1">Mit OBF Etiketten drucken (R)</td>';
                  print '<td colspan="1" class="prio">1</td>';
                  print '<td colspan="1"><input type="radio" name="question8vk" id="question8vk_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question8vk" id="question8vk_2" value="2"></td>';
                  print '<td colspan="1"><input type="checkbox" name="table1-check-8"></td>';
                  print '<td colspan="1"><input type="radio" name="question8nu" id="question8nu_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question8nu" id="question8nu_2" value="2"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">C1</td>';
                  print '<td colspan="1">MO STM (R)</td>';
                  print '<td colspan="1" class="prio">1</td>';
                  print '<td colspan="1"><input type="radio" name="question9vk" id="question9vk_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question9vk" id="question9vk_2" value="2"></td>';
                  print '<td colspan="1"><input type="checkbox" name="table1-check-9"></td>';
                  print '<td colspan="1"><input type="radio" name="question9nu" id="question9nu_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question9nu" id="question9nu_2" value="2"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">C2</td>';
                  print '<td colspan="1">MO HR Portal (R)</td>';
                  print '<td colspan="1" class="prio">2</td>';
                  print '<td colspan="1"><input type="radio" name="question10vk" id="question10vk_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question10vk" id="question10vk_2" value="2"></td>';
                  print '<td colspan="1"><input type="checkbox" name="table1-check-10"></td>';
                  print '<td colspan="1"><input type="radio" name="question10nu" id="question10nu_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question10nu" id="question10nu_2" value="2"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">C3</td>';
                  print '<td colspan="1">MO PEP (R)</td>';
                  print '<td colspan="1" class="prio">2</td>';
                  print '<td colspan="1"><input type="radio" name="question11vk" id="question11vk_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question11vk" id="question11vk_2" value="2"></td>';
                  print '<td colspan="1"><input type="checkbox" name="table1-check-11"></td>';
                  print '<td colspan="1"><input type="radio" name="question11nu" id="question11nu_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question11nu" id="question11nu_2" value="2"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">C4</td>';
                  print '<td colspan="1">MO ProDigi (R)</td>';
                  print '<td colspan="1" class="prio">2</td>';
                  print '<td colspan="1"><input type="radio" name="question12vk" id="question12vk_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question12vk" id="question12vk_2" value="2"></td>';
                  print '<td colspan="1"><input type="checkbox" name="table1-check-12"></td>';
                  print '<td colspan="1"><input type="radio" name="question12nu" id="question12nu_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question12nu" id="question12nu_2" value="2"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">C5</td>';
                  print '<td colspan="1">MO Intranet (R)</td>';
                  print '<td colspan="1" class="prio">2</td>';
                  print '<td colspan="1"><input type="radio" name="question13vk" id="question13vk_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question13vk" id="question13vk_2" value="2"></td>';
                  print '<td colspan="1"><input type="checkbox" name="table1-check-13"></td>';
                  print '<td colspan="1"><input type="radio" name="question13nu" id="question13nu_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question13nu" id="question13nu_2" value="2"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">C6</td>';
                  print '<td colspan="1">MO Korona Backoffice (R)</td>';
                  print '<td colspan="1" class="prio">2</td>';
                  print '<td colspan="1"><input type="radio" name="question14vk" id="question14vk_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question14vk" id="question14vk_2" value="2"></td>';
                  print '<td colspan="1"><input type="checkbox" name="table1-check-14"></td>';
                  print '<td colspan="1"><input type="radio" name="question14nu" id="question14nu_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question14nu" id="question14nu_2" value="2"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">C7</td>';
                  print '<td colspan="1">MO Webportal Instanthaltung (R)</td>';
                  print '<td colspan="1" class="prio">2</td>';
                  print '<td colspan="1"><input type="radio" name="question15vk" id="question15vk_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question15vk" id="question15vk_2" value="2"></td>';
                  print '<td colspan="1"><input type="checkbox" name="table1-check-15"></td>';
                  print '<td colspan="1"><input type="radio" name="question15nu" id="question15nu_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question15nu" id="question15nu_2" value="2"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">D1</td>';
                  print '<td colspan="1">Fototerminals (R/T)</td>';
                  print '<td colspan="1" class="prio">2</td>';
                  print '<td colspan="1"><input type="radio" name="question16vk" id="question16vk_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question16vk" id="question16vk_2" value="2"></td>';
                  print '<td colspan="1"><input type="checkbox" name="table1-check-16"></td>';
                  print '<td colspan="1"><input type="radio" name="question16nu" id="question16nu_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question16nu" id="question16nu_2" value="2"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">D2</td>';
                  print '<td colspan="1">EMA (R/T)</td>';
                  print '<td colspan="1" class="prio">1</td>';
                  print '<td colspan="1"><input type="radio" name="question17vk" id="question17vk_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question17vk" id="question17vk_2" value="2"></td>';
                  print '<td colspan="1"><input type="checkbox" name="table1-check-17"></td>';
                  print '<td colspan="1"><input type="radio" name="question17nu" id="question17nu_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question17nu" id="question17nu_2" value="2"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">D3</td>';
                  print '<td colspan="1">Telefonie (R/T)</td>';
                  print '<td colspan="1" class="prio">2</td>';
                  print '<td colspan="1"><input type="radio" name="question18vk" id="question18vk_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question18vk" id="question18vk_2" value="2"></td>';
                  print '<td colspan="1"><input type="checkbox" name="table1-check-18"></td>';
                  print '<td colspan="1"><input type="radio" name="question18nu" id="question18nu_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question18nu" id="question18nu_2" value="2"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="3">OPTIONAL (testen, wenn vorhanden)</td>';
                  print '<td colspan="1"></td>';
                  print '<td colspan="1"></td>';
                  print '<td colspan="1"></td>';
                  print '<td colspan="1"></td>';
                  print '<td colspan="1" contenteditable="true"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">E1</td>';
                  print '<td colspan="1">ESL (R)</td>';
                  print '<td colspan="1" class="prio">1</td>';
                  print '<td colspan="1"><input type="radio" name="question19vk" id="question19vk_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question19vk" id="question19vk_2" value="2"></td>';
                  print '<td colspan="1"><input type="checkbox" name="table1-check-19"></td>';
                  print '<td colspan="1"><input type="radio" name="question19nu" id="question19nu_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question19nu" id="question19nu_2" value="2"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">E2</td>';
                  print '<td colspan="1">Pfandautomaten (R/T)</td>';
                  print '<td colspan="1" class="prio">2</td>';
                  print '<td colspan="1"><input type="radio" name="question20vk" id="question20vk_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question20vk" id="question20vk_2" value="2"></td>';
                  print '<td colspan="1"><input type="checkbox" name="table1-check-20"></td>';
                  print '<td colspan="1"><input type="radio" name="question20nu" id="question20nu_1" value="1"></td>';
                  print '<td colspan="1"><input type="radio" name="question20nu" id="question20nu_2" value="2"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="7">Durchzuführen durch: (R)= Rossmann Personal (T)= Techniker (R/T)= Rossmann Personal oder Techniker</td>';
                  print '<td colspan="1" contenteditable="true"></td>';
               print '</tr>';
            print '</table>';
         print '</div>';
      print '</div>';
      print '<div class="row">';
         print '<div class="col-lg-6 col-xs-12 div-table-responsive-no-min" id="pieces-table">';
            print '<table class="noborder centpercent">';
               print '<tr class="oddeven">';
                  print '<td colspan="1">Der Umbau in VKST'.$store->b_number.' wurde erfolgreich abgeschlossen Wenn alles erfolgreich.</td>';
                  print '<td colspan="1"><input type="radio" name="table1" id="table1_1" value="1"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">Der Umbau in VKST '.$store->b_number.' wurde erfolgreich abgeschlossen. Mindestens 1 P2 Test konnte nicht erfolgreich durchgeführt werden (siehe unten).</td>';
                  print '<td colspan="1"><input type="radio" name="table1" id="table1_2" value="2"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">Der Umbau in VKST '.$store->b_number.' konnte nicht gestartet werden. Die Gründe sind unter "Sonstiges" zu finden.</td>';
                  print '<td colspan="1"><input type="radio" name="table1" id="table1_3" value="3"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">Der Umbau in VKST '.$store->b_number.' konnte nicht abgeschlossen werden. Der Rollback auf VKST3.0 war erfolgreich.</td>';
                  print '<td colspan="1"><input type="radio" name="table1" id="table1_4" value="4"></td>';
               print '</tr>';
               // print '<tr class="oddeven">';
               //    print '<td colspan="1">Der Umbau in VKST '.$store->b_number.' konnte nicht abgeschlossen werden. Mindestens 1 P2 Test konnte nicht erfolgreich durchgeführt werden (siehe unten)</td>';
               //    print '<td colspan="1"><input type="radio" name="table1" id="table1_5" value="5" class="p2-checkbox"></td>';
               // print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">Der Umbau in VKST '.$store->b_number.' konnte nicht abgeschlossen werden. Auch der Rollback war erfolglos. Der Technikerleitstand wurde bereits informiert.</td>';
                  print '<td colspan="1"><input type="radio" name="table1" id="table1_6" value="6"></td>';
               print '</tr>';
            print '</table>';
            Print '<h6 style="color: red; display: none" id="error-text">Error results: <h6>';
         print '</div>';
      print '</div>';
      print '<div class="row">';
         print '<div class="col-6" id="ruckbau-btn">';
            print '<button id="show" class="btn btn-primary" onclick="toggleVisibility(this.id)">Rückbau</button>';
         print '</div>';
         print '<div class="col-lg-6 col-xs-12 div-table-responsive-no-min" id="ruckbau-table" style="display:none">';
            print '<table class="noborder centpercent">';
               print '<tr class="oddeven">';
                  print '<td colspan="1">Fehlgeschlagener P1 Test (bitte Nummer angeben)</td>';
                  print '<td colspan="1"><input type="radio" name="table2" id="table2_1" value="1"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">Fehlendes Material</td>';
                  print '<td colspan="1"><input type="radio" name="table2" id="table2_2" value="2"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">Fehler der Automatisierung / App</td>';
                  print '<td colspan="1"><input type="radio" name="table2" id="table2_3" value="3"></td>';
               print '</tr>';
               print '<tr class="oddeven">';
                  print '<td colspan="1">Defekte Hardware</td>';
                  print '<td colspan="1"><input type="radio" name="table2" id="table2_4" value="4"></td>';
               print '</tr>';
            print '</table>';
            Print '<h6 style="color: red; display: none" id="error-text-p1">Error results: <h6>';
            print '<button id="hide" class="btn btn-primary" onclick="toggleVisibility(this.id)">Hide</button>';
         print '</div>';
      print '</div>';
      print '<br>';
      print '<form action="" method="POST" enctype="multipart/form-data"><input type="hidden" name="token" value="'.newToken().'">';
         print '<div class="row">
                     <div class="col">
                        <select style="width: 100%" name="image-type">
                           <option>serverschrank vorher</option>
                           <option>serverschrank nachher</option>
                           <option>arbeitssplaty nachher</option>
                           <option>seriennummer router</option>
                           <option>seriennummer firewall</option>
                           <option>image abnahmeprotokoll/testprotokoll</option>
                        </select>
                     </div>
                     <div class="col">
                        <input style="width: 100%" type="file" name="files[]" multiple>
                     </div>
                     <div class="col">
                        <input style="width: 100%" type="submit" name="submit" value="Upload">
                     </div>
               </div>';
      print '</form>';
      print '<br>';
      if($result[2]){
         // print '<div class="container">';
            print '<div class="row mb-2">';
            $imagesGroup = json_decode(base64_decode($result[2]));
            foreach($imagesGroup as $group){
               print '<div class="col-12 mt-2" style="background: #aaa;padding: 5px 0 5px 10px;">';
                  print $group->type;
               print '</div>';
               foreach($group->images as $image){
                  print '<div class="col-3 col-md-3 mt-2">';
                     print '<img class="group-image" src="formsImages/'.$image.'" style="width:100%; height:13rem" onclick="showImageFull(this.src)">';
                  print '</div>';
               }
            }
            print '</div>';
         // print '</div>';
      }
      print '<div>';
         print '<label>Additional Notes*</label>';
         print '<br>';
         print '<textarea name="additional-notes" required></textarea>';
      print '</div>';
      print '<br>';
      print '<div class="row">';
         print '<div class="col" style="text-align: center;">';
            print '<canvas id="signatureCanvasSesoco" class="signature-canvas" name="signatureCanvasSesoco"></canvas>';
            print '<br>';
            print '<input type="text" name="employee-name" placeholder="Name of Technician" value="'.$user->firstname.' '.$user->lastname.'">';
            print '<i class="ico-times clearCanvas" role="img" aria-label="Cancel"  onClick="clearCanvas(\'signatureCanvasSesoco\')"></i>';
            print '<br>';
         print '</div>';
         print '<div class="col" style="text-align: center;">';
            print '<canvas id="signatureCanvasCustomer" class="signature-canvas" name="signatureCanvasCustomer"></canvas>';
            print '<br>';
            print '<input type="text" name="customer-name" placeholder="Name of Customer">';
            print '<i class="ico-times clearCanvas" role="img" aria-label="Cancel"  onClick="clearCanvas(\'signatureCanvasCustomer\')"></i>';
            print '<br>';
         print '</div>';
      print '</div>';
   // }

 print '</div>';
 print '<div class="row mt-3">';
   print '<div class="col right">';
      print '<input type="submit" value="Save" id="save-form">';
   print '</div>';
   print '<div class="col left">';
      print '<input type="submit" value="Close" id="close-btn">';
   print '</div>';
 print '</div>';

 print '
         <div id="popup" class="closed">
            <div class="row">
               <div class="col-12">
                  <img id="popupImage" src="" style="width:100%">
               </div>
            </div>
            <div class="row mt-2">
               <div class="col-12" style="text-align: center">
                  <button class="btn btn-danger" id="closePopupBtn">Close</button>
               </div>
            </div>
         </div>';     


 $dir = DOL_DOCUMENT_ROOT.'/formsImages/';
 if(!is_dir($dir)){
    mkdir($dir);
 }
 
 $imagesList = array();
 $images = array();	

 $query = 'SELECT images FROM llx_tec_forms WHERE fk_ticket = '.$ticketId.' AND fk_user = '.$user->id.' AND fk_store = '.$storeid.' AND fk_soc = '.$object->fk_soc.';';
 $list = $db->query($query)->fetch_row();

 if($list[0 != null]) {

    $arr = json_decode(base64_decode($list[0]));
    foreach($arr as $elm){
       array_push($imagesList, $elm);
    }
 }


 if(isset($_POST['submit'])) {

    $allowed_types = array('jpg', 'png', 'jpeg', 'gif');
     
    $maxsize = 1024 * 1024;
    
    if(!empty(array_filter($_FILES['files']['name']))) {
       
  
       foreach ($_FILES['files']['tmp_name'] as $key => $value) {
          
          $file_tmpname = $_FILES['files']['tmp_name'][$key];
          $file_name = $_FILES['files']['name'][$key];
          $file_size = $_FILES['files']['size'][$key];
          $imageQuality = 80;
          $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
          
          $filepath = $dir.$file_name;
  

          if(in_array(strtolower($file_ext), $allowed_types)) {

                if(file_exists($filepath)) {
                   $fileN = time().$file_name;
                   $filepath = $dir.$fileN;
                   $compressedImage = $compress->compress_image($file_tmpname, $filepath, $imageQuality);
                   if( $compressedImage) {
                      array_push($images, $fileN);
                   }else {                    
                      dol_htmloutput_errors("Error uploading {$file_name} <br />");
                   }
                }else {
                   $compressedImage = $compress->compress_image($file_tmpname, $filepath, $imageQuality);
                   if($compressedImage) {
                      array_push($images,$file_name);
                   }else {                    
                      dol_htmloutput_errors("Error uploading {$file_name} <br />");
                   }
                }      
          }else {
             dol_htmloutput_errors("Error uploading {$file_name} ");
             dol_htmloutput_errors("({$file_ext} file type is not allowed)<br / >");
          }
       }
    }else {
       dol_htmloutput_errors("No files selected.");
    }
    $node = [
       "type" => $_POST['image-type'],
       "images" => $images
    ];
    array_push($imagesList, $node);
    $list = json_encode($imagesList);
    if($result[1]){
         $sql = 'UPDATE llx_tec_forms set images = "'.base64_encode($list).'" WHERE fk_ticket = '.$ticketId.' AND fk_user = '.$user->id.' AND fk_store = '.$storeid.' AND fk_soc = '.$object->fk_soc.';';
         $db->query($sql,0,'ddl');
    } else {

    }
 }








   print ' <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
         <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.5.0-beta4/html2canvas.min.js"></script>
         <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/5.3.2/jspdf.plugin.autotable.min.js"></script>';




   $parameters = json_decode(base64_decode($result[1]));
   $encoded_params = json_encode($parameters);
   if($result[1]){
      print '<script>';
        // deselect radio button when click on twice
        print '
              let parameters = \'' . $encoded_params . '\';
              console.log(JSON.parse(parameters));
              let decodedParameters = JSON.parse(parameters);
     
              decodedParameters.forEach(param => {
              const inputElement = document.querySelector(`[name="${param.name}"]`);
                  if (inputElement) {
                     // Set the value based on the input type
                     switch (inputElement.type) {
                        case "text":
                        case "textarea":
                        case "select-one":
                        case "number":
                        case "time":
                           inputElement.value = param.value;
                        break;
                        case "checkbox":
                           inputElement.checked = param.value === "1"; // Checked if value is "1"
                        break;
                     }
                  } else {
                     console.warn(`Input element with name "${param.name}" not found`);
                  }
                  const inputElement1 = document.querySelector(`[name="${param.name}"][value="${param.value}"]`);
                  if (inputElement1) {
                     // Set the value based on the input type
                     switch (inputElement1.type) {
                        case "radio":
                           inputElement1.checked = inputElement1.value === param.value;
                        break;
                     }
                  }
                  if ((param.name === "signatureCanvasCustomer" || param.name === "signatureCanvasSesoco") && param.value.startsWith("data:image/")) {
                     const canvas = document.getElementById(param.name);
                     if (canvas) {
                        const context = canvas.getContext("2d");
                        const img = new Image();
                        img.onload = function() {
                           context.clearRect(0, 0, canvas.width, canvas.height); 
                           context.drawImage(img, 0, 0);
                        };
                        img.src = param.value;
                     } else {
                        console.warn(`Canvas element with id "${param.name}" not found`);
                     }
                  }
              });
        ';
        print '</script>';

   }
   print '<script>';
    
   print '
         function showImageFull(src){
            const openPopupBtn = document.getElementById("openPopupBtn");
            const closePopupBtn = document.getElementById("closePopupBtn");
            const popup = document.getElementById("popup");
            const popupSrc = document.getElementById("popupImage");

            popupSrc.src = src;
            popup.classList.remove("closed");

            closePopupBtn.addEventListener("click", function() {
               popup.classList.add("closed");
            });
         }
   ';

   print '
         let lastSelectedRadio = null;
         document.querySelectorAll(\'input[type="radio"]\').forEach(radio => {
               radio.addEventListener("click", function(event) {
                  if (this === lastSelectedRadio) {
                     this.checked = false; // Deselect the radio button
                     lastSelectedRadio = null; // Reset last selected radio button
                  } else {
                     lastSelectedRadio = this; // Update last selected radio button
                  }
               });
         });';
   // end deselect radio button when click on twice

   // check P1, P2 table rows
   print ' 
         document.addEventListener("DOMContentLoaded", function(){
         var opt1 = document.getElementById("table1_1");
         var opt2 = document.getElementById("table1_2");
         var opt3 = document.getElementById("table1_3");
         var opt4 = document.getElementById("table1_4");
         var opt6 = document.getElementById("table1_6");


      
         function checkTests() {

            const rows = document.querySelectorAll(\'#questions-table .oddeven\');
            let prio1Failed = false;
            let prio1RollbackFailed = false;
            let prio2Failed = false;
            //console.log(rows.length);
            for (let i = 0; i < rows.length - 4; i++) {
            //console.log("Iteration: " + i);
                     const row = rows[i];
                     const cells = row.children;
                     const prio = cells[2].textContent.trim();
                     
                     const notAvailable = cells[5].querySelector(\'input[type="checkbox"]\').checked;
                           if(notAvailable){
                     //console.log("Not available");
                     cells[3].querySelector(\'input[type="radio"]\').disabled = true;
                     cells[3].querySelector(\'input[type="radio"]\').checked = false;
                     cells[4].querySelector(\'input[type="radio"]\').disabled = true;
                     cells[4].querySelector(\'input[type="radio"]\').checked = false;
                     cells[6].querySelector(\'input[type="radio"]\').disabled = true;
                     cells[6].querySelector(\'input[type="radio"]\').checked = false;
                     cells[7].querySelector(\'input[type="radio"]\').disabled = true;
                     cells[7].querySelector(\'input[type="radio"]\').checked = false;
                     continue;
         }else if(!notAvailable){
         cells[3].querySelector(\'input[type="radio"]\').disabled = false;
                     cells[4].querySelector(\'input[type="radio"]\').disabled = false;
                     cells[6].querySelector(\'input[type="radio"]\').disabled = false;
                     cells[7].querySelector(\'input[type="radio"]\').disabled = false;
         }
                     const testPassed = cells[3].querySelector(\'input[type="radio"]\').checked;
                     const testFailed = cells[4].querySelector(\'input[type="radio"]\').checked;
                     const rollbackPassed = cells[6].querySelector(\'input[type="radio"]\').checked;
                     const rollbackFailed = cells[7].querySelector(\'input[type="radio"]\').checked;
                     // console.log(row);
                     // console.log(prio);
                     // console.log(cells);
                     // console.log(testPassed);
                     // console.log(testFailed);
                     // console.log(rollbackPassed);
                     // console.log(rollbackFailed);
               

                     if (prio === "1") {
                        //console.log("Prio : 1");
                        if (testFailed) {
                           //console.log("Prio 1 test failed");
                           prio1Failed = true;
                           if (rollbackFailed) {
                                 //console.log("Prio 1 rollback failed");
                                 prio1RollbackFailed = true;
                           }
                        }else if(testPassed && opt4.checked == true && rollbackFailed == true){
                           prio1Failed = true;
                           prio1RollbackFailed = true;
                        }
                     } else if (prio === "2" && testFailed) {
                        prio2Failed = true;
                     }
               }

               if (prio1Failed) {
                     opt1.checked = false;
                     if (prio1RollbackFailed) {
                        opt6.checked = true;
                     } else {
                        opt4.checked = true;
                     }
               } else if (prio2Failed && prio1Failed == false) {
                     opt1.checked = false;
                     opt2.checked = true;
               }else if(prio1Failed == false && prio2Failed == false){
                     opt1.checked = true;
                     
               }
                     testTracker();
            //          console.log(prio1Failed + " " + prio1RollbackFailed + " " + prio2Failed);
            //  console.log(`opt1: ${opt1}, opt2: ${opt2}, opt3: ${opt3}, opt4: ${opt4}`);
         }
         checkTests();
         document.querySelectorAll(\'#questions-table input[type="radio"]\').forEach(radio => {
            radio.addEventListener("change", checkTests);
         });
         document.querySelectorAll(\'#questions-table input[type="checkbox"]\').forEach(checkbox => {
            checkbox.addEventListener("change", checkTests);
         });

         function testTracker(){
         document.getElementById("error-text").style.display = "none";
         document.getElementById("error-text-p1").style.display = "none";
         toggleVisibility("hide");

         let rows = document.querySelectorAll(\'#questions-table .oddeven\');
            if(opt1.checked){
               document.getElementById("error-text").style.display = "none";
            }else if(opt2.checked){
               document.getElementById("error-text").style.display = "block";
               document.getElementById("error-text").textContent = "Error results: ";
               for(let i = 0; i < rows.length - 4; i++){
               const row = rows[i];
               const cells = row.children;
               const prio = cells[2].textContent.trim();
               if(prio == 2){
                  const testFailed = cells[4].querySelector(\'input[type="radio"]\').checked;
                  if(testFailed){
                     const secondColumnText = row.querySelector("td:nth-child(1)").textContent.trim();
                     document.getElementById("error-text").textContent += secondColumnText+ ", ";
                  }
               }
         }
               document.getElementById("error-text").textContent = document.getElementById("error-text").textContent.slice(0, -2);
            }else if(opt3.checked){
               document.getElementById("error-text").style.display = "none";
               document.querySelectorAll(\'#questions-table input[type="checkbox"]\').forEach(checkbox => {
                  checkbox.disabled = true;
               });
               document.querySelectorAll(\'#questions-table input[type="radio"]\').forEach(radio => {
                  radio.disabled = true;
               });
            }else if(opt4.checked){
               document.getElementById("error-text").style.display = "block";
               document.getElementById("error-text").textContent = "Error results: ";
               for(let i = 0; i < rows.length - 4; i++){
               const row = rows[i];
               const cells = row.children;
               const prio = cells[2].textContent.trim();
               if(prio == 1){
                  const testFailed = cells[4].querySelector(\'input[type="radio"]\').checked;
                  if(testFailed){
                     const secondColumnText = row.querySelector("td:nth-child(1)").textContent.trim();
                     document.getElementById("error-text").textContent += secondColumnText+ ", ";
                  }
               }
         }
               document.getElementById("error-text").textContent = document.getElementById("error-text").textContent.slice(0, -2);
            }else if(opt6.checked){
               toggleVisibility("show");
               document.getElementById("error-text-p1").style.display = "block";
               document.getElementById("error-text-p1").textContent = "Error results: ";
               for(let i = 0; i < rows.length - 4; i++){
               const row = rows[i];
               const cells = row.children;
               const prio = cells[2].textContent.trim();
               if(prio == 1){
                  //const testFailed = cells[4].querySelector(\'input[type="radio"]\').checked;
                  const rollbackFailed = cells[7].querySelector(\'input[type="radio"]\').checked;
                  if(rollbackFailed){
                     const secondColumnText = row.querySelector("td:nth-child(1)").textContent.trim();
                     document.getElementById("error-text-p1").textContent += secondColumnText+ ", ";
                  }
               }


         }
               document.getElementById("error-text-p1").textContent = document.getElementById("error-text-p1").textContent.slice(0, -2);
            }

         }

         });';
   // end check P1, P2 table rows
   

   //save form
   print '$("#save-form").on("click", function() {
               const formData = new FormData();
               
               let parameters = [];

               // 1. Capture serialized form data
               const serializedData = $("#report-body").find(":input").serializeArray();
               serializedData.forEach(item => {
                  parameters.push({ name: item.name, value: item.value });
               });

               // 2. Capture checked checkboxes
               $("#report-body input[type=\'checkbox\']").each(function() {
                  parameters.push({ name: this.name, value: this.checked ? "1" : "0" });
               });

               // 3. Capture signature canvases
               $("#report-body canvas").each(function() {
                  parameters.push({ name: this.id, value: this.toDataURL() });  
               });

               // Add the full HTML content
               formData.append("form", $("#report-body").html());
               formData.append("parameters", JSON.stringify(parameters));
               // Add other required fields
               formData.append("storeId", "'.$storeid.'");
               formData.append("userId", "'.$user->id.'");
               formData.append("ticketId", "'.$ticketId.'");
               formData.append("socId", "'.$object->fk_soc.'");
               
               savePDFOnServer(formData);
            });

            function savePDFOnServer(formData) {
               $.ajax({
                  url: "tecform.php",
                  type: "POST",
                  data: formData,
                  processData: false,
                  contentType: false,
                  success: function(response) {
                        console.log(response);
                  },
                  error: function(xhr, status, error) {
                        console.error("Request failed with status: " + xhr.status + ", Error: " + error);
                  }
               });
            }';
   //end save form

   // calculate distance/times
   print '
         document.addEventListener("DOMContentLoaded", function() {
               const arrivalInput = document.getElementById("input-time-arrival");
               const departureInput = document.getElementById("input-time-departure");
               const workStartInput = document.getElementById("input-work-start");
               const workEndInput = document.getElementById("input-work-end");
               const durationHoursInput = document.getElementById("input-duration-hours");
               const durationMinutesInput = document.getElementById("input-duration-minutes");
         
               function floorTimeToNearest15(date) {
                  const minutes = date.getMinutes();
                  date.setMinutes(minutes - (minutes % 15), 0, 0); // floor to nearest 15 minutes
                  return date;
               }
         
               function ceilTimeToNearest15(date) {
                  const minutes = date.getMinutes();
                  date.setMinutes(minutes + (15 - (minutes % 15)) % 15, 0, 0); // ceil to nearest 15 minutes
                  return date;
               }
         
               function calculateDuration(endTime, startTime) {
                  let duration = (endTime - startTime) / (1000 * 60); // duration in minutes
                  const hours = Math.floor(duration / 60);
                  const minutes = duration % 60;
                  return { hours, minutes };
               }
         
               function updateDuration() {
                  if (arrivalInput.value && departureInput.value) {
                     let arrivalTime = new Date();
                     let departureTime = new Date();
         
                     const [arrHours, arrMinutes] = arrivalInput.value.split(":").map(Number);
                     const [depHours, depMinutes] = departureInput.value.split(":").map(Number);
         
                     arrivalTime.setHours(arrHours, arrMinutes, 0, 0);
                     departureTime.setHours(depHours, depMinutes, 0, 0);
         
                  if(arrivalTime < departureTime) {
                        alert("Ankunftszeit kann nicht vor Abfahrtszeit sein!");
                        return;
                     }
         
                     const { hours, minutes } = calculateDuration(arrivalTime, departureTime);
                     durationHoursInput.value = hours;
                     durationMinutesInput.value = minutes;
                  }
                  
               }
         
               arrivalInput.addEventListener("blur", function() {
                  if (arrivalInput.value) {
                     let arrivalTime = new Date();
                     const [hours, minutes] = arrivalInput.value.split(":").map(Number);
                     arrivalTime.setHours(hours, minutes, 0, 0);
                     arrivalTime = floorTimeToNearest15(arrivalTime);
                     arrivalInput.value = arrivalTime.toTimeString().substring(0, 5);
                     workStartInput.value = arrivalTime.toTimeString().substring(0, 5);
                     updateDuration();
                  }
               });
         
               departureInput.addEventListener("blur", function() {
                  if (departureInput.value) {
                     let departureTime = new Date();
                     const [hours, minutes] = departureInput.value.split(":").map(Number);
                     departureTime.setHours(hours, minutes, 0, 0);
                     departureTime = floorTimeToNearest15(departureTime);
                     departureInput.value = departureTime.toTimeString().substring(0, 5);
                     updateDuration();
                  }
               });
         
               workEndInput.addEventListener("blur", function() {
                  if (workEndInput.value) {
                     let workEndTime = new Date();
                     const [hours, minutes] = workEndInput.value.split(":").map(Number);
                     workEndTime.setHours(hours, minutes, 0, 0);
                     workEndTime = ceilTimeToNearest15(workEndTime);
                     workEndInput.value = workEndTime.toTimeString().substring(0, 5);
                  }
               });
         
               workStartInput.addEventListener("blur", function() {
                  if (workStartInput.value) {
                     let workStartTime = new Date();
                     const [hours, minutes] = workStartInput.value.split(":").map(Number);
                     workStartTime.setHours(hours, minutes, 0, 0);
                     workStartTime = floorTimeToNearest15(workStartTime);
                     workStartInput.value = workStartTime.toTimeString().substring(0, 5);
                     arrivalInput.value = workStartTime.toTimeString().substring(0, 5);
                     updateDuration();
                  }
               });
            });';
   // end calculate distance/times  

   // show/hide ruckbau table
   print '
         function toggleVisibility(id) {
            var button = document.getElementById("ruckbau-btn");
            var table = document.getElementById("ruckbau-table");
            if(id == "show"){
               button.style.display = "none";
               table.style.display = "block";
            } else {
               button.style.display = "block";
               table.style.display = "none";
            }
         }';
   //end show/hide ruckbau table
   
   // print '    
   //       document.getElementById("generate-pdf").addEventListener("click", () => {
   //          const { jsPDF } = window.jspdf;

   //          // Create a temporary div to hold the content
   //          const tempDiv = document.createElement("div");
   //          tempDiv.innerHTML = document.getElementById("report-body").innerHTML;
   //          document.body.appendChild(tempDiv);

   //          // Set the temp div to a fixed desktop width
   //          tempDiv.style.width = "2000px";
   //          tempDiv.style.position = "absolute";
   //          tempDiv.style.height = "2000px";

   //          html2canvas(tempDiv, {
   //             scale: 2,
   //             useCORS: true,
   //             logging: true,
   //             backgroundColor: "#fff"
   //          }).then(canvas => {

   //             // Remove the temporary div
   //             document.body.removeChild(tempDiv);

   //             const imgData = canvas.toDataURL("image/jpg");

   //             const pdf = new jsPDF("p", "mm", "a4");
   //             const pageWidth = 210;
   //             const pageHeight = 297;
   //             const padding = 5;
   //             const imgWidth = pageWidth - 2 * padding;
   //             const imgHeight = (canvas.height * imgWidth) / canvas.width;


   //             pdf.addImage(imgData, "PNG", padding, padding, imgWidth, imgHeight);

   //             pdf.save("exported-table.pdf");
   //          }).catch(error => {
   //             console.error("Error in html2canvas:", error);
   //          });
   //       });';

   // draw signatures
   print '
      const canvases = document.getElementsByClassName("signature-canvas");

      Array.from(canvases).forEach((canvas) => {
         const context = canvas.getContext("2d");
         let isDrawing = false;
         let lastX = 0;
         let lastY = 0;

         function startDrawing(event) {
            isDrawing = true;
            [lastX, lastY] = getCoordinates(event);
         }

         function draw(event) {
            if (!isDrawing) return;
            const [x, y] = getCoordinates(event);
            context.beginPath();
            context.moveTo(lastX, lastY);
            context.lineTo(x, y);
            context.strokeStyle = "#000";
            context.lineWidth = 2;
            context.stroke();
            [lastX, lastY] = [x, y];
         }

         function stopDrawing() {
            isDrawing = false;
         }

         function getCoordinates(event) {
            if (event.touches) {
               const rect = canvas.getBoundingClientRect();
               return [
                  event.touches[0].clientX - rect.left,
                  event.touches[0].clientY - rect.top
               ];
            } else {
               return [event.offsetX, event.offsetY];
            }
         }

         canvas.addEventListener("mousedown", startDrawing);
         canvas.addEventListener("mousemove", draw);
         canvas.addEventListener("mouseup", stopDrawing);
         canvas.addEventListener("mouseleave", stopDrawing);

         // Add touch event listeners
         canvas.addEventListener("touchstart", (event) => {
            event.preventDefault();
            startDrawing(event);
         });
         canvas.addEventListener("touchmove", (event) => {
            event.preventDefault();
            draw(event);
         });
         canvas.addEventListener("touchend", stopDrawing);
         canvas.addEventListener("touchcancel", stopDrawing);
      });';
      // Clear canvas
      // Only clear the canvas that was clicked
      print 'function clearCanvas(canvasId) {
            const canvas = document.getElementById(canvasId);
            if (canvas) {
                const context = canvas.getContext("2d");
                context.clearRect(0, 0, canvas.width, canvas.height);
            } else {
                console.warn(`Canvas element with id "${canvasId}" not found`);
            }
        }';
   // end draw signatures


   // generate pdf
   // print '      
   //       document.getElementById("generate-pdf").addEventListener("click", () => {
   //             const { jsPDF } = window.jspdf;

   //             // Create a temporary div to hold the content
   //             const tempDiv = document.createElement("div");
   //             tempDiv.innerHTML = document.getElementById("report-body").innerHTML;
   //             document.body.appendChild(tempDiv);

   //             // Set the temp div to match A4 aspect ratio
   //             const a4Width = 210; // A4 width in mm
   //             const a4Height = 297; // A4 height in mm
   //             const dpi = 130; // Screen resolution
   //             const a4WidthPx = Math.floor(a4Width * (dpi / 25.4)); // Convert mm to px
   //             const a4HeightPx = Math.floor(a4Height * (dpi / 25.4)); // Convert mm to px

   //             tempDiv.style.width = `${a4WidthPx}px`;
   //             tempDiv.style.position = "absolute";

   //             html2canvas(tempDiv, {
   //                scale: 4,
   //                useCORS: true,
   //                logging: true,
   //                backgroundColor: "#fff"
   //             }).then(canvas => {

   //                // Remove the temporary div
   //                document.body.removeChild(tempDiv);

   //                const imgData = canvas.toDataURL("image/png");

   //                const pdf = new jsPDF("p", "mm", "a4");
   //                const pageWidth = pdf.internal.pageSize.getWidth();
   //                const pageHeight = pdf.internal.pageSize.getHeight();
   //                const padding = 5;
   //                const imgWidth = pageWidth - 2 * padding;
   //                const imgHeight = (canvas.height * imgWidth) / canvas.width;

   //                if (imgHeight <= pageHeight - 2 * padding) {
   //                   pdf.addImage(imgData, "PNG", padding, padding, imgWidth, imgHeight);
   //                } else {
   //                   pdf.addImage(imgData, "PNG", padding, padding, imgWidth, pageHeight - 2 * padding);
   //                }

   //                pdf.save("exported-table.pdf");
   //             }).catch(error => {
   //                console.error("Error in html2canvas:", error);
   //             });
   //       });';
   // end generate pdf      
 print '</script>';

 print '<style>';
    print ' .textfield, textarea, select, .task-message {
                border: 1px solid #8080804a;
                background: #80808026;
                padding: 5px;
                border-radius: 5px;
                width: 100%
            }';
    print ' input {
                border: 1px solid #8080804a;
                background: #80808026;
                padding: 5px;
                border-radius: 5px;
                width: 50%
            }';
    print ' textarea {
                width: 100%;
                height: 200px
            }';
   print '.signature-canvas {
                border: 1px solid #ccc;
            }';
   print '#input-time {
               width: 100px
            }';
   print '[class^="ico-"], [class*=" ico-"] {
               font: normal 1em/1 Arial, sans-serif;
               display: inline-block;
               color: red;
            }
            .ico-times::before { content: "\2716"; }';
   print '.closed {
               display: none;
            }

          #popup {
               position: fixed;
               top: 50%;
               left: 50%;
               transform: translate(-50%, -50%);
               background-color: white;
               padding: 20px;
               border: 1px solid #ddd;
               border-radius: 5px;
               box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
               z-index: 10; /* Make sure the popup is above other elements */
            }

          #popup button {
               color: white;
               padding: 10px 20px;
               border: none;
               border-radius: 4px;
               cursor: pointer;
            }
          @media (max-width: 575px) { /* Target screens smaller than 576px (i.e. mobiles) */
               #popup {
                  width: 100vw; /* Set width to 100% of viewport width */
               }
               .group-image {
                  height: 5rem!important;
               }
            }';
 print '</style>';
