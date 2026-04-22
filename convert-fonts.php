<?php
/**
 * Script to convert custom fonts to TCPDF format
 * Run: php convert-fonts.php
 */

require_once __DIR__ . '/vendor/autoload.php';

// Google Fonts URLs - using direct download links
$googleFonts = [
    'DancingScript-Regular' => 'https://github.com/google/fonts/raw/main/ofl/dancingscript/static/DancingScript-Regular.ttf',
    'DancingScript-Bold' => 'https://github.com/google/fonts/raw/main/ofl/dancingscript/static/DancingScript-Bold.ttf',
    'Pacifico-Regular' => 'https://github.com/google/fonts/raw/main/ofl/pacifico/Pacifico-Regular.ttf',
    'GreatVibes-Regular' => 'https://github.com/google/fonts/raw/main/ofl/greatvibes/GreatVibes-Regular.ttf',
    'Allura-Regular' => 'https://github.com/google/fonts/raw/main/ofl/allura/Allura-Regular.ttf',
    'Sacramento-Regular' => 'https://github.com/google/fonts/raw/main/ofl/sacramento/Sacramento-Regular.ttf',
];

echo "Step 1: Downloading Google Fonts...\n";
foreach ($googleFonts as $name => $url) {
    $fontPath = __DIR__ . '/public/fonts/' . $name . '.ttf';
    if (!file_exists($fontPath)) {
        echo "Downloading {$name}...\n";
        
        // Use curl for better error handling
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($content && $httpCode == 200) {
            file_put_contents($fontPath, $content);
            echo "  ✓ Downloaded {$name}\n";
        } else {
            echo "  ✗ Failed to download {$name} (HTTP {$httpCode})\n";
        }
    } else {
        echo "  ✓ {$name} already exists\n";
    }
}

echo "\nStep 2: Converting fonts to TCPDF format...\n";

// Fonts to convert
$fontsToConvert = [
    'amsterdam' => __DIR__ . '/public/fonts/Amsterdam.ttf',
    'dancingscript' => __DIR__ . '/public/fonts/DancingScript-Regular.ttf',
    'dancingscript-bold' => __DIR__ . '/public/fonts/DancingScript-Bold.ttf',
    'pacifico' => __DIR__ . '/public/fonts/Pacifico-Regular.ttf',
    'greatvibes' => __DIR__ . '/public/fonts/GreatVibes-Regular.ttf',
    'allura' => __DIR__ . '/public/fonts/Allura-Regular.ttf',
    'sacramento' => __DIR__ . '/public/fonts/Sacramento-Regular.ttf',
];

foreach ($fontsToConvert as $fontName => $fontPath) {
    if (!file_exists($fontPath)) {
        echo "  ✗ Font file not found: {$fontPath}\n";
        continue;
    }
    
    echo "Converting {$fontName}...\n";
    
    try {
        // Use TCPDF_FONTS::addTTFfont to convert
        // Parameters: $fontfile, $fonttype='', $enc='', $flags=32, $outpath='', $platid=3, $encid=1, $addcbbox=false, $link=false
        // flags=32 means: 32 = Unicode (default)
        // We DON'T want uppercase, so we use default flags without modification
        $convertedName = TCPDF_FONTS::addTTFfont($fontPath, 'TrueTypeUnicode', '', 32);
        
        if ($convertedName) {
            echo "  ✓ Converted {$fontName} -> {$convertedName}\n";
        } else {
            echo "  ✗ Failed to convert {$fontName}\n";
        }
    } catch (Exception $e) {
        echo "  ✗ Error converting {$fontName}: " . $e->getMessage() . "\n";
    }
}

echo "\n✓ Font conversion completed!\n";
echo "Converted fonts are stored in: vendor/tecnickcom/tcpdf/fonts/\n";
