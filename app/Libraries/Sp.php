<?php
namespace App\Libraries;

use Illuminate\Http\Request;
use \ClouSale\AmazonSellingPartnerAPI;
use ClouSale\AmazonSellingPartnerAPI\ApiException;
use ClouSale\AmazonSellingPartnerAPI\Models\Shipping\GetRatesRequest;
use Illuminate\Support\Facades\Storage;

class Sp extends DatabaseLayer
{

    /**
     * API Refresh Token
     *
     * @var string
     */
    private $refresh_token = null;

    /**
     * API Client Id
     *
     * @var string
     */
    private $client_id = null;

    /**
     * API Client secret
     *
     * @var string
     */
    private $client_secret = null;

    /**
     * API Client region
     *
     * @var string
     */
    private $region = null;

    /**
     * API Client access key
     *
     * @var string
     */
    private $access_key = null;

    /**
     * API Client secret key
     *
     * @var string
     */

    private $secret_key = null;

    /**
     * API Client endpoint
     *
     * @var string
     */
    private $endpoint = null;

    /**
     * API Client acesstoken
     *
     * @var string
     */
    private $acesstoken = null;

    /**
     * API Client endpoind
     *
     * @var string
     */
    protected $reportData = null;

    /**
     * Report API Instance
     *
     * @var string
     */
    protected $apiInstance = null;

    /**
     * Sas User Id
     *
     * @var string
     */
    protected $userId = null;

    /**
     * Report Name
     *
     * @var string
     */
    protected $reportName = null;

    /**
     * Report Config
     * @var string
     */
    protected $reportConfig = null;

    /**
     * logFileName
     * @var string
     */
    private $logFileName = null;

    /**
     * marketPlace
     * @var string
     */
    private $marketPlace = null;

    /**
     * Class constructor
     *
     *
     *
     * @return void
     */
    public function __construct($reportConfig)
    {
        $this->demo = false;
        $this->reportConfig = $reportConfig;
        $this->refresh_token = $this->reportConfig->refresh_token;
        $this->client_id = $this->reportConfig->client_id;
        $this->client_secret = $this->reportConfig->client_secret;
        $this->region = $this->reportConfig->region;
        $this->access_key = $this->reportConfig->access_key;
        $this->secret_key = $this->reportConfig->secret_key;
        $this->endpoint = $this->reportConfig->endpoint;
        $this->userId = $this->reportConfig->userId;
        $this->reportName = $this->reportConfig->reportName;
        $this->merchantId = $this->reportConfig->merchantId;
        $this->marketPlace = 'Amazon';
        $this->createLogFolderIfNotExist();
		
    }

    private function createLogFolderIfNotExist()
    {
        if (!file_exists(base_path() . '/logs/' . $this->userId)) {
            mkdir(base_path() . '/logs/' . $this->userId, 0777, true);
        }
        if (!file_exists(base_path() . '/logs/' . $this->userId . '/' . $this->reportName)) {
            mkdir(base_path() . '/logs/' . $this->userId . '/' . $this->reportName, 0777, true);
        }
        $this->logFileName = base_path() . '/logs/' . $this->userId . '/' . $this->reportName . '/' . date("dmy-His", time()) . '.log';
    }

    /**
     * Generating acess Token
     */
    private function generateAcessToken()
    {
        $requestTime = gmdate("Y-m-d H:i:s");
        $requestBody = json_encode(array($this->refresh_token, $this->client_id, $this->client_secret));
        try
        {
			$this->accessToken = AmazonSellingPartnerAPI\SellingPartnerOAuth::getAccessTokenFromRefreshToken($this->refresh_token, $this->client_id, $this->client_secret);
			$responseTime = gmdate("Y-m-d H:i:s");
            $responseBody = $this->accessToken;
            $reponseStatus = '200';
			//$this->log($requestTime, $requestBody, $responseTime, $responseBody, $reponseStatus);
        } catch (\Throwable $e) {
            dd($e);
			$responseTime = gmdate("Y-m-d H:i:s");
            $responseBody = $e->getMessage();
            $reponseStatus = $e->getCode();
            //$this->log($requestTime, $requestBody, $responseTime, $responseBody, $reponseStatus);
            return null;
        }
    }

    /**
     * Configuring Report APi
     */
    private function configureReportApi()
    {
        $config = AmazonSellingPartnerAPI\Configuration::getDefaultConfiguration();
        $config->setHost($this->endpoint);
        $config->setAccessToken($this->accessToken);
        $config->setAccessKey($this->access_key);
        $config->setSecretKey($this->secret_key);
        $config->setRegion($this->region);
        $this->apiInstance = new AmazonSellingPartnerAPI\Api\ReportsApi($config);

    }

    /**
     * Configuring Report APi
     */
    private function configureFeedApi()
    {

        $config = AmazonSellingPartnerAPI\Configuration::getDefaultConfiguration();
        $config->setHost($this->endpoint);
        $config->setAccessToken($this->accessToken);
        $config->setAccessKey($this->access_key);
        $config->setSecretKey($this->secret_key);
        $config->setRegion($this->region);
        $this->apiInstance = new AmazonSellingPartnerAPI\Api\FeedsNewApi($config);

    }

    /**
     * Configuring Catalog APi
     */
    private function configureCatalogApi()
    {

        $config = AmazonSellingPartnerAPI\Configuration::getDefaultConfiguration();
        $config->setHost($this->endpoint);
        $config->setAccessToken($this->accessToken);
        $config->setAccessKey($this->access_key);
        $config->setSecretKey($this->secret_key);
        $config->setRegion($this->region);
        $this->apiInstance = new AmazonSellingPartnerAPI\Api\CatalogApi($config);

    }

    /**
     * Configuring Catalog APi
     */
    private function configureOtherApi()
    {

        $config = AmazonSellingPartnerAPI\Configuration::getDefaultConfiguration();
        $config->setHost($this->endpoint);
        $config->setAccessToken($this->accessToken);
        $config->setAccessKey($this->access_key);
        $config->setSecretKey($this->secret_key);
        $config->setRegion($this->region);
        $this->apiInstance = new AmazonSellingPartnerAPI\Api\OtherApi($config);

    }

    /**
     * Configuring Catalog APi
     */
    private function configureOrderApi()
    {

        $config = AmazonSellingPartnerAPI\Configuration::getDefaultConfiguration();
        $config->setHost($this->endpoint);
        $config->setAccessToken($this->accessToken);
        $config->setAccessKey($this->access_key);
        $config->setSecretKey($this->secret_key);
        $config->setRegion($this->region);
        $this->apiInstance = new AmazonSellingPartnerAPI\Api\OrdersApi($config);

    }
	
	private function configureShippingApi()
    {

        $config = AmazonSellingPartnerAPI\Configuration::getDefaultConfiguration();
        $config->setHost($this->endpoint);
        $config->setAccessToken($this->accessToken);
        $config->setAccessKey($this->access_key);
        $config->setSecretKey($this->secret_key);
        $config->setRegion($this->region);
        $this->apiInstance = new AmazonSellingPartnerAPI\Api\NewShippingApi($config);
        // $this->apiInstance = new AmazonSellingPartnerAPI\Api\ShippingApi($config);

    }

    /**
     * Configuring FbaInbound APi
     */
    private function configureFbaInboundApi()
    {

        $config = AmazonSellingPartnerAPI\Configuration::getDefaultConfiguration();
        $config->setHost($this->endpoint);
        $config->setAccessToken($this->accessToken);
        $config->setAccessKey($this->access_key);
        $config->setSecretKey($this->secret_key);
        $config->setRegion($this->region);
        $this->apiInstance = new AmazonSellingPartnerAPI\Api\FbaInboundApi($config);

    }

    /**
     * Configuring FbaInventory APi
     */
    private function configureFbaInventoryApi()
    {

        $config = AmazonSellingPartnerAPI\Configuration::getDefaultConfiguration();
        $config->setHost($this->endpoint);
        $config->setAccessToken($this->accessToken);
        $config->setAccessKey($this->access_key);
        $config->setSecretKey($this->secret_key);
        $config->setRegion($this->region);
        $this->apiInstance = new AmazonSellingPartnerAPI\Api\FbaInventoryApi($config);

    }

    /**
     *  Preparing Report Params
     */
    protected function prepareReportData()
    {
        if (!is_null($this->reportConfig->interval)) {
            $dateRange = Helpers::prepareDateRange($this->reportConfig->timezone, $this->reportConfig->interval);
        } else {
            $dateRange = new \stdClass;
            $dateRange->startTime = new \DateTime($this->reportConfig->reportStartDate, new \DateTimeZone($this->reportConfig->timezone));
            $dateRange->endTime = new \DateTime($this->reportConfig->reportEndDate, new \DateTimeZone($this->reportConfig->timezone));
        }
        $data = array(
            'report_type' => $this->reportConfig->reportType,
            'data_start_time' => $dateRange->startTime,
            'data_end_time' => $dateRange->endTime,
            'marketplace_ids' => $this->reportConfig->marketplaceIds,
        );
        $this->reportData = $data;
    }

    /**
     * Generating report id
     */
    private function generateReportId()
    {
        $requestTime = gmdate("Y-m-d H:i:s");
        $requestBody = json_encode($this->reportData);

        try
        {
            $reportSpec = new AmazonSellingPartnerAPI\Models\Reports\CreateReportSpecification($this->reportData);
            $result = $this
                ->apiInstance
                ->createReport($reportSpec);
            $responseTime = gmdate("Y-m-d H:i:s");
            $responseBody = ($result->getPayload());
            $reponseStatus = '200';
            $this->log($requestTime, $requestBody, $responseTime, $responseBody, $reponseStatus);
            return $result->getPayload()["report_id"];
        } catch (\Throwable $e) {
            $responseTime = gmdate("Y-m-d H:i:s");
            $responseBody = ($e->getMessage());
            $reponseStatus = $e->getCode();
            $this->log($requestTime, $requestBody, $responseTime, $responseBody, $reponseStatus);
            return null;
        }

    }

