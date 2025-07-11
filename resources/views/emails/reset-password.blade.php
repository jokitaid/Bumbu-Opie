<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Bumbu Opie</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #ff6b6b, #ff8e53);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
        }
        .message {
            font-size: 16px;
            margin-bottom: 30px;
            color: #666;
        }
        .reset-button {
            display: inline-block;
            background: linear-gradient(135deg, #ff6b6b, #ff8e53);
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            transition: all 0.3s ease;
        }
        .reset-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }
        .token-info {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .token-info h3 {
            margin: 0 0 10px 0;
            color: #495057;
            font-size: 16px;
        }
        .token-code {
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            word-break: break-all;
            color: #495057;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .footer a {
            color: #ff6b6b;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        .expiry-info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛍️ Bumbu Opie</h1>
            <p>Reset Password</p>
        </div>
        
        <div class="content">
            <div class="greeting">
                Halo <strong>{{ $userName }}</strong>! 👋
            </div>
            
            <div class="message">
                Kami menerima permintaan untuk mereset password akun Bumbu Opie Anda. 
                Jika Anda tidak melakukan permintaan ini, Anda dapat mengabaikan email ini.
            </div>
            
            <div class="expiry-info">
                ⏰ <strong>Penting:</strong> Link reset password ini akan kadaluarsa dalam 60 menit.
            </div>
            
            <div style="text-align: center;">
                <a href="{{ $resetUrl }}" class="reset-button">
                    🔐 Reset Password
                </a>
            </div>
            
            <div class="token-info">
                <h3>🔑 Token Reset Password:</h3>
                <div class="token-code">{{ $token }}</div>
                <p style="margin: 10px 0 0 0; font-size: 14px; color: #6c757d;">
                    Gunakan token di atas jika link tidak berfungsi
                </p>
            </div>
            
            <div class="warning">
                ⚠️ <strong>Keamanan:</strong> Jangan bagikan token ini kepada siapapun. 
                Tim Bumbu Opie tidak akan pernah meminta token ini melalui email atau telepon.
            </div>
            
            <div class="message">
                Setelah password berhasil direset, Anda akan otomatis logout dari semua perangkat 
                untuk keamanan akun Anda.
            </div>
        </div>
        
        <div class="footer">
            <p>
                Email ini dikirim dari sistem Bumbu Opie.<br>
                Jika Anda memiliki pertanyaan, silakan hubungi tim support kami.
            </p>
            <p>
                © {{ date('Y') }} Bumbu Opie. Semua hak dilindungi.
            </p>
        </div>
    </div>
</body>
</html> 