<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Database\Database;

class LoginController
{
    private $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function login(Request $request, Response $response): Response
    {
        // Obtener datos del body (JSON)
        $data = json_decode($request->getBody()->getContents(), true);
        $email = $data['email'] ?? '';
        $password = $data['contrasena'] ?? '';

        if (empty($email) || empty($password)) {
            $response->getBody()->write(json_encode(['error' => 'Email y contraseña son requeridos']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Buscar usuario por email
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$usuario) {
            $response->getBody()->write(json_encode(['error' => 'Usuario no encontrado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        // Verificar contraseña
        if (!password_verify($password, $usuario['contrasena'])) {
            $response->getBody()->write(json_encode(['error' => 'Contraseña incorrecta']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        
        $tipoLogin = ($usuario['rol'] === 'admin') ? 'admin' : 'cliente';

     
        $userData = [
            'id' => $usuario['id'],
            'nombre' => $usuario['nombre'],
            'apellido' => $usuario['apellido'],
            'email' => $usuario['email'],
            'telefono' => $usuario['telefono'],
            'rol' => $usuario['rol'],
            'tipo_login' => $tipoLogin
        ];

        $response->getBody()->write(json_encode([
            'message' => 'Login exitoso',
            'user' => $userData
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
