<script>

import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import timeGridPlugin from '@fullcalendar/timegrid';
import { ref, onMounted, watch } from 'vue';
import TareasForm from '@/Pages/Tareas/TareasForm.vue';
import { router } from '@inertiajs/vue3';
import { format, isSameDay } from 'date-fns';
import { es } from 'date-fns/locale';

export default {
  props: {
    tareasCalendario: {
      type: Array,
      required: true
    },
    tareaEditar: {
      type: Object,
      default: null
    }
  },
  components: {
    FullCalendar,
    AuthenticatedLayout,
    TareasForm
  },
  setup(props) {
    const showForm = ref(false);
    const showDetails = ref(false);
    const calendarEvents = ref([]);
    const fecha_ini = ref('');
    const fecha_fin = ref('');
    const tareaDetalles = ref(null);
    const tareaEditar = ref(null);
    const tipo = 'personal';
    const tareaEliminar = ref(null);
    const showConfirmModal = ref(false);
    

    const toggleForm = () => {
      showForm.value = !showForm.value;
      if (!showForm.value) {
        tareaEditar.value = null;
      }
    }

    const handleFormSubmitted = () => {
      showForm.value = false;
      tareaEditar.value = null;
    }

    const closeFormOnClickOutside = (event) => {
      if (event.target.classList.contains('form-overlay')) {
        showForm.value = false;
      }
    }

    const handleEventDrop = (eventDropInfo) => {
      const tarea = props.tareasCalendario.find(tarea => tarea.id == eventDropInfo.event.id);

      if (tarea) {
       
        router.post(route('tarea.cambiarFecha', tarea.id), {
          
          fecha_ini: toLocalISOString(new Date(eventDropInfo.event.start)),
          fecha_fin: toLocalISOString(new Date(eventDropInfo.event.end)),
        });
      }
    }
    
    function toLocalISOString(date) {
        const tzoffset = date.getTimezoneOffset() * 60000;
        const localISOTime = new Date(date - tzoffset).toISOString().slice(0, 16);
        return localISOTime;
    }

    const handleEventResize = (eventResizeInfo) => {
      const tarea = props.tareasCalendario.find(tarea => tarea.id == eventResizeInfo.event.id);
      if (tarea) {
        router.post(route('tarea.cambiarFecha', tarea.id), {
          fecha_ini: toLocalISOString(new Date(eventResizeInfo.event.start)),
          fecha_fin: toLocalISOString(new Date(eventResizeInfo.event.end)),
        });
      }
    }

    const closeDivOnClickOutside = (event) => {
      if (event.target.classList.contains('div-overlay')) {
        showForm.value = false;
        showDetails.value = false;
      }
    }

    

    const handleDateSelect = (selectionInfo) => {
      fecha_ini.value = selectionInfo.start;
      fecha_fin.value = selectionInfo.end;
      toggleForm();
    }

    const loadEvents = () => {
      if (props.tareasCalendario && Array.isArray(props.tareasCalendario)) {
        calendarEvents.value = props.tareasCalendario.map(tarea => ({
          id: tarea.id,
          title: tarea.titulo,
          start: new Date(tarea.fecha_ini).toISOString(),
          end: new Date(tarea.fecha_fin).toISOString(),
          color: tarea.color,
        }));
      }
    }

    watch(() => props.tareasCalendario, loadEvents, { immediate: true });

    onMounted(() => {
      loadEvents();
    });

    return {
      showForm,
      showDetails,
      tareaEditar,
      tipo,
      tareaDetalles,
      tareaEliminar,
      showConfirmModal,
      toggleForm,
      handleFormSubmitted,
      closeFormOnClickOutside,
      closeDivOnClickOutside,
      handleDateSelect,
      calendarEvents,
      fecha_ini,
      fecha_fin,
      calendarOptions: {
        height: '85vh',
        nowIndicator: true,
        locale: 'es',
        timeZone: 'local',
        plugins: [dayGridPlugin, interactionPlugin, timeGridPlugin],
        initialView: 'timeGridDay',
        editable: true,
        eventDrop: handleEventDrop,
        eventResize: handleEventResize,
        
        selectable: true,
        select: handleDateSelect,
        events: calendarEvents.value,
        eventClick: function(info) {
          const tarea = props.tareasCalendario.find(tarea => tarea.id == info.event.id);
          if (tarea) {
            tareaDetalles.value = tarea;
            showDetails.value = true;
          }
        },
        slotLabelFormat: {
          hour: 'numeric',
          minute: '2-digit',
          omitZeroMinute: false,
          meridiem: 'long',
          hour12: false
        },
        eventTimeFormat: {
          hour: 'numeric',
          minute: '2-digit',
          omitZeroMinute: false,
          meridiem: 'long',
          hour12: false
        },
        dayCellDidMount: function(info) {
          const today = new Date();
          const cellDate = new Date(info.date);
          
          // Compare year, month, and day
          if (
            cellDate.getFullYear() === today.getFullYear() &&
            cellDate.getMonth() === today.getMonth() &&
            cellDate.getDate() === today.getDate()
          ) {

            info.el.style.backgroundColor = '#e6ffe6'; // Verde clarito
          }
        }
      }
    }
  },

  methods: {
    eliminarTarea() {
      router.delete(route('tarea.delete', this.tareaEliminar), {
        onSuccess: () => {
          this.showDetails = false;
        },
      })
    },

    confirmEliminarTarea(id){
      this.tareaEliminar = id;
      this.showConfirmModal = true;
    },
    editarTarea(id) {
      this.showDetails = false;
      this.showForm = true;
      this.tareaEditar = this.$props.tareasCalendario.find(tarea => tarea.id === id);
    },
    formatDate(startDate, endDate) {
      const start = new Date(startDate);
      const end = new Date(endDate);

      if (isSameDay(start, end)) {
        return `${format(start, 'dd MMMM yyyy', { locale: es })} ${format(start, 'HH:mm', { locale: es })} - ${format(end, 'HH:mm', { locale: es })}`;
      } else {
        return `${format(start, 'dd MMMM yyyy HH:mm', { locale: es })} - ${format(end, 'dd MMMM yyyy HH:mm', { locale: es })}`;
      }
    },
     cambiarEstado  (id, estado) {
    router.post(route('tarea.estado', id), {
        estado: estado,
      onSuccess: () => {
        showDetails.value = false;
      },
    });
  }
  }
}
</script>
<template>
  <AuthenticatedLayout>
    <div class="">
      <div class="">
        <div class="font-sm">
          <FullCalendar :options="calendarOptions" />
        </div>
      </div>
    </div>

    <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center form-overlay bg-black bg-opacity-50 backdrop-filter backdrop-blur-sm" @click="closeFormOnClickOutside">
      <div class="w-[22rem] md:w-[70rem] max-w-lg  bg-white rounded-lg shadow-lg">
        <TareasForm :tipo="tipo" :tarea="tareaEditar" :fechaIni="fecha_ini" :fechaFin="fecha_fin" @formSubmitted="handleFormSubmitted" @closeForm="toggleForm" />
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
              <p v-if="tareaDetalles.fecha_ini && tareaDetalles.fecha_fin" class="text-gray-500 flex items-center mb-2 ">
                <font-awesome-icon :icon="['fas', 'calendar-alt']" class="mr-2" />
                {{ formatDate(tareaDetalles.fecha_ini, tareaDetalles.fecha_fin) }}
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
                    <input type="radio" class="rounded-full" v-model="tareaDetalles.estado" name="radio" value="to-do" @change="cambiarEstado(tareaDetalles.id,'to-do')">
                    <span class="name to-do rounded-full">To Do</span>
                </label>
                <label class="radio">
                    <input type="radio" class="rounded-full" v-model="tareaDetalles.estado" name="radio" value="doing" @change="cambiarEstado(tareaDetalles.id, 'doing')">
                    <span class="name doing rounded-full">Doing</span>
                </label>
                <label class="radio">
                    <input type="radio" class="rounded-full" v-model="tareaDetalles.estado" name="radio" value="done" @change="cambiarEstado(tareaDetalles.id,'done')">
                    <span class="name done rounded-full">Done</span>
                </label>
              </div>
            </div>
          </div>
          
          <!-- Buttons -->
          <div class="flex md:flex-col justify-between md:justify-start gap-3 md:pt-12 ">
            <button @click="editarTarea(tareaDetalles.id)" v-if="tareaDetalles.tipo === 'personal' || (tareaDetalles.tipo === 'equipo'  && rol === 'manager')" class="bg-gray-600 text-white py-2 px-4 rounded-full hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300 w-30">
              <font-awesome-icon :icon="['fas', 'pen-to-square']" class="mr-2" /> Editar
            </button>
            <button @click="asignarTarea" v-if="tareaDetalles.tipo === 'equipo' && tareaDetalles.asignada !== 1 && rol === 'manager'" class="bg-gray-600 text-white py-2 px-4 rounded-full hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300 w-30">
              <font-awesome-icon :icon="['fas', 'user-tag']" class="mr-2" /> Asignar
            </button>
            <button @click="autoasignarTarea" v-if="tareaDetalles.tipo === 'equipo' && tareaDetalles.asignada !== 1 && rol != 'manager'" class="bg-gray-600 text-white py-2 px-4 rounded-full hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300 w-30">
              Pa mi
            </button>
            <button @click="confirmEliminarTarea(tareaDetalles.id)" v-if="tareaDetalles.tipo === 'personal' || (tareaDetalles.tipo === 'equipo' && rol === 'manager')" class="bg-gray-600 text-white py-2 px-4 rounded-full hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300 w-30">
              <font-awesome-icon :icon="['fas', 'trash-can']" class="mr-2" /> Eliminar
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center div-overlay bg-black bg-opacity-50 backdrop-blur-sm">
      <div class="w-full max-w-lg mx-4 bg-white rounded-lg shadow-lg p-6">
        <p class="text-xl font-bold mb-4 text-center text-red-600">¿Estás seguro de que quieres eliminar esta tarea?</p>
        <div class="flex justify-end space-x-2">
          <button @click="eliminarTarea" class="bg-red-600 text-white py-2 px-4 rounded-full hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-300">Eliminar</button>
          <button @click="showConfirmModal = false" class="bg-gray-600 text-white py-2 px-4 rounded-full hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300">Cancelar</button>
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



.fc >>> .fc-toolbar-title {
  font-size: 1.75rem !important;
}

.fc >>> .fc-button {
  font-size: 1rem !important;
}

.fc >>> .fc-event-title {
  font-size: 1.5rem !important;
}
.fc >>> .fc-event{
  border-radius: 15px !important;
  padding: 1rem;
  box-shadow: 1px 3px 5px  rgba(0, 0, 0, 0.5) !important;
}



@media (max-width: 480px) {
  .fc >>> .fc-toolbar-title {
    font-size: 1.2rem !important;
  }

  .fc >>> .fc-button {
    font-size: 0.8rem !important;
  }

  .fc >>> .fc-event-title {
    font-size: 1.2rem !important; 
  }
}
</style>