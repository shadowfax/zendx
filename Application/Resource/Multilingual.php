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
	const DEFAULT_REGISTRY_KEY = 'ZendX_Multilingual';
	
	/**
	 * @var ZendX_Multilingual
	 */
	protected $_multilingual;
	

	/**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return Zend_Controller_Router_Rewrite
     */
    public function init()
    {
        return $this->getMultilingual();
    }

    public function getMultilingual()
    {
    	if (null === $this->_multilingual) {
    		$bootstrap = $this->getBootstrap();
    		
    		// Bootstrap the router
	    	try {
	            $bootstrap->bootstrap('router');
	        } catch (Exception $e) {}
	        
	        // Bootstrap the translator
    	    try {
	            $bootstrap->bootstrap('translate');
	        } catch (Exception $e) {}
	        
	        // Initialize the router and get all routes before 
	        // appending the localized routes
	        $router = $bootstrap->getContainer()->frontcontroller->getRouter();
	        $routes = $router->getRoutes();
	        
	        // Load options
	        $options = $this->getOptions();
	        
	        // Route option defaults to "path"
    		if (empty($options['route'])) {
            	$options['route'] = "path";
            }
            
            // Translator options
            if(isset($options['translate'])) {
            	if (!isset($options['translate']['content']) && !isset($options['translate']['data'])) {
	                require_once 'Zend/Application/Resource/Exception.php';
	                throw new Zend_Application_Resource_Exception('No translation source data provided for routing.');
	            } else if (array_key_exists('content', $options['translate']) && array_key_exists('data', $options['translate'])) {
	                require_once 'Zend/Application/Resource/Exception.php';
	                throw new Zend_Application_Resource_Exception(
	                    'Conflict on translation source data for routing: choose only one key between content and data.'
	                );
	            }
	            
	    		if (empty($options['translate']['adapter'])) {
	                $options['translate']['adapter'] = Zend_Translate::AN_ARRAY;
	            }
	
	            if (!empty($options['translate']['data'])) {
	                $options['translate']['content'] = $options['translate']['data'];
	                unset($options['translate']['data']);
	            }
	
	            if (isset($options['translate']['options'])) {
	                foreach($options['translate']['options'] as $key => $value) {
	                    $options['translate'][$key] = $value;
	                }
	            }
	
	            if (!empty($options['translate']['cache']) && is_string($options['translate']['cache'])) {
	                $bootstrap = $this->getBootstrap();
	                if ($bootstrap instanceof Zend_Application_Bootstrap_ResourceBootstrapper &&
	                    $bootstrap->hasPluginResource('CacheManager')
	                ) {
	                    $cacheManager = $bootstrap->bootstrap('CacheManager')
	                        ->getResource('CacheManager');
	                    if (null !== $cacheManager &&
	                        $cacheManager->hasCache($options['translate']['cache'])
	                    ) {
	                        $options['translate']['cache'] = $cacheManager->getCache($options['translate']['cache']);
	                    }
	                }
	            }
	            
            	if (!isset($options['translate']['locale'])) {
	                if (Zend_Registry::isRegistered('Zend_Translate')) {
	                	$zend_translate = Zend_Registry::get('Zend_Translate');
	                	$options['translate']['locale'] = $zend_translate->getLocale();
	                }
	            }
            }
	        
            // --- Routes ---
            // Build the main multilingual route
            switch (strtolower(trim($options['route']))) {
            	case 'host':
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
					        	'language' => '^(www|([a-zA-Z]{2}(\-[a-zA-Z]{2}){0,1}))$'
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
					        	'language' => '^[a-zA-Z]{2}(\-[a-zA-Z]{2}){0,1}$'
					        )  
					    );		
            			
            			break;
            		}
            }
            
            // Add the route to the router
            $router->addRoute('multilingual', $multilingualRoute);
            
    	    // Chain all other routes    
            foreach ($routes as $routeName => $route) {
            	$route = $router->getRoute($routeName);
            	if (!$route instanceof Zend_Controller_Router_Route_Hostname) {
	            	$router->addRoute('multilingual_' . $routeName, $multilingualRoute->chain($route));
	            	
	            	if (strcasecmp($routeName, 'default') === 0) {
	            		$defaultRoute = $route;
	            	}
            	}
            }
            
            // Create a default route if it didn't exist
            if (!isset($defaultRoute)) {
            	$defaultRoute = new Zend_Controller_Router_Route_Module();
            	$router->addRoute('default', $multilingualRoute->chain($defaultRoute));
            	$router->addRoute('multilingual_default', $multilingualRoute->chain($defaultRoute));
            }
	        
            // --- Plugin ---
            // Initialize the multilingual plugin
            $bootstrap->getContainer()->frontcontroller->registerPlugin(new ZendX_Multilingual_Controller_Plugin_Multilingual());
            
            // --- Finally ---
            // Create the multilingual object
            $this->_multilingual = new ZendX_Multilingual($options);
    	}
    	
    	return $this->_multilingual;
    }
    
}