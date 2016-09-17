<?php

namespace Solodkiy\ComposerMonorepoHelper;

use Composer\Json\JsonFile;
use Composer\Package\Package;
use Version\Version;

class Utils
{
    /**
     * @param Package $package
     * @return string
     */
    public static function packageChecksum(Package $package)
    {
        $packagePath = realpath($package->getDistUrl()) . DIRECTORY_SEPARATOR;

        $json = file_get_contents($packagePath.'composer.json');
        return self::getComposerJsonChecksum($json);
    }

    /**
     * @param $composerJsonContent
     * @return string
     */
    public static function getComposerJsonChecksum($composerJsonContent)
    {
        $data = JsonFile::parseJson($composerJsonContent);
        if (!isset($data['extra'])) {
            $data['extra'] = [];
        }

        // Remove dynamic params
        unset($data['version']);
        unset($data['extra']['checksum']);

        return md5(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * @param $currentVersion
     * @return string newMicroVersion
     */
    public static function getNextMicroVersion($currentVersion)
    {
        $numList = explode('.', $currentVersion);
        while (count($numList) < 4) {
            $numList[] = 0;
        }
        $numList[3]++;
        return implode('.', $numList);
    }
}