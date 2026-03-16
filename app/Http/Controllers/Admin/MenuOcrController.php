<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\MenuOcrExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;

class MenuOcrController extends Controller
{
    public function index()
    {
        return view('admin.menu-ocr.index');
    }

    public function process(Request $request)
    {
        $request->validate([
            'menu_image' => 'required|image|max:5120',
        ]);

        $apiKey = config('services.google_vision.key');

        if (!$apiKey) {
            return back()->with('error', 'Google Cloud Vision API key is not configured.');
        }

        $imageData = base64_encode(file_get_contents($request->file('menu_image')->getRealPath()));

        $response = Http::post("https://vision.googleapis.com/v1/images:annotate?key={$apiKey}", [
            'requests' => [[
                'image'    => ['content' => $imageData],
                'features' => [['type' => 'TEXT_DETECTION']],
            ]],
        ]);

        if ($response->failed()) {
            return back()->with('error', 'Google Vision API request failed: ' . $response->body());
        }

        $text = $response->json('responses.0.fullTextAnnotation.text') ?? '';

        if (!$text) {
            return back()->with('error', 'No text could be extracted from the image.');
        }

        $items = $this->parseMenuText($text);

        if (empty($items)) {
            return back()->with('error', 'Could not detect any menu items with prices in the image.');
        }

        return Excel::download(new MenuOcrExport($items), 'menu-items-' . now()->format('d-m-Y') . '.xlsx');
    }

    private function parseMenuText(string $text): array
    {
        $items = [];
        $lines = array_filter(array_map('trim', explode("\n", $text)));

        foreach ($lines as $line) {
            // Match: any text followed by a price like 120, 120.00, ₹120, Rs.120
            if (preg_match('/^(.+?)\s+(?:Rs\.?|₹|INR)?\s*(\d+(?:\.\d{1,2})?)$/i', $line, $m)) {
                $items[] = [
                    'name'  => trim($m[1]),
                    'price' => (float) $m[2],
                ];
            }
        }

        return $items;
    }
}
