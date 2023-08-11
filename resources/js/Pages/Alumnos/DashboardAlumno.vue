<template>
  <MainLayout>
    <div class="text-center">
      <h2>Bienvenido Alumno</h2>
      <h2>{{ `${usuario.nombre || '--'}` }}</h2>
      <p class="text-body1 text-primary">{{ usuario.numestudiante || '--' }}</p>
    </div>
  </MainLayout>
</template>

<script>
import MainLayout from '../../Layouts/MainLayout.vue';
export default {
  props: ["usuario"],
  components: { MainLayout }
};
</script>
