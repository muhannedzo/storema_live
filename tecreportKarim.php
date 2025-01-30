<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       stores/storesindex.php
 *	\ingroup    stores
 *	\brief      Home page of stores top menu
 */

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
print '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">';
print '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>';
print '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.5.0-beta4/html2canvas.min.css">';
print '<script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/2.3.1/purify.min.js"></script>';
print '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>';

dol_include_once('/ticket/class/ticket.class.php');
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/stores/class/branch.class.php';
dol_include_once('/ticket/class/ticket.class.php');
dol_include_once('/societe/class/societe.class.php');
dol_include_once('/projet/class/project.class.php');
dol_include_once('/stores/compress.php');


/*
 * View
 */

 $form = new Form($db);
 $formfile = new FormFile($db);
 $ticketId = GETPOST('id', 'int');
 
 $object = new Ticket($db);
 $object->fetch($ticketId);
 $socid = $object->socid;
 $storeid = $object->array_options["options_fk_store"];
 $company = new Societe($db);
 $company->fetch($socid);
 $store = new Branch($db);
 $store->fetch($storeid);
 $project = new Project($db);
 $project->fetch($object->fk_project);
 $compress = new Compress();
 $userId = $user->id;
 llxHeader("", $langs->trans("Report"));
 
 print load_fiche_titre($langs->trans("TicketReport").$project->title, '', '');
 //////////////////////////////////////////////////////////////////////////////////////////
$existingReportSQL = "SELECT content, parameters, rowid FROM llx_tec_forms WHERE fk_ticket = ".$ticketId;
$existingReportRes = $db->query($existingReportSQL)->fetch_all();

if($existingReportRes){
$tecFormId = $existingReportRes[0][2];
$form = base64_decode($existingReportRes[0][0]);
//echo base64_decode($existingReportRes[0][1]);
// Change later: Right now we have to manually create report-form and attach form to it when we open an already existing form
echo "<form id='report-form'>".$form."</form>";
echo '<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-body p-0">
        <img src="" id="modalImage" class="img-fluid" alt="Full Size Image">
      </div>
      <div class="modal-footer p-2">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>';
}else{
//echo "In second case";
$reportSQL = "SELECT * FROM llx_reports WHERE projectid = ".$object->fk_project;
$reportRes = $db->query($reportSQL)->fetch_all();
if($reportRes){
$reportData = $reportRes[0];
$form = base64_decode($reportData[2]);
echo $form;
echo '<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-body p-0">
        <img src="" id="modalImage" class="img-fluid" alt="Full Size Image">
      </div>
      <div class="modal-footer p-2">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>';
}else{
    $basicSQL = "SELECT * FROM llx_reports WHERE rowid = 0";
    $basicRes = $db->query($basicSQL)->fetch_all();
    $basicData = $basicRes[0];
    $form = base64_decode($basicData[2]);
    echo $form;

    
    echo '
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
            <div class="modal-body p-0">
                <img src="" id="modalImage" class="img-fluid" alt="Full Size Image">
            </div>
            <div class="modal-footer p-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
            </div>
        </div>
    </div>';
    
}
}



echo '<script>
// Define global variables for identifiers
var ticketId ='.$ticketId.';
var userId ='.$user->id.';
var storeId ='.$storeid.';
var socId = '.$object->fk_soc.';

fetchUploadedImages();
    setupCanvasEvents();

    setupSaveButtons();

    

    

