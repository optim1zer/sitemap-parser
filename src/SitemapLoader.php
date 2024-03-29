<?php
/**
 * @author: Optim1zer <optim1zer777@gmail.com>
 * Date: 19.07.2019
 * Time: 17:23
 */

namespace optim1zer\sitemap;


use GuzzleHttp\Client;

class SitemapLoader
{
    private $url;
    private $userAgent;
    private $guzzleOptions;
    private $tempFile;

    public function __construct($url, $userAgent, array $guzzleOptions = [])
    {
        $this->url = $url;
        $this->userAgent = $userAgent;
        $this->guzzleOptions = $guzzleOptions;
        $this->tempFile = tempnam(sys_get_temp_dir(), 'sitemap-xml');
        if ($url && ($ext = pathinfo($url, PATHINFO_EXTENSION))) {
            $ext = preg_match('~[^a-z]~is', $ext) ? 'xml' : $ext;
            $ext = '.'.$ext;
            if (rename($this->tempFile, $this->tempFile . $ext)) {
                $this->tempFile .= $ext;
            }
        }
    }

    public function __destruct()
    {
        @unlink($this->tempFile);
    }

    public function load()
    {
        $result = new SitemapResult($this->url);
        try {
            $client = new Client(array_replace_recursive([
                'allow_redirects' => true,
                'verify'          => false,
                'connect_timeout' => 20,
                'timeout'         => 60,
            ], $this->guzzleOptions));
            $response = $client->request('GET', $this->url, [
                'headers' => [
                    'User-Agent' => $this->userAgent
                ],
                'sink'    => $this->tempFile
            ]);
            if (!in_array($response->getStatusCode(), [200, 204], true)) {
                $result->setLoadError('HTTP-code: '.$response->getStatusCode());
            }
        } catch (\Throwable $e) {
            $result->setLoadError($e->getMessage());
        }
        return $result;
    }

    public function getSitemapPath()
    {
        return $this->tempFile;
    }
}