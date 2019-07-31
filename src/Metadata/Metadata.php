<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Metadata;

use Chomenko\InlineRouting\Route;
use Chomenko\NettRest\API;
use Chomenko\NettRest\Config;
use Doctrine\Common\Annotations\Reader;

class Metadata
{

	/**
	 * @var array
	 */
	private $scheme = [
		"methods" => [],
		"parameters" => [],
		"sections" => [],
	];

	/**
	 * @var Reader
	 */
	private $reader;

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @param Reader $reader
	 * @param Config $config
	 */
	public function __construct(Reader $reader, Config $config)
	{
		$this->reader = $reader;
		$this->config = $config;
	}

	/**
	 * @param Route $route
	 * @return Method|null
	 */
	public function methodByRoute(Route $route): ?Method
	{
		if (array_key_exists($route->getHash(), $this->scheme["methods"])) {
			return $this->scheme["methods"][$route->getHash()];
		}
		return NULL;
	}

	/**
	 * @param Route $route
	 * @param \ReflectionMethod $refMethod
	 * @param API $annotation
	 * @return Method
	 */
	public function createMethod(Route $route, \ReflectionMethod $refMethod, API $annotation): Method
	{
		$method = new Method($route, $this->config);
		$section = $annotation->getSection();
		$method->setDescription($annotation->getDescription());
		$method->setSection($section);
		$method->setLabel($annotation->getLabel());

		if (!array_key_exists($section, $this->scheme["sections"])) {
			$this->scheme["sections"][$section] = [];
		}

		$this->scheme["sections"][$section][] = $method;
		$this->scheme["methods"][$route->getHash()] = $method;
		return $method;
	}

	/**
	 * @param Method $method
	 * @param \ReflectionMethod $refMethod
	 * @param API\Response $annotation
	 * @return Response
	 * @throws \ReflectionException
	 */
	public function createResponse(Method $method, \ReflectionMethod $refMethod, API\Response $annotation): Response
	{
		$response = $method->getResponse();
		$response->setDescription($annotation->getDescription());

		foreach ($annotation->getErrors() as $error) {
			$response->addError($error);
		}
		foreach ($annotation->getMessages() as $message) {
			$response->addMessage($message);
		}

		$response->setCollection($annotation->isCollection());

		$class = $annotation->getClass();
		if ($class) {
			$reflection = new \ReflectionClass($class);
			$properties = $reflection->getProperties();
			foreach ($properties as $property) {
				$annotations = $this->reader->getPropertyAnnotations($property);
				foreach ($annotations as $propAnnotation) {
					if ($propAnnotation instanceof API\IAnnotation && $this->isAllowGroups($annotation->getGroups(), $propAnnotation->getGroups())) {
						$childParameter = $this->createParameter($response, $propAnnotation, $property);
						$response->addParameter($childParameter);
					}
				}
			}
		}

		foreach ($annotation->getItems() as $name => $example) {
			$parameter = new Parameter($name, $response->getFullName() . "." . $name, NULL);
			$parameter->setExample($example);
			$parameter->setParent($response);
			$response->addParameter($parameter);
		}

		return $response;
	}

	/**
	 * @param IMetaHierarchy $parent
	 * @param API\IParameter $annotation
	 * @param \ReflectionMethod|\ReflectionProperty $reflection
	 * @return Parameter
	 * @throws \ReflectionException
	 */
	public function createParameter(IMetaHierarchy $parent, API\IParameter $annotation, $reflection): Parameter
	{
		$fullName = $parent->getFullName() . "." . $annotation->getName();
		$parameter = new Parameter($annotation->getName(), $fullName, $annotation->getDescription());

		if ($reflection instanceof \ReflectionMethod) {
			$props = $reflection->getParameters();
			foreach ($props as $prop) {
				if ($prop->getName() == $parameter->getName()) {

					if ($prop->getType()) {
						$type = $prop->getType()->getName();
						if (class_exists($type)) {
							$parameter->setType("object");
						} else {
							$parameter->setType($type);
						}
					}

					if (!$prop->isOptional()) {
						$parameter->setRequired(TRUE);
					}
				}
			}
		}

		if ($parent instanceof Method) {
			$method = $parent->getMethod();
			$compile = $method->getRoute()->compile();
			$variables = $compile->getVariables();
			if (array_search($annotation->getName(), $variables) !== FALSE) {
				$parameter->setUrlParameter(TRUE);
				$parameter->setRequired(FALSE);
				if (!$method->getRoute()->getDefault($annotation->getName())) {
					$parameter->setRequired(TRUE);
				}
			}
		}

		$class = $annotation->getType();
		$parameter->setType($annotation->getType());
		if (class_exists($class)) {
			$parameter->setType(API\Parameter::TYPE_OBJECT);
			$reflection = new \ReflectionClass($class);
			$properties = $reflection->getProperties();

			foreach ($properties as $property) {
				$annotations = $this->reader->getPropertyAnnotations($property);
				foreach ($annotations as $propAnnotation) {
					if ($propAnnotation instanceof API\IAnnotation && $this->isAllowGroups($annotation->getGroups(), $propAnnotation->getGroups())) {
						$childParameter = $this->createParameter($parameter, $propAnnotation, $property);
						$parameter->addParameter($childParameter);
					}
				}
			}
		}
		$this->scheme["parameters"][$fullName] = $parameter;
		return $parameter;
	}

	/**
	 * @param array $list
	 * @param array $from
	 * @return bool
	 */
	protected function isAllowGroups(array $list, array $from): bool
	{
		foreach ($list as $group) {
			if (array_search($group, $from) !== FALSE) {
				return TRUE;
			}
		}

		if (empty($from)) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return array
	 */
	public function getScheme(): array
	{
		return $this->scheme;
	}

	/**
	 * @return array
	 */
	public function getSections(): array
	{
		return $this->scheme["sections"];
	}

}
