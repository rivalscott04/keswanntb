<?php

// Test script untuk mengecek struktur data API Kemenhub
echo "=== DETAILED API TEST ===\n";

$url = 'https://portaldata.kemenhub.go.id/api/microstrategy/data_stathub_uk?id_tabel=A.1.5.09&tahun=2024&format=json';

$context = stream_context_create([
    'http' => [
        'timeout' => 30,
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ]
]);

$response = file_get_contents($url, false, $context);

if ($response !== false) {
    $data = json_decode($response, true);
    
    echo "Response structure:\n";
    echo "Type: " . gettype($data) . "\n";
    
    if (is_array($data)) {
        echo "Array keys: " . implode(', ', array_keys($data)) . "\n";
        echo "Array length: " . count($data) . "\n\n";
        
        // Show first few elements
        echo "First 3 elements:\n";
        for ($i = 0; $i < min(3, count($data)); $i++) {
            echo "Element $i: " . json_encode($data[$i]) . "\n";
        }
        
        // Check if it's a numeric array with objects
        if (isset($data[0]) && is_array($data[0])) {
            echo "\nFirst element structure:\n";
            print_r($data[0]);
        }
    }
    
    // Save response to file for inspection
    file_put_contents('api_response.json', $response);
    echo "\nFull response saved to api_response.json\n";
    
} else {
    echo "Failed to get response\n";
} 