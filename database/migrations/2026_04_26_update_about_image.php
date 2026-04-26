<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $aboutPage = DB::table('pages')->where('slug', 'like', '%about%')->first();
        if ($aboutPage) {
            $content = str_replace(
                '/placeholder-award.jpg',
                '/storage/media/whatsapp-image-2026-04-20-at-011831-pHsjmN.webp',
                $aboutPage->content
            );
            
            // Also remove the admin instruction text
            $content = preg_replace('/<p[^>]*>\(Admin: Click on the image in the editor to replace it with your award photo\)<\/p>/i', '', $content);
            
            DB::table('pages')->where('id', $aboutPage->id)->update(['content' => $content]);
        }
    }

    public function down(): void
    {
    }
};