    /**
     * Generate Document id
     */
    private function generateDocumentId($reportId, $shouldWait = true)
    {
        $requestTime = gmdate("Y-m-d H:i:s");
        $requestBody = json_encode(array($reportId));
        try
        {

            $documentId = null;
            do {
                $requestTime = gmdate("Y-m-d H:i:s");
                $reportDetails = $this
                    ->apiInstance
                    ->getReport($reportId);
                $responseTime = gmdate("Y-m-d H:i:s");
                $responseBody = ($reportDetails->getPayload());
                $reponseStatus = '200';
                $this->log($requestTime, $requestBody, $responseTime, $responseBody, $reponseStatus);
                sleep($this->reportConfig->apiDelay);
                $reportStatus = $reportDetails->getPayload()["processing_status"] ?? null;
                if ($reportStatus == "DONE") {
                    $documentId = $reportDetails->getPayload()["report_document_id"] ?? null;
                }
            } while (in_array($reportStatus, array('IN_PROGRESS', 'IN_QUEUE')) && $shouldWait);
            if (!in_array($reportStatus, array('IN_PROGRESS', 'IN_QUEUE'))) {
                $this->deleteReportQueueIfExist($reportId, $this->reportConfig->userMarketPlaceId);
            }
            return $documentId;
        } catch (\Throwable $e) {
            $this->saveReportQueue($reportId, $this->reportConfig->userMarketPlaceId, $this->reportConfig->reportName, $this->reportConfig->modelName);
            $responseTime = gmdate("Y-m-d H:i:s");
            $responseBody = ($e->getMessage());
            $reponseStatus = $e->getCode();
            $this->log($requestTime, $requestBody, $responseTime, $responseBody, $reponseStatus);
            return null;
        }

    }

    /**
     * Loging Api responses
     */
    private function log($requestTime, $requestBody, $responseTime, $responseBody, $reponseStatus)
    {
        file_put_contents($this->logFileName, $responseBody . "\n\n", FILE_APPEND);
        $data = [
            'tenant_id' => $this->userId,
            'market_place_id' => json_encode($this->reportConfig->marketplaceIds),
            'market_place' => $this->marketPlace,
            'report-type' => $this->reportName,
            'request-time' => $requestTime,
            'request-body' => $requestBody,
            'response-time' => $responseTime,
            'response-file' => $this->logFileName,
            'response-status' => $reponseStatus,
        ];
        $this->logModel = new \App\Models\Logs($data);
        $this->logModel->save();
    }

    private function detectDelimiter($firstLine)
    {
        $delimiters = [";" => 0, "," => 0, "\t" => 0, "|" => 0];
        foreach ($delimiters as $delimiter => &$count) {
            $count = count(str_getcsv($firstLine, $delimiter));
        }
        return array_search(max($delimiters), $delimiters);
    }
    /**
     * Decrypting and saving file
     */
    private function decryptFileAndSaveData($url, $iv, $key, $skipSave, $disableEncryption, $compression)
    {
        if (!$disableEncryption) {
            $key = base64_decode($key);
            $iv = base64_decode($iv);
        }
        $decryptedData = $disableEncryption ? Helpers::downloadAndGetContent($url) : Helpers::decryptData($key, $iv, Helpers::downloadAndGetContent($url));
        if ($compression) {
            $data = gzdecode($decryptedData);
            $path = storage_path('temp/');
            $bytes = random_bytes(20);
            $fileName = $path . bin2hex($bytes);
            mkdir($fileName);
            $myfile = fopen($fileName . ".zip", "w");
            fwrite($myfile, $data);
            $zip = new \ZipArchive;
            if ($zip->open($fileName . ".zip") === true) {
                $zip->extractTo($fileName . '/');
                $zip->close();
                $csv = file_get_contents($fileName . '/' . scandir($fileName . '/')[2]);
                $decryptedData = $csv;
            } else {
                echo 'failed';
            }
        }
        $rows = explode("\n", $decryptedData);
        $seperator = $this->detectDelimiter($rows[0]);
        $header = str_getcsv(array_shift($rows), $seperator);
        $header = array_map(function ($h) {return str_replace('.', '', $h);}, $header);
        $csv = array();

        foreach ($rows as $row) {
            $c = str_getcsv($row, $seperator);
            if (count($header) == count($c)) {
                $rowData = array_combine($header, $c);
                $rowData["tenant_id"] = $this->userId;
                $rowData["marketplace"] = $this->marketPlace;
                $rowData["market_place_id"] = json_encode($this->reportConfig->marketplaceIds);
                $rowData["report_type"] = $this->reportConfig->reportType;
                $csv[] = $rowData;
            }
        }
        print_r("Saving start..." . count($csv));
        return $skipSave ? $csv : $this->saveData($csv);
    }

    /**
     * Logic for generating Report
     */
    public function generateReport($skipModelSave = false, $disableEncryption = true)
    {
        $this->generateAcessToken();
        if ($this->demo) {
            $this->accessToken = "true";
        }
        if (isset($this->accessToken)) {
            $this->configureReportApi();
            $this->prepareReportData();
            $reportId = $this->generateReportId();
            if (isset($reportId)) {
                try
                {
                    $documentId = $this->generateDocumentId($reportId);
                    if (isset($documentId)) {
                        $requestTime = gmdate("Y-m-d H:i:s");
                        $requestBody = json_encode($documentId);
                        try {
                            $result = $this
                                ->apiInstance
                                ->getReportDocument($documentId);
                            $responseTime = gmdate("Y-m-d H:i:s");
                            $responseBody = ($result->getPayload());
                            $reponseStatus = '200';
                            $this->log($requestTime, $requestBody, $responseTime, $responseBody, $reponseStatus);
                        } catch (\Throwable $e) {
                            $responseTime = gmdate("Y-m-d H:i:s");
                            $responseBody = ($e->getMessage());
                            $reponseStatus = $e->getCode();
                            $this->log($requestTime, $requestBody, $responseTime, $responseBody, $reponseStatus);
                            return null;

                        }
                        $url = $result->getPayload()["url"];
                        $iv = $disableEncryption ? '' : $result->getPayload()["encryption_details"]["initialization_vector"];
                        $key = $disableEncryption ? '' : $result->getPayload()["encryption_details"]["key"];
                        $compression = $result->getPayload()["compression_algorithm"] == 'GZIP';
                        return $this->decryptFileAndSaveData($url, $iv, $key, $skipModelSave, $disableEncryption, $compression);
                    }
                } catch (Throwable $e) {
                    return null;
                }
            }
        }

    }

    /**
     * Logic for generating pending Report
     */
    public function regeneratePendingReport($reportId, $skipModelSave = false, $disableEncryption = true)
    {
        $this->generateAcessToken();
        if ($this->demo) {
            $this->accessToken = "true";
        }
        if (isset($this->accessToken)) {
            $this->configureReportApi();
            if (isset($reportId)) {
                try
                {
                    $documentId = $this->generateDocumentId($reportId, false);
                    if (isset($documentId)) {
                        $requestTime = gmdate("Y-m-d H:i:s");
                        $requestBody = json_encode($documentId);
                        try {
                            $result = $this
                                ->apiInstance
                                ->getReportDocument($documentId);
                            $responseTime = gmdate("Y-m-d H:i:s");
                            $responseBody = ($result->getPayload());
                            $reponseStatus = '200';
                            $this->log($requestTime, $requestBody, $responseTime, $responseBody, $reponseStatus);
                        } catch (\Throwable $e) {
                            $responseTime = gmdate("Y-m-d H:i:s");
                            $responseBody = ($e->getMessage());
                            $reponseStatus = $e->getCode();
                            $this->log($requestTime, $requestBody, $responseTime, $responseBody, $reponseStatus);
                            return null;

                        }
                        $url = $result->getPayload()["url"];
                        $iv = $disableEncryption ? '' : $result->getPayload()["encryption_details"]["initialization_vector"];
                        $key = $disableEncryption ? '' : $result->getPayload()["encryption_details"]["key"];
                        $compression = $result->getPayload()["compression_algorithm"] == 'GZIP';
                        return $this->decryptFileAndSaveData($url, $iv, $key, $skipModelSave, $disableEncryption, $compression);
                    }
                } catch (Throwable $e) {
                    return null;
                }
            }
        }

    }

    // Update invetory
    // Array of products
    // $type -> price,inventory

    public function updateInventory($chunks, $type)
    {

        $this->generateAcessToken();
        if (isset($this->accessToken)) {
            $this->configureFeedApi();
            // 2. Creating Feed Document
            $feedCreate = $this->createFeedDocument();
            if ($feedCreate) {
                $feedDocumentId = $feedCreate["feedDocumentId"];
                $feedUploadUrl = $feedCreate["url"];
                $feedXml = $type == 'price' ? $this->preparePriceXml($chunks) : $this->prepareQuantityXml($chunks);
                //3. Upload XML
                $isUploaded = $this->uploadXML($feedUploadUrl, $feedXml);
                if ($isUploaded) {
                    //4. Create Feed
                    $amzType = $type == 'price' ? 'POST_PRODUCT_PRICING_DATA' : 'POST_INVENTORY_AVAILABILITY_DATA';
                    $feed = $this->createFeed($amzType, $feedDocumentId);
                    if ($feed) {
                        $feedId = $feed['feedId'];
                        //5 Get Feed Doument
                        $feedStatusDocumentId = $this->getFeedStatusDocument($feedId);
                        if ($feedStatusDocumentId) {
                            $feedResponseDoc = $this->generateDownloadUrl($feedStatusDocumentId);
                            $requestTime = gmdate("Y-m-d H:i:s");
                            $xmlResponse = file_get_contents($feedResponseDoc['url']);
                            $responseTime = gmdate("Y-m-d H:i:s");
                            $this->log($requestTime, $feedResponseDoc['url'], $responseTime, $xmlResponse, '200');
                            return $this->prepareSucessAndError($chunks, $xmlResponse);
                        }
                    }
                }

            }
        }

    }

    // get Catalog
    // Array of products
    // $type -> price,inventory

