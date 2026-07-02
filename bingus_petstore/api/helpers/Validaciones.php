<?php
/**
 * ============================================
 * Helper: Validaciones REGEX — Chile
 * ============================================
 * Validación backend de RUT, email, teléfono y dirección.
 * Espejo de las validaciones JS en el frontend.
 */

class Validaciones {

    // ========== REGIONES Y COMUNAS PERMITIDAS ==========

    /**
     * Regiones y comunas válidas para entrega
     */
    private static $regionesPermitidas = [
        'IV' => [
            'nombre' => 'Región de Coquimbo',
            'comunas' => [
                'La Serena', 'Coquimbo', 'Andacollo', 'La Higuera', 'Paihuano',
                'Vicuña', 'Illapel', 'Canela', 'Los Vilos', 'Salamanca',
                'Ovalle', 'Combarbalá', 'Monte Patria', 'Punitaqui', 'Río Hurtado'
            ]
        ],
        'RM' => [
            'nombre' => 'Región Metropolitana',
            'comunas' => [
                'Santiago', 'Cerrillos', 'Cerro Navia', 'Conchalí', 'El Bosque',
                'Estación Central', 'Huechuraba', 'Independencia', 'La Cisterna',
                'La Florida', 'La Granja', 'La Pintana', 'La Reina', 'Las Condes',
                'Lo Barnechea', 'Lo Espejo', 'Lo Prado', 'Macul', 'Maipú',
                'Ñuñoa', 'Pedro Aguirre Cerda', 'Peñalolén', 'Providencia',
                'Pudahuel', 'Quilicura', 'Quinta Normal', 'Recoleta', 'Renca',
                'San Joaquín', 'San Miguel', 'San Ramón', 'Vitacura',
                'Puente Alto', 'Pirque', 'San José de Maipo',
                'Colina', 'Lampa', 'Tiltil',
                'San Bernardo', 'Buin', 'Calera de Tango', 'Paine',
                'Melipilla', 'Alhué', 'Curacaví', 'María Pinto', 'San Pedro',
                'Talagante', 'El Monte', 'Isla de Maipo', 'Padre Hurtado', 'Peñaflor'
            ]
        ]
    ];

    // ========== VALIDACIÓN DE RUT CHILENO ==========

    /**
     * Validar formato y dígito verificador de un RUT chileno
     * @param string $rut — RUT a validar (con o sin puntos)
     * @return array ['valido' => bool, 'mensaje' => string]
     */
    public static function validarRut($rut) {
        if (empty(trim($rut))) {
            return ['valido' => false, 'mensaje' => 'El RUT es obligatorio.'];
        }

        // Limpiar: quitar puntos, espacios, dejar solo dígitos y guión-K
        $rutLimpio = strtoupper(str_replace(['.', ' '], '', trim($rut)));

        // Regex: 7 u 8 dígitos, guión, dígito verificador (0-9 o K)
        if (!preg_match('/^\d{7,8}-[\dK]$/', $rutLimpio)) {
            return ['valido' => false, 'mensaje' => 'Formato de RUT inválido. Ej: 12345678-9'];
        }

        // Separar cuerpo y dígito verificador
        $partes = explode('-', $rutLimpio);
        $cuerpo = $partes[0];
        $dvIngresado = $partes[1];

        // Calcular dígito verificador
        $dvCalculado = self::calcularDigitoVerificador($cuerpo);

        if ($dvIngresado !== $dvCalculado) {
            return ['valido' => false, 'mensaje' => 'El RUT ingresado no es válido (dígito verificador incorrecto).'];
        }

        return ['valido' => true, 'mensaje' => ''];
    }

    /**
     * Calcular el dígito verificador de un RUT chileno (módulo 11)
     * @param string $cuerpo — Parte numérica del RUT
     * @return string — Dígito verificador ("0"-"9" o "K")
     */
    private static function calcularDigitoVerificador($cuerpo) {
        $suma = 0;
        $multiplicador = 2;

        for ($i = strlen($cuerpo) - 1; $i >= 0; $i--) {
            $suma += intval($cuerpo[$i]) * $multiplicador;
            $multiplicador = $multiplicador === 7 ? 2 : $multiplicador + 1;
        }

        $resto = $suma % 11;
        $dv = 11 - $resto;

        if ($dv === 11) return '0';
        if ($dv === 10) return 'K';
        return (string)$dv;
    }

