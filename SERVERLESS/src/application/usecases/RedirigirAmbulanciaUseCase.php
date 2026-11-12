<?php
namespace Application\UseCases;

class RedirigirAmbulanciaUseCase {
    public function ejecutar(string $ticketId, string $nuevoDestino, string $motivo): array {
        // Aquí se actualizaría la base de datos y se enviaría una alerta MQTT al chofer
        return [
            'ticket_id' => $ticketId,
            'nuevo_destino' => $nuevoDestino,
            'timestamp' => date('Y-m-d H:i:s'),
            'mensaje' => "Ambulancia redirigida exitosamente a $nuevoDestino"
        ];
    }
}