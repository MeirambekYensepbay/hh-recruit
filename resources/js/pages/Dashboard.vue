<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const vacancies = ref([]);

const fetchVacancies = async () => {
    try {
        const response = await axios.get(
            `/api/vacancies`
        );
        vacancies.value = response.data.items;
    } catch (error) {
        console.error(error);
    }
};

const hasSalary = (v: any) => {
    if (!v || typeof v !== 'object' || !v.salary || typeof v.salary !== 'object') return false;
    const { from, to } = v.salary;
    const hasFrom = Number.isFinite(+from);
    const hasTo   = Number.isFinite(+to);
    return hasFrom || hasTo;
};
const formatSalary = (s: any) => {
    const fmt = (n: any) => new Intl.NumberFormat('ru-RU').format(+n);
    const cur = s?.currency === 'KZT' || !s?.currency ? '₸' : s.currency;

    if (Number.isFinite(+s?.from) && Number.isFinite(+s?.to)) return `${fmt(s.from)} - ${fmt(s.to)} ${cur}`;
    if (Number.isFinite(+s?.from)) return `от ${fmt(s.from)} ${cur}`;
    if (Number.isFinite(+s?.to))   return `до ${fmt(s.to)} ${cur}`;
    return 'З/п не указана';
};
onMounted(() => {
    fetchVacancies();
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div
                    class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                    v-for="vacancy in vacancies"
                >
                    <div style="padding: 20px; display: flex; flex-direction: column; justify-content: space-between; height: 100%">
                        <div style="display: flex; flex-direction: column;">
                            <div>
                                <b>
                                    <h4>{{ vacancy.name }}</h4>
                                </b>
                                <p v-if="hasSalary(vacancy)" style="color: gray">
                                    {{ formatSalary(vacancy.salary) }} за месяц
                                </p>
                            </div>
                            <p style="margin-top: 20px">{{vacancy.experience.name}}</p>
                            <div>
                                {{vacancy.employer.name}}
                            </div>
                        </div>

                        <a style="margin-top: 20px; cursor: pointer" :href="route('getVacancy', vacancy.id)"
                            class="inline-block rounded-sm border border-black bg-[#1b1b18] px-5 py-1.5 text-sm leading-normal text-white hover:border-black hover:bg-black dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                        >
                            Анализ откликов
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
