<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use ClouSale\AmazonSellingPartnerAPI\Api\ShippingApi;
use ClouSale\AmazonSellingPartnerAPI\Configuration;
use ClouSale\AmazonSellingPartnerAPI\Models\Shipping\GetRatesResult;
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
        ]);
        if ($request->byRate == $request->byDate) {
            return new ValidationException('Both cannot be same');
        }

        /**
         * Uncomment the lines to prefill values 
         */
        // $config = $this->populateOptions();
        // $config->setAccessToken('Atza|IwEBxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'); //access token of Selling Partner

        // $apiInstance = new ShippingApi(
        //     // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
        //     // This is optional, `GuzzleHttp\Client` will be used as default.
        //     // new \GuzzleHttp\Client(),
        //     $config
        // );
        // $body = new GetRatesResult(); // \Swagger\Client\Models\GetRatesRequest | 

        // try {
        //     $result = $apiInstance->getRates($body);
        //     print_r($result);
        // } catch (\Exception $e) {
        //     echo 'Exception when calling ShippingApi->getRates: ', $e->getMessage(), PHP_EOL;
        // }

        $json = Storage::disk('public')->get('rates.json');
        $values = json_decode($json);

        if ($request->byRate) {
            $minRate = null;
            $minRateObject = null;
            collect($values->payload->rates)->each(function ($rate) use (&$minRate, &$minRateObject) {
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
            
            collect($values->payload->rates)->each(function ($rate) use (&$minDeliveryWindow, &$minDeliveryObject) {
                $dates= $rate->promise->deliveryWindow;
                $end= Carbon::parse($dates->end); 
                $difference= $end->diffInHours($dates->start);
                if (is_null($minDeliveryWindow)) {
                    $minDeliveryWindow= $difference;
                    $minDeliveryObject= $rate;
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
                'minimum_time(in Hrs)'=> $minDeliveryWindow 
            ]);
        }
    }


    public function populateOptions()
    {
        $options = [
            'refresh_token' => '', // Aztr|...
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
}
