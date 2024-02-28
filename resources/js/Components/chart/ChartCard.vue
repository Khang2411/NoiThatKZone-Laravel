<script setup>
import Chart from 'chart.js/auto';
import { ref, onMounted } from 'vue'

const canvasBarRef = ref(null)
const canvasDoughnutRef = ref(null)

const props = defineProps({
    product_number: Number,
    order_number: Number,
    user_number: Number,
})

onMounted(() => {
    new Chart(canvasBarRef.value, {
        type: 'bar',
        data: {
            labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
            datasets: [{
                label: 'Thống kê thu nhập theo tháng',
                data: [3],
                backgroundColor: [
                    '#ffd333'
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
})

onMounted(() => {
    new Chart(canvasDoughnutRef.value, {
        type: 'doughnut',
        data: {
            labels: ['Sản phẩm', 'Đơn đặt hàng', 'Khách hàng'],
            datasets: [{
                label: 'Thống kê thu nhập',
                data: [props.product_number, props.order_number, props.user_number],
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)'
                ],
                hoverOffset: 4
            }]
        },
    });
})
</script>

<template>
    <div>
        <div class="grid grid-cols-3 gap-4">
            <div class="col-span-2 bg-gray-50 dark:bg-gray-800 p-2">
                <canvas ref="canvasBarRef"></canvas>
            </div>
            <div class="bg-gray-50 dark:bg-gray-800">
                <canvas ref="canvasDoughnutRef"></canvas>
            </div>
        </div>

    </div>
</template>
<style lang="scss" scoped></style>