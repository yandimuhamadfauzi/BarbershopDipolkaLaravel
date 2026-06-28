<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        DB::table('users')->insert([
            'nama'       => 'Administrator',
            'email'      => 'admin@dipolka.com',
            'password'   => Hash::make('admin123'),
            'is_admin'   => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Default layanan
        $layanan = [
            ['nama_layanan' => 'Cukur Rambut',    'emoji' => '✂️',  'harga' => 25000,  'deskripsi' => 'Potong rambut rapi, bersih, dan stilish'],
            ['nama_layanan' => 'Cukur Jenggot',   'emoji' => '🪒',  'harga' => 15000,  'deskripsi' => 'Rapikan jenggot & kumis secara profesional'],
            ['nama_layanan' => 'Creambath',        'emoji' => '💆',  'harga' => 50000,  'deskripsi' => 'Perawatan rambut dengan krim nutrisi premium'],
            ['nama_layanan' => 'Hair Color',       'emoji' => '🎨',  'harga' => 100000, 'deskripsi' => 'Pewarnaan rambut dengan produk berkualitas'],
            ['nama_layanan' => 'Perming',          'emoji' => '💈',  'harga' => 130000, 'deskripsi' => 'Keriting rambut dengan teknik modern'],
        ];

        foreach ($layanan as $item) {
            DB::table('layanan')->insert(array_merge($item, [
                'aktif'      => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
