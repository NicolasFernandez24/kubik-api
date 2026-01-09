<?php
namespace App\Service;

use App\Repository\ReservaRepository;

class ReservaService
{
    private ReservaRepository $repo;

    public function __construct(ReservaRepository $repo)
    {
        $this->repo = $repo;
    }

    private function haySolapamiento(
        array $reservas,
        int $inicioNueva,
        int $finNueva
    ): ?array {
        foreach ($reservas as $reserva) {
            $inicio = strtotime("{$reserva['fecha']} {$reserva['hora']}");
            $fin = $inicio + ($reserva['duracion'] * 60);

            if ($inicioNueva < $fin && $finNueva > $inicio) {
                return $reserva;
            }
        }
        return null;
    }

    public function crearReserva(array $data): void
    {
        $inicio = strtotime("{$data['fecha']} {$data['hora']}");
        $fin = $inicio + ($data['duracion'] * 60);

        $reservas = $this->repo->getBySalaAndFecha($data['sala_id'], $data['fecha']);

        if ($conflicto = $this->haySolapamiento($reservas, $inicio, $fin)) {
            throw new \DomainException(json_encode([
                'error' => 'La sala ya está reservada en ese horario.',
                'reserva_existente' => $conflicto
            ]));
        }

        $this->repo->insert($data);
    }

    public function actualizarReserva(int $id, array $data): void
    {
        $inicio = strtotime("{$data['fecha']} {$data['hora']}");
        $fin = $inicio + ($data['duracion'] * 60);

        $reservas = $this->repo->getBySalaAndFecha(
            $data['sala_id'],
            $data['fecha'],
            $id
        );

        if ($conflicto = $this->haySolapamiento($reservas, $inicio, $fin)) {
            throw new \DomainException(json_encode([
                'error' => 'La sala ya está reservada en ese horario.',
                'reserva_existente' => $conflicto
            ]));
        }

        $this->repo->update($id, $data);
    }

    public function calcularHorariosDisponibles(
        int $salaId,
        string $fecha,
        int $duracion
    ): array {
        $inicioDia = strtotime("$fecha 08:00");
        $finDia = strtotime("$fecha 20:00");

        $reservas = $this->repo->getBySalaAndFecha($salaId, $fecha);

        $ocupados = [];
        foreach ($reservas as $r) {
            $ini = strtotime("$fecha {$r['hora']}");
            $fin = $ini + ($r['duracion'] * 60);
            $ocupados[] = [$ini, $fin];
        }

        $disponibles = [];
        for ($i = $inicioDia; $i + ($duracion * 60) <= $finDia; $i += 1800) {
            $finProp = $i + ($duracion * 60);
            $conflicto = false;

            foreach ($ocupados as [$ini, $fin]) {
                if ($i < $fin && $finProp > $ini) {
                    $conflicto = true;
                    break;
                }
            }

            if (!$conflicto) {
                $disponibles[] = date('H:i', $i);
            }
        }

        return $disponibles;
    }
}
