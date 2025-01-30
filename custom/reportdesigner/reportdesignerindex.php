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
 *	\file       reportdesigner/reportdesignerindex.php
 *	\ingroup    reportdesigner
 *	\brief      Home page of reportdesigner top menu
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
	$i--;
	$j--;
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
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/project/modules_project.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/stores/class/branch.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
print '<!-- Bootstrap 5.3.3 JS Bundle (includes Popper.js) -->';
print '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>';

print '<!-- Bootstrap 5.3.3 CSS -->';
print '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">';

print '<!-- jQuery and jQuery UI -->';
print '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';
print '<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>';
print '<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css">';

print '<!-- Bootstrap Icons (Latest Version) -->';
print '<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">';


dol_include_once('/ticket/class/ticket.class.php');
dol_include_once('/societe/class/societe.class.php');
dol_include_once('/projet/class/project.class.php');
dol_include_once('/stores/compress.php');

// Load translation files required by the page
$langs->loadLangs(array("reportdesigner@reportdesigner"));

$action = GETPOST('action', 'aZ09');

$max = 5;
$now = dol_now();

// Security check - Protection if external user
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0) {
	$action = '';
	$socid = $user->socid;
}

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//if (!isModEnabled('reportdesigner')) {
//	accessforbidden('Module not enabled');
//}
//if (! $user->hasRight('reportdesigner', 'myobject', 'read')) {
//	accessforbidden();
//}
//restrictedArea($user, 'reportdesigner', 0, 'reportdesigner_myobject', 'myobject', '', 'rowid');
//if (empty($user->admin)) {
//	accessforbidden('Must be admin');
//}


/*
 * Actions
 */

// None
$form = new Form($db);
$formfile = new FormFile($db);
$action = GETPOST('action', 'aZ09');
$object = new Branch($db);
$projectid = GETPOST("projectid");
$socid = GETPOST("socid");

/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

llxHeader("", $langs->trans("ReportDesignerArea"), '', '', 0, 0, '', '', '', 'mod-reportdesigner page-index');

print load_fiche_titre($langs->trans("ReportDesignerArea"), '', 'reportdesigner.png@reportdesigner');

print '<div class="fichecenter"><div class="fichethirdleft">';


/* BEGIN MODULEBUILDER DRAFT MYOBJECT
// Draft MyObject
if (isModEnabled('reportdesigner') && $user->hasRight('reportdesigner', 'read')) {
	$langs->load("orders");

	$sql = "SELECT c.rowid, c.ref, c.ref_client, c.total_ht, c.tva as total_tva, c.total_ttc, s.rowid as socid, s.nom as name, s.client, s.canvas";
	$sql.= ", s.code_client";
	$sql.= " FROM ".MAIN_DB_PREFIX."commande as c";
	$sql.= ", ".MAIN_DB_PREFIX."societe as s";
	$sql.= " WHERE c.fk_soc = s.rowid";
	$sql.= " AND c.fk_statut = 0";
	$sql.= " AND c.entity IN (".getEntity('commande').")";
	if ($socid)	$sql.= " AND c.fk_soc = ".((int) $socid);

	$resql = $db->query($sql);
	if ($resql)
	{
		$total = 0;
		$num = $db->num_rows($resql);

		print '<table class="noborder centpercent">';
		print '<tr class="liste_titre">';
		print '<th colspan="3">'.$langs->trans("DraftMyObjects").($num?'<span class="badge marginleftonlyshort">'.$num.'</span>':'').'</th></tr>';

		$var = true;
		if ($num > 0)
		{
			$i = 0;
			while ($i < $num)
			{

				$obj = $db->fetch_object($resql);
				print '<tr class="oddeven"><td class="nowrap">';

				$myobjectstatic->id=$obj->rowid;
				$myobjectstatic->ref=$obj->ref;
				$myobjectstatic->ref_client=$obj->ref_client;
				$myobjectstatic->total_ht = $obj->total_ht;
				$myobjectstatic->total_tva = $obj->total_tva;
				$myobjectstatic->total_ttc = $obj->total_ttc;

				print $myobjectstatic->getNomUrl(1);
				print '</td>';
				print '<td class="nowrap">';
				print '</td>';
				print '<td class="right" class="nowrap">'.price($obj->total_ttc).'</td></tr>';
				$i++;
				$total += $obj->total_ttc;
			}
			if ($total>0)
			{

				print '<tr class="liste_total"><td>'.$langs->trans("Total").'</td><td colspan="2" class="right">'.price($total)."</td></tr>";
			}
		}
		else
		{

			print '<tr class="oddeven"><td colspan="3" class="opacitymedium">'.$langs->trans("NoOrder").'</td></tr>';
		}
		print "</table><br>";

		$db->free($resql);
	}
	else
	{
		dol_print_error($db);
	}
}
END MODULEBUILDER DRAFT MYOBJECT */


print '</div><div class="fichetwothirdright">';


$NBMAX = getDolGlobalInt('MAIN_SIZE_SHORTLIST_LIMIT');
$max = getDolGlobalInt('MAIN_SIZE_SHORTLIST_LIMIT');

/* BEGIN MODULEBUILDER LASTMODIFIED MYOBJECT
// Last modified myobject
if (isModEnabled('reportdesigner') && $user->hasRight('reportdesigner', 'read')) {
	$sql = "SELECT s.rowid, s.ref, s.label, s.date_creation, s.tms";
	$sql.= " FROM ".MAIN_DB_PREFIX."reportdesigner_myobject as s";
	$sql.= " WHERE s.entity IN (".getEntity($myobjectstatic->element).")";
	//if ($socid)	$sql.= " AND s.rowid = $socid";
	$sql .= " ORDER BY s.tms DESC";
	$sql .= $db->plimit($max, 0);

	$resql = $db->query($sql);
	if ($resql)
	{
		$num = $db->num_rows($resql);
		$i = 0;

		print '<table class="noborder centpercent">';
		print '<tr class="liste_titre">';
		print '<th colspan="2">';
		print $langs->trans("BoxTitleLatestModifiedMyObjects", $max);
		print '</th>';
		print '<th class="right">'.$langs->trans("DateModificationShort").'</th>';
		print '</tr>';
		if ($num)
		{
			while ($i < $num)
			{
				$objp = $db->fetch_object($resql);

				$myobjectstatic->id=$objp->rowid;
				$myobjectstatic->ref=$objp->ref;
				$myobjectstatic->label=$objp->label;
				$myobjectstatic->status = $objp->status;

				print '<tr class="oddeven">';
				print '<td class="nowrap">'.$myobjectstatic->getNomUrl(1).'</td>';
				print '<td class="right nowrap">';
				print "</td>";
				print '<td class="right nowrap">'.dol_print_date($db->jdate($objp->tms), 'day')."</td>";
				print '</tr>';
				$i++;
			}

			$db->free($resql);
		} else {
			print '<tr class="oddeven"><td colspan="3" class="opacitymedium">'.$langs->trans("None").'</td></tr>';
		}
		print "</table><br>";
	}
}
*/


// End of page
llxFooter();

////////////////////////
// Jump: Conditional modes
////////////////////////
// if ($action == "overview") {

// }

