<?php

namespace Support3w\Api\Generic\Provider;

use Stringy\Stringy as S;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Support3w\Api\Generic\DataObject\Controller;

/**
 * Class ControllerProvider
 *
 * @package Support3w\Api\Generic\Provider
 * @author  Olivier Beauchemin <obeauchemin@crakmedia.com>
 */
class ControllerProvider implements ServiceProviderInterface
{
    /**
     * @var array
     */
    private $controllers;

    /**
     * @param array $controllers
     */
    public function __construct(array $controllers)
    {
        $this->controllers = $controllers;
    }

    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Application $app
     */
    public function register(Application $app)
    {
        /** @var Controller $controller */
        foreach ($this->controllers as $controller) {
            $underscored = $controller->getNameUnderscored();
            $app[$underscored . '.controller'] = $app->share(
                function () use ($app, $controller) {
                    $namespace = $controller->getNamespace();

                    return new $namespace(
                        $app['rest_normalizer.builder'],
                        $app['logger'],
                        $app[$controller->getNameUnderscored() . '.repository'],
                        $app['request'],
                        $app['hateoas'],
                        $app['paginator.service'],
                        $app['json-api-transport.service'],
                        $controller->getModelNamespace()
                    );
                }
            );
        }
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     *
     * @param Application $app
     */
    public function boot(Application $app)
    {
        // TODO: Implement boot() method.
    }
}