<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Ejercicio Pedidos API",
    description: "API para gestión de productos, órdenes y usuarios con autenticación JWT y Sanctum"
)]
#[OA\Server(
    url: "http://localhost:8000/api",
    description: "Servidor local"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
abstract class Controller
{
    //
}