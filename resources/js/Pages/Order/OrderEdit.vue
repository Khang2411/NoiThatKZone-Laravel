<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Observer from '@/Components/order/Observer.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

const props = defineProps({
    order: Object,
    coupons: Object,
    cities: Object,
    districts: Object,
    wards: Object,
    products: Object,
})

const districtList = ref(props.districts)
const wardList = ref(props.wards)
const subtotal = ref(props.order.subtotal)
const total = ref(props.order.total)
const couponCode = ref()
const discount = ref()
const couponType = ref()

const getItems = async () => {
    axios.get(props.products.next_page_url).then((response) => {
        console.log(response.data)

        props.products.data = [...props.products.data, ...response.data.data]

        props.products.next_page_url = response.data.next_page_url
    })
}

const listStatus = [{
    id: 'completed',
    name: 'Hoàn thành'
},
{
    id: 'pending',
    name: 'Chờ thanh toán'
}, {
    id: 'processing',
    name: 'Đang xử lí'
}, {
    id: 'cancelled',
    name: 'Đã hủy'
},
{
    id: 'confirmed',
    name: 'Đã xác nhận'
}]

const form = useForm({
    id: props.order.id,
    email: props.order.user?.email,
    method: props.order.method,
    status: props.order.status,
    phone: props.order.phone,
    ship_address: props.order.ship_address,
    city_id: props.order.city_id,
    district_id: props.order.district_id,
    ward_id: props.order.ward_id,
    coupon_code: props.order.coupon_code,
    coupon_type: props.order?.coupon_type,
    discount: props.order.discount,
    products: props.order.products,
    deleteProductId: [],
    search: ''
})

const onHanldeChange = async (e) => {
    console.log(e.target.value)
    if (e.target.name === 'city_id') {
        const fetchDistrict = await fetch(`/api/v1/districts/${e.target.value}`)
        const response = await fetchDistrict.json();
        districtList.value = response.data
        form.district_id = ''
        form.ward_id = ''
    }
    if (e.target.name === 'district_id') {
        const fetchDistrict = await fetch(`/api/v1/wards/${e.target.value}`)
        const response = await fetchDistrict.json();
        wardList.value = response.data
        form.ward_id = ''
    }
}
const handleDeleteProduct = (id) => {
    let product = form.products.filter((item) => item.id !== id)
    form.deleteProductId.push(id)
    form.products = product
    form.coupon_code = ''
    form.discount = ''

    let temp = 0;
    product.map((item) => {
        temp = temp + item.pivot.quantity * item.pivot.price

    })
    subtotal.value = temp;
    total.value = temp;
}

const handleDecrement = (id) => {
    form.products.map((item) => {
        if (item.id === id) {
            if (item.pivot.quantity > 1) {
                item.pivot.quantity -= 1
                subtotal.value = subtotal.value - item.pivot.price
                total.value = subtotal.value
                form.coupon_code = ''
                form.discount = ''
            } else {
                item.pivot.quantity -= 0
            }
        }
    })
}

const handleIncrement = (id) => {
    form.products.map((item) => {
        if (item.id === id) {
            if (item.pivot.quantity < item.stock) {
                item.pivot.quantity += 1;
                subtotal.value = subtotal.value + item.pivot.price
                total.value = subtotal.value
                form.coupon_code = ''
                form.discount = ''
            } else {
                item.pivot.quantity += 0;
            }
        }
    })
}

const handleAddProduct = (product) => {
    product['pivot'] = { price: product.price, quantity: 1 }
    console.log(props.order)
    form.products = [...form.products, product]
    subtotal.value = subtotal.value + product.price
    if (props.order.discount >= 0 && props.order.discount <= 100) {
        total.value = subtotal.value - (subtotal.value * (form.discount / 100))
    } else {
        total.value = subtotal.value - form.discount
    }
}

const handleChangeCoupon = (type, code, amount, minimumSpend) => {
    if (subtotal.value >= minimumSpend) {
        couponCode.value = code
        discount.value = amount
        couponType.value = type
    } else {
        couponCode.value = ''
        discount.value = ''
        couponType.value = ''
        toast.error(`Không đủ điều kiện. Đơn hàng phải trên ${new Intl.NumberFormat('vi-VN', {
            style: 'currency', currency: 'VND'
        }).format(minimumSpend)}`);
    }
}

const submitCoupon = () => {
    form.coupon_code = couponCode.value
    form.discount = discount.value
    form.coupon_type = couponType.value

    if (form.coupon_type === 'percent') {
        total.value = subtotal.value - (subtotal.value * (form.discount / 100))
    } else {
        total.value = subtotal.value - form.discount
    }
}

const submit = () => {
    form.post(route('admin.order.update'), {
        onSuccess: () => {
            router.reload({ only: ['order'] })
            toast.success('Cập nhật thành công!');
        }
    });
};

</script>

