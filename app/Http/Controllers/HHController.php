<?php

namespace App\Http\Controllers;

use App\Services\HHClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class HHController extends \App\Http\Controllers\Controller
{

    public function index() {
        $hh = new HHClient();
        $hh->getVacancies();
        return $hh->vacancies;
    }

    public function getVacancy(int $id)
    {
        $hh = new HHClient();
        $hh->getVacancy($id);
        $vacancy = $hh->vacancy;
        return Inertia::render('Vacancy', [
            'vacancy' => $vacancy,
        ]);
    }

    public function getResponseUrls(int $id)
    {
        $hh = new HHClient();
        $hh->getVacancyResponses(id: $id);
        return $this->buildVacancyWithUrls($hh->vacancyResponses);
    }


    public function buildVacancyWithUrls($payload)
    {
        // 3) Извлекаем PDF-ссылки из items (оба варианта путей HH поддержаны)
        $urls = collect(data_get($payload, 'items', []))
            ->map(fn ($i) =>
                data_get($i, 'resume.download.pdf.url')
                ?? data_get($i, 'resume.actions.download.pdf.url')
            )
            ->filter()
            ->unique()
            ->values()
            ->all();
        // 4) Результат в нужном формате
        $result = [
            'urls'    => $urls,
        ];

        return response()->json($result);
    }

    public function getAnalyse(Request $request)
    {
        $arr = $request->all();

        Http::baseUrl("https://alphacentras.app.n8n.cloud")
            ->connectTimeout(600)
            ->timeout(600)
            ->post(url: '/webhook/fed4a5c4-61fe-4cff-912e-6bce27efaef4', data: $arr);
        return response()->json();
    }
}
