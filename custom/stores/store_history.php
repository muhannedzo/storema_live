<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2023 SuperAdmin
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
 *   	\file       branch_card.php
 *		\ingroup    stores
 *		\brief      Page to create/edit/view branch
 */

//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB', '1');				// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER', '1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC', '1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN', '1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION', '1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION', '1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK', '1');				// Do not check CSRF attack (test on referer + on token).
//if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL', '1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK', '1');				// Do not check style html tag into posted data
//if (! defined('NOREQUIREMENU'))            define('NOREQUIREMENU', '1');				// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))            define('NOREQUIREHTML', '1');				// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))            define('NOREQUIREAJAX', '1');       	  	// Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                  define("NOLOGIN", '1');					// If this page is public (can be called outside logged session). This include the NOIPCHECK too.
//if (! defined('NOIPCHECK'))                define('NOIPCHECK', '1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT', 'auto');					// Force lang to a particular value
//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE', 'aloginmodule');	// Force authentication handler
//if (! defined("NOREDIRECTBYMAINTOLOGIN"))  define('NOREDIRECTBYMAINTOLOGIN', 1);		// The main.inc.php does not make a redirect if not logged, instead show simple error message
//if (! defined("FORCECSP"))                 define('FORCECSP', 'none');				// Disable all Content Security Policies
//if (! defined('CSRFCHECK_WITH_TOKEN'))     define('CSRFCHECK_WITH_TOKEN', '1');		// Force use of CSRF protection with tokens even for GET
//if (! defined('NOBROWSERNOTIF'))     		 define('NOBROWSERNOTIF', '1');				// Disable browser notification
//if (! defined('NOSESSION'))     		     define('NOSESSION', '1');				    // Disable session

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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formadmin.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';

require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
dol_include_once('/stores/class/branch.class.php');
dol_include_once('/stores/lib/stores_branch.lib.php');
dol_include_once('/ticket/class/ticket.class.php');
dol_include_once('/User/class/user.class.php');
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';

