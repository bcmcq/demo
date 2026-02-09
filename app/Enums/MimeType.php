<?php

namespace App\Enums;

enum MimeType: string
{
    case ImageJpeg = 'image/jpeg';
    case ImagePng = 'image/png';
    case ImageGif = 'image/gif';
    case ImageWebp = 'image/webp';
    case VideoMp4 = 'video/mp4';

    /**
     * @return array<string>
     */
    public static function imageValues(): array
    {
        return [
            self::ImageJpeg->value,
            self::ImagePng->value,
            self::ImageGif->value,
            self::ImageWebp->value,
        ];
    }

    /**
     * @return array<string>
     */
    public static function videoValues(): array
    {
        return [
            self::VideoMp4->value,
        ];
    }

    /**
     * @return array<string>
     */
    public static function allValues(): array
    {
        return array_merge(self::imageValues(), self::videoValues());
    }
}
