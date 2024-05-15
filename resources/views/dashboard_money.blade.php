<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Transfer Money</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            background-color: #BFD7EA;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            font-family: 'Arial', sans-serif;
        }
        .container {
            width: 100%;
            max-width: 600px;
            padding: 40px 20px;
            animation: slideIn 0.5s ease-out forwards;
        }
        .shadow-custom {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1), 0 6px 20px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease-in-out;
        }
        .shadow-custom:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2), 0 16px 32px rgba(0, 0, 0, 0.2);
        }
        .form-control, .btn-primary {
            transition: all 0.3s ease-in-out;
        }
        .form-control:focus {
            border-color: #1a0f9c;
            box-shadow: 0 0 0 0.2rem rgba(33, 26, 252, 0.25);
        }
        .btn-primary {
            background-color: #221afc;
            border-color: #221afc;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: #1a0f9c;
            border-color: #190f8a;
        }
        .top-left, .top-right {
            position: absolute;
            top: 20px;
        }
        .top-left {
            left: 20px;
        }
        .top-right {
            right: 20px;
        }
        .logout-button, .transfer-points-button {
            background-color: #221afc; /* Dark blue background */
            color: white; /* White text */
            padding: 8px 16px; /* Padding for button size */
            border: none; /* No border */
            border-radius: 5px; /* Rounded corners */
            cursor: pointer; /* Pointer cursor on hover */
            text-decoration: none; /* Remove underline from link */
            margin-left: 10px; /* Margin between buttons */
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @media (max-width: 767px) {
            .container {
                padding: 20px;
            }
            .top-left {
                top: 10px;
                left: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="top-left">
        <a href="{{ route('logout') }}" class="logout-button" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">تسجيل الخروج</a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
    <div class="top-right">
        <a href="{{ route('dashboard') }}" class="transfer-points-button">تحويل النقاط</a>
    </div>
    <div class="container">
        <div class="card shadow-custom">
            <div class="card-body">
                <h5 class="card-title">تحويل المال للمستخدم</h5>
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                <form id="moneyTransferForm" action='/admin/transfer-money' method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="account_number" class="form-label">أدخل رقم حساب المستلم</label>
                        <input type="text" class="form-control" id="account_number" name="account_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">أدخل المبلغ</label>
                        <input type="number" class="form-control" id="amount" name="amount" required>
                    </div>
                    <button type="submit" class="btn btn-primary">تحويل المال</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
