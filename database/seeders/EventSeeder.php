<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Event::create([
            'name' => 'Pameran Foto Komunitas',
            'description' => 'Pameran karya fotografer lokal dan demo editing live.',
            'location' => 'Jakarta Convention Center',
            'start_date' => now()->addDays(7)->setTime(10,0),
            'end_date' => now()->addDays(7)->setTime(17,0),
            'is_public' => true,
        ]);

        Event::create([
            'name' => 'Workshop Fotografi Malam',
            'description' => 'Belajar long exposure dan teknik pemotretan malam hari.',
            'location' => 'Kota Tua, Jakarta',
            'start_date' => now()->addDays(14)->setTime(18,30),
            'end_date' => now()->addDays(14)->setTime(21,30),
            'is_public' => true,
        ]);
    }
}
