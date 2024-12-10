<?php


// require_once DOL_DOCUMENT_ROOT.'/ticket/class/ticket.class.php';
class Compress {

  
      

    public function compress_image($tempPath, $originalPath, $imageQuality){
			
        // Get image info 
        $imgInfo = getimagesize($tempPath); 
        $mime = $imgInfo['mime']; 
        
        // Create a new image from file 
        switch($mime){ 
            case 'image/jpeg': 
                $image = imagecreatefromjpeg($tempPath); 
                break; 
            case 'image/png': 
                $image = imagecreatefrompng($tempPath); 
                break; 
            case 'image/gif': 
                $image = imagecreatefromgif($tempPath); 
                break; 
            default: 
                $image = imagecreatefromjpeg($tempPath); 
        } 
        
        // Save image 
        imagejpeg($image, $originalPath, $imageQuality);    
        // Return compressed image 
        return $originalPath; 
    }
    // Fix this later, this is bad code:
    public function print_list($imagesList){
        // Process each image group to make 'images' a sequential array
        foreach($imagesList as &$elem){
            // Ensure 'images' is a sequential array
            $elem['images'] = array_values($elem['images']);
        }
        unset($elem);
    
        // Output the main modal and full-view modal once
        echo '
        <!-- Image Modal -->
        <div id="imageModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <p id="rotateButton" onclick="rotateImage()">Rotate</p>
                    <span class="close" id="closeModal">&times;</span>
                </div>
                <div class="modal-body">  
                    <div class="modal-image" style="display: flex; align-items: center; justify-content: space-evenly;">
                        <a id="prevButton" onclick="prevImage()"><i class="fa fa-arrow-left" style="font-size:20px"></i></a>
                        <img id="modalImage" alt="img" src="" onclick="openFullView();" style="cursor: pointer">
                        <a id="nextButton" onclick="nextImage()"><i class="fa fa-arrow-right" style="font-size:20px"></i></a>
                    </div>
                    <div><p id="modalDescription"></p></div>
                </div>
            </div>
        </div>
    
        <!-- Full View Modal -->
        <div id="fullViewModal" class="full-view">
            <span class="full-view-close" id="closeFullView">&times;</span>
            <img id="fullViewImage" class="full-view-content" src="">
        </div>
        ';
    
        // Loop over each image group
        foreach($imagesList as $groupIndex => $elem){
            echo '<div class="group">';
                echo '<div class="group-header">';
                    // Group title displayed directly
                    echo '<div style="display: flex; align-items: center;">';
                        echo '<div>';
                            echo $elem["title"]; // Display the group title directly
                        echo '</div>';
                    echo '</div>';
                    // Delete group and add more images options
                    echo '<div style="display: flex; align-items:center">';
                        echo '<form action="" method="POST"><input type="hidden" name="token" value="'.newToken().'">';
                            echo '<span id="delete-'.$groupIndex.'" class="fa fa-trash" style="color:red;margin:5px; cursor: pointer;" onclick="conf('.$groupIndex.')"></span>';
                            echo '<button type="submit" id="delete-group-delete-'.$groupIndex.'" name="delete-group" hidden>delete</button>';
                            echo '<span id="addmore-'.$groupIndex.'" class="fa fa-plus-circle add-icon" onclick="see('.$groupIndex.')"></span>';
                            echo '<input type="hidden" name="objectIndex" value="'.$groupIndex.'">';
                        echo '</form>';  
                    echo '</div>';
                echo '</div>';
    
                // Output each image in the group
                foreach($elem["images"] as $imageIndex => $image){
                    $parts = explode("|", $image, 2);
                    $imageName = $parts[0];
                    $desc = isset($parts[1]) ? $parts[1] : '';
                    $src = $elem['directoryUrl'] . $imageName;
                    echo '<div class="group-element">';
                        // Image thumbnail
                        echo '<div class="element-image">';
                            echo '<img class="myImg" alt="img" src="'.$src.'" width="100" height="100" onclick="openModal('.$groupIndex.', '.$imageIndex.');">';
                        echo '</div>';
                        // Form for editing description and deleting image
                        echo '<form action="" method="POST"><input type="hidden" name="token" value="'.newToken().'">';
                            echo '<div class="element-description">';
                                echo '<input id="desc-'.$groupIndex.'-'.$imageIndex.'" name="description" type="text" placeholder="Description.." value="'.htmlspecialchars($desc, ENT_QUOTES).'" disabled>';
                            echo '</div>';
                            echo '<div class="element-buttons">';
                                echo '<button type="submit" name="delete" onclick="return confirmDelete();">Delete</button>';
                                echo '<button type="button" id="edit-button-'.$groupIndex.'-'.$imageIndex.'" onclick="toggleEdit('.$groupIndex.', '.$imageIndex.')">Edit</button>';
                                echo '<button type="submit" name="edit" id="save-button-'.$groupIndex.'-'.$imageIndex.'" hidden>Save</button>';
                            echo '</div>';
                            // Hidden inputs for identification
                            echo '<input type="hidden" name="objectIndex" value="'.$groupIndex.'">';
                            echo '<input type="hidden" name="imgIndex" value="'.$imageIndex.'">';
                            echo '<input type="hidden" name="img" value="'.htmlspecialchars($imageName, ENT_QUOTES).'">';
                            echo '<input type="hidden" name="source" value="'.$elem['source'].'">';
                            if(isset($elem['id'])) {
                                echo '<input type="hidden" name="id" value="'.$elem['id'].'">';
                            }
                            if(isset($elem['formId'])) {
                                echo '<input type="hidden" name="formId" value="'.$elem['formId'].'">';
                            }
                        echo '</form>';
                    echo '</div>';
                }
    
                // Form to add more images to the group
                echo '<div class="addmore-'.$groupIndex.'" style="display:none">';
                    echo '<form action="" method="POST" enctype="multipart/form-data"><input type="hidden" name="token" value="'.newToken().'">';
                        echo '<input type="file" name="files[]" multiple>';
                        echo '<input type="submit" name="submitAdd" value="add more...">';
                        echo '<input type="text" name="index" value="'.$groupIndex.'" hidden>';
                    echo '</form>';  
                echo '</div>';
            echo '</div>';
        
        // JavaScript code (adjusted as needed)
        $imagesListJson = json_encode($imagesList);
        echo '<script>
        var imagesList = '.$imagesListJson.';
        var currentIndex = 0;
        var currentGroupKey = 0;
        var rotation = 0;
        var imageKeys = {};
    
        function openModal(groupKey, imageKey) {
            currentGroupKey = groupKey.toString(); // Ensure groupKey is a string
            rotation = 0; // Reset rotation
            var group = imagesList[currentGroupKey];
            var images = group["images"];
    
            // Get and store the reversed keys for this group if not already done
            if (!imageKeys[currentGroupKey]) {
                imageKeys[currentGroupKey] = Object.keys(images);
            }
    
            // Find the index of the current image key in the keys array
            currentIndex = imageKeys[currentGroupKey].indexOf(imageKey.toString());
    
            // Handle case where imageKey is not found
            if (currentIndex === -1) {
                currentIndex = 0; // Default to first image
            }
    
            updateModalContent();
            document.getElementById("imageModal").style.display = "block";
        }
    
        function updateModalContent() {
            var group = imagesList[currentGroupKey];
            var images = group["images"];
            var keys = imageKeys[currentGroupKey];
            var currentKey = keys[currentIndex];
    
            var imageData = images[currentKey].split("|");
            var imageSrc = group["directoryUrl"] + imageData[0];
            var imageDesc = imageData.length > 1 ? imageData.slice(1).join("|") : "";
            console.log(imageData);
            console.log(group["directoryUrl"]);
            var modalImage = document.getElementById("modalImage");
            var modalDescription = document.getElementById("modalDescription");
            modalImage.src = imageSrc;
            modalDescription.innerText = imageDesc;
            modalImage.style.transform = "rotate(" + rotation + "deg)";
        }
    
        function prevImage() {
            var images = imagesList[currentGroupKey]["images"];
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            updateModalContent();
        }
    
        function nextImage() {
            var images = imagesList[currentGroupKey]["images"];
            currentIndex = (currentIndex + 1) % images.length;
            updateModalContent();
        }
    
        function rotateImage() {
            rotation = (rotation + 90) % 360;
            var modalImage = document.getElementById("modalImage");
            modalImage.style.transform = "rotate(" + rotation + "deg)";
        }
    
        function openFullView() {
            var modalImageSrc = document.getElementById("modalImage").src;
            var fullViewImage = document.getElementById("fullViewImage");
            fullViewImage.src = modalImageSrc;
            fullViewImage.style.transform = "rotate(" + rotation + "deg)";
            document.getElementById("fullViewModal").style.display = "block";
        }
    
        document.getElementById("closeModal").onclick = function() {
            document.getElementById("imageModal").style.display = "none";
            resetRotation();
        };
    
        document.getElementById("closeFullView").onclick = function() {
            document.getElementById("fullViewModal").style.display = "none";
        };
    
        function resetRotation() {
            rotation = 0;
            var modalImage = document.getElementById("modalImage");
            modalImage.style.transform = "rotate(0deg)";
        }
    
        function conf(groupIndex){
            var deleteButton = document.getElementById("delete-group-delete-" + groupIndex);
            if(confirm("Are you sure you want to delete this group?")){
                deleteButton.click();
            }
        }
    
        function see(groupIndex){
            var addMoreDiv = document.querySelector(".addmore-" + groupIndex);
            addMoreDiv.style.display = "block";
        }
    
        function toggleEdit(groupIndex, imageIndex) {
            var descId = "desc-" + groupIndex + "-" + imageIndex;
            var editButtonId = "edit-button-" + groupIndex + "-" + imageIndex;
            var saveButtonId = "save-button-" + groupIndex + "-" + imageIndex;
    
            var input = document.getElementById(descId);
            var editButton = document.getElementById(editButtonId);
            var saveButton = document.getElementById(saveButtonId);
    
            if (input.disabled) {
                input.disabled = false;
                editButton.style.display = "none";
                saveButton.hidden = false;
            } else {
                input.disabled = true;
                editButton.style.display = "inline-block";
                saveButton.hidden = true;
            }
        }
    
        function confirmDelete() {
            return confirm("Are you sure you want to delete this image?");
        }
        </script>';
    
            print '<style>
            /* The Modal (background) */
            .modal-image {
                overflow: auto;
                // float: left;
            }
            .edit-icon{
              display: flex;
              align-items: center;
            }
            .group-header input:disabled, textarea:disabled, select[disabled="disabled"] {
              background: none;
            }
            .group-header{
              background-color: #4444;
              display: flex;
              justify-content: space-between;
              padding: 0px 10px 0px 10px;
            }
            .add-icon{
              display: flex;
              align-items: center;
            }
            .group-element{
              display: inline-flex;
              flex-direction: column;
              padding: 7px;
              column-gap: 1px;
              text-align: center;
              border: 1px solid #4444;
              margin: 6px;
            }
            .modal {
              display: none; /* Hidden by default */
              position: fixed; /* Stay in place */
              z-index: 999999999999999; /* Sit on top */
              padding-top: 5vh; /* Location of the box */
              left: 0;
              top: 0;
              width: 100%; /* Full width */
              height: 100%; /* Full height */
              overflow: auto; /* Enable scroll if needed */
              background-color: rgb(0,0,0); /* Fallback color */
              background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            }
            .myImg {
                cursor: pointer
            }
            .delBtn {
                cursor: pointer;
                border: 1px solid;
                color: black;
                background: #e9e9e9;
            }
            /* Modal Content */
            .modal-content {
              position: relative;
              background-color: #fefefe;
              margin: auto;
              padding: 0;
              border: 1px solid #888;
              width: 80%;
              box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19);
              -webkit-animation-name: animatetop;
              -webkit-animation-duration: 0.4s;
              animation-name: animatetop;
              animation-duration: 0.4s
            }
            
            /* Add Animation */
            @-webkit-keyframes animatetop {
              from {top:-300px; opacity:0} 
              to {top:0; opacity:1}
            }
            
            @keyframes animatetop {
              from {top:-300px; opacity:0}
              to {top:0; opacity:1}
            }
            
            /* The Close Button */
            .close, .form-close {
              color: #333333;
              float: right;
              font-size: 28px;
              font-weight: bold;
            }
            
            .close:hover,
            .close:focus, .form-close:hover,
            .form-close:focus {
              color: #000;
              text-decoration: none;
              cursor: pointer;
            }
            
            .modal-header {
              height: 3em;  
              padding: 2px 16px;
              background-color: #e9e9e9;
              color: white;
            }
            
            .modal-header p {
                float: left;
                color: black;
                cursor: pointer;
            }
            .modal-body {
                padding: 2px 16px;
                text-align: center;
            }
            .modal-body img{
                width: 50%;
                height: 35rem
            }
            
            .modal-footer {
              padding: 2px 16px;
              background-color: #e9e9e9;
              color: white;
            }
            </style>';
            print '<style>
            .full-view {
                display: none; /* Hidden by default */
                position: fixed; /* Stay in place */
                z-index: 999999999999999999; /* Sit on top */
                left: 0;
                top: 0;
                width: 100%; /* Full width */
                height: 100%; /* Full height */
                overflow: auto; /* Enable scroll if needed */
                background-color: rgb(0,0,0); /* Fallback color */
                background-color: rgba(0,0,0,0.9); /* Black w/ opacity */
              }
              
              /* Modal Content (image) */
              .full-view-content {
                margin: auto;
                display: block;
                width: 80%;
                max-width: 700px;
              }
            
              /* Add Animation */
              .full-view-content {  
                -webkit-animation-name: zoom;
                -webkit-animation-duration: 0.6s;
                animation-name: zoom;
                animation-duration: 0.6s;
              }
              
              @-webkit-keyframes zoom {
                from {-webkit-transform:scale(0)} 
                to {-webkit-transform:scale(1)}
              }
              
              @keyframes zoom {
                from {transform:scale(0)} 
                to {transform:scale(1)}
              }
              
              /* The Close Button */
              .full-view-close, .form-full-view-close {
                position: absolute;
                top: 15px;
                right: 35px;
                color: #f1f1f1;
                font-size: 40px;
                font-weight: bold;
                transition: 0.3s;
              }
              
              .full-view-close:hover,
              .full-view-close:focus,
              .form-full-view-close:hover,
              .form-full-view-close:focus {
                color: #bbb;
                text-decoration: none;
                cursor: pointer;
              }
              
              /* 100% Image Width on Smaller Screens */
              @media only screen and (max-width: 700px){
                .full-view-content {
                  width: 100%;
                }
              }
            </style>';

            print '<script>
            </script>';            
        }
    }

}