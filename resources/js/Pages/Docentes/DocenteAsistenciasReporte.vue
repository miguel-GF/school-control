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
        ref="table"
      >
        <template v-slot:top-left>
          <div class="row">
            <div class="text-h6 q-pr-md q-my-auto">Reporte de Asistencias<br> {{ fecha || '--' }}</div>
            <div class="q-pr-md q-pt-sm">
              <div> {{ `${datos['periodo'] || '--'} | ${datos['semestre'] || '--'}° ${datos['grupo'] || '--'}` }}</div>
              <div> {{ `Licenciatura: ${datos['licenciatura']}` }}</div>
              <div> {{ `Materia: ${datos['materia']}` }}</div>

            </div>
          </div>
        </template>
        <template v-slot:top-right>
          <q-btn @click="regresar()" dense icon-right="chevron_left" color="secondary"
            class="q-mr-md"
          >
            <div class="q-px-sm">Regresar</div>
          </q-btn>
          <q-input class="q-mr-md" outlined dense debounce="300" v-model="filter" placeholder="Búsqueda">
            <template v-slot:append>
              <q-icon name="search" />
            </template>
          </q-input>

          <q-btn
            color="primary"
            dense
            no-caps
            icon-right="las la-file-csv"
            aria-label="menu"
            label="Exportar"
            class="q-px-sm"
            @click="exportarExcel()"
        />  
        </template>
        <template v-slot:body-cell-asistencia="props">
          <q-td :props="props" class="text-center">
            <div class="text-center q-pr-">
              {{ props.value || '--' }}
            </div>
          </q-td>
        </template>
      </q-table>
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from '../../Layouts/MainLayout.vue';
import { loading } from '../../Utils/loading';
import { notify } from "../../Utils/notify.js";
import excel from "../../Utils/exportExcel.js";
export default {
  name: "DocenteAsistenciasReporte",
  props: ["asistencias", "usuario", "datos", "fecha"],
  components: { MainLayout },
  data() {
    return {
      filter: "",
      columns: [
        {
          name: 'numeroAlumno',
          label: '# Alumno',
          labelExcel: '# Alumno',
          align: 'left',
          field: row => row.numestudiante,
          format: val => val ?? '--',
          sortable: true,
          headerStyle: 'width: 20%',
        },
        {
          name: 'alumno',
          label: 'Alumno',
          labelExcel: 'Alumno',
          align: 'left',
          field: row => row.nombre,
          format: val => val ?? '--',
          sortable: true,
          headerStyle: 'width: 70%',
        },
        {
          name: 'asistencia',
          label: 'Asistencias',
          labelExcel: 'Asistencias',
          align: 'center',
          field: row => row.cantidad_asistencias,
          format: val => val ?? '--',
          sortable: true,
          headerStyle: 'width: 10%',
        },
      ],
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
    regresar() {
      loading(true);
      this.$inertia.get("/docente/asistenciasCargasAcademicas");
    },
    exportarExcel() {
      const valoresNombre = [
        'asistencias',
        this.datos['periodo'],
        this.datos['semestre'] + '°',
        this.datos['grupo'],
        this.datos['licenciatura'],
        this.datos['materia'],
        this.fecha
      ];
      const nombreArchivo = valoresNombre.join('_').replace(/\s/g, '_').toUpperCase();
      excel.exportarCsv(this.columns, this.$refs.table.filteredSortedRows, nombreArchivo);
    }
  }
};
</script>
