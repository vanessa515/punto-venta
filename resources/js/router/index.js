import { createRouter, createWebHistory } from 'vue-router'

import Inicio from '../views/Inicio.vue'
import Categorias from '../views/Categorias.vue'
import RolesPermisos from '../views/RolesPermisos.vue'
import Inventario from '../views/Inventario/Inventario.vue'
import Kardex from '../views/Inventario/Kardex.vue'
import Transferencias from '../views/Transferencia/Transferencias.vue'


const routes = [
    {
        path: '/inicio',
        component: Inicio
    },
    {
        path: '/categorias',
        component: Categorias
    },
    {
        path: '/rolespermisos',
        component: RolesPermisos
    },

    // ── Inventario ───────────────────────────────────────────────
    {
        path: '/inventario',
        name: 'Inventario',
        component: Inventario,
    },
    {
        path: '/inventario/kardex',
        name: 'Kardex',
        component: Kardex,
    },

    // ── Transferencias ───────────────────────────────────────────
    {
        path: '/transferencias',
        name: 'Transferencias',
        component: Transferencias,
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router