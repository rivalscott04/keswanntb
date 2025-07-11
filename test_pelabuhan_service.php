<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\PelabuhanService;

echo "=== TEST PELABUHAN SERVICE ===\n\n";

// Test 1: Get pelabuhan list
echo "1. Testing getPelabuhanList()...\n";
$pelabuhanList = PelabuhanService::getPelabuhanList();
echo "Total pelabuhan: " . count($pelabuhanList) . "\n";

if (count($pelabuhanList) > 0) {
    echo "Sample pelabuhan:\n";
    $count = 0;
    foreach ($pelabuhanList as $key => $value) {
        echo "- $value\n";
        $count++;
        if ($count >= 5) break;
    }
} else {
    echo "❌ Tidak ada data pelabuhan\n";
}

echo "\n";

// Test 2: Get stats
echo "2. Testing getPelabuhanStats()...\n";
$stats = PelabuhanService::getPelabuhanStats();
echo "Stats: " . json_encode($stats, JSON_PRETTY_PRINT) . "\n";

echo "\n";

// Test 3: Get options with loading
echo "3. Testing getPelabuhanOptionsWithLoading()...\n";
$options = PelabuhanService::getPelabuhanOptionsWithLoading();
echo "Options count: " . count($options) . "\n";

echo "\n";

// Test 4: Get placeholder
echo "4. Testing getPelabuhanPlaceholder()...\n";
$placeholder = PelabuhanService::getPelabuhanPlaceholder();
echo "Placeholder: $placeholder\n";

echo "\n";

// Test 5: Get helper text
echo "5. Testing getPelabuhanHelperText()...\n";
$helperText = PelabuhanService::getPelabuhanHelperText();
echo "Helper text: $helperText\n";

echo "\n=== SELESAI ===\n"; 