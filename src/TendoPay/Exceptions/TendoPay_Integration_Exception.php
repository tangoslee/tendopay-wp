<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 04.08.2018
 * Time: 13:25
 */

namespace TendoPay\Exceptions;


if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class TendoPay_Integration_Exception
 * @package TendoPay\Exceptions
 */
class TendoPay_Integration_Exception extends \Exception {
	/**
	 * TendoPay_Integration_Exception constructor.
	 */
	public function __construct( $message, \Throwable $previous = null ) {
    parent::__construct( $message, 0, $previous );

    try {
      $this->report($message, $previous);
    } catch(Exception $e) {}
  }


  private function report($message, $previous = null) {

    if ($previous) {
      ob_start();
      print_r($previous). PHP_EOL;
      $raw = ob_get_contents();
      ob_end_clean();

      $trace = (preg_match('/\[string:Exception:private\] => ([^#]+)/s', $raw, $match))
        ? $match[1] . PHP_EOL . $previous->getTraceAsString()
        : $previous->getTraceAsString();
    }

    $info = [$message];
    if (class_exists('WooCommerce')) {
      global $woocommerce;
      $info[] = 'woocommerce_version:' . $woocommerce->version;
      $info[] = 'active_plugins:';

      $active_plugins = array_reduce(get_option('active_plugins'), 
        function($hash, $item) {
          $hash[md5($item)] = 1;
          return $hash;
        }, []);

      foreach(get_plugins() as $key => $item) {
          if (isset($active_plugins[md5($key)])) {
            $info[] = $item['Name'] . ': v' . $item['Version'];
          }
      }

    } else {
      $info[] = 'woocommerce_version: inactive';
    }

	  $backtrace = array(
			'message' => $info,
			'trace' => explode("\n", $trace),
    );

		$http = new \GuzzleHttp\Client();
		$http->post('https://debug.tendopay.ph/log/tendopay/wp', [
			\GuzzleHttp\RequestOptions::JSON => $backtrace
		]);
  }

}
