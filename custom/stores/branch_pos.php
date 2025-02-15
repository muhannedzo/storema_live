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
 *  \file       stores/branch_agenda.php
 *  \ingroup    stores
 *  \brief      Tab of events on Branch
 */

//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB', '1');				// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER', '1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC', '1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN', '1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION', '1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION', '1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK', '1');				// Do not check CSRF attack (test on referer + on token if option MAIN_SECURITY_CSRF_WITH_TOKEN is on).
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

require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/ticket/class/ticket.class.php';
dol_include_once('/stores/class/branch.class.php');
dol_include_once('/stores/lib/stores_branch.lib.php');

include('compress.php');


// Load translation files required by the page
$langs->loadLangs(array("stores@stores", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$cancel = GETPOST('cancel', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');

if (GETPOST('actioncode', 'array')) {
	$actioncode = GETPOST('actioncode', 'array', 3);
	if (!count($actioncode)) {
		$actioncode = '0';
	}
} else {
	$actioncode = GETPOST("actioncode", "alpha", 3) ? GETPOST("actioncode", "alpha", 3) : (GETPOST("actioncode") == '0' ? '0' : (empty($conf->global->AGENDA_DEFAULT_FILTER_TYPE_FOR_OBJECT) ? '' : $conf->global->AGENDA_DEFAULT_FILTER_TYPE_FOR_OBJECT));
}
$search_agenda_label = GETPOST('search_agenda_label');

$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST('sortfield', 'aZ09comma');
$sortorder = GETPOST('sortorder', 'aZ09comma');
$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST("page", 'int');
if (empty($page) || $page == -1) {
	$page = 0;
}     // If $page is not defined, or '' or -1
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (!$sortfield) {
	$sortfield = 'a.datep,a.id';
}
if (!$sortorder) {
	$sortorder = 'DESC,DESC';
}

// Initialize technical objects
$object = new Branch($db);
$extrafields = new ExtraFields($db);
$ticket = new Ticket($db);
$diroutputmassaction = $conf->stores->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('branchagenda', 'globalcard')); // Note that conf->hooks_modules contains array
// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once  // Must be include, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals
if ($id > 0 || !empty($ref)) {
	$upload_dir = $conf->stores->multidir_output[!empty($object->entity) ? $object->entity : $conf->entity]."/".$object->id;
}

// There is several ways to check permission.
// Set $enablepermissioncheck to 1 to enable a minimum low level of checks
$enablepermissioncheck = 0;
if ($enablepermissioncheck) {
	$permissiontoread = $user->rights->stores->branch->read;
	$permissiontoadd = $user->rights->stores->branch->write;
} else {
	$permissiontoread = 1;
	$permissiontoadd = 1;
}

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (($object->status == $object::STATUS_DRAFT) ? 1 : 0);
//restrictedArea($user, $object->element, $object->id, $object->table_element, '', 'fk_soc', 'rowid', $isdraft);
if (empty($conf->stores->enabled)) accessforbidden();
if (!$permissiontoread) accessforbidden();


/*
 *  Actions
 */

$parameters = array('id'=>$id);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
	// Cancel
	if (GETPOST('cancel', 'alpha') && !empty($backtopage)) {
		header("Location: ".$backtopage);
		exit;
	}

	// Purge search criteria
	if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) { // All tests are required to be compatible with all browsers
		$actioncode = '';
		$search_agenda_label = '';
	}
}



/*
 *	View
 */

$form = new Form($db);