// else
if ($action == "assign") {
    $reportId = GETPOST("reportId", 'int');
    $projectid = GETPOST("projectid", 'int');
    $sqlUpdate = "UPDATE llx_reports SET projectid = " . $projectid . " WHERE rowid = " . $reportId;
    $db->query($sqlUpdate);
    echo '<script>window.location.href = "' . DOL_URL_ROOT . '/projet/card.php?id=' . $projectid . '&projectid=' . $projectid . '";</script>';
} else if ($action == "delete") {
    $reportId = GETPOST("reportId", 'int');
    $sql = "DELETE FROM llx_reports WHERE rowid = " . $reportId;
    $db->query($sql);
    echo '<script>window.location.href = "?action=overview";</script>';
} else if($action == "deassign"){
	$reportId = GETPOST("reportId", 'int');
	$sql = "UPDATE llx_reports SET projectid = NULL WHERE rowid = " . $reportId;
	$db->query($sql);
	echo '<script>window.location.href = "?action=overview";</script>';
} else if($action == "assign_project"){
        // Fetch all projects
        $sqlProjects = "SELECT rowid, title, description FROM llx_projet";
        $projects = $db->query($sqlProjects)->fetch_all(MYSQLI_ASSOC);
        //var_dump($sqlProjects);
        // Fetch all reports
        $sqlReports = "SELECT rowid, title, description, content, projectid FROM llx_reports";
        $reports = $db->query($sqlReports)->fetch_all(MYSQLI_ASSOC);
        $reportId = GETPOST("reportId");
        $projectId = GETPOST("projectId");
        
        echo '<h1>Projekt zuweisen</h1>';
        echo '<div class="container mt-3 ">';

        // Select project
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        echo '<label for="projectSelect" class="form-label">Projekt auswählen:</label>';
        echo '<select id="projectSelect" class="form-select">';
        foreach ($projects as $project) {
            
            $selected = (intval($project['rowid']) === intval($projectId)) ? 'selected' : '';
            echo '<option value="' . intval($project['rowid']) . '" data-description="' . htmlspecialchars($project['description']) . '" ' . $selected . '>'
                 . htmlspecialchars($project['title']) . '</option>';
        }

        echo '</select>';
        echo '<div id="projectDescription" class="mt-2 p-2 border rounded" style="min-height: 50px; background-color: #f9f9f9; overflow-y: auto; max-height: 300px; word-wrap: break-word;">
                <em>Projektbeschreibung wird hier angezeigt...</em>
              </div>';
        echo '</div>';

        // Select report design
            echo '<div class="col-md-6">';
            // foreach ($reports as $report) {
            //     var_dump($report);
            //     echo intval($report['rowid']);
            //     echo "<br>";
            //     echo "Test";
            //     echo htmlspecialchars($report['title']);
            //     echo "<br>";
            //     echo "Test";
            //     echo htmlspecialchars($report['description']);
            //     echo "<br>";
            //     echo htmlspecialchars($report['content']);
            //     echo "<br>";
            // }
            echo '<label for="reportSelect" class="form-label">Report Design auswählen:</label>';
            echo '<select id="reportSelect" class="form-select">';
            
            
            foreach ($reports as $report) {
                
                $selected = (intval($report['rowid']) === intval($reportId)) ? 'selected' : '';
                echo '<option value="' . intval($report['rowid']) . '" data-description="' . htmlspecialchars($report['description']) . '" data-content="' . $report['content'] . '" ' . $selected . '>'
                     . htmlspecialchars($report['title']) . '</option>';
            }
            echo '</select>';
                echo '<div id="reportDescription" class="mt-2 p-2 border rounded" style="min-height: 50px; background-color: #f9f9f9; overflow-y: auto; max-height: 300px; word-wrap: break-word;">
                        <em>Reportbeschreibung wird hier angezeigt...</em>
                    </div>';
                echo '</div>';
            echo '</div>';

            // Preview container
            echo '<div class="row mt-3 mb-3">';
                echo '<div class="col-12">';
                    echo '<h5>Design Vorschau:</h5>';
                    echo ' <div id="designPreview" class="border rounded p-3" style="max-height: 500px; overflow-y: auto; min-height: 200px;">
         </div>';
                echo '</div>';
            echo '</div>';

                // Submit button
            echo '<div class="row mt-3">';
                    echo '<div class="col-12 text-end">';
                        echo '<button id="assignProjectButton" class="btn btn-primary">Zuweisen</button>';
                    echo '</div>';
                echo '</div>';
            echo '</div>';

        // JavaScript for handling project assignment, descriptions, and preview
        echo '<script>
        document.addEventListener("DOMContentLoaded", () => {

            const reportSelect = document.getElementById("reportSelect");
            const projectSelect = document.getElementById("projectSelect");
            const designPreview = document.getElementById("designPreview");
            const projectDescriptionBox = document.getElementById("projectDescription");
            const reportDescriptionBox = document.getElementById("reportDescription");
            const assignButton = document.getElementById("assignProjectButton");

            // Display project description
            projectSelect.addEventListener("change", () => {
                const selectedOption = projectSelect.options[projectSelect.selectedIndex];
                const description = selectedOption.getAttribute("data-description");
                projectDescriptionBox.innerHTML = description ? description : "<em>Keine Beschreibung verfügbar</em>";
            });
            // Trigger initial description display
            projectSelect.dispatchEvent(new Event("change"));

            function b64DecodeUnicode(str) {
                // Going backwards: from bytestream, to percent-encoding, to original string.
                return decodeURIComponent(atob(str).split(\'\').map(function(c) {
                    return \'%\' + (\'00\' + c.charCodeAt(0).toString(16)).slice(-2);
                }).join(\'\'));
            }

            // Display report description and preview
            reportSelect.addEventListener("change", () => {
                const selectedOption = reportSelect.options[reportSelect.selectedIndex];
                const description = selectedOption.getAttribute("data-description");
                const encodedContent = selectedOption.getAttribute("data-content");

                // Update description
                reportDescriptionBox.innerHTML = description ? description : "<em>Keine Beschreibung verfügbar</em>";

                // Update preview
                if (encodedContent) {
                    const decodedContent = b64DecodeUnicode(encodedContent);
                    console.log(decodedContent);
                    designPreview.innerHTML = decodedContent;
                    document.getElementById("sortable-elements").style.border = null;
                    // Disable all inputs
                    designPreview.querySelectorAll("input, select, textarea").forEach(input => input.disabled = true);
                } else {
                    designPreview.innerHTML = "<em>Keine Vorschau verfügbar</em>";
                }
            });
            // Trigger initial description display
            reportSelect.dispatchEvent(new Event("change"));

            // Submit the project assignment
            assignButton.addEventListener("click", () => {
                const reportId = reportSelect.value;
                const projectId = projectSelect.value;

                if (!reportId || !projectId) {
                    alert("Bitte wählen Sie sowohl ein Projekt als auch ein Report Design aus.");
                    return;
                }

                const formData = new FormData();
                formData.append("action", "save_project_assignment");
                formData.append("reportId", reportId);
                formData.append("projectId", projectId);
                console.log(formData);
                fetch("reportDesignerUpload.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Projekt erfolgreich zugewiesen!");
                        window.location.href = "?action=overview";
                    } else {
                        alert("Fehler: " + data.error);
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.");
                });
            });
        });
        </script>';



}else{


    if($action == "new") {
        echo '
            <div class="container mb-3">
                <div class="row" style="min-height: 120px; padding-bottom: 1rem;">
                    <!-- Display Report ID and User ID -->
                    <div class="col-2">
                        <label class="form-label">Benutzer</label>
                        <input type="text" class="form-control" id="username" value="'.$user->firstname.' '.$user->lastname.'" readonly />
                        <input type="text" class="form-control" id="userId" value="'.$userId.'" hidden />
                    </div>

                    <!-- Title Input -->
                    <div class="col-12 mt-3">
                        <label for="designTitle" class="form-label">Titel des Designs</label>
                        <input type="text" class="form-control" id="designTitle" placeholder="Design Titel eingeben" value="'.$title.'"/>
                    </div>

                    <!-- Description Input -->
                    <div class="col-12 mt-2">
                        <label for="designDescription" class="form-label">Beschreibung</label>
                        <textarea class="form-control" id="designDescription" rows="3" placeholder="Beschreibung des Designs eingeben">'.$description.'</textarea>
                    </div>
                </div>
            </div>
            ';
            echo '
        <script>
        document.addEventListener("DOMContentLoaded", () => {
            const reportContainer = document.getElementById("report-container");
            const propertyPanelElement = document.getElementById("report-property-panel");

            const designTitleInput = document.getElementById("designTitle");
            const designDescriptionInput = document.getElementById("designDescription");

            // Initialize ReportGenerator
            const reportGenerator = new ReportGenerator(reportContainer, propertyPanelElement);
            reportGenerator.loadBasicDesign();
            reportGenerator.generateReport();

            // For now: title and description will be saved with saveDesign() of reportGenerator class
            let designTitle = "";
            let designDescription = "";

            // Event listeners for inputs
            designTitleInput.addEventListener("input", (e) => {
                designTitle = e.target.value;
                // If you want to store inside reportGenerator:
                // reportGenerator.designTitle = designTitle;
            });

            designDescriptionInput.addEventListener("input", (e) => {
                designDescription = e.target.value;
                // If you want to store inside reportGenerator:
                // reportGenerator.designDescription = designDescription;
            });

        });
        </script>
        ';
    }else if($action == "edit") {
    $reportId = GETPOST("reportId");
    //echo $reportId;
    $sql = "SELECT * FROM llx_reports WHERE rowid = ".$reportId;
    $res = $db->query($sql)->fetch_all();
    $userId = $res[0][1] ? $res[0][1] : $user->id;
    $description = $res[0][6];
    $title = $res[0][4];
    //$uploadRef = "reportDesigner.php?reportId=".$reportId."&userId=".$userId;
    // Base 64 decode the content of the report res[2]
    $parameters = base64_decode($res[0][3]);
    // $sqlList = "SELECT * FROM llx_reports_lists WHERE reportid = ".$reportId;
    // $resLists = $db->query($sqlList)->fetch_all();
    // $lists = [];
    // for($i = 0; $i < count($resLists); $i++){
    //     $lists[$i] = $resLists[$i][2];
    // }
    //echo $parameters;
    echo '
    <div class="container mb-3">
        <div class="row" style="min-height: 120px; padding-bottom: 1rem;">
            <!-- Display Report ID and User ID -->
            <div class="col-1">
                <label class="form-label">Report ID</label>
                <input type="text" class="form-control" id="reportId" value="'.$reportId.'" readonly />
            </div>
            <div class="col-2">
                <label class="form-label">Benutzer</label>
                <input type="text" class="form-control" id="username" value="'.$user->firstname.' '.$user->lastname.'" readonly />
                <input type="text" class="form-control" id="userId" value="'.$userId.'" hidden />
            </div>

            <!-- Title Input -->
            <div class="col-12 mt-3">
                <label for="designTitle" class="form-label">Titel des Designs</label>
                <input type="text" class="form-control" id="designTitle" placeholder="Design Titel eingeben" value="'.$title.'"/>
            </div>

            <!-- Description Input -->
            <div class="col-12 mt-2">
                <label for="designDescription" class="form-label">Beschreibung</label>
                <textarea class="form-control" id="designDescription" rows="3" placeholder="Beschreibung des Designs eingeben">'.$description.'</textarea>
            </div>

        </div>
    </div>
    ';

        // echo '
        // <script>
        // document.addEventListener("DOMContentLoaded", () => {
        //     const reportContainer = document.getElementById("report-container");
        //     const propertyPanelElement = document.getElementById("report-property-panel");

        //     const designTitleInput = document.getElementById("designTitle");
        //     const designDescriptionInput = document.getElementById("designDescription");
        //     const autosaveSpinner = document.getElementById("autosave-spinner");

        //     // Initialize ReportGenerator
        //     const reportGenerator = new ReportGenerator(reportContainer, propertyPanelElement);
        //     reportGenerator.loadBasicDesign();
        //     reportGenerator.generateReport();

        //     let designTitle = "";
        //     let designDescription = "";

        //     let typingTimeout = null;
        //     const typingDelay = 1000; // 1 second delay after typing stops

        //     // Show/Hide spinner
        //     function showSpinner() {
        //         autosaveSpinner.style.display = "inline-block";
        //     }

        //     function hideSpinner() {
        //         autosaveSpinner.style.display = "none";
        //     }

        //     // Function to save title/description via AJAX
        //     function autosaveTitleDescription() {
        //         if (!designTitle && !designDescription) {
        //             // If both are empty, maybe we do nothing or still save empty fields
        //             // Here we choose to save anyway, to reflect the current state.
        //         }

        //         showSpinner();

        //         const formData = new FormData();
        //         formData.append("action", "saveTitleDescription");
        //         formData.append("designTitle", designTitle);
        //         formData.append("designDescription", designDescription);

        //         $.ajax({
        //             url: "reportDesignerUpload.php", // Adjust URL as needed
        //             type: "POST",
        //             data: formData,
        //             processData: false,
        //             contentType: false,
        //             success: function(response) {
        //                 console.log("Auto-save response: ", response);
        //                 // Optionally parse JSON if needed and check success.
        //                 hideSpinner();
        //             },
        //             error: function(xhr, status, error) {
        //                 console.error("Auto-save error:", error);
        //                 hideSpinner();
        //             }
        //         });
        //     }

        //     // Reset timer and schedule autosave
        //     function scheduleAutosave() {
        //         if (typingTimeout) clearTimeout(typingTimeout);
        //         typingTimeout = setTimeout(() => {
        //             autosaveTitleDescription();
        //         }, typingDelay);
        //     }

        //     // Event listeners for inputs
        //     designTitleInput.addEventListener("input", (e) => {
        //         designTitle = e.target.value;
        //         scheduleAutosave();
        //     });

        //     designDescriptionInput.addEventListener("input", (e) => {
        //         designDescription = e.target.value;
        //         scheduleAutosave();
        //     });

        //     // If you want, you could also manually trigger autosave at certain events
        //     // For instance, if user tries to navigate away, etc.
        // });
        // </script>
        // ';
    echo '<script>
    document.addEventListener("DOMContentLoaded", () => {
    const reportGenerator = new ReportGenerator(document.getElementById("report-container"), document.getElementById("report-property-panel"));
    reportGenerator.initFromParams(JSON.parse(`'.$parameters.'`));
    reportGenerator.generateReport();
    });
    </script>';


}else if($action == "basicDesign"){
    $sql = "SELECT * FROM llx_reports WHERE rowid = 0";
    $res = $db->query($sql)->fetch_all();
    $parameters = base64_decode($res[0][3]);
    echo '
        <h1> Basis-Design bearbeiten
        </h1>
        <a href="?action=overview&param=overwrite" class="btn btn-outline-primary">Mit bestehendem Design überschreiben</a>
    ';
    echo '<script>
    document.addEventListener("DOMContentLoaded", () => {
    const reportGenerator = new ReportGenerator(document.getElementById("report-container"), document.getElementById("report-property-panel"));
    reportGenerator.initFromParams(JSON.parse(`'.$parameters.'`));
    reportGenerator.generateReport();
    });
    </script>';
}else {
	// Use a LEFT JOIN to fetch user and project data in one query
    // Leave out template with id 0, because this is the basic template we alawys use for new projects. See projet/card.php line 292
    
    $param = isset($_GET['param']) ? $_GET['param'] : '';
    $projectid = isset($_GET['projectid']) ? $_GET['projectid'] : '';
    // Determine if we are in overwrite mode
    $isOverwriteMode = ($param === 'overwrite');
    $isAssignMode = $projectid;
    
    if($isAssignMode){
        $sql = "
        SELECT
            r.rowid AS report_id,
            r.fk_user,
            r.title,
            r.description,
            r.date,
            r.projectid,
            u.lastname,
            u.firstname,
            p.title AS project_title,
            r.content
        FROM llx_reports r
        LEFT JOIN llx_user u ON r.fk_user = u.rowid
        LEFT JOIN llx_projet p ON r.projectid = p.rowid
        WHERE r.rowid != 0 AND r.projectid IS NULL
    ";
    }else{
        $sql = "
        SELECT
            r.rowid AS report_id,
            r.fk_user,
            r.title,
            r.description,
            r.date,
            r.projectid,
            u.lastname,
            u.firstname,
            p.title AS project_title,
            r.content
        FROM llx_reports r
        LEFT JOIN llx_user u ON r.fk_user = u.rowid
        LEFT JOIN llx_projet p ON r.projectid = p.rowid
        WHERE r.rowid != 0
    ";

    }
    

    $resql = $db->query($sql);
    if (!$resql) {
        // Handle query error
        echo $db->lasterror();
        exit;
    }

    $reports = $db->query($sql)->fetch_all(MYSQLI_ASSOC);

    
    echo "<h1>Übersicht</h1>";
    echo "<div class='container'>";

    echo "<div class='optionRow mb-3 d-flex gap-2'>";
    if(!$isOverwriteMode && !$isAssignMode){
        echo "<a href='?action=new' class='btn btn-outline-primary btn-sm'>Neues Design erstellen</a>";
        // "Zuweisen" always shown, no projectid check
        echo "<button id='deleteSelectedBtn' class='btn btn-sm btn-danger' disabled>Mehrere löschen</button>";
        //
        echo "<a href='?action=basicDesign' class='btn btn-sm btn-outline-primary'>Basis-Design bearbeiten</a>";
    }else if($isOverwriteMode && !$isAssignMode){
        echo "<a href='?action=overview' class='btn btn-outline-primary btn-sm'>Zurück zur Übersicht</a>";
        echo "<a href'?action=basicDesign' class='btn btn-sm btn-outline-primary'>Zurück zum Bearbeiten</a>";
        echo "<button id='overwriteDesignBtn' class='btn btn-sm btn-success' disabled>Überschreiben</button>";
    }else if($isAssignMode && !$isOverwriteMode){
        echo "<a href='".DOL_URL_ROOT."/projet/card.php?id=".$projectid."' class='btn btn-outline-primary btn-sm'>Zurück zum Projekt</a>";
        echo "<button id='overwriteDesignBtn' class='btn btn-sm btn-success' disabled>Zuweisen</button>";
    }
    echo '</div>';

    echo '<table class="table table-striped" id="reportTable">';
    echo '<thead>';
    echo '<tr>';
    // Checkbox for select all
    if(!$isOverwriteMode  && !$isAssignMode){
        echo '<th scope="col"><input type="checkbox" id="selectAllReports"></th>';
    }else{
        echo '<th scope="col"></th>';
    }
    //echo '<th scope="col">ID</th>';
    echo '<th scope="col">Titel</th>';
    echo '<th scope="col">Beschreibung</th>';
    echo '<th scope="col">Zuletzt bearbeitet am</th>';
    echo '<th scope="col">von</th>';
    if(!$isOverwriteMode  && !$isAssignMode) {
        echo '<th scope="col">Aktionen</th>';
    }
    //echo '<th scope="col">Aktionen</th>';
    echo '<th scope="col">Projekt</th>';
    echo '<th scope="col"></th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    if ($reports) {
        foreach ($reports as $report) {
            $reportId = $report['report_id'];
            $projectId = $report['projectid'] ? $report['projectid'] : $projectid;
            $title = $report['title'];
            $description = $report['description'];
            $lastModified = $report['date'];
            $userFullName = trim($report['firstname'] . ' ' . $report['lastname']);
            $projectTitle = $report['project_title'] ? $report['project_title'] : 'Nicht zugewiesen';
            $contentEncoded = $report['content']; // Base64 encoded HTML content

            // Convert date to "d.m.Y | H:i:s"
            $formattedDate = date("d.m.Y | H:i:s", strtotime($lastModified));

            echo '<tr class="report-row" data-report-id="'.intval($reportId).'" data-content="'.$contentEncoded.'">';
            if(!$isOverwriteMode  && !$isAssignMode){
                echo '<td><input type="checkbox" class="report-checkbox" value="'.intval($reportId).'"></td>';
            }else{
                echo '<td><input type="radio" name="selectRow" class="report-radio" value="'.intval($reportId).'"></td>';
            }
            //echo '<td>' . intval($reportId) . '</td>';
            echo '<td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">' . htmlspecialchars($title) . '</td>';
            $maxLength = 50; // Max length before truncating the text
            $shortDescription = strlen($description) > $maxLength ? substr($description, 0, $maxLength) . '...' : $description;
            echo '<td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">';
            echo '<span class="truncated-text" style="cursor: pointer; color: blue;" title="Klicken, um mehr zu sehen" data-full-text="' . htmlspecialchars($description) . '">'
                . htmlspecialchars($shortDescription) . '</span>';
            echo '</td>';
            echo '<td>' . $formattedDate . '</td>';
            echo '<td>' . htmlspecialchars($userFullName) . '</td>';

            if(!$isOverwriteMode && !$isAssignMode){   
                // Build actions
                $actions = '
                <div class="custom-dropdown">
                    <button class="custom-dropdown-toggle">Aktionen <span>&#9662;</span></button>
                    <div class="custom-dropdown-menu">
                        <a href="?action=assign_project&reportId=' . intval($reportId) .
                            (isset($projectId) && !empty($projectId) ? '&projectId=' . intval($projectId) : '') . '">
                            Zuweisen
                        </a>
                        <a href="?action=edit&reportId=' . intval($reportId) . '">Bearbeiten</a>
                        <a href="?action=delete&reportId=' . intval($reportId) . '">Löschen</a>
                        <a href="#" class="duplicate-report" data-report-id="' . intval($reportId) . '">Duplizieren</a>
                        <a href="?action=deassign&reportId=' . intval($reportId) . '">Zuweisung entfernen</a>
                    </div>
                </div>';
                
                echo '<td>'.$actions.'</td>';
            }
			$projectForUrl = new Project($db);
			$projectForUrl->fetch($projectId);
			$projectURL = $projectForUrl->getNomUrl(1);
			$link = $projectForUrl->getNomUrl(0);
			// Replace projectID in getNomUrl output with the title
            
			
            
            if($projectTitle !="Nicht zugewiesen"){
                $link = preg_replace('/>([^<]+)</', '>'.$projectTitle.'<', $link);
                $projectTitle = $link;
            }
            
            echo '<td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">' .$projectTitle. '</td>';
            echo '<td>';
            echo '<button class="btn btn-sm btn-secondary preview-btn p-2" type="button">Vorschau</button>';
            echo '</td>';
            echo '</tr>';

            // Hidden preview row
            echo '<tr class="preview-row" style="display: none;">
            <td colspan="8">
                <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto; min-height: 150px;">
                    <em>Vorschau wird geladen...</em>
                </div>
            </td>
        </tr>';
        }
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    echo '
    <div class="modal fade" id="fullTextModal" tabindex="-1" aria-labelledby="fullTextModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="max-width: 600px; margin: auto;">
          <div class="modal-header">
            <h5 class="modal-title" id="fullTextModalLabel">Vollständige Beschreibung</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="modalBodyContent" style="overflow-y: auto; max-height: 300px; word-wrap: break-word;">
            <!-- Full text will be inserted here -->
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Schließen</button>
          </div>
        </div>
      </div>
    </div>';


    // JavaScript for handling multiple delete, preview toggle, etc.
    echo '<script>
    isOverwriteMode = ' . ($isOverwriteMode ? 'true' : 'false') . ';
    isAssignMode = ' . ($isAssignMode ? 'true' : 'false') . ';
    document.addEventListener("DOMContentLoaded", () => {
    const selectAll = document.getElementById("selectAllReports") ? document.getElementById("selectAllReports") : null;
    const checkboxes = document.querySelectorAll(".report-checkbox");
    const deleteSelectedBtn = document.getElementById("deleteSelectedBtn") ? document.getElementById("deleteSelectedBtn") : null;
    const previewButtons = document.querySelectorAll(".preview-btn");
    const deleteButtons = document.querySelectorAll(".delete-report");
    const duplicateButtons = document.querySelectorAll(".duplicate-report");

   


    // Delete selected reports logic remains unchanged
    if(!isOverwriteMode && !isAssignMode){

      // Function to check if any checkbox is selected
        function updateDeleteButtonState() {
            const anyChecked = [...checkboxes].some(cb => cb.checked);
            deleteSelectedBtn.disabled = !anyChecked; // Enable or disable button
        }

        // Select all functionality
        selectAll.addEventListener("change", () => {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateDeleteButtonState();
        });

        checkboxes.forEach(cb => {
            cb.addEventListener("change", () => {
                if (!cb.checked) {
                    selectAll.checked = false;
                } else if ([...checkboxes].every(c => c.checked)) {
                    selectAll.checked = true;
                }
                updateDeleteButtonState();
            });
        });

        deleteSelectedBtn.addEventListener("click", () => {
            const selectedReportIds = [...checkboxes].filter(cb => cb.checked).map(cb => cb.value);
            if (selectedReportIds.length === 0) {
                alert("Bitte wählen Sie mindestens einen Report zum Löschen aus.");
                return;
            }

            if (confirm("Sind Sie sicher, dass Sie die ausgewählten Reports löschen möchten?")) {
                const formData = new FormData();
                formData.append("action", "deleteMultiple");
                formData.append("reportIds", JSON.stringify(selectedReportIds));

                fetch("reportDesignerUpload.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Ausgewählte Reports wurden erfolgreich gelöscht.");
                        selectedReportIds.forEach(id => {
                            const row = document.querySelector(`.report-row[data-report-id=\'${id}\']`);
                            if (row) {
                                row.nextElementSibling?.remove(); // Remove preview row
                                row.remove(); // Remove main row
                            }
                        });
                        updateDeleteButtonState();
                    } else {
                        alert("Fehler beim Löschen: " + data.error);
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.");
                });
            }
        });
        // Initialize button state on page load
        updateDeleteButtonState();

        
        // Confirm delete for individual report
        deleteButtons.forEach(button => {
            button.addEventListener("click", (e) => {
                e.preventDefault();
                const reportId = button.dataset.reportId;
                if (confirm("Sind Sie sicher, dass Sie diesen Report löschen möchten?")) {
                    window.location.href = `?action=delete&reportId=${reportId}`;
                }
            });
        });

        // Confirm duplicate report
        duplicateButtons.forEach(button => {
            button.addEventListener("click", (e) => {
                e.preventDefault();
                const reportId = button.dataset.reportId;

                if (confirm("Möchten Sie diesen Report wirklich duplizieren?")) {
                    const formData = new FormData();
                    formData.append("action", "duplicateReport");
                    formData.append("reportId", reportId);

                    fetch("reportDesignerUpload.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("Report wurde erfolgreich dupliziert!");
                            window.location.reload(); // Reload to reflect the new duplicated report
                        } else {
                            alert("Fehler: " + data.error);
                        }
                    })
                    .catch(error => {
                        console.error("Error:", error);
                        alert("Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.");
                    });
                }
            });
        });

        // Delete selected reports with confirmation
        deleteSelectedBtn.addEventListener("click", () => {
            const selectedReportIds = [...checkboxes].filter(cb => cb.checked).map(cb => cb.value);
            if (selectedReportIds.length === 0) {
                alert("Bitte wählen Sie mindestens einen Report zum Löschen aus.");
                return;
            }

            if (confirm("Sind Sie sicher, dass Sie die ausgewählten Reports löschen möchten?")) {
                const formData = new FormData();
                formData.append("action", "deleteMultiple");
                formData.append("reportIds", JSON.stringify(selectedReportIds));

                fetch("reportDesignerUpload.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Ausgewählte Reports wurden erfolgreich gelöscht.");
                        // Remove deleted rows from the table
                        selectedReportIds.forEach(id => {
                            const row = document.querySelector(`.report-row[data-report-id=\'${id}\']`);
                            if (row) {
                                row.nextElementSibling?.remove(); // Remove the preview row
                                row.remove(); // Remove the main row
                            }
                        });
                    } else {
                        alert("Fehler beim Löschen: " + data.error);
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.");
                });
            }
        });
    }if (isOverwriteMode) {
        // ------------------
        // Overwrite Mode
        // ------------------
        const overwriteDesignBtn = document.getElementById("overwriteDesignBtn");
        function updateOverwriteButtonState() {
            const selectedRadio = document.querySelector(".report-radio:checked");
            overwriteDesignBtn.disabled = !selectedRadio;
        }
        // Enable/disable button
        document.querySelectorAll(".report-radio").forEach(rb => {
            rb.addEventListener("change", () => {
                updateOverwriteButtonState();
            });
        });
        // Overwrite click
        overwriteDesignBtn.addEventListener("click", () => {
            const selectedRadio = document.querySelector(".report-radio:checked");
            if (!selectedRadio) return; // no selection

            if (confirm("Sind Sie sicher, dass Sie das ausgewählte Design überschreiben möchten?")) {
                const formData = new FormData();
                formData.append("action", "overwriteBasicDesign");
                formData.append("reportId", selectedRadio.value);
                formData.append("userId", ' . $user->id . ');

                fetch("reportDesignerUpload.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Basis-Design wurde erfolgreich überschrieben!");
                        window.location.href = "?action=overview";
                    } else {
                        alert("Fehler beim Überschreiben: " + data.error);
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.");
                });
            }
        });
        updateOverwriteButtonState();
    }else if (isAssignMode) {
        // ------------------
        // Assign Mode
        // ------------------
        // We re-use the same "overwriteDesignBtn" ID, but interpret it as "Zuweisen."
        const assignDesignBtn = document.getElementById("overwriteDesignBtn");
        function updateAssignButtonState() {
            const selectedRadio = document.querySelector(".report-radio:checked");
            assignDesignBtn.disabled = !selectedRadio;
        }
        // Enable/disable button
        document.querySelectorAll(".report-radio").forEach(rb => {
            rb.addEventListener("change", () => {
                updateAssignButtonState();
            });
        });

        // When the user clicks "Zuweisen"
        assignDesignBtn.addEventListener("click", () => {
            const selectedRadio = document.querySelector(".report-radio:checked");
            if (!selectedRadio) return; // no selection

            if (confirm("Sind Sie sicher, dass Sie dieses Design dem Projekt zuweisen möchten?")) {
                const formData = new FormData();
                formData.append("action", "save_project_assignment"); // <--- The new action
                formData.append("reportId", selectedRadio.value);
                formData.append("projectId", ' . intval($projectid) . ');
                formData.append("userId", ' . $user->id . ');

                fetch("reportDesignerUpload.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Design wurde erfolgreich zugewiesen!");
                        // Maybe redirect to the project card or overview
                        window.location.href = "' . DOL_URL_ROOT . '/projet/card.php?id=' . intval($projectid) . '";
                    } else {
                        alert("Fehler beim Zuweisen: " + data.error);
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.");
                });
            }
        });
        updateAssignButtonState();
    }
    

    // Base64 decode for UTF-8
    function b64DecodeUnicode(str) {
        return decodeURIComponent(atob(str).split("").map(function(c) {
            return "%" + ("00" + c.charCodeAt(0).toString(16)).slice(-2);
        }).join(""));
    }

    // Vorschau toggle
    previewButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            const row = btn.closest(".report-row");
            const previewRow = row.nextElementSibling;
            if (previewRow.style.display === "none") {
                // Show preview
                previewRow.style.display = "table-row";
                const contentEncoded = row.getAttribute("data-content");
                if (contentEncoded) {
                    const decodedContent = b64DecodeUnicode(contentEncoded);
                    const previewDiv = previewRow.querySelector("div");
                    previewDiv.innerHTML = decodedContent;

                    // Disable all inputs in preview
                    previewDiv.querySelectorAll("input, select, textarea").forEach(input => input.disabled = true);
                } else {
                    previewRow.querySelector("div").innerHTML = "<em>Keine Vorschau verfügbar</em>";
                }
            } else {
                // Hide preview
                previewRow.style.display = "none";
            }
        });
    });

    const truncatedTextElements = document.querySelectorAll(".truncated-text");
    const modalBody = document.getElementById("modalBodyContent");

    truncatedTextElements.forEach(element => {
        element.addEventListener("click", () => {
            const fullText = element.getAttribute("data-full-text");
            modalBody.textContent = fullText; // Insert the full text into the modal
            const modal = new bootstrap.Modal(document.getElementById("fullTextModal"));
            modal.show();
        });
    });

});

    </script>';
    echo '
        <style>
        table.table td, table.table th {
            padding: 12px 8px;
            vertical-align: middle;
        }


        /* Custom dropdown styles */
        .custom-dropdown {
            position: relative;
            display: inline-block;
        }

        .custom-dropdown-toggle {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 8px 12px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .custom-dropdown-toggle span {
            font-size: 1rem; /* Arrow size */
        }

        .custom-dropdown-menu {
            display: none;
            position: absolute;
            background-color: white;
            border: 1px solid #ddd;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            min-width: 160px;
            border-radius: 4px;
        }

        .custom-dropdown-menu a {
            color: #333;
            padding: 8px 12px;
            text-decoration: none;
            display: block;
            font-size: 14px;
        }

        .custom-dropdown-menu a:hover {
            background-color: #f1f1f1;
            color: #000;
        }

        .custom-dropdown:hover .custom-dropdown-menu {
            display: block; /* Show menu on hover */
        }
        </style>
        ';
}


    ////////////////////////
    // Jump: New or Edit
    ////////////////////////


