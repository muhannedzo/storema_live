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

// Util function to return most current version of a design based on family id
function fetchMaxVersionByBase($baseId, $db) {
    $baseId = intval($baseId);
    if ($baseId < 0) {
        return false;
    }
    // Use a prepared statement to avoid SQL injection.
    $sql = "SELECT * FROM llx_design_version WHERE base_id = $baseId ORDER BY version DESC LIMIT 1";
    $result = $db->query($sql);
    if (!$result || $result->num_rows === 0) {
        return false;
    }
    $assoc = $result->fetch_assoc();
    return $assoc;
}

function fetchMaxVersionOfDesign($designId, $db) {
    $designId = intval($designId);
    if ($designId <= 0) {
        return false;
    }
    $sql = "SELECT * FROM llx_design_version WHERE base_id = (SELECT base_id FROM llx_design_version WHERE rowid = $designId) ORDER BY version DESC LIMIT 1";
    $result = $db->query($sql);
    if (!$result || $result->num_rows === 0) {
        return false;
    }
    $assoc = $result->fetch_assoc();
    return $assoc;
}



// Util function to return whether a design is archived
function fetchIsArchived($baseID, $db) {
    $sql = "SELECT archived FROM llx_design WHERE rowid = $baseID";
    $res = $db->query($sql);
    if (!$res || $res->num_rows === 0) {
        return false;
    }
    return $res->fetch_assoc()['archived'];
}

// Util function to insert a new design. Returns true on success, false otherwise.
// function insertNewDesign($designId, $db){
//     $sql = "INSERT INTO llx_design (rowid, archived) VALUES ($designId, 0)";
//     $res = $db->query($sql);
//     if(!$res){
//         return false;
//     }
//     $sqlVersion = "INSERT INTO llx_design_version(rowid, fk_user, title, description, content, parameters, date, version, design_id) VALUES (0, 1, 'Neues Design', 'Neues Design', '', '', NOW(), 1, $designId)";
//     $res = $db->query($sqlVersion);
//     if(!$res){
//         return false;
//     }
//     return true;
// }


