<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue'
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

const toastId = ref('');

const form = useForm({
    name: '',
    code: '',
    limit: '',
    amount: '',
    type: '',
    minimum_spend: ''
});

const types = [
    {
        id: 'percent',
        name: 'Phần trăm'
    }, {
        id: 'fixed',
        name: 'Cố định'
    }]

const submit = () => {
    form.post(route('admin.coupon.store'), {
        onProgress: () => toastId.value = toast.loading('Loading...'),
        onSuccess: () => {
            toast.remove(toastId.value)
            toast.success('Thêm thành công!')
        }
    });
};

</script>

<template>

    <Head title="Thêm mã giảm giá" />
    <AuthenticatedLayout>
        <div>
            <p class="px-5 dark:text-white text-2xl">Thêm mã giảm giá</p>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 w-10/12 m-auto mt-5">
            <form @submit.prevent="submit" class="max-w-xl mx-auto">
                <div class="mb-5">
                    <InputLabel for="name" value="Tên thể loại" />
                    <TextInput type="text"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        v-model="form.name" />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="mb-5">
                    <InputLabel for="code" value="Mã" />
                    <TextInput type="text"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        v-model="form.code" />
                    <InputError class="mt-2" :message="form.errors.code" />
                </div>

                <div>
                    <div class="mb-5">
                        <InputLabel for="type" value="Chọn loại giảm giá" class="w-full" />
                        <select id="type"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                            v-model="form.type">
                            <option disabled value="">Chọn loại giảm giá</option>
                            <option v-for="(type, index) in types" :key="index" :value="type.id">
                                {{ type.name }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.type" />
                    </div>
                </div>

                <div class="mb-5">
                    <InputLabel for="name" value="Số tiền giảm" />
                    <TextInput type="text"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        v-model="form.amount" />
                    <InputError class="mt-2" :message="form.errors.amount" />
                </div>

                <div class="mb-5">
                    <InputLabel for="name" value="Chi tiêu tối thiểu" />
                    <TextInput type="text"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        v-model="form.minimum_spend" />
                    <InputError class="mt-2" :message="form.errors.minimum_spend" />
                </div>

                <div class="mb-5">
                    <InputLabel for="name" value="Giới hạn" />
                    <TextInput type="number" min="0"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        v-model="form.limit" />
                    <InputError class="mt-2" :message="form.errors.limit" />
                </div>

                <div class="text-right pr-2">
                    <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Thêm mã giảm giá
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<style lang="scss" scoped></style>