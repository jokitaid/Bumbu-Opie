@extends('filament::page')

@section('content')
    <div class="space-y-6">
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-bold mb-4">Detail Keranjang</h2>
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div><strong>Pengguna:</strong> {{ $record->user->name ?? '-' }}</div>
                <div><strong>Produk Utama:</strong> {{ $record->produk->nama ?? '-' }}</div>
                <div><strong>Jumlah:</strong> {{ $record->quantity }}</div>
            </div>
            <h3 class="text-md font-semibold mt-4 mb-2">Campuran Bumbu</h3>
            @if($komponenBumbu->isEmpty())
                <div class="text-gray-500">Tidak ada campuran bumbu.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 border-b">Nama Bumbu</th>
                                <th class="px-4 py-2 border-b">Jumlah</th>
                                <th class="px-4 py-2 border-b">Satuan</th>
                                <th class="px-4 py-2 border-b">Detail Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($komponenBumbu as $bumbu)
                                <tr>
                                    <td class="px-4 py-2 border-b">{{ $bumbu['nama'] }}</td>
                                    <td class="px-4 py-2 border-b">{{ $bumbu['jumlah'] }}</td>
                                    <td class="px-4 py-2 border-b">{{ $bumbu['satuan'] }}</td>
                                    <td class="px-4 py-2 border-b">{{ $bumbu['detail_satuan'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection 