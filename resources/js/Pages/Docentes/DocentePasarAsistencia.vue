<template>
  <MainLayout>
    <div class="q-pa-md">
      <q-table
        :rows="alumnos"
        :columns="columns"
        :rows-per-page-options="[10]"
        :filter="filter"
        class="tabla-listado striped-table"
        row-key="numestudiante"
        selection="multiple"
        v-model:selected="alumnosSeleccionados"
      >
        <template v-slot:top>
          <div class="row col-12">
            <div class="col-2">
              <div class="text-bold">Pasar Asistencia - {{ obtenerPeriodo }}</div>
              <div class="text-bold">{{ obtenerSemestreGrupo }}</div>
            </div>
            <div class="col q-my-auto">
              {{ obtenerLicenciaturaMateria }}
            </div>
            <div class="col-2">
              <q-input outlined dense debounce="300" v-model="filter" placeholder="Búsqueda">
                <template v-slot:append>
                  <q-icon name="search" />
                </template>
              </q-input>
            </div>
            <div class="col-1 text-right">
              <q-btn dense color="primary" flat round icon="save" @click="confirmarAsistencias()">
                <q-tooltip anchor="bottom left">
                  Guardar Asistencias
                </q-tooltip>
              </q-btn>
            </div>
          </div>
        </template>
      </q-table>
    </div>
    <!-- MODALES -->
    <!-- DIALOGO DE CONFIRMACION -->
    <the-dialog-confirm
      :mostrar="mostrarModalConfirmar"
      titulo="Confirmar Asistencias"
      @cerrar="mostrarModalConfirmar = false"
      @aceptar="guardarAsistencias()"
    >
      <template #body>
        <div class="row q-mb-md">
          <div class="col-2"></div>
          <div class="col-4 q-mt-sm">
            Fecha de Asistencia:
          </div>
          <div class="col-4">
            <q-input outlined dense v-model="fecha" mask="date" :rules="['date']">
              <template v-slot:append>
                <q-icon name="event" class="cursor-pointer">
                  <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                    <q-date v-model="fecha" :options="optionsFn">
                      <div class="row items-center justify-end">
                        <q-btn v-close-popup label="Close" color="primary" flat />
                      </div>
                    </q-date>
                  </q-popup-proxy>
                </q-icon>
              </template>
            </q-input>
          </div>
          <div class="col-2"></div>
        </div>
        
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
import { loading } from '../../Utils/loading';
import { obtenerFechaActualCompletaMostrar, obtenerFechaActualOperacion, esFechaValida } from '../../Utils/date';
import { notify } from '../../Utils/notify';
export default {
  props: ["alumnos", "status", "mensaje"],
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
      ],
      alumnosSeleccionados: [],
      mostrarModalConfirmar: false,
      mostrarModalExito: false,
      mensajeConfirmacion: "",
      fecha: obtenerFechaActualOperacion(),
      fechaActual: obtenerFechaActualOperacion(),
      optionsFn (date) {
        return date <= obtenerFechaActualOperacion()
      },
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
      if (this.alumnos.length == 0) {
        return '--';
      }
      const { periodo } = this.alumnos[0];
      return periodo;
    },
    obtenerSemestreGrupo() {
      if (this.alumnos.length == 0) {
        return '--';
      }

      const { semestre, grupo } = this.alumnos[0];
      let datos = [];
      datos[0] = 'Semestre: ' + semestre;
      datos[1] = 'Grupo: ' + grupo;

      return `${datos.join(' | ')}`;
    },
    obtenerLicenciaturaMateria() {
      if (this.alumnos.length == 0) {
        return '--';
      }

      const { licenciatura, materia } = this.alumnos[0];
      let datos = [];
      datos[0] = licenciatura;
      datos[1] = materia;

      return `${datos.join(' | ')}`;
    },
    obtenerMensajeConfirmacion() {
      const alumnosConAsistenciaCantidad = this.alumnosSeleccionados.length;
      const alumnosCantidad = this.alumnos.length;
      let mensajes = [];
      mensajes[0] = `Guardará asistencias con la siguiente información:<br>`;
      mensajes[1] = `Fecha: ${obtenerFechaActualCompletaMostrar()}`;
      mensajes[2] = `Número de alumnos: ${alumnosCantidad}`;
      mensajes[3] = `Número de alumnos que asistieron: ${alumnosConAsistenciaCantidad}`;
      mensajes[4] = `Alumnos con asistencia: ${alumnosConAsistenciaCantidad} de ${alumnosCantidad}`;
      return mensajes.join('<br>');
    },
  },
  methods: {
    confirmarAsistencias() {
      this.mostrarModalConfirmar = true;
    },
    guardarAsistencias() {
      if (!this.fecha) {
        return notify('Debe seleccionar una fecha', 'error');
      }
      const resValidacionFecha = esFechaValida(this.fecha);
      if (resValidacionFecha.status  >= 300) {
        return notify(resValidacionFecha.mensaje, 'error');
      }
      this.mostrarModalConfirmar = false;
      let alumnosCopia = [...this.alumnos];
      this.alumnosSeleccionados.forEach(as => {
        const index = alumnosCopia.findIndex(a => a.numestudiante == as.numestudiante);
        if (index >= 0) {
          alumnosCopia[index].asistencia = true;
        }
      });
      const { semestre, grupo, licenciatura, materia, clavemat, periodo } = this.alumnos[0];
      const form = {
        fecha: this.fecha,
        licenciatura,
        semestre,
        grupo,
        claveMateria: clavemat,
        alumnos: JSON.stringify(alumnosCopia),
        periodo,
        materia,
      };
      loading(true, 'Agregando ...');
      this.$inertia.post("/docente/pasarAsistencias", form);
    },
    irCargasAcademicas() {
      this.mostrarModalExito = false;
      loading(true);
      this.$inertia.get('/docente/cargasAcademicas');
    },
  }
};
</script>
