<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { defineProps, ref, computed } from 'vue';
import TareasForm from '@/Pages/Tareas/TareasForm.vue';
import { router, Link } from '@inertiajs/vue3';
import TareaLayout from '@/Pages/Tareas/TareaLayout.vue';
import Equipo from '@/Pages/Equipo/Equipo.vue';

const props = defineProps({
    tareasTablonEquipo: Array,
    equipo: Object,
    miembros: Array,
    rol: String,
    authUser: Object,
});



const tipo = 'equipo';
const showForm = ref(false);
const tareaEditar = ref(null);
const showEquipo = ref(false);


const toggleForm = () => {
  showForm.value = !showForm.value;
  if (!showForm.value) {
    tareaEditar.value = null;  // Reset tareaEditar when form is closed
  }
}

const handleFormSubmitted = () => {
  showForm.value = false;
  tareaEditar.value = null;  // Reset tareaEditar after form submission
}

const closeDivOnClickOutside = (event) => {
  if (event.target.classList.contains('div-overlay')) {
    showForm.value = false;
    showEquipo.value = false; 
  }
}

// New variables for sorting and filtering
const sortOrder = ref('highToLow'); // 'highToLow', 'lowToHigh', 'newestFirst', 'oldestFirst'
const selectedStatus = ref('todas'); // 'todas', 'to-do', 'doing', 'done', 'asignadas', 'sin asignar'

// Helper function to get numeric value for priority
const getPriorityValue = (priority) => {
  switch (priority) {
    case 'alta':
      return 3;
    case 'media':
      return 2;
    case 'baja':
      return 1;
    default:
      return 0;
  }
}

// Computed property to sort and filter tasks
const sortedAndFilteredTareas = computed(() => {
  let filteredTareas = props.tareasTablonEquipo;
  
  if (selectedStatus.value !== 'todas') {
    if (selectedStatus.value === 'asignadas') {
      filteredTareas = filteredTareas.filter(tarea => tarea.asignada);
    } else if (selectedStatus.value === 'sinAsignar') {
      filteredTareas = filteredTareas.filter(tarea => !tarea.asignada);
    } else {
      filteredTareas = filteredTareas.filter(tarea => tarea.estado === selectedStatus.value);
    }
  }

  return filteredTareas.sort((a, b) => {
    const aPriority = getPriorityValue(a.prioridad);
    const bPriority = getPriorityValue(b.prioridad);

    if (sortOrder.value === 'highToLow') {
      return bPriority - aPriority;
    } else if (sortOrder.value === 'lowToHigh') {
      return aPriority - bPriority;
    } else if (sortOrder.value === 'newestFirst') {
      return new Date(b.created_at) - new Date(a.created_at);
    } else if (sortOrder.value === 'oldestFirst') {
      return new Date(a.created_at) - new Date(b.created_at);
    }
  });
});

// Function to update sort order
const updateSortOrder = (event) => {
  sortOrder.value = event.target.value;
}

// Function to update selected status
const updateSelectedStatus = (event) => {
  selectedStatus.value = event.target.value;
}

const toggleEquipo = () => {
  showEquipo.value = !showEquipo.value;
}
</script>

