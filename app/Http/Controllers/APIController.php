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
            'shipTo'=> 'required',
            'shipFrom'=> 'required',
            'packages'=>'required',
            'valueAddedServices'=> 'required',
			'taxDetails' => 'required',
			'channelDetails' => 'required'
        ]);
        
		
		$reportConfig = new \App\Libraries\AmazonReport;
		$reportConfig->refresh_token = env('AMAZON_REFRESH_TOKEN','');
		$reportConfig->access_key = env('AMAZON_ACCESS_KEY');
        $reportConfig->secret_key = env('AMAZON_SECRET_KEY');
        $reportConfig->client_secret = env('AMAZON_CLIENT_SECRET');
        $reportConfig->client_id = env('AMAZON_CLIENT_ID');
        $reportConfig->region = "eu-west-1";
		
		$sp = new \App\Libraries\Sp($reportConfig);
		$result= $sp->getRates($request);


        if ($request->byRate) {
            $minRate = null;
            $minRateObject = null;
            collect($result->payload->rates)->each(function ($rate) use (&$minRate, &$minRateObject) {
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
            return response()->json([
                'data' => $minRateObject,
                'message' => 'Minimum Rate'
            ]);
        }
        if ($request->byDate) {
            $minDeliveryWindow = null;
            $minDeliveryObject = null;

            collect($result->payload->rates)->each(function ($rate) use (&$minDeliveryWindow, &$minDeliveryObject) {
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
            return response()->json([
                'data' => $minDeliveryWindow,
                'message' => 'Minimum Delivery Time',
                'minimum_time(in Hrs)' => $minDeliveryWindow
            ]);
        }
    }

    public function get_shipment(Request $request, $shipment_id)
    {

        /**
         * Uncomment the lines to prefill values 
         */
        $config = $this->populateOptions();


        $apiInstance = new ShippingApi(
            $config
        );
        try {
            $result = $apiInstance->getShipment($shipment_id);
            return response()->json([
                'data' => $result,
                'message' => 'Shipment Data'
            ]);
        } catch (\Exception $e) {
            echo 'Exception when calling ShippingApi->getShipment: ', $e->getMessage(), PHP_EOL;
        }
    }

    public function cancel_shipment(Request $request, $shipment_id)
    {
        $config = $this->populateOptions();


        $apiInstance = new ShippingApi(
            // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
            // This is optional, `GuzzleHttp\Client` will be used as default.
            // new \GuzzleHttp\Client(),
            $config
        );

        try {
            $result = $apiInstance->cancelShipment($shipment_id);
            return response()->json([
                'data' => $result,
                'message' => 'Shipment Data'
            ]);
        } catch (\Exception $e) {
            echo 'Exception when calling ShippingApi->cancelShipment: ', $e->getMessage(), PHP_EOL;
        }
    }

    public function get_tracking_info(Request $request, $tracking_id)
    {

        $config = $this->populateOptions();
        $apiInstance = new ShippingApi(
            // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
            // This is optional, `GuzzleHttp\Client` will be used as default.
            // new \GuzzleHttp\Client(),
            $config
        );
        try {
            $result = $apiInstance->getTrackingInformation($tracking_id);
            return response()->json([
                'data' => $result,
                'message' => 'Shipment Tracking Information'
            ]);
        } catch (\Exception $e) {
            echo 'Exception when calling ShippingApi->getTrackingInformation: ', $e->getMessage(), PHP_EOL;
        }
    }
    public function purchase_label(Request $request, $shipment_id)
    {
        // $config = $this->populateOptions();

        // $validate = $request->validate([
        //     'rate_id' => 'required',
        //     'label_specification' => 'required',
        // ]);

        // $apiInstance = new ShippingApi(
        //     // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
        //     // This is optional, `GuzzleHttp\Client` will be used as default.
        //     // new \GuzzleHttp\Client(),
        //     $config
        // );

        // $body = $request->all();

        // // $body = new PurchaseLabelsResult(); // \Swagger\Client\Models\PurchaseLabelsRequest | 

        // try {
        //     $result = $apiInstance->purchaseLabels($body, $shipment_id);
        //     return response()->json([
        //         'data' => $result,
        //         'message' => 'Purchase Label'
        //     ]);
        // } catch (\Exception $e) {
        //     echo 'Exception when calling ShippingApi->purchaseLabels: ', $e->getMessage(), PHP_EOL;
        // }

        /**
         * This snippet fetches data from local and parses the json to convert and store image to png
         * For use kindly comment $result assignment for json_decode below
         */
        $json = Storage::disk('public')->get('shipments.json');
        $result = json_decode($json);
        collect($result->payload->packageDocumentDetail->packageDocuments)->map(function ($doc) use ($shipment_id) {
            if ($doc->format == 'PNG') {
                $this->convertBase64ToImage($doc->contents, $shipment_id);
            }
        });
        return response()->json([
            'data' => [],
            'message' => 'Purchase Label'
        ]);
    }


    public function populateOptions()
    {
        $options = [
            'refresh_token' => env('AMAZON_REFRESH_TOKEN',''), // Aztr|...
            'client_id' => env('AMAZON_CLIENT_ID', ''), // App ID from Seller Central, amzn1.sellerapps.app.cfbfac4a-......
            'client_secret' => env('AMAZON_CLIENT_SECRET', ''), // The corresponding Client Secret
            'region' => \ClouSale\AmazonSellingPartnerAPI\SellingPartnerRegion::$EUROPE, // or NORTH_AMERICA / FAR_EAST
            'access_key' => env('AMAZON_ACCESS_KEY', ''), // Access Key of AWS IAM User, for example AKIAABCDJKEHFJDS
            'secret_key' => env('AMAZON_SECRET_KEY', ''), // Secret Key of AWS IAM User
            'endpoint' => \ClouSale\AmazonSellingPartnerAPI\SellingPartnerEndpoint::$EUROPE, // or NORTH_AMERICA / FAR_EAST
        ];
		
		$accessToken = \ClouSale\AmazonSellingPartnerAPI\SellingPartnerOAuth::getAccessTokenFromRefreshToken($options['refresh_token'],
            $options['client_id'],
            $options['client_secret']);
        
		
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
}
