<?php
/**
 * Created by PhpStorm.
 * User: LiXiang
 * Date: 2019/5/31
 * Time: 11:22
 */

namespace App\Services;

use GuzzleHttp\Client;

/**
 * 螺丝帽人机验证
 * Class LuoCaptchaService
 * @package App\Services
 */
class LuoCaptchaService
{
	private $api_key = null;

	private $response = null;

	private $verify_url = 'https://captcha.luosimao.com/api/site_verify';

	public function __construct ($response = '')
	{
		$this->response = $response;
		$this->api_key = env('LUOCAPTCHA_API_KEY');
	}

	/**
	 * @return bool
	 */
	public function checkResponse ()
	{
		$client = new Client();
		$response = $client->post($this->verify_url, [
			'form_params' => [
				'api_key'  => $this->api_key,
				'response' => $this->response,
			],
		]);
		$result = json_decode($response->getBody()->getContents(), true);

		return (int)$result['error'] === 0 && $result['res'] === 'success';
	}
}