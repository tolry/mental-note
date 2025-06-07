<?php

declare(strict_types=1);

namespace App\Thumbnail;

use App\Factory\MetainfoFactory;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class ThumbnailService
{
    private Filesystem $fs;
    private string $filepattern;

    public function __construct(
        #[Autowire('%kernel.project_dir%/public')] private readonly string $documentRoot,
        #[Autowire('%kernel.cache_dir%')] private readonly string $cacheDir,
        private readonly MetainfoFactory $metainfoFactory
    ) {
        $this->fs = new Filesystem();
        $this->filepattern = 'thumbnails/{name}_{width}x{height}.png';
    }

    /**
     * @param string $file
     * @param mixed  $url
     */
    public function getImageForUrl(string $url, string $file): void
    {
        $imageUrl = $this->metainfoFactory->create($url)->getImageUrl();

        if ($imageUrl) {
            file_put_contents($file, file_get_contents($imageUrl));

            return;
        }

        throw new \Exception('no file found for url ' . $url);
    }

    public function generate(string $url, int $width, int $height, string $name): Thumbnail
    {
        $hash = md5($url);

        $thumbnail = new Thumbnail();
        $thumbnail->url = $url;
        $thumbnail->width = $width;
        $thumbnail->height = $height;
        $thumbnail->relativePath = $this->compilePattern($width, $height, $name);
        $thumbnail->absolutePath = $this->documentRoot . '/' . $thumbnail->relativePath;

        if ($this->fs->exists($thumbnail->absolutePath)) {
            return $thumbnail;
        }

        $tmpFile = $this->cacheDir . '/' . $hash;

        if (!$this->fs->exists($this->cacheDir)) {
            $this->fs->mkdir($this->cacheDir);
        }

        if (!$this->fs->exists($tmpFile)) {
            $this->getImageForUrl($url, $tmpFile);
        }

        if (!$this->fs->exists(dirname($thumbnail->absolutePath))) {
            $this->fs->mkdir(dirname($thumbnail->absolutePath));
        }

        $cmd = "convert %s[0] -resize '%dx%d' -gravity center -crop %dx%d+0+0 +repage %s";

        $process = Process::fromShellCommandline(sprintf(
            $cmd,
            $tmpFile,
            $width,
            $height,
            $width,
            $height,
            $thumbnail->absolutePath
        ));
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }

        return $thumbnail;
    }

    private function compilePattern(int $width, int $height, string $name): string
    {
        $search = ['{width}', '{height}', '{name}'];
        $replace = [$width, $height, $name];

        return str_replace($search, $replace, $this->filepattern);
    }
}
