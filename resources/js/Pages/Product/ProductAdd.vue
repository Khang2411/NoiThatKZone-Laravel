<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Editor from '@tinymce/tinymce-vue';
import { ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

const previewUrl = ref('')
const previewMultipleUrl = ref([])
const toastId = ref('');

defineProps({
    collections: Object
})
const form = useForm({
    name: '',
    collection_id: '',
    price: '',
    promotion_price: '',
    describe: '',
    thumbnail: '',
    detail_images: [],
    tags: [],
    is_featured: false,
    is_hot: false,
});

const handleThumbnail = (e) => {
    const file = e.target.files[0];
    previewUrl.value = URL.createObjectURL(file);
}

const handleDetailImages = (e) => {
    const selectedFiles = [];
    const targetFiles = e.target.files;
    const targetFilesObject = [...targetFiles]
    targetFilesObject.map((file) => {
        return selectedFiles.push(URL.createObjectURL(file))
    })
    previewMultipleUrl.value = selectedFiles
}

function filePicker(callback, value, meta) {
    var x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
    var y = window.innerHeight || document.documentElement.clientHeight || document.getElementsByTagName('body')[0].clientHeight;

    var cmsURL = 'http://127.0.0.1:8000/' + 'laravel-filemanager?editor=' + meta.fieldname;
    if (meta.filetype == 'image') {
        cmsURL = cmsURL + '&type=Images';
    } else { cmsURL = cmsURL + '&type=Files'; }
    tinyMCE.activeEditor.windowManager.openUrl({
        url: cmsURL, title: 'Filemanager', width: x *
            0.8, height: y * 0.8, resizable: 'yes', close_previous: 'no', onMessage: (api, message) => {
                callback(message.content);
            }
    });
}

const submit = () => {
    form.post(route('admin.product.store'), {
        onProgress: () => toastId.value = toast.loading('Loading...'),
        onSuccess: () => {
            toast.remove(toastId.value)
            toast.success('Thêm thành công!')
        }
    });
};
</script>

<template>

    <Head title="Thêm sản phẩm" />
    <AuthenticatedLayout>
        <div>
            <p class="px-5 dark:text-white text-2xl">Thêm Sản Phẩm</p>
        </div>
        <div class="md:flex mt-5 bg-white dark:bg-gray-700 p-5 rounded">
            <ul class="flex-column space-y space-y-4 text-sm font-medium text-gray-500 dark:text-gray-400 md:me-4 mb-4 md:mb-0"
                id="default-tab" data-tabs-toggle="#default-tab-content" role="tablist">
                <li class="me-2" role="presentation">
                    <button
                        class="inline-flex items-center px-4 py-3 rounded-lg hover:text-gray-900 bg-gray-50 hover:bg-gray-100 w-full dark:bg-gray-800 dark:hover:bg-gray-700 dark:hover:text-white active"
                        id="profile-tab" data-tabs-target="#profile" type="button" role="tab" aria-controls="profile"
                        aria-selected="false">Thông tin chung</button>
                </li>
                <li class="me-2" role="presentation">
                    <button
                        class="inline-flex items-center px-4 py-3 rounded-lg hover:text-gray-900 bg-gray-50 hover:bg-gray-100 w-full dark:bg-gray-800 dark:hover:bg-gray-700 dark:hover:text-white"
                        id="dashboard-tab" data-tabs-target="#dashboard" type="button" role="tab"
                        aria-controls="dashboard" aria-selected="false">Ảnh sản phẩm</button>
                </li>
                <li class="me-2" role="presentation">
                    <button
                        class="inline-flex items-center px-4 py-3 rounded-lg hover:text-gray-900 bg-gray-50 hover:bg-gray-100 w-full dark:bg-gray-800 dark:hover:bg-gray-700 dark:hover:text-white"
                        id="settings-tab" data-tabs-target="#settings" type="button" role="tab" aria-controls="settings"
                        aria-selected="false">Bài viết</button>
                </li>
                <li role="presentation">
                    <button
                        class="inline-flex items-center px-4 py-3 rounded-lg hover:text-gray-900 bg-gray-50 hover:bg-gray-100 w-full dark:bg-gray-800 dark:hover:bg-gray-700 dark:hover:text-white"
                        id="contacts-tab" data-tabs-target="#contacts" type="button" role="tab" aria-controls="contacts"
                        aria-selected="false">Hiển thị</button>
                </li>
            </ul>

            <div id="default-tab-content" class="flex-1">
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <form @submit.prevent="submit">
                        <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="profile" role="tabpanel"
                            aria-labelledby="profile-tab">
                            <div>
                                <div>

                                </div>
                                <div class="flex flex-wrap items-center">
                                    <InputLabel for="name" value="Tên sản phẩm" class="w-1/6" />
                                    <TextInput type="text" class="mt-1 block w-6/12" v-model="form.name" />
                                </div>
                                <InputError class="mt-2" :message="form.errors.name" />
                                <div>
                                    <div class="mt-3 flex flex-wrap items-center ">
                                        <InputLabel for="collection" value="Thể loại" class="w-1/6" />
                                        <select id="collection"
                                            class="mt-1 block w-6/12 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                            v-model="form.collection_id">
                                            <option disabled value="">Chọn thể loại</option>
                                            <option v-for="(collection, index) in collections" :key="index"
                                                :value="collection.id">
                                                {{ collection.name }}
                                            </option>
                                        </select>
                                    </div>

                                    <InputError class="mt-2" :message="form.errors.collection_id" />
                                </div>

                                <div class="mt-3">
                                    <div>
                                        <div class="flex flex-wrap items-center">
                                            <InputLabel for="price" value="Giá" class="w-1/6" />
                                            <TextInput type="text" class="mt-1 block w-6/12" v-model="form.price"
                                                autocomplete="price" />
                                        </div>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.price" />

                                    <div class="mt-3">
                                        <div class="flex flex-wrap items-center">
                                            <InputLabel for="promotion_price" value="Giá khuyến mãi" class="w-1/6" />
                                            <TextInput type="text" class="mt-1 block w-6/12"
                                                v-model="form.promotion_price" autocomplete="promotion_price" />
                                        </div>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.promotion_price" />
                                </div>
                            </div>
                        </div>

                        <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="dashboard" role="tabpanel"
                            aria-labelledby="dashboard-tab">
                            <div>
                                <div>
                                    <InputLabel for="thumbnail" value="Chọn ảnh sản phẩm" class="cursor-pointer" />
                                    <input type="file" id="thumbnail" @input="form.thumbnail = $event.target.files[0]"
                                        class="hidden" @change="handleThumbnail" />
                                    <InputError class="mt-2" :message="form.errors.thumbnail" />
                                    <img v-if="previewUrl" :src="previewUrl" class="w-52 mt-4 h-52" />
                                </div>
                                <div class="mt-5">
                                    <InputLabel for="detail_images" value="Chọn ảnh chi tiết sản phẩm"
                                        class="cursor-pointer" />
                                    <input type="file" multiple id="detail_images"
                                        @input="form.detail_images = $event.target.files" class="hidden"
                                        @change="handleDetailImages" />
                                    <InputError class="mt-2" :message="form.errors.detail_images" />
                                    <div class="flex flex-wrap gap-2">
                                        <img v-for="(url, index) in previewMultipleUrl" :key="index" :src="url"
                                            class="w-52 mt-4 h-52" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="settings" role="tabpanel"
                            aria-labelledby="settings-tab">
                            <div>
                                <Editor v-model="form.describe"
                                    api-key="lndyux1kq5azq43ydw1r6vjsu3ogfzjkndo7xspczt5cnge0" :init="{
                        height: '500',
                        path_absolute: '/', selector: 'textarea.my-editor', relative_urls: false, plugins:
                            ['advlist autolink lists link image charmap print preview hr anchor pagebreak'
                                , 'searchreplace wordcount visualblocks visualchars code fullscreen'
                                , 'insertdatetime media nonbreaking save table directionality'
                                , 'emoticons template paste textpattern'],
                        toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media'
                        , file_picker_callback: function (callback, value, meta) { filePicker(callback, value, meta) }
                    }" />
                                <InputError class="mt-2" :message="form.errors.describe" />
                            </div>
                        </div>
                        <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 dark:text-white" id="contacts"
                            role="tabpanel" aria-labelledby="contacts-tab">
                            <div>
                                <input type="checkbox" v-model="form.is_hot" class="mr-2"> Sản phẩm bán chạy
                            </div>
                            <div class="mt-5">
                                <input type="checkbox" v-model="form.is_featured" class="mr-2"> Sản phẩm nổi bật
                            </div>
                        </div>
                        <div class="text-right pr-11">
                            <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing">
                                Thêm sản phẩm
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style lang="scss" scoped></style>