echo '
<div class="offcanvas offcanvas-start" data-bs-scroll="true" data-bs-backdrop="false" tabindex="-1" id="offcanvasScrolling" aria-labelledby="offcanvasScrollingLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasScrollingLabel">Offcanvas with body scrolling</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">';

  echo '<div id="report-property-panel" class="container border rounded p-3 mt-3">';
  echo '<ul class="nav nav-tabs" id="propertyLogicTabs" role="tablist">';
  echo '<li class="nav-item" role="presentation">';
  echo '<button class="nav-link active" id="properties-tab" data-bs-toggle="tab" data-bs-target="#properties" type="button" role="tab" aria-controls="properties" aria-selected="true">Properties</button>';
  echo '</li>';
  echo '<li class="nav-item" role="presentation">';
  echo '<button class="nav-link" id="logic-tab" data-bs-toggle="tab" data-bs-target="#logic" type="button" role="tab" aria-controls="logic" aria-selected="false">Logic</button>';
  echo '</li>';
  echo '</ul>';

  echo '<div class="tab-content" id="propertyLogicContent">';
  echo '<div class="tab-pane fade show active" id="properties" role="tabpanel" aria-labelledby="properties-tab">';
  echo '<div id="report-property-panel-header" class="text-center mb-3">';
  echo $langs->trans("Properties");
  echo '</div>';

  echo '<div class="row g-2">'; // Bootstrap row with gap
  // Measurement inputs
  echo '<div id="measurement" class="col-md-6">';
  echo '<div class="mb-2">
          <label for="property-width" class="form-label">'.$langs->trans("Width").'</label>
          <div class="d-flex align-items-center">
              <input id="property-width" class="form-control form-control-sm report-property" type="number" placeholder="'.$langs->trans("Width").'" disabled />
              <select id="property-width-unit" class="form-select form-select-sm ms-2" disabled>
              <option value="%">%</option>
              <option value="px">px</option>
              </select>
          </div>
        </div>';
  echo '<div>
          <label for="property-height" class="form-label">'.$langs->trans("Height").'</label>
          <div class="d-flex align-items-center">
              <input id="property-height" class="form-control form-control-sm report-property" type="number" placeholder="'.$langs->trans("Height").'" disabled />
              <select id="property-height-unit" class="form-select form-select-sm ms-2" disabled>
                  <option value="%">%</option>
                  <option value="px">px</option>
              </select>
          </div>
        </div>';
  echo '</div>';

  // Position input
  // echo '<div id="position" class="col-md-6">';
  // echo '<div>
  //         <label for="property-pos" class="form-label">'.$langs->trans("Position").'</label>
  //         <button id="property-pos-increase" class="btn btn-sm btn-primary">+</button>
  //         <button id="property-pos-decrease" class="btn btn-sm btn-primary">-</button>
  //       </div>';
  // echo '</div>';

  // Style inputs
  echo '<div id="style" class="col-12">';
  echo '<div class="mb-2">
          <label for="property-style-color" class="form-label">'.$langs->trans("TextColor").'</label>
          <input id="property-style-color" class="form-control report-property" type="text" placeholder="'.$langs->trans("TextColor").'" disabled />
        </div>';
  echo '<div class="mb-2">
          <label for="property-style-background-color" class="form-label">'.$langs->trans("BackgroundColor").'</label>
          <input id="property-style-background-color" class="form-control report-property form-control-color" type="color" placeholder="'.$langs->trans("BackgroundColor").'" disabled />
        </div>';
  echo '<div class="mb-2">
          <label for="property-style-font-size" class="form-label">'.$langs->trans("FontSize").'</label>
          <input id="property-style-font-size" class="form-control report-property" type="number" placeholder="'.$langs->trans("FontSize").'" disabled />
        </div>';
  echo '<div class="mb-2">
          <label for="property-style-font-weight" class="form-label">'.$langs->trans("FontWeight").'</label>
          <input id="property-style-font-weight" class="form-control report-property" type="text" placeholder="'.$langs->trans("FontWeight").'" disabled />
        </div>';
  echo '<div class="mb-2">
          <label for="property-style-padding" class="form-label">'.$langs->trans("Padding").'</label>
          <input id="property-style-padding" class="form-control report-property" type="number" placeholder="'.$langs->trans("Padding").'" disabled />
        </div>';
  echo '<div class="mb-2">
          <label for="property-style-margin" class="form-label">'.$langs->trans("Margin").'</label>
          <input id="property-style-margin" class="form-control report-property" type="number" placeholder="'.$langs->trans("MarginTop").'" disabled />
        </div>';
  echo '<div>
          <label for="property-style-border" class="form-label">'.$langs->trans("Border").'</label>
          <input id="property-style-border" class="form-control report-property" type="number" placeholder="'.$langs->trans("Border").'" disabled />
        </div>';
  echo '</div>';
  echo '</div>';
  echo '</div>';

  // Logic Tab
  echo '<div class="tab-pane fade" id="logic" role="tabpanel" aria-labelledby="logic-tab">';
  echo '<div class="text-center mt-3">Logic</div>'; // Placeholder for Logic tab content
  echo '</div>'; // end of logic tab

  echo '</div>'; // end of tab content
  echo '</div>'; // end of report-property-panel

echo '
  </div>
</div>';

echo '<div id="report-container" class="container-fluid mt-4 ">';
echo '</div>';

echo '<style>';
echo
'
#report-container {
    display: flex;
    flex-direction: column;
    margin-top: 1rem;
    padding: 1rem;
    min-height: 100%;
}

.report-table td {
    border: 1px solid black;
    width: 100%;
    resize: both;
}

#report-property-panel {
    display: flex;
    justify-content: center;
    flex-direction: column;
    gap: .25rem;
    margin-top: 1rem;
    border: 1px solid #b3d9ff;
    background-color: #f7f7f7;
    padding: 1rem;
}

#report-property-panel-header {
    display: flex;
    justify-content: center;
    margin-bottom: 1rem;
}
';
echo '</style>';


echo '
<div class="modal fade modal-xl" id="elementModal" tabindex="-1" aria-labelledby="elementModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="elementModalLabel">Auswahl und Konfiguration</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeElementBtn"></button>
      </div>
      <div class="modal-body d-flex">
        <!-- Options Column -->
        <div class="row">
        <div class="col-4">
        <div class="list-group" id="list-group" style="flex: 1;">';
        //   <button class="list-group-item list-group-item-action" data-value="display" onClick= >Anzeige</button>
        //   <button class="list-group-item list-group-item-action" data-value="input">Input</button>
        //   <button class="list-group-item list-group-item-action" data-value="textarea">Textfeld</button>
        //   <button class="list-group-item list-group-item-action" data-value="table">Tabelle</button>
        //   <button class="list-group-item list-group-item-action" data-value="upload">Upload</button>
        //   <button class="list-group-item list-group-item-action" data-value="clock">Uhrzeit</button>
        //   <button class="list-group-item list-group-item-action" data-value="div">Box</button>

echo    '</div>
        </div>
        <div class="col-8" id="modalConfigBox">
        <!-- Configuration Box -->
        <div id="elementConfigBox" class="ms-3 responsive" style="flex: 2; border-left: 1px solid #ccc; padding-left: 1rem;">
          <!-- Dynamic content goes here -->
        </div>
        </div>
        </div>


      </div>
      <div class="modal-footer" id="modalFooter">
        <button type="button" class="btn btn-primary" id="addElementBtn" disabled>Add Element</button>
      </div>
    </div>
  </div>
</div>
';

echo
'<script>


////////////////////////
// Jump: ReportGenerator.js
////////////////////////

class ReportGenerator {
    constructor(reportContainer, propertyPanelElement) {
        this.elements = [];
        this.selectedElement = null;
        this.propertyPanelElement = propertyPanelElement;
        if (!(reportContainer instanceof HTMLElement)) {
            throw new TypeError("Report container must be an instance of HTMLElement");
        } else {
            this.reportContainer = reportContainer;
        }
        this.elementCreator = new ReportElementCreator(this ,document.getElementById("elementModal"), document.getElementById("modalConfigBox") , document.getElementById("addElementBtn"), document.getElementById("closeElementBtn"));

        this.init();
    }

    // Called inside constructor to set up event listeners and bind to the correct context
    init () {
    console.log("Init");
        // Handle clicks outside of reportdesigner
        document.addEventListener("click", (e) => this.handleGlobalClick(e));

        // Handle clicks inside the designer
        this.reportContainer.addEventListener("click", (e) => this.handleClick(e));
        const propertyPanel = this.propertyPanelElement;
        const inputs = propertyPanel.querySelectorAll(".report-property");

        inputs.forEach(input => {
            input.addEventListener("input", (e) => this.handlePropertyChange(e));
        });

        const select = propertyPanel.querySelector("select");
        select.addEventListener("change", (e) => this.handlePropertyChange(e));

        // Hotkeys

        // Add event listener for keydown to handle delete key
        document.addEventListener(\'keydown\', (e) => {
            if (e.key === \'Delete\') {
                this.deleteSelectedElement();
            }
        });

