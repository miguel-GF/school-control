<template>
	<!-- view="hHh lpR fFf " -->
	<q-layout>
		<q-header reveal elevated class="bg-primary text-white">
			<q-toolbar>
				<q-btn dense flat round icon="menu" @click="toggleLeftDrawer" />

				<q-toolbar-title>
					<!-- <q-avatar>
						<img src="https://cdn.quasar.dev/logo-v2/svg/logo-mono-white.svg" />
					</q-avatar>
					Home-->
					<Link class="cursor-pointer" as="label" v-if="$page.props.usuario.tipo == 'alumno'" href="/alumno/dashboard">
					Home</Link>
					<Link class="cursor-pointer" as="label" v-else-if="$page.props.usuario.tipo == 'docente'"
						href="/docente/dashboard">Home</Link>
				</q-toolbar-title>
				<div class="text-left q-pr-sm">
					{{ $page.props.usuario.nombre || '--' }}
				</div>

				<q-btn dense flat round icon="settings" @click="toggleRightDrawer" />
			</q-toolbar>
		</q-header>

		<q-drawer v-model="leftDrawerOpen" side="left" overlay bordered>
			<q-list bordered>
				<template v-if="$page.props.usuario.tipo == 'alumno'">
					<template v-for="(opc, i) in opcionesAlumnos" :key="i">
						<ItemMenu :datos="opc" />
					</template>
				</template>
				<template v-else-if="$page.props.usuario.tipo == 'docente'">
					<template v-for="(opc, i) in opcionesDocentes" :key="i">
						<ItemMenu :datos="opc" />
					</template>
				</template>
			</q-list>
		</q-drawer>

		<q-drawer v-model="rightDrawerOpen" side="right" overlay bordered>
			<q-list bordered>
				<template v-for="(opc, i) in opcionesConfiguracion" :key="i">
					<ItemMenu :datos="opc" />
				</template>
			</q-list>
		</q-drawer>

		<q-page-container>
			<main>
				<slot />
			</main>
		</q-page-container>
	</q-layout>
</template>

<script setup>
import { ref } from "vue";
import ItemMenu from "../Components/ItemMenu.vue";
import { Link } from "@inertiajs/inertia-vue3";
const leftDrawerOpen = ref(false);
const rightDrawerOpen = ref(false);
const opcionesAlumnos = ref([
	{ label: "Calificaciones", tag: "calificaciones", icon: "list" },
]);
const opcionesDocentes = ref([
	{ label: "Cargas Académicas", tag: "cargasAcademicas", icon: "person" },
]);
const opcionesConfiguracion = ref([
	{ label: "Cerrar Sesión", tag: "cerrarSesion", icon: "logout" },
]);
const toggleLeftDrawer = () => leftDrawerOpen.value = !leftDrawerOpen.value;
const toggleRightDrawer = () => rightDrawerOpen.value = !rightDrawerOpen.value;
</script>
