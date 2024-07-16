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

try {
    if (isset($user->rights->stores->branch) && isset($user->rights->stores->branch->read)) {
		$permissiontoread = $user->rights->stores->branch->read;
    } else {
		$permissiontoread = 0;
    }
    if (isset($user->rights->stores->branch) && isset($user->rights->stores->branch->write)) {
		$permissiontoadd = $user->rights->stores->branch->write;
    } else {
		$permissiontoadd = 0;
    }
    if (isset($user->rights->stores->branch) && isset($user->rights->stores->branch->delete)) {
		$permissiontodelete = $user->rights->stores->branch->delete;
    } else {
		$permissiontodelete = 0;
    }
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}
if (empty($conf->stores->enabled)) accessforbidden();
if (!$permissiontoread) accessforbidden();
dol_include_once('/ticket/class/ticket.class.php');
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/stores/class/branch.class.php';

$projectid  = GETPOST('projectid', 'int');
$project_ref = GETPOST('project_ref', 'alpha');
$search_societe = GETPOST('search_societe', 'alpha');
$search_fk_project = GETPOST('search_fk_project', 'int') ?GETPOST('search_fk_project', 'int') : GETPOST('projectid', 'int');
$search_date_start = dol_mktime(0, 0, 0, GETPOST('search_date_startmonth', 'int'), GETPOST('search_date_startday', 'int'), GETPOST('search_date_startyear', 'int'));
$search_date_end = dol_mktime(23, 59, 59, GETPOST('search_date_endmonth', 'int'), GETPOST('search_date_endday', 'int'), GETPOST('search_date_endyear', 'int'));
$search_dateread_start = dol_mktime(0, 0, 0, GETPOST('search_dateread_startmonth', 'int'), GETPOST('search_dateread_startday', 'int'), GETPOST('search_dateread_startyear', 'int'));
$search_dateread_end = dol_mktime(23, 59, 59, GETPOST('search_dateread_endmonth', 'int'), GETPOST('search_dateread_endday', 'int'), GETPOST('search_dateread_endyear', 'int'));
$search_dateclose_start = dol_mktime(0, 0, 0, GETPOST('search_dateclose_startmonth', 'int'), GETPOST('search_dateclose_startday', 'int'), GETPOST('search_dateclose_startyear', 'int'));
$search_dateclose_end = dol_mktime(23, 59, 59, GETPOST('search_dateclose_endmonth', 'int'), GETPOST('search_dateclose_endday', 'int'), GETPOST('search_dateclose_endyear', 'int'));
$massaction = GETPOST('massaction', 'alpha'); // The bulk action (combo box choice into lists)

if (GETPOST('cancel', 'alpha')) {
	$action = 'list';
	$massaction = '';
}
if (!GETPOST('confirmmassaction', 'alpha') && $massaction != 'presend' && $massaction != 'confirm_presend' && $massaction != 'presendonclose' && $massaction != 'close') {
	$massaction = '';
}

$object = new Ticket($db);
$limit = GETPOST('limit', 'int') ?GETPOST('limit', 'int') : $conf->liste_limit;
// List of mass actions available
$arrayofmassactions = array(
	//'presend'=>img_picto('', 'email', 'class="pictofixedwidth"').$langs->trans("SendByMail"),
	//'builddoc'=>img_picto('', 'pdf', 'class="pictofixedwidth"').$langs->trans("PDFMerge"),
);
$form = new Form($db);

$massactionbutton = $form->selectMassAction('', $arrayofmassactions);


$mode = GETPOST('mode', 'alpha');

