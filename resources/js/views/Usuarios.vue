<template>

    <div>

        <div class="table table-responsive">

                <div class="d-flex gap-2">

                    <select
                        class="form-select"
                        v-model="filtro"
                        style="max-width: 180px;"
                    >
                        <option value="users.name">Usuario</option>
                        <option value="roles.nombre">Rol</option>
                    </select>

                    <input
                        type="text"
                        placeholder="Busqueda"
                        class="form-control busqueda"
                        v-model="busqueda"
                        @keyup.enter="listado"
                    >

                    <button
                        class="btn btn-primary"
                        @click="listado"
                    >
                        Buscar
                    </button>
                    <button
                        class="btn btn-primary ms-auto"
                        @click="abrirModal"
                    >
                        Registrar
                    </button>

                </div><br>

            <table class="table table-hover" >
            
                <thead>
                
                    <tr>

                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Estatus</th>
                        <th>Acciones</th>   

                    </tr>

                </thead>

                <tbody>

                    <tr v-if="usuarios.length === 0">
                        <td colspan="5" class="text-center">
                            No se encontraron registros
                        </td>
                    </tr>
                
                    <tr v-for="usuario in usuarios" :key="usuario.id">

                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img
                                    :src="usuario.avatar ? `/storage/${usuario.avatar}` : '/images/users_default.png'"
                                    alt="Avatar"
                                    class="avatar-tabla"
                                >
                                <span>{{ usuario.username }}</span>
                            </div>
                        </td>
                        <td> {{ usuario.correo }} </td>
                        <td>{{ usuario.rol }}</td>
                        <td>
                        
                            <span :class="usuario.estatus == 1 ? 'spanactivo' : 'spaninactivo'">
                                {{ usuario.estatus == 1 ? 'Activo' : 'Inactivo' }}
                            </span>
                        
                        </td>
                        <td>

                            <div>
                                
                                <button class="btn btn-sm btn-primary me-1" @click="editarUsuario(usuario)">

                                    <i class="fa-solid fa-pencil"></i>

                                </button>

                                <button class="btn btn-sm btn-danger me-1" @click="Eliminar(usuario)">

                                    <i class="fa-solid fa-trash"></i>

                                </button>

                            </div>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

    <div v-if="mostrarModal" class="modal-overlay">

            <div class="modal-content-custom">

                <ul class="nav nav-tabs mb-3">
                    <li class="nav-item">
                        <button
                        class="nav-link"
                        :class="{ active: tab === 1 }"
                        >
                        Datos generales
                        </button>
                    </li>

                    <li class="nav-item">
                        <button
                        class="nav-link"
                        :class="{ active: tab === 2 }"
                        >
                        Registro de usuario
                        </button>
                    </li>
                </ul>

                <div v-if="tab === 1">

                    <h5 class="mb-0">
                        <i
                            class="me-2"
                            :class="editando ? 'fa-solid fa-user-pen' : 'fa-solid fa-user-plus'"
                        ></i>
                            {{ editando ? 'Editar Persona' : 'Registrar Persona' }}
                    </h5>

                    <hr><br>

                    <input
                        type="text"
                        class="form-control"
                        placeholder="Ingrese el nombre"
                        v-model="nombre_personal"
                        :class="{ 'is-invalid': errorNombrePersonal }"
                    >
                    <small v-if="errorNombrePersonal" class="text-danger d-block mt-1">
                        {{ errorNombrePersonal }}
                    </small>
                    <br>
                    <div class="row mb-4">

                        <div class="col-md-6">

                            <input
                                type="text"
                                class="form-control"
                                placeholder="Ingrese el Codigo Postal"
                                v-model="cp"
                                :class="{ 'is-invalid': errorCp }"
                            >
                            <small v-if="errorCp" class="text-danger d-block mt-1">
                                {{ errorCp }}
                            </small>

                        </div>
                        <div class="col-md-6">
                        
                            <input
                                type="text"
                                class="form-control"
                                placeholder="Ingrese la direccion"
                                v-model="direccion"
                                :class="{ 'is-invalid': errorDireccion }"
                            >
                            <small v-if="errorDireccion" class="text-danger d-block mt-1">
                                {{ errorDireccion }}
                            </small>

                        </div>

                    </div>

                        <input
                            type="text"
                            class="form-control"
                            placeholder="Ingrese el telefono"
                            v-model="telefono"
                            :class="{ 'is-invalid': errorTelefono }"
                        >
                        <small v-if="errorTelefono" class="text-danger d-block mt-1">
                            {{ errorTelefono }}
                        </small>

                    <div class="col-md-12">
                        <br>
                        <select
                            class="form-select"
                            v-model="fk_sucursal"
                            :class="{ 'is-invalid': errorFk_sucursal }"
                        >
                            <option value="" disabled>
                                Selecciona una sucursal
                            </option>

                            <option
                                v-for="sucursal in sucursales"
                                :key="sucursal.id_sucursal"
                                :value="sucursal.id_sucursal"
                            >
                                {{ sucursal.nombre_sucursal }}
                            </option>
                        </select>

                        <small v-if="errorFk_sucursal" class="text-danger">
                            {{ errorFk_sucursal }}
                        </small>
                    </div>

                </div>

                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-4"  v-if="tab === 2">
                    <h5 class="mb-0">
                        <i
                            class="me-2"
                            :class="editando ? 'fa-solid fa-user-pen' : 'fa-solid fa-user-plus'"
                        ></i>
                            {{ editando ? 'Editar Usuario' : 'Registrar Usuario' }}
                    </h5>

                    <button
                        class="btn-close"
                        @click="mostrarModal = false"
                    ></button>
                </div>

                <div v-if="tab === 2">

                    <div class="mb-4">
                        <label class="form-label fw-semibold mb-2">
                            Avatar
                        </label>

                        <div class="avatar-upload-container">

                            <div class="avatar-preview-wrapper">
                                <img
                                    v-if="previewAvatar"
                                    :src="previewAvatar"
                                    alt="Avatar"
                                    class="avatar-preview"
                                >

                                <div
                                    v-else
                                    class="avatar-placeholder"
                                >
                                    <i class="fa-solid fa-user"></i>
                                </div>
                            </div>

                            <div class="flex-grow-1">

                                <input
                                    type="file"
                                    class="form-control avatar-input"
                                    accept="image/*"
                                    @change="seleccionarAvatar"
                                >

                                <small class="text-muted d-block mt-2">
                                    JPG, JPEG, PNG o WEBP (Máx. 2 MB)
                                </small>

                                <small
                                    v-if="errorAvatar"
                                    class="text-danger d-block mt-1"
                                >
                                    {{ errorAvatar }}
                                </small>

                            </div>

                        </div>
                        
                    </div>


                    <div class="mb-3">
                    
                        <label class="form-label fw-semibold">
                            Nombre
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            placeholder="Ingrese el nombre"
                            v-model="nombre"
                            :class="{ 'is-invalid': errorNombre }"
                        >

                        <small v-if="errorNombre" class="text-danger">
                            {{ errorNombre }}
                        </small>
                    </div>

            
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Correo electrónico
                        </label>

                        <input
                            type="email"
                            class="form-control"
                            placeholder="ejemplo@correo.com"
                            v-model="email"
                            :class="{ 'is-invalid': errorEmail }"
                            autocomplete="off"
                        >

                        <small v-if="errorEmail" class="text-danger">
                            {{ errorEmail }}
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Contraseña
                        </label>

                        <div class="input-group">
                            <input
                                :type="mostrarPassword ? 'text' : 'password'"
                                class="form-control"
                                placeholder="Ingrese una contraseña"
                                v-model="password"
                                :class="{ 'is-invalid': errorPassword }"
                                @focus="mostrarReglasPassword = true"
                                @blur="mostrarReglasPassword = false"
                                autocomplete="new-password"
                            >

                            <button
                                class="btn btn-outline-secondary"
                                type="button"
                                @click="mostrarPassword = !mostrarPassword"
                            >
                                <i :class="mostrarPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                            </button>
                        </div>

                        <div
                            v-if="mostrarReglasPassword"
                            class="mt-2 p-2 border rounded bg-light small"
                        >
                            <div :class="password.length >= 8 ? 'text-success' : 'text-danger'">
                                <i class="fa-solid fa-check me-1"></i>
                                Mínimo 8 caracteres
                            </div>

                            <div :class="/[A-Z]/.test(password) ? 'text-success' : 'text-danger'">
                                <i class="fa-solid fa-check me-1"></i>
                                Al menos una letra mayúscula
                            </div>

                            <div :class="/[!@#$%^&*()_\-+=\[\]{};:'&quot;,.<>/?\\|`~]/.test(password) ? 'text-success' : 'text-danger'">
                                <i class="fa-solid fa-check me-1"></i>
                                Al menos un carácter especial
                            </div>
                        </div>

                        <small v-if="errorPassword" class="text-danger">
                            {{ errorPassword }}
                        </small>
                    </div>

                    <div class="row mb-4">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Rol
                            </label>

                            <select
                                class="form-select"
                                v-model="fk_rol"
                                :class="{ 'is-invalid': errorFk_rol }"
                            >
                                <option value="" disabled>
                                    Selecciona un rol
                                </option>

                                <option
                                    v-for="rol in roles"
                                    :key="rol.id_rol"
                                    :value="rol.id_rol"
                                >
                                    {{ rol.nombre_rol }}
                                </option>
                            </select>

                            <small v-if="errorFk_rol" class="text-danger">
                                {{ errorFk_rol }}
                            </small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Estatus
                            </label>

                            <select
                                class="form-select"
                                v-model="estatus"
                                :class="{ 'is-invalid': Errorestatus }"
                            >
                                <option value="" disabled>
                                    Selecciona el estatus
                                </option>

                                <option value="1">
                                    Activo
                                </option>

                                <option value="0">
                                    Inactivo
                                </option>
                            </select>

                            <small v-if="Errorestatus" class="text-danger">
                                {{ Errorestatus }}
                            </small>
                        </div>

                    </div>

                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-3">
                    <button
                        class="btn btn-outline-secondary"
                        @click="mostrarModal = false"
                    >
                        Cancelar
                    </button>

                   <button
                        v-if="tab === 1"
                        class="btn btn-primary"
                        @click="validarTab1"
                    >
                        Siguiente
                    </button>
                    <div v-if="tab === 2" class="d-flex gap-2">

                        <button class="btn btn-secondary" @click="tab = 1">
                            Anterior
                        </button>

                        <button
                            class="btn btn-primary"
                            @click="editando ? Actualizar() : Registro()"
                        >
                            <i
                                class="me-2"
                                :class="editando ? 'fa-solid fa-user-pen' : 'fa-solid fa-user-plus'"
                            ></i>
                            {{ editando ? 'Actualizar' : 'Guardar' }}
                        </button>

                    </div>
        
                </div>

            </div>

        </div>

