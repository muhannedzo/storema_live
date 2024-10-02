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

// var_dump($object->fk_user_assign);
// $sql = 'SELECT content, parameters, fk_user, images FROM llx_tec_forms WHERE fk_ticket = '.$object->id.' AND fk_store = '.$storeid.' AND fk_soc = '.$object->fk_soc.' AND fk_user = '.$object->fk_user_assign.';';
 
$sql = 'SELECT f.content, f.parameters, f.fk_user, f.images, t.fk_project, p.ref, p.rowid 
         FROM llx_tec_forms f 
            LEFT JOIN llx_ticket t ON t.rowid = f.fk_ticket
            LEFT JOIN llx_projet p ON p.rowid = t.fk_project 
         WHERE f.fk_ticket = '.$object->id.' AND f.fk_user = '.$object->fk_user_assign.' AND f.fk_store = '.$storeid.' AND f.fk_soc = '.$object->fk_soc.';';
$result = $db->query($sql)->fetch_all()[0];
// var_dump($sql);
$parameters = json_decode(base64_decode($result[1]));
$encoded_params = json_encode($parameters);
$projectId = $result[4];
$projectRef = $result[5];
// var_dump($projectRef);

$techUser->fetch($result[2]);
$techName = $techUser->firstname.' '.$techUser->lastname;


llxHeader("", $langs->trans("Report"));
$head = ticket_prepare_head($object);

print dol_get_fiche_head($head, 'tabTicketReport', $langs->trans("Ticket"), -1, 'ticket');


