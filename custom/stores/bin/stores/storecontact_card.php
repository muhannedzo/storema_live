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
 *   	\file       storecontact_card.php
 *		\ingroup    stores
 *		\brief      Page to create/edit/view storecontact
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
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formadmin.class.php';
require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/contact.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
// require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
// require_once DOL_DOCUMENT_ROOT.'/core/class/html.formadmin.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
dol_include_once('/stores/class/storecontact.class.php');
dol_include_once('/stores/lib/stores_storecontact.lib.php');
require_once __DIR__.'/class/branch.class.php';

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
$object = new StoreContact($db);
$extrafields = new ExtraFields($db);
$storeNumber = "";
$socid = 0;
if($store){
	$storeObject = new Branch($db);
	$storeObject->fetch($store);
	$storeNumber = $storeObject->b_number;
	$socid = $storeObject->fk_soc;
	$objsoc = new Societe($db);
	// if ($socid > 0) {
		$objsoc->fetch($storeObject->fk_soc);
	// }
	// var_dump($storeObject->b_number);
}
$object->getCanvas($id);
$objcanvas = null;
$canvas = (!empty($object->canvas) ? $object->canvas : GETPOST("canvas"));
if (!empty($canvas)) {
	require_once DOL_DOCUMENT_ROOT.'/core/class/canvas.class.php';
	$objcanvas = new Canvas($db, $action);
	$objcanvas->getCanvas('contact', 'contactcard', $canvas);
}

$diroutputmassaction = $conf->stores->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('storecontactcard', 'globalcard')); // Note that conf->hooks_modules contains array

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
if ($enablepermissioncheck) {
	$permissiontoread = $user->rights->stores->storecontact->read;
	$permissiontoadd = $user->rights->stores->storecontact->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = $user->rights->stores->storecontact->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
	$permissionnote = $user->rights->stores->storecontact->write; // Used by the include of actions_setnotes.inc.php
	$permissiondellink = $user->rights->stores->storecontact->write; // Used by the include of actions_dellink.inc.php
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
	$permissiontodelete = 1;
	$permissionnote = 1;
	$permissiondellink = 1;
}

$upload_dir = $conf->stores->multidir_output[isset($object->entity) ? $object->entity : 1].'/storecontact';

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (isset($object->status) && ($object->status == $object::STATUS_DRAFT) ? 1 : 0);
//restrictedArea($user, $object->element, $object->id, $object->table_element, '', 'fk_soc', 'rowid', $isdraft);
if (empty($conf->stores->enabled)) accessforbidden();
if (!$permissiontoread) accessforbidden();


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

	$backurlforlist = dol_buildpath('/stores/storecontact_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/stores/storecontact_card.php', 1).'?id='.((!empty($id) && $id > 0) ? $id : '__ID__');
			}
		}
	}

	$triggermodname = 'STORES_STORECONTACT_MODIFY'; // Name of trigger action code to execute when we modify record

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
	$triggersendname = 'STORES_STORECONTACT_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_STORECONTACT_TO';
	$trackid = 'storecontact'.$object->id;
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
$formadmin = new FormAdmin($db);
$countrynotdefined = $langs->trans("ErrorSetACountryFirst").' ('.$langs->trans("SeeAbove").')';

