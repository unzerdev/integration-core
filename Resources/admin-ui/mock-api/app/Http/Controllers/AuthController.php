<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'publicKey' => 'required|string',
            'privateKey' => 'required|string',
            'environment' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        return response()->json([
            'publicKey' => $request->input('publicKey'),
            'privateKey' => $request->input('privateKey'),
            'environment' => $request->input('environment')
        ]);
    }


}
