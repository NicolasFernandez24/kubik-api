<?php
namespace App\Service;

use App\Repository\UsuarioRepository;
use DomainException;

class UsuarioService
{
    public function __construct(private UsuarioRepository $repository) {}

    public function listar(): array
    {
        return $this->repository->getAll();
    }

    public function obtener(int $id): array
    {
        $usuario = $this->repository->getById($id);

        if (!$usuario) {
            throw new DomainException('Usuario no encontrado');
        }

        return $usuario;
    }

    public function crear(array $data): void
    {
        $this->validar($data, true);

        $data['contrasena'] = password_hash(
            $data['contrasena'],
            PASSWORD_DEFAULT
        );

        $this->repository->insert($data);
    }

    public function actualizar(int $id, array $data): void
    {
        $this->validar($data, false);

        $data['contrasena'] = password_hash(
            $data['contrasena'],
            PASSWORD_DEFAULT
        );

        $this->repository->update($id, $data);
    }

    public function eliminar(int $id): void
    {
        $this->repository->delete($id);
    }

    

    private function validar(array $data, bool $crear): void
    {
        $campos = ['nombre', 'apellido', 'telefono', 'email', 'contrasena', 'rol'];

        foreach ($campos as $campo) {
            if (empty($data[$campo])) {
                throw new DomainException("Campo requerido: $campo");
            }
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new DomainException('Email inválido');
        }
    }
}
