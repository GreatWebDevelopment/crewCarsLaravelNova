<?php

namespace App\Http\Controllers;

use App\Services\ApiGatewayService;

abstract class Controller
{
    protected $apiGatewayService;

    public function __construct(ApiGatewayService $apiGatewayService)
    {
        $this->apiGatewayService = $apiGatewayService;
    }

    protected function extractDataFromDocument($documents)
    {
        foreach ($documents as $document) {
            $s3Key = str_replace(env('AWS_URL') . '/', '', $document['s3Key']);
            $data = json_encode([
                'detect_mode' => $document['mode'],
                's3_key' => $s3Key,
            ]);

            $result = [];
            $response = $this->apiGatewayService->postToApiGateway('/extract', $data);
            if ($response) {
                $result[$document['type']] = $response['body'];
            }

            return $result;
        }
    }

    protected function facialRecognize($photoUrl, $driverLicenseUrl)
    {
        $photoS3Key = str_replace(env('AWS_URL') . '/', '', $photoUrl);
        $driverLicenseKey = str_replace(env('AWS_URL') . '/', '', $driverLicenseUrl);

        $data = json_encode([
            'self_face_s3_key' => $photoS3Key,
            'dl_s3_key' => $driverLicenseKey
        ]);

        $response = $this->apiGatewayService->postToApiGateway('/recognize', $data);
        if ($response) {
            $result = json_decode($response);
            if ($result['statusCode'] == 200) {
                return true;
            }
        }

        return false;
    }
}
