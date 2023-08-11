<template>
  <q-item clickable v-ripple @click="onClick()">
    <q-item-section>{{ datos.label || '--' }}</q-item-section>
    <q-item-section avatar>
      <q-icon color="secondary" :name="datos.icon" />
    </q-item-section>
  </q-item>
</template>

<script>
export default {
  props: {
    datos: {
      type: Object,
      default: () => { },
    }
  },
  methods: {
    onClick() {
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
      
        default:
          url = "";
          break;
      }
      this.$inertia.get(url);
    }
  },
};
</script>