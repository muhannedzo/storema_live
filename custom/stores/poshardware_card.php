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
 *   	\file       poshardware_card.php
 *		\ingroup    stores
 *		\brief      Page to create/edit/view poshardware
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
dol_include_once('/stores/class/poshardware.class.php');
dol_include_once('/stores/lib/stores_poshardware.lib.php');

include_once('compress.php');

// Load translation files required by the page
$langs->loadLangs(array("stores@stores", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$lineid   = GETPOST('lineid', 'int');
$store = GETPOST('store', 'int');

$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : str_replace('_', '', basename(dirname(__FILE__)).basename(__FILE__, '.php')); // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$dol_openinpopup = GETPOST('dol_openinpopup', 'aZ09');

// Initialize technical objects
$object = new PosHardware($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->stores->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('poshardwarecard', 'globalcard')); // Note that conf->hooks_modules contains array

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

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once.

// There is several ways to check permission.
// Set $enablepermissioncheck to 1 to enable a minimum low level of checks
$enablepermissioncheck = 0;
// if ($enablepermissioncheck) {
// 	$permissiontoread = $user->rights->stores->poshardware->read;
// 	$permissiontoadd = $user->rights->stores->poshardware->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
// 	$permissiontodelete = $user->rights->stores->poshardware->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
// 	$permissionnote = $user->rights->stores->poshardware->write; // Used by the include of actions_setnotes.inc.php
// 	$permissiondellink = $user->rights->stores->poshardware->write; // Used by the include of actions_dellink.inc.php
// } else {
// 	$permissiontoread = 1;
// 	$permissiontoadd = 1; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
// 	$permissiontodelete = 1;
// 	$permissionnote = 1;
// 	$permissiondellink = 1;
// }

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
$upload_dir = $conf->stores->multidir_output[isset($object->entity) ? $object->entity : 1].'/poshardware';

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (isset($object->status) && ($object->status == $object::STATUS_DRAFT) ? 1 : 0);
//restrictedArea($user, $object->element, $object->id, $object->table_element, '', 'fk_soc', 'rowid', $isdraft);
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
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
	$error = 0;

	$backurlforlist = dol_buildpath('/stores/poshardware_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/stores/poshardware_card.php', 1).'?id='.((!empty($id) && $id > 0) ? $id : '__ID__');
			}
		}
	}

	$triggermodname = 'STORES_POSHARDWARE_MODIFY'; // Name of trigger action code to execute when we modify record

	// Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_setdraft, confirm_reopen
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
	$triggersendname = 'STORES_POSHARDWARE_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_POSHARDWARE_TO';
	$trackid = 'poshardware'.$object->id;
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

$title = $langs->trans("PosHardware");
$help_url = '';
llxHeader('', $title, $help_url);

// Example : Adding jquery code
// print '<script type="text/javascript">
// jQuery(document).ready(function() {
// 	function init_myfunc()
// 	{
// 		jQuery("#myid").removeAttr(\'disabled\');
// 		jQuery("#myid").attr(\'disabled\',\'disabled\');
// 	}
// 	init_myfunc();
// 	jQuery("#mybutton").click(function() {
// 		init_myfunc();
// 	});
// });
// </script>';


