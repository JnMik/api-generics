<?php

namespace Support3w\Api\Generic\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Support3w\Api\Generic\DataObject\Controller;
use Support3w\Api\Generic\Repository\DefaultRepository;

/**
 * Class RepositoryProvider
 *
 * @package Support3w\Api\Generic\Provider
 * @author  Olivier Beauchemin <obeauchemin@crakmedia.com>
 */
class RepositoryProvider implements ServiceProviderInterface
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
            
            $app[$underscored . '.repository'] = $app->share(
                function () use ($app, $controller) {
                    $underscored = $controller->getNameUnderscored();
                    $fieldTableAlias = array(
                        'id' => $underscored
                    );
                    $mainTableAlias = $underscored . '_alias';
                    
                    // If custom repository exist
                    $repositoryNs = $controller->getRepositoryNamespace();
                    if (class_exists($repositoryNs)) {
                        return new $repositoryNs($app['db'], $underscored, $fieldTableAlias, $mainTableAlias);
                    }
                    
                    return new DefaultRepository($app['db'], $underscored, $fieldTableAlias, $mainTableAlias);
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