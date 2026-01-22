<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchTranslationRequest;
use App\Http\Requests\StoreTranslationRequest;
use App\Http\Requests\UpdateTranslationRequest;
use App\Http\Resources\TranslationCollection;
use App\Http\Resources\TranslationResource;
use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for translation CRUD and search operations.
 */
class TranslationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly TranslationService $translationService
    ) {}

    /**
     * Display a listing of translations.
     *
     * @OA\Get(
     *     path="/api/translations",
     *     summary="List all translations",
     *     tags={"Translations"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function index(Request $request): TranslationCollection
    {
        $perPage = (int) $request->input('per_page', 15);
        $translations = $this->translationService->paginate($perPage);

        return new TranslationCollection($translations);
    }

    /**
     * Store a newly created translation.
     *
     * @OA\Post(
     *     path="/api/translations",
     *     summary="Create a new translation",
     *     tags={"Translations"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true),
     *     @OA\Response(response=201, description="Created")
     * )
     */
    public function store(StoreTranslationRequest $request): JsonResponse
    {
        $translation = $this->translationService->create(
            $request->only(['key', 'locale', 'value']),
            $request->input('tags', [])
        );

        return (new TranslationResource($translation))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified translation.
     *
     * @OA\Get(
     *     path="/api/translations/{id}",
     *     summary="Get a translation by ID",
     *     tags={"Translations"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $translation = $this->translationService->find($id);

        if (!$translation) {
            return response()->json([
                'message' => 'Translation not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return (new TranslationResource($translation))->response();
    }

    /**
     * Update the specified translation.
     *
     * @OA\Put(
     *     path="/api/translations/{id}",
     *     summary="Update a translation",
     *     tags={"Translations"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true),
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function update(UpdateTranslationRequest $request, int $id): JsonResponse
    {
        try {
            $translation = $this->translationService->update(
                $id,
                $request->only(['key', 'locale', 'value']),
                $request->has('tags') ? $request->input('tags') : null
            );

            return (new TranslationResource($translation))->response();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'message' => 'Translation not found.',
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Remove the specified translation.
     *
     * @OA\Delete(
     *     path="/api/translations/{id}",
     *     summary="Delete a translation",
     *     tags={"Translations"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="No content"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->translationService->delete($id);

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'message' => 'Translation not found.',
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Search translations by filters.
     *
     * @OA\Get(
     *     path="/api/translations/search",
     *     summary="Search translations",
     *     tags={"Translations"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="key", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="locale", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="content", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="tags", in="query", @OA\Schema(type="array", @OA\Items(type="integer"))),
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function search(SearchTranslationRequest $request): TranslationCollection
    {
        $translations = $this->translationService->search(
            $request->filters(),
            $request->perPage()
        );

        return new TranslationCollection($translations);
    }
}
