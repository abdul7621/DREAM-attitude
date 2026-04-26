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
        $folder = $request->query('folder', 'all');
        $search = $request->query('search', '');
        $perPage = $request->query('per_page', 50);

        $query = MediaAsset::query();

        if ($folder !== 'all') {
            $query->where('folder', $folder);
        }

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('filename', 'like', "%{$search}%")
                  ->orWhere('alt_text', 'like', "%{$search}%");
            });
        }

        $media = $query->latest()->paginate($perPage)->withQueryString();

        // Get unique folders for tabs
        $folders = MediaAsset::select('folder')->distinct()->pluck('folder');
        if (!$folders->contains('media')) {
            $folders->push('media');
        }

        return view('admin.media.index', compact('media', 'folder', 'folders', 'search', 'perPage'));
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
            $width = null;
            $height = null;
            
            if (file_exists($absolutePath) && str_starts_with(mime_content_type($absolutePath), 'image/')) {
                $dims = @getimagesize($absolutePath);
                if ($dims) {
                    $width = $dims[0];
                    $height = $dims[1];
                }
            }

            $asset = MediaAsset::create([
                'folder'     => $folder,
                'filename'   => basename($path),
                'path'       => $path,
                'alt_text'   => str_replace(['-', '_'], ' ', $originalName),
                'size_bytes' => file_exists($absolutePath) ? filesize($absolutePath) : 0,
                'mime_type'  => file_exists($absolutePath) ? mime_content_type($absolutePath) : $file->getClientMimeType(),
                'width'      => $width,
                'height'     => $height,
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

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:media_assets,id'
        ]);

        $assets = MediaAsset::whereIn('id', $request->ids)->get();
        foreach ($assets as $media) {
            if (Storage::disk('public')->exists($media->path)) {
                Storage::disk('public')->delete($media->path);
            }
            $media->delete();
        }

        return response()->json(['success' => true, 'message' => count($assets) . ' files deleted.']);
    }

    public function syncPreview()
    {
        $disk = Storage::disk('public');
        $files = $disk->allFiles();
        
        $newFilesCount = 0;
        $orphanCount = 0;

        // Check for orphans
        $dbPaths = MediaAsset::pluck('path')->toArray();
        foreach ($dbPaths as $path) {
            if (!$disk->exists($path)) {
                $orphanCount++;
            }
        }

        // Check for new files
        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                continue;
            }

            if (!in_array($file, $dbPaths)) {
                $newFilesCount++;
            }
        }

        return response()->json([
            'new_files' => $newFilesCount,
            'orphans' => $orphanCount
        ]);
    }

    public function syncExecute()
    {
        $disk = Storage::disk('public');
        $files = $disk->allFiles();

        // 1. Delete Orphans
        $dbAssets = MediaAsset::all();
        $orphanCount = 0;
        foreach ($dbAssets as $asset) {
            if (!$disk->exists($asset->path)) {
                $asset->delete();
                $orphanCount++;
            }
        }

        // 2. Add New Files
        $newFilesCount = 0;
        $dbPaths = MediaAsset::pluck('path')->toArray();
        
        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                continue;
            }

            if (in_array($file, $dbPaths)) {
                continue;
            }

            $folder = dirname($file);
            if ($folder === '.') {
                $folder = 'root';
            }

            $absolutePath = $disk->path($file);
            $width = null;
            $height = null;
            $size = 0;
            $mime = 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext);

            if (file_exists($absolutePath)) {
                $size = filesize($absolutePath);
                $mime = mime_content_type($absolutePath) ?: $mime;
                $dims = @getimagesize($absolutePath);
                if ($dims) {
                    $width = $dims[0];
                    $height = $dims[1];
                }
            }

            MediaAsset::create([
                'folder'     => $folder,
                'filename'   => basename($file),
                'path'       => $file,
                'alt_text'   => null,
                'size_bytes' => $size,
                'mime_type'  => $mime,
                'width'      => $width,
                'height'     => $height,
            ]);

            $newFilesCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "Sync complete: Added {$newFilesCount} new files, removed {$orphanCount} orphaned references."
        ]);
    }
}
