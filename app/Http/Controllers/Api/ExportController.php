<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for exporting translations as JSON.
 */
class ExportController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly ExportService $exportService
    ) {}

    /**
     * Export all translations as JSON grouped by locale and key.
     *
     * This endpoint is optimized for handling 100k+ records
     * with response time under 500ms.
     *
     * @OA\Get(
     *     path="/api/export",
     *     summary="Export all translations",
     *     tags={"Export"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="locale", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function export(Request $request): JsonResponse
    {
        $locale = $request->input('locale');

        if ($locale) {
            $data = $this->exportService->exportByLocale($locale);
        } else {
            $data = $this->exportService->export();
        }

        return response()->json([
            'data' => $data,
        ]);
    }
}
