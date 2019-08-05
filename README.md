Sitemap Parser
=======

Sitemap and sitemap index builder.

Features
--------

- Parse sitemap.xml files: either regular or gzipped.
- Parse sitemap index files.
- Very memory efficient, able to parse HUGE number of sitemap files.

Installation
------------

Installation via Composer is very simple:

```
composer require optim1zer/sitemap-parser
```

After that, make sure your application autoloads Composer classes by including
`vendor/autoload.php`.

How to use it
-------------

### Basic example
Parses any sitemap detected while parsing, to get an complete list of urls and maps.
All urls saved to $allUrls array.
In $results array - set of SitemapResult about all maps parsed.
Maximum quantity of maps for parsing can be limited by second parameter of SitemapParser::parse() (by default = 1000)

```php
$allUrls = [];
$parser = new SitemapParser(function ($urls) use (&$allUrls) {
    foreach ($urls as $url) {
        $allUrls[] = $url['loc'];
    }
});
$results = $parser->parse('http://example.com/sitemap.xml');
```

### Parse single xml from URL
Parses only 1 sitemap.xml even if it's sitemap index. 
Returns single SitemapResult
```php
$allUrls = [];
$parser = new SitemapParser(function ($urls) use (&$allUrls) {
        $allUrls = $urls;
});
$result = $parser->parseUrl('http://example.com/sitemap.xml');
```


### Parse single xml from file
Parses single XML sitemap from file.
Returns single SitemapResult
```php
$allUrls = [];
$parser = new SitemapParser(function ($urls) use (&$allUrls) {
        $allUrls = $urls;
});
$result = $parser->parseFile('/tmp/sitemap.xml');
```