    public function getCatalog($marketplaceId, $asin)
    {

        $this->generateAcessToken();
        if ($this->demo) {
            $this->accessToken = true;
        }
        if (isset($this->accessToken)) {
            $this->configureOtherApi();
            $request = json_encode(array('market' => $marketplaceId, 'asin' => $asin));
            $response = '';
            $requestTime = gmdate("Y-m-d H:i:s");
            $reponseStatus = 200;
            try {
                if ($this->demo) {
                    $result = '{"asin":"B08B1SL726","identifiers":[{"marketplaceId":"A21TJRUUN4KGV","identifiers":[]}],"images":[{"marketplaceId":"A21TJRUUN4KGV","images":[{"variant":"MAIN","link":"https:\/\/m.media-amazon.com\/images\/I\/71X7NrPzfIL.jpg","height":2000,"width":1741},{"variant":"MAIN","link":"https:\/\/m.media-amazon.com\/images\/I\/41sDeP6otLL.jpg","height":500,"width":435},{"variant":"PT01","link":"https:\/\/m.media-amazon.com\/images\/I\/71l9LpXXfEL.jpg","height":1200,"width":1200},{"variant":"PT01","link":"https:\/\/m.media-amazon.com\/images\/I\/51aAn27C9WL.jpg","height":500,"width":500},{"variant":"PT02","link":"https:\/\/m.media-amazon.com\/images\/I\/61LV0oDmB8L.jpg","height":1080,"width":1080},{"variant":"PT02","link":"https:\/\/m.media-amazon.com\/images\/I\/51qhrgFMALL.jpg","height":500,"width":500},{"variant":"PT03","link":"https:\/\/m.media-amazon.com\/images\/I\/61BB2kmGk8L.jpg","height":1080,"width":1080},{"variant":"PT03","link":"https:\/\/m.media-amazon.com\/images\/I\/41CJQqO7r9L.jpg","height":500,"width":500},{"variant":"PT04","link":"https:\/\/m.media-amazon.com\/images\/I\/617Q62MMBTL.jpg","height":1080,"width":1080},{"variant":"PT04","link":"https:\/\/m.media-amazon.com\/images\/I\/41xz3omEY5L.jpg","height":500,"width":500}]}],"productTypes":[{"marketplaceId":"A21TJRUUN4KGV","productType":"SOFTWARE"}],"ranks":[{"marketplaceId":"A21TJRUUN4KGV","ranks":[{"title":"Software","link":"http:\/\/www.amazon.in\/gp\/bestsellers\/software","value":20},{"title":"Antivirus & Security Software","link":"http:\/\/www.amazon.in\/gp\/bestsellers\/software\/5490081031","value":20}]}],"salesRanks":[{"marketplaceId":"A21TJRUUN4KGV","ranks":[{"title":"Software","link":"http:\/\/www.amazon.in\/gp\/bestsellers\/software","value":20},{"title":"Antivirus & Security Software","link":"http:\/\/www.amazon.in\/gp\/bestsellers\/software\/5490081031","value":20}]}],"summaries":[{"marketplaceId":"A21TJRUUN4KGV","brandName":"Kaspersky","browseNode":"5490103031","itemName":"Kaspersky Anti-Virus Security Latest Version - 1 User, 1 Year (Code emailed in 2 Hours - No CD)","manufacturer":"Kaspersky","styleName":"2020 Version (1 Year + 3 months Free)"}],"variations":[]}';
                    $result = json_decode($result, true);
                } else {
                    $result = $this->apiInstance->getCatalogItem($marketplaceId, $asin);
					$result = json_decode($result[0], true);
                }
                $responseTime = gmdate("Y-m-d H:i:s");
				return $result;
            } catch (\Exception $e) {
                $response = $e->getMessage();
                $responseTime = gmdate("Y-m-d H:i:s");
                $reponseStatus = $e->getCode();
            } finally {
                $this->log($requestTime, $request, $responseTime, $response, $reponseStatus);
            }
        }
    }

    // get Catalog
    // Array of products
    // $type -> price,inventory

    public function getListingsItem($sku,$marketPlace)
    {
        $this->generateAcessToken();
        $sellerId = "A2TIVGCAB08IHM";//$this->reportConfig->merchantId;
        if ($this->demo) {
            $this->accessToken = true;
        }
        if (isset($this->accessToken)) {
            $this->configureOtherApi();
            $request = json_encode(array('seller' => $sellerId, 'asin' => $sku));
            $response = '';
            $requestTime = gmdate("Y-m-d H:i:s");
            $reponseStatus = 200;
            try {
                if ($this->demo) {
                    $result = '{"sku":"KAV_1U_1Y_2_vn","summaries":[{"marketplaceId":"A21TJRUUN4KGV","asin":"B08B1SL726","productType":"SOFTWARE","conditionType":"new_new","status":["BUYABLE","DISCOVERABLE"],"itemName":"Kaspersky Anti-Virus Security Latest Version - 1 User, 1 Year (Code emailed in 2 Hours - No CD)","createdDate":"2021-06-19T13:13:32.859Z","lastUpdatedDate":"2021-07-20T23:31:17.957Z","mainImage":{"link":"https:\/\/m.media-amazon.com\/images\/I\/41sDeP6otLL.jpg","height":500,"width":435}}],"attributes":{"purchasable_offer":[{"currency":"INR","start_at":{"value":"2021-06-19T13:13:27.028Z"},"end_at":{"value":null},"list_price":[{"schedule":[{"value_with_tax":999}]}],"our_price":[{"schedule":[{"value_with_tax":325}]}],"marketplace_id":"A21TJRUUN4KGV"}],"fulfillment_availability":[{"fulfillment_channel_code":"DEFAULT","quantity":1925,"marketplace_id":"A21TJRUUN4KGV"}],"condition_type":[{"value":"new_new","marketplace_id":"A21TJRUUN4KGV"}],"list_price":[{"currency":"INR","value_with_tax":"999.00","marketplace_id":"A21TJRUUN4KGV"}],"merchant_shipping_group":[{"value":"7d0223a5-cb40-4c2c-8696-2fc212f8f1f5","marketplace_id":"A21TJRUUN4KGV"}],"merchant_suggested_asin":[{"value":"B08B1SL726","marketplace_id":"A21TJRUUN4KGV"}],"country_of_origin":[{"value":"IN","marketplace_id":"A21TJRUUN4KGV"}],"external_product_information":[{"entity":"HSN Code","value":"998713","marketplace_id":"A21TJRUUN4KGV"}]},"issues":[],"offers":[{"marketplaceId":"A21TJRUUN4KGV","offerType":"B2C","price":{"currency":"INR","amount":"325.00"}}],"fulfillmentAvailability":[{"fulfillmentChannelCode":"DEFAULT","quantity":1899}]}';
                    $result = json_decode($result, true);
                } else {
                    $result = $this->apiInstance->getItemListing($sellerId, $sku, $marketPlace);
					$result = json_decode($result[0], true);
				}
                $responseTime = gmdate("Y-m-d H:i:s");
                return $result;
            } catch (\Exception $e) {
                $response = $e->getMessage();
                $responseTime = gmdate("Y-m-d H:i:s");
                $reponseStatus = $e->getCode();
            } finally {
                $this->log($requestTime, $request, $responseTime, $response, $reponseStatus);
            }
        }

    }

    // get Catalog
    // Array of products
    // $type -> price,inventory

