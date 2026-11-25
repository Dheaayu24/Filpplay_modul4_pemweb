<?php

namespace Database\Seeders;

use App\Models\Media;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{
    /**
     * Seed initial FlipPlay media catalog (matching Modul 3 + additional samples).
     */
    public function run(): void
    {
        $items = [
            [
                'title' => 'Frieren: Beyond Journey End',
                'type' => 'anime',
                'genre' => 'Fantasy',
                'year' => 2023,
                'episodes' => 28,
                'country' => null,
            ],
            [
                'title' => 'Haikyuu!!',
                'type' => 'anime',
                'genre' => 'Sports',
                'year' => 2014,
                'episodes' => 25,
                'country' => null,
            ],
            [
                'title' => 'Queen of Tears',
                'type' => 'drama',
                'genre' => 'Romance',
                'year' => 2024,
                'episodes' => 16,
                'country' => 'Korea Selatan',
            ],
            [
                'title' => 'Moving',
                'type' => 'drama',
                'genre' => 'Action',
                'year' => 2023,
                'episodes' => 20,
                'country' => 'Korea Selatan',
            ],
            [
                'title' => 'Attack on Titan',
                'type' => 'anime',
                'genre' => 'Action',
                'year' => 2013,
                'episodes' => 25,
                'country' => null,
            ],
            [
                'title' => 'Crash Landing on You',
                'type' => 'drama',
                'genre' => 'Romance',
                'year' => 2019,
                'episodes' => 16,
                'country' => 'Korea Selatan',
            ],
        ];

        foreach ($items as $item) {
            Media::query()->firstOrCreate(
                ['title' => $item['title']],
                $item
            );
        }
    }
}
