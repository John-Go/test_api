<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * AWS
 *
 * @package		YanoljaTravel AWS
 * @author		YapenLab Dev Team
 * @copyright	Copyright (c) 2016
 * @since		Version 1.0
 */

class Aws_conf {

	/**
	 * AWS-SDK Auth key.
	 */
	const _AWS_KEY = 'AKIAJ5SG4BW4P5PUYCIA';
	const _AWS_SECRET = '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';

	/**
	 * AWS Region
	 */
	static $AWS_REGION_TOKYO = 'ap-northeast-1';
	static $AWS_REGION_SEOUL = 'ap-northeast-2';

	public function aws_init() {
		require_once 'aws-sdk/aws-autoloader.php';
     	$credentials = new Aws\Credentials\Credentials(self::_AWS_KEY, self::_AWS_SECRET);
        return $credentials;
	}
}