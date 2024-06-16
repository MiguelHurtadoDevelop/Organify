<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import heic2any from 'heic2any';




const form = useForm({
    nombre: '',
    apellidos: '',
    usuario: '',
    foto: null,
    confirmado: false,
    email: '',
    password: '',
    password_confirmation: '',
    currentImage: null,
});

const handleFileChange = async (event) => {
    const file = event.target.files[0];
    if (file) {
        if (file.name.split('.').pop().toLowerCase() === 'heic' || file.name.split('.').pop().toLowerCase() === 'hevc' ){
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


const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <h3 class="mt-6 mb-4 text-2xl font-extrabold text-gray-900">Crea tu cuenta:</h3>

        <form @submit.prevent="submit" enctype="multipart/form-data">

                <div class="mb-4">
                <div>
                    <InputLabel for="foto" value="Foto" class="text-white"/>
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
                            class="flex flex-col items-center justify-center w-52 h-52 border-2 border-green-500 border-dashed rounded-full cursor-pointer bg-gray-700 hover:bg-gray-600">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6 relative">
                                <div v-if="form.currentImage" class="absolute w-52 h-52 flex items-center justify-center bg-black border-2 border-green-500 border-dashed rounded-full cursor-pointer overflow-hidden">
                                    <img class="w-full h-full object-contain " :src="form.currentImage" alt="Vista previa de la imagen">
                                </div>
                                <font-awesome-icon :icon="['fas', 'camera']" class="text-white text-3xl" />
                                <p class="mb-2 text-xs text-white"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                            </div>                 
                            
                        </label>
                    </div>
                    <InputError :message="form.errors.foto" class="mt-2 text-red-500"/>
                </div>
            </div>


            <div>
                <InputLabel for="nombre" value="Nombre" />

                <TextInput
                    id="nombre"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.nombre"
                    autofocus
                    autocomplete="nombre"
                />

                <InputError class="mt-2" :message="form.errors.nombre" />
            </div>

            <div class="mt-4">
                <InputLabel for="apellidos" value="Apellidos" />

                <TextInput
                    id="apellidos"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.apellidos"
                    autocomplete="apellidos"
                />

                <InputError class="mt-2" :message="form.errors.apellidos" />
            </div>

            <div class="mt-4">
                <InputLabel for="usuario" value="Usuario" />

                <TextInput
                    id="usuario"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.usuario"
                    autocomplete="usuario"
                />

                <InputError class="mt-2" :message="form.errors.usuario" />
            </div>

            

            <div class="mt-4">
                <InputLabel for="email" value="Correo Electrónico" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Contraseña" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirmar Contraseña" />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link
                    :href="route('login')"
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    ¿Ya tienes una cuenta?
                </Link>

                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Registrarse
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
