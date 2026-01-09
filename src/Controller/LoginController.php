<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Service\AuthService;

class LoginController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request, Response $response): Response
    {
        $data = json_decode($request->getBody()->getContents(), true);

        try {
            $user = $this->authService->login(
                $data['email'] ?? '',
                $data['contrasena'] ?? ''
            );

            $response->getBody()->write(json_encode([
                'message' => 'Login exitoso',
                'user' => $user
            ]));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);

        } catch (\InvalidArgumentException $e) {
            return $this->error($response, $e->getMessage(), 400);
        } catch (\RuntimeException $e) {
            return $this->error($response, $e->getMessage(), 401);
        }
    }

    private function error(Response $response, string $message, int $status): Response
    {
        $response->getBody()->write(json_encode(['error' => $message]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
