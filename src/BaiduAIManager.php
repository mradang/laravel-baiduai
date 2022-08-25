<?php

namespace mradang\LaravelBaiduAI;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BaiduAIManager
{
    protected $app;

    protected $baseUrl = 'https://aip.baidubce.com';

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function getAccessToken()
    {
        $key = config('baiduai.appkey') . __FUNCTION__;
        $token = Cache::get($key, '');

        if ($token) {
            return $token;
        }

        Cache::lock($key, 10)->block(5, function () use ($key, &$token) {
            $token = Cache::get($key);
            if (empty($token)) {
                $res = $this->request('/oauth/2.0/token', 'post', [
                    'grant_type' => 'client_credentials',
                    'client_id' => config('baiduai.appkey'),
                    'client_secret' => config('baiduai.appsecret'),
                ], false);
                $token = data_get($res, 'access_token', '');
                if ($token) {
                    Cache::put($key, $token, (data_get($res, 'expires_in') ?? 7200) - 60);
                }
            }
        });

        return $token;
    }

    private function request(string $url, string $method, array $params = [], bool $withToken = true)
    {
        $headers = [];
        if ($withToken) {
            $params['access_token'] = $this->getAccessToken();
        }

        $options = [
            'proxy' => config('baiduai.proxy'),
            'base_uri' => $this->baseUrl,
        ];

        $params = !empty($params) ? $params : null;
        $response = Http::withHeaders($headers)->withOptions($options)->retry(3, 2000)->$method($url, $params);

        if ($response->successful() && $response['errcode'] === 0) {
            Log::info("[laravel-baiduai][$method][$this->baseUrl][$url]" . (string)$response);
            return $response;
        } else {
            Log::error("[laravel-baiduai][$method][$this->baseUrl][$url]" . (string)$response);
        }
    }

    public function get(string $api, array $params = [])
    {
        return $this->request($api, 'get', $params);
    }

    public function post(string $api, array $params = [])
    {
        return $this->request($api, 'post', $params);
    }
}