    public function searchContentPublishRecords($marketPlaceId, $asin)
    {

        $this->generateAcessToken();
        if ($this->demo) {
            $this->accessToken = true;
        }
        if (isset($this->accessToken)) {
            $this->configureOtherApi();
            $request = json_encode(array('market' => $marketPlaceId, 'asin' => $asin));
            $response = '';
            $requestTime = gmdate("Y-m-d H:i:s");
            $reponseStatus = 200;
            try {
                if ($this->demo) {
                    $result = '{"warnings":[],"nextPageToken":null,"publishRecordList":[{"marketplaceId":"A21TJRUUN4KGV","locale":"en_IN","asin":"B08B1SL726","contentType":"EBC","contentSubType":"","contentReferenceKey":"d986f85e-c8a7-4b22-9f36-a6c14f1df44c"}]}';
                    $result = json_decode($result, true);
                } else {
                    $result = $this->apiInstance->searchContentPublishRecords($marketplaceId, $asin);
                    $result = $result->getPayload();
                }
                $responseTime = gmdate("Y-m-d H:i:s");
                return $result;
            } catch (\Exception $e) {
                $response = $e->getMessage();
                $responseTime = gmdate("Y-m-d H:i:s");
                $reponseStatus = $e->getCode();
            } finally {
                $this->log($requestTime, $request, $responseTime, $response, $reponseStatus);
            }
        }

    }
    public function prepareAplus($marketPlaceId)
    {

        $this->generateAcessToken();
        if ($this->demo) {
            $this->accessToken = true;
        }
        if (isset($this->accessToken)) {
            $this->configureOtherApi();
            $request = json_encode(array('market' => $marketPlaceId));
            $response = '';
            $requestTime = gmdate("Y-m-d H:i:s");
            $reponseStatus = 200;
            try {
                if ($this->demo) {
                    $result = '{"warnings":[],"nextPageToken":null,"contentMetadataRecords":[{"contentReferenceKey":"44ed3bfa-1cd4-41f7-8d27-12e71c3a9e92","contentMetadata":{"name":"Kaspersky Total Security","marketplaceId":"A21TJRUUN4KGV","status":"APPROVED","badgeSet":["STANDARD"],"updateTime":"2021-11-12T07:06:26.678Z"}},{"contentReferenceKey":"adcc919f-2366-4651-a274-b336c1cc9a2e","contentMetadata":{"name":"Kaspersky Internet Security","marketplaceId":"A21TJRUUN4KGV","status":"DRAFT","badgeSet":["STANDARD"],"updateTime":"2021-11-11T07:51:30.695Z"}},{"contentReferenceKey":"d986f85e-c8a7-4b22-9f36-a6c14f1df44c","contentMetadata":{"name":"Kaspersky Anti- Virus","marketplaceId":"A21TJRUUN4KGV","status":"APPROVED","badgeSet":["STANDARD"],"updateTime":"2021-11-10T07:02:14.051Z"}},{"contentReferenceKey":"26d4f864-8b01-4d9b-b1ea-460cc1eaa1f2","contentMetadata":{"name":"MAV V2","marketplaceId":"A21TJRUUN4KGV","status":"APPROVED","badgeSet":["STANDARD"],"updateTime":"2021-07-22T07:37:36.268Z"}},{"contentReferenceKey":"ab3ec07a-7353-49fe-b55b-db11e0252f7d","contentMetadata":{"name":"MIS V2","marketplaceId":"A21TJRUUN4KGV","status":"APPROVED","badgeSet":["STANDARD"],"updateTime":"2021-07-22T07:07:51.631Z"}},{"contentReferenceKey":"6e26d979-9c17-4664-a7e9-56e881611759","contentMetadata":{"name":"MTP V3","marketplaceId":"A21TJRUUN4KGV","status":"APPROVED","badgeSet":["STANDARD"],"updateTime":"2021-07-22T06:52:23.61Z"}},{"contentReferenceKey":"760cb04d-c077-4371-8a86-4aa5e75275b3","contentMetadata":{"name":"McAfee Internet Security V1","marketplaceId":"A21TJRUUN4KGV","status":"DRAFT","badgeSet":["STANDARD"],"updateTime":"2021-07-15T16:58:44.072Z"}},{"contentReferenceKey":"a0fb65c7-f1a0-4562-884e-65c68d4bc781","contentMetadata":{"name":"McAfee Antivirus V1","marketplaceId":"A21TJRUUN4KGV","status":"DRAFT","badgeSet":["STANDARD"],"updateTime":"2021-07-15T16:31:13.447Z"}},{"contentReferenceKey":"7745c1a3-8457-4a26-af22-46abfdfadcac","contentMetadata":{"name":"McAfee Total Protection_v2","marketplaceId":"A21TJRUUN4KGV","status":"APPROVED","badgeSet":["STANDARD"],"updateTime":"2021-07-15T15:17:45.327Z"}},{"contentReferenceKey":"09e8ee95-4ff3-4d94-a2bd-5ed64a21b58a","contentMetadata":{"name":"McAfee Total Protection_v1","marketplaceId":"A21TJRUUN4KGV","status":"DRAFT","badgeSet":["STANDARD"],"updateTime":"2021-06-29T07:19:30.16Z"}}]}';
                    $result = json_decode($result, true);
                    $response = json_encode($result);
                } else {
                    $result = $this->apiInstance->searchContentDocuments($marketplaceId);
                    $result = $result->getPayload();
                    $response = json_encode($result);
                }
                $responseTime = gmdate("Y-m-d H:i:s");

                $this->log($requestTime, $request, $responseTime, $response, $reponseStatus);
                if (isset($result['contentMetadataRecords'])) {
                    foreach ($result['contentMetadataRecords'] as $content) {
                        if ($content['contentMetadata']['status'] == 'APPROVED') {
                            $contentReferenceKey = $content['contentReferenceKey'];

                            $request = json_encode(array('market' => $marketPlaceId, 'contentReferenceKey' => $contentReferenceKey));
                            $response = '';
                            $requestTime = gmdate("Y-m-d H:i:s");
                            $reponseStatus = 200;
                            $contentDocument = '';
                            if ($this->demo) {

                                $contentDocument = '{"warnings":[],"contentRecord":{"contentReferenceKey":"44ed3bfa-1cd4-41f7-8d27-12e71c3a9e92","contentMetadata":{"name":"Kaspersky Total Security","marketplaceId":"A21TJRUUN4KGV","status":"APPROVED","badgeSet":["STANDARD"],"updateTime":"2021-11-12T07:06:26.678Z"},"contentDocument":{"name":"Kaspersky Total Security","contentType":"EBC","contentSubType":null,"locale":"en-IN","contentModuleList":[{"contentModuleType":"STANDARD_IMAGE_TEXT_OVERLAY","standardCompanyLogo":null,"standardComparisonTable":null,"standardFourImageText":null,"standardFourImageTextQuadrant":null,"standardHeaderImageText":null,"standardImageSidebar":null,"standardImageTextOverlay":{"overlayColorType":"DARK","block":{"image":{"uploadDestinationId":"aplus-media-library-service-media\/2766f8b3-5e0d-4d09-998f-ae81545a6a9a.jpg","imageCropSpecification":{"size":{"width":{"value":970,"units":"pixels"},"height":{"value":300,"units":"pixels"}},"offset":{"x":{"value":0,"units":"pixels"},"y":{"value":0,"units":"pixels"}}},"altText":"kaspersky, anti virus, cyber security, data corrupt, safety , total security"},"headline":null,"body":{"textList":[{"value":"","decoratorSet":[]}]}}},"standardMultipleImageText":null,"standardProductDescription":null,"standardSingleImageHighlights":null,"standardSingleImageSpecsDetail":null,"standardSingleSideImage":null,"standardTechSpecs":null,"standardText":null,"standardThreeImageText":null},{"contentModuleType":"STANDARD_TEXT","standardCompanyLogo":null,"standardComparisonTable":null,"standardFourImageText":null,"standardFourImageTextQuadrant":null,"standardHeaderImageText":null,"standardImageSidebar":null,"standardImageTextOverlay":null,"standardMultipleImageText":null,"standardProductDescription":null,"standardSingleImageHighlights":null,"standardSingleImageSpecsDetail":null,"standardSingleSideImage":null,"standardTechSpecs":null,"standardText":{"headline":null,"body":{"textList":[{"value":"Kaspersky Total Security gives you a smarter way to protect your family\u2019s digital world\u2014on your PC, Mac and mobile devices. Along with award-winning protection for your privacy, money, communications and identity, it includes an easy-to-use password manager and extra security for your family\u2019s precious photos, music and files. You also get powerful tools that do more to help you to keep your children safe\u2014online and beyond.","decoratorSet":[{"type":"STYLE_BOLD","offset":0,"length":24,"depth":0}]}]}},"standardThreeImageText":null},{"contentModuleType":"STANDARD_FOUR_IMAGE_TEXT","standardCompanyLogo":null,"standardComparisonTable":null,"standardFourImageText":{"headline":{"value":"Product Benefits","decoratorSet":[]},"block1":{"image":{"uploadDestinationId":"aplus-media-library-service-media\/d5c3af92-145f-4ff1-8813-d5f1d222213c.jpg","imageCropSpecification":{"size":{"width":{"value":220,"units":"pixels"},"height":{"value":220,"units":"pixels"}},"offset":{"x":{"value":0,"units":"pixels"},"y":{"value":0,"units":"pixels"}}},"altText":"Kaspersky Total Security"},"headline":null,"body":null},"block2":{"image":{"uploadDestinationId":"aplus-media-library-service-media\/c0c3ab65-b8b5-4378-9fce-2425f69de7e4.jpg","imageCropSpecification":{"size":{"width":{"value":220,"units":"pixels"},"height":{"value":220,"units":"pixels"}},"offset":{"x":{"value":0,"units":"pixels"},"y":{"value":0,"units":"pixels"}}},"altText":"Kaspersky Total Security"},"headline":null,"body":null},"block3":{"image":{"uploadDestinationId":"aplus-media-library-service-media\/8a0d474f-8de1-4f52-b306-1469ffd84b72.jpg","imageCropSpecification":{"size":{"width":{"value":220,"units":"pixels"},"height":{"value":220,"units":"pixels"}},"offset":{"x":{"value":0,"units":"pixels"},"y":{"value":0,"units":"pixels"}}},"altText":"security"},"headline":null,"body":null},"block4":{"image":{"uploadDestinationId":"aplus-media-library-service-media\/158ed2ce-42c3-4e01-a527-57548c4b91c0.jpg","imageCropSpecification":{"size":{"width":{"value":220,"units":"pixels"},"height":{"value":220,"units":"pixels"}},"offset":{"x":{"value":0,"units":"pixels"},"y":{"value":0,"units":"pixels"}}},"altText":"kaspersky total security"},"headline":null,"body":null}},"standardFourImageTextQuadrant":null,"standardHeaderImageText":null,"standardImageSidebar":null,"standardImageTextOverlay":null,"standardMultipleImageText":null,"standardProductDescription":null,"standardSingleImageHighlights":null,"standardSingleImageSpecsDetail":null,"standardSingleSideImage":null,"standardTechSpecs":null,"standardText":null,"standardThreeImageText":null},{"contentModuleType":"STANDARD_FOUR_IMAGE_TEXT","standardCompanyLogo":null,"standardComparisonTable":null,"standardFourImageText":{"headline":null,"block1":{"image":{"uploadDestinationId":"aplus-media-library-service-media\/35aae4b4-d46b-4abd-88f2-ebbc6f8e8aca.jpg","imageCropSpecification":{"size":{"width":{"value":220,"units":"pixels"},"height":{"value":220,"units":"pixels"}},"offset":{"x":{"value":0,"units":"pixels"},"y":{"value":0,"units":"pixels"}}},"altText":"Kaspersky"},"headline":null,"body":null},"block2":{"image":{"uploadDestinationId":"aplus-media-library-service-media\/c843f652-466f-465e-bd92-43cca4000583.jpg","imageCropSpecification":{"size":{"width":{"value":220,"units":"pixels"},"height":{"value":220,"units":"pixels"}},"offset":{"x":{"value":0,"units":"pixels"},"y":{"value":0,"units":"pixels"}}},"altText":"kaspersky"},"headline":null,"body":null},"block3":{"image":{"uploadDestinationId":"aplus-media-library-service-media\/635b26b8-e602-441f-8565-72c157ce9876.jpg","imageCropSpecification":{"size":{"width":{"value":220,"units":"pixels"},"height":{"value":220,"units":"pixels"}},"offset":{"x":{"value":0,"units":"pixels"},"y":{"value":0,"units":"pixels"}}},"altText":"kaspersky "},"headline":null,"body":null},"block4":{"image":{"uploadDestinationId":"aplus-media-library-service-media\/4404cbe7-5f68-4305-9822-caffe47932c8.jpg","imageCropSpecification":{"size":{"width":{"value":220,"units":"pixels"},"height":{"value":220,"units":"pixels"}},"offset":{"x":{"value":0,"units":"pixels"},"y":{"value":0,"units":"pixels"}}},"altText":"kaspersky total security"},"headline":null,"body":null}},"standardFourImageTextQuadrant":null,"standardHeaderImageText":null,"standardImageSidebar":null,"standardImageTextOverlay":null,"standardMultipleImageText":null,"standardProductDescription":null,"standardSingleImageHighlights":null,"standardSingleImageSpecsDetail":null,"standardSingleSideImage":null,"standardTechSpecs":null,"standardText":null,"standardThreeImageText":null},{"contentModuleType":"STANDARD_COMPARISON_TABLE","standardCompanyLogo":null,"standardComparisonTable":{"productColumns":[{"position":1,"image":{"uploadDestinationId":"aplus-media-library-service-media\/043c019d-fe08-44a4-a9b1-bb6bde3b4b6c.jpg","imageCropSpecification":{"size":{"width":{"value":150,"units":"pixels"},"height":{"value":300,"units":"pixels"}},"offset":{"x":{"value":0,"units":"pixels"},"y":{"value":0,"units":"pixels"}}},"altText":"kaspersky security cloud"},"title":"Kaspersky Ultimate Security","asin":"B08B43W7QJ","highlight":false,"metrics":[{"position":1,"value":"Windows, Mac, Android"},{"position":2,"value":"\u2714"},{"position":3,"value":"\u2714"},{"position":4,"value":"\u2714"},{"position":5,"value":"\u2714"},{"position":6,"value":"\u2714"},{"position":7,"value":"\u2714"},{"position":8,"value":"\u2714"},{"position":9,"value":"\u2714"},{"position":10,"value":"\u2714"}]},{"position":2,"image":{"uploadDestinationId":"aplus-media-library-service-media\/9db06a82-d254-42d6-b0ca-7238e31ec421.jpg","imageCropSpecification":{"size":{"width":{"value":150,"units":"pixels"},"height":{"value":300,"units":"pixels"}},"offset":{"x":{"value":0,"units":"pixels"},"y":{"value":0,"units":"pixels"}}},"altText":"kaspersky, antivirus, total security, "},"title":"Kaspersky Internet Security","asin":"B073VKWTZG","highlight":false,"metrics":[{"position":1,"value":"Windows, Mac, Android"},{"position":2,"value":"\u2714"},{"position":3,"value":"\u2714"},{"position":4,"value":"\u2714"},{"position":5,"value":"\u2714"},{"position":6,"value":"\u2714"},{"position":7,"value":""},{"position":8,"value":""},{"position":9,"value":""},{"position":10,"value":""}]},{"position":3,"image":{"uploadDestinationId":"aplus-media-library-service-media\/4be3252b-01df-4f02-bcaa-be1cf4244fab.jpg","imageCropSpecification":{"size":{"width":{"value":150,"units":"pixels"},"height":{"value":300,"units":"pixels"}},"offset":{"x":{"value":0,"units":"pixels"},"y":{"value":0,"units":"pixels"}}},"altText":"kasperky, antivirus, total security"},"title":"Kaspersky Total Security","asin":"B08B1SD2HZ","highlight":true,"metrics":[{"position":1,"value":"Windows, Mac, Android"},{"position":2,"value":"\u2714"},{"position":3,"value":"\u2714"},{"position":4,"value":"\u2714"},{"position":5,"value":"\u2714"},{"position":6,"value":"\u2714"},{"position":7,"value":"\u2714"},{"position":8,"value":"\u2714"},{"position":9,"value":"\u2714"},{"position":10,"value":""}]},{"position":4,"image":{"uploadDestinationId":"aplus-media-library-service-media\/ed8be75d-572b-423c-a173-0222933e4a43.jpg","imageCropSpecification":{"size":{"width":{"value":150,"units":"pixels"},"height":{"value":300,"units":"pixels"}},"offset":{"x":{"value":0,"units":"pixels"},"y":{"value":0,"units":"pixels"}}},"altText":"kaspersky, antivirus, total security, cyber threat, data"},"title":"Kaspersky Anti- Virus","asin":"B073VL1XM3","highlight":false,"metrics":[{"position":1,"value":"Windows"},{"position":2,"value":"\u2714"},{"position":3,"value":"\u2714"},{"position":4,"value":"\u2714"},{"position":5,"value":""},{"position":6,"value":""},{"position":7,"value":""},{"position":8,"value":""},{"position":9,"value":""},{"position":10,"value":""}]},{"position":5,"image":{"uploadDestinationId":null,"imageCropSpecification":null,"altText":""},"title":"","asin":"","highlight":false,"metrics":[{"position":1,"value":""},{"position":2,"value":""},{"position":3,"value":""},{"position":4,"value":""},{"position":5,"value":""},{"position":6,"value":""},{"position":7,"value":""},{"position":8,"value":""},{"position":9,"value":""},{"position":10,"value":""}]},{"position":6,"image":{"uploadDestinationId":null,"imageCropSpecification":null,"altText":""},"title":"","asin":"","highlight":false,"metrics":[{"position":1,"value":""},{"position":2,"value":""},{"position":3,"value":""},{"position":4,"value":""},{"position":5,"value":""},{"position":6,"value":""},{"position":7,"value":""},{"position":8,"value":""},{"position":9,"value":""},{"position":10,"value":""}]}],"metricRowLabels":[{"position":1,"value":"Platforms Supported"},{"position":2,"value":"Real-Time Anti-Virus"},{"position":3,"value":"Anti-Phishing"},{"position":4,"value":"Performance Optimization"},{"position":5,"value":"Payment Protection"},{"position":6,"value":"Smart & fast VPN"},{"position":7,"value":"GPS Child Locator"},{"position":8,"value":"File Protection"},{"position":9,"value":"Password Manager"},{"position":10,"value":"Home Wifi Monitoring"}]},"standardFourImageText":null,"standardFourImageTextQuadrant":null,"standardHeaderImageText":null,"standardImageSidebar":null,"standardImageTextOverlay":null,"standardMultipleImageText":null,"standardProductDescription":null,"standardSingleImageHighlights":null,"standardSingleImageSpecsDetail":null,"standardSingleSideImage":null,"standardTechSpecs":null,"standardText":null,"standardThreeImageText":null}]}}}';
                                $contentDocument = json_decode($contentDocument, true);

                                $response = json_encode($contentDocument);
                            } else {
                                $contentDocument = $this->apiInstance->getContentDocument($marketplaceId, $contentReferenceKey);
                                $contentDocumentAsin = $this->apiInstance->listContentDocumentAsinRelations($marketplaceId, $contentReferenceKey);
                                $contentDocument = $contentDocument->getPayload();
                                $response = json_encode($contentDocument);
                            }
                            $responseTime = gmdate("Y-m-d H:i:s");
                            $this->log($requestTime, $request, $responseTime, $response, $reponseStatus);

                            $request = json_encode(array('market' => $marketPlaceId, 'contentReferenceKey' => $contentReferenceKey));
                            $response = '';
                            $requestTime = gmdate("Y-m-d H:i:s");
                            $reponseStatus = 200;
                            $contentDocumentAsin = '';
                            if ($this->demo) {

                                $contentDocumentAsin = '{"warnings":[],"nextPageToken":null,"asinMetadataSet":[{"asin":"B08B1SD2HZ","badgeSet":null,"parent":null,"title":null,"imageUrl":null,"contentReferenceKeySet":null},{"asin":"B08B43W7QJ","badgeSet":null,"parent":null,"title":null,"imageUrl":null,"contentReferenceKeySet":null},{"asin":"B08XZWPQ6B","badgeSet":null,"parent":null,"title":null,"imageUrl":null,"contentReferenceKeySet":null}]}';
                                $contentDocumentAsin = json_decode($contentDocumentAsin, true);
                                $response = json_encode($contentDocumentAsin);
                            } else {
                                $contentDocumentAsin = $this->apiInstance->listContentDocumentAsinRelations($marketplaceId, $contentReferenceKey);
                                $contentDocumentAsin = $contentDocumentAsin->getPayload();
                                $response = json_encode($contentDocumentAsin);
                            }
                            $responseTime = gmdate("Y-m-d H:i:s");
                            $this->log($requestTime, $request, $responseTime, $response, $reponseStatus);

                        }
                    }
                }

                return $result;
            } catch (\Exception $e) {
                $response = $e->getMessage();
                $responseTime = gmdate("Y-m-d H:i:s");
                $reponseStatus = $e->getCode();
            } finally {
                $this->log($requestTime, $request, $responseTime, $response, $reponseStatus);
            }
        }
    }

