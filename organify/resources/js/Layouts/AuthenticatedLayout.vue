<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import CreacionEquipoForm from '@/Pages/Equipo/CreacionEquipoForm.vue';
import { Link, router } from '@inertiajs/vue3';
import TareasForm from '@/Pages/Tareas/TareasForm.vue';

const { props } = usePage();

const tipo = 'personal';

const showFormTarea = ref(false);

const showingNavigationDropdown = ref(false);
const showFormEquipo = ref(false);
const showNotifications = ref(false);
const notificaciones = ref(props.NotificacionesUsuario);

const toggleForm = () => {
  showFormEquipo.value = !showFormEquipo.value;
}

const handleFormSubmitted = () => {
  showFormEquipo.value = false;
}

const toggleFormTarea = () => {
  showFormTarea.value = !showFormTarea.value;
}

const handleFormSubmittedTarea = () => {
  showFormTarea.value = false;
}

const closeDivOnClickOutside = (event) => {
  if (event.target.classList.contains('div-overlay')) {
    showFormEquipo.value = false;
    showFormTarea.value = false;
    showNotifications.value = false;
  }
}

const toggleMobileMenu = () => {
  showingNavigationDropdown.value = !showingNavigationDropdown.value;
}

const closeMobileMenu = () => {
  showingNavigationDropdown.value = false;
}

const aceptarUsuario = (usuarioId, equipoId, notificacionId) => {
  eliminarNotificacion(notificacionId);
  router.get(route('equipo.aceptarSolicitud', { equipo_id: equipoId, user: usuarioId }), {
    onSuccess: () => {
      console.log('Aceptando usuario', usuarioId, 'en equipo', equipoId);
      
    }
  });
}

const rechazarUsuario = (usuarioId, equipoId , notificacionId) => {
  eliminarNotificacion(notificacionId);
  router.get(route('equipo.rechazarSolicitud', { equipo_id: equipoId, user: usuarioId }), {
    onSuccess: () => {
      console.log('Rechazando usuario', usuarioId, 'en equipo', equipoId);
      
    }
  });
}

const aceptarTarea = (tareaId , notificacionId) => {
  eliminarNotificacion(notificacionId);
  router.get(route('tarea.aceptar', tareaId), {
    onSuccess: () => {
      console.log('Aceptando tarea', tareaId);
      
    }
  });
}

const rechazarTarea = (tareaId, notificacionId ) => {
  eliminarNotificacion(notificacionId);
  router.get(route('tarea.rechazar', tareaId), {
    onSuccess: () => {
      console.log('Rechazando tarea', tareaId);
      
    }
  });
}

const decodeData = (data) => {
  try {
    return JSON.parse(data);
  } catch (error) {
    console.error('Error decodificando JSON', error);
    return null;
  }
}

const eliminarNotificacion = (notificacionId) => {
  notificaciones.value = notificaciones.value.filter(notificacion => notificacion.id !== notificacionId);
  router.post(route('notificacion.eliminar'), {
    id: notificacionId,
    onSuccess: () => {
      console.log('Eliminando notificación', notificacionId);
    }
  });
}
</script>

