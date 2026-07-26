<script setup>
import { ref, watch } from 'vue'

const sidebarOpen = ref(true)

const darkMode = ref(
    localStorage.getItem('theme') === 'dark'
)

watch(
    darkMode,
    (value) => {
        document.body.classList.toggle('dark-theme', value)

        localStorage.setItem(
            'theme',
            value ? 'dark' : 'light'
        )
    },
    { immediate: true }
)

function toggleSidebar() {
    sidebarOpen.value = !sidebarOpen.value
}

function toggleTheme() {
    darkMode.value = !darkMode.value
}
</script>

<template>

    <div class="layout">

        <aside
            class="sidebar"
            :class="{ collapsed: !sidebarOpen }"
        >

            <div class="logo">
                <h2 v-if="sidebarOpen">
                    P.Venta
                </h2>

                <h2 v-else>
                    P
                </h2>
            </div>

            <nav>

                <router-link
                    to="/inicio"
                    class="menu-item"
                >
                    <span class="icon"><i class="fa-solid fa-house"></i></span>

                    <span
                        v-if="sidebarOpen"
                        class="label"
                    >
                        Inicio
                    </span>
                </router-link>

                <router-link
                    to="/categorias"
                    class="menu-item"
                >
                    <span class="icon"><i class="fa-solid fa-folder"></i></span>

                    <span
                        v-if="sidebarOpen"
                        class="label"
                    >
                        Categorías
                    </span>
                </router-link>

                <router-link
                    to="/productos"
                    class="menu-item"
                >
                    <span class="icon"><i class="fa-solid fa-box"></i></span>

                    <span
                        v-if="sidebarOpen"
                        class="label"
                    >
                        Productos
                    </span>
                </router-link>

                <router-link
                    to="/inventario"
                    class="menu-item"
                >
                    <span class="icon"><i class="fa-solid fa-clipboard"></i></span>

                    <span
                        v-if="sidebarOpen"
                        class="label"
                    >
                        Inventario
                    </span>
                </router-link>

                <router-link
                    to="/ventas"
                    class="menu-item"
                >
                    <span class="icon"><i class="fa-solid fa-tags"></i>
                    
                    </span>

                    <span
                        v-if="sidebarOpen"
                        class="label"
                    >
                        Ventas
                    </span>
                </router-link>

                <router-link
                    to="/clientes"
                    class="menu-item"
                >
                    <span class="icon"><i class="fa-regular fa-user"></i></span>

                    <span
                        v-if="sidebarOpen"
                        class="label"
                    >
                        Clientes
                    </span>
                </router-link>

                <router-link
                    to="/compras"
                    class="menu-item"
                >
                    <span class="icon"><i class="fa-solid fa-bag-shopping"></i></span>

                    <span
                        v-if="sidebarOpen"
                        class="label"
                    >
                        Compras
                    </span>
                </router-link>

                <router-link
                    to="/reportes"
                    class="menu-item"
                >
                    <span class="icon"><i class="fa-solid fa-chart-bar"></i></span>

                    <span
                        v-if="sidebarOpen"
                        class="label"
                    >
                        Reportes
                    </span>
                </router-link>

                <router-link
                    to="/usuarios"
                    class="menu-item"
                >
                    <span class="icon"><i class="fa-solid fa-gear"></i></span>

                    <span
                        v-if="sidebarOpen"
                        class="label"
                    >
                        Usuarios
                    </span>
                </router-link>

                <router-link
                    to="/sucursales"
                    class="menu-item"
                >
                    <span class="icon"><i class="fa-solid fa-gear"></i></span>

                    <span
                        v-if="sidebarOpen"
                        class="label"
                    >
                        Sucursales
                    </span>
                </router-link>

                <router-link
                    to="/rolespermisos"
                    class="menu-item"
                >
                    <span class="icon"><i class="fa-solid fa-gear"></i></span>

                    <span
                        v-if="sidebarOpen"
                        class="label"
                    >
                        Roles/Permisos
                    </span>
                </router-link>

            </nav>

        </aside>

        <main class="content">

            <header class="topbar">

                <button
                    class="top-btn"
                    @click="toggleSidebar"
                >
                    ☰
                </button>

                <div class="top-actions">

                    <button
                        class="top-btn"
                        @click="toggleTheme"
                    >
                        {{ darkMode ? '☀️' : '🌙' }}
                    </button>

                </div>

            </header>

            <section class="page-content">
                <router-view />
            </section>

        </main>

    </div>

</template>

<style scoped>

.layout{
    display:flex;
    min-height:100vh;
    background:#f4f6f9;
}

.sidebar{
    width:260px;
    background:#111827;
    color:white;
    padding:20px;
    transition:all .3s ease;
    overflow:hidden;
}

.sidebar.collapsed{
    width:80px;
}

.logo{
    text-align:center;
    margin-bottom:30px;
}

.logo h2{
    margin:0;
}

.menu-item{
    display:flex;
    align-items:center;
    gap:12px;
    padding:12px;
    margin-bottom:8px;
    color:#d1d5db;
    text-decoration:none;
    border-radius:10px;
    transition:.2s;
}

.menu-item:hover{
    background:#1f2937;
    color:white;
}

.router-link-active{
    background:#2563eb;
    color:white;
}

.icon{
    font-size:20px;
    min-width:25px;
}

.content{
    flex:1;
    display:flex;
    flex-direction:column;
}

.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:white;
    padding:15px 20px;
    border-bottom:1px solid #e5e7eb;
}

.top-btn{
    border:none;
    background:none;
    cursor:pointer;
    font-size:20px;
}

.page-content{
    padding:25px;
}

.top-actions{
    display:flex;
    align-items:center;
    gap:15px;
}

</style>

<style>

body{
    margin:0;
    font-family:Arial, Helvetica, sans-serif;
}

body.dark-theme{
    background:#0f172a;
    color:white;
}

body.dark-theme .sidebar{
    background:#020617;
}

body.dark-theme .topbar{
    background:#111827;
    color:white;
}

body.dark-theme .content{
    background:#0f172a;
}

body.dark-theme .page-content{
    color:white;
}

@media(max-width:768px){

    .sidebar{
        width:80px;
    }

}

</style>