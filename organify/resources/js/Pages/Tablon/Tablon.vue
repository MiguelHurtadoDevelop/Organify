<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'; // Importación del layout de autenticación
import { Head } from '@inertiajs/vue3'; // Importación del componente Head de Inertia
import { defineProps, ref, computed } from 'vue'; // Importación de funciones de Vue
import TareasForm from '@/Pages/Tareas/TareasForm.vue'; // Importación del componente TareasForm
import { router } from '@inertiajs/vue3'; // Importación del enrutador de Inertia
import TareaLayout from '../Tareas/TareaLayout.vue'; // Importación del layout de tarea desde la ruta relativa

// Definición de props esperados por el componente
const props = defineProps({
  tareasTablon: Array
});

// Variable constante para tipo de entidad (en este caso, 'personal')
const tipo = 'personal';

// Variables reactivas para manejar el estado del formulario y detalles de tarea
const showForm = ref(false); // Variable reactiva para mostrar/ocultar el formulario de tareas
const tareaEditar = ref(null); // Referencia reactiva para la tarea que se está editando

// Función para alternar la visualización del formulario de tareas
const toggleForm = () => {
  showForm.value = !showForm.value;
  if (!showForm.value) {
    tareaEditar.value = null;  // Reiniciar tareaEditar cuando se cierra el formulario
  }
}

// Función ejecutada cuando se envía el formulario de tareas
const handleFormSubmitted = () => {
  showForm.value = false;
  tareaEditar.value = null;  // Reiniciar tareaEditar después de enviar el formulario
}

// Función para cerrar el div al hacer clic fuera de él
const closeDivOnClickOutside = (event) => {
  if (event.target.classList.contains('div-overlay')) {
    showForm.value = false;
  }
}

// Variable reactiva para el orden de clasificación de las tareas
const sortOrder = ref('highToLow'); // Orden inicial: de alta a baja prioridad
// Variable reactiva para el estado seleccionado de las tareas
const selectedStatus = ref('todas'); // Estado inicial: todas las tareas

// Función para obtener el valor numérico de prioridad
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

// Computación de tareas filtradas y ordenadas según los filtros seleccionados
const sortedAndFilteredTareas = computed(() => {
  let filteredTareas = props.tareasTablon;
  
  // Aplicar filtro por estado seleccionado
  if (selectedStatus.value !== 'todas') {
    filteredTareas = filteredTareas.filter(tarea => tarea.estado === selectedStatus.value);
  }

  // Ordenar las tareas según el orden seleccionado
  return filteredTareas.sort((a, b) => {
    const aPriority = getPriorityValue(a.prioridad);
    const bPriority = getPriorityValue(b.prioridad);

    if (sortOrder.value === 'highToLow') {
      return bPriority - aPriority; // Orden de alta a baja prioridad
    } else if (sortOrder.value === 'lowToHigh') {
      return aPriority - bPriority; // Orden de baja a alta prioridad
    } else if (sortOrder.value === 'newestFirst') {
      return new Date(b.created_at) - new Date(a.created_at); // Orden por fecha más reciente primero
    } else if (sortOrder.value === 'oldestFirst') {
      return new Date(a.created_at) - new Date(b.created_at); // Orden por fecha más antigua primero
    }
  });
});

// Función para actualizar el orden de clasificación
const updateSortOrder = (event) => {
  sortOrder.value = event.target.value;
}

// Función para actualizar el estado seleccionado de las tareas
const updateSelectedStatus = (event) => {
  selectedStatus.value = event.target.value;
}
</script>

