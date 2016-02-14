<?php
/**
 * PPConfigManager loads the SDK configuration file and
 * hands out appropriate config params to other classes
 *
 * MTS Simple Booking シンプル予約システム用環境設定組込
 * @Author	S.Hayashi
 * @Date	2012-12-31
 *
 * Updated  2014-07-14
 * Updated  2013-11-18
 */
require_once 'exceptions/PPConfigurationException.php';

class PPConfigManager {

	private $config;
	/**
	 * @var PPConfigManager
	 */
	private static $instance;

	private function __construct(){

		$this->config = $this->getConfig();
	}

	// create singleton object for PPConfigManager
	public static function getInstance()
	{
		if ( !isset(self::$instance) ) {
			self::$instance = new PPConfigManager();
		}
		return self::$instance;
	}

	/**
	 * simple getter for configuration params
	 * If an exact match for key is not found,
	 * does a "contains" search on the key
	 */
	public function get($searchKey){

		if(array_key_exists($searchKey, $this->config))
		{
			return $this->config[$searchKey];
		}
		else {
			$arr = array();
			foreach ($this->config as $k => $v){
				if(strstr($k, $searchKey)){
					$arr[$k] = $v;
				}
			}
			
			return $arr;
		}

	}

	/**
	 * Utility method for handling account configuration
	 * return config key corresponding to the API userId passed in
	 *
	 * If $userId is null, returns config keys corresponding to
	 * all configured accounts
	 */
	public function getIniPrefix($userId = null) {

		if($userId == null) {
			$arr = array();
			foreach ($this->config as $key => $value) {
				$pos = strpos($key, '.');
				if(strstr($key, "acct")){
					$arr[] = substr($key, 0, $pos);
				}
			}
			return array_unique($arr);
		} else {
			$iniPrefix = array_search($userId, $this->config);
			$pos = strpos($iniPrefix, '.');
			$acct = substr($iniPrefix, 0, $pos);
			
			return $acct;
		}
	}

	/**
	 * PayPal SDK config.ini
	 *
	 */
	public function getConfig() {
		global $mts_simple_booking;

		// 保存認証データを取得する
		$credentials = $mts_simple_booking->oPPManager->getCredentials();

        $config = array (
            'acct1.UserName' => $credentials['pp_username'],
            'acct1.Password' => $credentials['pp_password'],
            'acct1.Signature' => $credentials['pp_signature'],
            //'acct1.UserName' => 'jb-us-seller_api1.paypal.com',
            //'acct1.Password' => 'WX4WTU3S8MY44S7F',
            //'acct1.Signature' => 'AFcWxV21C7fd0v3bYYYRCpSSRl31A7yDhhsPUU2XhtMoZXsWHFxu-RWy',
            'acct1.AppId' => 'APP-80W284485P519543T',
            // ;Subject is optional and is required only in case of third party authorization
            // acct1.Subject =

            // ;Certificate Credentials Test Account
            // acct2.UserName = platfo_1255170694_biz_api1.gmail.com
            // acct2.Password = 2DPPKUPKB7DQLXNR
            // ;Certificate path relative to config folder or absolute path in file system
            // acct2.CertPath = cert_key.pem

            // ;Connection Information
            'http.ConnectionTimeOut' => 30,
            'http.Retry' => 5,
            // ;http.Proxy

            // ;Logging Information
            //'log.FileName' => '../PayPal.log',
            'log.FileName' => 'wp-content/plugins/mts-simple-booking/PayPal.log',
            'log.LogLevel' => 'INFO',
            'log.LogEnabled' => false,
        );

        $sandbox = array (
            // ;Service Configuration
            // ; ------------------------------SANDBOX------------------------------ #
            // ; NOTE: both the URLs below are required (PayPalAPI, PayPalAPIAA)
            'service.EndPoint.PayPalAPI' => "https://api-3t.sandbox.paypal.com/2.0", // ; Endpoint for 3-token credentials
            'service.EndPoint.PayPalAPIAA' => "https://api-3t.sandbox.paypal.com/2.0", // ; Endpoint for 3-token credentials
            // ; Uncomment line below if you are using certificate credentials
            // ; service.EndPoint.PayPalAPI   = "https://api.sandbox.paypal.com/2.0"
            // ; service.EndPoint.PayPalAPIAA = "https://api.sandbox.paypal.com/2.0"

            'service.EndPoint.IPN' => "https://ipnpb.sandbox.paypal.com/cgi-bin/webscr",
            'service.RedirectURL' => "https://www.sandbox.paypal.com/webscr&cmd=",

            // ; Multiple end-points configuration - while using multiple SDKs in combination, like merchant APIs(expresscheckout etc) and Permissions etc, uncomment the respective endpoint. refer README for more information
            // ; Permissions Platform Service
            'service.EndPoint.Permissions' => "https://svcs.sandbox.paypal.com/",
        );

        $production = array(
            // ;Service Configuration
            // ; ------------------------------PRODUCTION------------------------------ #
            'service.EndPoint.PayPalAPI'   => "https://api-3t.paypal.com/2.0", // ; Endpoint for 3-token credentials
            'service.EndPoint.PayPalAPIAA' => "https://api-3t.paypal.com/2.0", // ; Endpoint for 3-token credentials
            // ;service.EndPoint.PayPalAPI   = "https://api.paypal.com/2.0"  ; Certificate credential
            // ;service.EndPoint.PayPalAPIAA = "https://api.paypal.com/2.0"  ; Certificate credential
            'service.EndPoint.Permissions' => "https://svcs.paypal.com/",
            'service.EndPoint.IPN'         => "https://ipnpb.paypal.com/cgi-bin/webscr",
            'service.RedirectURL' => "https://www.paypal.com/webscr&cmd=",
        );

        return array_merge($config, ($mts_simple_booking->oPPManager->getUseSandbox() ? $sandbox : $production));
	}
}