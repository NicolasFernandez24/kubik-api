<?php
namespace App\Repository;

use PDO;

class UsuarioRepository
{
    public function __construct(private PDO $db) {}

    public function getAll(): array
    {
        return $this->db
            ->query("SELECT * FROM usuarios")
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
 public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        return $usuario ?: null;
    }
    public function insert(array $data): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO usuarios (nombre, apellido, telefono, email, contrasena, rol)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $data['nombre'],
            $data['apellido'],
            $data['telefono'],
            $data['email'],
            $data['contrasena'],
            $data['rol']
        ]);
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            "UPDATE usuarios
             SET nombre=?, apellido=?, telefono=?, email=?, contrasena=?, rol=?
             WHERE id=?"
        );

        $stmt->execute([
            $data['nombre'],
            $data['apellido'],
            $data['telefono'],
            $data['email'],
            $data['contrasena'],
            $data['rol'],
            $id
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
    }
}
