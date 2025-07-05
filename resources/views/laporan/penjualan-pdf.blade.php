<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 11px;
            margin: 32px 36px 24px 36px;
            color: #222;
        }
        .header {
            text-align: center;
            margin-bottom: 18px;
            border-bottom: 3px solid #f59e42;
            padding-bottom: 10px;
        }
        .header .logo {
            width: 38px;
            height: 38px;
            display: inline-block;
            vertical-align: middle;
            margin-right: 8px;
        }
        .header h1 {
            display: inline-block;
            margin: 0;
            font-size: 24px;
            letter-spacing: 1px;
            color: #f59e42;
            vertical-align: middle;
        }
        .header .subtitle {
            margin: 2px 0 0 0;
            color: #666;
            font-size: 13px;
        }
        .header .periode {
            margin: 0;
            color: #444;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            table-layout: fixed;
            word-break: break-word;
        }
        th, td {
            border: 1px solid #e0e0e0;
            padding: 7px 4px;
            font-size: 11px;
        }
        th {
            background-color: #f59e42;
            color: #fff;
            font-weight: bold;
            text-align: center;
        }
        td {
            vertical-align: middle;
        }
        tr:nth-child(even) td {
            background-color: #fdf6ed;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .summary {
            margin-top: 18px;
            padding: 14px 20px;
            background: linear-gradient(90deg, #f59e42 0%, #fff3e0 100%);
            border: 1px solid #f59e42;
            border-radius: 7px;
            width: 60%;
            box-shadow: 0 2px 8px #f59e4222;
        }
        .summary h3 {
            margin: 0 0 8px 0;
            color: #f59e42;
            font-size: 15px;
            letter-spacing: 1px;
        }
        .summary p {
            margin: 4px 0;
            font-size: 12px;
        }
        .summary .icon {
            color: #f59e42;
            font-size: 13px;
            margin-right: 4px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #aaa;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="https://cdn-icons-png.flaticon.com/512/3075/3075977.png" class="logo" alt="Logo">
        <h1>LAPORAN PENJUALAN</h1>
        <div class="subtitle">Bumbu Opie</div>
        <div class="periode">Tanggal Cetak: {{ date('d/m/Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:28px;">No</th>
                <th style="width:60px;">Tanggal</th>
                <th style="width:80px;">Kode Pesanan</th>
                <th style="width:80px;">Pelanggan</th>
                <th style="width:90px;">Produk</th>
                <th style="width:32px;">Jml</th>
                <th style="width:60px;">Total Harga</th>
                <th style="width:36px;">Diskon</th>
                <th style="width:70px;">Metode</th>
                <th style="width:55px;">Status</th>
                <th style="width:70px;">Dibuat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laporan as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                <td class="text-center">{{ $item->kode_pesanan ?? ($item->pesanan->kode_pesanan ?? '-') }}</td>
                <td>{{ $item->nama_pelanggan ?? optional($item->user)->name ?? '-' }}</td>
                <td>{{ $item->nama_produk }}</td>
                <td class="text-center">{{ $item->jumlah_terjual }}</td>
                <td class="text-right">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                <td class="text-center">{{ $item->diskon !== null ? number_format($item->diskon, 2).'%' : '-' }}</td>
                <td class="text-center">{{ $item->metode_pembayaran ?? optional($item->pesanan)->metode_pembayaran ?? '-' }}</td>
                <td class="text-center">{{ ucfirst($item->status) }}</td>
                <td class="text-center">{{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <h3>Ringkasan</h3>
        <p><span class="icon">💰</span><strong>Total Omzet:</strong> Rp {{ number_format($total_omzet, 0, ',', '.') }}</p>
        <p><span class="icon">🛒</span><strong>Total Item Terjual:</strong> {{ number_format($total_item, 0, ',', '.') }}</p>
        <p><span class="icon">📄</span><strong>Jumlah Transaksi:</strong> {{ $laporan->count() }}</p>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} Bumbu Dapur &mdash; Sistem Laporan Penjualan
    </div>
</body>
</html> 