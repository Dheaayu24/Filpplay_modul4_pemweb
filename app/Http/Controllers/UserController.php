<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCollectionRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\MediaResource;
use App\Http\Resources\UserResource;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of users along with their media collections.
     */
    public function index(): AnonymousResourceCollection
    {
        $users = User::query()
            ->with('media')
            ->withCount('media')
            ->orderBy('id')
            ->paginate(10);

        return UserResource::collection($users);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = $data['password'] ?? 'password';

        $user = User::create($data);

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified user and their saved media collection.
     */
    public function show(User $user): UserResource
    {
        $user->load('media')->loadCount('media');

        return new UserResource($user);
    }

    /**
     * Display the media collection owned by a specific user.
     */
    public function collection(User $user): AnonymousResourceCollection
    {
        $collection = $user->media()->orderBy('media.id')->get();

        return MediaResource::collection($collection);
    }

    /**
     * Add a media item to the user's personal collection.
     */
    public function addToCollection(AddToCollectionRequest $request, User $user): JsonResponse
    {
        $mediaId = (int) $request->validated()['media_id'];

        return DB::transaction(function () use ($user, $mediaId): JsonResponse {
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($lockedUser->media()->where('media_id', $mediaId)->exists()) {
                return response()->json([
                    'message' => 'Media sudah ada di koleksi',
                ], 409);
            }

            $lockedUser->media()->attach($mediaId);
            $lockedUser->load('media')->loadCount('media');

            return response()->json([
                'message' => 'Media berhasil ditambahkan ke koleksi',
                'data' => new UserResource($lockedUser),
            ], 201);
        });
    }

    /**
     * Remove a media item from the user's personal collection.
     */
    public function removeFromCollection(User $user, Media $media): JsonResponse
    {
        if (! $user->media()->where('media_id', $media->id)->exists()) {
            return response()->json([
                'message' => 'Media tidak ditemukan di koleksi pengguna',
            ], 404);
        }

        $user->media()->detach($media->id);

        return response()->json([
            'message' => 'Media berhasil dihapus dari koleksi',
        ]);
    }
}
