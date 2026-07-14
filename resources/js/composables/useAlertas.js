/**
 * useAlertas.js
 *
 * Composable centralizado para todas las alertas del sistema.
 * Todas las vistas importan de aquí para mantener el mismo estilo.
 *
 * Uso:
 *   import { useAlertas } from '../composables/useAlertas'
 *   const { alertaExito, alertaError, alertaConfirmar } = useAlertas()
 */

import Swal from 'sweetalert2'

export function useAlertas() {

    // ── Éxito ────────────────────────────────────────────────────
    const alertaExito = (mensaje) => {
        Swal.fire({
            icon:              'success',
            title:             '¡Listo!',
            text:              mensaje,
            timer:             2000,
            showConfirmButton: false,
            timerProgressBar:  true,
        })
    }

    // ── Error ────────────────────────────────────────────────────
    const alertaError = (mensaje) => {
        Swal.fire({
            icon:  'error',
            title: 'Ocurrió un error',
            text:  mensaje,
        })
    }

    // ── Confirmación (devuelve true/false) ───────────────────────
    const alertaConfirmar = async (texto, textoBoton = 'Sí, continuar') => {
        const resultado = await Swal.fire({
            icon:               'warning',
            title:              '¿Estás seguro?',
            text:               texto,
            showCancelButton:   true,
            confirmButtonText:  textoBoton,
            cancelButtonText:   'Cancelar',
            confirmButtonColor: '#ef4444', // rojo Tailwind
            cancelButtonColor:  '#6b7280', // gris Tailwind
        })
        return resultado.isConfirmed
    }

    // ── Advertencia simple (sin acción) ─────────────────────────
    const alertaAdvertencia = (mensaje) => {
        Swal.fire({
            icon:  'warning',
            title: 'Atención',
            text:  mensaje,
        })
    }

    return { alertaExito, alertaError, alertaConfirmar, alertaAdvertencia }
}
