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
        <Link :href="route('logout')" method="post" as="button">Log Out</Link>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-800">Equipo</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        <template v-for="equipo in equipos" :key="equipo.id">
                            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                                <Link :href="route('equipo.tablon', equipo.id)" preserve-scroll class="flex flex-row w-full">
                                    <div class="flex items-center gap-3">
                                        <img
                                            class="rounded-full w-24 mb-3 border"
                                            :style="{ borderColor: equipo.color, boxShadow: `0 2px 4px ${equipo.color}`, objectFit: 'cover' }"
                                            :src="`/archivos/${equipo.foto}`"
                                            alt="Foto de perfil"
                                        />
                                        <p class="text-lg">{{ equipo.nombre }}</p>
                                    </div>
                                </Link>
                            </div>
                        </template>
                    </div>
                </div>





                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
