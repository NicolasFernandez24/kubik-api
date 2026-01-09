<?php
namespace App\Repository;

use PDO;

class ReservaRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAll(): array
    {
        return $this->db->query("
            SELECT r.*, u.nombre AS usuario_nombre, u.apellido, u.telefono, s.nombre AS sala_nombre
            FROM reservas r
            JOIN usuarios u ON r.usuario_id = u.id
            JOIN salas s ON r.sala_id = s.id
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByUsuarioId(int $usuarioId): array
    {
        $stmt = $this->db->prepare("
            SELECT r.*, u.nombre AS usuario_nombre, u.apellido, u.telefono, s.nombre AS sala_nombre
            FROM reservas r
            JOIN usuarios u ON r.usuario_id = u.id
            JOIN salas s ON r.sala_id = s.id
            WHERE r.usuario_id = ?
        ");
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBySalaAndFecha(int $salaId, string $fecha, ?int $excludeId = null): array
    {
        $sql = "SELECT * FROM reservas WHERE sala_id = ? AND fecha = ?";
        $params = [$salaId, $fecha];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert(array $data): void
    {
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
    }

    public function update(int $id, array $data): void
    {
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
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM reservas WHERE id = ?");
        $stmt->execute([$id]);
    }
}