// Part to create
if ($action == 'create') {
	if (empty($permissiontoadd)) {
		accessforbidden($langs->trans('NotEnoughPermissions'), 0, 1);
		exit;
	}

	$query = "SELECT * FROM llx_stores_branch";
	$resStore = $db->query($query)->fetch_all();

	if (!empty($conf->use_javascript_ajax)) {
		print "\n".'<script type="text/javascript">';
		print '$(document).ready(function () {
			$("#serialNumber").change(function() {
				document.getElementById("ref").value = document.getElementById("serialNumber").value;
			});
			document.getElementById("ref").value = document.getElementById("serialNumber").value;
		});';
		print '</script>'."\n";
	}

	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("PosHardware")), '', 'object_'.$object->picto);

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';
	}

	print dol_get_fiche_head(array(), '');

	// Set some default values
	//if (! GETPOSTISSET('fieldname')) $_POST['fieldname'] = 'myvalue';

	print '<table class="border centpercent tableforfieldcreate">'."\n";

	// Common attributes
	// include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_add.tpl.php';

	// Other attributes
	// include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_add.tpl.php';

	/////////////////////Muhannad Code///////////////////////////////
	
	// Store
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="storeId">'.$langs->trans("Store").'</label></td><td>';
	print '<select name="storeId" id="storeId">
				<option value="">--Please choose an option--</option>';
					foreach($resStore as $elem){						
						if($store && $store == $elem[0]){						
							print '<option value="'.$elem[0].'" selected>'.$elem[1].'</option>';
						}else{
							print '<option value="'.$elem[0].'">'.$elem[1].'</option>';
						}				
					}
	print '</select>'."\n";

	// type
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="type">'.$langs->trans("Type").'</label></td><td>';
	print '<input name="type" id="type" class="minwidth200"  autofocus="autofocus"></td></tr>'."\n";

	// manufacturer
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="manufacturer">'.$langs->trans("Manufacturer").'</label></td><td>';
	print '<input name="manufacturer" id="manufacturer" class="minwidth200"  autofocus="autofocus"></td></tr>'."\n";

	// device
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="device">'.$langs->trans("Device").'</label></td><td>';
	print '<input name="device" id="device" class="minwidth200"  autofocus="autofocus"></td></tr>'."\n";

	// standort
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="standort">'.$langs->trans("Standort").'</label></td><td>';
	print '<input name="standort" id="standort" class="minwidth200"  autofocus="autofocus"></td></tr>'."\n";

	// serialNumber
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="serialNumber">'.$langs->trans("Serial Number").'</label></td><td>';
	print '<input name="serialNumber" id="serialNumber" class="minwidth200"  autofocus="autofocus"></td></tr>'."\n";

	// mac
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="mac">'.$langs->trans("MAC").'</label></td><td>';
	print '<input name="mac" id="mac" class="minwidth200"  autofocus="autofocus"></td></tr>'."\n";

	// macWlan
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="macWlan">'.$langs->trans("MAC WLAN").'</label></td><td>';
	print '<input name="macWlan" id="macWlan" class="minwidth200"  autofocus="autofocus"></td></tr>'."\n";

	// inventorNr
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="inventorNr">'.$langs->trans("inventor Nr").'</label></td><td>';
	print '<input name="inventorNr" id="inventorNr" class="minwidth200"  autofocus="autofocus"></td></tr>'."\n";

	// posNo
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="posNo">'.$langs->trans("POS No").'</label></td><td>';
	print '<input name="posNo" id="posNo" class="minwidth200"  autofocus="autofocus"></td></tr>'."\n";

	// keg
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="keg">'.$langs->trans("KEG").'</label></td><td>';
	print '<input name="keg" id="keg" class="minwidth200"  autofocus="autofocus"></td></tr>'."\n";

	// info
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="info">'.$langs->trans("info").'</label></td><td>';
	print '<input name="info" id="info" class="minwidth200"  autofocus="autofocus"></td></tr>'."\n";

	// extra
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="extra">'.$langs->trans("Extra").'</label></td><td>';
	print '<input name="extra" id="extra" class="minwidth200"  autofocus="autofocus"></td></tr>'."\n";
	
	// status
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="status">'.$langs->trans("Status").'</label></td><td>';
	print '<select name="status" id="status">
				<option value="">--Please choose an option--</option>
				<option value="0">Draft</option>
				<option value="1">Validated</option>
				<option value="9">Canceled</option>
			</select>'."\n";

	// ref
	print '<input type="hidden" name="ref" id="ref"></td></tr>'."\n";
	

	/////////////////////End Muhannad Code///////////////////////////


	print '</table>'."\n";

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel("Create");

	print '</form>';

	//dol_set_focus('input[name="ref"]');
}

