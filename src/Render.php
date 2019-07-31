<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest;

use Chomenko\NettRest\Metadata\Metadata;
use Chomenko\NettRest\Metadata\Method;
use Nette\Utils\Html;
use Nette\Utils\Strings;

class Render
{

	/**
	 * @var Metadata
	 */
	private $metadata;

	public function __construct(Metadata $metadata)
	{
		$this->metadata = $metadata;
	}

	public function renderReferenceSections()
	{
		$prefix = "#introduction";
		$wrapped = Html::el("div", ["class" => "section introduction"]);
		$wrapped->addHtml(Html::el("h6", [
			"class" => "sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-green",
		])->setText("Introduction"));

		$list = Html::el("ul", [
			"class" => "nav flex-column mb-2",
		]);

		/**
		 * @var string $name
		 * @var Method[] $methods
		 */
		foreach ($this->metadata->getSections() as $name => $methods) {

			$parent = $list;
			$group = NULL;
			if (!empty($name)) {

				$group = Strings::webalize($name);
				$li = Html::el("li", [
					"class" => "nav-item",
				]);

				$link = Html::el("a", [
					"class" => "nav-link",
					"href" => $prefix . "/" . $group
				])->setText($name);
				$li->addHtml($link);

				$ul = Html::el("ul", [
					"class" => "nav flex-column",
				]);

				$li->addHtml($ul);
				$parent->addHtml($li);
				$parent = $ul;
			}

			foreach ($methods as $method) {
				$li = Html::el("li", [
					"class" => "nav-item",
				]);

				$label = $method->getLabel();
				if (!$label) {
					$method->getMethodName();
				}

				$hash = $prefix . "/" . ($group ? $group . "/" : "") . Strings::webalize($label);

				$link = Html::el("a", [
					"class" => "nav-link",
					"href" => $hash
				])->setText($label);
				$li->addHtml($link);
				$parent->addHtml($li);
			}
		}

		return $wrapped->addHtml($list);
	}

	/**
	 * @return Html
	 */
	public function renderContent(): Html
	{
		$wrapped = Html::el();
		/**
		 * @var string $name
		 * @var Method[] $methods
		 */
		foreach ($this->metadata->getSections() as $name => $methods) {
			foreach ($methods as $method) {
				$wrapped->addHtml($method->renderRequest());
				$wrapped->addHtml($method->renderResponse());
			}
		}
		return $wrapped;
	}

}
