<?php

namespace Database\Factories;

use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notifikasi>
 */
class NotifikasiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory()->create();
        
        return [
            'id' => Str::uuid(),
            'tipe' => 'pesanan_status',
            'data' => [
                'title' => $this->faker->sentence(),
                'message' => $this->faker->paragraph(),
                'pesanan_id' => $this->faker->numberBetween(1, 100),
                'kode_pesanan' => 'ORD-' . Str::upper(Str::random(8)),
                'status' => $this->faker->randomElement(['pending', 'processing', 'dikirim', 'completed', 'cancelled']),
                'total_harga' => $this->faker->numberBetween(10000, 500000),
                'total_harga_formatted' => 'Rp ' . number_format($this->faker->numberBetween(10000, 500000), 0, ',', '.'),
                'total_items' => $this->faker->numberBetween(1, 10),
                'produk_list' => [
                    [
                        'nama' => $this->faker->words(2, true),
                        'quantity' => $this->faker->numberBetween(1, 5),
                        'harga' => $this->faker->numberBetween(5000, 50000),
                        'subtotal' => $this->faker->numberBetween(5000, 250000),
                        'komponen_bumbu' => null,
                    ]
                ],
                'metode_pembayaran' => $this->faker->randomElement(['online_midtrans', 'cod']),
                'alamat_pengiriman' => $this->faker->address(),
                'created_at' => $this->faker->dateTimeBetween('-1 month', 'now')->format('d/m/Y H:i'),
            ],
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $user->id,
            'dibaca_pada' => $this->faker->optional(0.3)->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the notification is unread.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'dibaca_pada' => null,
        ]);
    }

    /**
     * Indicate that the notification is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'dibaca_pada' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }
} 