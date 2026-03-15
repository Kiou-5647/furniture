<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: system-ui, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Welcome to {{ config('app.name') }}</h1>

        <p>Your employee account has been created.</p>

        <p><strong>Email:</strong> {{ $user->email }}</p>

        <p>Please click the button below to set your password:</p>

        <p>
            <a href="{{ $resetUrl }}" class="btn">Set Password</a>
        </p>

        <p>If you did not expect this email, please contact your administrator.</p>

        <p>Thanks,<br>{{ config('app.name') }}</p>
    </div>
</body>

</html>