// Load variable for pagination
$limit = GETPOST('limit', 'int') ?GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST('sortfield', 'aZ09comma');
$sortorder = GETPOST('sortorder', 'aZ09comma');
$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST("page", 'int');
if (empty($page) || $page < 0 || GETPOST('button_search', 'alpha') || GETPOST('button_removefilter', 'alpha')) {
	// If $page is not defined, or '' or -1 or if we click on clear filters
	$page = 0;
}
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
// Initialize array of search criterias
$search_all = (GETPOSTISSET("search_all") ? GETPOST("search_all", 'alpha') : GETPOST('sall'));
$search = array();
foreach ($object->fields as $key => $val) {
	if (GETPOST('search_'.$key, 'alpha') !== '') {
		$search[$key] = GETPOST('search_'.$key, 'alpha');
	}
	if (preg_match('/^(date|timestamp|datetime)/', $val['type'])) {
		$search[$key.'_dtstart'] = dol_mktime(0, 0, 0, GETPOST('search_'.$key.'_dtstartmonth', 'int'), GETPOST('search_'.$key.'_dtstartday', 'int'), GETPOST('search_'.$key.'_dtstartyear', 'int'));
		$search[$key.'_dtend'] = dol_mktime(23, 59, 59, GETPOST('search_'.$key.'_dtendmonth', 'int'), GETPOST('search_'.$key.'_dtendday', 'int'), GETPOST('search_'.$key.'_dtendyear', 'int'));
	}
}

$arrayfields = array();
foreach ($object->fields as $key => $val) {
	// If $val['visible']==0, then we never show the field
	if (!empty($val['visible'])) {
		$visible = (int) dol_eval($val['visible'], 1, 1, '1');
		$arrayfields['t.'.$key] = array(
			'label'=>$val['label'],
			'checked'=>(($visible < 0) ? 0 : 1),
			'enabled'=>($visible != 3 && dol_eval($val['enabled'], 1)),
			'position'=>$val['position'],
			'help'=> isset($val['help']) ? $val['help'] : ''
		);
	}
}
// Load translation files required by the page
$langs->loadLangs(array("stores@stores"));

$action = GETPOST('action', 'aZ09');


// Security check
// if (! $user->rights->stores->myobject->read) {
// 	accessforbidden();
// }
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0) {
	$action = '';
	$socid = $user->socid;
}

$max = 5;
$now = dol_now();


/*
 * Actions
 */

// None


/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

llxHeader("", $langs->trans("StoresArea"));

print load_fiche_titre($langs->trans("StoresArea"), '', 'stores.png@stores');

$socid = 0;
if(isset($user->socid)){
	$socid = $user->socid;
}

