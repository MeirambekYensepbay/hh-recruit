<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HHClient
{
    public array $vacancies = [];
    public array $vacancy = [];
    public array $vacancyResponses = [];
    public function __construct(
        private string $baseUrl = '',
        private int    $timeout = 120,
        private string $token = ''
    ) {
        $this->baseUrl = $this->baseUrl ?: config('hh.url');
        $this->timeout = $this->timeout ?: (int) config('hh.timeout');
        $this->token = $this->token ?: config('hh.token');
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeader('Authorization', "Bearer {$this->token}")
            ->connectTimeout(10)
            ->timeout($this->timeout)
            ->asJson();
    }

    public function getVacancies(): void
    {
        $this->vacancies = $this->http()->get($this->baseUrl . '/vacancies?employer_id=791505')->json();
    }

    public function getVacancy(int $id): void
    {
        $this->vacancy = $this->http()->get($this->baseUrl . '/vacancies/' . $id . '?employer_id=791505')->json();
    }
    public function getVacancyResponses(int $id): void
    {
        $result = $this->http()->get($this->baseUrl . '/negotiations?vacancy_id='.$id.'&per_page=50')->json();
        $responses = collect($result['collections'])->firstWhere('id', 'response');

        $url = $responses['sub_collections'][0]['url'];

        $this->vacancyResponses = $this->http()->get($url.'&per_page=50')->json();
    }

}
