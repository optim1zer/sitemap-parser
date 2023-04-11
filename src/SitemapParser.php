<?php
/**
 * @author: Optim1zer <optim1zer777@gmail.com>
 * Date: 18.07.2019
 * Time: 7:46
 */

namespace optim1zer\sitemap;


use SimpleXMLElement;
use XMLReader;

class SitemapParser
{
    protected $callback;
    protected $userAgent;
    protected $guzzleOptions;
    protected $limit = 50000;

    public function __construct(callable $callback, $userAgent = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', array $guzzleOptions = [])
    {
        $this->callback = $callback;
        $this->userAgent = $userAgent;
        $this->guzzleOptions = $guzzleOptions;
    }

    /**
     * @param string|array $url
     * @param int $mapsLimit
     * @return SitemapResult[] $results
     */
    public function parse($url, $mapsLimit = 1000)
    {
        $maps = is_array($url)? $url : [$url];
        $results = [];
        while (count($maps) && --$mapsLimit >= 0) {
            $result = $this->parseUrl(array_shift($maps));
            $results[$result->getUrl()] = $result;
            foreach ($result->getMapUrls() as $mapUrl) {
                if (!isset($results[$mapUrl]) && !in_array($mapUrl, $maps, true)) {
                    $maps[] = $mapUrl;
                }
            }
            if ($result->isBreaked()) {
                break;
            }
        }
        return $results;
    }

    public function parseFile($path)
    {
        $result = new SitemapResult('');
        $this->parseXml($path, $result);
        return $result;
    }

    protected function parseUrl($url)
    {
        $loader = new SitemapLoader($url, $this->userAgent, $this->guzzleOptions);
        $result = $loader->load();
        if ($result->isSuccess()) {
            $this->parseXml($loader->getSitemapPath(), $result);
        }
        return $result;
    }

    protected function parseXml($path, SitemapResult $result)
    {
        if (strtolower(substr($path, -3)) == '.gz') {
            $path = 'compress.zlib://'.$path;
        }
        libxml_clear_errors();
        libxml_use_internal_errors(true);
        $xml = new XMLReader();
        $xml->open($path);

        $urls = [];
        $counter = 0;
        $parent = null;
        while ($xml->read()) {
            if ($xml->nodeType === XMLReader::ELEMENT) {
                if ($xml->depth == 0) {
                    $parent = $xml->name;
                    continue;
                }
                if ($xml->depth == 1 && ($element = $this->getElementFromXml($xml))) {
                    if (libxml_get_last_error()) {
                        break;
                    }
                    if ($xml->name === 'sitemap' && $parent == 'sitemapindex') {
                        $result->addMap($this->getSitemapFromElement($element));
                    } elseif ($xml->name === 'url' && $parent == 'urlset') {
                        $result->incrementUrls();
                        $urls[] = $this->getUrlFromElement($element);
                        if (++$counter >= $this->limit) {
                            $this->processUrls($urls, $result);
                            $urls = [];
                            $counter = 0;
                            if ($result->isBreaked()) {
                                break;
                            }
                        }
                    }
                }
            }
        }
        $xml->close();
        if ($xmlError = libxml_get_last_error()) {
            $result->setParseError($xmlError->message . ' (Line: ' . $xmlError->line . ', column: ' . $xmlError->column . ')');
            return;
        }
        if (count($urls) > 0) {
            $this->processUrls($urls, $result);
        }
    }

    protected function processUrls($urls, SitemapResult $result)
    {
        $r = call_user_func($this->callback, $urls, $result->getUrl());
        if ($r === false) {
            $result->setBreaked();
        }
    }

    protected function getSitemapFromElement(array $element)
    {
        return [
            'loc'        => $element['loc'],
            'lastmod'    => isset($element['lastmod']) ? $element['lastmod'] : null,
        ];
    }

    protected function getUrlFromElement(array $element)
    {
        return [
            'loc'        => $element['loc'],
            'lastmod'    => isset($element['lastmod'])    ? $element['lastmod']    : null,
            'changefreq' => isset($element['changefreq']) ? $element['changefreq'] : null,
            'priority'   => isset($element['priority'])   ? $element['priority']   : null,
        ];
    }

    protected function getElementFromXml(XMLReader $xml)
    {
        try {
            $element = new SimpleXMLElement($xml->readOuterXML(), LIBXML_NOCDATA);
            $element = (array)$element;
            return isset($element['loc']) && is_string($element['loc']) ? $element : false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

}