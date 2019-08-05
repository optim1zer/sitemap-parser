<?php
/**
 * @author: Optim1zer <optim1zer777@gmail.com>
 * Date: 19.07.2019
 * Time: 17:23
 */

namespace optim1zer\sitemap;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

class SitemapLoader
{
    private $url;
    private $userAgent;
    private $tempFile;

    public function __construct($url, $userAgent)
    {
        $this->url = $url;
        $this->userAgent = $userAgent;
        $this->tempFile = tempnam(sys_get_temp_dir(), 'sitemap-xml');
        if ($url) {
            $this->tempFile .= '.' . pathinfo($url, PATHINFO_EXTENSION);
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
            $client = new Client();
            $response = $client->request('GET', $this->url, [
                'headers' => [
                    'User-Agent'  => $this->userAgent
                ],
                'allow_redirects' => true,
                'verify'          => false,
                'connect_timeout' => 20,
                'timeout'         => 60,
                'sink'            => $this->tempFile
            ]);
            if (!in_array($response->getStatusCode(), [200, 204], true)) {
                $result->setLoadError('Load error with http-code: '.$response->getStatusCode());
            }
        } catch (TransferException $e) {
            $result->setLoadError($e->getMessage());
        }
        return $result;
    }

    public function getSitemapPath()
    {
        return $this->tempFile;
    }
}