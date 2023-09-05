<template>
  <MainLayout>
    <div class="q-pa-md">
      <q-table 
        :rows="cargasAcademicas"
        :columns="columns"
        :rows-per-page-options="[10]"
        :filter="filter"
        class="tabla-listado striped-table"
        row-key="cvemat" 
      >
        <template v-slot:top-left>
          <div class="row">
            <div class="text-h6 q-pr-md q-my-auto">Generar Reporte de Cargas Académicas</div>
          </div>
        </template>
        <template v-slot:top-right>
          <q-input outlined dense debounce="300" v-model="filter" placeholder="Búsqueda">
            <template v-slot:append>
              <q-icon name="search" />
            </template>
          </q-input>
        </template>
        <template v-slot:body-cell-materia="props">
          <q-td :props="props">
            <div class="ellipsis w250">
              {{ props.value || '--' }}
            </div>
          </q-td>
        </template>
        <template v-slot:body-cell-asistencia="props">
          <q-td :props="props">
            <q-btn dense color="primary" flat round icon="las la-calendar-check" @click="abrirModalConfirmacion(props.row)">
              <q-tooltip anchor="center left" self="center right" :offset="[10, 10]">
                Ver Reporte de Asistencias
              </q-tooltip>
            </q-btn>
          </q-td>
        </template>
      </q-table>
    </div>
    <!-- MODALES -->
    <!-- DIALOGO DE CONFIRMACION -->
    <the-dialog-confirm
      :mostrar="mostrarModalConfirmar"
      classes="w700-i"
      titulo="Seleccionar Fecha del Reporte de Asistencia"
      @cerrar="mostrarModalConfirmar = false"
      @aceptar="verReporte()"
    >
      <template #body>
        <div class="col-12 q-pa-sm">
          <div class="col-sm-6 col-md q-mb-md">
            <div class="text-bold">{{ cargaAcademica.licenciatura }}</div>
            <div class="text-bold">{{ `${cargaAcademica.periodo} | ${cargaAcademica.semestre || '--' }° ${cargaAcademica.grupo || '--'}` }}</div>
          </div>
          <q-date
            v-model="fecha"
            landscape
            :options="optionsFn"
            style="width: 100% !important"
          />
        </div>
      </template>
    </the-dialog-confirm>
  </MainLayout>
</template>

<script>
import MainLayout from '../../Layouts/MainLayout.vue';
import { loading } from '../../Utils/loading';
import { notify } from "../../Utils/notify.js";
import { obtenerFechaActualOperacion, esFechaValida } from '../../Utils/date';
export default {
  name: "DocenteAsistenciasCargasAcademicas",
  props: ["cargasAcademicas", "usuario", "filtrosRes", "status", "mensaje"],
  components: { MainLayout },
  data() {
    return {
      mostrarModalConfirmar: false,
      cargaAcademica: {},
      filter: "",
      columns: [
        {
          name: 'semestre',
          label: 'Semestre',
          align: 'center',
          field: row => row.semestre,
          format: val => val ?? '--',
          sortable: true
        },
        {
          name: 'grupo',
          label: 'Grupo',
          align: 'center',
          field: row => row.grupo,
          format: val => val ?? '--',
          sortable: true
        },
        {
          name: 'licenciatura',
          label: 'Licenciatura',
          align: 'left',
          field: row => row.licenciatura,
          format: val => val ?? '--',
          sortable: true
        },
        {
          name: 'claveMateria',
          label: 'Clave Materia',
          align: 'left',
          field: row => row.clavemat,
          format: val => val ?? '--',
          sortable: true
        },
        {
          name: 'materia',
          label: 'Materia',
          align: 'left',
          field: row => row.materia,
          format: val => val ?? '--',
          sortable: true,
        },
        {
          name: 'lunes',
          label: 'Lun',
          align: 'center',
          field: row => row.lun,
          format: val => val ?? '--',
          sortable: false
        },
        {
          name: 'martes',
          label: 'Mar',
          align: 'center',
          field: row => row.mar,
          format: val => val ?? '--',
          sortable: false
        },
        {
          name: 'miercoles',
          label: 'Mié',
          align: 'center',
          field: row => row.mie,
          format: val => val ?? '--',
          sortable: false
        },
        {
          name: 'jueves',
          label: 'Jue',
          align: 'center',
          field: row => row.jue,
          format: val => val ?? '--',
          sortable: false
        },
        {
          name: 'viernes',
          label: 'Vie',
          align: 'center',
          field: row => row.vie,
          format: val => val ?? '--',
          sortable: false
        },
        {
          name: 'sabado',
          label: 'Sáb',
          align: 'center',
          field: row => row.sab,
          format: val => val ?? '--',
          sortable: false
        },
        {
          name: 'asistencia',
          align: 'center',
          sortable: false
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
    abrirModalConfirmacion(cargaAcademica) {
      this.cargaAcademica = cargaAcademica;
      this.mostrarModalConfirmar = true;
    },
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
        loading(true);
        console.log('lllego');
        const params = {
          fecha: this.fecha,
          semestre: this.cargaAcademica.semestre,
          grupo: this.cargaAcademica.grupo,
          materia: this.cargaAcademica.materia,
          periodo: this.cargaAcademica.periodo,
          licenciatura: this.cargaAcademica.licenciatura,
        }
        console.log(params);
        this.$inertia.post("/docente/reporteAsistencias", params)
      } catch (error) {
        loading(false);
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
  }
};
</script>
