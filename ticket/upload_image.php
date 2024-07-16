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

$uploadDir = DOL_DOCUMENT_ROOT.'/ticket/ticket/img/';
// Check if the file was uploaded without errors
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    // Specify the directory where you want to save the uploaded image
    
    // Create the directory if it doesn't exist
    if(!is_dir($uploadDir)){
        mkdir($uploadDir);
    }

    // Get the temporary file name
    $tempFileName = $_FILES['file']['tmp_name'];

    // Generate a unique name for the uploaded file
    $uniqueName = uniqid() . '_' . $_FILES['file']['name'];

    // Build the path to the file
    $uploadPath = $uploadDir . $uniqueName;

    // Move the uploaded file to the desired directory
    if (move_uploaded_file($tempFileName, $uploadPath)) {
        // File upload successful
        echo $uniqueName;
    } else {
        // Error while moving the file
        echo 'Error uploading image.';
    }
} else {
    // Error in the file upload
    echo 'Error: ' . $_FILES['file']['error'];
}
?>
