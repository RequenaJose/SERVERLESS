// --- Navegación SPA ---
function switchView(viewId) {
    document.querySelectorAll('.view').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.nav-links button').forEach(el => el.classList.remove('active'));
    
    document.getElementById(viewId).classList.add('active');
    document.getElementById(`nav-${viewId}`).classList.add('active');
}
// ==========================================
// 1. BASE DE DATOS SIMULADA (UBIGEO)
// ==========================================
const mockCasosDb = {
    "120101": [ // Huancayo - Centro
        { id: "TICKET-84920", ambulancia: "MÓVIL A-04", tipo: "Infarto / Dolor Torácico", ubicacion: "Av. Giráldez 230", estado: "CRÍTICO", destino: "Hospital Regional Docente", fc: 112, spo2: 89, vel: 65, eta: "08 min" },
        { id: "TICKET-84921", ambulancia: "MÓVIL B-12", tipo: "Accidente Vehicular", ubicacion: "Calle Real 1050, Esq. Ayacucho", estado: "ESTABLE", destino: "Clínica Ortega", fc: 85, spo2: 96, vel: 40, eta: "12 min" },
        { id: "TICKET-84922", ambulancia: "MÓVIL A-09", tipo: "Asfixia / Resp. Grave", ubicacion: "Plaza Constitución", estado: "CRÍTICO", destino: "Hospital Carrión", fc: 130, spo2: 85, vel: 70, eta: "04 min" }
    ],
    "120114": [ // El Tambo
        { id: "TICKET-99234", ambulancia: "MÓVIL C-01", tipo: "Caída Grave (3er Piso)", ubicacion: "Av. Mariscal Castilla 2000", estado: "ESTABLE", destino: "EsSalud Ramiro Prialé", fc: 90, spo2: 95, vel: 55, eta: "15 min" },
        { id: "TICKET-99235", ambulancia: "MÓVIL C-03", tipo: "Quemadura 2do Grado", ubicacion: "Jr. Julio Sumar 450", estado: "ESTABLE", destino: "Hospital Regional Docente", fc: 98, spo2: 98, vel: 45, eta: "10 min" }
    ],
    "120107": [ // Chilca
        { id: "TICKET-77112", ambulancia: "MÓVIL D-05", tipo: "Intoxicación Severa", ubicacion: "Av. 9 de Diciembre 800", estado: "CRÍTICO", destino: "Hospital Carrión", fc: 140, spo2: 90, vel: 80, eta: "06 min" }
    ]
};

// ==========================================
// 2. VARIABLES GLOBALES
// ==========================================
let ticketActivo = null;
let casoActivoData = null;
let isConnected = true;
let simuladorMqttInterval = null;

