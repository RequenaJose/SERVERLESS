<?php
namespace Application\UseCases;
use Domain\Entities\MonitoreoAmbulancia;

class ObtenerTelemetriaUseCase {
    public function ejecutar(string $ticketId): array {
        // En el futuro, esto vendrá de un repositorio: $this->emergenciaRepository->obtenerPorTicket($ticketId);
        $datosEmergencia = [
            'ubicacion_exacta' => 'Av. Giráldez 230, Huancayo (Lat: -12.066, Lng: -75.207)',
            'descripcion' => 'Paciente masculino, 45 años, refiere dolor torácico intenso y dificultad para respirar tras esfuerzo físico.',
            'fotografia' => 'base64_image_placeholder...' 
        ];

        // Simulador de datos en tiempo real (GPS y Signos varían)
        $datosAmbulancia = [
            'unidad' => 'MÓVIL A-04 (Shock Trauma)',
            'origen' => 'Av. Giráldez 230, Huancayo',
            'destino' => 'Hospital Regional Docente Clínico Quirúrgico',
            'gps_actual' => 'Lat: -12.067, Lng: -75.208',
            'velocidad_kmh' => 65,
            'eta_minutos' => rand(5, 8)
        ];

        $signosVitales = [
            'frecuencia_cardiaca' => rand(105, 115), // Variación para simular tiempo real
            'saturacion_oxigeno' => rand(88, 93),
            'ecg_patron' => '/\_/\_/\_/\_/\_/\_/\_'
        ];

        $monitoreo = new MonitoreoAmbulancia($ticketId, $datosEmergencia, $datosAmbulancia, $signosVitales);
        
        return $monitoreo->toArray();
    }
}