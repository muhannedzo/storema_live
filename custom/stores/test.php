<!DOCTYPE html>
<html>
<head>
    <title>OpenStreetMap Example</title>
    <style>
        #map {
            height: 400px;
            width: 100%;
        }
    </style>
</head>
<body>
    <h2>Enter Address Information</h2>
    <form id="addressForm">
        House Number: <input type="text" id="houseNumber" required><br>
        Street: <input type="text" id="street" required><br>
        Zip Code: <input type="text" id="zipCode" required><br>
        <button type="submit">Show Map</button>
    </form>
    
    <div id="map"></div>
    
    <script>
        document.getElementById('addressForm').addEventListener('submit', function(event) {
            event.preventDefault();
            
            var houseNumber = document.getElementById('houseNumber').value;
            var street = document.getElementById('street').value;
            var zipCode = document.getElementById('zipCode').value;
            
            var address = houseNumber + ' ' + street + ', ' + zipCode;
            console.log(address);
            // Send address to PHP script for geocoding
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.open("GET", "geo.php?address=" + encodeURIComponent(address), true);
            xmlhttp.send();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    // var coordinates = JSON.parse(this.responseText);
                    console.log(this.responseText);
                    // showMap(coordinates.lat, coordinates.lon);
                }
            };
        });
        
        function showMap(lat, lon) {
            var map = L.map('map').setView([lat, lon], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            L.marker([lat, lon]).addTo(map);
        }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.js"></script>
</body>
</html>
