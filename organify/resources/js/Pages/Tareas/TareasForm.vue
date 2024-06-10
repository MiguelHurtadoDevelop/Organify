<script setup>
import { ref, watch } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
    tarea: Object,
    fechaIni: { type: [Date, String, null] },
    fechaFin: { type: [Date, String, null] },
    tipo: String,
    equipoId: { type: [Number, null], default: null },
});

const emit = defineEmits(['formSubmitted', 'closeForm']);

function toLocalISOString(date) {
    const tzoffset = date.getTimezoneOffset() * 60000;
    const localISOTime = new Date(date - tzoffset).toISOString().slice(0, 16);
    return localISOTime;
}

const form = useForm({
    id: null,
    portada: null,
    archivos: [],
    archivosExistentes: [],
    archivosParaEliminar: [],
    titulo: '',
    descripcion: '',
    fecha_ini: props.fechaIni ? toLocalISOString(new Date(props.fechaIni)) : '',
    fecha_fin: props.fechaFin ? toLocalISOString(new Date(props.fechaFin)) : '',
    prioridad: '',
    equipo_id: props.equipoId || null,
    color: '#000000',
    currentImage: null,
});

if (props.tarea) {
    watch(props.tarea, (newVal) => {
        if (newVal) {
            form.id = newVal.id || null;
            form.currentImage = newVal.imagen ? `/archivos/${newVal.imagen}` : null;
            form.titulo = newVal.titulo || '';
            form.descripcion = newVal.descripcion || '';
            form.fecha_ini = newVal.fecha_ini ? toLocalISOString(new Date(newVal.fecha_ini)) : '';
            form.fecha_fin = newVal.fecha_fin ? toLocalISOString(new Date(newVal.fecha_fin)) : '';
            form.prioridad = newVal.prioridad || '';
            form.archivosExistentes = newVal.archivos || [];
            form.color = newVal.color || '#000000';
        }
    }, { immediate: true });
}

const handleFileChange = (event) => {
    form.portada = event.target.files[0];
    form.currentImage = URL.createObjectURL(event.target.files[0]);
};

const handleFilesChange = (event) => {
    form.archivos = [...event.target.files];
};

const submit = () => {
    const data = new FormData();
    data.append('_method', form.id ? 'put' : 'post');

    if (form.portada) {
        data.append('portada', form.portada);
    }

    const routeName = form.id ? 
        (props.tipo === 'personal' ? 'tareaPersonal.update' : 'tareaEquipo.update') :
        (props.tipo === 'personal' ? 'tareaPersonal.create' : 'tareaEquipo.create');

    form.post(route(routeName, form.id ? form.id : ''), {
        data,
        onSuccess: () => {
            form.reset();
            emit('formSubmitted');
        },
        onError: () => {},
    });
};

const elimiarArchivo = (archivoId) => {
    form.archivosParaEliminar.push(archivoId);
    form.archivosExistentes = form.archivosExistentes.filter(archivo => archivo.id !== archivoId);
};
</script>

