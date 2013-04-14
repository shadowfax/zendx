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
 * Resource for setting multilingual site options
 *
 * @uses       Zend_Application_Resource_ResourceAbstract
 * @category   ZendX
 * @package    ZendX_Application
 * @subpackage Resource
 * @copyright  Copyright (c) 2012-2013 Juan Pedro Gonzalez Gutierrez
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class ZendX_Application_Resource_Multilingual extends Zend_Application_Resource_ResourceAbstract
{
	
	/**
     * @var Zend_Controller_Router_Rewrite
     */
    protected $_router;

	/**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return Zend_Controller_Router_Rewrite
     */
    public function init()
    {
        return $this->getRouter();
    }

	/**
     * Retrieve router object
     *
     * @return Zend_Controller_Router_Rewrite
     */
    public function getRouter()
    {
        if (null === $this->_router) {
            $bootstrap = $this->getBootstrap();
            $bootstrap->bootstrap('FrontController');
            try {
            	$bootstrap->bootstrap('router');
            } catch (Exception $e) {
            }
            
        	try {
            	$bootstrap->bootstrap('translate');
            } catch (Exception $e) {
            }
            
            $this->_router = $bootstrap->getContainer()->frontcontroller->getRouter();
            
            // Get all routes before adding my route
            $routes = $this->_router->getRoutes();

            $options = $this->getOptions();
            
            if (empty($options['type'])) {
            	$options['type'] = "path";
            }
            $options['type'] = strtolower($options['type']);
            
            // Create the multilingual route
            $requirements = array(
            	':language'	=> '[a-zA-Z]{2}'
            );
            
            $defaults = array('language');
            
            switch ($options['type']) {
            	case 'subdomain':
            		{
            			$domain = explode(".", $options['domain']);
            			if (strcasecmp($domain[0], 'www') === 0) {
            				$domain[0] = ":language";
            				$domain = implode('.', $domain);
            			} else {
            				$domain = ':language.' . implode('.', $domain);
            			}
            			
            			
            			$multilingualRoute = new Zend_Controller_Router_Route_Hostname(
            				$domain,
            				array(  
					            'language' => 'www'  
					        ),
					        array(
					        	'language' => '(www|[a-zA-Z]{2})'
					        )
            			);
            			
            			break;
            		}
            	default:
            		{
            			$multilingualRoute = new Zend_Controller_Router_Route(  
					        ':language/',  
					        array(  
					            'language' => 'en'  
					        ),
					        array(
					        	'language' => '[a-zA-Z]{2}'
					        )  
					    );		
            			break;
            		}
            }
              
            
            $this->_router->addRoute('multilingual', $multilingualRoute);
        
            $defaultRoute = null;
        	// Chain all other routes    
            foreach ($routes as $routeName => $route) {
            	$route = $this->_router->getRoute($routeName);
            	if (!$route instanceof Zend_Controller_Router_Route_Hostname) {
	            	$this->_router->addRoute('multilingual/' . $routeName, $multilingualRoute->chain($route));
	            	
	            	if (strcasecmp($routeName, 'default') === 0) {
	            		$defaultRoute = $route;
	            	}
            	}
            }
            
            // Must create a default route if none exists
            if (null === $defaultRoute) {
            	$defaultRoute = new Zend_Controller_Router_Route_Module();
            	$this->_router->addRoute('default', $multilingualRoute->chain($defaultRoute));
            	$this->_router->addRoute('multilingual/default', $multilingualRoute->chain($defaultRoute));
            }
            
            // Initialize the plugin
            $front = Zend_Controller_Front::getInstance();
    		$front->registerPlugin(new ZendX_Multilingual_Controller_Plugin_Multilingual());
        }

        return $this->_router;
    }
}