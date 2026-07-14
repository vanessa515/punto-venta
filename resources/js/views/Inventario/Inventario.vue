<template>
    <div class="p-6">

        <!-- Encabezado -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Inventario</h1>
                <p class="text-sm text-gray-500 mt-1">Stock actual por sucursal y producto</p>
            </div>
            <button
                @click="abrirModalAjuste()"
                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition"
            >
                <span class="text-lg leading-none">+</span> Nuevo Ajuste
            </button>
        </div>

        <!-- Barra de búsqueda + Kardex -->
        <div class="flex items-center gap-3 mb-4">
            <input
                v-model="busqueda"
                type="text"
                placeholder="Buscar por ID de producto..."
                class="w-72 text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
            >
            <router-link
                to="/inventario/kardex"
                class="text-sm text-blue-600 hover:underline ml-auto"
            >
                Ver Kardex →
            </router-link>
        </div>

        <!-- Alerta de bajo stock -->
        <div
            v-if="registrosBajoStock > 0"
            class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-2 mb-4"
        >
            <span>⚠️</span>
            <span>
                <strong>{{ registrosBajoStock }}</strong>
                {{ registrosBajoStock === 1 ? 'producto está' : 'productos están' }}
                por debajo del stock mínimo.
            </span>
        </div>

        <!-- Tabla -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">Producto</th>
                        <th class="px-4 py-3 text-left">Sucursal</th>
                        <th class="px-4 py-3 text-right">Cantidad</th>
                        <th class="px-4 py-3 text-right">Stock mín.</th>
                        <th class="px-4 py-3 text-right">Stock máx.</th>
                        <th class="px-4 py-3 text-right">Costo unit.</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-if="cargando">
                        <td colspan="8" class="text-center py-10 text-gray-400">Cargando...</td>
                    </tr>
                    <tr v-else-if="inventarioFiltrado.length === 0">
                        <td colspan="8" class="text-center py-10 text-gray-400">Sin registros en inventario</td>
                    </tr>
                    <tr
                        v-for="item in inventarioFiltrado"
                        :key="item.pk_inventario"
                        :class="item.bajo_stock ? 'bg-red-50' : 'hover:bg-gray-50'"
                        class="transition"
                    >
                        <td class="px-4 py-3 text-gray-400">{{ item.pk_inventario }}</td>
                        <td class="px-4 py-3 font-medium text-gray-700">{{ item.fk_producto }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ item.fk_sucursal }}</td>
                        <td class="px-4 py-3 text-right">
                            <span
                                :class="item.bajo_stock
                                    ? 'text-red-600 font-bold'
                                    : 'text-gray-800'"
                            >
                                {{ item.cantidad }}
                            </span>
                            <span v-if="item.bajo_stock" class="ml-1 text-xs text-red-400">↓ bajo</span>
                        </td>
                        <td class="px-4 py-3 text-right text-gray-500">{{ item.stock_minimo }}</td>
                        <td class="px-4 py-3 text-right text-gray-500">{{ item.stock_maximo ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-gray-500">
                            ${{ Number(item.costo_unitario).toFixed(2) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button
                                @click="abrirModalAjuste(item)"
                                class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-700 font-medium px-3 py-1 rounded-full transition"
                            >
                                Ajustar
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ── Modal: Ajuste de Inventario ── -->
        <Transition name="fade">
            <div
                v-if="mostrarModal"
                class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
                @click.self="cerrarModal"
            >
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">

                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-lg font-semibold text-gray-800">Ajuste de Inventario</h2>
                        <button @click="cerrarModal" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
                    </div>

                    <!-- Sucursal y Producto -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Sucursal</label>
                            <input
                                v-model="formulario.fk_sucursal"
                                type="number"
                                placeholder="ID sucursal"
                                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Producto</label>
                            <input
                                v-model="formulario.fk_producto"
                                type="number"
                                placeholder="ID producto"
                                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                            >
                        </div>
                    </div>

                    <!-- Tipo de movimiento -->
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipo de movimiento</label>
                        <div class="flex gap-2">
                            <button
                                v-for="tipo in ['entrada', 'salida', 'ajuste']"
                                :key="tipo"
                                @click="formulario.tipo_movimiento = tipo"
                                :class="formulario.tipo_movimiento === tipo
                                    ? tipoClaseActivo[tipo]
                                    : 'bg-gray-100 text-gray-500 hover:bg-gray-200'"
                                class="flex-1 text-xs font-medium py-2 rounded-lg transition capitalize"
                            >
                                {{ tipo }}
                            </button>
                        </div>
                    </div>

                    <!-- Cantidad y Costo -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Cantidad</label>
                            <input
                                v-model="formulario.cantidad"
                                type="number"
                                min="0.01"
                                step="0.01"
                                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                Costo unitario
                                <span class="text-gray-400 font-normal">(opcional)</span>
                            </label>
                            <input
                                v-model="formulario.costo_unitario"
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="$0.00"
                                class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                            >
                        </div>
                    </div>

                    <!-- Referencia -->
                    <div class="mb-5">
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Referencia
                            <span class="text-gray-400 font-normal">(opcional)</span>
                        </label>
                        <input
                            v-model="formulario.referencia"
                            type="text"
                            placeholder="Ej: Compra a proveedor X, Merma, Corrección..."
                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                        >
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
                            @click="guardarAjuste"
                            :disabled="guardando"
                            class="text-sm bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300 text-white font-medium px-5 py-2 rounded-lg transition"
                        >
                            {{ guardando ? 'Guardando...' : 'Guardar' }}
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
        const { alertaExito, alertaError } = useAlertas()
        return { alertaExito, alertaError }
    },

    data() {
        return {
            inventario:   [],
            busqueda:     '',
            cargando:     false,
            mostrarModal: false,
            guardando:    false,

            formulario: {
                fk_sucursal:     '',
                fk_producto:     '',
                tipo_movimiento: 'entrada',
                cantidad:        '',
                costo_unitario:  '',
                referencia:      '',
            },

            // Clases de color para cada tipo de movimiento activo
            tipoClaseActivo: {
                entrada: 'bg-green-100 text-green-700',
                salida:  'bg-red-100 text-red-700',
                ajuste:  'bg-yellow-100 text-yellow-700',
            },
        }
    },

    computed: {
        inventarioFiltrado() {
            if (!this.busqueda) return this.inventario
            return this.inventario.filter(i =>
                String(i.fk_producto).includes(this.busqueda)
            )
        },
        registrosBajoStock() {
            return this.inventario.filter(i => i.bajo_stock).length
        },
    },

    mounted() {
        this.obtenerInventario()
    },

    methods: {
        // ── Obtener ──────────────────────────────────────────────
        async obtenerInventario() {
            this.cargando = true
            try {
                const { data } = await axios.get('/api/inventario')
                this.inventario = data
            } catch (error) {
                this.alertaError('No se pudo cargar el inventario.')
            } finally {
                this.cargando = false
            }
        },

        // ── Modal ────────────────────────────────────────────────
        abrirModalAjuste(item = null) {
            // Si viene de un botón "Ajustar" de la tabla, pre-llena sucursal y producto
            if (item) {
                this.formulario.fk_sucursal = item.fk_sucursal
                this.formulario.fk_producto = item.fk_producto
            }
            this.mostrarModal = true
        },

        cerrarModal() {
            this.mostrarModal = false
            this.formulario = {
                fk_sucursal:     '',
                fk_producto:     '',
                tipo_movimiento: 'entrada',
                cantidad:        '',
                costo_unitario:  '',
                referencia:      '',
            }
        },

        // ── Guardar ──────────────────────────────────────────────
        async guardarAjuste() {
            if (!this.formulario.fk_sucursal || !this.formulario.fk_producto || !this.formulario.cantidad) {
                this.alertaError('Sucursal, producto y cantidad son obligatorios.')
                return
            }

            this.guardando = true
            try {
                const { data } = await axios.post('/api/inventario/ajustar', this.formulario)
                this.alertaExito(data.mensaje)
                this.cerrarModal()
                this.obtenerInventario()
            } catch (error) {
                const mensaje = error.response?.data?.error
                    ?? error.response?.data?.message
                    ?? 'Error al guardar el ajuste.'
                this.alertaError(mensaje)
            } finally {
                this.guardando = false
            }
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
