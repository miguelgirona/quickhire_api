<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

abstract class BaseResourceController extends ResourceController
{
    use ResponseTrait;

    // clave jwt
    protected $clave = "Alumn@1234";

    
    protected function verificarToken($token)
    {
        try {

            $decoded = JWT::decode($token, new Key($this->clave, 'HS256'));
     
            return (array) $decoded;
        } catch (\Exception $e) {
            log_message('error', 'Error al decodificar el token: ' . $e->getMessage());
            return null; 
        }
    }

}
