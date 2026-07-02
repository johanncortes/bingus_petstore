/**
 * ============================================
 * Datos de Regiones y Comunas — Chile
 * ============================================
 * Restringido a: IV Región de Coquimbo y Región Metropolitana.
 * Usado en checkout y registro de clientes.
 */

const REGIONES_PERMITIDAS = {
    "IV": {
        nombre: "Región de Coquimbo",
        comunas: [
            "La Serena",
            "Coquimbo",
            "Andacollo",
            "La Higuera",
            "Paihuano",
            "Vicuña",
            "Illapel",
            "Canela",
            "Los Vilos",
            "Salamanca",
            "Ovalle",
            "Combarbalá",
            "Monte Patria",
            "Punitaqui",
            "Río Hurtado"
        ]
    },
    "RM": {
        nombre: "Región Metropolitana",
        comunas: [
            "Santiago",
            "Cerrillos",
            "Cerro Navia",
            "Conchalí",
            "El Bosque",
            "Estación Central",
            "Huechuraba",
            "Independencia",
            "La Cisterna",
            "La Florida",
            "La Granja",
            "La Pintana",
            "La Reina",
            "Las Condes",
            "Lo Barnechea",
            "Lo Espejo",
            "Lo Prado",
            "Macul",
            "Maipú",
            "Ñuñoa",
            "Pedro Aguirre Cerda",
            "Peñalolén",
            "Providencia",
            "Pudahuel",
            "Quilicura",
            "Quinta Normal",
            "Recoleta",
            "Renca",
            "San Joaquín",
            "San Miguel",
            "San Ramón",
            "Vitacura",
            "Puente Alto",
            "Pirque",
            "San José de Maipo",
            "Colina",
            "Lampa",
            "Tiltil",
            "San Bernardo",
            "Buin",
            "Calera de Tango",
            "Paine",
            "Melipilla",
            "Alhué",
            "Curacaví",
            "María Pinto",
            "San Pedro",
            "Talagante",
            "El Monte",
            "Isla de Maipo",
            "Padre Hurtado",
            "Peñaflor"
        ]
    }
};

/**
 * Poblar un <select> de regiones
 * @param {string} selectId — ID del <select> de región
 * @param {string} comunaSelectId — ID del <select> de comuna (se actualiza al cambiar región)
 */
function poblarSelectRegion(selectId, comunaSelectId) {
    const sel = document.getElementById(selectId);
    if (!sel) return;

    sel.innerHTML = '<option value="">-- Selecciona una región --</option>';
    for (const [codigo, datos] of Object.entries(REGIONES_PERMITIDAS)) {
        const opt = document.createElement('option');
        opt.value = codigo;
        opt.textContent = datos.nombre;
        sel.appendChild(opt);
    }

    // Evento: al cambiar la región, poblar comunas
    sel.addEventListener('change', function () {
        poblarSelectComuna(comunaSelectId, this.value);
    });
}

/**
 * Poblar un <select> de comunas según la región seleccionada
 * @param {string} selectId — ID del <select> de comuna
 * @param {string} codigoRegion — Código de región ("IV" o "RM")
 */
function poblarSelectComuna(selectId, codigoRegion) {
    const sel = document.getElementById(selectId);
    if (!sel) return;

    sel.innerHTML = '<option value="">-- Selecciona una comuna --</option>';

    if (!codigoRegion || !REGIONES_PERMITIDAS[codigoRegion]) return;

    const comunas = REGIONES_PERMITIDAS[codigoRegion].comunas;
    comunas.forEach(comuna => {
        const opt = document.createElement('option');
        opt.value = comuna;
        opt.textContent = comuna;
        sel.appendChild(opt);
    });
}

/**
 * Construir la dirección completa a partir de los 3 campos
 * @param {string} regionId — ID del select de región
 * @param {string} comunaId — ID del select de comuna
 * @param {string} calleId — ID del input de calle
 * @returns {string} Dirección compuesta: "Calle 123, Comuna, Región de Coquimbo"
 */
function construirDireccion(regionId, comunaId, calleId) {
    const regionSel = document.getElementById(regionId);
    const comunaSel = document.getElementById(comunaId);
    const calleInput = document.getElementById(calleId);

    const regionTexto = regionSel ? regionSel.options[regionSel.selectedIndex]?.text : '';
    const comunaTexto = comunaSel ? comunaSel.value : '';
    const calleTexto = calleInput ? calleInput.value.trim() : '';

    const partes = [];
    if (calleTexto) partes.push(calleTexto);
    if (comunaTexto) partes.push(comunaTexto);
    if (regionTexto && regionTexto !== '-- Selecciona una región --') partes.push(regionTexto);

    return partes.join(', ');
}
