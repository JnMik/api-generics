<?php

namespace Support3w\Api\Generic\Provider;

use Crak\Component\RestNormalizer\Builder\ResponseBuilder;
use Crak\Component\RestNormalizer\HttpMethod;
use Crak\Component\RestNormalizer\Parameter;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Class RestNormalizerProvider
 *
 */
class RestNormalizerProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     */
    public function register(Application $app)
    {
        if ($app->offsetGet('api.version') === false) {
            throw new \Exception('Missing api version');
        }

        $app['rest_normalizer.builder'] = $app->protect(
            function ($object = null) use ($app) {
                $request = $app['request'];
                // @var $request Request

                $builder = ResponseBuilder::create(
                    $app['api.version'],
                    HttpMethod::valueOf($request->getMethod()),
                    $object
                );

                foreach ($request->request->all() as $key => $value) {
                    $parameter = Parameter::create($key, $value);
                    $builder->addParameter($parameter);
                }

                return $builder;
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
    }
}
