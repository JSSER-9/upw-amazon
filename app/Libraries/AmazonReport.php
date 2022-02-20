<?php

namespace App\Libraries;

class AmazonReport
{
    public $refresh_token = '1';
    public $client_id = '';
    public $client_secret = '';
    public $region = 'us-east-1';
    public $secret_key = '';
    public $access_key = '';
    public $endpoint = 'http://localhost:8080';
    public $userId = null;
    public $apiDelay = 5;
    public $reportName = 'FBA_RETURNS';
    public $reportType = 'GET_FBA_FULFILLMENT_CUSTOMER_RETURNS_DATA';
    public $marketplaceIds = array(
        'ATVPDKIKX0DER',
    );

    public $interval = null;
    public $reportStartDate = null;
    public $reportEndDate = null;
    public $timezone = 'US/Eastern';
    public $modelName = 'FbaReturns';
    public $merchantId = 'marketId';
    public $uniqueId = array();
	public $userMarketPlaceId = 0;
    public $caseConvertField = false;
}
