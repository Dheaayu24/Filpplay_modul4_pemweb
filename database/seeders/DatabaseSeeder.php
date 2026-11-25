<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(MediaSeeder::class);

        $dhea = User::query()->firstOrCreate(
            ['email' => 'dhea@flipplay.test'],
            [
                'name' => 'Dhea',
                'password' => 'password',
            ]
        );

        $budi = User::query()->firstOrCreate(
            ['email' => 'budi@flipplay.test'],
            [
                'name' => 'Budi',
                'password' => 'password',
            ]
        );

        $siti = User::query()->firstOrCreate(
            ['email' => 'siti@flipplay.test'],
            [
                'name' => 'Siti',
                'password' => 'password',
            ]
        );

        $frieren = Media::query()->where('title', 'Frieren: Beyond Journey End')->first();
        $haikyuu = Media::query()->where('title', 'Haikyuu!!')->first();
        $queenOfTears = Media::query()->where('title', 'Queen of Tears')->first();

        if ($frieren && $queenOfTears) {
            $dhea->media()->syncWithoutDetaching([$frieren->id, $queenOfTears->id]);
        }

        if ($haikyuu) {
            $budi->media()->syncWithoutDetaching([$haikyuu->id]);
        }

        if ($frieren && $haikyuu) {
            $siti->media()->syncWithoutDetaching([$frieren->id, $haikyuu->id]);
        }
    }
}
