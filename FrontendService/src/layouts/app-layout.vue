<template>
	<div class="bg-background text-on-background min-h-screen flex antialiased">
		<Sidebar />

		<div class="flex-1 flex flex-col ml-[280px] min-h-screen">
			<Header />
			<main class="flex-1 p-xl flex flex-col gap-xl bg-background">
				<slot />
			</main>
			<Footer />
		</div>
	</div>
</template>

<script setup lang="ts">
       import Header from '../components/layout/Header.vue'
       import Footer from '../components/layout/Footer.vue'
       import Sidebar from '../components/layout/Sidebar.vue'
</script>
