<?php

namespace DazzaDev\DgtCr;

use Composer\InstalledVersions;
use Exception;

class Listing
{
    /**
     * Get data directory
     */
    private static function getDataDirectory(): string
    {
        return InstalledVersions::getInstallPath('dazza-dev/dgt-xml-generator').'/src/Data/';
    }

    /**
     * Get listings
     */
    public static function getListings(): array
    {
        $dataDirectory = self::getDataDirectory();

        if (! is_dir($dataDirectory)) {
            throw new Exception('Directory not found');
        }

        $files = array_diff(scandir($dataDirectory), ['..', '.']);
        $fileNamesWithoutExtension = array_map(function ($file) {
            return pathinfo($file, PATHINFO_FILENAME);
        }, $files);

        return array_values($fileNamesWithoutExtension);
    }

    /**
     * Get Listing By Type
     */
    public static function getListing(string $type): array
    {
        $filePath = self::getDataDirectory()."$type.json";
        if (! file_exists($filePath)) {
            throw new Exception('File not found');
        }

        return json_decode(file_get_contents($filePath), true);
    }
}
