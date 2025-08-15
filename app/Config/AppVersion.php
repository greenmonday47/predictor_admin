<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class AppVersion extends BaseConfig
{
    /**
     * Semantic version, e.g. "1.0.3"
     */
    public string $version = '1.0.3';

    /**
     * Build number as a string, e.g. "3"
     */
    public string $buildNumber = '3';

    /**
     * Default download URL (Play Store page or direct APK link)
     */
    public string $downloadUrl = 'https://scores.binusu.site/public/api/app/download/latest';

    /**
     * If true, client should enforce update before continuing.
     */
    public bool $forceUpdate = false;

    /**
     * Message to show in the update dialog.
     */
    public string $updateMessage = 'New version available with bug fixes and performance improvements!';

    /**
     * Optional metadata
     */
    public ?string $fileSize = null; // e.g., "25.6 MB"

    /**
     * Optional array of release notes strings
     */
    public ?array $releaseNotes = null;

    /**
     * Platform-specific URLs (optional)
     */
    public ?string $androidDownloadUrl = null; // e.g., direct APK link
    public ?string $iosDownloadUrl = null;     // e.g., App Store link

    /**
     * Optional file integrity data for direct APK downloads
     */
    public ?string $fileHash = null;           // SHA-256 hash string
    public ?string $hashAlgorithm = 'sha256';  // Algorithm name
}


