<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\FAQResource;
use App\Models\FAQ;
use Illuminate\Http\Request;

class FAQController extends Controller
{
    /**
     * Display a listing of the resource with filters
     * Query params: category, keyword, per_page, page
     */
    public function index(Request $request)
    {
        $query = FAQ::active()->sorted();

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->byCategory($request->category);
        }

        // Filter by keyword
        if ($request->has('keyword') && $request->keyword) {
            $query->byKeyword($request->keyword);
        }

        // Search in question
        if ($request->has('search') && $request->search) {
            $query->where('question', 'like', '%'.$request->search.'%')
                  ->orWhere('answer', 'like', '%'.$request->search.'%');
        }

        $per_page = $request->input('per_page', 10);
        $faqs = $query->paginate($per_page);

        return response()->json([
            'success' => true,
            'message' => 'FAQs retrieved successfully',
            'data' => FAQResource::collection($faqs),
            'meta' => [
                'current_page' => $faqs->currentPage(),
                'per_page' => $faqs->perPage(),
                'total' => $faqs->total(),
                'last_page' => $faqs->lastPage(),
            ],
        ]);
    }

    /**
     * Get FAQs by specific category
     */
    public function getByCategory(Request $request, string $category)
    {
        $faqs = FAQ::active()
                   ->byCategory($category)
                   ->sorted()
                   ->paginate($request->input('per_page', 10));

        return response()->json([
            'success' => true,
            'message' => "FAQs for category '{$category}' retrieved successfully",
            'data' => FAQResource::collection($faqs),
            'meta' => [
                'category' => $category,
                'total' => $faqs->total(),
            ],
        ]);
    }

    /**
     * Get all categories
     */
    public function getCategories()
    {
        $categories = FAQ::active()
                        ->distinct()
                        ->pluck('category')
                        ->filter()
                        ->values();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $faq = FAQ::find($id);

        if (! $faq) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ not found',
            ], 404);
        }

        // Increment views
        $faq->increment('views');

        return response()->json([
            'success' => true,
            'data' => new FAQResource($faq),
        ]);
    }
}
