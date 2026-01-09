<?php
namespace App\Service;

use App\Repository\UsuarioRepository;

class AuthService
{
    private UsuarioRepository $usuarioRepository;

    public function __construct(UsuarioRepository $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    public function login(string $email, string $password): array
    {
        if (empty($email) || empty($password)) {
            throw new \InvalidArgumentException('Email y contraseña son requeridos');
        }

        $usuario = $this->usuarioRepository->findByEmail($email);

        if (!$usuario) {
            throw new \RuntimeException('Usuario no encontrado');
        }

        if (!password_verify($password, $usuario['contrasena'])) {
            throw new \RuntimeException('Contraseña incorrecta');
        }

        $tipoLogin = ($usuario['rol'] === 'admin') ? 'admin' : 'cliente';

        return [
            'id' => $usuario['id'],
            'nombre' => $usuario['nombre'],
            'apellido' => $usuario['apellido'],
            'email' => $usuario['email'],
            'telefono' => $usuario['telefono'],
            'rol' => $usuario['rol'],
            'tipo_login' => $tipoLogin
        ];
    }
}
