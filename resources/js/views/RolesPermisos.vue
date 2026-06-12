<template>

    <div>

        <div class="card-custom" >

            <div class="table table-responsive">

                <div class="d-flex align-items-center gap-2">
                    <input type="text" placeholder="Busqueda" class="form-control busqueda" v-model="busqueda" @keyup.enter="Roles">

                    <button
                        class="btn btn-primary ms-auto"
                        @click="abrirModal"
                    >
                        Registrar
                    </button>
                </div><br>

                <table class="table table-hover table-sm" >
                
                    <thead>
                    
                        <tr>
                        
                            <th>ID</th>
                            <th>ROL</th>
                            <th>FECHA DE REGISTRO</th>
                            <th>PERMISOS</th>
                            <th>ACCIONES</th>

                        </tr>

                    </thead>

                    <tbody>
                        <tr v-if="roles.length === 0">
                            <td colspan="5" class="text-center">
                                No se encontraron registros
                            </td>
                        </tr>

                        <tr v-for="rol in roles" :key="rol.id_rol">
                            <td>{{ rol.id_rol }}</td>
                            <td>{{ rol.nombre }}</td>
                            <td>{{ rol.created_at }}</td>
                            <td>
                                <div
                                    v-for="permiso in rol.permisos"
                                    :key="permiso.id_permiso"
                                    class="mb-1"
                                >
                                    <span class="badge bg-success">
                                        {{ permiso.nombre }}
                                    </span>
                                </div>
                            </td>
                            <td>
                            
                                <div>
                                
                                    <button class="btn btn-sm btn-primary me-1" @click="editarRol(rol)">
                                        <i class="fa-solid fa-pencil"></i>
                                    </button>

                                    <button class="btn btn-sm btn-danger me-1" @click="Eliminar(rol)">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>

                                </div>

                            </td>
                        </tr>
                    </tbody>

                </table>

            </div>

        </div>

    </div>



    <div v-if="mostrarModal" class="modal-overlay">
        <div class="modal-content-custom">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>
                    {{ editando ? 'Editar Rol' : 'Registrar Rol' }}
                </h5>

                <button
                    class="btn-close"
                    @click="mostrarModal = false"
                ></button>
            </div>

            <div class="mb-3">
                <input
                    type="text"
                    class="form-control"
                    placeholder="Nombre"
                    v-model="nombreRol"
                    :class="{ 'is-invalid': errorNombre }"
                    required
                >
                <small
                    v-if="errorNombre"
                    class="text-danger"
                >
                    {{ errorNombre }}
                </small>
                <br>
                
                <div class="table table-responsive">
                
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Módulo</th>
                                <th>Permiso</th>
                            </tr>
                        </thead>

                        <tbody>

                            <tr
                                v-for="modulo in permisos"
                                :key="modulo.name"
                            >

                                <td>{{ modulo.name }}</td>

                                <td>

                                    <div
                                        v-for="permiso in modulo.permisos"
                                        :key="permiso.id_permiso"
                                        class="form-check"
                                    >

                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            :value="permiso.id_permiso"
                                            v-model="permisosSeleccionados"
                                        >

                                        <label class="form-check-label">
                                            {{ permiso.nombre }}
                                        </label>

                                    </div>

                                </td>

                            </tr>

                        </tbody>
                        
                    </table>

                    <small
                        v-if="errorPermisos"
                        class="text-danger"
                    >
                        {{ errorPermisos }}
                    </small>

                </div>

            </div>

            <div class="text-end">
                <button
                    class="btn btn-secondary me-2"
                    @click="mostrarModal = false"
                >
                    Cancelar
                </button>

                <button
                    class="btn btn-primary"
                    @click="editando ? ActualizarRol() : GuardarPermisos()"
                >
                    {{ editando ? 'Actualizar' : 'Guardar' }}
                </button>
            </div>

        </div>
    </div>
    
</template>

<script>
import axios from "axios";
import Swal from 'sweetalert2';

export default {
  data() {
    return {
      roles: [],
      busqueda: '',
      mostrarModal: false,
      nombreRol: '',

      permisos: [],
      permisosSeleccionados: [],

      errorNombre: '',
      errorPermisos: '',

      editando: false,
      idRol: null,
    };
  },

  mounted() {
    this.Roles();
  },

  methods: {
    async Roles() {
      try {
       const response = await axios.get('/api/roles', {
            params: {
                busqueda: this.busqueda
            }
        });

        this.roles = response.data.roles;

      } catch (error) {

         console.log(error);

      }

    },

    async GuardarPermisos() {
    try {

        this.errorNombre = '';
        this.errorPermisos = '';

        if (!this.nombreRol.trim()) {
            this.errorNombre = 'El nombre es obligatorio.';
        return;
        }

        if (this.permisosSeleccionados.length === 0) {
            this.errorPermisos = 'Debes seleccionar al menos un permiso.';
            return;
        }

        const response = await axios.post('/api/permisosinsert',{

            nombre: this.nombreRol,
            permisos: this.permisosSeleccionados

        });

        Swal.fire({
            icon:'success',
            title:'Registrado',
            text:'El rol ' + this.nombreRol + ' se ha registrado correctamente'
        })

        this.nombreRol = '';
        this.permisosSeleccionados = [];
        this.mostrarModal = false;

        await this.Roles();

        } catch (error) {

            if (error.response?.data?.message) {

                this.errorNombre = error.response.data.message;

            } else {

                console.log(error);

            }

        }
    },

    async editarRol(rol) {

        this.editando = true;
        this.idRol = rol.id_rol;

        this.errorNombre = '';
        this.errorPermisos = '';

        await this.Permisos();

        this.nombreRol = rol.nombre;

        this.permisosSeleccionados = rol.permisos.map(
            permiso => permiso.id_permiso
        );

        this.mostrarModal = true;
    },

    async ActualizarRol() {
        try {

            this.errorNombre = '';
            this.errorPermisos = '';

            if (!this.nombreRol.trim()) {
                this.errorNombre = 'El nombre es obligatorio.';
                return;
            }

            if (this.permisosSeleccionados.length === 0) {
                this.errorPermisos = 'Debes seleccionar al menos un permiso.';
                return;
            }

            await axios.put('/api/permisosupdate', {
                id: this.idRol,
                nombre: this.nombreRol,
                permisos: this.permisosSeleccionados
            });

            this.mostrarModal = false;

            this.limpiarFormulario();

            Swal.fire({
                icon: 'success',
                title: 'Actualizado',
                text: 'El rol se actualizó correctamente'
            });

            await this.Roles();

        } catch (error) {

            if (error.response?.data?.message) {
                this.errorNombre = error.response.data.message;
            } else {
                console.log(error);
            }

        }
    },

    async Permisos() {
        try {

            const response = await axios.get('/api/permisos');

            this.permisos = response.data.permisos;

        } catch (error) {

            console.log("Error cargando permisos:", error);

        }
    },


    async Eliminar(rol) {

        const result = await Swal.fire({
            title: `<p style="text-align: center; ">Estas seguro de eliminar el rol: ${rol.nombre}?</p>`,
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });

        if (!result.isConfirmed) {
            return;
        }

        try {

            const response = await axios.delete('/api/permisosdelete', {
                data: {
                    id: rol.id_rol
                }
            });

            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: response.data.message
            });

            this.Roles();

        } catch (error) {

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.response?.data?.message || 'Ocurrió un error'
            });

        }
    },


    async abrirModal() {

        this.limpiarFormulario();

        this.mostrarModal = true;

        await this.Permisos();
    },

    limpiarFormulario() {

        this.editando = false;
        this.idRol = null;

        this.nombreRol = '';
        this.permisosSeleccionados = [];

        this.errorNombre = '';
        this.errorPermisos = '';
    }

  },
};
</script>
<style scoped>
.card-custom{
    padding:20px;
    border-radius:10px;
    box-shadow:0 2px 5px rgba(0,0,0,.1);
}

.busqueda{
    width: 30% !important;
}
</style>

<style>
body.dark-theme .card-custom{
    background-color:#111827;
    color:white;
}

body.dark-theme .table{
    color:white;
}

body.dark-theme .table th{
    background-color:#111827;
    color:white;
    border-color:#374151;
}

body.dark-theme .table td{
    background-color:#111827;
    color:white;
    border-color:#374151;
}

body.dark-theme .table-hover tbody tr:hover td{
    background-color:#1f2937;
}

body.dark-theme .form-control{
    background-color:#1f2937;
    border-color:#374151;
    color:white;
}

body.dark-theme .form-control::placeholder{
    color:#9ca3af;
}
.modal-overlay{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.5);
    display:flex;
    justify-content:center;
    align-items:center;
    z-index:9999;
}

.modal-content-custom{
    background:white;
    width:500px;
    max-width:90%;
    padding:20px;
    border-radius:10px;
}

body.dark-theme .modal-content-custom{
    background:#111827;
    color:white;
}
</style>