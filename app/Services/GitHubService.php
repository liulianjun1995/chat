<?php
/**
 * Created by PhpStorm.
 * User: LiXiang
 * Date: 2019/4/19
 * Time: 17:07
 */

namespace App\Services;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use function GuzzleHttp\Psr7\parse_query;

class GitHubService
{
    protected $accessToken = null;

    protected $code = null;
    protected $state = null;

    static $instance;

    public $error = null;

    protected $API_METHOD = [
        'code'          => 'https://github.com/login/oauth/authorize',
        'accessToken'   => 'https://github.com/login/oauth/access_token',
        'user'          => 'https://api.github.com/user'
    ];

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (!self::$instance instanceof self){
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * @param string $method
     * @return bool|mixed
     */
    private function getHttpUrl($method = '')
    {
        if (is_null($method) || empty(trim($method))) {
            return false;
        }
        return $this->API_METHOD[$method];
    }

    /**
     * @param null $code
     * @param null $token
     * @return $this
     */
    public function init($code = null, $state = null, $token = null)
    {
        $this->code = $code;
        $this->state = $state;
        $this->accessToken = $token;

        return $this;
    }

    // 获取 accessToken
    public function accessToken()
    {
        if (!$this->code){
            $this->error = '请先获取code';
            return false;
        }
        $url = $this->getHttpUrl('accessToken');
        $client = new Client();
        $response = $client->post($url, [
            'form_params' => [
                'client_id'     => config('services.github.client_id'),
                'client_secret' => config('services.github.client_secret'),
                'code'          => $this->code,
                'state'         => $this->state,
            ]
        ]);
        $body = parse_query($response->getBody());
        if (isset($body['error'])){
            $this->error = $body['error_description'];
            return false;
        }
        $this->accessToken = $body['access_token'];
        return $this->accessToken;
    }

    /**
     * 获取用户信息
     * @return array|bool|\Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function user()
    {
        $url = $this->getHttpUrl('user') . '?access_token=' . $this->accessToken;
        $client = new Client();
        try{
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Content-type'=> 'application/json'
                ]
            ]);
            $body = $response->getBody()->getContents();
        }catch (RequestException $exception){
            $this->error = $exception->getMessage();
            return false;
        }
        return json_decode($body, true);
    }
}