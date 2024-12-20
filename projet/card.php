<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2016 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2023      Charlene Benke       <charlene@patas_monkey.com>
 * Copyright (C) 2023      Christian Foellmann  <christian@foellmann.de>
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
 *	\file       htdocs/projet/card.php
 *	\ingroup    projet
 *	\brief      Project card
 */

// Load Dolibarr environment
require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/ticket/class/ticket.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formticket.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/project/modules_project.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/stores/class/branch.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';

// Load translation files required by the page
$langsLoad=array('projects', 'companies');
if (isModEnabled('eventorganization')) {
	$langsLoad[]='eventorganization';
}

$langs->loadLangs($langsLoad);

$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$backtopagejsfields = GETPOST('backtopagejsfields', 'alpha');
$cancel = GETPOST('cancel', 'alpha');
$confirm = GETPOST('confirm', 'aZ09');
$emailType = GETPOST('type', 'aZ09');

$dol_openinpopup = 0;
if (!empty($backtopagejsfields)) {
	$tmpbacktopagejsfields = explode(':', $backtopagejsfields);
	$dol_openinpopup = $tmpbacktopagejsfields[0];
}

$status = GETPOST('status', 'int');
$opp_status = GETPOST('opp_status', 'int');
$opp_percent = price2num(GETPOST('opp_percent', 'alphanohtml'));
$objcanvas = GETPOST("objcanvas", "alphanohtml");
$comefromclone = GETPOST("comefromclone", "alphanohtml");
$date_start = dol_mktime(0, 0, 0, GETPOST('projectstartmonth', 'int'), GETPOST('projectstartday', 'int'), GETPOST('projectstartyear', 'int'));
$date_end = dol_mktime(0, 0, 0, GETPOST('projectendmonth', 'int'), GETPOST('projectendday', 'int'), GETPOST('projectendyear', 'int'));
$date_start_event = dol_mktime(GETPOSTINT('date_start_eventhour'), GETPOSTINT('date_start_eventmin'), GETPOSTINT('date_start_eventsec'), GETPOSTINT('date_start_eventmonth'), GETPOSTINT('date_start_eventday'), GETPOSTINT('date_start_eventyear'), 'tzuserrel');
$date_end_event = dol_mktime(GETPOSTINT('date_end_eventhour'), GETPOSTINT('date_end_eventmin'), GETPOSTINT('date_end_eventsec'), GETPOSTINT('date_end_eventmonth'), GETPOSTINT('date_end_eventday'), GETPOSTINT('date_end_eventyear'), 'tzuserrel');
$location = GETPOST('location', 'alphanohtml');
$fk_project = GETPOSTINT('fk_project');


$mine = GETPOST('mode') == 'mine' ? 1 : 0;
//if (! $user->rights->projet->all->lire) $mine=1;	// Special for projects

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('projectcard', 'globalcard'));

$object = new Project($db);
$extrafields = new ExtraFields($db);

// Load object
//include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php';  // Can't use generic include because when creating a project, ref is defined and we dont want error if fetch fails from ref.
if ($id > 0 || !empty($ref)) {
	$ret = $object->fetch($id, $ref); // If we create project, ref may be defined into POST but record does not yet exists into database
	if ($ret > 0) {
		$object->fetch_thirdparty();
		if (getDolGlobalString('PROJECT_ALLOW_COMMENT_ON_PROJECT') && method_exists($object, 'fetchComments') && empty($object->comments)) {
			$object->fetchComments();
		}
		$id = $object->id;
	}
}

// fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

// Security check
$socid = GETPOST('socid', 'int');
//if ($user->socid > 0) $socid = $user->socid;    // For external user, no check is done on company because readability is managed by public status of project and assignement.
restrictedArea($user, 'projet', $object->id, 'projet&project');

if ($id == '' && $ref == '' && ($action != "create" && $action != "add" && $action != "update" && !GETPOST("cancel"))) {
	accessforbidden();
}

$permissiontoadd = $user->hasRight('projet', 'creer');
$permissiontodelete = $user->hasRight('projet', 'supprimer');
$permissiondellink = $user->hasRight('projet', 'creer');	// Used by the include of actions_dellink.inc.php


/*
 * Actions
 */

$parameters = array('id'=>$socid, 'objcanvas'=>$objcanvas);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
	$backurlforlist = DOL_URL_ROOT.'/projet/list.php';

	// Cancel
	if ($cancel) {
		if (GETPOST("comefromclone") == 1) {
			$result = $object->delete($user);
			if ($result > 0) {
				header("Location: index.php");
				exit;
			} else {
				dol_syslog($object->error, LOG_DEBUG);
				setEventMessages($langs->trans("CantRemoveProject", $langs->transnoentitiesnoconv("ProjectOverview")), null, 'errors');
			}
		}
	}

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = DOL_URL_ROOT.'/projet/card.php?id='.((!empty($id) && $id > 0) ? $id : '__ID__');
			}
		}
	}

	if ($cancel) {
		if (!empty($backtopageforcancel)) {
			header("Location: ".$backtopageforcancel);
			exit;
		} elseif (!empty($backtopage)) {
			header("Location: ".$backtopage);
			exit;
		}
		$action = '';
	}

	include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';		// Must be include, not include_once

	// Action setdraft object
	if ($action == 'confirm_setdraft' && $confirm == 'yes' && $permissiontoadd) {
		$result = $object->setStatut($object::STATUS_DRAFT, null, '', 'PROJECT_MODIFY');
		if ($result >= 0) {
			// Nothing else done
		} else {
			$error++;
			setEventMessages($object->error, $object->errors, 'errors');
		}
		$action = '';
	}

	// Action add
	if ($action == 'add' && $permissiontoadd) {
		$error = 0;
		if (!GETPOST('ref')) {
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("Ref")), null, 'errors');
			$error++;
		}
		if (!GETPOST('title')) {
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("ProjectLabel")), null, 'errors');
			$error++;
		}

		if (getDolGlobalString('PROJECT_USE_OPPORTUNITIES')) {
			if (GETPOST('usage_opportunity') != '' && !(GETPOST('opp_status') > 0)) {
				$error++;
				setEventMessages($langs->trans("ErrorOppStatusRequiredIfUsage"), null, 'errors');
			}
			if (GETPOST('opp_amount') != '' && !(GETPOST('opp_status') > 0)) {
				$error++;
				setEventMessages($langs->trans("ErrorOppStatusRequiredIfAmount"), null, 'errors');
			}
		}

		// Create with status validated immediatly
		if (getDolGlobalString('PROJECT_CREATE_NO_DRAFT') && !$error) {
			$status = Project::STATUS_VALIDATED;
		}

		if (!$error) {
			$error = 0;

			$db->begin();

			$object->ref             = GETPOST('ref', 'alphanohtml');
			$object->fk_project      = GETPOST('fk_project', 'int');
			$object->title           = GETPOST('title', 'alphanohtml');
			$object->socid           = GETPOST('socid', 'int');
			$object->description     = GETPOST('description', 'restricthtml'); // Do not use 'alpha' here, we want field as it is
			$object->public          = GETPOST('public', 'alphanohtml');
			$object->opp_amount      = price2num(GETPOST('opp_amount', 'alphanohtml'));
			$object->budget_amount   = price2num(GETPOST('budget_amount', 'alphanohtml'));
			$object->date_c = dol_now();
			$object->date_start      = $date_start;
			$object->date_end        = $date_end;
			$object->date_start_event = $date_start_event;
			$object->date_end_event   = $date_end_event;
			$object->location        = $location;
			$object->statut          = $status;
			$object->opp_status      = $opp_status;
			$object->opp_percent     = $opp_percent;
			$object->usage_opportunity    = (GETPOST('usage_opportunity', 'alpha') == 'on' ? 1 : 0);
			$object->usage_task           = (GETPOST('usage_task', 'alpha') == 'on' ? 1 : 0);
			$object->usage_bill_time      = (GETPOST('usage_bill_time', 'alpha') == 'on' ? 1 : 0);
			$object->usage_organize_event = (GETPOST('usage_organize_event', 'alpha') == 'on' ? 1 : 0);

			// Fill array 'array_options' with data from add form
			$ret = $extrafields->setOptionalsFromPost(null, $object);
			if ($ret < 0) {
				$error++;
			}

			$result = $object->create($user);
			if (!$error && $result > 0) {
				// Add myself as project leader
				$typeofcontact = 'PROJECTLEADER';
				$result = $object->add_contact($user->id, $typeofcontact, 'internal');

				// -3 means type not found (PROJECTLEADER renamed, de-activated or deleted), so don't prevent creation if it has been the case
				if ($result == -3) {
					setEventMessage('ErrorPROJECTLEADERRoleMissingRestoreIt', 'errors');
					$error++;
				} elseif ($result < 0) {
					$langs->load("errors");
					setEventMessages($object->error, $object->errors, 'errors');
					$error++;
				}
			} else {
				$langs->load("errors");
				setEventMessages($object->error, $object->errors, 'errors');
				$error++;
			}
			if (!$error && !empty($object->id) > 0) {
				// Category association
				$categories = GETPOST('categories', 'array');
				$result = $object->setCategories($categories);
				if ($result < 0) {
					$langs->load("errors");
					setEventMessages($object->error, $object->errors, 'errors');
					$error++;
				}
			}

			if (!$error) {
				$db->commit();
				//Muhannad's Code start
				$ticketstat = new Ticket($db);
				$stores = GETPOST('options_stores', 'alpha');
				foreach($stores as $store){
					$defaultref = $ticketstat->getDefaultRef();
					$trackid = 'tic'.$object->id.bin2hex(random_bytes(10));
					$ticketsubject = 'Rollout '.$object->title;
					$query = "INSERT INTO `llx_ticket` (`entity`, `ref`, `track_id`, `fk_soc`, `fk_project`, `fk_user_create`, `subject`, `message`, `fk_statut`, `type_code`, `category_code`, `severity_code`, `datec`)"; 
					$query .= " VALUES ('1', '" . $defaultref . "', '". $trackid ."', '" . $object->socid . "', '".$object->id."', '".$user->id."', '".$ticketsubject."', '".$object->description."', '0', 'Roll', 'INSTA OT', 'Termin', '".date("Y-m-d H:i:s")."')";
					$db->query($query, 'ddl');
					$iid = $db->last_insert_id('llx_ticket');
					$query_extra = "INSERT INTO `llx_ticket_extrafields` (`fk_object`, `fk_store`, `customer`, `ordervia`) VALUES ('" . $iid . "', '". $store ."', '" . $object->array_options["options_customerbranch"] . "', 'Storema')";
					$db->query($query_extra, 'ddl');
				}
				// Muhannad's code end

				if (!empty($backtopage)) {
					$backtopage = preg_replace('/--IDFORBACKTOPAGE--|__ID__/', $object->id, $backtopage); // New method to autoselect project after a New on another form object creation
					$backtopage = $backtopage.'&projectid='.$object->id; // Old method
					header("Location: ".$backtopage);
					exit;
				} else {
					header("Location:card.php?id=".$object->id);
					exit;
				}
			} else {
				$db->rollback();
				unset($_POST["ref"]);
				$action = 'create';
			}
		} else {
			$action = 'create';
		}
	}

	if ($action == 'update' && empty(GETPOST('cancel')) && $permissiontoadd) {
		$sql = "SELECT stores";
		$sql .= " FROM llx_projet_extrafields";
		$sql .= " WHERE fk_object = ".$object->id.";";
		$result = $db->query($sql)->fetch_all()[0][0];
		$existStores = explode(",", $result);
		
		$stores = GETPOST('options_stores', 'alpha');
		$storesDef = array_diff($stores, $existStores);


		$error = 0;

		if (empty($ref)) {
			$error++;
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("Ref")), null, 'errors');
		}
		if (!GETPOST("title")) {
			$error++;
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("ProjectLabel")), null, 'errors');
		}

		$db->begin();

		if (!$error) {
			$object->oldcopy = clone $object;

			$old_start_date = $object->date_start;

			$object->ref          = GETPOST('ref', 'alpha');
			$object->fk_project   = GETPOST('fk_project', 'int');
			$object->title        = GETPOST('title', 'alphanohtml'); // Do not use 'alpha' here, we want field as it is
			$object->statut       = GETPOST('status', 'int');
			$object->socid        = GETPOST('socid', 'int');
			$object->description  = GETPOST('description', 'restricthtml'); // Do not use 'alpha' here, we want field as it is
			$object->public       = GETPOST('public', 'alpha');
			$object->date_start   = (!GETPOST('projectstart')) ? '' : $date_start;
			$object->date_end     = (!GETPOST('projectend')) ? '' : $date_end;
			$object->date_start_event = (!GETPOST('date_start_event')) ? '' : $date_start_event;
			$object->date_end_event   = (!GETPOST('date_end_event')) ? '' : $date_end_event;
			$object->location     = $location;
			if (GETPOSTISSET('opp_amount')) {
				$object->opp_amount   = price2num(GETPOST('opp_amount', 'alpha'));
			}
			if (GETPOSTISSET('budget_amount')) {
				$object->budget_amount = price2num(GETPOST('budget_amount', 'alpha'));
			}
			if (GETPOSTISSET('opp_status')) {
				$object->opp_status   = $opp_status;
			}
			if (GETPOSTISSET('opp_percent')) {
				$object->opp_percent  = $opp_percent;
			}
			$object->usage_opportunity    = (GETPOST('usage_opportunity', 'alpha') == 'on' ? 1 : 0);
			$object->usage_task           = (GETPOST('usage_task', 'alpha') == 'on' ? 1 : 0);
			$object->usage_bill_time      = (GETPOST('usage_bill_time', 'alpha') == 'on' ? 1 : 0);
			$object->usage_organize_event = (GETPOST('usage_organize_event', 'alpha') == 'on' ? 1 : 0);

			// Fill array 'array_options' with data from add form
			$ret = $extrafields->setOptionalsFromPost(null, $object, '@GETPOSTISSET');
			if ($ret < 0) {
				$error++;
			}
		}

		if (getDolGlobalString('PROJECT_USE_OPPORTUNITIES')) {
			if ($object->opp_amount && ($object->opp_status <= 0)) {
				$error++;
				setEventMessages($langs->trans("ErrorOppStatusRequiredIfAmount"), null, 'errors');
			}
		}

		if (!$error) {
			$result = $object->update($user);
			if ($result < 0) {
				$error++;
				if ($result == -4) {
					setEventMessages($langs->trans("ErrorRefAlreadyExists"), null, 'errors');
				} else {
					setEventMessages($object->error, $object->errors, 'errors');
				}
			} else {
				// Category association
				$categories = GETPOST('categories', 'array');
				$result = $object->setCategories($categories);
				if ($result < 0) {
					$error++;
					setEventMessages($object->error, $object->errors, 'errors');
				}
			}
		}

		if (!$error) {
			if (GETPOST("reportdate") && ($object->date_start != $old_start_date)) {
				$result = $object->shiftTaskDate($old_start_date);
				if ($result < 0) {
					$error++;
					setEventMessages($langs->trans("ErrorShiftTaskDate").':'.$object->error, $object->errors, 'errors');
				}
			}
		}

		// Check if we must change status
		if (GETPOST('closeproject')) {
			$resclose = $object->setClose($user);
			if ($resclose < 0) {
				$error++;
				setEventMessages($langs->trans("FailedToCloseProject").':'.$object->error, $object->errors, 'errors');
			}
		}


		if ($error) {
			$db->rollback();
			$action = 'edit';
		} else {
			$db->commit();

			//Muhannad's Code start
			$ticketstat = new Ticket($db);
			foreach($storesDef as $store){
				$defaultref = $ticketstat->getDefaultRef();
				$trackid = 'tic'.$object->id.bin2hex(random_bytes(10));
				$ticketsubject = 'Rollout '.$object->title;
				$query = "INSERT INTO `llx_ticket` (`entity`, `ref`, `track_id`, `fk_soc`, `fk_project`, `fk_user_create`, `subject`, `message`, `fk_statut`, `type_code`, `category_code`, `severity_code`, `datec`)"; 
				$query .= " VALUES ('1', '" . $defaultref . "', '". $trackid ."', '" . $object->socid . "', '".$object->id."', '".$user->id."', '".$ticketsubject."', '".$object->description."', '0', 'Roll', 'INSTA OT', 'Termin', '".date("Y-m-d H:i:s")."')";
				$db->query($query, 'ddl');
				$iid = $db->last_insert_id('llx_ticket');
				$query_extra = "INSERT INTO `llx_ticket_extrafields` (`fk_object`, `fk_store`, `customer`, `ordervia`) VALUES ('" . $iid . "', '". $store ."', '" . $object->array_options["options_customerbranch"] . "', 'Storema')";
				$db->query($query_extra, 'ddl');
			}
			// Muhannad's code end

			if (GETPOST('socid', 'int') > 0) {
				$object->fetch_thirdparty(GETPOST('socid', 'int'));
			} else {
				unset($object->thirdparty);
			}
		}
	}

	// Build doc
	if ($action == 'builddoc' && $permissiontoadd) {
		// Save last template used to generate document
		if (GETPOST('model')) {
			$object->setDocModel($user, GETPOST('model', 'alpha'));
		}

		$outputlangs = $langs;
		if (GETPOST('lang_id', 'aZ09')) {
			$outputlangs = new Translate("", $conf);
			$outputlangs->setDefaultLang(GETPOST('lang_id', 'aZ09'));
		}
		$result = $object->generateDocument($object->model_pdf, $outputlangs);
		if ($result <= 0) {
			setEventMessages($object->error, $object->errors, 'errors');
			$action = '';
		}
	}

	// Delete file in doc form
	if ($action == 'remove_file' && $permissiontoadd) {
		if ($object->id > 0) {
			require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

			$langs->load("other");
			$upload_dir = $conf->project->multidir_output[$object->entity];
			$file = $upload_dir.'/'.GETPOST('file');
			$ret = dol_delete_file($file, 0, 0, 0, $object);
			if ($ret) {
				setEventMessages($langs->trans("FileWasRemoved", GETPOST('file')), null, 'mesgs');
			} else {
				setEventMessages($langs->trans("ErrorFailToDeleteFile", GETPOST('file')), null, 'errors');
			}
			$action = '';
		}
	}


	if ($action == 'confirm_validate' && $confirm == 'yes' && $permissiontoadd) {
		$result = $object->setValid($user);
		if ($result <= 0) {
			setEventMessages($object->error, $object->errors, 'errors');
		}
	}

	if ($action == 'confirm_close' && $confirm == 'yes' && $permissiontoadd) {
		$result = $object->setClose($user);
		if ($result <= 0) {
			setEventMessages($object->error, $object->errors, 'errors');
		}
	}

	if ($action == 'confirm_reopen' && $confirm == 'yes' && $permissiontoadd) {
		$result = $object->setValid($user);
		if ($result <= 0) {
			setEventMessages($object->error, $object->errors, 'errors');
		}
	}

	if ($action == 'confirm_delete' && $confirm == 'yes' && $permissiontodelete) {
		$object->fetch($id);
		$result = $object->delete($user);
		if ($result > 0) {
			setEventMessages($langs->trans("RecordDeleted"), null, 'mesgs');

			if (!empty($_SESSION['pageforbacktolist']) && !empty($_SESSION['pageforbacktolist']['project'])) {
				$tmpurl = $_SESSION['pageforbacktolist']['project'];
				$tmpurl = preg_replace('/__SOCID__/', $object->socid, $tmpurl);
				$urlback = $tmpurl.(preg_match('/\?/', $tmpurl) ? '&' : '?'). 'restore_lastsearch_values=1';
			} else {
				$urlback = DOL_URL_ROOT.'/projet/list.php?restore_lastsearch_values=1';
			}

			header("Location: ".$urlback);
			exit;
		} else {
			dol_syslog($object->error, LOG_DEBUG);
			setEventMessages($object->error, $object->errors, 'errors');
		}
	}

	if ($action == 'confirm_clone' && $permissiontoadd && $confirm == 'yes') {
		$clone_contacts = GETPOST('clone_contacts') ? 1 : 0;
		$clone_tasks = GETPOST('clone_tasks') ? 1 : 0;
		$clone_project_files = GETPOST('clone_project_files') ? 1 : 0;
		$clone_task_files = GETPOST('clone_task_files') ? 1 : 0;
		$clone_notes = GETPOST('clone_notes') ? 1 : 0;
		$move_date = GETPOST('move_date') ? 1 : 0;
		$clone_thirdparty = GETPOST('socid', 'int') ? GETPOST('socid', 'int') : 0;

		$result = $object->createFromClone($user, $object->id, $clone_contacts, $clone_tasks, $clone_project_files, $clone_task_files, $clone_notes, $move_date, 0, $clone_thirdparty);
		if ($result <= 0) {
			setEventMessages($object->error, $object->errors, 'errors');
		} else {
			// Load new object
			$newobject = new Project($db);
			$newobject->fetch($result);
			$newobject->fetch_optionals();
			$newobject->fetch_thirdparty(); // Load new object
			$object = $newobject;
			$action = 'view';
			$comefromclone = true;

			setEventMessages($langs->trans("ProjectCreatedInDolibarr", $newobject->ref), "", 'mesgs');
			//var_dump($newobject); exit;
		}
	}

	// Actions to send emails
	$triggersendname = 'PROJECT_SENTBYMAIL';
	$paramname = 'id';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_PROJECT_TO'; // used to know the automatic BCC to add
	$trackid = 'proj'.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
}


