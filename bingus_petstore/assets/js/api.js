/**
 * ============================================
 * API Client — Fetch Wrapper para Bingus Petstore
 * ============================================
 * Todas las vistas usan este módulo para comunicarse con la API.
 * Centraliza headers, errores, y la URL base.
 */

const Api = {
    // Ajusta si tu carpeta tiene otro nombre en htdocs
    BASE: '/bingus_petstore/api',

    /**
     * Petición GET
     */
    async get(endpoint) {
        try {
            const res = await fetch(this.BASE + endpoint, {
                method: 'GET',
                credentials: 'same-origin'
            });
            return await res.json();
        } catch (err) {
            console.error('API GET error:', err);
            return { success: false, message: 'Error de conexión con el servidor.' };
        }
    },

    /**
     * Petición POST con JSON
     */
    async post(endpoint, data) {
        try {
            const res = await fetch(this.BASE + endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            return await res.json();
        } catch (err) {
            console.error('API POST error:', err);
            return { success: false, message: 'Error de conexión con el servidor.' };
        }
    },

    /**
     * Petición POST con FormData (para subir archivos)
     */
    async postForm(endpoint, formData) {
        try {
            const res = await fetch(this.BASE + endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData  // No headers → browser pone multipart automáticamente
            });
            return await res.json();
        } catch (err) {
            console.error('API POST Form error:', err);
            return { success: false, message: 'Error de conexión con el servidor.' };
        }
    },

    /**
     * Petición PUT con JSON
     */
    async put(endpoint, data) {
        try {
            const res = await fetch(this.BASE + endpoint, {
                method: 'PUT',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            return await res.json();
        } catch (err) {
            console.error('API PUT error:', err);
            return { success: false, message: 'Error de conexión con el servidor.' };
        }
    },

    /**
     * Petición DELETE
     */
    async delete(endpoint) {
        try {
            const res = await fetch(this.BASE + endpoint, {
                method: 'DELETE',
                credentials: 'same-origin'
            });
            return await res.json();
        } catch (err) {
            console.error('API DELETE error:', err);
            return { success: false, message: 'Error de conexión con el servidor.' };
        }
    },

    /**
     * Helper: mostrar alerta SweetAlert2
     */
    alert(tipo, titulo, texto) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: tipo, title: titulo, text: texto, confirmButtonColor: '#667eea' });
        } else {
            alert(titulo + ': ' + texto);
        }
    },

    /**
     * Helper: confirmar acción peligrosa
     */
    async confirm(titulo, texto) {
        if (typeof Swal !== 'undefined') {
            const result = await Swal.fire({
                title: titulo, text: texto, icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c', cancelButtonColor: '#95a5a6',
                confirmButtonText: 'Sí, confirmar', cancelButtonText: 'Cancelar'
            });
            return result.isConfirmed;
        }
        return confirm(titulo + '\n' + texto);
    },

    /**
     * Helper: cerrar sesión
     */
    async logout() {
        await this.post('/auth/logout', {});
        window.location.href = '/bingus_petstore/views/auth/login.php';
    }
};
