/**
 * ============================================
 * Validaciones REGEX — Chile
 * ============================================
 * Validación de RUT, email y teléfono chilenos.
 * Se usa tanto en checkout como en registro de clientes.
 */

// ========== VALIDACIÓN DE RUT CHILENO ==========

/**
 * Limpiar un RUT quitando puntos y espacios, dejando solo dígitos y guión-K
 * @param {string} rut
 * @returns {string} RUT limpio (ej: "12345678-9")
 */
function limpiarRut(rut) {
    if (!rut) return '';
    return rut.replace(/\./g, '').replace(/\s/g, '').toUpperCase();
}

/**
 * Validar formato y dígito verificador de un RUT chileno
 * Acepta formatos: 12.345.678-9, 12345678-9, 1234567-8
 * @param {string} rut — RUT a validar
 * @returns {{valido: boolean, mensaje: string}}
 */
function validarRut(rut) {
    if (!rut || rut.trim() === '') {
        return { valido: false, mensaje: 'El RUT es obligatorio.' };
    }

    const rutLimpio = limpiarRut(rut);

    // Regex: 7 u 8 dígitos, guión, dígito verificador (0-9 o K)
    const regex = /^\d{7,8}-[\dK]$/;
    if (!regex.test(rutLimpio)) {
        return { valido: false, mensaje: 'Formato de RUT inválido. Ej: 12345678-9' };
    }

    // Separar cuerpo y dígito verificador
    const partes = rutLimpio.split('-');
    const cuerpo = partes[0];
    const dvIngresado = partes[1];

    // Calcular dígito verificador con algoritmo módulo 11
    const dvCalculado = calcularDigitoVerificador(cuerpo);

    if (dvIngresado !== dvCalculado) {
        return { valido: false, mensaje: 'El RUT ingresado no es válido (dígito verificador incorrecto).' };
    }

    return { valido: true, mensaje: '' };
}

/**
 * Calcular el dígito verificador de un RUT chileno
 * Algoritmo: Módulo 11
 * @param {string} cuerpo — Parte numérica del RUT (sin DV)
 * @returns {string} Dígito verificador calculado ("0"-"9" o "K")
 */
function calcularDigitoVerificador(cuerpo) {
    let suma = 0;
    let multiplicador = 2;

    // Recorrer dígitos de derecha a izquierda
    for (let i = cuerpo.length - 1; i >= 0; i--) {
        suma += parseInt(cuerpo.charAt(i)) * multiplicador;
        multiplicador = multiplicador === 7 ? 2 : multiplicador + 1;
    }

    const resto = suma % 11;
    const dv = 11 - resto;

    if (dv === 11) return '0';
    if (dv === 10) return 'K';
    return dv.toString();
}

/**
 * Formatear un RUT mientras se escribe (auto-formato)
 * Entrada: "123456789" → Salida: "12.345.678-9"
 * @param {string} valor — Valor crudo del input
 * @returns {string} RUT formateado
 */
function formatearRut(valor) {
    // Quitar todo excepto dígitos y K
    let limpio = valor.replace(/[^0-9kK]/g, '').toUpperCase();
    if (limpio.length === 0) return '';

    // Separar dígito verificador (último carácter)
    let dv = limpio.slice(-1);
    let cuerpo = limpio.slice(0, -1);

    // Si solo tiene 1 carácter, no formatear aún
    if (cuerpo.length === 0) return limpio;

    // Agregar puntos al cuerpo
    let cuerpoFormateado = '';
    let count = 0;
    for (let i = cuerpo.length - 1; i >= 0; i--) {
        cuerpoFormateado = cuerpo.charAt(i) + cuerpoFormateado;
        count++;
        if (count % 3 === 0 && i > 0) {
            cuerpoFormateado = '.' + cuerpoFormateado;
        }
    }

    return cuerpoFormateado + '-' + dv;
}


// ========== VALIDACIÓN DE EMAIL ==========

/**
 * Validar formato de email
 * @param {string} email
 * @returns {{valido: boolean, mensaje: string}}
 */
function validarEmail(email) {
    if (!email || email.trim() === '') {
        // Email es opcional en algunos contextos
        return { valido: true, mensaje: '' };
    }

    const regex = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;
    if (!regex.test(email.trim())) {
        return { valido: false, mensaje: 'Formato de email inválido. Ej: correo@ejemplo.com' };
    }

    return { valido: true, mensaje: '' };
}

/**
 * Validar formato de email (campo obligatorio)
 * @param {string} email
 * @returns {{valido: boolean, mensaje: string}}
 */
function validarEmailObligatorio(email) {
    if (!email || email.trim() === '') {
        return { valido: false, mensaje: 'El email es obligatorio.' };
    }
    return validarEmail(email);
}


// ========== VALIDACIÓN DE TELÉFONO CHILE ==========

