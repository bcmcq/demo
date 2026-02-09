<?php

namespace App\Services;

use MuxPhp\Api\AssetsApi;
use MuxPhp\Configuration;
use MuxPhp\Models\CreateAssetRequest;
use MuxPhp\Models\InputSettings;
use MuxPhp\Models\PlaybackPolicy;

class MuxService
{
    protected Configuration $config;

    protected AssetsApi $assetsApi;

    public function __construct()
    {
        $this->config = Configuration::getDefaultConfiguration()
            ->setUsername(config('services.mux.token_id'))
            ->setPassword(config('services.mux.token_secret'));

        $this->assetsApi = new AssetsApi(config: $this->config);
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