<template>
    <form @submit.prevent="submit" enctype="multipart/form-data" class="w-70 p-4 h-[35rem] md:h-[45rem] overflow-auto bg-gray-800 text-white relative rounded-lg">
        <button @click="$emit('closeForm')" class="text-gray-400 absolute right-2 top-2 text-xl font-bold focus:outline-none">
            <font-awesome-icon :icon="['fas', 'xmark']" />
        </button>
        
        <div class="mt-3">
            <InputLabel for="portada" value="Portada" class="text-white"/>
            <div class="flex flex-col items-center justify-center w-full relative">
                <input 
                    type="file" 
                    id="portada" 
                    name="portada" 
                    class="mt-1 hidden w-full z-10 opacity-0 cursor-pointer" 
                    @change="handleFileChange" 
                />
                <label 
                    for="portada" 
                    class="flex flex-col items-center justify-center w-full h-64 border-2 border-green-500 border-dashed rounded-lg cursor-pointer bg-gray-700 hover:bg-gray-600">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <div v-if="form.currentImage" class="absolute h-64 inset-0 flex items-center justify-center bg-black border-2 border-green-500 border-dashed rounded-lg cursor-pointer">
                            <img class="w-full h-full object-contain " :src="form.currentImage" alt="Vista previa de la imagen">
                        </div>
                        <font-awesome-icon :icon="['fas', 'camera']" class="text-white text-8xl" />
                        <p class="mb-2 text-sm text-white"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                    </div>                 
                    
                </label>
            </div>
            <InputError :message="form.errors.portada" class="mt-2 text-red-500"/>
        </div>

        <div class="mt-4">
            <InputLabel for="archivos" value="Archivos" class="text-white"/>
            <input type="file" id="archivos" name="archivos" class="mt-1 block w-full text-green-500 bg-gray-700 border-green-500" multiple @change="handleFilesChange"/>
            <InputError :message="form.errors.archivos" class="mt-2 text-red-500"/>
        </div>

        <div v-if="form.archivosExistentes.length > 0">
            <div class="mt-4">
                <InputLabel value="Archivos existentes" class="text-white"/>
                <div class="mt-1 block w-full">
                    <div v-for="archivo in form.archivosExistentes" :key="archivo.id" class="text-green-500">
                        <a :href="`/archivos/${archivo.nombre}`" target="_blank">{{ archivo.nombre }}</a>
                        <button type="button" @click="elimiarArchivo(archivo.id)" class="text-red-500">Eliminar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <InputLabel for="titulo" value="Título" class="text-white"/>
            <TextInput id="titulo" v-model="form.titulo" class="mt-1 block w-full bg-gray-700 text-white border-green-500" />
            <InputError :message="form.errors.titulo" class="mt-2 text-red-500"/>
        </div>

        <div class="mt-4">
            <InputLabel for="descripcion" value="Descripción" class="text-white"/>
            <textarea id="descripcion" v-model="form.descripcion" class="mt-1 block w-full bg-gray-700 text-white border-green-500"></textarea>
            <InputError :message="form.errors.descripcion" class="mt-2 text-red-500"/>
        </div>

        <div class="mt-4">
            <InputLabel for="fecha_ini" value="Fecha de inicio" class="text-white"/>
            <input type="datetime-local" id="fecha_ini" v-model="form.fecha_ini" class="mt-1 block w-full bg-gray-700 text-white border-green-500"/>
            <InputError :message="form.errors.fecha_ini" class="mt-2 text-red-500"/>
        </div>

        <div class="mt-4">
            <InputLabel for="fecha_fin" value="Fecha de fin" class="text-white"/>
            <input type="datetime-local" id="fecha_fin" v-model="form.fecha_fin" class="mt-1 block w-full bg-gray-700 text-white border-green-500"/>
            <InputError :message="form.errors.fecha_fin" class="mt-2 text-red-500"/>
        </div>

        <div class="mt-4">
            <InputLabel for="prioridad" value="Prioridad" class="text-white"/>
            <select id="prioridad" v-model="form.prioridad" class="mt-1 block w-full bg-gray-700 text-white border-green-500">
                <option value="alta">Alta</option>
                <option value="media">Media</option>
                <option value="baja">Baja</option>
            </select>
            <InputError :message="form.errors.prioridad" class="mt-2 text-red-500"/>
        </div>

        <div class="mt-4" v-if="tipo !== 'equipo'">
            <InputLabel for="color" value="Color" class="text-white"/>
            <input type="color" id="color" v-model="form.color" class="mt-1 block w-full bg-gray-700 text-white border-green-500"/>
            <InputError :message="form.errors.color" class="mt-2 text-red-500"/>
        </div>

        <div class="flex items-center justify-end mt-4">
            <PrimaryButton class="ml-4 bg-green-600 hover:bg-green-700 text-white">
                {{ form.id ? 'Actualizar' : 'Crear' }}
            </PrimaryButton>
        </div>
    </form>
</template>
