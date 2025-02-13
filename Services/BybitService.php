<?php

class BybitAPI
{
    private $apiKey;
    private $apiSecret;
    private $baseUrl;

    public function __construct($apiKey, $apiSecret, $baseUrl)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Fetch trading fee rates for a specific symbol
     */
    public function getTradingFeeRate($symbol, $category)
    {
        sleep(2);
        $endpoint = "/v5/account/fee-rate";
        $params = [
            "api_key" => $this->apiKey,
            "symbol" => $symbol,
            "timestamp" => round(microtime(true) * 1000),
            "category" => $category,
            "recv_window" => 10000
        ];

        // Generate API signature
        $params["sign"] = $this->generateSignature($params);

        $response = $this->sendRequest($endpoint, $params);

        if ($response && isset($response['result']['list'])) {
            foreach ($response['result']['list'] as $feeData) {
                if ($feeData['symbol'] === $symbol) {
                    return [
                        "symbol" => $symbol,
                        "takerFeeRate" => (float)$feeData['takerFeeRate'],
                        "makerFeeRate" => (float)$feeData['makerFeeRate']
                    ];
                }
            }
        }
        return null;
    }

    /**
     * Calculate trading fee based on order type
     */
    public function calculateFee($symbol, $tradeAmount, $orderType)
    {
        $feeRates = $this->getTradingFeeRate($symbol, $orderType);

        if (!$feeRates) {
            return "Error: Unable to fetch fee rates for {$symbol}";
        }

        // Determine the fee rate based on order type
        $feeRate = ($orderType === 'market') ? $feeRates['takerFeeRate'] : $feeRates['makerFeeRate'];

        // Calculate fee
        $fee = $tradeAmount * $feeRate;

        return number_format($fee, 6); // Format to 6 decimal places
    }

    /**
     * Generate HMAC-SHA256 API Signature
     */
    private function generateSignature($params)
    {
        ksort($params); // Sort parameters alphabetically
        $queryString = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        return hash_hmac('sha256', $queryString, $this->apiSecret);
    }

    /**
     * Send API request using cURL
     */
    private function sendRequest($endpoint, $params = [])
    {
        $url = $this->baseUrl . $endpoint . '?' . http_build_query($params);
        $headers = ["Content-Type: application/json"];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return ["error" => curl_error($ch)];
        }
        curl_close($ch);

        return json_decode($response, true);
    }


    // set base url
    public function __setBaseUrl(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }


    // Get Coin Info
    public function getCoinInfo($coin, $chain = null) {
        sleep(3);
        $endpoint = "/v5/asset/coin/query-info";
        $params = [
            "api_key" => $this->apiKey,
            "coin" => $coin,
            "timestamp" => round(microtime(true) * 1000),
            "recv_window" => 10000

        ];

        // Generate API signature
        $params["sign"] = $this->generateSignature($params);

        $response = $this->sendRequest($endpoint, $params);

        if ( $response && isset($response['result']['rows'])) {

            $rows = $response['result']['rows'];
            $chains = $rows[0]['chains'];

            if ( $chain !== null && array_search($chain, array_column( $chains, 'chain' )) ) {
                $result =  $chains[array_search($chain, array_column( $chains, 'chain' ))];

                $result['coin'] = $coin;
                return $result;
            }
        }

        return NULL;
    }


    // Get Deposit Wallet Address
    public function getDepositAddress($coin, $chain) {
        sleep(5);
        $endpoint = "/v5/asset/deposit/query-address";

        $params = [
            "api_key" => $this->apiKey,
            "coin" => $coin,
            "timestamp" => round(microtime(true) * 1000),
            "chainType" => $chain,
            "recv_window" => 10000
        ];

        // Generate API signature
        $params["sign"] = $this->generateSignature($params);

        $response = $this->sendRequest($endpoint, $params);
        if ( is_array( $response ) && isset( $response['retMsg'] ) && $response['retMsg'] === 'success' ) {
            $chains = $response['result']['chains'];    
            $result =  $chains[array_search( $chain, array_column( $chains, 'chain' ) )];

            return [
                "address" => $result['addressDeposit'],
                "tag" => $result['tagDeposit'],
                "chainType" => $result['chainType'],
                "chain" => $result['chain'],
                "depositLimit" => $result['batchReleaseLimit'],
            ];
        }

        return NULL;
    }


    // Check Balance for required coin and required pair
    public function getWalletBalance($accountType, $coin, $chain = null, $subAccountMemberId = null, $withBonus = 0)
    {
        sleep(3);
        $endpoint = "/v5/asset/transfer/query-account-coins-balance";


         $params = [
            "api_key" => $this->apiKey,
            "coin" => $coin,
            "timestamp" => round(microtime(true) * 1000),
            "accountType" => $accountType,
            'withBonus' => $withBonus
        ];

        if ( $chain !== null ) {
            $params['coin'] .= ',' . $chain;
        }

        if ( $subAccountMemberId !== null ) {
            $params['memberId'] = $subAccountMemberId;
        }

        // Generate API signature
        $params["sign"] = $this->generateSignature($params);

        $response = $this->sendRequest($endpoint, $params);
        var_dump($response);
        die("stop here");

        return $this->sendRequest($endpoint, $params);
    }


    // Make a withdrawal on the exchange 
    public function makeWithdrawal($coin, $amount, $address, $chain, $tag = null, $exchangeAccountType = "FUND", $forceChain = 0)
    {
        sleep(3);
        $endpoint = "/v5/asset/withdraw/create";
        $params = [
            "api_key" => $this->apiKey,
            "coin" => $coin,
            "amount" => $amount,
            "address" => $address,
            "chain" => $chain,
            "address" => $address,
            "amount" => $amount,
            "timestamp" => round(microtime(true) * 1000),
            "forceChain" => $forceChain,
            "accountType" => $exchangeAccountType
        ];

        $params["sign"] = $this->generateSignature($params);

        // Some coins require a tag (e.g., XRP, XLM)
        if ($tag) {
            $params["tag"] = $tag;
        }

        $response = $this->sendRequest($endpoint, $params);
        var_dump($response);
        die("stop here");

        return $this->sendRequest($endpoint, $params);
    }


    // Cancel Withdraw Order
    public function cancelWithdrawOrder() {}



    // Get all closed orders on the exchange i.e withdraw
    public function getAccountBalance() {}


    public function getDepositRecordsOnChain($coin, $limit = 20, $startTime = null, $endTime = null) {
        sleep(3);
        $endpoint = "/v5/asset/deposit/query-record";
        $params = [
            "api_key" => $this->apiKey,
            "coin" => $coin,
            "timestamp" => round(microtime(true) * 1000),
            'limit' => $limit,
            'recv_window' => 10000
        ];

        if ($startTime !== null) {
            $params["startTime"] = $startTime;
        }

        if ($endTime !== null) {
            $params["endTime"] = $endTime;
        }

        // Generate API signature
        $params["sign"] = $this->generateSignature($params);
        return $this->sendRequest($endpoint, $params);
    }

    public function getDepositRecordsOnChainFromAddress($coin, $toAddress, $amount, $chain = null, $limit = 20, $startTime = null, $endTime = null, $txID = "", $blockHash = "", $status = 3) {
        $response = $this->getDepositRecordsOnChain($coin, $limit, $startTime, $endTime);

        $records = $response['result']['rows'];

        var_dump($records);
        die("stop here");
    }
}
