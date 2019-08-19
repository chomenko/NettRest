<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Structure;

use BadMethodCallException;
use Chomenko\InlineRouting\Route;
use Chomenko\NettRest\API;
use Doctrine\Common\Annotations\Reader;
use ReflectionMethod;
use ReflectionClass;
use Chomenko\InlineRouting\Exceptions\RouteException;
use ReflectionProperty;
use Reflector;
use Tracy\Debugger;

class Builder
{

	/**
	 * @var Structure
	 */
	private $structure;

	/**
	 * @var Reader
	 */
	private $reader;

	/**
	 * MethodFactory constructor.
	 * @param Structure $structure
	 * @param Reader $reader
	 */
	public function __construct(Structure $structure, Reader $reader)
	{
		$this->structure = $structure;
		$this->reader = $reader;
	}

	/**
	 * @param Route $route
	 * @param ReflectionMethod $refMethod
	 * @param API\Method $annotation
	 * @return Method
	 * @throws RouteException
	 * @throws \ReflectionException
	 */
	public function buildMethod(Route $route, ReflectionMethod $refMethod, API\Method $annotation): Method
	{
		$methodName = $refMethod->getName();
		$className = $refMethod->getDeclaringClass()->getName();
		$method = new Method($route->getName(), $methodName, $className);
		$method->setDescription($annotation->getDescription());
		$method->setSection($annotation->getSection());
		$method->setLabel($annotation->getLabel());

		$this->createRequestStructure($method, $refMethod, $route);
		$this->createResponseStructure($method, $refMethod);

		$this->structure->addMethod($method);
		return $method;
	}

	/**
	 * @param API\IParameter $annotation
	 * @param Reflector $reflection
	 * @return Parameter
	 */
	public function buildParameter(API\IParameter $annotation, Reflector $reflection): Parameter
	{
		if ($reflection instanceof ReflectionProperty) {
			$className = $reflection->getDeclaringClass()->getName();
			$propertyName = $reflection->getName();
			$parameters = $this->structure->getParameters();
			$key = $className . "::" . $propertyName;
			if (array_key_exists($key, $parameters)) {
				return $parameters[$key];
			}
		}

		$name = $this->getName($annotation, $reflection);
		$parameter = new Parameter($name);
		$parameter->setDescription($annotation->getDescription());
		$parameter->setRequired($annotation->getRequired());
		$parameter->setGroups($annotation->getGroups());
		$parameter->setExample($annotation->getExample());
		$parameter->setCollection($annotation->isCollection());
		$parameter->setType($annotation->getType());

		$class = $annotation->getType();
		if (class_exists($class)) {
			$parameter->setType(API\Parameter::TYPE_OBJECT);
			$parameter->setTypeClass($class);
		}

		$this->setRules($parameter, $annotation);
		$this->setTypes($parameter, $reflection);
		$this->structure->addParameter($parameter);
		return $parameter;
	}

	/**
	 * @param Method $method
	 * @param ReflectionMethod $refMethod
	 * @param Route $route
	 * @return Request
	 * @throws \ReflectionException
	 */
	private function createRequestStructure(Method $method, ReflectionMethod $refMethod, Route $route): Request
	{
		$structure = $method->getRequest();
		$annotation = $this->reader->getMethodAnnotation($refMethod, API\Request::class);

		$compile = $route->compile();
		$structure->setVariables($compile->getVariables());
		foreach ($compile->getVariables() as $variable) {
			if (!$structure->getField($variable)) {
				$parameterAnnotation = new API\Parameter([]);
				$parameterAnnotation->setName($variable);
				if ($value = $route->getDefault($variable)) {
					$parameterAnnotation->setExample($value);
				}
				$structureParameter = $this->createParameter($structure, $parameterAnnotation, $refMethod, $parameterAnnotation->getGroups());
				$structureParameter->setUrlParameter();
			}
		}

		if (!$annotation instanceof API\Request) {
			return $structure;
		}

		$structure->setCollection($annotation->isCollection());
		$structure->setGroups($annotation->getGroups());
		$structure->setClass($annotation->getClass());
		$structure->setInto($annotation->getInto());
		$structure->setDescription($annotation->getDescription());


		$class = $annotation->getClass();
		if ($class) {
			$this->createClassStructure($structure, $class, $annotation->getGroups());
		}

		$this->createListStructure($structure, $annotation->getParameters(), $refMethod, $annotation->getGroups());

		return $structure;
	}

	/**
	 * @param Method $method
	 * @param ReflectionMethod $refMethod
	 * @return Response
	 * @throws \ReflectionException
	 */
	private function createResponseStructure(Method $method, ReflectionMethod $refMethod): Response
	{
		$structure = $method->getResponse();
		$annotation = $this->reader->getMethodAnnotation($refMethod, API\Response::class);
		if (!$annotation instanceof API\Response) {
			return $structure;
		}

		$structure->setCollection($annotation->isCollection());
		$structure->setGroups($annotation->getGroups());
		$structure->setClass($annotation->getClass());
		$structure->setDescription($annotation->getDescription());


		$class = $annotation->getClass();
		if ($class) {
			$this->createClassStructure($structure, $class, $annotation->getGroups());
		}
		$this->createListStructure($structure, $annotation->getParameters(), $refMethod, $annotation->getGroups());
		return $structure;
	}

