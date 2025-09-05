<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps<{ response: Response}>();

type Response = {
    vacancy_id: string;
    response_id: string;
    fio: string;
    email: string;
    phone: string;
    comment: string;
    category: string;
    title: string;
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: props.response?.title ?? 'Вакансия', href: '' },
    { title: props.response?.fio ?? 'Кандидат', href: '' },
];

</script>

<template>
    <Head :title="props.response.fio"/>

    <AppLayout :breadcrumbs="breadcrumbs">
        <h1>{{props.response.fio}}</h1>
        <h2>{{props.response.title}}</h2>
        <p>{{props.response.comment}}</p>
    </AppLayout>
</template>

<style scoped></style>
