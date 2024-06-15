<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Inicio Sesión" />

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <h3 class="mt-6 mb-4 text-2xl font-extrabold text-gray-900">Inicia sesión con tu cuenta:</h3>
        <form @submit.prevent="submit" class="max-w-md mx-auto">
            <div class="mb-6">
                <InputLabel for="email" value="Correo Electronico" />

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

            <div class="mb-6">
                <InputLabel for="password" value="Contraseña" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ml-2 text-sm text-gray-600">Recuérdame</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-6">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-gray-600 hover:text-gray-900"
                >
                    ¿Olvidaste tu contraseña?
                </Link>

                <PrimaryButton class="ml-4 px-6 py-3 text-lg font-semibold bg-green-500 text-white rounded-md hover:bg-green-600" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Iniciar sesión
                </PrimaryButton>
            </div>
        </form>

        <p class="mt-6 text-center text-sm text-gray-600">
            ¿No tienes una cuenta?
            <Link href="/register" class="font-medium text-gray-600 hover:text-gray-500">
                Regístrate
            </Link>
        </p>
    </GuestLayout>
</template>