    public function productSave($catalogResult, $listingResult)
    {
        $asin = isset($catalogResult['asin']) ? $catalogResult['asin'] : '';
        $catalog = array();
		$catalog['asin'] = $asin;
        $catalog["tenant_id"] = $this->userId;
        $catalog["marketplace"] = $this->marketPlace;
        $catalog["sku"] = isset($listingResult["sku"]) ? $listingResult["sku"] : '';
        $s = isset($listingResult['summaries']) ? $listingResult['summaries'][0] : [];
        $catalog["productType"] = isset($s['conditionType']) ? $s['conditionType'] : '';
        $catalog["conditionType"] = isset($s['conditionType']) ? $s['conditionType'] : '';
        $catalog["status"] = isset($s['status']) ? json_encode($s['status']) : '[]';
        
        $catalog["createdDate"] = isset($s['createdDate']) ? $s['createdDate'] : '';
        $catalog["lastUpdatedDate"] = isset($s['lastUpdatedDate']) ? $s['lastUpdatedDate'] : '';
        $catalog["mainImageLink"] = isset($s['mainImage']) ? $s['mainImage']['link'] : '';
        $catalog["mainImageHeight"] = isset($s['mainImage']) ? $s['mainImage']['height'] : '';
        $catalog["mainImageWidth"] = isset($s['mainImage']) ? $s['mainImage']['width'] : '';
        $catalog["category"] = isset($catalogResult['productTypes']) ? json_encode($catalogResult['productTypes']) : '[]';

        $s = isset($catalogResult['summaries']) ? $catalogResult['summaries'][0] : [];
		$catalog["itemName"] = isset($s['itemName']) ? $s['itemName'] : '';
        $catalog["brand"] = isset($s['brandName']) ? $s['brandName'] : '';
        $catalog["browseNode"] = isset($s['browseNode']) ? $s['browseNode'] : '';
        $catalog["styleName"] = isset($s['styleName']) ? $s['styleName'] : '';
        $productId = $this->saveProducts($catalog);
        $productImages = array();
		if(isset($catalogResult['images'][0]))
		{
			foreach ($catalogResult['images'][0]['images'] as $image) 
			{
				$image['asin'] = $asin;
				$image["tenant_id"] = $this->userId;
				$image["marketplace"] = $this->marketPlace;
				$image["product_id"] = $productId;
				array_push($productImages, $image);
			}
		}
		
        $this->saveProductImages($productImages, $this->userId, $asin);
    }

