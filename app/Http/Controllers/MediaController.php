<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMediaRequest;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MediaController extends Controller
{
    /**
     * Display a paginated listing of media, with optional title and type filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max((int) $request->query('per_page', 10), 1), 100);

        $media = Media::query()
            ->with('users')
            ->withCount('users')
            ->search($request->query('title'))
            ->filterType($request->query('type'))
            ->orderBy('id')
            ->paginate($perPage);

        return MediaResource::collection($media);
    }

    /**
     * Store a newly created media item in storage.
     */
    public function store(StoreMediaRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (($data['type'] ?? null) === 'anime') {
            $data['country'] = null;
        }

        $media = Media::create($data);

        return (new MediaResource($media))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified media item along with users who saved it.
     */
    public function show(Media $media): MediaResource
    {
        $media->load('users')->loadCount('users');

        return new MediaResource($media);
    }

    /**
     * Update the specified media item in storage.
     */
    public function update(StoreMediaRequest $request, Media $media): MediaResource
    {
        $data = $request->validated();
        if (($data['type'] ?? $media->type) === 'anime') {
            $data['country'] = null;
        }

        $media->update($data);
        $media->load('users')->loadCount('users');

        return new MediaResource($media);
    }

    /**
     * Remove the specified media item from storage.
     */
    public function destroy(Media $media): JsonResponse
    {
        $media->delete();

        return response()->json([
            'message' => 'Media berhasil dihapus',
        ]);
    }
}
