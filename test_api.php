<?php

// Test script untuk mengecek API Kemenhub
echo "=== TEST API KEMENHUB ===\n";
echo "URL: https://portaldata.kemenhub.go.id/api/microstrategy/data_stathub_uk\n";
echo "Parameter: id_tabel=A.1.5.09, tahun=2024, format=json\n\n";

// Test 1: Basic HTTP request
echo "1. Testing basic HTTP connection...\n";
$url = 'https://portaldata.kemenhub.go.id/api/microstrategy/data_stathub_uk?id_tabel=A.1.5.09&tahun=2024&format=json';

$context = stream_context_create([
    'http' => [
        'timeout' => 30,
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ]
]);

$start_time = microtime(true);
$response = file_get_contents($url, false, $context);
$end_time = microtime(true);

if ($response === false) {
    echo "❌ GAGAL: Tidak dapat mengakses API\n";
    echo "Error: " . error_get_last()['message'] . "\n\n";
} else {
    echo "✅ BERHASIL: API dapat diakses\n";
    echo "Response time: " . round(($end_time - $start_time) * 1000, 2) . "ms\n";
    echo "Response length: " . strlen($response) . " bytes\n\n";
}

// Test 2: Parse JSON response
echo "2. Testing JSON parsing...\n";
if ($response !== false) {
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "❌ GAGAL: Response bukan JSON valid\n";
        echo "JSON Error: " . json_last_error_msg() . "\n";
        echo "Response preview: " . substr($response, 0, 200) . "...\n\n";
    } else {
        echo "✅ BERHASIL: JSON valid\n";
        echo "Data structure:\n";
        print_r(array_keys($data));
        echo "\n";
        
        // Check if data contains pelabuhan information
        if (isset($data['data']) && is_array($data['data'])) {
            echo "✅ Data pelabuhan ditemukan: " . count($data['data']) . " records\n";
            
            // Show first few records
            echo "Sample data:\n";
            for ($i = 0; $i < min(3, count($data['data'])); $i++) {
                if (isset($data['data'][$i]['nama_pelabuhan'])) {
                    echo "- " . $data['data'][$i]['nama_pelabuhan'] . "\n";
                }
            }
        } else {
            echo "❌ Data pelabuhan tidak ditemukan dalam response\n";
        }
    }
}

// Test 3: Using cURL
echo "\n3. Testing with cURL...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$start_time = microtime(true);
$curl_response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$end_time = microtime(true);

if (curl_errno($ch)) {
    echo "❌ cURL Error: " . curl_error($ch) . "\n";
} else {
    echo "✅ cURL berhasil\n";
    echo "HTTP Code: " . $http_code . "\n";
    echo "Response time: " . round(($end_time - $start_time) * 1000, 2) . "ms\n";
    echo "Response length: " . strlen($curl_response) . " bytes\n";
}

curl_close($ch);

echo "\n=== SELESAI ===\n"; 