if ($object->id > 0) {
	$title = $langs->trans("StoreImages");
	//if (! empty($conf->global->MAIN_HTML_TITLE) && preg_match('/thirdpartynameonly/',$conf->global->MAIN_HTML_TITLE) && $object->name) $title=$object->name." - ".$title;
	$help_url = 'EN:Module_Agenda_En';
	llxHeader('', $title, $help_url);

	if (!empty($conf->notification->enabled)) {
		$langs->load("mails");
	}
	$head = branchPrepareHead($object);


	print dol_get_fiche_head($head, 'images', $langs->trans("Branch"), -1, $object->picto);

	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="'.dol_buildpath('/stores/branch_card.php', 1).'?id='.$id.'">'.$langs->trans("BackToCard").'</a>';

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
		$morehtmlref.='<br>'.$langs->trans('Project') . ' ';
		if ($permissiontoadd) {
			if ($action != 'classify') {
				//$morehtmlref.='<a class="editfielda" href="' . $_SERVER['PHP_SELF'] . '?action=classify&token='.newToken().'&id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetProject')) . '</a> : ';
			}
			$morehtmlref.=' : ';
			if ($action == 'classify') {
				//$morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'projectid', 0, 0, 1, 1);
				$morehtmlref.='<form method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'">';
				$morehtmlref.='<input type="hidden" name="action" value="classin">';
				$morehtmlref.='<input type="hidden" name="token" value="'.newToken().'">';
				$morehtmlref.=$formproject->select_projects($object->socid, $object->fk_project, 'projectid', $maxlength, 0, 1, 0, 1, 0, 0, '', 1);
				$morehtmlref.='<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
				$morehtmlref.='</form>';
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
	print '<div class="underbanner clearboth"></div>';

	// $object->info($object->id);
	// dol_print_object_info($object, 1);

	// print '</div>';

	print dol_get_fiche_end();



	// Actions buttons

	// $objthirdparty = $object;
	// $objcon = new stdClass();

	// $out = '&origin='.urlencode($object->element.'@'.$object->module).'&originid='.urlencode($object->id);
	// $urlbacktopage = $_SERVER['PHP_SELF'].'?id='.$object->id;
	// $out .= '&backtopage='.urlencode($urlbacktopage);
	// $permok = $user->rights->agenda->myactions->create;
	// if ((!empty($objthirdparty->id) || !empty($objcon->id)) && $permok) {
	// 	//$out.='<a href="'.DOL_URL_ROOT.'/comm/action/card.php?action=create';
	// 	if (get_class($objthirdparty) == 'Societe') {
	// 		$out .= '&socid='.urlencode($objthirdparty->id);
	// 	}
	// 	$out .= (!empty($objcon->id) ? '&contactid='.urlencode($objcon->id) : '').'&percentage=-1';
	// 	//$out.=$langs->trans("AddAnAction").' ';
	// 	//$out.=img_picto($langs->trans("AddAnAction"),'filenew');
	// 	//$out.="</a>";
	// }


	// print '<div class="tabsAction">';

	// if (isModEnabled('agenda')) {
	// 	if (!empty($user->rights->agenda->myactions->create) || !empty($user->rights->agenda->allactions->create)) {
	// 		print '<a class="butAction" href="'.DOL_URL_ROOT.'/comm/action/card.php?action=create'.$out.'">'.$langs->trans("AddAction").'</a>';
	// 	} else {
	// 		print '<a class="butActionRefused classfortooltip" href="#">'.$langs->trans("AddAction").'</a>';
	// 	}
	// }

	// print '</div>';

	// if (isModEnabled('agenda') && (!empty($user->rights->agenda->myactions->read) || !empty($user->rights->agenda->allactions->read))) {
	// 	$param = '&id='.$object->id.(!empty($socid) ? '&socid='.$socid : '');
	// 	if (!empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) {
	// 		$param .= '&contextpage='.urlencode($contextpage);
	// 	}
	// 	if ($limit > 0 && $limit != $conf->liste_limit) {
	// 		$param .= '&limit='.urlencode($limit);
	// 	}


	// 	//print load_fiche_titre($langs->trans("ActionsOnBranch"), '', '');

	// 	// List of all actions
	// 	$filters = array();
	// 	$filters['search_agenda_label'] = $search_agenda_label;

	// 	// TODO Replace this with same code than into list.php
	// 	show_actions_done($conf, $langs, $db, $object, null, 0, $actioncode, '', $filters, $sortfield, $sortorder, $object->module);
	// }

		$obj = new Compress();	
		print '<form action="" method="POST" enctype="multipart/form-data"><input type="hidden" name="token" value="'.newToken().'">
					<h2>'.$langs->trans("StoreImages").'</h2>
					<p style="display:flex">
						<input placeholder="Enter Images Label" type="text" id="images-lable" name="images-label">';
						// $ticket_item = new Ticket($db);
						// $tickets = "SELECT t.rowid, t.ref";
						// $tickets .= " FROM llx_ticket t";
						// $tickets .= " LEFT JOIN llx_ticket_extrafields te on te.fk_object = t.rowid";
						// $tickets .= " WHERE te.fk_store = ".$id.";";
						
						// $result = $db->query($tickets);
						// if ($result) {
						// 	$num = $db->num_rows($result);
						// 	$i = 0;
				
						// 	print '<select name="ticket" id="ticket">';
						// 	for($i=0;  $i < $num; $i++) {
								
						// 		$objc = $db->fetch_object($result);
				
						// 		$ticket_item->id = $objc->rowid;
						// 		$ticket_item->ref = $objc->ref;
				
						// 		print '<option value="'.$ticket_item->id.'|'.$ticket_item->ref.'">'.$ticket_item->ref.'</option>';
						// 	} 	
						// 	print '</select>';
						// } else {
						// 	print '<select id="ticket">';
						// 	print '<option>No Tickets</option>';
						// 	print '</select>';
						// }
						// $db->free($result);
						print '<br><br>	
						<input type="file" name="files[]" multiple>
						<br>';
					print'	<input type="submit" name="submit" value="Upload" >
					</p>
				</form>';

				$imagesList = array();
				$images = array();	
				$query = 'SELECT images FROM llx_stores_branch WHERE rowid = '.$id;
				$list = $db->query($query)->fetch_row();
				if($list[0 != null]){
					$arr = json_decode($list[0],true);
					foreach($arr as $elm){
						array_push($imagesList, $elm);
					}
				}
				// var_dump($imagesList);

			

				$dir = DOL_DOCUMENT_ROOT.'/custom/stores/img/';
				if(!is_dir($dir)){
					mkdir($dir);
				}
				// var_dump($images);
				if(isset($_POST['submit'])) {
					// var_dump($_POST);
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
							$imageQuality = 20;
							$file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
							
							// Set upload file path
							$filepath = $dir.$file_name;
				 
							// Check file type is allowed or not
							if(in_array(strtolower($file_ext), $allowed_types)) {

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
					$object = [
						"title" => $_POST['images-label'],
						"images" => $images
					];
					array_push($imagesList, $object);
					$list = json_encode($imagesList);
					$sql = 'UPDATE llx_stores_branch set images = "'.addslashes($list).'" WHERE rowid = '.$id;
					$db->query($sql,0,'ddl');
					// var_dump($db->query($sql,0,'ddl'));
				}
			//////////////////////List Of images//////////////////////	
			$query = 'SELECT images FROM llx_stores_branch WHERE rowid = '.$id;
			$list = $db->query($query)->fetch_row();
			$imagesList = [];
			if($list[0 != null]){
				$arr = json_decode($list[0],true);
				
				foreach($arr as $elm){
					array_push($imagesList, $elm);
				}
			}
			print '<table class="noborder" width="100%">';
			
							$obj->print_list($imagesList);
				if(isset($_POST['delete'])) {
					$imagesList = array_reverse($imagesList);
					$key = array_search($_POST["img"],$imagesList[$_POST["objectIndex"]]["images"]);
					unlink($dir.$_POST["img"]);
					unset($imagesList[$_POST["objectIndex"]]["images"][$key]);
					$list = json_encode(array_reverse($imagesList));
					$sql = 'UPDATE llx_stores_branch set images = "'.addslashes($list).'" WHERE rowid = '.$id;
					$db->query($sql,0,'ddl');
					print '<script>window.location.href = window.location.href;
					</script>';
				}
				if(isset($_POST['edit'])) {
					$imagesList = array_reverse($imagesList);
					$key = array_search($_POST["img"],$imagesList[$_POST["objectIndex"]]["images"]);
					$imagesList[$_POST["objectIndex"]]["images"][$key] = explode("|",$imagesList[$_POST["objectIndex"]]["images"][$key])[0]."|".$_POST["description"];
					$list = json_encode(array_reverse($imagesList));
					$sql = 'UPDATE llx_stores_branch set images = "'.addslashes($list).'" WHERE rowid = '.$id;
					$db->query($sql,0,'ddl');
					print '<script>window.location.href = window.location.href;
					</script>';
				}	
				if(isset($_POST['edit-label'])) {
					$imagesList = array_reverse($imagesList);
					$imagesList[$_POST["objectIndex"]]["title"] = $_POST["label"];
					$list = json_encode(array_reverse($imagesList));
					$sql = 'UPDATE llx_stores_branch set images = "'.addslashes($list).'" WHERE rowid = '.$id;
					$db->query($sql,0,'ddl');
					print '<script>window.location.href = window.location.href;
					</script>';
				}	
				if(isset($_POST['delete-group'])) {		
					$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeleteBranch'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
					// var_dump($_POST);
					$imagesList = array_reverse($imagesList);
					$imagesCount = count($imagesList[$_POST["objectIndex"]]["images"]);
					if($imagesCount > 0){
						foreach($imagesList[$_POST["objectIndex"]]["images"] as $elem){
							// var_dump(explode("|",$elem)[0]);
							unlink($dir.explode("|",$elem)[0]);
						}
					}
					unset($imagesList[$_POST["objectIndex"]]);
					$list = json_encode(array_reverse($imagesList));
					$sql = 'UPDATE llx_stores_branch set images = "'.addslashes($list).'" WHERE rowid = '.$id;
					$db->query($sql,0,'ddl');
					print '<script>window.location.href = window.location.href;
					</script>';
				}	
				if(isset($_POST['submitAdd'])) {
					$imagesList = array_reverse($imagesList);
 
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
							$imageQuality = 20;
							$file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
							
							// Set upload file path
							$filepath = $dir.$file_name;
				 
							// Check file type is allowed or not
							if(in_array(strtolower($file_ext), $allowed_types)) {

									if(file_exists($filepath)) {
										$fileN = time().$file_name;
										$filepath = $dir.$fileN;
										$compressedImage = $obj->compress_image($file_tmpname, $filepath, $imageQuality);
										if( $compressedImage) {
											array_push($imagesList[$_POST["index"]]["images"], $fileN);
										}else {                    
											dol_htmloutput_errors("Error uploading {$file_name} <br />");
										}
									}else {
										$compressedImage = $obj->compress_image($file_tmpname, $filepath, $imageQuality);
										if($compressedImage) {
											array_push($imagesList[$_POST["index"]]["images"], $file_name);
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
					$list = json_encode(array_reverse($imagesList));
					$sql = 'UPDATE llx_stores_branch set images = "'.addslashes($list).'" WHERE rowid = '.$id;
					$db->query($sql,0,'ddl');
					print '<script>window.location.href = window.location.href;
					</script>';
				}	
			////////////////////////////End Normal Images/////////////////////////////////	

			////////////////////////////Forms tickets images//////////////////////////////
			
			$query = 'SELECT f.rowid, f.fk_ticket, f.images FROM llx_tec_forms f
					  WHERE fk_store = '.$id;
			$forms = $db->query($query)->fetch_all();
			$formsList = [];
			$formObject = [];
			if($forms){
				foreach($forms as $form){
					$formObject = [
						"formId" => $form[0],
						"ticketId" => $form[1],
						"images" => $form[2]
					];
					array_push($formsList, $formObject);
				}
			}
			$listImages = [];
			$formsList = array_reverse($formsList);
			// var_dump($formsList);
			if($formsList) {
				foreach($formsList as $form) {
					$ticket->fetch($form["ticketId"]);
					$imagesGroup = json_decode(base64_decode($form["images"]));
					// var_dump($imagesGroup);
					$k = 0;
					print '<div class="group">';
						print '<div class="group-header">';
							print '<div style="display: flex">';
								print '<form action="" method="POST"><input type="hidden" name="token" value="'.newToken().'">';
									print $ticket->getNomUrl();
									// print '<div class="edit-icon" id="edit-icon '.$k.'"><span id="'.$k.'" class="fa fa-pen" onclick="changeLabel(this.id)"></span></div>';
									// print '<button type="submit" name="edit-label" id="save-edit '.$k.'" hidden>Save</button></td>';
									// print '<input type="hidden" name="objectIndex" value="'.$k.'">';
								print '</form>';
							print '</div>';  
							print '<div style="display: flex; align-items:center">';
								// print '<form action="" method="POST"><input type="hidden" name="token" value="'.newToken().'">';
								// print '<span id="delete '.$k.'" class="fa fa-trash" style="color:red;margin:5px" onclick="conf(this.id)"></span>';
								// print '<button type="submit" id="delete-group delete '.$k.'" name="delete-group" hidden>delete</button></td>';
								// print '<span id="addmore '.$k.'" class="fa fa-plus-circle add-icon" onclick="see(this.id)"></span>';
								// print '<input type="hidden" name="objectIndex" value="'.$k.'">';
								// print '</form>';  
							print '</div>';
						print '</div>';
						foreach($imagesGroup as $elem){
							$elements = $elem->images;
							$exploded_elements = array_map(function($element) {
								$parts = explode("|", $element);
								return $parts[0];
							}, $elements);
							$exploded_texts = array_map(function($element) {
								$parts = explode("|", $element);
								return $parts[1];
							}, $elements);
							
							$text = implode(", ", $exploded_elements);
							$titles = implode(", ", $exploded_texts);
									
							// $text = implode(", ", $elements);
							print '<input type="text" class="array '.$k.'" value="'.$text.'" hidden>';
							foreach($elements as $key => $image){

								print '<div class="group-element">';
								print '<input type="file" name="files[]" multiple hidden>';
								print '<div class="element-image">';
									print '<img class="myImg" id="'.$k.' '.$key.'" alt="img" src="../../formsImages/'.explode("|", $image)[0].'" width="100" height="100" onclick="ss(this.id, 2);">';
								print '</div>';
								print '<form action="" method="POST"><input type="hidden" name="token" value="'.newToken().'">';
								print '<div class="element-description">';
									print '<input id="desc '.$k.' '.$key.'" name="description"type="text" placeholder="Description.." value="'.$elem->type.'" disabled>';
								print '</div>';
									print '<div class="element-buttons">';
										print '<button type="submit" name="delete-form-img" onclick="return confirmDelete();">Delete</button>';
									print '</div>';
									print '<div id="form-modal '.$k.' '.$key.'" class="modal '.$k.' '.$key.'">
												<!-- Modal content -->
													<div class="modal-content">
														<div class="modal-header">
															<p class="'.$k.' '.$key.'" id="rotate '.$k.' '.$key.'" onclick="rotateImage(this.id,this.className, 2)">Rotate</p>
															<span class="form-close '.$k.' '.$key.'" id="form-close '.$k.' '.$key.'">&times;</span>
														</div>
														<div class="modal-body">  
															<div class="modal-image" style="display: flex; align-items: center; justify-content: space-evenly;">
																<a class="'.$k.' '.$key.'" id="'.$text.'|'.$titles.'" onclick="prevImage(this.id, this.className, 2)"><i class="fa fa-arrow-left" style="font-size:20px"></i></a>
																<img class="'.$k.' '.$key.'" id="form-img rotate '.$k.' '.$key.'" alt="img" src="../../formsImages/'.explode("|", $image)[0].'" onclick="se(this.id,this.className, 2);"
																						style="cursor: pointer">
																<a class="'.$k.' '.$key.'" id="'.$text.'|'.$titles.'" onclick="nextImage(this.id, this.className, 2)"><i class="fa fa-arrow-right" style="font-size:20px"></i></a>
															</div>';
															// if($desc != ""){
																print '<div><p id="form-txt rotate '.$k.' '.$key.'">'.$desc.'</p></div>';
															// }
													print '</div>
														<div class="modal-footer">
														</div>
													</div>
											</div>';
									print '<div id="form-full-model '.$k.' '.$key.'" class="full-view '.$key.'">
												<span class="form-full-view-close '.$k.' '.$key.'" id="form-full-view-close '.$k.' '.$key.'">&times;</span>
													<img class="full-view-content" id="form-full-view-img rotate '.$k.' '.$key.'" src="../../formsImages/'.explode("|", $image)[0].'">
											</div>';    
									print '<input type="hidden" name="objectIndex" value="'.$k.'">';
									print '<input type="hidden" name="imgIndex" value="'.$key.'">';
									print '<input type="hidden" name="img" value="'.explode("|", $image)[0].'">';
								print '</form>';
								print '</div>';
							}
							$k++;
						}
					print '</div>';
					print '<hr>';
					// print '<div class="row mt-2">';
					// 	print '<div class="col-12" style="background: #aaa;padding: 5px 0 5px 10px;">';
					// 		print $ticket->getNomUrl();
					// 	print '</div>';
					// 	foreach($imagesGroup as $group){
					// 		print '<div class="row mt-2">';
					// 				foreach($group->images as $image){
					// 					array_push($listImages, $image);
					// 					print '<div class="col-3 col-md-3 mt-2">';
					// 						print '<form action="" method="POST"><input type="hidden" name="token" value="'.newToken().'">';
					// 							print '<img class="group-image mb-2" src="../../formsImages/'.$image.'" id="'.$image.'" style="width:100%; height:13rem" onclick="showImageFull(this.src, this.id)">';
					// 							print '<input type="text" value="'.$group->type.'" name="type" disabled>'.' '.'<input class="btn btn-danger" type="submit" value="Delete" name="delete">';
					// 							print '<input type="text" name="ticketId" value="'.$form["ticketId"].'" hidden>';
					// 							print '<input type="text" name="formId" value="'.$form["formId"].'" hidden>';
					// 							print '<input type="text" name="images" value="'.$form["formId"].'" hidden>';
					// 						print '</form>';
					// 					print '</div>';
					// 				}
					// 		print '</div>';
					// 	}
					// print '</div>';
				}
			}
			$imgText = implode(",", $listImages);
			print '
					<div id="popup" class="closed">
						<div class="row">
							<div class="col-12" style="display: flex;align-items: center;">
								<i class="fa fa-chevron-left" id="'.$imgText.'" onclick="slideImages(this.id, \'prev\')"></i>
								<img id="popupImage" src="" style="width:100%">
								<i class="fa fa-chevron-right" id="'.$imgText.'" onclick="slideImages(this.id, \'next\')"></i>
							</div>
						</div>
						<div class="row mt-2">
							<div class="col-12" style="text-align: center">
								<button class="btn btn-danger" id="closePopupBtn">Close</button>
							</div>
						</div>
					</div>';  	
}

// End of page
llxFooter();
$db->close();

if(isset($_POST['delete'])) {
	var_dump($_POST);
	// var_dump($formsList);
	// var_dump(1);
	// $imagesList = array_reverse($imagesList);
	// $key = array_search($_POST["img"],$imagesList[$_POST["objectIndex"]]["images"]);
	// unlink($dir.$_POST["img"]);
	// unset($imagesList[$_POST["objectIndex"]]["images"][$key]);
	// $list = json_encode(array_reverse($imagesList));
	// $sql = 'UPDATE llx_stores_branch set images = "'.addslashes($list).'" WHERE rowid = '.$id;
	// $db->query($sql,0,'ddl');
	// print '<script>window.location.href = window.location.href;
	// 		</script>';
}
print '<script>';
    
	print '
		function showImageFull(src, img){
			const openPopupBtn = document.getElementById("openPopupBtn");
			const closePopupBtn = document.getElementById("closePopupBtn");
			const popup = document.getElementById("popup");
			const popupSrc = document.getElementById("popupImage");

			popupSrc.src = src;
			popupSrc.className = img;
			popup.classList.remove("closed");

			closePopupBtn.addEventListener("click", function() {
				popup.classList.add("closed");
			});
		}
	';
    
	print '
		function slideImages(images, status){
			var imagesList = images.split(",");
			const popupSrc = document.getElementById("popupImage");
			searchedImage = popupSrc.className;
			let currentIndex = imagesList.indexOf(searchedImage);
			
			let index = currentIndex;
			if(status == "prev"){
				if(currentIndex == 0){
					index = imagesList.length - 1;
				} else {
					index = currentIndex - 1;
				}
				popupSrc.src = "../../formsImages/" + imagesList[index];
				popupSrc.className = imagesList[index];
			}
			if(status == "next"){
				if(currentIndex == (imagesList.length - 1)){
					index = 0;
				} else {
					index = currentIndex + 1;
				}
				popupSrc.src = "../../formsImages/" + imagesList[index];
				popupSrc.className = imagesList[index];
			}
		}
	';
print '</script>';

print '<style>';
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
		 img {
		 	cursor: pointer;
		   }
		 i {
			padding: 10px;
			font-size: 25px;
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
print '<script>
                      
var currentIndex = 0;
function prevImage(id, className) {
	var lists = id.split("|")[0].split(", ");
	var listTexts = id.split("|")[1].split(", ");
	var className1 = "img rotate " + className;
	var imgElement = document.getElementById(className1);
	var src = imgElement.getAttribute("src");
	var imageName = src.split("/").pop();
	var imageIndex = lists.indexOf(imageName);
	if (imageIndex === 0) {
	  currentIndex = lists.length - 1;
	} else {
		currentIndex = imageIndex - 1;
	}
	updateImage(currentIndex, className, lists, listTexts);
}

function nextImage(id, className, number) {
	var lists = id.split("|")[0].split(", ");
	var listTexts = id.split("|")[1].split(", ");
	if(number == 1){
	  var className1 = "img rotate " + className;
	} else {
	  var className1 = "form-img rotate " + className;
	}
	var imgElement = document.getElementById(className1);
	var src = imgElement.getAttribute("src");
	var imageName = src.split("/").pop();
	var imageIndex = lists.indexOf(imageName);
	if (imageIndex === lists.length - 1) {
	  currentIndex = 0;
	} else {
		currentIndex = imageIndex + 1;
	}
	updateImage(currentIndex, className, lists, listTexts);
}
function updateImage(index, className, lists, listTexts) {
	var classImg = "img rotate " + className;
	var classText = "txt rotate " + className;
	var imgElement = document.getElementById(classImg);
	var txtElement = document.getElementById(classText);
	imgElement.src = "./img/" + lists[currentIndex];
	txtElement.innerHTML = listTexts[currentIndex];
}
  function conf(i){
	var m = "delete-group "+ i;
	document.getElementById(m).click();
  }
  function changeLabel(i){
	  var desc = "main-label " +i;
	  var btnS = "save-edit " +i;
	  var tt = "edit-icon "+ i;
	  var input = document.getElementById(desc);
	  var editB = document.getElementById(tt);
	  var buttonS = document.getElementById(btnS);
	  if (input.disabled) {
		  input.disabled = false;
		  editB.style.display = "none";
		  buttonS.hidden = false;
	  } else {
		  input.disabled = true;
		  editB.style.display = "block";
		  buttonS.hidden = true;
	  }
  }
  function see(i){
	var e = "addmore "+i;
	document.getElementsByClassName(e)[0].style.display="block";
  }
  var rotation = 0;
  var angle = 90;
  function rotateImage(i,j, number) {
	  if(number == 1){
		var c = "img "+i;
		var n = "full-view-img "+i;
	  } else {
		var c = "form-img "+i;
		var n = "form-full-view-img "+i;
	  }
	  var rotated = document.getElementById(c);
	  var rotated1 = document.getElementById(n);
	  var rotated2 = document.getElementById(j);
	  // alert(rotated2);
	  rotation = (rotation + angle) % 360;
	  rotated.style.transform = `rotate(${rotation}deg)`;
	  rotated.style.transform = `scale(${rotation}deg)`;
	  rotated1.style.transform = `rotate(${rotation}deg)`;
	  rotated1.style.transform = `scale(${rotation}deg)`;
	  rotated2.style.transform = `rotate(${rotation}deg)`;
	  rotated2.style.transform = `scale(${rotation}deg)`;
  }
  function se(i,j, number){
	  if(number == 1){
		var m = "full-model "+j;
		var n = "full-view-img rotate "+j;
		var spann = "full-view-close " +j;
	  } else {
		var m = "form-full-model "+j;
		var n = "form-full-view-img rotate "+j;
		var spann = "form-full-view-close " +j;
	  }
	  var img = document.getElementById(i);
	  var modal = document.getElementById(m);
	  // img.onclick = function(){
		  modal.style.display = "block";
	  // }
	  
	  var span = document.getElementById(spann);

	  span.onclick = function() { 
		  modal.style.display = "none";
	  }
	  
	  window.onclick = function(event) {
	  if (event.target == modal) {
		  modal.style.display = "none";
		  }
	  }
	  
  }  
</script>';
print   '<script>
  function toggleEdit(i) {
	  var desc = "desc " +i;
	  var btn = "edit-button " +i;
	  var btnS = "save-button " +i;
	  // alert(btnS);
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
  function ss(i, number){
	  // alert(i);
	  if(number == 1){
		var c = "close " +i;
		var m = "modal "+i;
	  } else {
		var c = "form-close " +i;
		var m = "form-modal "+i;
	  }
	  var modal = document.getElementById(m);
	  modal.style.display = "block";
	  var span = document.getElementById(c); 
	  span.onclick = function() {
		  modal.style.display = "none";
	  }
	  window.onclick = function(event) {
	  if (event.target == modal) {
		  modal.style.display = "none";
		  }
	  }
  }
</script>'; 
print '<style>
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
.close, .form-close {
color: #333333;
float: right;
font-size: 28px;
font-weight: bold;
}

.close:hover,
.close:focus, .form-close:hover,
.form-close:focus {
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
print '<style>
.full-view {
display: none; /* Hidden by default */
position: fixed; /* Stay in place */
z-index: 999999999999999999; /* Sit on top */
left: 0;
top: 0;
width: 100%; /* Full width */
height: 100%; /* Full height */
overflow: auto; /* Enable scroll if needed */
background-color: rgb(0,0,0); /* Fallback color */
background-color: rgba(0,0,0,0.9); /* Black w/ opacity */
}

/* Modal Content (image) */
.full-view-content {
margin: auto;
display: block;
width: 80%;
max-width: 700px;
}

/* Add Animation */
.full-view-content {  
-webkit-animation-name: zoom;
-webkit-animation-duration: 0.6s;
animation-name: zoom;
animation-duration: 0.6s;
}

@-webkit-keyframes zoom {
from {-webkit-transform:scale(0)} 
to {-webkit-transform:scale(1)}
}

@keyframes zoom {
from {transform:scale(0)} 
to {transform:scale(1)}
}

/* The Close Button */
.full-view-close, .form-full-view-close {
position: absolute;
top: 15px;
right: 35px;
color: #f1f1f1;
font-size: 40px;
font-weight: bold;
transition: 0.3s;
}

.full-view-close:hover,
.full-view-close:focus,
.form-full-view-close:hover,
.form-full-view-close:focus {
color: #bbb;
text-decoration: none;
cursor: pointer;
}

/* 100% Image Width on Smaller Screens */
@media only screen and (max-width: 700px){
.full-view-content {
width: 100%;
}
}
</style>';

print '<script>
</script>';  