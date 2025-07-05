<?php

return [
    'resource' => 'Sumber Daya',
    'resources' => 'Sumber Daya',
    'create' => 'Buat :Label',
    'edit' => 'Ubah :Label',
    'view' => 'Lihat :Label',
    'delete' => 'Hapus :Label',
    'delete_selected' => 'Hapus yang dipilih',
    'restore' => 'Pulihkan :Label',
    'restore_selected' => 'Pulihkan yang dipilih',
    'force_delete' => 'Paksa Hapus :Label',
    'force_delete_selected' => 'Paksa Hapus yang dipilih',
    'reorder' => 'Urutkan Ulang :Label',
    'show_all_records' => 'Tampilkan Semua Data',
    'messages' => [
        'created' => 'Berhasil dibuat',
        'saved' => 'Berhasil disimpan',
        'deleted' => 'Berhasil dihapus',
        'restored' => 'Berhasil dipulihkan',
        'force_deleted' => 'Berhasil dihapus secara paksa',
        'reordered' => 'Berhasil diurutkan ulang',
        'copied' => 'Berhasil disalin',
    ],
    'table' => [
        'columns' => [
            'id' => 'ID',
            'created_at' => 'Dibuat pada',
            'updated_at' => 'Diperbarui pada',
            'deleted_at' => 'Dihapus pada',
        ],
        'filters' => [
            'is_enabled' => 'Diaktifkan',
        ],
        'actions' => [
            'edit' => 'Ubah',
            'view' => 'Lihat',
            'delete' => 'Hapus',
            'restore' => 'Pulihkan',
            'force_delete' => 'Paksa Hapus',
        ],
    ],
    'form' => [
        'actions' => [
            'save' => 'Simpan',
            'cancel' => 'Batal',
            'delete' => 'Hapus',
        ],
    ],
    'validation' => [
        'unique' => ':Attribute sudah ada.',
    ],
]; 