<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadController extends Controller
{
    
    public function uploadImage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'], 
            'folder' => ['nullable', 'string', 'max:100'], 
        ]);

        try {
            $file = $validated['file'];
            $folder = $validated['folder'] ?? 'uploads';

            $extension = $file->getClientOriginalExtension();
            $filename = Str::random(40) . '.' . $extension;

            $path = $file->storeAs($folder, $filename, 'public');

            $url = Storage::url($path);
            
            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully.',
                'data' => [
                    'path' => $path,
                    'url' => $url,
                    'filename' => $filename,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to upload image', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function deleteFile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'path' => ['required', 'string'],
        ]);

        try {
            if (Storage::disk('public')->exists($validated['path'])) {
                Storage::disk('public')->delete($validated['path']);
                
                return response()->json([
                    'success' => true,
                    'message' => 'File deleted successfully.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'File not found.',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Failed to delete file', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete file.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
