<?php
/* Copyright (C) 2010      Regis Houssin       <regis.houssin@inodbox.com>
 * Copyright (C) 2012-2015 Laurent Destailleur <eldy@users.sourceforge.net>
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
 *       \file       htdocs/projet/contact.php
 *       \ingroup    project
 *       \brief      List of all contacts of a project
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
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/stores/class/branch.class.php';
if (isModEnabled('categorie')) {
	require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
}
dol_include_once('/pricematrix/class/projectmetrices.class.php');
dol_include_once('/pricematrix/lib/pricematrix_projectmetrices.lib.php');
// Load translation files required by the page
$langs->loadLangs(array("pricematrix@pricematrix", "other"));
// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$lineid   = GETPOST('lineid', 'int');

$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : str_replace('_', '', basename(dirname(__FILE__)).basename(__FILE__, '.php')); // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$dol_openinpopup = GETPOST('dol_openinpopup', 'aZ09');
$object = new Project($db);
$branch = new Branch($db);

include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once
if (!empty($conf->global->PROJECT_ALLOW_COMMENT_ON_PROJECT) && method_exists($object, 'fetchComments') && empty($object->comments)) {
	$object->fetchComments();
}
$title = $langs->trans('Projectpricematrix').' - '.$object->ref.' '.$object->name;
if (!empty($conf->global->MAIN_HTML_TITLE) && preg_match('/projectnameonly/', $conf->global->MAIN_HTML_TITLE) && $object->name) {
	$title = $object->ref.' '.$object->name.' - '.$langs->trans('ProjectContact');
}

$help_url = 'EN:Module_Projects|FR:Module_Projets|ES:M&oacute;dulo_Proyectos|DE:Modul_Projekte';

llxHeader('', $title, $help_url);
print '
	<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css" />
';
if ($id > 0 || !empty($ref)) {

	$head = project_prepare_head($object);
	print dol_get_fiche_head($head, 'tabname1', $langs->trans("Project"), -1, ($object->public ? 'projectpub' : 'project'));
    
	// Project card

	$linkback = '<a href="'.DOL_URL_ROOT.'/projet/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';
	
	$morehtmlref = '<div class="refidno">';
	// Title
	$morehtmlref .= $object->title;
	// Thirdparty
	if (!empty($object->thirdparty->id) && $object->thirdparty->id > 0) {
		$morehtmlref .= '<br>'.$object->thirdparty->getNomUrl(1, 'project');
	}
	$morehtmlref .= '</div>';

	// Define a complementary filter for search of next/prev ref.
	if (empty($user->rights->projet->all->lire)) {
		$objectsListId = $object->getProjectsAuthorizedForUser($user, 0, 0);
		$object->next_prev_filter = " rowid IN (".$db->sanitize(count($objectsListId) ?join(',', array_keys($objectsListId)) : '0').")";
	}
	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);
    print '<hr>';
	print '</div>';

	print '<script>';
	print '
			$(document).ready(function() {
				var paragraphs = document.querySelectorAll(".filter");
				paragraphs[0].style.backgroundColor = "gray";
			});
			function toggleTable(tableId, element) {
				// Show the selected table
				var rowToShow = document.getElementById(tableId);
				if (rowToShow) {
					if (rowToShow.style.display === "flex" && element.style.backgroundColor === "gray") {
						
						rowToShow.style.display = "none"; 
						element.style.backgroundColor = ""; 
					} else {
						rowToShow.style.display = "flex";
						element.style.backgroundColor = "gray";
					}
				}
			}
			window.onload = function() {
				var cells = document.querySelectorAll("td");
				cells.forEach(function(cell) {
					cell.addEventListener("input", function() {
						var content = this.innerText;
						if (content.includes("%")) {
							var value = parseFloat(content);
							if (value > 100) {
								this.innerText = "100,00%";
							}
						}
					});
				});
			};			
			';
	print '</script>';


	print '<div class="page-body">';
	
		print '<div class="row" style="display:flex; justify-content: center;" id="first">';
			
			print '<table id="first-table" class="col-8">';
				print '
						<tr>
							<th colspan="8" class="center">Basisdaten Grundlage zur Berechnungen</th>
						</tr>
						<tr>
							<th>Einsatzzeiten</th>
							<th></th>
							<th colspan="2" class="center">Timezone 1 <input type="checkbox"></th>
							<th colspan="2" class="center">Timezone 2 <input type="checkbox"></th>
							<th colspan="2" class="center">Timezone 3 <input type="checkbox"></th>
						</tr>
						<tr>
							<td>von bis Zeiten</td>
							<td></td>
							<td class="center" id="first_shift_first" contenteditable="true" oninput="handleChange(this.id, this.className)">08:00</td>
							<td class="center" id="first_shift_last" contenteditable="true" oninput="handleChange(this.id, this.className)">17:00</td>
							<td class="center" id="second_shift_first" contenteditable="true">17:00</td>
							<td class="center" id="second_shift_last" contenteditable="true" oninput="handleChange(this.id, this.className)">22:00</td>
							<td class="center" contenteditable="true" id="third_shift_first">22:00</td>
							<td class="center" contenteditable="true" id="third_shift_last">08:00</td>
						</tr>
						<tr>
							<td>Mo - Fr</td>
							<td class="center">+</td>
							<td colspan="2"></td>
							<td colspan="2" class="center">50%</td>
							<td colspan="2" class="center">75%</td>
						</tr>
						<tr>
							<td>Sa</td>
							<td class="center">+</td>
							<td colspan="2" class="center">50%</td>
							<td colspan="2" class="center">75%</td>
							<td colspan="2" class="center">100%</td>
						</tr>
						<tr>
							<td>Son / Feiertag</td>
							<td class="center">+</td>
							<td colspan="2" class="center">100%</td>
							<td colspan="2" class="center">150%</td>
							<td colspan="2" class="center">200%</td>
						</tr>
						';
			print '</table>';
			print '<div class="col-2">';
			print '</div>';

			print '<table id="second-table" class="col-8" style="margin-top:0">';
				print '
						<tr>
							<th colspan="2">Anfahrt</th>
							<th colspan="1" id="prokmzone" contenteditable="true" oninput="handleChange(this.id, this.className)">KM Zone1 <input type="checkbox"></th>
							<th colspan="1" id="prokmzone1" contenteditable="true" oninput="handleChange(this.id, this.className)">KM Zone2 <input type="checkbox"></th>
							<th colspan="1" id="prokmzone2" contenteditable="true" oninput="handleChange(this.id, this.className)">KM Zone3 <input type="checkbox"></th>
							<th colspan="1">KM über <input type="checkbox"></th>
							<th colspan="1" id="prokm" contenteditable="true" oninput="handleChange(this.id, this.className)">pro KM <input type="checkbox"></th>
							<th colspan="1" id="propauschal" contenteditable="true" oninput="handleChange(this.id, this.className)">Pauschal <input type="checkbox"></th>
						</tr>
						<tr>
							<th colspan="1">Zonen</th>
							<th colspan="1">KM Zonen</th>
							<th colspan="1" class="center">0 bis 80</th>
							<th colspan="1" class="center">80 bis 150</th>
							<th colspan="1" class="center">150 bis 200</th>
							<th colspan="1" class="center"> > 200 </th>
							<th colspan="1" class="center" id="prozone3" contenteditable="true" oninput="handleChange(this.id, this.className)"> >Zone 3 <input type="checkbox"></th>
							<th colspan="1" id="proanfahrt" contenteditable="true" oninput="handleChange(this.id, this.className)">Anfahrt Pauschal</th>
						</tr>
						<tr>
							<td colspan="1"></td>
							<td colspan="1">Tarif</td>
							<td class="center" id="pauschal_1" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td class="center" id="pauschal_2" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td class="center" id="pauschal_3" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td class="center" id="pauschal_4" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td class="center" id="pauschal_5" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td class="center" id="pauschal_6" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
						</tr>
						';
			print '</table>';
			print '<div class="col-2">';
			print '
			<i class="fa fa-arrow-right" style="align-self:center;" onclick="showPopup(\'popup1\')"></i>';
			print '<div id="popup1" class="popup">
						<span class="popup-close" onclick="closePopup(\'popup1\')">&times;</span>';
					print '<table>';
						print '
								<tr>
									<th colspan="1" class="center">SESOCO</th>
									<th colspan="1" class="center">Anfahrt</th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center">0.00</th>
									<th colspan="1" class="center">0.00</th>
									<th colspan="1" class="center">0.00</th>
									<th colspan="1" class="center">0.00</th>
									<th colspan="1" class="center">0.00</th>
									<th colspan="1" class="center">0.00</th>
								</tr>
								<tr>
									<td colspan="1" class="center">service partner</td>
									<td colspan="1" class="center" id="pauschal_service" contenteditable="true" oninput="handleChange(this.id, this.className)">50%</td>
									<td colspan="1" class="center"></td>
									<td colspan="1" class="center"></td>
									<td colspan="1" id="pauschal_service_1" class="center">0.00</td>
									<td colspan="1" id="pauschal_service_2" class="center">0.00</td>
									<td colspan="1" id="pauschal_service_3" class="center">0.00</td>
									<td colspan="1" id="pauschal_service_4" class="center">0.00</td>
									<td colspan="1" id="pauschal_service_5" class="center">0.00</td>
									<td colspan="1" id="pauschal_service_6" class="center">0.00</td>
								</tr>
								';
						print '</table>';
			print '</div>';
			print '</div>';

			print '<table id="second-table" class="col-8" style="margin-top:0">';
				print '
						<tr>
							<th colspan="2">Extras</th>
							<th colspan="1">Übernachtung <input type="checkbox"></th>
							<th colspan="1">Material <input type="checkbox"></th>
							<th colspan="1">? <input type="checkbox"></th>
							<th colspan="1">? <input type="checkbox"></th>
							<th colspan="1">? <input type="checkbox"></th>
							<th colspan="1">? <input type="checkbox"></th>
						</tr>
						<tr>
							<td colspan="2"></td>
							<td>0.00 €</td>
							<td>0.00 €</td>
							<td>0.00 €</td>
							<td>0.00 €</td>
							<td>0.00 €</td>
							<td>0.00 €</td>
						</tr>
						';
			print '</table>';
			print '<div class="col-2"></div>';

			print '<table id="third-table" class="col-8">';
				print '
						<tr>
							<th colspan="8" class="center">Ticket / Callvarianten</th>
						</tr>
						<tr>
							<th colspan="2">AZ inkl. Radius bis Km</th>
							<th id="prokmzonea">KM Zone1 <input type="checkbox"></th>
							<th id="prokmzone1a">KM Zone2 <input type="checkbox"></th>
							<th id="prokmzone2a">KM Zone3 <input type="checkbox"></th>
							<th> > KM Zone3 <input type="checkbox"></th>
							<th id="prokma">pro Km <input type="checkbox"></th>
							<th id="propauschala">Pauschal <input type="checkbox"></th>
						</tr>
						<tr>
							<td colspan="1" class="right">Minuten</th>
							<td colspan="1">90</th>
							<td id="pauschalaz_1" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td id="pauschalaz_2" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td id="pauschalaz_3" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td id="pauschalaz_4" contenteditable="true" class="pauschalT_uber_200" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td id="pauschalaz_5" contenteditable="true" class="pauschalT_pro" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td id="pauschalaz_6" contenteditable="true" class="pauschalT_pauschal" oninput="handleChange(this.id, this.className)">0.00 €</td>
						</tr>
						';
			print '</table>';
			print '<div class="col-2">';
			print '
			<i class="fa fa-arrow-right" style="align-self:center;" onclick="showPopup(\'popup2\')"></i>';
			print '<div id="popup2" class="popup">
						<span class="popup-close" onclick="closePopup(\'popup2\')">&times;</span>';
					print '<table>';
						print '
								<tr>
									<th colspan="1" class="center">SESOCO</th>
									<th colspan="1" class="center">Extras</th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center">0.00</th>
									<th colspan="1" class="center">0.00</th>
									<th colspan="1" class="center">0.00</th>
									<th colspan="1" class="center">0.00</th>
									<th colspan="1" class="center">0.00</th>
									<th colspan="1" class="center">0.00</th>
								</tr>
								<tr>
									<td colspan="1" class="center">service partner</td>
									<td id="pauschalaz_service" contenteditable="true" colspan="1" class="center" oninput="handleChange(this.id, this.className)">50%</td>
									<td colspan="1" class="center"></td>
									<td colspan="1" class="center"></td>
									<td id="pauschalaz_service_1" colspan="1" class="center">0.00</td>
									<td id="pauschalaz_service_2" colspan="1" class="center">0.00</td>
									<td id="pauschalaz_service_3" colspan="1" class="center">0.00</td>
									<td id="pauschalaz_service_4" colspan="1" class="center">0.00</td>
									<td id="pauschalaz_service_5" colspan="1" class="center">0.00</td>
									<td id="pauschalaz_service_6" colspan="1" class="center">0.00</td>
								</tr>
								';
						print '</table>';
			print '</div>';
			print '</div>';

			print '<table id="fourth-table" class="col-8">';
				print '
						<tr>
							<th colspan="2">AZ und gefahrene Km</th>
							<th>Tarif 1 <input type="checkbox"></th>
							<th>Tarif 2 <input type="checkbox"></th>
							<th>Tarif 3 <input type="checkbox"></th>
							<th>Tarif 4 <input type="checkbox"></th>
							<th id="prokma">Pro KM <input type="checkbox"></th>
							<th id="propauschala">Pauschal <input type="checkbox"></th>
						</tr>
						<tr>
							<td colspan="2">Anfahrt</td>
							<td id="pauschalgefahrene_1" class="pauschalT_0" contenteditable="true" oninput="handleChange(this.className, this.id)">0.00 €</td>
							<td id="pauschalgefahrene_2" class="pauschalT_80" contenteditable="true" oninput="handleChange(this.className, this.id)">0.00 €</td>
							<td id="pauschalgefahrene_3" class="pauschalT_150" contenteditable="true" oninput="handleChange(this.className, this.id)">0.00 €</td>
							<td id="pauschalgefahrene_4" class="pauschalT_uber_200" contenteditable="true" oninput="handleChange(this.className, this.id)">0.00 €</td>
							<td id="pauschalgefahrene_5" class="pauschalT_pro" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td id="pauschalgefahrene_6" class="pauschalT_pauschal" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
						</tr>
						';
			print '</table>';
			print '<div class="col-2">';
			print '
			<i class="fa fa-arrow-right" style="align-self:center;" onclick="showPopup(\'popup3\')"></i>';
			print '<div id="popup3" class="popup">
						<span class="popup-close" onclick="closePopup(\'popup3\')">&times;</span>';
					print '<table>';
						print '
								<tr>
									<th colspan="1" class="center">SESOCO</th>
									<th colspan="1" class="center">Anfahrt</th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
								</tr>
								<tr>
									<td colspan="1" class="center">service partner</td>
									<td id="pauschalgefahrene_service" colspan="1" class="center" oninput="handleChange(this.id, this.className)" contenteditable="true">50%</td>
									<td colspan="1" class="center"></td>
									<td colspan="1" class="center"></td>
									<td id="pauschalgefahrene_service_1" colspan="1" class="center">0.00</td>
									<td id="pauschalgefahrene_service_2" colspan="1" class="center">0.00</td>
									<td id="pauschalgefahrene_service_3" colspan="1" class="center">0.00</td>
									<td id="pauschalgefahrene_service_4" colspan="1" class="center">0.00</td>
									<td id="pauschalgefahrene_service_5" colspan="1" class="center">0.00</td>
									<td id="pauschalgefahrene_service_6" colspan="1" class="center">0.00</td>
								</tr>
								';
						print '</table>';
			print '</div>';
			print '</div>';

			print '<table id="fifth-table" class="col-8">';
				print '
						<tr>
							<th colspan="1">Anfahrt + Inkl Min</th>
							<th colspan="1">30</th>
							<th colspan="1" class="center">kombi <input type="checkbox"></th>
							<th colspan="1" class="center">kombi <input type="checkbox"></th>
							<th colspan="1" class="center">kombi <input type="checkbox"></th>
							<th colspan="1" class="center">kombi <input type="checkbox"></th>
							<th colspan="1" id="prozone3a"> >Zone 3 <input type="checkbox"></th>
							<th colspan="1" id="proanfahrta">Anfahrt Pauschal <input type="checkbox"></th>
						</tr>
						<tr>
							<td colspan="1">info (Kombination Arbeitszeit + Kilometer)</td>
							<td id="constnumber" contenteditable="true" oninput="handleChange(this.id, this.className)">45.00 €</td>
							<td id="pauschalT_const1" contenteditable="true" class="pauschanfahrt_1" oninput="handleChange(this.id, this.className)">45.00 €</td>
							<td id="pauschalT_const2" contenteditable="true" class="pauschanfahrt_2" oninput="handleChange(this.id, this.className)">45.00 €</td>
							<td id="pauschalT_const3" contenteditable="true" class="pauschanfahrt_3" oninput="handleChange(this.id, this.className)">45.00 €</td>
							<td class="pauschalT_uber_200" contenteditable="true" id="pauschanfahrt_4" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td class="pauschalT_pro" id="pauschanfahrt_5" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td class="pauschalT_pauschal" id="pauschanfahrt_6" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
						</tr>
						';
			print '</table>';
			print '<div class="col-2">';
			print '
			<i class="fa fa-arrow-right" style="align-self:center;" onclick="showPopup(\'popup4\')"></i>';
			print '<div id="popup4" class="popup">
						<span class="popup-close" onclick="closePopup(\'popup4\')">&times;</span>';
					print '<table>';
						print '
								<tr>
									<th colspan="1" class="center">SESOCO</th>
									<th colspan="1" class="center">Anfahrt + Inkl  Min</th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center">0.00</th>
									<th colspan="1" class="center">0.00</th>
									<th colspan="1" class="center">0.00</th>
									<th colspan="1" class="center">0.00</th>
									<th colspan="1" class="center">0.00</th>
									<th colspan="1" class="center">0.00</th>
								</tr>
								<tr>
									<td colspan="1" class="center">service partner</td>
									<td colspan="1" class="center" contenteditable="true" id="pauschanfahrt_service" oninput="handleChange(this.id, this.className)">50%</td>
									<td colspan="1" class="center"></td>
									<td colspan="1" class="center"></td>
									<td colspan="1" class="center" id="pauschanfahrt_service_1">0.00</td>
									<td colspan="1" class="center" id="pauschanfahrt_service_2">0.00</td>
									<td colspan="1" class="center" id="pauschanfahrt_service_3">0.00</td>
									<td colspan="1" class="center" id="pauschanfahrt_service_4">0.00</td>
									<td colspan="1" class="center" id="pauschanfahrt_service_5">0.00</td>
									<td colspan="1" class="center" id="pauschanfahrt_service_6">0.00</td>
								</tr>
								';
						print '</table>';
			print '</div>';
			print '</div>';

			print '<table id="sixth-table" class="col-8">';
				print '
						<tr>
							<th colspan="1">Stundenberechnung</th>
							<th colspan="1">Einsatzzeiten</th>
							<th colspan="1">min</th>
							<th colspan="1">Timezone 1 <input type="checkbox"></th>
							<th colspan="1">Timezone 2 <input type="checkbox"></th>
							<th colspan="1">Timezone 3 <input type="checkbox"></th>
							<th colspan="1">Anfahrt <input type="checkbox"></th>
							<th colspan="1">Extras <input type="checkbox"></th>
						</tr>
						<tr>
							<td colspan="1"></td>
							<td></td>
							<td>60</td>
							<td id="pauschalstundenberechnung_1" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td id="pauschalstundenberechnung_2" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td id="pauschalstundenberechnung_3" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td>30.00 €</td>
							<td>55.00 €</td>
						</tr>
						';
			print '</table>';
			print '<div class="col-2">';
			print '
			<i class="fa fa-arrow-right" style="align-self:center;" onclick="showPopup(\'popup5\')"></i>';
			print '<div id="popup5" class="popup">
						<span class="popup-close" onclick="closePopup(\'popup5\')">&times;</span>';
					print '<table>';
						print '
								<tr>
									<th colspan="1" class="center">SESOCO</th>
									<th colspan="1" class="center">Anfahrt</th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
								</tr>
								<tr>
									<td colspan="1" class="center">service partner</td>
									<td colspan="1" class="center" id="pauschalstundenberechnung_service" contenteditable="true" oninput="handleChange(this.id, this.className)">50%</td>
									<td colspan="1" class="center"></td>
									<td colspan="1" class="center" id="pauschalstundenberechnung_service_1">0.00</td>
									<td colspan="1" class="center" id="pauschalstundenberechnung_service_2">0.00</td>
									<td colspan="1" class="center" id="pauschalstundenberechnung_service_3">0.00</td>
									<td colspan="1" class="center"></td>
									<td colspan="1" class="center"></td>
									<td colspan="1" class="center"></td>
									<td colspan="1" class="center"></td>
								</tr>
								';
						print '</table>';
			print '</div>';
			print '</div>';

			print '<table id="seventh-table" class="col-8">';
				print '
						<tr>
							<td colspan="1">im 15 min Takt</td>
							<td id="pauschaltakt_1" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00</td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
						';
			print '</table>';
			print '<div class="col-2">';
			print '
			<i class="fa fa-arrow-right" style="align-self:center;" onclick="showPopup(\'popup6\')"></i>';
			print '<div id="popup6" class="popup">
						<span class="popup-close" onclick="closePopup(\'popup6\')">&times;</span>';			
					print '<table>';
						print '
								<tr>
									<td colspan="1" class="center">service partner</td>
									<td colspan="1" class="center" id="pauschaltakt_service" contenteditable="true" oninput="handleChange(this.id, this.className)">50%</td>
									<td colspan="1" class="center"></td>
									<td colspan="1" class="center"></td>
									<td colspan="1" class="center" id="pauschaltakt_service_1">0.00</td>
									<td colspan="1" class="center"></td>
									<td colspan="1" class="center"></td>
									<td colspan="1" class="center"></td>
									<td colspan="1" class="center"></td>
									<td colspan="1" class="center"></td>
								</tr>
								';
						print '</table>';
			print '</div>';
			print '</div>';

			print '<table id="eighth-table" class="col-8">';
				print '
						<tr>
							<th colspan="1">Installations Pauschle</th>
							<th colspan="1"> <input type="checkbox"></th>
							<th colspan="1">Extras <input type="checkbox"></th>
							<th colspan="1" id="prokmzonea">KM Zone1 <input type="checkbox"></th>
							<th colspan="1" id="prokmzone1a">KM Zone2 <input type="checkbox"></th>
							<th colspan="1" id="prokmzone2a">KM Zone3 <input type="checkbox"></th>
							<th colspan="1"> > Zone3 <input type="checkbox"></th>
							<th colspan="1">Anfahrt Pauschal <input type="checkbox"></th>
						</tr>
						<tr>
							<td colspan="1"></td>
							<td id="pauschalinstallations_1" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td>0.00 €</td>
							<td class="pauschalT_0" id="pauschalinstallations_2" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td class="pauschalT_80" id="pauschalinstallations_3" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td class="pauschalT_150" id="pauschalinstallations_4" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td class="pauschalT_pro" id="pauschalinstallations_5" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td class="pauschalT_pauschal" id="pauschalinstallations_6" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
						</tr>
						';
			print '</table>';
			print '<div class="col-2">';
			print '
			<i class="fa fa-arrow-right" style="align-self:center;" onclick="showPopup(\'popup7\')"></i>';
			print '<div id="popup7" class="popup">
						<span class="popup-close" onclick="closePopup(\'popup7\')">&times;</span>';			
					print '<table>';
						print '
								<tr>
									<th colspan="1" class="center">SESOCO</th>
									<th colspan="1" class="center">Anfahrt</th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
								</tr>
								<tr>
									<td colspan="1" class="center">service partner</td>
									<td colspan="1" class="center" id="pauschalinstallations_service" contenteditable="true" oninput="handleChange(this.id, this.className)">50%</td>
									<td colspan="1" class="center"></td>
									<td colspan="1" class="center"></td>
									<td colspan="1" class="center" id="pauschalinstallations_service_1">0.00</td>
									<td colspan="1" class="center" id="pauschalinstallations_service_2">0.00</td>
									<td colspan="1" class="center" id="pauschalinstallations_service_3">0.00</td>
									<td colspan="1" class="center" id="pauschalinstallations_service_4">0.00</td>
									<td colspan="1" class="center" id="pauschalinstallations_service_5">0.00</td>
									<td colspan="1" class="center" id="pauschalinstallations_service_6">0.00</td>
								</tr>
								';
						print '</table>';
			print '</div>';
			print '</div>';

			print '<table id="eighth-table" class="col-8">';
				print '
						<tr>
							<th colspan="1">Tagespauschale</th>
							<th colspan="1">Inkl. Drive</th>
							<th colspan="1"> <input type="checkbox"></th>
							<th colspan="1"> <input type="checkbox"></th>
							<th colspan="1"> <input type="checkbox"></th>
							<th colspan="1"> <input type="checkbox"></th>
							<th colspan="1">Anfahrt <input type="checkbox"></th>
							<th colspan="1">Extras <input type="checkbox"></th>
						</tr>
						<tr>
							<td colspan="1"></td>
							<td colspan="1">JA <input type="checkbox"> / Nein <input type="checkbox"></td>
							<td id="pauschaltags_1" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td class="pauschalT_0" id="pauschaltags_2" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td class="pauschalT_80" id="pauschaltags_3" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td class="pauschalT_150" id="pauschaltags_4" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td class="pauschalT_pro" id="pauschaltags_5" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td class="pauschalT_pauschal" id="pauschaltags_6" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
						</tr>
						';
			print '</table>';
			print '<div class="col-2">';
			print '
			<i class="fa fa-arrow-right" style="align-self:center;" onclick="showPopup(\'popup8\')"></i>';
			print '<div id="popup8" class="popup">
						<span class="popup-close" onclick="closePopup(\'popup8\')">&times;</span>';
					print '<table>';
						print '
								<tr>
									<th colspan="1" class="center">SESOCO</th>
									<th colspan="1" class="center">Anfahrt</th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
								</tr>
								<tr>
									<td colspan="1" class="center">service partner</td>
									<td colspan="1" class="center" id="pauschaltags_service" contenteditable="true" oninput="handleChange(this.id, this.className)">50%</td>
									<td colspan="1" class="center"></td>
									<td colspan="1" class="center"></td>
									<td colspan="1" class="center" id="pauschaltags_service_1">0.00</td>
									<td colspan="1" class="center" id="pauschaltags_service_2">0.00</td>
									<td colspan="1" class="center" id="pauschaltags_service_3">0.00</td>
									<td colspan="1" class="center" id="pauschaltags_service_4">0.00</td>
									<td colspan="1" class="center" id="pauschaltags_service_5">0.00</td>
									<td colspan="1" class="center" id="pauschaltags_service_6">0.00</td>
								</tr>
								';
						print '</table>';
			print '</div>';
			print '</div>';

			print '<table id="eighth-table" class="col-8">';
				print '
						<tr>
							<th>Projekt pro Stunde</th>
							<th>Outside</th>
							<th>Standart <input type="checkbox"></th>
							<th> <input type="checkbox"></th>
							<th> <input type="checkbox"></th>
							<th> <input type="checkbox"></th>
							<th>Km Zone <input type="checkbox"></th>
							<th>Extras <input type="checkbox"></th>
						</tr>
						<tr>
							<td colspan="1">select bei Anfahrt TZ 1 <input type="checkbox">/TZ 2 <input type="checkbox">/TZ 3 <input type="checkbox"></td>
							<td></td>
							<td id="pauschalproject_1" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td id="pauschalproject_2" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td id="pauschalproject_3" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td id="pauschalproject_4" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td class="pauschalT_0" id="pauschalproject_5" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td class="pauschalT_pauschal" id="pauschalproject_6" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
						</tr>
						';
			print '</table>';
			print '<div class="col-2">';
			print '
			<i class="fa fa-arrow-right" style="align-self:center;" onclick="showPopup(\'popup9\')"></i>';
			print '<div id="popup9" class="popup">
						<span class="popup-close" onclick="closePopup(\'popup9\')">&times;</span>';
					print '<table>';
						print '
								<tr>
									<th colspan="1" class="center">SESOCO</th>
									<th colspan="1" class="center">Anfahrt</th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
								</tr>
								<tr>
									<td colspan="1" class="center">service partner</td>
									<td colspan="1" class="center" id="pauschalproject_service" contenteditable="true" oninput="handleChange(this.id, this.className)">50%</td>
									<td colspan="1" class="center"></td>
									<td colspan="1" class="center"></td>
									<td colspan="1" class="center" id="pauschalproject_service_1">0.00</td>
									<td colspan="1" class="center" id="pauschalproject_service_2">0.00</td>
									<td colspan="1" class="center" id="pauschalproject_service_3">0.00</td>
									<td colspan="1" class="center" id="pauschalproject_service_4">0.00</td>
									<td colspan="1" class="center" id="pauschalproject_service_5">0.00</td>
									<td colspan="1" class="center" id="pauschalproject_service_6">0.00</td>
								</tr>
								';
						print '</table>';
			print '</div>';
			print '</div>';

			print '<table id="ninth-table" class="col-8">';
				print '
						<tr>
							<th>Projekt pro Stückzahlen / Einheiten</th>
							<th>Outside</th>
							<th><50 <input type="checkbox"></th>
							<th>50 bis 150 <input type="checkbox"></th>
							<th>bis 250 <input type="checkbox"></th>
							<th>bis 500 <input type="checkbox"></th>
							<th>bis 750 <input type="checkbox"></th>
							<th>größer 1000 <input type="checkbox"></th>
						</tr>
						<tr>
							<td></td>
							<td></td>
							<td id="pauschalprojectpro_1" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td id="pauschalprojectpro_2" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td id="pauschalprojectpro_3" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td id="pauschalprojectpro_4" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td id="pauschalprojectpro_5" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
							<td id="pauschalprojectpro_6" contenteditable="true" oninput="handleChange(this.id, this.className)">0.00 €</td>
						</tr>
						';
			print '</table>';
			print '<div class="col-2">';
			print '
			<i class="fa fa-arrow-right" style="align-self:center;" onclick="showPopup(\'popup10\')"></i>';
			print '<div id="popup10" class="popup">
						<span class="popup-close" onclick="closePopup(\'popup10\')">&times;</span>';
					print '<table>';
						print '
								<tr>
									<th colspan="1" class="center">SESOCO</th>
									<th colspan="1" class="center">Anfahrt</th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
									<th colspan="1" class="center"></th>
								</tr>
								<tr>
									<td colspan="1" class="center">service partner</td>
									<td colspan="1" class="center" id="pauschalprojectpro_service" contenteditable="true" oninput="handleChange(this.id, this.className)">50%</td>
									<td colspan="1" class="center"></td>
									<td colspan="1" class="center"></td>
									<td colspan="1" class="center" id="pauschalprojectpro_service_1">0.00</td>
									<td colspan="1" class="center" id="pauschalprojectpro_service_2">0.00</td>
									<td colspan="1" class="center" id="pauschalprojectpro_service_3">0.00</td>
									<td colspan="1" class="center" id="pauschalprojectpro_service_4">0.00</td>
									<td colspan="1" class="center" id="pauschalprojectpro_service_5">0.00</td>
									<td colspan="1" class="center" id="pauschalprojectpro_service_6">0.00</td>
								</tr>
								';
						print '</table>';
			print '</div>';
			print '</div>';

			print '<table id="tenth-table" class="col-8">';
				print '
						<tr>
							<th colspan="8" class="center">Rollout und Projektvarianten</th>
						</tr>
						<tr>
							<th>Dispatch / Callcenter</th>
							<th>Inside</th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
						</tr>
						<tr>
							<td>Bereitstellung</td>
							<td>30 min</td>
							<td>< 100 <input type="checkbox"></td>
							<td>100 bis 150 <input type="checkbox"></td>
							<td>bis 250 <input type="checkbox"></td>
							<td>bis 500 <input type="checkbox"></td>
							<td>bis 750 <input type="checkbox"></td>
							<td>größer 1000 <input type="checkbox"></td>
						</tr>
						<tr>
							<td>0.00 €</td>
							<td>pro Ticket</td>
							<td>0.00 €</td>
							<td>0.00 €</td>
							<td>0.00 €</td>
							<td>0.00 €</td>
							<td>0.00 €</td>
							<td>0.00 €</td>
						</tr>
						';
			print '</table>';
			print '<div class="col-2"></div>';

			print '<table id="eleventh-table" class="col-8">';
				print '
						<tr>
							<th>Staging</th>
							<th>Inside</th>
							<th> <input type="checkbox"></th>
							<th> <input type="checkbox"></th>
							<th> <input type="checkbox"></th>
							<th> <input type="checkbox"></th>
							<th> <input type="checkbox"></th>
							<th> <input type="checkbox"></th>
						</tr>
						<tr>
							<td></td>
							<td>Pauschal</td>
							<td>0</td>
							<td>0 bis 150</td>
							<td>bis 250</td>
							<td>bis 500</td>
							<td>bis 750</td>
							<td>größer 1000</td>
						</tr>
						<tr>
							<td>pro Asset</td>
							<td>0.00 €</td>
							<td>0.00 €</td>
							<td>0.00 €</td>
							<td>0.00 €</td>
							<td>0.00 €</td>
							<td>0.00 €</td>
							<td>0.00 €</td>
						</tr>
						';
			print '</table>';
			print '<div class="col-2"></div>';

			print '<table id="twelveth-table" class="col-8">';
				print '
						<tr>
							<th>Refurbished</th>
							<th>Inside</th>
							<th> <input type="checkbox"></th>
							<th> <input type="checkbox"></th>
							<th> <input type="checkbox"></th>
							<th> <input type="checkbox"></th>
							<th> <input type="checkbox"></th>
							<th> <input type="checkbox"></th>
						</tr>
						<tr>
							<td></td>
							<td>Pauschal</td>
							<td>0</td>
							<td>pro Asset</td>
							<td>cde</td>
							<td>xyz</td>
							<td>yza</td>
							<td>abc</td>
						</tr>
						<tr>
							<td></td>
							<td>0.00 €</td>
							<td>0.00 €</td>
							<td>0.00 €</td>
							<td></td>
							<td>0.00 €</td>
							<td>0.00 €</td>
							<td>0.00 €</td>
						</tr>
						';
			print '</table>';
			print '<div class="col-2"></div>';

			print '<table id="thirteen-table" class="col-8">';
				print '
						<tr>
							<th>Entsorgung</th>
							<th>Inside</th>
							<th> <input type="checkbox"></th>
							<th> <input type="checkbox"></th>
							<th> <input type="checkbox"></th>
							<th> <input type="checkbox"></th>
							<th> <input type="checkbox"></th>
							<th> <input type="checkbox"></th>
						</tr>
						<tr>
							<td></td>
							<td>Pauschal</td>
							<td>0</td>
							<td>pro Asset</td>
							<td>cde</td>
							<td>pro kilo</td>
							<td>yza</td>
							<td>abc</td>
						</tr>
						<tr>
							<td></td>
							<td>0.00 €</td>
							<td>0.00 €</td>
							<td>0.00 €</td>
							<td></td>
							<td>0.00 €</td>
							<td>0.00 €</td>
							<td>0.00 €</td>
						</tr>
						';
			print '</table>';
			print '<div class="col-2"></div>';

			print '<table id="fourteen-table" class="col-8">';
				print '
						<tr>
							<th>Logistik pro</th>
							<th>Standort</th>
							<th> <input type="checkbox"></th>
							<th> <input type="checkbox"></th>
							<th> <input type="checkbox"></th>
							<th> <input type="checkbox"></th>
							<th> <input type="checkbox"></th>
							<th> <input type="checkbox"></th>
						</tr>
						<tr>
							<td></td>
							<td>Pauschal</td>
							<td>pro Paket (Standart)</td>
							<td>pro Paket</td>
							<td>pro kurier</td>
							<td>pro Kurier (Express)</td>
							<td>pro Palette (Spedition)</td>
							<td>Einlagerung Palette</td>
						</tr>
						<tr>
							<td></td>
							<td>0.00 €</td>
							<td>0.00 €</td>
							<td>0.00 €</td>
							<td></td>
							<td>0.00 €</td>
							<td>0.00 €</td>
							<td>0.00 €</td>
						</tr>
						';
			print '</table>';
			print '<div class="col-2"></div>';

			print '<table id="fifteen-table" class="col-8">';
				print '
						<tr>
							<th></th>
							<th></th>
							<th>ABC 1 <input type="checkbox"></th>
							<th>ABC 2 <input type="checkbox"></th>
							<th>ABC 3 <input type="checkbox"></th>
							<th>ABC 4 <input type="checkbox"></th>
							<th>ABC 5 <input type="checkbox"></th>
							<th>ABC 6 <input type="checkbox"></th>
						</tr>
						<tr>
							<td></td>
							<td>Pauschal</td>
							<td>ABC 1.1</td>
							<td>ABC 1.2</td>
							<td>ABC 1.3</td>
							<td>ABC 1.4</td>
							<td>ABC 1.5</td>
							<td>ABC 1.6</td>
						</tr>
						<tr>
							<td></td>
							<td>0.00 €</td>
							<td>0.00 €</td>
							<td>0.00 €</td>
							<td></td>
							<td>0.00 €</td>
							<td>0.00 €</td>
							<td>0.00 €</td>
						</tr>
						';
			print '</table>';
			print '<div class="col-2"></div>';

		print '<div class="row">';
			print '<div class="col-8">';
			print '</div>';
			print '<div class="col-2 right">';
				print '<br>';
				print '<button id="saveMatrix">Save</button>';
			print '</div>';
		print '</div>';
	print '</div>';
	print '<div class="popup-overlay" id="popup-overlay" onclick="closeAllPopups()"></div>';
}

print '<script>';
	print ' function preventNewLine(event) {
				if (event.keyCode === 13) {
					event.preventDefault();
				}
			}
			
			const cells = document.querySelectorAll(\'[contenteditable="true"]\');
			cells.forEach(cell => {
				cell.addEventListener("keydown", preventNewLine);
			});';
	print '
			document.getElementById("showTables").addEventListener("click", function() {
				document.getElementById("popup").style.display = "block";
				var checkboxes = document.getElementsByName("tables");
				checkboxes[0].checked = true;
			});

			document.getElementById("tableForm").addEventListener("submit", function(event) {
				event.preventDefault();
				var checkboxes = document.getElementsByName("tables");
				for (var i = 0; i < checkboxes.length; i++) {
					var tableId = checkboxes[i].value;
					var table = document.getElementById(tableId);
					if (checkboxes[i].checked) {
						table.style.display = "flex";
					} else {
						table.style.display = "none";
					}
				}
				document.getElementById("popup").style.display = "none";
			});';		
	print '
		function handleChange(cellId, cellC) {
			const celluber = document.getElementById("uber");
			const celluberx = document.getElementById("uberx");

			const pauschal_1 = document.getElementById("pauschal_1");
			const pauschal_2 = document.getElementById("pauschal_2");
			const pauschal_3 = document.getElementById("pauschal_3");
			const pauschal_4 = document.getElementById("pauschal_4");
			const pauschal_5 = document.getElementById("pauschal_5");
			const pauschal_6 = document.getElementById("pauschal_6");
			const pauschal_service_1 = document.getElementById("pauschal_service_1");
			const pauschal_service_2 = document.getElementById("pauschal_service_2");
			const pauschal_service_3 = document.getElementById("pauschal_service_3");
			const pauschal_service_4 = document.getElementById("pauschal_service_4");
			const pauschal_service_5 = document.getElementById("pauschal_service_5");
			const pauschal_service_6 = document.getElementById("pauschal_service_6");

			const pauschalaz_1 = document.getElementById("pauschalaz_1");
			const pauschalaz_2 = document.getElementById("pauschalaz_2");
			const pauschalaz_3 = document.getElementById("pauschalaz_3");
			const pauschalaz_4 = document.getElementById("pauschalaz_4");
			const pauschalaz_5 = document.getElementById("pauschalaz_5");
			const pauschalaz_6 = document.getElementById("pauschalaz_6");
			const pauschalaz_service_1 = document.getElementById("pauschalaz_service_1");
			const pauschalaz_service_2 = document.getElementById("pauschalaz_service_2");
			const pauschalaz_service_3 = document.getElementById("pauschalaz_service_3");
			const pauschalaz_service_4 = document.getElementById("pauschalaz_service_4");
			const pauschalaz_service_5 = document.getElementById("pauschalaz_service_5");
			const pauschalaz_service_6 = document.getElementById("pauschalaz_service_6");
			
			const pauschal_service = document.getElementById("pauschal_service");
			const constnumber = document.getElementById("constnumber");
			const pauschal_const1 = document.getElementById("pauschalT_const1");
			const pauschal_const2 = document.getElementById("pauschalT_const2");
			const pauschal_const3 = document.getElementById("pauschalT_const3");
			const pauschal_const4 = document.getElementsByClassName("pauschal_const4")[0];
			const const_pauschal1 = document.getElementById("pauschalgefahrene_1");
			
			const const_pauschal2 = document.getElementById("pauschalgefahrene_2");
			const const_pauschal3 = document.getElementById("pauschalgefahrene_3");
			const const_pauschal4 = document.getElementsByClassName("const_pauschal4")[0];
			
			
			if(cellId == "first_shift_first"){
				const first_shift = document.getElementById("first_shift_first");
				const second_shift = document.getElementById("third_shift_last");
				second_shift.innerText = first_shift.innerText;
			}
			
			if(cellId == "first_shift_last"){
				const first_shift = document.getElementById("first_shift_last");
				const second_shift = document.getElementById("second_shift_first");
				second_shift.innerText = first_shift.innerText;
			}

			if(cellId == "second_shift_last"){
				const first_shift = document.getElementById("second_shift_last");
				const second_shift = document.getElementById("third_shift_first");
				second_shift.innerText = first_shift.innerText;
			}
			
			if(cellId == "prokm"){
				var prokm = document.getElementById("prokm");
				var cells = document.querySelectorAll(\'[id^="prokma"]\');
				cells.forEach(function(cell) {
					cell.innerText = prokm.innerText;
				});
			}
			
			if(cellId == "propauschal"){
				var propauschal = document.getElementById("propauschal");
				var cells = document.querySelectorAll(\'[id^="propauschala"]\');
				cells.forEach(function(cell) {
					cell.innerText = propauschal.innerText;
				});
			}
			
			if(cellId == "prozone3"){
				var prozone3 = document.getElementById("prozone3");
				var cells = document.querySelectorAll(\'[id^="prozone3a"]\');
				cells.forEach(function(cell) {
					cell.innerText = prozone3.innerText;
				});
			}
			
			if(cellId == "proanfahrt"){
				var proanfahrt = document.getElementById("proanfahrt");
				var cells = document.querySelectorAll(\'[id^="proanfahrta"]\');
				cells.forEach(function(cell) {
					cell.innerText = proanfahrt.innerText;
				});
			}
			
			if(cellId == "prokmzone"){
				var prokmzone = document.getElementById("prokmzone");
				var cells = document.querySelectorAll(\'[id^="prokmzonea"]\');
				cells.forEach(function(cell) {
					cell.innerText = prokmzone.innerText;
				});
			}
			
			if(cellId == "prokmzone1"){
				var prokmzone1 = document.getElementById("prokmzone1");
				var cells = document.querySelectorAll(\'[id^="prokmzone1a"]\');
				cells.forEach(function(cell) {
					cell.innerText = prokmzone1.innerText;
				});
			}
			
			if(cellId == "prokmzone2"){
				var prokmzone2 = document.getElementById("prokmzone2");
				var cells = document.querySelectorAll(\'[id^="prokmzone2a"]\');
				cells.forEach(function(cell) {
					cell.innerText = prokmzone2.innerText;
				});
			}

			if(cellId == "constnumber"){
				
				const newValue = constnumber.innerText.split(" ");
				const percentValue1 = const_pauschal1.innerText.split(" ");
				const percentValue2 = const_pauschal2.innerText.split(" ");
				const percentValue3 = const_pauschal3.innerText.split(" ");
				pauschal_const1.innerText = parseFloat((parseFloat(percentValue1[0].replace(",", "."))) + (parseFloat(newValue[0].replace(",", ".")))).toFixed(2) + " €";
				pauschal_const2.innerText = parseFloat((parseFloat(percentValue2[0].replace(",", "."))) + (parseFloat(newValue[0].replace(",", ".")))).toFixed(2) + " €";
				pauschal_const3.innerText = parseFloat((parseFloat(percentValue3[0].replace(",", "."))) + (parseFloat(newValue[0].replace(",", ".")))).toFixed(2) + " €";
			}
			
			if(cellC == "pauschalgefahrene_1"){
				console.log(1);
				const newValue = constnumber.innerText.split(" ");
				const percentValue = const_pauschal1.innerText.split(" ");
				pauschal_const1.innerText = parseFloat((parseFloat(percentValue[0].replace(",", "."))) + (parseFloat(newValue[0].replace(",", ".")))).toFixed(2) + " €";
			}
			
			if(cellC == "pauschalgefahrene_2"){
				
				const newValue = constnumber.innerText.split(" ");
				const percentValue = const_pauschal2.innerText.split(" ");
				pauschal_const2.innerText = parseFloat((parseFloat(percentValue[0].replace(",", "."))) + (parseFloat(newValue[0].replace(",", ".")))).toFixed(2) + " €";
			}
			
			if(cellC == "pauschalgefahrene_3"){
				
				const newValue = constnumber.innerText.split(" ");
				const percentValue = const_pauschal3.innerText.split(" ");
				pauschal_const3.innerText = parseFloat((parseFloat(percentValue[0].replace(",", "."))) + (parseFloat(newValue[0].replace(",", ".")))).toFixed(2) + " €";
			}
			
			// if(cellId == "const_pauschal4"){
				
			// 	const percentValue = const_pauschal4.innerText.split(" ");
			// 	pauschal_const4.innerText = parseFloat((parseFloat(percentValue[0].replace(",", ".")))).toFixed(2) + " €";
			// }
			
			if(cellId == "pauschal_1"){
				
				const newValue = constnumber.innerText.split(" ");
				const percentValue = pauschal_1.innerText.split(" ");
				pauschal_const1.innerText = parseFloat((parseFloat(percentValue[0].replace(",", "."))) + (parseFloat(newValue[0].replace(",", ".")))).toFixed(2) + " €";
				var cells = document.querySelectorAll(\'[class^="pauschalT_0"]\');
				cells.forEach(function(cell) {
					cell.innerText = pauschal_1.innerText;
				});
			}
			
			if(cellId == "pauschal_2"){
				
				const newValue = constnumber.innerText.split(" ");
				const percentValue = pauschal_2.innerText.split(" ");
				pauschal_const2.innerText = parseFloat((parseFloat(percentValue[0].replace(",", "."))) + (parseFloat(newValue[0].replace(",", ".")))).toFixed(2) + " €";
				var cells = document.querySelectorAll(\'[class^="pauschalT_80"]\');
				cells.forEach(function(cell) {
					cell.innerText = pauschal_2.innerText;
				});
			}
			
			if(cellId == "pauschal_3"){
				
				const newValue = constnumber.innerText.split(" ");
				const percentValue = pauschal_3.innerText.split(" ");
				pauschal_const3.innerText = parseFloat((parseFloat(percentValue[0].replace(",", "."))) + (parseFloat(newValue[0].replace(",", ".")))).toFixed(2) + " €";
				var cells = document.querySelectorAll(\'[class^="pauschalT_150"]\');
				cells.forEach(function(cell) {
					cell.innerText = pauschal_3.innerText;
				});
			}
			
			if(cellId == "pauschal_4"){
				var cells = document.querySelectorAll(\'[class^="pauschalT_uber_200"]\');
				cells.forEach(function(cell) {
					cell.innerText = pauschal_4.innerText;
				});
			}
			
			if(cellId == "pauschal_5"){
				var cells = document.querySelectorAll(\'[class^="pauschalT_pro"]\');
				cells.forEach(function(cell) {
					cell.innerText = pauschal_5.innerText;
				});
			}
			
			if(cellId == "pauschal_6"){
				var cells = document.querySelectorAll(\'[class^="pauschalT_pauschal"]\');
				cells.forEach(function(cell) {
					cell.innerText = pauschal_6.innerText;
				});
			}

			if (cellId.includes("pauschal_") && !cellId.includes("pauschal_service")) {
				const index = cellId.split("_")[1];
				const percent = "pauschal_service";
				const modifiedIndex = "pauschal_service_" + (index);
				const cell = document.getElementById(percent);
				const cell1 = document.getElementById(cellId);
				const cell2 = document.getElementById(modifiedIndex);
				const percentValue = cell.innerText.split("%");
				const newValue = cell1.innerText.split(" ");
				cell2.innerText = parseFloat((parseFloat(percentValue[0].replace(",", "."))/100) * (parseFloat(newValue[0].replace(",", ".")))).toFixed(2) + " €";
			}

			if (cellId === "pauschal_service") {
				const cell = document.getElementById(cellId);
				const newValue = cell.innerText.split("%");
				pauschal_service_1.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschal_1.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				
				if(pauschal_service_2.innerText){
					pauschal_service_2.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschal_2.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschal_service_3.innerText){
					pauschal_service_3.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschal_3.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschal_service_4.innerText){
					pauschal_service_4.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschal_4.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschal_service_5.innerText){
					pauschal_service_5.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschal_5.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschal_service_6.innerText){
					pauschal_service_6.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschal_6.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
			}

			if (cellId.includes("pauschalaz_") && !cellId.includes("pauschalaz_service")) {
				const index = cellId.split("_")[1];
				const percent = "pauschalaz_service";
				const modifiedIndex = "pauschalaz_service_" + (index);
				const cell = document.getElementById(percent);
				const cell1 = document.getElementById(cellId);
				const cell2 = document.getElementById(modifiedIndex);
				const percentValue = cell.innerText.split("%");
				const newValue = cell1.innerText.split(" ");
				cell2.innerText = parseFloat((parseFloat(percentValue[0].replace(",", "."))/100) * (parseFloat(newValue[0].replace(",", ".")))).toFixed(2) + " €";
			}

			if (cellId === "pauschalaz_service") {
				const cell = document.getElementById(cellId);
				const newValue = cell.innerText.split("%");
				pauschalaz_service_1.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalaz_1.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				
				if(pauschalaz_service_2.innerText){
					pauschalaz_service_2.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalaz_2.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschalaz_service_3.innerText){
					pauschalaz_service_3.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalaz_3.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschalaz_service_4.innerText){
					pauschalaz_service_4.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalaz_4.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschalaz_service_5.innerText){
					pauschalaz_service_5.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalaz_5.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschalaz_service_6.innerText){
					pauschalaz_service_6.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalaz_6.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
			}

			if (cellId.includes("pauschalgefahrene_") && !cellId.includes("pauschalgefahrene_service")) {
				const index = cellId.split("_")[1];
				const percent = "pauschalgefahrene_service";
				const modifiedIndex = "pauschalgefahrene_service_" + (index);
				const cell = document.getElementById(percent);
				const cell1 = document.getElementById(cellId);
				const cell2 = document.getElementById(modifiedIndex);
				const percentValue = cell.innerText.split("%");
				const newValue = cell1.innerText.split(" ");
				cell2.innerText = parseFloat((parseFloat(percentValue[0].replace(",", "."))/100) * (parseFloat(newValue[0].replace(",", ".")))).toFixed(2) + " €";
			}

			if (cellC.includes("pauschalgefahrene_") && !cellC.includes("pauschalgefahrene_service")) {
				const index = cellC.split("_")[1];
				const percent = "pauschalgefahrene_service";
				const modifiedIndex = "pauschalgefahrene_service_" + (index);
				const cell = document.getElementById(percent);
				const cell1 = document.getElementById(cellC);
				const cell2 = document.getElementById(modifiedIndex);
				const percentValue = cell.innerText.split("%");
				const newValue = cell1.innerText.split(" ");
				cell2.innerText = parseFloat((parseFloat(percentValue[0].replace(",", "."))/100) * (parseFloat(newValue[0].replace(",", ".")))).toFixed(2) + " €";
			}

			if (cellId === "pauschalgefahrene_service") {
				const cell = document.getElementById(cellId);
				const newValue = cell.innerText.split("%");
				pauschalgefahrene_service_1.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalgefahrene_1.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				
				if(pauschalgefahrene_service_2.innerText){
					pauschalgefahrene_service_2.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalgefahrene_2.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschalgefahrene_service_3.innerText){
					pauschalgefahrene_service_3.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalgefahrene_3.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschalgefahrene_service_4.innerText){
					pauschalgefahrene_service_4.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalgefahrene_4.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschalgefahrene_service_5.innerText){
					pauschalgefahrene_service_5.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalgefahrene_5.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschalgefahrene_service_6.innerText){
					pauschalgefahrene_service_6.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalgefahrene_6.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
			}

			if (cellId.includes("pauschalstundenberechnung_") && !cellId.includes("pauschalstundenberechnung_service")) {
				const index = cellId.split("_")[1];
				const percent = "pauschalstundenberechnung_service";
				const modifiedIndex = "pauschalstundenberechnung_service_" + (index);
				const cell = document.getElementById(percent);
				const cell1 = document.getElementById(cellId);
				const cell2 = document.getElementById(modifiedIndex);
				const percentValue = cell.innerText.split("%");
				const newValue = cell1.innerText.split(" ");
				cell2.innerText = parseFloat((parseFloat(percentValue[0].replace(",", "."))/100) * (parseFloat(newValue[0].replace(",", ".")))).toFixed(2) + " €";
			}

			if (cellId === "pauschalstundenberechnung_service") {
				const cell = document.getElementById(cellId);
				const newValue = cell.innerText.split("%");
				pauschalstundenberechnung_service_1.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalstundenberechnung_1.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				
				if(pauschalstundenberechnung_service_2.innerText){
					pauschalstundenberechnung_service_2.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalstundenberechnung_2.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschalstundenberechnung_service_3.innerText){
					pauschalstundenberechnung_service_3.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalstundenberechnung_3.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
			}
			

			if (cellId.includes("pauschaltakt_") && !cellId.includes("pauschaltakt_service")) {
				const index = cellId.split("_")[1];
				const percent = "pauschaltakt_service";
				const modifiedIndex = "pauschaltakt_service_" + (index);
				const cell = document.getElementById(percent);
				const cell1 = document.getElementById(cellId);
				const cell2 = document.getElementById(modifiedIndex);
				const percentValue = cell.innerText.split("%");
				const newValue = cell1.innerText.split(" ");
				cell2.innerText = parseFloat((parseFloat(percentValue[0].replace(",", "."))/100) * (parseFloat(newValue[0].replace(",", ".")))).toFixed(2) + " €";
			}

			if (cellId === "pauschaltakt_service") {
				const cell = document.getElementById(cellId);
				const newValue = cell.innerText.split("%");
				pauschaltakt_service_1.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschaltakt_1.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
			}
			
			if (cellId.includes("pauschalinstallations_") && !cellId.includes("pauschalinstallations_service")) {
				const index = cellId.split("_")[1];
				const percent = "pauschalinstallations_service";
				const modifiedIndex = "pauschalinstallations_service_" + (index);
				const cell = document.getElementById(percent);
				const cell1 = document.getElementById(cellId);
				const cell2 = document.getElementById(modifiedIndex);
				const percentValue = cell.innerText.split("%");
				const newValue = cell1.innerText.split(" ");
				cell2.innerText = parseFloat((parseFloat(percentValue[0].replace(",", "."))/100) * (parseFloat(newValue[0].replace(",", ".")))).toFixed(2) + " €";
			}

			if (cellId === "pauschalinstallations_service") {
				const cell = document.getElementById(cellId);
				const newValue = cell.innerText.split("%");
				pauschalinstallations_service_1.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalinstallations_1.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				
				if(pauschalinstallations_service_2.innerText){
					pauschalinstallations_service_2.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalinstallations_2.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschalinstallations_service_3.innerText){
					pauschalinstallations_service_3.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalinstallations_3.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschalinstallations_service_4.innerText){
					pauschalinstallations_service_4.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalinstallations_4.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschalinstallations_service_5.innerText){
					pauschalinstallations_service_5.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalinstallations_5.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschalinstallations_service_6.innerText){
					pauschalinstallations_service_6.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalinstallations_6.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
			}
			
			if (cellId.includes("pauschaltags_") && !cellId.includes("pauschaltags_service")) {
				const index = cellId.split("_")[1];
				const percent = "pauschaltags_service";
				const modifiedIndex = "pauschaltags_service_" + (index);
				const cell = document.getElementById(percent);
				const cell1 = document.getElementById(cellId);
				const cell2 = document.getElementById(modifiedIndex);
				const percentValue = cell.innerText.split("%");
				const newValue = cell1.innerText.split(" ");
				cell2.innerText = parseFloat((parseFloat(percentValue[0].replace(",", "."))/100) * (parseFloat(newValue[0].replace(",", ".")))).toFixed(2) + " €";
			}

			if (cellId === "pauschaltags_service") {
				const cell = document.getElementById(cellId);
				const newValue = cell.innerText.split("%");
				pauschaltags_service_1.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschaltags_1.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				
				if(pauschaltags_service_2.innerText){
					pauschaltags_service_2.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschaltags_2.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschaltags_service_3.innerText){
					pauschaltags_service_3.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschaltags_3.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschaltags_service_4.innerText){
					pauschaltags_service_4.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschaltags_4.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschaltags_service_5.innerText){
					pauschaltags_service_5.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschaltags_5.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschaltags_service_6.innerText){
					pauschaltags_service_6.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschaltags_6.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
			}
			
			if (cellId.includes("pauschalproject_") && !cellId.includes("pauschalproject_service")) {
				const index = cellId.split("_")[1];
				const percent = "pauschalproject_service";
				const modifiedIndex = "pauschalproject_service_" + (index);
				const cell = document.getElementById(percent);
				const cell1 = document.getElementById(cellId);
				const cell2 = document.getElementById(modifiedIndex);
				const percentValue = cell.innerText.split("%");
				const newValue = cell1.innerText.split(" ");
				cell2.innerText = parseFloat((parseFloat(percentValue[0].replace(",", "."))/100) * (parseFloat(newValue[0].replace(",", ".")))).toFixed(2) + " €";
			}

			if (cellId === "pauschalproject_service") {
				const cell = document.getElementById(cellId);
				const newValue = cell.innerText.split("%");
				pauschalproject_service_1.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalproject_1.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				
				if(pauschalproject_service_2.innerText){
					pauschalproject_service_2.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalproject_2.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschalproject_service_3.innerText){
					pauschalproject_service_3.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalproject_3.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschalproject_service_4.innerText){
					pauschalproject_service_4.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalproject_4.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschalproject_service_5.innerText){
					pauschalproject_service_5.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalproject_5.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschalproject_service_6.innerText){
					pauschalproject_service_6.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalproject_6.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
			}
			
			if (cellId.includes("pauschalprojectpro_") && !cellId.includes("pauschalprojectpro_service")) {
				const index = cellId.split("_")[1];
				const percent = "pauschalprojectpro_service";
				const modifiedIndex = "pauschalprojectpro_service_" + (index);
				const cell = document.getElementById(percent);
				const cell1 = document.getElementById(cellId);
				const cell2 = document.getElementById(modifiedIndex);
				const percentValue = cell.innerText.split("%");
				const newValue = cell1.innerText.split(" ");
				cell2.innerText = parseFloat((parseFloat(percentValue[0].replace(",", "."))/100) * (parseFloat(newValue[0].replace(",", ".")))).toFixed(2) + " €";
			}

			if (cellId === "pauschalprojectpro_service") {
				const cell = document.getElementById(cellId);
				const newValue = cell.innerText.split("%");
				pauschalprojectpro_service_1.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalprojectpro_1.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				
				if(pauschalprojectpro_service_2.innerText){
					pauschalprojectpro_service_2.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalprojectpro_2.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschalprojectpro_service_3.innerText){
					pauschalprojectpro_service_3.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalprojectpro_3.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschalprojectpro_service_4.innerText){
					pauschalprojectpro_service_4.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalprojectpro_4.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschalprojectpro_service_5.innerText){
					pauschalprojectpro_service_5.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalprojectpro_5.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschalprojectpro_service_6.innerText){
					pauschalprojectpro_service_6.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschalprojectpro_6.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
			}

			if (cellId.includes("pauschanfahrt_") && !cellId.includes("pauschanfahrt_service")) {
				const index = cellId.split("_")[1];
				const percent = "pauschanfahrt_service";
				const modifiedIndex = "pauschanfahrt_service_" + (index);
				const cell = document.getElementById(percent);
				const cell1 = document.getElementById(cellId);
				const cell2 = document.getElementById(modifiedIndex);
				const percentValue = cell.innerText.split("%");
				const newValue = cell1.innerText.split(" ");
				cell2.innerText = parseFloat((parseFloat(percentValue[0].replace(",", "."))/100) * (parseFloat(newValue[0].replace(",", ".")))).toFixed(2) + " €";
			}

			if (cellC.includes("pauschanfahrt_") && !cellC.includes("pauschanfahrt_service")) {
				const index = cellC.split("_")[1];
				const percent = "pauschanfahrt_service";
				const modifiedIndex = "pauschanfahrt_service_" + (index);
				const cell = document.getElementById(percent);
				const cell1 = document.getElementsByClassName(cellC)[0];
				const cell2 = document.getElementById(modifiedIndex);
				const percentValue = cell.innerText.split("%");
				const newValue = cell1.innerText.split(" ");
				cell2.innerText = parseFloat((parseFloat(percentValue[0].replace(",", "."))/100) * (parseFloat(newValue[0].replace(",", ".")))).toFixed(2) + " €";
			}

			const pauschanfahrt_1 = document.getElementsByClassName("pauschanfahrt_1")[0];
			const pauschanfahrt_2 = document.getElementsByClassName("pauschanfahrt_2")[0];
			const pauschanfahrt_3 = document.getElementsByClassName("pauschanfahrt_3")[0];

			if (cellId === "pauschanfahrt_service") {
				const cell = document.getElementById(cellId);
				const newValue = cell.innerText.split("%");
				pauschanfahrt_service_1.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschanfahrt_1.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				
				if(pauschanfahrt_service_2.innerText){
					pauschanfahrt_service_2.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschanfahrt_2.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschanfahrt_service_3.innerText){
					pauschanfahrt_service_3.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschanfahrt_3.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschanfahrt_service_4.innerText){
					pauschanfahrt_service_4.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschanfahrt_4.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschanfahrt_service_5.innerText){
					pauschanfahrt_service_5.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschanfahrt_5.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
				if(pauschanfahrt_service_6.innerText){
					pauschanfahrt_service_6.innerText = parseFloat((parseFloat(newValue[0].replace(",", "."))/100) * (parseFloat(pauschanfahrt_6.innerText.split(" ")[0].replace(",", ".")))).toFixed(2) + " €";
				}
			}


			if (cellId == "uber") {
				celluberx.innerText = "";
				celluberx.innerText = "über " + celluber.innerText;
			}
			
		}

        function showPopup(popupId) {
            document.getElementById(popupId).style.display = "block";
            document.getElementById("popup-overlay").style.display = "block";
        }

        function closePopup(popupId) {
            document.getElementById(popupId).style.display = "none";
            document.getElementById("popup-overlay").style.display = "none";
        }

        function closeAllPopups() {
            document.querySelectorAll(".popup").forEach(popup => popup.style.display = "none");
            document.getElementById("popup-overlay").style.display = "none";
        }
	';
	print '$(document).ready(function () {
			});';
print '</script>';
print ' <style>
			p.filter.active {
				background-color: lightblue; /* Change the color as needed */
			}
			table {
				font-family: Arial, sans-serif;
				border-collapse: collapse;
				width: 100%;
				margin-top: 10px;
			}

			td, th {
				border: 1px solid #dddddd;
				text-align: left;
			}

			th {
				background-color: #f2f2f2;
			}
			.row {
				--bs-gutter-x: 1.5rem;
				--bs-gutter-y: 0;
				display: flex;
				flex-wrap: wrap;
				margin-top: calc(-1* var(--bs-gutter-y));
				margin-right: calc(-.5* var(--bs-gutter-x));
				margin-left: calc(-.5* var(--bs-gutter-x));
			}
			.col-6 {
				flex: 0 0 auto;
				width: 50%;
			}
			.col-8 {
				flex: 0 0 auto;
				width: 80%;
			}
			.col-2 {
				display: flex;
				flex: 0 0 auto;
				width: 20%;
			}
			td, th {
				height: 5px;
				border: 1px solid #dddddd;
				text-align: left;
				padding: 3px;
				font-size: 0.7rem;
				width: 10%
			}
			.popup {
				display: none;
				position: fixed;
				top: 50%;
				left: 50%;
				transform: translate(-50%, -50%);
				background-color: #fff;
				padding: 20px;
				border: 1px solid #ccc;
				border-radius: 5px;
				z-index: 1000;
			}
			.col-1 {
				flex: 0 0 auto;
				width: 10%;
				cursor: pointer
			}
			.filter {
				border: 1px solid #ccc;
				padding: 10px;
				text-align: center;
				margin: 4px;
			}
			.tabBar {
				padding-bottom: 0 !important;
			}
			#first-table {
				margin-top: 0 !important;
			}
			.hidden-table {
				display: none;
			}
			.popup {
				display: none;
				position: fixed;
				top: 50%;
				left: 50%;
				transform: translate(-50%, -50%);
				background-color: white;
				padding: 20px;
				box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
				z-index: 1000;
			}
			.popup table {
				width: 100%;
			}
			.popup-overlay {
				display: none;
				position: fixed;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				background-color: rgba(0, 0, 0, 0.5);
				z-index: 999;
			}
			.popup-close {
				position: absolute;
				top: 10px;
				right: 10px;
				cursor: pointer;
			}
		</style>';






	// print '<div class="row">';
	// 	print '<div class="col-6">';
	// 	print '</div>';
	// 	print '<div class="col-6 right">';
	// 		print '<button id="showTables">Show Tables</button>';
	// 	print '</div>';
	// print '</div>';

	// print '<div id="popup" class="popup">
	// 			<h2>Select Tables</h2>
	// 			<form id="tableForm">
	// 				<input type="checkbox" id="first-table-checkbox" name="tables" value="first">
	// 				<label for="first-table-checkbox">First Table</label><br>
	// 				<input type="checkbox" id="second-table-checkbox" name="tables" value="second">
	// 				<label for="second-table-checkbox">Second Table</label><br>
	// 				<input type="checkbox" id="third-table-checkbox" name="tables" value="third">
	// 				<label for="third-table-checkbox">Third Table</label><br>
	// 				<input type="checkbox" id="fourth-table-checkbox" name="tables" value="fourth">
	// 				<label for="fourth-table-checkbox">Fourth Table</label><br>
	// 				<input type="checkbox" id="fifth-table-checkbox" name="tables" value="fifth">
	// 				<label for="fifth-table-checkbox">Fifth Table</label><br>
	// 				<input type="checkbox" id="sixth-table-checkbox" name="tables" value="sixth">
	// 				<label for="sixth-table-checkbox">Sixth Table</label><br>
	// 				<input type="checkbox" id="seventh-table-checkbox" name="tables" value="seventh">
	// 				<label for="seventh-table-checkbox">Seventh Table</label><br>
	// 				<input type="checkbox" id="eight-table-checkbox" name="tables" value="eight">
	// 				<label for="eight-table-checkbox">Eight Table</label><br>
	// 				<input type="checkbox" id="nine-table-checkbox" name="tables" value="nine">
	// 				<label for="nine-table-checkbox">Nine Table</label><br>
	// 				<input type="checkbox" id="ten-table-checkbox" name="tables" value="ten">
	// 				<label for="ten-table-checkbox">Ten Table</label><br>
	// 				<button type="submit">Show Selected Tables</button>
	// 			</form>
	// 		</div>';
	// print '<div class="row">';
	// 	print '<div class="col-1">';
	// 		print '<p class="filter" onclick="toggleTable(\'first\', this)">First Table</p>';
	// 	print '</div>';
	// 	print '<div class="col-1">';
	// 		print '<p class="filter" onclick="toggleTable(\'second\', this)">Second Table</p>';
	// 	print '</div>';
	// 	print '<div class="col-1">';
	// 		print '<p class="filter" onclick="toggleTable(\'third\', this)">Third Table</p>';
	// 	print '</div>';
	// 	print '<div class="col-1">';
	// 		print '<p class="filter" onclick="toggleTable(\'fourth\', this)">Fourth Table</p>';
	// 	print '</div>';
	// 	print '<div class="col-1">';
	// 		print '<p class="filter" onclick="toggleTable(\'fifth\', this)">Fifth Table</p>';
	// 	print '</div>';
	// 	print '<div class="col-1">';
	// 		print '<p class="filter" onclick="toggleTable(\'sixth\', this)">Sixth Table</p>';
	// 	print '</div>';
	// 	print '<div class="col-1">';
	// 		print '<p class="filter" onclick="toggleTable(\'seventh\', this)">Seventh Table</p>';
	// 	print '</div>';
	// 	print '<div class="col-1">';
	// 		print '<p class="filter" onclick="toggleTable(\'eight\', this)">Eight Table</p>';
	// 	print '</div>';
	// 	print '<div class="col-1">';
	// 		print '<p class="filter" onclick="toggleTable(\'nine\', this)">Nine Table</p>';
	// 	print '</div>';
	// 	print '<div class="col-1">';
	// 		print '<p class="filter" onclick="toggleTable(\'ten\', this)">Ten Table</p>';
	// 	print '</div>';
	// print '</div>';

    // print '
	// ';
	// print '$(document).ready(function () {
	// 			var lon1, lon2, lat1, lat2;
	// 			var current;
	// 			var storeid, tecid, distance;
	// 			var key = "";
	// 			var selectElement = document.getElementById("options_fk_store");
	// 			selectElement.disabled = true;
	// 			var selectElement1 = document.getElementById("options_fk_internal");
	// 			selectElement1.disabled = true;
	// 			var selectCurrent = document.getElementById("current_location");
	// 			// lon2 = null;
	// 			// lat2 = null;

	// 			selectCurrent.addEventListener("change", function() {
	// 				selectElement1.disabled = false;
	// 				if (selectCurrent.value == "1") {
	// 					if (navigator.geolocation) {
	// 						navigator.geolocation.getCurrentPosition(showPosition);
	// 					} else {
	// 						alert("Geolocation is not supported by this browser.");
	// 					}
	// 				}else{
	// 					current = 2;
	// 				}
	// 			});

	// 			selectElement1.addEventListener("change", function() {
	// 				tecid = selectElement1.value.split("|")[1];
	// 				if (selectElement1.value !== "1") {
	// 					selectElement.disabled = false;
	// 					if(current == 2){

	// 						// AJAX call to PHP script
	// 						var dataToSend = {
	// 							tecid: tecid
	// 						};
	// 						var xhr = new XMLHttpRequest();
	// 						xhr.open("POST", "upload.php", true);
	// 						xhr.setRequestHeader("Content-Type", "application/json");
	// 						xhr.send(JSON.stringify(dataToSend));
	// 						xhr.onload = function () {
	// 							if (xhr.status == 200) {
	// 								var result = xhr.response.split(",");
	// 								console.log(xhr.response.split(","));
	// 								if(result.length < 2){
	// 									var url1 = `https://api.opencagedata.com/geocode/v1/json?q=${selectElement1.value.split("|")[0]}&key=f2fc74d7cd6348e8a9e583cbb55c7848`;
	// 									fetch(url1)
	// 										.then(response => response.json())
	// 										.then(data => {
	// 											lon1 = data["results"][0]["geometry"]["lat"];
	// 											lat1 = data["results"][0]["geometry"]["lng"];
	// 										})
	// 										.catch(error => console.error("Error fetching data:", error));
	// 								}else{
	// 									lon1 = result[0];
	// 									lat1 = result[1];
	// 								}
	// 							} else {
	// 								console.error("Error saving data:", xhr.statusText);
	// 							}
	// 						};
	// 					}
	// 				} else {
	// 					selectElement.disabled = true;
	// 					lon1 = null;
	// 					lat1 = null;
	// 				}
	// 			});
	// 			selectElement.addEventListener("change", function() {
	// 				storeid = selectElement.value.split("|")[1];
	// 				if (selectElement.value !== "1") {
	// 					var dataToSend1 = {
	// 						storeid: storeid
	// 					};
	// 					var xhr = new XMLHttpRequest();
	// 					xhr.open("POST", "upload.php", true);
	// 					xhr.setRequestHeader("Content-Type", "application/json");
	// 					xhr.send(JSON.stringify(dataToSend1));
	// 					xhr.onload = function () {
	// 						if (xhr.status == 200) {
	// 							var result = xhr.response.split(",");
	// 							console.log(xhr.response.split(","));
	// 							if (result.length < 2) {
	// 								console.log("ddd");
	// 								var url1 = `https://api.opencagedata.com/geocode/v1/json?q=${selectElement.value.split("|")[0]}&key=f2fc74d7cd6348e8a9e583cbb55c7848`;
	// 								fetch(url1)
	// 									.then(response => response.json())
	// 									.then(data => {
	// 										lon2 = data["results"][0]["geometry"]["lat"];
	// 										lat2 = data["results"][0]["geometry"]["lng"];
	// 										calculateDistance(lat1, lon1, lat2, lon2);
	// 										// Move the dataToSend and AJAX call inside this block
	// 										var dataToSend = {
	// 											lon1: lon1,
	// 											lon2: lon2,
	// 											lat1: lat1,
	// 											lat2: lat2,
	// 											tecid: tecid,
	// 											storeid: storeid,
	// 											distance: distance
	// 										};
	// 										console.log(dataToSend);
	// 										var xhr = new XMLHttpRequest();
	// 										xhr.open("POST", "upload.php", true);
	// 										xhr.setRequestHeader("Content-Type", "application/json");
	// 										xhr.send(JSON.stringify(dataToSend));
	// 										xhr.onload = function() {
	// 											if (xhr.status == 200) {
	// 												console.log("Data saved successfully:", xhr.responseText);
	// 											} else {
	// 												console.error("Error saving data:", xhr.statusText);
	// 											}
	// 										};
	// 									})
	// 									.catch(error => console.error("Error fetching data:", error));
	// 							} else {
	// 								lon2 = parseFloat(result[0]);
	// 								lat2 = parseFloat(result[1]);
	// 								calculateDistance(lat1, lon1, lat2, lon2);
	// 								// Move the dataToSend and AJAX call inside this block
	// 								var dataToSend2 = {
	// 									lon1: lon1,
	// 									lon2: lon2,
	// 									lat1: lat1,
	// 									lat2: lat2,
	// 									tecid: tecid,
	// 									storeid: storeid,
	// 									distance: distance
	// 								};
	// 								var xhr1 = new XMLHttpRequest();
	// 								xhr1.open("POST", "upload.php", true);
	// 								xhr1.setRequestHeader("Content-Type", "application/json");
	// 								xhr1.send(JSON.stringify(dataToSend2));
	// 								xhr1.onload = function() {
	// 									if (xhr1.status == 200) {
	// 										console.log("Data saved successfully:", xhr1.responseText);
	// 									} else {
	// 										console.error("Error saving data:", xhr1.statusText);
	// 									}
	// 								};
	// 							}
	// 						} else {
	// 							console.error("Error saving data:", xhr.statusText);
	// 						}
	// 					};
	// 				} else {
	// 					lon2 = null;
	// 					lat2 = null;
	// 				}
	// 			});

	// 			function showPosition(position) {
	// 				lat1 = position.coords.latitude;
	// 				lon1 = position.coords.longitude;
	// 				current = 1;
	// 				console.log(lat1);
	// 				console.log(lon1);
	// 				console.log(current);
	// 			}
				
	// 			function calculateDistance(lat1, lon1, lat2, lon2) {
	// 					const R = 6371; // Radius of the earth in kilometers
	// 					const dLat = deg2rad(lat2 - lat1); // deg2rad below
	// 					const dLon = deg2rad(lon2 - lon1);
	// 					const a =
	// 						Math.sin(dLat / 2) * Math.sin(dLat / 2) +
	// 						Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
	// 						Math.sin(dLon / 2) * Math.sin(dLon / 2);
	// 					const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
	// 					distance = R * c; // Distance in km
	// 					var inputElement = document.getElementById("distance");
	// 					inputElement.value = distance;
	// 					return distance;
	// 				}
			
	// 			function deg2rad(deg) {
	// 				return deg * (Math.PI / 180);
	// 			}
	// 		});';		