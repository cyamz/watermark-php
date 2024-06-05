<?

require 'library8/Color.php';
require 'library8/Image.php';
require 'library8/WaterMark.php';
require 'library8/ContentBox.php';

use library8\Color;
use library8\Image;
use library8\WaterMark;

$image = new Image('test.png');
$color = Color::createPresetColor('桃红');

$config = [
    'image' => $image,
    'color' => $color,
    'font_size' => 120,
    'angle' => 45
];
$water_mark = new WaterMark(...$config);

$water_mark->add([
    'CyAmz',
    '棒棒哒'
]);
