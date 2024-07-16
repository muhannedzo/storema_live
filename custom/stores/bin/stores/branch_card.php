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
// require_once DOL_DOCUMENT_ROOT.'/core/class/FormTools.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formadmin.class.php';
// require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
// require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
// require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';

dol_include_once('/stores/class/branch.class.php');
dol_include_once('/stores/lib/stores_branch.lib.php');
// dol_include_once('/dolibarrutils/lib/functions2.lib.php');
// require_once DOL_DOCUMENT_ROOT.'/core/lib/common.lib.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';



include_once('compress.php');

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
// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once.

// There is several ways to check permission.
// Set $enablepermissioncheck to 1 to enable a minimum low level of checks
$enablepermissioncheck = 0;
// if ($enablepermissioncheck) {
// 	$permissiontoread = $user->rights->stores->branch->read;
// 	$permissiontoadd = $user->rights->stores->branch->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
// 	$permissiontodelete = $user->rights->stores->branch->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
// 	$permissionnote = $user->rights->stores->branch->write; // Used by the include of actions_setnotes.inc.php
// 	$permissiondellink = $user->rights->stores->branch->write; // Used by the include of actions_dellink.inc.php
// } else {
// 	$permissiontoread = 0;
// 	$permissiontoadd = 0; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
// 	$permissiontodelete = 0;
// 	$permissionnote = 0;
// 	$permissiondellink = 0;
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

$upload_dir = $conf->stores->multidir_output[isset($object->entity) ? $object->entity : 1].'/branch';

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

// var_dump($_SERVER['HTTP_REFERER']);

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

	$triggermodname = 'STORES_BRANCH_MODIFY'; // Name of trigger action code to execute when we modify record

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
$formcompany = new FormCompany($db);
// $formtools = $form = new FormTools();


$title = $langs->trans("Branch");
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

	$query = "SELECT * FROM llx_societe";
	$resThird = $db->query($query)->fetch_all();
	// var_dump($resThird);// Edit parameters

	if (!empty($conf->use_javascript_ajax)) {
		print "\n".'<script type="text/javascript">';
		
		print '// Save form field values when the form is submitted
		$("form").submit(function() {
			var formData = $(this).serializeArray();
			formData.forEach(function(field) {
				localStorage.setItem(field.name, field.value);
			});
		});
		';
		print '$(document).ready(function () {
			
			$("form").submit(function() {
				var formData = $(this).serializeArray();
				formData.forEach(function(field) {
					localStorage.setItem(field.name, field.value);
				});
			});
			var currentURL = window.location.href;
			var parts = currentURL.split("?");
			if (parts[1] == null) {
				$("form input, form select, form textarea, form span").each(function() {
					var storedValue = localStorage.getItem($(this).attr("name"));
					if (storedValue) {
						$(this).val(storedValue);
					}
				});
			}else{
				localStorage.clear();
			}

			const cookies = document.cookie.split(";");
			for (let i = 0; i < cookies.length; i++) {
				const cookie = cookies[i];
				const eqPos = cookie.indexOf("=");
				const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
				document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
			}
			$("#fk_soc").change(function() {
				if(document.getElementById("b_number").value != ""){
					var b = document.getElementById("b_number").value.split("-");
					if(b[0] && b[0].length === 3){
						b[0] = $("#fk_soc option:selected").text().substring(0,3);
						document.getElementById("b_number").value = b.join("-");
					}else{
						document.getElementById("b_number").value = $("#fk_soc option:selected").text().substring(0,3)+"-" + document.getElementById("b_number").value;
					}
				}else{
					document.getElementById("b_number").value = $("#fk_soc option:selected").text().substring(0,3)+"-";
				}
				// document.cookie = "b_number="+$("#fk_soc option:selected").text().substring(0,3)+"-";
			});
			
			function containsNumbers(str) {
				return /\d/.test(str);
			  }
			document.getElementById("ref").value = document.getElementById("b_number").value;
			$("#b_number").change(function() {
				document.getElementById("ref").value = document.getElementById("b_number").value;
			});
			$("#selectcountry_id").change(function (){
				$.post("branch_card.php", {country_id: $("#selectcountry_id option:selected").text()},
				function(data){
					const startIndex = $("#selectcountry_id option:selected").text().indexOf("(") + 1;
					const endIndex = $("#selectcountry_id option:selected").text().indexOf(")");

					const countryCode = $("#selectcountry_id option:selected").text().substring(startIndex, endIndex);
					console.log(countryCode);
					document.getElementById("country").value = $("#selectcountry_id option:selected").text();
					document.cookie = "country="+$("#selectcountry_id option:selected").text();
					var c = $("#b_number").val().split("-");
					if($("#b_number").val() != ""){
						if(c[1] && c[1].length === 2){
							c[1] = countryCode;
							document.cookie = "b_number="+c.join("-");
						}else{
							c.splice(1, 0, countryCode);
							// document.cookie = "b_number="+c[0]+ "-"+countryCode + "-";
							document.cookie = "b_number="+c.join("-");
						}
					}else{
						document.cookie = "b_number="+$("#b_number").val() + countryCode + "-";
					}
					document.cookie = "street="+$("#street").val();
					document.cookie = "house_number="+$("#house_number").val();
					document.cookie = "zip_code="+$("#postal_code").val();
					document.cookie = "country_id="+$("#selectcountry_id option:selected").val();
					document.cookie = "city="+$("#city").val();
					document.cookie = "phone="+$("#phone").val();
					// document.cookie = "store_manager="+$("#store_manager").val();
					// document.cookie = "district_manager="+$("#district_manager").val();
					document.cookie = "fk_soc="+$("#fk_soc option:selected").val();
					document.getElementById("country").value = $("#selectcountry_id option:selected").text();
					
					location.reload();
					}
				);

			});
			$("#state_id").change(function (){
				document.getElementById("state").value = $("#state_id option:selected").text();
				// alert(document.getElementById("state").value);
			});
		});';
		print '</script>'."\n";
	}

	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("Branch")), '', 'object_'.$object->picto);

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
	
	// Third Party	
	$fk_soc = "";
	if(isset($_COOKIE["fk_soc"])){
		// var_dump($_COOKIE["fk_soc"]);
		$fk_soc = $_COOKIE["fk_soc"];
	}
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="fk_soc">'.$langs->trans("Business Partner").'</label></td><td>';
		if($third){
			print '<span class="fas fa-building"></span> <a href="'.dol_buildpath('/societe/card.php',1).'?socid='.$third.'">'.$thirdParty->name.'</a>';
			print '<input name="fk_soc" id="fk_soc" value="'.$third.'" hidden>';
		}elseif($socid != null){
			print '<span class="fas fa-building"></span> <a href="'.dol_buildpath('/societe/card.php',1).'?socid='.$socid.'">'.$thirdPartyy->name.'</a>';
			print '<input name="fk_soc" id="fk_soc" value="'.$socid.'" hidden>';
		}else{
			print '<select name="fk_soc" id="fk_soc" required>
						<option value="">--Please choose an option--</option>';
							foreach($resThird as $elem){
									if($fk_soc && $fk_soc == $elem[0]){
										print '<option value="'.$elem[0].'" selected>'.$elem[1].'</option>';	
									}else{
										print '<option value="'.$elem[0].'">'.$elem[1].'</option>';	
									}			
							}
			print '</select>';
			print '<input name="fk_soc" id="fk_soc" value="'.$fk_soc.'" hidden>';
		}
	print "\n";	
	$b_number = "";
	if($third){
		$b_number = substr($thirdParty->name, 0, 3)."-";
	}
	if($socid){
		$b_number = substr($thirdPartyy->name, 0, 3)."-";
	}
	if(isset($_COOKIE["b_number"])){
		$b_number = $_COOKIE["b_number"];
	}	
	
	// branch number
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="b_number">'.$langs->trans("BranchNumber").'</label></td><td>';
	print '<input name="b_number" id="b_number" class="minwidth200"  autofocus="autofocus" value="'.$b_number.'"></td></tr>'."\n";

	
	$street = "";
	if(isset($_COOKIE["street"])){
		$street = $_COOKIE["street"];
	}

	$house_number = "";
	if(isset($_COOKIE["house_number"])){
		$house_number = $_COOKIE["house_number"];
	}
	// street
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="street">'.$langs->trans("StreetHouse").'</label></td><td>';
	print '<input name="street" id="street" class="minwidth200" value="'.$street.'"  autofocus="autofocus"><input style="margin-left: 5px" name="house_number" id="house_number" class="minwidth200" value="'.$house_number.'"  autofocus="autofocus"></td></tr>'."\n";

	// // house number
	// print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="house_number">'.$langs->trans("House Number").'</label></td><td>';
	// print '<input name="house_number" id="house_number" class="minwidth200" value="'.$house_number.'"  autofocus="autofocus"></td></tr>'."\n";

	$zip_code = "";
	if(isset($_COOKIE["zip_code"])){
		$zip_code = $_COOKIE["zip_code"];
	}

	$city = "";
	if(isset($_COOKIE["city"])){
		$city = $_COOKIE["city"];
	}
	// zip code
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="postal_code">'.$langs->trans("ZipCity").'</label></td><td>';
	print '<input name="postal_code" id="postal_code" class="minwidth200" value="'.$zip_code.'" autofocus="autofocus"><input style="margin-left: 5px" name="city" id="city" class="minwidth200" value="'.$city.'" autofocus="autofocus"</td></tr>'."\n";
	// city
	// print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="city">'.$langs->trans("City").'</label></td><td>';
	// print '<input name="city" id="city" class="minwidth200" value="'.$city.'" autofocus="autofocus"></td></tr>'."\n";

	$country_id = null;
	$country_code = "";
	if(isset($_COOKIE["country_id"])){
		$country_id = $_COOKIE["country_id"];
		$tmparray = getCountry($country_id, 'all');
		$country_code = $tmparray['code'];
		// var_dump($country_code);
	}

	// state
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="state">'.$langs->trans("State").'</label></td><td>'; 
	
	$tmparray = getCountry($country_id, 'all');
	$country_code = $tmparray['code'];

	print $formcompany->select_state(GETPOST('state_id') != '' ?GETPOST('state_id') : $object->state_id, $country_code);
	print '<input type="text" name="state" id="state" value="" hidden>';

	// country
	$country_select = $form->select_country($country_code, 'country_id', '', 0, 'minwidth300 maxwidth500 widthcentpercentminusx');
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="country">'.$langs->trans("Country").'</label></td><td>';
	print img_picto('', 'globe-americas', 'class="paddingrightonly"').$country_select;
	print '<input type="text" name="country" id="country" value="" hidden>';
	$phone = "";
	if(isset($_COOKIE["phone"])){
		$phone = $_COOKIE["phone"];
	}	
	// phone
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="phone">'.$langs->trans("Phone").'</label></td><td>';
	print '<input name="phone" id="phone" class="minwidth200" value="'.$phone.'"  autofocus="autofocus"></td></tr>'."\n";
	
	// status
	// print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="status">'.$langs->trans("Status").'</label></td><td>';
	// print '<select name="status" id="status" required>
	// 			<option value="">--Please choose an option--</option>
	// 			<option value="0">Draft</option>
	// 			<option value="1">Validated</option>
	// 			<option value="9">Canceled</option>
	// 		</select>'."\n";
	print '<input type="hidden" name="status" id="status" value="1"></td></tr>'."\n";

	// ref
	print '<input type="hidden" name="ref" id="ref"></td></tr>'."\n";

	// $store_manager = "";
	// if(isset($_COOKIE["store_manager"])){
	// 	$store_manager = $_COOKIE["store_manager"];
	// }	
	// // store manager
	// print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="store_manager">'.$langs->trans("Storemanager").'</label></td><td>';
	// print '<input name="store_manager" id="store_manager" class="minwidth200" value="'.$store_manager.'"  autofocus="autofocus"></td></tr>'."\n";

	// $district_manager = "";
	// if(isset($_COOKIE["district_manager"])){
	// 	$district_manager = $_COOKIE["district_manager"];
	// }	
	// // district manager
	// print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="district_manager">'.$langs->trans("Districtmanager").'</label></td><td>';
	// print '<input name="district_manager" id="district_manager" class="minwidth200" value="'.$district_manager.'"  autofocus="autofocus"></td></tr>'."\n";
	

	/////////////////////End Muhannad Code///////////////////////////

	print '</table>'."\n";

	print dol_get_fiche_end();
	
	print $form->buttonsSaveCancel("Create");

	print '</form>';

	//dol_set_focus('input[name="ref"]');
}

