<?php
namespace Application\UseCases;
use Domain\ValueObjects\AlertaSOS;

class RegistrarAlertaUseCase {
    public function ejecutar(string $ubicacion, string $descripcion, array $fotografias): array {
        $alerta = new AlertaSOS($ubicacion, $descripcion, $fotografias);
        
        // Simulación de encolado asíncrono (SQS/RabbitMQ) para alta demanda
        $colaEventos = true; 

        return [
            'ticket' => $alerta->getIdUnico(),
            'estado' => 'Registrado y encolado. Asignado a central de auxilio rápido.',
            'timestamp' => $alerta->getTimestamp(),
            'procesado_en_segundos' => 0.45 // Simulación de procesamiento < 2s
        ];
    }
}