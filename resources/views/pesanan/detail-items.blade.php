<div class="mt-8">
    <h3 class="text-lg font-bold mb-4 text-gray-100">Daftar Item Pesanan</h3>
    <div class="overflow-x-auto">
        <table class="filament-tables-table w-full text-sm text-left text-gray-200 dark:text-gray-100">
            <thead class="bg-gray-800 dark:bg-gray-900">
                <tr>
                    <th class="filament-tables-header-cell px-4 py-2 border-b border-gray-700">Produk</th>
                    <th class="filament-tables-header-cell px-4 py-2 border-b border-gray-700">Campuran Bumbu</th>
                    <th class="filament-tables-header-cell px-4 py-2 border-b border-gray-700">Harga Total</th>
                </tr>
            </thead>
            <tbody class="bg-gray-900">
                @foreach($items as $item)
                    <tr>
                        <td class="filament-tables-cell px-4 py-2 border-b border-gray-800">{{ $item->produk->nama ?? '-' }}</td>
                        <td class="filament-tables-cell px-4 py-2 border-b border-gray-800">
                            @php
                                $campuran = is_string($item->komponen_bumbu) ? json_decode($item->komponen_bumbu, true) : $item->komponen_bumbu;
                            @endphp
                            @if(is_array($campuran) && count($campuran))
                                <ul class="list-disc ml-4">
                                    @foreach($campuran as $b)
                                        <li class="mb-1">{{ $b['nama'] ?? '-' }} ({{ $b['jumlah'] ?? '-' }} {{ $b['satuan'] ?? '-' }}, Rp {{ isset($b['harga']) ? number_format($b['harga'], 0, ',', '.') : '-' }})</li>
                                    @endforeach
                                </ul>
                            @else
                                -
                            @endif
                        </td>
                        <td class="filament-tables-cell px-4 py-2 border-b border-gray-800 text-right font-semibold">
                            @php
                                $hargaUtama = $item->harga * $item->quantity;
                                $totalCampuran = 0;
                                $campuran = is_string($item->komponen_bumbu) ? json_decode($item->komponen_bumbu, true) : ($item->komponen_bumbu ?? []);
                                if (is_array($campuran)) {
                                    $totalCampuran = collect($campuran)->sum(function($b) {
                                        return isset($b['subtotal']) ? $b['subtotal'] : ((($b['harga'] ?? 0) * ($b['jumlah'] ?? 1)));
                                    });
                                }
                                $totalSemua = $hargaUtama + $totalCampuran;
                            @endphp
                            Rp {{ number_format($totalSemua, 0, ',', '.') }}
                            <div class="text-xs text-gray-400 text-left mt-1">
                                <div>Harga utama: <span class="float-right">Rp {{ number_format($hargaUtama, 0, ',', '.') }}</span></div>
                                @if(is_array($campuran) && count($campuran))
                                    <div class="mt-1">Campuran:
                                        <ul class="ml-2">
                                            @foreach($campuran as $b)
                                                <li>
                                                    {{ $b['nama'] ?? '-' }} ({{ $b['jumlah'] ?? '-' }} {{ $b['satuan'] ?? '-' }}):
                                                    Rp {{ isset($b['subtotal']) ? number_format($b['subtotal'], 0, ',', '.') : (isset($b['harga']) ? number_format($b['harga'] * ($b['jumlah'] ?? 1), 0, ',', '.') : '-') }}
                                                </li>
                                            @endforeach
                                        </ul>
                                        <div class="mt-1">Total campuran: <span class="float-right">Rp {{ number_format($totalCampuran, 0, ',', '.') }}</span></div>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div> 