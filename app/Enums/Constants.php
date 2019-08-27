<?php
/**
 * Created by PhpStorm.
 * User: LiXiang
 * Date: 2019/5/17
 * Time: 11:49
 */
namespace App\Enums;

class Constants
{
    /**
     * 请求添加用户
     */
    const CONSTANT_MESSAGE_TYPE_01 = 1;

    /**
     * 系统消息（添加好友）
     */
    const CONSTANT_MESSAGE_TYPE_02 = 2;

    /**
     * 请求加群
     */
    const CONSTANT_MESSAGE_TYPE_03 = 3;

    /**
     * 系统消息（添加群）
     */
    const CONSTANT_MESSAGE_TYPE_04 = 4;

    /**
     * 全体系统消息
     */
    const CONSTANT_MESSAGE_TYPE_05 = 5;

    /**
     * 邀请加入群
     */
    const CONSTANT_MESSAGE_TYPE_06 = 6;

    /**
     * 系统消息（邀请群）
     */
    const CONSTANT_MESSAGE_TYPE_07 = 7;

	/**
	 * 系统消息（退出群）
	 */
	const CONSTANT_MESSAGE_TYPE_08 = 8;

    /**
     * 待处理
     */
    const CONSTANT_MESSAGE_STATUS_01 = 1;

    /**
     * 已同意
     */
    const CONSTANT_MESSAGE_STATUS_02 = 2;

    /**
     * 已拒绝
     */
    const CONSTANT_MESSAGE_STATUS_03 = 3;

    /**
     * 用户性别：男
     */
    const CONSTANT_USER_SEX_01  = 1;

    /**
     * 用户性别：女
     */
    const CONSTANT_USER_SEX_02  = 2;

    /**
     * 加群验证：允许任何人
     */
    const CONSTANT_GROUP_VERIFY_01  = 1;

    /**
     * 加群验证：需身份验证
     */
    const CONSTANT_GROUP_VERIFY_02  = 2;

    /**
     * 加群验证：不允许任何人
     */
    const CONSTANT_GROUP_VERIFY_03  = 3;
}