print '<div class="fichecenter"><div class="fichethirdleft">';
		
		$sql = "SELECT count(*) FROM ".MAIN_DB_PREFIX."stores_branch";
		if($socid > 0){
			$sql .= " WHERE fk_soc =".$socid;
		}
		$total = $db->query($sql)->fetch_row()[0];
		$stateSql = "SELECT t.nom, (SELECT count(*) FROM ".MAIN_DB_PREFIX."ticket WHERE fk_soc = t.rowid) as 'count' FROM ".MAIN_DB_PREFIX."societe AS t";
		if($socid > 0){
			$stateSql .= " Where t.rowid =".$socid;
		}
		$stateSql .= " order by count desc limit 10;";
		$states = $db->query($stateSql)->fetch_all();
		$ss = array();
		foreach($states as $elem){
			$ss[] = array($elem[0], $elem[1]);
		}
		$thirdpartygraph = '<div class="div-table-responsive-no-min">';
		$thirdpartygraph .= '<table class="noborder nohover centpercent">'."\n";
		$thirdpartygraph .= '<tr class="liste_titre"><th colspan="2">'.$langs->trans("Statistics").'</th></tr>';
		$thirdpartygraph .= '<tr><td class="center" colspan="2">';
		
		include_once DOL_DOCUMENT_ROOT.'/core/class/dolgraph.class.php';
		$dolgraph = new DolGraph();
		$dolgraph->SetData($ss);
		$dolgraph->setShowLegend(2);
		$dolgraph->setShowPercent(1);
		$dolgraph->SetType(array('pie'));
		$dolgraph->setHeight('300');
		$dolgraph->draw('idgraphthirdparties');
		$thirdpartygraph .= $dolgraph->show();
		$thirdpartygraph .= '</td></tr>'."\n";
		$thirdpartygraph .= '<tr class="liste_total"><td>'.$langs->trans("UniqueStores").'</td><td class="right">';
		$thirdpartygraph .= $total;
		$thirdpartygraph .= '</td></tr>';
		$thirdpartygraph .= '</table>';
		$thirdpartygraph .= '</div>';

		print $thirdpartygraph;	
		/////////////////////////////end pie chart////////////////////////

		/////////////////////////////////////start last unread tickets table
		$max = 10;

		$sql = "SELECT t.rowid, t.ref, t.track_id, t.fk_soc, third.nom, t.datec, t.subject, t.type_code, t.category_code, t.severity_code, t.fk_statut, t.progress,";
		$sql .= " type.code as type_code, type.label as type_label,";
		$sql .= " category.code as category_code, category.label as category_label,";
		$sql .= " severity.code as severity_code, severity.label as severity_label,";
		$sql .= " te.store";
		$sql .= " FROM ".MAIN_DB_PREFIX."ticket as t";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_ticket_type as type ON type.code=t.type_code";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe as third ON third.rowid=t.fk_soc";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_ticket_category as category ON category.code=t.category_code";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_ticket_severity as severity ON severity.code=t.severity_code";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."ticket_extrafields as te ON te.fk_object=t.rowid";
		if (empty($user->rights->societe->client->voir) && !$socid) {
			$sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
		}
	
		$sql .= ' WHERE t.entity IN ('.getEntity('ticket').')';
		$sql .= " AND t.fk_statut=0";
		if (empty($user->rights->societe->client->voir) && !$socid) {
			$sql .= " AND t.fk_soc = sc.fk_soc AND sc.fk_user = ".((int) $user->id);
		}
	
		if ($user->socid > 0) {
			$sql .= " AND t.fk_soc= ".((int) $user->socid);
		} else {
			// Restricted to assigned user only
			if (!empty($conf->global->TICKET_LIMIT_VIEW_ASSIGNED_ONLY) && !$user->rights->ticket->manage) {
				$sql .= " AND t.fk_user_assign = ".((int) $user->id);
			}
		}
		$sql .= $db->order("t.datec", "DESC");
		$sql .= $db->plimit($max, 0);
	
		$result = $db->query($sql);

		if ($result) {
			$num = $db->num_rows($result);
	
			$i = 0;
	
			$transRecordedType = $langs->trans("LatestNewTickets", $max);
	
			print '<div class="div-table-responsive-no-min">';
			print '<table class="noborder centpercent">';
			print '<tr class="liste_titre"><th colspan="4">'.$transRecordedType.'</th>';
			print '<th class="right" colspan="2"><a href="'.DOL_URL_ROOT.'/ticket/list.php?search_fk_statut[]='.Ticket::STATUS_NOT_READ.'">'.$langs->trans("FullList").'</th>';
			print '</tr>';
			if ($num > 0) {
				while ($i < $num) {
					$objp = $db->fetch_object($result);
					// var_dump($objp);
					$object->id = $objp->rowid;
					$object->ref = $objp->ref;
					$object->track_id = $objp->track_id;
					$object->fk_statut = $objp->fk_statut;
					$object->progress = $objp->progress;
					$object->subject = $objp->subject;
					$object->fk_soc = $objp->fk_soc;
					$object->store = $objp->store;
					
					$store = new Branch($db);
					$store->fetch($objp->store);

					print '<tr class="oddeven">';
	
					// Ref
					print '<td class="nowraponall">';
					print $object->getNomUrl(1);
					print "</td>\n";
	
					// Creation date
					print '<td class="left">';
					print dol_print_date($db->jdate($objp->datec), 'day');
					print "</td>";

					print '<td class="nowrap">';
					print $objp->store ? $store->getNomUrl(1) : "";
					print "</td>\n";

					// // Subject
					// print '<td class="nowrap">';
					// print '<a href="card.php?track_id='.$objp->track_id.'">'.dol_trunc($objp->subject, 30).'</a>';
					// print "</td>\n";
	
					// // Type
					// print '<td class="nowrap tdoverflowmax100">';
					// $s = $langs->getLabelFromKey($db, 'TicketTypeShort'.$objp->type_code, 'c_ticket_type', 'code', 'label', $objp->type_code);
					// print '<span title="'.dol_escape_htmltag($s).'">'.$s.'</span>';
					// print '</td>';
	
					// Category
					print '<td class="nowrap">';
					if (!empty($obp->category_code)) {
						$s = $langs->getLabelFromKey($db, 'TicketCategoryShort'.$objp->category_code, 'c_ticket_category', 'code', 'label', $objp->category_code);
						print '<span title="'.dol_escape_htmltag($s).'">'.$s.'</span>';
					}
					//print $objp->category_label;
					print "</td>";
	
					// Severity
					print '<td class="nowrap">';
					$s = $langs->getLabelFromKey($db, 'TicketSeverityShort'.$objp->severity_code, 'c_ticket_severity', 'code', 'label', $objp->severity_code);
					print '<span title="'.dol_escape_htmltag($s).'">'.$s.'</span>';
					//print $objp->severity_label;
					print "</td>";
	
					print '<td class="nowraponall right">';
					print $object->getLibStatut(5);
					print "</td>";
	
					print "</tr>\n";
					$i++;
				}
	
				$db->free($result);
			} else {
				print '<tr><td colspan="6"><span class="opacitymedium">'.$langs->trans('NoUnreadTicketsFound').'</span></td></tr>';
			}
	
			print "</table>";
			print '</div>';
	
			print '<br>';
		} else {
			dol_print_error($db);
		}
	

