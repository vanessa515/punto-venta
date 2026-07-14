<template>
    <div class="p-6">

        <!-- Encabezado -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Transferencias</h1>
                <p class="text-sm text-gray-500 mt-1">Traspasos de productos entre sucursales</p>
            </div>
            <button
                @click="abrirModal"
                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition"
            >
                <span class="text-lg leading-none">+</span> Nuevo Traspaso
            </button>
        </div>

        <!-- Búsqueda + filtro por estado -->
        <div class="flex flex-wrap items-center gap-3 mb-4">
            <input
                v-model="busqueda"
                type="text"
                placeholder="Buscar por folio..."
                class="w-56 text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
            >
            <div class="flex gap-2 ml-auto">
                <button
                    v-for="estado in estadosFiltro"
                    :key="estado.valor"
                    @click="filtroEstado = estado.valor"
                    :class="filtroEstado === estado.valor
                        ? 'bg-blue-600 text-white'
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    class="text-xs font-medium px-3 py-1.5 rounded-full transition"
                >
                    {{ estado.etiqueta }}
                </button>
            </div>
        </div>

        <!-- Tabla -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left">Folio</th>
                        <th class="px-4 py-3 text-left">Origen</th>
                        <th class="px-4 py-3 text-left">Destino</th>
                        <th class="px-4 py-3 text-left">Estado</th>
                        <th class="px-4 py-3 text-left">Solicitó</th>
                        <th class="px-4 py-3 text-left">Recibió</th>
                        <th class="px-4 py-3 text-left">Fecha</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-if="cargando">
                        <td colspan="8" class="text-center py-10 text-gray-400">Cargando...</td>
                    </tr>
                    <tr v-else-if="transferenciasFiltradas.length === 0">
                        <td colspan="8" class="text-center py-10 text-gray-400">Sin transferencias</td>
                    </tr>
                    <tr
                        v-for="t in transferenciasFiltradas"
                        :key="t.pk_transferencia"
                        class="hover:bg-gray-50 transition"
                    >
                        <td class="px-4 py-3 font-medium text-gray-700">#{{ t.pk_transferencia }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ t.fk_sucursal_origen }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ t.fk_sucursal_destino }}</td>
                        <td class="px-4 py-3">
                            <span
                                class="text-xs font-medium px-2 py-1 rounded-full"
                                :class="claseEstado(t.estado)"
                            >
                                {{ etiquetaEstado(t.estado) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ t.usuario_solicita?.name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ t.usuario_recibe?.name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                            {{ formatearFecha(t.created_at) }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <!-- Enviar: solo cuando está pendiente -->
                                <button
                                    v-if="t.estado === 'pendiente'"
                                    @click="ejecutarAccion(t.pk_transferencia, 'enviar')"
                                    class="text-xs bg-yellow-100 hover:bg-yellow-200 text-yellow-700 font-medium px-3 py-1 rounded-full transition"
                                >
                                    Enviar
                                </button>

                                <!-- Recibir: solo cuando está en tránsito -->
                                <button
                                    v-if="t.estado === 'en_transito'"
                                    @click="ejecutarAccion(t.pk_transferencia, 'recibir')"
                                    class="text-xs bg-green-100 hover:bg-green-200 text-green-700 font-medium px-3 py-1 rounded-full transition"
                                >
                                    Recibir
                                </button>

                                <!-- Cancelar: cuando aún se puede revertir -->
                                <button
                                    v-if="t.estado === 'pendiente' || t.estado === 'en_transito'"
                                    @click="ejecutarAccion(t.pk_transferencia, 'cancelar')"
                                    class="text-xs bg-red-100 hover:bg-red-200 text-red-700 font-medium px-3 py-1 rounded-full transition"
                                >
                                    Cancelar
                                </button>

                                <!-- Sin acciones disponibles -->
                                <span
                                    v-if="t.estado === 'completada' || t.estado === 'cancelada'"
                                    class="text-xs text-gray-400"
                                >
                                    —
                                </span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ── Modal: Nuevo Traspaso ── -->
        <Transition name="fade">
            <div
                v-if="mostrarModal"
                class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
                @click.self="cerrarModal"
            >
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6 max-h-[90vh] overflow-y-auto">

                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-lg font-semibold text-gray-800">Nuevo Traspaso</h2>
                        <button @click="cerrarModal" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
                    </div>

                    <!-- Sucursales -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Sucursal Origen</label>
                            <input
                                v-model="formulario.fk_sucursal_origen"
                                type="number"
                                placeholder="ID origen"
                                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Sucursal Destino</label>
                            <input
                                v-model="formulario.fk_sucursal_destino"
                                type="number"
                                placeholder="ID destino"
                                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                            >
                        </div>
                    </div>

                    <!-- Notas -->
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Notas <span class="text-gray-400 font-normal">(opcional)</span>
                        </label>
                        <textarea
                            v-model="formulario.notas"
                            rows="2"
                            placeholder="Observaciones del traspaso..."
                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none"
                        ></textarea>
                    </div>

                    <hr class="my-4">

                    <!-- Agregar productos -->
                    <p class="text-xs font-medium text-gray-600 mb-2">Productos a transferir</p>
                    <div class="flex gap-2 mb-3">
                        <input
                            v-model="productoTemp.fk_producto"
                            type="number"
                            placeholder="ID producto"
                            class="flex-1 text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                            @keyup.enter="agregarProducto"
                        >
                        <input
                            v-model="productoTemp.cantidad"
                            type="number"
                            min="0.01"
                            step="0.01"
                            placeholder="Cantidad"
                            class="w-28 text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                            @keyup.enter="agregarProducto"
                        >
                        <button
                            @click="agregarProducto"
                            class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-3 py-2 rounded-lg transition"
                        >
                            Añadir
                        </button>
                    </div>

                    <!-- Lista de productos agregados -->
                    <div v-if="formulario.detalles.length === 0" class="text-xs text-center text-gray-400 py-4 border border-dashed border-gray-200 rounded-lg mb-4">
                        Aún no has agregado productos al traspaso
                    </div>
                    <div v-else class="border border-gray-100 rounded-lg overflow-hidden mb-4">
                        <table class="min-w-full text-xs">
                            <thead class="bg-gray-50 text-gray-500">
                                <tr>
                                    <th class="px-3 py-2 text-left">Producto (ID)</th>
                                    <th class="px-3 py-2 text-right">Cantidad</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="(d, i) in formulario.detalles" :key="i" class="hover:bg-gray-50">
                                    <td class="px-3 py-2 text-gray-700">{{ d.fk_producto }}</td>
                                    <td class="px-3 py-2 text-right text-gray-700">{{ d.cantidad }}</td>
                                    <td class="px-3 py-2 text-right">
                                        <button
                                            @click="quitarProducto(i)"
                                            class="text-red-400 hover:text-red-600 font-bold"
                                        >
                                            &times;
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-2 justify-end">
                        <button
                            @click="cerrarModal"
                            class="text-sm text-gray-600 hover:text-gray-800 px-4 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition"
                        >
                            Cancelar
                        </button>
                        <button
                            @click="guardarTraspaso"
                            :disabled="guardando"
                            class="text-sm bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300 text-white font-medium px-5 py-2 rounded-lg transition"
                        >
                            {{ guardando ? 'Guardando...' : 'Guardar Traspaso' }}
                        </button>
                    </div>

                </div>
            </div>
        </Transition>

    </div>
</template>

<script>
import axios from 'axios'
import { useAlertas } from '../../composables/useAlertas'

export default {
    setup() {
        const { alertaExito, alertaError, alertaConfirmar, alertaAdvertencia } = useAlertas()
        return { alertaExito, alertaError, alertaConfirmar, alertaAdvertencia }
    },

    data() {
        return {
            transferencias: [],
            busqueda:       '',
            filtroEstado:   'todos',
            cargando:       false,
            mostrarModal:   false,
            guardando:      false,

            productoTemp: { fk_producto: '', cantidad: '' },

            formulario: {
                fk_sucursal_origen:  '',
                fk_sucursal_destino: '',
                notas:               '',
                detalles:            [],
            },

            estadosFiltro: [
                { valor: 'todos',       etiqueta: 'Todos'       },
                { valor: 'pendiente',   etiqueta: 'Pendientes'  },
                { valor: 'en_transito', etiqueta: 'En tránsito' },
                { valor: 'completada',  etiqueta: 'Completadas' },
                { valor: 'cancelada',   etiqueta: 'Canceladas'  },
            ],

            // Textos de confirmación por acción
            confirmaciones: {
                enviar:   'El stock se descontará de la sucursal origen. ¿Confirmas el envío?',
                recibir:  'El stock se sumará a la sucursal destino. ¿Confirmas la recepción?',
                cancelar: '¿Seguro que deseas cancelar este traspaso? Si ya fue enviado, el stock regresará al origen.',
            },
        }
    },

    computed: {
        transferenciasFiltradas() {
            return this.transferencias.filter(t => {
                const coincideFolio  = !this.busqueda || String(t.pk_transferencia).includes(this.busqueda)
                const coincideEstado = this.filtroEstado === 'todos' || t.estado === this.filtroEstado
                return coincideFolio && coincideEstado
            })
        },
    },

    mounted() {
        this.obtenerTransferencias()
    },

    methods: {
        // ── Obtener ──────────────────────────────────────────────
        async obtenerTransferencias() {
            this.cargando = true
            try {
                const { data } = await axios.get('/api/transferencias')
                this.transferencias = data
            } catch (error) {
                this.alertaError('No se pudieron cargar las transferencias.')
            } finally {
                this.cargando = false
            }
        },

        // ── Modal ────────────────────────────────────────────────
        abrirModal() {
            this.mostrarModal = true
        },

        cerrarModal() {
            this.mostrarModal = false
            this.formulario   = {
                fk_sucursal_origen:  '',
                fk_sucursal_destino: '',
                notas:               '',
                detalles:            [],
            }
            this.productoTemp = { fk_producto: '', cantidad: '' }
        },

        // ── Gestión de productos en el modal ─────────────────────
        agregarProducto() {
            if (!this.productoTemp.fk_producto || !this.productoTemp.cantidad || this.productoTemp.cantidad <= 0) {
                this.alertaAdvertencia('Ingresa el ID del producto y una cantidad mayor a 0.')
                return
            }
            this.formulario.detalles.push({
                fk_producto: Number(this.productoTemp.fk_producto),
                cantidad:    Number(this.productoTemp.cantidad),
            })
            this.productoTemp = { fk_producto: '', cantidad: '' }
        },

        quitarProducto(index) {
            this.formulario.detalles.splice(index, 1)
        },

        // ── Guardar traspaso ─────────────────────────────────────
        async guardarTraspaso() {
            if (!this.formulario.fk_sucursal_origen || !this.formulario.fk_sucursal_destino) {
                this.alertaAdvertencia('Debes seleccionar la sucursal de origen y destino.')
                return
            }
            if (this.formulario.detalles.length === 0) {
                this.alertaAdvertencia('Agrega al menos un producto al traspaso.')
                return
            }

            this.guardando = true
            try {
                const { data } = await axios.post('/api/transferencias', this.formulario)
                this.alertaExito(data.mensaje)
                this.cerrarModal()
                this.obtenerTransferencias()
            } catch (error) {
                const mensaje = error.response?.data?.error
                    ?? error.response?.data?.message
                    ?? 'Error al crear el traspaso.'
                this.alertaError(mensaje)
            } finally {
                this.guardando = false
            }
        },

        // ── Acciones por estado (enviar / recibir / cancelar) ────
        async ejecutarAccion(id, tipo) {
            const confirmado = await this.alertaConfirmar(
                this.confirmaciones[tipo],
                tipo === 'cancelar' ? 'Sí, cancelar' : 'Sí, confirmar'
            )
            if (!confirmado) return

            try {
                const { data } = await axios.put(`/api/transferencias/${id}/${tipo}`)
                this.alertaExito(data.mensaje)
                this.obtenerTransferencias()
            } catch (error) {
                const mensaje = error.response?.data?.error
                    ?? error.response?.data?.message
                    ?? 'Error al procesar la acción.'
                this.alertaError(mensaje)
            }
        },

        // ── Helpers visuales ─────────────────────────────────────
        claseEstado(estado) {
            const clases = {
                pendiente:   'bg-gray-100 text-gray-600',
                en_transito: 'bg-yellow-100 text-yellow-700',
                completada:  'bg-green-100 text-green-700',
                cancelada:   'bg-red-100 text-red-600',
            }
            return clases[estado] ?? 'bg-gray-100 text-gray-600'
        },

        etiquetaEstado(estado) {
            const etiquetas = {
                pendiente:   'Pendiente',
                en_transito: 'En tránsito',
                completada:  'Completada',
                cancelada:   'Cancelada',
            }
            return etiquetas[estado] ?? estado
        },

        formatearFecha(fecha) {
            return new Date(fecha).toLocaleString('es-MX', {
                day:    '2-digit',
                month:  '2-digit',
                year:   'numeric',
                hour:   '2-digit',
                minute: '2-digit',
            })
        },
    },
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active { transition: opacity .2s ease; }
.fade-enter-from,
.fade-leave-to     { opacity: 0; }
</style>
