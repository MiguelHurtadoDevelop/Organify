<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section class="bg-white p-6 rounded-lg text-white">
        <header>
            <h2 class="text-lg font-medium text-gray-800">Actualizar Contraseña</h2>
            <p class="mt-1 text-sm text-gray-800">
                Asegúrate de que tu cuenta esté usando una contraseña larga y aleatoria para mantenerla segura.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
            <div>
                <InputLabel for="current_password" value="Current Password" class="text-gray-800" />
                <TextInput
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="mt-1 block w-full text-white bg-gray-700 border-green-500"
                    autocomplete="current-password"
                />
                <InputError :message="form.errors.current_password" class="mt-2 text-red-500" />
            </div>

            <div>
                <InputLabel for="password" value="New Password" class="text-gray-800" />
                <TextInput
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full text-white bg-gray-700 border-green-500"
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password" class="mt-2 text-red-500" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Confirm Password" class="text-gray-800" />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full text-white bg-gray-700 border-green-500"
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password_confirmation" class="mt-2 text-red-500" />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Guardar</PrimaryButton>
                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-green-600">Guardado.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
