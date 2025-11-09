<?php

/**
 * Get Deposit Address
 * 
 * This will return the deposit address for the specified coin and chain
 * 
 * @param string $coin
 * @param string $chain
 * 
 * @return array
 */

require_once './Services/BybitService.php';

$config  = include_once './config.php';

$bybit = new BybitAPI($config['BYBIT']['API_KEY'], $config['BYBIT']['SECRET_KEY'], $config['BYBIT']['ENDPOINT']);

$coin = $_GET['coin'] ?? null; // this must be in uppercase
$chain = $_GET['chain'] ?? null; // this must be in uppercase
$depositAmount = $_GET['amount'] ?? 100;
$depositFeePercentage = $config['BYBIT']['deposit_fee'];


$addressResponse = $bybit->getDepositAddress($coin, $chain);
if (!is_array($addressResponse)) {

    echo json_encode([
        'message' => "Failed to get deposit address \n",
        'status' => 'error'
    ]);
    exit;
}

$address = $addressResponse['address'];
$tag = $addressResponse['tag'];
$depositLimit = $addressResponse['depositLimit'];
$chainType = $addressResponse['chainType'];
$chain = $addressResponse['chain'];

$depositLimit =  (int) $depositLimit === -1 ? 'No Limit' : number_format($depositLimit, 6);

$depositAmount = ($depositFeePercentage > 0) ? $depositAmount + ($depositAmount * ($depositFeePercentage / 100)) :  $depositAmount;

echo json_encode([
    'message' => 'Parsing Coin Info',
    'data' => [
        'address' => $address,
        'depositAmount' => $depositAmount,
        'chain' => $chain,
        'tag' => $tag,
        'chainType' => $chainType,
        'depositLimit' => $depositLimit,
        'coin' => $coin,
    ],
    'status' => 'success'
]);