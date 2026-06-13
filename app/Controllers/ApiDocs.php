<?php

namespace App\Controllers;

class ApiDocs extends BaseController
{
    public function index(): string
    {
        return view('api/docs', [
            'pageTitle' => 'Dokumentasi API — Lazismu Keuangan',
            'baseUrl'   => rtrim(base_url(), '/'),
        ]);
    }
}
