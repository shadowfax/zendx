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
 * @category   ZendX
 * @package    ZendX_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2012-2013 Juan Pedro Gonzalez Gutierrez
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Application_Resource_ResourceAbstract
 */
require_once 'Zend/Application/Resource/ResourceAbstract.php';

/**
 * Resource for setting SEO options
 *
 * @uses       Zend_Application_Resource_ResourceAbstract
 * @category   ZendX
 * @package    ZendX_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2012-2013 Juan Pedro Gonzalez Gutierrez
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class ZendX_Application_Resource_Seo extends Zend_Application_Resource_ResourceAbstract
{
	const DEFAULT_REGISTRY_KEY = 'ZendX_Seo';
	
	/**
     * @var ZendX_Seo
     */
    protected $_seo;
	
    
    /**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return Zend_Seo
     */
    public function init()
    {
        return $this->getSeo();
    }
	
    /**
     * Retrieve SEO object
     *
     * @return Zend_Seo
     * @throws Zend_Application_Resource_Exception if registry key was used
     *          already but is no instance of Zend_Seo
     */
    public function getSeo()
    {
        if (null === $this->_seo) {
        	$options = $this->getOptions();
        	
        	// Initialize some options
        	// Default domain for start and canonical links.
            if (empty($options['domain'])) {
            	if (isset($_SERVER['SERVER_NAME'])) {
                	$options['domain'] = $_SERVER['SERVER_NAME'];
            	} elseif (isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])) {
            		$options['domain'] = $_SERVER['HTTP_HOST'];
            	}
            }
            
            // Keywords: Comma separated keywords to array.
            if (!empty($options['keywords'])) {
            	if (is_string($options['keywords'])) {
            		$options['keywords'] = explode(',', $options['keywords']);
            	}
            }
            
            // Robots
        	if (empty($options['robots'])) {
        		$options['robots'] = array('index', 'follow');
        	}elseif (is_string($options['robots'])) {
            	$options['robots'] = explode(',', $options['robots']);
            }
            
        	// Register
            $key = (isset($options['registry_key']) && !is_numeric($options['registry_key']))
                     ? $options['registry_key']
                     : self::DEFAULT_REGISTRY_KEY;
            unset($options['registry_key']);

            if(Zend_Registry::isRegistered($key)) {
                $seo = Zend_Registry::get($key);
                if(!$seo instanceof ZendX_Seo) {
                    require_once 'Zend/Application/Resource/Exception.php';
                    throw new Zend_Application_Resource_Exception($key
                                   . ' already registered in registry but is '
                                   . 'no instance of Zend_Seo');
                }

                $this->_seo = $seo;
            } else {
                $this->_seo = new ZendX_Seo($options);
                Zend_Registry::set($key, $this->_seo);
            }
            
            // Initialize the plugin
            $front = Zend_Controller_Front::getInstance();
    		$front->registerPlugin(new ZendX_Seo_Controller_Plugin_Seo());
        }
       
        return $this->_seo;
    }
        
}