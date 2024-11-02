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
} else {
    echo json_encode(['error' => 'No form data received']);
}