	public function getRates(Request $request){
		$this->generateAcessToken();
        $this->configureShippingApi();
		$body = json_encode($request->all()); 
		// $body = new AmazonSellingPartnerAPI\Models\Shipping\GetRatesRequest($request->all());
        
        try{
            $result= $this->apiInstance->getRates($body);
            return $result;
            // dd($this->apiInstance->getRates($body));
        }
        catch(\Exception $e){
            dd($e);
        }
	}
    public function purchaseLabel($data, $shipmentId){
		$this->generateAcessToken();
        $this->configureShippingApi();
		$body = json_encode($data, JSON_PRETTY_PRINT); 
        
        // echo $body;
        // die;
		// $body = new AmazonSellingPartnerAPI\Models\Shipping\GetRatesRequest($request->all());
        
        try{
            $result= $this->apiInstance->purchaseLabels($body, $shipmentId);
            return $result;
            // dd($this->apiInstance->getRates($body));
        }
        catch(\Exception $e){
            dd($e);
        }
	}
    

    public function fetchOrderAndSaveByDate($marketPlaceIds, $createdAfter, $createdBefore = null)
    {

        $this->generateAcessToken();
        if ($this->demo) {
            $this->accessToken = "true";
        }
        if (isset($this->accessToken)) {
            $this->configureOrderApi();
            $nextToken = null;
            do {
                $response = $this->getOrders($marketPlaceIds, $createdAfter, $createdBefore, $nextToken);
                $orders = $response["orders"];
                $nextToken = $response["next_token"];
                foreach ($orders as $order) {
                    $order = $order->__toString();
                    $order = json_decode($order, true);
                    $order['orderId'] = $order['AmazonOrderId'];
                    $order['currency'] = isset($order['OrderTotal']) ? $order['OrderTotal']['CurrencyCode'] : '';
                    $order['amount'] = isset($order['OrderTotal']) ? $order['OrderTotal']['Amount'] : 0;
                    $order['orderItemsSynced'] = 0;
                    $order['addressSynced'] = 0;
                    $order['buyerSynced'] = 0;
                    $order = $this->prepareForSave($order);
                    $this->saveOrUpdateOrder($order);
                }
            } while (!is_null($nextToken));
        }

    }

    public function syncShipmentAndSaveByDate($marketPlaceIds, $createdAfter, $createdBefore = null)
    {
        $this->generateAcessToken();
        if ($this->demo) {
            $this->accessToken = "true";
        }
        if (isset($this->accessToken)) {
            $this->configureFbaInboundApi();
            $nextToken = null;
            $response = $this->getShipmentItems($marketPlaceIds, $createdAfter, $createdBefore, $nextToken);
            $shipmentItems = $response["item_data"];
            $nextToken = $response["next_token"];
            $shipmentItemArray = array();
            foreach ($shipmentItems as $shipmentItem) {
                $shipmentItem = $shipmentItem->__toString();
                $shipmentItem = json_decode($shipmentItem, true);
                $shipmentItem = $this->prepareForSave($shipmentItem);
                $shipmentItemArray[$shipmentItem["ShipmentId"]] = $shipmentItem;
            }

            $nextTokenShipment = null;
            $chunkShipmentItems = array_chunk(array_keys($shipmentItemArray), 20);
            $counter = 0;
            $shipmentDetailsArray = array();
            foreach ($chunkShipmentItems as $chunk) {
                $responseShipment = $this->getShipments($marketPlaceIds, $chunk, $nextTokenShipment);
                $shipments = $responseShipment["shipment_data"];

                $nextTokenShipment = $responseShipment["next_token"];

                foreach ($shipments as $shipment) {
                    $shipment = $shipment->__toString();
                    $shipment = json_decode($shipment, true);
                    $shipment['ShipFromAddressName'] = isset($shipment['ShipFromAddress']['Name']) ? $shipment['ShipFromAddress']['Name'] : '';
                    $shipment['ShipFromAddressAddressLine1'] = isset($shipment['ShipFromAddress']['AddressLine1']) ? $shipment['ShipFromAddress']['AddressLine1'] : '';
                    $shipment['ShipFromAddressCity'] = isset($shipment['ShipFromAddress']['City']) ? $shipment['ShipFromAddress']['City'] : '';
                    $shipment['ShipFromAddressStateOrProvinceCode'] = isset($shipment['ShipFromAddress']['StateOrProvinceCode']) ? $shipment['ShipFromAddress']['StateOrProvinceCode'] : '';
                    $shipment['ShipFromAddressCountryCode'] = isset($shipment['ShipFromAddress']['CountryCode']) ? $shipment['ShipFromAddress']['CountryCode'] : '';
                    $shipment['ShipFromAddressPostalCode'] = isset($shipment['ShipFromAddress']['PostalCode']) ? $shipment['ShipFromAddress']['PostalCode'] : '';
                    $shipment = $this->prepareForSave($shipment);
                    $shipmentDetailsArray[$shipment["ShipmentId"]] = $shipment;
                }

                sleep($this->reportConfig->apiDelay);
            }

            foreach ($shipmentItems as $shipment) {
                $shipment = $shipment->__toString();
                $shipment = json_decode($shipment, true);
                $shipment = $this->prepareForSave($shipment);
                $shipment = array_merge($shipment, $shipmentDetailsArray[$shipment["ShipmentId"]]);
                $this->saveOrUpdateShipment($shipment);
            }
        }
    }

    public function getInventorySummariesAndSave($marketPlaceId, $startDateTime)
    {

        $this->generateAcessToken();
        if ($this->demo) {
            $this->accessToken = "true";
        }
        if (isset($this->accessToken)) {
            $this->configureOtherApi();
            $nextToken = null;
            do {
                $response = $this->getInventorySummaries($marketPlaceId, $startDateTime, $nextToken);
                if (isset($response['pagination']['nextToken'])) {
                    $nextToken = $response['pagination']['nextToken'];
                }
                $inventorySummaries = $response['payload']['inventorySummaries'];
                foreach ($inventorySummaries as $inventory) {
                    $inventory['fulfillableQuantity'] = $inventory['inventoryDetails']['fulfillableQuantity'];
                    $inventory['inboundWorkingQuantity'] = $inventory['inventoryDetails']['inboundWorkingQuantity'];
                    $inventory['inboundShippedQuantity'] = $inventory['inventoryDetails']['inboundShippedQuantity'];
                    $inventory['inboundReceivingQuantity'] = $inventory['inventoryDetails']['inboundReceivingQuantity'];
                    $inventory['reservedQuantity'] = $inventory['inventoryDetails']['reservedQuantity'];
                    $inventory['totalReservedQuantity'] = $inventory['inventoryDetails']['reservedQuantity']['totalReservedQuantity'];
                    $inventory['pendingCustomerOrderQuantity'] = $inventory['inventoryDetails']['reservedQuantity']['pendingCustomerOrderQuantity'];
                    $inventory['pendingTransshipmentQuantity'] = $inventory['inventoryDetails']['reservedQuantity']['pendingTransshipmentQuantity'];
                    $inventory['fcProcessingQuantity'] = $inventory['inventoryDetails']['reservedQuantity']['fcProcessingQuantity'];
                    $inventory['totalUnfulfillableQuantity'] = $inventory['inventoryDetails']['unfulfillableQuantity']['totalUnfulfillableQuantity'];
                    $inventory['customerDamagedQuantity'] = $inventory['inventoryDetails']['unfulfillableQuantity']['customerDamagedQuantity'];
                    $inventory['warehouseDamagedQuantity'] = $inventory['inventoryDetails']['unfulfillableQuantity']['warehouseDamagedQuantity'];
                    $inventory['distributorDamagedQuantity'] = $inventory['inventoryDetails']['unfulfillableQuantity']['distributorDamagedQuantity'];
                    $inventory['carrierDamagedQuantity'] = $inventory['inventoryDetails']['unfulfillableQuantity']['carrierDamagedQuantity'];
                    $inventory['defectiveQuantity'] = $inventory['inventoryDetails']['unfulfillableQuantity']['defectiveQuantity'];
                    $inventory['expiredQuantity'] = $inventory['inventoryDetails']['unfulfillableQuantity']['expiredQuantity'];
                    $inventory = $this->prepareForSave($inventory);
                    $this->saveOrUpdateFbaInventory($inventory);
                }
                sleep($this->reportConfig->apiDelay);
            } while (!is_null($nextToken));
        }

    }

    private function getInventorySummaries($marketPlaceId, $startDateTime, $nextToken)
    {

        $request = $marketPlaceId;
        $response = '';
        $requestTime = gmdate("Y-m-d H:i:s");
        $reponseStatus = 200;
        try {
            $result = $this->apiInstance->getInventorySummaries($marketPlaceId, $startDateTime, $nextToken);
            $responseTime = gmdate("Y-m-d H:i:s");
            $response = json_encode($result);
            return json_decode($result[0], true);
        } catch (Exception $e) {
            $response = $e->getMessage();
            $responseTime = gmdate("Y-m-d H:i:s");
            $reponseStatus = $e->getCode();
        } finally {
            $responseTime = gmdate("Y-m-d H:i:s");
            $this->log($requestTime, $request, $responseTime, $response, $reponseStatus);
        }
    }

    private function getShipments($marketPlaceIds, $shipmentIds, $nextToken)
    {

        $request = json_encode($shipmentIds);
        $response = '';
        $requestTime = gmdate("Y-m-d H:i:s");
        $reponseStatus = 200;
        try {
            $result = $this->apiInstance->getShipments('SHIPMENT', $marketPlaceIds, null, $shipmentIds, null, null, $nextToken);
            $responseTime = gmdate("Y-m-d H:i:s");
            $response = $result->getPayload();
            return $result->getPayload();
        } catch (Exception $e) {
            $response = $e->getMessage();
            $responseTime = gmdate("Y-m-d H:i:s");
            $reponseStatus = $e->getCode();
        } finally {
            $responseTime = gmdate("Y-m-d H:i:s");
            $this->log($requestTime, $request, $responseTime, $response, $reponseStatus);
        }
    }

