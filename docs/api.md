# Dokumentasi API Bumbu Dapur

## Base URL
```
http://localhost:8000/api
```

## Autentikasi
Semua endpoint API memerlukan autentikasi menggunakan token Bearer. Token dapat diperoleh melalui endpoint login.

### Header yang Diperlukan
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

## Endpoints

### Autentikasi

#### Login
```http
POST /login
```

**Request Body:**
```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

**Response Success (200):**
```json
{
    "status": "success",
    "message": "Login berhasil",
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com",
            "phone": "081234567890",
            "address": "Jl. Contoh No. 123",
            "role": "pengguna"
        }
    }
}
```

#### Register
```http
POST /register
```

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "user@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "081234567890",
    "address": "Jl. Contoh No. 123"
}
```

**Response Success (201):**
```json
{
    "status": "success",
    "message": "Registrasi berhasil",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com",
            "phone": "081234567890",
            "address": "Jl. Contoh No. 123",
            "role": "pengguna"
        }
    }
}
```

#### Logout
```http
POST /logout
```

**Response Success (200):**
```json
{
    "status": "success",
    "message": "Logout berhasil"
}
```

### Produk

#### Daftar Produk
```http
GET /products
```

**Query Parameters:**
- `search` (optional): Pencarian produk
- `category` (optional): Filter berdasarkan kategori
- `sort` (optional): Pengurutan (price_asc, price_desc, newest)
- `page` (optional): Halaman yang ingin ditampilkan

**Response Success (200):**
```json
{
    "status": "success",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "name": "Bumbu Nasi Goreng",
                "description": "Bumbu nasi goreng instan",
                "price": 15000,
                "stock": 100,
                "image": "products/bumbu-nasi-goreng.jpg",
                "category": {
                    "id": 1,
                    "name": "Bumbu Masak"
                }
            }
        ],
        "total": 50,
        "per_page": 10
    }
}
```

#### Detail Produk
```http
GET /products/{id}
```

**Response Success (200):**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "name": "Bumbu Nasi Goreng",
        "description": "Bumbu nasi goreng instan",
        "price": 15000,
        "stock": 100,
        "image": "products/bumbu-nasi-goreng.jpg",
        "category": {
            "id": 1,
            "name": "Bumbu Masak"
        },
        "reviews": [
            {
                "id": 1,
                "user": {
                    "id": 1,
                    "name": "John Doe"
                },
                "rating": 5,
                "comment": "Produk sangat bagus",
                "created_at": "2024-03-21T10:00:00.000000Z"
            }
        ]
    }
}
```

### Kategori

#### Daftar Kategori
```http
GET /categories
```

**Response Success (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "name": "Bumbu Masak",
            "description": "Berbagai bumbu masak instan",
            "image": "categories/bumbu-masak.jpg"
        }
    ]
}
```

### Keranjang

#### Tambah ke Keranjang
```http
POST /cart
```

**Request Body:**
```json
{
    "product_id": 1,
    "quantity": 2
}
```

**Response Success (200):**
```json
{
    "status": "success",
    "message": "Produk berhasil ditambahkan ke keranjang",
    "data": {
        "id": 1,
        "product": {
            "id": 1,
            "name": "Bumbu Nasi Goreng",
            "price": 15000,
            "image": "products/bumbu-nasi-goreng.jpg"
        },
        "quantity": 2,
        "subtotal": 30000
    }
}
```

#### Lihat Keranjang
```http
GET /cart
```

**Response Success (200):**
```json
{
    "status": "success",
    "data": {
        "items": [
            {
                "id": 1,
                "product": {
                    "id": 1,
                    "name": "Bumbu Nasi Goreng",
                    "price": 15000,
                    "image": "products/bumbu-nasi-goreng.jpg"
                },
                "quantity": 2,
                "subtotal": 30000
            }
        ],
        "total": 30000
    }
}
```

### Pesanan

#### Buat Pesanan
```http
POST /orders
```

**Request Body:**
```json
{
    "address": "Jl. Contoh No. 123",
    "phone": "081234567890",
    "payment_method": "transfer",
    "items": [
        {
            "product_id": 1,
            "quantity": 2
        }
    ]
}
```

**Response Success (201):**
```json
{
    "status": "success",
    "message": "Pesanan berhasil dibuat",
    "data": {
        "id": 1,
        "order_number": "ORD-20240321-001",
        "status": "pending",
        "total": 30000,
        "address": "Jl. Contoh No. 123",
        "phone": "081234567890",
        "payment_method": "transfer",
        "items": [
            {
                "product": {
                    "id": 1,
                    "name": "Bumbu Nasi Goreng",
                    "price": 15000,
                    "image": "products/bumbu-nasi-goreng.jpg"
                },
                "quantity": 2,
                "subtotal": 30000
            }
        ],
        "created_at": "2024-03-21T10:00:00.000000Z"
    }
}
```

#### Daftar Pesanan
```http
GET /orders
```

**Response Success (200):**
```json
{
    "status": "success",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "order_number": "ORD-20240321-001",
                "status": "pending",
                "total": 30000,
                "created_at": "2024-03-21T10:00:00.000000Z"
            }
        ],
        "total": 5,
        "per_page": 10
    }
}
```

## Kode Status

- `200 OK`: Request berhasil
- `201 Created`: Resource berhasil dibuat
- `400 Bad Request`: Request tidak valid
- `401 Unauthorized`: Tidak terautentikasi
- `403 Forbidden`: Tidak memiliki akses
- `404 Not Found`: Resource tidak ditemukan
- `422 Unprocessable Entity`: Validasi gagal
- `500 Internal Server Error`: Kesalahan server

## Error Response
```json
{
    "status": "error",
    "message": "Pesan error",
    "errors": {
        "field": [
            "Pesan error untuk field"
        ]
    }
}
``` 