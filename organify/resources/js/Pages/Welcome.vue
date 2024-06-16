<script setup>
import { onMounted, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const selectedImage = ref(1);
const totalImages = 3;


onMounted(() => {
    
    setInterval(() => {
        selectedImage.value = selectedImage.value < totalImages ? selectedImage.value + 1 : 1;
    }, 5000);
});
</script>

<template>
    <Head title="Inicio" />
    <header class="bg-gray-800 h-[8vh] p-[2vh] flex justify-between items-center  w-full fixed top-0 left-0 z-50">
        <div>
                <img src="/ORGANIFY_LOGO.png" alt="Organify Logo" class="w-44 lg:w-64" />
            </div>
            <nav v-if="canLogin" class="sm:hidden">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('tablon')"
                    class="rounded-full px-3 py-2 font-bold bg-green-500 text-white ring-1 ring-transparent transition hover:bg-green-600 focus:outline-none focus-visible:ring-[#FF2D20]"
                >
                    Mi tablón
                </Link>
                <template v-else >
                    <Dropdown>
                        <template #trigger="{ open }">
                            <button class="rounded-md px-2 py-1 bg-green-500 text-white ring-1 ring-transparent transition hover:bg-green-600 focus:outline-none focus-visible:ring-[#FF2D20]">
                                <font-awesome-icon :icon="['fas', open ? 'chevron-up' : 'chevron-down']" />
                            </button>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('login')">
                                Iniciar Sesión
                            </DropdownLink>
                            <DropdownLink v-if="canRegister" :href="route('register')">
                                Registrarse
                            </DropdownLink>
                        </template>
                    </Dropdown>
                    
                </template>

                
            </nav>
            
            <nav v-if="canLogin" class="hidden sm:block">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('tablon')"
                    class="rounded-full px-3 py-2 text-xl font-bold g-green-500 text-white ring-1 ring-transparent transition hover:bg-green-600 focus:outline-none focus-visible:ring-[#FF2D20]"
                >
                    Mi tablón
                </Link>
                <template v-else >
                    <Link
                        :href="route('login')"
                        class="rounded-full px-3 py-2 text-xl font-bold mr-5 bg-green-500 text-white ring-1 ring-transparent transition hover:bg-green-600 focus:outline-none focus-visible:ring-[#FF2D20]"
                    >
                        Iniciar Sesión
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="rounded-full px-3 py-2 text-xl font-bold bg-green-500 text-white ring-1 ring-transparent transition hover:bg-green-600 focus:outline-none focus-visible:ring-[#FF2D20]"
                    >
                        Registrarse
                    </Link>
                </template>

                
            </nav>  

    </header>
    <main class="mt-[8vh]">
        <section class="hero min-h-[92vh] bg-gray-800 flex">
            <div class="container mx-auto flex flex-col xl:flex-row items-center justify-around p-4">
                <div class="text-center lg:text-left lg:w-[45rem]">
                    <h1 class="text-4xl font-mono lg:text-8xl text-white font-bold">Bienvenido a </h1>
                    <h1 class="text-4xl font-mono lg:text-8xl text-green-400 font-bold">Organify</h1>
                    <p class="text-lg font-mono lg:text-2xl text-white mt-4">El mejor lugar para organizar tu vida, tu trabajo y tus proyectos.</p>
                </div>
                <div class="hidden xl:block">
                    <img src="/Images/AppEnPortatil.png" alt="Organify Logo" class="w-[55rem] animation " />
                </div>
                <div class="block xl:hidden mt-10">
                    <img src="/Images/AppEnMovil.png" alt="Organify Logo" class="w-[18rem] sm:[20rem] md:w-[22rem] animation" />
                </div>
            </div>
        </section>  
        <section class="bg-gray-900 flex flex-col justify-center">
            <div class="flex flex-col xl:flex-row justify-center items-center content-center gap-5 xl:gap-12 mt-8">
                <div class="flex flex-row md:justify-center gap-4 overflow-auto w-full p-6 xl:hidden">
                    <div @click="selectedImage = 1" :class="{'md:bg-gray-800': selectedImage === 1, 'hover:bg-gray-800': true, 'p-2 md:p-6 md:border md:border-gray-700 md:rounded-md': true}" class="w-full md:w-[40rem]">
                        <h3 :class="{'text-green-400 border-b-2 border-green-400 md:border-0': selectedImage === 1, 'text-gray-300': selectedImage !== 1}" class="text-lg text-center md:text-2xl">Calendario</h3>
                    </div>
                    <div @click="selectedImage = 2" :class="{'md:bg-gray-800': selectedImage === 2, 'hover:bg-gray-800': true, 'p-2 md:p-6 md:border md:border-gray-700 md:rounded-md': true}" class="w-full md:w-[40rem]">
                        <h3 :class="{'text-green-400 border-b-2 border-green-400 md:border-0': selectedImage === 2, 'text-gray-300': selectedImage !== 2}" class="text-lg text-center md:text-2xl">Tareas</h3>
                    </div>
                    <div @click="selectedImage = 3" :class="{'md:bg-gray-800': selectedImage === 3, 'hover:bg-gray-800': true, 'p-2 md:p-6 md:border md:border-gray-700 md:rounded-md': true}" class="w-full md:w-[40rem]">
                        <h3 :class="{'text-green-400 border-b-2 border-green-400 md:border-0': selectedImage === 3, 'text-gray-300': selectedImage !== 3}" class="text-lg text-center md:text-2xl">Equipos</h3>
                    </div>
                </div>
                <div class="flex flex-col gap-8  overflow-auto w-4/12 p-6 hidden xl:flex">
                    <div @click="selectedImage = 1" :class="{'bg-gray-800': selectedImage === 1, 'hover:bg-gray-800': true}" class="p-6 border border-gray-700 rounded-md cursor-pointer">
                        <h3 :class="{'text-green-400': selectedImage === 1, 'text-gray-300': selectedImage !== 1}" class="text-xl lg:text-2xl">Calendario</h3>
                        <p class="text-gray-300 mt-2">¡Organiza tus tareas y las de tu equipo en tu calendario para que nunca se te escape nada!</p>
                    </div>
                    <div @click="selectedImage = 2" :class="{'bg-gray-800': selectedImage === 2, 'hover:bg-gray-800': true}" class="p-6 border border-gray-700 rounded-md cursor-pointer">
                        <h3 :class="{'text-green-400': selectedImage === 2, 'text-gray-300': selectedImage !== 2}" class="text-xl lg:text-2xl">Tareas</h3>
                        <p class="text-gray-300 mt-2">¡Apunta todas tus tareas pendientes y mantén todo bajo control fácilmente! </p>
                    </div>
                    <div @click="selectedImage = 3" :class="{'bg-gray-800': selectedImage === 3, 'hover:bg-gray-800': true}" class="p-6 border border-gray-700 rounded-md cursor-pointer">
                        <h3 :class="{'text-green-400': selectedImage === 3, 'text-gray-300': selectedImage !== 3}" class="text-xl lg:text-2xl">Equipos</h3>
                        <p class="text-gray-300 mt-2">Únete a un equipo o crea uno propio, y disfruta de un tablón personalizado con todas las tareas de tu grupo. ¡La colaboración nunca fue tan fácil!</p>
                    </div>
                </div>             
                <div class=" text-center block xl:hidden">
                    <div key="image">
                        <div v-if="selectedImage === 1">
                            <p class="text-gray-300 mb-4 w-full ">¡Organiza tus tareas y las de tu equipo en tu calendario para que nunca se te escape nada!</p>
                            
                            <div class="flex justify-center">
                                <img
                                src="/Images/CalendarioMovil.png"
                                alt="Imagen de la característica 1"
                                class="w-[12rem]  rounded-md shadow-md"
                                />
                            </div>
                        </div>

                        <div v-if="selectedImage === 2">
                            <p class="text-gray-300 mb-4 w-full ">¡Apunta todas tus tareas pendientes y mantén todo bajo control fácilmente!</p>
                            <div class="flex justify-center">
                                <img
                                src="/Images/TareaMovil.png"
                                alt="Imagen de la característica 1"
                                class="w-[12rem]  rounded-md shadow-md"
                                />
                            </div>
                            
                        </div>

                        <div v-if="selectedImage === 3">
                            <p class="text-gray-300 mb-4 w-full">Únete a un equipo o crea uno propio, y disfruta de un tablón personalizado con todas las tareas de tu grupo. ¡La colaboración nunca fue tan fácil!</p>
                            
                            <div class="flex justify-center">
                                <img
                                src="/Images/EquipoMovil.png"
                                alt="Imagen de la característica 1"
                                class="w-[12rem] md:w[15rem] rounded-md shadow-md"
                                />
                            </div>
                        </div>
                        
                    </div>
                </div>
                <div class="text-center hidden xl:block">
                    <div key="image">
                        <img
                            v-if="selectedImage === 1"
                            src="/Images/CalendarioPortatil.png"
                            alt="Imagen de la característica 1"
                            class="w-[45rem]  rounded-md shadow-md"
                        />
                        <img
                            v-if="selectedImage === 2"
                            src="/Images/TareaPortatil.png"
                            alt="Imagen de la característica 2"
                            class="w-[45rem]   rounded-md shadow-md"
                        />
                        <img
                            v-if="selectedImage === 3"
                            src="/Images/EquipoPortatil.png"
                            alt="Imagen de la característica 3"
                            class="w-[45rem] rounded-md shadow-md"
                        />
                    </div>
                </div>            
            </div>                    
        </section>

        

        <!-- Sección Preguntas Frecuentes -->
        <section class="bg-gray-900 py-16">
            <div class="container mx-auto">
                <h2 class="text-4xl text-center font-mono text-white">Preguntas Frecuentes (FAQ)</h2>
                <div class="mt-8 flex flex-col justify-center items-center">
                    <div class="bg-gray-800 w-11/12 lg:w-8/12 p-6 rounded-md mb-4">
                        <h3 class="text-2xl text-green-400">¿Qué es Organify?</h3>
                        <p class="text-gray-300 mt-2">Organify es una plataforma gratuita diseñada para ayudarte a organizar tu vida, trabajo y proyectos de manera eficiente.</p>
                    </div>
                    <div class="bg-gray-800 w-11/12 lg:w-8/12 p-6 rounded-md mb-4">
                        <h3 class="text-2xl text-green-400">¿Cómo puedo registrarme?</h3>
                        <p class="text-gray-300 mt-2">Puedes registrarte haciendo clic en el enlace de "Registrarse" en la parte superior de la página y completando el formulario de registro.</p>
                    </div>
                    <div class="bg-gray-800 w-11/12 lg:w-8/12 p-6 rounded-md mb-4">
                        <h3 class="text-2xl text-green-400">¿Es Organify realmente gratuito?</h3>
                        <p class="text-gray-300 mt-2">Sí, Organify es totalmente gratuito. Pero siempre puedes aportar tu granito de arena con el boton de ¡Apóyanos!.</p>
                    </div>
                    <div class="bg-gray-800 w-11/12 lg:w-8/12 p-6 rounded-md mb-4">
                        <h3 class="text-2xl text-green-400">¿Cómo puedo colaborar con Organify?</h3>
                        <p class="text-gray-300 mt-2">Puedes colaborar con Organify de muchas maneras, como informando de errores, sugiriendo nuevas funcionalidades o compartiendo la plataforma con tus amigos y familiares.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <div id="donate-button-container">
        <span class="relative flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400  opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-400 "></span>
        </span>
        <a class=" text-white text-bold" target="_blank" href="https://www.paypal.com/donate/?hosted_button_id=LQTKMJPQSDDNW">
            <button class="bg-green-400 text-gray-900 font-bold text-sm md:text-xl px-4 py-2 rounded-full">
                <font-awesome-icon :icon="['fas', 'piggy-bank']" />
                <span class="ml-2">¡Apóyanos!</span>
            </button>
            
        </a>
    </div>
    
    <footer class="py-16 bg-gray-800 text-center text-md text-white">
        <div class=" flex flex-col lg:flex-row gap-5 justify-around items-center ">
            <p>&copy; 2024 Organify. Todos los derechos reservados.</p>
            <div class="flex flex-row justify-around gap-3 items-center">
                <p class="">Proyecto realizado por Miguel Hurtado.</p>
                <div class="text-2xl">
                    <a href="https://www.linkedin.com/in/miguel-hurtado-mesa-7ab7b12b7/" target="_blank" class="text-green-400 mr-2 hover:underline"><font-awesome-icon :icon="['fab', 'linkedin']" /></a> 
                    <a href="https://github.com/MiguelHurtadoDevelop" target="_blank" class="text-green-400 mr-2 hover:underline"><font-awesome-icon :icon="['fab', 'square-github']" /></a>
                    <a href="mailto:miguelhurtado.developer@gmail.com" class="text-green-400 hover:underline"><font-awesome-icon :icon="['fas', 'envelope']" /></a>
                </div>
            </div>
            
        </div>
    </footer>
</template>

<style>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.5s;
}
.fade-enter, .fade-leave-to {
    opacity: 0;
}



#donate-button-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
}

.animation{
    animation: bounce 4s infinite;

    
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(-5%);
        animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
    }
    50% {
        transform: translateY(0);
        animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
    }
    }
</style>
