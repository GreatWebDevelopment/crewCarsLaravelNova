<?php

namespace App\Http\Controllers;

use App\Services\ApiGatewayService;
use Carbon\Carbon;

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
                $data = [
                    'detect_mode' => $document['type'],
                    's3_key' => $document['s3Key'],
                ];

                $result = [];
                $response = $this->apiGatewayService->postToApiGateway('/extract', $data);
                if ($response) {
                    $body = json_decode($response['body'], true);

                    switch ($document['type']) {
                        case 'DL':
                            $body = collect($body)->collapse()->toArray();
                            $result = [
                                'name' => $body['FIRST_NAME'] . ' ' . $body['MIDDLE_NAME'] . ' ' . $body['LAST_NAME'],
                                'number' => $body['DOCUMENT_NUMBER'],
                                'issueDate' => empty($body['DATE_OF_ISSUE']) ? null : Carbon::parse($body['DATE_OF_ISSUE'])->format('Y-m-d'),
                                'expireDate' => empty($body['EXPIRATION_DATE']) ? null : Carbon::parse($body['EXPIRATION_DATE'])->format('Y-m-d'),
                                'type' => $document['type'],
                                'data' => $body,
                            ];
                            break;
                        default:
                            $result = getDataFromDocument($body, $document['type']);
                            break;
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
