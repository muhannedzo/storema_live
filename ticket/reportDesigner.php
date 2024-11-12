<?php 
// Priority 1: TODO: Entferne komplett Propertypanel und mach das neu. 
// Überlege was am meisten Sinn macht: Extra Klasse für Logik und Property? Panelklasse, die alles zusammen macht (unübersichtlich)? 
// Wahrscheinlich ist es am sinnvollsten eine Klasse für das Propertypanel zu machen und eine für die Logik.
// Die Klasse für Propertypanel ist grob wie folgt aufgebaut:
// - Konstruktor, der die Elemente des Propertypanels initialisiert
// - Activate Methode, die als Parameter den Typ des Elements bekommt und die entsprechenden Felder aktiviert (Conditional rendern)
// - Deactivate Methode, die alle Felder deaktiviert, sobald man irgendwo ausserhalb clickt (siehe reportGenerator.js)
// - Change Methode: On Change der Felder wird die Methode aufgerufen und die Werte in einem Array gespeichert.
// TODO: Think about what to do with position. If we have a div with 2 elements inside, how do we handle the position of the elements inside the div?



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
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/stores/class/branch.class.php';
// require_once DOL_DOCUMENT_ROOT.'/ticket/reportGenerator.class.php';
// require_once DOL_DOCUMENT_ROOT.'/ticket/reportElementDisplay.class.php';
// require_once DOL_DOCUMENT_ROOT.'/ticket/reportElementInput.class.php';
// require_once DOL_DOCUMENT_ROOT.'/ticket/reportElementDiv.class.php';
// require_once DOL_DOCUMENT_ROOT.'/ticket/reportElementTextarea.class.php';
// require_once DOL_DOCUMENT_ROOT.'/ticket/reportElementTable.class.php';
print '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">';
print '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>';
print '
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.0/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.0/themes/smoothness/jquery-ui.css">
';
dol_include_once('/ticket/class/ticket.class.php');
dol_include_once('/societe/class/societe.class.php');
dol_include_once('/projet/class/project.class.php');
dol_include_once('/stores/compress.php');


/*
 * View
 */

 $form = new Form($db);
 $formfile = new FormFile($db);
 $action = GETPOST('action', 'aZ09');
 $object = new Branch($db);
 $projectid = GETPOST("projectid");
$socid = GETPOST("socid");
// print ' <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
//          <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.5.0-beta4/html2canvas.min.js"></script>
//          <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/5.3.2/jspdf.plugin.autotable.min.js"></script>';
llxHeader("", $langs->trans("ReportDesigner"));

print load_fiche_titre($langs->trans("Ticket Report"), '', 'stores.png@stores');
// $reportGenerator = new ReportGenerator();
// $reportGenerator->addElement(new ReportElementDisplay(1, "Name vom Projekt / Report", "font-size: 20px; font-weight: bold; width: 100%; text-align: center;"));
// $reportGenerator->addElement(new ReportElementDisplay(2, "Beschreibung", "font-size: 16px; font-weight: bold; width: 100%; text-align: left; background-color: #b3d9ff; padding: 5px; margin-top: 100px;"));
// $reportGenerator->addElement(new ReportElementTextarea(3, "Text", "width: 100%; height: 100px; background-color: #f7f7f7; resize: none;"));
// $reportGenerator->addElement(new ReportElementTextarea(5, "Text", "width: 100%; height: 100px; background-color: #f7f7f7; resize: none;"));

// $reportGenerator->addElement(new ReportElementTable(6, "", "width: 100px; height: 100px; border: 1px solid black;", 2, 2));


////////////////////////
// Jump: Conditional modes
////////////////////////

