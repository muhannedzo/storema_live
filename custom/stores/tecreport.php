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
dol_include_once('/ticket/class/ticket.class.php');
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/stores/class/branch.class.php';
dol_include_once('/ticket/class/ticket.class.php');
dol_include_once('/societe/class/societe.class.php');


/*
 * View
 */

 $form = new Form($db);
 $formfile = new FormFile($db);
 $ticketId = GETPOST('id', 'int');
 
 llxHeader("", $langs->trans("Report"));
 
 print load_fiche_titre($langs->trans("Ticket Report"), '', 'stores.png@stores');
 //////////////////////////////////////////////////////////////////////////////////////////
 
 $object = new Ticket($db);
 $object->fetch($ticketId);
 $socid = $object->socid;
 $storeid = $object->array_options["options_fk_store"];
 $company = new Societe($db);
 $company->fetch($socid);
 $store = new Branch($db);
 $store->fetch($storeid);
 //task message
 print '<div class="task-message">';
    print 'Task Message:';
    print '<br>';
    print $object->message;
    print '<br>';
    print 'Date: '.date("d.m.y H:i", $object->datec);
 print '</div>';
 print '<h3>Company Information</h3>';
 print '<div>';
    print '<label>Ticket Number*</label>';
    print '<br>';
    print '<input class="textfield" type="text" value="'.$object->ref.'" required>';
 print '</div>';
 print '<br>';
 print '<div>';
    print '<label>Company*</label>';
    print '<br>';
    print '<input class="textfield" type="text" value="'.$company->nom.'" required>';
 print '</div>';
 print '<br>';
 print '<div>';
    print '<label>Store*</label>';
    print '<br>';
    print '<input class="textfield" type="text" value="'.$store->b_number.'" required>';
 print '</div>';
 print '<br>';
 print '<div>';
    print '<label>Street*</label>';
    print '<br>';
    print '<input class="textfield" type="text" value="'.$store->street.','. $store->zip_code.' '. $store->city.'" required>';
 print '</div>';
 print '<br>';
 print '<div>';
    print '<label>Work assignment*</label>';
    print '<br>';
    print '<textarea required></textarea>';
 print '</div>';
 print '<br>';
 print '<div>';
    print '<label>Required Mindset-PSA*</label>';
    print '<br>';
    print '<select required>
               <option>Test 1</option>
               <option>Test 2</option>
               <option>Test 3</option>
               <option>Test 4</option>
               <option>Test 5</option>
               <option>Test 6</option>
               <option>Test 7</option>
           </select>';
 print '</div>';
 print '<br>';
 print '<div>';
    print '<label>Technician Notes*</label>';
    print '<br>';
    print '<textarea required></textarea>';
 print '</div>';
 print '<br>';
 print '<div class="container">';
   print '<div class="row mb-3">';
      print '<div class="col-12 col-md-3 d-flex align-items-center">';
         print '<label for="input-time-departure" class="form-label mb-0">Time Departure: </label>';
      print '</div>';
      print '<div class="col-12 col-md-9">';
         print '<input type="time" id="input-time-departure" class="form-control">';
      print '</div>';
   print '</div>';
   print '<div class="row mb-3">';
      print '<div class="col-12 col-md-3 d-flex align-items-center">';
         print '<label for="input-time-arrival" class="form-label mb-0">Time Arrival: </label>';
      print '</div>';
      print '<div class="col-12 col-md-9">';
         print '<input type="time" id="input-time-arrival" class="form-control">';
      print '</div>';
   print '</div>';
   print '<div class="row mb-3">';
      print '<div class="col-12 col-md-3 d-flex align-items-center">';
         print '<label class="form-label mb-0">Duration of Trip: </label>';
      print '</div>';
      print '<div class="col-12 col-md-9 d-flex">';
         print '<input type="number" id="input-duration-hours" class="form-control me-2" style="max-width: 70px;" placeholder="h">';
         print '<span class="align-self-center me-2">h :</span>';
         print '<input type="number" id="input-duration-minutes" class="form-control" style="max-width: 70px;" max="60" placeholder="m">';
         print '<span class="align-self-center me-2">m</span>';
      print '</div>';
   print '</div>';
   print '<div class="row mb-3">';
      print '<div class="col-12 col-md-3 d-flex align-items-center">';
         print '<label for="input-km" class="form-label mb-0">KM: </label>';
      print '</div>';
      print '<div class="col-12 col-md-9">';
         print '<input type="number" id="input-km" class="form-control">';
      print '</div>';
   print '</div>';
   print '<div class="row mb-3">';
      print '<div class="col-12 col-md-3 d-flex align-items-center">';
         print '<label for="input-work-start" class="form-label mb-0">Work Start: </label>';
      print '</div>';
      print '<div class="col-12 col-md-9">';
         print '<input type="time" id="input-work-start" class="form-control">';
      print '</div>';
   print '</div>';
   print '<div class="row mb-3">';
      print '<div class="col-12 col-md-3 d-flex align-items-center">';
         print '<label for="input-work-end" class="form-label mb-0">Work End: </label>';
      print '</div>';
      print '<div class="col-12 col-md-9">';
         print '<input type="time" id="input-work-end" class="form-control">';
      print '</div>';
   print '</div>';
 print '</div>';
