<?php
/* Copyright (C) 2023 SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    lib/stores_branch.lib.php
 * \ingroup stores
 * \brief   Library files with common functions for Branch
 */

/**
 * Prepare array of tabs for Branch
 *
 * @param	Branch	$object		Branch
 * @return 	array					Array of tabs
 */
function branchPrepareHead($object)
{
	global $db, $langs, $conf;

	$langs->load("stores@stores");

	$showtabofpagecontact = 0;
	$showtabofpagenote = 0;
	$showtabofpagedocument = 0;
	$showtabofpageagenda = 0;
	$showtabofpagepos = 1;
	$showtabofpagemedia = 1;
	$showtabofpageimages = 1;
	$showtabofpagecontact = 1;
	$showtabofpagehistory = 1;

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/stores/branch_card.php", 1).'?id='.$object->id;
	$head[$h][1] = $langs->trans("Card");
	$head[$h][2] = 'card';
	$h++;

	// if ($showtabofpagecontact) {
	// 	$head[$h][0] = dol_buildpath("/stores/branch_contact.php", 1).'?id='.$object->id.'&socid='.$object->fk_soc;
	// 	$head[$h][1] = $langs->trans("Contacts");
	// 	$head[$h][2] = 'contact';
	// 	$h++;
	// }

	if ($showtabofpagepos) {
		$head[$h][0] = dol_buildpath("/stores/poshardware_list.php", 1).'?id='.$object->id;
		$head[$h][1] = $langs->trans("POSHardware");
		$head[$h][2] = 'pos';
		$h++;
	}

	if ($showtabofpagemedia) {
		$head[$h][0] = dol_buildpath("/stores/mediahardware_list.php", 1).'?id='.$object->id;
		$head[$h][1] = $langs->trans("MediaHardware");
		$head[$h][2] = 'media';
		$h++;
	}

	if ($showtabofpageimages) {
		$head[$h][0] = dol_buildpath("/stores/branch_pos.php", 1).'?id='.$object->id;
		$head[$h][1] = $langs->trans("StoreImages");
		$head[$h][2] = 'images';
		$h++;
	}

	if ($showtabofpagecontact) {
		$head[$h][0] = dol_buildpath("/stores/storecontact_list.php", 1).'?id='.$object->id;
		$head[$h][1] = $langs->trans("Contact");
		$head[$h][2] = 'contact';
		$h++;
	}

	if ($showtabofpagehistory) {
		$head[$h][0] = dol_buildpath("/stores/store_history.php", 1).'?id='.$object->id;
		$head[$h][1] = $langs->trans("History");
		$head[$h][2] = 'history';
		$h++;
	}

	if ($showtabofpagenote) {
		if (isset($object->fields['note_public']) || isset($object->fields['note_private'])) {
			$nbNote = 0;
			if (!empty($object->note_private)) {
				$nbNote++;
			}
			if (!empty($object->note_public)) {
				$nbNote++;
			}
			$head[$h][0] = dol_buildpath('/stores/branch_note.php', 1).'?id='.$object->id;
			$head[$h][1] = $langs->trans('Notes');
			if ($nbNote > 0) {
				$head[$h][1] .= (empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER) ? '<span class="badge marginleftonlyshort">'.$nbNote.'</span>' : '');
			}
			$head[$h][2] = 'note';
			$h++;
		}
	}

	if ($showtabofpagedocument) {
		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
		require_once DOL_DOCUMENT_ROOT.'/core/class/link.class.php';
		$upload_dir = $conf->stores->dir_output."/branch/".dol_sanitizeFileName($object->ref);
		$nbFiles = count(dol_dir_list($upload_dir, 'files', 0, '', '(\.meta|_preview.*\.png)$'));
		$nbLinks = Link::count($db, $object->element, $object->id);
		$head[$h][0] = dol_buildpath("/stores/branch_document.php", 1).'?id='.$object->id;
		$head[$h][1] = $langs->trans('Documents');
		if (($nbFiles + $nbLinks) > 0) {
			$head[$h][1] .= '<span class="badge marginleftonlyshort">'.($nbFiles + $nbLinks).'</span>';
		}
		$head[$h][2] = 'document';
		$h++;
	}

	if ($showtabofpageagenda) {
		$head[$h][0] = dol_buildpath("/stores/branch_agenda.php", 1).'?id='.$object->id;
		$head[$h][1] = $langs->trans("Events");
		$head[$h][2] = 'agenda';
		$h++;
	}

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	// $this->tabs = array(
	// 	'entity:+tabname:pos:@stores:/stores/branch_pos.php?id=__ID__'
	// ); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@stores:/stores/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'branch@stores');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'branch@stores', 'remove');

	return $head;
}

