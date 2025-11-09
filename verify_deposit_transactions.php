<?php

/**
 * Verify Deposit Transactions
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

// Example Usage
$bybit = new BybitAPI($config['BYBIT']['API_KEY'], $config['BYBIT']['SECRET_KEY'], $config['BYBIT']['ENDPOINT']);

$coin = $_GET['coin'] ?? 'USDT'; // this must be in uppercase
$depositAmount = $_GET['amount'];
$blockHashToExclude = $_GET['hash']; // this is the block hash to exclude ( this is important so it will prevent validating the same transaction twice) 

$transactionLimit = $_GET['transaction_limit'];
$chain = $_GET['chain'];

// coin 
$chainCoinDepositResponse = $bybit->getDepositRecordsOnChainFromAddress($coin, "", $depositAmount, $transactionLimit, $blockHashToExclude, true);

$chainTypeDepositResponse = $bybit->getDepositRecordsOnChainFromAddress($chain, "", $depositAmount, $transactionLimit, $blockHashToExclude, true);

if ($chainCoinDepositResponse !== null || $chainTypeDepositResponse !== null) {

    echo json_encode([
        'status' => 'success',
        'message' => 'Deposit has been confirmed',
        'data' => [
            'chain_response' => $chainTypeDepositResponse,
            'coin_response' => $chainCoinDepositResponse,
        ]
    ]);

    exit;
}

echo json_encode([
    'status' => 'error',
    'message' => 'An Error Occurred Please Try Again Later'
]);

exit;
