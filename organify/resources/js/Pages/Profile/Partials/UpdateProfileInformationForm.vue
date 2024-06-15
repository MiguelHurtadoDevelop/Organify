<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import heic2any from 'heic2any';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    nombre: user.nombre,
    apellidos: user.apellidos,
    email: user.email,
    foto: user.foto,
    currentImage: user.foto ? `/archivos/${user.foto}` : null,
});


const handleFileChange = async (event) => {
    console.log("File change event: ", event.target.files);
    const file = event.target.files[0];
    if (file) {
        console.log("File type: ", file.type);
        if (file.name.split('.').pop().toLowerCase() === 'heic'){
            console.log("Converting HEIC to PNG...");
            try {
                const convertedBlob = await heic2any({
                    blob: file,
                    toType: "image/png",
                });
                const convertedFile = new File([convertedBlob], file.name.split('.')[0] + '.png', { type: "image/png" });
                form.foto = convertedFile;
                form.currentImage = URL.createObjectURL(convertedFile);
            } catch (error) {
                console.error("Error converting HEIC to PNG: ", error);
            }
        } else {
            form.foto = file;
            form.currentImage = URL.createObjectURL(file);
        }
    }
};


const handleSubmit = async () => {
    const formData = new FormData();
    formData.append('nombre', form.nombre);
    formData.append('apellidos', form.apellidos);
    formData.append('email', form.email);
    formData.append('foto', form.foto);

    await form.post(route('profile.update'), formData);
};

</script>

<template>
    <section class="bg-white p-6 rounded-lg text-white">
        <header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-mono font-medium text-gray-800">Información del Perfil</h2>
                <Link :href="route('logout')" method="post" as="button" class="px-2 py-1 bg-red-700 rounded-full hover:bg-red-900">
                    <span class="text-sm font-semibold">Cerrar Sesión</span>
                </Link>
            </div>
            
            <p class="mt-4 text-md text-gray-800">
                Actualiza la información del perfil de tu cuenta y la dirección de correo electrónico.
            </p>
        </header>

        <form @submit.prevent="handleSubmit" class="mt-6 space-y-6">
            <div class="mb-4">
                <div>
                    <InputLabel for="foto" value="Foto" class="text-gray-800"/>
                    <div class="flex flex-col items-center justify-center w-full relative">
                        
                        <input 
                            type="file" 
                            id="foto" 
                            name="foto" 
                            class="mt-1 hidden w-full  opacity-0 cursor-pointer" 
                            @change="handleFileChange" 
                        />
                        <label 
                            for="foto" 
                            class="flex flex-col items-center justify-center w-32 h-32 border-2 border-green-500 border-dashed rounded-full cursor-pointer bg-gray-700 hover:bg-gray-600">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6 relative">
                                <div v-if="form.currentImage" class="absolute w-32 h-32 flex items-center justify-center bg-black border-2 border-green-500 border-dashed rounded-full cursor-pointer overflow-hidden">
                                    <img class="w-full h-full object-cover " :src="form.currentImage" alt="Vista previa de la imagen">
                                </div>
                                <font-awesome-icon :icon="['fas', 'camera']" class="text-white text-3xl" />
                                <p class="mb-2 text-xs text-white"><span class="font-semibold">Haz clic para subir</span> o arrastra y suelta</p>
                                <div class="absolute left-0 top-0 text-lg text-white bg-black rounded-full py-1 px-2">
                                    <font-awesome-icon :icon="['fas', 'camera']" />
                                </div>
                            </div>                 
                        </label>
                    </div>
                    <InputError :message="form.errors.foto" class="mt-2 text-red-500"/>
                </div>
            </div>
            <!-- Nombre -->
            <div>
                <InputLabel for="nombre" value="Nombre" class="text-gray-800" />
                <TextInput
                    id="nombre"
                    type="text"
                    class="mt-1 block w-full text-white bg-gray-700 border-green-500"
                    v-model="form.nombre"
                    autofocus
                    autocomplete="nombre"
                />
                <InputError class="mt-2" :message="form.errors.nombre" />
            </div>

            <!-- Apellidos -->
            <div>
                <InputLabel for="apellidos" value="Apellidos" class="text-gray-800"/>
                <TextInput
                    id="apellidos"
                    type="text"
                    class="mt-1 block w-full text-white bg-gray-700 border-green-500"
                    v-model="form.apellidos"
                    autocomplete="apellidos"
                />
                <InputError class="mt-2" :message="form.errors.apellidos" />
            </div>

            <!-- Email -->
            <div>
                <InputLabel for="email" value="Correo electrónico" class="text-gray-800"/>
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full text-white bg-gray-700 border-green-500"
                    v-model="form.email"
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="text-sm mt-2 text-white">
                    Tu dirección de correo electrónico no está verificada.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="underline text-sm text-white hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Haz clic aquí para reenviar el correo de verificación.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 font-medium text-sm text-green-600"
                >
                    Se ha enviado un nuevo enlace de verificación a tu dirección de correo electrónico.
                </div>
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
