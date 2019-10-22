<?php

return [
    'characters' => ['2', '3', '4', '6', '7', '8', '9'],
    'default' => [
        'length' => 5,
        'width' => 150,
        'height' => 50,
        'quality' => 90,
        'math' => false,
    ],
    'math' => [
        'length' => 5,
        'width' => 150,
        'height' => 50,
        'quality' => 90,
        'math' => true,
    ],

    'flat' => [
        'length' => 6,
        'width' => 160,
        'height' => 46,
        'quality' => 90,
        'lines' => 6,
        'bgImage' => false,
        'bgColor' => '#ecf2f4',
        'fontColors' => ['#2c3e50', '#c0392b', '#16a085', '#8e44ad', '#303f9f', '#f57c00', '#795548'],
        'contrast' => -5,
    ],
    'mini' => [
        'length' => 3,
        'width' => 60,
        'height' => 32,
    ],
    'inverse' => [
        'length' => 5,
        'width' => 150,
        'height' => 50,
        'quality' => 90,
        'sensitive' => true,
        'angle' => 12,
        'sharpen' => 10,
        'blur' => 2,
        'invert' => true,
        'contrast' => -5,
    ]
];
