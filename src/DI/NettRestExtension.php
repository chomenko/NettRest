<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\DI;

use Chomenko\NettRest\Doc\DocPresenter;
use Chomenko\NettRest\ErrorPresenter;
use Chomenko\NettRest\Render;
use Chomenko\NettRest\Metadata\Metadata;
use Chomenko\NettRest\Provider;
use Chomenko\NettRest\RequestBuilder;
use Chomenko\NettRest\Response;
use Chomenko\NettRest\InlineRouting;
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

	private $services = [
		"render" => Render::class,
		"doc" => DocPresenter::class,
		"error" => ErrorPresenter::class,
		"metadata" => Metadata::class,
		"requestBuilder" => RequestBuilder::class,
		"response" => Response::class,
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->default);

		$builder->addDefinition($this->prefix('config'))
			->setFactory(Config::class, ["settings" => $config]);

		$builder->addDefinition($this->prefix('inline.routing'))
			->setFactory(InlineRouting::class)
			->addTag("kdyby.subscriber", TRUE)
			->setAutowired(TRUE);

		$builder->addDefinition($this->prefix('provider'))
			->setFactory(Provider::class)
			->addTag("kdyby.subscriber", TRUE)
			->setAutowired(TRUE);

		foreach ($this->services as $id => $class) {
			$builder->addDefinition($this->prefix($id))
				->setFactory($class)
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
