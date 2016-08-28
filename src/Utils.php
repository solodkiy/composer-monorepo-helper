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
        $packageFiles = self::getPackageFiles($packagePath);
        $index = implode("\n", $packageFiles);

        $hashes = md5($index) . "\n";
        foreach ($packageFiles as $filePath) {
            $hash = self::getFileChecksum($filePath, $packagePath);
            $hashes .= $hash . "\n";
        }

        return md5($hashes);
    }

    /**
     * @param $filePath
     * @param $packagePath
     * @return string
     */
    private static function getFileChecksum($filePath, $packagePath)
    {
        if ($filePath == 'composer.json') {
            $json = file_get_contents($packagePath . $filePath);
            $data = JsonFile::parseJson($json);
            if (!isset($data['extra'])) {
                $data['extra'] = [];
            }

            // Remove dynamic params
            unset($data['version']);
            unset($data['extra']['checksum']);

            return md5(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            return md5(file_get_contents($packagePath . $filePath));
        }
    }

    /**
     * @param $packagePath
     * @return array
     */
    private static function getPackageFiles($packagePath)
    {
        $packageFiles = self::directoryList($packagePath, true);
        $packageFiles = array_filter($packageFiles, function ($path) {
            if (strpos($path, 'vendor/') === 0) {
                return false;
            }
            if ($path == 'composer.lock') {
                return false;
            }
            return true;
        });
        return $packageFiles;
    }

    /**
     * @param $directory
     * @param bool $recursive
     * @param bool $isFullPath
     * @return array
     */
    private static function directoryList($directory, $recursive = false, $isFullPath = false)
    {
        $directory = rtrim($directory, '/');
        if (!file_exists($directory) || !is_dir($directory)) {
            return array();
        }

        $files = scandir($directory);
        $resultFiles = array();
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $currentPath = $directory . '/' . $file;
            if ($recursive && is_dir($currentPath)) {
                $dirFiles = self::directoryList($currentPath, true);
                foreach ($dirFiles as $dirFile) {
                    if ($isFullPath) {
                        $resultFiles[] = $currentPath . '/' . $dirFile;
                    } else {
                        $resultFiles[] = $file . '/' . $dirFile;
                    }
                }
            } else {
                if ($isFullPath) {
                    $resultFiles[] = $currentPath;
                } else {
                    $resultFiles[] = $file;
                }
            }
        }
        return $resultFiles;
    }

    /**
     * @param $currentVersion
     * @return string newMicroVersion
     */
    public static function getNextMicroVersion($currentVersion)
    {
        $versionModel = Version::parse($currentVersion);
        $versionModel->setMicro($versionModel->getMicro() + 1);
        return (string)$versionModel;
    }
}