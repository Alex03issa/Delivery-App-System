<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Password Reset Email</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet" />

    <style>
        body {
            background-color: #f1f7fc;
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
        }

        .email-container {
            max-width: 600px;
            width: 90%;
            margin: 50px auto;
            background-color: #ffffff;
            padding: 40px;
            border-radius: 4px;
            box-shadow: 1px 1px 5px rgba(0, 0, 0, 0.1);
            color: #505e6c;
        }

        .email-title {
            font-size: 26px;
            font-weight: 700;
            text-align: center;
            color: #3a3a3a;
            margin-bottom: 20px;
        }

        .email-greeting,
        .email-text {
            font-size: 16px;
            text-align: center;
            color: #666;
            margin-bottom: 20px;
        }

        .btn-reset {
            display: inline-block;
            background-color: #fed136;
            color: #000;
            padding: 12px 28px;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            text-decoration: none;
            margin: 0 auto;
            transition: background-color 0.3s ease;
        }

        .btn-reset:hover {
            background-color: #fdd100;
        }

        .email-footer {
            font-size: 13px;
            color: #999999;
            text-align: center;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-title">
            Welcome to our Delivery App
        </div>

        <div class="email-greeting">
            Hello {{ $user->name }},
        </div>

        <div class="email-text">
            You requested a password reset. Click the button below to reset your password:
        </div>

        <div style="text-align: center; margin-bottom: 30px;">
            <a class="btn-reset" href="{{ $resetUrl }}" target="_blank">
                Reset Password
            </a>
        </div>

        <div class="email-footer">
            <p>If you did not request this, you can safely ignore this email.<br></p>
            <p>&copy; 2025 Delivery App. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
