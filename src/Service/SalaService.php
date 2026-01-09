<?php
namespace App\Service;

use App\Repository\SalaRepository;
use App\Utils\FileUploader;
use DomainException;

class SalaService
{
  

    public function __construct(
        private SalaRepository $repository,
        private FileUploader $uploader
    ) {}

    public function listar(): array
    {
        $salas = $this->repository->getAll();

        foreach ($salas as &$sala) {
            $sala['imagenes'] = isset($sala['imagenes'])
                ? json_decode($sala['imagenes'], true)
                : [];
        }

        return $salas;
    }

    public function obtener(int $id): array
    {
        $sala = $this->repository->getById($id);

        if (!$sala) {
            throw new DomainException('Sala no encontrada');
        }

        $sala['imagenes'] = json_decode($sala['imagenes'] ?? '[]', true);
        return $sala;
    }


   public function crear(array $data, array $files): void
{
    $this->validarDatos($data, true);

    if (empty($files['imagenFondo']) || empty($files['imagenes'])) {
        throw new DomainException('Imágenes obligatorias');
    }

    $imageFondo = $this->uploader->uploadSingle(
        $files['imagenFondo']
    );

    $imagenes = $this->uploader->uploadMultiple(
        $files['imagenes']
    );

    $payload = [
        'nombre'         => $data['nombre'],
        'descripcion'    => $data['descripcion'],
        'capacidad'      => $data['capacidad'],
        'precio'         => $data['precio'],
        'disponibilidad' => (int) filter_var($data['disponibilidad'], FILTER_VALIDATE_BOOLEAN),
        'imageFondo'     => $imageFondo,
        'imagenes'       => json_encode($imagenes)
    ];

    $this->repository->insert($payload);
}


   public function actualizar(int $id, array $data, array $files): void
{
    $this->validarDatos($data, false);

    $payload = [
        'nombre'         => $data['nombre'],
        'descripcion'    => $data['descripcion'],
        'capacidad'      => $data['capacidad'],
        'precio'         => $data['precio'],
        'disponibilidad' => (int) $data['disponibilidad']
    ];

    if (!empty($files['imagenFondo'])) {
        $payload['imageFondo'] = $this->uploader->uploadSingle(
            $files['imagenFondo']
        );
    }

    if (!empty($files['imagenes'])) {
        $payload['imagenes'] = json_encode(
            $this->uploader->uploadMultiple($files['imagenes'])
        );
    }

    $this->repository->update($id, $payload);
}


    public function eliminar(int $id): void
    {
        $this->repository->delete($id);
    }



    private function validarDatos(array $data, bool $crear): void
    {
        $campos = ['nombre', 'descripcion', 'capacidad', 'precio', 'disponibilidad'];

        foreach ($campos as $campo) {
            if (!isset($data[$campo])) {
                throw new DomainException("Campo requerido: $campo");
            }
        }

        if ($data['capacidad'] <= 0) {
            throw new DomainException('La capacidad debe ser mayor a 0');
        }

        if ($data['precio'] <= 0) {
            throw new DomainException('El precio debe ser mayor a 0');
        }
    }
}
