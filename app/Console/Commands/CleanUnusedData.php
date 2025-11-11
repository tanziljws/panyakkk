<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\ActivityLog;
use App\Models\Galeri;

class CleanUnusedData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-unused-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean unused data and optimize database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup process...');
        
        // Clean old activity logs (older than 30 days)
        $deletedLogs = ActivityLog::where('created_at', '<', now()->subDays(30))->delete();
        $this->info("Deleted {$deletedLogs} old activity logs");
        
        // Optimize database tables
        DB::statement('OPTIMIZE TABLE activity_logs');
        DB::statement('OPTIMIZE TABLE galeris');
        DB::statement('OPTIMIZE TABLE comments');
        DB::statement('OPTIMIZE TABLE likes');
        $this->info('Database tables optimized');
        
        // Clean orphaned files (files that don't have database records)
        $this->cleanOrphanedFiles();
        
        $this->info('Cleanup completed successfully!');
    }
    
    /**
     * Clean orphaned image files
     */
    private function cleanOrphanedFiles()
    {
        $imageDirectory = public_path('images');
        if (!File::exists($imageDirectory)) {
            return;
        }
        
        $files = File::files($imageDirectory);
        $galeriImages = Galeri::pluck('gambar', 'thumbnail')->flatten()->filter()->toArray();
        
        $deletedCount = 0;
        foreach ($files as $file) {
            $filename = $file->getFilename();
            
            // Skip if file is in database
            if (in_array($filename, $galeriImages)) {
                continue;
            }
            
            // Delete orphaned files older than 7 days
            if ($file->getMTime() < time() - (7 * 24 * 60 * 60)) {
                File::delete($file->getPathname());
                $deletedCount++;
            }
        }
        
        $this->info("Deleted {$deletedCount} orphaned files");
    }
}