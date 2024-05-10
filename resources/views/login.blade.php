<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        body {
            background-color: #BFD7EA; /* Baby blue background */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        form {
            width: 90%; /* Responsive width */
            max-width: 600px; /* Max width to maintain form size on larger screens */
            margin: auto; /* Center form vertically and horizontally */
            padding: 40px; /* Ample padding for better visual spacing */
            background: rgba(255, 255, 255, 0.8); /* Semi-transparent form background */
            border-radius: 10px; /* Rounded corners for the form */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);  /* For absolute positioning of button */
        }
        input {
            display: block;
            width: 100%; /* Full width inputs */
            padding: 12px; /* Comfortable padding */
            margin: 10px 0; /* Consistent vertical spacing */
            border-radius: 8px; /* Rounded edges for input fields */
            border: 1px solid #ccc; /* Subtle border styling */
        }
        button {
            background-color: #00008B; /* Dark blue button for consistency */
            color: white;
            padding: 8px 16px; /* Reduced padding for a smaller button */
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: block; /* Make button a block to manipulate size */
            margin: 10px auto; /* Center button horizontally with margin */
            width: auto; /* Auto width based on content size */
        }
        button:hover {
            opacity: 0.8; /* Hover effect for button */
        }
        .register-link {
            display: block;
            text-align: center;
            margin-top: 15px; /* Space above the link */
        }
        @media (max-width: 768px) {
            form {
                padding: 20px; /* Reduce padding on smaller screens */
                width: 95%; /* Increase width to utilize more screen space */
            }
            button, input {
                padding: 10px; /* Adjust padding for better fit */
            }
        }
    </style>
</head>
<body>
    <form method="POST" action="{{ route('dologin') }}">
        @csrf
        @if (Session::has('success'))
            <div class="alert alert-success">
                {{ Session::get('success') }}
            </div>
        @endif
        @if (Session::has('fail'))
            <div class="alert alert-danger">
                {{ Session::get('fail') }}
            </div>
        @endif
        <div>
            <input type="text" id="name" name="name"  placeholder="الاسم"  required>
        </div>
        <div>
            <input type="password" id="password" name="password" placeholder="كلمة المرور" required>
        </div>
        <div>
            <button type="submit">تسجيل الدخول</button>
        </div>
        @if($errors->any())
            <div style="color: red;">
                {{$errors->first()}}
            </div>
        @endif
        <div class="register-link">
            <a href="{{ route('doregister') }}">لا تملك حساب؟ اضغط هنا</a>
        </div>
    </form>
</body>
</html>
