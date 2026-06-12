<template>

    <div>

        <div class="table table-responsive">

                <div class="d-flex gap-2">

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

                        <th>Empresa</th>
                        <th>Rfc</th>
                        <th>Correo</th>
                        <th>Direccion</th>
                        <th>Telefono</th>
                        <th>Acciones</th>   

                    </tr>

                </thead>

                <tbody>

                    <tr v-if="companias.length === 0">
                        <td colspan="7" class="text-center">
                            No se encontraron registros
                        </td>
                    </tr>
                
                    <tr v-for="compania in companias" :key="compania.id">

                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img
                                    :src="compania.logo ? `/storage/${compania.logo}` : '/images/users_default.png'"
                                    alt="Logo"
                                    class="avatar-tabla"
                                >
                                <span>{{ compania.nombre }}</span>
                            </div>
                        </td>
                        <td>{{ compania.rfc }} </td>
                        <td>{{ compania.email }}</td>
                        <td>{{ compania.direccion }}</td>
                        <td>{{ compania.telefono }}</td>
        
                        <td>

                            <div>
                                
                                <button class="btn btn-sm btn-primary me-1" @click="editarCompania(compania)">

                                    <i class="fa-solid fa-pencil"></i>

                                </button>

                                <button class="btn btn-sm btn-danger me-1" @click="Eliminar(compania)">

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

                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-4">
                    <h5 class="mb-0">
                        <i
                            class="me-2"
                            :class="editando ? 'fa-solid fa-user-pen' : 'fa-solid fa-user-plus'"
                        ></i>
                            {{ editando ? 'Editar Compania' : 'Registrar Compania' }}
                    </h5>

                    <button
                        class="btn-close"
                        @click="mostrarModal = false"
                    ></button>
                </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold mb-2">
                            Logo
                        </label>

                        <div class="avatar-upload-container">

                            <div class="avatar-preview-wrapper">
                                <img
                                    v-if="previewLogo"
                                    :src="previewLogo"
                                    class="avatar-preview"
                                >

                                <div
                                    v-else
                                    class="avatar-placeholder"
                                >
                                    <i class="fa-brands fa-amazon"></i>
                                </div>
                            </div>

                            <div class="flex-grow-1">

                                <input
                                    type="file"
                                    class="form-control avatar-input"
                                    accept="image/*"
                                    @change="seleccionarImg"
                                >

                                <small class="text-muted d-block mt-2">
                                    JPG, JPEG, PNG o WEBP (Máx. 2 MB)
                                </small>

                                <small
                                    v-if="errorLogo"
                                    class="text-danger d-block mt-1"
                                >
                                    {{ errorLogo }}
                                </small>

                            </div>

                        </div>

                    <div class="row mb-4">
                    
                        <div class="col-md-6">

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

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">
                                Rfc
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                placeholder="Ingrese el rfc"
                                v-model="rfc"
                                :class="{ 'is-invalid': errorRfc }"
                            >

                            <small v-if="errorRfc" class="text-danger">
                                {{ errorRfc }}
                            </small>

                        </div>

                    </div>

                     <div class="row mb-4">

                        <div class="col-md-6">
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

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Telefono
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                v-model="telefono"
                                :class="{ 'is-invalid': errorTelefono }"
                                autocomplete="off"
                            >

                            <small v-if="errorTelefono" class="text-danger">
                                {{ errorTelefono }}
                            </small>
                        </div>

                        <div class="col-md"><br>

                            <input v-model="direccion" placeholder="Direccion" :class="{ 'is-invalid': errorDireccion }" class="form-control">
                            <small v-if="errorDireccion" class="text-danger">
                                    {{ errorDireccion }}
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

                    <div class="d-flex gap-2">

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

