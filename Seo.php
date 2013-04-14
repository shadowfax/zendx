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
 * @package   Zend_Seo
 * @copyright  Copyright (c) 2012-2013 Juan Pedro Gonzalez Gutierrez
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Base class for Search Engine Optimization (SEO)
 *
 * @category  ZendX
 * @package   Zend_Seo
 * @copyright  Copyright (c) 2012-2013 Juan Pedro Gonzalez Gutierrez
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class ZendX_Seo
{
	/**
	 * Prefered domain for the site.
	 * 
	 * @var string
	 */
	protected $_domain;
	
	/**
	 * Wheter to use Dublin Core Meta Data or not.
	 * @var boolean
	 */
	protected $_dublinCore = false;
	
	/**
	 * Common SEO keywords.
	 * 
	 * @var array
	 */
	protected $_keywords;
	
	/**
	 * Robots meta tag values
	 * 
	 * @var array
	 */
	protected $_robots;
	
	/**
     * Generates the standard seo object
     *
     * @param  array|Zend_Config $options Options to use
     * @throws Zend_Seo_Exception
     */
	public function __construct($options = array())
	{
	    if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }
        
        $this->setOptions($options);
	}
	
	/**
     * Set options en masse
     *
     * @param  array|Zend_Config $options
     * @return void
     */
	public function setOptions($options)
	{
		if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (!is_array($options)) {
            require_once 'Zend/Seo/Exception.php';
            throw new Zend_Seo_Exception('setOptions() expects either an array or a Zend_Config object');
        }
        
		foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
	}
	
	public function getDomain()
	{
		return $this->_domain;	
	}
	
	public function getKeywords()
	{
		return $this->_keywords;
	}
	
	public function getRobots()
	{
		return $this->_robots;
	}
	
	public function setDomain($domain)
	{
		$this->_domain = (string)$domain;
		return $this;
	}
	
	public function setKeywords($keywords)
	{
		if (is_array($keywords)) {
			$this->_keywords = $keywords;
		} elseif(is_string($keywords)) {
			$this->_keywords = explode(',', $keywords);
		} else {
			// ToDo: Throw exception
		}
		return $this;
	}
	
	public function setRobots($directives)
	{
		if (is_array($directives)) {
			$this->_robots = $directives;
		} elseif(is_string($directives)) {
			$this->_robots = explode(',', $directives);
		} else {
			// ToDo: Throw exception
		}
		return $this;
	}

}