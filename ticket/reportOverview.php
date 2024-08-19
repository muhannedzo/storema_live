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
$url_page_current = DOL_URL_ROOT.'/ticket/contact.php';
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
$sql = 'SELECT content, parameters, fk_user, images FROM llx_tec_forms WHERE fk_ticket = '.$object->id.' AND fk_store = '.$storeid.' AND fk_soc = '.$object->fk_soc.' AND fk_user = '.$object->fk_user_assign.';';
$result = $db->query($sql)->fetch_all()[0];
$parameters = json_decode(base64_decode($result[1]));
$encoded_params = json_encode($parameters);

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
         print '<div class="container">';
            print '<div class="row mb-3">';
               print '<div class="col-6 col-md-3 d-flex align-items-center">';
                  print '<label for="input-time-departure" class="form-label mb-0">Time Departure: </label>';
               print '</div>';
               print '<div class="col-6 col-md-9">';
                  print '<input type="time" id="input-time-departure" name="time-departure" class="form-control" value="'.$timeDeparture.'">';
               print '</div>';
            print '</div>';
            print '<div class="row mb-3">';
               print '<div class="col-6 col-md-3 d-flex align-items-center">';
                  print '<label for="input-time-arrival" class="form-label mb-0">Time Arrival: </label>';
               print '</div>';
               print '<div class="col-6 col-md-9">';
                  print '<input type="time" id="input-time-arrival" name="time-arrival" class="form-control" value="'.$timeArrival.'">';
               print '</div>';
            print '</div>';
            print '<div class="row mb-3">';
               print '<div class="col-6 col-md-3 d-flex align-items-center">';
                  print '<label class="form-label mb-0">Duration of Trip: </label>';
               print '</div>';
               print '<div class="col-6 col-md-9 d-flex">';
                  print '<input type="number" id="input-duration-hours" name="trip-hours" class="form-control me-2" style="max-width: 70px;" placeholder="h" value="'.$tripHours.'">';
                  print '<span class="align-self-center me-2">h :</span>';
                  print '<input type="number" id="input-duration-minutes" name="trip-minutes" class="form-control" style="max-width: 70px;" max="60" placeholder="m" value="'.$tripMinutes.'">';
                  print '<span class="align-self-center me-2">m</span>';
               print '</div>';
            print '</div>';
            print '<div class="row mb-3">';
               print '<div class="col-6 col-md-3 d-flex align-items-center">';
                  print '<label for="input-km" class="form-label mb-0">KM: </label>';
               print '</div>';
               print '<div class="col-6 col-md-9">';
                  print '<input type="number" id="input-km" class="form-control" name="km" value="'.$km.'">';
               print '</div>';
            print '</div>';
         print '</div>';
         

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

         $rowsCount += 1;         
         print '<script>';      
            // Add new row to the Messung table
            print '
                  let rowCounter = \'' . $rowsCount . '\';
                  let standard = "0000";
                  let prufnummer = "0000";

                  const addRowButton = document.getElementById("add-row");
                  const rowsCounter = document.getElementById("rows-counter");
                  const table = document.querySelector("#pieces-table table tbody");

                  addRowButton.addEventListener("click", () => {
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
                           cell.appendChild(textInput);
                        } 
                        //    else if (i == 18) {
                        //    // Create text inputs (unchanged)
                        //    const btn = document.createElement("button");
                        //    btn.className = "btn btn-primary";
                        //    btn.id = i;
                        //    btn.onclick = i;
                        //    btn.innerText = "+";
                        //    cell.appendChild(btn);
                        // }

                        newRow.appendChild(cell);
                     }

                     table.appendChild(newRow);
                     rowsCounter.value = rowCounter;

                     // Increment counters without modifying paddedSum
                     rowCounter++;
                     // prufnummer++;
                  });

                  // Function to generate padded sum with leading zeros
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
            print '<input class="textfield" type="text" name="street" value="'.$store->street.','. $store->zip_code.' '. $store->city.'" required disabled>';
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
               print '<table class="noborder centpercent" id="options-table">';
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
         print '<br>';
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
      }
   print '</div>';
   print '<div class="row mt-3">';
      print '<div class="col right">';
         print '<input type="submit" value="Save" id="save-form">';
      print '</div>';
      print '<div class="col left">';
         print '<input type="submit" value="PDF" id="generate-pdf">';
      print '</div>';
   print '</div>';









   print '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>';
   print '<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.5.0-beta4/html2canvas.min.js"></script>';
   print '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/5.3.2/jspdf.plugin.autotable.min.js"></script>';
   $parameters = json_decode(base64_decode($result[1]));
   $images = json_decode(base64_decode($result[3]));
   $encoded_params = json_encode($parameters);
   // var_dump($encoded_params);
   if($result[1] ){
      print '<script>';

         // auto fill inputs 
         print '
               
               let parameters = \'' . $encoded_params . '\';
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
                        case "date":
                           // updated from inputElement.value = param.value to this one so the pdf will show it
                           inputElement.setAttribute("value", param.value);
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
                     const a4Width = 210; // A4 width in mm
                     const a4Height = 297; // A4 height in mm
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

                        const pdf = new jsPDF("p", "mm", "a4");
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
         print '</script>';
	   } else if($action == "createMail"){
         $workStartValue = "";
         $workEndValue = "";
         $otherNote = "";
         $table2Checked = "";
         $p2tests = "";
         $serverImages = [];
         $documentImages = [];
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
            if ($item->name === 'table2') {
               $table2Checked = $item->value;
            }
            if ($item->name === 'note-other') {
               $otherNote = $item->value;
               break;
            }
         }
         foreach ($images as $group) {
            if ($group->type === 'serverschrank nachher') {
               $serverImages = $group->images;
            }
            if ($group->type === 'image abnahmeprotokoll/testprotokoll') {
               $documentImages = $group->images;
               break;
            }
         }
         print '<div id="mail-body">Sehr geehrtes VKST4.0 Projektteam,</div>';
         print '<div id="options-body"></div>';
         print '<script>	
                  document.getElementById("report-body").style.display = "none";
                  document.getElementById("reportOptions").style.display = "none";
                  document.getElementById("save-form").style.display = "none";
                  document.getElementById("generate-pdf").style.display = "none";


                  
                  const rows = Array.from(document.querySelectorAll("#options-table.noborder.centpercent tr.oddeven"));
                  console.log(rows);
                  let selectedOptionText = \'\';

                  rows.forEach(row => {
                     console.log(row);
                     const radio = row.querySelector("input[type=radio]");
                     console.log(radio);
                     if (radio && radio.checked) {
                        selectedOptionText = row.cells[0].innerText;
                     }
                  });

                  if (selectedOptionText) {
                     document.getElementById(\'options-body\').innerHTML += `${selectedOptionText}`;

                  }
               </script>';
         $s = '<div class="page-body row" id="page-body">';
            $s .= '<div class="col-12">';
               $s .= '<b id="ssss">VKST-Details</b>';
            $s .= '</div>';
            $s .= '<div class="col-12">';
               $s .= '<table class="noborder centpercent" id="header-table" style="width:50%">
                        <tr>
                              <td>VKST-ID:</td>
                              <td>'.$store->b_number.'</td>
                        </tr>
                        <tr>
                              <td>Adresse:</td>
                              <td>'.$store->street.','. $store->zip_code.' '. $store->city.'</td>
                        </tr>
                     </table>';
            $s .= '</div><br>';
            $s .= '<div class="col-12">';
               $s .= '<b>Umbaudetails</b>';
            $s .= '</div>';
            $s .= '<div class="col-12">';
               $s .= '<table class="noborder centpercent" id="body-table" style="width:50%">
                        <tr>
                              <td>Techniker (Nachname, Vorname)</td>
                              <td>'.$techName.'</td>
                        </tr>
                        <tr>
                              <td>Datum</td>
                              <td>'.date("d.m.y H:i", $object->datec).'</td>
                        </tr>
                        <tr>
                              <td>Uhrzeit Start</td>
                              <td>'.$workStartValue.'</td>
                        </tr>
                        <tr>
                              <td>Uhrzeit Ende</td>
                              <td>'.$workEndValue.'</td>
                        </tr>
                        <tr>
                              <td>Fehlgeschlagene P2 Tests</td>
                              <td>'.$p2tests.'</td>
                        </tr>
                     </table>';
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
            $emailContent .= '
                  <table class="noborder centpercent" id="body-table" style="width:95%">
                     <tr>
                           <th><b>Zustand</b></th>
                           <th><b>Betreff</b></th>
                           <th><b>Inhalt</b></th>
                     </tr>
                     <tr>
                        <td>
                           <ul>
                              <li>Umbau wurde erfolgreich abgeschlossen</li>
                              <li>Alle Tests wurden erfolgreich durchgeführt</li>
                           </ul>
                        </td>
                        <td>
                           VKST4.0 - '.$store->b_number.' Ende ERFOLGREICH
                        </td>
                        <td>
                           Der Umbau in VKST '.$store->b_number.' wurde erfolgreich abgeschlossen
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <ul>
                              <li>Umbau wurde erfolgreich abgeschlossen</li>
                              <li>Es ist mindestes ein P2 Tests fehlgeschlagen</li>
                           </ul>
                        </td>
                        <td>
                           VKST4.0 - '.$store->b_number.' Ende ERFOLGREICH, offene Themen
                        </td>
                        <td>
                           Der Umbau in VKST '.$store->b_number.' wurde erfolgreich abgeschlossen. Mindestens 1 P2 Test konnte nicht erfolgreich durchgeführt werden (siehe unten).
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <ul>
                              <li>Der Umbau konnte nicht stattfinden / wurde vor Beginn abgebrochen</li>
                           </ul>
                        </td>
                        <td>
                           VKST4.0 - '.$store->b_number.' NICHT ERFOLGT
                        </td>
                        <td>
                           Der Umbau in VKST '.$store->b_number.' konnte nicht gestartet werden. Die Gründe sind unter "Sonstiges" zu finden.
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <ul>
                              <li>Der Umbau auf VKST4.0 konnte nicht erfolgreich durchgeführt werden</li>
                              <li>Nach Rollback konnten alle Tests erfolgreich durchgeführt werden</li>
                           </ul>
                        </td>
                        <td>
                           VKST4.0 - '.$store->b_number.' ROLLBACK ERFOLGREICH
                        </td>
                        <td>
                           Der Umbau in VKST '.$store->b_number.' konnte nicht abgeschlossen werden. Der Rollback auf VKST3.0 war erfolgreich.
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <ul>
                              <li>Es wurde ein Rollback erfolgreich durchgeführt</li>
                              <li>Nach Rollback ist mindestens ein P2 Tests fehlgeschlagen</li>
                           </ul>
                        </td>
                        <td>
                           VKST4.0 - '.$store->b_number.' ROLLBACK ERFOLGREICH, offene Themen
                        </td>
                        <td>
                           Der Umbau in VKST '.$store->b_number.' konnte nicht abgeschlossen werden. Mindestens 1 P2 Test konnte nicht erfolgreich durchgeführt werden (siehe unten).
                        </td>
                     </tr>
                     <tr>
                        <td>
                           <ul>
                              <li>Der Rollback war nicht erfolgreich</li>
                              <li>Projektleitstand wurde informiert</li>
                           </ul>
                        </td>
                        <td>
                           VKST4.0 - '.$store->b_number.' ROLLBACK FEHLSCHLAG
                        </td>
                        <td>
                           Der Umbau in VKST '.$store->b_number.' konnte nicht abgeschlossen werden. Auch der Rollback war erfolglos. Der Technikerleitstand wurde bereits informiert.
                        </td>
                     </tr>
                  </table>';
         $emailContent .= '</div><br>';
         $emailContent .= '<p>Sehr geehrtes VKST4.0 Projektteam,</p><br>';
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
         $emailContent .= $techName;
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
         print '</div>';
      }
   } else {
      print '<script>';
      print 'document.getElementById("report-body").style.display = "none";';
      print 'document.getElementById("reportOptions").style.display = "none";';
      print 'document.getElementById("save-form").style.display = "none";';
      print 'document.getElementById("generate-pdf").style.display = "none";';
      print '</script>';
      print "Noch kein Bericht vorhanden";
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
			width: 100px
		 }';
print '[class^="ico-"], [class*=" ico-"] {
			font: normal 1em/1 Arial, sans-serif;
			display: inline-block;
			color: red;
		 }
		 .ico-times::before { content: "\2716"; }';
print '</style>';