// ==========================================
// 3. INICIALIZACIÓN PRINCIPAL
// ==========================================
document.addEventListener('DOMContentLoaded', () => {

    // --- NAVEGACIÓN SPA ---
    const navButtons = document.querySelectorAll('.nav-btn'); 
    const views = document.querySelectorAll('.view');

    navButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetId = button.getAttribute('data-target');

            views.forEach(view => view.classList.remove('active'));
            navButtons.forEach(btn => btn.classList.remove('active'));

            document.getElementById(targetId).classList.add('active');
            button.classList.add('active');
        });
    });

    // --- HU 1: ALERTA SOS ---
    const btnSOS = document.getElementById('btnActivarSOS');
    if(btnSOS) {
        btnSOS.addEventListener('click', async () => {
            const originalText = btnSOS.innerText;
            btnSOS.innerText = "⏳ ENVIANDO REGISTRO...";
            btnSOS.style.backgroundColor = "#B71C1C";
            btnSOS.disabled = true;

            const descripcion = document.getElementById('txtDescripcion') ? document.getElementById('txtDescripcion').value : '';

            // Bloque adaptado a tu código original (asumiendo que mantienes el fetch real o simulado)
            const response = await fetch('src/adapters/in/controllers/SOSController.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    descripcion: descripcion 
                })
            });
        });
    }

    // --- HU 2: MONITOREO DE AMBULANCIAS (FICHAS) ---
    const selDistrito = document.getElementById('selDistrito');
    const btnRedirigir = document.getElementById('btnRedirigir');

    if (selDistrito) {
        selDistrito.addEventListener('change', cargarFichasPorDistrito);
        cargarFichasPorDistrito(); // Cargar inicial
        
        // Iniciar el simulador "MQTT" de signos vitales (para el caso activo)
        iniciarSimuladorMQTT();
    }

    if (btnRedirigir) {
        btnRedirigir.addEventListener('click', manejarRedireccion);
    }

    // --- HU 3: DERIVACIÓN INTELIGENTE ---
    const btnConsultarCamas = document.getElementById('btnConsultarCamas');
    if (btnConsultarCamas) {
        btnConsultarCamas.addEventListener('click', () => {
            const status = document.getElementById('camasStatus');
            const table = document.getElementById('tablaHospitales');
            const tbody = document.getElementById('listaHospitales');
            
            if(status) {
                status.style.display = 'block';
                status.className = 'status-panel status-warning mt-15';
                status.innerText = "Consultando ESSALUD, MINSA y Privados...";
            }
            if(table) table.classList.add('hidden');

            setTimeout(() => {
                if(status) {
                    status.className = 'status-panel status-success mt-15';
                    status.innerText = "Resultados consolidados obtenidos en 1.1s";
                }
                if(table) table.classList.remove('hidden');
                
                if(tbody) {
                    tbody.innerHTML = `
                        <tr><td>Hosp. Carrión</td><td>Huancayo</td><td>4</td><td>12</td><td>2.5 km</td><td style="color:green">Tiempo Real</td></tr>
                        <tr><td>Clínica Ortega</td><td>Huancayo</td><td>1</td><td>5</td><td>3.1 km</td><td style="color:orange">En Caché (Circuit Breaker)</td></tr>
                        <tr><td>Hosp. Félix Mayorca</td><td>Tarma</td><td>0</td><td>2</td><td>45.0 km</td><td style="color:green">Tiempo Real</td></tr>
                    `;
                }
            }, 1100);
        });
    }

    // --- HU 4: RECETA ELECTRÓNICA ---
    const recetaForm = document.getElementById('recetaForm');
    if (recetaForm) {
        recetaForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const btn = document.getElementById('btnEmitirReceta');
            const status = document.getElementById('recetaStatus');
            
            if(btn) {
                btn.disabled = true;
                btn.innerText = 'Procesando...';
            }
            
            setTimeout(() => {
                const token = Math.random().toString(36).substring(2, 10).toUpperCase();
                if(status) {
                    status.style.display = 'block';
                    status.className = 'status-panel status-success mt-15';
                    status.innerHTML = `
                        <p>✅ <b>Colegiatura:</b> Validada en RENIEC/CMP.</p>
                        <p>✅ <b>Firma y Sincronización:</b> Documento firmado y enviado a B2B Farmacias.</p>
                        <p>📱 <b>App Paciente:</b> Receta enviada. Token: <strong>${token}</strong></p>`;
                }
                if(btn) btn.innerText = '✅ Receta Emitida Exitosamente';
            }, 1500);
        });
    }

});

// ==========================================
// 4. FUNCIONES DE HU 2 (MONITOREO)
// ==========================================

function cargarFichasPorDistrito() {
    const selDistrito = document.getElementById('selDistrito');
    const contenedor = document.getElementById('contenedorFichas');
    const detalleCaso = document.getElementById('detalleCaso');
    
    if (!selDistrito || !contenedor) return;

    const distritoId = selDistrito.value;
    const casos = mockCasosDb[distritoId] || [];
    
    contenedor.innerHTML = ''; 
    if(detalleCaso) detalleCaso.style.display = 'none'; 
    ticketActivo = null;
    casoActivoData = null;

    if (casos.length === 0) {
        contenedor.innerHTML = '<span style="font-size: 13px; color: #777; padding: 10px;">No hay emergencias activas en este distrito.</span>';
        return;
    }

    casos.forEach(caso => {
        const div = document.createElement('div');
        div.className = 'ficha-caso';
        div.dataset.id = caso.id;
        div.innerHTML = `
            <div class="ficha-titulo">🚑 ${caso.ambulancia}</div>
            <div class="ficha-desc"><strong>${caso.id}</strong> - ${caso.tipo}</div>
            <div class="ficha-desc">📍 ${caso.ubicacion}</div>
            <div class="ficha-estado">Estado: ${caso.estado}</div>
        `;
        
        div.addEventListener('click', () => seleccionarCaso(caso, div));
        contenedor.appendChild(div);
    });
}

