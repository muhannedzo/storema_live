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
// print '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.5.0-beta4/html2canvas.min.css">';
// print '<script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/2.3.1/purify.min.js"></script>';
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
        const displayElement = new ReportElementDisplay("Name vom Projekt / Report", "");
        const tableElement = new ReportElementTable("", "", 2, 2);
        console.log("tableElement id " + tableElement._id);
        const descriptionElement = new ReportElementDisplay("Beschreibung", "");
        const textElement  = new ReportElementTextarea("Text", "");
        const textElement2 = new ReportElementTextarea("Text", "");
        console.log(displayElement.content);
        reportGenerator.addElement(displayElement);
        reportGenerator.addElement(tableElement);
        reportGenerator.addElement(descriptionElement);
        reportGenerator.addElement(textElement);
        reportGenerator.addElement(textElement2);
        reportGenerator.generateReport();
    });
     </script>';
    }else if($action == "edit") {
    $reportId = GETPOST("reportId");
    //echo $reportId;
    $sql = "SELECT * FROM llx_reports WHERE rowid = ".$reportId;
    $res = $db->query($sql)->fetch_all();
    $userId = $res[0][1] ? $res[0][1] : $user->id;
    // Base 64 decode the content of the report res[2]
    $parameters = base64_decode($res[0][3]);
    //echo $parameters;
    echo '<script>
    document.addEventListener("DOMContentLoaded", () => {
    const reportGenerator = new ReportGenerator(document.getElementById("report-container"), document.getElementById("report-property-panel"));
    const reportParameters = JSON.parse(`'.$parameters.'`);
    
    reportParameters.forEach(param => {
        switch(param.type) {
            case "display":
                const displayElement = new ReportElementDisplay(param.content, param.style, param.label, param.contentType);
                reportGenerator.addElement(displayElement);
                break;
            case "input":
                const inputElement = new ReportElementInput(param.content, param.style, param.label);
                reportGenerator.addElement(inputElement);
                break;
            case "textarea":
                const textareaElement = new ReportElementTextarea(param.content, param.style, param.label);
                reportGenerator.addElement(textareaElement);
                break;
            case "table":
                const tableElement = new ReportElementTable(param.content, param.style, param.content.length, param.content[0].length, param.label);
                param.content.forEach((row, rowIndex) => {
                    row.forEach((cell, cellIndex) => {
                        const cellElement = tableElement.grid[rowIndex][cellIndex];
                        cellElement.content = cell.content;
                        cellElement.styles = cell.styles;
                        cellElement.label = cell.label;
                        cellElement.contentType = cell.contentType;
                        tableElement.updateContent(rowIndex, cellIndex);
                    });
                });
                reportGenerator.addElement(tableElement);
                break;
            case "div":
                const divElement = new ReportElementDiv(param.content, param.style);
                reportGenerator.addElement(divElement);
                break;
            default:
                break;
        }
    });
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
//     
//     //If an element has been selected collect its styles and fill the property panel with the values
//     if (this.selectedElement) {
//         const style = this.selectedElement.styles;

//         // Reset all input fields in the property panel
//         document.querySelectorAll(".report-property").forEach(input => {
//             input.value = "";
//             input.disabled = false;
//         });

//         // Reset all select fields in the property panel
//         document.querySelectorAll("select").forEach(select => {
//             select.disabled = false;
//         });

//         // Take styles and fill the fields in the property panel
//         const styleProperties = style.split(";").map(prop => prop.trim()).filter(prop => prop);
//         styleProperties.forEach(prop => {
//             const [key, value] = prop.split(":").map(item => item.trim());

//             // Check all css properties and remove the unit. So 200px -> 200 etc.
//             switch (key) {
//             // For width and height we can use pixels or percentage so we need to check for both. If px then remove px from string and set unit to px. Same for %
//                 case "width":
//                      if (value.endsWith("px")) {
//                         document.getElementById("property-width").value = value.replace("px", "");
//                         document.getElementById("property-width").dataset.unit = "px";
//                         document.getElementById("property-width-unit").value = "px";
//                     } else if (value.endsWith("%")) {
//                         document.getElementById("property-width").value = value.replace("%", "");
//                         document.getElementById("property-width").dataset.unit = "%";
//                         document.getElementById("property-width-unit").value = "%";
//                     }
//                     break;
//                 case "height":
//                     if (value.endsWith("px")) {
//                     
//                         document.getElementById("property-height").value = value.replace("px", "");
//                         document.getElementById("property-height").dataset.unit = "px";
//                         document.getElementById("property-height-unit").value = "px";
//                     } else if (value.endsWith("%")) {
//                         document.getElementById("property-height").value = value.replace("%", "");
//                         document.getElementById("property-height").dataset.unit = "%";
//                         document.getElementById("property-height-unit").value = "%";
//                     }
//                     break;
//                 case "color":
//                     document.getElementById("property-style-color").value = value;
//                     break;
//                 case "background-color":
//                     document.getElementById("property-style-background-color").value = value;
//                     break;
//                 case "font-size":
//                     document.getElementById("property-style-font-size").value = value.replace("px", "");
//                     break;
//                 case "font-weight":
//                     document.getElementById("property-style-font-weight").value = value;
//                     break;
//                 case "padding":
//                     document.getElementById("property-style-padding").value = value.replace("px", "");
//                     break;
//                 case "margin-top":
//                     document.getElementById("property-style-margin").value = value.replace("px", "");
//                     break;
//                 case "border":
//                     document.getElementById("property-style-border").value = value.replace("px", "");
//                     break;
//                 default:
//                     break;
//             }
//         });

//         // Set the position value
        
//     }
}



    // The code that follows now is shit but I had to do it this way, because i had to speed up.
    // Future: Create seperate class : elementCreator 
    // Methods: 
    // generateUI => switch statement from below
    // handlePreviewUpdate 
    // handleCellSelection
    // List of key/value pairs for renderable elements e.g table -> new ReportElementTable , yesNo -> new ReportElementRadio() + new ReportElementRadio() ...

    // handleAddElementButtonClick(e) {
    
    //   // Show the Bootstrap modal
    // const modal = new bootstrap.Modal(document.getElementById("elementModal"), {
    //     keyboard: true
    // });
    // modal.show();

    // // modal elements
    // const configBox = document.getElementById("elementConfigBox");
    // const addElementBtn = document.getElementById("addElementBtn");

    // addElementBtn.disabled = true;

    // // event listeners for buttons inside modal
    // const options = document.querySelectorAll("#elementModal .list-group-item");
    // options.forEach(option => {
    //     option.addEventListener("click", (event) => {
    //         const value = event.target.getAttribute("data-value");
    //         configBox.innerHTML = ""; // Clear previous config
    //         const selection = document.createElement("div");
    //         const preview = document.createElement("div");
    //         preview.classList.add("responsive");
    //         preview.id = "preview";
    //         selection.id = "selection";
    //         //Append the selection and preview divs to the configBox
    //         configBox.appendChild(selection);
    //         configBox.appendChild(preview); 
    //         preview.style.display = "flex";
    //         preview.style.flexDirection = "column";
    //         preview.style.gap = "1rem";

    //         preview.innerHTML = "Vorschau: ";
    //         configBox.style.display = "flex";
    //         configBox.style.flexDirection = "column";
    //         configBox.style.gap = "1rem";
    //         let previewReportElement = null;
           
    //         switch (value) {
    //             case "display":
    //                 selection.innerHTML = `
    //                     <div class="mb-3">
    //                         <input type="text" id="displayText" class="form-control" placeholder="Text eingeben">
    //                         <div>oder</div>
    //                         <select id="predefinedText" class="form-select">
    //                             <option value="default" selected>Dynamische Werte</option>
    //                             <option value="projectname">Projektname / Report</option>
    //                             <option value="ticketnumber">Ticketnummer</option>
    //                             <option value="currentDate">Aktuelles Datum</option>
    //                             <option value="creationDate">Erstellungsdatum</option>
    //                             <option value="time">Uhrzeit</option>
    //                             <option value="filial">Filiale</option>
    //                             <option value="severity">Dringlichkeit</option>
    //                             <option value="postalcode">Plz</option>
    //                             <option value="city">Stadt</option>
    //                             <option value="street">Straße</option>
    //                             <option value="tickettype">Ticketart</option>
    //                             <option value="telephonenumber">Telefonnummer</option>
    //                             <option value="ticketdescription">Beschreibung/Auftrag</option>
    //                         </select>
    //                     </div>
    //                 `;
    //                 previewReportElement = new ReportElementDisplay("Text eingeben", "font-size: 20px; font-weight: bold; width: 100%; text-align: center;");
    //                 preview.appendChild(previewReportElement.render());
    //                 previewReportElement.clicked = true;
    //                 // Preview and previewReportElement should change when input changes
    //                 document.getElementById("displayText").addEventListener("input", (e) => {
    //                     preview.innerHTML = "Vorschau:";
    //                     previewReportElement.content = e.target.value;
    //                     preview.appendChild(previewReportElement.render());
    //                 });
    //                 document.getElementById("predefinedText").addEventListener("change", (e) => {
    //                     preview.innerHTML = "Vorschau:";
    //                     if(e.target.value === "default") {
    //                         previewReportElement.content = "";
    //                         document.getElementById("displayText").disabled = false;
    //                     } else {
    //                         previewReportElement.content = e.target.options[e.target.selectedIndex].text+" (Automatisch)";
    //                         previewReportElement.contentType = "dynamic";
    //                         document.getElementById("displayText").disabled = true;
    //                     }
                        
    //                     //previewReportElement.content = e.target.value;
    //                     preview.appendChild(previewReportElement.render());
    //                 });
    //                 break;
    //             case "input":
    //                 selection.innerHTML = `
    //                     <div class="mb-3">
    //                         <input type="text" id="inputLabel" class="form-control" placeholder="Überschrift angeben">
    //                     </div>
    //                 `;
    //                 previewReportElement = new ReportElementInput("", "", "Überschrift angeben");
    //                 preview.appendChild(previewReportElement.render());
    //                 previewReportElement.clicked = true;
    //                 document.getElementById("inputLabel").addEventListener("input", (e) => {
    //                     preview.innerHTML = "Vorschau:";
    //                     previewReportElement.label = e.target.value;
    //                     preview.appendChild(previewReportElement.render());
    //                 });
    //                 break;
    //             case "textarea":
    //                 selection
    //                 .innerHTML = `
    //                     <div class="mb-3">
                            
    //                         <input type="text" id="textareaLabel" class="form-control" placeholder="Überschrift angeben">
    //                     </div>
    //                 `;
    //                 previewReportElement = new ReportElementTextarea("", "", "Enter textarea label");
    //                 preview.appendChild(previewReportElement.render());
    //                 previewReportElement.clicked = true;
    //                 document.getElementById("textareaLabel").addEventListener("input", (e) => {
    //                     preview.innerHTML = "Vorschau:";
    //                     previewReportElement.label = e.target.value;
    //                     preview.appendChild(previewReportElement.render());
    //                 });
    //                 break;
    //             case "table":
    //                 selection.innerHTML = `
    //                 <div class="mb-3" id="tableLabelSelection">
    //                 Tabellenüberschrift
    //                 <input type="text" id="tableLabel" class="form-control" value="Table">
    //                 </div>
    //                 <div class="mb-3 d-flex gap-1 flex-column" id="tableStructureSelection">
    //                 <label for="tableRows">Tabellenstruktur</label>
    //                     <div class="mb-3">
    //                         <label for="tableRows">Anzahl Zeilen</label>
    //                         <input type="number" id="tableRows" class="form-control" value=2>
    //                     </div>
    //                     <div class="mb-3">
    //                         <label for="tableCols">Anzahl Spalten</label>
    //                         <input type="number" id="tableCols" class="form-control" value=2>
    //                     </div>
    //                 </div>
    //                 <div class="mb-3 d-flex gap-1 flex-column" id="cellConfiguration" >
    //                     <label for="tableContent">Ausgewählte Zelle bearbeiten</label>
    //                     <div class="d-flex gap-1 flex-column">
    //                         <select id="cellTypeSelection" class="form-select" disabled>
    //                             <option value="default" selected>Zellentyp auswählen</option>
    //                             <option value="text">Einfacher Text</option>
    //                             <option value="input">Eingabefeld</option>
    //                             <option value="checkbox">Checkbox</option>
    //                             <option value="textarea">Textfeld</option>
    //                             <option value="upload">Upload</option>
    //                         </select>
    //                     </div>
    //                     <div id="cellContent"></div>
    //                 </div>
    //                 `;
    //                 // Function for filling the cell content
    //                 document.getElementById("cellTypeSelection").addEventListener("change", (e) => {
    //                     if(document.getElementById("cellTypeSelection").value === "text"){
    //                         const cellContent = document.getElementById("cellContent");
    //                         cellContent.innerHTML = "Anzeigetext eingeben";
    //                         const display = document.createElement("input");
    //                         display.setAttribute("type", "text");
    //                         display.setAttribute("id", "tableContent");
    //                         display.setAttribute("class", "form-control");
    //                         display.setAttribute("placeholder", "Text eingeben");
    //                         if(this.selectedElement) {
    //                             display.value = this.selectedElement.content;
    //                         }
    //                         cellContent.appendChild(display);
    //                         document.getElementById("cellConfiguration").appendChild(cellContent);
    //                     }else if(document.getElementById("cellTypeSelection").value === "upload"){
    //                         const cellContent = document.getElementById("cellContent");
    //                         cellContent.innerHTML = "";
    //                     }
    //                 });
    //                 // Preview of table that is being configured
    //                     previewReportElement = new ReportElementTable("", "", 2, 2);
    //                     preview.appendChild(previewReportElement.render());
    //                     previewReportElement.clicked = true;
    //                     document.getElementById("tableRows").addEventListener("change", (e) => {
    //                         preview.innerHTML = "Vorschau:";
    //                         previewReportElement.rows = e.target.value;
                            
    //                         preview.appendChild(previewReportElement.render());
    //                         if(this.selectedElement){
    //                             if(this.selectedElement.id.split("-")[3] >= e.target.value){
    //                                 this.selectedElement = null;
    //                                 this.disableProperties();
    //                             }
    //                         }
    //                     });
    //                     // Function for changing the number of columns in the table
    //                     document.getElementById("tableCols").addEventListener("change", (e) => {
    //                         preview.innerHTML = "Vorschau:";
    //                         previewReportElement.cols = e.target.value;
    //                         preview.appendChild(previewReportElement.render());
    //                         if(this.selectedElement){
    //                             if(this.selectedElement.id.split("-")[4] >= e.target.value){
    //                                 this.selectedElement = null;
    //                                 this.disableProperties();
    //                             }
    //                         }
    //                     });
    //                     // Function for selecting <based on clicked cell type
    //                     document.getElementById("cellTypeSelection").addEventListener("change", (e) => {
    //                         if(this.selectedElement){
    //                             this.selectedElement.contentType = document.getElementById("cellTypeSelection").value;
    //                             preview.innerHTML = "Vorschau:";
    //                             preview.appendChild(previewReportElement.render());
    //                         }
    //                     });

                        
    //                     // Function for changing the label of the table
    //                     document.getElementById("tableLabel").addEventListener("input", (e) => {
    //                         preview.innerHTML = "Vorschau:";
    //                         previewReportElement.label = e.target.value;
    //                         preview.appendChild(previewReportElement.render());
    //                     });

    //                     // Function for changing the selected cell\'s type based on selection
    //                     document.getElementById("cellContent").addEventListener("input", (e) => {
    //                         if(this.selectedElement){
    //                             this.selectedElement.content = e.target.value;
    //                             preview.innerHTML = "Vorschau:";
    //                             preview.appendChild(previewReportElement.render());
    //                         }
    //                     });

    //                     // Detect cell clicks similar to handleClick() and selectElement but just for modal
    //                     document.getElementById("elementModal").addEventListener("click", (e) => {
                        
    //                     const cellHTML = e.target.closest("td, th");
    //                         if(cellHTML){
    //                             const cellObj = previewReportElement.grid[cellHTML.id.split("-")[3]][cellHTML.id.split("-")[4]]
    //                             this.selectedElement = cellObj;
    //                             document.getElementById("cellTypeSelection").value = this.selectedElement.contentType;
    //                             document.getElementById("cellTypeSelection").disabled = false;
    //                             document.getElementById("cellTypeSelection").dispatchEvent(new Event("change"));
    //                         }else{
    //                             // ?
    //                         }
                            
                           
    //                     });
                        
    //                 break;
    //             default:
    //                 break;
    //         }

    //         // Enable the add button when an option is selected
    //         addElementBtn.disabled = false;

    //         // Set the click event for the add button
    //         // Bind input to ReportElement object and add it to the reportGenerator
    //         addElementBtn.onclick = () => {
    //             let element;
    //             switch (value) {
    //                 case "display":
    //                     element = new ReportElementDisplay(previewReportElement.content, previewReportElement.styles, previewReportElement.label, previewReportElement.contentType);
    //                     break;
    //                 case "input":
    //                     element = new ReportElementInput(previewReportElement.content, previewReportElement.styles, previewReportElement.label);
    //                     break;
    //                 case "textarea":
    //                     element = new ReportElementTextarea(previewReportElement.content, previewReportElement.styles, previewReportElement.label);
    //                     break;
    //                 case "table":
    //                     element = previewReportElement;
    //                     break;
    //                 case "div":
    //                     element = new ReportElementDiv(previewReportElement.content, previewReportElement.styles);
    //                     break;
    //                 case "upload":
    //                     element = previewReportElement;
    //                     break;
    //                 default:
    //                     break;
    //             }
    //             console.log("Adding element", element); 
    //             this.addElement(element);
    //             this.selectedElement = null;
    //             //this.generateReport();
    //             modal.hide(); // Hide the modal after adding the element
    //             console.log(this.elements);
    //         };
    //     });
    // });


    // }
    
    
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
            // { value: "clock", label: "Uhrzeit", factory: this.createClockElementUI.bind(this) },
            // { value: "div", label: "Box", factory: this.createDivElementUI.bind(this) }
        ];

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
                // Delegate adding the element to the reportGenerator
                this.reportGenerator.addElement(this.createFinalElement());
                //this.reportGenerator.addElement(this.preview);
                this.hideModal(); // Optionally hide modal after adding the element
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

    // Hide the modal
    hideModal() {
        const bootstrapModal = bootstrap.Modal.getInstance(this.modal);
        bootstrapModal.hide();
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
                    <option value="city">Stadt</option>
                    <option value="street">Straße</option>
                    <option value="tickettype">Ticketart</option>
                    <option value="telephonenumber">Telefonnummer</option>
                    <option value="ticketdescription">Beschreibung/Auftrag</option>
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
                this.mountPreview(container);
            }else{
                document.getElementById("displayText").disabled = false;
                this.preview.content = "Text eingeben";
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
    // #TODO : Add cell configuration
    createTableElementUI(container) {
        container.innerHTML = `
            <div class="mb-3" id="tableLabelSelection">
                Tabellenüberschrift
                <input type="text" id="tableLabel" class="form-control" value="Table">
            </div>
            <div class="mb-3 d-flex gap-1 flex-column" id="tableStructureSelection">
                <label for="tableRows">Tabellenstruktur</label>
                <div class="mb-3">
                    <label for="tableRows">Anzahl Zeilen</label>
                    <input type="number" id="tableRows" class="form-control" value=2>
                </div>
                <div class="mb-3">
                    <label for="tableCols">Anzahl Spalten</label>
                    <input type="number" id="tableCols" class="form-control" value=2>
                </div>
            </div>
        `;
        this.preview = new ReportElementTable("", "", 2, 2);
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
    }

    // Create UI for Upload element
    createUploadElementUI(container) {
        container.innerHTML = `
            <div class="mb-3">
                <input type="text" id="uploadLabel" class="form-control" placeholder="Überschrift angeben">
            </div>
        `;
        this.preview = new ReportElementUpload("", "");
        this.mountPreview(container);

        // Event listener for input changes
        container.querySelector("#uploadLabel").addEventListener("input", (e) => {
            this.preview.label = e.target.value;
            this.mountPreview(container);
        });
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
        if(this.constructor.name !== "ReportElementCell"){
            console.log("Cell detected, skipping");
            ReportGeneratorElement.nextId++;
        }
        this._id = `${type}-${ReportGeneratorElement.nextId}`;
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
            content: this._content,
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

        // wrapperDiv.addEventListener("mouseenter", () => {
        //     console.log("Entered");
        //     if (!this._clicked) {  // Only start the timeout if no click has occurred
        //         this.handleBarTimeout = setTimeout(() => {
        //             handleBar.style.display = "flex";  // Show handleBar using flex display
        //             wrapperDiv.style.cursor = "move";  // Change cursor to move
        //             handleBar.style.justifyContent = "center";  // Center the icon
        //             handleBar.style.alignItems = "center";  // Center the icon
        //         }, 1000);  // Show after 1 second
        //     }
        // });

        // wrapperDiv.addEventListener("mouseleave", () => {
        //     clearTimeout(this.handleBarTimeout);  // Clear any pending timeout
        //     handleBar.style.display = "none";  // Hide the handleBar
        //     wrapperDiv.style.cursor = "default";  // Reset cursor to default
        // });

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
        console.log("Passing id: " + `${this._id}-cell-0-${i}`);
            //headerRow.push(new ReportElementCell("Überschrift", "", "th", `${this._id}-cell-0-${i}`, this.updateContent.bind(this))); 
            const newCell = new ReportElementCell(new ReportElementDisplay("Überschrift"), `${this._id}-cell-0-${i}`);
            newCell.setCellType("th"); 
            headerRow.push(newCell);
        }
        this._grid.push(headerRow);
        for (let i = 1; i < this._rows; i++) {
            const row = [];
            for (let j = 0; j < this._cols; j++) {
            console.log("Passing id: " + `${this._id}-cell-${i}-${j}`);
                //row.push(new ReportElementCell("Cell", "", "td", `${this._id}-cell-${i}-${j}`, this.updateContent.bind(this)));
                row.push(new ReportElementCell(new ReportElementDisplay("Zelle"), `${this._id}-cell-${i}-${j}`));
            }
            this._grid.push(row);
        }
    }



    initializeContent() {
        this._content = this._grid.map(row =>
            row.map(cell => ({
                id: cell._id,
                content: cell._contentElement.content,
                contentType: cell._contentElement._type
            }))
        );
    }

    initializeGridFromContent() {
        this._grid = this._content.map((row, rowIndex) =>
            row.map((cell) => new ReportElementCell(
                this.createContentElement(cell.contentType, cell.content),
                cell.id
            ))
        );
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
                    newGridRow.push(new ReportElementCell(new ReportElementDisplay("Zelle"), `${this._id}-cell-${i}-${j}`));
                    newContentRow.push(newGridRow[j].params);
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

    set cols(newCols) {
        if (newCols < 1) newCols = 1; // Prevent less than 1 column

        if (newCols > this._cols) {
            // Add new columns to existing rows
            for (let j = this._cols; j < newCols; j++) {
                    const cell = new ReportElementCell(new ReportElementDisplay("Überschrift"), `${this._id}-cell-0-${j}`);
                    cell.setCellType("th");
                    this._grid[0].push(cell);
                    this._content[0].push(this._grid[0][j].params);
                }

            for (let i = 1; i < this._rows; i++) {
                for (let j = this._cols; j < newCols; j++) {
                    this._grid[i].push(new ReportElementCell(new ReportElementDisplay("Zelle") , `${this._id}-cell-${i}-${j}`));
                    this._content[i].push(this._grid[i][j].params);
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
        const newRow = [];
        for (let j = 0; j < this._cols; j++) {
            newRow.push(new ReportElementCell(new ReportElementDisplay("New Cell"), `table-cell-${this._rows}-${j}`));
        }
        this._grid.push(newRow);
        this._content.push(newRow.map(cell => ({
            id: cell._id,
            content: cell._contentElement.content,
            contentType: cell._contentElement._type
        })));
        this._rows++;
    }

    addColumn() {
        this._grid[0].push(new ReportElementCell(new ReportElementDisplay("New Header"), `table-header-${this._cols}`).cellType("th"));
        this._content[0].push({
            id: `table-header-${this._cols}`,
            content: "New Header",
            contentType: "display"
        });
        for (let i = 1; i < this._rows; i++) {
            this._grid[i].push(new ReportElementCell(new ReportElementDisplay("New Cell"), `table-cell-${i}-${this._cols}`));
            this._content[i].push({
                id: `table-cell-${i}-${this._cols}`,
                content: "New Cell",
                contentType: "input"
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
        if (row < this._rows && col < this._cols) {
            return this._grid[row][col];
        }else{
            throw new Error("Invalid row or column index");
        }
        return null;
    }

    // Change type of specified cell
    changeCellContentType(row, col, type) {
        if (row < this._rows && col < this._cols) {
            this._grid[row][col].contentType = type;
            this._content[row][col].contentType = type;
            this.render();  // Re-render to reflect changes
        }else{
            throw new Error("Invalid row or column index");
        }
    }

    changeCellType(row, col, type){
        if(type !== "th" && type !== "td"){
            throw new Error("Invalid cell type. Must be either \'th\' or \'td\'");
        }
        if(row < this._rows && col < this._cols){
            this._grid[row][col].type = type;
            this._content[row][col].type = type;
            this.render();
        }else{
            throw new Error("Invalid row or column index");
        }
    }

    changeCellContent(row, col, content) {
        if (row < this._rows && col < this._cols) {
            this._grid[row][col].content = content;
            this.render();  // Re-render to reflect changes
        }
    }

}




////////////////////////
// Jump: ReportElementCell.js
////////////////////////

class ReportElementCell {
    constructor(contentElement, id) {
        this._contentElement = contentElement;  // This is an instance of a ReportElement, like ReportElementDisplay or ReportElementInput
        this._id = id;  // Unique identifier tied to table and position
        this._cellType = "td";
    }

    setContent(contentElement) {
        this._contentElement = contentElement;
    }

    setCellType(cellType) {
        if (cellType !== "td" && cellType !== "th") {
            console.error("Unsupported cell type");
            return;
        }
        this._cellType = cellType;
    }

    changeContentType(newContentType) {
        switch (newContentType) {
            case "display":
                this._contentElement = new ReportElementDisplay("Updated Display");
                break;
            case "input":
                this._contentElement = new ReportElementInput("Updated Input");
                break;
            case "table":
                this._contentElement = new ReportElementTable(2, 2, "Nested Table");
                break;
            default:
                console.error("Unsupported content type");
        }
    }
    
   render() {
        const cell = this._cellType === "td" ? document.createElement("td") : document.createElement("th");
        cell.id = this._id;

        // Use renderContentOnly to get content without wrapperDiv and handleBar
        const contentElement = this._contentElement.renderContentOnly();
        cell.appendChild(contentElement);
        return cell;
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
    constructor( content = "", style ="", label = "", type = "file") {
        super( content, style, "file", label);
        this._label = label;
        this._content = [];
    }


    appendContent(wrapperDiv){
        const input = document.createElement("input");
        input.type = "file";
        input.id = this._id;
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


</style>';

}