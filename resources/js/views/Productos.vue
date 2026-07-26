<template>

    <div>

        <div class="card-custom">

            <div class="table table-responsive">

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <input type="text" placeholder="Busqueda" class="form-control busqueda" v-model="busqueda" @keyup.enter="Productos">

                    <input
                        type="text"
                        placeholder="Escanear código de barras..."
                        class="form-control busqueda-codigo"
                        ref="inputCodigo"
                        v-model="codigoEscaneado"
                        @keyup.enter="buscarPorCodigo"
                    >

                    <button
                        class="btn btn-outline-secondary"
                        @click="abrirModalCamara"
                    >
                        📷 Escanear con cámara
                    </button>

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
                            <th>PRODUCTO</th>
                            <th>CATEGORÍA</th>
                            <th>MARCA</th>
                            <th>VARIANTES</th>
                            <th>ESTATUS</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-if="productos.length === 0">
                            <td colspan="7" class="text-center">
                                No se encontraron registros
                            </td>
                        </tr>

                        <tr v-for="producto in productos" :key="producto.id_producto">
                            <td>{{ producto.id_producto }}</td>
                            <td>{{ producto.nombre }}</td>
                            <td>{{ producto.categoria ? producto.categoria.nombre : '—' }}</td>
                            <td>{{ producto.marca ? producto.marca.nombre : '—' }}</td>
                            <td>
                                <div
                                    v-for="variante in producto.variantes"
                                    :key="variante.id_variante"
                                    class="mb-1"
                                >
                                    <span class="badge bg-primary">
                                        {{ etiquetaVariante(variante) }} — ${{ variante.precio_venta }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span :class="producto.estatus ? 'badge bg-success' : 'badge bg-secondary'">
                                    {{ producto.estatus ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" @click="abrirModalEditar(producto)">
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
        <div class="modal-content-custom modal-grande">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>{{ modoEdicion ? 'Editar Producto' : 'Registrar Producto' }}</h5>

                <button
                    class="btn-close"
                    @click="cerrarModal"
                ></button>
            </div>

            <!-- Datos generales del producto -->
            <div class="row mb-3">
                <div class="col-md-6 mb-2">
                    <label class="form-label">Nombre</label>
                    <input
                        type="text"
                        class="form-control"
                        :class="{ 'is-invalid': erroresCampo.nombre }"
                        placeholder="Nombre del producto"
                        v-model="form.nombre"
                    >
                    <div v-if="erroresCampo.nombre" class="invalid-feedback d-block">
                        {{ erroresCampo.nombre }}
                    </div>
                </div>

                <div class="col-md-3 mb-2">
                    <label class="form-label">Categoría</label>
                    <select class="form-select" v-model="form.fk_categoria">
                        <option :value="null">— Sin categoría —</option>
                        <option v-for="cat in categorias" :key="cat.id_categoria" :value="cat.id_categoria">
                            {{ cat.nombre }}
                        </option>
                    </select>
                </div>

                <div class="col-md-3 mb-2">
                    <label class="form-label">Marca</label>
                    <select class="form-select" v-model="form.fk_marca">
                        <option :value="null">— Sin marca —</option>
                        <option v-for="m in marcas" :key="m.id_marca" :value="m.id_marca">
                            {{ m.nombre }}
                        </option>
                    </select>
                </div>

                <div class="col-md-8 mb-2">
                    <label class="form-label">Descripción</label>
                    <input type="text" class="form-control" placeholder="Descripción (opcional)" v-model="form.descripcion">
                </div>

                <div class="col-md-2 mb-2">
                    <label class="form-label">Unidad</label>
                    <input
                        type="text"
                        class="form-control"
                        :class="{ 'is-invalid': erroresCampo.unidad_medida }"
                        placeholder="pieza, kg..."
                        v-model="form.unidad_medida"
                    >
                    <div v-if="erroresCampo.unidad_medida" class="invalid-feedback d-block">
                        {{ erroresCampo.unidad_medida }}
                    </div>
                </div>

                <div class="col-md-2 mb-2 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" v-model="form.aplica_iva" id="checkIva">
                        <label class="form-check-label" for="checkIva">Aplica IVA</label>
                    </div>
                </div>

                <div class="col-md-4 mb-2">
                    <label class="form-label">Imagen del producto</label>
                    <input
                        type="file"
                        class="form-control"
                        accept="image/*"
                        @change="onImagenSeleccionada"
                    >
                    <img
                        v-if="previewImagen"
                        :src="previewImagen"
                        class="mt-2"
                        style="max-height: 80px; border-radius: 6px;"
                    >
                </div>
            </div>

            <!-- Switch: producto simple vs con variantes -->
            <div class="form-check form-switch mb-3" v-if="!modoEdicion">
                <input
                    class="form-check-input"
                    type="checkbox"
                    id="checkVariantes"
                    v-model="form.maneja_variantes"
                    @change="alCambiarManejaVariantes"
                >
                <label class="form-check-label" for="checkVariantes">
                    Este producto maneja variantes (talla / color)
                </label>
            </div>

            <!-- Tabla de variantes -->
            <div v-if="!modoEdicion">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">
                        {{ form.maneja_variantes ? 'Variantes del producto' : 'Datos de venta' }}
                    </label>

                    <button
                        v-if="form.maneja_variantes"
                        class="btn btn-sm btn-outline-primary"
                        @click="agregarFilaVariante"
                    >
                        + Agregar variante
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th v-if="form.maneja_variantes">Talla</th>
                                <th v-if="form.maneja_variantes">Color</th>
                                <th>SKU</th>
                                <th>Código de barras</th>
                                <th>Precio compra</th>
                                <th>Precio venta</th>
                                <th v-if="form.maneja_variantes"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(variante, index) in form.variantes" :key="index">
                                <td v-if="form.maneja_variantes">
                                    <input type="text" class="form-control form-control-sm"
                                        :class="{ 'is-invalid': erroresVariantes[index]?.combinacion }"
                                        placeholder="M, L, XL..." v-model="variante.talla">
                                </td>
                                <td v-if="form.maneja_variantes">
                                    <input type="text" class="form-control form-control-sm"
                                        :class="{ 'is-invalid': erroresVariantes[index]?.combinacion }"
                                        placeholder="Negro, Azul..." v-model="variante.color">
                                    <div v-if="erroresVariantes[index]?.combinacion" class="invalid-feedback d-block">
                                        {{ erroresVariantes[index].combinacion }}
                                    </div>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm"
                                        :class="{ 'is-invalid': erroresVariantes[index]?.sku }"
                                        placeholder="SKU único" v-model="variante.sku">
                                    <div v-if="erroresVariantes[index]?.sku" class="invalid-feedback d-block">
                                        {{ erroresVariantes[index].sku }}
                                    </div>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" placeholder="Código de barras" v-model="variante.codigo">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm"
                                        :class="{ 'is-invalid': erroresVariantes[index]?.precio_compra }"
                                        v-model.number="variante.precio_compra">
                                    <div v-if="erroresVariantes[index]?.precio_compra" class="invalid-feedback d-block">
                                        {{ erroresVariantes[index].precio_compra }}
                                    </div>
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control form-control-sm"
                                        :class="{ 'is-invalid': erroresVariantes[index]?.precio_venta }"
                                        v-model.number="variante.precio_venta">
                                    <div v-if="erroresVariantes[index]?.precio_venta" class="invalid-feedback d-block">
                                        {{ erroresVariantes[index].precio_venta }}
                                    </div>
                                </td>
                                <td v-if="form.maneja_variantes">
                                    <button class="btn btn-sm btn-outline-danger" @click="quitarFilaVariante(index)" :disabled="form.variantes.length === 1">
                                        ×
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
<!-- Modal: resultado de la búsqueda por código de barras -->
    <div v-if="mostrarResultadoCodigo" class="modal-overlay">
        <div class="modal-content-custom">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>Producto encontrado</h5>
                <button class="btn-close" @click="mostrarResultadoCodigo = false"></button>
            </div>

            <div v-if="productoEncontrado">
                <p class="mb-1"><strong>Producto:</strong> {{ productoEncontrado.producto.nombre }}</p>
                <p class="mb-1" v-if="productoEncontrado.talla || productoEncontrado.color">
                    <strong>Variante:</strong> {{ etiquetaVariante(productoEncontrado) }}
                </p>
                <p class="mb-1"><strong>SKU:</strong> {{ productoEncontrado.sku }}</p>
                <p class="mb-1"><strong>Precio de venta:</strong> ${{ productoEncontrado.precio_venta }}</p>
                <p class="mb-0"><strong>Categoría:</strong> {{ productoEncontrado.producto.categoria?.nombre || '—' }}</p>
            </div>

            <div v-else class="alert alert-warning mb-0">
                No se encontró ningún producto con ese código.
            </div>

            <div class="text-end mt-3">
                <button class="btn btn-secondary" @click="mostrarResultadoCodigo = false">
                    Cerrar
                </button>
            </div>

        </div>
    </div>

    <!-- Modal: escanear con cámara -->
    <div v-if="mostrarModalCamara" class="modal-overlay">
        <div class="modal-content-custom">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>Escanear código de barras</h5>
                <button class="btn-close" @click="cerrarModalCamara"></button>
            </div>

            <div id="lector-camara" style="width: 100%;"></div>

            <p class="text-muted small mt-2 mb-0">
                Apunta la cámara hacia el código de barras del producto.
            </p>

        </div>
    </div>

</template>

<script>
import axios from "axios";
import { Html5Qrcode } from "html5-qrcode";

export default {
    data() {
        return {
        productos: [],
        categorias: [],
        marcas: [],
        busqueda: '',
        mostrarModal: false,
        modoEdicion: false,
        guardando: false,
        error: null,
        erroresCampo: {},
        erroresVariantes: [],

        imagenSeleccionada: null,
        previewImagen: null,

        codigoEscaneado: '',
        productoEncontrado: null,
        mostrarResultadoCodigo: false,
        mostrarModalCamara: false,
        lectorCamara: null,

        form: this.formVacio(),
        };
    },

  mounted() {
    this.Productos();
  },

  methods: {
    formVacio() {
      return {
        id_producto: null,
        nombre: '',
        descripcion: '',
        fk_categoria: null,
        fk_marca: null,
        unidad_medida: 'pieza',
        maneja_variantes: false,
        aplica_iva: true,
        variantes: [this.filaVacia()],
      };
    },

    filaVacia() {
      return {
        talla: '',
        color: '',
        sku: '',
        codigo: '',
        precio_compra: 0,
        precio_venta: 0,
      };
    },

    etiquetaVariante(variante) {
      const partes = [variante.talla, variante.color].filter(Boolean);
      return partes.length ? partes.join(' / ') : variante.sku;
    },

    async Productos() {
      try {
        const response = await axios.get('/api/productos', {
          params: { busqueda: this.busqueda }
        });

        this.productos = response.data.productos;
      } catch (error) {
        console.log("Error cargando productos:", error);
      }
    },

    async cargarDatosFormulario() {
      try {
        const response = await axios.get('/api/productos/datos-formulario');
        this.categorias = response.data.categorias;
        this.marcas = response.data.marcas;
      } catch (error) {
        console.log("Error cargando datos del formulario:", error);
      }
    },

    async abrirModalNuevo() {
        this.modoEdicion = false;
        this.form = this.formVacio();
        this.imagenSeleccionada = null;
        this.previewImagen = null;
        this.mostrarModal = true;
        await this.cargarDatosFormulario();
    },

    async abrirModalEditar(producto) {
      // En edición solo se tocan los datos generales del producto.
      // Las variantes se gestionan en su propia pantalla/modal
      // para no complicar este formulario (ver nota en el controlador).
      this.modoEdicion = true;
      this.imagenSeleccionada = null;
      this.previewImagen = producto.imagen_principal || null;
      this.form = {
        id_producto: producto.id_producto,
        nombre: producto.nombre,
        descripcion: producto.descripcion,
        fk_categoria: producto.fk_categoria,
        fk_marca: producto.fk_marca,
        unidad_medida: producto.unidad_medida,
        maneja_variantes: !!producto.maneja_variantes,
        aplica_iva: !!producto.aplica_iva,
        variantes: [],
      };
      this.mostrarModal = true;
      await this.cargarDatosFormulario();
    },

    cerrarModal() {
      this.mostrarModal = false;
      this.error = null;
      this.erroresCampo = {};
      this.erroresVariantes = [];
    },

    alCambiarManejaVariantes() {
      // Al activar/desactivar variantes, reinicia a una sola fila limpia
      this.form.variantes = [this.filaVacia()];
    },

    agregarFilaVariante() {
      this.form.variantes.push(this.filaVacia());
    },

    quitarFilaVariante(index) {
      this.form.variantes.splice(index, 1);
    },

    onImagenSeleccionada(event) {
    const archivo = event.target.files[0];
    if (!archivo) return;

    this.imagenSeleccionada = archivo;
    this.previewImagen = URL.createObjectURL(archivo);
    },

    async buscarPorCodigo() {
    if (!this.codigoEscaneado.trim()) return;

    try {
        const response = await axios.get('/api/productos/buscar-por-codigo', {
        params: { codigo: this.codigoEscaneado.trim() }
        });

        this.productoEncontrado = response.data.variante;
    } catch (error) {
        this.productoEncontrado = null;
        console.log("Código no encontrado:", error);
    }

    this.mostrarResultadoCodigo = true;
    this.codigoEscaneado = '';
    },

    abrirModalCamara() {
    this.mostrarModalCamara = true;

    // Espera a que el modal se pinte en el DOM antes de iniciar la cámara
    this.$nextTick(() => {
        this.lectorCamara = new Html5Qrcode("lector-camara");

        this.lectorCamara.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: 250 },
        (codigoDetectado) => {
            this.codigoEscaneado = codigoDetectado;
            this.cerrarModalCamara();
            this.buscarPorCodigo();
        },
        () => {
            // Se llama constantemente mientras no detecta nada; lo ignoramos.
        }
        ).catch((err) => {
        console.log("No se pudo iniciar la cámara:", err);
        this.mostrarModalCamara = false;
        });
    });
    },

    cerrarModalCamara() {
    if (this.lectorCamara) {
        this.lectorCamara.stop().catch(() => {});
        this.lectorCamara = null;
    }
    this.mostrarModalCamara = false;
    },

    validarFormulario() {
      this.erroresCampo = {};
      this.erroresVariantes = [];

      if (!this.form.nombre || !this.form.nombre.trim()) {
        this.erroresCampo.nombre = 'El nombre del producto es obligatorio.';
      }

      if (!this.form.unidad_medida || !this.form.unidad_medida.trim()) {
        this.erroresCampo.unidad_medida = 'La unidad es obligatoria.';
      }

      const skusVistos = new Set();

      this.form.variantes.forEach((v, index) => {
        const erroresFila = {};

        if (!v.sku || !v.sku.trim()) {
          erroresFila.sku = 'El SKU es obligatorio.';
        } else if (skusVistos.has(v.sku.trim())) {
          erroresFila.sku = 'Este SKU ya se usó en otra fila.';
        } else {
          skusVistos.add(v.sku.trim());
        }

        if (this.form.maneja_variantes && !v.talla?.trim() && !v.color?.trim()) {
          erroresFila.combinacion = 'Define talla o color para esta variante.';
        }

        if (v.precio_compra === '' || v.precio_compra === null || v.precio_compra < 0) {
          erroresFila.precio_compra = 'Precio de compra inválido.';
        }

        if (v.precio_venta === '' || v.precio_venta === null || v.precio_venta <= 0) {
          erroresFila.precio_venta = 'El precio de venta debe ser mayor a 0.';
        }

        this.erroresVariantes[index] = erroresFila;
      });

      const hayErroresVariantes = this.erroresVariantes.some(
        fila => Object.keys(fila).length > 0
      );

      return Object.keys(this.erroresCampo).length === 0 && !hayErroresVariantes;
    },

    async guardar() {
        this.error = null;

        if (!this.validarFormulario()) {
          return;
        }

        this.guardando = true;

        try {
            const formData = new FormData();
            formData.append('fk_categoria', this.form.fk_categoria ?? '');
            formData.append('fk_marca', this.form.fk_marca ?? '');
            formData.append('nombre', this.form.nombre);
            formData.append('descripcion', this.form.descripcion ?? '');
            formData.append('unidad_medida', this.form.unidad_medida);
            formData.append('aplica_iva', this.form.aplica_iva ? 1 : 0);

            if (this.imagenSeleccionada) {
            formData.append('imagen_principal', this.imagenSeleccionada);
            }

            if (this.modoEdicion) {
            formData.append('_method', 'PUT');
            await axios.post(`/api/productos/${this.form.id_producto}`, formData);
            } else {
            formData.append('maneja_variantes', this.form.maneja_variantes ? 1 : 0);

            this.form.variantes.forEach((v, i) => {
                formData.append(`variantes[${i}][talla]`, v.talla || '');
                formData.append(`variantes[${i}][color]`, v.color || '');
                formData.append(`variantes[${i}][sku]`, v.sku);
                formData.append(`variantes[${i}][precio_compra]`, v.precio_compra);
                formData.append(`variantes[${i}][precio_venta]`, v.precio_venta);
                if (v.codigo) {
                formData.append(`variantes[${i}][codigos][0]`, v.codigo);
                }
            });

            await axios.post('/api/productos', formData);
            }

            this.cerrarModal();
            await this.Productos();
        } catch (error) {
            if (error.response && error.response.data && error.response.data.errors) {
            const primerError = Object.values(error.response.data.errors)[0];
            this.error = primerError[0];
            } else {
            this.error = 'Ocurrió un error al guardar el producto';
            }
            console.log("Error guardando producto:", error);
        } finally {
            this.guardando = false;
        }
    },
  },
};
</script>

<style scoped>

.busqueda-codigo{
    width: 35% !important;
}

.card-custom{
    padding:20px;
    border-radius:10px;
    box-shadow:0 2px 5px rgba(0,0,0,.1);
}

.busqueda{
    width: 30% !important;
}

.modal-grande{
    width: 800px !important;
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

body.dark-theme .form-control,
body.dark-theme .form-select{
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
    max-height: 90vh;
    overflow-y: auto;
}

body.dark-theme .modal-content-custom{
    background:#111827;
    color:white;
}
</style>