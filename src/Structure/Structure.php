<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Structure;

use Chomenko\InlineRouting\Route;
use Chomenko\NettRest\View\Column;
use Chomenko\NettRest\View\Layout;
use Chomenko\NettRest\View\Section;
use Doctrine\Common\Annotations\Reader;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Utils\Strings;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Routing\RouteCollection;
use Chomenko\NettRest\API;

class Structure
{

	const CACHE_NAME = "NettRest.cache";

	/**
	 * @var Method[]
	 */
	private $methods = [];

	/**
	 * @var Parameter[]
	 */
	private $parameters = [];

	/**
	 * @var Layout
	 */
	private $layout;

	/**
	 * @var Reader
	 */
	private $reader;

	/**
	 * @var array
	 */
	private $routes = [];

	/**
	 * @var Cache
	 */
	private $cache;

	public function __construct(Reader $reader, IStorage $storage)
	{
		$this->layout = new Layout();
		$this->reader = $reader;
		$this->cache = new Cache($storage, self::CACHE_NAME);
	}

	/**
	 * @throws \Throwable
	 */
	private function saveCache()
	{
		$data = [
			"methods" => $this->methods,
			"parameters" => $this->parameters,
			"layout" => $this->layout,
		];
		$this->cache->save("data", $data);
	}

	/**
	 * @param Route $route
	 * @param ReflectionClass $class
	 * @param ReflectionMethod $method
	 */
	public function routeInitialize(Route $route, \ReflectionClass $class, \ReflectionMethod $method)
	{
		$annotation = $this->reader->getMethodAnnotation($method, API\Method::class);
		if ($annotation instanceof API\Method) {
			$this->routes[] = [
				"route" => $route,
				"method" => $method,
				"annotation" => $annotation,
			];
		}
	}

	/**
	 * @param RouteCollection $collection
	 * @throws \Chomenko\InlineRouting\Exceptions\RouteException
	 * @throws \ReflectionException
	 * @throws \Throwable
	 */
	public function routeInitialized(RouteCollection $collection)
	{
		$data = $this->cache->load("data");
		$empty = FALSE;
		if (!$data) {
			$empty = TRUE;
			$data = [
				"methods" => [],
				"parameters" => [],
				"layout" => new Layout(),
			];
		}

		$this->methods = $data["methods"];
		$this->parameters = $data["parameters"];
		$this->layout = $data["layout"];

		if ($empty) {
			$factory = new Builder($this, $this->reader);
			foreach ($this->routes as $data) {
				$factory->buildMethod($data["route"], $data["method"], $data["annotation"]);
			}
			$this->saveCache();
		}
	}

	/**
	 * @param Method $method
	 */
	public function addMethod(Method $method): void
	{
		$this->methods[$method->getRouteName()] = $method;
		$sectionName = "reference";
		$section = $this->layout->getSection($sectionName);
		if (!$section) {
			$section = new Section($sectionName);
			$this->layout->addSection($section);
		}
		$parent = $section;
		if ($method->getSection()) {
			$col = $section->getColumn(Strings::webalize($method->getSection()));
			if (!$col) {
				$column = new Column($section, $method->getSection(), $method->getSection());
				$section->addColumn($column);
				$parent = $column;
			} else {
				$parent = $col;
			}
		}

		$column = new Column($parent, $method->getRouteName(), $method->getLabel());
		$column->setDescription($method->getDescription());
		$column->setApiMethod($method);
		$parent->addColumn($column);
	}

	/**
	 * @return Method[]
	 */
	public function getMethods(): array
	{
		return $this->methods;
	}

	/**
	 * @param string $routeName
	 * @return Method|null
	 */
	public function getMethod(string $routeName): ?Method
	{
		return isset($this->methods[$routeName]) ? $this->methods[$routeName] : NULL;
	}

	/**
	 * @return Parameter[]
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	/**
	 * @param Parameter $parameter
	 */
	public function addParameter(Parameter $parameter): void
	{
		$this->parameters[] = $parameter;
	}

	/**
	 * @return Layout
	 */
	public function getLayout(): Layout
	{
		return $this->layout;
	}

}
