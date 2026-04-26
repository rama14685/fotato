<?php
$image = imagecreatetruecolor(600, 400);
$white = imagecolorallocate($image, 255, 255, 255);
$blue = imagecolorallocate($image, 0, 102, 204);
imagefill($image, 0, 0, $white);
imagefilledrectangle($image, 50, 50, 550, 350, $blue);
imagestring($image, 5, 200, 180, 'Test Photo', 0xFFFFFF);
imagejpeg($image, 'storage/app/public/test.jpg', 90);
imagedestroy($image);
echo 'Test image created successfully';
?>
