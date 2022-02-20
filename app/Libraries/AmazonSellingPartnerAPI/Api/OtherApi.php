<?php
/**
 * FeedsApi.
 *
 * @author   Stefan Neuhaus / ClouSale
 */

/**
 * Selling Partner API for Feeds.
 *
 * The Selling Partner API for Feeds lets you upload data to Amazon on behalf of a selling partner.
 *
 * OpenAPI spec version: 2020-09-04
 */

namespace ClouSale\AmazonSellingPartnerAPI\Api;

use ClouSale\AmazonSellingPartnerAPI\Configuration;
use ClouSale\AmazonSellingPartnerAPI\HeaderSelector;
use ClouSale\AmazonSellingPartnerAPI\Helpers\SellingPartnerApiRequest;
use ClouSale\AmazonSellingPartnerAPI\ObjectSerializer;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

/**
 * FeedsApiNew Class Doc Comment.
 *
 * @author   Stefan Neuhaus / ClouSale
 */
class OtherApi
{
    use SellingPartnerApiRequest;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @var HeaderSelector
     */
    protected $headerSelector;

    public function __construct(Configuration $config)
    {
        $this->client = new Client();
        $this->config = $config;
        $this->headerSelector = new HeaderSelector();
    }

    /**
     * @return Configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function getItemListing($sellerId, $sku, $marketplaceId)
    {
        $resourcePath = '/listings/2021-08-01/items/{sellerId}/{sku}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;
		
		if (null !== $marketplaceId) {
            $queryParams['marketplaceIds'] = ObjectSerializer::toQueryValue($marketplaceId);
        }

        // path params
        if (null !== $sellerId) {
            $resourcePath = str_replace(
                '{' . 'sellerId' . '}',
                ObjectSerializer::toPathValue($sellerId),
                $resourcePath
            );
        }

        if (null !== $sku) {
            $resourcePath = str_replace(
                '{' . 'sku' . '}',
                str_replace(' ', '%20',$sku),
                $resourcePath
            );
        }
        $request = $this->generateRequest($multipart, $formParams, $queryParams, $resourcePath, $headerParams, 'GET', $httpBody);
        return $this->sendRequest($request, "string");
    }

    public function searchContentPublishRecords($marketplaceId, $asin)
    {
        $resourcePath = '/aplus/2020-11-01/contentPublishRecords';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // path params
        if (null !== $marketplaceId) {
            $queryParams['marketplaceId'] = ObjectSerializer::toQueryValue($marketplaceId);
        }
        if (null !== $asin) {
            $queryParams['asin'] = ObjectSerializer::toQueryValue($asin);
        }
		
		$queryParams['includedData'] = ObjectSerializer::toQueryValue("summaries,images,identifiers,productTypes,salesRanks,variations");

        $request = $this->generateRequest($multipart, $formParams, $queryParams, $resourcePath, $headerParams, 'GET', $httpBody);
        return $this->sendRequest($request, "string");
    }

    public function searchContentDocuments($marketplaceId, $pageToken)
    {
        $resourcePath = '/aplus/2020-11-01/contentDocuments';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // path params
        if (null !== $marketplaceId) {
            $queryParams['marketplaceId'] = ObjectSerializer::toQueryValue($marketplaceId);
        }
        if (null !== $pageToken) {
            $queryParams['pageToken'] = ObjectSerializer::toQueryValue($pageToken);
        }
        $request = $this->generateRequest($multipart, $formParams, $queryParams, $resourcePath, $headerParams, 'GET', $httpBody);
        return $this->sendRequest($request, "string");
    }

    public function getContentDocument($marketplaceId, $contentReferenceKey)
    {
        $resourcePath = '/aplus/2020-11-01/contentDocuments/{contentReferenceKey}';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        if (null !== $contentReferenceKey) {
            $resourcePath = str_replace(
                '{' . 'contentReferenceKey' . '}',
                ObjectSerializer::toPathValue($contentReferenceKey),
                $resourcePath
            );
        }

        // path params
        if (null !== $marketplaceId) {
            $queryParams['marketplaceId'] = ObjectSerializer::toQueryValue($marketplaceId);
        }
        $request = $this->generateRequest($multipart, $formParams, $queryParams, $resourcePath, $headerParams, 'GET', $httpBody);
        return $this->sendRequest($request, "string");
    }

    public function listContentDocumentAsinRelations($marketplaceId, $contentReferenceKey)
    {
        $resourcePath = '/aplus/2020-11-01/contentDocuments/{contentReferenceKey}/asins';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        if (null !== $contentReferenceKey) {
            $resourcePath = str_replace(
                '{' . 'contentReferenceKey' . '}',
                ObjectSerializer::toPathValue($contentReferenceKey),
                $resourcePath
            );
        }

        // path params
        if (null !== $marketplaceId) {
            $queryParams['marketplaceId'] = ObjectSerializer::toQueryValue($marketplaceId);
        }
        $request = $this->generateRequest($multipart, $formParams, $queryParams, $resourcePath, $headerParams, 'GET', $httpBody);
        return $this->sendRequest($request, "string");
    }

    public function getInventorySummaries($marketplaceId, $startDateTime = null, $next_token = null)
    {
        $resourcePath = '/fba/inventory/v1/summaries';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        if (null !== $next_token) {
            $queryParams['NextToken'] = ObjectSerializer::toQueryValue($next_token);
        }

        // path params
        if (null !== $marketplaceId) {
            $queryParams['marketplaceIds'] = ObjectSerializer::toQueryValue($marketplaceId);
            $queryParams['granularityId'] = ObjectSerializer::toQueryValue($marketplaceId);
        }

        if (null !== $startDateTime) {
            $queryParams['startDateTime'] = ObjectSerializer::toQueryValue($startDateTime);
        }

        $queryParams['details'] = ObjectSerializer::toQueryValue('true');
        $queryParams['granularityType'] = ObjectSerializer::toQueryValue('Marketplace');

        $request = $this->generateRequest($multipart, $formParams, $queryParams, $resourcePath, $headerParams, 'GET', $httpBody);
        return $this->sendRequest($request, "string");
    }


    public function getOrderFinancialEvents($orderId, $next_token = null)
    {
        $resourcePath = '/finances/v0/orders/{orderId}/financialEvents';
        $formParams = [];
        $queryParams = [];
        $headerParams = [];
        $httpBody = '';
        $multipart = false;

        // query params
        if (null !== $next_token) {
            $queryParams['NextToken'] = ObjectSerializer::toQueryValue($next_token);
        }

        if (null !== $orderId) {
            $resourcePath = str_replace(
                '{' . 'orderId' . '}',
                ObjectSerializer::toPathValue($orderId),
                $resourcePath
            );
        }

        $request = $this->generateRequest($multipart, $formParams, $queryParams, $resourcePath, $headerParams, 'GET', $httpBody);
        return $this->sendRequest($request, "string");
    }

	public function getCatalogItem($marketplace_id, $asin)
	{
	// verify the required parameter 'marketplace_id' is set
	if (null === $marketplace_id || (is_array($marketplace_id) && 0 === count($marketplace_id))) {
	throw new InvalidArgumentException('Missing the required parameter $marketplace_id when calling getCatalogItem');
	}
	// verify the required parameter 'asin' is set
	if (null === $asin || (is_array($asin) && 0 === count($asin))) {
	throw new InvalidArgumentException('Missing the required parameter $asin when calling getCatalogItem');
	}

	$resourcePath = '/catalog/2020-12-01/items/{asin}';
	$formParams = [];
	$queryParams = [];
	$headerParams = [];
	$httpBody = '';
	$multipart = false;

	// query params
	if (null !== $marketplace_id) {
	$queryParams['marketplaceIds'] = ObjectSerializer::toQueryValue($marketplace_id);
	}

	$queryParams['includedData'] = ObjectSerializer::toQueryValue("summaries,images,identifiers,productTypes,salesRanks,variations");
	// path params
	if (null !== $asin) {
	$resourcePath = str_replace(
	'{'.'asin'.'}',
	ObjectSerializer::toPathValue($asin),
	$resourcePath
	);
	}

	$request = $this->generateRequest($multipart, $formParams, $queryParams, $resourcePath, $headerParams, 'GET', $httpBody);
	return $this->sendRequest($request, "string");
	}
}
