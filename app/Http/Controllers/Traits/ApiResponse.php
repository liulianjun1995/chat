<?php
namespace App\Http\Controllers\Traits;

trait ApiResponse
{
    /**
     * 创建响应信息
     * @param bool $code
     * @param array $respond_data
     * @param string $message
     * @return array
     */
    protected function respond($code = false, $respond_data = [], $message = '') : array
    {
        return ['code' => $code, 'data' => $respond_data, 'message' => $message];
    }

    /**
     * 成功
     * @param array $respond_data
     * @param string $message
     * @param bool $code
     * @return array
     */
    protected function success($respond_data = [], string $message = 'success', $code = true) : array
    {
        return $this->respond($code, $respond_data, $message);
    }

    /**
     * 失败
     * @param string $message
     * @param array $respond_data
     * @param bool $code
     * @return array
     */
    protected function error(string $message = 'error', $respond_data = [], $code = false) : array
    {
        $this->error = trim($message);
        return $this->respond($code, $respond_data, $message);
    }
}