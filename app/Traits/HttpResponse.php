<?php


namespace App\Traits;


use Illuminate\Http\JsonResponse;

trait HttpResponse
{
    protected function success($message = null, $code = 200) : JsonResponse
    {
        return response()->json([
            'status' => 'Success',
            'message' => $message,
        ], $code);
    }
    protected function dataSuccess ($data, $message = null, $code = 200) : JsonResponse
    {
        if(empty($data)){
            return response()->json([
                'status' => 'Success',
                'message' => "No records found"
            ], $code);
        }
        return response()->json([
            'status' => 'Success',
            'data' => $data,
            'message' => $message
        ], $code);
    }
    protected function error($message, $code) : JsonResponse
    {
        return response()->json([
            'status' => 'Error',
            'message' => $message,
        ], $code);
    }
}