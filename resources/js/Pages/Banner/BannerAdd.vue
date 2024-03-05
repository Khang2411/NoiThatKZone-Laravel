<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue'

const previewThumbnailUrl = ref('')
const previewBannerUrl = ref('')

const form = useForm({
    name: '',
    thumbnail: '',
    banner: '',
});

const submit = () => {
   // form.post(route('admin.collection.store'));
};

const previewThumbnail = (e) => {
    const file = e.target.files[0];
    previewThumbnailUrl.value = URL.createObjectURL(file);
}
const previewBanner = (e) => {
    const file = e.target.files[0];
    previewBannerUrl.value = URL.createObjectURL(file);
}

</script>

<template>
    <Head title="Thêm Banner" />
    <AuthenticatedLayout>
        <div>
            <p class="px-5 dark:text-white text-2xl">Ảnh Banner</p>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 w-10/12 m-auto mt-5">
            <form @submit.prevent="submit" class="max-w-xl mx-auto">
                <div class="mb-5">
                    <InputLabel for="name" value="Ảnh Slider" />
                    <TextInput type="text"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        v-model="form.name" autocomplete="name" />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="mt-5">
                    <InputLabel for="thumbnail" value="Chọn ảnh bộ sưu tập" class="cursor-pointer" />
                    <input type="file" id="thumbnail" @input="form.thumbnail = $event.target.files[0]" class="hidden"
                        @change="previewThumbnail" />
                    <InputError class="mt-2" :message="form.errors.thumbnail" />
                    <img v-if="previewThumbnailUrl" :src="previewThumbnailUrl" class="w-52 mt-4 h-52" />
                </div>

                <div class="mt-5">
                    <InputLabel for="banner" value="Chọn ảnh banner" class="cursor-pointer" />
                    <input type="file" id="banner" @input="form.banner = $event.target.files[0]" class="hidden"
                        @change="previewBanner" />
                    <InputError class="mt-2" :message="form.errors.banner" />
                    <img v-if="previewBannerUrl" :src="previewBannerUrl" class="w-full mt-4 h-52" />
                </div>

                <div class="text-right p-4">
                    <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Thêm bộ sưu tập
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<style lang="scss" scoped></style>