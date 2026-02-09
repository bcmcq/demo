<?php

namespace App\Services;

use GuzzleHttp\Client;
use MuxPhp\Api\AssetsApi;
use MuxPhp\Api\DirectUploadsApi;
use MuxPhp\Configuration;
use MuxPhp\Models\CreateAssetRequest;
use MuxPhp\Models\CreateUploadRequest;
use MuxPhp\Models\InputSettings;
use MuxPhp\Models\PlaybackPolicy;
use RuntimeException;

class MuxService
{
    protected Configuration $config;

    protected AssetsApi $assetsApi;

    protected DirectUploadsApi $directUploadsApi;

    public function __construct()
    {
        $this->config = Configuration::getDefaultConfiguration()
            ->setUsername(config('services.mux.token_id'))
            ->setPassword(config('services.mux.token_secret'));

        $this->assetsApi = new AssetsApi(config: $this->config);
        $this->directUploadsApi = new DirectUploadsApi(config: $this->config);
    }

    /**
     * Upload file content directly to Mux and return asset details.
     *
     * Creates a direct upload, PUTs the file bytes, then polls until the asset is created.
     *
     * @return array{asset_id: string, playback_id: string|null}
     */
    public function uploadFileContent(string $fileContent): array
    {
        $uploadRequest = new CreateUploadRequest([
            'new_asset_settings' => new CreateAssetRequest([
                'playback_policy' => [PlaybackPolicy::_PUBLIC],
            ]),
            'timeout' => 3600,
        ]);

        $upload = $this->directUploadsApi->createDirectUpload($uploadRequest)->getData();
        $uploadUrl = $upload->getUrl();
        $uploadId = $upload->getId();

        $httpClient = new Client;
        $httpClient->put($uploadUrl, [
            'headers' => ['Content-Type' => 'application/octet-stream'],
            'body' => $fileContent,
        ]);

        return $this->pollForAssetCreation($uploadId);
    }

    /**
     * Poll a direct upload until its asset is created.
     *
     * @return array{asset_id: string, playback_id: string|null}
     *
     * @throws RuntimeException
     */
    protected function pollForAssetCreation(string $uploadId, int $maxAttempts = 30, int $intervalSeconds = 5): array
    {
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $upload = $this->directUploadsApi->getDirectUpload($uploadId)->getData();

            if ($upload->getAssetId()) {
                $asset = $this->getAsset($upload->getAssetId());

                return [
                    'asset_id' => $asset['id'],
                    'playback_id' => $asset['playback_id'],
                ];
            }

            if ($upload->getStatus() === 'errored') {
                throw new RuntimeException("Mux direct upload {$uploadId} failed with error.");
            }

            sleep($intervalSeconds);
        }

        throw new RuntimeException("Mux direct upload {$uploadId} timed out waiting for asset creation.");
    }

    /**
     * Create a Mux asset directly from a URL.
     *
     * @return array{asset_id: string, playback_id: string|null}
     */
    public function createAssetFromUrl(string $inputUrl): array
    {
        $request = new CreateAssetRequest([
            'input' => [
                new InputSettings(['url' => $inputUrl]),
            ],
            'playback_policy' => [PlaybackPolicy::_PUBLIC],
        ]);

        $response = $this->assetsApi->createAsset($request);
        $asset = $response->getData();

        $playbackIds = $asset->getPlaybackIds();
        $playbackId = ! empty($playbackIds) ? $playbackIds[0]->getId() : null;

        return [
            'asset_id' => $asset->getId(),
            'playback_id' => $playbackId,
        ];
    }

    /**
     * Get asset details including playback IDs and status.
     *
     * @return array{id: string, status: string, playback_id: string|null}
     */
    public function getAsset(string $assetId): array
    {
        $response = $this->assetsApi->getAsset($assetId);
        $asset = $response->getData();

        $playbackIds = $asset->getPlaybackIds();
        $playbackId = ! empty($playbackIds) ? $playbackIds[0]->getId() : null;

        return [
            'id' => $asset->getId(),
            'status' => $asset->getStatus(),
            'playback_id' => $playbackId,
        ];
    }

    /**
     * Delete a Mux asset.
     */
    public function deleteAsset(string $assetId): void
    {
        $this->assetsApi->deleteAsset($assetId);
    }
}
