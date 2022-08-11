<?php

namespace App\Console\Commands;

use DiDom\Document;
use Illuminate\Console\Command;

class UrlHtmlTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'url:html-tags {url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $url = $this->argument('url');

        if (!$this->isDomainAvailable($url)) {
            $this->error('Not connect is url: ' . $url);

            return 0;
        }

        $document = new Document($url, true);

        $links = [];

        foreach($document->find('a') as $a) {
            if ($a->attr('href') != '') {
                $links[] = $a->attr('href');
            }
        }

        $images = [];

        foreach($document->find('img') as $img) {
            if ($img->attr('src') != '') {
                $images[] = $img->attr('src');
            }
        }

        $scripts = [];

        foreach($document->find('script') as $script) {
            if ($script->attr('src') != '') {
                $scripts[] = $script->attr('src');
            }
        }

        $styles = [];

        foreach($document->find('link') as $link) {
            if ($link->attr('href') != '') {
                $styles[] = $link->attr('href');
            }
        }

        $this->info(json_encode([
            'a' => $links,
            'img' => $images,
            'script' => $scripts,
            'link' => $styles,
        ], JSON_PRETTY_PRINT |JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return 1;
    }

    public function isDomainAvailable($domain): bool
    {
        if (!filter_var($domain, FILTER_VALIDATE_DOMAIN)) {
            return false;
        }

        $curlInit = curl_init($domain);
        curl_setopt($curlInit, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curlInit, CURLOPT_HEADER, true);
        curl_setopt($curlInit, CURLOPT_NOBODY, true);
        curl_setopt($curlInit, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curlInit);
        curl_close($curlInit);

        if ($response) {
            return true;
        }

        return false;
    }

}
