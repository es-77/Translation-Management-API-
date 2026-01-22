<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Http\Resources\TagResource;
use App\Services\TagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for tag CRUD operations.
 */
class TagController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly TagService $tagService
    ) {}

    /**
     * Display a listing of tags.
     *
     * @OA\Get(
     *     path="/api/tags",
     *     summary="List all tags",
     *     tags={"Tags"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function index(): AnonymousResourceCollection
    {
        return TagResource::collection($this->tagService->all());
    }

    /**
     * Store a newly created tag.
     *
     * @OA\Post(
     *     path="/api/tags",
     *     summary="Create a new tag",
     *     tags={"Tags"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true),
     *     @OA\Response(response=201, description="Created")
     * )
     */
    public function store(StoreTagRequest $request): JsonResponse
    {
        $tag = $this->tagService->create($request->validated());

        return (new TagResource($tag))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified tag.
     *
     * @OA\Get(
     *     path="/api/tags/{id}",
     *     summary="Get a tag by ID",
     *     tags={"Tags"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $tag = $this->tagService->find($id);

        if (!$tag) {
            return response()->json([
                'message' => 'Tag not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return (new TagResource($tag))->response();
    }

    /**
     * Update the specified tag.
     *
     * @OA\Put(
     *     path="/api/tags/{id}",
     *     summary="Update a tag",
     *     tags={"Tags"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true),
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function update(UpdateTagRequest $request, int $id): JsonResponse
    {
        try {
            $tag = $this->tagService->update($id, $request->validated());

            return (new TagResource($tag))->response();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'message' => 'Tag not found.',
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Remove the specified tag.
     *
     * @OA\Delete(
     *     path="/api/tags/{id}",
     *     summary="Delete a tag",
     *     tags={"Tags"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="No content"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->tagService->delete($id);

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'message' => 'Tag not found.',
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