if($action == "overview"){

$sql = "SELECT * FROM llx_reports";
$res = $db->query($sql)->fetch_all();
if($res){
$sql = "SELECT * FROM llx_user WHERE rowid = ".$res[0][1];
$resUser = $db->query($sql)->fetch_all();
}
if($res[0][7] != null){
    $sql = "SELECT title FROM llx_projet WHERE rowid = ".$res[0][7];
    $resProject = $db->query($sql)->fetch_all();
}
echo "<h1>Übersicht</h1>";
echo "<div class='container'>";
echo "<div class='optionRow'>";
echo "<a href='?action=new' class='btn btn-outline-primary btn-sm'>Neuen Report erstellen</a>";
echo "</div>";
echo '<table class="table table-striped">';
echo '<thead>';
echo '<tr>';
echo '<th scope="col">ID</th>';
echo '<th scope="col">Titel</th>';
echo '<th scope="col">Beschreibung</th>';
echo '<th scrope="col">Zuletzt bearbeitet am: </th>';
echo '<th scope="col">von: </th>';
echo '<th scope="col">Aktionen</th>';
echo '<th scope="col">Projekt</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';
for($i = 0; $i < count($res); $i++){
    $report = $res[$i];
    $title = $resProject[$i][0];
    echo '<tr>';
    echo '<td>'.$report[0].'</td>';
    echo '<td>'.$report[4].'</td>';
    echo '<td>'.$report[6].'</td>';
    echo '<td>'.$report[5].'</td>';
    echo '<td>'.$resUser[0][19].'</td>';
    echo '<td>'.($projectid ? '<a href="?action=assign&reportId='.$report[0].'&projectid='.$projectid.'">Zuweisen</a> | ' : "").'<a href="?action=edit&reportId='.$report[0].'">Bearbeiten</a> | <a href="?action=delete&reportId='.$report[0].'">Löschen</a></td>';
    echo '<td>'.$title.'</td>';
    echo '</tr>';

}

echo '</tbody>';
echo '</table>';
echo '</div>';
}else if ($action == "assign"){
    $sqlUpdate = "UPDATE llx_reports SET projectid = ".GETPOST("projectid")." WHERE rowid = ".GETPOST("reportId");
    $db->query($sqlUpdate);
    echo '<script>window.location.href = "'.DOL_URL_ROOT.'/projet/card.php?id='.$projectid.'&projectid='.$projectid.'";</script>';
} else if($action == "delete") {
    $sql = "DELETE FROM llx_reports WHERE rowid = ".GETPOST("reportId");
    $db->query($sql);
    echo '<script>window.location.href = "?action=overview";</script>';

} else{


    if($action == "new") {
    echo '<script>
    // On dom load
    document.addEventListener("DOMContentLoaded", () => {
        const reportContainer = document.getElementById("report-container");
        const propertyPanelElement = document.getElementById("report-property-panel");
        const reportGenerator = new ReportGenerator(reportContainer, propertyPanelElement);
        reportGenerator.loadBasicDesign();
        reportGenerator.generateReport();
    });
     </script>';
    }else if($action == "edit") {
    $reportId = GETPOST("reportId");
    //echo $reportId;
    $sql = "SELECT * FROM llx_reports WHERE rowid = ".$reportId;
    $res = $db->query($sql)->fetch_all();
    $userId = $res[0][1] ? $res[0][1] : $user->id;
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
    echo '<script>
    document.addEventListener("DOMContentLoaded", () => {
    const reportGenerator = new ReportGenerator(document.getElementById("report-container"), document.getElementById("report-property-panel"));
    reportGenerator.initFromParams(JSON.parse(`'.$parameters.'`));
    reportGenerator.generateReport();
    });
    </script>';
    

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
                this.disableProperties();
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
            switch(param.type) {
                case "display":
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
                case "signature":
                    const signatureElement = new ReportElementSignature(param.content, param.style, param.label);
                    this.addElement(signatureElement);
                    break;
                default:
                    break;
            }
        });
    }


    // The basic design Mr. Michael wants
    loadBasicDesign(){
        const displayElement = new ReportElementDisplay("Name vom Projekt / Report", "");
        const tableElement = new ReportElementTable("", "", 2, 2);
        console.log("tableElement id " + tableElement._id);
        const descriptionElement = new ReportElementDisplay("Beschreibung", "");
        const textElement  = new ReportElementTextarea("Text", "");
        const textElement2 = new ReportElementTextarea("Text", "");
        console.log(displayElement.content);
        this.addElement(displayElement);
        this.addElement(tableElement);
        this.addElement(descriptionElement);
        this.addElement(textElement);
        this.addElement(textElement2);
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
            this.disableProperties();
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
        
        this.showProperties();
        return;
    }

    
        this.selectedElement = this.elements.find(element => element._id === target.id);
        this.showProperties();
    }
    
