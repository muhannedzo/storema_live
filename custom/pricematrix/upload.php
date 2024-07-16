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
// require_once '../master.inc.php';

$data = json_decode(file_get_contents('php://input'), true);

if($data){
    if(isset($data["storeid"]) && isset($data["tecid"]) && isset($data["lon2"]) && isset($data["lat2"]) && isset($data["lon1"])
    && isset($data["lat1"]) && isset($data["distance"])){
        $sqll = 'SELECT rowid FROM llx_pricematrix_distances WHERE storeid = '.$data["storeid"]. ' and technicianid = '.$data["tecid"].' and lng_store = '.$data["lon2"].' and lat_store = '.$data["lat2"].' and lng_tec = '.$data["lon1"].' and lat_tec = '.$data["lat1"].' and distance= '.$data["distance"];
        $result = $db->query($sqll)->fetch_all()[0];
        if($result){
            echo 'exist';
        }else{
            $sql = 'INSERT INTO llx_pricematrix_distances (`storeid`, `technicianid`, `lng_store`, `lat_store`, `lng_tec`, `lat_tec`, `distance`) 
                    VALUES ("'.$data["storeid"].'", "'.$data["tecid"].'", "'.$data["lon2"].'", "'.$data["lat2"].'", "'.$data["lon1"].'", "'.$data["lat1"].'", "'.$data["distance"].'")';
            
            if($db->query($sql, 0, 'ddl')){
                echo "sucess";
            }else{
                echo "fail";
            }
        }
    }
    if(isset($data["storeid"])){
        $sql = 'SELECT lng_store, lat_store FROM llx_pricematrix_distances WHERE storeid = '.$data["storeid"];
        $result = $db->query($sql)->fetch_all()[0];
        if($result){
            echo implode(',', $result);
        }else{
            echo 2;
        }
    }
    if(isset($data["tecid"])){
        $sql = 'SELECT lng_tec, lat_tec FROM llx_pricematrix_distances WHERE technicianid = '.$data["tecid"];
        $result = $db->query($sql)->fetch_all()[0];
        if($result){
            echo implode(',', $result);
        }else{
            echo 2;
        }
    }
}