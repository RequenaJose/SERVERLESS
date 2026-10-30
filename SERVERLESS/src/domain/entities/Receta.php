<?php
namespace Domain\Entities;
use Domain\ValueObjects\DetallePrescripcion;

class Receta {
    public string $idReceta;
    public string $estado;
    public string $tokenValidacion;
    public string $codigoQR;
    public array $detalles = [];

    public function __construct(public string $pacienteId, public string $diagnostico) {
        $this->idReceta = "REC-" . uniqid();
        $this->estado = "CREADA";
    }

    public function agregarDetalle(DetallePrescripcion $detalle): void {
        $this->detalles[] = $detalle;
    }

    public function generarTokenUnico(): void {
        $this->tokenValidacion = bin2hex(random_bytes(8));
        $this->codigoQR = "QR_" . $this->tokenValidacion;
    }
}