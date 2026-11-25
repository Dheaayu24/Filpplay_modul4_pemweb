<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlipPlayApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_all_media_and_filter_by_title(): void
    {
        Media::create([
            'title' => 'Frieren: Beyond Journey End',
            'type' => 'anime',
            'genre' => 'Fantasy',
            'year' => 2023,
            'episodes' => 28,
        ]);

        Media::create([
            'title' => 'Queen of Tears',
            'type' => 'drama',
            'genre' => 'Romance',
            'year' => 2024,
            'episodes' => 16,
            'country' => 'Korea Selatan',
        ]);

        $response = $this->getJson('/api/media');
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.title', 'Frieren: Beyond Journey End');

        $searchResponse = $this->getJson('/api/media?title=Frieren');
        $searchResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Frieren: Beyond Journey End');
    }

    public function test_can_show_media_detail_with_users_and_returns_404_when_missing(): void
    {
        $user = User::factory()->create(['name' => 'Dhea']);
        $media = Media::create([
            'title' => 'Haikyuu!!',
            'type' => 'anime',
            'genre' => 'Sports',
            'year' => 2014,
            'episodes' => 25,
        ]);
        $user->media()->attach($media->id);

        $response = $this->getJson("/api/media/{$media->id}");
        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Haikyuu!!')
            ->assertJsonPath('data.users.0.name', 'Dhea');

        $this->getJson('/api/media/9999')->assertStatus(404);
    }

    public function test_can_create_media_and_validates_empty_or_duplicate_title(): void
    {
        // Happy path: Anime
        $this->postJson('/api/media', [
            'title' => 'Solo Leveling',
            'type' => 'anime',
            'genre' => 'Action',
            'year' => 2024,
            'episodes' => 12,
        ])->assertStatus(201)
            ->assertJsonPath('data.title', 'Solo Leveling');

        // Happy path: Drama
        $this->postJson('/api/media', [
            'title' => 'Moving',
            'type' => 'drama',
            'genre' => 'Action',
            'year' => 2023,
            'episodes' => 20,
            'country' => 'Korea Selatan',
        ])->assertStatus(201)
            ->assertJsonPath('data.country', 'Korea Selatan');

        // Failure path: Empty title -> 422
        $this->postJson('/api/media', [
            'title' => '',
            'type' => 'anime',
            'genre' => 'Action',
            'year' => 2024,
            'episodes' => 12,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        // Failure path: Duplicate title -> 422
        $this->postJson('/api/media', [
            'title' => 'Solo Leveling',
            'type' => 'anime',
            'genre' => 'Action',
            'year' => 2024,
            'episodes' => 12,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['title']);

        // Failure path: Drama without country -> 422
        $this->postJson('/api/media', [
            'title' => 'Lovely Runner',
            'type' => 'drama',
            'genre' => 'Romance',
            'year' => 2024,
            'episodes' => 16,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['country']);
    }

    public function test_can_update_media_partially_and_prevents_duplicate_title(): void
    {
        $mediaA = Media::create([
            'title' => 'Frieren: Beyond Journey End',
            'type' => 'anime',
            'genre' => 'Fantasy',
            'year' => 2023,
            'episodes' => 28,
        ]);

        $mediaB = Media::create([
            'title' => 'Haikyuu!!',
            'type' => 'anime',
            'genre' => 'Sports',
            'year' => 2014,
            'episodes' => 25,
        ]);

        // Partial update without changing title
        $this->patchJson("/api/media/{$mediaA->id}", [
            'episodes' => 30,
        ])->assertStatus(200)
            ->assertJsonPath('data.episodes', 30)
            ->assertJsonPath('data.title', 'Frieren: Beyond Journey End');

        // Update with another media's title -> 422
        $this->patchJson("/api/media/{$mediaB->id}", [
            'title' => 'Frieren: Beyond Journey End',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_can_delete_media(): void
    {
        $media = Media::create([
            'title' => 'Temporary Anime',
            'type' => 'anime',
            'genre' => 'Comedy',
            'year' => 2022,
            'episodes' => 12,
        ]);

        $this->deleteJson("/api/media/{$media->id}")
            ->assertStatus(200)
            ->assertJsonPath('message', 'Media berhasil dihapus');

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    public function test_user_collection_attach_duplicate_conflict_and_detach(): void
    {
        $user = User::factory()->create(['name' => 'Dhea']);
        $media = Media::create([
            'title' => 'Queen of Tears',
            'type' => 'drama',
            'genre' => 'Romance',
            'year' => 2024,
            'episodes' => 16,
            'country' => 'Korea Selatan',
        ]);

        // Add media to user collection -> 201
        $this->postJson("/api/users/{$user->id}/collection", [
            'media_id' => $media->id,
        ])->assertStatus(201)
            ->assertJsonPath('message', 'Media berhasil ditambahkan ke koleksi')
            ->assertJsonPath('data.collection.0.id', $media->id);

        // Adding the same media again -> 409 Conflict
        $this->postJson("/api/users/{$user->id}/collection", [
            'media_id' => $media->id,
        ])->assertStatus(409)
            ->assertJsonPath('message', 'Media sudah ada di koleksi');

        // Adding non-existent media -> 422
        $this->postJson("/api/users/{$user->id}/collection", [
            'media_id' => 9999,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['media_id']);

        // Get user's collection
        $this->getJson("/api/users/{$user->id}/collection")
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Queen of Tears');

        // Remove media from user's collection -> 200
        $this->deleteJson("/api/users/{$user->id}/collection/{$media->id}")
            ->assertStatus(200)
            ->assertJsonPath('message', 'Media berhasil dihapus dari koleksi');

        // Removing again when not in collection -> 404
        $this->deleteJson("/api/users/{$user->id}/collection/{$media->id}")
            ->assertStatus(404);
    }
}
