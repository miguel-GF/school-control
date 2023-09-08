<template>
  <MainLayout>
    <div class="q-pa-md">
      <q-form ref="form" @submit.prevent="confirmarCaptura()">
        <input ref="inputSubmit" hidden type="submit">
        <q-table
          :rows="calificaciones"
          :columns="columns"
          :rows-per-page-options="[10]"
          :filter="filter"
          class="tabla-listado striped-table"
          row-key="numestudiante"
        >
          <template v-slot:top>
            <div class="row col-12">
              <div class="col-sm-6 col-md">
                <div class="text-bold">Capturar Calificaciones - {{ obtenerPeriodo }}</div>
                <div class="text-bold">{{ obtenerSemestreGrupo }}</div>
              </div>
              <div class="col-sm-6 col-md-4 q-my-auto ellipsis-2-lines text-left">
                {{ obtenerLicenciaturaMateria }}
              </div>
              <div class="col-sm-4 col-md-3 q-pl-md">
                <q-input outlined dense debounce="300" v-model="filter" placeholder="Búsqueda">
                  <template v-slot:append>
                    <q-icon name="search" />
                  </template>
                </q-input>
              </div>
              <div class="col-sm-4 col-md-1 text-right">
                <q-btn dense color="primary" flat round icon="save" @click="$refs.inputSubmit.click()">
                  <q-tooltip anchor="bottom left">
                    Guardar Calificaciones
                  </q-tooltip>
                </q-btn>
              </div>
            </div>
          </template>
          <template v-slot:body-cell-primerParcial="props">
            <q-td :props="props">
              <q-input
                max="10"
                min="0"
                v-model.number="props.row.primerparcial"
                type="number"
                step="any"
                id="primerParcial"
                dense
                outlined
                placeholder=""
                :rules="[val => validarDatoCalificacion(val)]"
              />
            </q-td>
          </template>
          <template v-slot:body-cell-segundoParcial="props">
            <q-td :props="props">
              <q-input
                max="10"
                min="0"
                v-model.number="props.row.segundoparcial"
                type="number"
                step="any"
                id="segundoParcial"
                dense
                outlined
                placeholder=""
                :rules="[val => validarDatoCalificacion(val)]"
              />
            </q-td>
          </template>
          <template v-slot:body-cell-ordinario="props">
            <q-td :props="props">
              <q-input
                max="10"
                min="0"
                v-model.number="props.row.ordinario"
                type="number"
                step="any"
                id="ordinario"
                dense
                outlined
                placeholder=""
                :rules="[val => validarDatoCalificacion(val)]"
              />
            </q-td>
          </template>
          <template v-slot:body-cell-extraordinario="props">
            <q-td :props="props">
              <q-input
                max="10"
                min="0"
                v-model.number="props.row.extraordinario"
                type="number"
                step="any"
                id="extraordinario"
                dense
                outlined
                placeholder=""
                :rules="[val => validarDatoCalificacion(val)]"
              />
            </q-td>
          </template>
          <template v-slot:body-cell-final="props">
            <q-td :props="props">
              <q-input
                max="10"
                min="0"
                v-model.number="props.row.final"
                type="number"
                step="any"
                id="final"
                dense
                outlined
                placeholder=""
                :rules="[val => validarDatoCalificacion(val)]"
              />
            </q-td>
          </template>
        </q-table>
      </q-form>
    </div>
    <!-- MODALES -->
    <!-- DIALOGO DE CONFIRMACION -->
    <the-dialog-confirm
      :mostrar="mostrarModalConfirmar"
      titulo="Confirmar Calificaciones"
      @cerrar="mostrarModalConfirmar = false"
      @aceptar="guardarCalificaciones()"
    >
      <template #body>
        <q-banner rounded class="col-12 bg-orange-4" v-html="obtenerMensajeConfirmacion">
        </q-banner>
      </template>
    </the-dialog-confirm>

    <!-- DIALOGO DE EXITO -->
    <the-dialog-response
      :mostrar="mostrarModalExito"
      :mensaje="mensaje"
      @aceptar="irCargasAcademicas()"
    />
  </MainLayout>
</template>

