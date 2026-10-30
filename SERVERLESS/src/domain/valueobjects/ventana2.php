<?php
namespace Domain\ValueObjects {
    class SignoVital {
        private float $frecuenciaCardiaca;
        private float $saturacionOxigeno;
        private string $idAmbulancia;
        private string $ubicacion;

        public function __construct(string $idAmbulancia, float $fc, float $spO2, string $ubicacion) {
            $this->idAmbulancia = $idAmbulancia;
            $this->frecuenciaCardiaca = $fc;
            $this->saturacionOxigeno = $spO2;
            $this->ubicacion = $ubicacion;
        }

        public function esCritico(): bool {
            // Regla de Negocio: FC > 100 BPM (Taquicardia) o SpO2 < 90% (Hipoxia)
            return ($this->frecuenciaCardiaca > 100 || $this->saturacionOxigeno < 90);
        }

        public function getIdAmbulancia(): string { return $this->idAmbulancia; }
        public function getFC(): float { return $this->frecuenciaCardiaca; }
        public function getSpO2(): float { return $this->saturacionOxigeno; }
        public function getUbicacion(): string { return $this->ubicacion; }
    }
}

namespace Application {
    use Domain\ValueObjects\SignoVital;

    class MonitorearAmbulanciaUseCase {
        public function procesarLectura(string $idAmbulancia, float $fc, float $spO2, string $ubicacion): array {
            $telemetria = new SignoVital($idAmbulancia, $fc, $spO2, $ubicacion);
            $esCritico = $telemetria->esCritico();

            return [
                'ambulancia' => $telemetria->getIdAmbulancia(),
                'fc'         => $telemetria->getFC(),
                'spO2'       => $telemetria->getSpO2(),
                'ubicacion'  => $telemetria->getUbicacion(),
                'es_critico' => $esCritico,
                'estado'     => $esCritico ? '🚨 SHOCK TRAUMA CRÍTICO' : '✅ PACIENTE ESTABLE',
                'timestamp'  => date('Y-m-d H:i:s')
            ];
        }
    }
}

namespace {
    use Application\MonitorearAmbulanciaUseCase;

    $useCase = new MonitorearAmbulanciaUseCase();
    $datosTelemetria = $useCase->procesarLectura('MÓVIL A-04', 110.0, 92.0, 'Av. Giráldez 230, Huancayo');

    if (php_sapi_name() === 'cli') {
        echo "======================================================================\n";
        echo " MONITOREO DE TELEMETRÍA MÓVIL EN TIEMPO REAL - VENTANA 2\n";
        echo "======================================================================\n";
        echo "Ambulancia : " . $datosTelemetria['ambulancia'] . "\n";
        echo "Ubicación  : " . $datosTelemetria['ubicacion'] . "\n";
        echo "Freq. Card : " . $datosTelemetria['fc'] . " BPM (Taquicardia)\n";
        echo "Saturación : " . $datosTelemetria['spO2'] . " % (Hipoxia Moderada)\n";
        echo "Estado     : " . $datosTelemetria['estado'] . "\n";
        echo "Hora/Fecha : " . $datosTelemetria['timestamp'] . "\n";
        echo "======================================================================\n";
        exit(0);
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ventana 2 - Sala de Urgencias Hospitalarias</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #F2F6F8; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background-color: #FFFFFF; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background-color: #173B4B; color: #FFFFFF; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .badge-status { background-color: #087F75; color: white; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: bold; }
        .content { padding: 25px; }
        .cards-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .card { background: #FFFFFF; border-radius: 6px; padding: 20px; border-left: 6px solid #173B4B; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .card.alert { border-left-color: #D94C4C; }
        .card-title { color: #666; font-size: 13px; text-transform: uppercase; font-weight: bold; }
        .card-value { font-size: 32px; font-weight: bold; margin: 10px 0; color: #173B4B; }
        .card-value.danger { color: #D94C4C; }
        .ecg-container { background-color: #111111; color: #087F75; padding: 20px; border-radius: 6px; font-family: monospace; font-size: 20px; overflow: hidden; letter-spacing: 2px; }
        .btn-group { display: flex; gap: 15px; margin-top: 25px; }
        .btn { padding: 12px 24px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; color: white; font-size: 14px; }
        .btn-danger { background-color: #D94C4C; }
        .btn-success { background-color: #087F75; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">🏥 Urgencias: Telemetría en Tiempo Real</h2>
            <span class="badge-status">● Streaming MQTT Activo</span>
        </div>
        <div class="content">
            <p><strong>Ambulancia:</strong> <?= htmlspecialchars($datosTelemetria['ambulancia']) ?> | <strong>Ubicación:</strong> <?= htmlspecialchars($datosTelemetria['ubicacion']) ?></p>
            
            <div class="cards-grid">
                <div class="card alert">
                    <div class="card-title">Frecuencia Cardíaca (FC)</div>
                    <div class="card-value danger"><?= $datosTelemetria['fc'] ?> <small style="font-size: 16px;">BPM</small></div>
                    <span style="color: #D94C4C; font-weight: bold;">⚠️ Taquicardia Detectada</span>
                </div>
                <div class="card alert">
                    <div class="card-title">Saturación de Oxígeno (SpO2)</div>
                    <div class="card-value danger"><?= $datosTelemetria['spO2'] ?> <small style="font-size: 16px;">%</small></div>
                    <span style="color: #D94C4C; font-weight: bold;">⚠️ Hipoxia Moderada</span>
                </div>
            </div>

            <div class="card-title" style="margin-bottom: 8px;">Electrocardiograma (ECG en vivo - 25 mm/s)</div>
            <div class="ecg-container">
                /\_/\_/\_/\_/\_/\_/\_/\_/\_/\_/\_/\_/\_/\_/\_/\_/\_/\_/\_/\_/\_/\_
            </div>

            <div class="btn-group">
                <button class="btn btn-danger">🚨 Preparar Camilla Shock Trauma</button>
                <button class="btn btn-success">🏥 Asignar Cama UCI</button>
            </div>
        </div>
    </div>
</body>
</html>
<?php } ?>
