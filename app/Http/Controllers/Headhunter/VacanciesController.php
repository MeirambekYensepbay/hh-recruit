<?php

namespace App\Http\Controllers\Headhunter;

use App\Http\Controllers\Controller;

class VacanciesController extends Controller {

    public function index()
    {
        $hh = HHService();
        $hh->getVacancies();

        return response()->json($hh->vacancies);
    }

}