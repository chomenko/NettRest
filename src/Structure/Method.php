<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Structure;

use Chomenko\InlineRouting\Route;
use Nette\Utils\Html;

class Method
{

	/**
	 * @var string
	 */
	private $routeName;

	/**
	 * @var string
	 */
	private $methodName;

	/**
	 * @var string
	 */
	private $className;

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
	 * @var Request
	 */
	private $request;

	/**
	 * @var Response
	 */
	private $response;

	/**
	 * Method constructor.
	 * @param string $routeName
	 * @param string $methodName
	 * @param string $className
	 */
	public function __construct(
		string $routeName,
		string $methodName,
		string $className
	) {
		$this->routeName = $routeName;
		$this->methodName = $methodName;
		$this->className = $className;
		$this->request = new Request($this);
		$this->response = new Response($this);
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
	 * @return string
	 */
	public function getRouteName(): string
	{
		return $this->routeName;
	}

	/**
	 * @return string
	 */
	public function getMethodName(): string
	{
		return $this->methodName;
	}

	/**
	 * @return string
	 */
	public function getClassName(): string
	{
		return $this->className;
	}

	/**
	 * @return Request
	 */
	public function getRequest(): Request
	{
		return $this->request;
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
			$section->addHtml(Html::el("p")->setHtml("<b>Body attributes</b>"));
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
		$route = $this->getMethod()->getRouteName();
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
			$section->addHtml(Html::el("p")->setHtml("<b>URL parameters</b>"));
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
		$section->addHtml(Html::el("p")->setHtml("<b>Response example</b>"));
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
				$value = $this->getResponseTree($parameter->getParameters());
			} else {
				$value = $parameter->getExample();
			}
			$parent = $parameter->getParent();

			if ($parent instanceof Parameter && $parent->isCollection()) {
				$data[] = $value;
				continue;
			}
			$data[$parameter->getName()] = $value;
		}
		return $data;
	}

}
