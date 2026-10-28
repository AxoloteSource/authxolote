<?php

namespace App\Http\Controllers\V1\Flow;

use App\Http\Controllers\Controller;
use App\Logics\Flow\FlowDeleteLogic;
use App\Logics\Flow\FlowIndexLogic;
use App\Logics\Flow\FlowShowLogic;
use App\Logics\Flow\FlowStoreLogic;
use App\Logics\Flow\FlowUpdateLogic;
use AxoloteSource\Logics\Data\Flow\FlowByIdData;
use AxoloteSource\Logics\Data\Flow\FlowIndexData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FlowController extends Controller
{
    public function index(FlowIndexData $data, FlowIndexLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    public function store(Request $data, FlowStoreLogic $logic): JsonResponse
    {
        return $logic->run(array_merge(
            $data->all(),
            ['model' => $data->route('model'), 'id' => $data->route('id')]
        ));
    }

    public function show(FlowByIdData $data, FlowShowLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }

    public function update(Request $data, FlowUpdateLogic $logic): JsonResponse
    {
        return $logic->run(array_merge(
            $data->all(),
            ['model' => $data->route('model'), 'id' => $data->route('id')]
        ));
    }

    public function delete(FlowByIdData $data, FlowDeleteLogic $logic): JsonResponse
    {
        return $logic->run($data);
    }
}
