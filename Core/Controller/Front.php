<?php
final class ENT_Core_Controller_Front {
	private $request;
	private $router;
	private $template;
	private $response;
	private $templateHeader;
	private $renderTemplate = true;
	private $renderView = true;
	private $renderViewLater = false;
	private $redirect = false;
	private $layout;
	private $_layout;
	
	public function __construct() {
		$this->request = new ENT_Core_Request();
		$this->router = new ENT_Core_Router();
		$this->response = new ENT_Core_Response();
	}
	
	public function redirect($path) {
		$this->redirect = true;
		$redirectRequest = new ENT_Core_Request();
		$redirectRequest->reconfig($path);
		$this->dispatch($redirectRequest);
	}
	
	public function getRequest() {
		return $this->request;
	}
	
	public function getResponse() {
		return $this->response;
	}
	
	public function dispatch($_request = false) {
		ENT_Profiler::startStep('root');
			$request = $_request ? $_request : $this->request;
			$request_cache = new ENT_Core_Request_Cache($request);
			$match = $this->router->match($request);
			$header = new ENT_Core_Template_Header();
		
			$this->layout = $match['layout'];
			$this->_layout = $match['layout'];
			$section_name = $match['section'];

			$controller_name = str_replace("_", "/", $match['controller']);
			$action_name = $match['action'];
			$view_name = $match['view'];
			$view_id = $section_name.'/'.$controller_name.'/'.$view_name;
			$mvc_path = $section_name.'/'.$controller_name.'/'.$action_name;			

			#die($mvc_path.":D");
			if ($controller = ENT::getController($section_name.'/'.$controller_name)) {
				$controller->setFrontController($this);
				$controller->setRequest($request);
				$controller->setRequestCache($request_cache);
				$controller->setResponse($this->response);
				$controller->setHeader($header);
				$controller->init();
			
				ENT_Profiler::startStep('beforeAction');
					$controller->_beforeAction();
				ENT_Profiler::stopStep();
				
				if ((!$this->redirect && !$_request) || ($this->redirect && $_request)) {	
					if (!$request_cache->isViable()) {
						$this->template = null;
						ENT_Profiler::startStep('processTemplate');
							$this->_processTemplate();
						ENT_Profiler::stopStep();
					
						$controller->setTemplateObject($this->template);
						$controller->_afterTemplateAction();
				
						$action = $match['action']."Action";
				
						if (preg_match('/^([0-9]+)/ism', $action)) {
							$action = "_".$action;
						}

						ENT_Profiler::startStep($action);
							#ob_start();
							$controller->$action();
							#$controller_action_contents = ob_get_contents();
							#ob_end_clean();
						ENT_Profiler::stopStep();
			
						if ((!$this->redirect && !$_request) || ($this->redirect && $_request)) {	
							$template = ENT::getViewTemplate($view_id);
				
							if ($this->renderView || $this->renderViewLater) {
								if ($view = ENT::getView($view_id, $template, false)) {
									if (is_object($view)) {
										$view->setHeader($header);
									}
								
									if (!$this->renderViewLater && (!$this->template || !$this->renderTemplate)) {
										ENT_Profiler::startStep('renderView');
											$view->render();
											$content = $view->getContents();
										ENT_Profiler::stopStep();
									}
								} else {
									echo "unable to load view: ".$view_id;
								}
							}

							ENT_Profiler::startStep('renderTemplate');
								if ($this->template) {
									$this->template->setContentView($view);
									$this->template->setContent($view);
									$this->template->setHeader($header);
								} else {
									echo "unable to find layout template:".$this->layout;
								}
					
								if ($this->renderTemplate && $this->template) {
									$content = $this->template->render();
								}
							ENT_Profiler::stopStep();
					
							#$content = $controller_action_contents.$content;
					
							if ($content) {
								$this->response->setContent($content);
							}
						if ($request_cache->isEnabled()) {
							$request_cache->save($this->response->getContent());
						}
					} 
				} else {
					ENT_Profiler::startStep("cacheLoad");
						$this->response->setContent($request_cache->getContent());
					ENT_Profiler::stopStep();
				}

				$controller->_afterAction();
					
				// Root stop
				ENT_Profiler::stopStep();
				if (ENT::getEnvironment() && ENT::getEnvironment()->getType() == 'development') {
					$this->response->setContent(str_replace("{{rad-profiler}}", ENT_Profiler::getHtmlContent(), $this->response->getContent()));
				} else {
					$this->response->setContent(str_replace("{{rad-profiler}}", "", $this->response->getContent()));
				}
				
				$this->response->send();
			}
		} else {
			$this->redirect('/');
		}
	}
	
	public function _processTemplate() {
		$this->layout = 'app/design/layout/'.$this->_layout;
		if (file_exists($this->layout.".phtml") && is_file($this->layout.".phtml")) {
			$this->template = ENT_Core_Template_Factory::getTemplate(ENT_Core_Template_Factory::PHP_HTML);
			$this->template->setFile($this->layout.".phtml");
		}
		else if (file_exists($this->layout.".xml") && is_file($this->layout.".xml")) {
			$this->template = ENT_Core_Template_Factory::getTemplate(ENT_Core_Template_Factory::XML);
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
	public function renderTemplate($render) {
		$this->renderTemplate = $render;
	}
	public function getHeader() {
		if (!$this->templateHeader) {
			$this->templateHeader = new ENT_Core_Template_Header();
		}
		
		return $this->templateHeader;
	}
}
?>
