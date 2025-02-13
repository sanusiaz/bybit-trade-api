<?php

require_once './Services/BybitService.php';

$config  = include_once './config.php';

// Example Usage
$bybit = new BybitAPI($config['BYBIT']['API_KEY'], $config['BYBIT']['SECRET_KEY'], $config['BYBIT']['ENDPOINT']);

// [ === START DEPOSIT PROCESS === ]
$depositAmount = 100;
$coin = "USDT";
$chain = "SOL";

// Get Coin Info 
$coinInfo = $bybit->getCoinInfo($coin, $chain);

if ( is_array( $coinInfo ) ) {

    echo "Coin info Has Been Loaded here \n";

    // coin info exists
    $chainType = $coinInfo['chainType'];
    $confirmation = $coinInfo['confirmation'];
    $chain = $coinInfo['chain'];    
    $coin = $coinInfo['coin'];    

    // check if chain deposit is allowed
    if ( (int) $coinInfo['chainDeposit'] === 1 ) {
        $addressResponse = $bybit->getDepositAddress($coin, $chain);
        echo "Deposit Address Request Successful \n";
        

        if ( !is_array($addressResponse) ) {
            echo "Failed to get deposit address \n";
            exit;   
        }

        $address = $addressResponse['address'];
        $tag = $addressResponse['tag'];
        $depositLimit = $addressResponse['depositLimit'];
        $chainType = $addressResponse['chainType'];
        $chain = $addressResponse['chain'];

        $depositLimit =  (int) $depositLimit === -1 ? 'No Limit' : number_format($depositLimit, 6);

        echo "Deposit Address: " . $address . ", " . "Tag: " . $tag . ", Confirmation(s): " . $confirmation . ", " . " Chain: " . $chain . ", " . "chainType: " . $chainType . ", " . "Coin: " . $coin . ", " . "Deposit Limit: " . $depositLimit . "\n";

        // check if deposit has been made
        $response = $bybit->getDepositRecordsOnChainFromAddress("USDT", $address, $depositAmount, 'BEP20', 30);
        var_dump($response);
        
    }
    else {
        echo "Chain Deposit is not allowed for this coin \n";
    }

    // [ === END DEPOSIT PROCESS === ]  



    // [ === START WITHDRAW PROCESS === ]
    $symbol = "BTCUSDT";
    $tradeAmount = 120;
    $orderType = "spot"; // spot or market
    $fee = $bybit->calculateFee($symbol, $tradeAmount, $orderType);
    echo "Trading Fee for {$orderType} order on {$symbol}: $ {$fee}\n"; 

    // get wallet balance
        // $walletBalance = $bybit->getWalletBalance("FUND", $coin, $chain);
    
    // [ === END WITHDRAW PROCESS === ]
    
}