    private function getShipmentItems($marketPlaceIds, $createdAfter, $createdBefore, $nextToken)
    {

        $request = $createdAfter;
        $response = '';
        $requestTime = gmdate("Y-m-d H:i:s");
        $reponseStatus = 200;
        try {
            $result = $this->apiInstance->getShipmentItems('DATE_RANGE', $marketPlaceIds, $createdAfter, $createdBefore, $nextToken);
            $responseTime = gmdate("Y-m-d H:i:s");
            $response = $result->getPayload();
            return $result->getPayload();
        } catch (Exception $e) {
            $response = $e->getMessage();
            $responseTime = gmdate("Y-m-d H:i:s");
            $reponseStatus = $e->getCode();
        } finally {
            $responseTime = gmdate("Y-m-d H:i:s");
            $this->log($requestTime, $request, $responseTime, $response, $reponseStatus);
        }
    }

    public function fetchOrderItemAndSave()
    {
        $this->generateAcessToken();
        if ($this->demo) {
            $this->accessToken = "true";
        }
        if (isset($this->accessToken)) {
            $this->configureOrderApi();
            $orders = array();
            do {
                $orders = $this->fetchPendingOrderItems();
                foreach ($orders as $order) {
                    $orderItemsResponse = $this->getOrderItems($order['orderId']);
                    $orderItems = array();
                    foreach ($orderItemsResponse as $item) {
                        $item = json_decode($item->__toString(), true);
                        $item['orderId'] = $order['orderId'];
                        $item = $this->prepareForSave($item);
                        array_push($orderItems, $item);
                    }
					
					$this->saveOrderItems($order, $orderItems);
                    echo "\nSaving order item:" . $order['orderId'];
                    sleep(2);
                }

            } while (count($orders) != 0);

        }

    }

    public function fetchOrderBuyerAndSave()
    {
        $this->generateAcessToken();
        if ($this->demo) {
            $this->accessToken = "true";
        }
        if (isset($this->accessToken)) {
            $this->configureOrderApi();
            $orders = array();
            do {
                $orders = $this->fetchPendingOrderBuyer();
                foreach ($orders as $order) {
                    $orderBuyer = $this->getOrderBuyerInfo($order['orderId']);
                    $order['buyerSynced'] = 1;
                    $order['buyer'] = $orderBuyer->__toString();
                    $this->saveOrUpdateOrder($order);
                    echo "\nSaving buyer info for order id:" . $order['orderId'];
                    sleep(7);
                }

            } while (count($orders) != 0);

        }

    }

    public function fetchOrderAddressAndSave()
    {

        $this->generateAcessToken();
        if ($this->demo) {
            $this->accessToken = "true";
        }
        if (isset($this->accessToken)) {
            $this->configureOrderApi();
            $orders = array();
            do {
                $orders = $this->fetchPendingOrderAddress();
                foreach ($orders as $order) {
                    $orderAddress = $this->getOrderAddress($order['orderId']);
                    $order['addressSynced'] = 1;
                    $order['address'] = $orderAddress->__toString();
                    $this->saveOrUpdateOrder($order);
                    echo "\nSaving address for order id:" . $order['orderId'];
                    sleep(7);
                }

            } while (count($orders) != 0);

        }

    }

    public function fetchFinancialEventsAndSave()
    {

        $this->generateAcessToken();
        if ($this->demo) {
            $this->accessToken = "true";
        }
        if (isset($this->accessToken)) {
            $this->configureOtherApi();
            $orders = array();
            do {
                $orders = $this->fetchPendingFinancialEvents();
                foreach ($orders as $order) {
                    $response = $this->getOrderFinancialEvents($order['orderId']);
                    $orderFinancialData = $response['payload']['FinancialEvents'];
                    foreach ($orderFinancialData as $event => $eventDetails) {
                        foreach ($eventDetails as $eventDetail) {
							if(!isset($eventDetail["AmazonOrderId"])){
								print_r($eventDetail);	
								continue;
							}
							
							$data["order_id"] = $eventDetail["AmazonOrderId"];
                            
							
							$data["posteddate"] = isset($eventDetail["PostedDate"]) ? $eventDetail["PostedDate"] : null;
                            $listName = \str_replace('EventList', '', $event) . 'ItemList';
                            if (isset($eventDetail[$listName])) {
                                foreach ($eventDetail[$listName] as $e) {
                                    $data["sellersku"] = $e["SellerSKU"];
                                    $data["order_item_id"] = $e["OrderItemId"];
                                    $data["quantity"] = $e["QuantityShipped"];
                                    $data["tenant_id"] = $this->userId;
                                    $data["marketplace"] = $this->marketPlace;
                                    $f = $this->saveFinancialDetails($data);
                                    // Save Finace

                                    foreach ($e as $e1Key => $e1) {
                                        if (is_array($e1)) {
                                            foreach ($e1 as $x) {
                                                $type = '';
                                                $amountFeild = '';
                                                foreach ($x as $xK => $xV) {
                                                    if (strpos($xK, 'Type')) {
                                                        $type = $xV;
                                                    }
                                                    if (strpos($xK, 'Amount')) {
                                                        $amountFeild = $xK;
                                                    }
                                                }
                                                $subData['category'] = $listName . "." . $e1Key;
                                                $subData['type'] = $type;
                                                $subData['amount'] = $x[$amountFeild]['CurrencyAmount'];
                                                $subData['currency'] = $x[$amountFeild]['CurrencyCode'];
                                                $subData['finance_id'] = $f->id;

                                                if ($subData['amount'] != 0) {
                                                    $this->saveOrderCharge($subData);
                                                }

                                            }
                                        }
                                    }

                                }
                            }
                        }}
                    $order['financialEventsSynced'] = 1;
                    $this->saveOrUpdateOrder($order);
                    echo "\nSaving order financial item:" . $order['orderId'];
					sleep(1);
                }

                
            } while (count($orders) != 0);

        }

    }

    private function prepareForSave($item)
    {
        foreach ($item as $k => $i) {
            if (is_array($item[$k])) {
                $item[$k] = json_encode($item[$k]);
            }
        }
        $item["tenant_id"] = $this->userId;
        $item["marketplace"] = $this->marketPlace;
        return $item;
    }

    private function getOrderAddress($orderId)
    {
        $request = $orderId;
        $response = '';
        $requestTime = gmdate("Y-m-d H:i:s");
        $reponseStatus = 200;
        try {
            $result = $this->apiInstance->getOrderAddress($orderId);
            $responseTime = gmdate("Y-m-d H:i:s");
            $response = $result->getPayload();
            return $result->getPayload();
        } catch (Exception $e) {
            $response = $e->getMessage();
            $responseTime = gmdate("Y-m-d H:i:s");
            $reponseStatus = $e->getCode()['order_items'];
        } finally {
            $this->log($requestTime, $request, $responseTime, $response, $reponseStatus);
        }
    }

    private function getOrderFinancialEvents($orderId)
    {
        $request = $orderId;
        $response = '';
        $requestTime = gmdate("Y-m-d H:i:s");
        $reponseStatus = 200;
        try {
            $result = $this->apiInstance->getOrderFinancialEvents($orderId);
            $responseTime = gmdate("Y-m-d H:i:s");
            $response = json_encode($result);
            return json_decode($result[0], true);
        } catch (Exception $e) {
            $response = $e->getMessage();
            $responseTime = gmdate("Y-m-d H:i:s");
            $reponseStatus = $e->getCode()['order_items'];
        } finally {
            $this->log($requestTime, $request, $responseTime, $response, $reponseStatus);
        }
    }

    private function getOrderBuyerInfo($orderId)
    {
        $request = $orderId;
        $response = '';
        $requestTime = gmdate("Y-m-d H:i:s");
        $reponseStatus = 200;
        try {
            $result = $this->apiInstance->getOrderBuyerInfo($orderId);
            $responseTime = gmdate("Y-m-d H:i:s");
            $response = $result->getPayload();
            return $result->getPayload();
        } catch (Exception $e) {
            $response = $e->getMessage();
            $responseTime = gmdate("Y-m-d H:i:s");
            $reponseStatus = $e->getCode()['order_items'];
        } finally {
            $this->log($requestTime, $request, $responseTime, $response, $reponseStatus);
        }
    }

    private function getOrderItems($orderId)
    {
        $request = $orderId;
        $response = '';
        $requestTime = gmdate("Y-m-d H:i:s");
        $reponseStatus = 200;
        try {
            $result = $this->apiInstance->getOrderItems($orderId);
            $responseTime = gmdate("Y-m-d H:i:s");
            $response = $result->getPayload();
            return $result->getPayload()['order_items'];
        } catch (Exception $e) {
            $response = $e->getMessage();
            $responseTime = gmdate("Y-m-d H:i:s");
            $reponseStatus = $e->getCode();
        } finally {
            $this->log($requestTime, $request, $responseTime, $response, $reponseStatus);
        }
    }

    private function getOrders($marketPlaceIds, $createdAfter, $createdBefore, $nextToken)
    {

        $request = $createdAfter;
        $response = '';
        $requestTime = gmdate("Y-m-d H:i:s");
        $reponseStatus = 200;
        try {
            $result = $this->apiInstance->getOrders($marketPlaceIds, null, null, $createdAfter, $createdBefore, null, null, null, null, null, null, null, $nextToken, null);
            //$result = $this->apiInstance->getOrders($marketPlaceIds, $createdAfter, $createdBefore, null, null, null, null, null, null, null, null, null, $nextToken, null);
            $response = $result->getPayload();
            return $result->getPayload();
        } catch (Exception $e) {
            $response = $e->getMessage();
            $reponseStatus = $e->getCode();
        } finally {
            $responseTime = gmdate("Y-m-d H:i:s");
            $this->log($requestTime, $request, $responseTime, $response, $reponseStatus);
        }
    }