<template>
    <div>

        <Head title="Thêm bộ sưu tập" />
        <AuthenticatedLayout>
            <div>
                <p class="px-5 dark:text-white text-2xl">Cập nhật đơn đặt hàng</p>
            </div>
            <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 w-10/12 m-auto mt-5 text-white">
                <form @submit.prevent="submit" class="max-w-4xl mx-auto">
                    <div class="mb-5">
                        <InputLabel for="phone" value="Số điện thoại" />
                        <TextInput type="text"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            v-model="form.phone" autocomplete="phone" />
                        <InputError class="mt-2" :message="form.errors.phone" />
                    </div>

                    <div class="mb-5">
                        <InputLabel for="email" value="Email" />
                        <TextInput type="text"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-400	 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            v-model="form.email" autocomplete="email" disabled />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div class="mb-5">
                        <InputLabel for="status" value="Trạng thái" />
                        <select id="status"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                            v-model="form.status">
                            <option disabled value="">Chọn trạng thái</option>
                            <option v-for="(status, index) in listStatus" :key="index" :value="status.id">
                                {{ status.name }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.status" />
                    </div>

                    <div class="mb-5">
                        <InputLabel for="ship_address" value="Số nhà" />
                        <TextInput type="text"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            v-model="form.ship_address" autocomplete="ship_address" />
                        <InputError class="mt-2" :message="form.errors.ship_address" />
                    </div>

                    <div class="mb-5">
                        <div class="grid md:grid-cols-3 md:gap-6">
                            <div class="relative z-0 w-full mb-5 group">
                                <InputLabel for="city" value="Tỉnh/Thành" />
                                <select id="city" @change="onHanldeChange($event)"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    name="city_id" v-model="form.city_id">
                                    <option disabled value="">Chọn tỉnh/thành</option>
                                    <option v-for="(city, index) in cities" :key="index" :value="city.id">
                                        {{ city.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="relative z-0 w-full mb-5 group">
                                <InputLabel for="city" value="Quận/Huyện" />
                                <select id="district_id" @change="onHanldeChange($event)"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    name='district_id' v-model="form.district_id">
                                    <option disabled value="">Chọn quận/huyện</option>
                                    <option v-for="(district, index) in districtList" :key="index" :value="district.id">
                                        {{ district.name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.district_id" />
                            </div>

                            <div class="relative z-0 w-full mb-5 group">
                                <InputLabel for="ward_id" value="Phường/Xã" />
                                <select id="ward_id" @change="onHanldeChange($event)"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    name='ward_id' v-model="form.ward_id">
                                    <option disabled value="">Chọn phường/xã</option>
                                    <option v-for="(ward, index) in wardList" :key="index" :value="ward.id">
                                        {{ ward.name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.ward_id" />
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="relative overflow-x-auto">
                            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                <thead
                                    class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">
                                            Tên
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center">
                                            Giá
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center">
                                            Số lượng
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center">
                                            Tổng
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(product, index) in form.products" :key="index"
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <th scope="row"
                                            class="w-40 px-2 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            <div
                                                class="flex items-center px-6 py-4 text-gray-900 whitespace-nowrap dark:text-white">
                                                <img class="w-14 h-14" :src=product.thumbnail alt="product" />
                                                <div class="ps-3">
                                                    <span class="text-xs font-semibold">{{ product.name }}</span>
                                                </div>
                                            </div>
                                        </th>
                                        <td class="px-6 py-4 text-center">
                                            {{ new Intl.NumberFormat('vi-VN', {
                                                style: 'currency', currency: 'VND'
                                            }).format(product.pivot.quantity * product.pivot.price) }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex gap-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    @click="handleDecrement(product.id)" stroke-width="1.5"
                                                    stroke="currentColor" class="w-6 h-6 cursor-pointer">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                                {{ product.pivot.quantity }}
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    @click="handleIncrement(product.id)" stroke-width="1.5"
                                                    stroke="currentColor" class="w-6 h-6 cursor-pointer">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            {{ new Intl.NumberFormat('vi-VN', {
                                                style: 'currency', currency:
                                                    'VND'
                                            }).format(product.pivot.quantity * product.pivot.price) }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                @click="handleDeleteProduct(product.id)" stroke-width="1.5"
                                                stroke="currentColor" class="w-6 h-6 cursor-pointer">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-right p-3 dark:text-white">
                            <div>
                                Tạm tính: {{ new Intl.NumberFormat('vi-VN', {
                                    style: 'currency', currency:
                                        'VND'
                                }).format(subtotal) }}
                            </div>
                            <div>
                                Mã Coupon: {{ form.coupon_code ? form.coupon_code : 'Không có' }}
                                {{ form.discount && (form.discount <= 100 ? `(${form.discount}%)` : `(${new
                                    Intl.NumberFormat('vi-VN', {
                                        style: 'currency', currency: 'VND'
                                    }).format(form.discount)})`) }} </div>
                                    <div>
                                        Tổng tiền: {{ new Intl.NumberFormat('vi-VN', {
                                            style: 'currency', currency:
                                                'VND'
                                        }).format(total) }}
                                    </div>

                                    <button type="button" data-modal-target="addProduct" data-modal-show="addProduct"
                                        class="mr-2 text-gray-900 hover:text-white border border-gray-800 hover:bg-gray-900 focus:ring-4 focus:outline-none 
                                    focus:ring-gray-300 font-medium rounded-lg text-xs mt-2 px-2 py-2   dark:border-gray-600 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-800">
                                        Thêm sản phẩm
                                    </button>

                                    <button type="button" data-modal-target="editUserModal" data-modal-show="editUserModal"
                                        class="text-gray-900 hover:text-white border border-gray-800 hover:bg-gray-900 focus:ring-4 focus:outline-none 
                                    focus:ring-gray-300 font-medium rounded-lg text-xs mt-2 px-2 py-2   dark:border-gray-600 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-800">
                                        Chọn coupon
                                    </button>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing">
                                Lưu
                            </PrimaryButton>
                        </div>
                </form>

                <!-- Modal Add Product -->
                <div id="addProduct" tabindex="-1" aria-hidden="true"
                    class="fixed top-0 left-0 right-0 z-50 items-center justify-center hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
                    <div class="relative w-full max-w-2xl max-h-full">
                        <!-- Modal content -->
                        <form @submit.prevent="submitCoupon" class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                            <!-- Modal header -->
                            <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-gray-600">
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                    Thêm sản phẩm
                                </h3>
                                <button type="button"
                                    class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                                    data-modal-hide="addProduct">
                                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 14 14">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                    </svg>
                                    <span class="sr-only">Close modal</span>
                                </button>
                            </div>
                            <!-- Modal body -->
                            <div>
                                <div class="p-5 mb-5 overflow-y-scroll h-[500px]">
                                    <div class="relative w-1/2 mx-auto">
                                        <div
                                            class="absolute inset-y-0 rtl:inset-r-0 start-0 flex items-center ps-3 pointer-events-none">
                                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                            </svg>
                                        </div>
                                        <TextInput type="text"
                                            class="block pt-2 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg w-80 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                            v-model="form.search" placeholder="Tìm kiếm" autocomplete="search"
                                            @keyup="handleSearch($event)" />
                                    </div>
                                    <div class="mt-5">
                                        <div v-for="(product, index) in products.data" :key="index"
                                            class="flex gap-5 mb-5 p-5 shadow-lg border border-[#454c59] justify-between">
                                            <div>
                                                <img class="w-14 h-14" :src=product.thumbnail alt="product" />
                                            </div>
                                            <div class="dark:text-white">
                                                <div>{{ product.name }}</div>
                                                <div class="text-[#d0021c] font-bold"> {{ new Intl.NumberFormat('vi-VN', {
                                                    style: 'currency', currency:
                                                        'VND'
                                                }).format(product.price) }}</div>
                                            </div>

                                            <div>
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    @click="handleAddProduct(product)" stroke-width="1.5"
                                                    stroke="currentColor" class="w-8 h-8 cursor-pointer">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                            </div>
                                        </div>
                                        <Observer @intersect="getItems" />
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal Coupon  -->
                <div id="editUserModal" tabindex="-1" aria-hidden="true"
                    class="fixed top-0 left-0 right-0 z-50 items-center justify-center hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
                    <div class="relative w-full max-w-2xl max-h-full">
                        <!-- Modal content -->
                        <form @submit.prevent="submitCoupon" class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                            <!-- Modal header -->
                            <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-gray-600">
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                    Chọn Coupon
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
                            <!-- Modal boy -->
                            <div class="p-10">
                                <div class="mb-5">
                                    <div>
                                        <div class="mb-4 flex gap-2">
                                            <input id='coupon' name="coupon" type="radio" value=""
                                                @change="handleChangeCoupon('', '', '', '')" />

                                            <InputLabel for='coupon' class="ml-2 dark:text-[#B6C6C2]">
                                                Không chọn
                                            </InputLabel>
                                        </div>
                                        <div class="mb-4 flex gap-2" v-for="(coupon, index) in coupons" :key="index">
                                            <input :id=coupon.code name="coupon" type="radio" :value="coupon.code"
                                                @change="handleChangeCoupon(coupon.type, coupon.code, coupon.amount, coupon.minimum_spend)" />

                                            <InputLabel :for=coupon.code class="ml-2 dark:text-[#B6C6C2]">
                                                {{ coupon.name + ' - ' + coupon.code }}
                                            </InputLabel>
                                        </div>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.coupon" />
                                </div>
                            </div>

                            <div class="text-right p-2">
                                <PrimaryButton data-modal-hide="editUserModal" class="ms-4"
                                    :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Lưu
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    </div>
</template>


<style lang="scss" scoped></style>