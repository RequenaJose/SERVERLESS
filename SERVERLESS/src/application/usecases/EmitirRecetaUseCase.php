<?php
namespace Application\UseCases;

use Application\Ports\Input\EmitirRecetaInputPort;
use Application\Ports\Output\{ValidadorColegiaturaPort, FirmaDigitalPort, SyncFarmaciaPort, NotificacionAppPort, RecetaRepositoryPort};
use Domain\Entities\Receta;
use Domain\ValueObjects\DetallePrescripcion;

class EmitirRecetaUseCase implements EmitirRecetaInputPort {
    public function __construct(
        private ValidadorColegiaturaPort $validador,
        private FirmaDigitalPort $firma,
        private SyncFarmaciaPort $b2b,
        private NotificacionAppPort $notificador,
        private RecetaRepositoryPort $repo
    ) {}

    public function emitir(array $datos): Receta {
        if (!$this->validador->validarColegiatura($datos['medicoCmp'], $datos['medicoDni'])) {
            throw new \Exception("Colegiatura no validada.");
        }

        $receta = new Receta($datos['pacienteId'], $datos['diagnostico']);
        foreach ($datos['detalles'] as $d) {
            $receta->agregarDetalle(new DetallePrescripcion($d['medicamento'], $d['dosis'], $d['frecuencia'], $d['duracion']));
        }
        $receta->generarTokenUnico();

        $docFirmado = $this->firma->firmarDocumento($receta);
        $receta->estado = "FIRMADA";

        $this->b2b->enviarAB2B($docFirmado);
        $this->notificador->enviarTokenYQR($receta->tokenValidacion, $receta->codigoQR, $receta->pacienteId);
        $this->repo->guardarReceta($receta);

        return $receta;
    }
}