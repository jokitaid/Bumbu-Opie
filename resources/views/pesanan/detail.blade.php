@extends('filament::page')

@section('content')
    <div class="space-y-6">
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-bold mb-4">Detail Pesanan</h2>
            <table class="min-w-full bg-white border border-gray-200 rounded-lg mb-6">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border-b">Produk Utama</th>
                        <th class="px-4 py-2 border-b">Jumlah</th>
                        <th class="px-4 py-2 border-b">Satuan</th>
                        <th class="px-4 py-2 border-b">Detail Satuan</th>
                        <th class="px-4 py-2 border-b">Campuran Bumbu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td class="px-4 py-2 border-b align-top">{{ $item['produk_utama'] }}</td>
                        <td class="px-4 py-2 border-b align-top">{{ $item['jumlah'] }}</td>
                        <td class="px-4 py-2 border-b align-top">{{ $item['satuan'] }}</td>
                        <td class="px-4 py-2 border-b align-top">{{ $item['detail_satuan'] }}</td>
                        <td class="px-4 py-2 border-b align-top">
                            @if($item['campuran']->isEmpty())
                                <span class="text-gray-500">-</span>
                            @else
                                <table class="min-w-full bg-gray-50 border border-gray-200 rounded">
                                    <thead>
                                        <tr>
                                            <th class="px-2 py-1 border-b">Nama</th>
                                            <th class="px-2 py-1 border-b">Jumlah</th>
                                            <th class="px-2 py-1 border-b">Satuan</th>
                                            <th class="px-2 py-1 border-b">Detail Satuan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($item['campuran'] as $bumbu)
                                        <tr>
                                            <td class="px-2 py-1 border-b">{{ $bumbu['nama'] }}</td>
                                            <td class="px-2 py-1 border-b">{{ $bumbu['jumlah'] }}</td>
                                            <td class="px-2 py-1 border-b">{{ $bumbu['satuan'] }}</td>
                                            <td class="px-2 py-1 border-b">{{ $bumbu['detail_satuan'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection 