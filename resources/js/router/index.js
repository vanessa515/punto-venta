import { createRouter, createWebHistory } from 'vue-router'

import Inicio from '../views/Inicio.vue'
import Categorias from '../views/Categorias.vue'
import RolesPermisos from '../views/RolesPermisos.vue'


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
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router