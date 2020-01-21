<?php declare(strict_types=1);
/**
 * This file is part of guzzle hook plugin.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-01-21 16:15:09 +0800
 */

namespace GuzzleHttp;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Util\Filesystem;
use ComposerIncludeFiles\Composer\AutoloadGenerator;
use ReflectionFunction;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var \Composer\Composer
     */
    protected $composer;

    /**
     * @var \ComposerIncludeFiles\Composer\AutoloadGenerator
     */
    protected $generator;

    public static function getSubscribedEvents()
    {
        return [
            'post-autoload-dump' => 'dumpFiles',
        ];
    }

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->generator = new AutoloadGenerator($composer->getEventDispatcher(), $io);
    }

    public function dumpFiles(): void
    {
        $filePath = __DIR__ . '/functions_include.php';
        $config = $this->composer->getConfig();

        $filesystem = new Filesystem;
        $filesystem->ensureDirectoryExists($config->get('vendor-dir'));
        $vendorPath = $filesystem->normalizePath(realpath(realpath($config->get('vendor-dir'))));
        $autoloadFilesFile = $vendorPath.'/composer/autoload_files.php';
        if (!is_file($autoloadFilesFile)) {
            return;
        }

        $files = include $autoloadFilesFile;
        foreach ($files as $fileName) {
            if ($this->stringEndwith($fileName, '/guzzlehttp/guzzle/src/functions_include.php')) {
                $path = dirname($fileName) . '/functions.php';
                if (!function_exists('GuzzleHttp\choose_handler')) {
                    include $path;
                }
                $refFunction = new ReflectionFunction('GuzzleHttp\choose_handler');
                $content = file_get_contents($path);
                $eol = $this->getEOL($content);
                $contents = explode($eol, $content);
                for ($i = $refFunction->getStartLine() - 1; $i < $refFunction->getEndLine(); ++$i) {
                    unset($contents[$i]);
                }
                $content = implode($eol, $contents);
                file_put_contents($filePath, $content);
                break;
            }
        }

        $this->generator->dumpFiles($this->composer, [
            $vendorPath . '/fwkit/guzzle-hook/src/functions.php',
            $vendorPath . '/fwkit/guzzle-hook/src/functions_include.php',
        ]);
    }

    protected function stringEndwith($string, $compare)
    {
        return substr($string, -strlen($compare)) === $compare;
    }

    protected function getEOL($content)
    {
        static $eols = [ "\r\n", "\n", "\r"];
        foreach ($eols as $eol) {
            if (strpos($content, $eol)) {
                return $eol;
            }
        }

        return PHP_EOL;
    }
}
