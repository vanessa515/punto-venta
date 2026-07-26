import { createRouter, createWebHistory } from 'vue-router'

import Inicio from '../views/Inicio.vue'
import Categorias from '../views/Categorias.vue'
import RolesPermisos from '../views/RolesPermisos.vue'
import Usuarios from '../views/Usuarios.vue'
import Sucursales from '../views/Sucursales.vue'
import Login from '../views/Login.vue'
import Menu from '../menu/sidebarmenu.vue'

const routes = [
    {
        path: '/',
        component: Login,
    },

    {
        path: '/',
        component: Menu,
        children: [
            {
                path: 'inicio',
                component: Inicio,
            },
            {
                path: 'categorias',
                component: Categorias,
            },
            {
                path: 'rolespermisos',
                component: RolesPermisos,
            },
            {
                path: 'usuarios',
                component: Usuarios,
            },
            {
                path: 'sucursales',
                component: Sucursales,
            },
        ],
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

export default router