<?php
/**
 * Created by PhpStorm.
 * User: LiXiang
 * Date: 2019/4/17
 * Time: 16:01
 */
namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\ApiResponse;
use App\Models\SystemMessage;

class CommonController extends Controller
{
    use ApiResponse;

	/**
	 * 推送系统消息
	 * @param int $uid
	 * @param array $data
	 * @return bool
	 */
	public function sendSocketMessageByUid($uid = 0, $data = [])
	{
		if ($uid <= 0) return false;
		$fd = app('swoole')->wsTable->get('uid:' . $uid);
		if ($fd !== false) {
			app('swoole')->push($fd['value'], json_encode($data));
		}
    }

	/**
	 * 更新消息盒子
	 * @param int $uid
	 * @return bool
	 */
	public function updateMsgBox($uid = 0)
	{
		if ($uid <= 0) return false;
		$fd = app('swoole')->wsTable->get('uid:' . $uid);
		if ($fd !== false) {
			$count = SystemMessage::query()->where('to_user_id', $uid)->where('read', 0)->count();
			$data = [
				'type'  => 'msgBox',
				'count' => $count,
			];
			app('swoole')->push($fd['value'], json_encode($data));
		}
    }

	/**
	 * 强制下线
	 */
	public function forceOffline($uid = 0)
	{
		if ($uid <= 0) return false;
		$fd = app('swoole')->wsTable->get('uid:' . $uid);
		if ($fd !== false) {
			$data = [
				'type'  => 'forceOffline',
			];
			app('swoole')->push($fd['value'], json_encode($data));
		}
    }

	/**
	 * 上传文件
	 * @param string $name
	 * @param string $path
	 * @param int $size
	 * @param array $ext
	 * @return array|string
	 */
	public function uploadOneFile ($name = '', $path = 'files', $size = 5242880, $ext = [])
	{

		$file = request()->file($name);
		if (!$file) {
			return '请选择上传文件';
		}
		if (!$file->isValid()) {
			return '上传失败';
		}
		if ($file->getSize() > $size) {
			return '上传文件大小不能超过5M';
		}
		$extension  = $file->extension();
		if (!empty($ext) && !in_array(strtolower($extension), $ext, true)){
			return '只能上传后缀为' . implode('、', $ext) . '的文件';
		}
		if ($filePath = $file->store($path . '/' . date('Y-m-d'), 'public')) {
			return [
				'path'			=> $filePath,
				'originalName'	=> $file->getClientOriginalName()
			];
		}
    }
}