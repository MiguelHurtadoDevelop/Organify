<script setup>
import { defineProps, ref } from 'vue'; // Importación de funciones de Vue
import { format, isSameDay } from 'date-fns'; // Importación de funciones de date-fns para manejo de fechas
import { es } from 'date-fns/locale'; // Importación del locale español para date-fns
import TareasForm from '@/Pages/Tareas/TareasForm.vue'; // Importación del componente TareasForm
import { router, useForm } from '@inertiajs/vue3'; // Importación del enrutador y useForm de Inertia
import InputError from '@/Components/InputError.vue'; // Importación del componente InputError

// Definición de props esperados por el componente
const props = defineProps({
  tarea: Object,
  miembros: Array|null,
  rol: String|null,
  authUser: Object|null,
});

// Constante para el tipo de tarea (personal o de equipo)
const tipo = props.tarea.tipo;

// Variables reactivas para manejar el estado del formulario y detalles de tarea
const showForm = ref(false); // Variable reactiva para mostrar/ocultar el formulario de tarea
const showDetails = ref(false); // Variable reactiva para mostrar/ocultar los detalles de la tarea
const showConfirmModal = ref(false); // Variable reactiva para mostrar/ocultar el modal de confirmación de eliminación
const showDeletedModal = ref(false); // Variable reactiva para mostrar/ocultar el modal de confirmación de eliminación exitosa
const tareaEliminar = ref(null); // Referencia reactiva para almacenar la tarea que se va a eliminar
const tareaDetalles = ref({}); // Referencia reactiva para almacenar los detalles de la tarea mostrada
const tareaEditar = ref(null); // Referencia reactiva para la tarea que se está editando
const showAsignar = ref(false); // Variable reactiva para mostrar/ocultar el formulario de asignación de tarea
const showSuccessModal = ref(false); // Variable reactiva para mostrar/ocultar el modal de éxito después de una acción
const successMessage = ref(''); // Mensaje de éxito para mostrar en el modal
const deletedMessage = ref('La tarea ha sido eliminada exitosamente.'); // Mensaje de eliminación exitosa para mostrar en el modal
const showAutoasignar = ref(false); // Variable reactiva para mostrar/ocultar el formulario de autoasignación de tarea

// Formulario reactiva para asignar tarea
const formAsignar = useForm({
  tarea_id: props.tarea.id,
  user_id: '', // Usuario asignado
  fecha_ini: props.tarea.fecha_ini || '', // Fecha de inicio de la tarea (opcional)
  fecha_fin: props.tarea.fecha_fin || '', // Fecha de fin de la tarea (opcional)
});

// Función para formatear la fecha de inicio y fin de la tarea
const formatDate = (startDate, endDate) => {
  const start = new Date(startDate);
  const end = new Date(endDate);

  if (isSameDay(start, end)) {
    return `${format(start, 'dd MMMM yyyy', { locale: es })} ${format(start, 'HH:mm', { locale: es })} - ${format(end, 'HH:mm', { locale: es })}`;
  } else {
    return `${format(start, 'dd MMMM yyyy HH:mm', { locale: es })} - ${format(end, 'dd MMMM yyyy HH:mm', { locale: es })}`;
  }
};

// Función para alternar la visualización del formulario de tarea
const toggleForm = () => {
  showForm.value = !showForm.value;
  if (!showForm.value) {
    tareaEditar.value = null; // Reiniciar tareaEditar cuando se cierra el formulario
  }
}

// Función ejecutada cuando se envía el formulario de tarea
const handleFormSubmitted = () => {
  showForm.value = false;
  tareaEditar.value = null; // Reiniciar tareaEditar después de enviar el formulario
}

// Función para cerrar el div al hacer clic fuera de él
const closeDivOnClickOutside = (event) => {
  if (event.target.classList.contains('div-overlay')) {
    showForm.value = false;
    showDetails.value = false;
    showAsignar.value = false;
    showSuccessModal.value = false;
    showConfirmModal.value = false;
    showDeletedModal.value = false;
    showAutoasignar.value = false;
  }
}

