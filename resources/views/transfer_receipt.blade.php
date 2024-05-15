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
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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
            max-width: 120px; /* Adjust based on your logo's aspect ratio */
        }
        .header .right h1, .header .right p {
            margin: 0;
            color: #003E6B; /* Adjust blue color as per the brand */
        }
        .header .right h1 {
            font-size: 20px; /* Smaller than your original for closer match */
        }
        .header .right p {
            font-size: 15px;
        }
        .details div {
            margin-bottom: 8px; /* Less space between rows */
        }
        .details label {
            font-weight: bold;
            color: #4B4B4B;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
        }
        .footer p {
            margin: 0;
            color: #003E6B; /* Consistent with header */
            font-size: 15px; /* Larger font for visibility */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="left">
                <img src="{{ asset('images/alrajhi_logo.png') }}" alt="Al Rajhi Bank">
                <p>مصرف الراجحي</p>
                <p>Al Rajhi Bank</p>
            </div>
            <div class="right">
                <h1>إيصال التحويل</h1>
                <p>Transfer Receipt</p>
            </div>
        </div>
        <div class="details">
            <!-- Ensure you pass these variables correctly from your controller -->
            <div><label>التاريخ:</label> <span>{{ $date }}</span></div>
            <div><label>المبلغ:</label> <span>{{ number_format($amount, 2) }} SAR</span></div>
            <div><label>من:</label> <span>{{ $sender_name }}</span></div>
            <div><label>رقم الحساب:</label> <span>{{ $sender_account_number }}</span></div>
            <div><label>إلى:</label> <span>{{ $receiver_name }}</span></div>
            <div><label>رقم الحساب:</label> <span>{{ $receiver_account_number }}</span></div>
            <div><label>الغرض:</label> <span>{{ $purpose }}</span></div>
        </div>
        <div class="footer">
            {!! QrCode::size(100)->color(0, 62, 107)->generate('Transaction ID: ' . $transaction_id) !!}
            <p>www.alrajhibank.com.sa</p>
            <p>920 003 344</p>
        </div>
    </div>
</body>
</html>
