<?php

namespace App\Console\Commands;

use App\Jobs\FetchArticlesFromGuardian;
use App\Jobs\FetchArticlesFromNewsAPI;
use App\Jobs\FetchArticlesFromNYT;
use Illuminate\Console\Command;

class FetchArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:articles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch articles from various news sources and save to the database';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        FetchArticlesFromNewsAPI::dispatch();
        FetchArticlesFromNYT::dispatch();
        FetchArticlesFromGuardian::dispatch();

        $this->info('Articles fetching job has been dispatched.');
    }
}
