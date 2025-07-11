<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kode OTP Reset Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&display=swap');
        body {
            font-family: 'Montserrat', Arial, sans-serif;
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 420px;
            margin: 40px auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px 0 rgba(0, 95, 3, 0.10);
            padding: 36px 28px 28px 28px;
            position: relative;
            overflow: hidden;
        }
        .header {
            text-align: center;
            margin-bottom: 18px;
        }
        .header-logo {
            font-size: 38px;
            margin-bottom: 8px;
            animation: bounce 1.2s infinite alternate;
        }
        @keyframes bounce {
            0% { transform: translateY(0); }
            100% { transform: translateY(-8px); }
        }
        .title {
            font-size: 22px;
            font-weight: 700;
            color: #005F03;
            margin-bottom: 6px;
        }
        .info {
            color: #444;
            font-size: 15px;
            margin-bottom: 18px;
            text-align: center;
        }
        .otp {
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 14px;
            color: #fff;
            background: #005F03;
            border-radius: 12px;
            padding: 18px 0;
            margin: 24px 0 18px 0;
            text-align: center;
            box-shadow: 0 2px 12px rgba(0, 95, 3, 0.15);
            animation: pulse 1.2s infinite;
            transition: box-shadow 0.3s;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(0,95,3,0.18); }
            70% { box-shadow: 0 0 0 12px rgba(0,95,3,0.08); }
            100% { box-shadow: 0 0 0 0 rgba(0,95,3,0.18); }
        }
        .warning {
            background: #fffde7;
            color: #8D6E00;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 14px;
            margin-bottom: 18px;
            border: 1px solid #FFF9C4;
            text-align: center;
        }
        .footer {
            color: #aaa;
            font-size: 13px;
            text-align: center;
            margin-top: 32px;
        }
        @media (max-width: 500px) {
            .container { padding: 18px 4vw 18px 4vw; }
            .otp { font-size: 28px; letter-spacing: 8px; padding: 12px 0; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-logo">🛍️</div>
            <div class="title">Kode OTP Reset Password</div>
        </div>
        <div class="info">
            Halo <b>{{ $userName }}</b>,<br>
            Berikut adalah kode OTP untuk reset password akun Bumbu Opie Anda.<br>
            <b>Kode berlaku 10 menit.</b>
        </div>
        <div class="otp">{{ $otp }}</div>
        <div class="warning">
            Jangan bagikan kode ini ke siapapun.<br>
            Jika Anda tidak meminta reset password, abaikan email ini.
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Bumbu Opie
        </div>
    </div>
</body>
</html> 