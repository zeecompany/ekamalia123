<?php
/**
 * eKamalia configuration template.
 * The installer writes the real config to config/config.php
 * (never commit / edit that file by hand in production).
 */
return [
    'db' => [
        'host'    => 'localhost',
        'name'    => 'u107154643_kml',
        'user'    => 'u107154643_kml1',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'url'      => 'https://ekamalia.com',
        'key'      => 'change-this-random-32-char-key',
        'timezone' => 'Asia/Karachi',
        'env'      => 'production',   // production | development
        'version'  => '1.0.0',
    ],
];
