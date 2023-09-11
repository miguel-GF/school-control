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
            <div class="text-h6 q-pr-md q-my-auto">Reporte de Asistencias</div>
            <div class="w250 q-pr-md">
              <q-select 
                :options="periodos"
                option-label="periodo"
                option-value="periodo"
                outlined dense
                emit-value
                v-model="filtros.periodo"
                clearable
                @update:modelValue="filtrarCargas"
              >
                <template v-slot:selected v-if="!filtros.periodo">
                  Todos los periodos
                </template>
              </q-select>
            </div>
            <!-- FECHA INICIO -->
            <div class="text-left row q-pr-md">
              <div class="q-my-auto q-pr-xs">Fecha del:</div>
              <q-input class="col" readonly hide-bottom-space outlined dense
                @click="$refs.iconoFechaInicio.$el.click()" v-model="fecha" mask="date" :rules="['date']">
                <template v-slot:append>
                  <q-icon name="event" ref="iconoFechaInicio" class="cursor-pointer">
                    <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                      <q-date v-model="fecha" :options="val => optionsFechaInicio(val, fechaFin)">
                        <div class="row items-center justify-end">
                          <q-btn v-close-popup label="Close" color="primary" flat />
                        </div>
                      </q-date>
                    </q-popup-proxy>
                  </q-icon>
                </template>
              </q-input>
            </div>
            <!-- FECHA FIN -->
            <div class="text-left row">
              <div class="q-my-auto q-pr-xs">al:</div>
              <q-input class="col" readonly hide-bottom-space outlined dense
                @click="$refs.iconoFechaFin.$el.click()" v-model="fechaFin" mask="date" :rules="['date']">
                <template v-slot:append>
                  <q-icon name="event" ref="iconoFechaFin" class="cursor-pointer">
                    <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                      <q-date v-model="fechaFin" :options="val => optionsFechaFin(val, fecha)">
                        <div class="row items-center justify-end">
                          <q-btn v-close-popup label="Close" color="primary" flat />
                        </div>
                      </q-date>
                    </q-popup-proxy>
                  </q-icon>
                </template>
              </q-input>
            </div>
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
            <div class="ellipsis w225">
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
      titulo="Confirmar Datos del Reporte de Asistencia"
      @cerrar="mostrarModalConfirmar = false"
      @aceptar="verReporte()"
    >
      <template #body>
        <div class="col-12 q-pa-sm">
          <div class="col-sm-6 col-md q-mb-md">
            <q-banner rounded class="col-12 bg-orange-4" v-html="obtenerMensajeConfirmacion" />
          </div>
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
  props: ["cargasAcademicas", "usuario", "filtrosRes", "status", "mensaje", "periodos"],
  components: { MainLayout },
  computed: {
    obtenerMensajeConfirmacion() {
      let mensajes = [];
      mensajes[0] = `Generará un reporte de asistencias con la siguiente información:<br>`;
      mensajes[1] = `Licenciatura: ${this.cargaAcademica.licenciatura || '--'}`;
      mensajes[2] = `Materia: ${this.cargaAcademica.materia || '--'}`;
      mensajes[3] = `Periodo: ${this.cargaAcademica.periodo || '--'}`;
      mensajes[4] = `Fecha inicial: ${this.fecha || '--'}`;
      mensajes[5] = `Fecha final: ${this.fechaFin || '--'}`;
      return mensajes.join('<br>');
    },
  },
  data() {
    return {
      mostrarModalConfirmar: false,
      cargaAcademica: {},
      filter: "",
      filtros: {
        periodo: ""
      },
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
          field: row => row.clavemat,
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
      fechaFin: obtenerFechaActualOperacion(),
      fechaActual: obtenerFechaActualOperacion(),
      optionsFechaInicio (date, dateFin) {
        return date <= obtenerFechaActualOperacion() && date <= dateFin
      },
      optionsFechaFin (date, dateInicio) {
        return date <= obtenerFechaActualOperacion() && date >= dateInicio
      },
    }
  },
  beforeMount() {
    this.filtros.periodo = this.filtrosRes['periodo'] ?? "";
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
      try {
        this.validarFechas();
        this.cargaAcademica = cargaAcademica;
        this.mostrarModalConfirmar = true;
      } catch (error) {
        return notify(error, 'error');
      }
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
        loading(true);
        const params = {
          fechaInicio: this.fecha,
          fechaFin: this.fechaFin,
          semestre: this.cargaAcademica.semestre,
          grupo: this.cargaAcademica.grupo,
          materia: this.cargaAcademica.materia,
          periodo: this.cargaAcademica.periodo,
          licenciatura: this.cargaAcademica.licenciatura,
        }
        this.$inertia.get("/docente/reporteAsistencias", params)
      } catch (error) {
        loading(false);
        return notify(error, 'error');
      }
    },
    validarFechas() {
      if (!this.fecha) {
        throw ('Debe introducir la fecha inicial de asistencia');
      }
      const resValidacionFecha = esFechaValida(this.fecha);
      if (resValidacionFecha.status  >= 300) {
        throw (resValidacionFecha.mensaje);
      }
      if (!this.fechaFin) {
        throw ('Debe introducir la fecha final de asistencia');
      }
      const resValidacionFechaFin = esFechaValida(this.fechaFin);
      if (resValidacionFechaFin.status  >= 300) {
        throw (resValidacionFechaFin.mensaje);
      }
    },
  }
};
</script>