<template>
  <Head title="Tablón de Equipo" />

  <AuthenticatedLayout>
    <div class="mb-5">
      <div class="flex gap-3 flex-col lg:flex-row items-start justify-between lg:items-center border-b-2 mb-6">
        <div @click="toggleEquipo" preserve-scroll class="cursor-pointer">
          <div class="flex items-center gap-3 ">
            <div :style="{ borderColor: equipo.color, boxShadow: `0 2px 4px ${equipo.color}` }" class="w-24 h-24 mb-3 bg-black rounded-full overflow-hidden border-2">
              <img  class="w-full h-full object-cover" :src="`/archivos/${equipo.foto}`" alt="Foto de equipo">
            </div>
            <p class="text-3xl font-mono font-bold">{{ equipo.nombre }}</p>
          </div>
        </div>

        
        <div class="flex pb-5 w-full justify-center lg:pb-0 lg:w-auto lg:justify-end">
          <button
            v-if="!showForm"
            class=" bottom-8  bg-red-600 text-white rounded-full py-2 px-4 shadow-lg hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-blue-300 text-xl font-bold"
            :style="{ backgroundColor: equipo.color, boxShadow: `0 2px 4px ${equipo.color}` }"
            @click="toggleForm"
          >
            Crear Tarea para {{ equipo.nombre }}
          </button>
        </div>
      </div>
            

      <!-- Sort and Filter controls -->
      <div class="flex  gap-5 flex-col justify-center mb-6 lg:flex-row items-start  items-center">
        <div class="relative h-10 w-72 min-w-[200px]">
            <select id="sortOrder" v-model="sortOrder" @change="updateSortOrder"
              class="peer h-full w-full rounded-[7px] border border-blue-gray-200 border-t-transparent bg-transparent px-3 py-2.5 font-sans text-sm font-normal text-blue-gray-700 outline outline-0 transition-all placeholder-shown:border placeholder-shown:border-blue-gray-200 placeholder-shown:border-t-blue-gray-200 empty:!bg-gray-900 focus:border-2 focus:border-gray-900 focus:border-t-transparent focus:outline-0 disabled:border-0 disabled:bg-blue-gray-50">
              <option value="highToLow">Prioridad Alta a Baja</option>
              <option value="lowToHigh">Prioridad Baja a Alta</option>
              <option value="newestFirst">Fecha de Creación: Más Recientes</option>
              <option value="oldestFirst">Fecha de Creación: Más Antiguos</option>
            </select>
            <label
              class="before:content[' '] after:content[' '] pointer-events-none absolute left-0 -top-1.5 flex h-full w-full select-none text-[11px] font-normal leading-tight text-blue-gray-400 transition-all before:pointer-events-none before:mt-[6.5px] before:mr-1 before:box-border before:block before:h-1.5 before:w-2.5 before:rounded-tl-md before:border-t before:border-l before:border-blue-gray-200 before:transition-all after:pointer-events-none after:mt-[6.5px] after:ml-1 after:box-border after:block after:h-1.5 after:w-2.5 after:flex-grow after:rounded-tr-md after:border-t after:border-r after:border-blue-gray-200 after:transition-all peer-placeholder-shown:text-sm peer-placeholder-shown:leading-[3.75] peer-placeholder-shown:text-blue-gray-500 peer-placeholder-shown:before:border-transparent peer-placeholder-shown:after:border-transparent peer-focus:text-[11px] peer-focus:leading-tight peer-focus:text-gray-900 peer-focus:before:border-t-2 peer-focus:before:border-l-2 peer-focus:before:border-gray-900 peer-focus:after:border-t-2 peer-focus:after:border-r-2 peer-focus:after:border-gray-900 peer-disabled:text-transparent peer-disabled:before:border-transparent peer-disabled:after:border-transparent peer-disabled:peer-placeholder-shown:text-blue-gray-500">
              Ordenar por:
            </label>
          </div>
              
          <div class="relative h-10 w-72 min-w-[200px]">
            <select id="selectedStatus" v-model="selectedStatus" @change="updateSelectedStatus"
              class="peer h-full w-full rounded-[7px] border border-blue-gray-200 border-t-transparent bg-transparent px-3 py-2.5 font-sans text-sm font-normal text-blue-gray-700 outline outline-0 transition-all placeholder-shown:border placeholder-shown:border-blue-gray-200 placeholder-shown:border-t-blue-gray-200 empty:!bg-gray-900 focus:border-2 focus:border-gray-900 focus:border-t-transparent focus:outline-0 disabled:border-0 disabled:bg-blue-gray-50">
              <option value="todas">Todas</option>
              <option value="to-do">To-Do</option>
              <option value="doing">Doing</option>
              <option value="done">Done</option>
              <option value="asignadas">Asignadas</option>
              <option value="sinAsignar">Sin Asignar</option>
            </select>
            <label
              class="before:content[' '] after:content[' '] pointer-events-none absolute left-0 -top-1.5 flex h-full w-full select-none text-[11px] font-normal leading-tight text-blue-gray-400 transition-all before:pointer-events-none before:mt-[6.5px] before:mr-1 before:box-border before:block before:h-1.5 before:w-2.5 before:rounded-tl-md before:border-t before:border-l before:border-blue-gray-200 before:transition-all after:pointer-events-none after:mt-[6.5px] after:ml-1 after:box-border after:block after:h-1.5 after:w-2.5 after:flex-grow after:rounded-tr-md after:border-t after:border-r after:border-blue-gray-200 after:transition-all peer-placeholder-shown:text-sm peer-placeholder-shown:leading-[3.75] peer-placeholder-shown:text-blue-gray-500 peer-placeholder-shown:before:border-transparent peer-placeholder-shown:after:border-transparent peer-focus:text-[11px] peer-focus:leading-tight peer-focus:text-gray-900 peer-focus:before:border-t-2 peer-focus:before:border-l-2 peer-focus:before:border-gray-900 peer-focus:after:border-t-2 peer-focus:after:border-r-2 peer-focus:after:border-gray-900 peer-disabled:text-transparent peer-disabled:before:border-transparent peer-disabled:after:border-transparent peer-disabled:peer-placeholder-shown:text-blue-gray-500">
              Filtrar por:
            </label>
          </div>
      </div>
      

        <div class="columns-sm " v-if="sortedAndFilteredTareas.length > 0">
          <div class="mb-5" v-for="tarea in sortedAndFilteredTareas" :key="tarea.id"  >
            <TareaLayout  :tarea="tarea" :miembros="miembros" :rol="rol" :authUser="authUser"/>
          </div>
        </div>
        <div v-else class="w-full flex justify-center text-gray-500 ">No hay tareas en el tablón</div>
      

      <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center div-overlay bg-black bg-opacity-50 backdrop-filter backdrop-blur-sm" @click="closeDivOnClickOutside">
        <div class="w-[22rem] md:w-[70rem] max-w-lg  bg-white rounded-lg shadow-lg">
          <TareasForm :equipoId="equipo.id" :tipo="tipo" :tarea="tareaEditar" :fechaIni="tareaEditar ? tareaEditar.fecha_ini : ''" :fechaFin="tareaEditar ? tareaEditar.fecha_fin : ''" @formSubmitted="handleFormSubmitted" @closeForm="toggleForm" />
        </div>
      </div>

      <div v-if="showEquipo" class="fixed inset-0 z-50 flex items-center justify-center div-overlay bg-black bg-opacity-50 backdrop-filter backdrop-blur-sm" @click="closeDivOnClickOutside">
        <div class="w-[22rem] md:w-[70rem] max-w-lg  bg-white rounded-lg shadow-lg">
          <Equipo :equipo="equipo" :miembros="miembros" :rol="rol" :aceptado="true" @closeEquipo="toggleEquipo"/>
        </div>
      </div>

      
     
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.div-overlay {
  display: flex;
  align-items: center;
  justify-content: center;
}

</style>
