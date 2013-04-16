<?php
/**
 * Zend Framework Extensions
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 *
 * @category  ZendX
 * @package   ZendX_IPAddress
 * @copyright  Copyright (c) 2012-2013 Juan Pedro Gonzalez Gutierrez
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Base class for multilingual sites
 *
 * @category  ZendX
 * @package   ZendX_IPAddress
 * @copyright  Copyright (c) 2012-2013 Juan Pedro Gonzalez Gutierrez
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */

class ZendX_IPAddress
{

	public static function isValid($address) 
	{
		if (function_exists('filter_var')) {
			if ((filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) || (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false)) return true;
			return false;
		}
		
		if ((self::isIPv4($address)) || (self::isIPv6($address))) return true;
		return false;
	}
	
	public static function isIPv4($address)
	{
		if (function_exists('filter_var')) {
			if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) return true;
			return false;
		}
		
		if (preg_match('/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/', $address)) return true;
		return false;
	}
	
	public static function isIPv6($address)
	{
		//if (function_exists('filter_var')) {
		//	if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) return true;
		//}
		 
		if (preg_match('/^(((?=.*(::))(?!.*\3.+\3))\3?|([\dA-F]{1,4}(\3|:\b|$)|\2))(?4){5}((?4){2}|(((2[0-4]|1\d|[1-9])?\d|25[0-5])\.?\b){4})\z/i', $address)) return true;
		return false;
	}

}