<?php
$targetDir = __DIR__ . '/../uploads/photos/';
$fileName = 'test.webp';
$filePath = $targetDir . $fileName;

if (!file_exists($targetDir)) {
    mkdir($targetDir, 0755, true);
}

$image = imagecreatetruecolor(100, 100);
$white = imagecolorallocate($image, 255, 255, 255);
imagefilledrectangle($image, 0, 0, 100, 100, $white);

if (imagewebp($image, $filePath, 80)) {
    echo "✅ Image enregistrée dans $filePath";
} else {
    echo "❌ Impossible d'enregistrer l'image.";
}

imagedestroy($image);