print load_fiche_titre($langs->trans("Reportübersicht - ").$project->title, '', '');

   $newcardbutton = dolGetButtonTitle($langs->trans('Edit'), '', 'fa fa-edit', dol_buildpath('/ticket/reportOverview.php', 1).'?id='.$id.'&action=edit', '', $permissiontoadd).' ';
   // var_dump(is_int(strpos(strtolower($company->name_alias), 'zeta')));
   if (!is_int(strpos(strtolower($company->name_alias), 'zeta'))) {
      $newcardbutton .= dolGetButtonTitle($langs->trans('CreateMail'), '', 'fa fa-mail-bulk', dol_buildpath('/ticket/reportOverview.php', 1).'?id='.$id.'&action=createMail', '', $permissiontoadd);
   }
   print '<div id="reportOptions">';
      print $newcardbutton;
   print '</div>';
   print '<br>';
   print '<div id="report-body">';
      if (strpos(strtolower($company->name_alias), 'zeta') !== false && strpos(strtolower($company->name_alias), 'zeta') >= 0) {
         
         $measuringDevice = "";
         $prufurDate = "";
         $timeArrival = "";
         $tripHours = "";
         $tripMinutes = "";
         $km = "";
         // var_dump($encoded_params);
         if($result){
            foreach ($parameters as $item) {
               if ($item->name === 'measuring-device') {
                  $measuringDevice = $item->value;
               }
               if ($item->name === 'prufur-date') {
                  $prufurDate = $item->value;
               }
               if ($item->name === 'time-departure') {
                  $timeDeparture = $item->value;
               }
               if ($item->name === 'time-arrival') {
                  $timeArrival = $item->value;
               }
               if ($item->name === 'trip-hours') {
                  $tripHours = $item->value;
               }
               if ($item->name === 'trip-minutes') {
                  $tripMinutes = $item->value;
               }
               if ($item->name === 'km') {
                  $km = $item->value;
               }
            }
         }

         print '<div class="row">';
            print '<div class="col-lg-6 col-xs-12 div-table-responsive-no-min">';
               print '<table class="noborder centpercent">';
                  print '<tr class="oddeven">';
                     print '<td>';
                        print 'Project';
                     print '</td>';
                     print '<td>';
                        print $project->title;
                     print '</td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td>';
                        print 'Ticket Nr';
                     print '</td>';
                     print '<td>';
                        print $object->getNomUrl();
                     print '</td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td>';
                        print 'External Ticket Nr';
                     print '</td>';
                     print '<td>';
                        print '<input type="text" name="ticket-external-number" value="'.$object->array_options["options_externalticketnumber"].'">';
                     print '</td>';
                  print '</tr>';
               print '</table>';
            print '</div>';
            // var_dump($object);
            
            print '<div class="col-lg-6 col-xs-12 div-table-responsive-no-min">';
               print '<table class="noborder centpercent">';
                  print '<tr class="oddeven">';
                     print '<td>';
                        print 'Prüfer';
                     print '</td>';
                     print '<td>';
                        print $user->firstname.' '.$user->lastname;
                     print '</td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td>';
                        print 'Messgerät';
                     print '</td>';
                     print '<td>';
                        print '<input name="measuring-device" type="text" value="'.$measuringDevice.'">';
                     print '</td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td>';
                        print 'Datum der Prüfung';
                     print '</td>';
                     print '<td>';
                        print '<input name="prufur-date" type="date" value="'.$prufurDate.'">';
                     print '</td>';
                  print '</tr>';
               print '</table>';
            print '</div>';
            
            print '<div class="col-lg-6 col-xs-12 div-table-responsive-no-min">';
               print '<table class="noborder centpercent">';
                  print '<tr class="oddeven">';
                     print '<td>';
                        print 'Kunde';
                     print '</td>';
                     print '<td>';
                        print $store->customer_name;
                     print '</td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td>';
                        print 'Straße / Nr';
                     print '</td>';
                     print '<td>';
                        print $store->street.', '.$store->house_number;
                     print '</td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td>';
                        print 'PLZ / Ort';
                     print '</td>';
                     print '<td>';
                        print $store->zip_code.', '.$store->city;
                     print '</td>';
                  print '</tr>';
               print '</table>';
            print '</div>';
            
            
            print '<div class="col-lg-6 col-xs-12 div-table-responsive-no-min">';
               print '<table class="noborder centpercent">';
                  print '<tr class="oddeven">';
                     print '<td>';
                        print 'Kontakt Person';
                     print '</td>';
                     print '<td>';
                        print '<input name="contact-person" type="text" value="'.$store->phone.'">';
                     print '</td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td>';
                        print 'Haustechniker';
                     print '</td>';
                     print '<td>';
                        print '<input name="haustechniker" type="text" value="'.$store->phone.'">';
                     print '</td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td>';
                        print 'Telefon';
                     print '</td>';
                     print '<td>';
                        print '<input name="telefon" type="text" value="'.$store->phone.'">';
                     print '</td>';
                  print '</tr>';
               print '</table>';
            print '</div>';
         print '</div>';

         print '<div class="row">';
            print '<div class="col-lg-6 col-xs-12 div-table-responsive-no-min">';
               print '<table class="noborder centpercent" id="times-table" style="border: none;">';
                  print '<tr class="oddeven" style="border: none; background: none">';
                     print '<td style="border: none;">';
                        print 'Time Departure:';
                     print '</td>';
                     print '<td style="border: none;">';
                        print '<input type="time" id="input-time-departure" name="time-departure" class="form-control" value="'.$timeDeparture.'">';
                     print '</td>';
                     print '<td style="border: none;">';
                        print 'Time Arrival:';
                     print '</td>';
                     print '<td style="border: none;">';
                        print '<input type="time" id="input-time-arrival" name="time-arrival" class="form-control" value="'.$timeArrival.'">';
                     print '</td>';
                  print '</tr>';
               print '</table>';
            print '</div>';
            print '<div class="col-lg-6 col-xs-12 div-table-responsive-no-min">';
               print '<table class="noborder centpercent" style="border: none">';
                  print '<tr class="oddeven" style="border: none; background: none">';
                     print '<td style="border: none;">';
                        print 'Duration of Trip:';
                     print '</td>';
                     print '<td class="d-flex h-100" style="border: none;">';
                        print '<input type="number" id="input-duration-hours" name="trip-hours" class="form-control me-2" style="max-width: 70px;" placeholder="h" value="'.$tripHours.'">';
                        print '<span class="align-self-center me-2">h :</span>';
                        print '<input type="number" id="input-duration-minutes" name="trip-minutes" class="form-control" style="max-width: 70px;" max="60" placeholder="m" value="'.$tripMinutes.'">';
                        print '<span class="align-self-center me-2">m</span>';
                     print '</td>';
                     print '<td style="border: none;">';
                        print 'KM:';
                     print '</td>';
                     print '<td style="border: none;">';
                        print '<input type="number" id="input-km" class="form-control" name="km" value="'.$km.'">';
                     print '</td>';
                  print '</tr>';
               print '</table>';
            print '</div>';
         print '</div>';

         // print '<div class="">';
         //    print '<div class="row">';
         //       print '<div class="col-6 col-md-3">';
         //          print '<div class="row mb-3">';
         //             print '<div class="col-5 col-md-5 d-flex align-items-center">';
         //                print '<label for="input-time-departure" class="form-label mb-0">Time Departure: </label>';
         //             print '</div>';
         //             print '<div class="col-7 col-md-7">';
         //                print '<input type="time" id="input-time-departure" name="time-departure" class="form-control" value="'.$timeDeparture.'">';
         //             print '</div>';
         //          print '</div>';
         //       print '</div>';
         //       print '<div class="col-6 col-md-3">';
         //          print '<div class="row mb-3">';
         //             print '<div class="col-5 col-md-5 d-flex align-items-center">';
         //                print '<label for="input-time-arrival" class="form-label mb-0">Time Arrival: </label>';
         //             print '</div>';
         //             print '<div class="col-7 col-md-7">';
         //                print '<input type="time" id="input-time-arrival" name="time-arrival" class="form-control" value="'.$timeArrival.'">';
         //             print '</div>';
         //          print '</div>';
         //       print '</div>';
         //       print '<div class="col-6 col-md-3">';
         //          print '<div class="row mb-3">';
         //             print '<div class="col-5 col-md-5 d-flex align-items-center">';
         //                print '<label class="form-label mb-0">Duration of Trip: </label>';
         //             print '</div>';
         //             print '<div class="col-7 col-md-7 d-flex">';
         //                print '<input type="number" id="input-duration-hours" name="trip-hours" class="form-control me-2" style="max-width: 70px;" placeholder="h" value="'.$tripHours.'">';
         //                print '<span class="align-self-center me-2">h :</span>';
         //                print '<input type="number" id="input-duration-minutes" name="trip-minutes" class="form-control" style="max-width: 70px;" max="60" placeholder="m" value="'.$tripMinutes.'">';
         //                print '<span class="align-self-center me-2">m</span>';
         //             print '</div>';
         //          print '</div>';
         //       print '</div>';
         //       print '<div class="col-6 col-md-3">';
         //          print '<div class="row mb-3">';
         //             print '<div class="col-5 col-md-5 d-flex align-items-center">';
         //                print '<label for="input-km" class="form-label mb-0">KM: </label>';
         //             print '</div>';
         //             print '<div class="col-7 col-md-7">';
         //                print '<input type="number" id="input-km" class="form-control" name="km" value="'.$km.'">';
         //             print '</div>';
         //          print '</div>';
         //       print '</div>';
         //    print '</div>';
         // print '</div>';
         print '<div>';
            print '<label>Additional Notes</label>';
            print '<br>';
            print '<textarea name="additional-notes" required style="height:150px"></textarea>';
         print '</div>';
         print '<br>';
         

         $rowsCount = "0";
         if($result){
            foreach ($parameters as $item) {
               if ($item->name === 'rows-counter') {
                  $rowsCount = $item->value;
                  break;
               }
            }
         }
         // var_dump($rowsCount);
         // $prufnummer = 
         print '<div class="row">';
            print '<div class="col-lg-12 col-xs-12 div-table-responsive-no-min" id="pieces-table">';
               print '<table class="noborder centpercent">';
                  print '<tr class="oddeven">';
                     print '<td colspan="19">';
                        print 'Messung';
                     print '</td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">';
                        print '<button id="add-row" class="btn btn-primary">+</button>';
                     print '</td>';
                     print '<td colspan="18" class="center">';
                        print 'Messungen';
                     print '</td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td>';
                        print 'Messung';
                     print '</td>';
                     print '<td>';
                        print 'Raum / Standort';
                     print '</td>';
                     print '<td>';
                        print 'Bezeichnung';
                     print '</td>';
                     print '<td>';
                        print 'Hersteller';
                     print '</td>';
                     print '<td>';
                        print 'Typ';
                     print '</td>';
                     print '<td>';
                        print 'SN Nummer';
                     print '</td>';
                     print '<td>';
                        print 'Einfache';
                     print '</td>';
                     print '<td>';
                        print 'Schwer';
                     print '</td>';
                     print '<td>';
                        print 'Hubsteiger';
                     print '</td>';
                     print '<td>';
                        print 'Reinigung';
                     print '</td>';
                     print '<td>';
                        print 'Prüfnummer';
                     print '</td>';
                     print '<td>';
                        print 'Prüfzyklus';
                     print '</td>';
                     print '<td>';
                        print 'Schutzklasse';
                     print '</td>';
                     print '<td>';
                        print 'IO';
                     print '</td>';
                     print '<td>';
                        print 'mit Mängeln';
                     print '</td>';
                     print '<td>';
                        print 'Mängel im Detail';
                     print '</td>';
                     print '<td>';
                        print 'defekt';
                     print '</td>';
                     print '<td>';
                        print 'Monitor / Player';
                     print '</td>';
                     print '<td>';
                        print 'Bild';
                     print '</td>';
                  print '</tr>';
                  if($rowsCount > 0){
                     for($i = 1; $i <= $rowsCount; $i++){
                        print '<tr class="oddeven">';
                           print '<td>';
                              print $i;
                           print '</td>';
                           print '<td>';
                              print '<input type="text" name="textInput_'.$i.'_1" style="width:100%">';
                           print '</td>';
                           print '<td>';
                              print '<input type="text" name="textInput_'.$i.'_2" style="width:100%">';
                           print '</td>';
                           print '<td>';
                              print '<input type="text" name="textInput_'.$i.'_3" style="width:100%">';
                           print '</td>';
                           print '<td>';
                              print '<input type="text" name="textInput_'.$i.'_4" style="width:100%">';
                           print '</td>';
                           print '<td>';
                              print '<input type="text" name="textInput_'.$i.'_5" style="width:100%">';
                           print '</td>';
                           print '<td>';
                              print '<input type="checkbox" name="checkbox_'.$i.'_6" style="width:100%">';
                           print '</td>';
                           print '<td>';
                              print '<input type="checkbox" name="checkbox_'.$i.'_7" style="width:100%">';
                           print '</td>';
                           print '<td>';
                              print '<input type="checkbox" name="checkbox_'.$i.'_8" style="width:100%">';
                           print '</td>';
                           print '<td>';
                              print '<input type="checkbox" name="checkbox_'.$i.'_9" style="width:100%">';
                           print '</td>';
                           print '<td>';
                              print '<input type="text" name="textInput_'.$i.'_10" style="width:100%">';
                           print '</td>';
                           print '<td>';
                              print '<input type="text" name="textInput_'.$i.'_11" style="width:100%">';
                           print '</td>';
                           print '<td>';
                              print '<input type="text" name="textInput_'.$i.'_12" style="width:100%">';
                           print '</td>';
                           print '<td>';
                              print '<input type="checkbox" name="checkbox_'.$i.'_13" style="width:100%">';
                           print '</td>';
                           print '<td>';
                              print '<input type="checkbox" name="checkbox_'.$i.'_14" style="width:100%">';
                           print '</td>';
                           print '<td>';
                              print '<input type="text" name="textInput_'.$i.'_15" style="width:100%">';
                           print '</td>';
                           print '<td>';
                              print '<input type="checkbox" name="checkbox_'.$i.'_16" style="width:100%">';
                           print '</td>';
                           print '<td>';
                              print '<input type="text" name="textInput_'.$i.'_17" style="width:100%">';
                           print '</td>';
                           print '<td>';
                              // print '<button id="'.$i.'" class="btn btn-primary" onclick="openImagesUploader(this.id)">+</button>';
                           print '</td>';
                        print '</tr>';
                     }
                  }
               print '</table>';
            print '</div>';
            print '<input id="rows-counter" name="rows-counter" type="hidden" value="'.$rowsCount.'" hidden>';
         print '</div>';
         print '</div>';
         print '<div id="add-row-popup" style="display: none;">
                  <div class="popup-header">
                     <div class="row">
                        <div class="col-8">
                           <h4>Add New Row</h4>
                        </div>
                        <div class="col-4" style="text-align: right; color: red">
                           <h4 id="close-popup">X</h4>
                        </div>
                     </div>
                  </div>
                  <hr>
                  <form id="add-row-form">
                     <div class="popup-body">
                        <div class="row">
                           <div class="col-6 col-md-3 d-flex align-items-center">
                              <label for="cell-1">Raum / Standort:</label>
                           </div>
                           <div class="col-6 col-md-9">
                              <input type="text" id="cell-1" name="1" style="width:100%">
                           </div>
                        </div>
                        <div class="row mt-2">
                           <div class="col-6 col-md-3 d-flex align-items-center">
                              <label for="cell-1">Bezeichnung:</label>
                           </div>
                           <div class="col-6 col-md-9">
                              <input type="text" id="cell-2" name="2" style="width:100%">
                           </div>
                        </div>
                        <div class="row mt-2">
                           <div class="col-6 col-md-3 d-flex align-items-center">
                              <label for="cell-1">Hersteller:</label>
                           </div>
                           <div class="col-6 col-md-9">
                              <input type="text" id="cell-3" name="3" style="width:100%">
                           </div>
                        </div>
                        <div class="row mt-2">
                           <div class="col-6 col-md-3 d-flex align-items-center">
                              <label for="cell-1">Typ:</label>
                           </div>
                           <div class="col-6 col-md-9">
                              <input type="text" id="cell-4" name="4" style="width:100%">
                           </div>
                        </div>
                        <div class="row mt-2">
                           <div class="col-6 col-md-3 d-flex align-items-center">
                              <label for="cell-1">SN Nummer:</label>
                           </div>
                           <div class="col-6 col-md-9">
                              <input type="text" id="cell-5" name="5" style="width:100%">
                           </div>
                        </div>
                        <div class="row mt-2">
                           <div class="col-6 col-md-3 d-flex align-items-center">
                              <label for="cell-1">Einfache:</label>
                           </div>
                           <div class="col-6 col-md-9">
                              <input type="checkbox" id="cell-6" name="6" style="width:100%">
                           </div>
                        </div>
                        <div class="row mt-2">
                           <div class="col-6 col-md-3 d-flex align-items-center">
                              <label for="cell-1">Schwer:</label>
                           </div>
                           <div class="col-6 col-md-9">
                              <input type="checkbox" id="cell-7" name="7" style="width:100%">
                           </div>
                        </div>
                        <div class="row mt-2">
                           <div class="col-6 col-md-3 d-flex align-items-center">
                              <label for="cell-1">Hubsteiger:</label>
                           </div>
                           <div class="col-6 col-md-9">
                              <input type="checkbox" id="cell-8" name="8" style="width:100%">
                           </div>
                        </div>
                        <div class="row mt-2">
                           <div class="col-6 col-md-3 d-flex align-items-center">
                              <label for="cell-1">Reinigung:</label>
                           </div>
                           <div class="col-6 col-md-9">
                              <input type="checkbox" id="cell-9" name="9" style="width:100%">
                           </div>
                        </div>
                        <div class="row mt-2">
                           <div class="col-6 col-md-3 d-flex align-items-center">
                              <label for="cell-1">Prüfzyklus:</label>
                           </div>
                           <div class="col-6 col-md-9">
                              <input type="text" id="cell-11" name="11" style="width:100%">
                           </div>
                        </div>
                        <div class="row mt-2">
                           <div class="col-6 col-md-3 d-flex align-items-center">
                              <label for="cell-1">Schutzklasse:</label>
                           </div>
                           <div class="col-6 col-md-9">
                              <input type="text" id="cell-12" name="12" style="width:100%">
                           </div>
                        </div>
                        <div class="row mt-2">
                           <div class="col-6 col-md-3 d-flex align-items-center">
                              <label for="cell-1">IO:</label>
                           </div>
                           <div class="col-6 col-md-9">
                              <input type="checkbox" id="cell-13" name="13" style="width:100%">
                           </div>
                        </div>
                        <div class="row mt-2">
                           <div class="col-6 col-md-3 d-flex align-items-center">
                              <label for="cell-1">mit Mängeln:</label>
                           </div>
                           <div class="col-6 col-md-9">
                              <input type="checkbox" id="cell-14" name="14" style="width:100%">
                           </div>
                        </div>
                        <div class="row mt-2">
                           <div class="col-6 col-md-3 d-flex align-items-center">
                              <label for="cell-1">Mängel im Detail:</label>
                           </div>
                           <div class="col-6 col-md-9">
                              <input type="text" id="cell-15" name="15" style="width:100%">
                           </div>
                        </div>
                        <div class="row mt-2">
                           <div class="col-6 col-md-3 d-flex align-items-center">
                              <label for="cell-1">Defekt:</label>
                           </div>
                           <div class="col-6 col-md-9">
                              <input type="checkbox" id="cell-16" name="16" style="width:100%">
                           </div>
                        </div>
                        <div class="row mt-2">
                           <div class="col-6 col-md-3 d-flex align-items-center">
                              <label for="cell-1">Monitor / Player:</label>
                           </div>
                           <div class="col-6 col-md-9">
                              <input type="text" id="cell-17" name="17" style="width:100%">
                           </div>
                        </div>
                        <input type="hidden" id="cell-18" name="18">
                     </div>
                     <hr>
                     <div class="popup-footer">
                        <div class="row">
                           <div class="col-6" style="text-align: center;">
                              <button class="btn btn-secondary" type="submit">Save</button>
                           </div>
                           <div class="col-6" style="text-align: center;">
                              <button class="btn btn-primary" id="next">Add & Next</button>
                           </div>
                        </div>
                     </div>
                  </form>
               </div>';

         $rowsCount += 1;         
         print '<script>';
            // Add new row to the Messung table

            print '
                  let rowCounter = \'' . $rowsCount . '\';
                  let standard = "0000";
                  let prufnummer = "0000";

                  const addRowButton = document.getElementById("add-row");
                  const closeButton = document.getElementById("close-popup");
                  const rowsCounter = document.getElementById("rows-counter");
                  const table = document.querySelector("#pieces-table table tbody");

                  addRowButton.addEventListener("click", () => {
                     document.getElementById("add-row-popup").style.display = "block";
                  });
                  closeButton.addEventListener("click", () => {
                     document.getElementById("add-row-popup").style.display = "none";
                  });

                  const addRowForm = document.getElementById("add-row-form");
                  const nextButton = document.getElementById("next");
                  nextButton.addEventListener("click", handleNextClick);

                  function handleNextClick(event) {
                     event.preventDefault();

                     const cellData = {};
                     for (const element of addRowForm.elements) {
                        if (element.tagName === "INPUT") {
                           if(element.type === "checkbox"){
                              cellData[element.name] = element.checked;
                           } else {
                              cellData[element.name] = element.value;
                           }
                        }
                     }
                     addRowToTable(cellData);

                     // Clear existing form values (unchanged)
                     const existingInputs = addRowForm.querySelectorAll("input");
                     for (const input of existingInputs) {
                        if(input.type === "checkbox"){
                           input.checked = false;
                        } else {
                           input.value = "";
                        }
                     }
                  }

                  addRowForm.addEventListener("submit", (event) => {
                     event.preventDefault();

                     const cellData = {};
                     for (const element of addRowForm.elements) {
                        if (element.tagName === "INPUT") {
                           if(element.type === "checkbox"){
                              cellData[element.name] = element.checked;
                           } else {
                              cellData[element.name] = element.value;
                           }
                        }
                     }
                     addRowToTable(cellData);
                     const existingInputs = addRowForm.querySelectorAll("input");
                     for (const input of existingInputs) {
                        if(input.type === "checkbox"){
                           input.checked = false;
                        } else {
                           input.value = "";
                        }
                     }
                     document.getElementById("add-row-popup").style.display = "none";
                  });

                  function addRowToTable(cellData) {
                     const newRow = document.createElement("tr");
                     newRow.classList.add("oddeven");
                     const firstCell = document.createElement("td");
                     firstCell.textContent = rowCounter;
                     newRow.appendChild(firstCell);

                     for (let i = 1; i < 19; i++) {
                        const cell = document.createElement("td");

                        if ((i >= 6 && i <= 9) || i == 13 || i == 14 || i == 16) {
                           // Create checkboxes (unchanged)
                           const checkbox = document.createElement("input");
                           checkbox.type = "checkbox";
                           checkbox.name = "checkbox" + "_" + rowCounter + "_" + i;
                           checkbox.checked = cellData[i];
                           checkbox.style.width = "100%";
                           cell.appendChild(checkbox);
                        } else if (i == 10) {
                           const textInput = document.createElement("input");
                           textInput.type = "text";
                           textInput.style.width = "100%";
                           textInput.name = "textInput" + "_" + rowCounter + "_" + i;
                           // Calculate paddedSum with leading zeros using a function
                           textInput.value = "A-" + generatePaddedSum(Number(rowCounter) + Number(prufnummer));
                           cell.appendChild(textInput);
                        } else if (i != 18) {
                           // Create text inputs (unchanged)
                           const textInput = document.createElement("input");
                           textInput.type = "text";
                           textInput.style.width = "100%";
                           textInput.name = "textInput" + "_" + rowCounter + "_" + i;
                           textInput.value = cellData[i];
                           cell.appendChild(textInput);
                        }
                        newRow.appendChild(cell);
                     }
                     table.appendChild(newRow);
                     rowsCounter.value = rowCounter;
                     rowCounter++;
                  }

                  function generatePaddedSum(number) {
                     return number.toString().padStart(standard.length, "0");
                  }
            ';
            // End add new row to the Messung table

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
                     });';
            // end calculate distance/times
         print '</script>';
      } else {
         print '<div class="row">';
            print '<div class="col-6">';
               print '<label>Ticket Number*</label>';
               print '<br>';
               print '<input class="textfield" type="text" name="ticket-number" value="'.$object->ref.'" required disabled>';
            print '</div>';
            print '<div class="col-6">';
               print '<label>Store*</label>';
               print '<br>';
               print '<input class="textfield" type="text" name="store-number" value="'.$store->b_number.'" required disabled>';
            print '</div>';
         print '</div>';
         print '<br>';
         print '<div>';
            print '<label>Street*</label>';
            print '<br>';
            print '<input class="textfield" type="text" name="street" value="'.$store->street.' '.$store->house_number.', '. $store->zip_code.' '. $store->city.'" required disabled>';
         print '</div>';
         print '<br>';
         print '<div>';
            print '<label>Phonenumber*</label>';
            print '<br>';
            print '<input class="textfield" type="text" name="phonenumber" value="'.$store->phone.'" required disabled>';
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
            if($projectId != 106 && $projectRef != "69-2407-0101"){
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
            }
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
                     print '<td colspan="1"><input type="text" name="lancom-router" style="width:100%" required></td>';
                     print '<td colspan="1" class="center"><input type="checkbox" name="router-value"></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">Palo Alto Firewall</td>';
                     print '<td colspan="1"><input type="text" name="firewall-qr" style="width:100%" required></td>';
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
         print '<table class="noborder centpercent">';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">Der Umbau in VKST-'.explode("-", $store->b_number)[2].' konnte nicht gestartet werden. Die Gründe sind unter "Sonstiges" zu finden.</td>';
                     print '<td colspan="1"><input type="radio" name="table1" id="table1_3" value="3"></td>';
                  print '</tr>';
               print '</table>';
            print '<div class="col-12 div-table-responsive-no-min">';
               print '<table id="questions-table" class="noborder centpercent">';
                  print '<tr class="liste_titre">';
                     print '<th colspan="3"></th>';
                     print '<th colspan="3">VKST 4.0</th>';
                     print '<th colspan="2">NUR NACH RÜCKBAU VKST 3.0</th>';
                  print '</tr>';
                  print '<tr class="liste_titre">';
                     print '<td colspan="1">Test NR</td>';
                     print '<td colspan="1">Testfalle</td>';
                     print '<td colspan="1">Prio</td>';
                     print '<td colspan="1" class="center"><i class="fa fa-check check-all" style="color:green" data-column="vk"></i></td>';
                     print '<td colspan="1" class="center"><i class="ico-times" role="img" aria-label="Cancel"></i></td>';
                     print '<td colspan="1" class="center">NV</td>';
                     print '<td colspan="1" class="center"><i class="fa fa-check center" style="color:green" data-column="nu"></i></td>';
                     print '<td colspan="1" class="center"><i class="ico-times center" role="img" aria-label="Cancel"></i></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">A1</td>';
                     print '<td colspan="1">Testartikel scannen (R)</td>';
                     print '<td colspan="1" class="prio">1</td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question1vk" id="question1vk_1" value="1" class="vk-radio"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question1vk" id="question1vk_2" value="2"></td>';
                     print '<td colspan="1" class="center"><input type="checkbox" name="table1-check-1"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question1nu" id="question1nu_1" value="1"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question1nu" id="question1nu_2" value="2"></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">A2</td>';
                     print '<td colspan="1">Bon Druck und TSE (R)</td>';
                     print '<td colspan="1" class="prio">1</td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question2vk" id="question2vk_1" value="1" class="vk-radio"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question2vk" id="question2vk_2" value="2"></td>';
                     print '<td colspan="1" class="center"><input type="checkbox" name="table1-check-2"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question2nu" id="question2nu_1" value="1"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question2nu" id="question2nu_2" value="2"></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">A3</td>';
                     print '<td colspan="1">EC-Zahlung (R)</td>';
                     print '<td colspan="1" class="prio">1</td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question3vk" id="question3vk_1" value="1" class="vk-radio"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question3vk" id="question3vk_2" value="2"></td>';
                     print '<td colspan="1" class="center"><input type="checkbox" name="table1-check-3"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question3nu" id="question3nu_1" value="1"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question3nu" id="question3nu_2" value="2"></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">A4</td>';
                     print '<td colspan="1">EC-Diagnose (R)</td>';
                     print '<td colspan="1" class="prio">1</td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question4vk" id="question4vk_1" value="1" class="vk-radio"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question4vk" id="question4vk_2" value="2"></td>';
                     print '<td colspan="1" class="center"><input type="checkbox" name="table1-check-4"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question4nu" id="question4nu_1" value="1"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question4nu" id="question4nu_2" value="2"></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">A5</td>';
                     print '<td colspan="1">Gutschein abfragen (R)</td>';
                     print '<td colspan="1" class="prio">1</td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question5vk" id="question5vk_1" value="1" class="vk-radio"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question5vk" id="question5vk_2" value="2"></td>';
                     print '<td colspan="1" class="center"><input type="checkbox" name="table1-check-5"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question5nu" id="question5nu_1" value="1"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question5nu" id="question5nu_2" value="2"></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">A6</td>';
                     print '<td colspan="1">Bediener Abmelden (R)</td>';
                     print '<td colspan="1" class="prio">1</td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question6vk" id="question6vk_1" value="1" class="vk-radio"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question6vk" id="question6vk_2" value="2"></td>';
                     print '<td colspan="1" class="center"><input type="checkbox" name="table1-check-6"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question6nu" id="question6nu_1" value="1"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question6nu" id="question6nu_2" value="2"></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">B1</td>';
                     print '<td colspan="1">Mit OBF einen Artikel scannen (R)</td>';
                     print '<td colspan="1" class="prio">1</td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question7vk" id="question7vk_1" value="1" class="vk-radio"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question7vk" id="question7vk_2" value="2"></td>';
                     print '<td colspan="1" class="center"><input type="checkbox" name="table1-check-7"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question7nu" id="question7nu_1" value="1"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question7nu" id="question7nu_2" value="2"></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">B2</td>';
                     print '<td colspan="1">Mit OBF Etiketten drucken (R)</td>';
                     print '<td colspan="1" class="prio">1</td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question8vk" id="question8vk_1" value="1" class="vk-radio"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question8vk" id="question8vk_2" value="2"></td>';
                     print '<td colspan="1" class="center"><input type="checkbox" name="table1-check-8"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question8nu" id="question8nu_1" value="1"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question8nu" id="question8nu_2" value="2"></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">C1</td>';
                     print '<td colspan="1">MO STM (R)</td>';
                     print '<td colspan="1" class="prio">1</td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question9vk" id="question9vk_1" value="1" class="vk-radio"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question9vk" id="question9vk_2" value="2"></td>';
                     print '<td colspan="1" class="center"><input type="checkbox" name="table1-check-9"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question9nu" id="question9nu_1" value="1"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question9nu" id="question9nu_2" value="2"></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">C2</td>';
                     print '<td colspan="1">MO HR Portal (R)</td>';
                     print '<td colspan="1" class="prio">2</td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question10vk" id="question10vk_1" value="1" class="vk-radio"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question10vk" id="question10vk_2" value="2"></td>';
                     print '<td colspan="1" class="center"><input type="checkbox" name="table1-check-10"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question10nu" id="question10nu_1" value="1"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question10nu" id="question10nu_2" value="2"></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">C3</td>';
                     print '<td colspan="1">MO PEP (R)</td>';
                     print '<td colspan="1" class="prio">2</td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question11vk" id="question11vk_1" value="1" class="vk-radio"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question11vk" id="question11vk_2" value="2"></td>';
                     print '<td colspan="1" class="center"><input type="checkbox" name="table1-check-11"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question11nu" id="question11nu_1" value="1"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question11nu" id="question11nu_2" value="2"></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">C4</td>';
                     print '<td colspan="1">MO ProDigi (R)</td>';
                     print '<td colspan="1" class="prio">2</td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question12vk" id="question12vk_1" value="1" class="vk-radio"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question12vk" id="question12vk_2" value="2"></td>';
                     print '<td colspan="1" class="center"><input type="checkbox" name="table1-check-12"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question12nu" id="question12nu_1" value="1"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question12nu" id="question12nu_2" value="2"></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">C5</td>';
                     print '<td colspan="1">MO Intranet (R)</td>';
                     print '<td colspan="1" class="prio">2</td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question13vk" id="question13vk_1" value="1" class="vk-radio"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question13vk" id="question13vk_2" value="2"></td>';
                     print '<td colspan="1" class="center"><input type="checkbox" name="table1-check-13"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question13nu" id="question13nu_1" value="1"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question13nu" id="question13nu_2" value="2"></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">C6</td>';
                     print '<td colspan="1">MO Korona Backoffice (R)</td>';
                     print '<td colspan="1" class="prio">2</td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question14vk" id="question14vk_1" value="1" class="vk-radio"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question14vk" id="question14vk_2" value="2"></td>';
                     print '<td colspan="1" class="center"><input type="checkbox" name="table1-check-14"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question14nu" id="question14nu_1" value="1"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question14nu" id="question14nu_2" value="2"></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">C7</td>';
                     print '<td colspan="1">MO Webportal Instanthaltung (R)</td>';
                     print '<td colspan="1" class="prio">2</td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question15vk" id="question15vk_1" value="1" class="vk-radio"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question15vk" id="question15vk_2" value="2"></td>';
                     print '<td colspan="1" class="center"><input type="checkbox" name="table1-check-15"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question15nu" id="question15nu_1" value="1"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question15nu" id="question15nu_2" value="2"></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">D1</td>';
                     print '<td colspan="1">Fototerminals (R/T)</td>';
                     print '<td colspan="1" class="prio">2</td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question16vk" id="question16vk_1" value="1" class="vk-radio"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question16vk" id="question16vk_2" value="2"></td>';
                     print '<td colspan="1" class="center"><input type="checkbox" name="table1-check-16"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question16nu" id="question16nu_1" value="1"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question16nu" id="question16nu_2" value="2"></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">D2</td>';
                     print '<td colspan="1">EMA (R/T)</td>';
                     print '<td colspan="1" class="prio">2</td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question17vk" id="question17vk_1" value="1" class="vk-radio"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question17vk" id="question17vk_2" value="2"></td>';
                     print '<td colspan="1" class="center"><input type="checkbox" name="table1-check-17"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question17nu" id="question17nu_1" value="1"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question17nu" id="question17nu_2" value="2"></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">D3</td>';
                     print '<td colspan="1">Telefonie (R/T)</td>';
                     print '<td colspan="1" class="prio">2</td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question18vk" id="question18vk_1" value="1" class="vk-radio"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question18vk" id="question18vk_2" value="2"></td>';
                     print '<td colspan="1" class="center"><input type="checkbox" name="table1-check-18"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question18nu" id="question18nu_1" value="1"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question18nu" id="question18nu_2" value="2"></td>';
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
                     print '<td colspan="1" class="center"><input type="radio" name="question19vk" id="question19vk_1" value="1" class="vk-radio"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question19vk" id="question19vk_2" value="2"></td>';
                     print '<td colspan="1" class="center"><input type="checkbox" name="table1-check-19"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question19nu" id="question19nu_1" value="1"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question19nu" id="question19nu_2" value="2"></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">E2</td>';
                     print '<td colspan="1">Pfandautomaten (R/T)</td>';
                     print '<td colspan="1" class="prio">2</td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question20vk" id="question20vk_1" value="1" class="vk-radio"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question20vk" id="question20vk_2" value="2"></td>';
                     print '<td colspan="1" class="center"><input type="checkbox" name="table1-check-20"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question20nu" id="question20nu_1" value="1"></td>';
                     print '<td colspan="1" class="center"><input type="radio" name="question20nu" id="question20nu_2" value="2"></td>';
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
               print '<table class="noborder centpercent" id="options-table">';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">Der Umbau in VKST-'.explode("-", $store->b_number)[2].' wurde erfolgreich abgeschlossen Wenn alles erfolgreich.</td>';
                     print '<td colspan="1"><input type="radio" name="table1" id="table1_1" value="1"></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">Der Umbau in VKST-'.explode("-", $store->b_number)[2].' wurde erfolgreich abgeschlossen. Mindestens 1 P2 Test konnte nicht erfolgreich durchgeführt werden (siehe unten).</td>';
                     print '<td colspan="1"><input type="radio" name="table1" id="table1_2" value="2"></td>';
                  print '</tr>';
                  // print '<tr class="oddeven">';
                  //    print '<td colspan="1">Der Umbau in VKST-'.explode("-", $store->b_number)[2].' konnte nicht gestartet werden. Die Gründe sind unter "Sonstiges" zu finden.</td>';
                  //    print '<td colspan="1"><input type="radio" name="table1" id="table1_3" value="3"></td>';
                  // print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">Der Umbau in VKST-'.explode("-", $store->b_number)[2].' konnte nicht abgeschlossen werden. Der Rollback auf VKST3.0 war erfolgreich.</td>';
                     print '<td colspan="1"><input type="radio" name="table1" id="table1_4" value="4"></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">Der Umbau in VKST-'.explode("-", $store->b_number)[2].' konnte nicht abgeschlossen werden. Der Rollback auf VKST3.0 war erfolgreich. Mindestens 1 P2 Test konnte nicht erfolgreich durchgeführt werden (siehe unten).</td>';
                     print '<td colspan="1"><input type="radio" name="table1" id="table1_5" value="5"></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">Der Umbau in VKST-'.explode("-", $store->b_number)[2].' konnte nicht abgeschlossen werden. Auch der Rollback war erfolglos. Der Technikerleitstand wurde bereits informiert.</td>';
                     print '<td colspan="1"><input type="radio" name="table1" id="table1_6" value="6"></td>';
                  print '</tr>';
               print '</table>';
               Print '<h6 style="color: red; display: none" id="error-text-p1">P1 Fehler (Umbau): </h6>';
               print '<input type="hidden" name="p1tests" id="p1tests">';
               print '<h6 style="color: red; display: none" id="error-text-p2">P2 Fehler (Umbau): <h6>';
               print '<input type="hidden" name="p2tests" id="p2tests">';
               Print '<h6 style="color: red; display: none" id="error-text-p1-rollback">P1 Fehler (Rollback): </h6>';
               print '<input type="hidden" name="p1testsRollback" id="p1testsRollback">';
               print '<h6 style="color: red; display: none" id="error-text-p2-rollback">P2 Fehler (Rollback): <h6>';
               print '<input type="hidden" name="p2testsRollback" id="p2testsRollback">';
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
                     print '<td colspan="1"><input type="radio" name="table2" id="table2_1" value="1" onchange="toggleNoteInput()"></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">Fehlendes Material</td>';
                     print '<td colspan="1"><input type="radio" name="table2" id="table2_2" value="2" onchange="toggleNoteInput()"></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">Fehler der Automatisierung / App</td>';
                     print '<td colspan="1"><input type="radio" name="table2" id="table2_3" value="3" onchange="toggleNoteInput()"></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">Defekte Hardware</td>';
                     print '<td colspan="1"><input type="radio" name="table2" id="table2_4" value="4" onchange="toggleNoteInput()"></td>';
                  print '</tr>';
                  print '<tr class="oddeven">';
                     print '<td colspan="1">Sonstiges</td>';
                     print '<td colspan="1"><input type="radio" name="table2" id="table2_5" value="5" onchange="toggleNoteInput()"></td>';
                  print '</tr>';
               print '</table>';
               Print '<input type="text" id="note-other" style="width:100%; margin-bottom: 15px; display: none" name="note-other">';
               Print '<h6 style="color: red; display: none" id="error-text-p1">Error results: <h6>';
               print '<button id="hide" class="btn btn-primary" onclick="toggleVisibility(this.id)">Hide</button>';
            print '</div>';
         print '</div>';
         print '<br>';
         print '<div class="row">
                     <div class="col">
                        <select style="width: 100%" name="image-type">
                           <option selected disabled>Bildtyp auswählen</option>
                           <option>Serverschrank vorher</option>
                           <option>Serverschrank nachher</option>
                           <option>Arbeitssplatz nachher</option>
                           <option>Seriennummer router</option>
                           <option>Seriennummer firewall</option>
                           <option>Firewall (Beschriftung Patchkabel)</option>
                           <option>Kabeletikett</option>
                           <option>Testprotokoll</option>
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
         if($result[3]){
            // print '<div class="container">';
               print '<div class="row mb-2">';
               $imagesGroup = json_decode(base64_decode($result[3]));
               foreach($imagesGroup as $group){
                  print '<div class="col-12 mt-2" style="background: #aaa;padding: 5px 0 5px 10px;">';
                     print $group->type;
                  print '</div>';
                  foreach($group->images as $image){
                     print '<div class="col-3 col-md-3 mt-2" style="text-align:center">';
                        print '<img class="group-image" src="../formsImages/'.$image.'" style="width:100%; height:13rem" onclick="showImageFull(this.src)">';
                        print '<form method="POST" enctype="multipart/form-data">';
                           print '<input type="hidden" name="image" value="'.$image.'">';
                           print '<input type="hidden" name="image-group" value="'.$group->type.'">';
                           print '<input type="submit" name="delete-image" class="btn btn-danger" id="'.$image.','.$group->type.'" style="font-size: 10px; padding: 5px;" value="'.$langs->trans("delete").'">';
                        print '</form>';
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
               print '<input type="text" name="employee-name" placeholder="Name of Technician" value="'.$techUser->firstname.' '.$techUser->lastname.'">';
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
         print '
               <div id="popup" class="closed">
                  <div class="row">
                     <div class="col-12">
                        <img id="popupImage" src="" style="width:100%; height: 40rem">
                     </div>
                  </div>
                  <div class="row mt-2">
                     <div class="col-12" style="text-align: center">
                        <button class="btn btn-danger" id="closePopupBtn">Close</button>
                     </div>
                  </div>
               </div>';

         print '<script>';
            // end check P1, P2 table rows
            print '
                  let rows2 = document.querySelectorAll(\'#questions-table .oddeven\');
                  for(let i = 0; i < rows2.length - 4; i++){
                     const row = rows2[i];
                     const cells = row.children;
                     const prio = cells[2].textContent.trim();
                     if(prio == 2){
                        const testFailed = cells[4].querySelector(\'input[type="radio"]\').checked;
                        if(testFailed){
                           const secondColumnText = row.querySelector("td:nth-child(1)").textContent.trim();
                           console.log("Wo es hakt" + document.getElementById("p2tests"));
                           document.getElementById("p2tests").value += secondColumnText+ ", ";
                           console.log("Found" + secondColumnText);
                        }
                     }
                  }
                  document.getElementById("p2tests").value = document.getElementById("p2tests").value.slice(0, -2);
            
            ';
            // end check P1, P2 table rows
   
            // Show full image
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
            // End show full image
            print '
            function checkTests() {
            var opt1 = document.getElementById("table1_1");
                     var opt2 = document.getElementById("table1_2");
                     var opt3 = document.getElementById("table1_3");
                     var opt4 = document.getElementById("table1_4");
                     var opt5 = document.getElementById("table1_5");
                     var opt6 = document.getElementById("table1_6");
                        const rows = document.querySelectorAll(\'#questions-table .oddeven\');
                        let prio1Failed = false;
                        let prio1RollbackFailed = false;
                        let prio2Failed = false;
                        let prio2RollbackFailed = false;
                        //console.log(rows.length);
                        for (let i = 0; i < rows.length; i++) {
                        //console.log("Iteration: " + i);
                                 const row = rows[i];
                                 const cells = row.children;
                                 
                                 // console.log(cells.length);
                                 if(cells.length == 8){
                                    const prio = cells[2].textContent.trim();
                                    const notAvailable = cells[5].querySelector(\'input[type="checkbox"]\').checked;
                                    if(notAvailable){
                                       // console.log("Not available");
                                       cells[3].querySelector(\'input[type="radio"]\').disabled = true;
                                       cells[3].querySelector(\'input[type="radio"]\').checked = false;
                                       cells[4].querySelector(\'input[type="radio"]\').disabled = true;
                                       cells[4].querySelector(\'input[type="radio"]\').checked = false;
                                       cells[6].querySelector(\'input[type="radio"]\').disabled = true;
                                       cells[6].querySelector(\'input[type="radio"]\').checked = false;
                                       cells[7].querySelector(\'input[type="radio"]\').disabled = true;
                                       cells[7].querySelector(\'input[type="radio"]\').checked = false;
                                       continue;
                                    } else if(!notAvailable){
                                       cells[3].querySelector(\'input[type="radio"]\').disabled = false;
                                       cells[4].querySelector(\'input[type="radio"]\').disabled = false;
                                       cells[6].querySelector(\'input[type="radio"]\').disabled = false;
                                       cells[7].querySelector(\'input[type="radio"]\').disabled = false;
                                    }
                                    const testPassed = cells[3].querySelector(\'input[type="radio"]\').checked;
                                    const testFailed = cells[4].querySelector(\'input[type="radio"]\').checked;
                                    const rollbackPassed = cells[6].querySelector(\'input[type="radio"]\').checked;
                                    const rollbackFailed = cells[7].querySelector(\'input[type="radio"]\').checked;
         
                                    if (prio === "1") {
                                       console.log("Prio : 1");
                                       if (testFailed) {
                                       // If the current selected prio1 test failed or a previous prio1 failed, set prio1Failed to true
                                          console.log("Prio 1 test failed");
                                          prio1Failed = true;
                                          if (rollbackFailed) {
                                          // If also the rollback failed then set prio1RollbackFailed to true
                                                console.log("Prio 1 rollback failed");
                                                prio1RollbackFailed = true;
                                          }else{
                                                //prio1RollbackFailed = false;
                                          }
                                       }else if(testPassed){
                                       // If the current selected prio1 test passed but there have been failed prio1 tests before, set prio1Failed to true
                                          if(rollbackFailed){
                                          // If this one passed the umbau but failed rollback then set prio1RollbackFailed to true
                                             prio1RollbackFailed = true;
                                          }
                                       }
                                    } else if (prio === "2") {
                                       console.log("Prio : 2");

                                       if(testFailed){
                                          prio2Failed = true;
                                          console.log("Prio 2 test failed");
                                       }
                                       if(rollbackFailed){
                                          prio2RollbackFailed = true;
                                          console.log("Prio 2 rollback failed");
                                          console.log(prio2RollbackFailed);
                                       }
                                    }
                                 }
                                 // console.log(row);
                                 // console.log(prio);
                                 // console.log(cells);
                                 // console.log(testPassed);
                                 // console.log(testFailed);
                                 // console.log(rollbackPassed);
                                 // console.log(rollbackFailed);
                           
                           }
                           console.log("Prio 1 failed? " + prio1Failed);
                           console.log("Prio 1 rollback failed? " + prio1RollbackFailed);
                           console.log("Prio 2 failed? " + prio2Failed);
                           console.log("Prio 2 rollback failed? " + prio2RollbackFailed);
                           if (prio1Failed) {
                           // If one prio1 test fails then opt1 is unchecked
                                 opt1.checked = false;
                                 if (prio1RollbackFailed) {
                                 // If one prio1 rollback test fails then opt6 is checked
                                    opt6.checked = true;
                                 } else {
                                 // If there are failed prio1 tests but every prio1 passed rollback then select 4
                                    if(prio2RollbackFailed == false){
                                       opt4.checked = true;
                                    }else{
                                       opt5.checked = true;
                                    } 
                                 }
                           } else if (prio2Failed && prio1Failed == false) {
                            // If there are failed prio2 and no failed prio1 tests then select opt2
                                 opt1.checked = false;
                                 opt2.checked = true;
                           }else if(prio1Failed == false && prio2Failed == false){
                           // If there are no failed prio1 and no failed prio2 tests then select opt1
                                 opt1.checked = true;  
                           }
                                 //setRequireRadio();
                                 testTracker();
                                 //checkFormValidity();
                        //          console.log(prio1Failed + " " + prio1RollbackFailed + " " + prio2Failed);
                        //  console.log(`opt1: ${opt1}, opt2: ${opt2}, opt3: ${opt3}, opt4: ${opt4}`);
                     }'
;

            print '
               function setRequireRadio() {
                  var opt1 = document.getElementById("table1_1");
                  var opt2 = document.getElementById("table1_2");
                  var opt3 = document.getElementById("table1_3");
                  var opt4 = document.getElementById("table1_4");
                  var opt5 = document.getElementById("table1_5");
                  var opt6 = document.getElementById("table1_6");
                  const rows = document.querySelectorAll(\'#questions-table .oddeven\');
                  if(opt4.checked || opt6.checked){
                     for (let i = 0; i < rows.length; i++) {
                        const row = rows[i];
                        const cells = row.children;
                        if(cells.length == 8){
                           const prio = cells[2].textContent.trim();
                           cells[6].querySelector(\'input[type="radio"]\').required = true;
                           cells[7].querySelector(\'input[type="radio"]\').required = true;
                        }
                     }
                  }else{
                     for (let i = 0; i < rows.length; i++) {
                        const row = rows[i];
                        const cells = row.children;
                        if(cells.length == 8){
                           const prio = cells[2].textContent.trim();
                           cells[6].querySelector(\'input[type="radio"]\').required = false;
                           cells[7].querySelector(\'input[type="radio"]\').required = false;
                        }   
                     }
                  }
               }
            ';

            print '
               function toggleTableInputs(disable) {
               console.log("Function called");
                  const rows = document.querySelectorAll(\'#questions-table .oddeven\');
                  for (let i = 0 ; i<rows.length; i++){
                     const row = rows[i];
                     const cells = row.children;
                     if(cells.length == 8){
                        cells[3].querySelector(\'input[type="radio"]\').disabled = disable;
                        cells[4].querySelector(\'input[type="radio"]\').disabled = disable;
                        cells[5].querySelector(\'input[type="checkbox"]\').disabled = disable;
                        cells[6].querySelector(\'input[type="radio"]\').disabled = disable;
                        cells[7].querySelector(\'input[type="radio"]\').disabled = disable;   
                     }  
                  }
               }
            ';
   

            print '
               function testTracker() {
                  var opt1 = document.getElementById("table1_1");
                  var opt2 = document.getElementById("table1_2");
                  var opt3 = document.getElementById("table1_3");
                  var opt4 = document.getElementById("table1_4");
                  var opt5 = document.getElementById("table1_5");
                  var opt6 = document.getElementById("table1_6");
                  document.getElementById("error-text-p1").style.display = "none";
                  document.getElementById("error-text-p1").textContent = "P1 Fehler (Umbau): ";
                  document.getElementById("error-text-p2").style.display = "none";
                  document.getElementById("error-text-p2").textContent = "P2 Fehler (Umbau): ";
                  document.getElementById("error-text-p1-rollback").style.display = "none";
                  document.getElementById("error-text-p1-rollback").textContent = "P1 Fehler (Rollback): ";
                  document.getElementById("error-text-p2-rollback").style.display = "none";
                  document.getElementById("error-text-p2-rollback").textContent = "P2 Fehler (Rollback): ";
                  document.getElementById("p1tests").value = "";
                  document.getElementById("p2tests").value = "";
                  document.getElementById("p1testsRollback").value = "";
                  document.getElementById("p2testsRollback").value = "";
                  toggleVisibility("hide");
                  let rows = document.querySelectorAll("#questions-table .oddeven");

                  if (opt1.checked) {
                     document.getElementById("error-text-p1").style.display = "none";
                     document.getElementById("error-text-p2").style.display = "none";
                     document.getElementById("error-text-p1-rollback").style.display = "none";
                     document.getElementById("error-text-p2-rollback").style.display = "none";
                  } else if (opt2.checked) {
                     document.getElementById("error-text-p2").style.display = "block";
                     document.getElementById("p2tests").value = "";
                     for (let i = 0; i < rows.length; i++) {
                           const row = rows[i];
                           const cells = row.children;
                           if(cells.length == 8){
                              const prio = cells[2].textContent.trim();
                              if (prio == 2) {
                                 const testFailed = cells[4].querySelector(\'input[type="radio"]\').checked;
                                 if (testFailed) {
                                    const secondColumnText = row.querySelector("td:nth-child(1)").textContent.trim();
                                    document.getElementById("error-text-p2").textContent += secondColumnText + ", ";
                                 }
                              }
                           }
                     }
                     if (document.getElementById("error-text-p2").textContent == "P2 Fehler (Umbau): ") {
                           document.getElementById("error-text-p2").textContent += "-";
                     }else{
                      document.getElementById("error-text-p2").textContent = document.getElementById("error-text-p2").textContent.slice(0, -2);
                     }  
                           // Remove P2 Fehler (Umbau): from the error text
                     p2tests.value = document.getElementById("error-text-p2").textContent.replace("P2 Fehler (Umbau): ", "");
                  } else if (opt3.checked) {
                     toggleVisibility("show");
                     // Logic for opt3
                     toggleTableInputs(true);
                  } else if (opt4.checked) {
                     document.getElementById("error-text-p1").style.display = "block";
                     document.getElementById("error-text-p2").style.display = "block";
                     //document.getElementById("error-text-p2-rollback").style.display = "block";
                     for (let i = 0; i < rows.length; i++) {
                           const row = rows[i];
                           const cells = row.children;
                           if(cells.length == 8){
                              const prio = cells[2].textContent.trim();
                              if (prio == 1) {
                                 const testFailed = cells[4].querySelector(\'input[type="radio"]\').checked;
                                 if (testFailed) {
                                    const secondColumnText = row.querySelector("td:nth-child(1)").textContent.trim();
                                    document.getElementById("error-text-p1").textContent += secondColumnText + ", ";
                                 }
                              }
                              if (prio == 2) {
                                 const testFailed = cells[4].querySelector(\'input[type="radio"]\').checked;
                                 const testRollbackFailed = cells[7].querySelector(\'input[type="radio"]\').checked;
                                 if (testFailed) {
                                    const secondColumnText = row.querySelector("td:nth-child(1)").textContent.trim();
                                    document.getElementById("error-text-p2").textContent += secondColumnText + ", ";
                                 }
                                 if (testRollbackFailed) {
                                    const secondColumnText = row.querySelector("td:nth-child(1)").textContent.trim();
                                    //document.getElementById("error-text-p2-rollback").textContent += secondColumnText + ", ";
                                 }
                              }
                           }
                     }
                     
                     if (document.getElementById("error-text-p1").textContent == "P1 Fehler (Umbau): ") {
                           document.getElementById("error-text-p1").textContent += "-";
                     }else{document.getElementById("error-text-p1").textContent = document.getElementById("error-text-p1").textContent.slice(0, -2);}
                     if (document.getElementById("error-text-p2").textContent == "P2 Fehler (Umbau): ") {
                           document.getElementById("error-text-p2").textContent += "-";
                     }else{document.getElementById("error-text-p2").textContent = document.getElementById("error-text-p2").textContent.slice(0, -2);}
                     //if (document.getElementById("error-text-p2-rollback").textContent == "P2 Fehler (Rollback): ") {
                      //     document.getElementById("error-text-p2-rollback").textContent += "-";
                     //}else{document.getElementById("error-text-p2-rollback").textContent = document.getElementById("error-text-p2-rollback").textContent.slice(0, -2);}
                     document.getElementById("p1tests").value = document.getElementById("error-text-p1").textContent.replace("P1 Fehler (Umbau): ", "");
                     document.getElementById("p2tests").value = document.getElementById("error-text-p2").textContent.replace("P2 Fehler (Umbau): ", "");
                     document.getElementById("p2testsRollback").value = document.getElementById("error-text-p2-rollback").textContent.replace("P2 Fehler (Rollback): ", "");

                  } else if(opt5.checked){
                     document.getElementById("error-text-p1").style.display = "block";
                     document.getElementById("error-text-p2").style.display = "block";
                     //document.getElementById("error-text-p1-rollback").style.display = "block";
                     document.getElementById("error-text-p2-rollback").style.display = "block";
                     for (let i = 0; i < rows.length; i++) {
                           const row = rows[i];
                           const cells = row.children;
                           if(cells.length == 8){
                              const prio = cells[2].textContent.trim();
                              if (prio == 1) {
                                 const testFailed = cells[4].querySelector(\'input[type="radio"]\').checked;
                                 if (testFailed) {
                                    const secondColumnText = row.querySelector("td:nth-child(1)").textContent.trim();
                                    document.getElementById("error-text-p1").textContent += secondColumnText + ", ";
                                 }
                              }
                              if (prio == 2) {
                                 const testFailed = cells[4].querySelector(\'input[type="radio"]\').checked;
                                 const testRollbackFailed = cells[7].querySelector(\'input[type="radio"]\').checked;
                                 if (testFailed) {
                                    const secondColumnText = row.querySelector("td:nth-child(1)").textContent.trim();
                                    document.getElementById("error-text-p2").textContent += secondColumnText + ", ";
                                 }
                                 if (testRollbackFailed) {
                                    const secondColumnText = row.querySelector("td:nth-child(1)").textContent.trim();
                                    document.getElementById("error-text-p2-rollback").textContent += secondColumnText + ", ";
                                 }
                              }
                           }
                     }
                     if (document.getElementById("error-text-p1").textContent == "P1 Fehler (Umbau): ") {
                           document.getElementById("error-text-p1").textContent += "-";
                     }else{document.getElementById("error-text-p1").textContent = document.getElementById("error-text-p1").textContent.slice(0, -2);}
                     if (document.getElementById("error-text-p2").textContent == "P2 Fehler (Umbau): ") {
                           document.getElementById("error-text-p2").textContent += "-";
                     }else{document.getElementById("error-text-p2").textContent = document.getElementById("error-text-p2").textContent.slice(0, -2);}
                     if (document.getElementById("error-text-p2-rollback").textContent == "P2 Fehler (Rollback): ") {
                           document.getElementById("error-text-p2-rollback").textContent += "-";
                     }else{document.getElementById("error-text-p2-rollback").textContent = document.getElementById("error-text-p2-rollback").textContent.slice(0, -2);}
                     document.getElementById("p1tests").value = document.getElementById("error-text-p1").textContent.replace("P1 Fehler (Umbau): ", "");
                     document.getElementById("p2tests").value = document.getElementById("error-text-p2").textContent.replace("P2 Fehler (Umbau): ", "");
                     document.getElementById("p2testsRollback").value = document.getElementById("error-text-p2-rollback").textContent.replace("P2 Fehler (Rollback): ", "");

                  }else if (opt6.checked) {
                     toggleVisibility("show");
                     document.getElementById("error-text-p1").style.display = "block";
                     document.getElementById("error-text-p2").style.display = "block";
                     document.getElementById("error-text-p1-rollback").style.display = "block";
                     document.getElementById("error-text-p2-rollback").style.display = "block";
                     for (let i = 0; i < rows.length; i++) {
                           const row = rows[i];
                           const cells = row.children;
                           if(cells.length == 8){
                              const prio = cells[2].textContent.trim();
                              if (prio == 1) {
                                 const rollbackFailed = cells[7].querySelector(\'input[type="radio"]\').checked;
                                 const testFailed = cells[4].querySelector(\'input[type="radio"]\').checked;
                                 if (rollbackFailed) {
                                    const secondColumnText = row.querySelector("td:nth-child(1)").textContent.trim();
                                    document.getElementById("error-text-p1-rollback").textContent += secondColumnText + ", ";
                                 }
                                 if(testFailed){
                                    const secondColumnText = row.querySelector("td:nth-child(1)").textContent.trim();
                                    document.getElementById("error-text-p1").textContent += secondColumnText + ", ";
                                 }
                                 
                                 
                              }
                              if (prio == 2) {
                                 const testFailed = cells[4].querySelector(\'input[type="radio"]\').checked;
                                 const testRollbackFailed = cells[7].querySelector(\'input[type="radio"]\').checked;
                                 if (testFailed) {
                                    const secondColumnText = row.querySelector("td:nth-child(1)").textContent.trim();
                                    document.getElementById("error-text-p2").textContent += secondColumnText + ", ";
                                 }
                                 if (testRollbackFailed) {
                                    const secondColumnText = row.querySelector("td:nth-child(1)").textContent.trim();
                                    document.getElementById("error-text-p2-rollback").textContent += secondColumnText + ", ";
                                 }
                              }
                           }
                     }
                     if(document.getElementById("error-text-p1").textContent == "P1 Fehler (Umbau): "){
                           document.getElementById("error-text-p1").textContent += "-";
                     }else{
                     document.getElementById("error-text-p1").textContent = document.getElementById("error-text-p1").textContent.slice(0, -2);
                     }
                     if(document.getElementById("error-text-p2").textContent == "P2 Fehler (Umbau): "){
                           document.getElementById("error-text-p2").textContent += "-";
                     }else{
                     document.getElementById("error-text-p2").textContent = document.getElementById("error-text-p2").textContent.slice(0, -2);
                     }
                     if(document.getElementById("error-text-p1-rollback").textContent == "P1 Fehler (Rollback): "){
                           document.getElementById("error-text-p1-rollback").textContent += "-";
                     }else{
                     document.getElementById("error-text-p1-rollback").textContent = document.getElementById("error-text-p1-rollback").textContent.slice(0, -2);
                     }
                     if(document.getElementById("error-text-p2-rollback").textContent == "P2 Fehler (Rollback): "){
                           document.getElementById("error-text-p2-rollback").textContent += "-";
                     }else{
                     document.getElementById("error-text-p2-rollback").textContent = document.getElementById("error-text-p2-rollback").textContent.slice(0, -2);
                     }
                     document.getElementById("p1tests").value = document.getElementById("error-text-p1").textContent.replace("P1 Fehler (Umbau): ", "");
                     document.getElementById("p2tests").value = document.getElementById("error-text-p2").textContent.replace("P2 Fehler (Umbau): ", "");
                     document.getElementById("p1testsRollback").value = document.getElementById("error-text-p1-rollback").textContent.replace("P1 Fehler (Rollback): ", "");
                     document.getElementById("p2testsRollback").value = document.getElementById("error-text-p2-rollback").textContent.replace("P2 Fehler (Rollback): ", "");

                  }else{
                        document.getElementById("error-text-p1").style.display = "none";
                        document.getElementById("error-text-p2").style.display = "none";
                        document.getElementById("error-text-p1-rollback").style.display = "none";
                        document.getElementById("error-text-p2-rollback").style.display = "none";
                     }
}';

            // check P1, P2 table rows
            print ' 
                  document.addEventListener("DOMContentLoaded", function(){
                    
                     testTracker();
                     //checkFormValidity();
                     document.querySelectorAll(\'#questions-table input[type="radio"]\').forEach(radio => {
                        radio.addEventListener("change", checkTests);
                        // Onclick, if checked uncheck, if unchecked, check
                     });
                     document.querySelectorAll(\'#questions-table input[type="checkbox"]\').forEach(checkbox => {
                        checkbox.addEventListener("change", checkTests);
                     });
                     // Attach testTracker and setRequiredRadio to radiobuttons table1_1, table1_2, table1_3, table1_4, table1_6
                     document.getElementById("table1_1").addEventListener("change", testTracker);
                     document.getElementById("table1_2").addEventListener("change", testTracker);
                     document.getElementById("table1_3").addEventListener("change", testTracker);
                     document.getElementById("table1_4").addEventListener("change", testTracker);
                     document.getElementById("table1_6").addEventListener("change", testTracker);
                     document.getElementById("table1_1").addEventListener("change", setRequireRadio);
                     document.getElementById("table1_2").addEventListener("change", setRequireRadio);
                     document.getElementById("table1_3").addEventListener("change", setRequireRadio);
                     document.getElementById("table1_4").addEventListener("change", setRequireRadio);
                     document.getElementById("table1_6").addEventListener("change", setRequireRadio);

                     // Enable all Inputs if the user decides to switch from opt3 to any other option manually instead of deselecting opt3
                        document.getElementById("table1_1").addEventListener("change", function(){
                           toggleTableInputs(false);
                        });
                        document.getElementById("table1_2").addEventListener("change", function(){
                           toggleTableInputs(false);
                        });
                        document.getElementById("table1_4").addEventListener("change", function(){
                           toggleTableInputs(false);
                        });
                        document.getElementById("table1_5").addEventListener("change", function(){
                           toggleTableInputs(false);
                        });
                        document.getElementById("table1_6").addEventListener("change", function(){
                           toggleTableInputs(false);
                        });

                     // If the checkboxes in any row are checked, disable the radio buttons. Check this at the beginning of loading the page, otherwise the radio buttons will be enabled despite the chekboxes being checked
                        const rows = document.querySelectorAll(\'#questions-table .oddeven\');
                        for (let i = 0; i < rows.length; i++) {
                           const row = rows[i];
                           const cells = row.children;
                           if(cells.length == 8){
                              const prio = cells[2].textContent.trim();
                              const notAvailable = cells[5].querySelector(\'input[type="checkbox"]\').checked;
                              if(notAvailable){
                                 // console.log("Not available");
                                 cells[3].querySelector(\'input[type="radio"]\').disabled = true;
                                 cells[3].querySelector(\'input[type="radio"]\').checked = false;
                                 cells[4].querySelector(\'input[type="radio"]\').disabled = true;
                                 cells[4].querySelector(\'input[type="radio"]\').checked = false;
                                 cells[6].querySelector(\'input[type="radio"]\').disabled = true;
                                 cells[6].querySelector(\'input[type="radio"]\').checked = false;
                                 cells[7].querySelector(\'input[type="radio"]\').disabled = true;
                                 cells[7].querySelector(\'input[type="radio"]\').checked = false;
                                 continue;
                              } 
                        }
                     }
                  
                  });';
            // end check P1, P2 table rows

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
                     }
            '; 
            //end show/hide ruckbau table

            // Show note input field when checking Sonstiges field
            print '
            function toggleNoteInput() {
               const noteInput = document.getElementById("note-other");
               const selectedRadio = document.querySelector(\'input[name="table2"]:checked\');

               if (selectedRadio && selectedRadio.value === "5") {
                  noteInput.style.display = "block";
               } else {
                  noteInput.style.display = "none";
               }
            }
         ';
         // End show note input field when checking Sonstiges field
         // Check all done VKST 4.0
            print '
            $(document).ready(function() {
               toggleNoteInput();
               $(".check-all").click(function() {
                  var column = $(this).data("column");
                  $("." + column + "-radio").prop("checked", true);
                  checkTests();
               });
            });
         ';
         // End check all done VKST 4.0
         print '</script>';   
      }
   print '</div>';
   print '<div class="row mt-3">';
      print '<div class="col center">';
         print '<input type="submit" value="Save" id="save-form">';
      print '</div>';
      print '<div class="col center">';
         print '<input type="submit" value="PDF" id="generate-pdf">';
      print '</div>';
      print '<div class="col center">';
         print '<input type="submit" value="CSV" id="csv" onclick="exportToCSV()">';
      print '</div>';
   print '</div>';


 
   $dir = DOL_DOCUMENT_ROOT.'/formsImages/';
   if(!is_dir($dir)){
      mkdir($dir);
   }
   
   $imagesList = array();
   $images = array();

   $sql = 'SELECT images FROM llx_tec_forms WHERE fk_ticket = '.$object->id.' AND fk_store = '.$storeid.' AND fk_soc = '.$object->fk_soc.' AND fk_user = '.$object->fk_user_assign.';';
   // var_dump($object);
   $list = $db->query($sql)->fetch_row();
  
   if($list[0 != null]) {
  
      $arr = json_decode(base64_decode($list[0]));
      foreach($arr as $elm){
         array_push($imagesList, $elm);
      }
   }

   if(isset($_POST['delete-image'])) {
      // var_dump(1);
      $imagesList = array_filter($imagesList, function ($object) {
         return $object->type !== $_POST["image-group"];
      });
      $filepath = $dir.$_POST["image"];
      $list = json_encode($imagesList);
      $sql = 'UPDATE llx_tec_forms set images = "'.base64_encode($list).'" WHERE fk_ticket = '.$object->id.' AND fk_user = '.$object->fk_user_assign.' AND fk_store = '.$storeid.' AND fk_soc = '.$object->fk_soc.';';
      // var_dump($sql);
      $db->query($sql,0,'ddl');
      unlink($filepath);
      print '<script>window.location.href = window.location.href;
      </script>';
   }
   print '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>';
   print '<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.5.0-beta4/html2canvas.min.js"></script>';
   print '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/5.3.2/jspdf.plugin.autotable.min.js"></script>';
   $parameters = json_decode(base64_decode($result[1]));
   $images = json_decode(base64_decode($result[3]));
   $encoded_params = json_encode($parameters);
   // var_dump($result);
   if($result[6]){
      print '<script>';
         // auto fill inputs 
         print '
               let parameters = \'' . $encoded_params . '\';
               let param = parameters.replace(/\r/g, "");
               param = param.replace(/\n/g, "");
               let decodedParameters = JSON.parse(param);
      
               decodedParameters.forEach(param => {
                  const inputElement = document.querySelector(`[name="${param.name}"]`);
                  if (inputElement) {
                     // Set the value based on the input type
                     switch (inputElement.type) {
                        case "text":
                        case "select-one":
                        case "number":
                        case "time":
                        case "date":
                           // updated from inputElement.value = param.value to this one so the pdf will show it
                           inputElement.setAttribute("value", param.value);
                        break;
                        case "textarea":
                           inputElement.value = param.value;
                        break;
                        case "checkbox":
                           if(param.value === "1"){
                              inputElement.setAttribute("checked", param.value); // Checked if value is "1"
                           }
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
               });';
         // end auto fill inputs      
      print '</script>';
	   if($action == "edit"){

         print '<script>';

            // export as csv
            print '
            function exportToCSV() {
               const tables = document.getElementById("report-body").querySelectorAll("table:not(#times-table)");
               const textareas = document.getElementById("report-body").querySelectorAll("textarea");
               const csvContent = [];
               

               tables.forEach(table => {
                  const rows = table.querySelectorAll("tr");
                  rows.forEach(row => {
                     const csvRow = [];
                     row.querySelectorAll("td").forEach(cell => {
                        let cellValue = cell.textContent.trim();

                        if (cellValue !== "") {
                           let elm = [];
                           cell.querySelectorAll("input").forEach(inputElement => {
                              if (inputElement) {
                                 switch (inputElement.type) {
                                    case "number":
                                       elm.push(inputElement.value);
                                       break;
                                 }
                              }
                           });
                           if(elm.length > 0){
                              cellValue = elm.join(":");
                           } else {
                              cellValue = cellValue;
                           }
                        } else {
                           cell.querySelectorAll("input").forEach(inputElement => {
                              if (inputElement) {
                                 // Handle input elements
                                 switch (inputElement.type) {
                                 case "date":
                                 case "datetime-local":
                                    const dateObj = new Date(inputElement.value);

                                    cellValue = dateObj.toLocaleString("de-DE", {
                                       year: "numeric",
                                       month: "numeric",
                                       day: "numeric"
                                    });
                                    break;
                                 case "time":
                                    cellValue = 2;
                                    break;
                                 case "number":
                                    cellValue = inputElement.value;
                                    break;
                                 case "checkbox":
                                    cellValue = inputElement.checked ? "Ja" : "Nein";
                                    break;
                                 default:
                                    cellValue = inputElement.value;
                                    break;
                                 }
                              } else {
                                 cellValue = "";
                              }
                           });
                        }

                        csvRow.push(cellValue);
                     });
                     csvContent.push(csvRow.join(","));
                  });
                  csvContent.push(""); // Add a blank line between tables
               });
               textareas.forEach(textarea => {
                  // Find the closest label
                  const label = textarea.previousElementSibling; // Assuming sibling relationship

                  // Check if label exists and has text content
                  if (label && label.textContent.trim() !== "") {
                     csvContent.push(label.textContent.trim()); // Add label content
                  }

                  csvContent.push(textarea.value.trim()); // Add textarea content
                  csvContent.push(""); // Add a blank line
               });

               const csvData = csvContent.join("\n");
               const blob = new Blob([csvData], { type: "text/csv" });
               const url = URL.createObjectURL(blob);

               const a = document.createElement("a");
               a.href = url;
               let ticket = "'.$object->ref.'";
               a.download = ticket + "-report";
               a.click();
               URL.revokeObjectURL(url);
            }
            function isValidTime(timeStr) {
               const timeRegex = /^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/;
               return timeRegex.test(timeStr);
            }
            ';
            // end export as csv

            // generate pdf
            print '      
                  document.getElementById("generate-pdf").addEventListener("click", () => {
                     const { jsPDF } = window.jspdf;

                     // Create a temporary div to hold the content
                     const tempDiv = document.createElement("div");
                     console.log(document.getElementById("report-body").innerHTML);
                     tempDiv.innerHTML = document.getElementById("report-body").innerHTML;
                     document.body.appendChild(tempDiv);

                     // Set the temp div to match A4 aspect ratio
                     const a4Width = 400; // A4 width in mm
                     const a4Height = 210; // A4 height in mm
                     const dpi = 160; // Screen resolution
                     const a4WidthPx = Math.floor(a4Width * (dpi / 25.4)); // Convert mm to px
                     const a4HeightPx = Math.floor(a4Height * (dpi / 25.4)); // Convert mm to px

                     tempDiv.style.width = `${a4WidthPx}px`;
                     tempDiv.style.position = "absolute";

                     html2canvas(tempDiv, {
                        scale: 4,
                        useCORS: true,
                        logging: true,
                        backgroundColor: "#fff"
                     }).then(canvas => {

                        // Remove the temporary div
                        document.body.removeChild(tempDiv);

                        const imgData = canvas.toDataURL("image/png");

                        const pdf = new jsPDF("l", "mm", "a4");
                        const pageWidth = pdf.internal.pageSize.getWidth();
                        const pageHeight = pdf.internal.pageSize.getHeight();
                        const padding = 4;
                        const imgWidth = pageWidth - 2 * padding;
                        const imgHeight = (canvas.height * imgWidth) / canvas.width;

                        if (imgHeight <= pageHeight - 2 * padding) {
                           pdf.addImage(imgData, "PNG", padding, padding, imgWidth, imgHeight);
                        } else {
                           pdf.addImage(imgData, "PNG", padding, padding, imgWidth, pageHeight - 2 * padding);
                        }

                        pdf.save("exported-table.pdf");
                     }).catch(error => {
                        console.error("Error in html2canvas:", error);
                     });
                  });';
            // end generate pdf 

            // Deselct radio button when click on twice
               print '
               let lastSelectedRadio = null;
               document.querySelectorAll(\'input[type="radio"]\').forEach(radio => {
                     radio.addEventListener("click", function(event) {
                        if (this === lastSelectedRadio) {
                           this.checked = false; // Deselect the radio button
                           // Additionally check if this is the table1_3 radio button, the call toggleTableInputs again
                           if (this.id === "table1_3") {
                              toggleTableInputs(false);
                              checkTests();
                           }
                           lastSelectedRadio = null; // Reset last selected radio button
                        } else {
                           lastSelectedRadio = this; // Update last selected radio button
                        }
                     });
               });';
            // End deselect radio button when click on twice
            
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
                        // console.log(parameters);
                        // Add the full HTML content
                        formData.append("form", $("#report-body").html());
                        formData.append("parameters", JSON.stringify(parameters));
                        // Add other required fields
                        formData.append("storeId", "'.$storeid.'");
                        formData.append("userId", "'.$object->fk_user_assign.'");
                        formData.append("ticketId", "'.$id.'");
                        formData.append("socId", "'.$object->fk_soc.'");
                        
                        savePDFOnServer(formData, 1);
                     });
         
                     function savePDFOnServer(formData, redirect) {
                        $.ajax({
                           url: "../tecform.php",
                           type: "POST",
                           data: formData,
                           processData: false,
                           contentType: false,
                           success: function(response) {
                              console.log(response);
                              if(redirect == 1){
                                 window.location.href = "reportOverview.php?id="+ "'.$id.'" + "&action=view";
                              }
                           },
                           error: function(xhr, status, error) {
                              console.error("Request failed with status: " + xhr.status + ", Error: " + error);
                              if(redirect == 1){
                                 window.location.href = "reportOverview.php?id="+ "'.$id.'" + "&action=edit";
                              }
                           }
                        });
                     }';
            //end save form
         print '</script>';
		
	   }else if($action == "view"){
         //turn every input into a text field, disable every checkbox and radio button but keep the values. Basically prevent user from changing anything
         print '<script>';
            print 'document.querySelectorAll("input").forEach(input => {
                     if (input.type === "checkbox" || input.type === "radio") {
                        input.disabled = true;
                     } else {
                        input.type = "text";
                        input.disabled = true;
                        if(input.name === "p1tests" || input.name === "p2tests" || input.name === "p1testsRollback" || input.name === "p2testsRollback"){
                           input.type = "hidden";
                        }
                     }
                  });';
            print 'document.querySelectorAll("select").forEach(select => {
                     select.disabled = true;
                     select.style.backgroundColor = "#80808026";
                  });';
            print 'document.querySelectorAll("textarea").forEach(textarea => {
                     textarea.disabled = true;
                     textarea.style.backgroundColor = "#80808026";
                  });';
            print 'document.querySelectorAll("button").forEach(textarea => {
                     textarea.disabled = true;
                  });';
            // However, enable the close button for popup images
            print 'document.getElementById("closePopupBtn").disabled = false;';
            
         print '</script>';
	   } else if($action == "createMail"){
         // var_dump($object->array_options["options_dateofuse"]);
         $workStartValue = "";
         $workEndValue = "";
         $otherNote = "";
         $table2Checked = "";
         $p2tests = "";
         $serverImages = [];
         $documentImages = [];
         $table1 = "";
         $ticketDate = $object->array_options["options_dateofuse"] ? date("d.m.y", $object->array_options["options_dateofuse"]) : "";
         foreach ($parameters as $item) {
            if ($item->name === 'work-start') {
               $workStartValue = $item->value;
            }
            if ($item->name === 'work-end') {
               $workEndValue = $item->value;
            }
            if ($item->name === 'p2tests') {
               
               $p2tests = $item->value;
            }
            if($item->name === 'p1tests'){
               
               $p1tests = $item->value;
            }
            if($item->name === 'p1testsRollback'){
               
               $p1testsRollback = $item->value;
            }
            if($item->name === 'p2testsRollback'){
               
               $p2testsRollback = $item->value;
            }
            if ($item->name === 'table2') {
               $table2Checked = $item->value;
            }
            if ($item->name === 'note-other') {
               $otherNote = $item->value;
               break;
            }
            if($item->name === 'table1'){
               $table1 = $item->value;
            }
            
            
         }
         foreach ($images as $group) {
            if ($group->type === 'serverschrank nachher' || $group->type === 'Serverschrank nachher') {
               $serverImages = $group->images;
            }
            if ($group->type === 'image abnahmeprotokoll/testprotokoll' || $group->type === 'Testprotokoll') {
               $documentImages = $group->images;
            }
         }
         print '<div id="mail-body">Sehr geehrtes VKST4.0 Projektteam,';
         print '<div id="options-body"></div>';
         print '<script>	
                  document.getElementById("report-body").style.display = "none";
                  document.getElementById("reportOptions").style.display = "none";
                  document.getElementById("save-form").style.display = "none";
                  document.getElementById("generate-pdf").style.display = "none";
                  document.getElementById("csv").style.display = "none";


                  
                  const rows = Array.from(document.querySelectorAll("#options-table.noborder.centpercent tr.oddeven"));
                  console.log(rows);
                  let selectedOptionText = "";

                  rows.forEach(row => {
                     console.log(row);
                     const radio = row.querySelector("input[type=radio]");
                     console.log(radio);
                     if (radio && radio.checked) {
                        selectedOptionText = row.cells[0].innerText;
                     }
                  });

                  if (selectedOptionText) {
                     document.getElementById("options-body").innerHTML += `${selectedOptionText}`;

                  }
               </script>';
         $s = '<div class="page-body row" id="page-body">';
            $s .= '<div class="col-12">';
               $s .= '<b id="ssss">VKST-Details</b>';
            $s .= '</div>';
            $s .= '<div class="col-12">';
               $s .= '<table  border="1" cellpadding="1" cellspacing="1" class="noborder centpercent" id="header-table" style="width:50%">
                        <tr>
                              <td style="width:150px">VKST-ID:</td>
                              <td style="width:150px">'.explode("-", $store->b_number)[2].'</td>
                        </tr>
                        <tr>
                              <td style="width:150px">Ticketnummer:</td>
                              <td style="width:150px">'.$object->ref.'</td>
                        </tr>
                        <tr>
                              <td style="width:150px">Adresse:</td>
                              <td style="width:150px">'.$store->street.' '.$store->house_number.', '. $store->zip_code.' '. $store->city.'</td>
                        </tr>
                     </table>';
            $s .= '</div><br>';
            $s .= '<div class="col-12">';
               $s .= '<b>Umbaudetails</b>';
            $s .= '</div>';
            $s .= '<div class="col-12">';
               $s .= '<table border="1" cellpadding="1" cellspacing="1" class="noborder centpercent" id="body-table" style="width:50%">
                        <tr>
                              <td style="width:150px">Techniker</td>
                              <td style="width:150px">'.$techName.'</td>
                        </tr>
                        <tr>
                              <td style="width:150px">Datum</td>
                              <td style="width:150px">'.$ticketDate.'</td>
                        </tr>
                        <tr>
                              <td style="width:150px">Uhrzeit Start</td>
                              <td style="width:150px">'.$workStartValue.'</td>
                        </tr>
                        <tr>
                              <td style="width:150px">Uhrzeit Ende</td>
                              <td style="width:150px">'.$workEndValue.'</td>
                        </tr>
                        ';
            if($table1 == "2"){
               $s .= '<tr>
                              <td style="width:150px">Fehlgeschlagene P2 Tests</td>
                              <td style="width:150px">'.$p2tests.'</td>
                        </tr>
                      ';
            }
            if($table1 == "4"){
               $s .= '<tr>
                        <td style="width:150px">Fehlgeschlagene P1 Tests</td>
                        <td style="width:150px">'.$p1tests.'</td>
                     </tr>';
                     $s .= '<tr>
                           <td style="width:150px">Fehlgeschlagene P2 Tests</td>
                           <td style="width:150px">'.$p2tests.'</td>
                        </tr>
                      ';
            }
            if($table1 == "5"){
               $s .= '<tr>
                        <td style="width:150px">Fehlgeschlagene P1 Tests</td>
                        <td style="width:150px">'.$p1tests.'</td>
                     </tr>';
               $s .= '<tr>
                           <td style="width:150px">Fehlgeschlagene P2 Tests</td>
                           <td style="width:150px">'.$p2tests.'</td>
                        </tr>
                      ';
               $s .= '<tr>
                        <td style="width:150px">Fehlgeschlagene P2 Tests (Rollback)</td>
                        <td style="width:150px">'.$p2testsRollback.'</td>
                     </tr>';
            }
            if($table1 == "6"){
               $s .= '<tr>
                        <td style="width:150px">Fehlgeschlagene P1 Tests</td>
                        <td style="width:150px">'.$p1tests.'</td>
                     </tr>';
               $s .= '<tr>
                           <td style="width:150px">Fehlgeschlagene P2 Tests</td>
                           <td style="width:150px">'.$p2tests.'</td>
                        </tr>
                      ';
               $s .= '<tr>
                        <td style="width:150px">Fehlgeschlagene P1 Tests (Rollback)</td>
                        <td style="width:150px">'.$p1testsRollback.'</td>
                     </tr>';
               $s .= '<tr>
                        <td style="width:150px">Fehlgeschlagene P2 Tests (Rollback)</td>
                        <td style="width:150px">'.$p2testsRollback.'</td>
                     </tr>';
            }
            $s .= '</table>';
            $s .= '</div>';
         $s .= '</div><br>';
         $s .= '<hr>';
         $se = '<div class="col-12">';
            $se .= '<b>Grund des Rückbaus (Sonstiges bitte unten beschreiben):</b>';
         $se .= '</div>';
         $se .= '<div class="col-12" id="ruckbau-table"">';
            $se .= '<table class="noborder centpercent" style="width:50%">';
               $se .= '<tr class="oddeven">';
                  $check1 = $table2Checked == "1" ? "checked" : "";
                  $se .= '<td colspan="1">Fehlgeschlagener P1 Test (bitte Nummer angeben)</td>';
                  $se .= '<td colspan="1"><input type="radio" name="table2" id="table2_1" value="1" '.$check1.' disabled></td>';
               $se .= '</tr>';
               $se .= '<tr class="oddeven">';
                  $check2 = $table2Checked == "2" ? "checked" : "";
                  $se .= '<td colspan="1">Fehlendes Material</td>';
                  $se .= '<td colspan="1"><input type="radio" name="table2" id="table2_2" value="2" '.$check2.' disabled></td>';
               $se .= '</tr>';
               $se .= '<tr class="oddeven">';
                  $check3 = $table2Checked == "3" ? "checked" : "";
                  $se .= '<td colspan="1">Fehler der Automatisierung / App</td>';
                  $se .= '<td colspan="1"><input type="radio" name="table2" id="table2_3" value="3" '.$check3.' disabled></td>';
               $se .= '</tr>';
               $se .= '<tr class="oddeven">';
                  $check4 = $table2Checked == "4" ? "checked" : "";
                  $se .= '<td colspan="1">Defekte Hardware</td>';
                  $se .= '<td colspan="1"><input type="radio" name="table2" id="table2_4" value="4" '.$check4.' disabled></td>';
               $se .= '</tr>';
               $se .= '<tr class="oddeven">';
                  $check5 = $table2Checked == "5" ? "checked" : "";
                  $se .= '<td colspan="1">Sonstiges</td>';
                  $se .= '<td colspan="1"><input type="radio" name="table2" id="table2_5" value="5" '.$check5.' disabled></td>';
               $se .= '</tr>';
            $se .= '</table><br>';
            $se .= '<label>Sonstiges: </label><input type="text" id="note-other" style="width:100%; margin-bottom: 15px;width:50%" name="note-other" value="'.$otherNote.'">';
         $se .= '</div>';
         print $s;
         print $se;
         print '<button onClick="copyToClipboard()">Tabelleninhalt kopieren</button> ';
         $ruckbaus = '<div class="col-12">';
            $ruckbaus .= '<b>Grund des Rückbaus (Sonstiges bitte unten beschreiben):</b>';
         $ruckbaus .= '</div>';
         $ruckbaus .= '<div class="col-12" id="ruckbau-table"">';
            if($table2Checked == "1"){
               $ruckbaus .= "Fehlgeschlagener P1 Test (bitte Nummer angeben)";
            }
            if($table2Checked == "2"){
               $ruckbaus .= "Fehlendes Material";
            }
            if($table2Checked == "3"){
               $ruckbaus .= "Fehler der Automatisierung / App";
            }
            if($table2Checked == "4"){
               $ruckbaus .= "Defekte Hardware";
            }
            if($table2Checked == "5"){
               $ruckbaus .= '<label>Sonstiges: '.$otherNote;
            }
         $ruckbaus .= '</div>'; 
         $ruckbaus .= '<hr>'; 
         $emailContent = '';
         
         $emailContent .= '<div class="col-12">';
         $success = 'Der Umbau in VKST-'.explode("-", $store->b_number)[2].' wurde erfolgreich abgeschlossen';
         $failedP2 = 'Der Umbau in VKST-'.explode("-", $store->b_number)[2].' wurde erfolgreich abgeschlossen. Mindestens 1 P2 Test konnte nicht erfolgreich durchgeführt werden (siehe unten).';
         $notstarted = 'Der Umbau in VKST-'.explode("-", $store->b_number)[2].' konnte nicht gestartet werden. Die Gründe sind unter "Sonstiges" zu finden.';
         $rollbackSuccess = 'Der Umbau in VKST-'.explode("-", $store->b_number)[2].' konnte nicht abgeschlossen werden. Der Rollback auf VKST3.0 war erfolgreich.';
         $rollbackP2Failed = 'Der Umbau in VKST-'.explode("-", $store->b_number)[2].' konnte nicht abgeschlossen. Mindestens 1 P2 Test konnte nicht erfolgreich durchgeführt werden (siehe unten).';
         $rollbackFailed = 'Der Umbau in VKST-'.explode("-", $store->b_number)[2].' konnte nicht abgeschlossen. Auch der Rollback war erfolglos. Der Technikerleitstand wurde bereits informiert.'; 
            // $emailContent .= '
            //       <table class="noborder centpercent" id="body-table" style="width:95%">
            //          <tr>
            //                <th><b>Zustand</b></th>
            //                <th><b>Betreff</b></th>
            //                <th><b>Inhalt</b></th>
            //          </tr>
            //          <tr>
            //             <td>
            //                <ul>
            //                   <li>Umbau wurde erfolgreich abgeschlossen</li>
            //                   <li>Alle Tests wurden erfolgreich durchgeführt</li>
            //                </ul>
            //             </td>
            //             <td>
            //                VKST4.0 - '.$store->b_number.' Ende ERFOLGREICH
            //             </td>
            //             <td>
            //                Der Umbau in VKST '.$store->b_number.' wurde erfolgreich abgeschlossen
            //             </td>
            //          </tr>
            //          <tr>
            //             <td>
            //                <ul>
            //                   <li>Umbau wurde erfolgreich abgeschlossen</li>
            //                   <li>Es ist mindestes ein P2 Tests fehlgeschlagen</li>
            //                </ul>
            //             </td>
            //             <td>
            //                VKST4.0 - '.$store->b_number.' Ende ERFOLGREICH, offene Themen
            //             </td>
            //             <td>
            //                Der Umbau in VKST '.$store->b_number.' wurde erfolgreich abgeschlossen. Mindestens 1 P2 Test konnte nicht erfolgreich durchgeführt werden (siehe unten).
            //             </td>
            //          </tr>
            //          <tr>
            //             <td>
            //                <ul>
            //                   <li>Der Umbau konnte nicht stattfinden / wurde vor Beginn abgebrochen</li>
            //                </ul>
            //             </td>
            //             <td>
            //                VKST4.0 - '.$store->b_number.' NICHT ERFOLGT
            //             </td>
            //             <td>
            //                Der Umbau in VKST '.$store->b_number.' konnte nicht gestartet werden. Die Gründe sind unter "Sonstiges" zu finden.
            //             </td>
            //          </tr>
            //          <tr>
            //             <td>
            //                <ul>
            //                   <li>Der Umbau auf VKST4.0 konnte nicht erfolgreich durchgeführt werden</li>
            //                   <li>Nach Rollback konnten alle Tests erfolgreich durchgeführt werden</li>
            //                </ul>
            //             </td>
            //             <td>
            //                VKST4.0 - '.$store->b_number.' ROLLBACK ERFOLGREICH
            //             </td>
            //             <td>
            //                Der Umbau in VKST '.$store->b_number.' konnte nicht abgeschlossen werden. Der Rollback auf VKST3.0 war erfolgreich.
            //             </td>
            //          </tr>
            //          <tr>
            //             <td>
            //                <ul>
            //                   <li>Es wurde ein Rollback erfolgreich durchgeführt</li>
            //                   <li>Nach Rollback ist mindestens ein P2 Tests fehlgeschlagen</li>
            //                </ul>
            //             </td>
            //             <td>
            //                VKST4.0 - '.$store->b_number.' ROLLBACK ERFOLGREICH, offene Themen
            //             </td>
            //             <td>
            //                Der Umbau in VKST '.$store->b_number.' konnte nicht abgeschlossen werden. Mindestens 1 P2 Test konnte nicht erfolgreich durchgeführt werden (siehe unten).
            //             </td>
            //          </tr>
            //          <tr>
            //             <td>
            //                <ul>
            //                   <li>Der Rollback war nicht erfolgreich</li>
            //                   <li>Projektleitstand wurde informiert</li>
            //                </ul>
            //             </td>
            //             <td>
            //                VKST4.0 - '.$store->b_number.' ROLLBACK FEHLSCHLAG
            //             </td>
            //             <td>
            //                Der Umbau in VKST '.$store->b_number.' konnte nicht abgeschlossen werden. Auch der Rollback war erfolglos. Der Technikerleitstand wurde bereits informiert.
            //             </td>
            //          </tr>
            //       </table>';
         //$emailContent .= '</div><br>';
         $emailContent .= '<p>Sehr geehrtes VKST4.0 Projektteam,</p>';
         if($table1 == "1"){
            $emailContent .= $success.'';
         }
         if($table1 == "2"){
            $emailContent .= $failedP2.'';
         }
         if($table1 == "3"){
            $emailContent .= $notstarted.'';
         }
         if($table1 == "4"){
            $emailContent .= $rollbackSuccess.'';
         }
         if($table1 == "5"){
            $emailContent .= $rollbackP2Failed.'';
         }
         if($table1 == "6"){
            $emailContent .= $rollbackFailed.'';
         }
         $emailContent .= '<br>';
         $emailContent .= '<br>';
         $emailContent .= $s;
         $emailContent .= $ruckbaus;
         $imagesNames = [];
         $imagesPaths = [];
         $imagesMimes = [];
         
         if($serverImages) {
            foreach($serverImages as $image){
               array_push($imagesNames, $image);
               array_push($imagesMimes, $formticket->get_image_mime_type_by_extension($image));
               array_push($imagesPaths, DOL_DOCUMENT_ROOT.'/formsImages/'.$image);
            }
         }
         if($documentImages) {
            foreach($documentImages as $image){
               array_push($imagesNames, $image);
               array_push($imagesMimes, $formticket->get_image_mime_type_by_extension($image));
               array_push($imagesPaths, DOL_DOCUMENT_ROOT.'/formsImages/'.$image);
            }
         }

         $emailContent .= 'Mit freundlichen Grüßen,';
         $emailContent .= '<br>';
         // $emailContent .= $techName;
         $emailContent .= 'SESOCO Team';
         if ($object->fk_soc > 0) {
            $object->fetch_thirdparty();
         }

         print '<script>
                        let rows2 = document.querySelectorAll(\'#questions-table .oddeven\');
                        for(let i = 0; i < rows2.length - 4; i++){
                           const row = rows2[i];
                           console.log("Row" + row);
                           const cells = row.children;
                           console.log("Cells " + cells);
                           const prio = cells[2].textContent.trim();
                           console.log("Prio" + prio);
                           if(prio == 2){
                              const testFailed = cells[4].querySelector(\'input[type="radio"]\').checked;
                                 if(testFailed){
                                    const secondColumnText = row.querySelector("td:nth-child(1)").textContent.trim();
                                    console.log("Wo es hakt" + document.getElementById("p2Errors"));
                                    document.getElementById("p2Errors").textContent += secondColumnText+ ", ";
                                    console.log("Found" + secondColumnText);
                                 }
                              }
                        }
                        document.getElementById("p2Errors").textContent = document.getElementById("p2Errors").textContent.slice(0, -2);
                        document.getElementById("workTimeStart").textContent = document.getElementById("input-work-start").value;
                        document.getElementById("workTimeEnd").textContent = document.getElementById("input-work-end").value;

                        function copyToClipboard() {
                              
                           // Iterate through all table rows and append inner html to copy such that left column : right column
                           let copyText = \'\';
                           let mailBody = document.getElementById(\'mail-body\').innerHTML;
                           let optionsBody = document.getElementById(\'options-body\').innerHTML;
                           // Remove HTML tags from mail body
                           let tempDiv = document.createElement(\'div\');
                           tempDiv.innerHTML = mailBody;
                           copyText += tempDiv.innerText + \'\\n\';
                           tempDiv.innerHTML = optionsBody;
                           copyText += tempDiv.innerText + \'\\n\';
                  
                           let rows = document.querySelectorAll(\'#body-table tr\');
                           let rows2 = document.querySelectorAll(\'#header-table tr\');
                           rows2.forEach(row => {
                              const cells = row.children;
                              copyText += cells[0].textContent + \': \' + cells[1].textContent + \'\\n\';
                           });
                           
                           rows.forEach(row => {
                              const cells = row.children;
                              copyText += cells[0].textContent + \': \' + cells[1].textContent + \'\\n\';
                           });
                  
                           // Create a temporary text field to copy the text to the clipboard
                           const tempInput = document.createElement(\'textarea\');
                           tempInput.value = copyText;
                           document.body.appendChild(tempInput);
                  
                           // Den Text markieren und in die Zwischenablage kopieren
                           tempInput.select();
                           document.execCommand(\'copy\');
                              
                           // Das temporäre Textfeld entfernen
                           document.body.removeChild(tempInput); 
                           alert(\'HTML-Code wurde in die Zwischenablage kopiert.\');
                        }
                  </script>';
         $outputlangs = $langs;
         $newlang = '';
         if (getDolGlobalInt('MAIN_MULTILANGS') && empty($newlang) && GETPOST('lang_id', 'aZ09')) {
            $newlang = GETPOST('lang_id', 'aZ09');
         } elseif (getDolGlobalInt('MAIN_MULTILANGS') && empty($newlang) && is_object($object->thirdparty)) {
            $newlang = $object->thirdparty->default_lang;
         }
         if (!empty($newlang)) {
            $outputlangs = new Translate("", $conf);
            $outputlangs->setDefaultLang($newlang);
         }

         $arrayoffamiliestoexclude = array('objectamount');

         $action = 'add_message'; // action to use to post the message
         $modelmail = 'ticket_send';

         // Substitution array
         $morehtmlright = '';
         $help = "";
         $substitutionarray = getCommonSubstitutionArray($outputlangs, 0, $arrayoffamiliestoexclude, $object);
         // $morehtmlright .= $form->textwithpicto('<span class="opacitymedium">'.$langs->trans("TicketMessageSubstitutionReplacedByGenericValues").'</span>', $help, 1, 'helpclickable', '', 0, 3, 'helpsubstitution');

         print '<div>';

         print '<div id="formmailbeforetitle" name="formmailbeforetitle"></div>';

         print load_fiche_titre($langs->trans('TicketAddMessage'), $morehtmlright, 'messages@ticket');

         print '<hr>';

         // $formticket = new FormTicket($db);
         $action = "add_message";
         $backtopage = "/ticket/reportOverview?id=".$object->id."&action=".$action;
         $formticket->action = $action;
         $formticket->track_id = $object->track_id;
         $formticket->ref = $object->ref;
         $formticket->id = $object->id;
         $formticket->trackid = 'tic'.$object->id;

         $formticket->withfile = 2;
         $formticket->withcancel = 1;
         $formticket->param = array('fk_user_create' => $user->id);
         $formticket->param['langsmodels'] = (empty($newlang) ? $langs->defaultlang : $newlang);

         // Table of additional post parameters
         $formticket->param['models'] = $modelmail;
         $formticket->param['models_id'] = GETPOST('modelmailselected', 'int');
         //$formticket->param['socid']=$object->fk_soc;
         $formticket->param['returnurl'] = $_SERVER["PHP_SELF"].'?id='.$object->id.'&action='.$action.'&track_id='.$object->track_id;
         $formticket->param['returnurlForm'] = $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=createMail&track_id='.$object->track_id;
         $formticket->param['imagesNames'] = $imagesNames;
         $formticket->param['imagesPaths'] = $imagesPaths;
         $formticket->param['imagesMimes'] = $imagesMimes;

         $formticket->withsubstit = 1;
         $formticket->substit = $substitutionarray;
         $formticket->backtopage = $backtopage;

         $formticket->showMessageFormReport('100%', $emailContent);
          // Automatically fill in the subject based on selected option
          print '<script>
                  if('.$table1.' == 1){
                     // Get subject input field with name subject
                     document.querySelector(\'input[name="subject"]\').value = "VKST4.0 - *VKST-'.explode("-", $store->b_number)[2].'* Ende ERFOLGREICH";
                  }else if('.$table1.' == 2){
                     document.querySelector(\'input[name="subject"]\').value = "VKST4.0 - *VKST-'.explode("-", $store->b_number)[2].'* Ende ERFOLGREICH, offene Themen";
                  }else if('.$table1.' == 3){
                     document.querySelector(\'input[name="subject"]\').value = "VKST4.0 - *VKST-'.explode("-", $store->b_number)[2].'* NICHT ERFOLGT";
                  }else if('.$table1.' == 4){  
                     document.querySelector(\'input[name="subject"]\').value = "VKST4.0 - *VKST-'.explode("-", $store->b_number)[2].'* ROLLBACK ERFOLGREICH";
                  }else if('.$table1.' == 5){
                     document.querySelector(\'input[name="subject"]\').value = "VKST4.0 - *VKST-'.explode("-", $store->b_number)[2].'* ROLLBACK ERFOLGREICH, offene Themen";
                  }else if('.$table1.' == 6){
                     document.querySelector(\'input[name="subject"]\').value = "VKST4.0 - *VKST-'.explode("-", $store->b_number)[2].'* ROLLBACK FEHLSCHLAG";
                  }
                  // Add margin left to that input 
                  document.querySelector(\'input[name="subject"]\').style.marginLeft = "10px";
               </script>';
         print '</div>';
      }
   } else {
      print '<script>';
      print 'document.getElementById("report-body").style.display = "none";';
      print 'document.getElementById("reportOptions").style.display = "none";';
      print 'document.getElementById("save-form").style.display = "none";';
      print 'document.getElementById("generate-pdf").style.display = "none";';
      print 'document.getElementById("csv").style.display = "none";';
      print '</script>';
      print '<div class="row">';
         print '<form action="" method="POST" enctype="multipart/form-data"><input type="hidden" name="token" value="'.newToken().'">';
            print '<div class="col-12">';
               print 'Noch kein Bericht vorhanden';
            print '</div>';
            print '<div class="col-12 mt-2">';
               print '<input type="submit" id="add-report" name="add-report" value="Add a new Report">';
            print '</div>';
         print '</form>';
      print '</div>';
   }
   if(isset($_POST['add-report'])) {
      $sql = 'INSERT INTO llx_tec_forms (`fk_ticket`, `fk_user`, `fk_soc`, `fk_store`) VALUES ("'.$object->id.'", "'.$object->fk_user_assign.'", "'.$object->fk_soc.'", "'.$object->array_options["options_fk_store"].'")';
      $db->query($sql, 0, 'ddl');
     print '<script>window.location.href = window.location.href;
     </script>';
   }


// var_dump($_POST);
// Action to add a message (private or not, with email or not).
	// This may also send an email (concatenated with email_intro and email footer if checkbox was selected)
	if (GETPOSTISSET('action') == 'add_message' && GETPOSTISSET('btn_add_message')) {
      
		$ret = $object->newMessageForm($user, $action, (GETPOST('private_message', 'alpha') == "on" ? 1 : 0), 0, $imagesNames, $imagesPaths, $imagesMimes);
      // var_dump($ret);
		if ($ret > 0) {
			if (!empty($backtopage)) {
				$url = $backtopage;
			} else {
				$url = 'card.php?id='.$object->id;
			}

			// header("Location: ".$url);
			exit;
		} else {
			setEventMessages($object->error, $object->errors, 'errors');
			$action = 'presend';
		}
	}   
	
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
			width: 150px
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

      #popup, #add-row-popup {
         position: fixed;
         top: 50%;
         left: 50%;
         width: 40%;
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
         #add-row-popup {
            width: 90%;
         }
      }';
print '#questions-table td {
            width: 100px;
      }';
print '#add-report {      
            width: 15%;
            font-size: 13px;
      }';
print '</style>';

