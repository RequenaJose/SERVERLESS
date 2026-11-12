// --- Navegación SPA ---
function switchView(viewId) {
    document.querySelectorAll('.view').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.nav-links button').forEach(el => el.classList.remove('active'));
    
    document.getElementById(viewId).classList.add('active');
    document.getElementById(`nav-${viewId}`).classList.add('active');
}

// --- HU 1: Alerta SOS ---
document.getElementById('btnSos').addEventListener('click', () => {
    const status = document.getElementById('sosStatus');
    const btn = document.getElementById('btnSos');
    btn.disabled = true;
    btn.innerText = "Procesando...";
    
    // Simulación de procesamiento Serverless de alta demanda (< 2s)
    setTimeout(() => {
        const ticket = "TKT-" + Math.floor(Math.random() * 1000000);
        status.style.display = 'block';
        status.className = 'status-panel status-success mt-15';
        status.innerHTML = `✅ <b>Alerta Procesada.</b><br>
                            Ticket de Emergencia: <strong>${ticket}</strong><br>
                            Estado: Encolado en gestor de eventos (Asíncrono) - Unidades en camino.`;
        btn.innerText = "🚨 ALERTA ENVIADA 🚨";
    }, 1200); 
});

// --- HU 2: Monitoreo Ambulancias ---
let isConnected = true;
let monitorInterval = setInterval(() => {
    if (!isConnected) return; // Si no hay red, acumula en batch (simulado)
    
    // Simular telemetría en tiempo real (< 1s latencia)
    document.getElementById('spo2').innerText = Math.floor(Math.random() * (100 - 92 + 1)) + 92;
    document.getElementById('ecg').innerText = Math.floor(Math.random() * (100 - 70 + 1)) + 70;
    
    let lat = parseFloat(document.getElementById('ambLat').innerText);
    document.getElementById('ambLat').innerText = (lat + 0.0001).toFixed(4);
}, 1000);

document.getElementById('btnToggleConn').addEventListener('click', (e) => {
    isConnected = !isConnected;
    const statusText = document.getElementById('ambConn');
    if(isConnected) {
        statusText.innerText = "Conectado (MQTT) - Sincronizando Batch";
        statusText.style.color = "green";
        e.target.innerText = "Simular Pérdida de Conexión";
    } else {
        statusText.innerText = "Desconectado - Almacenando localmente";
        statusText.style.color = "red";
        e.target.innerText = "Restaurar Conexión";
    }
});

// --- HU 3: Derivación Inteligente ---
document.getElementById('btnConsultarCamas').addEventListener('click', () => {
    const status = document.getElementById('camasStatus');
    const table = document.getElementById('tablaHospitales');
    const tbody = document.getElementById('listaHospitales');
    
    status.style.display = 'block';
    status.className = 'status-panel status-warning mt-15';
    status.innerText = "Consultando ESSALUD, MINSA y Privados...";
    table.classList.add('hidden');

    setTimeout(() => {
        // Simulación de respuesta con Circuit Breaker y Ordenamiento
        status.className = 'status-panel status-success mt-15';
        status.innerText = "Resultados consolidados obtenidos en 1.1s";
        table.classList.remove('hidden');
        
        tbody.innerHTML = `
            <tr><td>Hosp. Carrión</td><td>Huancayo</td><td>4</td><td>12</td><td>2.5 km</td><td style="color:green">Tiempo Real</td></tr>
            <tr><td>Clínica Ortega</td><td>Huancayo</td><td>1</td><td>5</td><td>3.1 km</td><td style="color:orange">En Caché (Circuit Breaker)</td></tr>
            <tr><td>Hosp. Félix Mayorca</td><td>Tarma</td><td>0</td><td>2</td><td>45.0 km</td><td style="color:green">Tiempo Real</td></tr>
        `;
    }, 1100);
});

// --- HU 4: Receta Electrónica ---
document.getElementById('recetaForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const btn = document.getElementById('btnEmitirReceta');
    const status = document.getElementById('recetaStatus');
    
    btn.disabled = true;
    btn.innerText = 'Procesando...';
    
    setTimeout(() => {
        const token = Math.random().toString(36).substring(2, 10).toUpperCase();
        status.style.display = 'block';
        status.className = 'status-panel status-success mt-15';
        status.innerHTML = `
            <p>✅ <b>Colegiatura:</b> Validada en RENIEC/CMP.</p>
            <p>✅ <b>Firma y Sincronización:</b> Documento firmado y enviado a B2B Farmacias.</p>
            <p>📱 <b>App Paciente:</b> Receta enviada. Token: <strong>${token}</strong></p>`;
        btn.innerText = '✅ Receta Emitida Exitosamente';
    }, 1500);
});

document.addEventListener('DOMContentLoaded', () => {
    const btnSOS = document.getElementById('btnActivarSOS');
    
    if(btnSOS) {
        btnSOS.addEventListener('click', async () => {
            const originalText = btnSOS.innerText;
            // 1. Cambiar el texto de carga:
            btnSOS.innerText = "⏳ ENVIANDO REGISTRO...";
            btnSOS.style.backgroundColor = "#B71C1C";
            btnSOS.disabled = true;

            const descripcion = document.getElementById('txtDescripcion').value;

            try {
                const response = await fetch('src/adapters/in/controllers/SOSController.php', {
                    // ... fetch body (se mantiene igual) ...
                });

                const json = await response.json();

                if(json.success) {
                    document.getElementById('lblTicketRes').innerText = json.data.ticket;
                    document.getElementById('alertaExito').style.display = 'block';
                    
                    // 2. Cambiar el texto de éxito:
                    btnSOS.innerText = "✅ REGISTRO ENVIADO";
                    btnSOS.style.backgroundColor = "#4CAF50"; 
                }
            } catch (error) {
                console.error("Error al enviar registro:", error);
                alert("Hubo un error de conexión, intentando reconectar...");
                btnSOS.innerText = originalText;
                btnSOS.disabled = false;
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', () => {
    // 1. Lógica para cambiar entre pestañas del menú
    const navButtons = document.querySelectorAll('.nav-btn'); // Asume que tus botones del menú tienen esta clase
    const views = document.querySelectorAll('.view');

    navButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetId = button.getAttribute('data-target'); // Ej: data-target="hu1"

            // Ocultar todas las vistas y quitar el estado activo de los botones
            views.forEach(view => view.classList.remove('active'));
            navButtons.forEach(btn => btn.classList.remove('active'));

            // Mostrar la vista objetivo y marcar el botón como activo
            document.getElementById(targetId).classList.add('active');
            button.classList.add('active');
        });
    });

    // ... (Aquí mantienes la lógica del botón SOS y de la ambulancia que ya tenías) ...
});