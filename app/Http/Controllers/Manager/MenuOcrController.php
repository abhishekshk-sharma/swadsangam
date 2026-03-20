<?php

namespace App\Http\Controllers\Manager;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MenuOcrController extends BaseManagerController
{
    public function index()
    {
        return view('manager.menu-ocr.index');
    }

    public function process(Request $request)
    {
        $request->validate(['menu_image' => 'required|image|max:5120']);

        $apiKey = config('services.google_vision.key');
        if (!$apiKey) {
            return response()->json(['error' => 'Google Cloud Vision API key is not configured.'], 422);
        }

        $imageData = base64_encode(file_get_contents($request->file('menu_image')->getRealPath()));

        $response = Http::withoutVerifying()->post("https://vision.googleapis.com/v1/images:annotate?key={$apiKey}", [
            'requests' => [[
                'image'    => ['content' => $imageData],
                'features' => [['type' => 'TEXT_DETECTION']],
            ]],
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Google Vision API request failed.'], 422);
        }

        $text = $response->json('responses.0.fullTextAnnotation.text') ?? '';
        if (!$text) {
            return response()->json(['error' => 'No text could be extracted from the image.'], 422);
        }

        $sections = $this->parseMenuText($text);
        if (empty($sections)) {
            return response()->json(['error' => 'Could not detect any menu items with prices in the image.'], 422);
        }

        return response()->json(['sections' => $sections]);
    }

    public function importExcel(Request $request)
    {
        $request->validate(['excel_file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);

        $path = $request->file('excel_file')->getRealPath();
        $ext  = strtolower($request->file('excel_file')->getClientOriginalExtension());

        $rows = [];
        if ($ext === 'csv') {
            $handle = fopen($path, 'r');
            while (($row = fgetcsv($handle)) !== false) $rows[] = $row;
            fclose($handle);
        } else {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            foreach ($spreadsheet->getActiveSheet()->getRowIterator() as $row) {
                $cells = [];
                foreach ($row->getCellIterator() as $cell) $cells[] = $cell->getValue();
                $rows[] = $cells;
            }
        }

        $sections     = [];
        $currentCat   = 'Uncategorized';
        $currentItems = [];
        $skipPhrases  = ['item name', 'price'];

        foreach ($rows as $row) {
            $name  = trim((string)($row[0] ?? ''));
            $price = isset($row[1]) ? trim((string)$row[1]) : '';

            if (!$name) continue;

            if (count(array_filter($skipPhrases, fn($p) => stripos($name, $p) !== false)) >= 1
                && stripos($price, 'price') !== false) continue;

            $price = preg_replace('/[^0-9.]/', '', $price);

            if ($price !== '' && (float)$price > 0) {
                $currentItems[] = ['name' => $name, 'price' => (float)$price];
            } else {
                if (!empty($currentItems)) {
                    $sections[] = ['category' => $currentCat, 'items' => $currentItems];
                    $currentItems = [];
                }
                $currentCat = $name;
            }
        }

        if (!empty($currentItems)) {
            $sections[] = ['category' => $currentCat, 'items' => $currentItems];
        }

        if (empty($sections)) {
            return back()->with('error', 'No valid items found in the Excel file.');
        }

        return back()->with('excel_sections', $sections);
    }

    public function import(Request $request)
    {
        $request->validate(['sections' => 'required|json']);

        $sections = json_decode($request->sections, true);
        $tenantId = $this->tenantId();
        $branchId = $this->branchId();
        $inserted = 0;

        foreach ($sections as $section) {
            if (empty($section['items'])) continue;

            $category = MenuCategory::firstOrCreate(
                ['name' => $section['category'], 'tenant_id' => $tenantId, 'branch_id' => $branchId],
                ['tenant_id' => $tenantId, 'branch_id' => $branchId]
            );

            foreach ($section['items'] as $item) {
                MenuItem::firstOrCreate(
                    ['name' => $item['name'], 'tenant_id' => $tenantId],
                    [
                        'tenant_id'        => $tenantId,
                        'price'            => $item['price'],
                        'menu_category_id' => $category->id,
                        'is_available'     => true,
                    ]
                );
                $inserted++;
            }
        }

        return redirect()->route('manager.menu.index')
            ->with('success', $inserted . ' menu items imported successfully.');
    }

    private function parseMenuText(string $text): array
    {
        $items = [];
        $lines = array_values(array_filter(array_map('trim', explode("\n", $text))));

        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];

            if (preg_match('/^(.+?)\s*[-\x{2013}\x{2014}:\.]{0,2}\s*(?:Rs\.?|\x{20b9}|INR)?\s*(\d+(?:\.\d{1,2})?)\s*(?:\/\-|\/)?$/iu', $line, $m)) {
                $name = trim($m[1]);
                if (mb_strlen($name) >= 1 && !is_numeric($name)) {
                    $items[] = ['name' => $name, 'price' => (float) $m[2]];
                    continue;
                }
            }

            if (isset($lines[$i + 1])) {
                $next = trim($lines[$i + 1]);
                if (preg_match('/^(?:Rs\.?|\x{20b9}|INR)?\s*(\d+(?:\.\d{1,2})?)\s*(?:\/\-|\/)?$/iu', $next, $m)) {
                    if (!is_numeric($line) && mb_strlen($line) >= 1) {
                        $items[] = ['name' => $line, 'price' => (float) $m[1]];
                        $i++;
                        continue;
                    }
                }
            }
        }

        if (empty($items)) return [];

        return [['category' => 'Menu', 'items' => $items]];
    }
}