// Load translation files required by the page
$langs->loadLangs(array("stores@stores", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$lineid   = GETPOST('lineid', 'int');
$third = GETPOST('third', 'int');
// var_dump($third);
$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : str_replace('_', '', basename(dirname(__FILE__)).basename(__FILE__, '.php')); // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$dol_openinpopup = GETPOST('dol_openinpopup', 'aZ09');

// Initialize technical objects
$object = new Branch($db);
if($third){
	$thirdParty = new Societe($db);
	$thirdParty->fetch($third);
	// var_dump($thirdParty);
}
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->stores->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('branchcard', 'globalcard')); // Note that conf->hooks_modules contains array

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$search_all = GETPOST("search_all", 'alpha');
$search = array();
foreach ($object->fields as $key => $val) {
	if (GETPOST('search_'.$key, 'alpha')) {
		$search[$key] = GETPOST('search_'.$key, 'alpha');
	}
}

if (empty($action) && empty($id) && empty($ref)) {
	$action = 'view';
}

include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; 


$enablepermissioncheck = 0;

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



$upload_dir = $conf->stores->multidir_output[isset($object->entity) ? $object->entity : 1].'/branch';


if (empty($conf->stores->enabled)) accessforbidden();
if (!$permissiontoread) accessforbidden();
$socid = 0;
if($permissiontoread > 0 && $permissiontoread != null){
	$socid = $user->socid;
}
if($socid != null){
	$thirdPartyy = new Societe($db);
	$thirdPartyy->fetch($socid);
}


/*
 * Actions
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); 
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
	$error = 0;

	$backurlforlist = dol_buildpath('/stores/branch_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} elseif($third){
				$backtopage = dol_buildpath('/societe/card.php', 1).'?id='.$third;
			} else{
				$backtopage = dol_buildpath('/stores/branch_card.php', 1).'?id='.((!empty($id) && $id > 0) ? $id : '__ID__');
			}
		}
	}

	$triggermodname = 'STORES_BRANCH_MODIFY'; 
	
	include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';

	// Actions when linking object each other
	include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Action to move up and down lines of object
	//include DOL_DOCUMENT_ROOT.'/core/actions_lineupdown.inc.php';

	// Action to build doc
	include DOL_DOCUMENT_ROOT.'/core/actions_builddoc.inc.php';

	if ($action == 'set_thirdparty' && $permissiontoadd) {
		$object->setValueFrom('fk_soc', GETPOST('fk_soc', 'int'), '', '', 'date', '', $user, $triggermodname);
	}
	if ($action == 'classin' && $permissiontoadd) {
		$object->setProject(GETPOST('projectid', 'int'));
	}

	// Actions to send emails
	$triggersendname = 'STORES_BRANCH_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_BRANCH_TO';
	$trackid = 'branch'.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
}


/*
 * View
 *
 * Put here all code to build page
 */

 $form = new Form($db);
 $formfile = new FormFile($db);
 $formproject = new FormProjets($db);
 
 $title = $langs->trans("History");
 $help_url = '';
 llxHeader('', $title, $help_url);
 
 $res = $object->fetch_optionals();

 $head = branchPrepareHead($object);
 print dol_get_fiche_head($head, 'history', $langs->trans("History"), -1, $object->picto);

/////////////////////////////////////////////////////////////////////////////////////////

 $linkback = '<a href="'.$_SERVER['HTTP_REFERER'].'">'.$langs->trans("BackToList").'</a>';

 $morehtmlref = '<div class="refidno">';

 $morehtmlref .= '</div>';


 dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);

 print dol_get_fiche_end();
 ///////////////////////////////////////////////////////////////////////////////////////

 print '<div class="fichecenter">';
 print '<div class="fichehalfleft">';
	print '<table class="border centpercent tableforfield">'."\n";

	$businessPartner = new Societe($db);
	$businessPartner->fetch($object->fk_soc);
		print '<tbody>';
			print '<tr class="field_b_number">';
				print '<td class="titlefield fieldname_b_number">'.$langs->trans('BranchesNumber').'</td>';
				print '<td class="valuefield fieldname_b_number">'.$object->b_number.'</td>';
			print '</tr>';
			print '<tr class="field_street">';
				print '<td class="titlefield fieldname_street">'.$langs->trans('Street').'</td>';
				print '<td class="valuefield fieldname_street">'.$object->street.'</td>';
			print '</tr>';
			print '<tr class="field_house">';
				print '<td class="titlefield fieldname_house">'.$langs->trans('Housenumber').'</td>';
				print '<td class="valuefield fieldname_house">'.$object->house_number.'</td>';
			print '</tr>';
			print '<tr class="field_zip">';
				print '<td class="titlefield fieldname_zip">'.$langs->trans('Zipcode').'</td>';
				print '<td class="valuefield fieldname_zip">'.$object->zip_code.'</td>';
			print '</tr>';
			print '<tr class="field_city">';
				print '<td class="titlefield fieldname_city">'.$langs->trans('City').'</td>';
				print '<td class="valuefield fieldname_city">'.$object->city.'</td>';
			print '</tr>';
			print '<tr class="field_state">';
				print '<td class="titlefield fieldname_state">'.$langs->trans('State').'</td>';
				print '<td class="valuefield fieldname_state">'.$object->state.'</td>';
			print '</tr>';
			print '<tr class="field_country">';
				print '<td class="titlefield fieldname_country">'.$langs->trans('Country').'</td>';
				print '<td class="valuefield fieldname_country">'.$object->country.'</td>';
			print '</tr>';
			print '<tr class="field_phone">';
				print '<td class="titlefield fieldname_phone">'.$langs->trans('Phone').'</td>';
				print '<td class="valuefield fieldname_phone">'.$object->phone.'</td>';
			print '</tr>';
			print '<tr class="field_fk_soc">';
				print '<td class="titlefield fieldname_fk_soc">'.$langs->trans('ThirdParty').'</td>';
				print '<td class="valuefield fieldname_fk_soc">'.$businessPartner->getNomUrl(1).'</td>';
			print '</tr>';
		print '</tbody>';
	print '</table>';
	print '<br>';
	/////////////////////////////////////start last unread tickets table
	$max = 10;

	$sql = "SELECT t.rowid, t.ref, t.track_id, t.fk_soc, third.nom, t.datec, t.subject, t.type_code, t.category_code, t.severity_code, t.fk_statut, t.progress,";
	$sql .= " type.code as type_code, type.label as type_label,";
	$sql .= " category.code as category_code, category.label as category_label,";
	$sql .= " severity.code as severity_code, severity.label as severity_label,";
	$sql .= " te.fk_store, t.fk_user_assign";
	$sql .= " FROM ".MAIN_DB_PREFIX."ticket as t";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_ticket_type as type ON type.code=t.type_code";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe as third ON third.rowid=t.fk_soc";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_ticket_category as category ON category.code=t.category_code";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_ticket_severity as severity ON severity.code=t.severity_code";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."ticket_extrafields as te ON te.fk_object=t.rowid";
	$sql .= " WHERE t.fk_statut != 8 and te.fk_store = ".$object->id;
	
	$object1 = new Ticket($db);
	$sql .= $db->order("t.datec", "DESC");
	$sql .= $db->plimit($max, 0);

	$result = $db->query($sql);

	if ($result) {
		$num = $db->num_rows($result);

		$i = 0;

		$transRecordedType = $langs->trans("Open Call Ticket", $max);

		print '<div class="div-table-responsive-no-min">';
		print '<table class="noborder centpercent">';
		print '<tr class="liste_titre"><th colspan="1">'.$transRecordedType.'</th>';
		print '<th colspan="6">'.$object->b_number.'</th>';
		print '</tr>';
		if ($num > 0) {
			while ($i < $num) {
				$objp = $db->fetch_object($result);
				$object1->id = $objp->rowid;
				$object1->ref = $objp->ref;
				$object1->track_id = $objp->track_id;
				$object1->fk_statut = $objp->fk_statut;
				$object1->progress = $objp->progress;
				$object1->subject = $objp->subject;
				$object1->fk_soc = $objp->fk_soc;
				$object1->fk_store = $objp->fk_store;
				$object1->fk_user_assign = $objp->fk_user_assign;
				
				$store = new Branch($db);
				$store->fetch($objp->fk_store);
				$user = new User($db);
				$user->fetch($objp->fk_user_assign);

				print '<tr class="oddeven">';

				// Ref
				print '<td class="nowraponall">';
				print $object1->getNomUrl(1);
				print "</td>\n";

				// Creation date
				print '<td class="left">';
				print date('d.m.y', $db->jdate($objp->datec));
				print "</td>";

				// Subject
				print '<td class="nowrap">';
				print $objp->subject;
				print "</td>\n";

				// Category
				print '<td class="nowrap">';
				if (!empty($obp->category_code)) {
					$s = $langs->getLabelFromKey($db, 'TicketCategoryShort'.$objp->category_code, 'c_ticket_category', 'code', 'label', $objp->category_code);
					print '<span title="'.dol_escape_htmltag($s).'">'.$s.'</span>';
				}
				print "</td>";

				// // Status
				print '<td class="nowraponall left" colspan="1">';
				print $object1->getLibStatut(5);
				print "</td>";

				print "</tr>\n";

				print '<tr class="oddeven">';

					// Severity
					print '<td class="nowrap">';
					$s = $langs->getLabelFromKey($db, 'TicketSeverityShort'.$objp->severity_code, 'c_ticket_severity', 'code', 'label', $objp->severity_code);
					print '<span title="'.dol_escape_htmltag($s).'">'.$s.'</span>';
					print "</td>";
					// // Assigned User
					print '<td class="nowraponall" colspan="4">';
					print $objp->fk_user_assign ? $user->getNomUrl(1) : "";
					print "</td>\n";

				print "</tr>\n";

				$i++;
			}

			$db->free($result);
		} else {
			print '<tr><td colspan="6"><span class="opacitymedium">'.$langs->trans('No Open Tickets').'</span></td></tr>';
		}

		print "</table>";
		print '</div>';

		print '<br>';
	} else {
		dol_print_error($db);
	}
	/////////////////////////////////////start last tickets table
	$max = 10;

	$sql = "SELECT t.rowid, t.ref, t.track_id, t.fk_soc, third.nom, t.datec, t.subject, t.type_code, t.category_code, t.severity_code, t.fk_statut, t.progress,";
	$sql .= " type.code as type_code, type.label as type_label,";
	$sql .= " category.code as category_code, category.label as category_label,";
	$sql .= " severity.code as severity_code, severity.label as severity_label,";
	$sql .= " te.fk_store";
	$sql .= " FROM ".MAIN_DB_PREFIX."ticket as t";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_ticket_type as type ON type.code=t.type_code";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe as third ON third.rowid=t.fk_soc";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_ticket_category as category ON category.code=t.category_code";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_ticket_severity as severity ON severity.code=t.severity_code";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."ticket_extrafields as te ON te.fk_object=t.rowid";
	$sql .= " WHERE t.fk_statut != 8 and te.fk_store = ".$object->id;
	
	$object1 = new Ticket($db);
	$sql .= $db->order("t.datec", "DESC");
	$sql .= $db->plimit($max, 0);

	$result = $db->query($sql);

	if ($result) {
		$num = $db->num_rows($result);

		$i = 0;

		$transRecordedType = $langs->trans("Last Call Ticket", $max);

		print '<div class="div-table-responsive-no-min">';
		print '<table class="noborder centpercent">';
		print '<tr class="liste_titre"><th colspan="1">'.$transRecordedType.'</th>';
		print '<th colspan="5">'.$object->b_number.'</th>';
		print '</tr>';
		if ($num > 0) {
			while ($i < $num) {
				$objp = $db->fetch_object($result);
				$object1->id = $objp->rowid;
				$object1->ref = $objp->ref;
				$object1->track_id = $objp->track_id;
				$object1->fk_statut = $objp->fk_statut;
				$object1->progress = $objp->progress;
				$object1->subject = $objp->subject;
				$object1->fk_soc = $objp->fk_soc;
				$object1->fk_store = $objp->fk_store;
				
				$store = new Branch($db);
				$store->fetch($objp->fk_store);
				$third = new Societe($db);
				$third->fetch($objp->fk_soc);

				print '<tr class="oddeven">';

				// Ref
				print '<td class="nowraponall">';
				print $object1->getNomUrl(1);
				print "</td>\n";

				// Subject
				print '<td class="nowrap">';
				print $objp->subject;
				print "</td>\n";

				// Category
				print '<td class="nowrap">';
				if (!empty($obp->category_code)) {
					$s = $langs->getLabelFromKey($db, 'TicketCategoryShort'.$objp->category_code, 'c_ticket_category', 'code', 'label', $objp->category_code);
					print '<span title="'.dol_escape_htmltag($s).'">'.$s.'</span>';
				}
				print "</td>";

				// Severity
				print '<td class="nowrap">';
				$s = $langs->getLabelFromKey($db, 'TicketSeverityShort'.$objp->severity_code, 'c_ticket_severity', 'code', 'label', $objp->severity_code);
				print '<span title="'.dol_escape_htmltag($s).'">'.$s.'</span>';
				print "</td>";

				// Creation date
				print '<td class="left">';
				print date('d.m.y', $db->jdate($objp->datec));
				print "</td>";

				print '<td class="nowraponall right">';
				print $object1->getLibStatut(5);
				print "</td>";

				print "</tr>\n";
				$i++;
			}

			$db->free($result);
		} else {
			print '<tr><td colspan="6"><span class="opacitymedium">'.$langs->trans('No Closed Tickets').'</span></td></tr>';
		}

		print "</table>";
		print '</div>';

		print '<br>';
	} else {
		dol_print_error($db);
	}
 print '</div>';
 //////////////////////////////////////////////////////
 print '<div class="fichehalfright">';
 //////////////////////////////////start pie chart
	$currentDate = new DateTime();
	// var_dump($currentDate->format('Y-m-d'));
	$currentDay = $currentDate->format('Y-m-d');
	$currentYear = $currentDate->format('Y');
	$startyear = $currentYear;
	$dateString = 'From '. $startyear . '-01-01 To '. $currentDay;
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["date-from"]) && isset($_POST["date-to"])) {
		$datefrom = $_POST["date-from"];
		$dateto = $_POST["date-to"];
		$dateString = 'From '.$_POST["date-from"].' to '.$_POST["date-to"];
	}
	$stringtoshow = '<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery("#idsubimgDOLUSERCOOKIE_ticket_by_status").click(function() {
				jQuery("#idfilterDOLUSERCOOKIE_ticket_by_status").toggle();
			});
		});
		</script>';
	$stringtoshow .= '<div class="center hideobject" id="idfilterDOLUSERCOOKIE_ticket_by_status">'; // hideobject is to start hidden
	$stringtoshow .= '<form class="flat formboxfilter" method="POST" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'">';
	$stringtoshow .= '<input type="hidden" name="token" value="'.newToken().'">';
	$stringtoshow .= '<input type="hidden" name="action" value="refresh">';
	$stringtoshow .= '<input type="hidden" name="DOL_AUTOSET_COOKIE" value="DOLUSERCOOKIE_ticket_by_status:year,shownb,showtot">';
	$stringtoshow .= $langs->trans("From").' <input class="flat" size="4" type="date" name="date-from">';
	$stringtoshow .= $langs->trans("To").' <input class="flat" size="4" type="date" name="date-to">';
	$stringtoshow .= '<input type="image" alt="'.$langs->trans("Refresh").'" src="'.img_picto($langs->trans("Refresh"), 'refresh.png', '', '', 1).'">';
	$stringtoshow .= '</form>';
	$stringtoshow .= '</div>';
	$stateSql = "SELECT tt.label as 'te', count(*) FROM ".MAIN_DB_PREFIX."ticket as t
					LEFT JOIN ".MAIN_DB_PREFIX."ticket_extrafields as te on t.rowid = te.fk_object
					LEFT JOIN ".MAIN_DB_PREFIX."c_ticket_type as tt on t.type_code = tt.code
					LEFT JOIN ".MAIN_DB_PREFIX."c_ticket_severity as ts on t.severity_code = ts.code
				WHERE te.fk_store = ".$object->id;
	if(isset($datefrom) && isset($dateto)){
		$stateSql .= " AND CAST(t.datec AS DATE) BETWEEN CAST('".$datefrom."' AS DATE) AND CAST('".$dateto."' AS DATE)";
	}else{
		$stateSql .= " AND CAST(t.datec AS DATE) BETWEEN CAST('".$startyear."-01-01' AS DATE) AND CAST('".$currentDay."' AS DATE)";
	}
	$stateSql .= " AND t.type_code != 'Service' GROUP BY tt.label";
	$stateSql .= " UNION";
	$stateSql .= " SELECT ts.code as 'te', count(*) FROM ".MAIN_DB_PREFIX."ticket as t
					LEFT JOIN ".MAIN_DB_PREFIX."ticket_extrafields as te on t.rowid = te.fk_object
					LEFT JOIN ".MAIN_DB_PREFIX."c_ticket_type as tt on t.type_code = tt.code
					LEFT JOIN ".MAIN_DB_PREFIX."c_ticket_severity as ts on t.severity_code = ts.code
				WHERE te.fk_store = ".$object->id;
	if(isset($datefrom) && isset($dateto)){
		$stateSql .= " AND CAST(t.datec AS DATE) BETWEEN CAST('".$datefrom."' AS DATE) AND CAST('".$dateto."' AS DATE)";
	}else{
		$stateSql .= " AND CAST(t.datec AS DATE) BETWEEN CAST('".$startyear."-01-01' AS DATE) AND CAST('".$currentDay."' AS DATE)";
	}
	$stateSql .= " AND t.type_code = 'Service' GROUP BY ts.code";
	// var_dump($stateSql);
	$states = $db->query($stateSql)->fetch_all();
	$num = $db->num_rows($states);
	// var_dump($num);
	$ss = array();
	foreach($states as $elem){
		$ss[] = array($elem[0], $elem[1]);
	}
	$thirdpartygraph = '<div class="div-table-responsive-no-min">';
	$thirdpartygraph .= '<table class="noborder nohover centpercent">'."\n";
	$thirdpartygraph .= '<tr class="liste_titre"><th>'.$langs->trans("Store History").'</th><th colspan="2">'.$object->b_number.'</th><th>Datum: '.$dateString.''.img_picto('', 'filter.png', 'id="idsubimgDOLUSERCOOKIE_ticket_by_status" class="linkobject"').'</th></tr>';
	$thirdpartygraph .= '<tr><td  colspan="4" class="center">';
	$thirdpartygraph .= $stringtoshow;


	$thirdpartygraph .= '<tr><td class="center" colspan="4">';
	if($num > 0){
	include_once DOL_DOCUMENT_ROOT.'/core/class/dolgraph.class.php';
	$dolgraph = new DolGraph();
	$dolgraph->SetData($ss);
	$dolgraph->setShowLegend(2);
	$dolgraph->setShowPercent(1);
	$dolgraph->SetType(array('pie'));
	$dolgraph->setHeight('200');
	$dolgraph->draw('idgraphthirdparties');
	$thirdpartygraph .= $dolgraph->show();
	$thirdpartygraph .= '</td></tr>'."\n";
	} else {
		$thirdpartygraph .= '<span class="left">'.$langs->trans('No Tickets').'</span>';
	}
	$thirdpartygraph .= '<tr class="liste_total"><td colspan="3"></td><td class="right">';
	$thirdpartygraph .= '</td></tr>';
	$thirdpartygraph .= '</table>';
	$thirdpartygraph .= '</div>';

	print $thirdpartygraph;
	/////////////////////////////end pie chart////////////////////////

	print '<br>';

	///////////////////////////////start last call project history table/////////////////////////

	$thirdparty_static = new Project($db);
	$sql = "SELECT DISTINCT p.ref, p.rowid, p.title, p.dateo, p.datee, p.fk_statut, t.datec";
	$sql .= " FROM ".MAIN_DB_PREFIX."projet p";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."categorie_project as cp on p.rowid = cp.fk_project";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."ticket t on t.fk_project = p.rowid";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."ticket_extrafields te on te.fk_object = t.rowid";
	$sql .= " WHERE cp.fk_categorie = 22 AND te.fk_store = ".$object->id;
	$sql .= " ORDER BY t.datec DESC;";

	$lastmodified="";
	$result = $db->query($sql);

	$lastmodified = "\n<!-- last thirdparties modified -->\n";
	$lastmodified .= '<div class="div-table-responsive-no-min">';
	$lastmodified .= '<table class="noborder centpercent">';
	
	$lastmodified .= '<tr class="liste_titre"><th colspan="5">Last call history by project</th>';
	$lastmodified .= '</tr>'."\n";

	if ($result) {
		$num = $db->num_rows($result);
		
		for ($i = 0; $i < $num; $i++) {

				$objp = $db->fetch_object($result);
				$thirdparty_static->ref = $objp->ref;
				$thirdparty_static->id = $objp->rowid;
				$thirdparty_static->title = $objp->title;
				$thirdparty_static->date_start = $db->jdate($objp->dateo);
				$thirdparty_static->date_end = $db->jdate($objp->datee);
				$thirdparty_static->status = $objp->fk_statut;
				$thirdparty_static->ticket_date = $db->jdate($objp->datec);
				
				$lastmodified .= '<tr class="oddeven">';
				// Name
				$lastmodified .= '<td class="nowrap tdoverflowmax200">';
				$lastmodified .= $thirdparty_static->getNomUrl(1, '', 0, '', '-', 0, -1, 'nowraponall');
				$lastmodified .= "</td>\n";
				
				// Label
				$lastmodified .= '<td class="">';
				$lastmodified .= $thirdparty_static->title;
				$lastmodified .= '</td>';
				
				// date start
				$lastmodified .= '<td class="center">';
				$lastmodified .= dol_print_date($thirdparty_static->ticket_date, 'day', 'tzuserrel');;
				$lastmodified .= '</td>';

				// date end
				$lastmodified .= '<td class="center">';
				$lastmodified .= $thirdparty_static->date_end ? dol_print_date($thirdparty_static->date_end, 'day', 'tzuserrel') : '';
				$lastmodified .= '</td>';

				// status
				$lastmodified .= '<td class="center">';
				$lastmodified .= $thirdparty_static->getLibStatut(2);
				$lastmodified .= '</td>';
		}
		$db->free($result);

		$lastmodified .= "</table>\n";
		$lastmodified .= '</div>';
		$lastmodified .= "<!-- End last thirdparties modified -->\n";
	} else {
		dol_print_error($db);
	}
	print $lastmodified;
	//////////////////////////////////end last call project history table/////////////////

	print '<br>';

	/////////////////////////////start open projects//////////////////	
	
	$project_static = new Project($db);
	$ssql = "SELECT DISTINCT p.rowid, p.ref, p.fk_statut, p.title 
			FROM llx_projet as p
				LEFT JOIN llx_categorie_project as cp on p.rowid = cp.fk_project
				LEFT JOIN llx_ticket as t on p.rowid = t.fk_project
				LEFT JOIN llx_ticket_extrafields as te on t.rowid = te.fk_object
			WHERE cp.fk_categorie = 22 and p.fk_soc = ".$object->thirdparty->id." and te.fk_store = ".$object->id.";";
	$lastmodified="";
	$result = $db->query($ssql);
	$num = $db->num_rows($result);

	$lastmodified = "\n<!-- last thirdparties modified -->\n";
	$lastmodified .= '<div class="div-table-responsive-no-min">';
	$lastmodified .= '<table class="noborder centpercent">';
	
	$lastmodified .= '<tr class="liste_titre">';
	$lastmodified .= '<th colspan="2">Open Rollouts<span class="badge marginleftonlyshort">'.$num.'</span></th>';
	$lastmodified .= '</tr>'."\n";

	if ($result) {
		$num = $db->num_rows($result);
		
		for ($i = 0; $i < $num; $i++) {

				$objp = $db->fetch_object($result);
				$project_static->ref = $objp->ref;
				$project_static->id = $objp->rowid;
				$project_static->status = $objp->fk_statut;
				$project_static->title = $objp->title;
				
				$lastmodified .= '<tr class="oddeven">';
				// Ref
				$lastmodified .= '<td class="nowrap tdoverflowmax200">';
				$lastmodified .= $project_static->getNomUrl(1, '', 0, '', '-', 0, -1, 'nowraponall').'<br><span class="opacitymedium">'.dol_trunc($project_static->title, 24).'</span>';
				$lastmodified .= "</td>\n";
				
				// Status
				$lastmodified .= '<td class="right">';
				$lastmodified .= $project_static->getLibStatut(3);
				$lastmodified .= '</td></tr>';
				
		}
		$db->free($result);

		$lastmodified .= "</table>\n";
		$lastmodified .= '</div>';
		$lastmodified .= "<!-- End last thirdparties modified -->\n";
	} else {
		dol_print_error($db);
	}
	print $lastmodified;
	///////////////////////////////end open projects/////////////////////////////

	print '<br>';

	///////////////////////////////project history table/////////////////////////

	$thirdparty_static = new Project($db);
	$sql = "SELECT DISTINCT p.ref, p.rowid, p.title, p.dateo, p.datee, p.fk_statut";
	$sql .= " FROM ".MAIN_DB_PREFIX."projet p";
	$sql .= " LEFT JOIN  ".MAIN_DB_PREFIX."categorie_project as cp on p.rowid = cp.fk_project";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."ticket t on t.fk_project = p.rowid";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."ticket_extrafields te on te.fk_object = t.rowid";
	$sql .= " WHERE cp.fk_categorie = 22 AND te.fk_store = ".$object->id;
	$sql .= " ORDER BY p.dateo DESC";

	$lastmodified="";
	$result = $db->query($sql);
	$transRecordedType = $langs->trans("LastModifiedThirdParties", $max);

	$lastmodified = "\n<!-- last thirdparties modified -->\n";
	$lastmodified .= '<div class="div-table-responsive-no-min">';
	$lastmodified .= '<table class="noborder centpercent">';
	
	$lastmodified .= '<tr class="liste_titre"><th colspan="5">Project history</th>';
	$lastmodified .= '</tr>'."\n";

	if ($result) {
		$num = $db->num_rows($result);

		for ($i = 0; $i < $num; $i++) {

				$objp = $db->fetch_object($result);
				$thirdparty_static->ref = $objp->ref;
				$thirdparty_static->id = $objp->rowid;
				$thirdparty_static->title = $objp->title;
				$thirdparty_static->date_start = $db->jdate($objp->dateo);
				$thirdparty_static->date_end = $db->jdate($objp->datee);
				$thirdparty_static->status = $objp->fk_statut;

				$lastmodified .= '<tr class="oddeven">';

				// Name
				$lastmodified .= '<td class="nowrap tdoverflowmax200">';
				$lastmodified .= $thirdparty_static->getNomUrl(1, '', 0, '', '-', 0, -1, 'nowraponall');
				$lastmodified .= "</td>\n";

				// Label
				$lastmodified .= '<td class="">';
				$lastmodified .= $thirdparty_static->title;
				$lastmodified .= '</td>';
				
				// date start
				$lastmodified .= '<td class="center">';
				$lastmodified .= dol_print_date($thirdparty_static->date_start, 'day', 'tzuserrel');;
				$lastmodified .= '</td>';
				
				// date end
				$lastmodified .= '<td class="center">';
				$lastmodified .= dol_print_date($thirdparty_static->date_end, 'day', 'tzuserrel');
				$lastmodified .= '</td>';
				
				// status
				$lastmodified .= '<td class="center">';
				$lastmodified .= $thirdparty_static->getLibStatut(2);
				$lastmodified .= '</td>';
		}
		$db->free($result);

		$lastmodified .= "</table>\n";
		$lastmodified .= '</div>';
		$lastmodified .= "<!-- End last thirdparties modified -->\n";
	} else {
		dol_print_error($db);
	}
	print $lastmodified;
	//////////////////////////////////end project history table/////////////////
 print '</div>';