print '</div><div class="fichetwothirdright">';
//////////////////////////////////third parties table

$thirdparty_static = new Societe($db);
$max = 15;
$sql = "SELECT s.rowid, s.nom as name, s.email, s.client, s.fournisseur";
$sql .= ", s.code_client";
$sql .= ", (select count(*) from ".MAIN_DB_PREFIX."stores_branch where fk_soc = s.rowid) as `count_stores`";
$sql .= ", (select count(*) from ".MAIN_DB_PREFIX."ticket where fk_soc = s.rowid) as `tickets_count`";
$sql .= ", s.code_fournisseur";
if (!empty($conf->global->MAIN_COMPANY_PERENTITY_SHARED)) {
	$sql .= ", spe.accountancy_code_supplier as code_compta_fournisseur";
	$sql .= ", spe.accountancy_code_customer as code_compta";
} else {
	$sql .= ", s.code_compta_fournisseur";
	$sql .= ", s.code_compta";
}
$sql .= ", s.logo";
$sql .= ", s.entity";
$sql .= ", s.canvas, s.tms as date_modification, s.status as status";
$sql .= " FROM ".MAIN_DB_PREFIX."societe as s";
if (!empty($conf->global->MAIN_COMPANY_PERENTITY_SHARED)) {
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe_perentity as spe ON spe.fk_soc = s.rowid AND spe.entity = " . ((int) $conf->entity);
}
if (empty($user->rights->societe->client->voir) && !$socid) {
	$sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
}
$sql .= ' WHERE s.entity IN ('.getEntity('societe').')';
if (empty($user->rights->societe->client->voir) && !$socid) {
	$sql .= " AND s.rowid = sc.fk_soc AND sc.fk_user = ".((int) $user->id);
}
if (empty($user->rights->fournisseur->lire)) {
	$sql .= " AND (s.fournisseur != 1 OR s.client != 0)";
}
// Add where from hooks
$parameters = array('socid' => $socid);
$reshook = $hookmanager->executeHooks('printFieldListWhere', $parameters, $thirdparty_static); // Note that $action and $object may have been modified by hook
if (empty($reshook)) {
	if ($socid > 0) {
		$sql .= " AND s.rowid = ".((int) $socid);
	}
}
$sql .= $hookmanager->resPrint;
$sql .= $db->order("tickets_count", "DESC");
$sql .= $db->plimit(10, 0);

