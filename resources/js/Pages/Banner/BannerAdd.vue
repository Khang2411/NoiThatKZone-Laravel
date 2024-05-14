<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

const props = defineProps({
    banners: Object
})
const previewThumbnailUrl = ref('')
const toastId = ref('');

const form = useForm({
    title: '',
    thumbnail: '',
    url: ''
});

const handleModal = (banner) => {
    console.log(banner)
    previewThumbnailUrl.value = ''
    form.defaults({
        id: banner.id,
        title: banner.title,
        thumbnail: banner.thumbnail,
        url: banner.url,
    })
    form.reset();
    form.errors = [];
}
const handleChangeThumbnail = (e) => {
    const file = e.target.files[0];
    previewThumbnailUrl.value = URL.createObjectURL(file);
}

const submit = () => {
    form.post(route('admin.banner.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // router.reload({ only: ['banners'] })
            toast.remove(toastId.value)
            toast.success('Cập nhật thành công!');
            const targetEl = 'editUserModal';
            var currentModalObj = FlowbiteInstances.getInstance('Modal', targetEl);
            currentModalObj.hide();
        },
        onProgress: () => toastId.value = toast.loading('Loading...')
    });
};
</script>

<template>

    <Head title="Danh sách ảnh banner" />
    <AuthenticatedLayout>
        <div>
            <p class="px-5 dark:text-white text-2xl">Ảnh Banner</p>
        </div>
        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800 w-11/12 m-auto mt-5">
            <form @submit.prevent="submit">
                <div class="flex flex-wrap gap-2 justify-between">
                    <div class="md:w-[49%]">
                        <div class="relative">
                            <img class="rounded-[10px] lg:h-[510px] md:h-[310px]" :src=banners[0].thumbnail
                                alt="Picture of the banner" />
                            <div class="absolute max-w-[300px] top-1 left-1">
                                <h6 class="text-[#32355d] font-bold" fontWeight={600}>{{ banners[0].title }}</h6>
                            </div>
                            <button type="button" data-modal-target="editUserModal" data-modal-show="editUserModal"
                                @click="handleModal(banners[0])"
                                class="absolute bottom-3 right-3 text-gray-900 bg-gradient-to-r from-red-200 via-red-300 to-yellow-200 hover:bg-gradient-to-bl focus:ring-4 focus:outline-none focus:ring-red-100 dark:focus:ring-red-400 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">
                                Cập nhật</button>

                        </div>
                    </div>
                    <div class="md:w-[23.5%]">
                        <div class="relative mb-1">
                            <img class="rounded-[10px] lg:h-[255px] md:h-[155px]" :src=banners[1].thumbnail
                                alt="Picture of the banner" />

                            <div class="absolute max-w-[300px] top-1 left-1">
                                <h6 class="text-[#32355d] font-bold" fontWeight={600}>{{ banners[1].title }}</h6>
                            </div>
                            <button type="button" data-modal-target="editUserModal" data-modal-show="editUserModal"
                                @click="handleModal(banners[1])"
                                class="absolute bottom-3 right-3 text-gray-900 bg-gradient-to-r from-red-200 via-red-300 to-yellow-200 hover:bg-gradient-to-bl focus:ring-4 focus:outline-none focus:ring-red-100 dark:focus:ring-red-400 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">
                                Cập nhật</button>
                        </div>

                        <div class="relative">
                            <img class="rounded-[10px] lg:h-[255px] md:h-[155px]" :src=banners[2].thumbnail
                                alt="Picture of the banner" />
                            <div class="absolute max-w-[300px] top-1 left-1">
                                <h6 class="text-[#32355d] font-bold" fontWeight={600}>{{ banners[2].title }}</h6>
                            </div>
                            <button type="button" data-modal-target="editUserModal" data-modal-show="editUserModal"
                                @click="handleModal(banners[2])"
                                class="absolute bottom-3 right-3 text-gray-900 bg-gradient-to-r from-red-200 via-red-300 to-yellow-200 hover:bg-gradient-to-bl focus:ring-4 focus:outline-none focus:ring-red-100 dark:focus:ring-red-400 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">
                                Cập nhật
                            </button>
                        </div>

                    </div>

                    <div class="md:w-[23.5%]">
                        <div class="relative">
                            <img class="rounded-[10px] lg:h-[510px] md:h-[310px]" :src=banners[3].thumbnail
                                alt="Picture of the banner" />
                            <div class="absolute max-w-[300px] top-1 left-1">
                                <h6 class="text-[#32355d] font-bold" fontWeight={600}>{{ banners[3].title }}</h6>
                            </div>
                            <button type="button" data-modal-target="editUserModal" data-modal-show="editUserModal"
                                @click="handleModal(banners[3])"
                                class="absolute bottom-3 right-3 text-gray-900 bg-gradient-to-r from-red-200 via-red-300 to-yellow-200 hover:bg-gradient-to-bl focus:ring-4 focus:outline-none focus:ring-red-100 dark:focus:ring-red-400 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2">
                                Cập nhật</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
    <div id="editUserModal" tabindex="-1" aria-hidden="true"
        class="fixed top-0 left-0 right-0 z-50 items-center justify-center hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative w-full max-w-2xl max-h-full">
            <!-- Modal content -->
            <div class="relative w-full max-w-2xl max-h-full">
                <form @submit.prevent="submit" class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-gray-600">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Cập nhật banner
                        </h3>
                        <button type="button"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                            data-modal-hide="editUserModal">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-10">
                        <div class="mb-5">
                            <InputLabel for="title" value="Tên tiêu đề cho banner" />
                            <TextInput type="text"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                v-model="form.title" />
                            <InputError class="mt-2" :message="form.errors.title" />
                        </div>

                        <div class="mb-5">
                            <InputLabel for="url" value="Đường dẫn cho banner" />
                            <TextInput type="text"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                v-model="form.url" />
                            <InputError class="mt-2" :message="form.errors.url" />
                        </div>

                        <div class="mt-5">
                            <InputLabel for="thumbnail" value="Chọn ảnh banner" class="cursor-pointer" />
                            <input type="file" id="thumbnail" @input="form.thumbnail = $event.target.files[0]"
                                class="hidden" @change="handleChangeThumbnail" />
                            <InputError class="mt-2" :message="form.errors.thumbnail" />
                            <img v-if="!previewThumbnailUrl && form.thumbnail" :src="form.thumbnail"
                                class="w-52 mt-4 h-52" />
                            <img v-if="previewThumbnailUrl" :src="previewThumbnailUrl" class="w-52 mt-4 h-52" />
                        </div>
                    </div>

                    <div class="text-right p-2">
                        <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing">
                            Lưu
                        </PrimaryButton>
                    </div>
                </form>
            </div>

        </div>
    </div>
</template>

<style lang="scss" scoped></style>