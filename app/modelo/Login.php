<?php 
 
require_once __DIR__ . "/AccesoDatosUsuario.php"; 
 
class Login { 
    private AccesoDatosUsuario $accesoDatosUsuario; 
 
    public function __construct(AccesoDatosUsuario $accesoDatosUsuario) { 
        $this->accesoDatosUsuario = $accesoDatosUsuario; 
    } 
 
    public function autenticar(string $documento_identidad, string $clave): ?Usuario { 
 
        $usuario = $this->accesoDatosUsuario->buscarUsuario($documento_identidad); 
 
        if ($usuario === null) { 
            return null; 
        } 
 
        if (!password_verify($clave, $usuario->getClaveHash())) { 
            return null; 
        } 
 
        return $usuario; 
    } 
} 
 
?>