//print $sql;
$lastmodified="";
$result = $db->query($sql);
if ($result) {
	$num = $db->num_rows($result);

	$i = 0;

	if ($num > 0) {
		$transRecordedType = $langs->trans("LastModifiedThirdParties", $max);

		$lastmodified = "\n<!-- last thirdparties modified -->\n";
		$lastmodified .= '<div class="div-table-responsive-no-min">';
		$lastmodified .= '<table class="noborder centpercent">';
		
		$lastmodified .= '<tr class="liste_titre"><th colspan="1">Customer of last ticket</th>';
		$lastmodified .= '<th colspan="1">Stores Count</th>';
		$lastmodified .= '<th colspan="1">Create Date</th>';
		$lastmodified .= '<th colspan="1">Calls number</th>';
		$lastmodified .= '<th colspan="1">Status</th>';
		$lastmodified .= '</tr>'."\n";

		while ($i < $num) {
			$objp = $db->fetch_object($result);
			// var_dump($objp);
			$thirdparty_static->id = $objp->rowid;
			$thirdparty_static->name = $objp->name;
			$thirdparty_static->client = $objp->client;
			$thirdparty_static->fournisseur = $objp->fournisseur;
			$thirdparty_static->logo = $objp->logo;
			$thirdparty_static->date_modification = $db->jdate($objp->date_modification);
			$thirdparty_static->status = $objp->status;
			$thirdparty_static->code_client = $objp->code_client;
			$thirdparty_static->code_fournisseur = $objp->code_fournisseur;
			$thirdparty_static->canvas = $objp->canvas;
			$thirdparty_static->email = $objp->email;
			$thirdparty_static->entity = $objp->entity;
			$thirdparty_static->code_compta_fournisseur = $objp->code_compta_fournisseur;
			$thirdparty_static->code_compta = $objp->code_compta;
			$thirdparty_static->count_stores = $objp->count_stores;
			$thirdparty_static->tickets_count = $objp->tickets_count;

			$lastmodified .= '<tr class="oddeven">';
			// Name
			$lastmodified .= '<td class="nowrap tdoverflowmax200">';
			$lastmodified .= $thirdparty_static->getNomUrl(1);
			$lastmodified .= "</td>\n";
			// Type
			$lastmodified .= '<td class="center">';
			$lastmodified .= $thirdparty_static->count_stores;
			$lastmodified .= '</td>';
			// Last modified date
			$lastmodified .= '<td class="center tddate" title="'.dol_escape_htmltag($langs->trans("DateModification").' '.dol_print_date($thirdparty_static->date_modification, 'dayhour', 'tzuserrel')).'">';
			$lastmodified .= dol_print_date($thirdparty_static->date_modification, 'day', 'tzuserrel');
			$lastmodified .= "</td>";
			$lastmodified .= '<td class="center nowrap">';
			$lastmodified .= $thirdparty_static->tickets_count;
			$lastmodified .= "</td>";
			$lastmodified .= '<td class="right nowrap">';
			$lastmodified .= $thirdparty_static->getLibStatut(3);
			$lastmodified .= "</td>";
			$lastmodified .= "</tr>\n";
			$i++;
		}

		$db->free($result);

		$lastmodified .= "</table>\n";
		$lastmodified .= '</div>';
		$lastmodified .= "<!-- End last thirdparties modified -->\n";
	}
} else {
	dol_print_error($db);
}
print $lastmodified;
//////////////////////////////////end third parties table

include DOL_DOCUMENT_ROOT.'/theme/'.$conf->theme.'/theme_vars.inc.php';
$listofoppstatus = array(); $listofopplabel = array(); 
$listofoppcode = array(); $colorseries = array();
$sql = "SELECT cls.rowid, cls.code, cls.percent, cls.label";
$sql .= " FROM ".MAIN_DB_PREFIX."c_lead_status as cls";
$sql .= " WHERE active=1";
$resql = $db->query($sql);
if ($resql) {
	$num = $db->num_rows($resql);
	$i = 0;

	while ($i < $num) {
		$objp = $db->fetch_object($resql);
		$listofoppstatus[$objp->rowid] = $objp->percent;
		$listofopplabel[$objp->rowid] = $objp->label;		// default label if translation from "OppStatus".code not found.
		$listofoppcode[$objp->rowid] = $objp->code;
		switch ($objp->code) {
			case 'PROSP':
				$colorseries[$objp->rowid] = "-".$badgeStatus0;
				break;
			case 'QUAL':
				$colorseries[$objp->rowid] = "-".$badgeStatus1;
				break;
			case 'PROPO':
				$colorseries[$objp->rowid] = $badgeStatus1;
				break;
			case 'NEGO':
				$colorseries[$objp->rowid] = $badgeStatus4;
				break;
			case 'LOST':
				$colorseries[$objp->rowid] = $badgeStatus9;
				break;
			case 'WON':
				$colorseries[$objp->rowid] = $badgeStatus6;
				break;
			default:
				$colorseries[$objp->rowid] = $badgeStatus2;
				break;
		}
		$i++;
	}
} else {
	dol_print_error($db);
}
$search_project_user = GETPOST('search_project_user', 'int');
$mine = GETPOST('mode', 'aZ09') == 'mine' ? 1 : 0;
if ($mine == 0 && $search_project_user === '') {
	$search_project_user = (empty($user->conf->MAIN_SEARCH_PROJECT_USER_PROJECTSINDEX) ? '' : $user->conf->MAIN_SEARCH_PROJECT_USER_PROJECTSINDEX);
}
if ($search_project_user == $user->id) {
	$mine = 1;
}
$companystatic = new Societe($db);
$projectstatic = new Project($db);
$form = new Form($db);
$formfile = new FormFile($db);
$projectset = ($mine ? $mine : (empty($user->rights->projet->all->lire) ? 0 : 2));
$projectsListId = $projectstatic->getProjectsAuthorizedForUser($user, $projectset, 1);