$title = $langs->trans("StoreContact");
$help_url = '';
llxHeader('', $title, $help_url);
$images = "";
$image = "";
if($object->photo){
	$images = "img/contact/".$object->photo;
	$image = $object->photo;
}else{
	$images = "img/contact/placeholder.png";
	$image = "placeholder.png";
}
print '<script>';
print '$(document).ready(function() {
	$(".fa-file").remove();
	element = document.createElement("a");
	element.className = "pictopreview documentpreview";
	element.href = "'.$images.'";
	$(".photoref").append(element);
	newElement = document.createElement("img");
	newElement.src = "img/contact/'.$image.'";
	newElement.width= "100";
	$(".pictopreview").attr("mime", "image/jpeg");
	$(".pictopreview").append(newElement);
});';
print '</script>';

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
			$("#image").change(function(e) {
				e.preventDefault();
			
				var formData = new FormData();
				formData.append("image", $("#image")[0].files[0]);
				console.log($("#image")[0].files[0]);
				$.ajax({
				  url: "upload.php",
				  type: "POST",
				  data: formData,
				  contentType: false,
				  processData: false,
				  success: function(response) {
					console.log(response);
					$("#contact-image").show();
					$("#contact-image").attr("src", "");
					$("#contact-image").attr("src", "img/contact/" + response);
					$("#photo-contact").attr("value", response);
				  }
				});
				
			  });
			const cookies = document.cookie.split(";");

			for (let i = 0; i < cookies.length; i++) {
				const cookie = cookies[i];
				const eqPos = cookie.indexOf("=");
				const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
				document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
			}

			document.getElementById("ref").value = document.getElementById("firstname").value + " " + document.getElementById("lastname").value;
			$("#lastname").change(function() {
				document.getElementById("ref").value = document.getElementById("firstname").value + " " + document.getElementById("lastname").value;
			});

			document.getElementById("country").value = $("#selectcountry_id option:selected").text();
			$("#selectcountry_id").change(function (){
				$.post("branch_card.php", {country_id: $("#selectcountry_id option:selected").text()},
				function(data){
						document.getElementById("country").value = $("#selectcountry_id option:selected").text();
						document.cookie = "country="+$("#selectcountry_id option:selected").text();
						document.cookie = "lastname="+$("#lastname").val();
						document.cookie = "firstname="+$("#firstname").val();
						document.cookie = "civility="+$("#civility option:selected").val();
						document.cookie = "title="+$("#title").val();
						document.cookie = "address="+$("#address").val();
						document.cookie = "zipcode="+$("#zipcode").val();
						document.cookie = "town="+$("#town").val();
						document.cookie = "country_id="+$("#selectcountry_id option:selected").val();
						document.cookie = "phone="+$("#phone").val();
						document.cookie = "phone_perso="+$("#phone_perso").val();
						document.cookie = "phone_mobile="+$("#phone_mobile").val();
						document.cookie = "fax="+$("#fax").val();
						document.cookie = "email="+$("#email").val();
						document.cookie = "priv="+$("#priv option:selected").val();
						document.cookie = "default_lang="+$("#default_lang option:selected").val();
						document.cookie = "birthday="+$("#birthday").val();
						
						location.reload();
					}
				);

			});

			document.getElementById("state").value = $("#state_id option:selected").text();
			$("#state_id").change(function (){
				document.getElementById("state").value = $("#state_id option:selected").text();
			});

			document.getElementById("visibility").value = $("#priv option:selected").text();
			$("#priv").change(function (){
				document.getElementById("visibility").value = $("#priv option:selected").text();
			});

			$("#copyaddressfromstore").click(function(){
				document.cookie = "copyClicked=1";
				document.getElementById("country").value = $("#selectcountry_id option:selected").text();
				document.cookie = "country="+$("#selectcountry_id option:selected").text();
				document.cookie = "lastname="+$("#lastname").val();
				document.cookie = "firstname="+$("#firstname").val();
				document.cookie = "civility="+$("#civility option:selected").val();
				document.cookie = "title="+$("#title").val();
				document.cookie = "address="+$("#address").val();
				document.cookie = "zipcode="+$("#zipcode").val();
				document.cookie = "town="+$("#town").val();
				document.cookie = "country_id="+$("#selectcountry_id option:selected").val();
				document.cookie = "phone="+$("#phone").val();
				document.cookie = "phone_perso="+$("#phone_perso").val();
				document.cookie = "phone_mobile="+$("#phone_mobile").val();
				document.cookie = "fax="+$("#fax").val();
				document.cookie = "email="+$("#email").val();
				document.cookie = "priv="+$("#priv option:selected").val();
				document.cookie = "default_lang="+$("#default_lang option:selected").val();
				document.cookie = "birthday="+$("#birthday").val();
						
				location.reload();
			});

		});';
		print '</script>'."\n";
	}

	// print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("StoreContact")), '', 'object_'.$object->picto);


	// $title = (!empty($conf->global->SOCIETE_ADDRESSES_MANAGEMENT) ? $langs->trans("NewContact") : $langs->trans("NewContactAddress"));
	$linkback = '';
	print load_fiche_titre($title, $linkback, 'address');

	// Show errors
	dol_htmloutput_errors(is_numeric($error) ? '' : $error, $errors);


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

	$lastname = "";
	$firstname = "";
	if(isset($_COOKIE["lastname"])){
		$lastname = $_COOKIE["lastname"];
		// var_dump($lastname);
	}	
	if(isset($_COOKIE["firstname"])){
		$firstname = $_COOKIE["firstname"];
		// var_dump($firstname);
	}	

	// Store
	if($storeNumber != ""){
		print '<tr><td><label for="storeId">'.$langs->trans("Store").'</label></td>';
		print '<td colspan="3" class="maxwidthonsmartphone">';
		print '<span class="fa fa-store"></span> <a href="./branch_card.php?id='.$store.'">'.$storeNumber.'</a>';
		print '</td>';
		print '<input type="hidden" name="storeId" id="storeId" value="'.$storeObject->id.'">';
		print '</td></tr>';
		print '<input type="hidden" name="fk_soc" id="fk_soc" value="'.$storeObject->fk_soc.'">';
	}else{
		print '<select name="storeId" id="storeId">
					<option value="">--Please choose an option--</option>';
						foreach($resStore as $elem){
								print '<option value="'.$elem[0].'">'.$elem[1].'</option>';				
						}
		print '</select>';
		print '<input type="hidden" name="fk_soc" id="fk_soc" value="">';

	}

	
	// Name
	print '<tr><td class="titlefieldcreate fieldrequired"><label for="lastname">'.$langs->trans("Lastname").' / '.$langs->trans("Label").'</label></td>';
	print '<td colspan="3"><input name="lastname" id="lastname" type="text" class="maxwidth100onsmartphone" maxlength="80" value="'.dol_escape_htmltag(GETPOST("lastname", 'alpha') ?GETPOST("lastname", 'alpha') : $lastname).'" autofocus="autofocus"></td>';
	print '</tr>';

	print '<tr>';
	print '<td><label for="firstname">';
	print $form->textwithpicto($langs->trans("Firstname"), $langs->trans("KeepEmptyIfGenericAddress")).'</label></td>';
	print '<td colspan="3"><input name="firstname" id="firstname"type="text" class="maxwidth100onsmartphone" maxlength="80" value="'.dol_escape_htmltag(GETPOST("firstname", 'alpha') ?GETPOST("firstname", 'alpha') : $firstname).'"></td>';
	print '</tr>';

	$civility = "";
	if(isset($_COOKIE["civility"])){
		$civility = $_COOKIE["civility"];
	}	
	// Civility
	print '<tr><td><label for="civility">'.$langs->trans("UserTitle").'</label></td><td colspan="3">';
	print $formcompany->select_civility($civility, 'civility');
	print '</td></tr>';

	$position = "";
	if(isset($_COOKIE["title"])){
		$position = $_COOKIE["title"];
		// var_dump($civility);
	}
	// Job position
	print '<tr><td><label for="title">'.$langs->trans("PostOrFunction").'</label></td>';
	print '<td colspan="3"><input name="poste" id="title" type="text" class="minwidth100" maxlength="255" value="'.dol_escape_htmltag(GETPOSTISSET("poste") ?GETPOST("poste", 'alphanohtml') : $position).'"></td>';


	$zipcode = "";
	$town = "";
	if(isset($_COOKIE["copyClicked"])){
		$zipcode = $storeObject->postal_code;
		$town = $storeObject->city;	
	}else{
		if(isset($_COOKIE["zipcode"])){
			$zipcode = $_COOKIE["zipcode"];
		}
		if(isset($_COOKIE["town"])){
			$town = $_COOKIE["town"];
		}
	}


	$address = "";
	if(isset($_COOKIE["copyClicked"])){
		if($storeObject->street && $storeObject->house_number){
			$address = $storeObject->street.", ".$storeObject->house_number;
		}
		if($storeObject->street && !$storeObject->house_number){
			$address = $storeObject->street;
		}
		if(!$storeObject->street && $storeObject->house_number){
			$address = $storeObject->house_number;
		}
	}else{
		if(isset($_COOKIE["address"])){
			$address = $_COOKIE["address"];
		}
	}
	// Address
	$colspan = 2;
	print '<tr><td><label for="address">'.$langs->trans("Address").'</label></td>';
	print '<td colspan="'.$colspan.'" id="contactaddress">
			<textarea class="flat quatrevingtpercent" name="address" id="address" rows="'.ROWS_2.'">'.(GETPOST("address", 'alpha') ?GETPOST("address", 'alpha') : $address).'</textarea>
	</td>';
	// $socid = 1;
	if ($conf->use_javascript_ajax && $socid > 0) {
		$rowspan = 3;
		if (empty($conf->global->SOCIETE_DISABLE_STATE)) {
			$rowspan++;
		}

		print '<td class="valignmiddle center" rowspan="'.$rowspan.'">';
		print '<a href="#" id="copyaddressfromstore">'.$langs->trans('Copyadressfromstoredetails').'</a>';
		print '</td>';
	}
	print '</tr>';

	// Zip / Town
	print '<tr><td><label for="zipcode">'.$langs->trans("Zip").'</label> / <label for="town">'.$langs->trans("Town").'</label></td><td colspan="'.$colspan.'" class="maxwidthonsmartphone" id="contactzipcity">';
	print $formcompany->select_ziptown((GETPOST("zipcode", 'alpha') ? GETPOST("zipcode", 'alpha') : $zipcode), 'zipcode', array('town', 'selectcountry_id', 'state_id'), 6).'&nbsp;';
	print $formcompany->select_ziptown((GETPOST("town", 'alpha') ? GETPOST("town", 'alpha') : $town), 'town', array('zipcode', 'selectcountry_id', 'state_id'));
	print '</td></tr>';


	$country_id = "";
	$country_code = "";
	if(isset($_COOKIE["copyClicked"])){
		$country_id = $storeObject->country_id;
		$tmparray = getCountry($country_id, 'all');
		$country_code = $tmparray['code'];
	}else{
		if(isset($_COOKIE["country_id"])){
			$country_id = $_COOKIE["country_id"];
			$tmparray = getCountry($country_id, 'all');
			$country_code = $tmparray['code'];
		}	
	}
	
	// country
	$country_select = $form->select_country($country_code, 'country_id', '', 0, 'minwidth300 maxwidth500 widthcentpercentminusx');
	print '<tr><td><label for="country">'.$langs->trans("Country").'</label></td><td id="contact-country">';
	print img_picto('', 'globe-americas', 'class="paddingrightonly"').$country_select;
	print '<input type="text" name="country" id="country" value="" hidden></td>';
	print '</tr>';

	// state
	print '<tr><td><label for="state">'.$langs->trans("State").'</label></td><td id="contact-state">'; 
	
	$state_id = $object->state_id;
	if(isset($_COOKIE["copyClicked"])){
		$state_id = $storeObject->state_id;
	}

	print $formcompany->select_state(GETPOST('state_id') != '' ?GETPOST('state_id') : $state_id, $country_code);
	print '<input type="text" name="state" id="state" value="" hidden>';
	print '</td></tr>';


	$phone = "";
	if(isset($_COOKIE["phone"])){
		$phone = $_COOKIE["phone"];
		// var_dump($civility);
	}
	$phone_perso = "";
	if(isset($_COOKIE["phone_perso"])){
		$phone_perso = $_COOKIE["phone_perso"];
		// var_dump($civility);
	}
	$phone_mobile = "";
	if(isset($_COOKIE["phone_mobile"])){
		$phone_mobile = $_COOKIE["phone_mobile"];
		// var_dump($civility);
	}
	$fax = "";
	if(isset($_COOKIE["fax"])){
		$fax = $_COOKIE["fax"];
		// var_dump($civility);
	}
	$email = "";
	if(isset($_COOKIE["email"])){
		$email = $_COOKIE["email"];
		// var_dump($civility);
	}
	$priv = "";
	if(isset($_COOKIE["priv"])){
		$priv = $_COOKIE["priv"];
		// var_dump($civility);
	}
	$default_lang = "";
	if(isset($_COOKIE["default_lang"])){
		$default_lang = $_COOKIE["default_lang"];
		// var_dump($civility);
	}
	$birthday = "";
	if(isset($_COOKIE["birthday"])){
		$birthday = $_COOKIE["birthday"];
		// var_dump($civility);
	}

	// Phone / Fax
	print '<tr><td>'.$form->editfieldkey('Phone', 'phone_', '', $object, 0).'</td>';
	print '<td>';
	print img_picto('', 'object_phoning');
	print '<input type="text" name="phone" id="phone" class="maxwidth200" value="'.(GETPOSTISSET('phone') ? GETPOST('phone', 'alpha') : $phone).'"></td>';
	if ($conf->browser->layout == 'phone') {
		print '</tr><tr>';
	}
	print '<td>'.$form->editfieldkey('PhonePerso', 'phone_perso', '', $object, 0).'</td>';
	print '<td>';
	print img_picto('', 'object_phoning');
	print '<input type="text" name="phone_perso" id="phone_perso" class="maxwidth200" value="'.(GETPOSTISSET('phone_perso') ? GETPOST('phone_perso', 'alpha') : $phone_perso).'"></td></tr>';

	print '<tr><td>'.$form->editfieldkey('PhoneMobile', 'phone_mobile', '', $object, 0).'</td>';
	print '<td>';
	print img_picto('', 'object_phoning_mobile');
	print '<input type="text" name="phone_mobile" id="phone_mobile" class="maxwidth200" value="'.(GETPOSTISSET('phone_mobile') ? GETPOST('phone_mobile', 'alpha') : $phone_mobile).'"></td>';
	if ($conf->browser->layout == 'phone') {
		print '</tr><tr>';
	}
	print '<td>'.$form->editfieldkey('Fax', 'fax', '', $object, 0).'</td>';
	print '<td>';
	print img_picto('', 'object_phoning_fax');
	print '<input type="text" name="fax" id="fax" class="maxwidth200" value="'.(GETPOSTISSET('fax') ? GETPOST('fax', 'alpha') : $fax).'"></td>';
	print '</tr>';

	if (((isset($objsoc->typent_code) && $objsoc->typent_code == 'TE_PRIVATE') || !empty($conf->global->CONTACT_USE_COMPANY_ADDRESS)) && dol_strlen(trim($object->email)) == 0) {
		$object->email = $objsoc->email; // Predefined with third party
	}

	// Email
	print '<tr><td>'.$form->editfieldkey('EMail', 'email', '', $object, 0, 'string', '').'</td>';
	print '<td>';
	print img_picto('', 'object_email');
	print '<input type="text" name="email" id="email" value="'.(GETPOSTISSET('email') ? GETPOST('email', 'alpha') : $email).'"></td>';
	print '</tr>';

	// Visibility
	print '<tr><td><label for="priv">'.$langs->trans("ContactVisibility").'</label></td><td colspan="3">';
	$selectarray = array('0'=>$langs->trans("ContactPublic"), '1'=>$langs->trans("ContactPrivate"));
	print $form->selectarray('priv', $selectarray, (GETPOST("priv", 'alpha') ?GETPOST("priv", 'alpha') : $priv), 0);
	print '<input type="text" name="visibility" id="visibility" value="" hidden>';
	print '</td></tr>';

	// photo
	print '<tr><td><label for="priv">'.$langs->trans("photo").'</label></td><td colspan="3">';
	print '<img id="contact-image" style="display:none; border: 1px solid grey; border-radius: 10px" width="100px">';
	print '</td></tr>';
	print '<tr>';
		print '<td></td>';
		print '<td colspan="3">';
			print '<input type="file" id="image">';
			print '<input type="text" name="photo" id="photo-contact" hidden>';
		print '</td>';
	print '</tr>';

	//Default language
	if (!empty($conf->global->MAIN_MULTILANGS)) {
		print '<tr><td>'.$form->editfieldkey('DefaultLang', 'default_lang', '', $object, 0).'</td><td colspan="3" class="maxwidthonsmartphone">'."\n";
		print img_picto('', 'language', 'class="pictofixedwidth"').$formadmin->select_language(GETPOST('default_lang', 'alpha') ? GETPOST('default_lang', 'alpha') : ($default_lang ? $default_lang : ''), 'default_lang', 0, 0, 1, 0, 0, 'maxwidth200onsmartphone');
		print '</td>';
		print '</tr>';
	}

	// Categories
	if (!empty($conf->categorie->enabled) && !empty($user->rights->categorie->lire)) {
		print '<tr><td>'.$form->editfieldkey('Categories', 'contcats', '', $object, 0).'</td><td colspan="3">';
		$cate_arbo = $form->select_all_categories(Categorie::TYPE_CONTACT, null, 'parent', null, null, 1);
		print img_picto('', 'category').$form->multiselectarray('contcats', $cate_arbo, GETPOST('contcats', 'array'), null, null, null, null, '90%');
		print "</td></tr>";
	}

	// Contact by default
	// if (!empty($socid)) {
	// 	print '<tr><td>'.$langs->trans("ContactByDefaultFor").'</td>';
	// 	print '<td colspan="3">';
	// 	$contactType = $object->listeTypeContacts('external', '', 1);
	// 	print $form->multiselectarray('roles', $contactType, array(), 0, 0, 'minwidth500');
	// 	print '</td></tr>';
	// }

	// Other attributes
	$parameters = array('socid' => $socid, 'objsoc' => $objsoc, 'colspan' => ' colspan="3"', 'cols' => 3, 'colspanvalue' => 3);
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_add.tpl.php';

	print "</table><br>";

	print '<hr style="margin-bottom: 20px">';

	// Add personnal information
	print load_fiche_titre('<div class="comboperso">'.$langs->trans("PersonalInformations").'</div>', '', '');

	print '<table class="border centpercent">';

	// Date To Birth
	print '<tr><td><label for="birthday">'.$langs->trans("DateOfBirth").'</label></td><td>';
	$form = new Form($db);
	if ($object->birthday) {
		print $form->selectDate($object->birthday, 'birthday', 0, 0, 0, "perso", 1, 0);
	} else {
		print $form->selectDate('', 'birthday', 0, 0, 1, "perso", 1, 0);
	}
	print '</td>';
	// status
	print '<tr class="oddeven"><td class="fieldrequired wordbreak"><label for="status">'.$langs->trans("Status").'</label></td><td>';
	print '<select name="status" id="status">
				<option value="0">Draft</option>
				<option value="1">Validated</option>
				<option value="9">Canceled</option>
			</select>'."\n";

	// ref
	print '<input type="hidden" name="ref" id="ref"></td></tr>'."\n";


	


	// Common attributes
	// include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_add.tpl.php';

	// Other attributes
	// include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_add.tpl.php';

	print '</table>'."\n";

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel("Create");

	print '</form>';
	//dol_set_focus('input[name="ref"]');
}

