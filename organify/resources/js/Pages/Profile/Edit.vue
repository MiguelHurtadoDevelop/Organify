<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    equipos: {
        type: Array,
    },
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Profile</h2>
        </template>
        
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="flex flex-col lg:flex-row gap-10 justify-center">
                    <div class="p-4 sm:p-8  justify-center items-center content-center bg-gray-800 shadow rounded-lg">
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                            class="max-w-xl"
                        />
                    </div>

                    <div  class="p-4 sm:p-8 max-h-[45rem] overflow-auto bg-gray-800 shadow rounded-lg">
                        <h3 class="text-lg font-mono font-semibold mb-2 text-white">Mis equipos</h3>
                        <div class="grid grid-cols-1 gap-6">
                            <template v-for="equipo in equipos" :key="equipo.id" v-if="equipos.length > 0">
                                <div class="p-2 w-full bg-white shadow rounded-lg">
                                    <Link :href="route('equipo.tablon', equipo.id)" preserve-scroll class="flex flex-row w-full">
                                        <div class="flex items-center gap-3">
                                            <img
                                                class="rounded-full w-24 mb-3 border"
                                                :style="{ borderColor: equipo.color, boxShadow: `0 2px 4px ${equipo.color}`, objectFit: 'cover' }"
                                                :src="`/archivos/${equipo.foto}`"
                                                alt="Foto de perfil"
                                            />
                                            <p class="text-lg text-gray-900 w-64 truncate">{{ equipo.nombre }}</p>
                                        </div>
                                    </Link>
                                </div>
                            </template>
                            <div v-else>
                                <p class="text-lg text-white">¡Aún no perteneces a ningun esquipo!</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col lg:flex-row  gap-10 ">
                    <div class="p-4 sm:p-8 bg-gray-800 shadow rounded-lg">
                        <UpdatePasswordForm class="max-w-xl" />
                    </div>

                    <div class="p-4 sm:p-8 h-full bg-gray-800 shadow rounded-lg">
                        <DeleteUserForm class="max-w-xl" />
                    </div>
                </div>
                
            </div>
        </div>
    </AuthenticatedLayout>
</template>
