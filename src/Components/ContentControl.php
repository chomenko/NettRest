<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Components;

use Chomenko\InlineRouting\Routing;
use Chomenko\NettRest\Config;
use Chomenko\NettRest\Structure\Field;
use Chomenko\NettRest\Structure\FieldsStructure;
use Chomenko\NettRest\Structure\Method;
use Chomenko\NettRest\Structure\Request;
use Chomenko\NettRest\View\Column;
use Nette\Application\UI\Control;
use Nette\Utils\Html;

class ContentControl extends Control implements ContentInterface
{

	/**
	 * @var Column
	 */
	private $column;

	/**
	 * @var Routing
	 */
	private $routing;

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @param Column $column
	 * @param Routing $routing
	 * @param Config $config
	 */
	public function __construct(Column $column, Routing $routing, Config $config)
	{
		$this->column = $column;
		$this->routing = $routing;
		$this->config = $config;
	}

	public function renderDefinition()
	{
		$this->template->column = $this->column;
		$this->template->method = $this->column->getApiMethod();
		$this->template->setFile(__DIR__ . "/templates/definition.latte");
		$this->template->render();
	}

	public function renderExample()
	{
		$this->template->column = $this->column;
		$this->template->method = $this->column->getApiMethod();
		$this->template->setFile(__DIR__ . "/templates/example.latte");
		$this->template->render();
	}

	/**
	 * @param Method $method
	 * @return array
	 * @internal
	 */
	public function getMethodAccessMethods(Method $method): array
	{
		$route = $this->routing->getRoute($method->getRouteName());
		if ($route) {
			return $route->getMethods();
		}
		return [];
	}

	/**
	 * @param Method $method
	 * @return Html
	 * @internal
	 */
	public function getMethodUrl(Method $method): Html
	{
		$route = $this->routing->getRoute($method->getRouteName());
		$path = $route->getPath();
		foreach ($route->compile()->getPathVariables() as $name) {
			$path = str_replace("{" . $name . "}", "<span class='parameter'>{$name}</span>", $path);
		}
		return Html::el("span")->setHtml($this->config->getHost() . $path);
	}

	/**
	 * @param FieldsStructure $structure
	 * @param bool $urlField
	 * @return Html
	 */
	public function getHtmlFieldsStructure(FieldsStructure $structure, bool $urlField = FALSE): ?Html
	{
		$fields = $fields = $structure->getFields();
		if ($fields) {
			$wrapped = Html::el("ul", ["class" => "attributes"]);
			$this->recursiveFields($fields, $wrapped, $urlField);
			if (!$wrapped->getChildren()) {
				return NULL;
			}
			return $wrapped;
		}
		return NULL;
	}

	/**
	 * @param Field[] $fields
	 * @param Html $wrapped
	 * @param bool $urlField
	 */
	protected function recursiveFields(array $fields, Html $wrapped, bool $urlField = FALSE)
	{
		foreach ($fields as $field) {
			if ($urlField && !$field->isUrlParameter()) {
				continue;
			} elseif (!$urlField && $field->isUrlParameter()) {
				continue;
			}

			$parameter = $field->getParameter();
			$li = Html::el("li");
			$li->addHtml(Html::el("b", [
				"class" => "name",
			])->setText($field->getName()));

			$type = $parameter->getType();
			if ($field->getFields()) {
				$type = "object";
			}
			if ($parameter->isCollection()) {
				$type = "collection";
			}

			$structure = $field->getFieldStructure();
			$types = " " . $type;
			if ($structure instanceof Request) {
				$types .= ":" . ($field->isRequired() ? "required" : "optional");
			}
			if ($field->isRecursive()) {
				$types .= ":recursive";
			}

			$li->addHtml(Html::el("code")
				->setText($types));

			$li->addHtml(Html::el("span")
				->setText(" " . $parameter->getDescription()));
			$wrapped->addHtml($li);

			if (!$field->isRecursive()) {
				if ($field->getFields()) {
					$ul = Html::el("ul", ["class" => "attributes"]);
					$li->addHtml($ul);
					$this->recursiveFields($field->getFields(), $ul, $urlField);
				}
			}
		}
	}

	/**
	 * @param FieldsStructure $structure
	 * @return Html
	 */
	public function getHtmlResponseExample(FieldsStructure $structure): Html
	{
		$response = [
			"code" => 200,
			"data" => $this->getResponseTree($structure->getFields()),
			"errors" => [],
			"messages" => [],
		];
		$json = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		return $pre = Html::el("pre", ["class" => "raw-section"])->addText($json);
	}

	/**
	 * @param Field[] $fields
	 * @return array
	 */
	protected function getResponseTree(array $fields): array
	{
		$data = [];
		foreach ($fields as $field) {
			if ($field->isRecursive()) {
				continue;
			}

			if ($field->getFields()) {
				$value = $this->getResponseTree($field->getFields());
			} else {
				$value = $field->getParameter()->getExample();
			}

			$parent = $field->getParent();
			if ($parent instanceof FieldsStructure && $parent->isCollection()
				|| $parent instanceof Field && $parent->getParameter()->isCollection()) {
				$data[0][$field->getName()] = $value;
				continue;
			}
			$data[$field->getName()] = $value;
		}
		return $data;
	}

}
