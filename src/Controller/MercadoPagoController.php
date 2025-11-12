<?php
namespace App\Controller;

require __DIR__ . '/../../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use App\Database\Database;
class MercadoPagoController {
    private $db;
    private string $accessToken;

    public function __construct(array $settings,Database $database)
    {
        $this->db = $database->getConnection();
        $this->accessToken = $settings['mercadopago']['access_token'];

        error_log("[MercadoPagoController] Inicializando con access token: " . substr($this->accessToken, 0, 10) . "...");

        MercadoPagoConfig::setAccessToken($this->accessToken);
    }

    public function crearPreferencia(Request $request, Response $response, array $args): Response {
        try {
            $data = json_decode($request->getBody(), true);
            error_log("[MercadoPagoController] Datos recibidos: " . json_encode($data));

         
            $monto = floatval($data['pagado'] ?? 0);
            $sala_id = intval($data['sala_id'] ?? 0);
            $usuario_id = intval($data['usuario_id'] ?? 0);
            $fecha = $data['fecha'] ?? null;
            $hora = $data['hora'] ?? null;
            $duracion = intval($data['duracion'] ?? 60);
            
            $stmt = $this->db->prepare("SELECT * FROM salas WHERE id = ?");
            $stmt->execute([$sala_id]);
            $sala = $stmt->fetch();
        
            if ($monto <= 0 || $sala_id <= 0 || !$usuario_id || !$fecha || !$hora) {
                throw new \Exception("Datos de reserva incompletos o inválidos.");
            }

          
            $external_reference = json_encode([
                "usuario_id" => $usuario_id,
                "sala_id" => $sala_id,
                "fecha" => $fecha,
                "hora" => $hora,
                "duracion" => $duracion,
                "pagado" => $monto
            ]);

          
            $client = new PreferenceClient();
            $prefRequest = [
                "items" => [
                    [
                        "title" => "Reserva Sala {$sala['nombre']}",
                        "quantity" => 1,
                        "unit_price" => $monto
                    ]
                ],
                "back_urls" => [
                   
                    "success" => "https://7a01c5a9e0e6.ngrok-free.app/success.php",
                    "failure" => "https://7a01c5a9e0e6.ngrok-free.app/failure.php",
                    "pending" => "https://7a01c5a9e0e6.ngrok-free.app/pending.php"
                ],
                "auto_return" => "approved",
                "external_reference" => $external_reference
            ];

            error_log("[MercadoPagoController] Payload enviado a MercadoPago: " . json_encode($prefRequest));

            $preference = $client->create($prefRequest);

            error_log("[MercadoPagoController] Preferencia creada correctamente: " . json_encode($preference));

         
            $payload = [
                "init_point" => $preference->init_point ?? null,
                "preference_id" => $preference->id ?? null
            ];

            $response->getBody()->write(json_encode($payload));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (MPApiException $e) {
            error_log("[MercadoPagoController] Error API MercadoPago: " . json_encode($e->getApiResponse()->getContent()));

            $response->getBody()->write(json_encode([
                "error" => "Error en la API de MercadoPago",
                "details" => $e->getApiResponse()->getContent()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);

        } catch (\Exception $e) {
            error_log("[MercadoPagoController] Error crearPreferencia: {$e->getMessage()}");

            $response->getBody()->write(json_encode([
                "error" => "No se pudo crear la preferencia: " . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
