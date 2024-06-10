<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'inline-flex  items-center border-b-1 border-green-600 text-xl font-semibold text-white bg-green-100 focus:outline-none focus:border-green-700 transition duration-150 ease-in-out'
        : 'inline-flex  items-center border-b-1 border-transparent text-xl font-semibold text-gray-400 hover:text-white hover:border-gray-500 focus:outline-none focus:text-white focus:border-gray-500 transition duration-150 ease-in-out'
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
