<?php

namespace App\Http\Controllers\Admin;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MenuOcrController extends BaseAdminController
{
    public function index()
    {
        return view('admin.menu-ocr.index');
    }

    // Step 1 — OCR only, return parsed sections as JSON
    public function process(Request $request)
    {
        $request->validate([
            'menu_image' => 'required|image|max:5120',
            'language'   => 'nullable|in:en,hi,gu',
            'ocr_mode'   => 'nullable|in:standard,variant',
        ]);

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

        $ocrMode  = $request->input('ocr_mode', 'standard');
        $sections = $ocrMode === 'variant'
            ? $this->parseMenuTextVariant($text)
            : $this->parseMenuText($text);
        if (empty($sections)) {
            return response()->json(['error' => 'Could not detect any menu items with prices in the image.'], 422);
        }

        $targetLang  = $request->input('language', 'en');
        $needsTranslate = $targetLang !== 'en' || $this->hasNonLatinScript($text);
        if ($needsTranslate) {
            $sections = $this->translateSections($sections, $targetLang);
        }

        return response()->json(['sections' => $sections]);
    }

    private function hasNonLatinScript(string $text): bool
    {
        // Matches Devanagari (Hindi) or Gujarati Unicode blocks
        return (bool) preg_match('/[\x{0900}-\x{097F}\x{0A80}-\x{0AFF}]/u', $text);
    }

    private function translateSections(array $sections, string $targetLang): array
    {
        $translateKey = config('services.google_translate.key');
        if (!$translateKey) return $sections;

        // Collect all strings to translate in one batch
        $strings = [];
        foreach ($sections as $section) {
            $strings[] = $section['category'];
            foreach ($section['items'] as $item) {
                $strings[] = $item['name'];
            }
        }

        $response = Http::withoutVerifying()->post(
            "https://translation.googleapis.com/language/translate/v2?key={$translateKey}",
            ['q' => $strings, 'target' => $targetLang, 'format' => 'text']
        );

        if ($response->failed()) return $sections;

        $translations = collect($response->json('data.translations') ?? [])
            ->pluck('translatedText')
            ->toArray();

        if (count($translations) !== count($strings)) return $sections;

        $idx = 0;
        foreach ($sections as &$section) {
            $section['category'] = $translations[$idx++];
            foreach ($section['items'] as &$item) {
                $item['name'] = $translations[$idx++];
            }
        }

        return $sections;
    }

    // Excel upload — Variant format: col A = item, col B+ = variant names from header row
    public function importExcelVariant(Request $request)
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

        if (empty($rows)) return back()->with('error', 'Empty file.');

        // First row = headers: col 0 = "Item Name", col 1+ = variant names (oil, butter, etc.)
        $headers  = array_map('trim', $rows[0]);
        $variants = array_slice($headers, 1); // e.g. ['oil', 'butter']

        if (empty($variants)) return back()->with('error', 'No variant columns found in header row.');

        $items = [];
        foreach (array_slice($rows, 1) as $row) {
            $itemName = trim((string)($row[0] ?? ''));
            if (!$itemName) continue;

            foreach ($variants as $vi => $variant) {
                $price = preg_replace('/[^0-9.]/', '', trim((string)($row[$vi + 1] ?? '')));
                if ($price !== '' && (float)$price > 0) {
                    $items[] = [
                        'name'  => $itemName . ' (' . $variant . ')',
                        'price' => (float)$price,
                    ];
                }
            }
        }

        if (empty($items)) return back()->with('error', 'No valid variant items found.');

