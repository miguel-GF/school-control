<template>
  <MainLayout>
    <div class="q-pa-md">
      <q-table 
        title="Calificaciones"
        :rows="calificaciones"
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
      </q-table>
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from '../../Layouts/MainLayout.vue';
import { formatearNumero } from '../../Utils/format';
export default {
  props: ["calificaciones"],
  components: { MainLayout },
  data() {
    return {
      filter: "",
      columns: [
        {
          name: 'claveMateria',
          label: 'Clave Materia',
          align: 'left',
          field: row => row.cvemat,
          format: val => val ?? '--',
          sortable: true
        },
        {
          name: 'materia',
          label: 'Materia',
          align: 'left',
          field: row => row.materia,
          format: val => val ?? '--',
          sortable: true
        },
        {
          name: 'periodo',
          label: 'Periodo',
          align: 'left',
          field: row => row.periodo,
          format: val => val ?? '--',
          sortable: true
        },
        {
          name: 'primerParcial',
          label: '1° Parcial',
          align: 'center',
          field: row => row.primerparcial,
          format: val => formatearNumero(val),
          sortable: true
        },
        {
          name: 'segundoParcial',
          label: '2° Parcial',
          align: 'center',
          field: row => row.segundoparcial,
          format: val => formatearNumero(val),
          sortable: true
        },
        {
          name: 'ordinario',
          label: 'Ordinario',
          align: 'center',
          field: row => row.ordinario,
          format: val => formatearNumero(val),
          sortable: true
        },
        {
          name: 'extraordinario',
          label: 'Extraordinario',
          align: 'center',
          field: row => row.extraordinario,
          format: val => formatearNumero(val),
          sortable: true
        },
        {
          name: 'final',
          label: 'Final',
          align: 'center',
          field: row => row.final,
          format: val => formatearNumero(val),
          sortable: true
        },
      ]
    }
  },
};
</script>
