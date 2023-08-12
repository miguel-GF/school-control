<template>
  <MainLayout>
    <div class="q-pa-md">
      <q-table 
        title="Cargas Académicas"
        :rows="cargasAcademicas"
        :columns="columns"
        :rows-per-page-options="[10]"
        :filter="filter"
        class="tabla-listado striped-table"
        row-key="cvemat" 
      >
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
      </q-table>
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from '../../Layouts/MainLayout.vue';
import { loading } from '../../Utils/loading';
export default {
  props: ["cargasAcademicas"],
  components: { MainLayout },
  data() {
    return {
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
      ]
    }
  },
  created() {
    loading(false);
  }
};
</script>