    private function generateDownloadUrl($feedStatusDocumentId)
    {
        $request = $feedStatusDocumentId;
        $response = '';
        $requestTime = gmdate("Y-m-d H:i:s");
        $reponseStatus = 200;
        try {
            if (!$this->demo) {
                $feedResponseDoc = $this->apiInstance->getFeedDocument($feedStatusDocumentId);
            } else {
                $feedResponseDoc = '{"feedDocumentId":"amzn1.tortuga.3.10e4fb7a-cdba-4e37-a518-4a00d5b3e759.T2VX1GKCTF0H67","url":"http://localhost/price/failure.xml"}';
            }
            $responseTime = gmdate("Y-m-d H:i:s");
            $response = $feedResponseDoc;

            $feedResponseDoc = json_decode($feedResponseDoc, true);
            return $feedResponseDoc;
        } catch (\Exception $e) {
            $response = $e->getMessage();
            $responseTime = gmdate("Y-m-d H:i:s");
            $reponseStatus = $e->getCode();
        } finally {
            $this->log($requestTime, $request, $responseTime, $response, $reponseStatus);
        }
    }

    private function getFeedStatusDocument($feedId)
    {
        $request = '';
        $response = '';
        $requestTime = gmdate("Y-m-d H:i:s");
        $reponseStatus = 200;
        try {
            $documentId = null;
            do {
                $requestTime = gmdate("Y-m-d H:i:s");
                if (!$this->demo) {
                    $request = $feedId;
                    $feedStatus = $this->apiInstance->getFeed($feedId);
                } else {
                    $feedStatus = '{"processingEndTime":"2021-11-06T14:54:27+00:00","processingStatus":"DONE","marketplaceIds":["A21TJRUUN4KGV"],"feedId":"346657018937","feedType":"POST_PRODUCT_PRICING_DATA","createdTime":"2021-11-06T14:53:31+00:00","processingStartTime":"2021-11-06T14:53:38+00:00","resultFeedDocumentId":"amzn1.tortuga.3.b5f11e68-7536-48b7-b757-cbf8182d9a5d.T3QSCJZBOGP6V6"}
            ';}
                $responseTime = gmdate("Y-m-d H:i:s");
                $response = $feedStatus;
                $feedStatus = json_decode($feedStatus, true);
                sleep($this->reportConfig->apiDelay);
                $status = $feedStatus["processingStatus"] ?? null;
                if ($status == "DONE") {
                    $documentId = $feedStatus["resultFeedDocumentId"] ?? null;
                }
                $this->log($requestTime, $request, $responseTime, $response, $reponseStatus);
            } while (in_array($status, array('IN_PROGRESS', 'IN_QUEUE')));
            return $documentId;
        } catch (\Exception $e) {
            $responseTime = gmdate("Y-m-d H:i:s");
            $response = $e->getMessage();
            $reponseStatus = $e->getCode();
            $this->log($requestTime, $request, $responseTime, $response, $reponseStatus);
            return false;
        }
    }

    private function createFeed($feedType, $feedDocumentId)
    {
        $request = '';
        $response = '';
        $requestTime = gmdate("Y-m-d H:i:s");
        $reponseStatus = 200;
        $body = new AmazonSellingPartnerAPI\Models\Feeds\CreateFeedSpecification(array("feed_type" => $feedType,
            "marketplace_ids" => $this->reportConfig->marketplaceIds,
            "input_feed_document_id" => $feedDocumentId));
        $request = $body;
        try {
            if (!$this->demo) {
                $feed = $this->apiInstance->createFeed($body);
            } else {
                $feed = '{"feedId":"346657018937"}';
            }
            $responseTime = gmdate("Y-m-d H:i:s");
            $response = $feed;
            $feed = json_decode($feed, true);
            return $feed;
        } catch (Exception $e) {
            $responseTime = gmdate("Y-m-d H:i:s");
            $response = $e->getMessage();
            $reponseStatus = $e->getCode();
        } finally {
            $this->log($requestTime, $request, $responseTime, $response, $reponseStatus);
        }

    }

    private function createFeedDocument()
    {
        $request = '';
        $response = '';
        $requestTime = gmdate("Y-m-d H:i:s");
        $reponseStatus = 200;
        $body = new AmazonSellingPartnerAPI\Models\Feeds\CreateFeedDocumentSpecification(array('content_type' => 'application/xml')); // \Swagger\Client\Models\CreateFeedDocumentSpecification |
        $request = $body;
        try {
            if (!$this->demo) {
                $feedCreate = $this->apiInstance->createFeedDocument($body);
            } else {
                $feedCreate = '{"feedDocumentId":"amzn1.tortuga.3.c566f106-3a16-4711-8587-ab9dc4ec8989.TBNZQ7NLC3WOI",
            "url":"https://tortuga-prod-eu.s3-eu-west-1.amazonaws.com/%2FNinetyDays/amzn1.tortuga.3.c566f106-3a16-4711-8587-ab9dc4ec8989.TBNZQ7NLC3WOI?X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Date=20211106T142730Z&X-Amz-SignedHeaders=content-type%3Bhost&X-Amz-Expires=300&X-Amz-Credential=AKIAX2ZVOZFBEBASACT4%2F20211106%2Feu-west-1%2Fs3%2Faws4_request&X-Amz-Signature=8ecc5823ec49d081e2338c5877b6fcc41e49b66c0ac7b08a2a741840783944a6"}         ';
            }
            $responseTime = gmdate("Y-m-d H:i:s");
            $response = $feedCreate;
            $feedCreate = json_decode($feedCreate, true);
            return $feedCreate;
        } catch (Exception $e) {
            $responseTime = gmdate("Y-m-d H:i:s");
            $reponseStatus = $e->getCode();
            $response = $e->getMessage();
            return false;
        } finally {
            $this->log($requestTime, $request, $responseTime, $response, $reponseStatus);
        }
    }

    private function uploadXML($feedUploadUrl, $feedXml)
    {
        $request = '';
        $response = '';
        $requestTime = gmdate("Y-m-d H:i:s");
        $httpcode = 200;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $feedUploadUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $feedXml,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/xml',
            ),
        ));
        $response = curl_exec($curl);
        $responseTime = gmdate("Y-m-d H:i:s");
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $this->log($requestTime, $feedXml, $responseTime, $response, $httpcode);
        return $this->demo ? true : $httpcode == 200;

    }

    private function preparePriceXml($products)
    {
        $dom = new \DOMDocument();
        $dom->loadXML('<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
    <Header>
    <DocumentVersion>1.01</DocumentVersion>
    <MerchantIdentifier>DYNAMIC VALUE</MerchantIdentifier>
    </Header>
    <MessageType>Price</MessageType>
    </AmazonEnvelope>');
        $i = 1;
        $dom->getElementsByTagName("MerchantIdentifier")->item(0)->nodeValue = $this->merchantId;
        foreach ($products as $product) {
            if (intval($product['1']) > 0) {
                $messageElement = $dom->createElement('Message');
                $messageIdElement = $dom->createElement('MessageID', $i);
                $operationTypeElement = $dom->createElement('OperationType', 'Update');
                $priceElement = $dom->createElement('Price');
                $skuElement = $dom->createElement('SKU', $product['0']);
                $stdPriceElement = $dom->createElement('StandardPrice', $product['1']);
                $stdPriceElement->setAttribute('currency', $product['2']);
                $priceElement->appendChild($skuElement);
                $priceElement->appendChild($stdPriceElement);
                $messageElement->appendChild($messageIdElement);
                $messageElement->appendChild($operationTypeElement);
                $messageElement->appendChild($priceElement);
                $dom->getElementsByTagName("AmazonEnvelope")[0]->appendChild($messageElement);
            }
            $i++;
        }
        return $dom->saveXML();
    }

    private function prepareQuantityXml($products)
    {
        $dom = new \DOMDocument();
        $dom->loadXML('<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
    <Header>
    <DocumentVersion>1.01</DocumentVersion>
    <MerchantIdentifier>DYNAMIC VALUE</MerchantIdentifier>
    </Header>
    <MessageType>Inventory</MessageType>
    </AmazonEnvelope>');
        $i = 1;
        $dom->getElementsByTagName("MerchantIdentifier")->item(0)->nodeValue = $this->merchantId;
        foreach ($products as $product) {
            if (intval($product['1']) > 0) {
                $messageElement = $dom->createElement('Message');
                $messageIdElement = $dom->createElement('MessageID', $i);
                $operationTypeElement = $dom->createElement('OperationType', 'Update');
                $priceElement = $dom->createElement('Inventory');
                $skuElement = $dom->createElement('SKU', $product['0']);
                $stdPriceElement = $dom->createElement('Quantity', $product['1']);
                $priceElement->appendChild($skuElement);
                $priceElement->appendChild($stdPriceElement);
                $messageElement->appendChild($messageIdElement);
                $messageElement->appendChild($operationTypeElement);
                $messageElement->appendChild($priceElement);
                $dom->getElementsByTagName("AmazonEnvelope")[0]->appendChild($messageElement);
            }
            $i++;
        }
        return $dom->saveXML();
    }

    private function prepareSucessAndError($chunks, $outputxml)
    {
        $errors = array();
        $sucees = array();
        $dom = new \DOMDocument();
        $dom->loadXML($outputxml);
        $messages = $dom->getElementsByTagName("Result");
        foreach ($messages as $message) {
            $arrayIndex = intval($message->getElementsByTagName("MessageID")[0]->nodeValue - 1);
            if (isset($chunks[$arrayIndex])) {
                $item = $chunks[$arrayIndex];
                array_push($item, $message->getElementsByTagName("ResultDescription")[0]->nodeValue);
                array_push($errors, $item);
                unset($chunks[$arrayIndex]);
            }
        }
        if (isset($dom) && $dom->getElementsByTagName("StatusCode")[0]->nodeValue == 'Complete') {
            foreach ($chunks as $c) {
                $item = $c;
                array_push($sucees, $item);
            }
        } else {
            foreach ($chunks as $c) {
                $item = $c;
                array_push($item, 'Failure');
                array_push($item, 'Failed to process');
                array_push($errors, $item);
            }
        }
        return array('sucess' => $sucees, 'error' => $errors);
    }
}
