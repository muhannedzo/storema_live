<?php 

// Load Dolibarr environment
require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/project/modules_project.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once '../master.inc.php';




if (isset($_FILES['pdfFile'])) {
    $file = $_FILES['pdfFile'];
    $project = $_POST['project'];
    $id = $_POST['id'];
    $form = $_POST['form'];
    $project_id = $_POST['project_id'];
    $backtopage = $_POST['backtopage'];
    $directory = 'ticket/pdfs/';
    $filename = str_replace("/", "-", $project) . '_' . uniqid() . '.pdf';
    if (!file_exists($directory)) {
        mkdir($directory, 0777, true); // 0777 sets read, write, and execute permissions for owner, group, and others
        chmod($directory, 0777); // Set permissions explicitly
        echo "Directory created with full access.";
    }

    $filePath = $directory . $filename;
    // var_dump($_FILES);
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
			setEventMessages('PDF saved on the server: ' . $filePath, null, 'mesgs');
      $sql = 'INSERT INTO llx_forms (`file_name`, `file_path`, `project_id`, `ticket_id`, `form`) VALUES ("'.$filename.'", "'.$filePath.'", "'.$project_id.'", "'.$id.'", "'.base64_encode($form).'")';
      $db->query($sql, 0, 'ddl');
  } else {
    dol_htmloutput_errors('Failed to save PDF on the server.');
  }
} else {
  dol_htmloutput_errors('No file data received.');
}

