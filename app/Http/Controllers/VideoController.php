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

                    // Get actual video duration using FFprobe if available
                    $duration = $this->getActualVideoDuration($filePath);
                    $durationFormatted = $this->formatDuration($duration);

                    $videoFiles[] = [
                        'name' => pathinfo($filename, PATHINFO_FILENAME),
                        'filename' => $filename,
                        'url' => $url,
                        'size' => $size,
                        'size_formatted' => $this->formatBytes($size),
                        'last_modified' => date('M j, Y', $lastModified),
                        'duration' => $durationFormatted, // Actual duration
                        'duration_seconds' => $duration, // Duration in seconds for more precise handling
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

    private function getActualVideoDuration($filePath)
    {
        // Try to use FFprobe to get actual video duration if available
        $ffprobePath = 'ffprobe'; // This assumes FFprobe is installed and in PATH

        // Check if ffprobe is available
        $whichResult = shell_exec('which ffprobe 2>/dev/null');
        if (!$whichResult) {
            // Try Windows command
            $whereResult = shell_exec('where ffprobe 2>nul');
            if (!$whereResult) {
                // If ffprobe is not available, return 0 and we'll calculate duration from file size as approximation
                return $this->estimateDurationFromFileSize($filePath);
            }
        }

        $command = "$ffprobePath -v quiet -show_entries format=duration -of csv=p=0 \"$filePath\" 2>/dev/null";
        $result = shell_exec($command);

        if ($result !== null && is_numeric(trim($result))) {
            $duration = floatval(trim($result));
            if ($duration > 0) {
                return $duration;
            }
        }

        // If ffprobe failed, estimate duration from file size
        return $this->estimateDurationFromFileSize($filePath);
    }

    private function estimateDurationFromFileSize($filePath)
    {
        // More realistic estimation based on common video file properties
        $fileSize = filesize($filePath); // Size in bytes

        // For MP4 files, a common bitrate is around 1-2 Mbps (125-250 KB/s)
        // Let's use 150 KB/s as a middle ground for estimation
        $estimatedKBPerSecond = 150; // Adjust based on typical video quality

        $estimatedSeconds = $fileSize / ($estimatedKBPerSecond * 1024);

        // Return a realistic value, minimum 1 second
        return max(1, round($estimatedSeconds));
    }

    private function formatDuration($durationSeconds)
    {
        if ($durationSeconds <= 0) {
            // If we couldn't determine the duration, return a placeholder
            return '00:00';
        }

        $hours = floor($durationSeconds / 3600);
        $minutes = floor(($durationSeconds % 3600) / 60);
        $seconds = floor($durationSeconds % 60);

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        } else {
            return sprintf('%d:%02d', $minutes, $seconds);
        }
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