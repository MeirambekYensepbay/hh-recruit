<!-- resources/js/Pages/Vacancy.vue -->
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
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

const props = defineProps<{ vacancy: Vacancy }>();
const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: props.vacancy?.name ?? 'Вакансия', href: '' },
];
const responses = ref([]);
const mustHave = ref('');
const niceToHave = ref('');
const fetch = async () => {
    try {
        const response = await axios.get(`/api/vacancy/response-urls/`+props.vacancy.id);
        responses.value = response.data.resumes;
    } catch (error) {
        console.error(error);
    }
};

onMounted(() =>{
    fetch()
});
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
            resumes: responses.value
        }
    );
        console.log(response)
    } catch (error) {
        console.error(error)
    }
}
</script>

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

                <!-- Сырой объект (для отладки) -->
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                    <div class="relative overflow-hidden rounded-xl mb-2 text-sm text-gray-500 dark:border-sidebar-border">Список резюме</div>
<!--                    <pre class="overflow-auto text-xs">{{ JSON.stringify(responses, null, 2) }}</pre>-->
                    <div class="border border-sidebar-border/70 p-4" v-for="response in responses" v-bind:key="response.id">
                        <p style="font-size: 12px; font-weight: bold; color: gray">{{response.id}}</p>
                        <p>{{response.fio}}</p>
                    </div>
<!--                    <p v-else style="color: gray; padding-top: 20px; font-size: 12px; font-weight: bold">Пусто</p>-->

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