if (isset($_POST['form'])) {
    $form = $_POST['form'];
    $parameters = $_POST['parameters'];
    $designId = isset($_POST['reportId']) ? $_POST['reportId'] : null;
    $userId = $_POST['userId'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    //echo json_encode(['message' => 'Form received successfully $designId: ' . $designId . ' $userId: ' . $userId]);
    echo json_encode(['message' => 'Form received successfully $designId: ' . var_dump($designId)]);
    // If we have an existing version row ID, we create a NEW version (not new design)
    if ($designId !== NULL && $userId) {
        echo json_encode(['message' => 'we entered the if statement']);
        // Fetch specific version
        $fetchDesign = "SELECT * FROM llx_design_version WHERE rowid = $designId";
        $res = $db->query($fetchDesign);
        
        $design = $res->fetch_assoc();
        // If the design (family) is archived, we cannot edit it
        if(fetchIsArchived($design["base_id"], $db)){
            echo json_encode(['error' => 'Design ist archiviert und kann nicht bearbeitet werden.']);
            exit;
        }else if(fetchMaxVersionByBase($design["base_id"], $db)['version'] > $design['version']){
            echo json_encode(['error' => 'Es existiert bereits eine neuere Version dieses Designs.']);
            exit;
        }
        
        // Check if the content is the same as the existing version
        if ($parameters === base64_decode($design['parameters']) ) {
            // Only update description, title and date if the content is the same
            $sql = "UPDATE llx_design_version
                    SET title = '" . $db->escape($title) . "',
                        description = '" . $db->escape($description) . "',
                        date = NOW()
                    WHERE rowid = " . intval($designId);
            $db->query($sql);
            echo json_encode(['message' => 'Title and description updated successfully']);
        }else{
            //echo json_encode(['message' => 'These are the two contents: ' . $form . ' ' . base64_decode($design['content'])]);
            // Existing design family ID
            $designId = $design['base_id'];
            // Current version + 1
            $newVersion = $design['version'] + 1;

            // Insert a new row to represent the updated (new) version
            $sql = "INSERT INTO llx_design_version
                    (fk_user, content, parameters, title, description, base_id, version, date)
                    VALUES
                    (
                        '" . intval($userId)                 . "',
                        '" . $db->escape(base64_encode($form))       . "',
                        '" . $db->escape(base64_encode($parameters)) . "',
                        '" . $db->escape($title)             . "',
                        '" . $db->escape($description)       . "',
                        '" . intval($designId)               . "',
                        '" . intval($newVersion)             . "',
                        NOW()
                    )";
            $db->query($sql);

            echo json_encode(['message' => 'Form updated (new version) successfully']);
        }
        // Fetch the existing design_version row
        // $sql = "SELECT design_id, version
        //         FROM llx_design_version
        //         WHERE rowid = " . intval($designId);
        //$result = $db->query($sql)->fetch_assoc();
        
    }
    else if($userId && !$designId){
        // No existing version ID => we’re creating a brand-new design (family).
        // 1) Create a new row in llx_design to represent the design family.
        $sql = "INSERT INTO llx_design (archived) VALUES (0)";
        $db->query($sql);
        $newDesignId = $db->last_insert_id('llx_design');

        // 2) Create the first version in llx_design_version
        $sql = "INSERT INTO llx_design_version
                   (fk_user, content, parameters, title, description, version, date, base_id)
                VALUES
                   (
                    '" . intval($userId)                 . "',
                    '" . $db->escape(base64_encode($form))       . "',
                    '" . $db->escape(base64_encode($parameters)) . "',
                    '" . $db->escape($title)             . "',
                    '" . $db->escape($description)       . "',
                    1,
                    NOW(),
                    " . intval($newDesignId) . "
                   )";
        $db->query($sql);

        echo json_encode(['message' => 'New design + first version saved. ' . $db->lasterror()]);
    }else if(!$userId){
        echo json_encode(['error' => 'Benutzer-ID fehlt.']);
    }else{
        echo json_encode(['error' => 'Fehler beim Speichern des Designs (Undefiniert).']);
    }
}

if ($_POST['action'] === 'archiveMultiple') {
    if (isset($_POST['reportIds'])) {
        $designIds = json_decode($_POST['reportIds'], true);
        if (!is_array($designIds) || empty($designIds)) {
            echo json_encode(['success' => false, 'error' => 'Ungültige Report IDs.']);
            exit;
        }

        // Sanitize and prepare IDs
        $designIds = array_map('intval', $designIds);
        $idsString = implode(",", $designIds);

        // Set archived flag for all selected designs
        $sql = "UPDATE llx_design SET archived = 1, fk_project = NULL WHERE rowid IN (SELECT base_id FROM llx_design_version WHERE rowid IN ($idsString))";
        if ($db->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Fehler beim Archivieren der Reports.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Keine Report IDs angegeben.']);
    }
    exit;
}

if($_POST['action'] === 'unassign'){
    $projectId = intval($_POST['projectId']);
    $sql = "UPDATE llx_design SET fk_project = NULL WHERE fk_project = $projectId";
    if ($db->query($sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Fehler beim Entfernen der Projektzuweisung.']);
    }
    exit;
}

if ($_POST['action'] === 'overwriteBasicDesign') {
    // Sanitize and validate input
    $designId = isset($_POST['reportId']) ? intval($_POST['reportId']) : 0;
    $userId = isset($_POST['userId']) ? intval($_POST['userId']) : 0;

    // Validate reportId and userId
    if ($designId <= 0 || $userId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Ungültige Bericht-ID oder Benutzer-ID.']);
        exit;
    }

	//Test if reportId with value 0 exists. If not, create it by copying the report with the given reportId and setting it's id to 0
	$sql = "SELECT * FROM llx_design WHERE rowid = 0";
	$result = $db->query($sql);

    if (!$result) {
        echo json_encode(['success' => false, 'error' => $db->error]);
        exit;
    }

	if($result->num_rows == 0){
        // If base design does not exist (for whatever reason, this should not happen) insert the base design
        $sql = "INSERT INTO llx_design (rowid, archived) VALUES (0, 0)";
        $db->query($sql);
        // Then fetch the latest version of the report we want to overwrite with
		$maxDesignVersion = fetchMaxVersionOfDesign($designId, $db);
        if (!$maxDesignVersion) {
            echo json_encode(['success' => false, 'error' => 'Keine Version gefunden.']);
            exit;
        }
		// $maxDesignVersion = $result->fetch_assoc();
		$title = $db->escape($maxDesignVersion['title']);
		$description = $db->escape($maxDesignVersion['description']);
		$content = $db->escape($maxDesignVersion['content']);
		$parameters = $db->escape($maxDesignVersion['parameters']);

        // Insert the latest version of the design we want to overwrite the base design with as a new version of the base design
		$sql = "INSERT INTO llx_design_version(rowid, fk_user, title, description, content, parameters, date, version, base_id) VALUES (0, $userId, '$title', '$description', '$content', '$parameters', NOW(), 1, $designId)";
        $db->query($sql);
        

    }else{
        $maxBaseVersion = fetchMaxVersionByBase(0, $db);
        $maxDesignVersion = fetchMaxVersionOfDesign($designId, $db);
        
        if (!$maxBaseVersion) {
            echo json_encode(['success' => false, 'error' => 'Keine Version des Basisdesigns gefunden.']);
            exit;
        }else if(!$maxDesignVersion){
            echo json_encode(['success' => false, 'error' => 'Keine Version des zu überschreibenden Designs gefunden.']);
            exit;
        }

        // Insert a new version of the base design by creating a new version which is a copy of the latest version of the report we want to overwrite with
        $newVersion = $maxBaseVersion['version'] + 1;
        $maxVersionId = $maxDesignVersion['rowid'];
        $sqlInsert = "INSERT INTO llx_design_version (fk_user, title, description, content, parameters, date, version, base_id)
        SELECT fk_user, title, description, content, parameters, NOW(), $newVersion, 0
        FROM llx_design_version
        WHERE rowid = $maxVersionId
        ";

        $res = $db->query($sqlInsert);
        if (!$res) {
           echo json_encode(['success' => false, 'error' => var_dump($sqlInsert)]);
            exit;
        }
        echo json_encode(['success' => true, 'message' => 'Basiskonzept (rowid=0) erfolgreich überschrieben!']);
        exit;
	}
}

if ($_POST['action'] === 'duplicateReport') {
    $designId = intval($_POST['reportId']);
    #TODO: Check if we send userId in reportdesignerindex.php
    $userId = intval($_POST['userId']);
    if (!$designId) {
        echo json_encode(['success' => false, 'error' => 'Ungültige Design ID.']);
        exit;
    }

    // Fetch the original report
    $design = fetchMaxVersionOfDesign($designId, $db);
    if (!$design) {
        echo json_encode(['success' => false, 'error' => 'Originalreport nicht gefunden.']);
        exit;
    }
    $newTitle = $design['title'] . " (Kopie)";
    $description = $db->escape($design['description']);
    $content = $db->escape($design['content']);
    $parameters = $db->escape($design['parameters']);
    $userId = intval($design['fk_user']);
    $version = intval($design['version']);
    
    // Create new design family
    $sqlInsertDesign = "INSERT INTO llx_design (archived) VALUES (0)";
    $res = $db->query($sqlInsertDesign);
    if (!$res) {
        echo json_encode(['success' => false, 'error' => 'Fehler beim Erstellen der Design-Familie.']);
        exit;
    }
    $resId = $db->last_insert_id('llx_design');
    
    // Insert the duplicated report as a new row
    $sqlInsert = "
        INSERT INTO llx_design_version (fk_user, title, description, content, parameters, date, version, base_id)
        VALUES ($userId, '$newTitle', '$description', '$content', '$parameters', NOW(), 1, $resId)
    ";

    if ($db->query($sqlInsert)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Fehler beim Duplizieren des Reports.']);
    }
    exit;
}

if($_POST['action'] === "save_project_assignment"){
    $projectId = intval($_POST['projectId']);
    $designId = intval($_POST['reportId']);


    // Validate input
    if ($projectId > 0 && $designId > 0) {
        // Update the project ID for the latest version of the design
        // We assume that the designId var corresponds to the most current version of the design
        $sql = "UPDATE llx_design d
        JOIN llx_design_version dv ON d.rowid = dv.base_id
        SET d.fk_project = $projectId
        WHERE dv.rowid = $designId";
        if ($db->query($sql)) {
            echo json_encode([
                'success' => true,
                'message' => 'Projekt erfolgreich zugewiesen!'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Fehler beim Aktualisieren des Reports: ' . var_dump($sql)
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
