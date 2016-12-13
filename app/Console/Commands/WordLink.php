<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

class WordLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wordlink:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate word link';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $links = $this->zinchNodes();
        //dd($links);
        DB::connection('zixun')->table('word_link')->insert($links);
    }

    private function zinchNodes()
    {
        $links = [];
        DB::table('node')
            ->join('content_type_undergradschool', 'node.nid', '=', 'content_type_undergradschool.nid')
            ->where('node.type', 'undergradschool')
            ->where('node.status', 1)
            ->where('content_type_undergradschool.field_instnm_chinese_value', '!=', '')
            ->where('content_type_undergradschool.field_school_type_value', '!=', 18)
            ->whereNotNull('content_type_undergradschool.field_instnm_chinese_value')
            ->select('node.nid as nid', 'content_type_undergradschool.field_instnm_chinese_value as title')
            ->chunk(500, function ($nodes) use (&$links) {
                foreach ($nodes as $node) {
                    $link = [];
                    $link['text'] = $node->title;
                    $link['url'] = 'http://www.zinch.cn/'.$this->getZinchPath('node/'.$node->nid);
                    $link['url_title'] = $node->title;
                    $link['except_list'] = '';
                    $links[] = $link;
                }
            });

        return $links;
    }

    private function getZinchPath($path)
    {
        $dst = DB::table('url_alias')->where('src', $path)->value('dst');

        return $dst ?: $path;
    }
}
