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



if (isset($_POST['form'])) {
    $form = $_POST['form'];
    $parameters = $_POST['parameters'];
    $reportId = $_POST['reportId'];
    $userId = $_POST['userId'];

    // $sql = 'SELECT * FROM llx_reports WHERE rowid = '.$reportId.' AND fk_user = '.$userId;
    // $result = $db->query($sql)->fetch_all()[0];
    
    if ($reportId && $userId) {
        // Update existing form
        $sql = 'UPDATE llx_reports SET content = "'.base64_encode($form).'", parameters = "'.base64_encode($parameters).'", date = NOW(), fk_user = "'.$userId.'" WHERE rowid = '.$reportId;
        $db->query($sql);
        echo json_encode(['message' => 'Form updated successfully']);
    } else {
        // Insert new form
        $sql = 'INSERT INTO llx_reports (fk_user, content, parameters) VALUES ("'.$userId.'", "'.base64_encode($form).'", "'.base64_encode($parameters).'")';
        $db->query($sql);
        echo json_encode(['message' => 'Form saved successfully']);
    }
}else if(isset($_POST['list'])){
    $list = $_POST['list'];
    if (!isset($listData['action'])) {
        echo json_encode(['error' => 'No action specified for list operation']);
        exit;
    }

    $action = $listData['action'];

    switch ($action) {
        case 'create':
            // Validate and sanitize input
            $reportId = isset($listData['reportId']) ? intval($listData['reportId']) : 0;
            $name = isset($listData['name']) ? trim($listData['name']) : '';
            $options = isset($listData['options']) ? $listData['options'] : [];
        
            if ($reportId <= 0 || empty($name) || !is_array($options)) {
                echo json_encode(['error' => 'Invalid input data for list creation']);
                exit;
            }
        
            // Convert options array to JSON
            $optionsJson = json_encode($options);
        
            // Prepare SQL statement
            $stmt = $db->db->prepare('INSERT INTO llx_reports_lists (reportId, name, options, date_created, date_updated) VALUES (?, ?, ?, NOW(), NOW())');
            $stmt->bind_param('iss', $reportId, $name, $optionsJson);
        
            if ($stmt->execute()) {
                $listId = $stmt->insert_id;
                echo json_encode(['message' => 'List created successfully', 'listId' => $listId]);
            } else {
                echo json_encode(['error' => 'Failed to create list']);
            }
            break;
        case 'update':
             // Validate and sanitize input
            $listId = isset($listData['listId']) ? intval($listData['listId']) : 0;
            $name = isset($listData['name']) ? trim($listData['name']) : '';
            $options = isset($listData['options']) ? $listData['options'] : [];

            if ($listId <= 0 || empty($name) || !is_array($options)) {
                echo json_encode(['error' => 'Invalid input data for list update']);
                exit;
            }

            // Convert options array to JSON
            $optionsJson = json_encode($options);

            // Prepare SQL statement
            $stmt = $db->db->prepare('UPDATE llx_reports_lists SET name = ?, options = ?, date_updated = NOW() WHERE id = ?');
            $stmt->bind_param('ssi', $name, $optionsJson, $listId);

            if ($stmt->execute()) {
                echo json_encode(['message' => 'List updated successfully']);
            } else {
                echo json_encode(['error' => 'Failed to update list']);
            }
            break;
        case 'delete':
            // Validate and sanitize input
            $listId = isset($listData['listId']) ? intval($listData['listId']) : 0;

            if ($listId <= 0) {
                echo json_encode(['error' => 'Invalid list ID for deletion']);
                exit;
            }

            // Prepare SQL statement
            $stmt = $db->db->prepare('DELETE FROM llx_reports_lists WHERE id = ?');
            $stmt->bind_param('i', $listId);

            if ($stmt->execute()) {
                echo json_encode(['message' => 'List deleted successfully']);
            } else {
                echo json_encode(['error' => 'Failed to delete list']);
            }
            break;
        case 'fetch':
            // Validate and sanitize input
            $reportId = isset($listData['reportId']) ? intval($listData['reportId']) : 0;

            if ($reportId <= 0) {
                echo json_encode(['error' => 'Invalid report ID for fetching lists']);
                exit;
            }

            // Prepare SQL statement
            $stmt = $db->db->prepare('SELECT id, name, options FROM llx_reports_lists WHERE reportId = ?');
            $stmt->bind_param('i', $reportId);

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $lists = [];

                while ($row = $result->fetch_assoc()) {
                    $lists[] = [
                        'id' => $row['id'],
                        'name' => $row['name'],
                        'options' => json_decode($row['options'], true)
                    ];
                }

                echo json_encode(['lists' => $lists]);
            } else {
                echo json_encode(['error' => 'Failed to fetch lists']);
            }
            break;
        default:
            echo json_encode(['error' => 'Invalid action specified for list operation']);
            break;
    }
}else {
    echo json_encode(['error' => 'No form data received']);
}

