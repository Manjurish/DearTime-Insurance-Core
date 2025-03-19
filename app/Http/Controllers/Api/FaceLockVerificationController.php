<?php

namespace App\Http\Controllers\Api;

use App\CustomerVerification;
use App\CustomerVerificationDetail;
use App\Helpers;
use App\Http\Controllers\User\VerificationController;
use App\Beneficiary;
use App\Individual;
use App\SelfieMatch;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Config;
use Aws\Rekognition\RekognitionClient;


class FaceLockVerificationController extends Controller
{
    public function verify(Request $request)
    {
        try {
            $customerVerification = $request->user()->profile->verification;
            $customerVerificationDetail   = CustomerVerificationDetail::where("kyc_id", $customerVerification->id)->where(["type" => "user"])->orWhere("type", "auto")->orderBy("created_at", "desc")->first();
            $selfie_file   =   $request->file('selfie');
            $selfie_uploaded_file   =   Helpers::crateDocumentFromUploadedFile($selfie_file, $customerVerificationDetail, 'face_lock_selfie');
            $user_selfie_file = $customerVerificationDetail->documents()->where("type", "myKad")->latest()->first();

            $rekognitionClient = RekognitionClient::factory(array(
                'region'    => "ap-southeast-1",
                'version'    => 'latest',
            ));

            $compareFaceResults = $rekognitionClient->compareFaces([
                'SimilarityThreshold' => 90,
                'SourceImage' => [
                    'S3Object' => [
                        'Bucket' => config('filesystems.disks.s3.bucket'),
                        'Name'  => $selfie_uploaded_file->path
                    ],
                ],
                'TargetImage' => [
                    'S3Object' => [
                        'Bucket' => config('filesystems.disks.s3.bucket'),
                        'Name'  => $user_selfie_file->path
                    ],
                ],
            ]);

            if (
                isset($compareFaceResults['FaceMatches']) && count($compareFaceResults['FaceMatches']) > 0
                && isset($compareFaceResults['FaceMatches'][0]['Similarity'])
            ) {
                $similarityPercent = $compareFaceResults['FaceMatches'][0]['Similarity'];
                if ($similarityPercent > 90) {
                    return [
                        'status' => 'success',
                        'message' => 'Matched Successfully.',
                        'match' => true
                    ];
                }
            }

            return [
                'status' => 'success',
                'message' => 'Not a Geniune.',
                'match' => false
            ];
        } catch (\Throwable $e) {
            return ['status' => 'success', 'message' => $e->getMessage(), 'match' => false];
        }
    }
}
