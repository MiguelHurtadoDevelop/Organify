<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});


const imagePreview = ref(null);
const user = usePage().props.auth.user;



const form = useForm({
    nombre: user.nombre,
    apellidos: user.apellidos,
    email: user.email,
    foto: user.foto,
});

const handleFileChange = (e) => {
    const selectedFile = e.target.files[0];
    form.foto = selectedFile;
    // Vista previa de la imagen seleccionada
    imagePreview.value = URL.createObjectURL(selectedFile);
};
const handleSubmit = async () => {
    const formData = new FormData();
    formData.append('nombre', form.nombre);
    formData.append('apellidos', form.apellidos);
    formData.append('email', form.email);
    formData.append('foto', form.foto);

    // Realizar la solicitud POST utilizando Inertia.js
    await form.post(route('profile.update'), formData);

    // Puedes manejar el resultado aquí si es necesario
};

</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Profile Information</h2>

            <p class="mt-1 text-sm text-gray-600">
                Update your account's profile information and email address.
            </p>
        </header>

        <form @submit.prevent="handleSubmit" class="mt-6 space-y-6">

            <!-- Foto de Perfil -->
            <div>
                <InputLabel for="foto" value="Foto de Perfil" />
                <input
                    id="foto"
                    type="file"
                    class="mt-1 block w-full"
                    accept="image/*"
                    @change="handleFileChange"
                />
                <InputError class="mt-2" :message="form.errors.foto" />
            </div>

            <!-- Mostrar vista previa de la imagen seleccionada (opcional) -->
            <div v-if="!imagePreview" class="w-24 h-24 mb-3 bg-black rounded-full overflow-hidden">
                <img class="w-full h-full object-cover" :src="`/archivos/${form.foto}`" alt="Foto de perfil">
            </div>
            <div v-else class="w-24 h-24 mb-3 bg-black rounded-full overflow-hidden">
                <img class="w-full h-full object-cover" :src="imagePreview" alt="Foto de perfil">
            </div>
            <!-- Nombre -->
            <div>
                <InputLabel for="nombre" value="Nombre" />
                <TextInput
                    id="nombre"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.nombre"
                    required
                    autofocus
                    autocomplete="nombre"
                />
                <InputError class="mt-2" :message="form.errors.nombre" />
            </div>

            <!-- Apellidos -->
            <div>
                <InputLabel for="apellidos" value="Apellidos" />
                <TextInput
                    id="apellidos"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.apellidos"
                    required
                    autocomplete="apellidos"
                />
                <InputError class="mt-2" :message="form.errors.apellidos" />
            </div>

            <!-- Email -->
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="text-sm mt-2 text-gray-800">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 font-medium text-sm text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
