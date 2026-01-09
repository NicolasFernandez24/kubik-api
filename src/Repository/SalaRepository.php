<?php
namespace App\Repository;

use PDO;

class SalaRepository
{
    public function __construct(private PDO $db) {}

    public function getAll(): array
    {
        return $this->db->query("SELECT * FROM salas")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM salas WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function insert(array $data): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO salas (nombre, descripcion, capacidad, precio, disponibilidad, imageFondo, imagenes)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['nombre'],
            $data['descripcion'],
            $data['capacidad'],
            $data['precio'],
            $data['disponibilidad'],
            $data['imageFondo'],
            json_encode($data['imagenes'])
        ]);
    }

    public function update(int $id, array $data): void
    {
        $sql = "UPDATE salas SET nombre=?, descripcion=?, capacidad=?, precio=?, disponibilidad=?";
        $params = [
            $data['nombre'],
            $data['descripcion'],
            $data['capacidad'],
            $data['precio'],
            $data['disponibilidad']
        ];

        if (!empty($data['imageFondo'])) {
            $sql .= ", imageFondo=?";
            $params[] = $data['imageFondo'];
        }

        if (!empty($data['imagenes'])) {
            $sql .= ", imagenes=?";
            $params[] = json_encode($data['imagenes']);
        }

        $sql .= " WHERE id=?";
        $params[] = $id;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM salas WHERE id = ?");
        $stmt->execute([$id]);
    }
}