// Part to edit record
if (($id || $ref) && $action == 'edit') {
	print load_fiche_titre($langs->trans("StoreContact"), '', 'object_'.$object->picto);

	if (!empty($conf->use_javascript_ajax)) {
		print "\n".'<script type="text/javascript">';
		print '$(document).ready(function () {
			$("#image").change(function(e) {
				e.preventDefault();
			
				var formData = new FormData();
				formData.append("image", $("#image")[0].files[0]);
				console.log($("#image")[0].files[0]);
				$.ajax({
				  url: "upload.php",
				  type: "POST",
				  data: formData,
				  contentType: false,
				  processData: false,
				  success: function(response) {
					console.log(response);
					$("#contact-image").show();
					$("#contact-image").attr("src", "");
					$("#contact-image").attr("src", "img/contact/" + response);
					$("#photo-contact").attr("value", "");
					$("#photo-contact").attr("value", response);
				  }
				});
				
			  });
			const cookies = document.cookie.split(";");

			for (let i = 0; i < cookies.length; i++) {
				const cookie = cookies[i];
				const eqPos = cookie.indexOf("=");
				const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
				document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
			}

			document.getElementById("ref").value = document.getElementById("firstname").value + " " + document.getElementById("lastname").value;
			$("#lastname").change(function() {
				document.getElementById("ref").value = document.getElementById("firstname").value + " " + document.getElementById("lastname").value;
			});

			document.getElementById("country").value = $("#selectcountry_id option:selected").text();
			$("#selectcountry_id").change(function (){
				$.post("branch_card.php", {country_id: $("#selectcountry_id option:selected").text()},
				function(data){
					// alsert(""); 
						document.getElementById("country").value = $("#selectcountry_id option:selected").text();
						document.cookie = "country="+$("#selectcountry_id option:selected").text();
						document.cookie = "lastname="+$("#lastname").val();
						document.cookie = "firstname="+$("#firstname").val();
						document.cookie = "civility="+$("#civility option:selected").val();
						document.cookie = "title="+$("#title").val();
						document.cookie = "address="+$("#address").val();
						document.cookie = "zipcode="+$("#zipcode").val();
						document.cookie = "town="+$("#town").val();
						document.cookie = "country_id="+$("#selectcountry_id option:selected").val();
						document.cookie = "phone="+$("#phone").val();
						document.cookie = "phone_perso="+$("#phone_perso").val();
						document.cookie = "phone_mobile="+$("#phone_mobile").val();
						document.cookie = "fax="+$("#fax").val();
						document.cookie = "email="+$("#email").val();
						document.cookie = "priv="+$("#priv option:selected").val();
						document.cookie = "default_lang="+$("#default_lang option:selected").val();
						document.cookie = "birthday="+$("#birthday").val();
						
						location.reload();
					}
				);

			});

			document.getElementById("state").value = $("#state_id option:selected").text();
			$("#state_id").change(function (){
				document.getElementById("state").value = $("#state_id option:selected").text();
			});

			document.getElementById("visibility").value = $("#priv option:selected").text();
			$("#priv").change(function (){
				document.getElementById("visibility").value = $("#priv option:selected").text();
			});

			$("#Copyadressfromstoredetails").click(function(){
				document.cookie = "copyClicked=1";
				document.getElementById("country").value = $("#selectcountry_id option:selected").text();
				document.cookie = "country="+$("#selectcountry_id option:selected").text();
				document.cookie = "lastname="+$("#lastname").val();
				document.cookie = "firstname="+$("#firstname").val();
				document.cookie = "civility="+$("#civility option:selected").val();
				document.cookie = "title="+$("#title").val();
				document.cookie = "address="+$("#address").val();
				document.cookie = "zipcode="+$("#zipcode").val();
				document.cookie = "town="+$("#town").val();
				document.cookie = "country_id="+$("#selectcountry_id option:selected").val();
				document.cookie = "phone="+$("#phone").val();
				document.cookie = "phone_perso="+$("#phone_perso").val();
				document.cookie = "phone_mobile="+$("#phone_mobile").val();
				document.cookie = "fax="+$("#fax").val();
				document.cookie = "email="+$("#email").val();
				document.cookie = "priv="+$("#priv option:selected").val();
				document.cookie = "default_lang="+$("#default_lang option:selected").val();
				document.cookie = "birthday="+$("#birthday").val();
						
				location.reload();
			});

		});';
		print '</script>'."\n";
	}
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
	// include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_edit.tpl.php';

	// Other attributes
	// include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_edit.tpl.php';
	if (GETPOSTISSET("country_id") || $object->country_id) {
		$tmparray = getCountry($object->country_id, 'all');
		$object->country_code = $tmparray['code'];
		$object->country      = $tmparray['label'];
	}
	
	$objsoc = new Societe($db);
	$objsoc->fetch($object->fk_soc);
	
	$objStore = new Branch($db);
	$objStore->fetch($object->storeId);
	

	// ref
	print '<input type="hidden" name="ref" id="ref"></td></tr>'."\n";

	//store
	print '<tr><td><label for="storeId">'.$langs->trans("Store").'</label></td>';
	print '<td colspan="3" class="maxwidthonsmartphone">';
	print '<span class="fa fa-store"></span> <a href="./branch_card.php?id='.$objStore->id.'">'.$objStore->b_number.'</a>';
	print '</td>';
	print '<input type="hidden" name="storeId" id="storeId" value="'.$objStore->id.'">';
	print '</td></tr>';
	print '<input type="hidden" name="fk_soc" id="fk_soc" value="'.$objStore->fk_soc.'">';

	$lastname = $object->lastname;
	$firstname = $object->firstname;
	if(isset($_COOKIE["lastname"])){
		$lastname = $_COOKIE["lastname"];
	}
	if(isset($_COOKIE["firstname"])){
		$firstname = $_COOKIE["firstname"];
	}
	// Lastname
	print '<tr><td class="titlefieldcreate fieldrequired"><label for="lastname">'.$langs->trans("Lastname").' / '.$langs->trans("Label").'</label></td>';
	print '<td colspan="3"><input name="lastname" id="lastname" type="text" class="minwidth200" maxlength="80" value="'.(GETPOSTISSET("lastname") ? GETPOST("lastname") : $lastname).'" autofocus="autofocus"></td>';
	print '</tr>';
	print '<tr>';
	// Firstname
	print '<td><label for="firstname">'.$langs->trans("Firstname").'</label></td>';
	print '<td colspan="3"><input name="firstname" id="firstname" type="text" class="minwidth200" maxlength="80" value="'.(GETPOSTISSET("firstname") ? GETPOST("firstname") : $firstname).'"></td>';
	print '</tr>';

	$civility = $object->civility;
	$title = $object->poste;
	if(isset($_COOKIE["civility"])){
		$civility = $_COOKIE["civility"];
	}
	if(isset($_COOKIE["title"])){
		$title = $_COOKIE["title"];
	}
	// Civility
	print '<tr><td><label for="civility">'.$langs->trans("UserTitle").'</label></td><td colspan="3">';
	print $formcompany->select_civility(GETPOSTISSET("civility") ? GETPOST("civility", "aZ09") : $civility, 'civility');
	print '</td></tr>';

	// Job position
	print '<tr><td><label for="title">'.$langs->trans("PostOrFunction").'</label></td>';
	print '<td colspan="3"><input name="poste" id="title" type="text" class="minwidth100" maxlength="255" value="'.dol_escape_htmltag(GETPOSTISSET("poste") ? GETPOST("poste", 'alphanohtml') : $title).'"></td></tr>';

	$address = $object->address;
	if(isset($_COOKIE["copyClicked"])){
		
		if($objStore->street && $objStore->house_number){
			$address = $objStore->street.", ".$objStore->house_number;
		}
		if($objStore->street && !$objStore->house_number){
			$address = $objStore->street;
		}
		if(!$objStore->street && $objStore->house_number){
			$address = $objStore->house_number;
		}
		
	}else{
		if(isset($_COOKIE["address"])){
			$address = $_COOKIE["address"];
		}
	}
	// Address
	print '<tr><td><label for="address">'.$langs->trans("Address").'</label></td>';
	print '<td colspan="3">';
	print '<div class="paddingrightonly valignmiddle inline-block quatrevingtpercent">';
	print '<textarea class="flat minwidth200 centpercent" name="address" id="address">'.(GETPOSTISSET("address") ? GETPOST("address", 'alphanohtml') : $address).'</textarea>';
	print '</div><div class="paddingrightonly valignmiddle inline-block">';
	if ($conf->use_javascript_ajax) {
		print '<a href="#" id="Copyadressfromstoredetails">'.$langs->trans('Copyadressfromstoredetails').'</a><br>';
	}
	print '</div>';
	print '</td>';


	$zipcode = $object->zipcode;
	$town = $object->town;
	if(isset($_COOKIE["copyClicked"])){
		$zipcode = $objStore->postal_code;
		$town = $objStore->city;	
	}else{
		if(isset($_COOKIE["zipcode"])){
			$zipcode = $_COOKIE["zipcode"];
		}
		if(isset($_COOKIE["town"])){
			$town = $_COOKIE["town"];
		}
	}

	// Zip / Town
	print '<tr><td><label for="zipcode">'.$langs->trans("Zip").'</label> / <label for="town">'.$langs->trans("Town").'</label></td><td colspan="3" class="maxwidthonsmartphone">';
	print $formcompany->select_ziptown((GETPOSTISSET("zipcode") ? GETPOST("zipcode") : $zipcode), 'zipcode', array('town', 'selectcountry_id', 'state_id'), 6).'&nbsp;';
	print $formcompany->select_ziptown((GETPOSTISSET("town") ? GETPOST("town") : $town), 'town', array('zipcode', 'selectcountry_id', 'state_id'));
	print '</td></tr>';

	

	$country_id = $object->country_id;
	$country_code = "";
	if(isset($_COOKIE["copyClicked"])){
		$country_id = $objStore->country_id;
		$tmparray = getCountry($country_id, 'all');
		$country_code = $tmparray['code'];
	}else{
		if(isset($_COOKIE["country_id"])){
			$country_id = $_COOKIE["country_id"];
			$tmparray = getCountry($country_id, 'all');
			$country_code = $tmparray['code'];
		}	
	}

	// Country
	print '<tr><td><label for="selectcountry_id">'.$langs->trans("Country").'</label></td><td colspan="3" class="maxwidthonsmartphone">';
	print img_picto('', 'globe-americas', 'class="paddingrightonly"');
	print $form->select_country(GETPOSTISSET("country_id") ? GETPOST("country_id") : $country_id, 'country_id');
	if ($user->admin) {
		print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"), 1);
	}
	print '<input type="text" name="country" id="country" value="" hidden>';
	print '</td></tr>';

	$state_id = $object->state_id;
	if(isset($_COOKIE["copyClicked"])){
		$state_id = $objStore->state_id;
	}
	
	// State
	if (empty($conf->global->SOCIETE_DISABLE_STATE)) {
		if (!empty($conf->global->MAIN_SHOW_REGION_IN_STATE_SELECT) && ($conf->global->MAIN_SHOW_REGION_IN_STATE_SELECT == 1 || $conf->global->MAIN_SHOW_REGION_IN_STATE_SELECT == 2)) {
			print '<tr><td><label for="state_id">'.$langs->trans('Region-State').'</label></td><td colspan="3" class="maxwidthonsmartphone">';
		} else {
			print '<tr><td><label for="state_id">'.$langs->trans('State').'</label></td><td colspan="3" class="maxwidthonsmartphone">';
		}

		print img_picto('', 'state', 'class="pictofixedwidth"');
		print $formcompany->select_state(GETPOSTISSET('state_id') ? GETPOST('state_id', 'alpha') : $state_id, $country_code, 'state_id');
		print '<input type="text" name="state" id="state" value="" hidden>';
		print '</td></tr>';
	}


	$phone = $object->phone;
	$phone_perso = $object->phone_perso;
	$phone_mobile = $object->phone_mobile;
	$fax = $object->fax;
	$email = $object->email;
	if(isset($_COOKIE["phone"])){
		$phone = $_COOKIE["phone"];
	}
	if(isset($_COOKIE["phone_perso"])){
		$phone_perso = $_COOKIE["phone_perso"];
	}
	if(isset($_COOKIE["phone_mobile"])){
		$phone_mobile = $_COOKIE["phone_mobile"];
	}
	if(isset($_COOKIE["fax"])){
		$fax = $_COOKIE["fax"];
	}
	if(isset($_COOKIE["email"])){
		$email = $_COOKIE["email"];
	}
	// Phone
	print '<tr><td>'.$form->editfieldkey('PhonePro', 'phone', GETPOST('phone', 'alpha'), $object, 0).'</td>';
	print '<td>';
	print img_picto('', 'object_phoning');
	print '<input type="text" name="phone" id="phone" class="maxwidth200" maxlength="80" value="'.(GETPOSTISSET('phone') ?GETPOST('phone', 'alpha') : $phone).'"></td>';
	print '<td>'.$form->editfieldkey('PhonePerso', 'fax', GETPOST('phone_perso', 'alpha'), $object, 0).'</td>';
	print '<td>';
	print img_picto('', 'object_phoning');
	print '<input type="text" name="phone_perso" id="phone_perso" class="maxwidth200" maxlength="80" value="'.(GETPOSTISSET('phone_perso') ?GETPOST('phone_perso', 'alpha') : $phone_perso).'"></td></tr>';

	print '<tr><td>'.$form->editfieldkey('PhoneMobile', 'phone_mobile', GETPOST('phone_mobile', 'alpha'), $object, 0, 'string', '').'</td>';
	print '<td>';
	print img_picto('', 'object_phoning_mobile');
	print '<input type="text" name="phone_mobile" id="phone_mobile" class="maxwidth200" maxlength="80" value="'.(GETPOSTISSET('phone_mobile') ?GETPOST('phone_mobile', 'alpha') : $phone_mobile).'"></td>';
	print '<td>'.$form->editfieldkey('Fax', 'fax', GETPOST('fax', 'alpha'), $object, 0).'</td>';
	print '<td>';
	print img_picto('', 'object_phoning_fax');
	print '<input type="text" name="fax" id="fax" class="maxwidth200" maxlength="80" value="'.(GETPOSTISSET('phone_fax') ?GETPOST('phone_fax', 'alpha') : $fax).'"></td></tr>';

	// EMail
	print '<tr><td>'.$form->editfieldkey('EMail', 'email', GETPOST('email', 'alpha'), $object, 0, 'string', '', (!empty($conf->global->SOCIETE_EMAIL_MANDATORY))).'</td>';
	print '<td>';
	print img_picto('', 'object_email');
	print '<input type="text" name="email" id="email" class="maxwidth100onsmartphone quatrevingtpercent" value="'.(GETPOSTISSET('email') ?GETPOST('email', 'alpha') : $email).'"></td>';
	print '</tr>';


	$priv = $object->priv;
	$default_lang = $object->default_lang;
	if(isset($_COOKIE["priv"])){
		$priv = $_COOKIE["priv"];
	}
	if(isset($_COOKIE["default_lang"])){
		$default_lang = $_COOKIE["default_lang"];
	}

	// Visibility
	print '<tr><td><label for="priv">'.$langs->trans("ContactVisibility").'</label></td><td colspan="3">';
	$selectarray = array('0'=>$langs->trans("ContactPublic"), '1'=>$langs->trans("ContactPrivate"));
	print $form->selectarray('priv', $selectarray, $priv, 0);
	print '<input type="text" name="visibility" id="visibility" value="" hidden>';
	print '</td></tr>';

	//Default language
	if (!empty($conf->global->MAIN_MULTILANGS)) {
		print '<tr><td>'.$form->editfieldkey('DefaultLang', 'default_lang', '', $object, 0).'</td><td colspan="3" class="maxwidthonsmartphone">'."\n";
		print img_picto('', 'language', 'class="pictofixedwidth"').$formadmin->select_language(GETPOST('default_lang', 'alpha') ? GETPOST('default_lang', 'alpha') : ($default_lang ? $default_lang : ''), 'default_lang', 0, 0, 1, 0, 0, 'maxwidth200onsmartphone');
		print '</td>';
		print '</tr>';
	}

	// Status
	print '<tr><td>'.$langs->trans("Status").'</td>';
	print '<td colspan="3">';
	print $object->getLibStatut(4);
	print '</td></tr>';

	// Photo
	print '<tr>';
	print '<td>'.$langs->trans("PhotoFile").'</td>';
	print '<td colspan="3">';
	$image = "";
	if($object->photo){
		$image = $object->photo;
	}else{
		$image = "placeholder.png";
	}
	print '<img id="contact-image" src="img/contact/'.$image.'" width="100" style="border: 1px solid grey; border-radius: 10px">';
	print '</td>';
	print '</tr>';
	print '<tr>';
	print '<td></td>';
	print '<td colspan="3">';
		print '<input type="file" id="image">';
		print '<input type="text" name="photo" id="photo-contact" hidden>';
	print '</td>';
	print '</tr>';


	print '</table>';

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel();

	print '</form>';
	// var_dump($action);
}
	

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {
	$res = $object->fetch_optionals();

	$head = storecontactPrepareHead($object);
	print dol_get_fiche_head($head, 'card', $langs->trans("StoreContact"), -1, $object->picto);

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeleteStoreContact'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
	}
	// Confirmation to delete line
	if ($action == 'deleteline') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&lineid='.$lineid, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_deleteline', '', 0, 1);
	}
