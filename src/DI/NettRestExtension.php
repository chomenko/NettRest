<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\DI;

use Chomenko\NettRest\ApiManager;
use Chomenko\NettRest\Components\IContentControl;
use Chomenko\NettRest\Components\IRenderControl;
use Chomenko\NettRest\Module\DocPresenter;
use Chomenko\NettRest\Module\ErrorPresenter;
use Chomenko\NettRest\Response;
use Chomenko\NettRest\ResponseDriver;
use Chomenko\NettRest\Structure\Structure;
use Chomenko\NettRest\Subscribers\Application;
use Chomenko\NettRest\Subscribers\InlineRouting;
use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Chomenko\NettRest\Config;

class NettRestExtension extends CompilerExtension
{

	/**
	 * @var array
	 */
	private $default = [
		"host" => NULL,
	];

	/**
	 * @var array
	 */
	private $services = [
		"docPresenter" => DocPresenter::class,
		"errorPresenter" => ErrorPresenter::class,
		"metadata" => Structure::class,
		"responseBuilder" => ResponseDriver::class,
		"response" => Response::class,
		"manager" => ApiManager::class,
	];

	/**
	 * @var array
	 */
	private $components = [
		"renderControl" => IRenderControl::class,
		"contentControl" => IContentControl::class,
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->default);

		$builder->addDefinition($this->prefix('config'))
			->setFactory(Config::class, ["settings" => $config]);

		$builder->addDefinition($this->prefix('subscriber.inlineRouting'))
			->setFactory(InlineRouting::class)
			->addTag("kdyby.subscriber", TRUE)
			->setAutowired(TRUE);

		$builder->addDefinition($this->prefix('subscriber.application'))
			->setFactory(Application::class)
			->addTag("kdyby.subscriber", TRUE)
			->setAutowired(TRUE);

		foreach ($this->services as $id => $class) {
			$builder->addDefinition($this->prefix($id))
				->setFactory($class)
				->setAutowired(TRUE);
		}

		foreach ($this->components as $id => $class) {
			$builder->addDefinition($this->prefix($id))
				->setImplement($class)
				->setAutowired(TRUE);
		}
	}

	/**
	 * @param Configurator $configurator
	 */
	public static function register(Configurator $configurator)
	{
		$configurator->onCompile[] = function ($config, Compiler $compiler) {
			$compiler->addExtension('nettrest', new NettRestExtension());
		};
	}

}
