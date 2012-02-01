<?php
final class ENT_Controller_Front {
	private $request;
	private $router;
	private $template;
	private $response;
	private $templateHeader;
	private $renderLayout = true;
	private $renderView = true;
	private $renderViewLater = false;
	private $redirect = false;
	private $layout;
	private $_layout;
	
	public function __construct() {
		$this->request = new ENT_Request();
		$this->router = ENT::app()->getRouter();
		$this->response = new ENT_Response();
	}
	
	public function redirect($path) {
		$this->redirect = true;

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
			$header = new ENT_Template_Header();
			
			$this->layout = $this->_layout = $match->layout;
			$section_name = $match->section;

			$controller_name = str_replace("_", "/", $match->controller);
			$action_name = $match->action;
			$view_name = $match->view;
			$view_id = $section_name.'/'.$controller_name.'/'.$view_name;
			
			$mvc_path = $section_name.'/'.$controller_name.'/'.$action_name;			

			if ($controller = ENT::getController($match->section.'/'.$match->controller)) {
				$controller->setFrontController($this)
							  ->setRequest($request)
							  ->setRequestCache($request_cache)
							  ->setResponse($this->response)
							  ->setHeader($header)
							  ->init();
			
				Entrophy_Profiler::startStep('beforeAction');
					$controller->_beforeAction();
				Entrophy_Profiler::stopStep();
				
				if ((!$this->redirect && !$_request) || ($this->redirect && $_request)) {	
					if (!$request_cache->isViable()) {
						$this->template = null;
						Entrophy_Profiler::startStep('processTemplate');
							$this->_processTemplate();
						Entrophy_Profiler::stopStep();
					
						$controller->setLayoutObject($this->template);
						$controller->_afterTemplateAction();
				
						$action = $match->action."Action";
				
						if (preg_match('/^([0-9]+)/ism', $action)) {
							$action = "_".$action;
						}

						Entrophy_Profiler::startStep($action);
							$controller->$action();
						Entrophy_Profiler::stopStep();
			
						if ((!$this->redirect && !$_request) || ($this->redirect && $_request)) {	
							$template = ENT::getViewTemplate($view_id);
				
							if ($this->renderView || $this->renderViewLater) {
								if ($view = ENT::getView($view_id, $template, false)) {
									if (is_object($view)) {
										$view->setHeader($header);
									}
								
									if (!$this->renderViewLater && (!$this->template || !$this->renderLayout)) {
										Entrophy_Profiler::startStep('renderView');
											$view->render();
											$content = $view->getContents();
										Entrophy_Profiler::stopStep();
									}
								} else {
									#echo "unable to load view: ".$view_id."<br />\n";
								}
							}

							Entrophy_Profiler::startStep('renderTemplate');
								if ($this->template) {
									$this->template->setContentView($view);
									$this->template->setContent($view);
									$this->template->setHeader($header);
								} elseif ($this->renderLayout) {
									#echo "unable to find layout template:".$this->layout."<br />\n";
								}
					
								if ($this->renderLayout && $this->template) {
									$content = $this->template->render();
								}
							Entrophy_Profiler::stopStep();

							if ($content) {
								$this->response->setContent($content);
							}
						if ($request_cache->isEnabled()) {
							$request_cache->save($this->response->getContent());
						}
					} 
				} else {
					Entrophy_Profiler::startStep("cacheLoad");
						$this->response->setContent($request_cache->getContent());
					Entrophy_Profiler::stopStep();
				}

				$controller->_afterAction();
					
				// Root stop
				Entrophy_Profiler::stopStep();
				if (ENT::getEnvironment() && ENT::getEnvironment()->getType() == 'development') {
					$this->response->setContent(str_replace("{{rad-profiler}}", Entrophy_Profiler::getHtmlContent(), $this->response->getContent()));
				} else {
					$this->response->setContent(str_replace("{{rad-profiler}}", "", $this->response->getContent()));
				}
				
				#die(":D");
				$this->response->send();
			}
		} else {
			if ($this->router->getDefault() && $this->router->getDefault() != $request->getPath()) {
				$this->redirect($this->router->getDefault());
			} else {		
				#echo "unable to load controller: ".$section_name.'/'.$controller_name."<br />\n";
			}
		}
	}
	
	public function _processTemplate() {
		$this->layout = 'app/design/layout/'.$this->_layout;
		if (file_exists($this->layout.".phtml") && is_file($this->layout.".phtml")) {
			$this->template = ENT_Template_Factory::getTemplate(ENT_Template_Factory::PHP_HTML);
			$this->template->setFile($this->layout.".phtml");
		}
		else if (file_exists($this->layout.".xml") && is_file($this->layout.".xml")) {
			$this->template = ENT_Template_Factory::getTemplate(ENT_Template_Factory::XML);
			$this->template->setFile($this->layout.".xml");
		}

		if ($this->template) {
			$this->template->process();
		}
	}
	
	public function setTemplate($template) {
		$this->_layout = $template;
	}
	public function getTemplate() {
		return $this->_layout;
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
