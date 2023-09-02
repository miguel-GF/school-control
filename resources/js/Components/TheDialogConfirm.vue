<template>
  <q-dialog v-model="mostrar" persistent>
    <q-card :class="classes">
      <q-card-section class="row">
        <div class="text-h6 ellipsis">{{ titulo }}</div>
        <q-space></q-space>
        <div>
          <q-btn rounded flat dense icon="close" @click="cerrar()"/>
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section class="card-body-height scroll">
        <slot name="body">
          <div v-html="mensaje"></div>
        </slot>
      </q-card-section>

      <q-separator />

      <q-card-actions align="center" class="q-py-md">
        <q-btn flat label="Cerrar" color="primary" @click="cerrar()" class="q-mr-sm" />
        <q-btn label="Aceptar" color="primary" @click="aceptar()" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
export default {
  props: {
    mostrar: {
      type: Boolean,
      default: false,
    },
    titulo: {
      type: String,
      default: 'Título',
    },
    mensaje: {
      type: String,
      default: 'Mensaje default ...',
    },
    classes: {
      type: String,
      default: 'card-width',
    },
  },
  methods: {
    cerrar() {
      this.$emit('cerrar');
    },
    aceptar() {
      this.$emit('aceptar');
    },
  }
};
</script>

<style scoped>
.card-width {
  width: 500px;
}

.card-body-height {
  min-height: 25vh;
  max-height: 52vh;
}
</style>