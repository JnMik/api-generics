<?php

namespace Support3w\Api\Generic\DataObject;

use Stringy\Stringy as S;

/**
 * Class Controller
 *
 * @package Support3w\Api\Generic\DataObject
 * @author  Olivier Beauchemin <obeauchemin@crakmedia.com>
 */
class Controller
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $modelNamespace;

    /**
     * @var string
     */
    private $repositoryNamespace;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $fullName;


    /**
     * @param string $namespace
     */
    public function __construct($namespace)
    {
        $this->namespace = $namespace;
        $this->modelNamespace = $this->findModelNamespace($namespace);
        $this->repositoryNamespace = $this->findRepositoryNamespace($namespace);
        $this->name = $this->findControllerName($namespace);
        $this->fullName = $this->name . 'Controller';
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getModelNamespace()
    {
        return $this->modelNamespace;
    }

    /**
     * @return string
     */
    public function getRepositoryNamespace()
    {
        return $this->repositoryNamespace;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @return string
     */
    public function getNameUnderscored()
    {
        $underscored = S::create($this->name)->underscored()->__toString();

        return $underscored;
    }

    /**
     * Find controller's name
     *
     * @param string $namespace
     * @return bool|string
     */
    private function findControllerName($namespace)
    {
        $exploded = explode('\\', $namespace);
        $controller = $exploded[count($exploded) - 1];

        if (($pos = strrpos($controller, 'Controller')) !== false) {
            $searchLength  = strlen('Controller');
            $str = substr_replace($controller, '', $pos, $searchLength);

            return $str;
        }

        return false;
    }

    /**
     * Find model's namespace
     *
     * @param string $namespace Controller namespace
     *
     * @return bool|string
     */
    private function findModelNamespace($namespace)
    {
        $modelNs = str_replace('\\Controller\\', '\\Model\\', $namespace);
        if (($pos = strrpos($modelNs, 'Controller')) !== false) {
            $searchLength  = strlen('Controller');
            $str = substr_replace($modelNs, 'Model', $pos, $searchLength);

            return $str;
        }

        return false;
    }

    /**
     * Find repository's namespace
     *
     * @param string $namespace Controller namespace
     *
     * @return bool|string
     */
    private function findRepositoryNamespace($namespace)
    {
        $modelNs = str_replace('\\Controller\\', '\\Repository\\', $namespace);
        if (($pos = strrpos($modelNs, 'Controller')) !== false) {
            $searchLength  = strlen('Controller');
            $str = substr_replace($modelNs, 'Repository', $pos, $searchLength);

            return $str;
        }

        return false;
    }
}