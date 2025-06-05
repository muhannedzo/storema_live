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
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/stores/class/branch.class.php';
dol_include_once('/stores/compress.php');

// Fetch variables
$compress = new Compress();
$object = new Ticket($db);

$action = GETPOST('action', 'none');
$mode = GETPOST('mode', 'none');

// Initialize variables
$ticketId = GETPOST('ticketId', 'int');
$userId = GETPOST('userId', 'int');
$storeId = GETPOST('storeId', 'int');

$store = new Branch($db);
$store->fetch($storeId);

// Flag for file input with multiple pic upload
$mult = GETPOST('mult', 'alpha');


$socId = GETPOST('socId', 'int');
$object->fetch($ticketId);

// Fetch the ticket details
if ($object->fetch($ticketId) <= 0) {
    dol_htmloutput_errors('Ticket not found.');
    exit;
}

// Fetch the store details
if ($store->fetch($storeId) <= 0) {
    dol_htmloutput_errors('Store not found.');
    exit;
}

// Directory for storing images
$dir = DOL_DOCUMENT_ROOT.'/formsImages/';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

function fetchImages($ticketId, $db) {
    // $sql = 'SELECT images FROM llx_tec_forms WHERE fk_ticket = '.(int)$ticketId.' AND fk_user = '.(int)$userId.' AND fk_store = '.(int)$storeId.' AND fk_soc = '.(int)$socId;
    $sql = 'SELECT images FROM llx_tec_forms WHERE fk_ticket = '.(int)$ticketId;
    $resql = $db->query($sql);
    if ($resql) {
        $row = $db->fetch_array($resql);
        if ($row && $row['images']) {
            return json_decode(base64_decode($row['images']), true);
        } else {
            return []; // No images yet
        }
    } else {
        dol_print_error($db);
        return [];
    }
}


function updateImageList($ticketId, $imagesList, $db) {
    foreach ($imagesList as &$node) {
        if (isset($node['images']) && is_array($node['images'])) {
            $node['images'] = array_values($node['images']);  // ensures 0-based, dense arrays
        }
    }
    unset($node);
    $encodedList = base64_encode(json_encode($imagesList));
    // Check if record exists
    // $sqlCheck = 'SELECT COUNT(*) as count FROM llx_tec_forms WHERE fk_ticket = '.(int)$ticketId.' AND fk_user = '.(int)$userId.' AND fk_store = '.(int)$storeId.' AND fk_soc = '.(int)$socId;
    $sqlCheck = 'SELECT COUNT(*) as count FROM llx_tec_forms WHERE fk_ticket = '.(int)$ticketId;
    $resqlCheck = $db->query($sqlCheck);
    if ($resqlCheck) {
        $rowCheck = $db->fetch_array($resqlCheck);
        if ($rowCheck['count'] > 0) {
            // Record exists, perform UPDATE
            //$sqlUpdate = 'UPDATE llx_tec_forms SET images = "'.$db->escape($encodedList).'" WHERE fk_ticket = '.(int)$ticketId.' AND fk_user = '.(int)$userId.' AND fk_store = '.(int)$storeId.' AND fk_soc = '.(int)$socId;
            $sqlUpdate = 'UPDATE llx_tec_forms SET images = "'.$db->escape($encodedList).'" WHERE fk_ticket = '.(int)$ticketId;
            $db->query($sqlUpdate);
        } else {
            // No record exists, perform INSERT
            //$sqlInsert = 'INSERT INTO llx_tec_forms (fk_ticket, fk_user, fk_store, fk_soc, images) VALUES ('.(int)$ticketId.', '.(int)$userId.', '.(int)$storeId.', '.(int)$socId.', "'.$db->escape($encodedList).'")';
            $sqlInsert = 'INSERT INTO llx_tec_forms (fk_ticket, images) VALUES ('.(int)$ticketId.','.$db->escape($encodedList).'")';
            $db->query($sqlInsert);
        }
    } else {
        dol_print_error($db);
    }
}


// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $mode === 'image') {
    switch ($action) {
        case 'upload_images':
            $images = [];
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $imageType = GETPOST('imageType', 'none');
            $imageQuality = 20;

            $imagesList = fetchImages($ticketId, $db);

            $maxIndex = 0;
            foreach ($imagesList as $n) {
                if ($n['type'] === $imageType) {
                    foreach ($n['images'] as $fn) {
                        if (preg_match('/_(\d+)\.\w+$/', $fn, $m)) {
                            $maxIndex = max($maxIndex, (int) $m[1]);
                        }
                    }
                    break;
                }
            }

            $counter = $maxIndex + 1;

            if (!empty(array_filter($_FILES['files']['name']))) {
                foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
                    $file_tmpname = $_FILES['files']['tmp_name'][$key];
                    $file_name = $_FILES['files']['name'][$key];
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                    // Custom file naming
                    if ($imageType == "Testprotokoll") {
                        $file_name = date("d.m.y, H:i", $object->array_options["options_dateofuse"])."_".$store->city."_VKST_".explode("-", $store->b_number)[2].".".$file_ext;
                    } elseif ($imageType == "Serverschrank nachher") {
                        $file_name = "VKST_".explode("-", $store->b_number)[2]."_".explode(" ", $imageType)[0]."_".$ticketId.".".$file_ext;
                    } else {
                        $file_name = "VKST_".explode("-", $store->b_number)[2]."_".$imageType."_".$ticketId.".".$file_ext;
                    }

                    if ($mult) {                          // add “ 1 ”, “ 2 ”, … just before extension
                        $file_name = preg_replace(
                            '/(\.\w+)$/',
                            '_' . $counter++ . '$1',      // appends a space-number sequence
                            $file_name
                        );
                    }

                    $filepath = $dir.$file_name;

                    if (in_array($file_ext, $allowed_types)) {
                        // Handle EXIF orientation
                        if ($file_ext == 'jpg' || $file_ext == 'jpeg') {
                            $exif = exif_read_data($file_tmpname);
                            if (!empty($exif['Orientation'])) {
                                $image = imagecreatefromjpeg($file_tmpname);
                                switch ($exif['Orientation']) {
                                    case 3:
                                        $image = imagerotate($image, 180, 0);
                                        break;
                                    case 6:
                                        $image = imagerotate($image, -90, 0);
                                        break;
                                    case 8:
                                        $image = imagerotate($image, 90, 0);
                                        break;
                                }
                                imagejpeg($image, $file_tmpname, 90);
                                imagedestroy($image);
                            }
                        }

                        // Compress and move image
                        if (file_exists($filepath)) {
                            unlink($filepath);
                        }
                        $compressedImage = $compress->compress_image($file_tmpname, $filepath, $imageQuality);
                        if ($compressedImage) {
                            $images[] = $file_name;
                        } else {
                            dol_htmloutput_errors("Error uploading {$file_name} <br />");
                        }
                    } else {
                        dol_htmloutput_errors("Error uploading {$file_name} ({$file_ext} file type is not allowed)<br />");
                    }
                }
            } else {
                dol_htmloutput_errors("No files selected.");
            }

            
            // Check if the imageType exists in imagesList
            $found = false;
            foreach ($imagesList as &$node) {
                if ($node['type'] === $imageType) {
                    if ($mult) {
                        // Append new images to existing list
                        $node['images'] = array_unique(
                            array_merge($node['images'], $images)
                        );
                    } else {
                        // Replace existing images (single-upload mode)
                        $node['images'] = $images;
                    }
                    $found = true;
                    break;
                }

            }
            unset($node);

            if (!$found) {
                // Add new node
                $imagesList[] = [
                    'type' => $imageType,
                    'images' => $images
                ];
            }
            updateImageList($ticketId, $imagesList, $db);

            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'images' => $imagesList]);
            exit;

        case 'delete_image':
            try {
                $imageType = GETPOST('imageType', 'none');
                $filenameToDelete = GETPOST('filename', 'none');

                // Update images list
                $imagesList = fetchImages($ticketId, $db);
                foreach ($imagesList as &$node) {
                    if ($node['type'] === $imageType) {
                        $node['images'] = array_filter($node['images'], function ($image) use ($filenameToDelete) {
                            return $image !== $filenameToDelete;
                        });
                    }
                }
                // Remove empty nodes
                unset($node);

                // Remove empty nodes
                $imagesList = array_filter($imagesList, function ($node) {
                    return !empty($node['images']);
                });
                updateImageList($ticketId, $imagesList, $db);

                // Delete file from server
                $filepath = $dir.$filenameToDelete;
                if (file_exists($filepath)) {
                    unlink($filepath);
                    error_log("Image deleted successfully: " . $filenameToDelete);
                }

                header('Content-Type: application/json');
                echo json_encode(['status' => 'success']);
                exit;
            } catch (Exception $e) {
                error_log("Error deleting image: " . $e->getMessage());
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                exit;
            }

        case 'fetch_images':
            $imagesList = fetchImages($ticketId, $db);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'images' => $imagesList]);
            exit;

        default:
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            exit;
    }
    exit;
}else if(isset($_POST['form'])){
    $form = $_POST['form'];
    //$storeId = $_POST['storeId'];
    //$userId = $_POST['userId'];
    $ticketId = $_POST['ticketId'];
    //$socId = $_POST['socId'];
    $parameters = $_POST['parameters'];

    $sql = 'SELECT * FROM llx_tec_forms WHERE fk_ticket = '.$ticketId;
    $result = $db->query($sql)->fetch_all()[0];
    if($result[0]){
        $sql = 'UPDATE llx_tec_forms SET content = "'.base64_encode($form).'", parameters = "'.base64_encode($parameters).'" WHERE fk_ticket = '.$ticketId.';';
        $db->query($sql, 0, 'ddl');
        setEventMessages('Form saved on the DB', null, 'mesgs');
    }else{
        $sqlll = 'INSERT INTO llx_tec_forms (`fk_ticket`, `fk_user`, `fk_soc`, `fk_store`, `content`, `parameters`) VALUES ("'.$ticketId.'", "'.$userId.'", "'.$socId.'", "'.$storeId.'", "'.base64_encode($form).'", "'.base64_encode($parameters).'")';
        $db->query($sqlll, 0, 'ddl');
        setEventMessages('Form saved on the DB', null, 'mesgs');
    }
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => 'Form saved on the DB: '.$_POST['parameters']]);
    exit;
}else{

    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'No data received.']);
    exit;
}