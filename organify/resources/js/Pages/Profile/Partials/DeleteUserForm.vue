<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.reset();
};
</script>

<template>
    <section class="bg-white p-6 rounded-lg text-white space-y-6">
        <header>
            <h2 class="text-lg font-medium text-gray-800">Eliminar Cuenta</h2>
            <p class="mt-1 text-sm text-gray-800">
                Una vez eliminada su cuenta, todos sus recursos y datos se borrarán permanentemente. 
                Antes de eliminar su cuenta, descargue los datos o la información que desee conservar.
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion" class="bg-red-700 hover:bg-red-900">Eliminar Cuenta</DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6 bg-white rounded-lg">
                <h2 class="text-lg font-medium text-gray-800">
                    ¿Estás seguro que deseas eliminar tu cuenta?
                </h2>
                <p class="mt-1 text-sm text-gray-800">
                    Una vez eliminada su cuenta, todos sus recursos y datos se borrarán permanentemente. Antes de eliminar su cuenta, descargue los datos o la información que desee conservar.
                </p>

                <div class="mt-6">
                    <InputLabel for="password" value="Password" class="text-gray-800 sr-only" />
                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-3/4 text-white bg-gray-700 border-green-500"
                        placeholder="Password"
                        @keyup.enter="deleteUser"
                    />
                    <InputError :message="form.errors.password" class="mt-2 text-red-500" />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal" class="bg-gray-600 hover:bg-gray-500">Cancelar</SecondaryButton>
                    <DangerButton
                        class="ml-3 bg-red-700 hover:bg-red-900"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Eliminar Cuenta
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
