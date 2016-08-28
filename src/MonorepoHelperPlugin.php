<?php

namespace Solodkiy\ComposerMonorepoHelper;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\Package;
use Composer\Plugin\PluginInterface;
use Composer\Repository\PathRepository;
use Composer\Script\Event;

class MonorepoHelperPlugin implements PluginInterface, EventSubscriberInterface
{

    /**
     * Apply plugin modifications to Composer
     *
     * @param Composer $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        // Nothing
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     * * The method name to call (priority defaults to 0)
     * * An array composed of the method name to call and the priority
     * * An array of arrays composed of the method names to call and respective
     *   priorities, or 0 if unset
     *
     * For instance:
     *
     * * array('eventName' => 'methodName')
     * * array('eventName' => array('methodName', $priority))
     * * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            'pre-update-cmd' => 'preUpdate'
        ];
    }

    public function preUpdate(Event $e)
    {
        $repositoryManager = $e->getComposer()->getRepositoryManager();
        foreach ($repositoryManager->getRepositories() as $repository) {
            if ($repository instanceof PathRepository) {
                $this->processRepository($repository);
            }
        }
    }

    private function processRepository(PathRepository $repo)
    {
        $packages = $repo->getPackages();
        foreach ($packages as $pack) {
            if ($pack instanceof Package) {
                $this->processPackage($pack);
            }
        }
    }

    private function processPackage(Package $pack)
    {
        $extra = $pack->getExtra();
        $prevCheckSum = isset($extra['checksum']) ? $extra['checksum'] : null;
        $currentChecksum = Utils::packageChecksum($pack);

        if ($currentChecksum != $prevCheckSum) {
            $newVersion = Utils::getNextMicroVersion($pack->getVersion());
            $pack->replaceVersion($newVersion, $newVersion);
            $this->updateComposeJson($pack, $newVersion, $currentChecksum);
        }
    }

    private function updateComposeJson(Package $pack, $newVersion, $currentChecksum)
    {
        $packagePath = realpath($pack->getDistUrl()) . DIRECTORY_SEPARATOR;
        $composerFilePath = $packagePath.'composer.json';

        $json = file_get_contents($composerFilePath);
        $data = JsonFile::parseJson($json);
        if (!isset($data['extra'])) {
            $data['extra'] = [];
        }
        $data['version'] = $newVersion;
        $data['extra']['checksum'] = $currentChecksum;

        file_put_contents($composerFilePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }


}