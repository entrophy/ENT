<?php
final class ENT_Controller_Front {
	private $request;
	private $router;
	private $template;
	private $response;
	private $templateHeader;
	private $renderLayout = true;
	private $renderView = true;
	private $redirect = false;
	private $layout;
	private $layout_file = null;
	private $view;	
	private $header;
	private $REST_to_CRUD = array(
		'GET' => 'read',
		'PUT' => 'update',
		'POST' => 'create',
		'DELETE' => 'delete'
	);
	
	public function __construct() {
		$this->request = new ENT_Request();
		$this->router = ENT::app()->getRouter();
		$this->response = new ENT_Response();
	}
	
	public function redirect($path) {
		$this->redirect = true;
		$this->layout_file = null;

		$request = $this->request;
		$request->init($path);
		$this->dispatch($request);
	}
	
	public function getRequest() {
		return $this->request;
	}
	
	public function getResponse() {
		return $this->response;
	}
	
	public function dispatch($_request = false) {
		Entrophy_Profiler::startStep('root');
		$request = $_request ? : $this->request;

		$request_cache = new ENT_Request_Cache($request);
		
		$match = $this->router->match($request);
		$this->header = $header = new ENT_Template_Header();
		
		$section_name = $match->section;

		$controller_name = str_replace("_", "/", $match->controller);
		$action_name = $match->action;
		$view_id = $match->view;
		$view_template = $match->template;
		
		$mvc_path = $match->path;	
		
		if ($controller = ENT::getController($match->section.'/'.$match->controller)) {
			$controller->setFrontController($this)
						  ->setRequest($request)
						  ->setResponse($this->response)
						  ->init();
						  
			if ($controller->getType() == 'MVC') {
				$controller->setHeader($header);
			} else {
				#echo $request->getMethod();
			}
		
			Entrophy_Profiler::startStep('beforeAction');
				$controller->_beforeAction();
			Entrophy_Profiler::stopStep();
							
			if ((!$this->redirect && !$_request) || ($this->redirect && $_request)) {			
				if ($controller->getType() == 'REST') {
					$action = $this->REST_to_CRUD[$request->getMethod()]."Action";
				} else {
					$action = $match->action."Action";
				}
		
				if (preg_match('/^([0-9]+)/ism', $action)) {
					$action = "_".$action;
				}

				Entrophy_Profiler::startStep($action);
					$controller->$action();
				Entrophy_Profiler::stopStep();
				
				if ((!$this->redirect && !$_request) || ($this->redirect && $_request)) {	
					Entrophy_Profiler::startStep('processLayout');
						$this->processLayout($match);
					Entrophy_Profiler::stopStep();
			
					if ($this->layout) {
						$controller->setLayoutObject($this->layout);
						$controller->_afterTemplateAction();
					}
				
					Entrophy_Profiler::startStep('renderView');
						$content = $this->processView($match);
					Entrophy_Profiler::stopStep();

					Entrophy_Profiler::startStep('renderLayout');
						if ($this->renderLayout && $this->layout) {
							#$this->layout->setContentView($this->view);
							$this->layout->setContent($this->view);
							$this->layout->setHeader($header);

							$content = $this->layout->render();
						}
					Entrophy_Profiler::stopStep();

					if ($content) {
						$this->response->setContent($content);
					}
					
					$controller->_afterAction();
				
					// Root stop
					Entrophy_Profiler::stopStep();
					if (ENT::getEnvironment() && ENT::getEnvironment()->getType() == 'development') {
						$this->response->setContent(str_replace("{{rad-profiler}}", Entrophy_Profiler::getHtmlContent(), $this->response->getContent()));
					} else {
						$this->response->setContent(str_replace("{{rad-profiler}}", "", $this->response->getContent()));
					}
			
					$this->response->send();
				}
			}
		} else {
			if ($this->router->getDefault() && $this->router->getDefault() != $request->getPath()) {
				$this->redirect($this->router->getDefault());
			} else {		
				throw new ENT_Exception("Unable to load controller: ".$section_name.'/'.$controller_name);
			}
		}
	}
	
	private function processView($match) {
		$this->view = null;
						
		if ($this->renderView) {
			$template = ENT::getViewTemplate($match->template);
			
			$tried = array();		
			$tries = array_filter(array($match->view, $match->found->view, ENT::getConfig()->getValue('default/view')));
			
			foreach ($tries as $try) {
				if ($view = ENT::getView($try, $template, false)) {
					break;
				} else {
					$tried[] = $try;
				}
			}
			unset($tries);

			if ($view) {
				$this->view = $view;
				$view->setHeader($this->header);
			
				if (!$this->layout || !$this->renderLayout) {
					$view->render();
					return $view->getContents();
				}
			} else {
				throw new ENT_Exception("Unable to load view, tried: ".implode(", ", $tried));
			}
		}
	}
	
	private function processLayout($match) {
		$this->layout = null;
		
		if ($this->renderLayout) {
			$tried = array();	
			$tries = array_filter(array($this->layout_file, $match->layout, $match->found->layout, ENT::getConfig()->getValue('default/layout')));
			
			foreach ($tries as $try) {
				if ($try) {
					$layout_file = 'app/design/layout/'.$try;
					
					if (file_exists($layout_file.".phtml")) {
						$layout = ENT_Layout_Factory::getTemplate(ENT_Layout_Factory::PHP_HTML);
						$layout->setFile($layout_file.".phtml");
					} else if (file_exists($layout_file.".xml")) {
						$layout = ENT_Layout_Factory::getTemplate(ENT_Layout_Factory::XML);
						$layout->setFile($layout_file.".xml");
					} else {
						$tried[] = $try;
					}
				}
			}

			if ($layout) {
				$this->layout = $layout;
				$this->layout->process();
			} else {
				if (count($tried)) {
					throw new ENT_Exception("Unable to load layout, tried: ".implode(", ", $tried));
				} else {
					throw new ENT_Exception("Unable to load layout, no layout file provided");
				}
			}
		}
	}
	
	public function setLayout($layout) {
		$this->layout_file = $layout;
	}
	public function getLayout() {
		return $this->layout_file;
	}
	
	public function renderView($render) {
		$this->renderView = $render;
	}
	public function renderViewLater($render) {
		$this->renderViewLater = $render;
	}
	public function renderLayout($render) {
		$this->renderLayout = $render;
	}
	public function getHeader() {
		if (!$this->templateHeader) {
			$this->templateHeader = new ENT_Template_Header();
		}
		
		return $this->templateHeader;
	}
}
?>
