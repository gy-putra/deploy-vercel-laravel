<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

Route::get('/', function () {
    return view('welcome');
});

// Custom route for serving pilgrim documents
Route::get('/storage/pilgrim-documents/{filename}', function ($filename) {
    $path = 'pilgrim-documents/' . $filename;
    
    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }
    
    $file = Storage::disk('public')->get($path);
    $type = Storage::disk('public')->mimeType($path);
    
    return response($file, 200)
        ->header('Content-Type', $type)
        ->header('Cache-Control', 'public, max-age=31536000');
})->name('pilgrim.documents.show');
