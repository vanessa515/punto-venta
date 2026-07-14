<template>
    <div class="p-6">

        <!-- Encabezado -->
        <div class="flex items-center gap-3 mb-6">
            <router-link
                to="/inventario"
                class="text-gray-400 hover:text-gray-600 text-sm transition"
            >
                ← Inventario
            </router-link>
            <span class="text-gray-300">/</span>
            <h1 class="text-2xl font-bold text-gray-800">Kardex</h1>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-xl shadow p-4 mb-6">
            <p class="text-xs font-medium text-gray-500 mb-3">Consultar historial de movimientos</p>
            <div class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Sucursal</label>
                    <input
                        v-model="filtro.fk_sucursal"
                        type="number"
                        placeholder="ID sucursal"
                        class="w-36 text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                        @keyup.enter="obtenerKardex"
                    >
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Producto</label>
                    <input
                        v-model="filtro.fk_producto"
                        type="number"
                        placeholder="ID producto"
                        class="w-36 text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                        @keyup.enter="obtenerKardex"
                    >
                </div>
                <button
                    @click="obtenerKardex"
                    :disabled="cargando"
                    class="text-sm bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300 text-white font-medium px-5 py-2 rounded-lg transition"
                >
                    {{ cargando ? 'Buscando...' : 'Consultar' }}
                </button>
                <button
                    v-if="movimientos.length > 0"
                    @click="limpiar"
                    class="text-sm text-gray-500 hover:text-gray-700 underline"
                >
                    Limpiar
                </button>
            </div>
        </div>

        <!-- Resumen rápido -->
        <div v-if="movimientos.length > 0" class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-green-50 border border-green-100 rounded-xl p-4 text-center">
                <p class="text-xs text-green-600 font-medium mb-1">Total entradas</p>
                <p class="text-xl font-bold text-green-700">{{ totalEntradas }}</p>
            </div>
            <div class="bg-red-50 border border-red-100 rounded-xl p-4 text-center">
                <p class="text-xs text-red-600 font-medium mb-1">Total salidas</p>
                <p class="text-xl font-bold text-red-700">{{ totalSalidas }}</p>
            </div>
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-center">
                <p class="text-xs text-blue-600 font-medium mb-1">Movimientos</p>
                <p class="text-xl font-bold text-blue-700">{{ movimientos.length }}</p>
            </div>
        </div>

        <!-- Tabla de movimientos -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">Tipo</th>
                        <th class="px-4 py-3 text-right">Cantidad</th>
                        <th class="px-4 py-3 text-right">Costo unit.</th>
                        <th class="px-4 py-3 text-left">Referencia</th>
                        <th class="px-4 py-3 text-left">Usuario</th>
                        <th class="px-4 py-3 text-left">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-if="cargando">
                        <td colspan="7" class="text-center py-10 text-gray-400">Buscando movimientos...</td>
                    </tr>
                    <tr v-else-if="!consultado">
                        <td colspan="7" class="text-center py-10 text-gray-400">
                            Ingresa sucursal y producto para consultar el kardex
                        </td>
                    </tr>
                    <tr v-else-if="movimientos.length === 0">
                        <td colspan="7" class="text-center py-10 text-gray-400">
                            Sin movimientos para este producto en esta sucursal
                        </td>
                    </tr>
                    <tr
                        v-for="mov in movimientos"
                        :key="mov.pk_movimiento"
                        class="hover:bg-gray-50 transition"
                    >
                        <td class="px-4 py-3 text-gray-400">{{ mov.pk_movimiento }}</td>
                        <td class="px-4 py-3">
                            <span
                                class="text-xs font-medium px-2 py-1 rounded-full"
                                :class="claseTipo(mov.tipo_movimiento)"
                            >
                                {{ mov.tipo_movimiento }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-medium"
                            :class="esEntrada(mov.tipo_movimiento) ? 'text-green-600' : 'text-red-600'"
                        >
                            {{ esEntrada(mov.tipo_movimiento) ? '+' : '-' }}{{ mov.cantidad }}
                        </td>
                        <td class="px-4 py-3 text-right text-gray-500">
                            {{ mov.costo_unitario ? '$' + Number(mov.costo_unitario).toFixed(2) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ mov.referencia ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ mov.usuario?.name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                            {{ formatearFecha(mov.created_at) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</template>

<script>
import axios from 'axios'
import { useAlertas } from '../../composables/useAlertas'

export default {
    setup() {
        const { alertaError, alertaAdvertencia } = useAlertas()
        return { alertaError, alertaAdvertencia }
    },

    data() {
        return {
            movimientos: [],
            cargando:    false,
            consultado:  false,

            filtro: {
                fk_sucursal: '',
                fk_producto: '',
            },
        }
    },

    computed: {
        totalEntradas() {
            return this.movimientos
                .filter(m => this.esEntrada(m.tipo_movimiento))
                .reduce((acc, m) => acc + Number(m.cantidad), 0)
                .toFixed(2)
        },
        totalSalidas() {
            return this.movimientos
                .filter(m => !this.esEntrada(m.tipo_movimiento))
                .reduce((acc, m) => acc + Number(m.cantidad), 0)
                .toFixed(2)
        },
    },

    methods: {
        // ── Consultar ────────────────────────────────────────────
        async obtenerKardex() {
            if (!this.filtro.fk_sucursal || !this.filtro.fk_producto) {
                this.alertaAdvertencia('Ingresa tanto la sucursal como el producto para consultar.')
                return
            }

            this.cargando = true
            this.consultado = false

            try {
                const { data } = await axios.get('/api/inventario/kardex', { params: this.filtro })
                this.movimientos = data
                this.consultado  = true
            } catch (error) {
                this.alertaError('No se pudo cargar el kardex.')
            } finally {
                this.cargando = false
            }
        },

        limpiar() {
            this.movimientos = []
            this.consultado  = false
            this.filtro      = { fk_sucursal: '', fk_producto: '' }
        },

        // ── Helpers ──────────────────────────────────────────────
        esEntrada(tipo) {
            return tipo === 'entrada' || tipo === 'ajuste'
        },

        claseTipo(tipo) {
            const clases = {
                entrada:       'bg-green-100 text-green-700',
                salida:        'bg-red-100 text-red-700',
                ajuste:        'bg-yellow-100 text-yellow-700',
                transferencia: 'bg-blue-100 text-blue-700',
            }
            return clases[tipo] ?? 'bg-gray-100 text-gray-600'
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
