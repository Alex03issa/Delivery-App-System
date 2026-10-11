<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<body style="background-color: #f1f7fc; font-family: 'Montserrat', sans-serif; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 40px auto; background-color: #ffffff; padding: 40px; border-radius: 4px; box-shadow: 1px 1px 5px rgba(0, 0, 0, 0.1); color: #505e6c;">

        <!-- Title -->
        <h2 style="text-align: center; color: #055ada; font-weight: 700; font-size: 24px; margin-bottom: 20px;">
            Welcome to Cabs Online
        </h2>

        <!-- Greeting -->
        <p style="text-align: center; font-size: 16px; margin-bottom: 20px;">
            Hello <strong>{{ $user->name }}</strong>,
        </p>

        <!-- Body -->
        <p style="text-align: center; font-size: 15px; margin-bottom: 30px;">
            Thank you for signing up! Please click the button below to verify your email address.
        </p>

        <!-- Button -->
        <div style="text-align: center;">
            <a href="{{ $verificationUrl }}" target="_blank"
               style="display: inline-block; padding: 12px 28px; background-color: #055ada; color: white; border-radius: 4px; text-decoration: none; font-weight: 600;">
                Verify Email
            </a>
        </div>

        <!-- Footer -->
        <p style="text-align: center; font-size: 13px; color: #6f7a85; margin-top: 30px;">
            If you didn’t create an account, you can safely ignore this email.
        </p>

        <p style="text-align: center; font-size: 12px; color: #a0a0a0; margin-top: 10px;">
            &copy; {{ now()->year }} Cabs Online. All rights reserved.
        </p>
    </div>
</body>
</html>
