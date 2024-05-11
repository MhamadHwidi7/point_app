<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>انشاء الحساب</title>
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
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
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
        input {
            display: block;
            width: 100%; /* Full width inputs */
            padding: 12px; /* Comfortable padding */
            margin: 10px 0; /* Consistent vertical spacing */
            border-radius: 8px; /* Rounded edges for input fields */
            border: 1px solid #ccc; /* Subtle border */
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
    <form method="POST" action="{{ route('doregister') }}">
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
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <input type="text" name="name" placeholder=" الأسم" required value="{{ old('name') }}">
    <input type="text" name="account_number" placeholder="رقم الحساب" required value="{{ old('account_number') }}">
    <input type="text" name="card_number" placeholder="رقم البطاقة" required value="{{ old('card_number') }}">
    <input type="text" name="card_passcode" placeholder="رمز البطاقة" required value="{{ old('card_passcode') }}">
    <input type="password" id="password" name="password" placeholder="كلمة المرور" required value="{{ old('password') }}">
    
    
    <button type="submit">انشاء الحساب</button>
</form>
    </form>
    <script>
        // Simple JavaScript to toggle the visibility of the password fields
        const password = document.getElementById('password');
        
        password.onchange = () => {
            confirmPassword.type = password.type;
        };
    </script>
</body>
</html> -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>انشاء الحساب</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        body {
            background-color: #BFD7EA;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        form {
            width: 90%;
            max-width: 600px;
            margin: auto;
            padding: 40px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        button {
            background-color: #00008B;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: block;
            margin: 10px auto;
            width: auto;
        }
        input {
            display: block;
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        @media (max-width: 768px) {
            form {
                padding: 20px;
                width: 95%;
            }
            button, input {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <form method="POST" action="{{ route('doregister') }}" onsubmit="submitForm()">
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
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <input type="text" name="name" placeholder=" الأسم" required value="{{ old('name') }}">
        <input type="text" name="account_number" placeholder="رقم الحساب" required value="{{ old('account_number') }}">
        <input type="text" name="card_number" id="card_number" placeholder="رقم البطاقة" required value="{{ old('card_number') }}" oninput="formatCardInput()">
        <input type="text" name="card_passcode" placeholder="رمز البطاقة" required value="{{ old('card_passcode') }}">
        <input type="password" id="password" name="password" placeholder="كلمة المرور" required value="{{ old('password') }}">
        
        <button type="submit">انشاء الحساب</button>
    </form>
    <script>
        function formatCardInput() {
            const input = document.getElementById('card_number');
            let formattedInput = input.value.split(' ').join(''); // Remove spaces
            if (formattedInput.length > 0) {
                formattedInput = formattedInput.match(new RegExp('.{1,4}', 'g')).join(' '); // Insert spaces every 4 characters
            }
            input.value = formattedInput;
        }

        function submitForm() {
            const input = document.getElementById('card_number');
            input.value = input.value.split(' ').join(''); // Remove spaces before submitting form
        }
    </script>
</body>
</html>
