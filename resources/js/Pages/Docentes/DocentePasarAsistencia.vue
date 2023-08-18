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
              <div class="text-bold">Pasar Asistencia</div>
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
      :mensaje="mensajeConfirmacion"
      @cerrar="mostrarModalConfirmar = false"
      @aceptar="guardarAsistencias()"
    />

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
import { obtenerFechaActualCompletaMostrar, obtenerFechaActualOperacion } from '../../Utils/date';
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
    }
  },
  methods: {
    confirmarAsistencias() {
      this.mensajeConfirmacion = this.verificarCantidadAsistencias();
      this.mostrarModalConfirmar = true;
    },
    verificarCantidadAsistencias() {
      const alumnosConAsistenciaCantidad = this.alumnosSeleccionados.length;
      const alumnosCantidad = this.alumnos.length;
      let mensajes = [];
      mensajes[0] = `Guardará asistencias con la siguiente información:`;
      mensajes[1] = `Fecha: ${obtenerFechaActualCompletaMostrar()}`;
      mensajes[2] = `Número de alumnos: ${alumnosCantidad}`;
      mensajes[3] = `Número de alumnos que asistieron: ${alumnosConAsistenciaCantidad}`;
      mensajes[4] = `Alumnos con asistencia: ${alumnosConAsistenciaCantidad} de ${alumnosCantidad}`;
      return mensajes.join('<br>');
    },
    guardarAsistencias() {
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
        fecha: obtenerFechaActualOperacion(),
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
    fechaActual() {
      let fecha = new Date();
      // let opciones = { day: '2-digit', month: '2-digit', year: 'numeric' };
      let opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
      let fechaFormateada = fecha.toLocaleDateString('es-MX', opciones);
      console.log(fechaFormateada);
      return fechaFormateada;
    }
  }
};
</script>
