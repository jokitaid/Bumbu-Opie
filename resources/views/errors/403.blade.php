<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak</title>
    <script>
        setTimeout(function() {
            window.location.href = "/admin/login";
        }, 5000);
    </script>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f8f9fa;
        }
        .error-container {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #dc3545;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        p {
            color: #6c757d;
            margin-bottom: 1rem;
        }
        .countdown {
            color: #28a745;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>Akses Ditolak</h1>
        <p>Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.</p>
        <p>Anda akan diarahkan ke halaman login dalam <span class="countdown">5</span> detik...</p>
    </div>
    <script>
        let count = 5;
        const countdownElement = document.querySelector('.countdown');
        const countdownInterval = setInterval(() => {
            count--;
            countdownElement.textContent = count;
            if (count <= 0) {
                clearInterval(countdownInterval);
            }
        }, 1000);
    </script>
</body>
</html> 