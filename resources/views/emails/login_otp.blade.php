<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login OTP</title>
</head>
<body style="background: #f1f7fc; font-family: Arial, sans-serif; padding: 60px 0;">

    <div style="
        max-width: 480px;
        width: 90%;
        margin: 0 auto;
        background-color: #ffffff;
        padding: 40px;
        border-radius: 4px;
        color: #505e6c;
        box-shadow: 1px 1px 5px rgba(0, 0, 0, 0.1);
        text-align: center;
    ">
        <h2 style="
            margin-top: 5px;
            font-weight: 700;
            font-size: 28px;
            margin-bottom: 30px;
        ">
            Your One-Time Password
        </h2>

        <p style="font-size: 16px; margin-bottom: 20px;">Hello {{ $user->name }},</p>

        <p style="font-size: 16px;">Your login OTP is:</p>

        <h3 style="
            font-size: 32px;
            margin: 20px 0;
            color: #055ada;
            letter-spacing: 4px;
        ">
            {{ $otpCode }}
        </h3>

        <p style="font-size: 14px; margin-bottom: 30px;">
            This code will expire at <strong>{{ $expiresAt }}</strong>.
        </p>

        <p style="font-size: 14px; color: #888;">
            If you didn’t try to log in, you can safely ignore this email.
        </p>

        <p style="margin-top: 40px; font-size: 13px; color: #999;">
            — The Delivery App Team
        </p>
    </div>

</body>
</html>
