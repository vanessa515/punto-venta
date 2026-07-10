import { createRouter, createWebHistory } from 'vue-router'

import Inicio from '../views/Inicio.vue'
import Categorias from '../views/Categorias.vue'
import RolesPermisos from '../views/RolesPermisos.vue'
import Marcas from '../views/Marcas.vue'
import Productos from '../views/Productos.vue'


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
    {
        path: '/marcas',
        component: Marcas
    },
    {
        path: '/productos',
        component: Productos
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router