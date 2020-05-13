<?php
/**
 * Created by PhpStorm.
 * User: azmy
 * Date: 09-May-20
 * Time: 4:00 AM
 */

function apiResponse($status,$message,$data){

    $response = [
        'status' => $status,
        'message' => $message,
        'data' => $data
    ];

    return response()->json($response);

};

?>