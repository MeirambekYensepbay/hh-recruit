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
    { title: props.response?.title ?? 'Вакансия', href: '/vacancy/'+props.response.vacancy_id },
    { title: props.response?.fio ?? 'Кандидат', href: '' },
];

</script>

<template>
    <Head :title="props.response.fio"/>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-4 md:col-span-2 dark:border-sidebar-border">
                    <h1 class="text-2xl font-semibold">{{props.response.fio}}</h1>
                    <h2 class="mt-1">{{props.response.title}}</h2>
                    <p class="prose">{{props.response.comment}}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped></style>