        document.addEventListener(\'keydown\', (e) => {
            if (e.key === \'Escape\') {

                this.selectedElement = null;

            }
            // If control, shift and s are pressed at the same time, save the report
            if (e.key === \'s\' && e.ctrlKey && e.shiftKey) {
                this.saveDesign();
            }
            if(e.key === \'c\' && e.ctrlKey && e.shiftKey) {
                this.copyElement();
            }
        });

        // Add event listeners for selecting elements
        document.querySelectorAll(\'.sortable-item\').forEach(item => {
            item.addEventListener(\'click\', (e) => {
                const elementId = e.currentTarget.getAttribute(\'data-element-id\');
                this.selectedElement = this.elements.find(element => element._id === elementId);
            });
        });





    }



    // Pass json decoded params to load the report
    initFromParams(params){
        const reportParameters = params;
        console.log(reportParameters);
        reportParameters.forEach(param => {
        if(param.label === ""){
            param.label = " ";
        }
            switch(param.type) {
                case "display":
                    //console.log(param.contentType);
                    const displayElement = new ReportElementDisplay(param.content, param.style, param.label, param.contentType);
                    this.addElement(displayElement);
                    break;
                case "input":
                    const inputElement = new ReportElementInput(param.content, param.style, param.label);
                    this.addElement(inputElement);
                    break;
                case "textarea":
                    const textareaElement = new ReportElementTextarea(param.content, param.style, param.label);
                    this.addElement(textareaElement);
                    break;
                case "table":
                    console.log(param.content);
                    let tableElement = new ReportElementTable(param.content, param.style, param.content.length, param.content[0].length, param.label);
                    // param.content.forEach((row, rowIndex) => {
                    //     row.forEach((cell, cellIndex) => {
                    //         tableElement.changeCellChildElement(rowIndex, cellIndex, tableElement.createCellContentElement(cell.contentType, cell.content));
                    //     });
                    // });
                    this.addElement(tableElement);
                    break;
                case "div":
                    const divElement = new ReportElementDiv(param.content, param.style);
                    this.addElement(divElement);
                    break;
                case "upload":
                    const uploadElement = new ReportElementUpload(param.content, param.style, param.label, param.includeSelect || false, param.selectOptions || []);
                    this.addElement(uploadElement);
                    break;
                case "checkbox":
                    const checkboxElement = new ReportElementCheckbox(param.content, param.style, param.label);
                    this.addElement(checkboxElement);
                    break;
                case "radio":
                    const radioElement = new ReportElementRadio(param.content, param.style, param.label, param.group);
                    this.addElement(radioElement);
                    break;
                case "signature":
                    const signatureElement = new ReportElementSignature(param.content, param.style, param.label);
                    this.addElement(signatureElement);
                    break;
                case "timerange":
                    const timeRangeElement = new ReportElementTimeRange(param.content, param.style, param.label);
                    this.addElement(timeRangeElement);
                    break;
                case "time":
                    const timeElement = new ReportElementTime(param.content, param.style, param.label);
                    this.addElement(timeElement);
                    break;
                default:
                    break;
            }
        });
    }


    // The basic design Mr. Michael wants
    loadBasicDesign(){
            const displayElement = new ReportElementDisplay("Name vom Projekt / Report", "", "", "dynamic");
            const tableElement = new ReportElementTable("", "", 4, 3);
            tableElement.label = "";
            tableElement.changeCellChildElement(0, 0, new ReportElementDisplay("Ticketnummer", "", "", "dynamic"));
            tableElement.changeCellChildElement(0, 1, new ReportElementDisplay("", "", "", "static"));
            tableElement.changeCellChildElement(0, 2, new ReportElementDisplay("Ticketart", "", "", "dynamic"));

            tableElement.changeCellChildElement(1, 0, new ReportElementDisplay("Filiale", "", "", "dynamic"));
            tableElement.changeCellChildElement(1, 1, new ReportElementDisplay("Stopp", "", "", "dynamic"));
            tableElement.changeCellChildElement(1, 2, new ReportElementDisplay("Dringlichkeit", "", "", "dynamic"));

            tableElement.changeCellChildElement(2, 0, new ReportElementDisplay("Strasse", "", "", "dynamic"));
            tableElement.changeCellChildElement(2, 1, new ReportElementDisplay("", "", "", "static"));
            tableElement.changeCellChildElement(2, 2, new ReportElementDisplay("Termin", "", "", "dynamic"));

            tableElement.changeCellChildElement(3, 0, new ReportElementDisplay("Ort", "", "", "dynamic"));
            tableElement.changeCellChildElement(3, 1, new ReportElementDisplay("", "", "", "static"));
            tableElement.changeCellChildElement(3, 2, new ReportElementDisplay("Tel Filiale", "", "", "dynamic"));


            const descriptionElement = new ReportElementDisplay("Auftrag / Störungsbeschreibung", "", "", "static" );
            const descriptionElementDynamic = new ReportElementDisplay("Auftrag / Störungsbeschreibung (dynamisch)", "", "", "dynamic" );

            this.addElement(displayElement);
            this.addElement(tableElement);
            this.addElement(descriptionElement);
            this.addElement(descriptionElementDynamic);
            this.addElement(new ReportElementDisplay("Lösungsvorschlag", "", "", "static"));
            this.addElement(new ReportElementDisplay("Lösungsvorschlag (dynamisch)", "", "", "dynamic"));
            // #TODO: Should be loaded from template, since HH:mm should be calculated from the time range and we can only do this inside a template class
            const arrivalTable = new ReportElementTable("", "", 2, 3 );
            arrivalTable.label = "";
            arrivalTable.changeCellChildElement(0, 0, new ReportElementDisplay("Ankunftszeit", "", "", "static"));
            arrivalTable.changeCellChildElement(0, 1, new ReportElementDisplay("HH:mm", "", "", "static"));
            arrivalTable.changeCellChildElement(0, 2, new ReportElementDisplay("Km", "", "", "static"));
            // const arrivalTime = new ReportElementTable("", "", 1, 3);
            // arrivalTime.label = "";
            // arrivalTime.changeCellChildElement(0, 0, new ReportElementTime("", "", ""));
            // arrivalTime.changeCellChildElement(0, 1, new ReportElementDisplay("bis", "", "", "static"));
            // // turn 0,1 to td instead of th
            // arrivalTime.changeCellChild
            // arrivalTime.changeCellChildElement(0, 2, new ReportElementTime("", "", ""));

            // arrivalTable.changeCellChildElement(1, 0, arrivalTime);
            arrivalTable.changeCellChildElement(1, 0, new ReportElementTimeRange("", "", ""));
            arrivalTable.changeCellChildElement(1, 1, new ReportElementTime("", "", ""));
            arrivalTable.changeCellChildElement(1, 2, new ReportElementInput("", "", ""));

            this.addElement(arrivalTable);
            const extendableTable = new ReportElementTable("", "", 3, 3);
            extendableTable.label = "Ersatzteile/Material";
            extendableTable.extendable = false;
            this.addElement(extendableTable);
            this.addElement(new ReportElementDisplay("Techniker Notizen", "", "", "static"));
            const technikerText = new ReportElementTextarea("", "", "");
            technikerText.label = "Techniker Notizen";
            this.addElement(technikerText);

            this.addElement(new ReportElementUpload("Bild 1", "", "Bild 1"));
            this.addElement(new ReportElementUpload("Bild 2", "", "Bild 2"));
            this.addElement(new ReportElementUpload("Bild 3", "", "Bild 3"));
            this.addElement(new ReportElementUpload("Bild 4", "", "Bild 4"));
            this.addElement(new ReportElementUpload("Bild 5", "", "Bild 5"));

            const partsTableNew = new ReportElementTable("", "", 3, 2);
            partsTableNew.label = "Ersatzteile/Material Neu";
            partsTableNew.extendable = false;
            this.addElement(partsTableNew);

            const partsTableOld = new ReportElementTable("", "", 3, 2);
            partsTableOld.label = "Ersatzteile/Material Alt/Abbau";
            partsTableOld.extendable = false;
            this.addElement(partsTableOld);

            const signatureTechnician = new ReportElementSignature("", "", "Unterschrift Techniker");
            this.addElement(signatureTechnician);

            const signatureLeader = new ReportElementSignature("", "", "Unterschrift Leiter");
            this.addElement(signatureLeader);


            const signoffTable = new ReportElementTable("", "", 2, 2);
            signoffTable.label = "Abmeldung";
            signoffTable.extendable = false;
            signoffTable.changeCellChildElement(0, 0, new ReportElementDisplay("Kundenname", "", "", "static"));
            signoffTable.changeCellChildElement(0, 1, new ReportElementDisplay("Telefonnummer", "", "", "static"));
            signoffTable.changeCellChildElement(1, 0, new ReportElementInput("", "", "", ""));
            signoffTable.changeCellChildElement(1, 1, new ReportElementInput("", "", "", ""));
            this.addElement(signoffTable);

            const workTimeTable = new ReportElementTable("", "", 2, 2);
            workTimeTable.label = "Arbeitszeit";
            workTimeTable.extendable = false;
            workTimeTable.changeCellChildElement(0, 0, new ReportElementDisplay("Arbeitszeit", "", "", "static"));
            workTimeTable.changeCellChildElement(0, 1, new ReportElementDisplay("HH:mm", "", "", "static"));
            workTimeTable.changeCellChildElement(1, 0, new ReportElementTimeRange("", "", ""));
            workTimeTable.changeCellChildElement(1, 1, new ReportElementTime("", "", ""));
            this.addElement(workTimeTable);

            const successCheckTable = new ReportElementTable("", "", 2, 2);
            successCheckTable.label = "Erneute Anfahrt";
            successCheckTable.extendable = false;
            successCheckTable.changeCellChildElement(0, 0, new ReportElementDisplay("Erfolgreich", "", "", "static"));
            successCheckTable.changeCellChildElement(0, 1, new ReportElementDisplay("Erneute Anfahrt", "", "", "static"));
            successCheckTable.changeCellChildElement(1, 0, new ReportElementCheckbox("", "", ""));
            successCheckTable.changeCellChildElement(1, 1, new ReportElementCheckbox("", "", ""));
            this.addElement(successCheckTable);

    }



    deleteSelectedElement() {
        console.log("Delete Selected Element");
        if (!this.selectedElement) {
        console.log("No element selected");
            return;
        }

        const elementId = this.selectedElement._type === "td" || this.selectedElement._type === "th" ? this.selectedElement._id.split("-")[0]+"-"+this.selectedElement._id.split("-")[1] : this.selectedElement._id;
        console.log("Found elementId: " + elementId);
        console.log("Selected Element: " + JSON.stringify(this.selectedElement));
        // Find the index of the element with the given ID
        const index = this.elements.findIndex(element => element._id === elementId);
        console.log(this.elements);
        console.log("Index: " + index);
        // If the element is found, remove it from the array
        if (index !== -1) {
            this.elements.splice(index, 1);
        }

        // Update the positions of the remaining elements
        this.updateElementOrder();

        // Remove the element from the DOM
        const element = document.querySelector(`.sortable-item[data-element-id="${elementId}"]`);
        if (element) {
            console.log("Element found");
            element.remove();
        }

            // Clear the selected element
            this.selectedElement = null;
        }

    editSelectedElement(){
        console.log("Edit Selected Element");
        if (!this.selectedElement) {
            console.log("No element selected");
            return;
        }
        // Overwrite selectedElement with element returend by editElement from creator
        this.selectedElement = this.elementCreator.editElement(this.selectedElement);
        // Shit code. The creator calls generator methods when the add button is clicked. #FUTURE: Change this
    }

    handleClick(e) {
        console.log("Handle Click");

        const cell = e.target.closest("th", "td");
        if(cell){
            console.log("Table Cell Clicked", cell);
            this.selectElement(cell);
            return;
        }

        console.log("Fired" + JSON.stringify(e.target));
        const target = e.target.closest(".report-element");
        // If add element button is clicked then do not select any element and disable draggable timeout for current element
        if (e.target.id === "add-element-button-wrapper" && e.target.innerHTML === "+") {
            console.log("Add Element Button Clicked");
            if (this.selectedElement) {
                this.selectedElement.clicked = false;
                this.selectedElement = null;
            }
            this.elementCreator.showModal();
        } else if (target) {
        // If a report element has been clicked disable draggable from currently selectedElement and set the clicked on element as the new selectedElement.
        // Then remove the draggable timeout from the new selectedElement
            console.log("Element Clicked");
            if(this.selectedElement) {
                this.selectedElement.clicked = false;
                this.selectedElement = null;
            }
            this.selectElement(target);
            this.selectedElement.clicked = true;
            clearTimeout(this.selectedElement.handleBarTimeout);
            console.log("Selected" + JSON.stringify(this.selectedElement));
        } else {
            console.log("Nothing Clicked");
        // If no ReportElement has been clicked then disable draggable from currently selectedElement and remove that element
            if (this.selectedElement) {

                this.selectedElement.clicked = false;

                this.selectedElement = null;
            }

        }
    }



    handleGlobalClick(e) {
        console.log("Handle global Click");
        // Check if the click is inside the report container
        if (!this.reportContainer.contains(e.target) && !this.propertyPanelElement.contains(e.target) && document.getElementById("elementModal").style.display != "block" && !document.getElementById("elementModal").contains(e.target)) {
            // If clicked outside the report container, reset clicked state
            if (this.selectedElement) {
                this.selectedElement.clicked = false;
                this.selectedElement = null;

                console.log("Clicked outside ReportDesigner, resetting state.");
            }
        }else if(this.propertyPanelElement.contains(e.target)) {
            console.log("Clicked inside property panel");

        }else if(document.getElementById("preview") && document.getElementById("preview").contains(e.target)) {
            console.log("Clicked inside ElementModal");
        }else {
            console.log("Clicked inside ReportDesigner");
        }
    }


    // Iterate through elements array and find element with id of target
    selectElement(target) {
        // If the target is a table cell then search for the table
        if(target.tagName === "TD" || target.tagName === "TH") {

            const table = target.closest("table");
            const tableId = table.id;

            const tableObj = this.elements.find(element => element._id === tableId);

            this.selectedElement = tableObj.grid[target.id.split("-")[3]][target.id.split("-")[4]];

            //this.showProperties();
            return;
        }


        this.selectedElement = this.elements.find(element => element._id === target.id);
        //this.showProperties();
    }


    // Function to add a new ReportElement to the array
    addElement(element) {
        console.log("Element added");
        if (!(element instanceof ReportGeneratorElement)) {
            throw new TypeError("Element must be an instance of ReportGeneratorElement");
        }
        element.onDelete((element) => {
            this.selectedElement = element;
            this.deleteSelectedElement();
        });
        element.onEdit((element) => {
            this.selectedElement = element;
            this.editSelectedElement();
        });
        this.elements.push(element);
        element.pos = this.elements.length;
        // Sort elements by position after adding the new element
        this.elements.sort((a, b) => a.pos - b.pos);
        this.generateReport();
    }

    // Because the user will first add the div, then the elements that should be attached to the div, we need to use this function to do that
    attachElementToDiv(divPos, element) {
        if (!(element instanceof ReportGeneratorElement)) {
            throw new TypeError("Element must be an instance of ReportGeneratorElement");
        }
        for (let el of this.elements) {
            if (el instanceof ReportElementDiv && el.getPos() === divPos) {
                el.addElement(element);
                break;
            }
        }
    }

    // Function to delete an element from the array/from the form
    deleteElement(pos) {
        this.elements = this.elements.filter(element => element.getPos() !== pos);
        this.generateReport();
    }

    updateElementOrder() {
    const orderedIds = Array.from(document.querySelectorAll("#sortable-elements .sortable-item"))
        .map(item => item.getAttribute(\'data-element-id\'));  // Fetch data-element-id

    console.log("Ordered Ids:", orderedIds);  // Ensure IDs are logged correctly

    // Reorder this.elements based on the new order of IDs
    this.elements.sort((a, b) => orderedIds.indexOf(a._id) - orderedIds.indexOf(b._id));

    // // Reassign positions based on the new order
    // this.elements.forEach((element, index) => {
    //     element.pos = index + 1;
    // });

    console.log("Elements reordered:", this.elements);
}

  generateReport() {
    this.reportContainer.innerHTML = ""; // Clear previous contents
    // Create form so we can use php later
    const form = document.createElement("form");
    form.id = "report-form";
    form.style.height = "100%";


    // Create a div to wrap the sortable elements
    const sortableDiv = document.createElement("div");
    sortableDiv.id = "sortable-elements";
    sortableDiv.style.padding = "2rem";
    sortableDiv.style.border = "1px solid #ccc";
    sortableDiv.style.position = "relative";  // Ensures correct positioning context
    sortableDiv.style.display = "flex";
    sortableDiv.style.flexDirection = "column";
    sortableDiv.style.gap = "1rem";
    sortableDiv.style.height = "100%";

    // Iterate through every ReportElement object inside the array and render them
    // Note: The ReportElements themselves are objects. The render method returns an HTMLElement specified in the classes
    this.elements.forEach(element => {
        const elementNode = element.render();
        elementNode.classList.add("sortable-item"); // Add a class for easy identification
        elementNode.setAttribute(\'data-element-id\', element._id);
        sortableDiv.appendChild(elementNode);
    });



    // Add the sortableDiv to the form
    form.appendChild(sortableDiv);

    // Footer for button controls
    const footer = document.createElement("div");
    footer.style.display = "flex";
    footer.style.justifyContent = "center";
    footer.style.marginTop = "1rem";
    footer.style.gap = "1rem";


    // Add a div with a button inside that will initiate the process of adding a new element
    const addElementButtonWrapper = document.createElement("div");
    addElementButtonWrapper.style.display = "flex";
    addElementButtonWrapper.style.justifyContent = "center";
    addElementButtonWrapper.style.marginTop = "1rem";
    addElementButtonWrapper.id = "add-element-button-wrapper";
    addElementButtonWrapper.innerHTML = "+";
    addElementButtonWrapper.style.border = "2px solid #b3d9ff";
    addElementButtonWrapper.style.borderRadius = "5px";
    addElementButtonWrapper.style.padding = "1rem";
    addElementButtonWrapper.style.cursor = "pointer";
    footer.appendChild(addElementButtonWrapper);

    // Save button
    const saveFormButtonWrapper = document.createElement("div");
    saveFormButtonWrapper.style.display = "flex";
    saveFormButtonWrapper.style.justifyContent = "center";
    saveFormButtonWrapper.style.marginTop = "1rem";
    saveFormButtonWrapper.id = "save-form-button-wrapper";
    saveFormButtonWrapper.innerHTML = "Save";
    saveFormButtonWrapper.style.border = "2px solid #b3d9ff";
    saveFormButtonWrapper.style.borderRadius = "5px";
    saveFormButtonWrapper.style.padding = "1rem";
    saveFormButtonWrapper.style.cursor = "pointer";
    footer.appendChild(saveFormButtonWrapper);
    saveFormButtonWrapper.addEventListener("click", () => this.saveDesign());
    // saveFormButtonWrapper.addEventListener("click", () => this.saveTitleDescription());

    // Append the form to the report container
    this.reportContainer.appendChild(form);
    this.reportContainer.appendChild(footer);

    // Make the elements inside the sortableDiv sortable, using the handleBar for dragging
    $(sortableDiv).sortable({
        handle: ".handle-bar",  // Use the dragButton insdie handleBar for dragging
        axis: "y",  // Restrict movement to vertical
        containment: "parent",  // Restrict dragging to the parent container
        tolerance: "pointer",  // Use the pointer for tolerance to avoid overlap
        placeholder: "sortable-placeholder",  // CSS class to define the placeholder
        stop: (event, ui) => {
            this.updateElementOrder();  // Update the element order after sorting
        }
    });
}

    saveDesign() {
        const formData = new FormData();
        let parameters = [];

        // Collect all elements content, style, and id
        this.elements.forEach(element => {
            parameters.push(element.params);
        });

        // Append the HTML content and other parameters to formData

        //(#TODO: We may or may not need this...on one hand it is easier to generate the report for the technician on the other hand it creates storage overhead
        // and reconstructing the report from the parameters is not that hard but requires the js classes to be available which might be wrong, because
        // the reportGenerator is there to allow the design of reports and not the generation of them)

        // reportContainer without buttons:
        const addButton = this.reportContainer.querySelector("#add-element-button-wrapper");
        const saveButton = this.reportContainer.querySelector("#save-form-button-wrapper");
        if(addButton) addButton.remove();
        if(saveButton) saveButton.remove();
        formData.append("form", this.reportContainer.innerHTML);  // Full HTML
        formData.append("parameters", JSON.stringify(parameters));
        //formData.append("storeId", storeId);   // Assuming storeId, userId, etc. are available in the scope
        formData.append("userId", '.$user->id.');
        formData.append("description", document.getElementById("designDescription").value);
        formData.append("title", document.getElementById("designTitle").value);
        const reportId = '.$reportId.'+"";
        if(reportId != "" && reportId != "undefined") {
            formData.append("reportId", '.$reportId.');
        }
		// var projectid = '.$projectid.';
		// if(projectid != "" && projectid != "undefined") {
		// 	formData.append("projectid", '.$projectid.');
		// }

        //formData.append("ticketId", ticketId);
        //formData.append("socId", socId);
        if(addButton) this.reportContainer.appendChild(addButton);
        if(saveButton) this.reportContainer.appendChild(saveButton);
        // AJAX request to send the form data to the server
        $.ajax({
            url: "reportDesignerUpload.php",  // The PHP file that handles the saving
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log("Response: " + response);
                alert("Design saved successfully!");
            },
            error: function(xhr, status, error) {
                console.error("Request failed with status: " + xhr.status + ", Error: " + error);
                alert("Failed to save design. Please try again.");
            }
        });
    }

}

////////////////////////
// Jump: Utility classes
// Note: Not used yet, but can be used to create a new element type
////////////////////////

// utils/EventEmitter.js
class EventEmitter {
    constructor() {
        this.events = {};
    }

    on(event, listener) {
        if (!this.events[event]) this.events[event] = [];
        this.events[event].push(listener);
    }

    emit(event, ...args) {
        if (this.events[event]) {
            this.events[event].forEach(listener => listener(...args));
        }
    }

    off(event, listenerToRemove) {
        if (!this.events[event]) return;
        this.events[event] = this.events[event].filter(listener => listener !== listenerToRemove);
    }
}





////////////////////////
// Jump: ReportElementCreator.js
////////////////////////

// If you want to add a new element type, you need to create a new factory function and add it to the elements array.
// For example: A display is created by the createDisplayElementUI function. You can create a new function like this for a new element type.
// Then after creating the function, add a new object to the elements array with the value, label, and factory function (see constructor)

// Class definition for ReportElementCreator
class ReportElementCreator {
    constructor(reportGenerator, modalElement, configBox, addButton, closeButton) {
        this.reportGenerator = reportGenerator;  // Reference to the main ReportGenerator instance to be able to add elements to the report
        this.modal = modalElement;   // Reference to base modal element
        this.configBox = configBox;  // Reference to the configuration box within the modal -> mountSelectionUI()
        this.addButton = addButton;  // Reference to the "Add Element" button inside modal -> createFinalElement()
        this.closeButton = closeButton;  // Reference to the "Close" button inside modal -> hideModal()
        this.selection = null;       // Current selected option in modal -> handleSelection()
        this.preview = null;         // Reference to preview element in the modal -> mountPreview()
        this.mode = "create";        // Mode to determine if the modal is in create or edit mode

        this.elements = [
            { value: "display", label: "Anzeige", factory: this.createDisplayElementUI.bind(this) },
            { value: "input", label: "Input", factory: this.createInputElementUI.bind(this) },
            { value: "textarea", label: "Textfeld", factory: this.createTextareaElementUI.bind(this) },
            { value: "table", label: "Tabelle", factory: this.createTableElementUI.bind(this) },
            { value: "upload", label: "Upload", factory: this.createUploadElementUI.bind(this) },
            { value: "Signature", label: "Unterschrift", factory: this.createSignatureElementUI.bind(this) },
            // { value: "clock", label: "Uhrzeit", factory: this.createClockElementUI.bind(this) },
            // { value: "div", label: "Box", factory: this.createDivElementUI.bind(this) }
        ];
        // Testing, remove showModal() when finished
        //this.showModal();
        this.mountElementFactories();
        this.bindEvents();
    }

    // Mount factory functions for different report elements
    mountElementFactories() {
        this.elementFactories = {};
        this.elements.forEach(el => {
            this.elementFactories[el.value] = el.factory;
        });
    }

    // Bind click events to each of the buttons in the modal
    bindEvents() {
        const options = this.modal.querySelectorAll(".list-group-item");
        options.forEach((option) => {
            option.addEventListener("click", (e) => {
                this.handleSelection(e.target.getAttribute("data-value"));
            });
        });

        // Add element button handler
         this.addButton.addEventListener("click", () => {
            if (this.preview) {
                if(this.preview.type === "upload" && (this.preview.label === "" || this.preview.label === "undefined")) {
                    alert("Uploads müssen eine Überschrift haben, da dies der Dateiname ist.");
                }else if(this.mode === "create") {
                    // Delegate adding the element to the reportGenerator
                    this.reportGenerator.addElement(this.createFinalElement());
                    //this.reportGenerator.addElement(this.preview);
                    this.hideModal(); // Optionally hide modal after adding the element
                }else if(this.mode === "edit") {
                // Really bad code but no time. #FUTURE: Change this so that reportGenerator handles all of this
                    const elements = this.reportGenerator.elements;
                    const index = elements.findIndex(element => element._id === this.preview._id);
                    elements[index] = this.preview;
                    elements[index].clicked = false;
                    this.hideModal();
                    this.mode = "create";
                    this.reportGenerator.selectedElement = null;
                    this.reportGenerator.generateReport();
                }

            }
        });
        // Handle modal hiding when the user clicks outside the modal frame. This prevents the ReportElement from being stuck in the edit mode and undraggable
        $(this.modal).on(\'hidden.bs.modal\', () => {
            if(this.mode === "edit") {
                this.mode = "create";
                this.preview.clicked = false;
                this.reportGenerator.selectedElement = null;
            }
        });

        // Close button handler
        // For Muhannad: This preserves state of the modal. If you want to use custom modal (non bootstrap) you have to change hideModal and add lines to clear both ui state and class state
        //  this.selection = null;
        //  this.preview = null;
        this.closeButton.addEventListener("click", () => {
            this.hideModal();
        });
    }

     // Method to create the final element to add to the report
    createFinalElement() {
        this.preview.clicked = false; // Reset the clicked state so handleBar appears and you can drag and drop element
        return this.preview;
    }

    // Handles the selection of a new element to be created
    handleSelection(value) {
        this.selection = value;   // Save the selected value
        this.addButton.disabled = false;  // Enable the add button
        this.mountSelectionUI();  // Mount the corresponding configuration UI
    }

    editElement(element) {
        // Do not mound the selection list since we are editing an element
        this.mode = "edit";
        this.preview = element;
        this.handleSelection(element.type);
        const bootstrapModal = new bootstrap.Modal(this.modal);
        bootstrapModal.show();
    }

    // Mount selection-specific UI to the configuration box
    mountSelectionUI() {
        // Clear previous configuration content
        this.configBox.innerHTML = "";

        // Call the factory function to build UI for the selected type
        if (this.elementFactories[this.selection]) {
            this.elementFactories[this.selection](this.configBox);
        } else {
            console.error(`No handler found for selection: ${this.selection}`);
        }
    }

    // Mount the list column UI based on the elements array
    mountElementListUI() {
        const listContainer = this.modal.querySelector(".list-group");
        listContainer.innerHTML = ""; // Clear previous list items

        this.elements.forEach((el) => {
            const item = document.createElement("button");
            item.classList.add("list-group-item", "list-group-item-action");
            item.setAttribute("data-value", el.value);
            item.innerText = el.label;
            item.addEventListener("click", () => {
                this.handleSelection(el.value);
            });
            listContainer.appendChild(item);
        });
    }

    // Show modal on button click
    showModal() {
        const bootstrapModal = new bootstrap.Modal(this.modal);
        this.mountElementListUI();
        bootstrapModal.show();
    }

    // Hide modal on button click
    hideModal() {
        const bootstrapModal = bootstrap.Modal.getInstance(this.modal);
        bootstrapModal.hide();
    }


    // Method to mount the preview element in the configuration box
    mountPreview(container) {
        const previewContainer = document.createElement("div");
        previewContainer.classList.add("preview-container", "mt-3");
        previewContainer.innerHTML = "<strong>Vorschau:</strong>";
        previewContainer.appendChild(this.preview.render());

        // Remove any existing preview and append the new one
        const existingPreview = container.querySelector(".preview-container");
        if (existingPreview) {
            existingPreview.remove();
        }
        this.preview.clicked = true;
        container.appendChild(previewContainer);
    }

    // UI factory functions


    // Create UI for Display element
    createDisplayElementUI(container) {
        container.innerHTML = `
            <div class="mb-3">
                <input type="text" id="displayText" class="form-control" placeholder="Text eingeben">
                <div>oder</div>
                <select id="predefinedText" class="form-select">
                    <option value="default" selected>Dynamische Werte</option>
                    <option value="projectname">Projektname / Report</option>
                    <option value="ticketnumber">Ticketnummer</option>
                    <option value="currentDate">Aktuelles Datum</option>
                    <option value="creationDate">Erstellungsdatum</option>
                    <option value="time">Uhrzeit</option>
                    <option value="filial">Filiale</option>
                    <option value="severity">Dringlichkeit</option>
                    <option value="postalcode">Plz</option>
                    <option value="city">Ort</option>
                    <option value="street">Straße</option>
                    <option value="tickettype">Ticketart</option>
                    <option value="telephonenumber">Telefonnummer</option>
                    <option value="ticketdescription">Beschreibung / Auftrag</option>
                </select>
            </div>
        `;
        if(this.mode === "create") {
            this.preview = new ReportElementDisplay("Text eingeben", "font-size: 20px; font-weight: bold; width: 100%; text-align: center;");
        }


        this.mountPreview(container);

        // Event listener for input changes
        container.querySelector("#displayText").addEventListener("input", (e) => {
            this.preview.content = e.target.value;
            this.mountPreview(container);
        });

        // Event listener for predefined text selection
        container.querySelector("#predefinedText").addEventListener("change", (e) => {
            if (e.target.value !== "default") {
                document.getElementById("displayText").disabled = true;
                this.preview.content = e.target.options[e.target.selectedIndex].text;
                this.preview.contentType = "dynamic";
                this.mountPreview(container);
            }else{
                document.getElementById("displayText").disabled = false;
                this.preview.content = "Text eingeben";
                this.preview.contentType = "static";
                this.mountPreview(container);
            }
        });
    }

    // Create UI for Input element
    createInputElementUI(container) {
        container.innerHTML = `
            <div class="mb-3">
                <input type="text" id="inputLabel" class="form-control" placeholder="Überschrift angeben">
            </div>
        `;
        this.preview = new ReportElementInput("", "", "Überschrift angeben");
        this.mountPreview(container);

        // Event listener for input changes
        container.querySelector("#inputLabel").addEventListener("input", (e) => {
            this.preview.label = e.target.value;
            this.mountPreview(container);
        });
    }

    // Create UI for Textarea element
    createTextareaElementUI(container) {
        container.innerHTML = `
            <div class="mb-3">
                <input type="text" id="textareaLabel" class="form-control" placeholder="Überschrift angeben">
            </div>
        `;
        if(this.mode === "create") {
        this.preview = new ReportElementTextarea("", "", "Enter textarea label");
        }else if(this.mode === "edit") {
            document.getElementById("textareaLabel").value = this.preview.label;
        }

        this.mountPreview(container);

        // Event listener for input changes
        container.querySelector("#textareaLabel").addEventListener("input", (e) => {
            this.preview.label = e.target.value;
            this.mountPreview(container);
        });
    }

    // Create UI for Table element
    createTableElementUI(container) {
        this.configBox = container;
        container.innerHTML = `
            <div class="accordion" id="accordionExample">
    <!-- Table Label Section Accordion Item -->
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingLabel">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLabel" aria-expanded="true" aria-controls="collapseLabel">
                Überschrift Optionen
            </button>
        </h2>
        <div id="collapseLabel" class="accordion-collapse collapse show" aria-labelledby="headingLabel">
            <div class="accordion-body">
                <div class="mb-3" id="tableLabelSelection">
                    Tabellenüberschrift
                    <input type="text" id="tableLabel" class="form-control" value="Table">
                </div>
            </div>
        </div>
    </div>

    <!-- Table Structure Section Accordion Item -->
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingStructure">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStructure" aria-expanded="false" aria-controls="collapseStructure">
                Tabellenstruktur Optionen
            </button>
        </h2>
        <div id="collapseStructure" class="accordion-collapse collapse" aria-labelledby="headingStructure">
            <div class="accordion-body">
                <div class="mb-3 d-flex gap-1 flex-column" id="tableStructureSelection">
                    <div class="mb-3">
                        <label for="tableRows">Anzahl Zeilen</label>
                        <input type="number" id="tableRows" class="form-control" value="2">
                    </div>
                    <div class="mb-3">
                        <label for="tableCols">Anzahl Spalten</label>
                        <input type="number" id="tableCols" class="form-control" value="2">
                    </div>
                    <div class="mb-3">
                        <label for="extendableTable">Erweiterbare Tabelle</label>
                        <input type="checkbox" id="extendableTable" class="form-check-input">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Cell Section Accordion Item -->
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingCell">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCell" aria-expanded="false" aria-controls="collapseCell">
                Zellen Optionen
            </button>
        </h2>
        <div id="collapseCell" class="accordion-collapse collapse" aria-labelledby="headingCell">
            <div class="accordion-body">
                <div class="mb-3 d-flex gap-1 flex-column" id="tableCellContentSelection">
                    <label for="cellContent">Zelleninhalt</label>
                    <div class="mb-3">
                        <input type="text" id="cellContent" class="form-control" value="Cell">
                    </div>
                </div>
                <div class="mb-3 d-flex gap-1 flex-column" id="cellContentTypeSelection">
                    <label for="cellType">Zellentyp</label>
                    <div class="mb-3">
                        <select id="cellType" class="form-select">
                            <option value="display" selected>Text</option>
                            <option value="input">Input</option>
                            <option value="textarea">Textarea</option>
                            <option value="upload">Upload</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="radio">Radio</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3" id="radioGroup" style="display: none;">
                    <label for="radioGroup">Radio Gruppe</label>
                    <i class="bi bi-question-circle" data-bs-toggle="popover" data-bs-content="Bei Buttons, die einer Gruppe angehören, kann immer nur ein Button angeklickt sein" data-bs-placement="right"></i>
                    <input type="text" id="radioGroupInput" class="form-control" value="Gruppe1">
                    <select id="prevGroup" class="form-select" style="display: none;">
                    </select>
                </div>
                <div class="mb-3" id="displayType">
                    <select id="predefinedText" class="form-select">
                        <option value="default" selected>Dynamische Werte</option>
                        <option value="projectname">Projektname / Report</option>
                        <option value="ticketnumber">Ticketnummer</option>
                        <option value="currentDate">Aktuelles Datum</option>
                        <option value="creationDate">Erstellungsdatum</option>
                        <option value="time">Uhrzeit</option>
                        <option value="filial">Filiale</option>
                        <option value="severity">Dringlichkeit</option>
                        <option value="postalcode">Plz</option>
                        <option value="city">Stadt</option>
                        <option value="street">Straße</option>
                        <option value="tickettype">Ticketart</option>
                        <option value="telephonenumber">Telefonnummer</option>
                        <option value="ticketdescription">Beschreibung / Auftrag</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

        `;
        
        // Create or reuse the preview table
        if (this.mode === "create") {
            this.preview = new ReportElementTable();
        }
        // If editing, presumably we already have "this.preview" as a ReportElementTable
        this.preview.onActiveCellChange = this.handleActiveCellChange.bind(this);

        // Set a default or existing active cell
        if (!this.preview._activeCell) {
            this.preview._activeCell = this.preview._grid[0][0];
        }
        this.handleActiveCellChange(this.preview._activeCell);

        // Show/hide the dynamic text or radio group sections as needed
        if (this.preview._activeCell.contentElement.type === "display") {
            container.querySelector("#displayType").style.display = "block";
            container.querySelector("#radioGroup").style.display = "none";
        } else if (this.preview._activeCell.contentElement.type === "radio") {
            container.querySelector("#radioGroup").style.display = "block";
            container.querySelector("#displayType").style.display = "none";
        } else {
            container.querySelector("#displayType").style.display = "none";
            container.querySelector("#radioGroup").style.display = "none";
        }

        // If edit mode, populate the fields
        if (this.mode === "edit") {
            container.querySelector("#tableLabel").value = this.preview.label;
            container.querySelector("#tableRows").value = this.preview.rows;
            container.querySelector("#tableCols").value = this.preview.cols;
            container.querySelector("#extendableTable").checked = this.preview.extendable;
        }

        // Finally, show the table in the preview
        this.mountPreview(container);

        // --- Event listeners ---

        // Update rows
        container.querySelector("#tableRows").addEventListener("change", (e) => {
            this.preview.rows = +e.target.value;
            this.mountPreview(container);
        });

        // Update columns
        container.querySelector("#tableCols").addEventListener("change", (e) => {
            this.preview.cols = +e.target.value;
            this.mountPreview(container);
        });

        // Update table label
        container.querySelector("#tableLabel").addEventListener("input", (e) => {
            this.preview.label = e.target.value;
            this.mountPreview(container);
        });

        // Extendable?
        container.querySelector("#extendableTable").addEventListener("change", (e) => {
            this.preview.extendable = e.target.checked;
            this.mountPreview(container);
        });

        // Cell text input
        container.querySelector("#cellContent").addEventListener("input", (e) => {
            if (this.preview._activeCell) {
                this.preview._activeCell.contentElement.content = e.target.value;
                this.preview.initializeContent(); // keep internal data updated
                this.mountPreview(container);
            }
        });

        // Cell element type
        container.querySelector("#cellType").addEventListener("change", (e) => {
            if (this.preview._activeCell) {
                // CHANGED: Instead of changeContentType(...), we create a new sub-element.
                const newType = e.target.value;
                let newEl;

                // Create the appropriate element
                switch (newType) {
                    case "display":
                        newEl = new ReportElementDisplay("", "", "", "static");
                        break;
                    case "input":
                        newEl = new ReportElementInput("", "", "Input Label");
                        break;
                    case "textarea":
                        newEl = new ReportElementTextarea("", "", "Textarea Label");
                        break;
                    case "upload":
                        newEl = new ReportElementUpload("", "", "Upload Label");
                        break;
                    case "checkbox":
                        newEl = new ReportElementCheckbox("", "", "Checkbox Label");
                        break;
                    case "radio":
                        newEl = new ReportElementRadio("", "", "Radio Label", "Gruppe1");
                        break;
                    default:
                        console.error("Unknown cellType:", newType);
                        return;
                }

                // Replace the sub-element in the active cell
                this.preview._activeCell.contentElement = newEl;
                // Optionally clear text input
                container.querySelector("#cellContent").value = "";
                // Show/hide the dynamic or radio UI
                if (newType === "display") {
                    container.querySelector("#displayType").style.display = "block";
                    container.querySelector("#radioGroup").style.display = "none";
                    container.querySelector("#cellContent").disabled = false;
                } else if (newType === "radio") {
                    container.querySelector("#radioGroup").style.display = "block";
                    container.querySelector("#displayType").style.display = "none";
                    container.querySelector("#cellContent").disabled = true;
                } else {
                    container.querySelector("#displayType").style.display = "none";
                    container.querySelector("#radioGroup").style.display = "none";
                    container.querySelector("#cellContent").disabled = false;
                }

                // Re-sync data
                this.preview.initializeContent();
                this.mountPreview(container);
            }
        });

        // Predefined text selection for display
        container.querySelector("#predefinedText").addEventListener("change", (e) => {
            if (this.preview._activeCell && this.preview._activeCell.contentElement.type === "display") {
                const selectedVal = e.target.value;
                const selectedText = e.target.options[e.target.selectedIndex].text;
                if (selectedVal !== "default") {
                    // Make it dynamic
                    this.preview._activeCell.contentElement.content = selectedText;
                    this.preview._activeCell.contentElement.contentType = "dynamic";
                    container.querySelector("#cellContent").disabled = true;
                } else {
                    // Switch back to static
                    this.preview._activeCell.contentElement.content = "Cell";
                    this.preview._activeCell.contentElement.contentType = "static";
                    container.querySelector("#cellContent").disabled = false;
                    container.querySelector("#cellContent").value = "Cell";
                }
                this.preview.initializeContent();
                this.mountPreview(container);
            }
        });

        // Radio group name
        container.querySelector("#radioGroup").addEventListener("input", (e) => {
            if (this.preview._activeCell && this.preview._activeCell.contentElement.type === "radio") {
                this.preview._activeCell.contentElement.group = e.target.value;
                this.preview.initializeContent();
                this.mountPreview(container);
            }
        });

        // Initialize popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll(\'[data-bs-toggle="popover"]\'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }

    // Called by the table whenever the user clicks a different cell
    handleActiveCellChange(activeCell) {
        console.log(`Active cell changed to: ${activeCell.id}`);

        const cellContentType = this.configBox.querySelector("#cellType");
        const cellContentInput = this.configBox.querySelector("#cellContent");

        if (cellContentType && cellContentInput) {
            // Update dropdown to match the sub-elements "type"
            cellContentType.value = activeCell.contentElement.type;
            // Update text input
            cellContentInput.value = activeCell.contentElement.content || "";

            // Show/hide dynamic or radio
            if (activeCell.contentElement.type === "display") {
                this.configBox.querySelector("#displayType").style.display = "block";
                this.configBox.querySelector("#radioGroup").style.display = "none";
                cellContentInput.disabled = false;
            } else if (activeCell.contentElement.type === "radio") {
                this.configBox.querySelector("#radioGroup").style.display = "block";
                this.configBox.querySelector("#displayType").style.display = "none";
                cellContentInput.disabled = true;
            } else {
                this.configBox.querySelector("#displayType").style.display = "none";
                this.configBox.querySelector("#radioGroup").style.display = "none";
                cellContentInput.disabled = false;
            }
        } else {
            console.error("UI elements not found for cell editing.");
        }
    }



    // Create UI for Upload element
    createUploadElementUI(container) {
    container.innerHTML = `
        <div class="mb-3">
            <label for="uploadLabel">Uploader Label</label>
            <input type="text" id="uploadLabel" class="form-control" placeholder="Enter uploader label">
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" id="toggleMultiple">
            <label class="form-check-label" for="toggleMultiple">Mehrere Dateien erlauben</label>
        </div>
        <div class="mb-3 form-check">
            <label for="fileType">Dateityp</label>
            <select id="fileType" class="form-select">
                <option value="img">Bilder</option>
            </select>
        </div>
    `;

    // Create a preview instance
    this.preview = new ReportElementUpload();

     // Get references to the input elements
    const uploadLabelInput = container.querySelector("#uploadLabel");
    //const includeSelectCheckbox = container.querySelector("#includeSelect");
    const toggleMultipleCheckbox = container.querySelector("#toggleMultiple");
    const fileTypeSelect = container.querySelector("#fileType");

    // Event listeners for input changes
    uploadLabelInput.addEventListener("input", (e) => {
        this.preview.label = e.target.value;
        this.mountPreview(container);
    });

    toggleMultipleCheckbox.addEventListener("change", (e) => {
        this.preview.multiple = e.target.checked;
        this.mountPreview(container);
    });

    fileTypeSelect.addEventListener("change", (e) => {
        const fileType = e.target.value;
        switch (fileType) {
            case \'all\':
                this.preview.accept = \'*/*\';
                break;
            case \'pdf\':
                this.preview.accept = \'application/pdf\';
                break;
            // case \'doc\':
            //     this.preview.accept = \'.doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document\';
            //     break;
            case \'img\':
                this.preview.accept = \'image/*\';
                break;
        }
        this.mountPreview(container);
    });


    // Mount the preview
    this.mountPreview(container);


}


    // Create UI for Clock element
    createClockElementUI(container) {
        container.innerHTML = `
            <div class="mb-3">
                <input type="text" id="clockLabel" class="form-control" placeholder="Überschrift angeben">
            </div>
        `;
        this.preview = new ReportElementClock("", "");
        this.mountPreview(container);

        // Event listener for input changes
        container.querySelector("#clockLabel").addEventListener("input", (e) => {
            this.preview.label = e.target.value;
            this.mountPreview(container);
        });
    }

    createSignatureElementUI(container){
        container.innerHTML = `
            <div class="mb-3">
                <input type="text" id="signatureLabel" class="form-control" placeholder="Überschrift angeben">
            </div>
        `;
        this.preview = new ReportElementSignature("", "");
        document.getElementById("signatureLabel").addEventListener("input", (e) => {
            this.preview.label = e.target.value;
            this.mountPreview(container);
        });
        this.mountPreview(container);
    }

}





////////////////////////
// Jump: ReportGeneratorElement.js
////////////////////////

class ReportGeneratorElement {
    // Static property to keep track of the next ID
    static nextId = 0;
    constructor(content = "", style = "", type, label = "") {
        if (new.target === ReportGeneratorElement) {
            throw new TypeError("Cannot construct Abstract instances directly");
        }
        this._content = content;
        this._style = style !== "" ? style : "";
        this._pos = 0;  // Default position
        this._clicked = false;
        this._type = type;
        // If name of constructor Reportelementcell then do not increment
        this._id = `${type}-${ReportGeneratorElement.nextId++}`;
        this._label = label || type.charAt(0).toUpperCase() + type.slice(1);  // Default to type if no label provided
        // Callbacks for deleting and editing
        this.deleteCallback = null;
        this.editCallback = null;
    }

    // Getters and setters for label
    get label() {
        return this._label;
    }

    set label(newLabel) {
        this._label = newLabel;
        if (this._labelElement) { // If label element exists, update its text
            this._labelElement.innerText = this._label;

        }
    }

    // Getters and setters for type
    get type(){
        return this._type;
    }

    // Getters and setters for content
    get content() {
        return this._content;
    }

    set content(newContent) {
        this._content = newContent;
        if (this._contentElement) {  // If content element exists, update its text
            this._contentElement.innerText = this._content;
        }
    }

    get pos() {
        return this._pos;
    }

    set pos(newPos) {
        this._pos = newPos;

    }

    get id() {
        return this._id;
    }

    get styles() {
        return this._style;
    }

    set styles(newStyle) {
        this._style = newStyle;
    }

    get clicked() {
        return this._clicked;
    }

    set clicked(value) {
        this._clicked = value;
    }

    set id(newId) {
        this._id = newId;
    }

    onDelete(callback) {
        this.deleteCallback = callback;
    }

    onEdit(callback) {
        this.editCallback = callback;
    }

    get params(){

        const baseParams = {
            id: this._id,
            content: this.content,
            style: this._style,
            type: this._type,
            label: this._label
        }

        if(this instanceof ReportElementCell || this instanceof ReportElementDisplay){
            baseParams.contentType = this._contentType;
        }

        return baseParams;
    }

    createLabel() {
        const labelElement = document.createElement("label");
        labelElement.classList.add("report-element-label");
        labelElement.innerText = this._label;  // Use the label attribute
        labelElement.style.fontSize = "1rem";
        // Remove absolute positioning
        // labelElement.style.position = "absolute";
        // labelElement.style.top = "0";
        // labelElement.style.left = "0";
        labelElement.style.padding = "2px 5px";
        labelElement.style.borderRadius = "3px";
        labelElement.style.zIndex = "10";  // Optional: adjust as needed
        if(this._type === "display"){
            labelElement.style.display = "none";
        }
        return labelElement;
    }

    handleBar() {
        // const handleBar = document.createElement("div");
        // handleBar.classList.add("handle-bar");
        // handleBar.style.display = "none";  // Initially hidden
        // handleBar.innerHTML = "&#x21C5;";  // Use a hamburger icon (or any icon)
        // handleBar.style.cursor = "move";  // Cursor style to indicate draggable handle
        // handleBar.style.padding = "5px";
        // handleBar.style.backgroundColor = "rgba(224, 224, 224, 0.5)";
        // handleBar.style.borderRight = "1px solid #ccc";
        // handleBar.style.width = "100%";
        // handleBar.style.height = "100%";
        // handleBar.style.textAlign = "center";

        // return handleBar;

        const handleBar = document.createElement("div");
        handleBar.classList.add("handle-bar");
        handleBar.style.display = "none";  // Initially hidden
        handleBar.style.cursor = "move";  // Cursor style for the handle area
        handleBar.style.padding = "5px";
        handleBar.style.backgroundColor = "rgba(224, 224, 224, 0.5)";
        handleBar.style.borderRight = "1px solid #ccc";
        handleBar.style.width = "100%";
        handleBar.style.height = "100%";
        handleBar.style.textAlign = "center";
        handleBar.style.alignItems = "center";

        // Drag Button - this will act as the draggable handle
        const dragButton = document.createElement("div");
        dragButton.classList.add("drag-button"); // You can style this in CSS as needed
        dragButton.style.cursor = "move";
        dragButton.innerHTML = "&#x21C5;";  // Icon or symbol to represent dragging
        dragButton.style.marginRight = "auto";
        handleBar.appendChild(dragButton);

        // Edit Button
        const editButton = document.createElement("button");
        editButton.classList.add("btn", "btn-sm", "btn-primary", "me-1");
        editButton.innerHTML = "<i class=\'bi bi-pencil\'></i>";
        handleBar.appendChild(editButton);
        editButton.addEventListener(\'click\', () => {
            event.preventDefault();
            this.editCallback(this); // Call the edit callback with the current element
        });

        // Delete Button
        const deleteButton = document.createElement("button");
        deleteButton.classList.add("btn", "btn-sm", "btn-danger");
        deleteButton.innerHTML = "<i class=\'bi bi-trash\'></i>";
        deleteButton.addEventListener(\'click\', () => {
            event.preventDefault();
            this.deleteCallback(this); // Call the delete callback with the current element
        });
        handleBar.appendChild(deleteButton);

        return handleBar;
    }

    createWrapperDiv() {
        const wrapperDiv = document.createElement("div");
        wrapperDiv.classList.add("report-element-wrapper", "sortable-item", "d-flex", "flex-column", "gap-1"); // Class for styling and sorting (JQuery)
        wrapperDiv.style.position = "relative";  // Make the wrapper a positioning context
        // Create and append the label
        const label = this.createLabel();
        wrapperDiv.appendChild(label);

        const handleBar = this.handleBar();  // Calling the handleBar method from parent class
        wrapperDiv.appendChild(handleBar);  // Append the handleBar to the wrapperDiv


        wrapperDiv.addEventListener("mouseenter", () => this.detectMouseEnter(wrapperDiv, handleBar));
        wrapperDiv.addEventListener("mouseleave", () => this.detectMouseLeave(wrapperDiv, handleBar));


        return wrapperDiv;
    }

     detectMouseEnter(wrapperDiv, handleBar) {
        if (!this._clicked) {
            this.handleBarTimeout = setTimeout(() => {
                handleBar.style.display = "flex";  // Show handleBar
                wrapperDiv.style.cursor = "move";  // Change cursor to move
                handleBar.style.justifyContent = "center";
                handleBar.style.alignItems = "center";
            }, 1000);  // Show after 1 second
        }
    }

    detectMouseLeave(wrapperDiv, handleBar) {
        clearTimeout(this.handleBarTimeout);  // Clear any pending timeout
        handleBar.style.display = "none";  // Hide the handleBar
        wrapperDiv.style.cursor = "default";  // Reset cursor to default
    }

    // Abstract method to append content - must be implemented by subclasses
    appendContent(wrapperDiv) {
        throw new Error("appendContent method must be implemented by subclass");
    }

    // General render method to be called by subclasses
    render() {
        const wrapperDiv = this.createWrapperDiv();
        this.appendContent(wrapperDiv);  // Append specific content

        return wrapperDiv;
    }


    renderContentOnly() {
        // Use the existing render method to get the full content
        const fullContent = this.render();

        // Clone the content to avoid modifying the original elements
        const contentClone = fullContent.cloneNode(true);

        // Remove the handleBar from the content
        const handleBar = contentClone.querySelector(\'.handle-bar\');
        if (handleBar) {
            handleBar.remove();
        }

        // Remove the label element if it exists
        const labelElement = contentClone.querySelector(\'.report-element-label\');
        if (labelElement) {
            labelElement.remove();
        }

        // Extract and return the inner content
        // Assuming the wrapperDiv now contains only the content element
        const contentElement = contentClone.firstElementChild;
        return contentElement;
    }

}


////////////////////////
// Jump: ReportElementDisplay.js
////////////////////////

class ReportElementDisplay extends ReportGeneratorElement {
    constructor(content = "", style = "", label = "", contentType = "static") {
        super(content, style, "display", label);
        //We need this to declare if a display is static or dynamic. Static means it just displays whatever has been set in the editor.
        // Dynamic means it displays a value that is set by the server for example filianumber
        this.contentType = contentType;
    }

    get contentType() {
        return this._contentType;
    }

    set contentType(newType) {
        this._contentType = newType;
    }



   appendContent(wrapperDiv) {
        wrapperDiv.style.textAlign = "center";
        wrapperDiv.style.justifyContent = "center";
        wrapperDiv.style.backgroundColor = "#f2f2f2";
        wrapperDiv.style.padding = "1rem";
        const contentDiv = document.createElement("div");
        contentDiv.id = this._id;
        contentDiv.classList.add("report-element");
        contentDiv.style.cssText = this._style;
        contentDiv.innerHTML = this._content;
        contentDiv.style.fontSize = "16px";
        // Add data-content-type attribute
        contentDiv.setAttribute("data-content-type", this._contentType);

        // Append the contentDiv to the wrapperDiv
        wrapperDiv.appendChild(contentDiv);
    }

    get params(){
        const baseParams = super.params;
        baseParams.contentType = this._contentType;
        return baseParams;
    }
}


////////////////////////
// Jump: ReportElementDiv.js
////////////////////////

class ReportElementDiv extends ReportGeneratorElement {
    constructor(content = "", style = "") {
        super(content, style, "div", label);
        this._elements = [];
    }

    addElement(element) {
        if (!(element instanceof ReportGeneratorElement)) {
            throw new TypeError("Element must be an instance of ReportGeneratorElement");
        }
        this._elements.push(element);
    }

    appendContent(wrapperDiv) {
        const div = document.createElement("div");
        div.id = this._id;
        div.classList.add("report-element");
        div.style.cssText = this._style;

        // Append each sub-element to the div
        this._elements.forEach(element => {
            div.appendChild(element.render());
        });

        // Append the div to the wrapperDiv
        wrapperDiv.appendChild(div);
    }
}


////////////////////////
// Jump: ReportElementTextarea.js (concrete class)
////////////////////////

class ReportElementTextarea extends ReportGeneratorElement {
    constructor(content = "", style = "", label = "") {
        super(content, style, "textarea", label);
    }

    appendContent(wrapperDiv) {
        const textarea = document.createElement("textarea");
        textarea.id = this._id;
        textarea.classList.add("form-control", "report-element");
        textarea.style.cssText = this._style + " resize: none;";
        textarea.value = this._content;
        textarea.maxLength = 255;
        textarea.addEventListener("input", (e) => {
            this._content = e.target.value;
        });
        // Append the textarea to the wrapperDiv
        wrapperDiv.appendChild(textarea);
    }
}


////////////////////////
// Jump: ReportElementTable.js
////////////////////////

class ReportElementTable extends ReportGeneratorElement {
    constructor(content = null, style = "", rows = 2, cols = 2, label = "") {
        super("", style, "table", label);
        this._rows = Math.max(rows, 1);
        this._cols = Math.max(cols, 1);
        this._grid = [];
        this._content = [];
        this._activeCell = null;
        this._onActiveCellChange = null;
        this._extendable = false;

        console.log("Received id: " + this._id);

        // If we have content, it means we are re-loading from saved data
        if (content) {
            this._content = content;
            this.initializeGridFromContent();
        } else {
            // Initialize an empty table with default display cells
            this.initializeGrid();
            // Then fill _content from that initial grid
            this.initializeContent();
        }
    }

    // 1) Build the “_grid” array from scratch (no data loaded)
    initializeGrid() {
        const headerRow = [];
        for (let i = 0; i < this._cols; i++) {
            const id = `${this._id}-cell-0-${i}`;
            console.log("Passing id: " + id);

            // By default, each header cell is a "display" type
            const displayEl = new ReportElementDisplay("Überschrift", "", "", "static");
            const newCell = new ReportElementCell(displayEl, "th", id);
            headerRow.push(newCell);
        }
        this._grid.push(headerRow);

        for (let rowIndex = 1; rowIndex < this._rows; rowIndex++) {
            const row = [];
            for (let colIndex = 0; colIndex < this._cols; colIndex++) {
                const id = `${this._id}-cell-${rowIndex}-${colIndex}`;
                console.log("Passing id: " + id);

                const displayEl = new ReportElementDisplay("", "", "", "static");
                const newCell = new ReportElementCell(displayEl, "td", id);
                row.push(newCell);
            }
            this._grid.push(row);
        }
    }

    // 2) Convert the _grid’s cells into a simple data structure
    //    that can be saved or re-used for re-init
    initializeContent() {
        this._content = this._grid.map(row =>
            row.map(cell => {
                return {
                    // top-level cell info
                    id: cell.id,
                    cellType: cell.cellType,
                    // sub-element info => merged from the sub-element’s .params
                    ...cell.contentElement.params
                };
            })
        );
    }

    // 3) If we already have _content, build the actual _grid from it.
    //    For each cellData, we re-create the sub-element (like display or input).
    initializeGridFromContent() {
        this._grid = this._content.map((row, rowIndex) => {
            return row.map(cellData => {
                // Step 1: Re-create the sub-element from cellData.type, etc.
                const element = this.createElementFromParams(cellData);

                // Step 2: Construct the Cell
                // default to "th" in row 0, else "td"
                const forcedType = (rowIndex === 0 ? "th" : "td");
                const cellType = cellData.cellType || forcedType;

                const cell = new ReportElementCell(element, cellType, cellData.id);
                return cell;
            });
        });
    }

    // Helper that picks the correct sub-element class (Display, Input, etc.)
    createElementFromParams(cellData) {
        switch (cellData.type) {
            case "display":
                return new ReportElementDisplay(
                    cellData.content,
                    cellData.style || "",
                    cellData.label || "",
                    cellData.contentType || "static"
                );
            case "input":
                return new ReportElementInput(
                    cellData.content || "",
                    cellData.style || "",
                    cellData.label || ""
                );
            case "textarea":
                return new ReportElementTextarea(
                    cellData.content || "",
                    cellData.style || "",
                    cellData.label || ""
                );
            case "timerange":
                return new ReportElementTimeRange(
                    cellData.content || "",
                    cellData.style || "",
                    cellData.label || ""
                );
            case "upload":
                return new ReportElementUpload(
                    cellData.content || "",
                    cellData.style || "",
                    cellData.label || ""
                );
            case "checkbox":
                return new ReportElementCheckbox(
                    cellData.content || "",
                    cellData.style || "",
                    cellData.label || ""
                );
            case "radio":
                return new ReportElementRadio(
                    cellData.content || "",
                    cellData.style || "",
                    cellData.label || "",
                    cellData.group || "Gruppe1"
                );
            case "time":
                return new ReportElementTime(
                    cellData.content || "",
                    cellData.style || "",
                    cellData.label || ""
                );
            default:
                throw new Error("Unsupported cellData.type: " + cellData.type);
        }
    }

    // Simple getters/setters
    get grid() {
        return this._grid;
    }

    get rows() {
        return this._rows;
    }

    set rows(newRows) {
        if (newRows < 1) newRows = 1;

        if (newRows > this._rows) {
            // Add new rows
            for (let i = this._rows; i < newRows; i++) {
                const newRow = [];
                for (let j = 0; j < this._cols; j++) {
                    const id = `${this._id}-cell-${i}-${j}`;
                    // default new cell is a display cell
                    const displayEl = new ReportElementDisplay("", "", "", "static");
                    const newCell = new ReportElementCell(displayEl, "td", id);
                    newRow.push(newCell);
                }
                this._grid.push(newRow);
            }
        } else if (newRows < this._rows) {
            // Remove extra rows
            this._grid = this._grid.slice(0, newRows);
        }
        this._rows = newRows;
        // Update _content to match
        this.initializeContent();
    }

    get cols() {
        return this._cols;
    }

    get extendable() {
        return this._extendable;
    }

    set extendable(value) {
        this._extendable = value;
    }

    set cols(newCols) {
        if (newCols < 1) newCols = 1;

        if (newCols > this._cols) {
            // Add new columns
            for (let colIndex = this._cols; colIndex < newCols; colIndex++) {
                // For row 0, use "th"
                const headerId = `${this._id}-cell-0-${colIndex}`;
                const headerEl = new ReportElementDisplay("Überschrift", "", "", "static");
                const headerCell = new ReportElementCell(headerEl, "th", headerId);
                this._grid[0].push(headerCell);

                // For subsequent rows, use "td"
                for (let rowIndex = 1; rowIndex < this._rows; rowIndex++) {
                    const id = `${this._id}-cell-${rowIndex}-${colIndex}`;
                    const displayEl = new ReportElementDisplay("", "", "", "static");
                    const newCell = new ReportElementCell(displayEl, "td", id);
                    this._grid[rowIndex].push(newCell);
                }
            }
        } else if (newCols < this._cols) {
            // Remove columns
            for (let rowIndex = 0; rowIndex < this._rows; rowIndex++) {
                this._grid[rowIndex] = this._grid[rowIndex].slice(0, newCols);
            }
        }
        this._cols = newCols;
        // Update the stored content to match
        this.initializeContent();
    }

    // Append the <table> with all <tr> / <th> / <td>
    appendContent(wrapperDiv) {
        const table = document.createElement("table");
        table.classList.add("table", "table-bordered", "report-element");
        if (this._extendable) {
            table.setAttribute("data-extendable", "true");
        }
        table.id = this._id;

        const thead = document.createElement("thead");
        const headerRow = document.createElement("tr");
        for (let j = 0; j < this._cols; j++) {
            const cellElement = this._grid[0][j].render();
            cellElement.addEventListener("click", () => this.handleCellClick(0, j));
            headerRow.appendChild(cellElement);
        }
        thead.appendChild(headerRow);

        const tbody = document.createElement("tbody");
        for (let i = 1; i < this._rows; i++) {
            const tr = document.createElement("tr");
            for (let j = 0; j < this._cols; j++) {
                const cellElement = this._grid[i][j].render();
                cellElement.addEventListener("click", () => this.handleCellClick(i, j));
                tr.appendChild(cellElement);
            }
            tbody.appendChild(tr);
        }

        table.appendChild(thead);
        table.appendChild(tbody);
        wrapperDiv.appendChild(table);
    }

    // Example: dynamic row addition
    addRow() {
        const newRowIndex = this._rows;  // index of next row
        const newRow = [];
        for (let j = 0; j < this._cols; j++) {
            const id = `${this._id}-cell-${newRowIndex}-${j}`;
            const displayEl = new ReportElementDisplay("New Cell", "", "", "static");
            const newCell = new ReportElementCell(displayEl, "td", id);
            newRow.push(newCell);
        }
        this._grid.push(newRow);
        this._rows++;
        this.initializeContent();
        this.render();
    }

    addColumn() {
        const newColIndex = this._cols;

        // Add to row 0 => a header cell
        const headerId = `${this._id}-cell-0-${newColIndex}`;
        const headerEl = new ReportElementDisplay("New Header", "", "", "static");
        const headerCell = new ReportElementCell(headerEl, "th", headerId);
        this._grid[0].push(headerCell);

        // Add to each subsequent row => normal "td"
        for (let i = 1; i < this._rows; i++) {
            const id = `${this._id}-cell-${i}-${newColIndex}`;
            const displayEl = new ReportElementDisplay("New Cell", "", "", "static");
            const newCell = new ReportElementCell(displayEl, "td", id);
            this._grid[i].push(newCell);
        }
        this._cols++;
        this.initializeContent();
        this.render();
    }

    removeRow() {
        if (this._rows > 1) {
            this._grid.pop();
            this._rows--;
            this.initializeContent();
            this.render();
        }
    }

    removeColumn() {
        if (this._cols > 1) {
            for (let i = 0; i < this._rows; i++) {
                this._grid[i].pop();
            }
            this._cols--;
            this.initializeContent();
            this.render();
        }
    }

    getCell(row, col) {
        if (row < 0 || row >= this._rows || col < 0 || col >= this._cols) {
            throw new Error("Invalid row or column index");
        }
        return this._grid[row][col];
    }

    changeCellChildElement(row, col, element) {
        const cell = this.getCell(row, col);

        if (typeof element === "string") {
            // “element” is actually a new contentType => must re-create from scratch
            // e.g. cell.changeContentType("display", { contentType: "dynamic" })
            throw new Error("Not implemented in this version. Use a sub-element directly.");
        } else if (element instanceof ReportGeneratorElement) {
            cell.contentElement = element;
        } else {
            throw new Error("Invalid element parameter. Must be a contentType string or a ReportGeneratorElement instance");
        }

        // Refresh stored content
        this.initializeContent();
    }

    changeCellType(row, col, type) {
        if (type !== "th" && type !== "td") {
            throw new Error("Invalid cell type. Must be either \'th\' or \'td\'");
        }
        const cell = this.getCell(row, col);
        cell.cellType = type;

        // Refresh stored content
        this.initializeContent();
    }

    changeCellChildContent(row, col, newContent) {
        const cell = this.getCell(row, col);
        cell.contentElement.content = newContent;

        // Refresh stored content
        this.initializeContent();
    }

    handleCellClick(row, col) {
        const cell = this._grid[row][col];
        console.log("Clicked cell:", cell.id);

        // Remove highlight from the previous active cell
        if (this._activeCell && this._activeCell !== cell) {
            this._activeCell.element.classList.remove("active-cell");
        }
        // Set new active cell
        this._activeCell = cell;
        this._activeCell.element.classList.add("active-cell");

        if (typeof this.onActiveCellChange === "function") {
            this.onActiveCellChange(this._activeCell);
        }
    }
}




class ReportElementCell {
    /**
     * Instead of (contentType, contentData, id), we pass in
     * the actual ReportGeneratorElement instance plus cellType + id.
     */
    constructor(contentElement, cellType = "td", id) {
        this._id = id;              // Unique identifier for the cell
        this._cellType = cellType;  // "td" or "th"
        this._contentElement = contentElement;

        // Validate the content element
        if (!(this._contentElement instanceof ReportGeneratorElement)) {
            throw new Error("ContentElement of Cell must be an instance of ReportGeneratorElement");
        }
    }

    get contentElement() {
        return this._contentElement;
    }

    changeContentType(newType, contentData = {}) {
        const newElement = this.createElementFromParams({
            type: newType, 
            // plus any relevant fields from contentData
            ...contentData
        });
        this.contentElement = newElement;
    }


    set contentElement(newElement) {
        if (newElement instanceof ReportGeneratorElement) {
            this._contentElement = newElement;
        } else {
            throw new Error("Cell contentElement must be a ReportGeneratorElement");
        }
    }

    get id() {
        return this._id;
    }

    get cellType() {
        return this._cellType;
    }

    set cellType(newType) {
        if (newType !== "td" && newType !== "th") {
            throw new Error(`Unsupported cell type: ${newType}`);
        }
        this._cellType = newType;
    }

    /**
     * Renders the <th> or <td>, then calls renderContentOnly()
     * on the sub-element so we don’t get the handleBar in cells.
     */
    render() {
        const cell = document.createElement(this._cellType);
        cell.id = this._id;

        const contentElement = this._contentElement.renderContentOnly();
        cell.appendChild(contentElement);

        // Keep a reference to the <th>/<td> node
        this._element = cell;
        return cell;
    }

    get element() {
        return this._element;
    }
}

////////////////////////
/// ReportElementSignature.js
////////////////////////

class ReportElementSignature extends ReportGeneratorElement {
    constructor(content = "", style = "", label = "") {
        super(content, style, "signature", label);
        this._label = label;
    }

    appendContent(wrapperDiv) {
        // Use drawImage and getRect
        const canvas = document.createElement("canvas");
        canvas.id = this._id;
        canvas.classList.add("report-element");
        canvas.width = 100;
        canvas.height = 100;
        canvas.style.width = "15%";
        canvas.style.height = "15%";
        const ctx = canvas.getContext("2d");
        ctx.fillStyle = "#f9f9f9";
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.strokeStyle = "#000";
        ctx.lineWidth = 2;
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;
        wrapperDiv.appendChild(canvas);
    }
}




////////////////////////
// Jump: ReportElementInput.js
////////////////////////

class ReportElementInput extends ReportGeneratorElement {
    constructor( content = "", style = "", label = "") {
        super( content, style, "input", label);
        this._label = label;

    }

    appendContent(wrapperDiv) {
        const input = document.createElement("input");
        input.type = "text";
        input.id = this._id;
        input.classList.add("form-control", "report-element");
        input.value = this._content;
        input.addEventListener("input", (e) => {
            this._content = e.target.value;
        });
        // Append the input to the wrapperDiv
        wrapperDiv.appendChild(input);
    }

}

////////////////////////
// Jump: ReportElementTime.js
////////////////////////

class ReportElementTime extends ReportGeneratorElement {
    constructor(content = "", style = "", label = "") {
        super(content, style, "time", label);
        // We expect content in "HH:mm" format. If empty, default to "00:00"
        if (!this._content || !/^\d{2}:\d{2}$/.test(this._content)) {
            this._content = "00:00";
        }

        // Parse initial content into hours and minutes
        const [initialHours, initialMinutes] = this._content.split(":").map(num => parseInt(num, 10));
        this._hours = isNaN(initialHours) ? 0 : initialHours;
        this._minutes = isNaN(initialMinutes) ? 0 : initialMinutes;
    }

    appendContent(wrapperDiv) {
        const container = document.createElement("div");
        container.classList.add("report-element", "d-flex", "align-items-center", "gap-1");
        container.style.cssText = this._style;

        // Create Hours Input
        const hoursInput = document.createElement("input");
        hoursInput.type = "number";
        hoursInput.id = `${this._id}-hours`;
        hoursInput.classList.add("form-control", "report-element-time-hours");
        hoursInput.value = this._hours.toString().padStart(2, "0");
        hoursInput.min = 0;
        hoursInput.max = 23;
        hoursInput.style.width = "4rem";
        hoursInput.addEventListener("input", () => {
            let val = parseInt(hoursInput.value, 10);
            if (isNaN(val) || val < 0) val = 0;
            if (val > 23) val = 23;
            this._hours = val;
            hoursInput.value = val.toString().padStart(2, "0");
            this.updateContent();
        });

        // Create a separator (":")
        const separator = document.createElement("span");
        separator.textContent = ":";
        separator.style.fontWeight = "bold";

        // Create Minutes Input
        const minutesInput = document.createElement("input");
        minutesInput.type = "number";
        minutesInput.id = `${this._id}-minutes`;
        minutesInput.classList.add("form-control", "report-element-time-minutes");
        minutesInput.value = this._minutes.toString().padStart(2, "0");
        minutesInput.min = 0;
        minutesInput.max = 59;
        minutesInput.style.width = "4rem";
        minutesInput.addEventListener("input", () => {
            let val = parseInt(minutesInput.value, 10);
            if (isNaN(val) || val < 0) val = 0;
            if (val > 59) val = 59;
            this._minutes = val;
            minutesInput.value = val.toString().padStart(2, "0");
            this.updateContent();
        });

        container.appendChild(hoursInput);
        container.appendChild(separator);
        container.appendChild(minutesInput);

        wrapperDiv.appendChild(container);
    }

    // Update _content whenever hours or minutes change
    updateContent() {
        const hh = this._hours.toString().padStart(2, "0");
        const mm = this._minutes.toString().padStart(2, "0");
        this._content = `${hh}:${mm}`;
    }

    get content() {
        return this._content;
    }

    set content(newContent) {
        if (newContent && /^\d{2}:\d{2}$/.test(newContent)) {
            this._content = newContent;
            const [h, m] = newContent.split(":").map(num => parseInt(num, 10));
            this._hours = h;
            this._minutes = m;
        } else {
            // If invalid format, reset to "00:00"
            this._content = "00:00";
            this._hours = 0;
            this._minutes = 0;
        }
    }

    // Optionally, you can override params to provide a structured time format
    get params() {
        const baseParams = super.params;
        baseParams.hours = this._hours;
        baseParams.minutes = this._minutes;
        return baseParams;
    }
}

////////////////////////
// Jump: ReportElementTimeRange.js
////////////////////////

class ReportElementTimeRange extends ReportGeneratorElement {
    constructor(content = "", style = "", label = "") {
        super(content, style, "timerange", label);

        // Validate the content format: "HH:mm - HH:mm"
        // If invalid or empty, default to "00:00 - 00:00"
        if (!this._content || !/^\d{2}:\d{2}\s-\s\d{2}:\d{2}$/.test(this._content)) {
            this._content = "00:00 - 00:00";
        }

        // Parse initial content into start and end times
        const [start, end] = this._content.split(" - ");
        const [startHours, startMinutes] = start.split(":").map(num => parseInt(num, 10));
        const [endHours, endMinutes] = end.split(":").map(num => parseInt(num, 10));

        this._startHours = isNaN(startHours) ? 0 : startHours;
        this._startMinutes = isNaN(startMinutes) ? 0 : startMinutes;

        this._endHours = isNaN(endHours) ? 0 : endHours;
        this._endMinutes = isNaN(endMinutes) ? 0 : endMinutes;
    }

    appendContent(wrapperDiv) {
        const container = document.createElement("div");
        container.classList.add("report-element", "d-flex", "align-items-center", "gap-1");
        container.style.cssText = this._style;

        // Start Hours Input
        const startHoursInput = document.createElement("input");
        startHoursInput.type = "number";
        startHoursInput.id = `${this._id}-start-hours`;
        startHoursInput.classList.add("form-control", "report-element-time-hours");
        startHoursInput.value = this._startHours.toString().padStart(2, "0");
        startHoursInput.min = 0;
        startHoursInput.max = 23;
        startHoursInput.style.width = "4rem";
        startHoursInput.addEventListener("input", () => {
            let val = parseInt(startHoursInput.value, 10);
            if (isNaN(val) || val < 0) val = 0;
            if (val > 23) val = 23;
            this._startHours = val;
            startHoursInput.value = val.toString().padStart(2, "0");
            this.updateContent();
        });

        // Start Minutes Input
        const startMinutesInput = document.createElement("input");
        startMinutesInput.type = "number";
        startMinutesInput.id = `${this._id}-start-minutes`;
        startMinutesInput.classList.add("form-control", "report-element-time-minutes");
        startMinutesInput.value = this._startMinutes.toString().padStart(2, "0");
        startMinutesInput.min = 0;
        startMinutesInput.max = 59;
        startMinutesInput.style.width = "4rem";
        startMinutesInput.addEventListener("input", () => {
            let val = parseInt(startMinutesInput.value, 10);
            if (isNaN(val) || val < 0) val = 0;
            if (val > 59) val = 59;
            this._startMinutes = val;
            startMinutesInput.value = val.toString().padStart(2, "0");
            this.updateContent();
        });

        // Separator "bis"
        const separator = document.createElement("span");
        separator.textContent = "bis";
        separator.style.fontWeight = "bold";
        separator.style.margin = "0 0.5rem";

        // End Hours Input
        const endHoursInput = document.createElement("input");
        endHoursInput.type = "number";
        endHoursInput.id = `${this._id}-end-hours`;
        endHoursInput.classList.add("form-control", "report-element-time-hours");
        endHoursInput.value = this._endHours.toString().padStart(2, "0");
        endHoursInput.min = 0;
        endHoursInput.max = 23;
        endHoursInput.style.width = "4rem";
        endHoursInput.addEventListener("input", () => {
            let val = parseInt(endHoursInput.value, 10);
            if (isNaN(val) || val < 0) val = 0;
            if (val > 23) val = 23;
            this._endHours = val;
            endHoursInput.value = val.toString().padStart(2, "0");
            this.updateContent();
        });

        // End Minutes Input
        const endMinutesInput = document.createElement("input");
        endMinutesInput.type = "number";
        endMinutesInput.id = `${this._id}-end-minutes`;
        endMinutesInput.classList.add("form-control", "report-element-time-minutes");
        endMinutesInput.value = this._endMinutes.toString().padStart(2, "0");
        endMinutesInput.min = 0;
        endMinutesInput.max = 59;
        endMinutesInput.style.width = "4rem";
        endMinutesInput.addEventListener("input", () => {
            let val = parseInt(endMinutesInput.value, 10);
            if (isNaN(val) || val < 0) val = 0;
            if (val > 59) val = 59;
            this._endMinutes = val;
            endMinutesInput.value = val.toString().padStart(2, "0");
            this.updateContent();
        });

        container.appendChild(startHoursInput);
        container.appendChild(startMinutesInput);
        container.appendChild(separator);
        container.appendChild(endHoursInput);
        container.appendChild(endMinutesInput);

        wrapperDiv.appendChild(container);
    }

    updateContent() {
        const startHH = this._startHours.toString().padStart(2, "0");
        const startMM = this._startMinutes.toString().padStart(2, "0");
        const endHH = this._endHours.toString().padStart(2, "0");
        const endMM = this._endMinutes.toString().padStart(2, "0");
        this._content = `${startHH}:${startMM} - ${endHH}:${endMM}`;
    }

    get content() {
        return this._content;
    }

    set content(newContent) {
        if (newContent && /^\d{2}:\d{2}\s-\s\d{2}:\d{2}$/.test(newContent)) {
            this._content = newContent;
            const [start, end] = this._content.split(" - ");
            const [h1, m1] = start.split(":").map(num => parseInt(num, 10));
            const [h2, m2] = end.split(":").map(num => parseInt(num, 10));
            this._startHours = h1;
            this._startMinutes = m1;
            this._endHours = h2;
            this._endMinutes = m2;
        } else {
            this._content = "00:00 - 00:00";
            this._startHours = 0;
            this._startMinutes = 0;
            this._endHours = 0;
            this._endMinutes = 0;
        }
    }

    get params() {
        const baseParams = super.params;
        baseParams.startHours = this._startHours;
        baseParams.startMinutes = this._startMinutes;
        baseParams.endHours = this._endHours;
        baseParams.endMinutes = this._endMinutes;
        return baseParams;
    }
}



////////////////////////
// Jump: ReportElementUpload.js
////////////////////////

class ReportElementUpload extends ReportGeneratorElement {
    constructor( content = "", style ="", label = "", type = "upload") {
        super( content, style, "upload", label);
        this._label = label;
        this._content = [];
        this._multiple = false;
        this._accept = "*/*";
    }

    get multiple() {
        return this._multiple;
    }

    get accept() {
        return this._accept;
    }

    set multiple(value) {
        this._multiple = value;
    }

    set accept(value) {
        this._accept = value;
    }



    appendContent(wrapperDiv){
        const input = document.createElement("input");
        input.type = "file";
        input.id = this._id;
        input.multiple = this._multiple;
        input.accept = this._accept;
        input.classList.add("form-control", "report-element");
        input.addEventListener("change", (e) => {
            this._content = e.target.files;
        });
        input.disabled = true;
        wrapperDiv.appendChild(input);
    }

}

////////////////////////
// Jump: ReportElementClock.js
////////////////////////

class ReportElementClock extends ReportGeneratorElement {
    constructor( content = "", style = "", label = "", type = "clock") {
        super( content, style, "clock", label);
        this._label = label;
    }

    appendContent(wrapperDiv){
        const clock = document.createElement("div");
        clock.id = this._id;
        clock.classList.add("report-element");

    }

}


////////////////////////
// Jump: ReportElementCheckbox.js
////////////////////////

class ReportElementCheckbox extends ReportGeneratorElement {
    constructor(content = false, style = "", label = "") {
        super(content, style, "checkbox", label);
    }

    set content(newContent) {
        if(typeof newContent === "boolean"){
            this._content = newContent;
        }else{
            this._content = false;

        }
    }

    appendContent(wrapperDiv) {
        // Create a container for the checkbox and label
        const container = document.createElement("div");
        container.classList.add("form-check");

        // Create the checkbox input
        const checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.id = this._id;
        checkbox.classList.add("report-element");
        checkbox.checked = this.content;

        // Create the label for the checkbox
        const labelElement = document.createElement("label");
        labelElement.classList.add("form-check-label");
        labelElement.htmlFor = this._id;
        labelElement.innerText = this._label;

        // Event listener to update the checked state
        checkbox.addEventListener("change", (e) => {
            this.content = e.target.checked;
        });

        // Append elements to the container
        container.appendChild(checkbox);
        container.appendChild(labelElement);

        // Append the container to the wrapper
        wrapperDiv.appendChild(container);
    }

    // Override renderContentOnly to include the checkbox state
    renderContentOnly() {
        const checkbox = document.createElement("input");
        checkbox.classList.add("report-element");
        checkbox.type = "checkbox";
        checkbox.id = this._id;
        checkbox.checked = this.content;
        checkbox.disabled = true;  // Disable the checkbox
        return checkbox;
    }

    get params(){
        const baseParams = super.params;
        baseParams.checked = this.content;
        return baseParams;
    }

    // Method to initialize from parameters
    initializeFromParams(params){
        if(params.checked !== undefined){
            this.checked = params.checked;
        }
    }
}


////////////////////////
// Jump: ReportElementRadio.js
////////////////////////

class ReportElementRadio extends ReportGeneratorElement{
    constructor(content = false, style = "", label = "", group=""){
        super(content, style, "radio", label);
        this._group = group;
    }

    get group(){
        return this._group;
    }

    set group(newGroup){
        if(typeof newGroup === "string"){
            this._group = newGroup;
        }else{
            this._group = "";
            alert("Group must be a string");
        }
    }

    appendContent(wrapperDiv){
        // Create a container for the radio button and label
        const container = document.createElement("div");
        container.classList.add("form-check");

        // Create the radio input
        const radio = document.createElement("input");
        radio.type = "radio";
        radio.id = this._id;
        radio.name = this._group;
        radio.classList.add("form-check-input", "report-element");
        radio.checked = this.content;

        // Create the label for the radio button
        const labelElement = document.createElement("label");
        labelElement.classList.add("form-check-label");
        labelElement.htmlFor = this._id;
        labelElement.innerText = this._label;

        // Event listener to update the checked state
        radio.addEventListener("change", (e) => {
            this.content = e.target.checked;
        });

        // Append elements to the container
        container.appendChild(radio);
        container.appendChild(labelElement);

        // Append the container to the wrapper
        wrapperDiv.appendChild(container);
    }

    renderContentOnly(){
        const radio = document.createElement("input");
        radio.classList.add("report-element");
        radio.type = "radio";
        radio.id = this._id;
        radio.name = this._group;
        radio.checked = this.content;
        radio.disabled = true;  // Disable the radio button
        return radio;
    }

    get params(){
        const baseParams = super.params;
        baseParams.checked = this.content;
        baseParams.group = this._group;s
        return baseParams;
    }

}




</script>';


////////////////////////
// Jump: Styles
////////////////////////

echo '<style>
// .report-element-wrapper {
//     position: relative;  /* Make the wrapper a positioning context */

//     align-items: center;
//     padding: 5px;
//     margin-bottom: 5px;
//     gap: .5rem;
//     background-color: #f9f9f9;
//     cursor: pointer;
//     overflow: hidden;  /* Prevent overflow of content */
// }

.handle-bar {
    position: absolute;  /* Position it absolutely within the wrapper */
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    padding: 5px;
    cursor: move;  /* Cursor style to indicate draggable handle */
    background-color: rgba(224, 224, 224, 0.5);  /* Semi-transparent background */
    border-right: 1px solid #ccc;
    z-index: 10;  /* Higher z-index to appear on top */
    display: none;  /* Hidden by default */
}



.report-element {
    z-index: 5;  /* Lower z-index to go behind the handleBar */
}


.sortable-placeholder {
    border: 2px dashed #b3d9ff;  /* Dashed border for placeholder */
    background-color: #f0f0f0;   /* Light background color */
    height: 60px;                /* Set a consistent height */
    margin-bottom: 5px;          /* Same spacing as other elements */
    width: 100%;                 /* Full width of the container */
    display: flex;               /* Keep it aligned like other elements */
    align-items: center;         /* Center the content vertically */
    justify-content: center;     /* Center the content horizontally */
    box-sizing: border-box;
}

table {
    overflow-y : scroll;
    width : 100%;
}

.form-check-input:focus {
    box-shadow: none;
    outline: none;
}

.active-cell {
    outline: 2px solid blue;
}


</style>';

}
$db->close();