showProperties() {
// Implement seperate class for property panel
return;
}

    
    
    // Function to add a new ReportElement to the array
    addElement(element) {
        console.log("Element added");
        if (!(element instanceof ReportGeneratorElement)) {
            throw new TypeError("Element must be an instance of ReportGeneratorElement");
        }
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

    // Append the form to the report container
    this.reportContainer.appendChild(form);
    this.reportContainer.appendChild(footer);

    // Make the elements inside the sortableDiv sortable, using the handleBar for dragging
    $(sortableDiv).sortable({
        handle: ".handle-bar",  // Use the handleBar for dragging
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
        this.reportContainer.querySelector("#add-element-button-wrapper").remove();
        this.reportContainer.querySelector("#save-form-button-wrapper").remove();
        formData.append("form", this.reportContainer.innerHTML);  // Full HTML
        formData.append("parameters", JSON.stringify(parameters));
        //formData.append("storeId", storeId);   // Assuming storeId, userId, etc. are available in the scope
        formData.append("userId", '.$user->id.');
        const reportId = '.$reportId.'+"";
        if(reportId != "" && reportId != "undefined") {
            formData.append("reportId", '.$reportId.');
        }
        //formData.append("ticketId", ticketId);
        //formData.append("socId", socId);
        this.reportContainer.appendChild(addButton);
        this.reportContainer.appendChild(saveButton);
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
            }else{
                // Delegate adding the element to the reportGenerator
                this.reportGenerator.addElement(this.createFinalElement());
                //this.reportGenerator.addElement(this.preview);
                this.hideModal(); // Optionally hide modal after adding the element
            }
               
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
        this.preview = new ReportElementDisplay("Text eingeben", "font-size: 20px; font-weight: bold; width: 100%; text-align: center;");
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
        this.preview = new ReportElementTextarea("", "", "Enter textarea label");
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
                        </select>
                    </div>
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
        // Create the preview table
        this.preview = new ReportElementTable();

        // Assign the onActiveCellChange callback # FUTURE: Change if event emitter is implemented
        this.preview.onActiveCellChange = this.handleActiveCellChange.bind(this);

        // Set the default active cell
        this.preview._activeCell = this.preview._grid[0][0];
        this.handleActiveCellChange(this.preview._activeCell);

        this.mountPreview(container);

        // Event listener for row changes
        container.querySelector("#tableRows").addEventListener("change", (e) => {
            this.preview.rows = e.target.value;
            this.mountPreview(container);
        });

        // Event listener for column changes
        container.querySelector("#tableCols").addEventListener("change", (e) => {
            this.preview.cols = e.target.value;
            this.mountPreview(container);
        });

        // Event listener for label changes
        container.querySelector("#tableLabel").addEventListener("input", (e) => {
            this.preview.label = e.target.value;
            this.mountPreview(container);
        });

        // Event listener for cell content changes
        container.querySelector("#cellContent").addEventListener("input", (e) => {
            if (this.preview._activeCell) {
                this.preview._activeCell.contentElement.content = e.target.value;
                this.mountPreview(container);
                this.preview.initializeContent();
            }
        });

        // Event listener for cell type changes
        container.querySelector("#cellType").addEventListener("change", (e) => {
            if (this.preview._activeCell) {
                let currActiveCell = this.preview._activeCell;
                this.preview._activeCell.changeContentType(e.target.value);
                this.preview._activeCell.contentElement.content = "";
                container.querySelector("#cellContent").value = "";
                if(this.preview._activeCell.contentElement.type === "display") {
                    container.querySelector("#displayType").style.display = "block";
                }else{
                    container.querySelector("#displayType").style.display = "none";
                }
                if(currActiveCell.contentElement.type !== this.preview._activeCell.contentElement.type) {
                    container.querySelector("#predefinedText").value = "default";
                }
                this.mountPreview(container);
                this.preview.initializeContent();
            }
        });

        // Event listener for extendable table checkbox
        container.querySelector("#extendableTable").addEventListener("change", (e) => {
            this.preview.extendable = e.target.checked;
            this.mountPreview(container);
        });

        // Event listener for predefined text selection
        container.querySelector("#predefinedText").addEventListener("change", (e) => {
            if (this.preview._activeCell && this.preview._activeCell.contentElement.type === "display") {
                this.preview._activeCell.contentElement.content = e.target.options[e.target.selectedIndex].text;
                if(e.target.options[e.target.selectedIndex].value !== "default") {
                    this.preview._activeCell.contentElement.contentType = "dynamic";
                    container.querySelector("#cellContent").disabled = true;
                }else{
                    container.querySelector("#cellContent").disabled = false;
                    this.preview._activeCell.contentElement.contentType = "static";
                }
                this.mountPreview(container);
                this.preview.initializeContent();
                // Disable the content input field
                

            }
        });



    }

    // Auxillary function for table creation. Not elegant # FUTURE: Remove when event emitter is implemented
    handleActiveCellChange(activeCell) {
        console.log(`Active cell changed to: ${activeCell.id}`);

        const cellContentType = this.configBox.querySelector(\'#cellType\');
        const cellContentInput = this.configBox.querySelector(\'#cellContent\');

        if (cellContentType && cellContentInput) {
            // Update the selector to match the active cells content type
            cellContentType.value = activeCell.contentElement.type;

            // Update the content input field
            cellContentInput.value = activeCell.contentElement.content;
        } else {
            console.error(\'UI elements not found.\');
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
        const handleBar = document.createElement("div");
        handleBar.classList.add("handle-bar");
        handleBar.style.display = "none";  // Initially hidden
        handleBar.innerHTML = "&#x21C5;";  // Use a hamburger icon (or any icon)
        handleBar.style.cursor = "move";  // Cursor style to indicate draggable handle
        handleBar.style.padding = "5px";
        handleBar.style.backgroundColor = "rgba(224, 224, 224, 0.5)";
        handleBar.style.borderRight = "1px solid #ccc";
        handleBar.style.width = "100%";
        handleBar.style.height = "100%";
        handleBar.style.textAlign = "center";
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
        console.log("Entered");
        console.log(wrapperDiv);
        console.log(handleBar);
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
// Jump: ReportElementTable.js (concrete class)
////////////////////////

class ReportElementTable extends ReportGeneratorElement {
    constructor(content = null, style = "", rows = 2, cols = 2, label = "") {
        super("", style, "table", label);  
        this._rows = Math.max(rows, 1); 
        this._cols = Math.max(cols, 1); 
        this._grid = [];
        this._content = [];
        this._activeCell = null; // Reference to the last clicked cell
        this._onActiveCellChange = null; // Callback function to handle active cell change. I will change this and use an event emitter class instead later on #FUTURE
        this._extendable = false;  // Flag to indicate if the table is extendable
        // Initialize the grid with ReportElementCells
        console.log("Recieved id: " + this._id);
        if(content){
            this._content = content;
            this.initializeGridFromContent();
        }else{
            this.initializeGrid();
            this.initializeContent();
        }
    }

    // Initialize the grid structure based on rows and cols
    initializeGrid() {
        const headerRow = [];
        for(let i = 0; i < this._cols; i++){
            const id = `${this._id}-cell-0-${i}`;
            console.log("Passing id: " + id);
            const newCell = new ReportElementCell("display", { content: "Überschrift" }, id);
            newCell.cellType = "th";  // Use property setter
            headerRow.push(newCell);
        }
        this._grid.push(headerRow);
        for (let i = 1; i < this._rows; i++) {
            const row = [];
            for (let j = 0; j < this._cols; j++) {
                const id = `${this._id}-cell-${i}-${j}`;
                console.log("Passing id: " + id);
                const newCell = new ReportElementCell("display", { content: "Zelle" }, id);
                // Default cellType is "td"; no need to set it
                row.push(newCell);
            }
            this._grid.push(row);
        }
    }



    initializeContent() {
        this._content = this._grid.map(row =>
            row.map(cell => ({
                id: cell.id,
                content: cell.contentElement.content,
                contentType: cell.contentElement.type
            }))
        );
    }

    initializeGridFromContent() {
        this._grid = this._content.map((row, rowIndex) => {
        const newRow = row.map((cellData) => {
            const { id, content, contentType, cellType } = cellData;
            const cell = new ReportElementCell(contentType, { content: content }, id);
            cell.cellType = cellType || (rowIndex === 0 ? "th" : "td");
            return cell;
        });
        return newRow;
        });
    }


    get grid() {
        return this._grid;
    }

    get rows() {
        return this._rows;
    }

    set rows(newRows) {
        if (newRows < 1) newRows = 1; // Prevent less than 1 row

        if (newRows > this._rows) {
            // Add new rows
            for (let i = this._rows; i < newRows; i++) {
                const newGridRow = [];
                const newContentRow = [];
                for (let j = 0; j < this._cols; j++) {
                    const id = `${this._id}-cell-${i}-${j}`;
                    const newCell = new ReportElementCell("display", { content: "Zelle" }, id);
                    newGridRow.push(newCell);
                    newContentRow.push({
                        id: newCell.id,
                        content: newCell.contentElement.content,
                        contentType: newCell.contentElement.type,
                    });
                }
                this._grid.push(newGridRow);
                this._content.push(newContentRow);
            }
        } else if (newRows < this._rows) {
            // Remove extra rows
            this._grid = this._grid.slice(0, newRows);
            this._content = this._content.slice(0, newRows);
        }
        this._rows = newRows;
        
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
        if (newCols < 1) newCols = 1; // Prevent less than 1 column

        if (newCols > this._cols) {
            // Add new columns to existing rows
            for (let j = this._cols; j < newCols; j++) {
                const headerId = `${this._id}-cell-0-${j}`;
                const headerCell = new ReportElementCell("display", { content: "Überschrift" }, headerId);
                headerCell.cellType = "th";
                this._grid[0].push(headerCell);
                this._content[0].push({
                    id: headerCell.id,
                    content: headerCell.contentElement.content,
                    contentType: headerCell.contentElement.type,
                });
            }

            for (let i = 1; i < this._rows; i++) {
                for (let j = this._cols; j < newCols; j++) {
                    const id = `${this._id}-cell-${i}-${j}`;
                    const newCell = new ReportElementCell("display", { content: "Zelle" }, id);
                    this._grid[i].push(newCell);
                    this._content[i].push({
                        id: newCell.id,
                        content: newCell.contentElement.content,
                        contentType: newCell.contentElement.type,
                    });
                }
            }
        } else if (newCols < this._cols) {
            // Remove extra columns from each row
            for (let i = 0; i < this._rows; i++) {
                this._grid[i] = this._grid[i].slice(0, newCols);
                this._content[i] = this._content[i].slice(0, newCols);
            }
        }
        this._cols = newCols;
           
    }


    appendContent(wrapperDiv) {
        const table = document.createElement("table");
        table.classList.add("table", "table-bordered", "report-element");
        if(this._extendable){
            // Set data-extendable attribute
            table.setAttribute("data-extendable", "true");
        }
        table.id = this._id;
        // Create the header row
        const thead = document.createElement("thead");
        const headerRow = document.createElement("tr");
        for (let j = 0; j < this._cols; j++) {
            const cellElement = this._grid[0][j].render();
            cellElement.addEventListener("click", () => this.handleCellClick(0, j));
            headerRow.appendChild(cellElement);
        }
        thead.appendChild(headerRow);

        // Create the body of the table
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

        // Append thead and tbody to the table
        table.appendChild(thead);
        table.appendChild(tbody);

        wrapperDiv.appendChild(table);
    }


     addRow() {
        const id = `${this._id}-cell-${newRowIndex}-${j}`;
        const newCell = new ReportElementCell("display", { content: "New Cell" }, id);
        newRow.push(newCell);
        newContentRow.push({
            id: newCell.id,
            content: newCell.contentElement.content,
            contentType: newCell.contentElement.type,
            cellType: newCell.cellType
        });
    }

    addColumn() {
        const newColIndex = this._cols;
        // Add new header cell
        const headerId = `${this._id}-cell-0-${newColIndex}`;
        const headerCell = new ReportElementCell("display", { content: "New Header" }, headerId);
        headerCell.cellType = "th";
        this._grid[0].push(headerCell);
        this._content[0].push({
            id: headerCell.id,
            content: headerCell.contentElement.content,
            contentType: headerCell.contentElement.type,
            cellType: headerCell.cellType
        });

        for (let i = 1; i < this._rows; i++) {
            const id = `${this._id}-cell-${i}-${newColIndex}`;
            const newCell = new ReportElementCell("display", { content: "New Cell" }, id);
            this._grid[i].push(newCell);
            this._content[i].push({
                id: newCell.id,
                content: newCell.contentElement.content,
                contentType: newCell.contentElement.type,
                cellType: newCell.cellType,
                checked: cell.contentElement.checked || false
            });
        }
        this._cols++;
    }

    // Remove the last row
    removeRow() {
        if (this._rows > 1) {
            this._grid.pop();
            this._content.pop();
            this._rows--;
            this.render();  // Re-render to reflect changes
        }     
    }


    // Remove the last column
    removeColumn() {
        if (this._cols > 1) {
            for (let i = 0; i < this._rows; i++) {
                this._grid[i].pop();
                this._content[i].pop();
            }
            this._cols--;
            this.render();  // Re-render to reflect changes
        }
    }

    // Access a specific cell
    getCell(row, col) {
        if (row >= 0 && row < this._rows && col >= 0 && col < this._cols) {
            return this._grid[row][col];
        }else{
            throw new Error("Invalid row or column index");
        }
        return null;
    }

    // Change type of specified cell
    changeCellChildElement(row, col, element) {
        if (row >= 0 && row < this._rows && col >= 0 && col < this._cols) {
        const cell = this._grid[row][col];
        if (typeof element === \'string\') {
            // `element` is the new contentType
            cell.changeContentType(element);
        } else if (element instanceof ReportGeneratorElement) {
            // Assign a new contentElement to the cell
            cell.contentElement = element;
        } else {
            throw new Error("Invalid element parameter. Must be a valid contentType string or a ReportGeneratorElement instance");
        }
        // Update content representation
        this._content[row][col] = {
            id: cell.id,
            content: cell.contentElement.content,
            contentType: cell.contentElement.type,
            cellType: cell.cellType
        };
        // Optionally re-render if needed
    } else {
        throw new Error("Invalid row or column index");
    }
    }

    changeCellType(row, col, type) {
        if (type !== "th" && type !== "td") {
            throw new Error("Invalid cell type. Must be either \'th\' or \'td\'");
        }
        if (row >= 0 && row < this._rows && col >= 0 && col < this._cols) {
            const cell = this._grid[row][col];
            cell.cellType = type;
            this._content[row][col].cellType = type;
            // Optionally re-render if needed
        } else {
            throw new Error("Invalid row or column index");
        }
    }

    changeCellChildContent(row, col, content) {
        if (row >= 0 && row < this._rows && col >= 0 && col < this._cols) {
            const cell = this._grid[row][col];
            cell.contentElement.content = content;
            this._content[row][col].content = content;
            // Optionally re-render if needed
        } else {
            throw new Error("Invalid row or column index");
        }
    }

    handleCellClick(row, col) {
    const cell = this._grid[row][col];
    console.log("Active cell: " + this._activeCell.id);

    // Remove highlight from the previous active cell
    if (this._activeCell && this._activeCell !== cell) {
        this._activeCell.element.classList.remove(\'active-cell\');
    }

    // Update the active cell
    this._activeCell = cell;
    console.log("Active cell: " + this._activeCell.id);

    // Add highlight to the new active cell
    this._activeCell.element.classList.add(\'active-cell\');

    // Invoke the callback if its set
    if (typeof this.onActiveCellChange === \'function\') {
        this.onActiveCellChange(this._activeCell);
    }
}

}




////////////////////////
// Jump: ReportElementCell.js
////////////////////////

class ReportElementCell {
    constructor(contentType, contentData = {}, id) {
        this._id = id;  // Unique identifier for the cell
        this._cellType = "td";  // Default cell type

        // Create the content element
        this._contentElement = this.createContentElement(contentType, contentData);

        // Validate the content element
        if (!(this._contentElement instanceof ReportGeneratorElement)) {
            throw new Error("ContentElement of Cell must be an instance of ReportGeneratorElement");
        }
    }

    get contentElement(){
        return this._contentElement;
    }

    set contentElement(contentElement) {
        if (contentElement instanceof ReportGeneratorElement) {
            this._contentElement = contentElement;
        } else {
            throw new Error("ContentElement must be an instance of ReportGeneratorElement");
        }
    }

    get id() {
        return this._id;
    }

    get cellType() {
        return this._cellType;
    }

    set cellType(cellType) {
        if (cellType !== "td" && cellType !== "th") {
            throw new Error(`Unsupported cell type: ${cellType}`);
        }
        this._cellType = cellType;
    }

    createContentElement(contentType, contentData = {}) {
        switch (contentType) {
            case "display":
            case "text":
                return new ReportElementDisplay(contentData.content || "");
            case "input":
                return new ReportElementInput(
                    contentData.id || "",
                    contentData.style || "",
                    contentData.label || "Input Label"
                );
            case "textarea":
                return new ReportElementTextarea(
                    contentData.id || "",
                    contentData.style || "",
                    contentData.label || "Textarea Label"
                );
            case "upload":
                return new ReportElementUpload(
                    contentData.id || "",
                    contentData.style || ""
                );
            case "checkbox":
                return new ReportElementCheckbox(
                    contentData.id || "",
                    contentData.style || "",
                    contentData.label || "Checkbox Label"
                );
            // Add more cases as needed
            default:
                throw new Error(`Unsupported content type: ${contentType}`);
        }
    }

    changeContentType(newContentType, contentData = {}) {
        const newContentElement = this.createContentElement(newContentType, contentData);
        this.contentElement = newContentElement;
    }
    
   render() {
        const cell = document.createElement(this._cellType);
        cell.id = this._id;

        // Use renderContentOnly to get content without wrapperDiv and handleBar
        const contentElement = this._contentElement.renderContentOnly();
        cell.appendChild(contentElement);

        // Store a reference to the rendered element
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