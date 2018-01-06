<?php

namespace AppBundle\Thumbnail;

use AppBundle\Cache\MetainfoCache;
use AppBundle\Factory\MetainfoFactory;
use AppBundle\Url\MetaInfo;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class ThumbnailService
{
    private $documentRoot;
    private $filepattern;
    private $cacheDir;
    private $fs;
    private $metainfoFactory;

    public function __construct($documentRoot, $cacheDir, $filepattern, MetainfoFactory $metainfoFactory)
    {
        $this->documentRoot = $documentRoot;
        $this->cacheDir = $cacheDir;
        $this->filepattern = $filepattern;
        $this->fs = new Filesystem();
        $this->metainfoFactory = $metainfoFactory;
    }

    private function compilePattern($width, $height, $name)
    {
        $search  = array('{width}', '{height}', '{name}');
        $replace = array($width, $height, $name);

        return str_replace($search, $replace, $this->filepattern);
    }

    /**
     * @param string $file
     */
    public function getImageForUrl($url, $file)
    {
        $imageUrl = $this->metainfoFactory->create($url)->getImageUrl();

        if ($imageUrl) {
            file_put_contents($file, file_get_contents($imageUrl));

            return;
        }

        throw new \Exception('no file found for url '.$url);
    }

    public function generate($url, $width, $height, $name)
    {
        $hash = md5($url);

        $thumbnail               = new Thumbnail();
        $thumbnail->url          = $url;
        $thumbnail->width        = $width;
        $thumbnail->height       = $height;
        $thumbnail->relativePath = $this->compilePattern($width, $height, $name);
        $thumbnail->absolutePath = $this->documentRoot.'/'.$thumbnail->relativePath;

        if ($this->fs->exists($thumbnail->absolutePath)) {
            return $thumbnail;
        }

        $tmpFile = $this->cacheDir.'/'.$hash;

        if (!$this->fs->exists($this->cacheDir)) {
            $this->fs->mkdir($this->cacheDir);
        }

        if (!$this->fs->exists($tmpFile)) {
            $this->getImageForUrl($url, $tmpFile);
        }

        if (!$this->fs->exists(dirname($thumbnail->absolutePath))) {
            $this->fs->mkdir(dirname($thumbnail->absolutePath));
        }

        $cmd = "convert %s[0] -resize '%dx%d^' -gravity center -crop %dx%d+0+0 +repage %s";

        $process = new Process(sprintf(
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
}
