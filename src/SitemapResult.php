<?php
/**
 * @author: Optim1zer <optim1zer777@gmail.com>
 * Date: 19.07.2019
 * Time: 17:14
 */

namespace optim1zer\sitemap;


class SitemapResult
{
    const STATUS_SUCCESS     = 1;
    const STATUS_LOAD_ERROR  = 2;
    const STATUS_PARSE_ERROR = 3;

    protected $url;
    protected $status = self::STATUS_SUCCESS;
    protected $error;
    protected $maps = [];
    protected $urlsCount = 0;
    protected $breaked = false;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function isSuccess()
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function incrementUrls()
    {
        $this->urlsCount++;
    }

    public function addMap(array $mapElement)
    {
        $this->maps[] = $mapElement;
    }

    public function getMaps()
    {
        return $this->maps;
    }

    public function getMapUrls()
    {
        return array_map(function ($v) {
            return $v['loc'];
        }, $this->maps);
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setLoadError($error)
    {
        $this->status = self::STATUS_LOAD_ERROR;
        $this->error = $error;
    }

    public function isLoadError()
    {
        return $this->status === self::STATUS_LOAD_ERROR;
    }

    public function setParseError($error)
    {
        $this->status = self::STATUS_PARSE_ERROR;
        $this->error = $error;
    }

    public function isParseError()
    {
        return $this->status === self::STATUS_PARSE_ERROR;
    }

    public function getError()
    {
        return $this->error;
    }

    public function isBreaked()
    {
        return $this->breaked;
    }

    public function setBreaked()
    {
        $this->breaked = true;
    }
}