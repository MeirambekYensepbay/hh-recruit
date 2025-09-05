<!-- resources/js/Pages/Vacancy.vue -->
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

type HHSalary = { from?: number | null; to?: number | null; currency?: string | null; gross?: boolean };
type Vacancy = {
    id: number;
    name: string;
    area?: { name?: string };
    employer?: { name?: string; logo_urls?: Record<string, string> };
    salary?: HHSalary;
    description?: string; // на hh приходит HTML
    published_at?: string;
    alternate_url?: string;
    experience?: { name: string };
    languages?: [{ name: string; }];
};

type Responses = {
    fio: string;
    url: string;
    response_id: string;
    vacancy_id: string;
    response: Response;
};
type Response = {
    id: number;
    vacancy_id: string;
    response_id: string;
    fio: string;
    email: string;
    phone: string;
    comment: string;
    category: string;
    title: string;
};

const props = defineProps<{ vacancy: Vacancy, resumes: [Responses]}>();
const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: props.vacancy?.name ?? 'Вакансия', href: '' },
];

const mustHave = ref('');
const niceToHave = ref('');
const categoryClass = ref({
    A: 'green',
    B: 'orange',
    C: 'red',
} as const);

type Category = keyof typeof categoryClass.value; // 'A' | 'B' | 'C'
const analyseResponse = async () => {
    try {
        const response = await axios.post(`/api/vacancy/analyse`, {
            vacancy: {
                vacancy_id: props.vacancy.id,
                title: props.vacancy.name,
                must_have: mustHave.value,
                nice_to_have: niceToHave.value,
                min_years: props.vacancy.experience?.name,
                languages: ''
            },
        }
    );
        console.log(response)
    } catch (error) {
        console.error(error)
    }
}
</script>
<style scoped>
    .green, .orange, .red {
        text-align: center;
        border-radius: 20px;
        width: 50px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .green {
        background: #c6eac2;
        border: 1px solid #2eb88a;
        color: #2eb88a;
    }
    .orange {
        background: #ffd8bc;
        border: 1px solid #e88c30;
        color: #e88c30;
    }
    .red {
        background: rgb(255, 190, 194);
        border: 1px solid #ef4e4e;
        color: #ef4e4e;
    }
</style>
<template>
    <Head :title="props.vacancy?.name ?? 'Vacancy'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <!-- Основная карточка -->
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-4 md:col-span-2 dark:border-sidebar-border">
                    <h1 class="text-2xl font-semibold">{{ props.vacancy.name }}</h1>
                    <p class="mt-1 text-gray-500">{{ props.vacancy.employer?.name }} • {{ props.vacancy.area?.name }}</p>

                    <p v-if="props.vacancy.salary" class="mt-2">
                        Зарплата:
                        {{ props.vacancy.salary?.from ?? '—' }} —
                        {{ props.vacancy.salary?.to ?? '—' }}
                        {{ props.vacancy.salary?.currency ?? '' }}
                    </p>

                    <!-- описание часто приходит как HTML -->
                    <div v-if="props.vacancy.description" class="prose mt-4" v-html="props.vacancy.description"></div>

                    <a
                        v-if="props.vacancy.alternate_url"
                        :href="props.vacancy.alternate_url"
                        target="_blank"
                        rel="noopener"
                        class="mt-4 inline-block underline"
                    >
                        Открыть на hh.kz
                    </a>
                </div>

                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                    <div class="relative overflow-hidden rounded-xl mb-2 text-sm text-gray-500 dark:border-sidebar-border">Список резюме</div>
                    <div class="border border-sidebar-border/70 p-2 grid auto-rows-min gap-4 md:grid-cols-2" v-for="resume in props.resumes" v-bind:key="resume.response_id">
                        <div>
                            <p style="font-size: 12px; font-weight: bold; color: gray">{{resume.response_id}}</p>
                            <p>{{resume.fio}}</p>
                        </div>
                        <div v-if="resume.response == null">
                        </div>
                        <a :href="route('show.vacancy', resume.response.id)" v-else style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; cursor: pointer" >
                            <span :class="resume.response.category != null ? categoryClass[resume.response.category as Category] : ''">{{ resume.response.category }}</span>
                        </a>

                    </div>

                    <div style="padding-top: 20px">
                        <p>Обязательные требования</p>
                        <textarea style="width: 100%; padding: 10px; border-radius: 5px; border: gray 1px solid" v-model="mustHave" />
                    </div>
                    <div style="padding-top: 10px">
                        <p>Желательные требования</p>
                        <textarea style="width: 100%; padding: 10px; border-radius: 5px; border: gray 1px solid" v-model="niceToHave" />
                    </div>
                    <button style="margin-top: 20px" v-on:click="analyseResponse"
                            class="inline-block rounded-sm border border-black bg-[#1b1b18] px-5 py-1.5 text-sm leading-normal text-white hover:border-black hover:bg-black dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                    >Проанализировать отклики</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
