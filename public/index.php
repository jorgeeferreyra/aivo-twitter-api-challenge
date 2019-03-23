<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Defino algunas rutas globales que pueden servirme en un futuro
define("ROOT_PATH",   join(DIRECTORY_SEPARATOR, [ __DIR__, ".." ]));
define("VENDOR_PATH", join(DIRECTORY_SEPARATOR, [ ROOT_PATH, "vendor" ]));
define("APP_PATH",    join(DIRECTORY_SEPARATOR, [ ROOT_PATH, "app" ]));

// Incluyo el autoload de composer, 
// donde también declaro mi carpeta app con el namespace App
require join(DIRECTORY_SEPARATOR, [ VENDOR_PATH, "autoload.php" ]);

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];

$c = new \Slim\Container($configuration);

// Inicializo la aplicación
$app = new \Slim\App($c);

// Importo las constantes
require join(DIRECTORY_SEPARATOR, [ APP_PATH, "constants.php" ]);
// Importo las rutas
require join(DIRECTORY_SEPARATOR, [ APP_PATH, "routes.php" ]);

// Lanzo la aplicación
$app->run();