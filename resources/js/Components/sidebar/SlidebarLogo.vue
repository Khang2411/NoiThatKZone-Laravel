<script setup>
import { Link } from '@inertiajs/vue3';
</script>
<template>
    <div>
        <Link :href="route('dashboard')" class="text-center dark:text-white text-2xl">
        <p>KZone</p>
        </Link>
    </div>
</template>
<style lang="scss" scoped></style>