<?php
namespace restlt\utils\output;


use restlt\utils\output\TypeConversionStrategyInterface;


class HtmlOutputStrategy implements TypeConversionStrategyInterface{
	
	/**
	 * 
	 * @var \restlt\utils\output\template\TemplateEngineInterface
	 */
	protected $templateEngine = null;
	
	public function execute(\restlt\Result $data) {
		$html = $data->getData();
		return $html;
	}
	/**
	 * @return \restlt\utils\output\template\TemplateEngineInterface $templateEngine
	 */
	public function getTemplateEngine() {
		return $this->templateEngine;
	}

	/**
	 * @param TemplateEngineInterface $templateEngine
	 */
	public function setTemplateEngine(\restlt\utils\output\template\TemplateEngineInterface $templateEngine) {
		$this->templateEngine = $templateEngine;
	}

	
}