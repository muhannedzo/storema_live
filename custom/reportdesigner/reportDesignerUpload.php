<?php

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
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/project/modules_project.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
$action = GETPOST('action', 'alpha');

if (isset($_POST['form'])) {
    $form = $_POST['form'];
    $parameters = $_POST['parameters'];
    $reportId = $_POST['reportId'];
    $userId = $_POST['userId'];
    $title = $_POST['title'];
    $description = $_POST['description'];


    // $sql = 'SELECT * FROM llx_reports WHERE rowid = '.$reportId.' AND fk_user = '.$userId;
    // $result = $db->query($sql)->fetch_all()[0];

    if ($reportId && $userId) {
        // Update existing form
        //$sql = 'UPDATE llx_reports SET content = "'.base64_encode($form).'", parameters = "'.base64_encode($parameters).'", date = NOW(), fk_user = "'.$userId.'" WHERE rowid = '.$reportId;
        $sql = 'UPDATE llx_reports SET content = "'.base64_encode($form).'", parameters = "'.base64_encode($parameters).'", title = "'.$title.'", description = "'.$description.'", fk_user = "'.$userId.'" WHERE rowid = '.$reportId;
        $db->query($sql);
        echo json_encode(['message' => 'Form updated successfully']);
    } else {
        // Insert new form
        //$sql = 'INSERT INTO llx_reports (fk_user, content, parameters) VALUES ("'.$userId.'", "'.base64_encode($form).'", "'.base64_encode($parameters).'")';
        $sql = 'INSERT INTO llx_reports (fk_user, content, parameters, title, description) VALUES ("'.$userId.'", "'.base64_encode($form).'", "'.base64_encode($parameters).'", "'.$title.'", "'.$description.'")';
        $db->query($sql);
        echo json_encode(['message' => 'Form saved successfully']);
    }

}

if ($_POST['action'] === 'deleteMultiple') {
    if (isset($_POST['reportIds'])) {
        $reportIds = json_decode($_POST['reportIds'], true);

        if (!is_array($reportIds) || empty($reportIds)) {
            echo json_encode(['success' => false, 'error' => 'Ungültige Report IDs.']);
            exit;
        }

        // Sanitize and prepare IDs
        $reportIds = array_map('intval', $reportIds);
        $idsString = implode(",", $reportIds);

        // Execute deletion query
        $sql = "DELETE FROM llx_reports WHERE rowid IN ($idsString)";
        if ($db->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Fehler beim Löschen der Reports.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Keine Report IDs angegeben.']);
    }
    exit;
}

if($_POST['action'] == 'unassign'){
    $projectId = intval($_POST['projectId']);
    $sql = "UPDATE llx_reports SET projectid = '' WHERE projectid = $projectId";
   
    if ($db->query($sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Fehler beim Entfernen der Projektzuweisung.']);
    }
    exit;
}

if ($_POST['action'] === 'overwriteBasicDesign') {
    // Sanitize and validate input
    $reportId = isset($_POST['reportId']) ? intval($_POST['reportId']) : 0;
    $userId = isset($_POST['userId']) ? intval($_POST['userId']) : 0;

    // Validate reportId and userId
    if ($reportId <= 0 || $userId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Ungültige Bericht-ID oder Benutzer-ID.']);
        exit;
    }

    // Construct the SQL statement using JOIN
    $sql = "
        UPDATE llx_reports AS target
        JOIN llx_reports AS source ON source.rowid = $reportId
        SET
            target.fk_user = $userId,
            target.title = source.title,
            target.description = source.description,
            target.content = source.content,
            target.parameters = source.parameters
        WHERE target.rowid = 0
    ";

    // Execute the query
    if ($db->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Bericht erfolgreich überschrieben!']);
    } else {
        // Return the database error message
        echo json_encode(['success' => false, 'error' => $db->error]);
    }
    exit;
}

if ($_POST['action'] === 'duplicateReport') {
    $reportId = intval($_POST['reportId']);

    if (!$reportId) {
        echo json_encode(['success' => false, 'error' => 'Ungültige Report ID.']);
        exit;
    }

    // Fetch the original report
    $sqlFetch = "SELECT fk_user, title, description, content, parameters FROM llx_reports WHERE rowid = $reportId";
    $result = $db->query($sqlFetch);
    $report = $result->fetch_assoc();
    if ($result) {
        $newTitle = $report['title'] . " (Kopie)";
        $description = $db->escape($report['description']);
        $content = $db->escape($report['content']);
        $parameters = $db->escape($report['parameters']);
        $userId = intval($report['fk_user']);

        // Insert the duplicated report as a new row
        $sqlInsert = "
            INSERT INTO llx_reports (fk_user, title, description, content, parameters, date)
            VALUES ($userId, '$newTitle', '$description', '$content', '$parameters', NOW())
        ";

        if ($db->query($sqlInsert)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Fehler beim Duplizieren des Reports.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Originalreport nicht gefunden.']);
    }
    exit;
}

if($_POST['action'] === "save_project_assignment"){
    $projectId = intval($_POST['projectId']);
    $reportId = intval($_POST['reportId']);

    // Validate input
    if ($projectId > 0 && $reportId > 0) {
        $sql = "UPDATE llx_reports SET projectid = $projectId WHERE rowid = $reportId";

        // Execute the query and check for errors
        if ($db->query($sql)) {
            echo json_encode([
                'success' => true,
                'message' => 'Projekt erfolgreich zugewiesen!'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Fehler beim Aktualisieren des Reports: ' . $db->lasterror()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Ungültige Eingabewerte für Projekt- oder Report-ID.'
        ]);
    }
    exit;
}else {
    echo json_encode(['error' => 'No form data received']);
}