$NBMAX = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;
$max = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;

if (empty($conf->global->PROJECT_HIDE_PROJECT_LIST_ON_PROJECT_AREA)) {
	// This list can be very long, so we allow to hide it to prefer to use the list page.
	// Add constant PROJECT_HIDE_PROJECT_LIST_ON_PROJECT_AREA to hide this list

	// print '<br>';

	print_projecttasks_array($db, $form, $socid, $projectsListId, 0, 1, $listofoppstatus, array());
}

print '</div>';
print '<br>';
////////////////////////////////////////////////////list of all tickets
// Build and execute select
// --------------------------------------------------------------------
$sql = 'SELECT ';
$sql .= $object->getFieldList('t');
// Add fields from extrafields
if (!empty($extrafields->attributes[$object->table_element]['label'])) {
	foreach ($extrafields->attributes[$object->table_element]['label'] as $key => $val) {
		$sql .= ($extrafields->attributes[$object->table_element]['type'][$key] != 'separate' ? ", ef.".$key." as options_".$key : '');
	}
}
// Add fields from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListSelect', $parameters, $object); // Note that $action and $object may have been modified by hook
$sql .= preg_replace('/^,/', '', $hookmanager->resPrint);
$sql = preg_replace('/,\s*$/', '', $sql);
$sql .= ", te.store"; 
$sql .= " FROM ".MAIN_DB_PREFIX.$object->table_element." as t";
if (isset($extrafields->attributes[$object->table_element]['label']) && is_array($extrafields->attributes[$object->table_element]['label']) && count($extrafields->attributes[$object->table_element]['label'])) {
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX.$object->table_element."_extrafields as ef on (t.rowid = ef.fk_object)";
}
// Add table from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListFrom', $parameters, $object); // Note that $action and $object may have been modified by hook
$sql .= $hookmanager->resPrint;
$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON (t.fk_soc = s.rowid)";
$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."ticket_extrafields as te ON (t.rowid = te.fk_object)";
$sql .= " WHERE t.entity IN (".getEntity($object->element).")";
if ($socid > 0) {
	$sql .= " AND t.fk_soc = ".((int) $socid);
}

