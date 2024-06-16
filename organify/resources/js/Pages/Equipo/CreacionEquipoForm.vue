<script setup>
import InputError from '@/Components/InputError.vue'; // Importación de componentes de Vue
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { router, useForm } from '@inertiajs/vue3'; // Importación de funciones de Vue y de Inertia
import { defineProps, defineEmits, ref, watch } from 'vue'; // Importación de funciones de Vue

import heic2any from 'heic2any'; // Importación de la librería para convertir HEIC a PNG

// Definición de props esperados por el componente
const props = defineProps({
    equipo: {
        type: Object,
        default: () => ({
            id: null,
            foto: null,
            nombre: '',
            descripcion: '',
            tipo: '',
            color: '#000000',
        })
    }
});

// Definición de eventos emitidos por el componente
const emit = defineEmits(['formSubmitted', 'closeForm']);

// Uso del hook useForm para manejar el formulario
const form = useForm({
    id: props.equipo.id,
    foto: null,
    nombre: props.equipo.nombre,
    descripcion: props.equipo.descripcion,
    tipo: props.equipo.tipo,
    color: props.equipo.color,
    currentImage: null,
});

// Función para manejar el cambio de archivo de imagen
const handleFileChange = async (event) => {
    const file = event.target.files[0]; // Obtención del archivo seleccionado
    if (file) {
        if (file.name.split('.').pop().toLowerCase() === 'heic' || file.name.split('.').pop().toLowerCase() === 'hevc' ){
            try {
                const convertedBlob = await heic2any({ // Conversión del archivo HEIC a PNG
                    blob: file,
                    toType: "image/png",
                });
                const convertedFile = new File([convertedBlob], file.name.split('.')[0] + '.png', { type: "image/png" });
                form.foto = convertedFile; // Asignación del archivo convertido al formulario
                form.currentImage = URL.createObjectURL(convertedFile); // Creación de URL para mostrar la imagen convertida
            } catch (error) {
                console.error("Error converting HEIC to PNG: ", error); // Manejo de errores en la conversión
            }
        } else {
            form.foto = file; // Asignación del archivo al formulario
            form.currentImage = URL.createObjectURL(file); // Creación de URL para mostrar la imagen seleccionada
        }
    }
};

// Función para enviar el formulario
const submit = () => {
    const routeName = form.id ? 'equipo.update' : 'equipo.create'; // Determinación de la ruta según sea creación o actualización
    form.post(route(routeName, form.id), { // Envío del formulario utilizando Inertia
        onSuccess: () => {
            form.reset(); // Reinicio del formulario después de enviar
            emit('formSubmitted'); // Emisión del evento de formulario enviado
        }
    });
};
</script>


<template>
    <form @submit.prevent="submit" enctype="multipart/form-data" class="w-70 p-4 h-[35rem] md:h-[45rem] bg-gray-800 text-white rounded-lg shadow-md overflow-auto relative">
        
        <button @click="$emit('closeForm')" class="text-gray-400 absolute right-2 top-2 text-xl font-bold focus:outline-none">
            <font-awesome-icon :icon="['fas', 'xmark']" />
        </button>
        
        <h2 class="text-center font-mono text-2xl mt-4 mb-4 text-green-400">¡Vamos a crear un Equipo!</h2>
        <p class="text-center font-mono mb-4">Impulsa la productividad de tu equipo facilitando el acceso a todas las tareas de una forma sencilla.</p>

        <div class="mb-4">
            <div>
                <InputLabel for="foto" value="Foto" class="text-white"/>
                <div class="flex flex-col items-center justify-center w-full relative">
                    <input 
                        type="file" 
                        id="foto" 
                        name="foto" 
                        class="mt-1 hidden w-full z-10 opacity-0 cursor-pointer" 
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

        <div class="mb-4">
            <InputLabel for="nombre" value="Nombre" class="text-white"/>
            <TextInput
                id="nombre"
                type="text"
                class="mt-1 block w-full w-full bg-gray-700 text-white border-green-500 "
                v-model="form.nombre"
                autocomplete="nombre"
            />
            <InputError class="mt-2 text-red-500" :message="form.errors.nombre" />
        </div>

        <div class="mb-4">
            <InputLabel for="descripcion" value="Descripción" class="text-white"/>
            <TextInput
                id="descripcion"
                type="text"
                class="mt-1 block w-full w-full bg-gray-700 text-white border-green-500"
                v-model="form.descripcion"
                autocomplete="descripcion"
            />
            <InputError class="mt-2 text-red-500" :message="form.errors.descripcion" />
        </div>

        <div class="mb-4">
            <InputLabel for="tipo" value="Tipo" class="text-white"/>
            <select id="tipo" class="mt-1 block w-full w-full bg-gray-700 text-white border-green-500" v-model="form.tipo">
                <option value="publico">Público</option>
                <option value="privado">Privado</option>
            </select>
            <InputError class="mt-2 text-red-500" :message="form.errors.tipo" />
        </div>

        <div class="mb-4">
            <InputLabel for="color" value="Color del equipo" class="text-white"/>
            <input type="color" id="color" class="mt-1 block w-full w-full bg-gray-700 text-white border-green-500" v-model="form.color" />
            <InputError class="mt-2 text-red-500" :message="form.errors.color" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <PrimaryButton class="ms-4 bg-green-500 text-white hover:bg-green-700" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                {{ form.id ? 'Actualizar Equipo' : 'Crear Equipo' }}
            </PrimaryButton>
        </div>
    </form>
</template>
