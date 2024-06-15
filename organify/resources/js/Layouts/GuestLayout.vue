<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-800">
        <div class="fixed top-0 bg-gray-800 p-4 w-full">
            <Link href="/">
                <ApplicationLogo class=" w-auto h-12 fill-current text-gray-500" />
            </Link>
        </div>

        <div
            class="w-[22rem] lg:w-[45rem] max-h-[36rem] lg:max-h-[45rem] sm:max-w-md mt-24 px-6 py-4 bg-white shadow-md  overflow-auto rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
