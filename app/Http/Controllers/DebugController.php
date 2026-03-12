<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MenuItem;

class DebugController extends Controller
{
    public function testUpload(Request $request)
    {
        if ($request->isMethod('post')) {
            $log = [];
            $log[] = 'Has file: ' . ($request->hasFile('image') ? 'YES' : 'NO');
            
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $log[] = 'Original name: ' . $image->getClientOriginalName();
                $log[] = 'Size: ' . $image->getSize();
                $log[] = 'Valid: ' . ($image->isValid() ? 'YES' : 'NO');
                
                $imageName = time() . '_' . $image->getClientOriginalName();
                $path = public_path('uploads/menu');
                $log[] = 'Target path: ' . $path;
                $log[] = 'Path writable: ' . (is_writable($path) ? 'YES' : 'NO');
                
                try {
                    $image->move($path, $imageName);
                    $log[] = 'Move successful!';
                    $log[] = 'File exists: ' . (file_exists($path . '/' . $imageName) ? 'YES' : 'NO');
                } catch (\Exception $e) {
                    $log[] = 'Error: ' . $e->getMessage();
                }
            }
            
            return response()->json($log);
        }
        
        return view('debug.upload');
    }
}
