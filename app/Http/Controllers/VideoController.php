<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class VideoController extends Controller
{
    public function index()
    {
        $videosPath = storage_path('app/public/videos');

        // Check if the directory exists
        if (!file_exists($videosPath)) {
            return Inertia::render('Videos/Index', [
                'videos' => []
            ]);
        }

        // Get all files in the videos directory
        $files = scandir($videosPath);

        // Remove '.' and '..' entries
        $files = array_diff($files, ['.', '..']);

        $videoFiles = [];

        foreach ($files as $filename) {
            $filePath = $videosPath . '/' . $filename;

            // Check if it's a file (not a subdirectory)
            if (is_file($filePath)) {
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                // Only include video files
                if (in_array($extension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'm4v'])) {
                    $size = filesize($filePath);
                    $lastModified = filemtime($filePath);

                    // Generate the URL for the video
                    $url = asset('storage/videos/' . $filename);

                    $videoFiles[] = [
                        'name' => pathinfo($filename, PATHINFO_FILENAME),
                        'filename' => $filename,
                        'url' => $url,
                        'size' => $size,
                        'size_formatted' => $this->formatBytes($size),
                        'last_modified' => date('M j, Y', $lastModified),
                        'duration' => $this->getVideoDuration($filename), // Placeholder - would need actual implementation
                        'views' => rand(100, 2500), // Placeholder - would need actual view tracking
                        'category' => $this->getCategoryForVideo($filename), // Placeholder category
                        'status' => 'Not Started', // Placeholder status
                        'progress' => 0 // Placeholder progress
                    ];
                }
            }
        }

        // Sort by most recently modified
        usort($videoFiles, function ($a, $b) {
            return strtotime($b['last_modified']) - strtotime($a['last_modified']);
        });

        return Inertia::render('Videos/Index', [
            'videos' => $videoFiles
        ]);
    }

    public function show($filename)
    {
        // Sanitize the filename to prevent directory traversal
        $filename = basename($filename);

        $videosPath = storage_path('app/public/videos');
        $filePath = $videosPath . '/' . $filename;

        // Check if the file exists
        if (!file_exists($filePath) || !is_file($filePath)) {
            abort(404, 'Video not found');
        }

        // Get file info
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Only allow video files
        if (!in_array($extension, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'm4v'])) {
            abort(404, 'Invalid video file');
        }

        $size = filesize($filePath);
        $lastModified = filemtime($filePath);

        // Generate the URL for the video
        $url = asset('storage/videos/' . $filename);

        $videoData = [
            'name' => pathinfo($filename, PATHINFO_FILENAME),
            'filename' => $filename,
            'url' => $url,
            'size' => $size,
            'size_formatted' => $this->formatBytes($size),
            'last_modified' => date('M j, Y', $lastModified),
            'duration' => $this->getVideoDuration($filename), // Placeholder - would need actual implementation
            'views' => rand(100, 2500), // Placeholder - would need actual view tracking
            'category' => $this->getCategoryForVideo($filename), // Placeholder category
            'status' => 'Not Started', // Placeholder status
            'progress' => 0 // Placeholder progress
        ];

        return Inertia::render('Videos/Show', [
            'video' => $videoData
        ]);
    }

    private function formatBytes($size, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
    
    private function getVideoDuration($file)
    {
        // This is a placeholder - in a real implementation you would use FFmpeg or similar
        // to get the actual video duration
        $durations = ['04:30', '12:15', '06:45', '08:20', '05:10', '09:45'];
        return $durations[array_rand($durations)];
    }
    
    private function getCategoryForVideo($filename)
    {
        $categories = [
            'Early Detection',
            'Response Protocol', 
            'Team Communication',
            'Medication Administration',
            'Protocol',
            'Medication',
            'Teamwork',
            'Patient Care'
        ];
        
        return $categories[array_rand($categories)];
    }
}