<?php
namespace Domain\ValueObjects;

class DetallePrescripcion {
    public function __construct(
        public string $medicamento,
        public string $dosis,
        public string $frecuencia,
        public string $duracion
    ) {}
}