# API Forgot Password - Bumbu Dapur

Dokumentasi lengkap untuk fitur forgot password pada aplikasi Bumbu Dapur.

## Overview

Fitur forgot password memungkinkan pengguna untuk mereset password mereka jika lupa. Proses ini terdiri dari 3 tahap:

1. **Request Reset Password** - Pengguna memasukkan email untuk meminta reset password
2. **Verifikasi Token** - Memverifikasi token reset password (opsional)
3. **Reset Password** - Menggunakan token untuk mereset password

## Base URL

```
https://api.bumbudapur.com/api
```

## Endpoints

### 1. Request Reset Password

**POST** `/forgot-password`

Mengirim email reset password ke alamat email yang terdaftar.

#### Request Body

```json
{
    "email": "user@example.com"
}
```

#### Response Success (200)

```json
{
    "status": "success",
    "message": "Link reset password telah dikirim ke email Anda"
}
```

#### Response Error (404)

```json
{
    "status": "error",
    "message": "Email tidak ditemukan"
}
```

#### Response Error (422)

```json
{
    "status": "error",
    "message": "Validasi gagal",
    "errors": {
        "email": ["Email harus valid"]
    }
}
```

### 2. Verify Reset Token

**POST** `/verify-reset-token`

Memverifikasi token reset password sebelum melakukan reset.

#### Request Body

```json
{
    "email": "user@example.com",
    "token": "reset_token_here"
}
```

#### Response Success (200)

```json
{
    "status": "success",
    "message": "Token valid"
}
```

#### Response Error (400)

```json
{
    "status": "error",
    "message": "Token reset password tidak valid"
}
```

atau

```json
{
    "status": "error",
    "message": "Token reset password sudah expired"
}
```

### 3. Reset Password

**POST** `/reset-password`

Mereset password menggunakan token yang valid.

#### Request Body

```json
{
    "email": "user@example.com",
    "token": "reset_token_here",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

#### Response Success (200)

```json
{
    "status": "success",
    "message": "Password berhasil direset"
}
```

#### Response Error (400)

```json
{
    "status": "error",
    "message": "Token reset password tidak valid"
}
```

#### Response Error (422)

```json
{
    "status": "error",
    "message": "Validasi gagal",
    "errors": {
        "password": ["Password minimal 8 karakter"],
        "password_confirmation": ["Konfirmasi password tidak cocok"]
    }
}
```

## Flow Penggunaan

### 1. User Lupa Password

1. User membuka halaman "Lupa Password"
2. User memasukkan email yang terdaftar
3. Sistem mengirim email dengan link reset password

### 2. User Menerima Email

1. User membuka email dari Bumbu Dapur
2. User melihat token reset password
3. User mengklik link reset password atau copy token

### 3. User Reset Password

1. User membuka halaman reset password
2. User memasukkan token dan password baru
3. Sistem memverifikasi token dan mereset password
4. User otomatis logout dari semua perangkat

## Keamanan

### Token Expiry
- Token reset password berlaku selama **60 menit**
- Setelah expired, user harus request token baru

### Password Requirements
- Password minimal 8 karakter
- Password harus dikonfirmasi

### Auto Logout
- Setelah password berhasil direset, user otomatis logout dari semua perangkat
- Semua token Sanctum dihapus untuk keamanan

### Rate Limiting
- Implementasi rate limiting untuk mencegah spam
- Maksimal 5 request per menit per IP

## Email Template

Email reset password menggunakan template yang menarik dengan:
- Header dengan logo Bumbu Dapur
- Informasi user yang personal
- Link reset password yang mudah diklik
- Token reset password sebagai backup
- Peringatan keamanan
- Informasi expiry time

## Error Handling

### Common Errors

1. **Email tidak ditemukan**
   - Status: 404
   - Message: "Email tidak ditemukan"

2. **Token tidak valid**
   - Status: 400
   - Message: "Token reset password tidak valid"

3. **Token expired**
   - Status: 400
   - Message: "Token reset password sudah expired"

4. **Validasi gagal**
   - Status: 422
   - Message: "Validasi gagal"
   - Errors: Detail error validasi

## Testing

### Test Cases

1. **Valid Email**
   ```bash
   curl -X POST https://api.bumbudapur.com/api/forgot-password \
     -H "Content-Type: application/json" \
     -d '{"email": "user@example.com"}'
   ```

2. **Invalid Email**
   ```bash
   curl -X POST https://api.bumbudapur.com/api/forgot-password \
     -H "Content-Type: application/json" \
     -d '{"email": "invalid-email"}'
   ```

3. **Reset Password**
   ```bash
   curl -X POST https://api.bumbudapur.com/api/reset-password \
     -H "Content-Type: application/json" \
     -d '{
       "email": "user@example.com",
       "token": "your_token_here",
       "password": "newpassword123",
       "password_confirmation": "newpassword123"
     }'
   ```

## Implementation Notes

### Database
- Menggunakan tabel `password_reset_tokens` yang sudah ada
- Token di-hash sebelum disimpan ke database
- Timestamp untuk tracking expiry

### Mail Configuration
- Menggunakan Laravel Mail dengan template Blade
- Fallback ke response token jika email gagal
- Konfigurasi SMTP di `.env`

### Environment Variables
```env
FRONTEND_URL=http://localhost:3000
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@bumbudapur.com
MAIL_FROM_NAME="Bumbu Dapur"
```

## Support

Jika mengalami masalah dengan fitur forgot password, silakan hubungi tim support Bumbu Dapur. 