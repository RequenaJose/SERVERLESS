<?php
namespace Domain\ValueObjects;

class AlertaSOS {
    private string $idUnico;
    private string $ubicacionGps;
    private string $descripcion;
    private array $fotografias;
    private string $timestamp;

    public function __construct(string $ubicacionGps, string $descripcion, array $fotografias = []) {
        $this->idUnico = 'TICKET-' . rand(10000, 99999);
        $this->ubicacionGps = $ubicacionGps;
        $this->descripcion = $descripcion;
        $this->fotografias = $fotografias;
        $this->timestamp = date('Y-m-d H:i:s');
    }

    public function getIdUnico(): string { return $this->idUnico; }
    public function getUbicacion(): string { return $this->ubicacionGps; }
    public function getDescripcion(): string { return $this->descripcion; }
    public function getTimestamp(): string { return $this->timestamp; }
}