</template>

<script>

import axios from "axios";
import Swal from 'sweetalert2';
import Sucursales from "./Sucursales.vue";

export default {

    data() {
        return {
            usuarios: [],
            roles: [],
            sucursales: [],
            busqueda: '',
            estatus: '',
            filtro: 'users.name',
            mostrarModal: false,
            mostrarPassword: false,
            mostrarReglasPassword: false,

            // Campos del formulario datos personales

            nombre_personal: '',
            cp: '',
            direccion: '',
            telefono: '',
            fk_sucursal: '',

            // Campos del formulario usuario
            nombre: '',
            email: '',
            password: '',
            fk_rol: '',
            avatar: null,

            // Validaciones
            errorFk_sucursal: '',
            errorTelefono: '',
            errorDireccion: '',
            errorCp: '',
            errorNombrePersonal: '',
            errorNombre: '',
            errorEmail: '',
            errorPassword: '',
            errorFk_rol: '',
            Errorestatus: '',

            /////Avatar
            previewAvatar: null,
            errorAvatar: '',

            ///Edicion
            editando: false,

            ///tabs
             tab: 1
            

        }
    },

    mounted() {
        this.listado();
    },

    methods: {

        async listado() {
            try {
                const response = await axios.get('/api/usuarios',{

                    params:{

                        busqueda: this.busqueda,
                        filtro: this.filtro

                    }

                });

                this.usuarios = response.data.usuarios;

                this.roles = response.data.roles;

                this.sucursales = response.data.sucursales;

            } catch (error) {
                console.error('Error al obtener usuarios:', error);
            }
        },

        async Registro() {

            try {

                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                this.errorNombre = '';
                this.errorEmail = '';
                this.errorPassword = '';
                this.errorFk_rol = '';
                this.Errorestatus = '';

                if (!this.nombre.trim()) {
                    this.errorNombre = 'El nombre de usuario es obligatorio.';
                return;
                }

                if (!this.email.trim()) {
                    this.errorEmail = 'El email es obligatorio.';
                return;
                }

                if (!emailRegex.test(this.email)) {
                    this.errorEmail = 'Ingrese un correo electrónico válido.';
                return;
                }


                if (!this.password.trim()) {
                    this.errorPassword = 'La contraseña es obligatoria.';
                    return;
                }

                if (this.password.length < 8) {
                    this.errorPassword = 'La contraseña debe tener al menos 8 caracteres.';
                    return;
                }

                if (!/[A-Z]/.test(this.password)) {
                    this.errorPassword = 'La contraseña debe contener al menos una mayúscula.';
                    return;
                }

                if (!/[!@#$%^&*()_\-+=\[\]{};:'",.<>/?\\|`~]/.test(this.password)) {
                    this.errorPassword = 'La contraseña debe contener al menos un carácter especial.';
                    return;
                }

                if (!this.fk_rol) {
                    this.errorFk_rol = 'El rol es obligatorio.';
                    return;
                }


                const formData = new FormData();

                formData.append('nombre', this.nombre);
                formData.append('email', this.email);
                formData.append('password', this.password);
                formData.append('fk_rol', this.fk_rol);

                //////PERSONA

                formData.append('nombre_personal', this.nombre_personal);
                formData.append('cp', this.cp);
                formData.append('direccion', this.direccion);
                formData.append('telefono', this.telefono);
                formData.append('fk_sucursal', this.fk_sucursal);


                if (this.avatar) {
                    formData.append('avatar', this.avatar);
                }

                const response = await axios.post(
                    '/api/registrousr',
                    formData,
                );

                console.log(response.data);
                this.limpiarFormulario();

                Swal.fire({
                    icon: 'success',
                    title: 'Usuario registrado exitosamente',
                    showConfirmButton: false,
                    timer: 1500
                });

                this.mostrarModal = false;

                this.listado();

            } catch (error) {

                 if (error.response && error.response.status === 422) {

                    const errors = error.response.data.errors;

                    if (errors.nombre) {
                        this.errorNombre = errors.nombre[0];
                    }

                    if (errors.email) {
                        this.errorEmail = errors.email[0];
                    }

                    if (errors.password) {
                        this.errorPassword = errors.password[0];
                    }

                    if (errors.fk_rol) {
                        this.errorFk_rol = errors.fk_rol[0];
                    }

                    if (errors.nombre_personal) {
                        this.errorNombrePersonal = errors.nombre_personal[0];
                    }

                    if (errors.cp) {
                        this.errorCp = errors.cp[0];
                    }

                    if (errors.direccion) {
                        this.errorDireccion = errors.direccion[0];
                    }

                    if (errors.telefono) {
                        this.errorTelefono = errors.telefono[0];
                    }

                    if (errors.fk_sucursal) {
                        this.errorFk_sucursal = errors.fk_sucursal[0];
                    }

                    return;
                }

                console.error('Error al registrar usuario:', error);
            }
        },

        async abrirModal() {

            this.limpiarFormulario();

            this.mostrarModal = true;

            this.tab = 1;

        },

        validarTab1() {

            this.errorNombrePersonal = '';
            this.errorCp = '';
            this.errorDireccion = '';
            this.errorTelefono = '';
            this.errorFk_sucursal = '';

            let valido = true;

            if (!this.nombre_personal.trim()) {
                this.errorNombrePersonal = 'El nombre es obligatorio';
                valido = false;
            }

            if (!this.cp.trim()) {
                this.errorCp = 'El código postal es obligatorio';
                valido = false;
            }

            if (!this.direccion.trim()) {
                this.errorDireccion = 'La dirección es obligatoria';
                valido = false;
            }

            if (!this.telefono.trim()) {
                this.errorTelefono = 'El teléfono es obligatorio';
                valido = false;
            }

            if (!this.fk_sucursal) {
                this.errorFk_sucursal = 'Selecciona una sucursal';
                valido = false;
            }

            if (valido) {
                this.tab = 2;
            }
        },

        limpiarFormulario() {

            this.editando = false;

            this.nombre = '';
            this.email = ''
            this.password = '';
            this.avatar = null,

            //Persona

            this.nombre_personal = '',
            this.cp = '',
            this.direccion = '',
            this.telefono = '',
            this.fk_sucursal = '',

            this.errorNombre = '';
            this.errorEmail = '';
            this.errorPassword = '';
            this.errorFk_rol = '';

            this.previewAvatar = null;
            this.errorAvatar = '';

            mostrarModal: false;
            
        },

        async editarUsuario(usuario) {

            this.editando = true;

            this.idUsuario = usuario.id;

            this.errorNombre = '';
            this.errorEmail = '';
            this.errorPassword = '';
            this.errorFk_rol = '';

            ///Persona

            this.errorNombrePersonal = '';
            this.errorCp = '';
            this.errorDireccion = '';
            this.errorTelefono = '';
            this.errorFk_sucursal = '';

            this.nombre = usuario.username;
            this.email = usuario.correo;
            this.avatar = usuario.avatar;
            this.estatus = usuario.estatus;
            this.previewAvatar = `/storage/${usuario.avatar}`; 
            this.fk_rol = usuario.id_rol;

            this.nombre_personal = usuario.nombre_personal;
            this.cp = usuario.cp;
            this.direccion = usuario.direccion;
            this.telefono = usuario.telefono;
            this.fk_sucursal = usuario.id_sucursal;

            this.mostrarModal = true;

            this.tab = 1;
        },


        async Actualizar() {

            try {

                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                this.errorNombre = '';
                this.errorEmail = '';
                this.errorPassword = '';
                this.errorFk_rol = '';

                if (!this.nombre.trim()) {
                    this.errorNombre = 'El nombre de usuario es obligatorio.';
                    return;
                }

                if (!this.email.trim()) {
                    this.errorEmail = 'El email es obligatorio.';
                    return;
                }

                if (!emailRegex.test(this.email)) {
                    this.errorEmail = 'Ingrese un correo electrónico válido.';
                    return;
                }

                if (!this.fk_rol) {
                    this.errorFk_rol = 'El rol es obligatorio.';
                    return;
                }
              
                if (this.password) {

                    if (this.password.length < 8) {
                        this.errorPassword = 'La contraseña debe tener al menos 8 caracteres.';
                        return;
                    }

                    if (!/[A-Z]/.test(this.password)) {
                        this.errorPassword = 'La contraseña debe contener al menos una mayúscula.';
                        return;
                    }

                    if (!/[!@#$%^&*()_\-+=\[\]{};:'",.<>/?\\|`~]/.test(this.password)) {
                        this.errorPassword = 'La contraseña debe contener al menos un carácter especial.';
                        return;
                    }

                }
                
                // if (!this.estatus.trim()) {
                //     this.Errorestatus = 'El estatus del usuario es obligatorio.';
                // return;
                // }
  
               const formData = new FormData();

                formData.append('_method', 'PUT');
                formData.append('id', this.idUsuario);
                formData.append('nombre', this.nombre);
                formData.append('email', this.email);
                formData.append('fk_rol', this.fk_rol);
                formData.append('estatus', this.estatus);

                 //////PERSONA

                formData.append('nombre_personal', this.nombre_personal);
                formData.append('cp', this.cp);
                formData.append('direccion', this.direccion);
                formData.append('telefono', this.telefono);
                formData.append('fk_sucursal', this.fk_sucursal);

                if (this.password) {
                    formData.append('password', this.password);
                }

                if (this.avatar instanceof File) {
                    formData.append('avatar', this.avatar);
                }

                await axios.post('/api/actualizarusr', formData);

                this.mostrarModal = false;

                Swal.fire({
                    icon: 'success',
                    title: 'Actualizado',
                    text: 'El usuario se actualizó correctamente'
                });

                await this.listado();

            } catch (error) {

             if (error.response && error.response.status === 422) {

                    const errors = error.response.data.errors;

                    if (errors.nombre) {
                        this.errorNombre = errors.nombre[0];
                    }

                    if (errors.email) {
                        this.errorEmail = errors.email[0];
                    }

                    if (errors.password) {
                        this.errorPassword = errors.password[0];
                    }

                    if (errors.fk_rol) {
                        this.errorFk_rol = errors.fk_rol[0];
                    }

                    if (errors.nombre_personal) {
                        this.errorNombrePersonal = errors.nombre_personal[0];
                    }

                    if (errors.cp) {
                        this.errorCp = errors.cp[0];
                    }

                    if (errors.direccion) {
                        this.errorDireccion = errors.direccion[0];
                    }

                    if (errors.telefono) {
                        this.errorTelefono = errors.telefono[0];
                    }

                    if (errors.fk_sucursal) {
                        this.errorFk_sucursal = errors.fk_sucursal[0];
                    }

                    return;
                }

            }

        },

        async Eliminar(usuario) {

            const result = await Swal.fire({
                title: `<p style="text-align: center; ">Estas seguro de eliminar el usuario: ${usuario.username}?</p>`,
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

                const response = await axios.delete('/api/usrdelete', {
                    data: {
                        id: usuario.id
                    }
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: response.data.message
                });

                this.listado();

            } catch (error) {

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.response?.data?.message || 'Ocurrió un error'
                });

            }
        },

        seleccionarAvatar(event) {

            const archivo = event.target.files[0];

            if (!archivo) {
                return;
            }

            const formatosPermitidos = [
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/webp'
            ];

            if (!formatosPermitidos.includes(archivo.type)) {
                this.errorAvatar = 'Seleccione una imagen válida.';
                event.target.value = '';
                return;
            }

            this.errorAvatar = '';
            this.avatar = archivo;

            this.previewAvatar = URL.createObjectURL(archivo);
        },

    }

}

</script>

<style>

.busqueda{

    width: 30% !important;

}

.modal-content-custom{
    background: #fff;
    border-radius: 12px;
    padding: 24px;
    width: 100%;
    max-width: 550px;
    box-shadow: 0 10px 30px rgba(0,0,0,.15);
}

.avatar-upload-container{
    display:flex;
    align-items:center;
    gap:20px;
}

.avatar-preview-wrapper{
    flex-shrink:0;
}

.avatar-preview,
.avatar-placeholder{
    width:110px;
    height:110px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid #f3f4f6;
    box-shadow:0 4px 15px rgba(0,0,0,.08);
}

.avatar-placeholder{
    display:flex;
    align-items:center;
    justify-content:center;
    background:#f8f9fa;
    color:#6c757d;
    font-size:2rem;
}

.avatar-input{
    border-radius:14px;
    padding:12px;
}
.avatar-tabla {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}
.spanactivo {
    background-color: #28a745;
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.spaninactivo {
    background-color: #dc3545;
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.modal-content-custom {
    background: #fff;
    width: 600px; 
    max-height: 95vh; 
    overflow-y: auto;
    border-radius: 10px;
    padding: 20px;
}
</style>