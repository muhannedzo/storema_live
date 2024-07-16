<?php
/* Copyright (C) 2011-2016 Jean-François Ferry    <hello@librethic.io>
 * Copyright (C) 2011      Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2016      Christophe Battarel <christophe@altairis.fr>
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
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 *       \file       htdocs/ticket/contact.php
 *       \ingroup    ticket
 *       \brief      Contacts of tickets
 */

// Load Dolibarr environment
require '../main.inc.php';

require_once DOL_DOCUMENT_ROOT.'/ticket/class/ticket.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/ticket.lib.php';

require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT."/core/lib/company.lib.php";
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
if (isModEnabled('project')) {
	include_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
	include_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
	include_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
}

// Load translation files required by the page
$langs->loadLangs(array('companies', 'ticket'));

// Get parameters
$socid = GETPOST("socid", 'int');
$action = GETPOST("action", 'alpha');
$track_id = GETPOST("track_id", 'alpha');
$id = GETPOST("id", 'int');
$ref = GETPOST('ref', 'alpha');

$type = GETPOST('type', 'alpha');
$source = GETPOST('source', 'alpha');

$ligne = GETPOST('ligne', 'int');
$lineid = GETPOST('lineid', 'int');


// Store current page url
$url_page_current = DOL_URL_ROOT.'/ticket/contact.php';

$object = new Ticket($db);


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


/*
 * Actions
 */

if ($action == 'addcontact' && $user->rights->ticket->write) {
	$result = $object->fetch($id, '', $track_id);

	if ($result > 0 && ($id > 0 || (!empty($track_id)))) {
		$contactid = (GETPOST('userid', 'int') ? GETPOST('userid', 'int') : GETPOST('contactid', 'int'));
		$typeid = (GETPOST('typecontact') ? GETPOST('typecontact') : GETPOST('type'));

		$error = 0;

		$codecontact = dol_getIdFromCode($db, $typeid, 'c_type_contact', 'rowid', 'code');
		if ($codecontact=='SUPPORTTEC') {
			$internal_contacts = $object->listeContact(-1, 'internal', 0, 'SUPPORTTEC');
			foreach ($internal_contacts as $key => $contact) {
				if ($contact['id'] !== $contactid) {
					//print "user à effacer : ".$useroriginassign;
					$result = $object->delete_contact($contact['rowid']);
					if ($result<0) {
						$error ++;
						setEventMessages($object->error, $object->errors, 'errors');
					}
				}
			}
			$ret = $object->assignUser($user, $contactid);
			if ($ret < 0) {
				$error ++;
				setEventMessages($object->error, $object->errors, 'errors');
			}
		}

		if (empty($error)) {
			$result = $object->add_contact($contactid, $typeid, GETPOST("source", 'aZ09'));
		}
	}

	if ($result >= 0) {
		Header("Location: ".$url_page_current."?id=".$object->id);
		exit;
	} else {
		if ($object->error == 'DB_ERROR_RECORD_ALREADY_EXISTS') {
			$langs->load("errors");
			setEventMessages($langs->trans("ErrorThisContactIsAlreadyDefinedAsThisType"), null, 'errors');
		} else {
			setEventMessages($object->error, $object->errors, 'errors');
		}
	}
}

// bascule du statut d'un contact
if ($action == 'swapstatut' && $user->rights->ticket->write) {
	if ($object->fetch($id, '', $track_id)) {
		$result = $object->swapContactStatus($ligne);
	} else {
		dol_print_error($db, $object->error);
	}
}

// Efface un contact
if ($action == 'deletecontact' && $user->rights->ticket->write) {
	if ($object->fetch($id, '', $track_id)) {
		$internal_contacts = $object->listeContact(-1, 'internal', 0, 'SUPPORTTEC');
		foreach ($internal_contacts as $key => $contact) {
			if ($contact['rowid'] == $lineid && $object->fk_user_assign==$contact['id']) {
				$ret = $object->assignUser($user, null);
				if ($ret < 0) {
					$error ++;
					setEventMessages($object->error, $object->errors, 'errors');
				}
			}
		}
		$result = $object->delete_contact($lineid);

		if ($result >= 0) {
			Header("Location: ".$url_page_current."?id=".$object->id);
			exit;
		}
	}
}



