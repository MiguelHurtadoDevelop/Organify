<template>
    <GuestLayout>
        <h1 class="text-4xl font-mono text-center font-bold mb-4 text-gray-900"> <font-awesome-icon :icon="['fas', 'ban']" /> Página no encontrada</h1>
        <p class="text-center font-semibold text-xl text-gray-800">Lo sentimos, la página que buscas no existe.</p>
        <p class="text-center text-gray-600 mt-4">Verifica la URL o regresa al <a class="py-2 px-4 font-bold text bg-green-500 text-gray-900 rounded-full"  href="/">Inicio</a>.</p>
    </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
</script>

<style scoped>
/* Estilos CSS específicos para este componente */
</style>
