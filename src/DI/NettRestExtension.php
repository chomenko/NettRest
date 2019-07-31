<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Chomenko\NettRest\DI;

use Chomenko\NettRest\Doc\DocPresenter;
use Chomenko\NettRest\Render;
use Chomenko\NettRest\Metadata\Metadata;
use Chomenko\NettRest\RequestProvider;
use Chomenko\NettRest\ResponseProvider;
use Chomenko\NettRest\RoutingProvider;
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
		"mappings" => [
			'App\ApiModule\<version>\<presenter>Api',
		],
		"module" => "Api",
		"host" => NULL,
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		if ($this->default["host"] === NULL && isset($_SERVER['HTTP_HOST'])) {
			$this->default["host"] = (empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
		}

		$config = $this->getConfig($this->default);

		$builder->addDefinition($this->prefix('config'))
			->setFactory(Config::class, ["settings" => $config]);

		$builder->addDefinition($this->prefix('routing.provider'))
			->setFactory(RoutingProvider::class)
			->addTag("kdyby.subscriber", TRUE)
			->setAutowired(TRUE);

		$builder->addDefinition($this->prefix('response.provider'))
			->setFactory(ResponseProvider::class)
			->addTag("kdyby.subscriber", TRUE)
			->setAutowired(TRUE);

		$builder->addDefinition($this->prefix('inline.subscriber'))
			->setFactory(RoutingProvider::class)
			->addTag("kdyby.subscriber", TRUE)
			->setAutowired(TRUE);

		$builder->addDefinition($this->prefix('metadata'))
			->setFactory(Metadata::class)
			->setAutowired(TRUE);

		$builder->addDefinition($this->prefix('presenter'))
			->setFactory(DocPresenter::class)
			->setAutowired(TRUE);

		$builder->addDefinition($this->prefix('request.provider'))
			->setFactory(RequestProvider::class)
			->setAutowired(TRUE);

		$builder->addDefinition($this->prefix('doc.render'))
			->setFactory(Render::class)
			->setAutowired(TRUE);
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