function setupFileInputs() {
    var fileInputs = document.querySelectorAll(\'input[type="file"]\');
    fileInputs.forEach(function(input) {
        input.accept = ".jpg, .png";
        input.disabled = false;

        let uploadedImagesContainer = input.parentElement.querySelector(\'.uploaded-images-container\');
        if (!uploadedImagesContainer) {
            uploadedImagesContainer = document.createElement("div");
            uploadedImagesContainer.classList.add("uploaded-images-container", "row");
            input.parentElement.appendChild(uploadedImagesContainer);
        }

        // Attach change event listener to this file input
        input.addEventListener("change", function() {
            const files = input.files;
            if (files.length > 1) {
                alert("Bitte nur eine Datei auswählen."); // "Please select only one file."
                input.value = ""; // Reset the input
                return;
            }
            if (files.length === 1) {
                uploadFiles(files, input, uploadedImagesContainer);
            }
        });
    });
}

function setupCanvasEvents() {
    var canvases = document.querySelectorAll(\'canvas.report-element\');
    canvases.forEach(function(canvas) {
        setupCanvasEventForCanvas(canvas);
    });
}

function setupCanvasEventForCanvas(canvas) {
    setupCanvas(canvas);
    const context = canvas.getContext("2d");
    let isDrawing = false;
    let lastX = 0;
    let lastY = 0;

    canvas.addEventListener("mousedown", startDrawing);
    canvas.addEventListener("mousemove", draw);
    canvas.addEventListener("mouseup", stopDrawing);
    canvas.addEventListener("mouseleave", stopDrawing);

    // Add touch event listeners
    canvas.addEventListener("touchstart", (event) => {
        event.preventDefault();
        startDrawing(event);
    });
    canvas.addEventListener("touchmove", (event) => {
        event.preventDefault();
        draw(event);
    });
    canvas.addEventListener("touchend", stopDrawing);
    canvas.addEventListener("touchcancel", stopDrawing);

    function startDrawing(event) {
        isDrawing = true;
        [lastX, lastY] = getCoordinates(event, canvas);
    }

    function draw(event) {
        if (!isDrawing) return;
        let [x, y] = getCoordinates(event, canvas);

        // Round to the nearest 0.5 pixel for sharper lines
        x = Math.round(x * 2) / 2;
        y = Math.round(y * 2) / 2;

        context.beginPath();
        context.moveTo(lastX, lastY);
        context.lineTo(x, y);
        context.stroke();
        [lastX, lastY] = [x, y];
    }

    function stopDrawing() {
        isDrawing = false;
    }

    function getCoordinates(event, canvas) {
        const rect = canvas.getBoundingClientRect();
        let x, y;
        if (event.touches && event.touches.length > 0) {
            x = event.touches[0].clientX - rect.left;
            y = event.touches[0].clientY - rect.top;
        } else {
            x = event.clientX - rect.left;
            y = event.clientY - rect.top;
        }

        // Adjust for device pixel ratio
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;

        return [x * scaleX, y * scaleY];
    }
}

    // Function to set up the canvas for high-DPI displays
    function setupCanvas(canvas) {
        const ctx = canvas.getContext(\'2d\');
        const rect = canvas.getBoundingClientRect();
        const dpr = window.devicePixelRatio || 1;

        // Set the canvas width and height to the CSS size multiplied by DPR
        canvas.width = rect.width * dpr;
        canvas.height = rect.height * dpr;

        // Scale the context to ensure correct drawing operations
        ctx.scale(dpr, dpr);

        // Optional: Set default styles
        ctx.strokeStyle = "#000";
        ctx.lineWidth = 3; // This will remain consistent after scaling
        ctx.lineCap = "round";
        ctx.lineJoin = "round";
    }

    function clearCanvas(canvasId) {
        const canvas = document.getElementById(canvasId);
        if (canvas) {
            const context = canvas.getContext("2d");
            context.setTransform(1, 0, 0, 1, 0, 0); // Reset transform
            context.clearRect(0, 0, canvas.width, canvas.height);
            setupCanvas(canvas); // Re-setup canvas after clearing

        } else {
            console.warn(`Canvas element with id "${canvasId}" not found`);
        }
    }

    // Query to enable all input type text and checkbox
    var inputs = document.querySelectorAll("input[type=text], input[type=checkbox], input[type=radio]");
    inputs.forEach(function(input) {
        input.disabled = false;
    });


    

    

    // Handle file inputs on initial load
    var fileInputs = document.querySelectorAll(\'input[type="file"]\');
    fileInputs.forEach(function(input) {
        input.accept = ".jpg, .png";
        input.disabled = false;
        let uploadedImagesContainer = input.parentElement.querySelector(\'.uploaded-images-container\');
        if (!uploadedImagesContainer) {
            uploadedImagesContainer = document.createElement("div");
            uploadedImagesContainer.classList.add("uploaded-images-container", "row");
            input.parentElement.appendChild(uploadedImagesContainer);
        }

        // Attach change event listener to this file input
        input.addEventListener("change", function() {
            const files = input.files;
            if (files.length > 1) {
                alert("Bitte nur eine Datei auswählen."); // "Please select only one file."
                input.value = ""; // Reset the input
                return;
            }
            if (files.length === 1) {
                uploadFiles(files, input, uploadedImagesContainer);
            }
        });
    });

    // Code to handle tables that should be extendable by the technician
    // (Assuming this code is correct and required)

    var extendableTables = document.querySelectorAll(\'table[data-extendable="true"]\');
    extendableTables.forEach(function(table) {
        var parentDiv = table.parentElement;

        // Create the "Add Row" button
        var addButton = document.createElement("button");
        addButton.textContent = "Zeile Hinzufügen";
        addButton.classList.add("btn", "btn-primary", "mt-2");
        addButton.type = "button";

        // Append the button to the parent div
        parentDiv.appendChild(addButton);

        // Bind the click event to the button
        addButton.addEventListener("click", function() {
            var tbody = table.querySelector("tbody");
            if (!tbody) {
                tbody = document.createElement("tbody");
                var rows = table.querySelectorAll("tr");
                rows.forEach(function(row, index) {
                    if (index > 0) {
                        tbody.appendChild(row);
                    }
                });
                table.appendChild(tbody);
            }

            var lastRow = tbody.querySelector("tr:last-child");
            if (lastRow) {
                var newRow = lastRow.cloneNode(true);
                updateRowAttributes(newRow, tbody.rows.length);
                tbody.appendChild(newRow);
                reattachEventListeners(newRow);
            } else {
                alert("No row to clone.");
            }
        });
    });

    // Function to update IDs and names in the new row
    function updateRowAttributes(row, rowIndex) {
        var inputs = row.querySelectorAll("input, select, textarea");
        inputs.forEach(function(input) {
            var name = input.getAttribute("name");
            if (name) {
                var newName = name.replace(/(\d+)/g, function(match) {
                    return parseInt(match) + rowIndex;
                });
                input.setAttribute("name", newName);
            }

            var id = input.getAttribute("id");
            if (id) {
                var newId = id.replace(/(\d+)/g, function(match) {
                    return parseInt(match) + rowIndex;
                });
                input.setAttribute("id", newId);
            }

            if (input.type === "checkbox" || input.type === "radio") {
                input.checked = false;
            } else {
                input.value = "";
            }
        });
    }

    // Function to reattach event listeners to inputs in the new row
    function reattachEventListeners(row) {
        var fileInputs = row.querySelectorAll(\'input[type="file"]\');
        fileInputs.forEach(function(input) {
            input.accept = ".jpg, .png";
            input.disabled = false;
            const uploadedImagesContainer = document.createElement("div");
            uploadedImagesContainer.classList.add("uploaded-images-container", "row");
            input.parentElement.appendChild(uploadedImagesContainer);

            // Attach change event listener to this file input
            input.addEventListener("change", function() {
                const files = input.files;
                if (files.length > 0) {
                    uploadFiles(files, input, uploadedImagesContainer);
                }
            });
        });
    }

    function setupSaveButtons() {
    const form = document.getElementById("report-form");
    const submitButton = form.querySelector("button#save-form-button-wrapper");
    submitButton.type = "button";
    const saveStayButton = form.querySelector("button#save-form-button");
    saveStayButton.type = "button";
    form.appendChild(saveStayButton);
    form.appendChild(submitButton);

    // Set up event listeners
    submitButton.addEventListener("click", saveFormAndExit);

    saveStayButton.addEventListener("click", function(event) {
        event.preventDefault();
        saveForm(function() {
            // After saving, redirect to the same page with action=view
            //window.location.href = window.location.pathname + \'?id=\' + ticketId + \'&action=edit\';
        });
    });
}

function saveFormAndExit(event) {
    event.preventDefault();
    saveForm(function() {
        // Redirect after saving
        window.location.href = "index.php";
    });
}

function saveForm(callback) {
    event.preventDefault(); // Prevent default form submission
    
    const form = document.getElementById("report-form");

    // Clone the form to manipulate it without affecting the DOM
    const formClone = form.cloneNode(true);

    // Remove dynamically added image elements
    const uploadedImagesContainers = formClone.querySelectorAll(\'.uploaded-images-container\');
    uploadedImagesContainers.forEach(container => container.remove());

    // Now get the HTML of the cloned form without images
    //const formHtml = formClone.innerHTML;
    const originalElements = form.querySelectorAll(\'.report-element\');

    let parameters = [];
    const uploadedImages = window.uploadedImagesData || [];

    // Capture form data
    const elements = formClone.querySelectorAll(\'.report-element\');
    originalElements.forEach((element) => {
        const id = element.id;
        let value = "";
        if (element.type === "checkbox" || element.type === "radio") {
            value = element.checked;
            parameters.push({ id: id, value: value });
        } else if (element.type === "file") {
            // No action needed for file inputs
        } else if (element.tagName === "INPUT" || element.tagName === "TEXTAREA" || element.tagName === "SELECT") {
            value = element.value;
            parameters.push({ id: id, value: value });
            console.log("Element:", element, "Value:", value);
        } else if (element.tagName === "CANVAS") {
            var dataURL = element.toDataURL();
            parameters.push({ id: id, value: dataURL });
        } else {
            // For other elements, save their innerHTML if needed
            value = element.innerHTML;
            // parameters.push({ id: id, value: value }); // Uncomment if necessary
        }
    });

     const formHtml = formClone.innerHTML;

    // Prepare form data for AJAX
    const formData = new FormData();
    formData.append("form", formHtml); // Use the HTML without images
    formData.append("parameters", JSON.stringify(parameters));
    formData.append("uploadedImages", JSON.stringify(uploadedImages));

    formData.append("storeId", storeId);
    formData.append("userId", userId);
    formData.append("ticketId", ticketId);
    formData.append("socId", socId);
    // AJAX request to save the form
    $.ajax({
        url: "'.DOL_MAIN_URL_ROOT.'/tecform.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.status === \'success\') {
                alert("Report saved successfully");
                console.log("Save Response:", response);
                if (typeof callback === \'function\') {
                    callback();
                }
            } else {
                console.error("Server Error:", response.message);
                alert("Save failed: " + response.message);
            }
            
        },
        error: function(xhr, status, error) {
            console.error("Request failed with status: " + xhr.status + ", Error: " + error);
        }
    });
}

// Function to fetch images via AJAX
    function fetchUploadedImages() {
        console.log("Fetching uploaded images...");
        const formData = new FormData();
        formData.append("action", "fetch_images");
        formData.append("mode", "image");
        formData.append("ticketId", ticketId);
        formData.append("userId", userId);
        formData.append("storeId", storeId);
        formData.append("socId", socId);

        $.ajax({
            url: "tecform.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log(response);
                if (response.status === \'success\') {
                    const imagesArray = Object.values(response.images);
                    window.uploadedImagesData = imagesArray;
                    window.uploadedImagesData.forEach(imageNode => {
                        const inputId = imageNode.type;
                        // Search for wrapper with label.innerHTML = inputId
                        const labels = document.querySelectorAll(\'label\');
                        const label = Array.from(labels).find(label => label.innerHTML === inputId);
                        const wrapper = label.parentElement;
                        const fileInput = wrapper.querySelector(\'input[type="file"]\');
                        if (fileInput) {
                            let uploadedImagesContainer = fileInput.parentElement.querySelector(\'.uploaded-images-container\');
                            if (!uploadedImagesContainer) {
                                uploadedImagesContainer = document.createElement(\'div\');
                                uploadedImagesContainer.classList.add(\'uploaded-images-container\', \'row\');
                                fileInput.parentElement.appendChild(uploadedImagesContainer);
                            }
                            imageNode.images.forEach(filename => {
                                const image = { filename: filename, inputId: inputId };
                                displayUploadedImage(image, uploadedImagesContainer);
                            });
                        }
                    });
                } else {
                    console.error(\'Failed to fetch uploaded images:\', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("Request failed with status: " + xhr.status + ", Error: " + error);
            }
        });
    }

    // Function to display a single uploaded image
    function displayUploadedImage(image, container) {
        console.log("Displaying image:", image);
        const colDiv = document.createElement("div");
        colDiv.classList.add("col-3", "col-md-3", "mt-2", "text-center");

        const img = document.createElement("img");
        // Important because this was super annoying to figure out:  We need to add the timestamp to the image URL to prevent caching
        // If we do not do this, then after overwriting an image, the browser will still show the old image from cache
        img.src = "formsImages/" + encodeURIComponent(image.filename) + "?t=" + new Date().getTime();
        console.log("Image url:", img.src);
        img.style.width = "100%";
        img.style.height = "13rem";
        img.onerror = function() {
            console.error("Failed to load image:", img.src);
        };
        img.onload = function() {
            console.log("Image loaded successfully:", img.src);
        };
        img.onclick = function() {
            showImageFull(img.src);
        };

        const deleteButton = document.createElement("button");
        deleteButton.classList.add("btn", "btn-danger", "mt-2");
        deleteButton.style.fontSize = "10px";
        deleteButton.style.padding = "5px";
        deleteButton.textContent = "Delete";
        deleteButton.type = "button";
        deleteButton.onclick = function() {
            deleteImage(image.filename, colDiv, image.inputId);
        };

        colDiv.appendChild(img);
        colDiv.appendChild(deleteButton);
        container.appendChild(colDiv);
    }

    // Function to show the image in full size
    function showImageFull(src) {
        const modalImage = document.getElementById("modalImage");
        modalImage.src = src;

        // Initialize and show the modal using Bootstraps JavaScript API
        const imageModal = new bootstrap.Modal(document.getElementById(\'imageModal\'), {
            keyboard: true
        });
        imageModal.show();
    }

    // Function to upload files via AJAX
    function uploadFiles(files, fileInput, uploadedImagesContainer) {
        const formData = new FormData();
        // Grab the div with the attribute data-element-id = fileInput.id
        const parentWrapper = fileInput.parentElement;
        // Grab label which is child of parentWrapper
        const label = parentWrapper.querySelector("label");
        const inputId = label.innerHTML || "unknown";
        const imageType = inputId;

        // Append files to formData
        for (let i = 0; i < files.length; i++) {
            formData.append("files[]", files[i]);
        }

        // Add additional data
        formData.append("imageType", imageType);
        formData.append("action", "upload_images");
        formData.append("mode", "image");
        formData.append("ticketId", ticketId);
        formData.append("userId", userId);
        formData.append("storeId", storeId);
        formData.append("socId", socId);

        $.ajax({
            url: "tecform.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log(response);
                if (response.status === \'success\') {
                    // Update uploaded images data
                    const imagesList = Object.values(response.images);
                    window.uploadedImagesData = imagesList;

                    // Clear and update the UI
                    uploadedImagesContainer.innerHTML = "";
                    imagesList.forEach(imageNode => {
                        if (imageNode.type === imageType) {
                            imageNode.images.forEach(filename => {
                                const image = { filename: filename, inputId: inputId };
                                displayUploadedImage(image, uploadedImagesContainer);
                            });
                        }
                    });
                } else {
                    alert("Upload failed: " + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("Request failed with status: " + xhr.status + ", Error: " + error);
                alert("An error occurred during the upload.");
            }
        });
    }

    // Function to delete an image via AJAX
    function deleteImage(filename, imageElement, inputId) {
        const formData = new FormData();
        formData.append("action", "delete_image");
        formData.append("mode", "image");
        formData.append("filename", filename);
        formData.append("imageType", inputId);
        formData.append("ticketId", ticketId);
        formData.append("userId", userId);
        formData.append("storeId", storeId);
        formData.append("socId", socId);

        $.ajax({
            url: "tecform.php",
            type: "POST",
            data: formData,
            processData: false, // Prevent jQuery from processing the data
            contentType: false, // Prevent jQuery from setting the content type
            success: function(response) {
                console.log("Delete Image Response:", response);
                if (response.status === \'success\') {
                    // Remove the image element from the UI
                    imageElement.remove();

                    // Update the global uploadedImagesData array
                    window.uploadedImagesData = window.uploadedImagesData.filter(node => {
                        if (node.type === inputId) {
                            node.images = node.images.filter(img => img !== filename);
                            return node.images.length > 0;
                        }
                        return true;
                    });
                } else {
                    // Display an error message if deletion failed
                    alert("Deletion failed: " + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error - Status:", status, "Error:", error);
                alert("An error occurred during deletion.");
            }
        });
    }
</script>
';

echo '<style>
canvas {
    border: 1px solid black;
    width: 600px;   /* Desired display width */
    height: 200px;  /* Desired display height */
}
</style>';

if(!$existingReportRes){
$dateofuse = $object->array_options["options_dateofuse"];
if($dateofuse == ""){
    $dateofuse = 0;
}
echo '<script>
    const form = document.getElementById("report-form");
    const submitButton = document.createElement("button");
    submitButton.innerHTML = "Speichern und verlassen";
    submitButton.id = "save-form-button-wrapper";
    submitButton.classList.add("btn", "btn-primary");
    const saveStayButton = document.createElement("button");
    saveStayButton.innerHTML = "Speichern";
    saveStayButton.classList.add("btn", "btn-primary");
    saveStayButton.id = "save-form-button";
    form.appendChild(saveStayButton);
    form.appendChild(submitButton);
    setupSaveButtons();
    
    // Switch to handle the dynamically generated content

    let dynamicDisplays = document.querySelectorAll([\'[data-content-type]\']);
    dynamicDisplays.forEach((element) => {
        var text = element.innerHTML.toLowerCase();
        var index = text.indexOf(" ") !== -1 ? text.indexOf(" ") : text.length;
        var result = text.substring(0, index);
        if(element.dataset.contentType === "dynamic"){
            console.log(result);
            switch(result){
                case "filiale":
                    element.innerHTML = "Filiale: '.$store->b_number.'";
                    break;
                case "tickettyp":
                case "ticketart":
                    element.innerHTML = "Ticketart: '.$object->type_label.'";
                    break;
                case "termin":
                    let dateofuse = '.$dateofuse.';
                    // Format dateofuse to dd.mm.yyyy hh:ii
                    let date = 0;
                    if(dateofuse !== 0){
                        date = new Date(dateofuse * 1000);
                        date = date.toLocaleString("de-DE");
                    }else{
                        date = "Kein Termin festgelegt";
                    }
                    element.innerHTML = "Termindatum: " + date;
                    break;
                case "Themengruppe":
                    element.innerHTML = "Themengruppe: '.$object->category_code.'";
                    break;
                case "ticketnummer":
                    element.innerHTML = "Ticketnummer: '.$object->ref.'";
                    break;
                case "kundennummer":
                    element.innerHTML = "Kundennummer: '.$company->id.'";
                    break;
                case "kundenname":
                    element.innerHTML = "Kundenname: '.$store->customer_name.'";
                    break;
                case "name":
                    element.innerHTML = "'.$project->title.'";
                    break;
                case "stop":
                case "stopp":
                    element.innerHTML = "Stopp: '.$object->array_options["options_stopnummer"].'";
                    break;
                case "datum":
                    
                    break;
                case "uhrzeit":
                    
                    break;
                case "priorität":
                    break;
                case "dringlichkeit":
                    element.innerHTML = "Dringlichkeit: '.$object->severity_code.'";
                    break;
                case "kategorie":
                    element.innerHTML = "Kategorie: '.$object->category_label.'";
                    break;
                case "auftrag":
                    element.innerHTML = "Auftrag: '.$object->message.'";
                    break;
                case "strasse":
                case "straße":
                    element.innerHTML = "Straße: '.$store->street.', '.$store->house_number.'";
                    break;
                case "hausnummer":
                    element.innerHTML = "Hnr: '.$store->house_number.'";
                    break;
                case "stadt":
                case "ort":
                    element.innerHTML = "Ort: '.$store->city.', '.$store->zip_code.'";
                    break;
                case "plz":
                    element.innerHTML = "Plz: '.$store->zip_code.'";
                    break;
                case "ext.ticketnummer":
                    element.innerHTML = "Ext. Ticketnummer: '.$object->array_options["options_externalticketnumber"].'";
                    break;
                case "telefonnummer":
                case "tel":
                    element.innerHTML = "Tel.Nummer: '.$store->phone.'";
                    break;
                default:
                    element.innerHTML = "nothing";
                    break;
            }
        }
});

    

    
    </script>';
}else{
echo
 '<script>
        const form = document.getElementById("report-form");
        
        // Fill the form with the existing data
        const elements = document.querySelectorAll(\'.report-element\');
        const params = '.base64_decode($existingReportRes[0][1]).';
        params.forEach(param => {
            const element = document.getElementById(param.id);
            console.log("Element:", element);
            console.log("Param:", param);
            if (element) {
                if (element.type === "checkbox" || element.type === "radio") {
                    element.checked = param.value;
                } else if (element.type === "file") {
                    // Do not attempt to set element.value
                    // Instead, handle displaying previously uploaded files
                }else if(element.tagName === "CANVAS"){
                    console.log("Canvas element found with id:", param.id);
                    const context = element.getContext("2d");
                        let canvas = document.getElementById(param.id);
                        const img = new Image();
                        img.onload = function() {
                            console.log("Image loaded successfully for canvas with id:", param.id);
                            console.log("Canvas width:", element.width, "Canvas height:", element.height);
                            console.log("Image width:", img.width, "Image height:", img.height);
                            console.log("Device Pixel Ratio:", window.devicePixelRatio || 1);
                            console.log("Canvas width / DPR:", element.width / (window.devicePixelRatio || 1));
                            // Draw the image at the correct size without additional scaling
                            context.clearRect(0, 0, canvas.width, canvas.height); 
                            context.drawImage(img, 0, 0);
                        };
                        img.onerror = function() {
                            console.error("Failed to load image for canvas with id:", param.id);
                        };
                        img.src = param.value;
                        console.log("Image source:", img.src);
                }else {
                    element.value = param.value;
                }
            }
        });
        //setupSaveButtons();
    </script>';
}
