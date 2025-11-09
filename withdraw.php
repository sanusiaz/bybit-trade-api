<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crypto Withdraw Page</title>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #121212;
            font-family: Arial, sans-serif;
            color: #fff;
        }

        .container {
            background: #1e1e1e;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            width: 350px;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
        }

        .logo {
            width: 100px;
            margin-bottom: 10px;
        }

        input,
        button {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: none;
        }

        input {
            background: #333;
            color: #fff;
        }

        button {
            background: #ff9900;
            color: #fff;
            cursor: pointer;
        }

        button:hover {
            background: #e68a00;
        }

        .details {
            margin-top: 15px;
            text-align: left;
            display: none;
        }

        #address {
            word-wrap: break-word;
        }

        #response {
            display: none;
            position: fixed;
            place-content: center;
            place-items: center;
            height: 100vh;
            width: 100vw;
            top: 0px;
            bottom: 0px;
            right: 0px;
            left: 0px;
            color: black;
            background-color: #464c50d9;
        }


        #response .close_btn svg {
            height: 100%;
            width: 100%;
        }

        #response .close_btn {
            height: 20px;
            width: 20px;
            position: absolute;
            top: 6px;
            right: 10px;
            cursor: pointer;

        }

        #response #response_container {
            background-color: white;
            display: block;
            flex-direction: column;
            max-width: 500px;
            align-items: center;
            justify-content: center;
            gap: 10px;
            word-wrap: break-word;
            overflow: hidden;
            color: black;
            padding: 20px;
            border-radius: 5px;
            position: relative;
            padding-top: 25px;
        }

        #response #response_container span {
            display: block;
            word-wrap: break-word;
            overflow: hidden;
            width: 100%;
        }

        #response #top_header {
            display: flex;
            gap: 5px;
            align-items: center;
            justify-content: center;
        }

        #response #response_inner_text {
            display: flex;
            gap: 5px;
            flex-direction: column;
            align-items: flex-start;
        }
    </style>
</head>

<body>
    <div class="container">

        <div class="inner_container" id="coinDetailsForm">
            <!-- <img src="logo.png" alt="Logo" class="logo"> -->
            <h2>Crypto Withdraw</h2>
            <input type="text" id="coin" placeholder="Enter Coin (e.g., USDT)" value="USDT">
            <input type="text" id="chain" placeholder="Enter Chain (e.g., TRX)" value="BSC">
            <input type="text" id="address" placeholder="Enter wallet address">
            <button class="get_address_info">Proceed</button>
        </div>

        <div class="details" id="depositDetails">
            <p><strong>Deposit Address:</strong> <span id="address"></span></p>
            <p><strong>Tag:</strong> <span id="tag"></span></p>
            <p><strong>Deposit Limit:</strong> <span id="limit"></span></p>
            <p><strong>Amount to send:</strong> <span id="depositAmount"></span></p>
            <button onclick="goBackToDeposit()">Back</button>
            <button class="validate_transaction_btn" onclick="validateDepositTransactions()">Submit Request</button>

        </div>

    <a href="./deposit.php" style="color: white; display: block; text-decoration: underline;">Go to Deposit.</a>

    </div>
    <div id="response">
        <div id="response_container">

            <!-- Close Btn -->
            <div class="close_btn">
                <svg data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                </svg>
            </div>


            <div id="top_header">

                <svg style="stroke: green; height: 30px; width: 30px;" data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path>
                </svg>
                <div id="response_text"></div>
            </div>


            <div id="response_inner_text">
                <span><b>Hash Code:</b> <span class="hash_code"></span></span>
                <span><b>From Address:</b> <span class="from_address"></span></span>
                <span><b>Confirmations:</b>: <span class="confirmations"></span></span>
                <span><b>Status:</b> <span class="status"></span></span>
                <span><b>Confirmed At:</b> <span class="confirmed_at"></span></span>
            </div>
        </div>
    </div>


    <script>
        $(document).ready(function() {
            $('.close_btn').click(function() {
                $('#response').fadeOut();
            });

            return false;



            // get wallet address type, chain and coin type
            $(".get_address_info").click(function () {
                let __address = $("#address").val();

                fetch(`https://services.tokenview.io/vipapi/address/balances?apikey={$apiKey}&address=${__address}`)
.then(response => response.json())
.then(data => console.log(data.chain, data.symbol, data.type))
.catch(err => console.error(err));
            });

        });
    </script>

    <script>
        function goBackToDeposit() {
            document.getElementById('depositDetails').style.display = 'none';
            document.getElementById('coinDetailsForm').style.display = 'block';

            $('#coinDetailsForm button').text('Proceed');

            $('.validate_transaction_btn').text('Validate Transaction');

        }


        function validateDepositTransactions() {

            $('.validate_transaction_btn').text('Sending Request...');
            // Validate transactions
            let coin = document.getElementById('coin').value.trim();
            let chain = document.getElementById('chain').value.trim();
            let amount = document.getElementById('amount').value.trim();

            let __data = {}
            __data['coin'] = coin
            __data['chain'] = chain
            __data['transaction_limit'] = 20
            __data['hash'] = ''
            __data['amount'] = amount

            $.ajax({
                url: './verify_deposit_transactions.php',
                type: 'GET',
                data: __data,
                beforeSend: () => {
                    console.log("sending request ");

                },
                complete: (response) => {
                    let __response = JSON.parse(response.responseText)
                    console.log(__response);


                    if (__response.status === 'success') {
                        // deposit has been made successfully
                        let __data = __response.data

                        let hashCode = __data.coin_response?.blockHash ?? __data.chain_response?.blockHash;

                        __data = __data.coin_response ?? __data.chain_response;
                        let toAddress = __data.toAddress;
                        let fromAddress = __data.fromAddress
                        let tag = __data.tag
                        let txId = __data.txID;

                        let amount = __data.amount;
                        let confirmations = __data.confirmations
                        let paidAt = __data.successAt
                        let depositFee = __data.depositFee
                        let depositType = __data.depositType
                        let txIndex = __data.txIndex
                        let status = __data.status

                        let convertedTime = new Date(paidAt).toLocaleString();

                        $('#response .hash_code').text(hashCode);
                        $('#response .from_address').text(fromAddress);
                        $('#response .confirmations').text(confirmations);
                        $('#response .status').text(status);
                        $('#response .confirmed_at').text(convertedTime);

                        $('#response').fadeIn();
                        $("#response_text").text(__response.message);
                    }

                },
                error: (err) => {
                    console.error(err);
                }
            });
        }
    </script>
</body>

</html>