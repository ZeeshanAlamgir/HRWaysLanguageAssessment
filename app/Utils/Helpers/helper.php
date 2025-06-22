<?php
function apiResponse($message, $status = null, $data = null)
{
    return response()->json(
        [
            'message' => $message,
            'status' => $status ?? false,
            'data' => $data ?? null,
            'status_code' => $status == true ? 200 : 404,
        ]
    );
}