// Part to edit record
if (($id || $ref) && $action == 'edit') {

	if (!empty($conf->use_javascript_ajax)) {
		print "\n".'<script type="text/javascript">';
		
		print '// Save form field values when the form is submitted
		$("form").submit(function() {
			var formData = $(this).serializeArray();
			formData.forEach(function(field) {
				localStorage.setItem(field.name, field.value);
			});
		});
		';
		print '$(document).ready(function () {
			
			$("form").submit(function() {
				var formData = $(this).serializeArray();
				formData.forEach(function(field) {
					localStorage.setItem(field.name, field.value);
				});
			});
			var currentURL = window.location.href;
			var parts = currentURL.split("?");
			if (parts[1] == null) {
				$("form input, form select, form textarea, form span").each(function() {
					var storedValue = localStorage.getItem($(this).attr("name"));
					if (storedValue) {
						$(this).val(storedValue);
					}
				});
			}else{
				localStorage.clear();
			}

			const cookies = document.cookie.split(";");

			for (let i = 0; i < cookies.length; i++) {
				const cookie = cookies[i];
				const eqPos = cookie.indexOf("=");
				const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
				document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
			}
			document.getElementById("ref").value = document.getElementById("b_number").value;
			$("#b_number").change(function() {
				document.getElementById("ref").value = document.getElementById("b_number").value;
			});
			function containsNumbers(str) {
				return /\d/.test(str);
			  }
			$("#selectcountry_id").change(function (){
				$.post("branch_card.php", {country_id: $("#selectcountry_id option:selected").text()},
				function(data){
					const startIndex = $("#selectcountry_id option:selected").text().indexOf("(") + 1;
					const endIndex = $("#selectcountry_id option:selected").text().indexOf(")");

					const countryCode = $("#selectcountry_id option:selected").text().substring(startIndex, endIndex);
					console.log(countryCode);
					document.getElementById("country").value = $("#selectcountry_id option:selected").text();
					document.cookie = "country="+$("#selectcountry_id option:selected").text();
					var c = $("#b_number").val().split("-");
					if($("#b_number").val() != ""){
						if(c[1] && c[1].length === 2 && !containsNumbers(c[1])){
							c[1] = countryCode;
							document.cookie = "b_number="+c.join("-");
						}else{
							c.splice(1, 0, countryCode);
							// document.cookie = "b_number="+c[0]+ "-"+countryCode + "-";
							document.cookie = "b_number="+c.join("-");
						}
					}else{
						document.cookie = "b_number="+$("#b_number").val() + countryCode + "-";
					}
					document.cookie = "street="+$("#street").val();
					document.cookie = "house_number="+$("#house_number").val();
					document.cookie = "zip_code="+$("#zip_code").val();
					document.cookie = "country_id="+$("#selectcountry_id option:selected").val();
					document.cookie = "city="+$("#city").val();
					document.cookie = "phone="+$("#phone").val();
					document.cookie = "store_manager="+$("#store_manager").val();
					document.cookie = "district_manager="+$("#district_manager").val();
					document.cookie = "open="+$("#opening").val();
					document.cookie = "close="+$("#closing").val();
					document.cookie = "cash="+$("#cashers_desks").val();
					document.cookie = "store_size="+$("#store_size").val();
					document.cookie = "sales="+$("#sales_area").val();
					document.cookie = "warehouse="+$("#warehouse_area").val();
					document.cookie = "height="+$("#branch_height").val();
					document.getElementById("country").value = $("#selectcountry_id option:selected").text();
					
					location.reload();
					}
				);

			});
			document.getElementById("state").value = $("#state_id option:selected").text();
			$("#state_id").change(function (){
				document.getElementById("state").value = $("#state_id option:selected").text();
				// alert(document.getElementById("state").value);
			});
		});';
		print '</script>'."\n";
	}
	print load_fiche_titre($langs->trans("Branch"), '', 'object_'.$object->picto);

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

	/////////////////////Muhanned code////////////////////////////////
	// var_dump($object);
	$sql = 'SELECT * FROM llx_stores_branch WHERE rowid = '.$id;
	$branch = $db->query($sql)->fetch_row();
	// var_dump($branch);
	// branch number
	$b = $object->b_number;
	if(isset($_COOKIE["b_number"])){
		$b = $_COOKIE["b_number"];
	}
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="b_number">'.$langs->trans("BranchNumber").'</label></td><td>';
	print '<input name="b_number" id="b_number" class="minwidth200"  autofocus="autofocus" value = "'.$b.'"></td></tr>'."\n";
	// street
	$st = $object->street;
	if(isset($_COOKIE["street"])){
		$st = $_COOKIE["street"];
	}

	// house number
	$hn = $object->house_number;
	if(isset($_COOKIE["house_number"])){
		$hn = $_COOKIE["house_number"];
	}
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="street">'.$langs->trans("Street").'</label></td><td>';
	print '<input name="street" id="street" class="minwidth200"  autofocus="autofocus" value = "'.$st.'"><input style="margin-left: 5px" name="house_number" id="house_number" class="minwidth200"  autofocus="autofocus" value = "'.$hn.'"></td></tr>'."\n";
	// print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="house_number">'.$langs->trans("HouseNumber").'</label></td><td>';
	// print '<input name="house_number" id="house_number" class="minwidth200"  autofocus="autofocus" value = "'.$hn.'"></td></tr>'."\n";

	// zip code
	$zc = $object->postal_code;
	if(isset($_COOKIE["zip_code"])){
		$zc = $_COOKIE["zip_code"];
	}

	// city
	$ct = $object->city;
	if(isset($_COOKIE["city"])){
		$ct = $_COOKIE["city"];
	}
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="zip_code">'.$langs->trans("ZipCity").'</label></td><td>';
	print '<input name="postal_code" id="zip_code" class="minwidth200"  autofocus="autofocus" value = "'.$zc.'"><input style="margin-left: 5px" name="city" id="city" class="minwidth200"  autofocus="autofocus" value = "'.$ct.'"></td></tr>'."\n";

	// print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="city">'.$langs->trans("City").'</label></td><td>';
	// print '<input name="city" id="city" class="minwidth200"  autofocus="autofocus" value = "'.$ct.'"></td></tr>'."\n";

	$country_id = $object->country_id;
	$country_code = "";
	if(isset($_COOKIE["country_id"])){
		$country_id = $_COOKIE["country_id"];
		$tmparray = getCountry($country_id, 'all');
		$country_code = $tmparray['code'];
		// var_dump($country_code);
	}else{
		$tmparray = getCountry($country_id, 'all');
		$country_code = $tmparray['code'];
	}	

	// state
	// $state_select = $form->select_state('state_id', null, true);
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="state">'.$langs->trans("State").'</label></td><td>';
	
	$tmparray = getCountry($country_id, 'all');
	$country_code = $tmparray['code'];

	print $formcompany->select_state(GETPOST('state_id') != '' ?GETPOST('state_id') : $object->state_id, $country_code);
	print '<input type="text" name="state" id="state" value="" hidden>';

	// country
	$country_select = $form->select_country($country_code, 'country_id', '', 0, 'minwidth300 maxwidth500 widthcentpercentminusx');
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="country">'.$langs->trans("Country").'</label></td><td>';
	print img_picto('', 'globe-americas', 'class="paddingrightonly"').$country_select;
	print '<input type="text" name="country" id="country" value="" hidden>';

	// phone
	$ph = $object->phone;
	if(isset($_COOKIE["phone"])){
		$ph = $_COOKIE["phone"];
	}
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="phone">'.$langs->trans("Phone").'</label></td><td>';
	print '<input name="phone" id="phone" class="minwidth200"  autofocus="autofocus" value = "'.$ph.'"></td></tr>'."\n";

	// // status
	// print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="status">'.$langs->trans("Status").'</label></td><td>';
	// print '<select name="status" id="status" required>
	// 			<option value="">--Please choose an option--</option>
	// 			<option value="0">Draft</option>
	// 			<option value="1">Validated</option>
	// 			<option value="9">Canceled</option>
	// 		</select>'."\n";

	//opening
	$open = $object->opening ? $object->opening : -1;
	if(isset($_COOKIE["open"])){
		$open = $_COOKIE["open"];
	}
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="opening">'.$langs->trans("Opening").'</label></td><td>';
	print $form->selectDate($open, 'opening', 0, 0, 0, "perso", 1, 0);

	//closing
	$close = $object->closing ? $object->closing : -1;
	if(isset($_COOKIE["close"])){
		$close = $_COOKIE["close"];
	}
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="closing">'.$langs->trans("Closing").'</label></td><td>';
	print $form->selectDate($close, 'closing', 0, 0, 0, "perso", 1, 0);

	// cashers desks
	$cash = $object->cashers_desks;
	if(isset($_COOKIE["cash"])){
		$cash = $_COOKIE["cash"];
	}
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="cashers_desks">'.$langs->trans("Casher").'</label></td><td>';
	print '<input type="number" name="cashers_desks" id="cashers_desks" class="minwidth200"  autofocus="autofocus" value = "'.$cash.'"></td></tr>'."\n";

	// store size
	$ss = $object->store_size;
	if(isset($_COOKIE["store_size"])){
		$ss = $_COOKIE["store_size"];
	}
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="store_size">'.$langs->trans("Storesize").'</label></td><td>';
	print '<input type="text" name="store_size" id="store_size" class="minwidth200"  autofocus="autofocus" value = "'.$ss.'"></td></tr>'."\n";

	// Sales area
	$sales = $object->sales_area;
	if(isset($_COOKIE["sales"])){
		$sales = $_COOKIE["sales"];
	}
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="sales_area">'.$langs->trans("Salesarea").'</label></td><td>';
	print '<input type="text" name="sales_area" id="sales_area" class="minwidth200"  autofocus="autofocus" value = "'.$sales.'"></td></tr>'."\n";

	// Warehouse
	$warehouse = $object->warehouse_area;
	if(isset($_COOKIE["warehouse"])){
		$warehouse = $_COOKIE["warehouse"];
	}
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="warehouse_area">'.$langs->trans("Warehousearea").'</label></td><td>';
	print '<input type="text" name="warehouse_area" id="warehouse_area" class="minwidth200"  autofocus="autofocus" value = "'.$warehouse.'"></td></tr>'."\n";

	// Branch height
	$height = $object->branch_height;
	if(isset($_COOKIE["height"])){
		$height = $_COOKIE["height"];
	}
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="branch_height">'.$langs->trans("Branchheight").'</label></td><td>';
	print '<input type="text" name="branch_height" id="branch_height" class="minwidth200"  autofocus="autofocus" value = "'.$height.'"></td></tr>'."\n";


	print '<input type="hidden" name="status" id="status" value="1"></td></tr>'."\n";

	// ref
	print '<input type="hidden" name="ref" id="ref"></td></tr>'."\n";

	// $store_manager = $object->store_manager;
	// if(isset($_COOKIE["store_manager"])){
	// 	$store_manager = $_COOKIE["store_manager"];
	// }	
	// // store manager
	// print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="store_manager">'.$langs->trans("Storemanager").'</label></td><td>';
	// print '<input name="store_manager" id="store_manager" class="minwidth200" value="'.$store_manager.'"  autofocus="autofocus"></td></tr>'."\n";

	// $district_manager = $object->district_manager;
	// if(isset($_COOKIE["district_manager"])){
	// 	$district_manager = $_COOKIE["district_manager"];
	// }	
	// // district manager
	// print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="district_manager">'.$langs->trans("Districtmanager").'</label></td><td>';
	// print '<input name="district_manager" id="district_manager" class="minwidth200" value="'.$district_manager.'"  autofocus="autofocus"></td></tr>'."\n";
	//////////////////////////////////////////////////////////////////
	// Common attributes
	// include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_edit.tpl.php';

	// Other attributes
	// include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_edit.tpl.php';

	print '</table>';

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel();

	print '</form>';
}

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {
	$res = $object->fetch_optionals();

	$head = branchPrepareHead($object);
	print dol_get_fiche_head($head, 'card', $langs->trans("Branch"), -1, $object->picto);

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeleteBranch'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
	}

	// // Confirmation to delete
	// if ($action == 'delete') {
	// 	$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeleteBranch'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
	// }
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
		$text = $langs->trans('ConfirmActionBranch', $object->ref);
		/*if (! empty($conf->notification->enabled))
		{
			require_once DOL_DOCUMENT_ROOT . '/core/class/notify.class.php';
			$notify = new Notify($db);
			$text .= '<br>';
			$text .= $notify->confirmMessage('BRANCH_CLOSE', $object->socid, $object);
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
	

	$string = $_SERVER['HTTP_REFERER'];
	$substring = "/custom/stores/branch_list.php?id=";

	if (strpos($string, $substring) !== false) {
		$linkback = '<a href="'.$_SERVER['HTTP_REFERER'].'">'.$langs->trans("BackToList").'</a>';
	} else {
		$linkback = '<a href="'.dol_buildpath('/stores/branch_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';
	}
	
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

	if($user->socid){
		print '<style>';
		print '.pagination .pagination{
			display: none
		}';
		print '</style>';
	}

	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent tableforfield">'."\n";

	// Common attributes
	//$keyforbreak='fieldkeytoswitchonsecondcolumn';	// We change column just before this field
	//unset($object->fields['fk_project']);				// Hide field already shown in banner
	//unset($object->fields['fk_soc']);					// Hide field already shown in banner
	
	// print '<tbody><tr class="field_b_number"><td class="titlefield fieldname_b_number">Store Number</td><td class="valuefield fieldname_b_number">SES-DE-14587</td></tr><tr class="field_street"><td class="titlefield fieldname_street">Street</td><td class="valuefield fieldname_street">Manahel Street</td></tr><tr class="field_house_number"><td class="titlefield fieldname_house_number">House Number</td><td class="valuefield fieldname_house_number">1451</td></tr><tr class="field_postal_code"><td class="titlefield fieldname_postal_code">PostalCode</td><td class="valuefield fieldname_postal_code">125684</td></tr><tr class="field_city"><td class="titlefield fieldname_city">City</td><td class="valuefield fieldname_city">Munich</td></tr><tr class="field_state"><td class="titlefield fieldname_state">State/Province</td><td class="valuefield fieldname_state">BE - Berlin</td></tr><tr class="field_country"><td class="titlefield fieldname_country">Country</td><td class="valuefield fieldname_country">Germany</td></tr><tr class="field_phone"><td class="titlefield fieldname_phone">Phone</td><td class="valuefield fieldname_phone">+428256667</td></tr><tr class="field_fk_soc"><td class="titlefield fieldname_fk_soc">Third Party</td><td class="valuefield fieldname_fk_soc"><a href="/doli/dolibarr/htdocs/societe/card.php?socid=1"><span class="fas fa-building paddingright" style=" color: #6c6aa8;"></span>SESOCO (sesoco)</a></td></tr></tbody>';

	$businessPartner = new Societe($db);
	$businessPartner->fetch($object->fk_soc);
	print '<tbody>';
		print '<tr class="field_fk_soc">';
			print '<td class="titlefield fieldname_fk_soc">'.$langs->trans('ThirdParty').'</td>';
			print '<td class="valuefield fieldname_fk_soc">'.$businessPartner->getNomUrl(1).'</td>';
		print '</tr>';
		print '<tr class="field_b_number">';
			print '<td class="titlefield fieldname_b_number">'.$langs->trans('BranchesNumber').'</td>';
			print '<td class="valuefield fieldname_b_number">'.$object->b_number.'</td>';
		print '</tr>';
		print '<tr class="field_street_house">';
			print '<td class="titlefield fieldname_street_house">'.$langs->trans('StreetHouse').'</td>';
			print '<td class="valuefield fieldname_street_house">'.$object->street.', '.$object->house_number.'</td>';
		print '</tr>';
		print '<tr class="field_zip_city">';
			print '<td class="titlefield fieldname_zip_city">'.$langs->trans('ZipCity').'</td>';
			print '<td class="valuefield fieldname_zip_city">'.$object->postal_code.', '.$object->city.'</td>';
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
		print '<tr class="field_opening">';
			print '<td class="titlefield fieldname_phone">'.$langs->trans('Opening').'</td>';
			$opening = $object->opening ? date("Y-m", $object->opening) : "";
			print '<td class="valuefield fieldname_phone">'.$opening.'</td>';
		print '</tr>';
		print '<tr class="field_closing">';
			print '<td class="titlefield fieldname_phone">'.$langs->trans('Closing').'</td>';
			$closing = $object->closing ? date("Y-m", $object->closing) : "";
			print '<td class="valuefield fieldname_phone">'.$closing.'</td>';
		print '</tr>';
		print '<tr class="field_cashers_desks">';
			print '<td class="titlefield fieldname_phone">'.$langs->trans('Casher').'</td>';
			print '<td class="valuefield fieldname_phone">'.$object->cashers_desks.'</td>';
		print '</tr>';
		print '<tr class="field_store_size">';
			print '<td class="titlefield fieldname_phone">'.$langs->trans('Storesize').'</td>';
			$store_size = $object->store_size ? $object->store_size." qm" : "";
			print '<td class="valuefield fieldname_phone">'.$store_size.'</td>';
		print '</tr>';
		print '<tr class="field_sales_area">';
			print '<td class="titlefield fieldname_phone">'.$langs->trans('Salesarea').'</td>';
			$sales_area = $object->sales_area ? $object->sales_area." qm" : "";
			print '<td class="valuefield fieldname_phone">'.$sales_area.'</td>';
		print '</tr>';
		print '<tr class="field_warehouse_area">';
			print '<td class="titlefield fieldname_phone">'.$langs->trans('Warehousearea').'</td>';
			$warehouse_area = $object->warehouse_area ? $object->warehouse_area." qm" : "";
			print '<td class="valuefield fieldname_phone">'.$warehouse_area.'</td>';
		print '</tr>';
		print '<tr class="field_branch_height">';
			print '<td class="titlefield fieldname_phone">'.$langs->trans('Branchheight').'</td>';
			$branch_height = $object->branch_height ? $object->branch_height." m" : "";
			print '<td class="valuefield fieldname_phone">'.$branch_height.'</td>';
		print '</tr>';
	print '</tbody>';
	
	
	// include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_view.tpl.php';
	
	// Other attributes. Fields from hook formObjectOptions and Extrafields.
	// include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_view.tpl.php';

	print '</table>';

	print '</div>';
		
	print '<div class="fichehalfright">';
	print '<div class="underbanner clearboth"></div>';
	$query = 'SELECT days FROM llx_stores_branch WHERE rowid = '.$id;
	$list = $db->query($query)->fetch_row();
	$daysList = json_decode($list[0],true);
	$mon = $langs->trans("monday");$mon_fs = "";$mon_fe = "";$mon_ss = "";$mon_se = "";$monS = "";$monF = "";
	$tue = $langs->trans("tuesday");$tue_fs = "";$tue_fe = "";$tue_ss = "";$tue_se = "";$tueS = "";$tueF = "";
	$wed = $langs->trans("wednesday");$wed_fs = "";$wed_fe = "";$wed_ss = "";$wed_se = "";$wedS = "";$wedF = "";
	$thu = $langs->trans("thursday");$thu_fs = "";$thu_fe = "";$thu_ss = "";$thu_se = "";$thuS = "";$thuF = "";
	$fri = $langs->trans("friday");$fri_fs = "";$fri_fe = "";$fri_ss = "";$fri_se = "";$friS = "";$friF = "";
	$sat = $langs->trans("saturday");$sat_fs = "";$sat_fe = "";$sat_ss = "";$sat_se = "";$satS = "";$satF = "";
	$sun = $langs->trans("sunday");$sun_fs = "";$sun_fe = "";$sun_ss = "";$sun_se = "";$sunS = "";$sunF = "";
	$query = 'SELECT goods FROM llx_stores_branch WHERE rowid = '.$id;
	$list = $db->query($query)->fetch_row();
	$goodsList = json_decode($list[0],true);
	$mon_goods = "";$tue_goods = "";$wed_goods = "";$thur_goods = "";$fri_goods = "";$sat_goods = "";$sun_goods = "";
	if($daysList != null){
		$mon_goods = $goodsList[0]["hour"];
		$mon_goods_end = $goodsList[0]["hour_end"];
		$mon_full_goods = "";
		if($mon_goods || $mon_goods_end){
			$mon_full_goods = $mon_goods." - ".$mon_goods_end ; 
		}
		$tue_goods = $goodsList[1]["hour"];
		$tue_goods_end = $goodsList[1]["hour_end"];
		$tue_full_goods = "";
		if($tue_goods || $tue_goods_end){
			$tue_full_goods = $tue_goods." - ".$tue_goods_end ; 
		}
		$wed_goods = $goodsList[2]["hour"];
		$wed_goods_end = $goodsList[2]["hour_end"];
		$wed_full_goods = "";
		if($wed_goods || $wed_goods_end){
			$wed_full_goods = $wed_goods." - ".$wed_goods_end ; 
		}
		$thur_goods = $goodsList[3]["hour"];
		$thur_goods_end = $goodsList[3]["hour_end"];
		$thur_full_goods = "";
		if($thur_goods || $thur_goods_end){
			$thur_full_goods = $thur_goods." - ".$thur_goods_end ; 
		}
		$fri_goods = $goodsList[4]["hour"];
		$fri_goods_end = $goodsList[4]["hour_end"];
		$fri_full_goods = "";
		if($fri_goods || $fri_goods_end){
			$fri_full_goods = $fri_goods." - ".$fri_goods_end ; 
		}
		$sat_goods = $goodsList[5]["hour"];
		$sat_goods_end = $goodsList[5]["hour_end"];
		$sat_full_goods = "";
		if($sat_goods || $sat_goods_end){
			$sat_full_goods = $sat_goods." - ".$sat_goods_end ; 
		}
		$sun_goods = $goodsList[6]["hour"];
		$sun_goods_end = $goodsList[6]["hour_end"];
		$sun_full_goods = "";
		if($sun_goods || $sun_goods_end){
			$sun_full_goods = $sun_goods." - ".$sun_goods_end ; 
		}
	}
	if($daysList != null){
		// print '<div class="sto" style="display:block; align-items: center;margin: 5px;"><button id="del" onclick="sol()">'.$langs->trans("addWorkingHours").'</button></div>';
		$mon = $langs->trans($daysList[0]["day"]);$mon_fs = $daysList[0]["hours"]["open-f"];$mon_fe = $daysList[0]["hours"]["close-f"];$mon_ss = $daysList[0]["hours"]["open-s"];$mon_se = $daysList[0]["hours"]["close-s"];
		$monF = "";$monS = "";
		if(!$mon_fs && !$mon_fe && !$mon_ss && !$mon_se){
			$monF = $langs->trans("closed");
			$monS = "";
		}else{
			if(!$mon_fs && !$mon_fe){
				$monF = $langs->trans("closed");
			}else{
				$monF = $mon_fs." - ".$mon_fe;
			}
			if(!$mon_ss && !$mon_se){
				$monS = $langs->trans("closed");
			}else{
				$monS = $mon_ss." - ".$mon_se;
			}
		}
		$tue = $langs->trans($daysList[1]["day"]);$tue_fs = $daysList[1]["hours"]["open-f"];$tue_fe = $daysList[1]["hours"]["close-f"];$tue_ss = $daysList[1]["hours"]["open-s"];$tue_se = $daysList[1]["hours"]["close-s"];
		$tueF = "";$tueS = "";
		if(!$tue_fs && !$tue_fe && !$tue_ss && !$tue_se){
			$tueF = $langs->trans("closed");
			$tueS = "";
		}else{
			if(!$tue_fs && !$tue_fe){
				$tueF = $langs->trans("closed");
			}else{
				$tueF = $tue_fs." - ".$tue_fe;
			}
			if(!$tue_ss && !$tue_se){
				$tueS = $langs->trans("closed");
			}else{
				$tueS = $tue_ss." - ".$tue_se;
			}
		}
		$wed = $langs->trans($daysList[2]["day"]);$wed_fs = $daysList[2]["hours"]["open-f"];$wed_fe = $daysList[2]["hours"]["close-f"];$wed_ss = $daysList[2]["hours"]["open-s"];$wed_se = $daysList[2]["hours"]["close-s"];
		$wedF = "";$wedS = "";
		if(!$wed_fs && !$wed_fe && !$wed_ss && !$wed_se){
			$wedF = $langs->trans("closed");
			$wedS = "";
		}else{
			if(!$wed_fs && !$wed_fe){
				$wedF = $langs->trans("closed");
			}else{
				$wedF = $wed_fs." - ".$wed_fe;
			}
			if(!$wed_ss && !$wed_se){
				$wedS = $langs->trans("closed");
			}else{
				$wedS = $wed_ss." - ".$wed_se;
			}
		}
		$thu = $langs->trans($daysList[3]["day"]);$thu_fs = $daysList[3]["hours"]["open-f"];$thu_fe = $daysList[3]["hours"]["close-f"];$thu_ss = $daysList[3]["hours"]["open-s"];$thu_se = $daysList[3]["hours"]["close-s"];
		$thuF = "";$thuS = "";
		if(!$thu_fs && !$thu_fe && !$thu_ss && !$thu_se){
			$thuF = $langs->trans("closed");
			$thuS = "";
		}else{
			if(!$thu_fs && !$thu_fe){
				$thuF = $langs->trans("closed");
			}else{
				$thuF = $thu_fs." - ".$thu_fe;
			}
			if(!$sun_ss && !$sun_se){
				$thuS = $langs->trans("closed");
			}else{
				$thuS = $thu_ss." - ".$thu_se;
			}
		}
		$fri = $langs->trans($daysList[4]["day"]);$fri_fs = $daysList[4]["hours"]["open-f"];$fri_fe = $daysList[4]["hours"]["close-f"];$fri_ss = $daysList[4]["hours"]["open-s"];$fri_se = $daysList[4]["hours"]["close-s"];
		$friF = "";$friS = "";
		if(!$fri_fs && !$fri_fe && !$fri_ss && !$fri_se){
			$friF = $langs->trans("closed");
			$friS = "";
		}else{
			if(!$fri_fs && !$fri_fe){
				$friF = $langs->trans("closed");
			}else{
				$friF = $fri_fs." - ".$fri_fe;
			}
			if(!$fri_ss && !$fri_se){
				$friS = $langs->trans("closed");
			}else{
				$friS = $fri_ss." - ".$fri_se;
			}
		}
		$sat = $langs->trans($daysList[5]["day"]);$sat_fs = $daysList[5]["hours"]["open-f"];$sat_fe = $daysList[5]["hours"]["close-f"];$sat_ss = $daysList[5]["hours"]["open-s"];$sat_se = $daysList[5]["hours"]["close-s"];
		$satF = "";$satS = "";
		if(!$sat_fs && !$sat_fe && !$sat_ss && !$sat_se){
			$satF = $langs->trans("closed");
			$satS = "";
		}else{
			if(!$sat_fs && !$sat_fe){
				$satF = $langs->trans("closed");
			}else{
				$satF = $sat_fs." - ".$sat_fe;
			}
			if(!$sat_ss && !$sat_se){
				$satS = $langs->trans("closed");
			}else{
				$satS = $sat_ss." - ".$sat_se;
			}
		}
		$sun = $langs->trans($daysList[6]["day"]);$sun_fs = $daysList[6]["hours"]["open-f"];$sun_fe = $daysList[6]["hours"]["close-f"];$sun_ss = $daysList[6]["hours"]["open-s"];$sun_se = $daysList[6]["hours"]["close-s"];
		$sunF = "";$sunS = "";
		if(!$sun_fs && !$sun_fe && !$sun_ss && !$sun_se){
			$sunF = $langs->trans("closed");
			$sunS = "";
		}else{
			if(!$sun_fs && !$sun_fe){
				$sunF = $langs->trans("closed");
			}else{
				$sunF = $sun_fs." - ".$sun_fe;
			}
			if(!$sun_ss && !$sun_se){
				$sunS = $langs->trans("closed");
			}else{
				$sunS = $sun_ss." - ".$sun_se;
			}
		}
	}
		print '<table class="liste formdoc noborder centpercent" id="days-table">
				<tbody>
					<tr class="liste_titre">
						<th class="wrapcolumntitle liste_titre">'.$langs->trans("Day").'</th>
						<th class="wrapcolumntitle liste_titre">'.$langs->trans("FirstShift").'</th>
						<th class="wrapcolumntitle liste_titre">'.$langs->trans("SecondShift").'</th>
						<th class="wrapcolumntitle liste_titre">'.$langs->trans("goodsDelivery").'</th>
					</tr>
					<tr class="oddeven">
						<td class="nowraponall">'.$mon.'</td>
						<td class="nowraponall">'.$monF.'</td>
						<td class="nowraponall">'.$monS.'</td>
						<td class="nowraponall">'.$mon_full_goods.'</td>
					</tr>
					<tr class="oddeven">
						<td class="nowraponall">'.$tue.'</td>
						<td class="nowraponall">'.$tueF.'</td>
						<td class="nowraponall">'.$tueS.'</td>
						<td class="nowraponall">'.$tue_full_goods.'</td>
					</tr>
					<tr class="oddeven">
						<td class="nowraponall">'.$wed.'</td>
						<td class="nowraponall">'.$wedF.'</td>
						<td class="nowraponall">'.$wedS.'</td>
						<td class="nowraponall">'.$wed_full_goods.'</td>
					</tr>
					<tr class="oddeven">
						<td class="nowraponall">'.$thu.'</td>
						<td class="nowraponall">'.$thuF.'</td>
						<td class="nowraponall">'.$thuS.'</td>
						<td class="nowraponall">'.$thur_full_goods.'</td>
					</tr>
					<tr class="oddeven">
						<td class="nowraponall">'.$fri.'</td>
						<td class="nowraponall">'.$friF.'</td>
						<td class="nowraponall">'.$friS.'</td>
						<td class="nowraponall">'.$fri_full_goods.'</td>
					</tr>
					<tr class="oddeven">
						<td class="nowraponall">'.$sat.'</td>
						<td class="nowraponall">'.$satF.'</td>
						<td class="nowraponall">'.$satS.'</td>
						<td class="nowraponall">'.$sat_full_goods.'</td>
					</tr>
					<tr class="oddeven">
						<td class="nowraponall">'.$sun.'</td>
						<td class="nowraponall">'.$sunF.'</td>
						<td class="nowraponall">'.$sunS.'</td>
						<td class="nowraponall">'.$sun_full_goods.'</td>
					</tr>
				</tbody>';
		print '</table>';
		print '<br>';
		// var_dump($daysList[0]["hours"]["open-f"]);
		print '<div>';
			print '<button class="butAction" id="del" onclick="sol()">'.$langs->trans("editHours").'</button>';
			print '<button class="butAction" id="del1" onclick="sol1()">'.$langs->trans("edit").'</button>';
		print '</div>';
	print '<div id="modal" class="modal">
				<!-- Modal content -->
				<form action="" method="POST"><input type="hidden" name="token" value="'.newToken().'">
					<div class="modal-content">
						<div class="modal-header">
							<span class="close" id="close">&times;</span>
						</div>
						<div class="modal-body">
							<label for="monday" style="font-weight: bold">- '.$langs->trans("monday").' -</label>
	 						<div id="monday">
								<label for="monday-open">'.$langs->trans("firstShiftOpen").'</label>
									<input type="time" name="monday-open-f" id="monday-open-f" value="'.$mon_fs.'">
								<label for="monday-close">'.$langs->trans("firstShiftClose").'</label>
									<input type="time" name="monday-close-f" id="monday-close-f" value="'.$mon_fe.'">
								<label for="monday-open">'.$langs->trans("secondShiftOpen").'</label>
									<input type="time" name="monday-open-s" id="monday-open-s" value="'.$mon_ss.'">
								<label for="monday-close">'.$langs->trans("secondShiftClose").'</label>
									<input type="time" name="monday-close-s" id="monday-close-s" value="'.$mon_se.'">
							</div>
							<br>
							<label for="tuesday" style="font-weight: bold">- '.$langs->trans("tuesday").' -</label>
	 						<div id="tuesday">
								<label for="tuesday-open">'.$langs->trans("firstShiftOpen").'</label>
								<input type="time" name="tuesday-open-f" id="tuesday-open-f" value="'.$tue_fs.'">
								<label for="tuesday-close">'.$langs->trans("firstShiftClose").'</label>
								<input type="time" name="tuesday-close-f" id="tuesday-close-f" value="'.$tue_fe.'">
								<label for="tuesday-open">'.$langs->trans("secondShiftOpen").'</label>
								<input type="time" name="tuesday-open-s" id="tuesday-open-s" value="'.$tue_ss.'">
								<label for="tuesday-close">'.$langs->trans("secondShiftClose").'</label>
								<input type="time" name="tuesday-close-s" id="tuesday-close-s" value="'.$tue_se.'">
							</div>
							<br>
							<label for="wednesday" style="font-weight: bold">- '.$langs->trans("wednesday").' -</label>
	 						<div id="wednesday">
								<label for="wednesday-open">'.$langs->trans("firstShiftOpen").'</label>
								<input type="time" name="wednesday-open-f" id="wednesday-open-f" value="'.$wed_fs.'">
								<label for="wednesday-close">'.$langs->trans("firstShiftClose").'</label>
								<input type="time" name="wednesday-close-f" id="wednesday-close-f" value="'.$wed_fe.'">
								<label for="wednesday-open">'.$langs->trans("secondShiftOpen").'</label>
								<input type="time" name="wednesday-open-s" id="wednesday-open-s" value="'.$wed_ss.'">
								<label for="wednesday-close">'.$langs->trans("secondShiftClose").'</label>
								<input type="time" name="wednesday-close-s" id="wednesday-close-s" value="'.$wed_se.'">
							</div>
							<br>
							<label for="thursday" style="font-weight: bold">- '.$langs->trans("thursday").' -</label>
	 						<div id="thursday">
								<label for="thursday-open">'.$langs->trans("firstShiftOpen").'</label>
								<input type="time" name="thursday-open-f" id="thursday-open-f" value="'.$thu_fs.'">
								<label for="thursday-close">'.$langs->trans("firstShiftClose").'</label>
								<input type="time" name="thursday-close-f" id="thursday-close-f" value="'.$thu_fe.'">
								<label for="thursday-open">'.$langs->trans("secondShiftOpen").'</label>
								<input type="time" name="thursday-open-s" id="thursday-open-s" value="'.$thu_ss.'">
								<label for="thursday-close">'.$langs->trans("secondShiftClose").'</label>
								<input type="time" name="thursday-close-s" id="thursday-close-s" value="'.$thu_se.'">
							</div>
							<br>
							<label for="friday" style="font-weight: bold">- '.$langs->trans("friday").' -</label>
	 						<div id="friday">
								<label for="friday-open">'.$langs->trans("firstShiftOpen").'</label>
								<input type="time" name="friday-open-f" id="friday-open-f" value="'.$fri_fs.'">
								<label for="friday-close">'.$langs->trans("firstShiftClose").'</label>
								<input type="time" name="friday-close-f" id="friday-close-f" value="'.$fri_fe.'">
								<label for="friday-open">'.$langs->trans("secondShiftOpen").'</label>
								<input type="time" name="friday-open-s" id="friday-open-s" value="'.$fri_ss.'">
								<label for="friday-close">'.$langs->trans("secondShiftClose").'</label>
								<input type="time" name="friday-close-s" id="friday-close-s" value="'.$fri_se.'">
							</div>
							<br>
							<label for="saturday" style="font-weight: bold">- '.$langs->trans("saturday").' -</label>
	 						<div id="saturday">
								<label for="saturday-open">'.$langs->trans("firstShiftOpen").'</label>
								<input type="time" name="saturday-open-f" id="saturday-open-f" value="'.$sat_fs.'">
								<label for="saturday-close">'.$langs->trans("firstShiftClose").'</label>
								<input type="time" name="saturday-close-f" id="saturday-close-f" value="'.$sat_fe.'">
								<label for="saturday-open">'.$langs->trans("secondShiftOpen").'</label>
								<input type="time" name="saturday-open-s" id="saturday-open-s" value="'.$sat_ss.'">
								<label for="saturday-close">'.$langs->trans("secondShiftClose").'</label>
								<input type="time" name="saturday-close-s" id="saturday-close-s" value="'.$sat_se.'">
							</div>
							<br>
							<label for="sunday" style="font-weight: bold">- '.$langs->trans("sunday").' -</label>
	 						<div id="sunday">
								<label for="sunday-open">'.$langs->trans("firstShiftOpen").'</label>
								<input type="time" name="sunday-open-f" id="sunday-open-f" value="'.$sun_fs.'">
								<label for="sunday-close">'.$langs->trans("firstShiftClose").'</label>
								<input type="time" name="sunday-close-f" id="sunday-close-f" value="'.$sun_fe.'">
								<label for="sunday-open">'.$langs->trans("secondShiftOpen").'</label>
								<input type="time" name="sunday-open-s" id="sunday-open-s" value="'.$sun_ss.'">
								<label for="sunday-close">'.$langs->trans("secondShiftClose").'</label>
								<input type="time" name="sunday-close-s" id="sunday-close-s" value="'.$sun_se.'">
							</div>
							<br><hr>
							<input type="submit" name="submit" value="'.$langs->trans("submit").'">';
				print ' </div>
						<div class="modal-footer">
						</div>
					</div></form>
			</div>';
			print '<hr>';
			// $query = 'SELECT goods FROM llx_stores_branch WHERE rowid = '.$id;
			// $list = $db->query($query)->fetch_row();
			// $goodsList = json_decode($list[0],true);
			// $mon_goods = "";$tue_goods = "";$wed_goods = "";$thur_goods = "";$fri_goods = "";$sat_goods = "";$sun_goods = "";
			// if($daysList != null){
			// 	$mon_goods = $goodsList[0]["hour"];
			// 	$tue_goods = $goodsList[1]["hour"];
			// 	$wed_goods = $goodsList[2]["hour"];
			// 	$thur_goods = $goodsList[3]["hour"];
			// 	$fri_goods = $goodsList[4]["hour"];
			// 	$sat_goods = $goodsList[5]["hour"];
			// 	$sun_goods = $goodsList[6]["hour"];
			// }
			// print '
			//   <table class="liste formdoc noborder centpercent">
			//   	<tbody>
			// 	  <tr class="liste_titre">
			// 		  <th class="wrapcolumntitle liste_titre" colspan="2">'.$langs->trans("goodsDelivery").'</th>
			// 	  </tr>
			// 	  <tr class="oddeven">
			// 	  	<td class="nowraponall">'.$langs->trans("monday").'</td>
			// 	  	<td class="nowraponall">'.$mon_goods.'</td>
			// 	  </tr>
			// 	  <tr class="oddeven">
			// 	  	<td class="nowraponall">'.$langs->trans("tuesday").'</td>
			// 	  	<td class="nowraponall">'.$tue_goods.'</td>
			// 	  </tr>
			// 	  <tr class="oddeven">
			// 	  	<td class="nowraponall">'.$langs->trans("wednesday").'</td>
			// 	  	<td class="nowraponall">'.$wed_goods.'</td>
			// 	  </tr>
			// 	  <tr class="oddeven">
			// 	  	<td class="nowraponall">'.$langs->trans("thursday").'</td>
			// 	  	<td class="nowraponall">'.$thur_goods.'</td>
			// 	  </tr>
			// 	  <tr class="oddeven">
			// 	  	<td class="nowraponall">'.$langs->trans("friday").'</td>
			// 	  	<td class="nowraponall">'.$fri_goods.'</td>
			// 	  </tr>
			// 	  <tr class="oddeven">
			// 	  	<td class="nowraponall">'.$langs->trans("saturday").'</td>
			// 	  	<td class="nowraponall">'.$sat_goods.'</td>
			// 	  </tr>
			// 	  <tr class="oddeven">
			// 	  	<td class="nowraponall">'.$langs->trans("sunday").'</td>
			// 	  	<td class="nowraponall">'.$sun_goods.'</td>
			// 	  </tr>
			// 	</tbody>
			//   </table>
			//   <button id="del1" onclick="sol1()">'.$langs->trans("edit").'</button>
			// ';
			print '<div id="goodsmodal" class="goodsmodal">
						<!-- Modal content -->
						<form action="" method="POST"><input type="hidden" name="token" value="'.newToken().'">
							<div class="goods-modal-content">
								<div class="goods-modal-header">
									<span class="goods-close" id="goods-close">&times;</span>
								</div>
								<div class="goods-modal-body">
									<div class="table-wrap">
										<label for="monday-goods">'.$langs->trans("monday").':</label>
										<label for="monday-goods">'.$langs->trans("Start").'</label>
										<input type="time" name="monday-goods" id="monday-goods" value="'.$mon_goods.'">
										<label for="monday-goods-end">'.$langs->trans("End").'</label>
										<input type="time" name="monday-goods-end" id="monday-goods-end" value="'.$mon_goods_end.'">
									</div>
									<br>
									<div class="table-wrap">
										<label for="tuesday-goods">'.$langs->trans("tuesday").':</label>
										<label for="tuesday-goods">'.$langs->trans("Start").'</label>
										<input type="time" name="tuesday-goods" id="tuesday-goods" value="'.$tue_goods.'">
										<label for="tuesday-goods-end">'.$langs->trans("End").'</label>
										<input type="time" name="tuesday-goods-end" id="tuesday-goods-end" value="'.$tue_goods_end.'">
									</div>
									<br>
									<div class="table-wrap">
										<label for="wednesday-goods">'.$langs->trans("wednesday").':</label>
										<label for="wednesday-goods">'.$langs->trans("Start").'</label>
										<input type="time" name="wednesday-goods" id="wednesday-goods" value="'.$wed_goods.'">
										<label for="wednesday-goods-end">'.$langs->trans("End").'</label>
										<input type="time" name="wednesday-goods-end" id="wednesday-goods-end" value="'.$wed_goods_end.'">
									</div>
									<br>
									<div class="table-wrap">
										<label for="thursday-goods">'.$langs->trans("thursday").':</label>
										<label for="thursday-goods">'.$langs->trans("Start").'</label>
										<input type="time" name="thursday-goods" id="thursday-goods" value="'.$thur_goods.'">
										<label for="thursday-goods-end">'.$langs->trans("End").'</label>
										<input type="time" name="thursday-goods-end" id="thursday-goods-end" value="'.$thur_goods_end.'">
									</div>
									<br>
									<div class="table-wrap">
										<label for="friday-goods">'.$langs->trans("friday").':</label>
										<label for="friday-goods">'.$langs->trans("Start").'</label>
										<input type="time" name="friday-goods" id="friday-goods" value="'.$fri_goods.'">
										<label for="friday-goods-end">'.$langs->trans("End").'</label>
										<input type="time" name="friday-goods-end" id="friday-goods-end" value="'.$fri_goods_end.'">
									</div>
									<br>
									<div class="table-wrap">
										<label for="saturday-goods">'.$langs->trans("saturday").':</label>
										<label for="saturday-goods">'.$langs->trans("Start").'</label>
										<input type="time" name="saturday-goods" id="saturday-goods" value="'.$sat_goods.'">
										<label for="saturday-goods-end">'.$langs->trans("End").'</label>
										<input type="time" name="saturday-goods-end" id="saturday-goods-end" value="'.$sat_goods_end.'">
									</div>
									<br>
									<div class="table-wrap">
										<label for="sunday-goods">'.$langs->trans("sunday").':</label>
										<label for="sunday-goods">'.$langs->trans("Start").'</label>
										<input type="time" name="sunday-goods" id="sunday-goods" value="'.$sun_goods.'">
										<label for="sunday-goods-end">'.$langs->trans("End").'</label>
										<input type="time" name="sunday-goods-end" id="sunday-goods-end" value="'.$sun_goods_end.'">
									</div>
									<br><hr>
									<input type="submit" name="submitGoods" value="'.$langs->trans("submit").'">';
						print ' </div>
								<div class="goods-modal-footer">
								</div>
							</div></form>
					</div>';
					print '<style>
						.table-wrap{
							display: flex;
							flex-wrap: wrap;
							column-gap: 1rem;
							row-gap: 1rem;
							text-align: center;
							align-items: center;
							align-content: center;
							justify-content: center;
						}
					</style>';
			if(isset($_POST['submitGoods'])) {
				$goods = [
					[
						"day" => "monday",
						"hour" => $_POST["monday-goods"],
						"hour_end" => $_POST["monday-goods-end"]
					],
					[
						"day" => "tuesday",
						"hour" => $_POST["tuesday-goods"],
						"hour_end" => $_POST["tuesday-goods-end"]
					],
					[
						"day" => "wednesday",
						"hour" => $_POST["wednesday-goods"],
						"hour_end" => $_POST["wednesday-goods-end"]
					],
					[
						"day" => "thursday",
						"hour" => $_POST["thursday-goods"],
						"hour_end" => $_POST["thursday-goods-end"]
					],
					[
						"day" => "friday",
						"hour" => $_POST["friday-goods"],
						"hour_end" => $_POST["friday-goods-end"]
					],
					[
						"day" => "saturday",
						"hour" => $_POST["saturday-goods"],
						"hour_end" => $_POST["saturday-goods-end"]
					],
					[
						"day" => "sunday",
						"hour" => $_POST["sunday-goods"],
						"hour_end" => $_POST["sunday-goods-end"]
					],
				];

				$goodsL = json_encode($goods);
				$sql = 'UPDATE llx_stores_branch set goods = "'.addslashes($goodsL).'" WHERE rowid = '.$id;
				$db->query($sql,0,'ddl');
				print '<script>window.location.href = window.location.href;
				</script>';
			}
			if(isset($_POST['submit'])) {
				$days = [
					[
						"day" => "monday",
						"hours" => [
							"open-f" => $_POST["monday-open-f"],
							"close-f" => $_POST["monday-close-f"],
							"open-s" => $_POST["monday-open-s"],
							"close-s" => $_POST["monday-close-s"],
						]
					],
					[
						"day" => "tuesday",
						"hours" => [
							"open-f" => $_POST["tuesday-open-f"],
							"close-f" => $_POST["tuesday-close-f"],
							"open-s" => $_POST["tuesday-open-s"],
							"close-s" => $_POST["tuesday-close-s"],
						]
					],
					[
						"day" => "wednesday",
						"hours" => [
							"open-f" => $_POST["wednesday-open-f"],
							"close-f" => $_POST["wednesday-close-f"],
							"open-s" => $_POST["wednesday-open-s"],
							"close-s" => $_POST["wednesday-close-s"],
						]
					],
					[
						"day" => "thursday",
						"hours" => [
							"open-f" => $_POST["thursday-open-f"],
							"close-f" => $_POST["thursday-close-f"],
							"open-s" => $_POST["thursday-open-s"],
							"close-s" => $_POST["thursday-close-s"],
						]
					],
					[
						"day" => "friday",
						"hours" => [
							"open-f" => $_POST["friday-open-f"],
							"close-f" => $_POST["friday-close-f"],
							"open-s" => $_POST["friday-open-s"],
							"close-s" => $_POST["friday-close-s"],
						]
					],
					[
						"day" => "saturday",
						"hours" => [
							"open-f" => $_POST["saturday-open-f"],
							"close-f" => $_POST["saturday-close-f"],
							"open-s" => $_POST["saturday-open-s"],
							"close-s" => $_POST["saturday-close-s"],
						]
					],
					[
						"day" => "sunday",
						"hours" => [
							"open-f" => $_POST["sunday-open-f"],
							"close-f" => $_POST["sunday-close-f"],
							"open-s" => $_POST["sunday-open-s"],
							"close-s" => $_POST["sunday-close-s"],
						]
					],
				];

				$daysL = json_encode($days);
				$sql = 'UPDATE llx_stores_branch set days = "'.addslashes($daysL).'" WHERE rowid = '.$id;
				$db->query($sql,0,'ddl');
				print '<script>window.location.href = window.location.href;
				</script>';
			}
	print '<script>';
	print 'function sol(){
		var m = document.getElementById("modal");
		
		m.style.display = "block";
		var span = document.getElementById("close");

		span.onclick = function() { 
			m.style.display = "none";
		}
		
		window.onclick = function(event) {
		if (event.target == m) {
				m.style.display = "none";
			}
		}
	}';
	print 'function sol1(){
		var me = document.getElementById("goodsmodal");
		
		me.style.display = "block";
		var span = document.getElementById("goods-close");

		span.onclick = function() { 
			me.style.display = "none";
		}
		
		window.onclick = function(event) {
		if (event.target == me) {
				me.style.display = "none";
			}
		}
	}';
	print '</script>'; 
	print '<style>
	#days-table {
		font-family: arial, sans-serif;
		border-collapse: collapse;
		width: 100%;
	  }
	  
	#days-table td, th {
		text-align: left;
		padding: 8px;
	}
	  
	#days-table tr:nth-child(even) {
		background-color: #dddddd;
	}
	/* The Modal (background) */
	.modal-image {
		overflow: auto;
		// float: left;
	}
	.edit-icon{
	  display: flex;
	  align-items: center;
	}
	.group-header input:disabled, textarea:disabled, select[disabled="disabled"] {
	  background: none;
	}
	.group-header{
	  background-color: #4444;
	  display: flex;
	  justify-content: space-between;
	  padding: 0px 10px 0px 10px;
	}
	.add-icon{
	  display: flex;
	  align-items: center;
	}
	.group-element{
	  display: inline-flex;
	  flex-direction: column;
	  padding: 7px;
	  column-gap: 1px;
	  text-align: center;
	  border: 1px solid #4444;
	  margin: 6px;
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
	.goodsmodal {
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
	.goods-modal-content {
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
	.goods-close {
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
	
	.goods-close:hover,
	.goods-close:focus {
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
		overflow-y: scroll;
		height: 70%;
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
	
	.goods-modal-header {
	  height: 3em;  
	  padding: 2px 16px;
	  background-color: #e9e9e9;
	  color: white;
	}
	
	.goods-modal-header p {
		float: left;
		color: black;
		cursor: pointer;
	}
	.goods-modal-body {    
		padding: 2px 16px;
		overflow-y: scroll;
		height: 70%;
		text-align: center;
	}
	.goods-modal-body img{
		width: 50%;
		height: 35rem
	}
	
	.goods-modal-footer {
	  padding: 2px 16px;
	  background-color: #e9e9e9;
	  color: white;
	}
	</style>';
	// print '<div class="dist" style="display:block; align-items: center;margin: 5px;"><h4 style="float: left;">'.$langs->trans('Districtmanager').': </h4><p style="float: right;"> '.$object->district_manager.'</p></div>';
	
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
		// print 1;
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
			print dolGetButtonAction($langs->trans('Open Ticket'), '', 'default', dol_buildpath('/ticket/card.php', 1).'?action=create&store='.$object->id.'&third='.$object->fk_soc.'&token='.newToken(), '', $permissiontoadd);

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
		print '<div class="fichecenter">';
		// bottom right
		//	end bottom right
		// bottom left
		print '<div class="fichehalfleft">';
		print '<a name="builddoc"></a>'; // ancre


		

		print '</div>';

		print '<div class="fichehalfright">';

		// $sql = "SELECT count(*) FROM ".MAIN_DB_PREFIX."stores_branch;";
		// $total = $db->query($sql)->fetch_row()[0];
		// $stateSql = "SELECT t.nom, (SELECT count(*) FROM ".MAIN_DB_PREFIX."ticket WHERE fk_soc = t.rowid) as 'count' FROM ".MAIN_DB_PREFIX."societe AS t order by count desc;";
		// $states = $db->query($stateSql)->fetch_all();
		// // var_dump($states);
		// $ss = array();
		// foreach($states as $elem){
		// 	$ss[] = array($elem[0], $elem[1]);
		// }
		// // var_dump($ss);
		// $thirdpartygraph = '<div class="div-table-responsive-no-min">';
		// $thirdpartygraph .= '<table class="noborder nohover centpercent">'."\n";
		// $thirdpartygraph .= '<tr class="liste_titre"><th colspan="2">'.$langs->trans("Statistics").'</th></tr>';
		// $thirdpartygraph .= '<tr><td class="center" colspan="2">';
		
		// include_once DOL_DOCUMENT_ROOT.'/core/class/dolgraph.class.php';
		// $dolgraph = new DolGraph();
		// $dolgraph->SetData($ss);
		// $dolgraph->setShowLegend(2);
		// $dolgraph->setShowPercent(1);
		// $dolgraph->SetType(array('pie'));
		// $dolgraph->setHeight('500');
		// $dolgraph->draw('idgraphthirdparties');
		// $thirdpartygraph .= $dolgraph->show();
		// $thirdpartygraph .= '</td></tr>'."\n";
		// $thirdpartygraph .= '<tr class="liste_total"><td>'.$langs->trans("UniqueStores").'</td><td class="right">';
		// $thirdpartygraph .= $total;
		// $thirdpartygraph .= '</td></tr>';
		// $thirdpartygraph .= '</table>';
		// $thirdpartygraph .= '</div>';

		// print $thirdpartygraph;		

		print '</div>';
	}

	//Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	// Presend form
	$modelmail = 'branch';
	$defaulttopic = 'InformationMessage';
	$diroutput = $conf->stores->dir_output;
	$trackid = 'branch'.$object->id;

	include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';
}

// End of page
llxFooter();
$db->close();
