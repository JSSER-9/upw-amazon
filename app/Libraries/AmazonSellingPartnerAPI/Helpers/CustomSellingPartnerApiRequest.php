<?php

namespace ClouSale\AmazonSellingPartnerAPI\Helpers;

use ClouSale\AmazonSellingPartnerAPI\ApiException;
use ClouSale\AmazonSellingPartnerAPI\ObjectSerializer;
use ClouSale\AmazonSellingPartnerAPI\Signature;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Query;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Utils;

/**
 * Trait SellingPartnerApiRequest.
 *
 * @author Stefan Neuhaus / ClouSale
 */
trait CustomSellingPartnerApiRequest
{
    use SellingPartnerApiRequest;


    /**
     * @throws ApiException
     */
    private function sendRequest(Request $request, string $returnType=null): array
    {
        try {
			$options = $this->createHttpClientOption();
            try {
				$response = $this->client->send($request, $options);
            } catch (RequestException $e) {
				throw new ApiException("[{$e->getCode()}] {$e->getMessage()}", $e->getCode(), $e->getResponse() ? $e->getResponse()->getHeaders() : null, $e->getResponse() ? $e->getResponse()->getBody()->getContents() : null);
            }
            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(sprintf('[%d] Error connecting to the API (%s)', $statusCode, $request->getUri()), $statusCode, $response->getHeaders(), $response->getBody());
            }

            $responseBody = $response->getBody();

            if ('\SplFileObject' === $returnType) {
                $content = $responseBody; //stream goes to serializer
            } 
            else if(is_null($returnType)){
                $content = $responseBody;
                $content = json_decode($content);
            }
            else {
                $content = $responseBody->getContents();
                if (!in_array($returnType, ['string', 'integer', 'bool'])) {
                    $content = json_decode($content);
                }
            }
//            var_dump($content);
//            exit();

            return [
                is_null($returnType)? $content :ObjectSerializer::deserialize($content, $returnType, []),
                $response->getStatusCode(),
                $response->getHeaders(),
            ];
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 503:
                case 500:
                case 429:
                case 404:
                case 403:
                case 401:
                case 400:
                case 200:
                    dd($e->getResponseBody());
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        $returnType,
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }
}