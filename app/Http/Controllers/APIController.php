<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class APIController extends Controller
{
    //
    public function get_rates(Request $request)
    {

        $validate = $request->validate([
            'byRate' => 'required|boolean',
            'byDate' => 'required|boolean',
            'shipTo' => 'required',
            'shipFrom' => 'required',
            'packages' => 'required',
            'valueAddedServices' => 'required',
            'taxDetails' => 'required',
            'channelDetails' => 'required'
        ]);


        $reportConfig = new \App\Libraries\AmazonReport;
        $reportConfig->refresh_token = env('AMAZON_REFRESH_TOKEN', '');
        $reportConfig->access_key = env('AMAZON_ACCESS_KEY');
        $reportConfig->secret_key = env('AMAZON_SECRET_KEY');
        $reportConfig->client_secret = env('AMAZON_CLIENT_SECRET');
        $reportConfig->client_id = env('AMAZON_CLIENT_ID');
        $reportConfig->region = "eu-west-1";

        $sp = new \App\Libraries\Sp($reportConfig);
        $result = $sp->getRates($request);

        // return response()->json($result);
        $request_token = $result->payload->requestToken;

        if ($request->byRate) {
            $minRate = null;
            $minRateObject = null;
            collect($result->payload->rates)->each(function ($rate) use (&$minRate, &$minRateObject, $request_token, $sp) {
                if (is_null($minRate)) {
                    $minRate = $rate->totalCharge;
                    $minRateObject = $rate;
                }
                if ($rate->totalCharge < $minRate) {
                    $minRate = $rate->totalCharge;
                    $minRateObject = $rate;
                }
                //do nothing if both are equal
                else if ($rate->totalCharge == $minRate) {
                }
            });
            $shipment_id= $this->purchaseLabelAPI($request_token, $minRateObject, $sp);
            $minRateObject->shipment_id= $shipment_id;
            
            return response()->json([
                'data' => $minRateObject,
                'message' => 'Minimum Rate'
            ]);
        }
        if ($request->byDate) {
            $minDeliveryWindow = null;
            $minDeliveryObject = null;

            collect($result->payload->rates)->each(function ($rate) use (&$minDeliveryWindow, &$minDeliveryObject, $request_token, $sp) {
                $dates = $rate->promise->deliveryWindow;
                $end = Carbon::parse($dates->end);
                $difference = $end->diffInHours($dates->start);
                if (is_null($minDeliveryWindow)) {
                    $minDeliveryWindow = $difference;
                    $minDeliveryObject = $rate;
                }
                if ($difference < $minDeliveryWindow) {
                    $minDeliveryWindow = $difference;
                    $minDeliveryObject = $rate;
                }
                //do nothing if both are equal
                else if ($minDeliveryWindow == $difference) {
                }
            });

            $shipment_id= $this->purchaseLabelAPI($request_token, $minDeliveryObject, $sp);

            $minDeliveryObject->shipment_id= $shipment_id;
            return response()->json([
                'data' => $minDeliveryObject,
                'message' => 'Minimum Delivery Time',
                'minimum_time(in Hrs)' => $minDeliveryWindow
            ]);
        }
    }

    public function get_shipment(Request $request, $shipment_id)
    {

        $reportConfig = new \App\Libraries\AmazonReport;
        $reportConfig->refresh_token = env('AMAZON_REFRESH_TOKEN', '');
        $reportConfig->access_key = env('AMAZON_ACCESS_KEY');
        $reportConfig->secret_key = env('AMAZON_SECRET_KEY');
        $reportConfig->client_secret = env('AMAZON_CLIENT_SECRET');
        $reportConfig->client_id = env('AMAZON_CLIENT_ID');
        $reportConfig->region = "eu-west-1";

        $sp = new \App\Libraries\Sp($reportConfig);
        $result = $sp->getShipmentDocs($shipment_id, $request);

        // return response()->json([
        //     'data' => $result->payload,
        //     'message' => 'Shipment Documents',
        // ]);
        $request_token= $result->payload->shipmentId;
        collect($result->payload->packageDocumentDetail->packageDocuments)->map(function ($doc) use ($request_token) {
            if ($doc->format == 'PNG') {
                $this->convertBase64ToImage($doc->contents, $request_token);
            }
        });
        return response()->json([
            'data' => $result->payload,
            'message' => 'Shipment Documents',
        ]);
    }

    public function cancel_shipment(Request $request, $shipment_id)
    {
        $reportConfig = new \App\Libraries\AmazonReport;
        $reportConfig->refresh_token = env('AMAZON_REFRESH_TOKEN', '');
        $reportConfig->access_key = env('AMAZON_ACCESS_KEY');
        $reportConfig->secret_key = env('AMAZON_SECRET_KEY');
        $reportConfig->client_secret = env('AMAZON_CLIENT_SECRET');
        $reportConfig->client_id = env('AMAZON_CLIENT_ID');
        $reportConfig->region = "eu-west-1";

        $sp = new \App\Libraries\Sp($reportConfig);
        $result = $sp->cancelShipment($shipment_id);

        return response()->json([
            'data' => $result->payload,
            'message' => 'Shipment Cancelled',
        ]);
    }

    public function get_tracking_info(Request $request)
    {

        $reportConfig = new \App\Libraries\AmazonReport;
        $reportConfig->refresh_token = env('AMAZON_REFRESH_TOKEN', '');
        $reportConfig->access_key = env('AMAZON_ACCESS_KEY');
        $reportConfig->secret_key = env('AMAZON_SECRET_KEY');
        $reportConfig->client_secret = env('AMAZON_CLIENT_SECRET');
        $reportConfig->client_id = env('AMAZON_CLIENT_ID');
        $reportConfig->region = "eu-west-1";

        $sp = new \App\Libraries\Sp($reportConfig);
        $result = $sp->trackShipment($request);

        return response()->json([
            'data' => $result,
            'message' => 'Tracking Information',
        ]);
    }

    public function populateOptions()
    {
        $options = [
            'refresh_token' => env('AMAZON_REFRESH_TOKEN', ''), // Aztr|...
            'client_id' => env('AMAZON_CLIENT_ID', ''), // App ID from Seller Central, amzn1.sellerapps.app.cfbfac4a-......
            'client_secret' => env('AMAZON_CLIENT_SECRET', ''), // The corresponding Client Secret
            'region' => \ClouSale\AmazonSellingPartnerAPI\SellingPartnerRegion::$EUROPE, // or NORTH_AMERICA / FAR_EAST
            'access_key' => env('AMAZON_ACCESS_KEY', ''), // Access Key of AWS IAM User, for example AKIAABCDJKEHFJDS
            'secret_key' => env('AMAZON_SECRET_KEY', ''), // Secret Key of AWS IAM User
            'endpoint' => \ClouSale\AmazonSellingPartnerAPI\SellingPartnerEndpoint::$EUROPE, // or NORTH_AMERICA / FAR_EAST
        ];

        $accessToken = \ClouSale\AmazonSellingPartnerAPI\SellingPartnerOAuth::getAccessTokenFromRefreshToken(
            $options['refresh_token'],
            $options['client_id'],
            $options['client_secret']
        );


        $config = \ClouSale\AmazonSellingPartnerAPI\Configuration::getDefaultConfiguration();
        $config->setHost($options['endpoint']);
        $config->setAccessToken($accessToken);
        $config->setAccessKey($options['access_key']);
        $config->setSecretKey($options['secret_key']);
        $config->setRegion($options['region']);

        return $config;
    }

    public function convertBase64ToImage($input, $image_name)
    {
        $file_name = $image_name . '.png'; //generating unique file name;

        if ($input != "") { // storing image in storage/app/public Folder
            Storage::disk('public')->put($file_name, base64_decode($input));
        }
    }
    private function purchaseLabelAPI($request_token, $data, $sp)
    {

        $shipmentData = [
            'requestToken' => $request_token,
            'rateId' => $data->rateId,
        ];
        $shipmentData['requestedValueAddedServices'] = array();


        $shipmentData['requestedValueAddedServices'][] = [
            'id' => $data->availableValueAddedServiceGroups[0]->valueAddedServices[0]->name
        ];
        collect($data->supportedDocumentSpecifications)->each(function ($d) use (&$shipmentData) {
            if ($d->format) {
                $shipmentData['requestedDocumentSpecification'] = [
                    'format' => 'PNG',
                    'size' => $d->size,
                    'dpi' => $d->printOptions[0]->supportedDPIs[0],
                    'pageLayout' => $d->printOptions[0]->supportedPageLayouts[0],
                    'needFileJoining' => $d->printOptions[0]->supportedFileJoiningOptions[0],
                    'requestedDocumentTypes' => [
                        $d->printOptions[0]->supportedDocumentDetails[0]->name
                    ]
                ];
            }
        });

        $res = $sp->purchaseLabel($shipmentData, $request_token);

        
        collect($res->payload->packageDocumentDetails)->map(function ($doc) use ($request_token) {
            
            if ($doc->packageDocuments[0]->format == 'PNG') {
                $this->convertBase64ToImage($doc->packageDocuments[0]->contents, $request_token);
            }
        });

        return $res->payload->shipmentId;
    }
}
