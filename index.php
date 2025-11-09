<?php

require_once './Services/BybitService.php';

$config  = include_once './config.php';

// Example Usage
$bybit = new BybitAPI($config['BYBIT']['API_KEY'], $config['BYBIT']['SECRET_KEY'], $config['BYBIT']['ENDPOINT']);

// [ === START DEPOSIT PROCESS === ]
$depositAmount = 418.270855;
$coin = "USDT"; // this must be in uppercase
$chain = "TRX"; // this must be in uppercase
$transactionLimit = 20;
$depositFromAddress = "TGNfYNQo4JSU7ZE5awn7W73WNHotkS3Yoq"; // this is the address the deposit is coming from 
$withdrawToAddress = ""; // this is the address the withdraw is going to
$withdrawAmount = 120; // this is the amount to withdraw
$orderType = "market"; // spot or market
$withdrawSymbol = $chain . $coin;

$parsedWithdrawPercentageFee = 10;
$parsedDepositPercentageFee = 0.01;

$blockHashToExclude = ""; // this is the block hash to exclude ( this is important so it will prevent validating the same transaction twice)



// Get Coin Info 
$coinInfo = $bybit->getCoinInfo($coin, $chain);

if (is_array($coinInfo)) {

    // coin info exists
    $chainType = $coinInfo['chainType'];
    $confirmation = $coinInfo['confirmation'];
    $chain = $coinInfo['chain'];
    $coin = $coinInfo['coin'];
    $withdrawFee =  $coinInfo['withdrawFee'];
    $withdrawMin = $coinInfo['withdrawMin'];
    $withdrawPercentageFee = $coinInfo['withdrawPercentageFee'];


    // check if chain deposit is allowed
    if ((int) $coinInfo['chainDeposit'] === 1) {
        $addressResponse = $bybit->getDepositAddress($coin, $chain);


        if (!is_array($addressResponse)) {
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


        echo "This is for the coin \n";

        // coin 
        $chainCoinDepositResponse = $bybit->getDepositRecordsOnChainFromAddress($coin, $depositFromAddress, $depositAmount, $transactionLimit, $blockHashToExclude, true);

        echo "This is now for the chain \n";

        // chain type
        $chainTypeDepositResponse = $bybit->getDepositRecordsOnChainFromAddress($chain, $depositFromAddress, $depositAmount, $transactionLimit, $blockHashToExclude, true);

        echo " We are done with the deposit records\n";
        if ($chainCoinDepositResponse !== null || $chainTypeDepositResponse !== null) {

            // check if the parsed block hash 

            // we will remove the deposit Fee percentage from the deposit. Note this is optional 
            $depositAmount = ($parsedDepositPercentageFee > 0) ? $depositAmount - ($depositAmount * ($parsedDepositPercentageFee / 100)) :  $depositAmount;

            echo "Deposit Has been verified with amount after fee deducted is " . $depositAmount . "\n";
        }
    }



    // [ === START WITHDRAW PROCESS === ]
    if ((int) $coinInfo['chainWithdraw'] === 1) {
        $withdrawPercentageFee = $withdrawPercentageFee === 0 ? $parsedWithdrawPercentageFee : $withdrawPercentageFee;

        $fee = $withdrawAmount * ($withdrawPercentageFee / 100);

        $withdrawAmount = $withdrawAmount - $fee - $withdrawFee;

        echo "The allowable withdraw amount is " . $withdrawAmount . " \n";

        // send a withdraw request and check if it is successful 
        // $withdrawRequest = $bybit->makeWithdrawal();
        // return transaction history when the transaction has been made
    }
    // [ === END WITHDRAW PROCESS === ]

}