// Función para abrir los detalles de una tarea específica
const abrirDetalles = (tarea) => {
  if (tarea) {
    showDetails.value = true;
    tareaDetalles.value = tarea;
  }
}

// Función para eliminar una tarea
const eliminarTarea = () => {
  router.delete(route('tarea.delete', tareaEliminar.value), {
    onSuccess: () => {
      showDeletedModal.value = true;
      showDetails.value = false;
      showConfirmModal.value = false;
    },
  });
}

// Función para mostrar el modal de confirmación de eliminación de tarea
const confirmEliminarTarea = (id) => {
  tareaEliminar.value = id;
  showConfirmModal.value = true;
}

// Función para editar una tarea
const editarTarea = (tarea) => {
  showDetails.value = false;
  showForm.value = true;
  tareaEditar.value = tarea;
}

// Función para mostrar el formulario de asignación de tarea
const asignarTarea = () => {
  showAsignar.value = true;
}

// Función para autoasignar una tarea al usuario autenticado
const autoasignarTarea = () => {
  showAutoasignar.value = true;
  formAsignar.user_id = props.authUser.id; // Asignación automática al usuario autenticado
}

// Función ejecutada cuando se envía el formulario de asignación de tarea
const handleAsignarFormSubmitted = () => {
  formAsignar.post(route('tarea.asignar', props.tarea.id), {
    onSuccess: () => {
      const assignedMember = props.miembros.find(member => member.user.id === formAsignar.user_id);
      const assignedMessage = `La tarea ha sido asignada a ${assignedMember.user.nombre} el ${formatDate(formAsignar.fecha_ini, formAsignar.fecha_fin )}.`;
      successMessage.value = assignedMessage;
      showSuccessModal.value = true;
      formAsignar.reset(); // Reiniciar el formulario de asignación
      showAsignar.value = false; // Ocultar el formulario de asignación
      showAutoasignar.value = false; // Ocultar el formulario de autoasignación
    },
  });
};

// Función para cambiar el estado de una tarea
const cambiarEstado = (id, estado) => {
  router.post(route('tarea.estado', id), {
    estado: estado, // Nuevo estado de la tarea
    onSuccess: () => {
      showDetails.value = false; // Ocultar los detalles de la tarea después de cambiar el estado
    },
  });
}
</script>