// Part to edit record
if (($id || $ref) && $action == 'edit') {
	print load_fiche_titre($langs->trans("PosHardware"), '', 'object_'.$object->picto);

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';
	}

	print dol_get_fiche_head();

	print '<table class="border centpercent tableforfieldedit">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_edit.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_edit.tpl.php';

	print '</table>';

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel();

	print '</form>';
}

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {
	$res = $object->fetch_optionals();

	$head = poshardwarePrepareHead($object);
	print dol_get_fiche_head($head, 'card', $langs->trans("PosHardware"), -1, $object->picto);

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeletePosHardware'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
	}
	// Confirmation to delete line
	if ($action == 'deleteline') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&lineid='.$lineid, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_deleteline', '', 0, 1);
	}

	// Clone confirmation
	if ($action == 'clone') {
		// Create an array for form
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('ToClone'), $langs->trans('ConfirmCloneAsk', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
	}

	// Confirmation of action xxxx (You can use it for xxx = 'close', xxx = 'reopen', ...)
	if ($action == 'xxx') {
		$text = $langs->trans('ConfirmActionPosHardware', $object->ref);
		/*if (! empty($conf->notification->enabled))
		{
			require_once DOL_DOCUMENT_ROOT . '/core/class/notify.class.php';
			$notify = new Notify($db);
			$text .= '<br>';
			$text .= $notify->confirmMessage('POSHARDWARE_CLOSE', $object->socid, $object);
		}*/

		$formquestion = array();
		/*
		$forcecombo=0;
		if ($conf->browser->name == 'ie') $forcecombo = 1;	// There is a bug in IE10 that make combo inside popup crazy
		$formquestion = array(
			// 'text' => $langs->trans("ConfirmClone"),
			// array('type' => 'checkbox', 'name' => 'clone_content', 'label' => $langs->trans("CloneMainAttributes"), 'value' => 1),
			// array('type' => 'checkbox', 'name' => 'update_prices', 'label' => $langs->trans("PuttingPricesUpToDate"), 'value' => 1),
			// array('type' => 'other',    'name' => 'idwarehouse',   'label' => $langs->trans("SelectWarehouseForStockDecrease"), 'value' => $formproduct->selectWarehouses(GETPOST('idwarehouse')?GETPOST('idwarehouse'):'ifone', 'idwarehouse', '', 1, 0, 0, '', 0, $forcecombo))
		);
		*/
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('XXX'), $text, 'confirm_xxx', $formquestion, 0, 1, 220);
	}

	// Call Hook formConfirm
	$parameters = array('formConfirm' => $formconfirm, 'lineid' => $lineid);
	$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	if (empty($reshook)) {
		$formconfirm .= $hookmanager->resPrint;
	} elseif ($reshook > 0) {
		$formconfirm = $hookmanager->resPrint;
	}

	// Print form confirm
	print $formconfirm;


	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="'.dol_buildpath('/stores/poshardware_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';

	$morehtmlref = '<div class="refidno">';
	/*
	 // Ref customer
	 $morehtmlref.=$form->editfieldkey("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', 0, 1);
	 $morehtmlref.=$form->editfieldval("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', null, null, '', 1);
	 // Thirdparty
	 $morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . (is_object($object->thirdparty) ? $object->thirdparty->getNomUrl(1) : '');
	 // Project
	 if (! empty($conf->project->enabled)) {
	 $langs->load("projects");
	 $morehtmlref .= '<br>'.$langs->trans('Project') . ' ';
	 if ($permissiontoadd) {
	 //if ($action != 'classify') $morehtmlref.='<a class="editfielda" href="' . $_SERVER['PHP_SELF'] . '?action=classify&token='.newToken().'&id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetProject')) . '</a> ';
	 $morehtmlref .= ' : ';
	 if ($action == 'classify') {
	 //$morehtmlref .= $form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'projectid', 0, 0, 1, 1);
	 $morehtmlref .= '<form method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'">';
	 $morehtmlref .= '<input type="hidden" name="action" value="classin">';
	 $morehtmlref .= '<input type="hidden" name="token" value="'.newToken().'">';
	 $morehtmlref .= $formproject->select_projects($object->socid, $object->fk_project, 'projectid', $maxlength, 0, 1, 0, 1, 0, 0, '', 1);
	 $morehtmlref .= '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
	 $morehtmlref .= '</form>';
	 } else {
	 $morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'none', 0, 0, 0, 1);
	 }
	 } else {
	 if (! empty($object->fk_project)) {
	 $proj = new Project($db);
	 $proj->fetch($object->fk_project);
	 $morehtmlref .= ': '.$proj->getNomUrl();
	 } else {
	 $morehtmlref .= '';
	 }
	 }
	 }*/
	$morehtmlref .= '</div>';


	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent tableforfield">'."\n";

	// Common attributes
	//$keyforbreak='fieldkeytoswitchonsecondcolumn';	// We change column just before this field
	//unset($object->fields['fk_project']);				// Hide field already shown in banner
	//unset($object->fields['fk_soc']);					// Hide field already shown in banner
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_view.tpl.php';

	// Other attributes. Fields from hook formObjectOptions and Extrafields.
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_view.tpl.php';

	print '</table>';
	print '</div>';
	print '</div>';

	print '<div class="clearboth"></div>';

	print dol_get_fiche_end();


	/*
	 * Lines
	 */

	if (!empty($object->table_element_line)) {
		// Show object lines
		$result = $object->getLinesArray();

		print '	<form name="addproduct" id="addproduct" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.(($action != 'editline') ? '' : '#line_'.GETPOST('lineid', 'int')).'" method="POST">
		<input type="hidden" name="token" value="' . newToken().'">
		<input type="hidden" name="action" value="' . (($action != 'editline') ? 'addline' : 'updateline').'">
		<input type="hidden" name="mode" value="">
		<input type="hidden" name="page_y" value="">
		<input type="hidden" name="id" value="' . $object->id.'">
		';

		if (!empty($conf->use_javascript_ajax) && $object->status == 0) {
			include DOL_DOCUMENT_ROOT.'/core/tpl/ajaxrow.tpl.php';
		}

		print '<div class="div-table-responsive-no-min">';
		if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline')) {
			print '<table id="tablelines" class="noborder noshadow" width="100%">';
		}

		if (!empty($object->lines)) {
			$object->printObjectLines($action, $mysoc, null, GETPOST('lineid', 'int'), 1);
		}

		// Form to add new line
		if ($object->status == 0 && $permissiontoadd && $action != 'selectlines') {
			if ($action != 'editline') {
				// Add products/services form

				$parameters = array();
				$reshook = $hookmanager->executeHooks('formAddObjectLine', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
				if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
				if (empty($reshook))
					$object->formAddObjectLine(1, $mysoc, $soc);
			}
		}

		if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline')) {
			print '</table>';
		}
		print '</div>';

		print "</form>\n";
	}


	// Buttons for actions

	if ($action != 'presend' && $action != 'editline') {
		print '<div class="tabsAction">'."\n";
		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		if ($reshook < 0) {
			setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
		}

		if (empty($reshook)) {
			// Send
			// if (empty($user->socid)) {
			// 	print dolGetButtonAction($langs->trans('SendMail'), '', 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=presend&mode=init&token='.newToken().'#formmailbeforetitle');
			// }

			// Back to draft
			// if ($object->status == $object::STATUS_VALIDATED) {
			// 	print dolGetButtonAction($langs->trans('SetToDraft'), '', 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=confirm_setdraft&confirm=yes&token='.newToken(), '', $permissiontoadd);
			// }

			print dolGetButtonAction($langs->trans('Modify'), '', 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=edit&token='.newToken(), '', $permissiontoadd);

			// Validate
			if ($object->status == $object::STATUS_DRAFT) {
				if (empty($object->table_element_line) || (is_array($object->lines) && count($object->lines) > 0)) {
					print dolGetButtonAction($langs->trans('Validate'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_validate&confirm=yes&token='.newToken(), '', $permissiontoadd);
				} else {
					$langs->load("errors");
					print dolGetButtonAction($langs->trans("ErrorAddAtLeastOneLineFirst"), $langs->trans("Validate"), 'default', '#', '', 0);
				}
			}

			// Clone
			// print dolGetButtonAction($langs->trans('ToClone'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.(!empty($object->socid)?'&socid='.$object->socid:'').'&action=clone&token='.newToken(), '', $permissiontoadd);

			/*
			if ($permissiontoadd) {
				if ($object->status == $object::STATUS_ENABLED) {
					print dolGetButtonAction($langs->trans('Disable'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=disable&token='.newToken(), '', $permissiontoadd);
				} else {
					print dolGetButtonAction($langs->trans('Enable'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=enable&token='.newToken(), '', $permissiontoadd);
				}
			}
			if ($permissiontoadd) {
				if ($object->status == $object::STATUS_VALIDATED) {
					print dolGetButtonAction($langs->trans('Cancel'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=close&token='.newToken(), '', $permissiontoadd);
				} else {
					print dolGetButtonAction($langs->trans('Re-Open'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=reopen&token='.newToken(), '', $permissiontoadd);
				}
			}
			*/

			// Delete (need delete permission, or if draft, just need create/modify permission)
			print dolGetButtonAction($langs->trans('Delete'), '', 'delete', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=delete&token='.newToken(), '', $permissiontodelete || ($object->status == $object::STATUS_DRAFT && $permissiontoadd));
		}
		print '</div>'."\n";
	}


	// Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	if ($action != 'presend') {
		print '<div class="fichecenter"><div class="fichehalfleft">';
		print '<a name="builddoc"></a>'; // ancre

		// $includedocgeneration = 0;

		// // Documents
		// if ($includedocgeneration) {
		// 	$objref = dol_sanitizeFileName($object->ref);
		// 	$relativepath = $objref.'/'.$objref.'.pdf';
		// 	$filedir = $conf->stores->dir_output.'/'.$object->element.'/'.$objref;
		// 	$urlsource = $_SERVER["PHP_SELF"]."?id=".$object->id;
		// 	$genallowed = $permissiontoread; // If you can read, you can build the PDF to read content
		// 	$delallowed = $permissiontoadd; // If you can create/edit, you can remove a file on card
		// 	print $formfile->showdocuments('stores:PosHardware', $object->element.'/'.$objref, $filedir, $urlsource, $genallowed, $delallowed, $object->model_pdf, 1, 0, 0, 28, 0, '', '', '', $langs->defaultlang);
		// }

		// // Show links to link elements
		// $linktoelem = $form->showLinkToObjectBlock($object, null, array('poshardware'));
		// $somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);


		// print '</div><div class="fichehalfright">';

		// $MAXEVENT = 10;

		// $morehtmlcenter = dolGetButtonTitle($langs->trans('SeeAll'), '', 'fa fa-bars imgforviewmode', dol_buildpath('/stores/poshardware_agenda.php', 1).'?id='.$object->id);

		// List of actions on element
		// include_once DOL_DOCUMENT_ROOT.'/core/class/html.formactions.class.php';
		// $formactions = new FormActions($db);
		// $somethingshown = $formactions->showactions($object, $object->element.'@'.$object->module, (is_object($object->thirdparty) ? $object->thirdparty->id : 0), 1, '', $MAXEVENT, '', $morehtmlcenter);

		// print '</div></div>';
		$obj = new Compress();
		print '<form action="" method="POST" enctype="multipart/form-data"><input type="hidden" name="token" value="' . newToken().'">
					<h2>POS Hardware Images</h2>
					<p style="display:flex">
						<input type="file" name="files[]" multiple>
						<br>';
					print'	<input type="submit" name="submit" value="Upload" >
					</p>
				</form>';
				$query = 'SELECT images FROM llx_stores_poshardware WHERE rowid = '.$id;
				$list = $db->query($query)->fetch_row();
				$imagesList = [];
				if($list[0 != null]){
					$imagesList = explode(',', $list[0]);
				}
				$images = [];
				foreach($imagesList as $elem){
					if($elem != ""){
						array_push($images, $elem);
					}
				}
				$dir = DOL_DOCUMENT_ROOT.'/custom/stores/img/pos/';
				if(!is_dir($dir)){
					mkdir($dir);
				}
				// var_dump($images);
				if(isset($_POST['submit'])) {
 
					// Configure upload directory and allowed file types
					$allowed_types = array('jpg', 'png', 'jpeg', 'gif');
					 
					$maxsize = 1024 * 1024;
					
					// Checks if user sent an empty form
					if(!empty(array_filter($_FILES['files']['name']))) {
						
				 
						// Loop through each file in files[] array
						foreach ($_FILES['files']['tmp_name'] as $key => $value) {
							
							$file_tmpname = $_FILES['files']['tmp_name'][$key];
							$file_name = $_FILES['files']['name'][$key];
							$file_size = $_FILES['files']['size'][$key];
							$imageQuality = 80;
							$file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
							
							// Set upload file path
							$filepath = $dir.$file_name;
				 
							// Check file type is allowed or not
							if(in_array(strtolower($file_ext), $allowed_types)) {
								// if ($file_size > $maxsize){
								// 	dol_htmloutput_errors("File size is larger than the allowed limit.");
								// }else{

									if(file_exists($filepath)) {
										$fileN = time().$file_name;
										$filepath = $dir.$fileN;
										$compressedImage = $obj->compress_image($file_tmpname, $filepath, $imageQuality);
										if( $compressedImage) {
											array_push($images, $fileN);
										}else {                    
											dol_htmloutput_errors("Error uploading {$file_name} <br />");
										}
									}else {
										$compressedImage = $obj->compress_image($file_tmpname, $filepath, $imageQuality);
										if($compressedImage) {
											array_push($images,$file_name);
										}else {                    
											dol_htmloutput_errors("Error uploading {$file_name} <br />");
										}
									}
									
								// }        
				 
							}else {
								dol_htmloutput_errors("Error uploading {$file_name} ");
								dol_htmloutput_errors("({$file_ext} file type is not allowed)<br / >");
							}
						}
					}else {
						dol_htmloutput_errors("No files selected.");
					}
					$dbImages = implode(',', $images);
					$sql = 'UPDATE llx_stores_poshardware set images = "'.$dbImages.'" WHERE rowid = '.$id;
					$db->query($sql,0,'ddl');
				}
			//////////////////////List Of images//////////////////////	
			$query = 'SELECT images FROM llx_stores_poshardware WHERE rowid = '.$id;
			$list = $db->query($query)->fetch_row();
			$imagesList = [];
			if($list[0 != null]){
				$imagesList = explode(',', $list[0]);
			}
			print '<table class="noborder" width="100%">';
				print '<tr class="liste_titre"><th colspan="3">'.$langs->trans("Images").'</th></tr>';	
					for($i = 0; $i < count($imagesList); $i++)
						{
							// var_dump($elem);
							if(count($imagesList) > 0){
								$desc = "";
								if(count(explode("|",$imagesList[$i])) > 1){
									$desc = explode("|",$imagesList[$i])[1];
								}
								print '<tr class="oddeven">';
								print '<td><img class="myImg" id="'.$i.'" alt="img" src="./img/pos/'.explode("|",$imagesList[$i])[0].'" width="100" height="100" onclick="ss(this.id);"></td>';
								print '<form action="" method="POST"><input type="hidden" name="token" value="' . newToken().'">
												<td><input id="desc '.$i.'" name="description"type="text" placeholder="Description.." value="'.$desc.'" disabled></td>
												<td><button type="submit" name="delete" onclick="return confirmDelete();">Delete</button>
												<button type="button" class="'.$i.'" id="edit-button '.$i.'" onclick="toggleEdit(this.className)">Edit</button>
												<button type="submit" name="edit" id="save-button '.$i.'" hidden>Save</button></td>
												<div id="myModal" class="modal '.$i.'">
														<!-- Modal content -->
														<div class="modal-content">
														<div class="modal-header">
															<p id="rotate '.$i.'" onclick="rotateImage(this.id)">Rotate</p>
															<span class="close '.$i.'">&times;</span>
														</div>
														<div class="modal-body">  
															<div class="modal-image">
																<img id="img rotate '.$i.'" alt="img" src="./img/pos/'.explode("|",$imagesList[$i])[0].'">
															</div>';
															if($desc != ""){
																print "<div><p>$desc</p></div>";
															}
														print '</div>
														<div class="modal-footer">
														</div>
														</div>
													</div>
												<input type="hidden" name="img" value="'.$i.'">
											</form>';
								print "</tr>";       

							}
							print '<script>
										let rotation = 0;
										const angle = 90;
										function rotateImage(i) {
											var c = "img "+i;
											var rotated = document.getElementById(c);
											rotation = (rotation + angle) % 360;
											rotated.style.transform = `rotate(${rotation}deg)`;
											rotated.style.transform = `scale(${rotation}deg)`;
										}
									</script>';
							print   '<script>
										function toggleEdit(i) {
											var desc = "desc " +i;
											var btn = "edit-button " +i;
											var btnS = "save-button " +i;
											var input = document.getElementById(desc);
											var button = document.getElementById(btn);
											var buttonS = document.getElementById(btnS);
											var img = document.getElementById(i);
											if (input.disabled) {
												input.disabled = false;
												// button.innerHTML = "Save";
												button.hidden = true;
												buttonS.hidden = false;
											} else {
												input.disabled = true;
												// button.innerHTML = "Edit";
												button.hidden = false;
												buttonS.hidden = true;
											}
										}
									</script>';
									print '<script>

										function ss(i){
											var c = "close " +i;
											// alert(c);
											var modal = document.getElementsByClassName(i)[0];
											modal.style.display = "block";
											var span = document.getElementsByClassName(c)[0]; 
											span.onclick = function() {
												modal.style.display = "none";
											}
											window.onclick = function(event) {
											if (event.target == modal) {
												modal.style.display = "none";
												}
											}
										}
										// var btn = document.getElementById("myBtn");
										// btn.onclick = function() {
										// // modal.style.display = "block";
										// }
									</script>'; 
									print '<style>
									/* The Modal (background) */
									.modal-image {
										overflow: auto;
										// float: left;
									}
									.modal {
									display: none; /* Hidden by default */
									position: fixed; /* Stay in place */
									z-index: 999999999999999; /* Sit on top */
									padding-top: 5vh; /* Location of the box */
									left: 0;
									top: 0;
									width: 100%; /* Full width */
									height: 100%; /* Full height */
									overflow: auto; /* Enable scroll if needed */
									background-color: rgb(0,0,0); /* Fallback color */
									background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
									}
									.myImg {
										cursor: pointer
									}
									.delBtn {
										cursor: pointer;
										border: 1px solid;
										color: black;
										background: #e9e9e9;
									}
									/* Modal Content */
									.modal-content {
									position: relative;
									background-color: #fefefe;
									margin: auto;
									padding: 0;
									border: 1px solid #888;
									width: 80%;
									box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19);
									-webkit-animation-name: animatetop;
									-webkit-animation-duration: 0.4s;
									animation-name: animatetop;
									animation-duration: 0.4s
									}
									
									/* Add Animation */
									@-webkit-keyframes animatetop {
									from {top:-300px; opacity:0} 
									to {top:0; opacity:1}
									}
									
									@keyframes animatetop {
									from {top:-300px; opacity:0}
									to {top:0; opacity:1}
									}
									
									/* The Close Button */
									.close {
									color: #333333;
									float: right;
									font-size: 28px;
									font-weight: bold;
									}
									
									.close:hover,
									.close:focus {
									color: #000;
									text-decoration: none;
									cursor: pointer;
									}
									
									.modal-header {
									height: 3em;  
									padding: 2px 16px;
									background-color: #e9e9e9;
									color: white;
									}
									
									.modal-header p {
										float: left;
										color: black;
										cursor: pointer;
									}
									.modal-body {
										padding: 2px 16px;
										text-align: center;
									}
									.modal-body img{
										width: 50%;
										height: 35rem
									}
									
									.modal-footer {
									padding: 2px 16px;
									background-color: #e9e9e9;
									color: white;
									}
									</style>';   
						}

				print '</table>';
				if(isset($_POST['delete'])) {
					unlink($dir.explode("|",$imagesList[$_POST["img"]])[0]);
					unset($imagesList[$_POST["img"]]);
					$dbImages = implode(',', $imagesList);
					$sql = 'UPDATE llx_stores_poshardware set images = "'.$dbImages.'" WHERE rowid = '.$id;
					$db->query($sql,0,'ddl');
					print '<script>window.location.href = window.location.href;
					</script>';
				}
				if(isset($_POST['edit'])) {
					$imagesList[$_POST["img"]] = explode("|",$imagesList[$_POST["img"]])[0]."|".$_POST["description"];
					$dbImages = implode(',', $imagesList);
					$sql = 'UPDATE llx_stores_poshardware set images = "'.$dbImages.'" WHERE rowid = '.$id;
					$db->query($sql,0,'ddl');
					print '<script>window.location.href = window.location.href;
					</script>';
				}

		
	}

	//Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	// Presend form
	$modelmail = 'poshardware';
	$defaulttopic = 'InformationMessage';
	$diroutput = $conf->stores->dir_output;
	$trackid = 'poshardware'.$object->id;

	include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';
}

// End of page
llxFooter();
$db->close();
