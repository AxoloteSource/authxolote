<?php

namespace App\Http\Controllers\V1\User;

use App\Data\User\UserIndexData;
use App\Data\User\UserStoreData;
use App\Http\Controllers\Controller;
use App\Logics\User\UserIndexLogic;
use App\Logics\User\UserStoreLogic;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function index(UserIndexData $data, UserIndexLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    public function store(UserStoreData $data, UserStoreLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }
}
