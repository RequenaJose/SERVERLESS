<?php
namespace Domain\Entities;

class MonitoreoAmbulancia {
    private string $idTicket; // ID de la Emergencia
    private string $idAmbulancia; 
    private string $ubigeo; // Clave para la partición regional (Ej: '120101' - Huancayo)
    private array $datosEmergencia;
    private array $datosAmbulancia;
    private array $signosVitales;

    public function __construct(string $idTicket, array $datosEmergencia, array $datosAmbulancia, array $signosVitales) {
        $this->idTicket = $idTicket;
        $this->datosEmergencia = $datosEmergencia;
        $this->datosAmbulancia = $datosAmbulancia;
        $this->signosVitales = $signosVitales;
        $this->timestamp = date('Y-m-d H:i:s.v'); // Precisión de milisegundos para series de tiempo
    }

    public function toArray(): array {
        // Lógica de negocio para determinar criticidad basada en signos
        $fc = $this->signosVitales['frecuencia_cardiaca'];
        $spo2 = $this->signosVitales['saturacion_oxigeno'];
        $estado = ($fc > 100 || $spo2 < 90) ? 'CRÍTICO' : 'ESTABLE';

        return [
            'ticket_id' => $this->idTicket,
            'emergencia' => $this->datosEmergencia,
            'ambulancia' => $this->datosAmbulancia,
            'signos_vitales' => array_merge($this->signosVitales, [
                'alerta_fc' => $fc > 100 ? '⚠️ Taquicardia' : 'Normal',
                'alerta_spo2' => $spo2 < 90 ? '⚠️ Hipoxia' : 'Normal',
                'estado_paciente' => $estado
            ]),
            'timestamp' => $this->timestamp
        ];
    }

    public function cambiarDestino(string $nuevoHospital): void {
        $this->datosAmbulancia['destino'] = $nuevoHospital;
        $this->datosAmbulancia['estado_ruta'] = 'DESVIADO_POR_EMERGENCIA';
    }
}