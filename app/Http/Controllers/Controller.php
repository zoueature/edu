<?php

namespace App\Http\Controllers;

use App\Http\Constant\Errcode;
use App\Http\Constant\ErrMsg;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function responseJson($code, $data = [], $msg = ''): \Illuminate\Http\JsonResponse
    {
        if (empty($msg)) {
            $msg = ErrMsg::MSG[$code] ?? '';
        }
        $response = [
            'code' => intval($code),
            'msg' => $msg,
            'data' => $data,
        ];
        return response()->json($response);
    }

    protected function success(): \Illuminate\Http\JsonResponse
    {
        return $this->responseJson(Errcode::SUCCESS);
    }

}