foreach ($search as $key => $val) {
	if ($key == 'fk_statut' && !empty($search['fk_statut'])) {
		$newarrayofstatus = array();
		foreach ($search['fk_statut'] as $key2 => $val2) {
			if (in_array($val2, array('openall', 'closeall'))) {
				continue;
			}
			$newarrayofstatus[] = $val2;
		}
		if ($search['fk_statut'] == 'openall' || in_array('openall', $search['fk_statut'])) {
			$newarrayofstatus[] = Ticket::STATUS_NOT_READ;
			$newarrayofstatus[] = Ticket::STATUS_READ;
			$newarrayofstatus[] = Ticket::STATUS_ASSIGNED;
			$newarrayofstatus[] = Ticket::STATUS_IN_PROGRESS;
			$newarrayofstatus[] = Ticket::STATUS_NEED_MORE_INFO;
			$newarrayofstatus[] = Ticket::STATUS_WAITING;
		}
		if ($search['fk_statut'] == 'closeall' || in_array('closeall', $search['fk_statut'])) {
			$newarrayofstatus[] = Ticket::STATUS_CLOSED;
			$newarrayofstatus[] = Ticket::STATUS_CANCELED;
		}
		if (count($newarrayofstatus)) {
			$sql .= natural_search($key, join(',', $newarrayofstatus), 2);
		}
		continue;
	} elseif ($key == 'fk_user_assign' || $key == 'fk_user_create' || $key == 'fk_project') {
		if ($search[$key] > 0) {
			$sql .= natural_search($key, $search[$key], 2);
		}
		continue;
	}

	$mode_search = ((!empty($object->fields[$key]) && ($object->isInt($object->fields[$key]) || $object->isFloat($object->fields[$key]))) ? 1 : 0);
	// $search[$key] can be an array of values, or a string. We add filter if array not empty or if it is a string.
	if ((is_array($search[$key]) && !empty($search[$key])) || (!is_array($search[$key]) && $search[$key] != '')) {
		$sql .= natural_search($key, $search[$key], $mode_search);
	}
}
if ($search_all) {
	$sql .= natural_search(array_keys($fieldstosearchall), $search_all);
}
if ($search_societe) {
	$sql .= natural_search('s.nom', $search_societe);
}
if ($search_fk_project > 0) {
	$sql .= natural_search('fk_project', $search_fk_project, 2);
}
if ($search_date_start) {
	$sql .= " AND t.datec >= '".$db->idate($search_date_start)."'";
}
if ($search_date_end) {
	$sql .= " AND t.datec <= '".$db->idate($search_date_end)."'";
}
if ($search_dateread_start) {
	$sql .= " AND t.date_read >= '".$db->idate($search_dateread_start)."'";
}
if ($search_dateread_end) {
	$sql .= " AND t.date_read <= '".$db->idate($search_dateread_end)."'";
}
if ($search_dateclose_start) {
	$sql .= " AND t.date_close >= '".$db->idate($search_dateclose_start)."'";
}
if ($search_dateclose_end) {
	$sql .= " AND t.date_close <= '".$db->idate($search_dateclose_end)."'";
}

if (!$user->socid && ($mode == "mine" || (!$user->admin && $conf->global->TICKET_LIMIT_VIEW_ASSIGNED_ONLY))) {
	$sql .= " AND (t.fk_user_assign = ".((int) $user->id);
	if (empty($conf->global->TICKET_LIMIT_VIEW_ASSIGNED_ONLY)) {
		$sql .= " OR t.fk_user_create = ".((int) $user->id);
	}
	$sql .= ")";
}
// var_dump($sql);
// Add where from extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_sql.tpl.php';
// Add where from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListWhere', $parameters, $object); // Note that $action and $object may have been modified by hook
$sql .= $hookmanager->resPrint;

// Count total nb of records
$nbtotalofrecords = '';
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
	/* The fast and low memory method to get and count full list converts the sql into a sql count */
	$sqlforcount = preg_replace('/^SELECT[a-z0-9\._\s\(\),]+FROM/i', 'SELECT COUNT(*) as nbtotalofrecords FROM', $sql);
	$resql = $db->query($sqlforcount);
	$objforcount = $db->fetch_object($resql);
	$nbtotalofrecords = $objforcount->nbtotalofrecords;
	if (($page * $limit) > $nbtotalofrecords) {	// if total of record found is smaller than page * limit, goto and load page 0
		$page = 0;
		$offset = 0;
	}
	$db->free($resql);
}

// Complete request and execute it with limit
$sql .= $db->order($sortfield, $sortorder);
if ($limit) {
	$sql .= $db->plimit($limit + 1, $offset);
}

$resql = $db->query($sql);
if (!$resql) {
	dol_print_error($db);
	exit;
}

$num = $db->num_rows($resql);
// var_dump($sql);
// Direct jump if only one record found
if ($num == 1 && !empty($conf->global->MAIN_SEARCH_DIRECT_OPEN_IF_ONLY_ONE) && $search_all && !$page) {
	$obj = $db->fetch_object($resql);
	$id = $obj->rowid;
	header("Location: ".DOL_URL_ROOT.'/ticket/card.php?id='.$id);
	exit;
}