<template>
  <div :key="tarea.id" :style="{ borderColor: tarea.color, boxShadow: `0 2px 4px ${tarea.color}` }" class="border border-gray-300 rounded-lg shadow-md overflow-hidden bg-white hover:shadow-lg transition-shadow duration-300 w-full">
    <div @click="abrirDetalles(tarea)" class="relative cursor-pointer">
       <p :class=" {
          'text-green-600': tarea.estado === 'done',
          'text-yellow-600': tarea.estado === 'doing',
          'text-red-600': tarea.estado === 'to-do',
          'absolute': true,
          'right-2' : true,
          'top-1.5' : true,
        }" class="text-xl font-bold truncate"><font-awesome-icon :icon="['fas', 'circle']" /></p>
      <div v-if="tarea.imagen" class="h-40 overflow-hidden bg-black">
        <img :src="`/archivos/${tarea.imagen}`" alt="portada" class="w-full h-full object-contain"/>
      </div>
      <div class="p-4">
        <p class="text-xl font-bold truncate"><font-awesome-icon :icon="['fas', 'tasks']" class="mr-2 text-gray-700" />{{ tarea.titulo }}</p>
        <p v-if="tarea.descripcion" class="text-sm text-gray-600 truncate"><font-awesome-icon :icon="['fas', 'align-left']" class=" mr-2 text-gray-700" />{{ tarea.descripcion }}</p>
        <p v-if="tarea.fecha_ini && tarea.fecha_fin" class="text-sm text-gray-500"> <font-awesome-icon :icon="['fas', 'calendar-alt']" class="mr-2" />{{ formatDate(tarea.fecha_ini, tarea.fecha_fin) }}</p>
        <p class="text-gray-700 flex items-center">
              <font-awesome-icon :icon="['fas', 'exclamation-circle']" class="mr-2 text-gray-700" />

              <strong class="mr-1">Prioridad: </strong> 
              <span :class=" {
                  'text-green-600': tarea.prioridad === 'baja',
                  'text-yellow-600': tarea.prioridad === 'media',
                  'text-red-600': tarea.prioridad === 'alta',
                }">
                {{ tarea.prioridad }}
              </span>
            </p>
      </div>
    </div>
    <div class="p-2 flex  gap-3 justify-end border-t border-gray-200">
      <button @click="editarTarea(tarea)" v-if="tarea.tipo === 'personal' || (tarea.tipo === 'equipo'  && rol === 'manager')" class="bg-gray-600 text-white py-2 px-4 rounded-full hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300 w-30">
        <font-awesome-icon :icon="['fas', 'pen-to-square']" /> 
      </button>
      <button @click="confirmEliminarTarea(tarea.id)" v-if="tarea.tipo === 'personal' || (tarea.tipo === 'equipo' && rol === 'manager')" class="bg-gray-600 text-white py-2 px-4 rounded-full hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300 w-30">
        <font-awesome-icon :icon="['fas', 'trash-can']"  /> 
      </button>
    </div>
  </div>

  <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center div-overlay bg-black bg-opacity-50 backdrop-blur-sm" @click="closeDivOnClickOutside">
    <div class="w-[22rem] md:w-[70rem] max-w-lg  bg-white rounded-lg shadow-lg">
      <TareasForm :equipoId="tareaEditar.equipo_id" :tipo="tipo" :tarea="tareaEditar" :fechaIni="tareaEditar ? tareaEditar.fecha_ini : ''" :fechaFin="tareaEditar ? tareaEditar.fecha_fin : ''" @formSubmitted="handleFormSubmitted" @closeForm="toggleForm" />
    </div>
  </div>

  <div v-if="showDetails" @click="closeDivOnClickOutside" class="fixed inset-0 z-50 flex items-center justify-center div-overlay bg-black bg-opacity-50 backdrop-blur-sm">
    <div class="w-11/12 sm:w-[40rem] md:w-[45rem] lg:w-[60rem] bg-white rounded-lg shadow-lg relative max-h-[40rem] overflow-auto">
      <!-- Close Button -->
      <button @click="showDetails = false" class="text-gray-400 absolute right-2 top-2 text-xl font-bold focus:outline-none">
        <font-awesome-icon :icon="['fas', 'xmark']" />
      </button>
      <!-- Image Header -->
      <div v-if="tareaDetalles.imagen" class="h-60 overflow-hidden bg-black rounded-t-lg">
        <img :src="`/archivos/${tareaDetalles.imagen}`" alt="portada" class="w-full h-full object-contain" />
      </div>
      <!-- Modal Content -->
      <div class="p-5 flex flex-col md:flex-row justify-between h-full">
        <!-- Title and Description -->
        <div class="md:w-4/5">
          <div class="mb-5">
            <h1 class="text-3xl font-bold flex items-center mb-3">
              <font-awesome-icon :icon="['fas', 'tasks']" class="mr-2 text-gray-700" />
              {{ tareaDetalles.titulo }}
            </h1>
            <p v-if="tareaDetalles.descripcion" class="text-gray-700 text-xl">
              <font-awesome-icon :icon="['fas', 'align-left']" class=" mr-2 text-gray-700" />
              <strong>Descripción:</strong>
            </p>
            <p v-if="tareaDetalles.descripcion" class="text-gray-600 text-lg">{{ tareaDetalles.descripcion }}</p>

          </div>
          <!-- Date and Priority -->
          <div class="mb-2">
            <p v-if="tarea.fecha_ini && tarea.fecha_fin" class="text-gray-500 flex items-center mb-2 ">
              <font-awesome-icon :icon="['fas', 'calendar-alt']" class="mr-2" />
              {{ formatDate(tarea.fecha_ini, tarea.fecha_fin) }}
            </p>
            <p class="text-gray-700 flex items-center">
              <font-awesome-icon :icon="['fas', 'exclamation-circle']" class="mr-2 text-gray-700" />

              <strong class="mr-1">Prioridad: </strong> 
              <span :class=" {
                  'text-green-600': tareaDetalles.prioridad === 'baja',
                  'text-yellow-600': tareaDetalles.prioridad === 'media',
                  'text-red-600': tareaDetalles.prioridad === 'alta',
                }">
                {{ tareaDetalles.prioridad }}
              </span>
            </p>
          </div>
          <!-- Attached Files -->
          <div v-if="tareaDetalles.archivos.length > 0" class="mb-2">
            <h2 class="text-lg font-bold flex items-center mb-2">
              <font-awesome-icon :icon="['fas', 'paperclip']" class="mr-2" />
              Archivos Adjuntos
            </h2>
            <ul>
              <li v-for="archivo in tareaDetalles.archivos" :key="archivo" class="mb-2">
                <div v-if="['jpeg', 'jpg', 'png', 'gif'].some(ext => archivo.nombre.endsWith(ext))" class="flex items-center space-x-2">
                  <img :src="`/archivos/${archivo.nombre}`" alt="archivo" class="w-16 h-16 object-cover rounded-lg" />
                  <a :href="`/archivos/${archivo.nombre}`" target="_blank" class="text-blue-500 hover:underline">{{ archivo.nombre }}</a>
                </div>
                <div v-else>
                  <a :href="`/archivos/${archivo.nombre}`" target="_blank" class="text-blue-500 hover:underline">{{ archivo.nombre }}</a>
                </div>
              </li>
            </ul>
          </div>
          <!-- Radio Inputs -->
          <div class="mb-4">
            <h2 class="text-lg font-bold flex items-center mb-2">
              <font-awesome-icon :icon="['fas', 'tasks']" class="mr-2" />
              Estado de la Tarea
            </h2>
            <div class="radio-inputs rounded-full bg-gray-600">
              <label class="radio">
                  <input type="radio" class="rounded-full" v-model="tarea.estado" name="radio" value="to-do" @change="cambiarEstado(tarea.id,'to-do')">
                  <span class="name to-do rounded-full">To Do</span>
              </label>
              <label class="radio">
                  <input type="radio" class="rounded-full" v-model="tarea.estado" name="radio" value="doing" @change="cambiarEstado(tarea.id, 'doing')">
                  <span class="name doing rounded-full">Doing</span>
              </label>
              <label class="radio">
                  <input type="radio" class="rounded-full" v-model="tarea.estado" name="radio" value="done" @change="cambiarEstado(tarea.id,'done')">
                  <span class="name done rounded-full">Done</span>
              </label>
            </div>
          </div>
        </div>
        
        <!-- Buttons -->
        <div class="flex md:flex-col justify-between md:justify-start gap-3 md:pt-12 ">
          <button @click="editarTarea(tareaDetalles)" v-if="tareaDetalles.tipo === 'personal' || (tareaDetalles.tipo === 'equipo' && rol === 'manager')" class="bg-gray-600 text-white py-2 px-4 rounded-full hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300 w-35">
              <font-awesome-icon :icon="['fas', 'pen-to-square']" class="sm:mr-2" /> 
              <span class="hidden md:inline">
                  Editar
              </span>
          </button>
          <button @click="asignarTarea" v-if="tareaDetalles.tipo === 'equipo' && tareaDetalles.asignada !== 1 && rol === 'manager'" class="bg-gray-600 text-white py-2 px-4 rounded-full hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300 w-35">
              <font-awesome-icon :icon="['fas', 'user-tag']" class="sm:mr-2" />
              <span class="hidden md:inline">
                  Asignar
              </span>
          </button>
          <button @click="autoasignarTarea" v-if="tareaDetalles.tipo === 'equipo' && tareaDetalles.asignada !== 1 && rol != 'manager'" class="bg-gray-600 text-white py-2 px-4 rounded-full hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300 w-35">
              <font-awesome-icon :icon="['fas', 'user-tag']" class="sm:mr-2" />
              <span class="hidden md:inline">
                  Autoasignar
              </span>
          </button>
          <button @click="confirmEliminarTarea(tareaDetalles.id)" v-if="tareaDetalles.tipo === 'personal' || (tareaDetalles.tipo === 'equipo' && rol === 'manager')" class="bg-gray-600 text-white py-2 px-4 rounded-full hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300 w-35">
              <font-awesome-icon :icon="['fas', 'trash-can']" class="sm:mr-2" />
              <span class="hidden md:inline">
                  Eliminar
              </span>
          </button>
      </div>

      </div>
    </div>
  </div>

  <div v-if="showAsignar" @click="closeDivOnClickOutside" class="fixed inset-0 z-50 flex items-center justify-center div-overlay bg-black bg-opacity-50 backdrop-blur-sm">
    <div class="w-[22rem] md:w-[70rem] max-w-lg p-4 max-h-[35rem] md:max-h-[45rem] bg-gray-800 relative rounded-lg shadow-md overflow-auto">
      <h3 class="text-2xl font-bold text-white mb-4">Asignar Tarea</h3>
      
      <form @submit.prevent="handleAsignarFormSubmitted">
        <button @click="showAsignar = false" class="text-gray-400 absolute right-2 top-2 text-xl font-bold focus:outline-none">
            <font-awesome-icon :icon="['fas', 'xmark']" />
        </button>
        <div class="mt-4">
          <label for="user_id" class="block text-sm font-medium text-white">Seleccionar miembro</label>
          <select id="user_id" v-model="formAsignar.user_id" class="mt-1 block w-full">
            <option value="">Seleccionar miembro</option>
            <option v-for="miembro in miembros" :key="miembro.user.id" :value="miembro.user.id">{{ miembro.user.nombre }}</option>
          </select>
          <InputError :message="formAsignar.errors?.user_id" class="mt-2"/>
        </div>

        <div class="mt-4">
          <label for="fecha_ini" class="block text-sm font-medium text-white">Fecha de inicio</label>
          <input type="datetime-local" id="fecha_ini" v-model="formAsignar.fecha_ini" class="mt-1 block w-full"/>
          <InputError :message="formAsignar.errors?.fecha_ini" class="mt-2"/>
        </div>

        <div class="mt-4">
          <label for="fecha_fin" class="block text-sm font-medium text-white">Fecha de fin</label>
          <input type="datetime-local" id="fecha_fin" v-model="formAsignar.fecha_fin" class="mt-1 block w-full"/>
          <InputError :message="formAsignar.errors?.fecha_fin" class="mt-2"/>
        </div>
        

        <div class="flex items-center justify-end mt-4">
          <button type="submit" class="text-white bg-green-500 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 font-medium rounded-full text-sm px-4 py-2 transition-colors duration-200">Asignar Tarea</button>
        </div>
      </form>
    </div>
  </div>

  <div v-if="showAutoasignar" @click="closeDivOnClickOutside" class="fixed inset-0 z-50 flex items-center justify-center div-overlay bg-black bg-opacity-50 backdrop-blur-sm">
    <div class="w-[22rem] md:w-[70rem] max-w-lg p-4 max-h-[35rem] md:max-h-[45rem] bg-gray-800 relative rounded-lg shadow-md overflow-auto">
      <h3 class="text-2xl font-bold text-white mb-4">Autoasignar Tarea</h3>
      <form @submit.prevent="handleAsignarFormSubmitted">
        <button @click="showAutoasignar = false" class="text-gray-400 absolute right-2 top-2 text-xl font-bold focus:outline-none">
            <font-awesome-icon :icon="['fas', 'xmark']" />
        </button>

        <div class="mt-4">
          <label for="fecha_ini" class="block text-sm font-medium text-white">Fecha de inicio</label>
          <input type="datetime-local" id="fecha_ini" v-model="formAsignar.fecha_ini" class="mt-1 block w-full"/>
          <InputError :message="formAsignar.errors?.fecha_ini" class="mt-2"/>
        </div>

        <div class="mt-4">
          <label for="fecha_fin" class="block text-sm font-medium text-white">Fecha de fin</label>
          <input type="datetime-local" id="fecha_fin" v-model="formAsignar.fecha_fin" class="mt-1 block w-full"/>
          <InputError :message="formAsignar.errors?.fecha_fin" class="mt-2"/>
        </div>
        

        <div class="flex items-center justify-end mt-4">
          <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 font-medium rounded-full text-sm px-4 py-2 transition-colors duration-200">Asignar Tarea</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal de éxito -->
  <div v-if="showSuccessModal" class="fixed inset-0 z-50 flex items-center justify-center div-overlay bg-black bg-opacity-50 backdrop-blur-sm">
    <div class="w-full max-w-lg mx-4 bg-white rounded-lg shadow-lg p-6">
      <p class="text-xl font-bold mb-4 text-center text-green-600">{{ successMessage }}</p>
      <button @click="showSuccessModal = false" class="text-white bg-blue-600 hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300 font-medium rounded-full text-sm px-4 py-2 transition-colors duration-200 w-full">Cerrar</button>
    </div>
  </div>

  <!-- Modal de confirmación -->
  <div v-if="showConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center div-overlay bg-black bg-opacity-50 backdrop-blur-sm">
    
    <div class="w-[22rem] md:w-[70rem] max-w-lg p-4 max-h-[35rem] md:max-h-[45rem] bg-gray-800 relative rounded-lg shadow-md overflow-auto">
      
      
      <p class="text-xl font-bold mb-4 text-center text-red-600">¿Estás seguro de que quieres eliminar esta tarea?</p>
      <div class="flex justify-end space-x-2">
        <button @click="eliminarTarea" class="bg-red-600 text-white py-2 px-4 rounded-full hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-300">Eliminar</button>
        <button @click="showConfirmModal = false" class="bg-gray-600 text-white py-2 px-4 rounded-full hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300">Cancelar</button>
      </div>
    </div>
  </div>

  <!-- Modal de eliminado -->
  <div v-if="showDeletedModal" class="fixed inset-0 z-50 flex items-center justify-center div-overlay bg-black bg-opacity-50 backdrop-blur-sm">
    <div class="w-full max-w-lg mx-4 bg-white rounded-lg shadow-lg p-6">
      <p class="text-xl font-bold mb-4 text-center text-red-600">{{ deletedMessage }}</p>
      <button @click="showDeletedModal = false" class="text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 font-medium rounded-full text-sm px-4 py-2 transition-colors duration-200 w-full">Cerrar</button>
    </div>
  </div>
</template>

<style scoped>
.div-overlay {
  display: flex;
  align-items: center;
  justify-content: center;
}

.radio-inputs {
  position: relative;
  display: flex;
  flex-wrap: wrap;
  box-sizing: border-box;
  box-shadow: 0 0 0px 1px rgba(0, 0, 0, 0.06);
  padding: 0.25rem;
  width: 300px;
  font-size: 14px;
}

.radio-inputs .radio {
  flex: 1 1 auto;
  text-align: center;
}

.radio-inputs .radio input {
  display: none;
}

.radio-inputs .radio .name {
  display: flex;
  cursor: pointer;
  align-items: center;
  justify-content: center;
  border: none;
  padding: .2rem 0;
  color: white;
  transition: all .15s ease-in-out;
}

.radio-inputs .radio input:checked + .name {
  font-weight: 600;
  color: #EEE;
}

 input:checked + .to-do {
  background-color: #ff0303;
}

 input:checked + .doing {
  background-color: #ffb803;

}
input:checked + .done {
  background-color: #00ff0d;
}


</style>
