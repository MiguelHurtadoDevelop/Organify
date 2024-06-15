<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Olvidé mi Contraseña" />

        <div class="mb-4 text-sm text-gray-600">
            ¿Olvidaste tu contraseña? No hay problema. Solo indícanos tu dirección de correo electrónico y te enviaremos un enlace para restablecer tu contraseña por correo electrónico, lo que te permitirá elegir una nueva.
        </div>

        <form @submit.prevent="submit" class="max-w-md mx-auto">
            <div class="mb-6">
                <InputLabel for="email" value="Correo Electrónico" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="flex items-center justify-end mt-6">
                <PrimaryButton class="px-6 py-3 text-lg font-semibold bg-green-500 text-white rounded-md hover:bg-green-600" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Enviar Enlace para Restablecer Contraseña
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
