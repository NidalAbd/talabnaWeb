<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\Sub_categories;
use App\Services\DalleImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AiImageController extends Controller
{
    protected DalleImageService $dalleService;

    public function __construct(DalleImageService $dalleService)
    {
        $this->dalleService = $dalleService;
    }

    /**
     * Generate AI image for a single category.
     */
    public function generateCategory(int $id): RedirectResponse
    {
        $category = Categories::findOrFail($id);
        $name = $category->name['en'] ?? $category->name['ar'] ?? 'Category';

        $success = $this->dalleService->generateForCategory($category);

        if ($success) {
            return redirect()->back()->with('success', "AI image generated for \"{$name}\".");
        }

        return redirect()->back()->with('error', "Failed to generate AI image for \"{$name}\". Check logs for details.");
    }

    /**
     * Generate AI image for a single subcategory.
     */
    public function generateSubcategory(int $id): RedirectResponse
    {
        $subcategory = Sub_categories::with('category')->findOrFail($id);
        $name = $subcategory->name['en'] ?? $subcategory->name['ar'] ?? 'Subcategory';

        $success = $this->dalleService->generateForSubcategory($subcategory);

        if ($success) {
            return redirect()->back()->with('success', "AI image generated for \"{$name}\".");
        }

        return redirect()->back()->with('error', "Failed to generate AI image for \"{$name}\". Check logs for details.");
    }

    /**
     * Start or continue batch generation for all categories missing photos.
     */
    public function generateAllCategories(Request $request): RedirectResponse
    {
        // If continuing batch, process the next item
        if ($request->has('continue') && session()->has('ai_batch_ids')) {
            return $this->processNextBatchItem('category');
        }

        // Start new batch: find all categories without photos
        $ids = Categories::whereDoesntHave('photos')
            ->pluck('id')
            ->toArray();

        if (empty($ids)) {
            return redirect()->back()->with('success', 'All categories already have images!');
        }

        // Store batch info in session
        session([
            'ai_batch_ids' => $ids,
            'ai_batch_type' => 'category',
            'ai_batch_total' => count($ids),
            'ai_batch_done' => 0,
            'ai_batch_errors' => [],
            'ai_batch_completed' => [],
            'ai_batch_started_at' => now()->toISOString(),
        ]);

        return redirect()->route('ai-image.status');
    }

    /**
     * Start or continue batch generation for all subcategories missing photos.
     */
    public function generateAllSubcategories(Request $request): RedirectResponse
    {
        // If continuing batch, process the next item
        if ($request->has('continue') && session()->has('ai_batch_ids')) {
            return $this->processNextBatchItem('subcategory');
        }

        // Start new batch: find all subcategories without photos
        $ids = Sub_categories::whereDoesntHave('photos')
            ->pluck('id')
            ->toArray();

        if (empty($ids)) {
            return redirect()->back()->with('success', 'All subcategories already have images!');
        }

        // Store batch info in session
        session([
            'ai_batch_ids' => $ids,
            'ai_batch_type' => 'subcategory',
            'ai_batch_total' => count($ids),
            'ai_batch_done' => 0,
            'ai_batch_errors' => [],
            'ai_batch_completed' => [],
            'ai_batch_started_at' => now()->toISOString(),
        ]);

        return redirect()->route('ai-image.status');
    }

    /**
     * Show the batch progress status page.
     */
    public function status()
    {
        $batchIds = session('ai_batch_ids', []);
        $batchType = session('ai_batch_type', 'category');
        $total = session('ai_batch_total', 0);
        $done = session('ai_batch_done', 0);
        $errors = session('ai_batch_errors', []);
        $completed = session('ai_batch_completed', []);
        $startedAt = session('ai_batch_started_at');

        $isComplete = empty($batchIds);
        $remaining = count($batchIds);

        // Get the name of the next item to process
        $nextItemName = null;
        if (!$isComplete) {
            $nextId = $batchIds[0] ?? null;
            if ($nextId) {
                if ($batchType === 'category') {
                    $item = Categories::find($nextId);
                } else {
                    $item = Sub_categories::find($nextId);
                }
                $nextItemName = $item ? ($item->name['en'] ?? $item->name['ar'] ?? 'Unknown') : 'Unknown';
            }
        }

        return view('admin.ai-image-status', compact(
            'batchType', 'total', 'done', 'errors', 'completed',
            'isComplete', 'remaining', 'nextItemName', 'startedAt'
        ));
    }

    /**
     * Process the next item in the batch queue.
     */
    protected function processNextBatchItem(string $type): RedirectResponse
    {
        $ids = session('ai_batch_ids', []);

        if (empty($ids)) {
            return redirect()->route('ai-image.status');
        }

        // Pop the first ID
        $nextId = array_shift($ids);
        session(['ai_batch_ids' => $ids]);

        $success = false;
        $itemName = 'Unknown';

        if ($type === 'category') {
            $item = Categories::find($nextId);
            if ($item) {
                $itemName = $item->name['en'] ?? $item->name['ar'] ?? 'Category #' . $nextId;
                $success = $this->dalleService->generateForCategory($item);
            }
        } else {
            $item = Sub_categories::with('category')->find($nextId);
            if ($item) {
                $itemName = $item->name['en'] ?? $item->name['ar'] ?? 'Subcategory #' . $nextId;
                $success = $this->dalleService->generateForSubcategory($item);
            }
        }

        // Update session counters
        $done = session('ai_batch_done', 0) + 1;
        session(['ai_batch_done' => $done]);

        if ($success) {
            $completed = session('ai_batch_completed', []);
            $completed[] = $itemName;
            session(['ai_batch_completed' => $completed]);
        } else {
            $errors = session('ai_batch_errors', []);
            $errors[] = $itemName;
            session(['ai_batch_errors' => $errors]);
        }

        return redirect()->route('ai-image.status');
    }
}