export default {

    data() {
        return {
            companias: [],
            busqueda: '',

            rfc: '',
            nombre: '',
            email: '',
            telefono: '',
            direccion: '',

            mostrarModal: false,

            // Validaciones
            errorTelefono: '',
            errorDireccion: '',
            errorNombre: '',
            errorEmail: '',

            errorRfc: '',

            /////IMg
            previewLogo: null,
            errorLogo: '',
            logo: null,

            ///Edicion
            editando: false,

        }
    },

    mounted() {
        this.listado();
    },

    methods: {

        async listado() {
            try {
                const response = await axios.get('/api/companias',{

                    params:{

                        busqueda: this.busqueda,

                    }

                });

                this.companias = response.data.companias;


            } catch (error) {
                console.error('Error al obtener companias:', error);
            }
        },

        async Registro() {

            try {

                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                this.errorNombre = '';
                this.errorEmail = '';
                this.errorRfc = '';
                this.errorTelefono = '';
      

                if (!this.nombre.trim()) {
                    this.errorNombre = 'El nombre de la compania es obligatorio.';
                return;
                }
                if (!this.rfc.trim()) {
                    this.errorRfc = 'El rfc de la compania es obligatorio.';
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

                if (!this.telefono.trim()) {
                    this.errorTelefono = 'El telefono es obligatorio.';
                return;
                }

                if (!this.direccion.trim()) {
                    this.errorDireccion = 'La direccion es obligatorio.';
                return;
                }
                const formData = new FormData();

                formData.append('nombre', this.nombre);
                formData.append('email', this.email);
                formData.append('rfc', this.rfc);
                formData.append('telefono', this.telefono);
                formData.append('direccion', this.direccion);


                if (this.logo) {
                    formData.append('logo', this.logo);
                }

                const response = await axios.post(
                    '/api/companias/store',
                    formData,
                );

                console.log(response.data);
                this.limpiarFormulario();

                Swal.fire({
                    icon: 'success',
                    title: 'Compania registrada exitosamente',
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

                    if (errors.direccion) {
                        this.errorDireccion = errors.direccion[0];
                    }

                    if (errors.telefono) {
                        this.errorTelefono = errors.telefono[0];
                    }

                    return;
                }

                console.error('Error al registrar compania:', error);
            }
        },

        async abrirModal() {

            this.limpiarFormulario();

            this.mostrarModal = true;

        },
        limpiarFormulario() {

            this.editando = false;

            this.nombre = '';
            this.email = '';
            this.rfc = '';
            this.logo = null,
            this.direccion = '';
            this.telefono = '';

            this.errorNombre = '';
            this.errorEmail = '';
            this.errorDireccion = '';
            this.errorTelefono = '';
            this.errorRfc = '',

            this.previewLogo = null;
            this.errorLogo = '';
            this.logo = '';

            mostrarModal: false;
            
        },

        async editarCompania(compania) {

            this.editando = true;

            this.idCompania = compania.id_compania;

            this.errorNombre = '';
            this.errorEmail = '';
            this.errorDireccion = '';
            this.errorTelefono = '';
            this.errorRfc = '',

            this.nombre = compania.nombre;
            this.email = compania.email;
            this.rfc = compania.rfc;
            this.logo = compania.logo;
            this.previewLogo = `/storage/${compania.logo}`; 
            this.direccion = compania.direccion;
            this.telefono = compania.telefono;

            this.mostrarModal = true;

        },


        async Actualizar() {

            try {

                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                this.errorNombre = '';
                this.errorEmail = '';
                this.errorRfc = '';
                this.errorTelefono = '';
      

                if (!this.nombre.trim()) {
                    this.errorNombre = 'El nombre de la compania es obligatorio.';
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

                if (!this.telefono.trim()) {
                    this.errorTelefono = 'El telefono es obligatorio.';
                return;
                }

                if (!this.direccion.trim()) {
                    this.errorDireccion = 'La direccion es obligatorio.';
                return;
                }

               const formData = new FormData();

                formData.append('_method', 'PUT');
                formData.append('id', this.idCompania);
                formData.append('nombre', this.nombre);
                formData.append('email', this.email);
                formData.append('direccion', this.direccion);
                formData.append('telefono', this.telefono);
                formData.append('rfc', this.rfc);

                if (this.logo instanceof File) {
                    formData.append('logo', this.logo);
                }

                await axios.post('/api/actualizar/comp', formData);

                this.mostrarModal = false;

                Swal.fire({
                    icon: 'success',
                    title: 'Actualizado',
                    text: 'La compania se actualizó correctamente'
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

                    if (errors.rfc) {
                        this.errorRfc = errors.rfc[0];
                    }

                    if (errors.direccion) {
                        this.errorDireccion = errors.direccion[0];
                    }

                    if (errors.telefono) {
                        this.errorTelefono = errors.telefono[0];
                    }

                    return;
                }

            }

        },

        async Eliminar(compania) {

            const result = await Swal.fire({
                title: `<p style="text-align: center; ">Estas seguro de eliminar la compania: ${compania.nombre}?</p>`,
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

                const response = await axios.delete('/api/eliminar/comp', {
                    data: {
                        id: compania.id_compania
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

        seleccionarImg(event) {

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
                this.errorLogo = 'Seleccione una imagen válida.';
                event.target.value = '';
                return;
            }

            this.errorLogo = '';
            this.logo = archivo;

            this.previewLogo = URL.createObjectURL(archivo);
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