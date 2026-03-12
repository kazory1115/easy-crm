<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserPermissionService;

class PermissionController extends Controller
{
    public function __construct(private readonly UserPermissionService $userPermissionService)
    {
    }

    public function modules()
    {
        return response()->json([
            'data' => $this->userPermissionService->accessControlOptions(),
        ]);
    }
}
