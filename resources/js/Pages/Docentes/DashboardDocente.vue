<template>
  <div>
    <h1 class="text-3xl font-semibold mb-4">Welcome to the Dashboard!</h1>
    <p class="text-gray-600">You are logged in as {{ usuario.nombre || '--' }}</p>
    <p class="text-gray-600">usuario Type: {{ usuario.tipo || '--' }}</p>
    <button
      @click="logout"
      class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline mt-4"
    >
      Logout
    </button>
  </div>
</template>

<script>
export default {
  props: ["usuario"],
  methods: {
    logout() {
      this.$inertia.post("/logout");
    },
  },
};
</script>
