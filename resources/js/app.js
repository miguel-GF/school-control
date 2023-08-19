import './bootstrap';

import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/inertia-vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { Quasar, Notify, Loading } from "quasar";
import TheDialogConfirm from "./Components/TheDialogConfirm.vue"
import TheDialogResponse from "./Components/TheDialogResponse.vue"

// Import icon libraries
import '@quasar/extras/material-icons/material-icons.css'

// Import Quasar css
import 'quasar/src/css/index.sass'

// Images assets
import.meta.glob([ '../images/**', ]);

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) =>
    resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob("./Pages/**/*.vue")
    ),
  setup({ el, app, props, plugin }) {
    const vueApp = createApp({ render: () => h(app, props) })
    .use(plugin)
    .use(Quasar, {
      plugins: { Notify, Loading }
    });
    vueApp.component('the-dialog-confirm', TheDialogConfirm);
    vueApp.component('the-dialog-response', TheDialogResponse);
    vueApp.mount(el);
    return vueApp;
  },
});