<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import Editor from '@tinymce/tinymce-vue';
import { ref, onMounted } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

const props = defineProps({
    post: Object
})
const back_to = ref(new URLSearchParams(window.location.search).get('back_to'))
const previewThumbnailUrl = ref('')
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
    id: props.post.id,
    title: props.post.title,
    thumbnail: props.post.thumbnail,
    content: props.post.content,
    back_to: decodeURIComponent(back_to.value),
});

const handleChangeThumbnail = (e) => {
    const file = e.target.files[0];
    previewThumbnailUrl.value = URL.createObjectURL(file);
}

function filePicker(callback, value, meta) {
    cloudinaryWidgetRef.value.open()
    inputImageTinymce.value = callback
    console.log(inputImageTinymce.value )
    console.log(callback)
}

const submit = () => {
    form.post(route('admin.post.update'), {
        onSuccess: () => {
            toast.remove(toastId.value)
            toast.success('Cập nhật thành công!');
        },
        onProgress: () => toastId.value = toast.loading('Loading...'),
        onError: () => { toast.remove(toastId.value) },
    });
};
</script>

<template>

    <Head title="Cập nhật bài viết" />
    <AuthenticatedLayout>
        <div>
            <p class="px-5 dark:text-white text-2xl">Cập nhật bài viết #{{ props.post.id }}</p>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
            <form @submit.prevent="submit" class="max-w-5xl mx-auto">
                <div class="mb-5 mx-auto">
                    <InputLabel for="name" value="Tiêu đề" />
                    <TextInput type="text"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        v-model="form.title" autocomplete="name" />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="mt-5 mb-11">
                    <InputLabel for="thumbnail" value="Chọn ảnh bộ sưu tập" class="cursor-pointer" />
                    <input type="file" id="thumbnail" @input="form.thumbnail = $event.target.files[0]" class="hidden"
                        @change="handleChangeThumbnail" />
                    <InputError class="mt-2" :message="form.errors.thumbnail" />
                    <img v-if="!previewThumbnailUrl && form.thumbnail" :src="form.thumbnail" class="w-52 mt-4 h-52" />
                    <img v-if="previewThumbnailUrl" :src="previewThumbnailUrl" class="w-52 mt-4 h-52" />
                </div>

                <div class="mt-5 rounded ">
                    <InputLabel value="Nội dung bài viết" class="cursor-pointer mb-2" />

                    <Editor v-model="form.content" api-key="lndyux1kq5azq43ydw1r6vjsu3ogfzjkndo7xspczt5cnge0" :init="{
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

                <div class="text-right p-4">
                    <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Lưu
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
