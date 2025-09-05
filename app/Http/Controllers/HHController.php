<?php

namespace App\Http\Controllers;

use App\Models\Response;
use App\Models\ResponseTemp;
use App\Services\HHClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class HHController extends \App\Http\Controllers\Controller
{

    public function index(): array
    {
        $hh = new HHClient();
        $hh->getVacancies();
        return $hh->vacancies;
    }

    public function getVacancy(int $id): \Inertia\Response
    {
        $hh = new HHClient();
        $hh->getVacancy($id);
        $vacancy = $hh->vacancy;

        $hh->getVacancyResponses($id);
        $this->buildVacancyWithUrls($hh->vacancyResponses, $id);

        $resume = ResponseTemp::query()->where('vacancy_id', $id)
            ->with('response')->get();

        return Inertia::render('Vacancy', [
            'vacancy' => $vacancy,
            'resumes' => $resume,
        ]);
    }

    public function buildVacancyWithUrls($payload, $vacancyId)
    {
        return collect(data_get($payload, 'items', []))
            ->map(function ($i) use ($vacancyId) {
                $resume = data_get($i, 'resume', []);
                $fio = data_get($resume, 'last_name').' '.data_get($resume, 'first_name').' '.data_get($resume, 'middle_name');

                ResponseTemp::query()->updateOrCreate([
                    'vacancy_id' => $vacancyId,
                    'fio' => $fio,
                ],[
                    'fio' => $fio,
                    'url'      => data_get($resume, 'download.pdf.url')
                        ?? data_get($resume, 'actions.download.pdf.url'),
                    'response_id' => data_get($i, 'id'),
                    'vacancy_id' => $vacancyId,
                ]);

                return [
                    'response_id' => data_get($i, 'id'),
                    'fio'      => $fio,
                    'url'      => data_get($resume, 'download.pdf.url')
                        ?? data_get($resume, 'actions.download.pdf.url'),
                    'vacancy_id'  => $vacancyId,
                ];
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function getAnalyse(Request $request): \Illuminate\Http\JsonResponse
    {
        $arr = $request->all();

        $response = ResponseTemp::query()->select(['id', 'response_id', 'fio', 'url', 'vacancy_id'])->where('vacancy_id', $arr['vacancy']['vacancy_id'])->without('response')->get();

        $data = [
            'vacancy' => $arr['vacancy'],
            'resumes' => $response->toArray()
        ];

        Http::baseUrl("https://alphacentras.app.n8n.cloud")
            ->connectTimeout(600)
            ->timeout(600)
            ->post(url: '/webhook/fed4a5c4-61fe-4cff-912e-6bce27efaef4', data: $data);

        return response()->json();
    }

    public function setResponses(Request $request): void
    {
        $arr = $request->only(['vacancy_id', 'response_id', 'fio', 'email', 'phone', 'comment', 'category', 'title']);
        Response::query()->create($arr);
    }
}
