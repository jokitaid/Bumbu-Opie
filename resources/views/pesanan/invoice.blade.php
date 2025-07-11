@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto bg-white shadow rounded-lg p-6">
    <h2 class="text-xl font-bold mb-2">INVOICE PESANAN</h2>
    <div class="mb-4 border-b pb-2">
        <div><b>Kode Pesanan:</b> {{ $record->kode_pesanan }}</div>
        <div><b>Pelanggan:</b> {{ $record->user->name ?? '-' }}</div>
        <div><b>Tanggal:</b> {{ $record->created_at->format('d-m-Y H:i') }}</div>
    </div>
    <div>
        <b>Detail Item:</b>
        <ol class="list-decimal ml-6">
            @foreach($record->itemPesanan as $item)
                <li class="mb-2">
                    <div>
                        <b>{{ $item->produk->nama ?? '-' }}</b> ({{ $item->quantity }} {{ $item->produk->satuan ?? '' }})
                        <span class="float-right">Rp {{ number_format($item->harga * $item->quantity, 0, ',', '.') }}</span>
                    </div>
                    @if($item->komponen_bumbu)
                        @php
                            $campuran = is_string($item->komponen_bumbu) ? json_decode($item->komponen_bumbu, true) : $item->komponen_bumbu;
                        @endphp
                        @if(is_array($campuran) && count($campuran))
                            <div class="ml-4 text-sm text-gray-600">
                                Campuran:
                                <ul class="list-disc ml-4">
                                    @foreach($campuran as $b)
                                        <li>
                                            {{ $b['nama'] ?? '-' }} ({{ $b['jumlah'] ?? '-' }} {{ $b['satuan'] ?? '-' }}, Rp {{ isset($b['harga']) ? number_format($b['harga'], 0, ',', '.') : '-' }})
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
    <div class="border-t mt-4 pt-2">
        <div class="flex justify-between">
            <span><b>Total</b></span>
            <span class="font-bold text-lg">Rp {{ number_format($record->total_harga, 0, ',', '.') }}</span>
        </div>
    </div>
</div>
@endsection 