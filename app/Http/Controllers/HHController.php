<?php

namespace App\Http\Controllers;

use App\Models\Response;
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
        $resumes = collect(data_get($payload, 'items', []))
            ->map(function ($i) {
                $resume = data_get($i, 'resume', []);
                $fio = data_get($resume, 'last_name').' '.data_get($resume, 'first_name').' '.data_get($resume, 'middle_name');
                return [
                    'id'       => data_get($i, 'id'),
                    'fio'      => $fio,
                    'url'      => data_get($resume, 'download.pdf.url')
                        ?? data_get($resume, 'actions.download.pdf.url'),
                ];
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
        $result = [
            'resumes'    => $resumes,
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

    public function setResponses(Request $request)
    {
        $arr = $request->only(['vacancy_id', 'fio', 'email', 'phone', 'comment', 'category', 'title']);
        Response::create($arr);
    }
}
