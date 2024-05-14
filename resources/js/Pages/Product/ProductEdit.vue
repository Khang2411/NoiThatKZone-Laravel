<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import Editor from '@tinymce/tinymce-vue';
import { ref, watch, onMounted } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import { formatNumeral } from 'cleave-zen'

const props = defineProps({
    collections: Object,
    product: Object
})

const back_to = ref(new URLSearchParams(window.location.search).get('back_to'))
const previewThumbnailUrl = ref('')
const previewDetailImage = ref(props.product.detail_images)
const detailImages = ref([])
const removeDetailImage = ref([])
const toastId = ref('');
const cloudinaryWidgetRef = ref(null)
const inputImageTinymce = ref('')

onMounted(() => {
    var myWidget = cloudinary.createUploadWidget({
        cloudName: 'dqsfwus9c',
        uploadPreset: 'posts_noithatkzone',
        folder: 'noithatkzone/posts',
        clientAllowedFormats: ["image"],
        multiple: false
    }, (error, result) => {
        if (!error && result && result.event === "success") {
            console.log('Done! Here is the image info: ', result.info);
            console.log(result.info.secure_url)
            inputImageTinymce.value(result.info.secure_url);
        }
    }
    )
    cloudinaryWidgetRef.value = myWidget;
})

const form = useForm({
    id: props.product.id,
    name: props.product.name,
    collection_id: props.product.collection_id,
    price: props.product.price_before_discount ? new Intl.NumberFormat().format(props.product.price_before_discount) : new Intl.NumberFormat().format(props.product.price),
    promotion_price: props.product.price_before_discount ? new Intl.NumberFormat().format(props.product.price) : null,
    describe: props.product.describe,
    thumbnail: props.product.thumbnail,
    detail_images: props.product.detail_images,
    tags: [],
    is_featured: props.product.is_featured,
    is_hot: props.product.is_hot,
    back_to: decodeURIComponent(back_to.value),
});

watch(
    () => form.price,
    () => {
        form.price = formatNumeral(form.price)
    }
)

watch(
    () => form.promotion_price,
    () => {
        console.log(`count is`)
        form.promotion_price = formatNumeral(form.promotion_price)
    }
)

const handleThumbnail = (e) => {
    const file = e.target.files[0];
    previewThumbnailUrl.value = URL.createObjectURL(file);
}

const handleDetailImages = (e) => {
    const targetFiles = e.target.files;
    const targetFilesObject = [...targetFiles]
    targetFilesObject.map((file) => {
        previewDetailImage.value = [...previewDetailImage.value, URL.createObjectURL(file)]
        detailImages.value = [...detailImages.value, file]
    })
}

const handleRemoveImage = (url, index) => {
    if (url.id) {
        removeDetailImage.value = [...removeDetailImage.value, url]
    } else {
        detailImages.value.splice(index - (previewDetailImage.value.length - detailImages.value.length), 1)
    }
    previewDetailImage.value.splice(index, 1);
}


function filePicker(callback, value, meta) {
    cloudinaryWidgetRef.value.open()
    inputImageTinymce.value = callback
}

const submit = () => {
    console.log(form)
    form.defaults({
        ...form,
        detail_images: detailImages.value,
        removeDetailImage: removeDetailImage.value
    })
    form.reset();
    form.post(route('admin.product.update'), {
        onProgress: () => toastId.value = toast.loading('Loading...'),
        onSuccess: () => {
            toast.remove(toastId.value)
            toast.success('Cập nhật thành công!')
        }, onError: () => { toast.remove(toastId.value) },
    });
}

</script>

<template>

    <Head title="Cập nhật sản phẩm" />
    <AuthenticatedLayout>
        <div>
            <p class="px-5 dark:text-white text-2xl">Cập nhật sản Phẩm</p>
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
                    <form @submit.prevent="submit()">
                        <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="profile" role="tabpanel"
                            aria-labelledby="profile-tab">
                            <div>
                                <div>

                                </div>
                                <div class="flex flex-wrap items-center">
                                    <InputLabel for="name" value="Tên sản phẩm" class="w-1/6" />
                                    <TextInput type="text" class="mt-1 block w-6/12" v-model="form.name"
                                        autocomplete="name" />
                                </div>
                                <InputError class="mt-2" :message="form.errors.name" />
                                <div>
                                    <div class="mt-3 flex flex-wrap items-center ">
                                        <InputLabel for="collection" value="Thể loại" class="w-1/6" />
                                        <select id="collection"
                                            class="mt-1 block w-6/12 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                            v-model="form.collection_id">
                                            <option disabled value="">Chọn thể loại</option>
                                            <option v-for="( collection, index ) in  collections " :key="index"
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
                                    <img v-if="!previewThumbnailUrl && form.thumbnail" :src="form.thumbnail"
                                        class="w-52 mt-4 h-52" />
                                    <img v-if="previewThumbnailUrl" :src="previewThumbnailUrl" class="w-52 mt-4 h-52" />
                                </div>
                                <div class="mt-5">
                                    <InputLabel for="detail_images" value="Chọn ảnh chi tiết sản phẩm"
                                        class="cursor-pointer" />
                                    <input type="file" multiple id="detail_images"
                                        @input="form.detail_images = $event.target.files" class="hidden"
                                        @change="handleDetailImages" />
                                    <InputError class="mt-2" :message="form.errors.detail_images" />
                                    <div class="flex flex-wrap gap-2">
                                        <div v-for="( url, index ) in  previewDetailImage " :key="index">
                                            <div v-if="url" class="w-44 mt-4 h-44 relative">
                                                <img :src="url.image ? url.image : url" class="w-44 mt-4 h-44" />
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor"
                                                    class="w-6 h-6 absolute top-0 right-0"
                                                    @click="handleRemoveImage(url, index)">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                            </div>
                                        </div>
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
                        toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media',
                        file_picker_callback: function (callback, value, meta) { filePicker(callback, value, meta) }
                    }" />
                                <InputError class="mt-2" :message="form.errors.describe" />

                            </div>
                        </div>
                        <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="contacts" role="tabpanel"
                            aria-labelledby="contacts-tab">
                            <div>
                                <input type="checkbox" v-model="form.is_hot" :checked="form.is_hot === 1" class="mr-2">
                                Sản phẩm bán chạy
                            </div>
                            <div class="mt-5">
                                <input type="checkbox" v-model="form.is_featured" :checked="form.is_featured === 1"
                                    class="mr-2"> Sản phẩm nổi bật
                            </div>
                        </div>
                        <div class="text-right pr-11">
                            <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing">
                                Lưu
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style lang="scss" scoped></style>