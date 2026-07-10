<template>

    <div>

        <div class="card-custom">

            <div class="table table-responsive">

                <div class="d-flex align-items-center gap-2">
                    <input type="text" placeholder="Busqueda" class="form-control busqueda" v-model="busqueda" @keyup.enter="Marcas">

                    <button
                        class="btn btn-primary ms-auto"
                        @click="abrirModalNuevo"
                    >
                        Registrar
                    </button>
                </div><br>

                <table class="table table-hover table-sm">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>NOMBRE</th>
                            <th>FECHA DE REGISTRO</th>
                            <th>ESTATUS</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-if="marcas.length === 0">
                            <td colspan="5" class="text-center">
                                No se encontraron registros
                            </td>
                        </tr>

                        <tr v-for="marca in marcas" :key="marca.id_marca">
                            <td>{{ marca.id_marca }}</td>
                            <td>{{ marca.nombre }}</td>
                            <td>{{ marca.created_at }}</td>
                            <td>
                                <span :class="marca.estatus ? 'badge bg-success' : 'badge bg-secondary'">
                                    {{ marca.estatus ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" @click="abrirModalEditar(marca)">
                                    Editar
                                </button>
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
                <h5>{{ modoEdicion ? 'Editar Marca' : 'Registrar Marca' }}</h5>

                <button
                    class="btn-close"
                    @click="cerrarModal"
                ></button>
            </div>

            <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': erroresCampo.nombre }"
                    placeholder="Nombre de la marca"
                    v-model="form.nombre"
                >
                <div v-if="erroresCampo.nombre" class="invalid-feedback d-block">
                    {{ erroresCampo.nombre }}
                </div>
            </div>

            <div v-if="error" class="alert alert-danger py-2">
                {{ error }}
            </div>

            <div class="text-end">
                <button
                    class="btn btn-secondary me-2"
                    @click="cerrarModal"
                >
                    Cancelar
                </button>

                <button class="btn btn-primary" @click="guardar" :disabled="guardando">
                    {{ guardando ? 'Guardando...' : 'Guardar' }}
                </button>
            </div>

        </div>
    </div>

</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      marcas: [],
      busqueda: '',
      mostrarModal: false,
      modoEdicion: false,
      guardando: false,
      error: null,
      erroresCampo: {},

      form: {
        id_marca: null,
        nombre: '',
      },
    };
  },

  mounted() {
    this.Marcas();
  },

  methods: {
    async Marcas() {
      try {
        const response = await axios.get('/api/marcas', {
          params: { busqueda: this.busqueda }
        });

        this.marcas = response.data.marcas;
      } catch (error) {
        console.log("Error cargando marcas:", error);
      }
    },

    abrirModalNuevo() {
      this.modoEdicion = false;
      this.resetForm();
      this.mostrarModal = true;
    },

    abrirModalEditar(marca) {
      this.modoEdicion = true;
      this.form = {
        id_marca: marca.id_marca,
        nombre: marca.nombre,
      };
      this.mostrarModal = true;
    },

    cerrarModal() {
      this.mostrarModal = false;
      this.error = null;
      this.erroresCampo = {};
    },

    resetForm() {
      this.form = {
        id_marca: null,
        nombre: '',
      };
    },

    validarFormulario() {
      this.erroresCampo = {};

      if (!this.form.nombre || !this.form.nombre.trim()) {
        this.erroresCampo.nombre = 'El nombre es obligatorio.';
      } else if (this.form.nombre.trim().length < 3) {
        this.erroresCampo.nombre = 'El nombre debe tener al menos 3 caracteres.';
      }

      return Object.keys(this.erroresCampo).length === 0;
    },

    async guardar() {
      this.error = null;

      if (!this.validarFormulario()) {
        return;
      }

      this.guardando = true;

      try {
        if (this.modoEdicion) {
          await axios.put(`/api/marcas/${this.form.id_marca}`, this.form);
        } else {
          await axios.post('/api/marcas', this.form);
        }

        this.cerrarModal();
        await this.Marcas();
      } catch (error) {
        if (error.response && error.response.data && error.response.data.errors) {
          const primerError = Object.values(error.response.data.errors)[0];
          this.error = primerError[0];
        } else {
          this.error = 'Ocurrió un error al guardar la marca';
        }
        console.log("Error guardando marca:", error);
      } finally {
        this.guardando = false;
      }
    },
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