	/**
	 * @param IStructure|Field $parent
	 * @param string $class
	 * @param array $groups
	 * @throws \ReflectionException
	 */
	private function createClassStructure(IStructure $parent, string $class, array $groups = [])
	{
		$reflection = new ReflectionClass($class);
		$properties = $reflection->getProperties();
		foreach ($properties as $property) {
			$annotation = $this->reader->getPropertyAnnotation($property, API\IParameter::class);
			if ($annotation instanceof API\IParameter) {
				$this->createParameter($parent, $annotation, $property, $groups);
			}
		}
	}

	/**
	 * @param IStructure|Field $parent
	 * @param API\IParameter[] $annotations
	 * @param ReflectionMethod $refMethod
	 * @param array $groups
	 * @throws \ReflectionException
	 */
	private function createListStructure(IStructure $parent, array $annotations, ReflectionMethod $refMethod, array $groups = [])
	{
		foreach ($annotations as $parameter) {
			$groups = array_merge($groups, $parameter->getGroups());
			$this->createParameter($parent, $parameter, $refMethod, $groups);
		}
	}

	/**
	 * @param IStructure|Field $parent
	 * @param API\IParameter $annotation
	 * @param Reflector $reflection
	 * @param array $groups
	 * @return Field|null
	 * @throws \ReflectionException
	 */
	private function createParameter(IStructure $parent, API\IParameter $annotation, Reflector $reflection, array $groups = []): ?Field
	{
		$parameter = $this->buildParameter($annotation, $reflection);
		if (!$parameter->isAllowGroups($groups)) {
			return NULL;
		}

		$parStructure = new Field($parent, $parameter);
		$parent->addField($parameter->getName(), $parStructure);

		if ($parent instanceof Request) {
			$variables = $parent->getVariables();
			if (array_search($parStructure->getName(), $variables) !== FALSE) {
				$parStructure->setUrlParameter(TRUE);
			}
		}

		if (!$parameter->hasType(API\Parameter::TYPE_OBJECT)) {
			return $parStructure;
		}

		if ($parent instanceof Field && $parent->getParameter()->getDeclareProperty()) {
			$structure = $this->detectRecursiveStructure($parent, $parameter->getTypeClass(), $parent->getParameter()->getDeclareProperty());
			if ($structure instanceof Field) {
				$parStructure->setRecursive(TRUE);
				foreach ($structure->getFields() as $name => $field) {
					$parStructure->addField($name, $field);
				}
				return $parStructure;
			}
		}

		$this->createClassStructure($parStructure, $parameter->getTypeClass(), $groups);
		return $parStructure;
	}


	/**
	 * @param mixed $parent
	 * @param string $class
	 * @param string $property
	 * @return IStructure|null
	 */
	private function detectRecursiveStructure($parent, string $class, string $property): ?IStructure
	{
		if ($parent instanceof Field) {
			if ($parent->getParameter()->getDeclareClass() === $class && $parent->getParameter()->getDeclareProperty() === $property) {
				return $parent;
			}
			if ($parent->getParent() instanceof IStructure) {
				return $this->detectRecursiveStructure($parent->getParent(), $class, $property);
			}
		}
		return NULL;
	}


	/**
	 * @param API\IParameter $annotation
	 * @param Reflector|ReflectionProperty|ReflectionClass $reflection
	 * @return string
	 */
	private function getName(API\IParameter $annotation, Reflector $reflection): string
	{
		$name = $annotation->getName();
		if (!$name) {
			if (!$reflection instanceof ReflectionProperty) {
				$mustClass = API\Parameter::class;
				$inClass = $reflection->getDeclaringClass()->getName();
				throw new BadMethodCallException("Annotation '{$mustClass}' must sets 'name'. In '{$inClass}'");
			}
			$name = $reflection->getName();
		}
		return $name;
	}

	/**
	 * @param Parameter $parameter
	 * @param API\IParameter $annotation
	 */
	private function setRules(Parameter $parameter, API\IParameter $annotation): void
	{
		foreach ($annotation->getRules() as $rule) {
			$parameter->addRule($rule);
		}
	}

	/**
	 * @param Parameter $parameter
	 * @param Reflector $reflection
	 */
	private function setTypes(Parameter $parameter, Reflector $reflection): void
	{
		if ($reflection instanceof ReflectionProperty) {
			$className = $reflection->getDeclaringClass()->getName();
			$propertyName = $reflection->getName();
			$parameter->setDeclare($className, $propertyName);
		}
	}

}
