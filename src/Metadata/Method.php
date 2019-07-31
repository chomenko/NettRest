<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Metadata;

use Chomenko\InlineRouting\Route;
use Chomenko\NettRest\Config;
use Nette\Utils\Html;

class Method extends MetaHierarchy
{

	/**
	 * @var Route
	 */
	private $route;

	/**
	 * @var string|null
	 */
	private $description;

	/**
	 * @var string|null
	 */
	private $section;

	/**
	 * @var string|null
	 */
	private $label;

	/**
	 * @var Response
	 */
	private $response;

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * Method constructor.
	 * @param Route $route
	 * @param Config $config
	 */
	public function __construct(Route $route, Config $config)
	{
		$this->route = $route;
		$this->response = new Response($route->getHash() . ".response");
		$this->response->setParent($this);
		parent::__construct($route->getHash());
		$this->config = $config;
	}

	/**
	 * @return string|null
	 */
	public function getDescription(): ?string
	{
		return $this->description;
	}

	/**
	 * @param string|null $description
	 */
	public function setDescription(?string $description): void
	{
		$this->description = $description;
	}

	/**
	 * @return string|null
	 */
	public function getSection(): ?string
	{
		return $this->section;
	}

	/**
	 * @param string|null $section
	 */
	public function setSection(?string $section): void
	{
		$this->section = $section;
	}

	/**
	 * @return string|null
	 */
	public function getLabel(): ?string
	{
		return $this->label;
	}

	/**
	 * @param string|null $label
	 */
	public function setLabel(?string $label): void
	{
		$this->label = $label;
	}

	/**
	 * @return Route
	 */
	public function getRoute(): Route
	{
		return $this->route;
	}

	/**
	 * @return string
	 */
	public function getMethodName(): string
	{
		return $this->route->getMethod();
	}

	/**
	 * @return Response
	 */
	public function getResponse(): Response
	{
		return $this->response;
	}

	/**
	 * @return Html
	 */
	public function renderRequest(): Html
	{
		$label = $this->getLabel();
		if (!$label) {
			$this->getMethodName();
		}

		$wrapped = Html::el("div", ["class" => "col-lg-6 left no-padding"]);
		$wrapped->addHtml(Html::el("h1", [
			"class" => "section-title border-bottom",
		])->setText($label));

		$section = Html::el("div", ["class" => "section-padding"]);
		$section->addHtml(Html::el("p", ["class" => "section-description"])->setHtml($this->getDescription()));

		$parameters = $this->getParamsTypeAttributes();

		if (count($parameters) > 0) {
			$section->addHtml(Html::el("p")->setHtml("<b>Attributes</b>"));
			$ul = Html::el("ul", ["class" => "attributes"]);
			foreach ($parameters as $parameter) {
				$paramHtml = $parameter->render();
				if ($paramHtml) {
					$ul->addHtml($paramHtml);
				}
			}
			$section->addHtml($ul);
		}

		$wrapped->addHtml($section);
		return $wrapped;
	}

	/**
	 * @return Html
	 */
	public function renderResponse(): Html
	{
		$label = $this->getLabel();
		if (!$label) {
			$this->getMethodName();
		}

		$wrapped = Html::el("div", ["class" => "col-lg-6 right no-padding"]);
		$apiUrl = Html::el("div", ["class" => "api-url section-padding border-bottom border-top"]);
		$route = $this->getMethod()->getRoute();
		$methods = $route->getMethods();
		foreach ($methods as $method) {
			$apiUrl->addHtml(Html::el("span", ["class" => "badge badge-success"])->setText($method));
		}
		if (!$methods) {
			$apiUrl->addHtml(Html::el("span", ["class" => "badge badge-success"])->setText("GET"));
		}

		$apiUrl->addHtml($this->createApiHtmlUrl($route));
		$wrapped->addHtml($apiUrl);

		$section = Html::el("div", ["class" => "section-padding border-bottom"]);
		$parameters = $this->getParamsTypeParameters();

		if (count($parameters) > 0) {
			$section->addHtml(Html::el("p")->setHtml("<b>Parameters</b>"));
			$ul = Html::el("ul", ["class" => "attributes"]);
			foreach ($parameters as $parameter) {
				$paramHtml = $parameter->render();
				if ($paramHtml) {
					$ul->addHtml($paramHtml);
				}
			}
			$section->addHtml($ul);
		}
		$wrapped->addHtml($section);

		$response = $this->getResponseTree($this->response->getParameters());
		if ($this->response->isCollection()) {
			$response = [$response];
		}

		$response = [
			"code" => 200,
			"data" => $response,
			"errors" => [],
			"messages" => [],
		];

		$json = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

		$section = Html::el("div", ["class" => "section-padding border-bottom"]);
		$section->addHtml(Html::el("p")->setHtml("<b>Response</b>"));
		if ($this->response->getDescription()) {
			$section->addHtml(Html::el("p", ["class" => "section-description"])->setHtml($this->response->getDescription()));
		}
		$pre = Html::el("pre", ["class" => "raw-section"]);
		$pre->addText($json);
		$section->addHtml($pre);

		$wrapped->addHtml($section);


		return $wrapped;
	}

	/**
	 * @param Route $route
	 * @return Html
	 */
	protected function createApiHtmlUrl(Route $route): Html
	{
		$path = $route->getPath();
		foreach ($route->compile()->getPathVariables() as $name) {
			$path = str_replace("{" . $name . "}", "<span class='parameter'>{$name}</span>", $path);
		}
		return Html::el("span")->setHtml($this->config->getHost() . $path);
	}

	/**
	 * @param Parameter[] $parameters
	 * @return array
	 */
	protected function getResponseTree(array $parameters): array
	{
		$data = [];
		foreach ($parameters as $parameter) {
			if ($parameter->isMultiple()) {
				$data[$parameter->getName()] = $this->getParameters($parameter->getParameters());
			} else {
				$data[$parameter->getName()] = $parameter->getExample();
			}
		}
		return $data;
	}

}
