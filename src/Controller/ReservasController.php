<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Database\Database;

class ReservasController
{
    private $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function getAll(Request $request, Response $response): Response
    {
        $stmt = $this->db->query("
            SELECT r.*, u.nombre AS usuario_nombre, u.apellido, u.telefono, s.nombre AS sala_nombre
            FROM reservas r
            JOIN usuarios u ON r.usuario_id = u.id
            JOIN salas s ON r.sala_id = s.id
        ");
        $reservas = $stmt->fetchAll();
        $response->getBody()->write(json_encode($reservas));
        return $response->withHeader('Content-Type', 'application/json');
    }
public function getByUsuarioId(Request $request, Response $response, array $args): Response
{
    $usuario_id = $args['usuario_id'];

    try {
        $sql = "
            SELECT r.*, 
                   u.nombre AS usuario_nombre, 
                   u.apellido, 
                   u.telefono, 
                   s.nombre AS sala_nombre
            FROM reservas r
            JOIN usuarios u ON r.usuario_id = u.id
            JOIN salas s ON r.sala_id = s.id
            WHERE r.usuario_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuario_id]);
        $reservas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($reservas));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (\PDOException $e) {
        $error = ['error' => $e->getMessage()];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
}


    public function getBySala(Request $request, Response $response, array $args): Response
    {
        $sala_id = $args['sala_id'];
        $stmt = $this->db->prepare("
            SELECT r.*, u.nombre AS usuario_nombre, u.apellido, u.telefono, s.nombre AS sala_nombre
            FROM reservas r
            JOIN usuarios u ON r.usuario_id = u.id
            JOIN salas s ON r.sala_id = s.id
            WHERE r.sala_id = ?
        ");
        $stmt->execute([$sala_id]);
        $reservas = $stmt->fetchAll();
        $response->getBody()->write(json_encode($reservas));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function create(Request $request, Response $response): Response
    {
        $data = json_decode($request->getBody(), true);
        $fecha = $data['fecha'];
        $hora = $data['hora'];
        $duracion = intval($data['duracion']);
        $sala_id = $data['sala_id'];

        // Convertimos hora y duración a formato datetime
        $inicioNueva = strtotime("$fecha {$hora}");
        $finNueva = $inicioNueva + ($duracion * 60);

        // Verificamos superposición
        $stmt = $this->db->prepare("
            SELECT * FROM reservas
            WHERE sala_id = ? AND fecha = ?
        ");
        $stmt->execute([$sala_id, $fecha]);
        $reservas = $stmt->fetchAll();

        foreach ($reservas as $reserva) {
            
            $inicioExistente = strtotime("{$reserva['fecha']} {$reserva['hora']}");
            $finExistente = $inicioExistente + ($reserva['duracion'] * 60);
            if ($inicioNueva < $finExistente && $finNueva > $inicioExistente) {
                $response->getBody()->write(json_encode([
                    'error' => 'La sala ya está reservada en ese horario.',
                    'reserva_existente' => $reserva
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(409); // conflicto
            }
        }

        // Insertamos si no hay conflicto
        $stmt = $this->db->prepare("
            INSERT INTO reservas (usuario_id, sala_id, fecha, hora, duracion, pagado)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['usuario_id'],
            $data['sala_id'],
            $data['fecha'],
            $data['hora'],
            $data['duracion'],
            $data['pagado']
        ]);

        $response->getBody()->write(json_encode(['message' => 'Reserva creada con éxito']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }
    public function update(Request $request, Response $response, array $args): Response
{
    $id = $args['id'];
    $data = json_decode($request->getBody(), true);

    $fecha = $data['fecha'];
    $hora = $data['hora'];
    $duracion = intval($data['duracion']);
    $sala_id = $data['sala_id'];

    $inicioNueva = strtotime("$fecha {$hora}");
    $finNueva = $inicioNueva + ($duracion * 60);

    // Verificar solapamiento (excluyendo esta misma reserva)
    $stmt = $this->db->prepare("
        SELECT * FROM reservas
        WHERE sala_id = ? AND fecha = ? AND id != ?
    ");
    $stmt->execute([$sala_id, $fecha, $id]);
    $reservas = $stmt->fetchAll();

    foreach ($reservas as $reserva) {
        $inicioExistente = strtotime("{$reserva['fecha']} {$reserva['hora']}");
        $finExistente = $inicioExistente + ($reserva['duracion'] * 60);

        if ($inicioNueva < $finExistente && $finNueva > $inicioExistente) {
            $response->getBody()->write(json_encode([
                'error' => 'La sala ya está reservada en ese horario.',
                'reserva_existente' => $reserva
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
        }
    }

    // Update
    $stmt = $this->db->prepare("
        UPDATE reservas
        SET usuario_id = ?, sala_id = ?, fecha = ?, hora = ?, duracion = ?, pagado = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $data['usuario_id'],
        $data['sala_id'],
        $data['fecha'],
        $data['hora'],
        $data['duracion'],
        $data['pagado'],
        $id
    ]);

    $response->getBody()->write(json_encode(['message' => 'Reserva actualizada correctamente']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
}

public function getHorariosDisponibles(Request $request, Response $response, array $args): Response
{
    try {
        $sala_id = $args['sala_id'];
        $fecha = $request->getQueryParams()['fecha'] ?? null;
        $duracion = intval($request->getQueryParams()['duracion'] ?? 60);

        if (!$fecha) {
            $response->getBody()->write(json_encode(['error' => 'Fecha requerida (query param)']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $horaInicio = strtotime("$fecha 08:00");
        $horaFin = strtotime("$fecha 20:00");

        $stmt = $this->db->prepare("SELECT hora, duracion FROM reservas WHERE sala_id = ? AND fecha = ?");
        $stmt->execute([$sala_id, $fecha]);
        $reservas = $stmt->fetchAll();

        $rangosOcupados = [];
        foreach ($reservas as $reserva) {
            $inicio = strtotime("$fecha {$reserva['hora']}");
            $fin = $inicio + ($reserva['duracion'] * 60);
            $rangosOcupados[] = [$inicio, $fin];
        }

        $disponibles = [];
        for ($inicioPropuesto = $horaInicio; $inicioPropuesto + ($duracion * 60) <= $horaFin; $inicioPropuesto += 1800) {
            $finPropuesto = $inicioPropuesto + ($duracion * 60);
            $conflicto = false;

            foreach ($rangosOcupados as [$ini, $fin]) {
                if ($inicioPropuesto < $fin && $finPropuesto > $ini) {
                    $conflicto = true;
                    break;
                }
            }

            if (!$conflicto) {
                $disponibles[] = date("H:i", $inicioPropuesto);
            }
        }

        $response->getBody()->write(json_encode([
            'fecha' => $fecha,
            'duracion' => $duracion,
            'horarios_disponibles' => $disponibles
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (\Throwable $e) {
        error_log("❌ Error en getHorariosDisponibles: " . $e->getMessage());
        error_log($e->getTraceAsString());
        $response->getBody()->write(json_encode(['error' => 'Error interno del servidor']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
}



    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $stmt = $this->db->prepare("DELETE FROM reservas WHERE id = ?");
        $stmt->execute([$id]);
        $response->getBody()->write(json_encode(['message' => 'Reserva eliminada']));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
