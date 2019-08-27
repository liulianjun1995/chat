<?php
/**
 * Created by PhpStorm.
 * User: LiXiang
 * Date: 2019/6/28
 * Time: 14:28
 */

namespace App\Admin\Extensions\Tools;


use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;

class UserGender extends AbstractTool
{

	public function script ()
	{
		$url = \Request::fullUrlWithQuery(['gender' => '_gender_']);

		return <<<EOT

$('input:radio.user-gender').change(function () {
    var url = "$url".replace('_gender_', $(this).val());
    $.pjax({container:'#pjax-container', url: url });
});

EOT;

	}

	/**
	 * {@inheritdoc}
	 */
	public function render ()
	{
		Admin::script($this->script());

		$options = [
			'all'	=> 'All',
			'm'		=> 'Male',
			'f'		=> 'Female'
		];

		return view('admin.tools.gender', compact('options'));
	}
}