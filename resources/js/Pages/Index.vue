<script setup>
import { ref } from "vue";
import KinstaLayout from "../Layouts/KinstaLayout.vue";
defineProps({
  articles: String,
});
const text = ref("");
</script>

<template>
  <KinstaLayout>
    <section>
      <h2>Read our latest articles</h2>
      <article>
        <div>
          <img src="/images/kinsta-logo.png" alt="Article thumbnail" />
        </div>

        <h3>Title for the blog</h3>
        <p>
          {{ articles ?? '-no-articles-' }}
        </p>

        <q-input outlined v-model="text" dense label="Outlined" />
      </article>
    </section>
  </KinstaLayout>
</template>