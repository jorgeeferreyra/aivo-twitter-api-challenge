<?php
/*
 * Los end points irían aquí
 */

// Declaro la ruta root con un callback a través de un metodo de instancia
// con la forma que provee Slim
$app->get('/', App\Controllers\FrontController::class . ":index");
$app->get('/{username}', App\Controllers\FrontController::class . ":getTwitterFeed");