/*
 *	View
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);
$formticket = new FormTicket($db);
$userstatic = new User($db);

$title = $langs->trans("Project").' - '.$object->ref.(!empty($object->thirdparty->name) ? ' - '.$object->thirdparty->name : '').(!empty($object->title) ? ' - '.$object->title : '');
if (getDolGlobalString('MAIN_HTML_TITLE') && preg_match('/projectnameonly/', $conf->global->MAIN_HTML_TITLE)) {
	$title = $object->ref.(!empty($object->thirdparty->name) ? ' - '.$object->thirdparty->name : '').(!empty($object->title) ? ' - '.$object->title : '');
}

$help_url = "EN:Module_Projects|FR:Module_Projets|ES:M&oacute;dulo_Proyectos|DE:Modul_Projekte";

llxHeader("", $title, $help_url);

$titleboth = $langs->trans("LeadsOrProjects");
$titlenew = $langs->trans("NewLeadOrProject"); // Leads and opportunities by default
if (!getDolGlobalInt('PROJECT_USE_OPPORTUNITIES')) {
	$titleboth = $langs->trans("Projects");
	$titlenew = $langs->trans("NewProject");
}
if (getDolGlobalInt('PROJECT_USE_OPPORTUNITIES') == 2) { // 2 = leads only
	$titleboth = $langs->trans("Leads");
	$titlenew = $langs->trans("NewLead");
}

if ($action == 'create' && $user->hasRight('projet', 'creer')) {
	/*
	 * Create
	 */

	$thirdparty = new Societe($db);
	
	if ($socid > 0) {
		$thirdparty->fetch($socid);
	}
	$st = array('method' => 'getContacts',
				'url' => dol_buildpath('/custom/stores/ajax/stores.php', 1),
				'htmlname' => 'contactid',
				'params' => array('add-customer-contact' => 'disabled')
			);
	$htmlname = 'options_customerbranch';
	if($socid){
		print '	<script type="text/javascript">
				$(document).ready(function () {
					runJs();
					jQuery("#'.$htmlname.'").change(function () {
						runJs();
					});
	
					function runJs(){
						console.log('.$socid.');
						var id = $("#'.$htmlname.'").val();
						var obj = '.json_encode($st).';
						$.getJSON(obj["url"],
								{
									id: id,
								},
								function(response) {
									console.log(response["data"]);
									//$("#options_stores").empty();
									var form = document.getElementById("checkboxForm");
									form.innerHTML = "";
									//var stores = document.getElementById("options_stores");
									for(var i = 0; i < response["data"].length; i++){
										//var option = document.createElement("option");
										
										// Set the text and value of the <option> element
										//option.text = response["data"][i][7];
										//option.value = response["data"][i][0];
										// option.data-html = response["data"][i][0];
										
										// Append the <option> element to the <select> element
										//stores.appendChild(option);


										var checkbox = document.createElement("input");
										checkbox.type = "checkbox";
										checkbox.id = response["data"][i][0];
										checkbox.name = "options_stores[]";
										checkbox.value = response["data"][i][0];
							
										var label = document.createElement("label");
										label.htmlFor = response["data"][i][0];
										label.appendChild(document.createTextNode(response["data"][i][7]));
							
										var div = document.createElement("div");
										div.className= "col-4";

										div.appendChild(checkbox);
										div.appendChild(label);
										form.appendChild(div);
										form.appendChild(document.createElement("br"));
									}
								}
						);
					}
				});
				</script>';

	} else {
		print '	<script type="text/javascript">
				$(document).ready(function () {
					jQuery("#'.$htmlname.'").change(function () {
						runJs();
					});
					
					
					function runJs(){
						var id = $("#'.$htmlname.'").val();
						var obj = '.json_encode($st).';
						$.getJSON(obj["url"],
								{
									id: id,
								},
								function(response) {
									console.log(response["data"]);
									//$("#options_stores").empty();
									var form = document.getElementById("checkboxForm");
									form.innerHTML = "";
									//var stores = document.getElementById("options_stores");
									for(var i = 0; i < response["data"].length; i++){
										//var option = document.createElement("option");
										
										// Set the text and value of the <option> element
										//option.text = response["data"][i][7];
										//option.value = response["data"][i][0];
										// option.data-html = response["data"][i][0];
										
										// Append the <option> element to the <select> element
										//stores.appendChild(option);


										var checkbox = document.createElement("input");
										checkbox.type = "checkbox";
										checkbox.id = response["data"][i][0];
										checkbox.name = "options_stores[]";
										checkbox.value = response["data"][i][0];
							
										var label = document.createElement("label");
										label.htmlFor = response["data"][i][0];
										label.appendChild(document.createTextNode(response["data"][i][7]));
							
										var div = document.createElement("div");
										div.className= "col-4";

										div.appendChild(checkbox);
										div.appendChild(label);
										form.appendChild(div);
										form.appendChild(document.createElement("br"));
									}
								}
						);
					}
				});
				</script>';
	}			

	print load_fiche_titre($titlenew, '', 'project');

	print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
	print '<input type="hidden" name="action" value="add">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';
	print '<input type="hidden" name="backtopagejsfields" value="'.$backtopagejsfields.'">';
	print '<input type="hidden" name="dol_openinpopup" value="'.$dol_openinpopup.'">';

	print dol_get_fiche_head();

	print '<table class="border centpercent tableforfieldcreate">';

	$defaultref = '';
	$modele = !getDolGlobalString('PROJECT_ADDON') ? 'mod_project_simple' : $conf->global->PROJECT_ADDON;

	// Search template files
	$file = '';
	$classname = '';
	$filefound = 0;
	$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
	foreach ($dirmodels as $reldir) {
		$file = dol_buildpath($reldir."core/modules/project/".$modele.'.php', 0);
		if (file_exists($file)) {
			$filefound = 1;
			$classname = $modele;
			break;
		}
	}

	if ($filefound) {
		$result = dol_include_once($reldir."core/modules/project/".$modele.'.php');
		$modProject = new $classname();

		$defaultref = $modProject->getNextValue($thirdparty, $object);
	}

	if (is_numeric($defaultref) && $defaultref <= 0) {
		$defaultref = '';
	}

	// Ref
	$suggestedref = (GETPOST("ref") ? GETPOST("ref") : $defaultref);
	print '<tr><td class="titlefieldcreate"><span class="fieldrequired">'.$langs->trans("Ref").'</span></td><td class><input class="maxwidth150onsmartphone" type="text" name="ref" value="'.dol_escape_htmltag($suggestedref).'">';
	if ($suggestedref) {
		print ' '.$form->textwithpicto('', $langs->trans("YouCanCompleteRef", $suggestedref));
	}
	print '</td></tr>';

	//External Ref
	print '<tr class="fieldrequired field_options_externalprojectnumber project_extras_externalprojectnumber trextrafields_collapse" data-element="extrafield" data-targetelement="project" data-targetid="">
				<td class="titlefieldcreate wordbreak">'.$langs->trans("External Project Number").'</td>
				<td class="valuefieldcreate project_extras_externalprojectnumber">
					<input type="text" class="flat minwidth400 maxwidthonsmartphone" name="options_externalprojectnumber" id="options_externalprojectnumber" maxlength="255" value="'.$object->array_options["options_externalprojectnumber"].'">
				</td>
			</tr>';

	// Label
	print '<tr><td><span class="fieldrequired">'.$langs->trans("Label").'</span></td><td><input class="width500 maxwidth150onsmartphone" type="text" name="title" value="'.dol_escape_htmltag(GETPOST("title", 'alphanohtml')).'" autofocus></td></tr>';

	// Parent
	if (getDolGlobalInt('PROJECT_ENABLE_SUB_PROJECT')) {
		print '<tr><td>'.$langs->trans("Parent").'</td><td class="maxwidthonsmartphone">';
		print img_picto('', 'project', 'class="pictofixedwidth"');
		$formproject->select_projects(-1, '', 'fk_project', 64, 0, 1, 1, 0, 0, 0, '', 0, 0, '', '', '');
		print '</td></tr>';
	}

	// Usage (opp, task, bill time, ...)
	if (getDolGlobalString('PROJECT_USE_OPPORTUNITIES') || !getDolGlobalString('PROJECT_HIDE_TASKS') || isModEnabled('eventorganization')) {
		print '<tr><td class="tdtop">';
		print $langs->trans("Usage");
		print '</td>';
		print '<td>';
		if (getDolGlobalString('PROJECT_USE_OPPORTUNITIES')) {
			print '<input type="checkbox" id="usage_opportunity" name="usage_opportunity"'.(GETPOSTISSET('usage_opportunity') ? (GETPOST('usage_opportunity', 'alpha') ? ' checked="checked"' : '') : ' checked="checked"').'"> ';
			$htmltext = $langs->trans("ProjectFollowOpportunity");
			print '<label for="usage_opportunity">'.$form->textwithpicto($langs->trans("ProjectFollowOpportunity"), $htmltext).'</label>';
			print '<script>';
			print '$( document ).ready(function() {
					jQuery("#usage_opportunity").change(function() {
						if (jQuery("#usage_opportunity").prop("checked")) {
							console.log("Show opportunities fields");
							jQuery(".classuseopportunity").show();
						} else {
							console.log("Hide opportunities fields "+jQuery("#usage_opportunity").prop("checked"));
							jQuery(".classuseopportunity").hide();
						}
					});
					';
			if (GETPOSTISSET('usage_opportunity') && !GETPOST('usage_opportunity')) {
				print 'jQuery(".classuseopportunity").hide();';
			}
			print '});';
			print '</script>';
			print '<br>';
		}
		if (!getDolGlobalString('PROJECT_HIDE_TASKS')) {
			print '<input type="checkbox" id="usage_task" name="usage_task"'.(GETPOSTISSET('usage_task') ? (GETPOST('usage_task', 'alpha') ? ' checked="checked"' : '') : ' checked="checked"').'"> ';
			$htmltext = $langs->trans("ProjectFollowTasks");
			print '<label for="usage_task">'.$form->textwithpicto($langs->trans("ProjectFollowTasks"), $htmltext).'</label>';
			print '<script>';
			print '$( document ).ready(function() {
					jQuery("#usage_task").change(function() {
						if (jQuery("#usage_task").prop("checked")) {
							console.log("Show task fields");
							jQuery(".classusetask").show();
						} else {
							console.log("Hide tasks fields "+jQuery("#usage_task").prop("checked"));
							jQuery(".classusetask").hide();
						}
					});
					';
			if (GETPOSTISSET('usage_task') && !GETPOST('usage_task')) {
				print 'jQuery(".classusetask").hide();';
			}
			print '});';
			print '</script>';
			print '<br>';
		}
		if (!getDolGlobalString('PROJECT_HIDE_TASKS') && getDolGlobalString('PROJECT_BILL_TIME_SPENT')) {
			print '<input type="checkbox" id="usage_bill_time" name="usage_bill_time"'.(GETPOSTISSET('usage_bill_time') ? (GETPOST('usage_bill_time', 'alpha') ? ' checked="checked"' : '') : '').'"> ';
			$htmltext = $langs->trans("ProjectBillTimeDescription");
			print '<label for="usage_bill_time">'.$form->textwithpicto($langs->trans("BillTime"), $htmltext).'</label>';
			print '<script>';
			print '$( document ).ready(function() {
					jQuery("#usage_bill_time").change(function() {
						if (jQuery("#usage_bill_time").prop("checked")) {
							console.log("Show bill time fields");
							jQuery(".classusebilltime").show();
						} else {
							console.log("Hide bill time fields "+jQuery("#usage_bill_time").prop("checked"));
							jQuery(".classusebilltime").hide();
						}
					});
					';
			if (GETPOSTISSET('usage_bill_time') && !GETPOST('usage_bill_time')) {
				print 'jQuery(".classusebilltime").hide();';
			}
			print '});';
			print '</script>';
			print '<br>';
		}
		if (isModEnabled('eventorganization')) {
			print '<input type="checkbox" id="usage_organize_event" name="usage_organize_event"'.(GETPOSTISSET('usage_organize_event') ? (GETPOST('usage_organize_event', 'alpha') ? ' checked="checked"' : '') : '').'"> ';
			$htmltext = $langs->trans("EventOrganizationDescriptionLong");
			print '<label for="usage_organize_event">'.$form->textwithpicto($langs->trans("ManageOrganizeEvent"), $htmltext).'</label>';
			print '<script>';
			print '$( document ).ready(function() {
					jQuery("#usage_organize_event").change(function() {
						if (jQuery("#usage_organize_event").prop("checked")) {
							console.log("Show organize event fields");
							jQuery(".classuseorganizeevent").show();
						} else {
							console.log("Hide organize event fields "+jQuery("#usage_organize_event").prop("checked"));
							jQuery(".classuseorganizeevent").hide();
						}
					});
					';
			if (!GETPOST('usage_organize_event')) {
				print 'jQuery(".classuseorganizeevent").hide();';
			}
			print '});';
			print '</script>';
		}
		print '</td>';
		print '</tr>';
	}

	// Thirdparty
	if (isModEnabled('societe')) {
		print '<tr><td>';
		print(!getDolGlobalString('PROJECT_THIRDPARTY_REQUIRED') ? '' : '<span class="fieldrequired">');
		print $langs->trans("ThirdParty");
		print(!getDolGlobalString('PROJECT_THIRDPARTY_REQUIRED') ? '' : '</span>');
		print '</td><td class="maxwidthonsmartphone">';
		$filter = '';
		if (getDolGlobalString('PROJECT_FILTER_FOR_THIRDPARTY_LIST')) {
			$filter = $conf->global->PROJECT_FILTER_FOR_THIRDPARTY_LIST;
		}
		$text = img_picto('', 'company').$form->select_company(GETPOST('socid', 'int'), 'socid', $filter, 'SelectThirdParty', 1, 0, array(), 0, 'minwidth300 widthcentpercentminusxx maxwidth500');
		if (!getDolGlobalString('PROJECT_CAN_ALWAYS_LINK_TO_ALL_SUPPLIERS') && empty($conf->dol_use_jmobile)) {
			$texthelp = $langs->trans("IfNeedToUseOtherObjectKeepEmpty");
			print $form->textwithtooltip($text.' '.img_help(), $texthelp, 1);
		} else {
			print $text;
		}
		if (!GETPOSTISSET('backtopage')) {
			$url = '/societe/card.php?action=create&client=3&fournisseur=0&backtopage='.urlencode($_SERVER["PHP_SELF"].'?action=create');
			$newbutton = '<span class="fa fa-plus-circle valignmiddle paddingleft" title="'.$langs->trans("AddThirdParty").'"></span>';
			// TODO @LDR Implement this
			if (getDolGlobalInt('MAIN_FEATURES_LEVEL') >= 2) {
				$tmpbacktopagejsfields = 'addthirdparty:socid,search_socid';
				print dolButtonToOpenUrlInDialogPopup('addthirdparty', $langs->transnoentitiesnoconv('AddThirdParty'), $newbutton, $url, '', '', '', $tmpbacktopagejsfields);
			} else {
				print ' <a href="'.DOL_URL_ROOT.$url.'">'.$newbutton.'</a>';
			}
		}
		print '</td></tr>';
	}

	// Status
	if ($status != '') {
		print '<tr><td>'.$langs->trans("Status").'</td><td>';
		print '<input type="hidden" name="status" value="'.$status.'">';
		print $object->LibStatut($status, 4);
		print '</td></tr>';
	}

	// Visibility
	print '<tr><td>'.$langs->trans("Visibility").'</td><td class="maxwidthonsmartphone">';
	$array = array();
	if (!getDolGlobalString('PROJECT_DISABLE_PRIVATE_PROJECT')) {
		$array[0] = $langs->trans("PrivateProject");
	}
	if (!getDolGlobalString('PROJECT_DISABLE_PUBLIC_PROJECT')) {
		$array[1] = $langs->trans("SharedProject");
	}

	if (count($array) > 0) {
		print $form->selectarray('public', $array, GETPOST('public'), 0, 0, 0, '', 0, 0, 0, '', '', 1);
	} else {
		print '<input type="hidden" name="public" id="public" value="'.GETPOST('public').'">';

		if (GETPOST('public') == 0) {
			print img_picto($langs->trans('PrivateProject'), 'private', 'class="paddingrightonly"');
			print $langs->trans("PrivateProject");
		} else {
			print img_picto($langs->trans('SharedProject'), 'world', 'class="paddingrightonly"');
			print $langs->trans("SharedProject");
		}
	}
	print '</td></tr>';

	if (getDolGlobalString('PROJECT_USE_OPPORTUNITIES')) {
		// Opportunity status
		print '<tr class="classuseopportunity"><td><span class="fieldrequired">'.$langs->trans("OpportunityStatus").'</span></td>';
		print '<td class="maxwidthonsmartphone">';
		print $formproject->selectOpportunityStatus('opp_status', GETPOSTISSET('opp_status') ? GETPOST('opp_status') : $object->opp_status, 1, 0, 0, 0, '', 0, 1);

		// Opportunity probability
		print ' <input class="width50 right" type="text" id="opp_percent" name="opp_percent" title="'.dol_escape_htmltag($langs->trans("OpportunityProbability")).'" value="'.dol_escape_htmltag(GETPOSTISSET('opp_percent') ? GETPOST('opp_percent') : '').'"><span class="hideonsmartphone"> %</span>';
		print '<input type="hidden" name="opp_percent_not_set" id="opp_percent_not_set" value="'.dol_escape_htmltag(GETPOSTISSET('opp_percent') ? '0' : '1').'">';
		print '</td>';
		print '</tr>';

		// Opportunity amount
		print '<tr class="classuseopportunity"><td>'.$langs->trans("OpportunityAmount").'</td>';
		print '<td><input class="width75 right" type="text" name="opp_amount" value="'.dol_escape_htmltag(GETPOSTISSET('opp_amount') ? GETPOST('opp_amount') : '').'">';
		print ' '.$langs->getCurrencySymbol($conf->currency);
		print '</td>';
		print '</tr>';
	}

	// Budget
	print '<tr><td>'.$langs->trans("Budget").'</td>';
	print '<td><input class="width75 right" type="text" name="budget_amount" value="'.dol_escape_htmltag(GETPOSTISSET('budget_amount') ? GETPOST('budget_amount') : '').'">';
	print ' '.$langs->getCurrencySymbol($conf->currency);
	print '</td>';
	print '</tr>';

	// Date project
	print '<tr><td>'.$langs->trans("Date").(isModEnabled('eventorganization') ? ' <span class="classuseorganizeevent">('.$langs->trans("Project").')</span>' : '').'</td><td>';
	print $form->selectDate(($date_start ? $date_start : ''), 'projectstart', 0, 0, 0, '', 1, 0);
	print ' <span class="opacitymedium"> '.$langs->trans("to").' </span> ';
	print $form->selectDate(($date_end ? $date_end : -1), 'projectend', 0, 0, 0, '', 1, 0);
	print '</td></tr>';

	if (isModEnabled('eventorganization')) {
		// Date event
		print '<tr class="classuseorganizeevent"><td>'.$langs->trans("Date").' ('.$langs->trans("Event").')</td><td>';
		print $form->selectDate(($date_start_event ? $date_start_event : -1), 'date_start_event', 1, 1, 1, '', 1, 0);
		print ' <span class="opacitymedium"> '.$langs->trans("to").' </span> ';
		print $form->selectDate(($date_end_event ? $date_end_event : -1), 'date_end_event', 1, 1, 1, '', 1, 0);
		print '</td></tr>';

		// Location
		print '<tr class="classuseorganizeevent"><td>'.$langs->trans("Location").'</td>';
		print '<td><input class="minwidth300 maxwidth500" type="text" name="location" value="'.dol_escape_htmltag($location).'"></td>';
		print '</tr>';
	}

	// Severity => Priority
	print '<tr><td><span class="fieldrequired"><label for="selectseverity_code">'.$langs->trans("TicketSeverity").'</span></label></td><td>';
	$formticket->selectSeveritiesTickets('', 'options_severity', '', 1, 1, 1);
	print '</td></tr>';

	// Description
	print '<tr><td class="tdtop">'.$langs->trans("Description").'</td>';
	print '<td>';
	$doleditor = new DolEditor('description', GETPOST("description", 'restricthtml'), '', 90, 'dolibarr_notes', '', false, true, getDolGlobalString('FCKEDITOR_ENABLE_SOCIETE'), ROWS_3, '90%');
	$doleditor->Create();
	print '</td></tr>';

	if (isModEnabled('categorie')) {
		// Categories
		print '<tr><td>'.$langs->trans("Categories").'</td><td colspan="3">';
		$cate_arbo = $form->select_all_categories(Categorie::TYPE_PROJECT, '', 'parent', 64, 0, 1);
		$arrayselected = GETPOST('categories', 'array');
		print img_picto('', 'category', 'class="pictofixedwidth"').$form->multiselectarray('categories', $cate_arbo, $arrayselected, '', 0, 'quatrevingtpercent widthcentpercentminusx', 0, 0);
		print "</td></tr>";
	}
	
	// Customer
	print '<tr><td class="titlefield">'.$langs->trans("Customer / Branch").'</td><td>';
	print '<span class="fas fa-building" style=" color: #6c6aa8;"></span>';
	print $form->select_company("", 'options_customerbranch', '', 1, 1, '', $events, 0, 'minwidth200');
	print '</td></tr>';

	if (isModEnabled('stores')) {
		// Stores
		print '<tr><td>'.$langs->trans("Stores").'</td><td colspan="3">';
		print '<input id="openPopupBtn" type="text" readonly>';
		print "</td></tr>";
	}

	// Other options
	// $parameters = array();
	// $reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	// print $hookmanager->resPrint;
	// if (empty($reshook)) {
	// 	print $object->showOptionals($extrafields, 'create');
	// }


	print '    

    
    <div id="popup" class="popup">
		<div class="popup-header">                                        
            <span class="close">&times;</span>
            <h2>Select Stores</h2>
		</div>
        <div class="popup-content">
            <div id="checkboxForm" class="row">
            </div>
        </div>
		<div class="popup-footer">
			<button type="button" id="selectBtn">Select</button>
			<button type="button" id="selectAllBtn">Select All</button>
		</div>
    </div>';


	print '<style>
	.row {
		--bs-gutter-x: 1.5rem;
		--bs-gutter-y: 0;
		display: flex;
		flex-wrap: wrap;
		margin-top: calc(-1* var(--bs-gutter-y));
		margin-right: calc(-.5* var(--bs-gutter-x));
		margin-left: calc(-.5* var(--bs-gutter-x));
	}
	.col-4 {
		flex: 0 0 auto;
		width: 10%;
	}
	.popup {
		display: none;
		position: fixed;
		top: 50%;
		width: 90%;
		height: 90%;
		left: 50%;
		transform: translate(-50%, -50%);
		border: 1px solid #ccc;
		background-color: #fff;
		padding: 20px;
		z-index: 9999;
	}
	
	.popup-content {
		padding: 5px;
		width: 100%;
		height: 80%;
		overflow-y: scroll;
		overflow-x: hidden;
	}
	.popup-content, .popup-header, .popup-footer {
		text-align: center;
	}
	
	.close {
		position: absolute;
		top: 5px;
		right: 10px;
		cursor: pointer;
	}
	
	#checkboxForm {
		margin-top: 20px;
	}
	
	#selectBtn {
		margin-top: 10px;
	}
	
	#openPopupBtn {
		width: 100%;
	}
	
	
	</style>';


	print '<script>
				document.addEventListener("DOMContentLoaded", function() {
					var selectAllBtn = document.getElementById("selectAllBtn");
					selectAllBtn.addEventListener("click", function() {
						var checkboxes = document.querySelectorAll("#checkboxForm input[type=\'checkbox\']");
						checkboxes.forEach(function(checkbox) {
							checkbox.checked = true;
						});
					});
					var openPopupBtn = document.getElementById("openPopupBtn");
					var popup = document.getElementById("popup");
					var closeBtn = document.querySelector(".close");
					var selectBtn = document.getElementById("selectBtn");
					
					openPopupBtn.addEventListener("click", function() {
						popup.style.display = "block";
					});
					
					closeBtn.addEventListener("click", function() {
						popup.style.display = "none";
					});
					
					selectBtn.addEventListener("click", function() {
						var selectedLabels = [];
						$("input[name=\'options_stores[]\']:checked").each(function() {
							var label = $("label[for=\'" + $(this).attr("id") + "\']").text();
							selectedLabels.push(label);
						});
						$("#openPopupBtn").val(selectedLabels.join(", "));
						popup.style.display = "none";
					});
				});
			</script>';

	print '</table>';

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel('CreateDraft');

	print '</form>';

	// Change probability from status or role of project
	// Set also dependencies between use taks and bill time
	print '<script type="text/javascript">
        jQuery(document).ready(function() {
        	function change_percent()
        	{
                var element = jQuery("#opp_status option:selected");
                var defaultpercent = element.attr("defaultpercent");
                /*if (jQuery("#opp_percent_not_set").val() == "") */
                jQuery("#opp_percent").val(defaultpercent);
        	}

			/*init_myfunc();*/
        	jQuery("#opp_status").change(function() {
        		change_percent();
        	});

        	jQuery("#usage_task").change(function() {
        		console.log("We click on usage task "+jQuery("#usage_task").is(":checked"));
                if (! jQuery("#usage_task").is(":checked")) {
                    jQuery("#usage_bill_time").prop("checked", false);
                }
        	});

        	jQuery("#usage_bill_time").change(function() {
        		console.log("We click on usage to bill time");
                if (jQuery("#usage_bill_time").is(":checked")) {
                    jQuery("#usage_task").prop("checked", true);
                }
        	});
        });
        </script>';
} elseif ($object->id > 0) {
	/*
	 * Show or edit
	 */

	$res = $object->fetch_optionals();

	// To verify role of users
	$userAccess = $object->restrictedProjectArea($user, 'read');
	$userWrite  = $object->restrictedProjectArea($user, 'write');
	$userDelete = $object->restrictedProjectArea($user, 'delete');
	//print "userAccess=".$userAccess." userWrite=".$userWrite." userDelete=".$userDelete;


	// Confirmation validation
	if ($action == 'validate') {
		print $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('ValidateProject'), $langs->trans('ConfirmValidateProject'), 'confirm_validate', '', 0, 1);
	}
	// Confirmation close
	if ($action == 'close') {
		print $form->formconfirm($_SERVER["PHP_SELF"]."?id=".$object->id, $langs->trans("CloseAProject"), $langs->trans("ConfirmCloseAProject"), "confirm_close", '', '', 1);
	}
	// Confirmation reopen
	if ($action == 'reopen') {
		print $form->formconfirm($_SERVER["PHP_SELF"]."?id=".$object->id, $langs->trans("ReOpenAProject"), $langs->trans("ConfirmReOpenAProject"), "confirm_reopen", '', '', 1);
	}
	// Confirmation delete
	if ($action == 'delete') {
		$text = $langs->trans("ConfirmDeleteAProject");
		$task = new Task($db);
		$taskarray = $task->getTasksArray(0, 0, $object->id, 0, 0);
		$nboftask = count($taskarray);
		if ($nboftask) {
			$text .= '<br>'.img_warning().' '.$langs->trans("ThisWillAlsoRemoveTasks", $nboftask);
		}
		print $form->formconfirm($_SERVER["PHP_SELF"]."?id=".$object->id, $langs->trans("DeleteAProject"), $text, "confirm_delete", '', '', 1);
	}

	// Clone confirmation
	if ($action == 'clone') {
		$formquestion = array(
			'text' => $langs->trans("ConfirmClone"),
			array('type' => 'other', 'name' => 'socid', 'label' => $langs->trans("SelectThirdParty"), 'value' => $form->select_company(GETPOST('socid', 'int') > 0 ? GETPOST('socid', 'int') : $object->socid, 'socid', '', "None", 0, 0, null, 0, 'minwidth200 maxwidth250')),
			array('type' => 'checkbox', 'name' => 'clone_contacts', 'label' => $langs->trans("CloneContacts"), 'value' => true),
			array('type' => 'checkbox', 'name' => 'clone_tasks', 'label' => $langs->trans("CloneTasks"), 'value' => true),
			array('type' => 'checkbox', 'name' => 'move_date', 'label' => $langs->trans("CloneMoveDate"), 'value' => true),
			array('type' => 'checkbox', 'name' => 'clone_notes', 'label' => $langs->trans("CloneNotes"), 'value' => true),
			array('type' => 'checkbox', 'name' => 'clone_project_files', 'label' => $langs->trans("CloneProjectFiles"), 'value' => false),
			array('type' => 'checkbox', 'name' => 'clone_task_files', 'label' => $langs->trans("CloneTaskFiles"), 'value' => false)
		);

		print $form->formconfirm($_SERVER["PHP_SELF"]."?id=".$object->id, $langs->trans("ToClone"), $langs->trans("ConfirmCloneProject"), "confirm_clone", $formquestion, '', 1, 400, 590);
	}


	print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';
	print '<input type="hidden" name="comefromclone" value="'.$comefromclone.'">';

	$head = project_prepare_head($object);

	if ($action == 'edit' && $userWrite > 0) {
		$st = array('method' => 'getContacts',
			'url' => dol_buildpath('/custom/stores/ajax/stores.php', 1),
			'htmlname' => 'contactid',
			'params' => array('add-customer-contact' => 'disabled')
		);
		$htmlname = 'options_customerbranch';
		$selected = $object->array_options["options_stores"];	
		
		print '	<script type="text/javascript">
				$(document).ready(function () {
					runJs();
					jQuery("#'.$htmlname.'").change(function () {
						runJs();
					});
	
					function runJs(){
						var id = $("#'.$htmlname.'").val();
						var obj = '.json_encode($st).';
						$.getJSON(obj["url"],
								{
									id: id,
								},
								function(response) {
									// console.log(response);
									var selected = "'.$selected.'";
									var list = selected.split(",");
									
									// $("#options_stores").empty();
									// var stores = document.getElementById("options_stores");
									// for(var i = 0; i < response["data"].length; i++){
									// 	var option = document.createElement("option");
										
									// 	// Set the text and value of the <option> element
									// 	option.text = response["data"][i][7];
									// 	option.value = response["data"][i][0];
									// 	// option.data-html = response["data"][i][0];
									// 	if (list.includes(response["data"][i][0])) {
									// 		option.selected = true;
									// 	}
									// 	// Append the <option> element to the <select> element
									// 	stores.appendChild(option);
									// }

									
									//$("#options_stores").empty();
									var form = document.getElementById("checkboxForm");
									form.innerHTML = "";
									var selectedList = [];
									for(var i = 0; i < response["data"].length; i++){
			
										var checkbox = document.createElement("input");
										checkbox.type = "checkbox";
										checkbox.id = response["data"][i][0];
										checkbox.name = "options_stores[]";
										checkbox.value = response["data"][i][0];
										if (list.includes(response["data"][i][0])) {
											checkbox.checked = true;
											selectedList.push(response["data"][i][7]);
										}
										var label = document.createElement("label");
										label.htmlFor = response["data"][i][0];
										label.appendChild(document.createTextNode(response["data"][i][7]));
							
										var div = document.createElement("div");
										div.className= "col-4";
										div.appendChild(checkbox);
										div.appendChild(label);
										form.appendChild(div);
										form.appendChild(document.createElement("br"));
									}
										
									$("#openPopupBtn").val(selectedList.join(", "));
								}
						);
					}
				});
				</script>';
		print dol_get_fiche_head($head, 'project', $langs->trans("Project"), 0, ($object->public ? 'projectpub' : 'project'));

		print '<table class="border centpercent">';

		// Ref
		$suggestedref = $object->ref;
		print '<tr><td class="titlefield fieldrequired">'.$langs->trans("Ref").'</td>';
		print '<td><input size="25" name="ref" value="'.$suggestedref.'">';
		print ' '.$form->textwithpicto('', $langs->trans("YouCanCompleteRef", $suggestedref));
		print '</td></tr>';

		//External Ref
		print '<tr class="fieldrequired field_options_externalprojectnumber project_extras_externalprojectnumber trextrafields_collapse" data-element="extrafield" data-targetelement="project" data-targetid="">
					<td class="titlefieldcreate wordbreak">'.$langs->trans("External Project Number").'</td>
					<td class="valuefieldcreate project_extras_externalprojectnumber">
						<input type="text" class="flat minwidth400 maxwidthonsmartphone" name="options_externalprojectnumber" id="options_externalprojectnumber" maxlength="255" value="'.$object->array_options["options_externalprojectnumber"].'">
					</td>
				</tr>';

		// Label
		print '<tr><td class="fieldrequired">'.$langs->trans("Label").'</td>';
		print '<td><input class="quatrevingtpercent" name="title" value="'.dol_escape_htmltag($object->title).'"></td></tr>';

		// Status
		print '<tr><td class="fieldrequired">'.$langs->trans("Status").'</td><td>';
		print '<select class="flat" name="status" id="status">';
		$statuses = $object->labelStatusShort;
		if (getDolGlobalString('MAIN_DISABLEDRAFTSTATUS') || getDolGlobalString('MAIN_DISABLEDRAFTSTATUS_PROJECT')) {
			unset($statuses[$object::STATUS_DRAFT]);
		}
		foreach ($statuses as $key => $val) {
			print '<option value="'.$key.'"'.((GETPOSTISSET('status') ? GETPOST('status') : $object->statut) == $key ? ' selected="selected"' : '').'>'.$langs->trans($val).'</option>';
		}
		print '</select>';
		print ajax_combobox('status');
		print '</td></tr>';

		// Parent
		if (getDolGlobalInt('PROJECT_ENABLE_SUB_PROJECT')) {
			print '<tr><td>'.$langs->trans("Parent").'</td><td class="maxwidthonsmartphone">';
			print img_picto('', 'project', 'class="pictofixedwidth"');
			$formproject->select_projects(-1, $object->fk_project, 'fk_project', 64, 0, 1, 1, 0, 0, 0, '', 0, 0, '', '', '');
			print '</td></tr>';
		}

		// Usage
		if (getDolGlobalString('PROJECT_USE_OPPORTUNITIES') || !getDolGlobalString('PROJECT_HIDE_TASKS') || isModEnabled('eventorganization')) {
			print '<tr><td class="tdtop">';
			print $langs->trans("Usage");
			print '</td>';
			print '<td>';
			if (getDolGlobalString('PROJECT_USE_OPPORTUNITIES')) {
				print '<input type="checkbox" id="usage_opportunity" name="usage_opportunity"'.(GETPOSTISSET('usage_opportunity') ? (GETPOST('usage_opportunity', 'alpha') != '' ? ' checked="checked"' : '') : ($object->usage_opportunity ? ' checked="checked"' : '')).'> ';
				$htmltext = $langs->trans("ProjectFollowOpportunity");
				print '<label for="usage_opportunity">'.$form->textwithpicto($langs->trans("ProjectFollowOpportunity"), $htmltext).'</label>';
				print '<script>';
				print '$( document ).ready(function() {
					jQuery("#usage_opportunity").change(function() {
						set_usage_opportunity();
					});

					set_usage_opportunity();

					function set_usage_opportunity() {
						console.log("set_usage_opportunity");
						if (jQuery("#usage_opportunity").prop("checked")) {
							console.log("Show opportunities fields");
							jQuery(".classuseopportunity").show();
						} else {
							console.log("Hide opportunities fields "+jQuery("#usage_opportunity").prop("checked"));
							jQuery(".classuseopportunity").hide();
						}
					}
				});';
				print '</script>';
				print '<br>';
			}
			if (!getDolGlobalString('PROJECT_HIDE_TASKS')) {
				print '<input type="checkbox" id="usage_task" name="usage_task"' . (GETPOSTISSET('usage_task') ? (GETPOST('usage_task', 'alpha') != '' ? ' checked="checked"' : '') : ($object->usage_task ? ' checked="checked"' : '')) . '> ';
				$htmltext = $langs->trans("ProjectFollowTasks");
				print '<label for="usage_task">'.$form->textwithpicto($langs->trans("ProjectFollowTasks"), $htmltext).'</label>';
				print '<script>';
				print '$( document ).ready(function() {
					jQuery("#usage_task").change(function() {
						set_usage_task();
					});

					set_usage_task();

					function set_usage_task() {
						console.log("set_usage_task");
						if (jQuery("#usage_task").prop("checked")) {
							console.log("Show task fields");
							jQuery(".classusetask").show();
						} else {
							console.log("Hide task fields "+jQuery("#usage_task").prop("checked"));
							jQuery(".classusetask").hide();
						}
					}
				});';
				print '</script>';
				print '<br>';
			}
			if (!getDolGlobalString('PROJECT_HIDE_TASKS') && getDolGlobalString('PROJECT_BILL_TIME_SPENT')) {
				print '<input type="checkbox" id="usage_bill_time" name="usage_bill_time"' . (GETPOSTISSET('usage_bill_time') ? (GETPOST('usage_bill_time', 'alpha') != '' ? ' checked="checked"' : '') : ($object->usage_bill_time ? ' checked="checked"' : '')) . '> ';
				$htmltext = $langs->trans("ProjectBillTimeDescription");
				print '<label for="usage_bill_time">'.$form->textwithpicto($langs->trans("BillTime"), $htmltext).'</label>';
				print '<script>';
				print '$( document ).ready(function() {
					jQuery("#usage_bill_time").change(function() {
						set_usage_bill_time();
					});

					set_usage_bill_time();

					function set_usage_bill_time() {
						console.log("set_usage_bill_time");
						if (jQuery("#usage_bill_time").prop("checked")) {
							console.log("Show bill time fields");
							jQuery(".classusebilltime").show();
						} else {
							console.log("Hide bill time fields "+jQuery("#usage_bill_time").prop("checked"));
							jQuery(".classusebilltime").hide();
						}
					}
				});';
				print '</script>';
				print '<br>';
			}
			if (isModEnabled('eventorganization')) {
				print '<input type="checkbox" id="usage_organize_event" name="usage_organize_event"'. (GETPOSTISSET('usage_organize_event') ? (GETPOST('usage_organize_event', 'alpha') != '' ? ' checked="checked"' : '') : ($object->usage_organize_event ? ' checked="checked"' : '')) . '> ';
				$htmltext = $langs->trans("EventOrganizationDescriptionLong");
				print '<label for="usage_organize_event">'.$form->textwithpicto($langs->trans("ManageOrganizeEvent"), $htmltext).'</label>';
				print '<script>';
				print '$( document ).ready(function() {
					jQuery("#usage_organize_event").change(function() {
						set_usage_event();
					});

					set_usage_event();

					function set_usage_event() {
						console.log("set_usage_event");
						if (jQuery("#usage_organize_event").prop("checked")) {
							console.log("Show organize event fields");
							jQuery(".classuseorganizeevent").show();
						} else {
							console.log("Hide organize event fields "+jQuery("#usage_organize_event").prop("checked"));
							jQuery(".classuseorganizeevent").hide();
						}
					}
				});';
				print '</script>';
			}
			print '</td></tr>';
		}
		print '</td></tr>';

		// Thirdparty
		if (isModEnabled('societe')) {
			print '<tr><td>';
			print(!getDolGlobalString('PROJECT_THIRDPARTY_REQUIRED') ? '' : '<span class="fieldrequired">');
			print $langs->trans("ThirdParty");
			print(!getDolGlobalString('PROJECT_THIRDPARTY_REQUIRED') ? '' : '</span>');
			print '</td><td>';
			$filter = '';
			if (getDolGlobalString('PROJECT_FILTER_FOR_THIRDPARTY_LIST')) {
				$filter = $conf->global->PROJECT_FILTER_FOR_THIRDPARTY_LIST;
			}
			$text = img_picto('', 'company', 'class="pictofixedwidth"');
			$text .= $form->select_company(!empty($object->thirdparty->id) ? $object->thirdparty->id : "", 'socid', $filter, 'None', 1, 0, array(), 0, 'minwidth300');
			if (!getDolGlobalString('PROJECT_CAN_ALWAYS_LINK_TO_ALL_SUPPLIERS') && empty($conf->dol_use_jmobile)) {
				$texthelp = $langs->trans("IfNeedToUseOtherObjectKeepEmpty");
				print $form->textwithtooltip($text.' '.img_help(), $texthelp, 1, 0, '', '', 2);
			} else {
				print $text;
			}
			print '</td></tr>';
		}

		// Visibility
		print '<tr><td>'.$langs->trans("Visibility").'</td><td>';
		$array = array();
		if (!getDolGlobalString('PROJECT_DISABLE_PRIVATE_PROJECT')) {
			$array[0] = $langs->trans("PrivateProject");
		}
		if (!getDolGlobalString('PROJECT_DISABLE_PUBLIC_PROJECT')) {
			$array[1] = $langs->trans("SharedProject");
		}

		if (count($array) > 0) {
			print $form->selectarray('public', $array, $object->public, 0, 0, 0, '', 0, 0, 0, '', '', 1);
		} else {
			print '<input type="hidden" id="public" name="public" value="'.$object->public.'">';

			if ($object->public == 0) {
				print img_picto($langs->trans('PrivateProject'), 'private', 'class="paddingrightonly"');
				print $langs->trans("PrivateProject");
			} else {
				print img_picto($langs->trans('SharedProject'), 'world', 'class="paddingrightonly"');
				print $langs->trans("SharedProject");
			}
		}
		print '</td></tr>';

		if (getDolGlobalString('PROJECT_USE_OPPORTUNITIES')) {
			$classfortr = ($object->usage_opportunity ? '' : ' hideobject');
			// Opportunity status
			print '<tr class="classuseopportunity'.$classfortr.'"><td>'.$langs->trans("OpportunityStatus").'</td>';
			print '<td>';
			print '<div>';
			print $formproject->selectOpportunityStatus('opp_status', $object->opp_status, 1, 0, 0, 0, 'minwidth150 inline-block valignmiddle', 1, 1);

			// Opportunity probability
			print ' <input class="width50 right" type="text" id="opp_percent" name="opp_percent" title="'.dol_escape_htmltag($langs->trans("OpportunityProbability")).'" value="'.(GETPOSTISSET('opp_percent') ? GETPOST('opp_percent') : (strcmp($object->opp_percent, '') ? vatrate($object->opp_percent) : '')).'"> %';
			print '<span id="oldopppercent" class="opacitymedium"></span>';
			print '</div>';

			print '<div id="divtocloseproject" class="inline-block valign clearboth paddingtop" style="display: none;">';
			print '<input type="checkbox" id="inputcloseproject" name="closeproject" />';
			print '<label for="inputcloseproject">';
			print $form->textwithpicto($langs->trans("AlsoCloseAProject"), $langs->trans("AlsoCloseAProjectTooltip")).'</label>';
			print ' </div>';

			print '</td>';
			print '</tr>';

			// Opportunity amount
			print '<tr class="classuseopportunity'.$classfortr.'"><td>'.$langs->trans("OpportunityAmount").'</td>';
			print '<td><input class="width75 right" type="text" name="opp_amount" value="'.(GETPOSTISSET('opp_amount') ? GETPOST('opp_amount') : (strcmp($object->opp_amount, '') ? price2num($object->opp_amount) : '')).'">';
			print $langs->getCurrencySymbol($conf->currency);
			print '</td>';
			print '</tr>';
		}

		// Budget
		print '<tr><td>'.$langs->trans("Budget").'</td>';
		print '<td><input class="width75 right" type="text" name="budget_amount" value="'.(GETPOSTISSET('budget_amount') ? GETPOST('budget_amount') : (strcmp($object->budget_amount, '') ? price2num($object->budget_amount) : '')).'">';
		print $langs->getCurrencySymbol($conf->currency);
		print '</td>';
		print '</tr>';

		// Date project
		print '<tr><td>'.$langs->trans("Date").(isModEnabled('eventorganization') ? ' <span class="classuseorganizeevent">('.$langs->trans("Project").')</span>' : '').'</td><td>';
		print $form->selectDate($object->date_start ? $object->date_start : -1, 'projectstart', 0, 0, 0, '', 1, 0);
		print ' <span class="opacitymedium"> '.$langs->trans("to").' </span> ';
		print $form->selectDate($object->date_end ? $object->date_end : -1, 'projectend', 0, 0, 0, '', 1, 0);
		$object->getLinesArray(null, 0);
		if (!empty($object->usage_task) && !empty($object->lines)) {
			print ' <span id="divreportdate" class="hidden">&nbsp; &nbsp; <input type="checkbox" class="valignmiddle" id="reportdate" name="reportdate" value="yes" ';
			if ($comefromclone) {
				print 'checked ';
			}
			print '/><label for="reportdate" class="valignmiddle opacitymedium">'.$langs->trans("ProjectReportDate").'</label></span>';
		}
		print '</td></tr>';

		if (isModEnabled('eventorganization')) {
			// Date event
			print '<tr class="classuseorganizeevent"><td>'.$langs->trans("Date").' ('.$langs->trans("Event").')</td><td>';
			print $form->selectDate(($date_start_event ? $date_start_event : ($object->date_start_event ? $object->date_start_event : -1)), 'date_start_event', 1, 1, 1, '', 1, 0);
			print ' <span class="opacitymedium"> '.$langs->trans("to").' </span> ';
			print $form->selectDate(($date_end_event ? $date_end_event : ($object->date_end_event ? $object->date_end_event : -1)), 'date_end_event', 1, 1, 1, '', 1, 0);
			print '</td></tr>';

			// Location
			print '<tr class="classuseorganizeevent"><td>'.$langs->trans("Location").'</td>';
			print '<td><input class="minwidth300 maxwidth500" type="text" name="location" value="'.dol_escape_htmltag(GETPOSTISSET('location') ? GETPOST('location') : $object->location).'"></td>';
			print '</tr>';
		}

		// Severity => Priority
		print '<tr><td><span class="fieldrequired"><label for="selectseverity_code">'.$langs->trans("TicketSeverity").'</span></label></td><td>';
		$formticket->selectSeveritiesTickets($object->array_options["options_severity"], 'options_severity', '', 1, 1, 1);
		print '</td></tr>';

		// Description
		print '<tr><td class="tdtop">'.$langs->trans("Description").'</td>';
		print '<td>';
		$doleditor = new DolEditor('description', $object->description, '', 90, 'dolibarr_notes', '', false, true, getDolGlobalInt('FCKEDITOR_ENABLE_SOCIETE'), ROWS_3, '90%');
		$doleditor->Create();
		print '</td></tr>';

		// Tags-Categories
		if (isModEnabled('categorie')) {
			$arrayselected = array();
			print '<tr><td>'.$langs->trans("Categories").'</td><td>';
			$cate_arbo = $form->select_all_categories(Categorie::TYPE_PROJECT, '', 'parent', 64, 0, 1);
			$c = new Categorie($db);
			$cats = $c->containing($object->id, Categorie::TYPE_PROJECT);
			foreach ($cats as $cat) {
				$arrayselected[] = $cat->id;
			}
			print img_picto('', 'category', 'class="pictofixedwidth"').$form->multiselectarray('categories', $cate_arbo, $arrayselected, 0, 0, 'quatrevingtpercent widthcentpercentminusx', 0, '0');
			print "</td></tr>";
		}

		// Other options
		// $parameters = array();
		// $reshook = $hookmanager->executeHooks('formObjectOptions', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		
		// print $hookmanager->resPrint;
		// if (empty($reshook)) {
		// 	print $object->showOptionals($extrafields, 'edit');
		// }
		
	// Customer
	print '<tr><td class="titlefield">'.$langs->trans("Customer / Branch").'</td><td>';
	print '<span class="fas fa-building" style=" color: #6c6aa8;"></span>';
	print $form->select_company($object->array_options["options_customerbranch"], 'options_customerbranch', '', 1, 1, '', $events, 0, 'minwidth200');
	print '</td></tr>';

	if (isModEnabled('stores')) {
		// Stores
		print '<tr><td>'.$langs->trans("Stores").'</td><td colspan="3">';
		print '<input id="openPopupBtn" type="text" readonly>';
		print "</td></tr>";
	}



	print '    

    
    <div id="popup" class="popup">
		<div class="popup-header">                                        
            <span class="close">&times;</span>
            <h2>Select Stores</h2>
		</div>
        <div class="popup-content">
            <div id="checkboxForm" class="row">
            </div>
        </div>
		<div class="popup-footer">
			<button type="button" id="selectBtn">Select</button>
			<button type="button" id="selectAllBtn">Select All</button>
		</div>
    </div>';


	print '<style>
	.row {
		--bs-gutter-x: 1.5rem;
		--bs-gutter-y: 0;
		display: flex;
		flex-wrap: wrap;
		margin-top: calc(-1* var(--bs-gutter-y));
		margin-right: calc(-.5* var(--bs-gutter-x));
		margin-left: calc(-.5* var(--bs-gutter-x));
	}
	.col-4 {
		flex: 0 0 auto;
		width: 10%;
	}
	.popup {
		display: none;
		position: fixed;
		top: 50%;
		width: 90%;
		height: 90%;
		left: 50%;
		transform: translate(-50%, -50%);
		border: 1px solid #ccc;
		background-color: #fff;
		padding: 20px;
		z-index: 9999;
	}
	
	.popup-content {
		padding: 5px;
		width: 100%;
		height: 80%;
		overflow-y: scroll;
		overflow-x: hidden;
	}
	.popup-content, .popup-header, .popup-footer {
		text-align: center;
	}
	
	.close {
		position: absolute;
		top: 5px;
		right: 10px;
		cursor: pointer;
	}
	
	#checkboxForm {
		margin-top: 20px;
	}
	
	#selectBtn {
		margin-top: 10px;
	}
	
	#openPopupBtn {
		width: 100%;
	}
	
	
	</style>';


	print '<script>
				document.addEventListener("DOMContentLoaded", function() {

					var selectAllBtn = document.getElementById("selectAllBtn");
					selectAllBtn.addEventListener("click", function() {
						var checkboxes = document.querySelectorAll("#checkboxForm input[type=\'checkbox\']");
						checkboxes.forEach(function(checkbox) {
							checkbox.checked = true;
						});
					});
					var openPopupBtn = document.getElementById("openPopupBtn");
					var popup = document.getElementById("popup");
					var closeBtn = document.querySelector(".close");
					var selectBtn = document.getElementById("selectBtn");
					
					openPopupBtn.addEventListener("click", function() {
						popup.style.display = "block";
					});
					
					closeBtn.addEventListener("click", function() {
						popup.style.display = "none";
					});
					
					selectBtn.addEventListener("click", function() {
						var selectedLabels = [];
						$("input[name=\'options_stores[]\']:checked").each(function() {
							var label = $("label[for=\'" + $(this).attr("id") + "\']").text();
							selectedLabels.push(label);
						});
						console.log(selectedLabels);
						$("#openPopupBtn").val(selectedLabels.join(", "));
						popup.style.display = "none";
					});
				});
			</script>';

		print '</table>';
	} else {
		print dol_get_fiche_head($head, 'project', $langs->trans("Project"), -1, ($object->public ? 'projectpub' : 'project'));

		// Project card
		if (!empty($_SESSION['pageforbacktolist']) && !empty($_SESSION['pageforbacktolist']['project'])) {
			$tmpurl = $_SESSION['pageforbacktolist']['project'];
			$tmpurl = preg_replace('/__SOCID__/', $object->socid, $tmpurl);
			$linkback = '<a href="'.$tmpurl.(preg_match('/\?/', $tmpurl) ? '&' : '?'). 'restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';
		} else {
			$linkback = '<a href="'.DOL_URL_ROOT.'/projet/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';
		}

		$morehtmlref = '<div class="refidno">';
		// Title
		$morehtmlref .= dol_escape_htmltag($object->title);
		$morehtmlref .= '<br>';
		// Thirdparty
		if (!empty($object->thirdparty->id) && $object->thirdparty->id > 0) {
			$morehtmlref .= $object->thirdparty->getNomUrl(1, 'project');
		}
		// Parent
		if (getDolGlobalInt('PROJECT_ENABLE_SUB_PROJECT')) {
			if (!empty($object->fk_project) && $object->fk_project) {
				$parent = new Project($db);
				$parent->fetch($object->fk_project);
				$morehtmlref .= $langs->trans("Child of").' '.$parent->getNomUrl(1, 'project').' '.$parent->title;
			}
		}
		$morehtmlref .= '</div>';

		// Define a complementary filter for search of next/prev ref.
		if (!$user->hasRight('projet', 'all', 'lire')) {
			$objectsListId = $object->getProjectsAuthorizedForUser($user, 0, 0);
			$object->next_prev_filter = "rowid IN (".$db->sanitize(count($objectsListId) ? join(',', array_keys($objectsListId)) : '0').")";
		}

		dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);

		print '<div class="fichecenter">';
		print '<div class="fichehalfleft">';
		print '<div class="underbanner clearboth"></div>';

		print '<table class="border tableforfield centpercent">';

		// Usage
		if (getDolGlobalString('PROJECT_USE_OPPORTUNITIES') || !getDolGlobalString('PROJECT_HIDE_TASKS') || isModEnabled('eventorganization')) {
			print '<tr><td class="tdtop">';
			print $langs->trans("Usage");
			print '</td>';
			print '<td>';
			if (getDolGlobalString('PROJECT_USE_OPPORTUNITIES')) {
				print '<input type="checkbox" disabled name="usage_opportunity"'.(GETPOSTISSET('usage_opportunity') ? (GETPOST('usage_opportunity', 'alpha') != '' ? ' checked="checked"' : '') : ($object->usage_opportunity ? ' checked="checked"' : '')).'> ';
				$htmltext = $langs->trans("ProjectFollowOpportunity");
				print $form->textwithpicto($langs->trans("ProjectFollowOpportunity"), $htmltext);
				print '<br>';
			}
			if (!getDolGlobalString('PROJECT_HIDE_TASKS')) {
				print '<input type="checkbox" disabled name="usage_task"'.(GETPOSTISSET('usage_task') ? (GETPOST('usage_task', 'alpha') != '' ? ' checked="checked"' : '') : ($object->usage_task ? ' checked="checked"' : '')).'> ';
				$htmltext = $langs->trans("ProjectFollowTasks");
				print $form->textwithpicto($langs->trans("ProjectFollowTasks"), $htmltext);
				print '<br>';
			}
			if (!getDolGlobalString('PROJECT_HIDE_TASKS') && getDolGlobalString('PROJECT_BILL_TIME_SPENT')) {
				print '<input type="checkbox" disabled name="usage_bill_time"'.(GETPOSTISSET('usage_bill_time') ? (GETPOST('usage_bill_time', 'alpha') != '' ? ' checked="checked"' : '') : ($object->usage_bill_time ? ' checked="checked"' : '')).'> ';
				$htmltext = $langs->trans("ProjectBillTimeDescription");
				print $form->textwithpicto($langs->trans("BillTime"), $htmltext);
				print '<br>';
			}

			if (isModEnabled('eventorganization')) {
				print '<input type="checkbox" disabled name="usage_organize_event"'.(GETPOSTISSET('usage_organize_event') ? (GETPOST('usage_organize_event', 'alpha') != '' ? ' checked="checked"' : '') : ($object->usage_organize_event ? ' checked="checked"' : '')).'> ';
				$htmltext = $langs->trans("EventOrganizationDescriptionLong");
				print $form->textwithpicto($langs->trans("ManageOrganizeEvent"), $htmltext);
			}
			print '</td></tr>';
		}

		// Visibility
		print '<tr><td class="titlefield">'.$langs->trans("Visibility").'</td><td>';
		if ($object->public) {
			print img_picto($langs->trans('SharedProject'), 'world', 'class="paddingrightonly"');
			print $langs->trans('SharedProject');
		} else {
			print img_picto($langs->trans('PrivateProject'), 'private', 'class="paddingrightonly"');
			print $langs->trans('PrivateProject');
		}
		print '</td></tr>';

		if (getDolGlobalString('PROJECT_USE_OPPORTUNITIES') && !empty($object->usage_opportunity)) {
			// Opportunity status
			print '<tr><td>'.$langs->trans("OpportunityStatus").'</td><td>';
			$code = dol_getIdFromCode($db, $object->opp_status, 'c_lead_status', 'rowid', 'code');
			if ($code) {
				print $langs->trans("OppStatus".$code);
			}

			// Opportunity percent
			print ' <span title="'.$langs->trans("OpportunityProbability").'"> / ';
			if (strcmp($object->opp_percent, '')) {
				print price($object->opp_percent, 0, $langs, 1, 0).' %';
			}
			print '</span></td></tr>';

			// Opportunity Amount
			print '<tr><td>'.$langs->trans("OpportunityAmount").'</td><td>';
			if (strcmp($object->opp_amount, '')) {
				print '<span class="amount">'.price($object->opp_amount, 0, $langs, 1, 0, -1, $conf->currency).'</span>';
				if (strcmp($object->opp_percent, '')) {
					print ' &nbsp; &nbsp; &nbsp; <span title="'.dol_escape_htmltag($langs->trans('OpportunityWeightedAmount')).'"><span class="opacitymedium">'.$langs->trans("Weighted").'</span>: <span class="amount">'.price($object->opp_amount * $object->opp_percent / 100, 0, $langs, 1, 0, -1, $conf->currency).'</span></span>';
				}
			}
			print '</td></tr>';
		}

		// Budget
		print '<tr><td>'.$langs->trans("Budget").'</td><td>';
		if (!is_null($object->budget_amount) && strcmp($object->budget_amount, '')) {
			print '<span class="amount">'.price($object->budget_amount, 0, $langs, 1, 0, 0, $conf->currency).'</span>';
		}
		print '</td></tr>';

		// Date start - end project
		print '<tr><td>'.$langs->trans("Dates").'</td><td>';
		$start = dol_print_date($object->date_start, 'day');
		print($start ? $start : '?');
		$end = dol_print_date($object->date_end, 'day');
		print ' <span class="opacitymedium">-</span> ';
		print($end ? $end : '?');
		if ($object->hasDelay()) {
			print img_warning("Late");
		}
		print '</td></tr>';

		// Other attributes
		$cols = 2;
		include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_view.tpl.php';

		// Project Stores
		if (isModEnabled('stores')) {
			$stores = [];
			if($object->array_options["options_stores"]){
				$stores = explode(",", $object->array_options["options_stores"]);
			}

			print '<tr><td class="valignmiddle">'.$langs->trans("Project Stores").'</td><td>';
			print count($stores);
			print "</td></tr>";
		}

		print '</table>';

		print '<script>';
		print ' 
				$( document ).ready(function() {
					var tables = document.getElementsByClassName("tableforfield");
					console.log(tables);
					for (var j = 0; j < tables.length; j++) {
					var rows = tables[j].getElementsByTagName("tr");
				
					for (var i = 0; i < rows.length; i++) {
						var cells = rows[i].getElementsByClassName("project_extras_stores");
						if (cells.length > 0) {
							rows[i].style.display = "none";
						}
					}
					}
				});';
		print '</script>';

		print '</div>';
		print '<div class="fichehalfright">';
		print '<div class="underbanner clearboth"></div>';

		print '<table class="border tableforfield centpercent">';

		// Description
		print '<td class="titlefield tdtop">'.$langs->trans("Description").'</td><td>';
		print dol_htmlentitiesbr($object->description);
		print '</td></tr>';

		// Categories
		if (isModEnabled('categorie')) {
			print '<tr><td class="valignmiddle">'.$langs->trans("Categories").'</td><td>';
			print $form->showCategories($object->id, Categorie::TYPE_PROJECT, 1);
			print "</td></tr>";
		}

		// Project Stores
		if (isModEnabled('stores')) {
			$client_stores = "SELECT *";
			$client_stores .= " FROM llx_stores_branch s";
			$client_stores .= " WHERE s.fk_soc = ".$object->socid.";";
			$result = $db->query($client_stores);
			$num = $db->num_rows($result);

			print '<tr><td class="valignmiddle">'.$langs->trans("Client Stores").'</td><td>';
			print $num ? $num : 0;
			print "</td></tr>";
		}
		print '</table>';
		print '<br>';

		// SQL query to fetch countries
		$sql_countries = "SELECT DISTINCT country_id FROM llx_stores_branch";
		$result_countries = $db->query($sql_countries);
		$countries = array();
		if ($result_countries) {
			while ($row = $db->fetch_array($result_countries)) {
				if($row['country_id']){
					$country_info = array(
						'country' => getCountry($row['country_id'], 2),
						'country_id' => $row['country_id']
					);
					$countries[] = $country_info;
				}
			}
		}
		print '
		<style>
			.row {
				--bs-gutter-y: 0;
				display: flex;
				flex-wrap: wrap;
				margin-top: calc(-1* var(--bs-gutter-y));
				margin-right: calc(-.5* var(--bs-gutter-x));
				margin-left: calc(-.5* var(--bs-gutter-x));
			}
			.col-4 {
				flex: 0 0 auto;
				width: 5%;
				text-align: center;
				border: 1px solid #00000040;
			}
			.clickable {
				cursor: pointer
			}
		</style>
		';
		if($object->array_options["options_stores"]){

			// Dropdown filter for countries
			$countryFilter = GETPOST('country') ? GETPOST('country') : "DE";
	
			$country_filter = '<div class="row">';
			foreach ($countries as $country) {
				if($country["country"] == $countryFilter) {
					$country_filter .= '<div class="col-4 clickable" style="background: #e0e0e0;" onclick="reloadPage(\''.$country["country"].'\', \''.$country["country_id"].'\')">';
				} else {
					$country_filter .= '<div class="col-4 clickable" onclick="reloadPage(\''.$country["country"].'\', \''.$country["country_id"].'\')">';
				}
				$country_filter .= $country["country"].'</div>';
			}
			print ' <script>
						function reloadPage(country, country_id) {
							window.location.href = "?id='.$object->id.'&country=" + country + "&country_id=" + country_id;
						}
					</script>
					';
			$country_filter .= '</div>';
			// Display country filter above tables
			$allStores .= $country_filter;
			$allStores .= '<div style="display: flex;">';
	
			$countryIDFilter = GETPOST('country_id') ? GETPOST('country_id') : "5";
			// First Table
			$allStores .= '<div class="div-table-responsive-no-min">';
			$allStores .= '<table class="noborder centpercent">';
			$allStores .= '<tr class="liste_titre">';
			$allStores .= '<th>PLZ</th>';
			$allStores .= '<th class="center">Stores</th>';
			$allStores .= '<th class="center">Im Projekt</th>';
			$allStores .= '<th class="center">Fertig/Offen</th>';
			$allStores .= '</tr>';
			// $sql = "SELECT DISTINCT LEFT(s.zip_code, 1) AS `zipcode`, 
			// 					(SELECT COUNT(*) FROM llx_stores_branch WHERE fk_soc = ".$object->array_options["options_customerbranch"]." AND LEFT(zip_code, 1) = LEFT(s.zip_code, 1) AND s.country_id = ".$countryIDFilter.") AS `zipcode_stores`, 
			// 					(SELECT COUNT(*) FROM llx_stores_branch ss 
			// 					LEFT JOIN llx_projet_extrafields p ON CONCAT(',', p.stores, ',') LIKE CONCAT('%,', ss.rowid, ',%') 
			// 					WHERE p.fk_object = ".$id." AND s.fk_soc = ".$object->array_options["options_customerbranch"]." AND LEFT(zip_code, 1) = LEFT(s.zip_code, 1) AND s.country_id = ".$countryIDFilter.") AS `zipcode_stores_p`,
			// 					(SELECT COUNT(DISTINCT te.fk_store) 
			// 						FROM llx_ticket_extrafields te 
			// 						JOIN llx_ticket t ON te.fk_object = t.rowid 
			// 						JOIN llx_stores_branch sb ON te.fk_store = sb.rowid 
			// 						JOIN llx_projet pr ON t.fk_project = pr.rowid 
			// 					WHERE te.customer = s.fk_soc AND t.fk_project = ".$id." AND LEFT(sb.zip_code, 1) = LEFT(s.zip_code, 1) AND t.fk_statut = 8 AND s.country_id = ".$countryIDFilter.") AS `total_stores_done`,
			// 					(SELECT COUNT(DISTINCT te.fk_store) 
			// 						FROM llx_ticket_extrafields te 
			// 						JOIN llx_ticket t ON te.fk_object = t.rowid 
			// 						JOIN llx_stores_branch sb ON te.fk_store = sb.rowid 
			// 						JOIN llx_projet pr ON t.fk_project = pr.rowid 
			// 					WHERE te.customer = s.fk_soc AND t.fk_project = ".$id." AND LEFT(sb.zip_code, 1) = LEFT(s.zip_code, 1) AND t.fk_statut <> 8 AND s.country_id = ".$countryIDFilter.") AS `total_stores_open`
			// 			FROM llx_stores_branch s
			// 			WHERE s.fk_soc = ".$object->array_options["options_customerbranch"]." AND s.country_id = ".$countryIDFilter." ORDER BY zipcode";
			$sql = "SELECT
						LEFT(s.zip_code, 1) AS zipcode,
						(SELECT COUNT(*) FROM llx_stores_branch ss WHERE ss.fk_soc = ".$object->array_options["options_customerbranch"]." AND ss.country_id = ".$countryIDFilter." AND LEFT(ss.zip_code, 1) = zipcode) as zipcode_stores,
						COUNT(DISTINCT CASE WHEN p.fk_object = ".$id." AND s.fk_soc = ".$object->array_options["options_customerbranch"]." AND s.country_id = ".$countryIDFilter." THEN s.rowid END) AS zipcode_stores_p,
						COUNT(DISTINCT CASE WHEN p.fk_object = ".$id." AND s.fk_soc = ".$object->array_options["options_customerbranch"]." AND s.country_id = ".$countryIDFilter." AND t.fk_statut = 8 THEN s.rowid END) AS total_stores_done,
						COUNT(DISTINCT CASE WHEN p.fk_object = ".$id." AND s.fk_soc = ".$object->array_options["options_customerbranch"]." AND s.country_id = ".$countryIDFilter." AND t.fk_statut <> 8 THEN s.rowid END) AS total_stores_open
					FROM
						llx_stores_branch s
					LEFT JOIN llx_projet_extrafields p ON CONCAT(',', p.stores, ',') LIKE CONCAT('%,', s.rowid, ',%')
					LEFT JOIN llx_ticket_extrafields te ON te.customer = s.fk_soc AND te.fk_store = s.rowid
					LEFT JOIN llx_ticket t ON te.fk_object = t.rowid
					LEFT JOIN llx_projet pr ON t.fk_project = pr.rowid
					WHERE
						s.fk_soc = ".$object->array_options["options_customerbranch"]." AND
						s.country_id = ".$countryIDFilter." AND 
						pr.rowid = ".$id."
					GROUP BY
						LEFT(s.zip_code, 1)
					ORDER BY
						zipcode;";				
			$result = $db->query($sql);
			$stores = new Branch($db);
			if ($result) {
				$num = $db->num_rows($result);
			
				$i = 0;
			
				if ($num > 0) {
			
					while ($i < $num && $objp = $db->fetch_object($result)) {
			
						$stores->zipcode = $objp->zipcode;
						$stores->zipcode_stores = $objp->zipcode_stores;
						$stores->zipcode_stores_p = $objp->zipcode_stores_p;
						$stores->total_stores_done = $objp->total_stores_done;
						$stores->total_stores_open = $objp->total_stores_open;
			
						// To divide into two tables, we can check the iteration count
						// Let's assume half the records are in each table for simplicity
						if ($i < $num / 2) {
							$allStores .= '<tr class="oddeven">';
							// Zipcode
							$allStores .= '<td class="nowrap tdoverflowmax200">';
							$allStores .= $stores->zipcode;
							$allStores .= "</td>\n";
							// Thirdparty Stores in the zipcode
							$allStores .= '<td class="center">';
							$allStores .= $stores->zipcode_stores;
							$allStores .= '</td>';
							// Thirdparty Stores in the zipcode for the current project
							$allStores .= '<td class="center">';
							$allStores .= $stores->zipcode_stores_p;
							$allStores .= '</td>';
							// done/open
							$allStores .= '<td class="center">';
							$allStores .= $stores->total_stores_done.'/'.$stores->total_stores_open;
							$allStores .= '</td>';
							$allStores .= "</tr>\n";
						}
						$i++;
					}
			
					$db->free($result);
			
				} else {
					$allStores .= '<td class="left" colspan="6">';
					$allStores .= 'No Stores';
					$allStores .= '</td>';
				}
				$allStores .= "</table>\n";
				$allStores .= '</div>';
				$allStores .= "<!-- End last thirdparties modified -->\n";
			} else {
				dol_print_error($db);
			}
			
			// Second Table
			$allStores .= '<div class="div-table-responsive-no-min">';
			$allStores .= '<table class="noborder centpercent">';
			$allStores .= '<tr class="liste_titre">';
			$allStores .= '<th>PLZ</th>';
			$allStores .= '<th class="center">Stores</th>';
			$allStores .= '<th class="center">Im Projekt</th>';
			$allStores .= '<th class="center">Fertig/Offen</th>';
			$allStores .= '</tr>';
			
			$result = $db->query($sql); // Execute the query again to reset the result pointer
			if ($result) {
				$num = $db->num_rows($result);
			
				$i = 0;
			
				if ($num > 0) {
			
					while ($i < $num && $objp = $db->fetch_object($result)) {
			
						$stores->zipcode = $objp->zipcode;
						$stores->zipcode_stores = $objp->zipcode_stores;
						$stores->zipcode_stores_p = $objp->zipcode_stores_p;
						$stores->total_stores_done = $objp->total_stores_done;
						$stores->total_stores_open = $objp->total_stores_open;
			
						// Displaying the second half of the records in the second table
						if ($i >= $num / 2) {
							$allStores .= '<tr class="oddeven">';
							// Zipcode
							$allStores .= '<td class="nowrap tdoverflowmax200">';
							$allStores .= $stores->zipcode;
							$allStores .= "</td>\n";
							// Thirdparty Stores in the zipcode
							$allStores .= '<td class="center">';
							$allStores .= $stores->zipcode_stores;
							$allStores .= '</td>';
							// Thirdparty Stores in the zipcode for the current project
							$allStores .= '<td class="center">';
							$allStores .= $stores->zipcode_stores_p;
							$allStores .= '</td>';
							// done/open
							$allStores .= '<td class="center">';
							$allStores .= $stores->total_stores_done.'/'.$stores->total_stores_open;
							$allStores .= '</td>';
							$allStores .= "</tr>\n";
						}
						$i++;
					}
			
					$db->free($result);
			
				} else {
					$allStores .= '<td class="left" colspan="6">';
					$allStores .= 'No Stores';
					$allStores .= '</td>';
				}
				$allStores .= "</table>\n";
				$allStores .= '</div>';
				$allStores .= "<!-- End last thirdparties modified -->\n";
			} else {
				dol_print_error($db);
			}
			print $allStores;

		}
		print '</div>';
		print '</div>';
		print '</div>';

		print '<div class="clearboth"></div>';
	}

	print dol_get_fiche_end();

	if ($action == 'edit' && $userWrite > 0) {
		print $form->buttonsSaveCancel();
	}

	print '</form>';

	// Set also dependencies between use taks and bill time
	print '<script type="text/javascript">
        jQuery(document).ready(function() {
        	jQuery("#usage_task").change(function() {
        		console.log("We click on usage task "+jQuery("#usage_task").is(":checked"));
                if (! jQuery("#usage_task").is(":checked")) {
                    jQuery("#usage_bill_time").prop("checked", false);
                }
        	});

        	jQuery("#usage_bill_time").change(function() {
        		console.log("We click on usage to bill time");
                if (jQuery("#usage_bill_time").is(":checked")) {
                    jQuery("#usage_task").prop("checked", true);
                }
        	});

			jQuery("#projectstart").change(function() {
				console.log("We modify the start date");
				jQuery("#divreportdate").show();
			});
        });
        </script>';

	// Change probability from status
	if (!empty($conf->use_javascript_ajax) && getDolGlobalString('PROJECT_USE_OPPORTUNITIES')) {
		// Default value to close or not when we set opp to 'WON'.
		$defaultcheckedwhenoppclose = 1;
		if (!getDolGlobalString('PROJECT_HIDE_TASKS')) {
			$defaultcheckedwhenoppclose = 0;
		}

		print '<!-- Javascript to manage opportunity status change -->';
		print '<script type="text/javascript">
            jQuery(document).ready(function() {
            	function change_percent()
            	{
                    var element = jQuery("#opp_status option:selected");
                    var defaultpercent = element.attr("defaultpercent");
                    var defaultcloseproject = '.((int) $defaultcheckedwhenoppclose).';
                    var elemcode = element.attr("elemcode");
                    var oldpercent = \''.dol_escape_js($object->opp_percent).'\';

                    console.log("We select "+elemcode);

                    /* Define if checkbox to close is checked or not */
                    var closeproject = 0;
                    if (elemcode == \'LOST\') closeproject = 1;
                    if (elemcode == \'WON\') closeproject = defaultcloseproject;
                    if (closeproject) jQuery("#inputcloseproject").prop("checked", true);
                    else jQuery("#inputcloseproject").prop("checked", false);

                    /* Make the close project checkbox visible or not */
                    console.log("closeproject="+closeproject);
                    if (elemcode == \'WON\' || elemcode == \'LOST\')
                    {
                        jQuery("#divtocloseproject").show();
                    }
                    else
                    {
                        jQuery("#divtocloseproject").hide();
                    }

                    /* Change percent with default percent (defaultpercent) if new status (defaultpercent) is higher than current (jQuery("#opp_percent").val()) */
                    if (oldpercent != \'\' && (parseFloat(defaultpercent) < parseFloat(oldpercent)))
                    {
	                    console.log("oldpercent="+oldpercent+" defaultpercent="+defaultpercent+" def < old");
                        if (jQuery("#opp_percent").val() != \'\' && oldpercent != \'\') {
							jQuery("#oldopppercent").text(\' - '.dol_escape_js($langs->transnoentities("PreviousValue")).': \'+price2numjs(oldpercent)+\' %\');
						}

						if (parseFloat(oldpercent) != 100 && elemcode != \'LOST\') { jQuery("#opp_percent").val(oldpercent); }
                        else { jQuery("#opp_percent").val(price2numjs(defaultpercent)); }
                    } else {
	                    console.log("oldpercent="+oldpercent+" defaultpercent="+defaultpercent);
                    	if (jQuery("#opp_percent").val() == \'\' || (parseFloat(jQuery("#opp_percent").val()) < parseFloat(defaultpercent))) {
                        	if (jQuery("#opp_percent").val() != \'\' && oldpercent != \'\') {
								jQuery("#oldopppercent").text(\' - '.dol_escape_js($langs->transnoentities("PreviousValue")).': \'+price2numjs(oldpercent)+\' %\');
							}
                        	jQuery("#opp_percent").val(price2numjs(defaultpercent));
                    	}
                    }
            	}

            	jQuery("#opp_status").change(function() {
            		change_percent();
            	});
        });
        </script>';
	}


	/*
	 * Actions Buttons
	 */

	print '<div class="tabsAction">';
	$parameters = array();
	$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been
	// modified by hook
	if (empty($reshook)) {
		if ($action != "edit" && $action != 'presend' && $action != 'reportsMail') {
			// Create event
			/*if (isModEnabled('agenda') && !empty($conf->global->MAIN_ADD_EVENT_ON_ELEMENT_CARD)) 				// Add hidden condition because this is not a
				// "workflow" action so should appears somewhere else on
				// page.
			{
				print '<a class="butAction" href="'.DOL_URL_ROOT.'/comm/action/card.php?action=create&amp;origin=' . $object->element . '&amp;originid=' . $object->id . '&amp;socid=' . $object->socid . '&amp;projectid=' . $object->id . '">' . $langs->trans("AddAction") . '</a>';
			}*/

			// CSV
			print dolGetButtonAction('', $langs->trans('csv'), 'default', $_SERVER["PHP_SELF"].'?action=csv&token='.newToken().'&id='.$object->id.'&mode=init#formmailbeforetitle', '');

			// Send
			if (empty($user->socid)) {
				if ($object->statut != Project::STATUS_CLOSED) {
					print dolGetButtonAction('', $langs->trans('SendMail'), 'default', $_SERVER["PHP_SELF"].'?action=presend&token='.newToken().'&id='.$object->id.'&mode=init#formmailbeforetitle', '');
				}
			}

			// Reports Mail
			$customer = new Societe($db);
			$customer->fetch($object->array_options["options_customerbranch"]);
			if($customer->name_alias == "ROS"){
				print dolGetButtonAction('', $langs->trans('Reports Mail'), 'default', $_SERVER['PHP_SELF'].'?action=presend&token='.newToken().'&id='.$object->id.'&type=reportsMail&mode=init#formmailbeforetitle', '');
			}
			

			// Accounting Report
			/*
			$accouting_module_activated = isModEnabled('comptabilite') || isModEnabled('accounting');
			if ($accouting_module_activated && $object->statut != Project::STATUS_DRAFT) {
				$start = dol_getdate((int) $object->date_start);
				$end = dol_getdate((int) $object->date_end);
				$url = DOL_URL_ROOT.'/compta/accounting-files.php?projectid='.$object->id;
				if (!empty($object->date_start)) $url .= '&amp;date_startday='.$start['mday'].'&amp;date_startmonth='.$start['mon'].'&amp;date_startyear='.$start['year'];
				if (!empty($object->date_end)) $url .= '&amp;date_stopday='.$end['mday'].'&amp;date_stopmonth='.$end['mon'].'&amp;date_stopyear='.$end['year'];
				print dolGetButtonAction('', $langs->trans('ExportAccountingReportButtonLabel'), 'default', $url, '');
			}
			*/

			// Back to draft
			if (!getDolGlobalString('MAIN_DISABLEDRAFTSTATUS') && !getDolGlobalString('MAIN_DISABLEDRAFTSTATUS_PROJECT')) {
				if ($object->statut != Project::STATUS_DRAFT && $user->hasRight('projet', 'creer')) {
					if ($userWrite > 0) {
						print dolGetButtonAction('', $langs->trans('SetToDraft'), 'default', $_SERVER["PHP_SELF"].'?action=confirm_setdraft&amp;confirm=yes&amp;token='.newToken().'&amp;id='.$object->id, '');
					} else {
						print dolGetButtonAction($langs->trans('NotOwnerOfProject'), $langs->trans('SetToDraft'), 'default', $_SERVER['PHP_SELF']. '#', '', false);
					}
				}
			}

			// Modify
			if ($object->statut != Project::STATUS_CLOSED && $user->hasRight('projet', 'creer')) {
				if ($userWrite > 0) {
					print dolGetButtonAction('', $langs->trans('Modify'), 'default', $_SERVER["PHP_SELF"].'?action=edit&token='.newToken().'&id='.$object->id, '');
				} else {
					print dolGetButtonAction($langs->trans('NotOwnerOfProject'), $langs->trans('Modify'), 'default', $_SERVER['PHP_SELF']. '#', '', false);
				}
			}

			// Validate
			if ($object->statut == Project::STATUS_DRAFT && $user->hasRight('projet', 'creer')) {
				if ($userWrite > 0) {
					print dolGetButtonAction('', $langs->trans('Validate'), 'default', $_SERVER["PHP_SELF"].'?action=validate&amp;token='.newToken().'&amp;id='.$object->id, '');
				} else {
					print dolGetButtonAction($langs->trans('NotOwnerOfProject'), $langs->trans('Validate'), 'default', $_SERVER['PHP_SELF']. '#', '', false);
				}
			}

			// Close
			if ($object->statut == Project::STATUS_VALIDATED && $user->hasRight('projet', 'creer')) {
				if ($userWrite > 0) {
					print dolGetButtonAction('', $langs->trans('Close'), 'default', $_SERVER["PHP_SELF"].'?action=close&amp;token='.newToken().'&amp;id='.$object->id, '');
				} else {
					print dolGetButtonAction($langs->trans('NotOwnerOfProject'), $langs->trans('Close'), 'default', $_SERVER['PHP_SELF']. '#', '', false);
				}
			}

			// Reopen
			if ($object->statut == Project::STATUS_CLOSED && $user->hasRight('projet', 'creer')) {
				if ($userWrite > 0) {
					print dolGetButtonAction('', $langs->trans('ReOpen'), 'default', $_SERVER["PHP_SELF"].'?action=reopen&amp;token='.newToken().'&amp;id='.$object->id, '');
				} else {
					print dolGetButtonAction($langs->trans('NotOwnerOfProject'), $langs->trans('ReOpen'), 'default', $_SERVER['PHP_SELF']. '#', '', false);
				}
			}

			// Buttons Create
			if (!getDolGlobalString('PROJECT_HIDE_CREATE_OBJECT_BUTTON')) {
				$arrayforbutaction = array(
					10 => array('lang'=>'propal', 'enabled'=>isModEnabled("propal"), 'perm'=>$user->hasRight('propal', 'creer'), 'label' => 'AddProp', 'url'=>'/comm/propal/card.php?action=create&amp;projectid='.$object->id.'&amp;socid='.$object->socid),
					20 => array('lang'=>'orders', 'enabled'=>isModEnabled("commande"), 'perm'=>$user->hasRight('commande', 'creer'), 'label' => 'CreateOrder', 'url'=>'/commande/card.php?action=create&amp;projectid='.$object->id.'&amp;socid='.$object->socid),
					30 => array('lang'=>'bills', 'enabled'=>isModEnabled("facture"), 'perm'=>$user->hasRight('facture', 'creer'), 'label' => 'CreateBill', 'url'=>'/compta/facture/card.php?action=create&amp;projectid='.$object->id.'&amp;socid='.$object->socid),
					40 => array('lang'=>'supplier_proposal', 'enabled'=>isModEnabled("supplier_proposal"), 'perm'=>$user->hasRight('supplier_proposal', 'creer'), 'label' => 'AddSupplierProposal', 'url'=>'/supplier_proposal/card.php?action=create&amp;projectid='.$object->id.'&amp;socid='.$object->socid),
					50 => array('lang'=>'suppliers', 'enabled'=>isModEnabled("supplier_order"), 'perm'=>$user->hasRight('fournisseur', 'commande', 'creer'), 'label' => 'AddSupplierOrder', 'url'=>'/fourn/commande/card.php?action=create&amp;projectid='.$object->id.'&amp;socid='.$object->socid),
					60 => array('lang'=>'suppliers', 'enabled'=>isModEnabled("supplier_invoice"), 'perm'=>$user->hasRight('fournisseur', 'facture', 'creer'), 'label' => 'AddSupplierInvoice', 'url'=>'/fourn/facture/card.php?action=create&amp;projectid='.$object->id.'&amp;socid='.$object->socid),
					70 => array('lang'=>'interventions', 'enabled'=>isModEnabled("ficheinter"), 'perm'=>$user->hasRight('fichinter', 'creer'), 'label' => 'AddIntervention', 'url'=>'/fichinter/card.php?action=create&amp;projectid='.$object->id.'&amp;socid='.$object->socid),
					80 => array('lang'=>'contracts', 'enabled'=>isModEnabled("contrat"), 'perm'=>$user->hasRight('contrat', 'creer'), 'label' => 'AddContract', 'url'=>'/contrat/card.php?action=create&amp;projectid='.$object->id.'&amp;socid='.$object->socid),
					90 => array('lang'=>'trips', 'enabled'=>isModEnabled("expensereport"), 'perm'=>$user->hasRight('expensereport', 'creer'), 'label' => 'AddTrip', 'url'=>'/expensereport/card.php?action=create&amp;projectid='.$object->id.'&amp;socid='.$object->socid),
				   100 => array('lang'=>'donations', 'enabled'=>isModEnabled("don"), 'perm'=>$user->hasRight('don', 'creer'), 'label' => 'AddDonation', 'url'=>'/don/card.php?action=create&amp;projectid='.$object->id.'&amp;socid='.$object->socid),
				);

				$params = array('backtopage' => $_SERVER["PHP_SELF"].'?id='.$object->id);

				print dolGetButtonAction('', $langs->trans("Create"), 'default', $arrayforbutaction, '', 1, $params);
			}

			// Clone
			if ($user->hasRight('projet', 'creer')) {
				if ($userWrite > 0) {
					print dolGetButtonAction('', $langs->trans('ToClone'), 'default', $_SERVER["PHP_SELF"].'?action=clone&amp;token='.newToken().'&amp;id='.$object->id, '');
				} else {
					print dolGetButtonAction($langs->trans('NotOwnerOfProject'), $langs->trans('ToClone'), 'default', $_SERVER['PHP_SELF']. '#', '', false);
				}
			}

			// Delete
			if ($user->hasRight('projet', 'supprimer') || ($object->statut == Project::STATUS_DRAFT && $user->hasRight('projet', 'creer'))) {
				if ($userDelete > 0 || ($object->statut == Project::STATUS_DRAFT && $user->hasRight('projet', 'creer'))) {
					print dolGetButtonAction('', $langs->trans('Delete'), 'delete', $_SERVER["PHP_SELF"].'?action=delete&token='.newToken().'&id='.$object->id, '');
				} else {
					print dolGetButtonAction($langs->trans('NotOwnerOfProject'), $langs->trans('Delete'), 'default', $_SERVER['PHP_SELF']. '#', '', false);
				}
			}
		}
	}

	print "</div>";

	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	if ($action == 'reportsMail') {

		// $sql = "SELECT f.rowid, f.fk_ticket, f.parameters, t.fk_statut, t.ref FROM llx_tec_forms f";
		// $sql .= " LEFT JOIN llx_ticket t on t.rowid = f.fk_ticket";
		// $sql .= " WHERE t.fk_project = ".$object->id;
		// $sql .= " ORDER BY t.fk_statut DESC";
		// $results = $db->query($sql)->fetch_all();
		
		// print '<table id="questions-table" class="noborder centpercent">';
		// 	foreach($results as $result) {
		// 		$parameters = json_decode(base64_decode($result[2]));
		// 		$p2tests = "-";
		// 		$p1tests = "-";
		// 		$p1testsRollback = "-";
		// 		$p2testsRollback = "-";
		// 		$table2Checked = "";
		// 		$table1 = "";
		// 		$otherNote = "-";
		// 		foreach ($parameters as $item) {
		// 			if ($item->name === 'p2tests') {
		// 				$p2tests = $item->value;
		// 			}
		// 			if($item->name === 'p1tests'){
		// 				$p1tests = $item->value;
		// 			}
		// 			if($item->name === 'p1testsRollback'){
		// 				$p1testsRollback = $item->value;
		// 			}
		// 			if($item->name === 'p2testsRollback'){
		// 				$p2testsRollback = $item->value;
		// 			}
		// 			if ($item->name === 'table2') {
		// 				$table2Checked = $item->value;
		// 			}
		// 			if($item->name === 'table1'){
		// 				$table1 = $item->value;
		// 			}
		// 			if ($item->name === 'note-other') {
		// 				$otherNote = $item->value;
		// 				break;
		// 			}
		// 		}
		// 		print '<tr class="oddeven">';
		// 			print '<td>';
		// 				print $result[4];
		// 			print '</td>';
		// 			print '<td>';
		// 				print $result[3] == 8 ? 'Finished and Closed' : 'Not finished';
		// 			print '</td>';
		// 			print '<td>';
		// 				print 'P1 Fehler (Umbau): '.$p1tests.'<br>';
		// 				print 'P1 Fehler (Rollback): '.$p1testsRollback.'<br>';
		// 				print 'P2 Fehler (Umbau): '.$p2tests.'<br>';
		// 				print 'P2 Fehler (Rollback): '.$p2testsRollback.'<br>';
		// 				print 'Sonstiges: '.$otherNote;
		// 			print '</td>';
		// 		print '</tr>';
		// 	}
		// print '</table>';

	}

	if ($action != 'presend' && $action != 'reportsMail' && $action != 'csv') {
		print '<div class="fichecenter"><div class="fichehalfleft">';
		print '<a name="builddoc"></a>'; // ancre

		if (getDolGlobalInt('PROJECT_ENABLE_SUB_PROJECT')) {
			/*
			 * Sub-projects (children)
			 */
			$children = $object->getChildren();
			if ($children) {
				print '<table class="centpercent notopnoleftnoright table-fiche-title">';
				print '<tr class="titre"><td class="nobordernopadding valignmiddle col-title">';
				print '<div class="titre inline-block">'.$langs->trans('Sub-projects').'</div>';
				print '</td></tr></table>';

				print '<div class="div-table-responsive-no-min">';
				print '<table class="centpercent noborder'.($morecss ? ' '.$morecss : '').'">';
				print '<tr class="liste_titre">';
				print getTitleFieldOfList('Ref', 0, $_SERVER["PHP_SELF"], '', '', '', '', '', '', '', 1);
				print getTitleFieldOfList('Title', 0, $_SERVER["PHP_SELF"], '', '', '', '', '', '', '', 1);
				print getTitleFieldOfList('Status', 0, $_SERVER["PHP_SELF"], '', '', '', '', '', '', '', 1);
				print '</tr>';
				print "\n";

				$subproject = new Project($db);
				foreach ($children as $child) {
					$subproject->fetch($child->rowid);
					print '<tr class="oddeven">';
					print '<td class="nowraponall">'.$subproject->getNomUrl(1, 'project').'</td>';
					print '<td class="nowraponall tdoverflowmax125">'.$child->title.'</td>';
					print '<td class="nowraponall">'.$subproject->getLibStatut(5).'</td>';
					print '</tr>';
				}

				print '</table>';
				print '</div>';
			}
		}

		/*
		 * Generated documents
		 */
		$filename = dol_sanitizeFileName($object->ref);
		$filedir = $conf->project->multidir_output[$object->entity]."/".dol_sanitizeFileName($object->ref);
		$urlsource = $_SERVER["PHP_SELF"]."?id=".$object->id;
		$genallowed = ($user->hasRight('projet', 'lire') && $userAccess > 0);
		$delallowed = ($user->hasRight('projet', 'creer') && $userWrite > 0);

		print $formfile->showdocuments('project', $filename, $filedir, $urlsource, $genallowed, $delallowed, $object->model_pdf, 1, 0, 0, 0, 0, '', '', '', '', '', $object);

		print '</div><div class="fichehalfright">';

		$MAXEVENT = 10;

		$morehtmlcenter = '<div class="nowraponall">';
		$morehtmlcenter .= dolGetButtonTitle($langs->trans('FullConversation'), '', 'fa fa-comments imgforviewmode', DOL_URL_ROOT.'/projet/messaging.php?id='.$object->id);
		$morehtmlcenter .= dolGetButtonTitle($langs->trans('FullList'), '', 'fa fa-bars imgforviewmode', DOL_URL_ROOT.'/projet/agenda.php?id='.$object->id);
		$morehtmlcenter .= '</div>';

		// List of actions on element
		include_once DOL_DOCUMENT_ROOT.'/core/class/html.formactions.class.php';
		$formactions = new FormActions($db);
		$somethingshown = $formactions->showactions($object, 'project', 0, 1, '', $MAXEVENT, '', $morehtmlcenter);

		print '</div></div>';
	}
	// $_SESSION["dateString"] = "";
	// $_SESSION["startDate"] = "";
	// $_SESSION["endDate"] = "";
	// $_SESSION["techniciansList"] = "";
	// $_SESSION["techniciansString"] = "";
	if($action == 'csv'){
		session_start();
		$sort = GETPOST('sort');
		$currentDate = new DateTime();
		// var_dump($currentDate->format('Y-m-d'));
		$currentDay = $currentDate->format('Y-m-d');
		$currentMonth = $currentDate->format('m');
		$currentYear = $currentDate->format('Y');
		$startyear = $currentYear;
		$dateString = 'From '. $startyear . '-'.$currentMonth.'-01 To '. $currentDay;
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["date-from"]) && isset($_POST["date-to"]) && $_POST["date-from"] != "" && $_POST["date-to"] != "") {
			$datefrom = $_POST["date-from"];
			$dateto = $_POST["date-to"];
			$dateString = 'From '.$_POST["date-from"].' to '.$_POST["date-to"];
			$_SESSION["dateString"] = $dateString;
			$_SESSION["startDate"] = $_POST["date-from"];
			$_SESSION["endDate"] = $_POST["date-to"];
		}
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["fk_user"])) {
			$technicianIds = $_POST["fk_user"];
			
			$filteredIds = array_filter($technicianIds, function($id) {
				return !empty($id);
			});
			$technicianIdString = implode(",", $filteredIds);
			$_SESSION["techniciansList"] = $filteredIds;
			$_SESSION["techniciansString"] = $technicianIdString;
		}
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["fk_businesspartner"])) {
			$businesspartnerId = $_POST["fk_businesspartner"];
			
			$_SESSION["businesspartnerId"] = $businesspartnerId;
		}
		$stringtoshow = '
			<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery("#idsubimgDOLUSERCOOKIE_ticket_by_status").click(function() {
						jQuery("#idfilterDOLUSERCOOKIE_ticket_by_status").toggle();
					});
				});
					
				function exportToCSV() {
					const tables = document.getElementById("summary-table").querySelectorAll("table:not(#times-table)");
					const csvContent = [];

					tables.forEach(table => {
						const rows = table.querySelectorAll("tr");
						rows.forEach(row => {
						const csvRow = [];

						// Include both th and td elements
						const cells = row.querySelectorAll("th, td");
						cells.forEach(cell => {
							let cellValue = cell.textContent.trim();

							if (cellValue !== "") {
							let elm = [];
							cell.querySelectorAll("input").forEach(inputElement => {
								if (inputElement) {
									switch (inputElement.type) {
										case "number":
											elm.push(inputElement.value);
											break;
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
											// Handle time input type (logic might need adjustment)
											cellValue = "HH:MM"; // Placeholder for time format
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
								}
							});
								if (elm.length > 0) {
									cellValue = elm.join(":");
								}
							} else {
								cell.querySelectorAll("input").forEach(inputElement => {
									if (inputElement) {
										// Handle input elements with empty text content
										switch (inputElement.type) {
											case "checkbox":
											cellValue = ""; // Set empty string for unchecked checkboxes
											break;
											default:
											// Handle other input types with empty content (optional)
											break;
										}
									}
								});
							}

							csvRow.push(cellValue);
						});

						csvContent.push(csvRow.join(","));
						});
						csvContent.push(""); // Add a blank line between tables
					});

					const csvData = csvContent.join("\n");
					const blob = new Blob([csvData], { type: "text/csv" });
					const url = URL.createObjectURL(blob);

					const a = document.createElement("a");
					a.href = url;
					// Set a larger default cell width in Excel (adjust as needed)
					a.download = "report.csv"; // Add ".csv" extension for proper file type
					a.click();
					URL.revokeObjectURL(url);
				}
			</script>';
		$events[] = array('method' => 'getContacts', 'url' => dol_buildpath('/projet/ajax/contacts.php', 1), 'htmlname' => 'fk_user', 'params' => array('add-customer-contact' => 'disabled'));
		$customer='fk_businesspartner';
		print '<script type="text/javascript">
				$(document).ready(function () {

					jQuery("#'.$customer.'").change(function () {
						var obj = '.json_encode($events).';
						$.each(obj, function(key,values) {
							if (values.method.length) {
								runJsCodeForEvent'.$customer.'(values);
							}
						});
					});
					function runJsCodeForEvent'.$customer.'(obj) {
						console.log("Run runJsCodeForEvent'.$customer.'");
						var id = $("#'.$customer.'").val();
						var method = obj.method;
						var url = obj.url;
						var htmlname = obj.htmlname;
						var showempty = obj.showempty;
						$.getJSON(url,
								{
									action: method,
									id: id,
									htmlname: htmlname,
									showempty: showempty
								},
								function(response) {
									$.each(obj.params, function(key,action) {
										if (key.length) {
											var num = response.num;
											if (num > 0) {
												$("#" + key).removeAttr(action);
											} else {
												$("#" + key).attr(action, action);
											}
										}
									});
									$("select#" + htmlname).html(response.value);
									if (response.num) {
										var selecthtml_str = response.value;
										var selecthtml_dom=$.parseHTML(selecthtml_str);
										if (typeof(selecthtml_dom[0][0]) !== \'undefined\') {
											$("#inputautocomplete"+htmlname).val(selecthtml_dom[0][0].innerHTML);
										}
									} else {
										$("#inputautocomplete"+htmlname).val("");
									}
									$("select#" + htmlname).change();	/* Trigger event change */
								}
						);
					}
				});
			</script>';
		$stringtoshow .= '<div class="center hideobject" id="idfilterDOLUSERCOOKIE_ticket_by_status">'; // hideobject is to start hidden
			$stringtoshow .= '<form class="flat formboxfilter" method="POST" action="'.$_SERVER["PHP_SELF"].'?action=csv&token='.newToken().'&id='.$object->id.'&mode=init#formmailbeforetitle">';
				$stringtoshow .= '<input type="hidden" name="token" value="'.newToken().'">';
				$stringtoshow .= '<input type="hidden" name="action" value="refresh">';
				$stringtoshow .= '<input type="hidden" name="DOL_AUTOSET_COOKIE" value="DOLUSERCOOKIE_ticket_by_status:year,shownb,showtot">';
				$stringtoshow .= $langs->trans("Geschäftspartner").": ".$form->select_company('', 'fk_businesspartner', '', 1, 1, '', $events, 0, 'minwidth400');
				$stringtoshow .= $langs->trans("Techniker").": ".$form->selectcontactsListing("", $_SESSION["techniciansList"], 'fk_user', 3, '', '', 0, 'minwidth200', '', '', '', '', '', '', true, 0);
				$stringtoshow .= $langs->trans("von").' <input class="flat" size="4" type="date" name="date-from" value="'.$_SESSION["startDate"].'">';
				$stringtoshow .= $langs->trans("bis").' <input class="flat" size="4" type="date" name="date-to" value="'.$_SESSION["endDate"].'">';
				$stringtoshow .= '<input type="image" alt="'.$langs->trans("Refresh").'" src="'.img_picto($langs->trans("Refresh"), 'refresh.png', '', '', 1).'">';
			$stringtoshow .= '</form>';
		$stringtoshow .= '</div>';

		$ticketObject = new Ticket($db);
		$technicianObject = new User($db);
		$storeObject = new Branch($db);
		$sql = 'SELECT t.rowid, t.ref, t.fk_user_assign, t.fk_statut, f.parameters, te.dateofuse, te.fk_store, s.b_number, u.firstname
				FROM llx_projet p
					LEFT JOIN llx_ticket t on t.fk_project = p.rowid
					LEFT JOIN llx_ticket_extrafields te on te.fk_object = t.rowid
					LEFT JOIN llx_tec_forms f on f.fk_ticket = t.rowid
					LEFT JOIN llx_stores_branch s on te.fk_store = s.rowid
					LEFT JOIN llx_user u on t.fk_user_assign = u.rowid
				WHERE p.rowid = '.$object->id;
		$sql .= ' AND t.fk_user_assign != "" ';
		$sql .= ' AND t.fk_statut = 8 ';
		if(isset($_SESSION["businesspartnerId"]) && $_SESSION["businesspartnerId"] != -1){
			$sql .= " AND u.fk_soc = ".$_SESSION["businesspartnerId"];
		}
		if(isset($_SESSION["startDate"]) && isset($_SESSION["endDate"])){
			$sql .= " AND CAST(te.dateofuse AS DATE) BETWEEN CAST('".$_SESSION["startDate"]."' AS DATE) AND CAST('".$_SESSION["endDate"]."' AS DATE)";
		}
		if(isset($_SESSION["techniciansString"])){
			$sql .= " AND t.fk_user_assign IN (".$_SESSION["techniciansString"].")";
		}
		if(isset($sort) && $sort == "store_asc"){
			$sql .= " ORDER BY s.b_number ASC";
		}
		if(isset($sort) && $sort == "store_desc"){
			$sql .= " ORDER BY s.b_number DESC";
		}
		if(isset($sort) && $sort == "installation_asc"){
			$sql .= " ORDER BY te.dateofuse ASC";
		}
		if(isset($sort) && $sort == "installation_desc"){
			$sql .= " ORDER BY te.dateofuse DESC";
		}
		if(isset($sort) && $sort == "technician_asc"){
			$sql .= " ORDER BY u.firstname ASC";
		}
		if(isset($sort) && $sort == "technician_desc"){
			$sql .= " ORDER BY u.firstname DESC";
		}
		// var_dump($sql);
		$result = $db->query($sql);
		print '<div class="datefilter">';
			print '<div class="div-table-responsive-no-min">';
				print '<table class="noborder nohover centpercent">'."\n";
					print '<tr class="liste_titre"><th>'.$langs->trans("Datum Filter").'</th><th>Datum: '.$dateString.''.img_picto('', 'filter.png', 'id="idsubimgDOLUSERCOOKIE_ticket_by_status" class="linkobject"').'</th><th><input class="butAction" type="submit" value="Download" id="csv" onclick="exportToCSV()"></th></tr>';
					print '<tr><td  colspan="4" class="center">';
					print $stringtoshow;
				print '</table>';
			print '</div>';
		print '</div>';
		$storeSort = "store_asc";
		$technicianSort = "technician_asc";
		$installationSort = "installation_asc";
		if($sort == "store_asc"){
			$storeSort = "store_desc";
		}
		if($sort == "store_desc"){
			$storeSort = "store_asc";
		}
		if($sort == "technician_asc"){
			$technicianSort = "technician_desc";
		}
		if($sort == "technician_desc"){
			$technicianSort = "technician_asc";
		}
		if($sort == "installation_asc"){
			$installationSort = "installation_desc";
		}
		if($sort == "installation_desc"){
			$installationSort = "installation_asc";
		}
		print '<div class="row summary-table" id="summary-table">';
			print '<table class="noborder centpercent">';
				print '<tbody>';
					print '<tr class="liste_titre">';
						print '<th>';
							print 'Ticket-Nummer';
						print '</th>';
						print '<th>';
							print '<a class="reposition" href="'.$_SERVER["PHP_SELF"].'?action=csv&token='.newToken().'&id='.$object->id.'&sort='.$storeSort.'&mode=init#formmailbeforetitle">';
								print 'Filialnummer';
							print '</a>';
						print '</th>';
						print '<th>';
							print '<a class="reposition" href="'.$_SERVER["PHP_SELF"].'?action=csv&token='.newToken().'&id='.$object->id.'&sort='.$installationSort.'&mode=init#formmailbeforetitle">';
								print 'Installationsdatum';
							print '</a>';
						print '</th>';
						print '<th>';
							print '<a class="reposition" href="'.$_SERVER["PHP_SELF"].'?action=csv&token='.newToken().'&id='.$object->id.'&sort='.$technicianSort.'&mode=init#formmailbeforetitle">';
								print 'Techniker Name';
							print '</a>';
						print '</th>';
						print '<th>';
							print 'Arbeitsbeginn';
						print '</th>';
						print '<th>';
							print 'Arbeitsende';
						print '</th>';
					print '</tr>';
					if ($result) {
						$num = $db->num_rows($result);
						if($num > 0){
							for ($i = 0; $i < $num; $i++) {
								$objp = $db->fetch_object($result);
								$ticketObject->id = $objp->rowid;
								$ticketObject->fetch($ticketObject->id);
								$ticketObject->status = $objp->fk_statut;
								$ticketObject->parameters = $objp->parameters;
								$ticketObject->fk_store = $objp->fk_store;
								$ticketObject->fk_user = $objp->fk_user_assign;
								$ticketObject->dateofuse = $objp->dateofuse;
								$storeObject->fetch($ticketObject->fk_store);
								$technicianObject->fetch($ticketObject->fk_user);
								$parameters = json_decode(base64_decode($ticketObject->parameters));
								$installationDate = new DateTime($ticketObject->dateofuse);
								$storeNumber = "";
								$workStart = "";
								$workEnd = "";
								foreach ($parameters as $item) {
									// if ($item->name === 'store-number') {
									// 	$storeNumber = $item->value;
									// }
									if ($item->name === 'work-start') {
										$workStart = $item->value;
									}
									if ($item->name === 'work-end') {
										$workEnd = $item->value;
										break;
									}
								}
								print '<tr class="oddeven">';
									print '<td class="nowrap tdoverflowmax200">';
										print $ticketObject->ref;
									print '</td>';
									print '<td class="nowrap tdoverflowmax200">';
										print $storeObject->b_number;
									print '</td>';
									print '<td class="nowrap tdoverflowmax200">';
										print $ticketObject->dateofuse ? $installationDate->format('d.m.y') : "";
									print '</td>';
									print '<td class="nowrap tdoverflowmax200">';
										print $ticketObject->fk_user ? $technicianObject->firstname." ".$technicianObject->lastname : "";
									print '</td>';
									print '<td class="nowrap tdoverflowmax200">';
										print $workStart;
									print '</td>';
									print '<td class="nowrap tdoverflowmax200">';
										print $workEnd;
									print '</td>';
								print '</tr>';
							}
						} else {
							print '<tr class="oddeven">';
								print '<td class="nowrap tdoverflowmax200" colspan="6">';
									print 'No results found.';
								print '</td>';
							print '</tr>';
						}
						$db->free($result);
					} else {
						dol_print_error($db);
					}
				print '</tbody>';
			print '</table>';
		print '</div>';
	}
	// Presend form
	$modelmail = 'project';
	$defaulttopic = 'SendProjectRef';
	$defaulttopiclang = 'projects';
	$diroutput = $conf->project->multidir_output[$object->entity];
	$autocopy = 'MAIN_MAIL_AUTOCOPY_PROJECT_TO'; // used to know the automatic BCC to add
	$trackid = 'proj'.$object->id;

	include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';

	// Hook to add more things on page
	$parameters = array();
	$reshook = $hookmanager->executeHooks('mainCardTabAddMore', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
} else {
	print $langs->trans("RecordNotFound");
}

// End of page
llxFooter();
$db->close();
