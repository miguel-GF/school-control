<template>
  <q-item clickable v-ripple @click="onClick()">
    <q-item-section>{{ datos.label || '--' }}</q-item-section>
    <q-item-section avatar>
      <q-icon color="secondary" :name="datos.icon" />
    </q-item-section>
  </q-item>
</template>

<script>
import { loading } from '../Utils/loading';
export default {
  props: {
    datos: {
      type: Object,
      default: () => { },
    }
  },
  methods: {
    onClick() {
      loading(true, 'Cargando ...')
      if (this.datos.tag == 'cerrarSesion') {
        this.logout();
      } else {
        this.redirect();
      }
    },
    logout() {
      this.$inertia.post("/logout");
    },
    redirect() {
      let url;
      switch (this.datos.tag) {
        case 'calificaciones':
          url = "/alumno/calificaciones";
          break;
      
        case 'cargasAcademicas':
          url = "/docente/cargasAcademicas";
          break;

        default:
          url = "";
          break;
      }
      this.$inertia.get(url);
    }
  },
};
</script>