// var_dump($action);
	if($action == 'edit') {
		var_dump("here");
		$dir = DOL_DOCUMENT_ROOT.'/custom/stores/img/';
		$file_OK = is_uploaded_file($_FILES['photo']['tmp_name']);
		if (GETPOST('deletephoto') && $object->photo) {
			$fileimg = $dir.'/'.$object->photo;
			$dirthumbs = $dir.'/thumbs';
			dol_delete_file($fileimg);
			dol_delete_dir_recursive($dirthumbs);
			$object->photo = '';
		}
		if ($file_OK) {
			if (image_format_supported($_FILES['photo']['name']) > 0) {
				dol_mkdir($dir);
	
				if (@is_dir($dir)) {
					$newfile = $dir.'/'.dol_sanitizeFileName($_FILES['photo']['name']);
					$result = dol_move_uploaded_file($_FILES['photo']['tmp_name'], $newfile, 1);
	
					if (!($result > 0)) {
						$errors[] = "ErrorFailedToSaveFile";
					} else {
						$object->photo = dol_sanitizeFileName($_FILES['photo']['name']);
	
						// Create thumbs
						$object->addThumbs($newfile);
					}
				}
			} else {
				$errors[] = "ErrorBadImageFormat";
			}
		} else {
			switch ($_FILES['photo']['error']) {
				case 1: //uploaded file exceeds the upload_max_filesize directive in php.ini
				case 2: //uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form
					$errors[] = "ErrorFileSizeTooLarge";
					break;
				case 3: //uploaded file was only partially uploaded
					$errors[] = "ErrorFilePartiallyUploaded";
					break;
			}
		}
	
	}

	// Clone confirmation
	if ($action == 'clone') {
		// Create an array for form
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('ToClone'), $langs->trans('ConfirmCloneAsk', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
	}

	// Confirmation of action xxxx (You can use it for xxx = 'close', xxx = 'reopen', ...)
	if ($action == 'xxx') {
		$text = $langs->trans('ConfirmActionStoreContact', $object->ref);
		/*if (! empty($conf->notification->enabled))
		{
			require_once DOL_DOCUMENT_ROOT . '/core/class/notify.class.php';
			$notify = new Notify($db);
			$text .= '<br>';
			$text .= $notify->confirmMessage('STORECONTACT_CLOSE', $object->socid, $object);
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
	$linkback = '<a href="'.dol_buildpath('/stores/storecontact_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';

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
			if (empty($user->socid)) {
				print dolGetButtonAction($langs->trans('SendMail'), '', 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=presend&mode=init&token='.newToken().'#formmailbeforetitle');
			}

			// Back to draft
			if ($object->status == $object::STATUS_VALIDATED) {
				print dolGetButtonAction($langs->trans('SetToDraft'), '', 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=confirm_setdraft&confirm=yes&token='.newToken(), '', $permissiontoadd);
			}

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
			print dolGetButtonAction($langs->trans('ToClone'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.(!empty($object->socid)?'&socid='.$object->socid:'').'&action=clone&token='.newToken(), '', $permissiontoadd);

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

		$includedocgeneration = 0;

		// Documents
		if ($includedocgeneration) {
			$objref = dol_sanitizeFileName($object->ref);
			$relativepath = $objref.'/'.$objref.'.pdf';
			$filedir = $conf->stores->dir_output.'/'.$object->element.'/'.$objref;
			$urlsource = $_SERVER["PHP_SELF"]."?id=".$object->id;
			$genallowed = $permissiontoread; // If you can read, you can build the PDF to read content
			$delallowed = $permissiontoadd; // If you can create/edit, you can remove a file on card
			print $formfile->showdocuments('stores:StoreContact', $object->element.'/'.$objref, $filedir, $urlsource, $genallowed, $delallowed, $object->model_pdf, 1, 0, 0, 28, 0, '', '', '', $langs->defaultlang);
		}

		// Show links to link elements
		$linktoelem = $form->showLinkToObjectBlock($object, null, array('storecontact'));
		$somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);


		print '</div><div class="fichehalfright">';

		$MAXEVENT = 10;

		$morehtmlcenter = dolGetButtonTitle($langs->trans('SeeAll'), '', 'fa fa-bars imgforviewmode', dol_buildpath('/stores/storecontact_agenda.php', 1).'?id='.$object->id);

		// List of actions on element
		include_once DOL_DOCUMENT_ROOT.'/core/class/html.formactions.class.php';
		$formactions = new FormActions($db);
		$somethingshown = $formactions->showactions($object, $object->element.'@'.$object->module, (is_object($object->thirdparty) ? $object->thirdparty->id : 0), 1, '', $MAXEVENT, '', $morehtmlcenter);

		print '</div></div>';
	}

	//Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	// Presend form
	$modelmail = 'storecontact';
	$defaulttopic = 'InformationMessage';
	$diroutput = $conf->stores->dir_output;
	$trackid = 'storecontact'.$object->id;

	include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';
}

// End of page
llxFooter();
$db->close();