        $sections = [['category' => 'Menu', 'items' => $items]];
        return back()->with('excel_sections', $sections);
    }

    // Excel upload — parse sheet, open same review modal via session flash
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

            // Skip header row
            if (count(array_filter($skipPhrases, fn($p) => stripos($name, $p) !== false)) >= 1
                && stripos($price, 'price') !== false) continue;

            // Strip currency symbols from price
            $price = preg_replace('/[^0-9.]/', '', $price);

            if ($price !== '' && (float)$price > 0) {
                $currentItems[] = ['name' => $name, 'price' => (float)$price];
            } else {
                // No price = category
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

    // Step 2 — Insert reviewed/edited data into DB
    public function import(Request $request)
    {
        $request->validate(['sections' => 'required|json']);

        $sections = json_decode($request->sections, true);
        $tenantId = $this->tenantId();
        $inserted = 0;

        foreach ($sections as $section) {
            if (empty($section['items'])) continue;

            $category = MenuCategory::firstOrCreate(
                ['name' => $section['category'], 'tenant_id' => $tenantId],
                ['tenant_id' => $tenantId]
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

        return redirect()->route('admin.menu.index')
            ->with('success', $inserted . ' menu items imported successfully.');
    }

    private function normalizeDigits(string $text): string
    {
        // Convert Unicode digit blocks to ASCII 0-9
        // Devanagari (Hindi): ०-९ U+0966-U+096F
        // Gujarati: ૦-૯ U+0AE6-U+0AEF
        $from = ['०','१','२','३','४','५','६','७','८','९',
                 '૦','૧','૨','૩','૪','૫','૬','૭','૮','૯'];
        $to   = ['0','1','2','3','4','5','6','7','8','9',
                 '0','1','2','3','4','5','6','7','8','9'];
        return str_replace($from, $to, $text);
    }

    private function parseMenuText(string $text): array
    {
        $text  = $this->normalizeDigits($text);
        $items = [];
        $lines = array_values(array_filter(array_map('trim', explode("\n", $text))));

        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];

            // Same line: anything ending with a price
            if (preg_match('/^(.+?)\s*[-\x{2013}\x{2014}:\.]{0,2}\s*(?:Rs\.?|\x{20b9}|INR)?\s*(\d+(?:\.\d{1,2})?)\s*(?:\/\-|\/)?$/iu', $line, $m)) {
                $name = trim($m[1]);
                if (mb_strlen($name) >= 1 && !is_numeric($name)) {
                    $items[] = ['name' => $name, 'price' => (float) $m[2]];
                    continue;
                }
            }

            // Next line is a price
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

    public function parseMenuTextVariant(string $text): array
    {
        $text  = $this->normalizeDigits($text);
        $lines = array_values(array_filter(array_map('trim', explode("\n", $text))));
        $items = [];

        $isPrice = fn(string $l): bool =>
            (bool) preg_match('/^(?:Rs\.?|\x{20b9}|INR)?\s*\d+(?:\.\d{1,2})?\s*[\/\\|\-]*$/iu', $l);

        $toPrice = fn(string $l): float =>
            (float) preg_replace('/[^0-9.]/', '', $l);

        $isTextOnly = fn(string $l): bool =>
            !preg_match('/\d/', $l) && mb_strlen(trim($l)) >= 1;

        $n = count($lines);

        // ── Step 1: find variant header ───────────────────────────────────────
        // Strategy: scan for N consecutive all-text short-word lines (variant labels)
        // confirmed by: the pattern (text-line then N price-lines) repeating 2+ times.
        // IMPORTANT: skip any lines before the first price appears in the document
        // (restaurant name / title block at top will never be variant headers)
        $variants  = [];
        $dataStart = 0;

        // Find index of first price line in the whole document
        $firstPriceIdx = $n;
        for ($i = 0; $i < $n; $i++) {
            if ($isPrice($lines[$i])) { $firstPriceIdx = $i; break; }
        }

        // Helper: does a string contain only non-Latin script chars (Gujarati/Devanagari)?
        // Variant labels on a Gujarati menu will be Gujarati words, not ASCII caps
        $isNonLatin = fn(string $s): bool =>
            (bool) preg_match('/[\x{0900}-\x{097F}\x{0A80}-\x{0AFF}]/u', $s);

        // Try 2-variant header (most common: Half/Full = હાફ/કુલ)
        for ($i = 0; $i < $n - 4; $i++) {
            if (!$isTextOnly($lines[$i])) continue;
            // Must appear within a few lines before the first price
            if ($i >= $firstPriceIdx) continue;

            // Candidate A: two consecutive single-word lines
            if ($isTextOnly($lines[$i+1] ?? '')) {
                $w1 = trim($lines[$i]);
                $w2 = trim($lines[$i+1]);
                $oneWord = fn($s) => count(preg_split('/\s+/u', trim($s))) === 1;
                // Both must be short AND at least one must be non-Latin script
                // (prevents "MAMA"/"BHANJA" from matching)
                if ($oneWord($w1) && $oneWord($w2)
                    && mb_strlen($w1) <= 8 && mb_strlen($w2) <= 8
                    && ($isNonLatin($w1) || $isNonLatin($w2))
                ) {
                    // Verify pattern repeats 2+ times after these 2 lines
                    $matches = 0;
                    $j = $i + 2;
                    while ($j < $n) {
                        if ($isTextOnly($lines[$j]) && isset($lines[$j+1]) && $isPrice($lines[$j+1])
                            && isset($lines[$j+2]) && $isPrice($lines[$j+2])) {
                            $matches++; $j += 3;
                        } elseif ($isTextOnly($lines[$j]) && isset($lines[$j+1]) && $isPrice($lines[$j+1])) {
                            $j += 2;
                        } else { $j++; }
                        if ($matches >= 2) break;
                    }
                    if ($matches >= 2) {
                        $variants  = [$w1, $w2];
                        $dataStart = $i + 2;
                        break;
                    }
                }
            }

            // Candidate B: single line with exactly 2 short non-Latin words "હાફ કુલ"
            $words = array_values(array_filter(preg_split('/[\s\/|]+/u', $lines[$i]), fn($w) => mb_strlen($w) >= 1));
            if (count($words) === 2 && mb_strlen($words[0]) <= 8 && mb_strlen($words[1]) <= 8
                && ($isNonLatin($words[0]) || $isNonLatin($words[1]))
            ) {
                $matches = 0;
                $j = $i + 1;
                while ($j < $n) {
                    if ($isTextOnly($lines[$j]) && isset($lines[$j+1]) && $isPrice($lines[$j+1])
                        && isset($lines[$j+2]) && $isPrice($lines[$j+2])) {
                        $matches++; $j += 3;
                    } elseif ($isTextOnly($lines[$j]) && isset($lines[$j+1]) && $isPrice($lines[$j+1])) {
                        $j += 2;
                    } else { $j++; }
                    if ($matches >= 2) break;
                }
                if ($matches >= 2) {
                    $variants  = $words;
                    $dataStart = $i + 1;
                    break;
                }
            }
        }

        // ── Step 2: parse body using detected variants ────────────────────────
        if (!empty($variants)) {
            $varCount   = count($variants);
            $currentCat = 'Menu';
            $i          = $dataStart;

            while ($i < $n) {
                $l = $lines[$i];

                if (!$isTextOnly($l) && !(!$isPrice($l) && mb_strlen($l) >= 1)) {
                    $i++; continue;
                }

                // Collect item name — merge next line only when OCR split a single name
                // Condition: next line is text AND its next line is a price
                // BUT skip merge if current line alone already leads to prices within varCount steps
                $nameParts = [$l];
                $j = $i + 1;
                if ($j < $n && $isTextOnly($lines[$j]) && $isPrice($lines[$j + 1] ?? '')) {
                    // Only merge if current line ($l) does NOT have a price within varCount lines
                    // i.e. $l by itself is NOT a complete item name
                    $priceDirectly = false;
                    for ($pk = $i + 1; $pk <= $i + $varCount && $pk < $n; $pk++) {
                        if ($isPrice($lines[$pk])) { $priceDirectly = true; break; }
                    }
                    if (!$priceDirectly) {
                        $nameParts[] = $lines[$j];
                        $j++;
                    }
                }
                $name = implode(' ', $nameParts);
                $i    = $j;

                // Collect prices (up to varCount)
                $collected = [];
                while ($i < $n && count($collected) < $varCount && $isPrice($lines[$i])) {
                    $p = $toPrice($lines[$i]);
                    $collected[] = $p;
                    $i++;
                }

                if (empty($collected)) {
                    // No price found — this is a category header
                    $currentCat = $name;
                    continue;
                }

                if (count($collected) === 1) {
                    if ($collected[0] > 0)
                        $items[] = ['name' => $name, 'price' => $collected[0], 'category' => $currentCat];
                } else {
                    foreach ($variants as $vi => $vn) {
                        if (isset($collected[$vi]) && $collected[$vi] > 0)
                            $items[] = ['name' => $name . ' (' . $vn . ')', 'price' => $collected[$vi], 'category' => $currentCat];
                    }
                }
            }

            if (!empty($items)) {
                $grouped = [];
                foreach ($items as $item) {
                    $cat = $item['category'];
                    unset($item['category']);
                    $grouped[$cat][] = $item;
                }
                return array_map(
                    fn($cat, $its) => ['category' => $cat, 'items' => $its],
                    array_keys($grouped), $grouped
                );
            }
        }

        // ── Step 3: fallback to standard parser ───────────────────────────────
        return $this->parseMenuText($text);
    }
}