<template>
  <AuthenticatedLayout>
    <div class="mb-5 pb-5">
      <!-- Sort and Filter controls -->
      <h1 class="text-3xl font-mono font-semibold flex justify-center text-gray-800 mb-4">Tablón Personal</h1>
      <div class="flex gap-5 flex-col justify-center mb-6 lg:flex-row items-start  items-center">
        <div class="relative h-10 w-72 min-w-[200px]">
            <select id="sortOrder" v-model="sortOrder" @change="updateSortOrder"
              class="peer h-full w-full rounded-[7px] border border-blue-gray-200 border-t-transparent bg-transparent px-3 py-2.5 font-sans text-sm font-normal text-blue-gray-700 outline outline-0 transition-all placeholder-shown:border placeholder-shown:border-blue-gray-200 placeholder-shown:border-t-blue-gray-200 empty:!bg-gray-900 focus:border-2 focus:border-gray-900 focus:border-t-transparent focus:outline-0 disabled:border-0 disabled:bg-blue-gray-50">
              <option value="highToLow">Prioridad Alta a Baja</option>
              <option value="lowToHigh">Prioridad Baja a Alta</option>
              <option value="newestFirst">Fecha de Creación: Más Recientes</option>
              <option value="oldestFirst">Fecha de Creación: Más Antiguos</option>
            </select>
            <label
              class="before:content[' '] after:content[' '] pointer-events-none absolute left-0 -top-1.5 flex h-full w-full select-none text-[11px] font-normal leading-tight text-blue-gray-400 transition-all before:pointer-events-none before:mt-[6.5px] before:box-border before:block before:h-1.5 before:w-2.5 before:rounded-tl-md before:border-t before:border-l before:border-blue-gray-200 before:transition-all after:pointer-events-none after:mt-[6.5px]  after:box-border after:block after:h-1.5 after:w-2.5 after:flex-grow after:rounded-tr-md after:border-t after:border-r after:border-blue-gray-200 after:transition-all peer-placeholder-shown:text-sm peer-placeholder-shown:leading-[3.75] peer-placeholder-shown:text-blue-gray-500 peer-placeholder-shown:before:border-transparent peer-placeholder-shown:after:border-transparent peer-focus:text-[11px] peer-focus:leading-tight peer-focus:text-gray-900 peer-focus:before:border-t-2 peer-focus:before:border-l-2 peer-focus:before:border-gray-900 peer-focus:after:border-t-2 peer-focus:after:border-r-2 peer-focus:after:border-gray-900 peer-disabled:text-transparent peer-disabled:before:border-transparent peer-disabled:after:border-transparent peer-disabled:peer-placeholder-shown:text-blue-gray-500">
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
            </select>
            <label
              class="before:content[' '] after:content[' '] pointer-events-none absolute left-0 -top-1.5 flex h-full w-full select-none text-[11px] font-normal leading-tight text-blue-gray-400 transition-all before:pointer-events-none before:mt-[6.5px]  before:box-border before:block before:h-1.5 before:w-2.5 before:rounded-tl-md before:border-t before:border-l before:border-blue-gray-200 before:transition-all after:pointer-events-none after:mt-[6.5px]  after:box-border after:block after:h-1.5 after:w-2.5 after:flex-grow after:rounded-tr-md after:border-t after:border-r after:border-blue-gray-200 after:transition-all peer-placeholder-shown:text-sm peer-placeholder-shown:leading-[3.75] peer-placeholder-shown:text-blue-gray-500 peer-placeholder-shown:before:border-transparent peer-placeholder-shown:after:border-transparent peer-focus:text-[11px] peer-focus:leading-tight peer-focus:text-gray-900 peer-focus:before:border-t-2 peer-focus:before:border-l-2 peer-focus:before:border-gray-900 peer-focus:after:border-t-2 peer-focus:after:border-r-2 peer-focus:after:border-gray-900 peer-disabled:text-transparent peer-disabled:before:border-transparent peer-disabled:after:border-transparent peer-disabled:peer-placeholder-shown:text-blue-gray-500">
              Filtrar por:
            </label>
          </div>
      </div>
      

        <div class=" columns-sm" v-if="sortedAndFilteredTareas.length > 0">
          <div class="mb-5"  v-for="tarea in sortedAndFilteredTareas" :key="tarea.id">
            <TareaLayout :tarea="tarea" />
          </div>
          
        </div>
        <div v-else class="w-full flex justify-center text-gray-500 ">No hay tareas en el tablón</div>
      

      <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center div-overlay bg-black bg-opacity-50 backdrop-filter backdrop-blur-sm" @click="closeDivOnClickOutside">
        <div class="w-[22rem] md:w-[70rem] max-w-lg  rounded-lg shadow-lg">
          <TareasForm :tipo="tipo" :fechaIni="tareaEditar ? tareaEditar.fecha_ini : ''" :fechaFin="tareaEditar ? tareaEditar.fecha_fin : ''" @formSubmitted="handleFormSubmitted" @closeForm="toggleForm" />
        </div>
      </div>

      
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.container {
  max-width: 1200px;
  background-color: #f0f4f8; /* Light background for better contrast */
  padding: 2rem; /* Padding around the container */
  border-radius: 8px; /* Rounded corners for the container */
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
}

.card {
  transition: transform 0.2s, box-shadow 0.2s;
  background-color: #ffffff; /* White background for cards */
  border-radius: 12px; /* Rounded corners for cards */
  overflow: hidden; /* Hide overflow */
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow for cards */
  margin: 0.5rem; /* Margin around each card */
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* More pronounced shadow on hover */
}

.card-body {
  background-color: white;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 1rem; /* Padding inside cards */
}

.card-title {
  color: #2d3748; /* Darker color for title */
  font-weight: bold; /* Bold font for title */
}

.card-text {
  color: #4a5568; /* Gray color for text */
  margin-top: 0.5rem; /* Margin above text */
}

.div-overlay {
  display: flex;
  align-items: center;
  justify-content: center;
}

.fixed-button {
  background-color: #ff6b6b; /* Red background for the button */
  color: white; /* White color for the button text */
  border-radius: 50%; /* Circular button */
  padding: 1rem; /* Padding inside the button */
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Shadow for the button */
  transition: background-color 0.2s; /* Smooth background color transition */
}

.fixed-button:hover {
  background-color: #ff4757; /* Darker red on hover */
}
</style>
