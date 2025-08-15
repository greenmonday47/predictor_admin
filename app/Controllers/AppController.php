<?php

namespace App\Controllers;

use Config\AppVersion;

class AppController extends BaseController
{
    /**
     * GET /public/api/app/version
     * Returns the latest app version metadata.
     */
    public function version()
    {
        $cfg = config(AppVersion::class);

        // Defaults from config
        $latestVersion = $cfg->version;
        $latestBuild = $cfg->buildNumber;
        $downloadUrl = $cfg->downloadUrl;
        $forceUpdate = $cfg->forceUpdate;
        $updateMessage = $cfg->updateMessage;

        // Try reading public/app_version.json (format: { "v": "1.0.3+3", ... })
        $jsonPath = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'app_version.json';
        $jsonData = $this->readCachedJson($jsonPath, 300); // cache 5 minutes

        if (is_array($jsonData)) {
            // v => semanticVersion+buildNumber
            if (!empty($jsonData['v']) && is_string($jsonData['v'])) {
                // Validate v format x.y.z+b
                if (preg_match('/^\d+\.\d+\.\d+\+\d+$/', $jsonData['v'])) {
                    [$v, $b] = explode('+', $jsonData['v'], 2);
                    $latestVersion = $v;
                    $latestBuild = $b;
                }
            }

            // Optional overrides from JSON (friendly keys)
            if (!empty($jsonData['download_url']) && is_string($jsonData['download_url'])) {
                $downloadUrl = $jsonData['download_url'];
            }
            if (array_key_exists('force', $jsonData)) {
                $forceUpdate = (bool) $jsonData['force'];
            } elseif (array_key_exists('force_update', $jsonData)) {
                $forceUpdate = (bool) $jsonData['force_update'];
            }
            if (!empty($jsonData['message']) && is_string($jsonData['message'])) {
                $updateMessage = $jsonData['message'];
            } elseif (!empty($jsonData['update_message']) && is_string($jsonData['update_message'])) {
                $updateMessage = $jsonData['update_message'];
            }
        }

        $data = [
            'version' => $latestVersion,
            'build_number' => $latestBuild,
            'download_url' => $downloadUrl,
            'force_update' => $forceUpdate,
            'update_message' => $updateMessage,
        ];

        // Optionals from config (still supported)
        if (!empty($cfg->fileSize)) {
            $data['file_size'] = $cfg->fileSize;
        }
        if (!empty($cfg->releaseNotes)) {
            $data['release_notes'] = $cfg->releaseNotes;
        }
        if (!empty($cfg->androidDownloadUrl)) {
            $data['android_download_url'] = $cfg->androidDownloadUrl;
        }
        if (!empty($cfg->iosDownloadUrl)) {
            $data['ios_download_url'] = $cfg->iosDownloadUrl;
        }
        if (!empty($cfg->fileHash)) {
            $data['file_hash'] = $cfg->fileHash;
            $data['hash_algorithm'] = $cfg->hashAlgorithm;
        }

        return $this->respond([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Optional: GET /public/api/app/download/{version}
     * If you host direct APKs on this server under writable/uploads or public folder,
     * this can stream the file. Otherwise, prefer using direct download_url.
     */
    public function download($version)
    {
        $fileName = 'predictor-app-v' . $version . '.apk';

        // Prefer public/downloads
        $publicPath = FCPATH . 'downloads/' . $fileName; // FCPATH points to public/
        if (is_file($publicPath)) {
            return $this->response->download($publicPath, null);
        }

        // Fallback: writable/uploads
        $writablePath = WRITEPATH . 'uploads/' . $fileName; // WRITEPATH points to writable/
        if (is_file($writablePath)) {
            return $this->response->download($writablePath, null);
        }

        // Fallback to a stable filename if you always upload as app-release.apk
        $stableName = 'app-release.apk';
        $stablePublic = FCPATH . 'downloads/' . $stableName;
        if (is_file($stablePublic)) {
            return $this->response->download($stablePublic, null);
        }

        $stableWritable = WRITEPATH . 'uploads/' . $stableName;
        if (is_file($stableWritable)) {
            return $this->response->download($stableWritable, null);
        }

        return $this->failNotFound('Update file not found for version ' . $version . ' or as app-release.apk');
    }

    /**
     * GET /public/api/app/download/latest
     * Always stream the stable app-release.apk if present.
     */
    public function downloadLatest()
    {
        $stableName = 'app-release.apk';

        $publicPath = FCPATH . 'downloads/' . $stableName;
        if (is_file($publicPath)) {
            return $this->response->download($publicPath, null);
        }

        $writablePath = WRITEPATH . 'uploads/' . $stableName;
        if (is_file($writablePath)) {
            return $this->response->download($writablePath, null);
        }

        return $this->failNotFound('Latest APK not found (app-release.apk)');
    }

    /**
     * Read and cache JSON file from disk for a short TTL.
     * Returns array|null.
     */
    private function readCachedJson(string $path, int $ttlSeconds = 300): ?array
    {
        try {
            $cache = service('cache');
            $key = 'app_version_json_' . md5($path);
            $cached = $cache->get($key);
            if (is_array($cached)) {
                return $cached;
            }

            if (!is_file($path)) {
                return null;
            }

            $raw = @file_get_contents($path);
            if ($raw === false) {
                return null;
            }
            $decoded = json_decode($raw, true);
            if (!is_array($decoded)) {
                return null;
            }

            // Cache parsed structure
            $cache->save($key, $decoded, $ttlSeconds);
            return $decoded;
        } catch (\Throwable $e) {
            return null;
        }
    }
}