// Loop on record
// --------------------------------------------------------------------
$i = 0;
$totalarray = array();
$totalarray['nbfield'] = 0;
$now = dol_now();
print '<div class="">';
print '<table class="border centpercent tableforfieldedit">';
print '	<tr class="liste_titre">
			<th colspan="9">'.$langs->trans("AllTickets").'</th>
		</tr>';
print '	<tr class="liste_titre">
			<th>Ref.</th>
			<th>Subject</th>
			<th>Type</th>
			<th>Severity</th>
			<th>Store</th>
			<th>Creation date</th>
			<th>Close date</th>
			<th>Assigned to</th>
			<th>Status</th>
		</tr>';
$cacheofoutputfield = array();
while ($i < ($limit ? min($num, $limit) : $num)) {
	$obj = $db->fetch_object($resql);
	// var_dump($obj);
	if (empty($obj)) {
		break; // Should not happen
	}

	// Store properties in $object
	$object->setVarsFromFetchObj($obj);
	// var_dump($object);

	$object->status = $object->fk_statut; // fk_statut is deprecated
	$soc = new Societe($db);
	$soc->fetch($object->fk_soc);
	$store = new Branch($db);
	$store->fetch($obj->store);

	// Show here line of result
	print '<tr class="oddeven">';
	
		// Ref
		print '<td class="nowraponall">';
		print $object->getNomUrl(1);
		print "</td>\n";

		// Subject
		print '<td class="nowrap">';
		print '<a href="card.php?track_id='.$object->track_id.'">'.dol_trunc($object->subject, 30).'</a>';
		print "</td>\n";

		// // Type
		print '<td class="nowrap tdoverflowmax100">';
		$s = $langs->getLabelFromKey($db, 'TicketTypeShort'.$object->type_code, 'c_ticket_type', 'code', 'label', $object->type_code);
		print '<span title="'.dol_escape_htmltag($s).'">'.$s.'</span>';
		print '</td>';

		// Severity
		print '<td class="nowrap">';
		$s = $langs->getLabelFromKey($db, 'TicketSeverityShort'.$object->severity_code, 'c_ticket_severity', 'code', 'label', $object->severity_code);
		print '<span title="'.dol_escape_htmltag($s).'">'.$s.'</span>';
		print "</td>";

		// Third party
		print '<td class="nowrap">';
		print $obj->store ? $store->getNomUrl(1) : "";
		print "</td>\n";
	
		// Creation date
		print '<td class="nowrap">';
		print $object->datec ? date('d/m/y H:i A', $object->datec) : "";
		print "</td>";
	
		// Creation date
		print '<td class="nowrap">';
		print $object->date_close ? date('d/m/y H:i A', $object->date_close) : "";
		print "</td>";
	
		// assigned to
		print '<td class="nowrap">';
				if ($object->fk_user_assign > 0) {
					if (isset($conf->cache['user'][$object->fk_user_assign])) {
						$user_temp = $conf->cache['user'][$object->fk_user_assign];
					} else {
						$user_temp = new User($db);
						$user_temp->fetch($object->fk_user_assign);
						$conf->cache['user'][$object->fk_user_assign] = $user_temp;
					}
					print $user_temp->getNomUrl(-1);
				}
		print '</td>';		

		print '<td class="nowraponall">';
		print $object->getLibStatut(5);
		print "</td>";

	// Extra fields
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_print_fields.tpl.php';
	// Fields from hook
	$parameters = array('arrayfields'=>$arrayfields, 'object'=>$object, 'obj'=>$obj, 'i'=>$i, 'totalarray'=>&$totalarray);
	$reshook = $hookmanager->executeHooks('printFieldListValue', $parameters, $object); // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;
	// Action column
	print '<td class="nowrap center">';
	if ($massactionbutton || $massaction) {   // If we are in select mode (massactionbutton defined) or if we have already selected and sent an action ($massaction) defined
		$selected = 0;
		if (in_array($obj->rowid, $arrayofselected)) {
			$selected = 1;
		}
		print '<input id="cb'.$obj->rowid.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$obj->rowid.'"'.($selected ? ' checked="checked"' : '').'>';
	}
	print '</td>';
	if (!$i) {
		$totalarray['nbfield']++;
	}

	print '</tr>'."\n";

	$i++;
}
print '</table>';
print '</div>';

print '</div>';

// End of page
llxFooter();
$db->close();
