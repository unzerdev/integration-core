<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StoresController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function stores(): JsonResponse
    {
        return response()->json([
            [
                'id' => 'a2e21117-943d-4297-9321-fb2bd851b03e',
                'name' => 'Store 1',
                'isConnected' => true,
                'environment' => "live",
                'credentials' => 's-pub-123**'
            ],
            [
                'id' => 'e2bb77b0-6021-4723-913a-e0c0fe87afb1',
                'name' => 'Store 2',
                'isConnected' => false,
                'environment' => "sandbox",
                'credentials' => 's-pub-222**'
            ],
            [
                'id' => '5879920c-e523-452d-96f2-66c31a15eecf',
                'name' => 'Store 3',
                'isConnected' => true,
                'environment' => "sandbox",
                'credentials' => 's-pub-333**'
            ]
        ]);
    }


}