//  print '<div class="row">';
//    print '<div class="col-12 col-md-6">';
//       print '<label>Time departure: </label>';
//       print '<input type="time" id="input-time">';
//    print '</div>';
//    print '<div class="col-12 col-md-6">';
//       print '<label>Time Arrival: </label>';
//       print '<input type="time" id="input-time">';
//    print '</div>';
//  print '</div>';
//  print '<div class="row">';
//    print '<div class="col-12 col-md-6">';
//       print '<label>Duration of Trip: </label>';
//       print '<input type="number" id="input-time"> h : <input type="number" id="input-time" max="60"> m';
//    print '</div>';
//    print '<div class="col-12 col-md-6">';
//       print '<label>KM: </label>';
//       print '<input type="number" id="input-time">';
//    print '</div>';
//  print '</div>';
//  print '<div class="row">';
//    print '<div class="col-12 col-md-6">';
//       print '<label>Work Start: </label>';
//       print '<input type="time" id="input-time">';
//    print '</div>';
//    print '<div class="col-12 col-md-6">';
//       print '<label>Work End: </label>';
//       print '<input type="time" id="input-time">';
//    print '</div>';
//  print '</div>';
//  print '<div class="row">';
//    print '<div class="col-12 col-md-6">';
//       print '<label>Waiting Time: </label>';
//       print '<input type="time" id="input-time">';
//    print '</div>';
//  print '</div>';
 print '<br>';
 print '<div class="row">';
   print '<div class="col-12 div-table-responsive-no-min">';
      print '<table class="noborder centpercent">';
         print '<tr class="liste_titre">';
            print '<th colspan="4"></th>';
            print '<th colspan="2">VKST 4.0</th>';
            print '<th colspan="2">NUR NACH RUCKBAUI VKST 4.0</th>';
            print '<th colspan="1"></th>';
         print '</tr>';
         print '<tr class="liste_titre">';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1">Test NR</td>';
            print '<td colspan="1">Testfalle</td>';
            print '<td colspan="1">Prio</td>';
            print '<td colspan="1">Erfolgr.</td>';
            print '<td colspan="1">Fehlgescht.</td>';
            print '<td colspan="1">Erfolgr.</td>';
            print '<td colspan="1">Fehlgescht.</td>';
            print '<td colspan="1" contenteditable="true"></td>';
         print '</tr>';
         print '<tr class="oddeven">';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
         print '</tr>';
         print '<tr class="oddeven">';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
         print '</tr>';
         print '<tr class="oddeven">';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
         print '</tr>';
         print '<tr class="oddeven">';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
         print '</tr>';
         print '<tr class="oddeven">';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
         print '</tr>';
         print '<tr class="oddeven">';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
         print '</tr>';
         print '<tr class="oddeven">';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
         print '</tr>';
         print '<tr class="oddeven">';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
         print '</tr>';
         print '<tr class="oddeven">';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
         print '</tr>';
         print '<tr class="oddeven">';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
         print '</tr>';
         print '<tr class="oddeven">';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
         print '</tr>';
         print '<tr class="oddeven">';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
         print '</tr>';
         print '<tr class="oddeven">';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
         print '</tr>';
         print '<tr class="oddeven">';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
         print '</tr>';
         print '<tr class="oddeven">';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
         print '</tr>';
         print '<tr class="oddeven">';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
         print '</tr>';
         print '<tr class="oddeven">';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
         print '</tr>';
         print '<tr class="oddeven">';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
         print '</tr>';
         print '<tr class="oddeven">';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
         print '</tr>';
         print '<tr class="oddeven">';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
         print '</tr>';
         print '<tr class="oddeven">';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
         print '</tr>';
         print '<tr class="oddeven">';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
         print '</tr>';
         print '<tr class="oddeven">';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
         print '</tr>';
         print '<tr class="oddeven">';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
         print '</tr>';
         print '<tr class="oddeven">';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1"><input type="checkbox"></td>';
            print '<td colspan="1" contenteditable="true"></td>';
         print '</tr>';
      print '</table>';
   print '</div>';
 print '</div>';
 print '<br>';
 print '<div>';
    print '<label>Additional Notes*</label>';
    print '<br>';
    print '<textarea required></textarea>';
 print '</div>';
 print '<div class="row">
            <div class="col">
               <select style="width: 100%">
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
 print '<br>';
 print '<div class="row">';
   print '<div class="col" style="text-align: center;">';
      print '<canvas id="signatureCanvas1" class="signature-canvas"></canvas>';
      print '<br>';
      print '<input type="text" placeholder="Name of Technician" value="'.$user->firstname.' '.$user->lastname.'">';
      print '<br>';
   print '</div>';
   print '<div class="col" style="text-align: center;">';
      print '<canvas id="signatureCanvas1" class="signature-canvas"></canvas>';
      print '<br>';
      print '<input type="text" placeholder="Name of Customer">';
      print '<br>';
   print '</div>';
 print '</div>';











 print '<script>';
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
      });

';
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
   // print '.row {
   //             --bs-gutter-x: 1.5rem;
   //             --bs-gutter-y: 0;
   //             display: flex;
   //             flex-wrap: wrap;
   //             margin-top: calc(-1* var(--bs-gutter-y));
   //             margin-right: calc(-.5* var(--bs-gutter-x));
   //             margin-left: calc(-.5* var(--bs-gutter-x));
   //          }
   //          .col-6 {
   //             flex: 0 0 auto;
   //             width: 50%;
   //             text-align: center;
   //          }
   //          .col-3 {
   //             flex: 0 0 auto;
   //             width: 25%;
   //          }
   //          .col-12 {
   //             flex: 0 0 auto;
   //             width: 100%;
   //          }';
   print '#input-time {
               width: 100px
            }';
 print '</style>';