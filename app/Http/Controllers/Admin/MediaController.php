<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use App\Services\ImageOptimizerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $folder = $request->query('folder', 'media');
        
        // Sync database with storage if requested
        if ($request->has('sync')) {
            $this->syncStorageToDatabase();
            return redirect()->route('admin.media.index')->with('success', 'Media synchronized successfully.');
        }

        $media = MediaAsset::where('folder', $folder)
            ->latest()
            ->paginate(30);

        // Get unique folders for sidebar
        $folders = MediaAsset::select('folder')->distinct()->pluck('folder');
        if (!$folders->contains('media')) {
            $folders->push('media');
        }

        return view('admin.media.index', compact('media', 'folder', 'folders'));
    }

    public function store(Request $request, ImageOptimizerService $optimizer)
    {
        $request->validate([
            'files.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max
            'folder'  => 'nullable|string|max:255',
        ]);

        if (!$request->hasFile('files')) {
            return response()->json(['error' => 'No files uploaded.'], 422);
        }

        $folder = $request->input('folder', 'media') ?: 'media';
        $folder = Str::slug($folder);
        $uploadedAssets = [];

        foreach ($request->file('files') as $file) {
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = strtolower($file->getClientOriginalExtension());
            $filename = Str::slug($originalName) . '-' . Str::random(6) . '.' . $extension;
            
            // Store file
            $path = $file->storeAs($folder, $filename, 'public');
            
            // Auto WebP Compression if image
            if (str_starts_with($file->getMimeType(), 'image/')) {
                $path = $optimizer->optimize($path, ImageOptimizerService::MAX_HERO); // Max 1400px
            }

            // Record in Database
            $absolutePath = Storage::disk('public')->path($path);
            $asset = MediaAsset::create([
                'folder'     => $folder,
                'filename'   => basename($path),
                'path'       => $path,
                'alt_text'   => str_replace(['-', '_'], ' ', $originalName),
                'size_bytes' => file_exists($absolutePath) ? filesize($absolutePath) : 0,
                'mime_type'  => $file->getClientMimeType(),
            ]);

            $uploadedAssets[] = $asset;
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'assets' => $uploadedAssets]);
        }

        return back()->with('success', 'Images uploaded successfully.');
    }

    public function update(Request $request, MediaAsset $media)
    {
        $request->validate([
            'alt_text' => 'nullable|string|max:255',
        ]);

        $media->update(['alt_text' => $request->alt_text]);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Media details updated.');
    }

    public function destroy(MediaAsset $media)
    {
        if (Storage::disk('public')->exists($media->path)) {
            Storage::disk('public')->delete($media->path);
        }
        $media->delete();

        return back()->with('success', 'Media deleted successfully.');
    }

    /**
     * Helper to sync manually uploaded/imported files into DB
     */
    private function syncStorageToDatabase()
    {
        $disk = Storage::disk('public');
        $files = $disk->allFiles();

        foreach ($files as $file) {
            // Skip non-images
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                continue;
            }

            if (MediaAsset::where('path', $file)->exists()) {
                continue; // Already exists
            }

            $folder = dirname($file);
            if ($folder === '.') {
                $folder = 'root';
            }

            $absolutePath = $disk->path($file);
            MediaAsset::create([
                'folder'     => $folder,
                'filename'   => basename($file),
                'path'       => $file,
                'alt_text'   => null,
                'size_bytes' => file_exists($absolutePath) ? filesize($absolutePath) : 0,
                'mime_type'  => 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext),
            ]);
        }
    }
}
