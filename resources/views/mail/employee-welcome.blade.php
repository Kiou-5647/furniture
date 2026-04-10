<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to {{ config('app.name') }}</title>
</head>
<body style="font-family: sans-serif; color: #333;">
    <h2>Xin chào {{ $name }},</h2>
    
    <p>Tài khoản nhân viên của bạn đã được tạo thành công.</p>

    <table style="margin: 20px 0; border-collapse: collapse;">
        <tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Email:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">{{ $email }}</td></tr>
        <tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Mật khẩu:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">{{ $password }}</td></tr>
    </table>

    <p>Bạn có thể đăng nhập tại đây: <a href="{{ $loginUrl }}">{{ $loginUrl }}</a></p>
    
    <p>Vui lòng đổi mật khẩu ngay sau khi đăng nhập lần đầu.</p>
</body>
</html>
