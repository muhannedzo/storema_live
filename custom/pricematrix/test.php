<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Popup Tables</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .hidden-table {
            display: none;
        }
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        .popup table {
            width: 100%;
        }
        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        .popup-close {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
        }
			p.filter.active {
				background-color: lightblue; /* Change the color as needed */
			}
			table {
				font-family: Arial, sans-serif;
				border-collapse: collapse;
				width: 100%;
				margin-top: 10px;
			}

			td, th {
				border: 1px solid #dddddd;
				text-align: left;
			}

			th {
				background-color: #f2f2f2;
			}
			.row {
				--bs-gutter-x: 1.5rem;
				--bs-gutter-y: 0;
				display: flex;
				flex-wrap: wrap;
				margin-top: calc(-1* var(--bs-gutter-y));
				margin-right: calc(-.5* var(--bs-gutter-x));
				margin-left: calc(-.5* var(--bs-gutter-x));
			}
			.col-6 {
				flex: 0 0 auto;
				width: 50%;
			}
			.col-8 {
				flex: 0 0 auto;
				width: 80%;
			}
			.col-2 {
				display: flex;
				flex: 0 0 auto;
				width: 20%;
			}
			td, th {
				height: 5px;
				border: 1px solid #dddddd;
				text-align: left;
				padding: 3px;
				font-size: 0.7rem;
				width: 10%
			}
			.popup {
				display: none;
				position: fixed;
				top: 50%;
				left: 50%;
				transform: translate(-50%, -50%);
				background-color: #fff;
				padding: 20px;
				border: 1px solid #ccc;
				border-radius: 5px;
				z-index: 1000;
			}
			.col-1 {
				flex: 0 0 auto;
				width: 10%;
				cursor: pointer
			}
			.filter {
				border: 1px solid #ccc;
				padding: 10px;
				text-align: center;
				margin: 4px;
			}
			.tabBar {
				padding-bottom: 0 !important;
			}
			#first-table {
				margin-top: 0 !important;
			}
    </style>
</head>
<body>
    <div class="page-body">
        <div class="row" style="display:flex; justify-content: center;" id="first">
            <table id="first-table" class="col-8">
                <!-- Table content here -->
                <tr>
							<th colspan="8" class="center">Basisdaten Grundlage zur Berechnungen</th>
						</tr>
						<tr>
							<th>Einsatzzeiten</th>
							<th></th>
							<th colspan="2" class="center">Timezone 1 <input type="checkbox"></th>
							<th colspan="2" class="center">Timezone 2 <input type="checkbox"></th>
							<th colspan="2" class="center">Timezone 3 <input type="checkbox"></th>
						</tr>
						<tr>
							<td>von bis Zeiten</td>
							<td></td>
							<td class="center" id="first_shift_first" contenteditable="true" oninput="handleChange(this.id, this.className)">08:00</td>
							<td class="center" id="first_shift_last" contenteditable="true" oninput="handleChange(this.id, this.className)">17:00</td>
							<td class="center" id="second_shift_first" contenteditable="true">17:00</td>
							<td class="center" id="second_shift_last" contenteditable="true" oninput="handleChange(this.id, this.className)">22:00</td>
							<td class="center" contenteditable="true" id="third_shift_first">22:00</td>
							<td class="center" contenteditable="true" id="third_shift_last">08:00</td>
						</tr>
						<tr>
							<td>Mo - Fr</td>
							<td class="center">+</td>
							<td colspan="2"></td>
							<td colspan="2" class="center">50%</td>
							<td colspan="2" class="center">75%</td>
						</tr>
						<tr>
							<td>Sa</td>
							<td class="center">+</td>
							<td colspan="2" class="center">50%</td>
							<td colspan="2" class="center">75%</td>
							<td colspan="2" class="center">100%</td>
						</tr>
						<tr>
							<td>Son / Feiertag</td>
							<td class="center">+</td>
							<td colspan="2" class="center">100%</td>
							<td colspan="2" class="center">150%</td>
							<td colspan="2" class="center">200%</td>
						</tr>
            </table>
            <div class="col-2">
                <i class="fa fa-arrow-right" style="align-self:center;" onclick="showPopup(\'popup1\')"></i>
                <div id="popup1" class="popup">
                    <span class="popup-close" onclick="closePopup('popup1')">&times;</span>
                    <table>
                        <!-- Hidden table content here -->
						<tr>
							<th colspan="1" class="center">SESOCO</th>
							<th colspan="1" class="center">Anfahrt</th>
							<th colspan="1" class="center"></th>
							<th colspan="1" class="center"></th>
							<th colspan="1" class="center"></th>
							<th colspan="1" class="center"></th>
							<th colspan="1" class="center"></th>
							<th colspan="1" class="center"></th>
							<th colspan="1" class="center"></th>
							<th colspan="1" class="center"></th>
						</tr>
						<tr>
							<td colspan="1" class="center">service partner</td>
							<td colspan="1" class="center" id="pauschal_service" contenteditable="true" oninput="handleChange(this.id, this.className)">50%</td>
							<td colspan="1" class="center"></td>
							<td colspan="1" class="center"></td>
							<td colspan="1" id="pauschal_service_1" class="center">0.00</td>
							<td colspan="1" id="pauschal_service_2" class="center">0.00</td>
							<td colspan="1" id="pauschal_service_3" class="center">0.00</td>
							<td colspan="1" id="pauschal_service_4" class="center">0.00</td>
							<td colspan="1" id="pauschal_service_5" class="center">0.00</td>
							<td colspan="1" id="pauschal_service_6" class="center">0.00</td>
						</tr>
                    </table>
                </div>
            </div>
            <table id="second-table" class="col-8" style="margin-top:0">
                <!-- Table content here -->
            </table>
            <div class="col-2">
                <i class="fa fa-arrow-right" style="align-self:center;" onclick="showPopup('popup2')"></i>
                <div id="popup2" class="popup">
                    <span class="popup-close" onclick="closePopup('popup2')">&times;</span>
                    <table>
                        <!-- Hidden table content here -->
                    </table>
                </div>
            </div>
            <table id="third-table" class="col-8">
                <!-- Table content here -->
            </table>
            <div class="col-2">
                <i class="fa fa-arrow-right" style="align-self:center;" onclick="showPopup('popup3')"></i>
                <div id="popup3" class="popup">
                    <span class="popup-close" onclick="closePopup('popup3')">&times;</span>
                    <table>
                        <!-- Hidden table content here -->
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="popup-overlay" id="popup-overlay" onclick="closeAllPopups()"></div>
    <script>
        function showPopup(popupId) {
            document.getElementById(popupId).style.display = "block";
            document.getElementById("popup-overlay").style.display = "block";
        }

        function closePopup(popupId) {
            document.getElementById(popupId).style.display = "none";
            document.getElementById("popup-overlay").style.display = "none";
        }

        function closeAllPopups() {
            document.querySelectorAll(".popup").forEach(popup => popup.style.display = "none");
            document.getElementById("popup-overlay").style.display = "none";
        }
    </script>
</body>
</html>
