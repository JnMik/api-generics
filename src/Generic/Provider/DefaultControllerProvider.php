<?php

namespace Support3w\Api\Generic\Provider;

use Support3w\JsonApiTransportService\Service\GuzzleJsonApiTransportService;
use Support3w\Api\Generic\Paging\BaseZeroPaging;
use Support3w\Api\Generic\Paging\PaginatorService;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Class DefaultControllerProvider
 *
 */
class DefaultControllerProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     */
    public function register(Application $app)
    {
        $app['json-api-transport.service'] = $app->share(
            function () use ($app) {
                return new GuzzleJsonApiTransportService();
            }
        );

        $app['paginator.service'] = $app->share(
            function () use ($app) {

                $start = 0;
                $limit = 100;

                if (!is_null($app['request']->query->get('start'))) {
                    $start = $app['request']->query->get('start');
                }

                if (!is_null($app['request']->query->get('limit'))) {
                    $limit = $app['request']->query->get('limit');
                }

                $paging = new BaseZeroPaging(
                    $start,
                    $limit,
                    null,
                    500);

                return new PaginatorService($paging);
            }
        );
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     */
    public function boot(Application $app)
    {
        // TODO: Implement boot() method.
    }
}