/**
 * Validar formato de teléfono chileno
 * Formatos aceptados: +56912345678, +56 9 1234 5678, +569 12345678, 912345678, 9 1234 5678
 * @param {string} telefono
 * @returns {{valido: boolean, mensaje: string}}
 */
function validarTelefono(telefono) {
    if (!telefono || telefono.trim() === '') {
        // Teléfono es opcional
        return { valido: true, mensaje: '' };
    }

    // Limpiar: quitar espacios extras
    const limpio = telefono.trim();

    // Regex: acepta +56 9 XXXX XXXX con variaciones de espacios
    // También acepta solo 9XXXXXXXX
    const regex = /^(\+56\s?)?9\s?\d{4}\s?\d{4}$/;
    if (!regex.test(limpio)) {
        return { valido: false, mensaje: 'Formato inválido. Ej: +56 9 1234 5678' };
    }

    return { valido: true, mensaje: '' };
}

/**
 * Formatear teléfono chileno mientras se escribe
 * @param {string} valor
 * @returns {string} Teléfono formateado
 */
function formatearTelefono(valor) {
    // Quitar todo excepto dígitos y +
    let limpio = valor.replace(/[^\d+]/g, '');

    // Si empieza con +56, formatear como +56 9 XXXX XXXX
    if (limpio.startsWith('+56')) {
        let numeros = limpio.substring(3); // quitar +56
        if (numeros.length > 0 && numeros[0] === '9') {
            let resultado = '+56 ' + numeros[0];
            if (numeros.length > 1) resultado += ' ' + numeros.substring(1, 5);
            if (numeros.length > 5) resultado += ' ' + numeros.substring(5, 9);
            return resultado;
        }
        return '+56' + numeros;
    }

    // Si empieza con 9, formatear como +56 9 XXXX XXXX
    if (limpio.startsWith('9') && limpio.length > 1) {
        let resultado = '+56 ' + limpio[0];
        if (limpio.length > 1) resultado += ' ' + limpio.substring(1, 5);
        if (limpio.length > 5) resultado += ' ' + limpio.substring(5, 9);
        return resultado;
    }

    return valor;
}


// ========== UI: MOSTRAR/OCULTAR ERRORES INLINE ==========

/**
 * Mostrar mensaje de error debajo de un campo
 * @param {string} inputId — ID del input/select
 * @param {string} mensaje — Mensaje de error (vacío para limpiar)
 */
function mostrarErrorCampo(inputId, mensaje) {
    const input = document.getElementById(inputId);
    const errorSpan = document.getElementById(inputId + 'Error');

    if (input) {
        if (mensaje) {
            input.classList.add('input-error');
        } else {
            input.classList.remove('input-error');
        }
    }

    if (errorSpan) {
        errorSpan.textContent = mensaje;
        errorSpan.style.display = mensaje ? 'block' : 'none';
    }
}

/**
 * Limpiar todos los errores de un formulario
 * @param {string[]} ids — Array de IDs de inputs
 */
function limpiarErrores(ids) {
    ids.forEach(id => mostrarErrorCampo(id, ''));
}


// ========== AUTO-FORMATO EN INPUTS ==========

/**
 * Configurar auto-formato de RUT en un input
 * @param {string} inputId — ID del input de RUT
 */
function configurarAutoFormatoRut(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;

    input.addEventListener('input', function () {
        const cursorPos = this.selectionStart;
        const valorAnterior = this.value;
        const valorFormateado = formatearRut(this.value);
        this.value = valorFormateado;

        // Ajustar posición del cursor
        const diff = valorFormateado.length - valorAnterior.length;
        this.setSelectionRange(cursorPos + diff, cursorPos + diff);
    });

    // Validar al perder el foco
    input.addEventListener('blur', function () {
        const resultado = validarRut(this.value);
        mostrarErrorCampo(inputId, resultado.mensaje);
    });
}

/**
 * Configurar auto-formato de teléfono en un input
 * @param {string} inputId — ID del input de teléfono
 */
function configurarAutoFormatoTelefono(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;

    input.addEventListener('blur', function () {
        if (this.value.trim()) {
            const resultado = validarTelefono(this.value);
            mostrarErrorCampo(inputId, resultado.mensaje);
        } else {
            mostrarErrorCampo(inputId, '');
        }
    });
}

/**
 * Configurar validación de email en un input
 * @param {string} inputId — ID del input de email
 * @param {boolean} obligatorio — Si el email es obligatorio
 */
function configurarValidacionEmail(inputId, obligatorio = false) {
    const input = document.getElementById(inputId);
    if (!input) return;

    input.addEventListener('blur', function () {
        const fn = obligatorio ? validarEmailObligatorio : validarEmail;
        const resultado = fn(this.value);
        mostrarErrorCampo(inputId, resultado.mensaje);
    });
}
