<?php

namespace App\Repos;

use Carbon\Carbon;
use DB;

class Zinch
{
    public function zinch()
    {
        $pages = $this->zinchFronts();
        $pages = array_merge($pages, $this->zinchChannels());
        $pages = array_merge($pages, $this->zinchNodes());

        return $pages;
    }

    public function zixun()
    {
        $pages = $this->zixunFronts();
        $pages = array_merge($pages, $this->zixunChannels());
        $pages = array_merge($pages, $this->zixunTerms());
        $pages = array_merge($pages, $this->zixunNodes());

        return $pages;
    }

    private function zinchFronts()
    {
        $slugs = ['zixun'];
        $modified = Carbon::now()->toDateTimeString();
        $priority = '1.0';
        $freq = 'daily';

        return $this->pagesPreprocess($slugs, $modified, $priority, $freq);
    }

    private function zixunFronts()
    {
        $slugs = ['zixun'];
        $modified = Carbon::now()->toDateTimeString();
        $priority = '1.0';
        $freq = 'daily';

        return $this->pagesPreprocess($slugs, $modified, $priority, $freq);
    }

    private function zinchChannels()
    {
        $slugs = [
            'top',
            'top/world',
            'top/university/world/usnews2016',
            'top/university/367583/2016/367589/367632',
            'top/university/367583/2016/367589/367630',
            'top/university/367583/2016/367589/367771',
            'top/university/367583/2016/367589/367631',
            'top/university/367584/2017/367589/367633',
            'top/university/367584/2017/367589/367634',
            'top/university/367584/2015/367945/367946',
            'top/university/367584/2016/367589/367950',
            'top/university/367585/2015/367589/367949',
            'top/uk',
            'top/uk/times',
            'top/university/367586/2016/367589/367641',
            'top/au',
            'top/us',
            'top/qs',
            'top/usnews',
            'ss/us',
            'ss/us/hs',
            'ss/us/program',
            'ss/us/business',
            'ss/us/co',
            'ss/us/graduate',
            'ss/IEP',
            'ss/uk',
            'ss/au',
            'ss/ca',
            'ss/jp',
            'ss/jp/language',
            'ss/schools/Europe',
            'ss/schools/Asia',
            'ss/art',
            'journey/my-chance',
            'match/school-match-report',
            'journey/apply/gpacalculat',
            'journey/prepare/sat2400',
            'journey/ielts',
            'journey/gmat',
            'journey/finance',
            'match/advisor/submit-1',
            'match/comment/submit-1',
        ];
        $modified = Carbon::now()->toDateTimeString();
        $priority = '0.9';
        $freq = 'weekly';

        return $this->pagesPreprocess($slugs, $modified, $priority, $freq);
    }

    private function zixunChannels()
    {
        $slugs = [
            'zixun/art',
            'zixun/zhuanti',
            'zixun/hotuniversity',
        ];
        $modified = Carbon::now()->toDateTimeString();
        $priority = '0.9';
        $freq = 'weekly';

        return $this->pagesPreprocess($slugs, $modified, $priority, $freq);
    }

    private function zixunTerms()
    {
        $pages = [];
        $tids = DB::connection('zixun')
            ->table('taxonomy_term_data')
            ->whereIn('vid', [2, 3, 4])
            ->pluck('tid');
        foreach ($tids as $tid) {
            $page = [];
            $page['slug'] = 'http://www.zinch.cn/zixun/'.$this->getZixunPath('taxonomy/term/'.$tid);
            $page['modified'] = Carbon::now()->toDateTimeString();
            $page['priority'] = '0.5';
            $page['freq'] = 'weekly';
            $pages[] = $page;
        }

        return $pages;
    }

    private function zinchNodes()
    {
        $pages = [];
        DB::table('node')
            ->where('type', 'undergradschool')
            ->where('status', 1)
            ->chunk(500, function ($nodes) use (&$pages) {
                foreach ($nodes as $node) {
                    $tabs = ['', '/info', '/major_grad', '/transfer', '/rank', '/news', '/media'];
                    foreach ($tabs as $tab) {
                        $page = [];
                        $page['slug'] = 'http://www.zinch.cn/'.$this->getZinchPath('node/'.$node->nid).$tab;
                        $page['modified'] = Carbon::createFromTimestamp($node->changed)->toDateTimeString();
                        $page['priority'] = '0.5';
                        $page['freq'] = 'weekly';
                        $pages[] = $page;
                    }
                }
            });

        return $pages;
    }

    private function zixunNodes()
    {
        $pages = [];
        DB::connection('zixun')
            ->table('node')
            ->where('type', 'news')
            ->where('status', 1)
            ->chunk(500, function ($nodes) use (&$pages) {
                foreach ($nodes as $node) {
                    $page = [];
                    $page['slug'] = 'http://www.zinch.cn/zixun/'.$this->getZixunPath('node/'.$node->nid);
                    $page['modified'] = Carbon::createFromTimestamp($node->changed)->toDateTimeString();
                    $page['priority'] = '0.5';
                    $page['freq'] = 'weekly';
                    $pages[] = $page;
                }
            });

        return $pages;
    }

    private function pagesPreprocess($slugs, $modified, $priority, $freq)
    {
        $pages = [];
        foreach ($slugs as $slug) {
            $slug = 'http://www.zinch.cn/'.$slug;
            $slug = trim($slug, '/');
            $pages[] = compact('slug', 'modified', 'priority', 'freq');
        }

        return $pages;
    }

    private function getZinchPath($path)
    {
        $dst = DB::table('url_alias')->where('src', $path)->value('dst');

        return $dst ?: $path;
    }

    private function getZixunPath($path)
    {
        $alias = DB::connection('zixun')->table('url_alias')->where('source', $path)->value('alias');

        return $alias ?: $path;
    }
}