<script>
import MainLayout from '../../Layouts/MainLayout.vue';
import { obtenerFechaActualOperacion } from '../../Utils/date';
import { loading } from '../../Utils/loading';
import { notify } from '../../Utils/notify';
export default {
  name: "DocenteCapturarCalificaciones",
  props: ["calificaciones", "status", "mensaje", "idCargaAcademica"],
  components: { MainLayout },
  data() {
    return {
      filter: "",
      columns: [
        {
          name: 'numeroEstudiante',
          label: '# Alumno',
          align: 'left',
          field: row => row.numestudiante,
          format: val => val ?? '--',
          sortable: true
        },
        {
          name: 'alumno',
          label: 'Alumno',
          align: 'left',
          field: row => row.alumno_nombre,
          format: val => val ?? '--',
          sortable: true
        },
        {
          name: 'primerParcial',
          label: '1° Parcial',
          align: 'left',
          field: row => row.primerparcial,
          format: val => val ?? '',
          sortable: false
        },
        {
          name: 'segundoParcial',
          label: '2° Parcial',
          align: 'left',
          field: row => row.segundoparcial,
          format: val => val ?? '',
          sortable: false
        },
        {
          name: 'ordinario',
          label: 'Ordinario',
          align: 'left',
          field: row => row.ordinario,
          format: val => val ?? '',
          sortable: false
        },
        {
          name: 'extraordinario',
          label: 'Extraordinario',
          align: 'left',
          field: row => row.extraordinario,
          format: val => val ?? '',
          sortable: false
        },
        {
          name: 'final',
          label: 'Final',
          align: 'left',
          field: row => row.final,
          format: val => val ?? '',
          sortable: false
        },
      ],
      calificacionesSeleccionados: [],
      mostrarModalConfirmar: false,
      mostrarModalExito: false,
      mensajeConfirmacion: "",
      fecha: obtenerFechaActualOperacion(),
    }
  },
  created() {
    loading(false);
  },
  updated() {
    loading(false);
    if (this.status == 200) {
      this.mostrarModalExito = true;
    }
  },
  computed: {
    obtenerPeriodo() {
      if (this.calificaciones.length == 0) {
        return '--';
      }
      const { periodo } = this.calificaciones[0];
      return periodo;
    },
    obtenerSemestreGrupo() {
      if (this.calificaciones.length == 0) {
        return '--';
      }

      const { semestre, grupo } = this.calificaciones[0];
      let datos = [];
      datos[0] = 'Semestre: ' + semestre;
      datos[1] = 'Grupo: ' + grupo;

      return `${datos.join(' | ')}`;
    },
    obtenerLicenciaturaMateria() {
      if (this.calificaciones.length == 0) {
        return '--';
      }

      const { licenciatura, materia } = this.calificaciones[0];
      let datos = [];
      datos[0] = licenciatura;
      datos[1] = materia;

      return `${datos.join(' | ')}`;
    },
    obtenerMensajeConfirmacion() {
      let mensajes = [];
      mensajes[0] = `Guardará calificaciones de la siguiente asignatura:<br>`;
      mensajes[1] = this.obtenerLicenciaturaMateria;
      mensajes[2] = this.obtenerPeriodo;
      mensajes[3] = this.obtenerSemestreGrupo;
      mensajes[4] = `Número de Alumnos: ${this.calificaciones?.length}`;
      return mensajes.join('<br>');
    },
  },
  methods: {
    confirmarCaptura() {
      try {
        this.mostrarModalConfirmar = true;
      } catch (error) {
        return notify(error, 'error');
      }
    },
    guardarCalificaciones() {
      this.mostrarModalConfirmar = false;
      const form = {
        calificaciones: JSON.stringify(this.calificaciones),
        idCargaAcademica: this.idCargaAcademica,
        fecha: this.fecha,
      };
      loading(true, 'Agregando ...');
      this.$inertia.post("/docente/guardarCalificaciones", form);
    },
    irCargasAcademicas() {
      this.mostrarModalExito = false;
      loading(true);
      this.$inertia.get('/docente/cargasAcademicas');
    },
    validarDatoCalificacion(val) {
      if (val != "") {
        if (Number(val) > 10) {
          return "No puede ser mayor a 10";
        }
        if (Number(val) < 0) {
          return "No puede ser menor a 0";
        }
      }
      return true;
    },
  }
};
</script>
