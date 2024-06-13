<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <GuestLayout>
        <Head title="Verificación de Correo Electrónico" />

        <div class="mb-4 text-sm text-gray-600">
            ¡Gracias por registrarte! Antes de comenzar, ¿podrías verificar tu dirección de correo electrónico haciendo clic en el enlace que acabamos de enviarte por correo electrónico? Si no recibiste el correo electrónico, con gusto te enviaremos otro.
        </div>

        <div class="mb-4 font-medium text-sm text-green-600" v-if="verificationLinkSent">
            Se ha enviado un nuevo enlace de verificación a la dirección de correo electrónico que proporcionaste durante el registro.
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Reenviar Correo Electrónico de Verificación
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >Cerrar Sesión</Link
                >
            </div>
        </form>
    </GuestLayout>
</template>
