<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\HttpService;
use Illuminate\Support\Facades\Log;

class LatestNews extends Component
{
    public $selectedApi;
    public $news;
    protected HttpService $httpService;

    // niente URL liberi dal client: solo alias controllati
    private array $apiMap = [
        'news_it' => 'https://newsapi.org/v2/top-headlines?country=it&apiKey=5fbe92849d5648eabcbe072a1cf91473',
        'news_us' => 'https://newsapi.org/v2/top-headlines?country=us&apiKey=5fbe92849d5648eabcbe072a1cf91473',
        'news_gb' => 'https://newsapi.org/v2/top-headlines?country=gb&apiKey=5fbe92849d5648eabcbe072a1cf91473',
        // test:
        // 'internal_finance' => 'http://internal.finance:8001/user-data.php',
    ];

    public function __construct()
    {
        $this->httpService = app(HttpService::class);
    }

    public function fetchNews()
    {
        $this->news = null;

        if (!$this->selectedApi || !array_key_exists($this->selectedApi, $this->apiMap)) {
            Log::warning('SSRF blocked: invalid source alias -'
                . ' selected_api: ' . $this->selectedApi
                . ', user_id: ' . auth()->id()
                . ', ip: ' . request()->ip()
            );
            $this->news = 'Invalid source';
            return;
        }

        try {
            $url = $this->apiMap[$this->selectedApi];
            $raw = $this->httpService->getRequest($url);
            $this->news = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            Log::warning('News fetch failed/blocked -'
                . ' selected_api: ' . $this->selectedApi
                . ', user_id: ' . auth()->id()
                . ', ip: ' . request()->ip()
                . ', error: ' . $e->getMessage()
            );
            $this->news = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.latest-news');
    }
}
