<?php

namespace App\Http\Controllers;

use App\Repos\Zinch;

class SitemapController extends Controller
{
    private $zinch;

    public function __construct(Zinch $zinch)
    {
        $this->zinch = $zinch;
    }

    public function index()
    {
        $sitemap = app('sitemap');

        $zinch = $this->zinch->zinch();
        $this->add($zinch, $sitemap);

        $zixun = $this->zinch->zixun();
        $this->add($zixun, $sitemap);

        $sitemap->store('xml', 'sitemap', 'sitemap');

        return $sitemap->render('xml');
    }

    private function add($pages, $sitemap)
    {
        foreach ($pages as $page) {
            $sitemap->add($page['slug'], $page['modified'], $page['priority'], $page['freq']);
        }
    }
}
