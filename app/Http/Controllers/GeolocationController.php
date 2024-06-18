<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeolocationController extends Controller
{
    public function getGeolocation()
    {
        $response = Http::withHeaders([ //Actulizar con datos:
            'BX-TOKEN' => '',
            'BX-USERCODE' => 0,
            'BX-CLIENT_ACCOUNT' => ''
        ])->get("https://bx-tracking.bluex.cl/bx-geo/state/all");
    }
}