function seleccionarCaso(caso, elementoFicha) {
    document.querySelectorAll('.ficha-caso').forEach(el => el.classList.remove('activa'));
    elementoFicha.classList.add('activa');
    
    ticketActivo = caso.id;
    casoActivoData = caso; // Guardar referencia para el simulador MQTT

    const detalleCaso = document.getElementById('detalleCaso');
    if(detalleCaso) detalleCaso.style.display = 'block';

    actualizarDatosVistasHTML(caso);
}

function actualizarDatosVistasHTML(caso) {
    const setElem = (id, valor) => {
        const el = document.getElementById(id);
        if(el) el.innerText = valor;
    };

    setElem('mon-ticket', caso.id);
    setElem('mon-ubicacion-emerg', caso.ubicacion);
    setElem('mon-desc', `Paciente reportado por ${caso.tipo}.`);
    setElem('mon-unidad', caso.ambulancia);
    setElem('mon-vel', caso.vel);
    setElem('mon-eta', caso.eta);
    setElem('mon-destino', caso.destino);
    
    // Signos vitales base (serán modificados por el simulador)
    setElem('mon-fc', caso.fc);
    setElem('mon-spo2', caso.spo2);

    const selNuevoDestino = document.getElementById('selNuevoDestino');
    if(selNuevoDestino) selNuevoDestino.value = caso.destino;
}

function manejarRedireccion() {
    if(!ticketActivo) return;
    
    const selNuevoDestino = document.getElementById('selNuevoDestino');
    const monDestino = document.getElementById('mon-destino');
    const btn = document.getElementById('btnRedirigir');
    
    if(!selNuevoDestino || !monDestino || !btn) return;

    const nuevoDestino = selNuevoDestino.value;
    const destinoActual = monDestino.innerText;
    
    if(nuevoDestino === destinoActual) {
        alert("La ambulancia ya se dirige a este destino.");
        return;
    }

    if (confirm(`¿Redirigir el caso ${ticketActivo} hacia ${nuevoDestino}?`)) {
        btn.innerText = "⏳ Enviando...";
        btn.disabled = true;

        setTimeout(() => { 
            monDestino.innerText = nuevoDestino;
            
            const selDistrito = document.getElementById('selDistrito');
            const distritoId = selDistrito ? selDistrito.value : null;
            
            if (distritoId) {
                const casoIndex = mockCasosDb[distritoId].findIndex(c => c.id === ticketActivo);
                if(casoIndex !== -1) mockCasosDb[distritoId][casoIndex].destino = nuevoDestino;
                if(casoActivoData) casoActivoData.destino = nuevoDestino;
            }

            alert(`✅ Ruta recalculada. La ambulancia ha sido notificada para ir a: ${nuevoDestino}`);
            btn.innerText = "🔄 Redirigir";
            btn.disabled = false;
        }, 1000);
    }
}

// Simula la llegada de datos MQTT modificando sutilmente los signos vitales del caso activo
function iniciarSimuladorMQTT() {
    if (simuladorMqttInterval) clearInterval(simuladorMqttInterval);

    simuladorMqttInterval = setInterval(() => {
        if (!isConnected || !ticketActivo || !casoActivoData) return;

        // Oscilación leve para FC (± 2 BPM)
        let deltaFC = Math.floor(Math.random() * 5) - 2; 
        let nuevoFC = casoActivoData.fc + deltaFC;
        
        // Oscilación leve para SpO2 (± 1%)
        let deltaSpo2 = Math.floor(Math.random() * 3) - 1;
        let nuevoSpo2 = casoActivoData.spo2 + deltaSpo2;
        if(nuevoSpo2 > 100) nuevoSpo2 = 100;

        const fcEl = document.getElementById('mon-fc');
        const spoEl = document.getElementById('mon-spo2');
        const ecg = document.getElementById('mon-ecg');

        if(fcEl) fcEl.innerText = nuevoFC;
        if(spoEl) spoEl.innerText = nuevoSpo2;
        
        if(ecg) {
            ecg.innerText = ecg.innerText.substring(2) + '/\\';
        }
        
    }, 2000);
}