/*
 * View
 */

$help_url = 'FR:DocumentationModuleTicket';
llxHeader('', $langs->trans("TicketHistory"), $help_url);

$form = new Form($db);
$formcompany = new FormCompany($db);
$contactstatic = new Contact($db);
$userstatic = new User($db);
$ticket = new Ticket($db);
$societe = new Societe($db);

if ($id > 0 || !empty($track_id) || !empty($ref)) {
	if ($object->fetch($id, $ref, $track_id) > 0) {
		if ($socid > 0) {
			$object->fetch_thirdparty();
			$head = societe_prepare_head($object->thirdparty);
			// print dol_get_fiche_head($head, 'ticket', $langs->trans("ThirdParty"), 0, 'company');
			dol_banner_tab($object->thirdparty, 'socid', '', ($user->socid ? 0 : 1), 'rowid', 'nom');
			print dol_get_fiche_end();
		}

		if (!$user->socid && !empty($conf->global->TICKET_LIMIT_VIEW_ASSIGNED_ONLY)) {
			$object->next_prev_filter = "te.fk_user_assign = '".$user->id."'";
		} elseif ($user->socid > 0) {
			$object->next_prev_filter = "te.fk_soc = '".$user->socid."'";
		}

		$head = ticket_prepare_head($object);

		print dol_get_fiche_head($head, 'ticketHistory', $langs->trans("Ticket"), -1, 'ticket');

		$morehtmlref = '<div class="refidno">';
		$morehtmlref .= $object->subject;
		// Author
		if ($object->fk_user_create > 0) {
			$morehtmlref .= '<br>'.$langs->trans("CreatedBy").' : ';

			$fuser = new User($db);
			$fuser->fetch($object->fk_user_create);
			$morehtmlref .= $fuser->getNomUrl(-1);
		} elseif (!empty($object->email_msgid)) {
			$morehtmlref .= '<br>'.$langs->trans("CreatedBy").' : ';
			$morehtmlref .= img_picto('', 'email', 'class="paddingrightonly"');
			$morehtmlref .= dol_escape_htmltag($object->origin_email).' <small class="hideonsmartphone opacitymedium">('.$form->textwithpicto($langs->trans("CreatedByEmailCollector"), $langs->trans("EmailMsgID").': '.$object->email_msgid).')</small>';
		} elseif (!empty($object->origin_email)) {
			$morehtmlref .= '<br>'.$langs->trans("CreatedBy").' : ';
			$morehtmlref .= img_picto('', 'email', 'class="paddingrightonly"');
			$morehtmlref .= dol_escape_htmltag($object->origin_email).' <small class="hideonsmartphone opacitymedium">('.$langs->trans("CreatedByPublicPortal").')</small>';
		}

		// Thirdparty
		if (isModEnabled("societe")) {
			$morehtmlref .= '<br>';
			$morehtmlref .= img_picto($langs->trans("ThirdParty"), 'company', 'class="pictofixedwidth"');
			if ($action != 'editcustomer' && 0) {
				$morehtmlref .= '<a class="editfielda" href="'.$url_page_current.'?action=editcustomer&token='.newToken().'&track_id='.$object->track_id.'">'.img_edit($langs->transnoentitiesnoconv('SetThirdParty'), 0).'</a> ';
			}
			$morehtmlref .= $form->form_thirdparty($url_page_current.'?track_id='.$object->track_id, $object->socid, $action == 'editcustomer' ? 'editcustomer' : 'none', '', 1, 0, 0, array(), 1);
		}

		// Project
		if (isModEnabled('project')) {
			$langs->load("projects");
			if (0) {
				$morehtmlref .= '<br>';
				$morehtmlref .= img_picto($langs->trans("Project"), 'project', 'class="pictofixedwidth"');
				if ($action != 'classify') {
					$morehtmlref .= '<a class="editfielda" href="'.$_SERVER['PHP_SELF'].'?action=classify&token='.newToken().'&id='.$object->id.'">'.img_edit($langs->transnoentitiesnoconv('SetProject')).'</a> ';
				}
				$morehtmlref .= $form->form_project($_SERVER['PHP_SELF'].'?id='.$object->id, $object->socid, $object->fk_project, ($action == 'classify' ? 'projectid' : 'none'), 0, 0, 0, 1, '', 'maxwidth300');
			} else {
				if (!empty($object->fk_project)) {
					$morehtmlref .= '<br>';
					$proj = new Project($db);
					$proj->fetch($object->fk_project);
					$morehtmlref .= $proj->getNomUrl(1);
					if ($proj->title) {
						$morehtmlref .= '<span class="opacitymedium"> - '.dol_escape_htmltag($proj->title).'</span>';
					}
				}
			}
		}

		$morehtmlref .= '</div>';
        ////////////////////////////////////////// end ticket details
		$linkback = '<a href="'.dol_buildpath('/ticket/list.php', 1).'"><strong>'.$langs->trans("BackToList").'</strong></a> ';

		dol_banner_tab($object, 'ref', $linkback, (!empty($user->socid) ? 0 : 1), 'ref', 'ref', $morehtmlref, '', 0, '', '', 1, '');

		print dol_get_fiche_end();

		print '<hr>';
		print '<br>';
        
        $sql = 'SELECT t.rowid, t.ref, t.fk_soc, t.fk_project, t.subject, t.message, t.fk_statut, t.type_code, t.category_code, t.severity_code, t.datec,';
        $sql .= ' te.parentticket';
        $sql .= ' FROM '.MAIN_DB_PREFIX.'ticket t';
        $sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'ticket_extrafields te';
        $sql .= ' ON t.rowid = te.fk_object';
		if($object->array_options["options_parentticket"]){
			$sql .= ' WHERE t.rowid = '.$object->array_options["options_parentticket"];
			$sql .= ' OR te.parentticket = '.$object->array_options["options_parentticket"];
		} else {
			$sql .= ' WHERE t.rowid = '.$object->id;
			$sql .= ' OR te.parentticket = '.$object->id;
		}
		$resql = $db->query($sql);
		// var_dump($sql);
        if ($resql) {
			print '<div class="div-table-responsive-no-min">';
			print '<table class="noborder centpercent">';
			print '<tr class="liste_titre">';
			print '<th colspan="7"></th>';
			print '</tr>';
		
			$num = $db->num_rows($resql);
		
			if ($num) {
				$i = 0;
				while ($i < $num) {
					$obj = $db->fetch_object($resql);
		
					print '<tr class="oddeven">';
		
					$ticket->id = $obj->rowid;
					$ticket->ref = $obj->ref;
					$ticket->subject = $obj->subject;
					$ticket->message = $obj->message;
					$ticket->datec = $obj->datec;
					$ticket->status = $obj->fk_statut;
					$ticket->parent = $obj->parentticket;
		
					$ticket->socid = $obj->fk_soc;
					$societe->fetch($ticket->socid);
					
					print '<td class="nobordernopadding nowraponall">';
					print $ticket->getNomUrl(1);
					print '</td>';
		
					// Subject
					print '<td class="tdoverflowmax150" title="'.dol_escape_htmltag($obj->ref).'">';
					print $ticket->subject;
					print '</td>';
		
					// Message
					print '<td class="nobordernopadding nowraponall" title="'.dol_escape_htmltag($obj->ref).'">';
					print $ticket->message;
					print '</td>';
		
					// Parent
					print '<td class="nowrap">';
					if (!$ticket->parent) {
						print 'Main Ticket';
					}
					print '</td>';
		
					// Date
					$datem = $db->jdate($obj->datec);
					print '<td class="center" title="'.dol_escape_htmltag($langs->trans("DateModification").': '.dol_print_date($datem, 'dayhour', 'tzuserrel')).'">';
					print dol_print_date($datem, 'day', 'tzuserrel');
					print '</td>';
		
					// Status
					print '<td class="right">'.$ticket->LibStatut($ticket->status, 3).'</td>';
					print '</tr>';
					$i++;
				}
			} else {
				print '<tr><td colspan="4"><span class="opacitymedium">'.$langs->trans("None").'</span></td></tr>';
			}
			print "</table></div>";
		} else {
			dol_print_error($db);
		}

	} else {
		print "ErrorRecordNotFound";
	}
}

// End of page
llxFooter();
$db->close();