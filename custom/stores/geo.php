<?php

if(isset($_GET['address'])) {
    $address = urlencode($_GET['address']);
    $url = "https://nominatim.openstreetmap.org/search?format=json&q={$address}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    
    if (!empty($result)) {
        $firstResult = $result[0]; // Get the first result
    
        if (isset($firstResult['lat']) && isset($firstResult['lon'])) {
            $lat = $firstResult['lat'];
            $lon = $firstResult['lon'];
            echo json_encode(['lat' => $lat, 'lon' => $lon]);
        } else {
            echo json_encode(['error' => 'Latitude or longitude not found in the first result']);
        }
    } else {
        echo json_encode(['error' => 'No results found']);
    }
    
    
    curl_close($ch);
    
}
