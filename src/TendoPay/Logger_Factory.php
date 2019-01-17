<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 17.01.2019
 * Time: 08:50
 */

namespace TendoPay;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Logger_Factory {
	public static function create_logger( $channel_name ) {
		$log = new Logger( $channel_name );
		try {
			$upload_dir = wp_upload_dir();
			$log_dir    = $upload_dir["basedir"] . Constants::WP_UPLOAD_LOGGER_PATH . "tendopay.log";
			$log->pushHandler( new StreamHandler( $log_dir ) );
		} catch ( \Exception $e ) {
			// silently fail
		}

		return $log;
	}
}