<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\Components;

use Chomenko\NettRest\Structure\Method;
use Chomenko\NettRest\Structure\Structure;
use Chomenko\NettRest\View\Column;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;

class RenderControl extends Control
{

	/**
	 * @var Structure
	 */
	private $structure;

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var array
	 */
	private $columnsComponents = [];

	/**
	 * RenderControl constructor.
	 * @param Structure $structure
	 * @param Container $container
	 */
	public function __construct(Structure $structure, Container $container)
	{
		$this->structure = $structure;
		$this->container = $container;
	}

	/**
	 * @param Presenter $presenter
	 */
	public function attached($presenter)
	{
		parent::attached($presenter);
		$this->createComponentList();
	}

	private function createComponentList()
	{
		$layout = $this->structure->getLayout();
		foreach ($layout->getSections() as $section) {
			$columns = $section->getColumns();
			if ($columns) {
				$this->recursiveCreator($columns);
			}
		}
	}

	/**
	 * @param Column[] $columns
	 */
	private function recursiveCreator(array $columns)
	{
		foreach ($columns as $column) {
			$class = $column->getComponent();
			$factory = $this->container->getByType($class);
			$service = $factory->create($column);
			$name = $column->getComponentName();
			$this->addComponent($service, $name);
			$this->columnsComponents[$name] = $service;
			$this->recursiveCreator($column->getColumns());
		}
	}


	public function renderBookmark()
	{
		$this->template->layout = $this->structure->getLayout();
		$this->template->setFile(__DIR__ . "/templates/bookmark.latte");
		$this->template->render();
	}

	public function renderContent()
	{
		$this->template->components = $this->columnsComponents;
		$this->template->setFile(__DIR__ . "/templates/content.latte");
		$this->template->render();
	}

}
