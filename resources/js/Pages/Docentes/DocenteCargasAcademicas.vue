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
            <div class="text-h6 q-pr-md q-my-auto">Cargas Académicas</div>
            <div class="w250">
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
            <q-btn dense color="primary" flat round icon="check" @click="irPasarAsistencia(props.row)">
              <q-tooltip>
                Pasar Asistencia
              </q-tooltip>
            </q-btn>
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
export default {
  props: ["cargasAcademicas", "periodos", "usuario", "filtrosRes", "status", "mensaje"],
  components: { MainLayout },
  data() {
    return {
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
      ]
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
      this.showNotif(mensaje, 'exito');
    }
  },
  methods: {
    irPasarAsistencia({ clavemat }) {
      loading(true, 'Cargando ...');
      const url = "/docente/pasarAsistencias/" + clavemat;
      this.$inertia.get(url);
    },
    showNotif (message, tipo) {
      return notify(message, tipo);
    },
    filtrarCargas() {
      setTimeout(() => {
        loading(true);
        const currentRoute = window.location.href;
        this.$inertia.get(currentRoute, this.filtros);
      }, 300);
    }
  }
};
</script>