<template>
  

  <div class="flex h-screen">
    <!-- Fixed Sidebar -->
    <nav class="w-72 flex flex-col h-full fixed hidden md:flex border-r-2 border-solid border-gray-800 z-30 bg-gray-800 text-white justify-between">
      <div class="absolute left-4 top-4">
        <button v-if="notificaciones.length > 0" @click="showNotifications = true" class="justify-center p-2 rounded-md">
          <font-awesome-icon :icon="['fas', 'bell']" class="text-red-600 text-2xl" />
        </button>
        <button v-else @click="showNotifications = true" class="justify-center p-2 rounded-md">
          <font-awesome-icon :icon="['fas', 'bell']" class=" text-2xl" />
            </button>
      </div> 
      
      <div class="flex h-full flex-col justify-between">
              <div>
                <ResponsiveNavLink :href="route('profile.edit')" :active="route().current('profile.edit')" class="nav-link mt-12 mb-6 rounded-md">
                  <div class="flex flex-col justify-center items-center ">
                    <div class="w-36 h-36 mb-6  bg-gray-600 rounded-full overflow-hidden">
                      <img class="w-full h-full object-cover" :src="`/archivos/${props.auth.user.foto}`" alt="Foto de perfil">
                    </div>
                    <div class="flex justify-center items-center">
                      <p class="text-2xl">{{ props.auth.user.nombre }}</p>
                    </div>
                  </div>
                </ResponsiveNavLink>

                <div class="border-t-2 pt-6">
                  <ResponsiveNavLink :href="route('calendario')" :active="route().current('calendario')" class="nav-link mb-6 p-4 rounded-md">
                    <font-awesome-icon class="mr-3" :icon="['fas', 'calendar-days']" /> <span >Calendario</span> 
                  </ResponsiveNavLink>
                  <ResponsiveNavLink :href="route('tablon')" :active="route().current('tablon')" class="nav-link p-4 rounded-md">
                    <font-awesome-icon class="mr-3" :icon="['fas', 'table-list']" /><span >Tablón</span> 
                  </ResponsiveNavLink>
                </div>
              </div>
              
              <div class=" flex flex-col justify-end  h-70 pb-4" v-if="props.EquiposUsuario.length > 0">
                  <div class="flex justify-center pb-2">
                    <p class="border-b-2">Mis Equipos</p>
                  </div>
                  <div class="max-h-52 overflow-auto">
                    <div  class=" font-semibold" v-for="equipo in props.EquiposUsuario" :key="equipo.id">
                      <ResponsiveNavLink :href="`/equipo/${equipo.id}/tablon`" :active="route().current('equipo.tablon') && Number(route().params.equipo) === Number(equipo.id)" class="nav-link mb-1 rounded-md">
                        <div class="flex items-center gap-2">
                          <div :style="{ borderColor: equipo.color, boxShadow: `0 2px 4px ${equipo.color}` }" class="w-10 h-10 bg-gray-600 rounded-full overflow-hidden border-2">
                            <img class="w-full h-full object-cover" :src="`/archivos/${equipo.foto}`" alt="Foto de equipo">
                          </div>
                          <p class="text-md w-[9rem]  font-bold truncate">{{ equipo.nombre }}</p>
                        </div>
                      </ResponsiveNavLink>
                    </div>
                  </div>
              </div>
            </div>
            
            <!-- Botones de Crear y Unirse a Equipo -->
            <div class="flex justify-around border-t-2 pb-4 pt-4">
                  <button @click="toggleForm" class="flex justify-center px-4 py-2 rounded-full bg-green-500 text-white hover:bg-green-600">
                    Crear Equipo
                  </button>
                  <Link :href="route('equipos')" :active="route().current('equipos')" class="flex justify-center px-4 py-2 rounded-full bg-green-500 text-white hover:bg-green-600">
                    Unirse a Equipo
                  </Link>
            </div>  

    </nav>

    <!-- Page Content -->
    <div class="flex-1 w-screen md:ml-72">
      <main>
        <div class="fixed top-0 left-0 right-0 bg-gray-800 text-white shadow-lg md:ml-72 z-10">
          <div class="flex items-center justify-between md:justify-center p-4">
            <!-- Logo -->
            <button @click="toggleMobileMenu" class="p-2 rounded-md md:hidden text-white hover:text-whitehover:bg-white focus:outline-none focus:bg-white focus:text-white transition duration-150 ease-in-out">
              <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path v-if="!showingNavigationDropdown" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
            <div class="shrink-0 sm:flex items-center">
              <Link :href="route('tablon')">
                <ApplicationLogo class="block h-9 w-auto fill-current" />
              </Link>
            </div>
          </div>
        </div>
        <div class="mt-24 px-4 w-screen md:w-full sm:px-6 lg:px-8">
          <slot />
        </div>
      </main>
    </div>

    <!-- Responsive Navigation Menu -->
    <div v-if="showingNavigationDropdown" class="fixed inset-0 z-50 flex">
      <div class="bg-gray-800 text-white w-4/5 h-full p-4 overflow-y-auto">
        <div class="flex justify-end">
          <button @click="toggleMobileMenu" class="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div class="absolute left-4 top-4">
          <button v-if="notificaciones.length > 0" @click="showNotifications = true" class="justify-center p-2 rounded-md">
            <font-awesome-icon :icon="['fas', 'bell']" class="text-red-600 text-2xl" />
          </button>
          <button v-else @click="showNotifications = true" class="justify-center p-2 rounded-md">
            <font-awesome-icon :icon="['fas', 'bell']" class=" text-2xl" />
          </button>
        </div> 
        <div class="space-y-4 h-[95%] text-lg flex flex-col justify-between">
            <div class="flex h-full flex-col justify-between">
              <div>
                <ResponsiveNavLink :href="route('profile.edit')" :active="route().current('profile.edit')" class="nav-link mb-4 rounded-md">
                  <div class="flex flex-col justify-center items-center ">
                    <div class="w-32 h-32 mb-3  bg-gray-600 rounded-full overflow-hidden">
                      <img class="w-full h-full object-cover" :src="`/archivos/${props.auth.user.foto}`" alt="Foto de perfil">
                    </div>
                    <div class="flex justify-center items-center">
                      <p class="text-2xl">{{ props.auth.user.nombre }}</p>
                    </div>
                  </div>
                </ResponsiveNavLink> 

                <div class="border-t-2 pt-6">
                  <ResponsiveNavLink :href="route('calendario')" :active="route().current('calendario')" class="nav-link mb-6 p-4 rounded-md">
                    <font-awesome-icon class="mr-3" :icon="['fas', 'calendar-days']" /> <span >Calendario</span> 
                  </ResponsiveNavLink>
                  <ResponsiveNavLink :href="route('tablon')" :active="route().current('tablon')" class="nav-link p-4 rounded-md">
                    <font-awesome-icon class="mr-3" :icon="['fas', 'table-list']" /><span >Tablón</span> 
                  </ResponsiveNavLink>
                </div>
              </div>
              
              <div class=" flex flex-col justify-end  h-70 pb-4" v-if="props.EquiposUsuario.length > 0">
                  <div class="flex justify-center pb-2">
                    <p class="border-b-2">Mis Equipos</p>
                  </div>
                  <div class="max-h-[9rem] overflow-auto">
                    <div  class=" font-semibold" v-for="equipo in props.EquiposUsuario" :key="equipo.id">
                      <ResponsiveNavLink :href="`/equipo/${equipo.id}/tablon`" :active="route().current('equipo.tablon') && Number(route().params.equipo) === Number(equipo.id)" class="nav-link mb-2 rounded-md">
                        <div class="flex items-center gap-2">
                          <div :style="{ borderColor: equipo.color, boxShadow: `0 2px 4px ${equipo.color}` }" class="w-10 h-10 bg-gray-600 rounded-full overflow-hidden border-2">
                            <img class="w-full h-full object-cover" :src="`/archivos/${equipo.foto}`" alt="Foto de equipo">
                          </div>
                          <p class="text-md w-[9rem]  font-bold truncate">{{ equipo.nombre }}</p>
                        </div>
                      </ResponsiveNavLink>
                    </div>
                  </div>
              </div>
            </div>
            
            <!-- Botones de Crear y Unirse a Equipo -->
            <div class="flex justify-around border-t-2 pt-4">
                  <button @click="toggleForm" class="flex justify-center p-2 rounded-full bg-green-500 text-white hover:bg-green-600">
                    Crear Equipo
                  </button>
                  <Link :href="route('equipos')" :active="route().current('equipos')" class="flex justify-center p-2 rounded-full bg-green-500 text-white hover:bg-green-600">
                    Unirse a Equipo
                  </Link>
            </div>  
        
                          
        </div>
      </div>
      <div class="w-1/5 h-full bg-black bg-opacity-50" @click="closeMobileMenu"></div>
    </div>

  


    <!-- Modal for Creating Team -->
    <div v-if="showFormEquipo" class="fixed inset-0 z-50 flex items-center justify-center div-overlay" @click="closeDivOnClickOutside">
      <div class="w-[22rem] md:w-[70rem] max-w-lg  bg-white rounded-lg shadow-lg">
        <CreacionEquipoForm @formSubmitted="handleFormSubmitted" @closeForm="toggleForm" />
      </div>
    </div>

    <div v-if="showFormTarea" class="fixed inset-0 z-50 flex items-center justify-center div-overlay bg-black bg-opacity-50 backdrop-filter backdrop-blur-sm" @click="closeDivOnClickOutside">
        <div class="w-[22rem] md:w-[70rem] max-w-lg  bg-white rounded-lg shadow-lg">
          <TareasForm :tipo="tipo"  @formSubmitted="handleFormSubmittedTarea" @closeForm="toggleFormTarea" />
        </div>
    </div>

    <button
        v-else
        class="fixed bottom-5 right-5 bg-green-600 text-white rounded-full z-10 p-4 shadow-lg hover:bg-green-700 focus:outline-none focus:ring-4 focus:ring-blue-300"
        @click="toggleFormTarea"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
    </button>

  </div>  

  <!-- Modal for notifications -->
  <div v-if="showNotifications" class="fixed inset-0 z-50 flex items-center justify-center div-overlay" @click="closeDivOnClickOutside">
    <div class="w-[22rem] md:w-[70rem] max-w-lg p-4 max-h-[35rem] md:max-h-[45rem] bg-gray-800 relative rounded-lg shadow-md overflow-auto">
      <button @click="showNotifications = false" class="text-gray-400 absolute right-2 top-2 text-xl font-bold focus:outline-none">
            <font-awesome-icon :icon="['fas', 'xmark']" />
      </button>
      <h2 class="text-center text-2xl mt-4 mb-4 text-green-400">Notificaciones</h2>
      <div class="" v-if="notificaciones.length > 0">
        <div v-for="notificacion in notificaciones" :key="notificacion.id" class="mt-1 mb-4 p-4 bg-gray-100 rounded-lg shadow-sm relative">
          <button @click="eliminarNotificacion(notificacion.id)" class="absolute top-2 right-2 text-gray-500 hover:text-red-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
          <div v-if="notificacion.tipo === 'solicitud'">
            <h3 class="text-xl font-semibold mb-2">{{ notificacion.titulo }}</h3>
            <p class="mb-2">{{ notificacion.descripcion }}</p>
            <div v-if="decodeData(notificacion.data)">
              <p class="mb-2"><strong>Usuario:</strong> {{ decodeData(notificacion.data).user.nombre }}</p>
              <p class="mb-4"><strong>Equipo ID:</strong> {{ decodeData(notificacion.data).equipo_id }}</p>
              <div class="flex justify-end gap-2">
                <button @click="aceptarUsuario(decodeData(notificacion.data).user.id, decodeData(notificacion.data).equipo_id, notificacion.id)" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">Aceptar</button>
                <button @click="rechazarUsuario(decodeData(notificacion.data).user.id, decodeData(notificacion.data).equipo_id , notificacion.id)" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">Rechazar</button>
              </div>
            </div>
          </div>
          <div v-if="notificacion.tipo === 'solicitudTarea'">
            <h3 class="text-xl font-semibold mb-2">{{ notificacion.titulo }}</h3>
            <p class="mb-2">{{ notificacion.descripcion }}</p>
            <div v-if="decodeData(notificacion.data)">
              <div class="flex justify-end gap-2">
                <button @click="aceptarTarea(decodeData(notificacion.data).tarea , notificacion.id)" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">Aceptar</button>
                <button @click="rechazarTarea(decodeData(notificacion.data).tarea , notificacion.id)" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">Rechazar</button>
              </div>
            </div>
          </div>
          <div v-else-if="notificacion.tipo === 'aceptado' || notificacion.tipo === 'unido' || notificacion.tipo === 'salido' || notificacion.tipo === 'abandono'">
            <h3 class="text-xl font-semibold mb-2">{{ notificacion.titulo }}</h3>
            <p>{{ notificacion.descripcion }}</p>
          </div>
        </div>
      </div>
      <div v-else class="text-center text-white">
        <p>No hay notificaciones</p>
      </div>
      
    </div>
  </div>
</template>

<style scoped>
.div-overlay {
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(5px);
}

.nav-link {
  display: flex;
  justify-content: center;
  padding: 0.6rem;
  border-radius: 0.375rem;
  color: white;
  background-color: transparent;
  transition: all 0.3s ease;
  position: relative;
}

.nav-link::after {
  content: '';
  display: block;
  width: 0;
  height: 2px;
  background: #4CAF50;
  transition: width 0.3s;
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
}

.nav-link:hover::after,
.nav-link[aria-current="page"]::after {
  width: 100%;
}

.nav-link:hover {
  color: #A5D6A7;
}

.nav-link[aria-current="page"] {
  color: #A5D6A7;
  background-color: rgba(76, 175, 80, 0.1);
}

*::-webkit-scrollbar {
  width: 2px;
}
 
*::-webkit-scrollbar-track {
  box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
}
 
*::-webkit-scrollbar-thumb {
  background-color: #4CAF50;
  outline: 1px solid #4CAF50;
}

</style>
