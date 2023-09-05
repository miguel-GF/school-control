<template>
  <MainLayout>
    <div class="q-pa-md">
      <q-table 
        :rows="asistencias"
        :columns="columns"
        :rows-per-page-options="[10]"
        :filter="filter"
        class="tabla-listado striped-table"
        row-key="cvemat" 
      >
        <template v-slot:top-left>
          <div class="row">
            <div class="text-h6 q-pr-md q-my-auto">Reporte de Asistencias<br> {{ fecha || '--' }}</div>
            <div class="q-pr-md ellipsis-2-lines q-pt-sm">{{ datos || '--' }}</div>
          </div>
        </template>
        <template v-slot:top-right>
          <q-btn @click="regresar()" dense icon-right="chevron_left" color="secondary"
            class="q-mr-md"
          >
            <div class="q-px-sm">Regresar</div>
          </q-btn>
          <q-input outlined dense debounce="300" v-model="filter" placeholder="Búsqueda">
            <template v-slot:append>
              <q-icon name="search" />
            </template>
          </q-input>
        </template>
      </q-table>
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from '../../Layouts/MainLayout.vue';
import { loading } from '../../Utils/loading';
import { notify } from "../../Utils/notify.js";
import { obtenerFechaActualOperacion, esFechaValida } from '../../Utils/date';
export default {
  name: "DocenteAsistenciasReporte",
  props: ["asistencias", "usuario", "datos"],
  components: { MainLayout },
  data() {
    return {
      filter: "",
      columns: [
        {
          name: 'numeroAlumno',
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
          field: row => row.nombre,
          format: val => val ?? '--',
          sortable: true
        },
        {
          name: 'asistencia',
          label: 'Asistencia',
          align: 'left',
          field: row => row.asistencia_nombre,
          format: val => val ?? '--',
          sortable: true
        },
      ],
      fecha: obtenerFechaActualOperacion(),
      optionsFn (date) {
        return date <= obtenerFechaActualOperacion()
      },
    }
  },
  created() {
    loading(false);
  },
  updated() {
    const { status, mensaje } = this.$page.props;
    if (status == 200) {
      this.showNotify(mensaje, 'exito');
    }
  },
  methods: {
    showNotify (message, tipo) {
      return notify(message, tipo);
    },
    filtrarCargas() {
      setTimeout(() => {
        loading(true);
        const currentRoute = window.location.href;
        this.$inertia.get(currentRoute, this.filtros);
      }, 300);
    },
    verReporte() {
      try {
        this.validarFechas();
      } catch (error) {
        return notify(error, 'error');
      }
    },
    validarFechas() {
      if (!this.fecha) {
        throw ('Debe introducir la fecha de asistencia');
      }
      const resValidacionFecha = esFechaValida(this.fecha);
      if (resValidacionFecha.status  >= 300) {
        throw (resValidacionFecha.mensaje);
      }
    },
    regresar() {
      loading(true);
      this.$inertia.get("/docente/asistenciasCargasAcademicas");
    }
  }
};
</script>
