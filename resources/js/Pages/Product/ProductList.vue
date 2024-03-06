<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Paginate from '@/Components/Paginate.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import Editor from '@tinymce/tinymce-vue';
import { debounce } from 'lodash';
import moment from "moment";
import { ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

const props = defineProps({
    products: Object,
    collections: Object,
    list_action: Object,
    status: String,
    count: Array
})
const queryParam = ref(new URLSearchParams(window.location.search).get('status'))
const previewThumbnailUrl = ref('')
const previewDetailImage = ref([])
const detailImages = ref([])
const removeDetailImage = ref([])
const toastId = ref('');

const form = useForm({
    search: new URLSearchParams(window.location.search).get('search'),
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
    removeDetailImage: [],
    list_check: [],
    all_selected: false
});

const handleSelectAll = () => {
    console.log(form.all_selected)
    if (form.all_selected === true) {
        for (let i in props.products.data) {
            form.list_check.push(props.products.data[i].id)
            console.log(props.products.data[i].id)
        }
    } else {
        form.list_check = []
    }
}

const handleModal = (product) => {
    console.log(product)
    previewDetailImage.value = [...product.detail_images]
    previewThumbnailUrl.value = ''
    removeDetailImage.value = ''
    detailImages.value = ''
    form.defaults({
        id: product.id,
        name: product.name,
        describe: product.describe,
        collection_id: product.collection_id,
        price: product.price_before_discount ? product.price_before_discount : product.price,
        promotion_price: product.price_before_discount ? product.price : "",
        thumbnail: product.thumbnail,
        is_featured: product.is_featured,
        is_hot: product.is_hot

    })
    form.reset();
    form.errors = [];
}

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

function filePicker(callback, value, meta) {
    var x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
    var y = window.innerHeight || document.documentElement.clientHeight || document.getElementsByTagName('body')[0].clientHeight;

    var cmsURL = import.meta.env.VITE_SENTRY_DSN_PUBLIC + '/' + 'laravel-filemanager?editor=' + meta.fieldname;
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

const handleRemoveImage = (url, index) => {
    if (url.id) {
        removeDetailImage.value = [...removeDetailImage.value, url]
    } else {
        detailImages.value.splice(index - (previewDetailImage.value.length - detailImages.value.length), 1)
    }
    previewDetailImage.value.splice(index, 1);
}

const handleAction = (action) => {
    console.log(action)
    router.post(route('admin.product.action'), {
        action: action,
        list_check: form.list_check
    }, {
        onSuccess: () => {
            router.reload({ only: ['products,count'] })
            toast.remove(toastId.value)
            toast.success('Thao tác thành công!');
        },
        onStart: () => { toastId.value = toast.loading('Loading...') }
    });
}

const handleRemove = (id) => {
    if (confirm("Bạn có muốn xóa?")) {
        router.post(route('admin.product.delete', id), {
        }, {
            onSuccess: () => {
                router.reload({ only: ['products,count'] })
                toast.remove(toastId.value)
                toast.success('Xóa thành công!');
            },
            onStart: () => { toastId.value = toast.loading('Loading...') }
        });
    }
}

const handleSearch = debounce((e) => {
    router.get('', { search: e.target.value }, { replace: true })
}, 500)


const submit = (id) => {
    form.defaults({
        ...form,
        detail_images: detailImages.value,
        removeDetailImage: removeDetailImage.value
    })
    form.reset();
    form.post(route('admin.product.update'), {
        onSuccess: () => {
            router.reload({ only: ['products'] })
            toast.remove(toastId.value)
            toast.success('Cập nhật thành công!');
            const targetEl = 'editUserModal';
            var currentModalObj = FlowbiteInstances.getInstance('Modal', targetEl);
            currentModalObj.hide();
        }, onProgress: () => toastId.value = toast.loading('Loading...')
    });
};
</script>

<template>

    <Head title="Danh sách sản phẩm" />
    <AuthenticatedLayout>
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">

            <div v-if="$page.props.flash.status"
                class="p-4 mb-4 text-yellow-800 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300"
                role="alert">
                <span class="font-medium">{{ $page.props.flash.status }}</span>
            </div>
            <div class="text-blue-400 dark:text-purple-50 text-sm flex gap-2">
                <Link :class="{ 'active': queryParam === 'active' }" class="[&.active]:border-b-4 border-indigo-500"
                    href="?status=active" :only="['products,list_action']">
                Tất cả ({{ count[0] }})</Link>
                <span class="after:content-['_|']"></span>
                <Link :class="{ 'active': queryParam === 'trash' }" class="[&.active]:border-b-4 border-indigo-500"
                    href="?status=trash" :only="['products,list_action']">Rác ({{ count[1] }})</Link>
            </div>
            <div
                class="flex items-center justify-between flex-column md:flex-row flex-wrap space-y-4 md:space-y-0 py-4 bg-white dark:bg-gray-900 px-2">
                <div>
                    <button id="dropdownActionButton" data-dropdown-toggle="dropdownAction"
                        class="inline-flex items-center text-gray-500 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm px-3 py-1.5 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
                        type="button">
                        <span class="sr-only">Action button</span>
                        Tùy chọn
                        <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 4 4 4-4" />
                        </svg>
                    </button>
                    <!-- Dropdown menu -->
                    <div id="dropdownAction"
                        class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700 dark:divide-gray-600">
                        <ul class="py-1 text-sm text-gray-700 dark:text-gray-200"
                            aria-labelledby="dropdownActionButton">
                            <li v-for="( [key, value], index ) in Object.entries(list_action) " :key="index">
                                <a href="#" @click="handleAction(key)"
                                    class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">{{
                value }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <label for="table-search" class="sr-only">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 rtl:inset-r-0 start-0 flex items-center ps-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                        </svg>
                    </div>
                    <input type="text" id="table-search-users" @keyup="handleSearch($event)"
                        class="block pt-2 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg w-80 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        placeholder="Tim kiếm">
                </div>
            </div>
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="p-4">
                            <div class="flex items-center">
                                <input id="checkbox-all-search" type="checkbox" @change="handleSelectAll()"
                                    v-model="form.all_selected"
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="checkbox-all-search" class="sr-only">checkbox</label>
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Tên
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Giá
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Thể loại
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Giá KM
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Thời gian
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Tác vụ
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="( product, index ) in  products.data" :key="index"
                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="w-4 p-4">
                            <div class="flex items-center">
                                <input id="checkbox-table-search-1" type="checkbox" v-model="form.list_check"
                                    :value="product.id"
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="checkbox-table-search-1" class="sr-only">checkbox</label>
                            </div>
                        </td>
                        <th scope="row">
                            <div class="flex items-center px-6 py-4 text-gray-900 whitespace-nowrap dark:text-white">
                                <img class="w-16 h-16" :src="product.thumbnail" alt="img-product">
                                <div class="ps-3">
                                    <span class="text-xs font-semibold">{{ product.name }}</span>
                                </div>
                            </div>

                        </th>
                        <td class="px-11 py-4">
                            {{ product.price_before_discount ? new Intl.NumberFormat('vi-VN', {
                style: 'currency', currency: 'VND'
            }).format(product.price_before_discount) : new Intl.NumberFormat('vi-VN', {
                style: 'currency', currency: 'VND'
            }).format(product.price) }}
                        </td>
                        <td class="px-6 py-4">
                            {{ product.cate_name }}
                        </td>
                        <td class="px-6 py-4">
                            {{ product.price_before_discount ? new Intl.NumberFormat('vi-VN', {
                style: 'currency', currency: 'VND'
            }).format(product.price) : '' }}
                        </td>
                        <td class="px-6 py-4">
                            {{ moment(product.updated_at).format("DD-MM-YYYY") }}
                        </td>
                        <td class="px-6 py-4 ">
                            <div class="flex items-center">
                                <a v-if="queryParam != 'trash'" href="#" type="button" @click="handleModal(product)"
                                    data-modal-target="editUserModal" data-modal-show="editUserModal"
                                    class="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" data-slot="icon" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </a>
                                <a href="#" @click="handleRemove(product.id)">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" data-slot="icon" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </a>
                            </div><!-- Modal toggle -->
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="mt-2 mb-2 px-2">
                <Paginate :links="products.links"></Paginate>
            </div>

            <!-- Edit user modal -->
            <div id="editUserModal" tabindex="-1" aria-hidden="true"
                class="fixed top-0 left-0 right-0 z-50 items-center justify-center hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
                <div class="relative w-full max-w-2xl max-h-full">
                    <!-- Modal content -->
                    <div class="md:flex mt-5 bg-white dark:bg-gray-700 p-5 rounded">
                        <ul class="flex-column space-y space-y-4 text-sm font-medium text-gray-500 dark:text-gray-400 md:me-4 mb-4 md:mb-0"
                            id="default-tab" data-tabs-toggle="#default-tab-content" role="tablist">
                            <li class="me-2" role="presentation">
                                <button
                                    class="inline-flex items-center px-4 py-3 rounded-lg hover:text-gray-900 bg-gray-50 hover:bg-gray-100 w-full dark:bg-gray-800 dark:hover:bg-gray-700 dark:hover:text-white active"
                                    id="profile-tab" data-tabs-target="#profile" type="button" role="tab"
                                    aria-controls="profile" aria-selected="false">Thông tin chung</button>
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
                                    id="settings-tab" data-tabs-target="#settings" type="button" role="tab"
                                    aria-controls="settings" aria-selected="false">Bài viết</button>
                            </li>
                            <li role="presentation">
                                <button
                                    class="inline-flex items-center px-4 py-3 rounded-lg hover:text-gray-900 bg-gray-50 hover:bg-gray-100 w-full dark:bg-gray-800 dark:hover:bg-gray-700 dark:hover:text-white"
                                    id="contacts-tab" data-tabs-target="#contacts" type="button" role="tab"
                                    aria-controls="contacts" aria-selected="false">Hiển thị</button>
                            </li>
                        </ul>

                        <div id="default-tab-content" class="flex-1">
                            <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                                <form @submit.prevent="submit(form.id)">
                                    <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="profile"
                                        role="tabpanel" aria-labelledby="profile-tab">
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
                                                        <option v-for="( collection, index ) in  collections "
                                                            :key="index" :value="collection.id">
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
                                                        <TextInput type="text" class="mt-1 block w-6/12"
                                                            v-model="form.price" autocomplete="price" />
                                                    </div>
                                                </div>
                                                <InputError class="mt-2" :message="form.errors.price" />

                                                <div class="mt-3">
                                                    <div class="flex flex-wrap items-center">
                                                        <InputLabel for="promotion_price" value="Giá khuyến mãi"
                                                            class="w-1/6" />
                                                        <TextInput type="text" class="mt-1 block w-6/12"
                                                            v-model="form.promotion_price"
                                                            autocomplete="promotion_price" />
                                                    </div>
                                                </div>
                                                <InputError class="mt-2" :message="form.errors.promotion_price" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="dashboard"
                                        role="tabpanel" aria-labelledby="dashboard-tab">
                                        <div>
                                            <div>
                                                <InputLabel for="thumbnail" value="Chọn ảnh sản phẩm"
                                                    class="cursor-pointer" />
                                                <input type="file" id="thumbnail"
                                                    @input="form.thumbnail = $event.target.files[0]" class="hidden"
                                                    @change="handleThumbnail" />
                                                <InputError class="mt-2" :message="form.errors.thumbnail" />
                                                <img v-if="!previewThumbnailUrl && form.thumbnail" :src="form.thumbnail"
                                                    class="w-52 mt-4 h-52" />
                                                <img v-if="previewThumbnailUrl" :src="previewThumbnailUrl"
                                                    class="w-52 mt-4 h-52" />
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
                                                            <img :src="url.image ? url.image : url"
                                                                class="w-44 mt-4 h-44" />
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor"
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

                                    <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="settings"
                                        role="tabpanel" aria-labelledby="settings-tab">
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
                                    <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="contacts"
                                        role="tabpanel" aria-labelledby="contacts-tab">
                                        <div>
                                            <input type="checkbox" v-model="form.is_hot" :checked="form.is_hot === 1"
                                                class="mr-2"> Sản phẩm bán chạy
                                        </div>
                                        <div class="mt-5">
                                            <input type="checkbox" v-model="form.is_featured"
                                                :checked="form.is_featured === 1" class="mr-2"> Sản phẩm nổi bật
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
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style lang="scss" scoped></style>