    // ========== VALIDACIÓN DE EMAIL ==========

    /**
     * Validar formato de email
     * @param string $email
     * @param bool $obligatorio — Si el campo es obligatorio
     * @return array ['valido' => bool, 'mensaje' => string]
     */
    public static function validarEmail($email, $obligatorio = false) {
        $email = trim($email);

        if (empty($email)) {
            if ($obligatorio) {
                return ['valido' => false, 'mensaje' => 'El email es obligatorio.'];
            }
            return ['valido' => true, 'mensaje' => ''];
        }

        // Regex + filter_var para doble validación
        $regex = '/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/';
        if (!preg_match($regex, $email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['valido' => false, 'mensaje' => 'Formato de email inválido. Ej: correo@ejemplo.com'];
        }

        return ['valido' => true, 'mensaje' => ''];
    }

    // ========== VALIDACIÓN DE TELÉFONO CHILE ==========

    /**
     * Validar formato de teléfono chileno
     * Formatos aceptados: +56 9 1234 5678, +56912345678, 912345678
     * @param string $telefono
     * @return array ['valido' => bool, 'mensaje' => string]
     */
    public static function validarTelefono($telefono) {
        $telefono = trim($telefono);

        if (empty($telefono)) {
            // Teléfono es opcional
            return ['valido' => true, 'mensaje' => ''];
        }

        // Regex: acepta +56 9 XXXX XXXX con variaciones de espacios
        $regex = '/^(\+56\s?)?9\s?\d{4}\s?\d{4}$/';
        if (!preg_match($regex, $telefono)) {
            return ['valido' => false, 'mensaje' => 'Formato de teléfono inválido. Ej: +56 9 1234 5678'];
        }

        return ['valido' => true, 'mensaje' => ''];
    }

    // ========== VALIDACIÓN DE DIRECCIÓN ==========

    /**
     * Validar que la región y comuna sean válidas
     * @param string $region — Código de región ("IV" o "RM")
     * @param string $comuna — Nombre de la comuna
     * @return array ['valido' => bool, 'mensaje' => string]
     */
    public static function validarDireccion($region, $comuna) {
        if (empty($region)) {
            return ['valido' => false, 'mensaje' => 'Debe seleccionar una región.'];
        }

        if (!isset(self::$regionesPermitidas[$region])) {
            return ['valido' => false, 'mensaje' => 'Región no válida. Solo se permite entrega en la IV Región y Región Metropolitana.'];
        }

        if (empty($comuna)) {
            return ['valido' => false, 'mensaje' => 'Debe seleccionar una comuna.'];
        }

        if (!in_array($comuna, self::$regionesPermitidas[$region]['comunas'])) {
            return ['valido' => false, 'mensaje' => 'Comuna no válida para la región seleccionada.'];
        }

        return ['valido' => true, 'mensaje' => ''];
    }

    /**
     * Obtener el nombre completo de una región por código
     * @param string $codigo — "IV" o "RM"
     * @return string|null
     */
    public static function getNombreRegion($codigo) {
        return self::$regionesPermitidas[$codigo]['nombre'] ?? null;
    }

    // ========== VALIDACIÓN MASIVA ==========

    /**
     * Validar todos los campos de un cliente de una vez
     * @param array $datos — ['rut' => ..., 'email' => ..., 'telefono' => ...]
     * @param bool $emailObligatorio
     * @return array ['valido' => bool, 'errores' => ['campo' => 'mensaje']]
     */
    public static function validarDatosCliente($datos, $emailObligatorio = false) {
        $errores = [];

        // Validar RUT
        $resultadoRut = self::validarRut($datos['rut'] ?? '');
        if (!$resultadoRut['valido']) {
            $errores['rut'] = $resultadoRut['mensaje'];
        }

        // Validar Email
        $resultadoEmail = self::validarEmail($datos['email'] ?? '', $emailObligatorio);
        if (!$resultadoEmail['valido']) {
            $errores['email'] = $resultadoEmail['mensaje'];
        }

        // Validar Teléfono
        $resultadoTel = self::validarTelefono($datos['telefono'] ?? '');
        if (!$resultadoTel['valido']) {
            $errores['telefono'] = $resultadoTel['mensaje'];
        }

        return [
            'valido' => empty($errores),
            'errores' => $errores
        ];
    }
}
?>
