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

    protected function extractDataFromDocuments($documents)
    {
        $documents = collect($documents)
            ->map(function ($document) {
                $data = json_encode([
                    'detect_mode' => $document['type'],
                    's3_key' => $document['s3Key'],
                ]);

                $result = [];
                $response = $this->apiGatewayService->postToApiGateway('/extract', $data);
                if ($response) {
                    $body = $response['body'];

                    switch ($document['type']) {
                        case 'DL':
                            $result = [
                                'name' => $body['FIRST_NAME'] . ' ' . $body['MIDDLE_NAME'] . ' ' . $body['LAST_NAME'],
                                'number' => $body['DOCUMENT_NUMBER'],
                                'issueDate' => $body['DATE_OF_ISSUE'],
                                'expireDate' => $body['EXPIRATION_DATE'],
                                'type' => $document['type'],
                                'data' => $body,
                            ];
                            break;
                        default:
                            $result = getDataFromDocument($body, $document['type']);
                    }
                }

                return $result;
            });

        return $documents;
    }

    protected function facialRecognize($photoUrl, $driverLicenseUrl)
    {
        $data = json_encode([
            'self_face_s3_key' => $photoUrl,
            'dl_s3_key' => $driverLicenseUrl
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
