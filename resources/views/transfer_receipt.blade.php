<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إيصال التحويل - Transfer Receipt</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            direction: rtl;
            text-align: right;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 100%;
            max-width: 700px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative; /* To position the QR code and footer elements */
            border-top: 20px solid #003E6B; /* Add a dark indigo top border */
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .header .left {
            text-align: left;
        }
        .header .left img {
            max-width: 100px;
        }
        .header .right h1, .header .right p {
            margin: 0;
            color: #003E6B;
        }
        .header .right h1 {
            font-size: 24px;
            font-weight: bold;
        }
        .header .right p {
            font-size: 25px;
            font-weight: bold;
        }
        .details {
            margin-bottom: 20px;
        }
        .details .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .details .detail-row .label-ar {
            text-align: right;
            flex: 1;
            font-weight: bold;
            color: #4B4B4B;
        }
        .details .detail-row .label-en {
            text-align: left;
            flex: 1;
            font-weight: bold;
            color: #4B4B4B;
        }
        .details .detail-row .value {
            text-align: center;
            flex: 2;
            color: #4B4B4B;
        }
        .container-grey-trans {
            background-color: #e0e0e0; /* Grey color */
            padding: 10px; /* Add some padding */
            border-radius: 5px; /* Optional: Add rounded corners */
            margin-bottom: 10px; /* Optional: Add margin for spacing */
        }
        .detail-row-transfer-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .label-ar-trans, .label-en-trans {
            font-weight: bold;
            color: #4B4B4B;
        }
        .detail-date {
            display: initial;
            justify-content: right;
            align-items: right;
            font-weight: bold;
            color: black;
            margin-bottom: 10px;
        }
        .detail-date .label-ar, .detail-date .label-en {
            font-weight: bold;
            color: black;
            flex: 1;
        }
        .detail-date .value {
            text-align: right;
            color: black;
        }
        .qr-code {
            position: absolute;
            left: 20px;
            bottom: 20px;
        }
        .footer {
            position: absolute;
            right: 20px;
            bottom: 20px;
            text-align: right;
            padding-right: 30px;
        }
        .footer p {
            margin: 0;
            color: #003E6B;
            
            font-size: 30px;
        }
        .footer c {
            margin: 0;
            color: #003E6B;
            font-size: 15px;
        }
        .social-icons {
            display: flex;
            justify-content: center;
            margin-top: 10px;
        }
        .social-icons img {
            width: 20px;
            height: 20px;
            margin: 0 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div style="height: 20px;"></div> <!-- This will add a vertical space of 20px -->

        <div class="header">
            <div class="right">
                <h1>إيصال التحويل</h1>
                <p>Transfer Receipt</p>
            </div>
            <div class="left">
                <img src="{{ asset('images/alrajhi_logo.png') }}" alt="Al Rajhi Bank">
            </div>
        </div>
        <div style="height: 20px;"></div> <!-- This will add a vertical space of 20px -->

        <div class="detail-date">
            <span class="label-ar">التاريخ</span>
            <span class="label-en">Date</span>
            <div class="value">{{ $date }}</div>
        </div>

        <div style="height: 20px;"></div> <!-- This will add a vertical space of 20px -->

        <div class="details" style="padding-left: 20px; padding-right: 20px;">
            <div style="height: 20px;"></div> <!-- This will add a vertical space of 20px -->
            <div class="container-grey-trans">
                <div class="detail-row-transfer-details">
                    <span class="label-ar-trans">تفاصيل التحويل</span>
                    <span class="label-en-trans">Transfer Details</span>
                </div>
            </div>
            <div style="height: 20px;"></div> <!-- This will add a vertical space of 20px -->

            <div class="detail-row">
                <span class="label-ar">المبلغ</span>
                <span class="value">{{ number_format($amount, 2) }} SAR</span>
                <span class="label-en">Amount</span>
            </div>
            <div style="height: 20px;"></div> <!-- This will add a vertical space of 20px -->
            <div class="detail-row">
                <span class="label-ar">من</span>
                <span class="value">{{ $sender_name }}</span>
                <span class="label-en">From</span>
            </div>
            <div style="height: 20px;"></div> <!-- This will add a vertical space of 20px -->
            <div class="detail-row">
                <span class="label-ar">إلى</span>
                <span class="value">{{ $receiver_name }}</span>
                <span class="label-en">To</span>
            </div>
            <div style="height: 20px;"></div> <!-- This will add a vertical space of 20px -->
            <div class="detail-row">
                <span class="label-ar">الغرض</span>
                <span class="value">{{ $purpose }}</span>
                <span class="label-en">Purpose</span>
            </div>
        </div>
        <div style="height: 170px;"></div> <!-- This will add a vertical space of 20px -->

        <div class="qr-code">
            {!! QrCode::size(100)->color(0, 62, 107)->generate('Transaction ID: ' . $transaction_id) !!}
        </div>
        <div class="footer">
            <c>www.alrajhibank.com.sa</c>
            <p>920 003 344</p>
            <div class="social-icons">
                <img src="{{ asset('images/twitter_icon.png') }}" alt="Twitter">
                <img src="{{ asset('images/whatsapp_icon.png') }}" alt="WhatsApp">
            </div>
        </div>
    </div>
</body>
</html>
