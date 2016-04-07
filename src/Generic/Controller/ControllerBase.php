<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jm
 * Date: 15-12-03
 * Time: 20:43
 * To change this template use File | Settings | File Templates.
 */

namespace Support3w\Api\Generic\Controller;

use Support3w\Api\Generic\Model\DefaultModel;
use Support3w\Api\Generic\Paging\PaginatorService;
use Support3w\Api\Generic\Repository\RepositoryBase;
use ReflectionClass;
use Support3w\JsonApiTransportService\Service\JsonApiTransportService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class ControllerBase extends Controller
{

    /**
     * @var \Support3w\Api\Generic\Repository\RepositoryBase;
     */
    protected $repository;
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;
    /**
     * @var array
     */
    protected $hateoas;
    /**
     * @var \Support3w\Api\Generic\Paging\PaginatorService
     */
    protected $paginatorService;

    /**
     * @var \Support3w\JsonApiTransportService\Service\JsonApiTransportService
     */
    protected $jsonApiTransportService;

    protected $defaultModelClassName;

    public function __construct(
        \Closure $responseBuilderClosure,
        $logger,
        RepositoryBase $repository,
        Request $request,
        array $hateoas,
        PaginatorService $paginatorService,
        JsonApiTransportService $jsonApiTransportService,
        $defaultModelClassName
    )
    {
        parent::__construct($responseBuilderClosure, $logger);

        $this->repository = $repository;
        $this->request = $request;
        $this->hateoas = $hateoas;
        $this->paginatorService = $paginatorService;
        $this->jsonApiTransportService = $jsonApiTransportService;
        $this->defaultModelClassName = $defaultModelClassName;
    }

    public function fetchAll()
    {

        if ($this->request->query->count() >= 1) {
            $data = $this->repository->findByParameters($this->paginatorService, $this->request->query->getIterator()->getArrayCopy());
        } else {
            $data = $this->repository->fetchAll($this->paginatorService);
        }

        if ($data) {
            $data = $this->addHATEOAS($data);
        }

        return new JsonResponse([
            'status' => 'OK',
            'data' => $data,
            'rows' => $this->paginatorService->getBaseZeroPaging()->getRowsCount(),
            'pages' => $this->paginatorService->getBaseZeroPaging()->getPageCount(),
            'paging' => $this->paginatorService->getBaseZeroPaging()->getResponse()
        ], 200);

    }

    public function create()
    {

        try {
            /** @var DefaultModel $object */
            $reflectionClass = new ReflectionClass($this->defaultModelClassName);
            $object = $reflectionClass->newInstance();
            $object->loadFromJson($this->request->getContent());
            $object = $this->repository->create($object);
            // convert object to array
            $object = json_decode(json_encode($object), true);
            $object = $this->addHATEOAS($object);

            return new JsonResponse(['status' => 'OK', 'data' => $object], 200);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            return new JsonResponse(['status' => 'error', 'msg' => 'Unique constraint violation.'], 409);
        } catch (\Doctrine\DBAL\Exception\NotNullConstraintViolationException $e) {
            return new JsonResponse(['status' => 'error', 'msg' => 'Field cannot be null.'], 417);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'msg' => $e->getMessage()], 500);
        }
    }

    public function findById($id)
    {
        $data = $this->repository->findById($id);
        if ($data) {
            $data = $this->addHATEOAS($data);
        }
        return new JsonResponse(['status' => 'OK', 'data' => $data], 200);
    }

    public function update($id)
    {

        try {
            /** @var DefaultModel $object */
            $reflectionClass = new ReflectionClass($this->defaultModelClassName);
            $object = $reflectionClass->newInstance();

            // We preload the object first from the database, that allow partial update without error
            $data = $this->repository->findById($id);
            $object->loadFromArray($data);

            $object->loadFromJson($this->request->getContent());
            $object = $this->repository->update($object, $id);
            $object = json_decode(json_encode($object), true);
            $object = $this->addHATEOAS($object);
            return new JsonResponse(['status' => 'OK', 'data' => $object], 200);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            return new JsonResponse(['status' => 'error', 'msg' => 'Unique constraint violation.'], 409);
        } catch (\Doctrine\DBAL\Exception\NotNullConstraintViolationException $e) {
            return new JsonResponse(['status' => 'error', 'msg' => 'Field cannot be null.'], 417);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'msg' => $e->getMessage()], 500);
        }

    }

    public function delete($id)
    {
        $success = $this->repository->delete($id);
        return new JsonResponse(['status' => 'OK', 'success' => $success], 200);
    }

    /**
     * @param array $data
     * @return array
     */
    public function addHATEOAS(array $data)
    {
        return $data;
    }

    /**
     * @return mixed
     */
    abstract public function applyFiltersOnHateoas();

}