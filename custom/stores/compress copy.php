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

    public function print_list($imagesList){
        $imagesList = array_reverse($imagesList);
        $tickett = new Ticket($db);
        foreach($imagesList as $k => $elem){
            print '<div class="group">';
              print '<div class="group-header">';
                print '<div style="display: flex">';
                    print '<form action="" method="POST"><input type="hidden" name="token" value="'.newToken().'">';
                        print '<input id="main-label '.$k.'" name="label" type="text" placeholder="Label..." value="'.$elem["title"].'" disabled>';
                        print '<div class="edit-icon" id="edit-icon '.$k.'"><span id="'.$k.'" class="fa fa-pen" onclick="changeLabel(this.id)"></span></div>';
                        print '<button type="submit" name="edit-label" id="save-edit '.$k.'" hidden>Save</button></td>';
                        print '<input type="hidden" name="objectIndex" value="'.$k.'">';
                    print '</form>';
                print '</div>';  
                // print '<div style="display: flex; align-items:center">';
                //     if(isset($elem["ticket"]) && $elem["ticket"] != null){
                      
                //       print '<a href="../../ticket/card.php?id='.explode("|", $elem["ticket"])[0].'">'.explode("|", $elem["ticket"])[1].'</a>';
                //     }
                // print '</div>';  
                print '<div style="display: flex; align-items:center">';
                  print '<form action="" method="POST"><input type="hidden" name="token" value="'.newToken().'">';
                    print '<span id="delete '.$k.'" class="fa fa-trash" style="color:red;margin:5px" onclick="conf(this.id)"></span>';
                    print '<button type="submit" id="delete-group delete '.$k.'" name="delete-group" hidden>delete</button></td>';
                    print '<span id="addmore '.$k.'" class="fa fa-plus-circle add-icon" onclick="see(this.id)"></span>';
                    print '<input type="hidden" name="objectIndex" value="'.$k.'">';
                  print '</form>';  
                print '</div>';
              print '</div>';
            $elements = array_reverse($elem["images"]);
    
            // var_dump($titles);
            $exploded_elements = array_map(function($element) {
                $parts = explode("|", $element);
                return $parts[0];
            }, $elements);
            $exploded_texts = array_map(function($element) {
                $parts = explode("|", $element);
                return $parts[1];
            }, $elements);
            
            $text = implode(", ", $exploded_elements);
            $titles = implode(", ", $exploded_texts);
                    
            // $text = implode(", ", $elements);
            print '<input type="text" class="array '.$k.'" value="'.$text.'" hidden>';
            foreach($elements as $key => $image){
                $desc = "";
                if(count(explode("|", $image)) > 1){
                  $desc = explode("|", $image)[1];
                }
                print '<div class="group-element">';
                print '<input type="file" name="files[]" multiple hidden>';
                  print '<div class="element-image">';
                    print '<img class="myImg" id="'.$k.' '.$key.'" alt="img" src="./img/'.explode("|", $image)[0].'" width="100" height="100" onclick="ss(this.id, 1);">';
                  print '</div>';
                  print '<form action="" method="POST"><input type="hidden" name="token" value="'.newToken().'">';
                  print '<div class="element-description">';
                    print '<input id="desc '.$k.' '.$key.'" name="description"type="text" placeholder="Description.." value="'.$desc.'" disabled>';
                  print '</div>';
                  print '<div class="element-buttons">';
                    print '<button type="submit" name="delete" onclick="return confirmDelete();">Delete</button>';
                    print '<button type="button" class="'.$k.' '.$key.'" id="edit-button '.$k.' '.$key.'" onclick="toggleEdit(this.className)">Edit</button>';
                    print '<button type="submit" name="edit" id="save-button '.$k.' '.$key.'" hidden>Save</button></td>';
                  print '</div>';
                  print '<div id="modal '.$k.' '.$key.'" class="modal '.$k.' '.$key.'">
                              <!-- Modal content -->
                                  <div class="modal-content">
                                      <div class="modal-header">
                                          <p class="'.$k.' '.$key.'" id="rotate '.$k.' '.$key.'" onclick="rotateImage(this.id,this.className, 1)">Rotate</p>
                                          <span class="close '.$k.' '.$key.'" id="close '.$k.' '.$key.'">&times;</span>
                                      </div>
                                      <div class="modal-body">  
                                          <div class="modal-image" style="display: flex; align-items: center; justify-content: space-evenly;">
                                              <a class="'.$k.' '.$key.'" id="'.$text.'|'.$titles.'" onclick="prevImage(this.id, this.className, 1)"><i class="fa fa-arrow-left" style="font-size:20px"></i></a>
                                              <img class="'.$k.' '.$key.'" id="img rotate '.$k.' '.$key.'" alt="img" src="./img/'.explode("|", $image)[0].'" onclick="se(this.id,this.className, 1);"
                                                                      style="cursor: pointer">
                                              <a class="'.$k.' '.$key.'" id="'.$text.'|'.$titles.'" onclick="nextImage(this.id, this.className, 1)"><i class="fa fa-arrow-right" style="font-size:20px"></i></a>
                                          </div>';
                                          // if($desc != ""){
                                              print '<div><p id="txt rotate '.$k.' '.$key.'">'.$desc.'</p></div>';
                                          // }
                                print '</div>
                                      <div class="modal-footer">
                                      </div>
                                  </div>
                          </div>';
                  print '<div id="full-model '.$k.' '.$key.'" class="full-view '.$key.'">
                              <span class="full-view-close '.$k.' '.$key.'" id="full-view-close '.$k.' '.$key.'">&times;</span>
                                  <img class="full-view-content" id="full-view-img rotate '.$k.' '.$key.'" src="./img/'.explode("|", $image)[0].'">
                          </div>';    
                  print '<input type="hidden" name="objectIndex" value="'.$k.'">';
                  print '<input type="hidden" name="imgIndex" value="'.$key.'">';
                  print '<input type="hidden" name="img" value="'.explode("|", $image)[0].'">';
                  print '</form>';
                print '</div>';
            }
              print '<div class="addmore '.$k.'" style="display:none">';
                print '<form action="" method="POST"  enctype="multipart/form-data"><input type="hidden" name="token" value="'.newToken().'">';
                  print '<input type="file" name="files[]" multiple>';
                  print '<input type="submit" name="submitAdd" value="add more...">';
                  print '<input type="text" name="index" value="'.$k.'" hidden>';
                print '</form>';  
              print '</div>';
            print '</div>';
            print '<script>
                      
                      var currentIndex = 0;
                      function prevImage(id, className) {
                          var lists = id.split("|")[0].split(", ");
                          var listTexts = id.split("|")[1].split(", ");
                          var className1 = "img rotate " + className;
                          var imgElement = document.getElementById(className1);
                          var src = imgElement.getAttribute("src");
                          var imageName = src.split("/").pop();
                          var imageIndex = lists.indexOf(imageName);
                          if (imageIndex === 0) {
                            currentIndex = lists.length - 1;
                          } else {
                              currentIndex = imageIndex - 1;
                          }
                          updateImage(currentIndex, className, lists, listTexts);
                      }
                  
                      function nextImage(id, className, number) {
                          var lists = id.split("|")[0].split(", ");
                          var listTexts = id.split("|")[1].split(", ");
                          if(number == 1){
                            var className1 = "img rotate " + className;
                          } else {
                            var className1 = "form-img rotate " + className;
                          }
                          var imgElement = document.getElementById(className1);
                          var src = imgElement.getAttribute("src");
                          var imageName = src.split("/").pop();
                          var imageIndex = lists.indexOf(imageName);
                          if (imageIndex === lists.length - 1) {
                            currentIndex = 0;
                          } else {
                              currentIndex = imageIndex + 1;
                          }
                          updateImage(currentIndex, className, lists, listTexts);
                      }
                      function updateImage(index, className, lists, listTexts) {
                          var classImg = "img rotate " + className;
                          var classText = "txt rotate " + className;
                          var imgElement = document.getElementById(classImg);
                          var txtElement = document.getElementById(classText);
                          imgElement.src = "./img/" + lists[currentIndex];
                          txtElement.innerHTML = listTexts[currentIndex];
                      }
                        function conf(i){
                          var m = "delete-group "+ i;
                          document.getElementById(m).click();
                        }
                        function changeLabel(i){
                            var desc = "main-label " +i;
                            var btnS = "save-edit " +i;
                            var tt = "edit-icon "+ i;
                            var input = document.getElementById(desc);
                            var editB = document.getElementById(tt);
                            var buttonS = document.getElementById(btnS);
                            if (input.disabled) {
                                input.disabled = false;
                                editB.style.display = "none";
                                buttonS.hidden = false;
                            } else {
                                input.disabled = true;
                                editB.style.display = "block";
                                buttonS.hidden = true;
                            }
                        }
                        function see(i){
                          var e = "addmore "+i;
                          document.getElementsByClassName(e)[0].style.display="block";
                        }
                        var rotation = 0;
                        var angle = 90;
                        function rotateImage(i,j, number) {
                            if(number == 1){
                              var c = "img "+i;
                              var n = "full-view-img "+i;
                            } else {
                              var c = "form-img "+i;
                              var n = "form-full-view-img "+i;
                            }
                            var rotated = document.getElementById(c);
                            var rotated1 = document.getElementById(n);
                            var rotated2 = document.getElementById(j);
                            // alert(rotated2);
                            rotation = (rotation + angle) % 360;
                            rotated.style.transform = `rotate(${rotation}deg)`;
                            rotated.style.transform = `scale(${rotation}deg)`;
                            rotated1.style.transform = `rotate(${rotation}deg)`;
                            rotated1.style.transform = `scale(${rotation}deg)`;
                            rotated2.style.transform = `rotate(${rotation}deg)`;
                            rotated2.style.transform = `scale(${rotation}deg)`;
                        }
                        function se(i,j, number){
                            if(number == 1){
                              var m = "full-model "+j;
                              var n = "full-view-img rotate "+j;
                              var spann = "full-view-close " +j;
                            } else {
                              var m = "form-full-model "+j;
                              var n = "form-full-view-img rotate "+j;
                              var spann = "form-full-view-close " +j;
                            }
                            var img = document.getElementById(i);
                            var modal = document.getElementById(m);
                            // img.onclick = function(){
                                modal.style.display = "block";
                            // }
                            
                            var span = document.getElementById(spann);

                            span.onclick = function() { 
                                modal.style.display = "none";
                            }
                            
                            window.onclick = function(event) {
                            if (event.target == modal) {
                                modal.style.display = "none";
                                }
                            }
                            
                        }  
                    </script>';
            print   '<script>
                        function toggleEdit(i) {
                            var desc = "desc " +i;
                            var btn = "edit-button " +i;
                            var btnS = "save-button " +i;
                            // alert(btnS);
                            var input = document.getElementById(desc);
                            var button = document.getElementById(btn);
                            var buttonS = document.getElementById(btnS);
                            var img = document.getElementById(i);
                            if (input.disabled) {
                                input.disabled = false;
                                // button.innerHTML = "Save";
                                button.hidden = true;
                                buttonS.hidden = false;
                            } else {
                                input.disabled = true;
                                // button.innerHTML = "Edit";
                                button.hidden = false;
                                buttonS.hidden = true;
                            }
                        }
                    </script>';
            print '<script>
                        function ss(i, number){
                            // alert(i);
                            if(number == 1){
                              var c = "close " +i;
                              var m = "modal "+i;
                            } else {
                              var c = "form-close " +i;
                              var m = "form-modal "+i;
                            }
                            var modal = document.getElementById(m);
                            modal.style.display = "block";
                            var span = document.getElementById(c); 
                            span.onclick = function() {
                                modal.style.display = "none";
                            }
                            window.onclick = function(event) {
                            if (event.target == modal) {
                                modal.style.display = "none";
                                }
                            }
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