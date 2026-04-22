<?php
/**
 * Script to convert custom fonts to TCPDF format
 * Run: php convert-fonts.php
 */

require_once __DIR__ . '/vendor/autoload.php';

// Google Fonts URLs
$googleFonts = [
    'DancingScript-Regular' => 'https://github.com/google/fonts/raw/main/ofl/dancingscript/DancingScript%5Bwght%5D.ttf',
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
        $content = @file_get_contents($url);
        if ($content) {
            file_put_contents($fontPath, $content);
            echo "  ✓ Downloaded {$name}\n";
        } else {
            echo "  ✗ Failed to download {$name}\n";
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
        $convertedName = TCPDF_FONTS::addTTFfont($fontPath, 'TrueTypeUnicode', '', 96);
        
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
