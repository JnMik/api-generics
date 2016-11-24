<?php

namespace Support3w\Api\Generic\Controller;

use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use Support3w\Api\Generic\Exception\InvalidDataIdException;
use Support3w\Api\Generic\Model\DefaultModel;
use Support3w\Api\Generic\Model\ModelInterface;
use Support3w\Api\Generic\Paging\PaginatorService;
use Support3w\Api\Generic\Repository\RepositoryBase;
use ReflectionClass;
use Support3w\JsonApiTransportService\Service\JsonApiTransportService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ControllerBase
 *
 * @package Support3w\Api\Generic\Controller
 */
abstract class ControllerBase extends Controller
{
    /**
     * @var RepositoryBase;
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
     * @var PaginatorService
     */
    protected $paginatorService;

    /**
     * @var JsonApiTransportService
     */
    protected $jsonApiTransportService;

    /**
     * @var string
     */
    protected $defaultModelClassName;

    /**
     * @param \Closure $responseBuilderClosure
     * @param $logger
     * @param RepositoryBase $repository
     * @param Request $request
     * @param array $hateoas
     * @param PaginatorService $paginatorService
     * @param JsonApiTransportService $jsonApiTransportService
     * @param string $defaultModelClassName
     */
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
        parent::__construct($responseBuilderClosure);

        $this->repository = $repository;
        $this->request = $request;
        $this->hateoas = $hateoas;
        $this->paginatorService = $paginatorService;
        $this->jsonApiTransportService = $jsonApiTransportService;
        $this->defaultModelClassName = $defaultModelClassName;
    }

    /**
     * Fetch ALL
     *
     * @return JsonResponse
     */
    public function fetchAll()
    {
        try {
            if ($this->request->query->count() >= 1) {
                $data = $this->repository->findByParameters(
                    $this->paginatorService,
                    $this->request->query->getIterator()->getArrayCopy()
                );
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

        }catch(Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'msg' => 'Unexpected error.',
                'dev_details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create
     *
     * @return JsonResponse
     */
    public function create()
    {
        try {
            /** @var DefaultModel $object */
            $reflectionClass = new ReflectionClass($this->defaultModelClassName);
            $object = $reflectionClass->newInstance();
            $object->loadFromJson($this->request->getContent());
            $object = $this->repository->create($object);

            // Convert object to array
            $object = json_decode(json_encode($object), true);

            /**
             * @var array $object
             */
            $object = $this->addHATEOAS($object);

            return new JsonResponse([
                'status' => 'OK',
                'data' => $object
            ], 200);

        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse([
                'status' => 'error',
                'msg' => 'Unique constraint violation.',
                'dev_details' => $e->getMessage()
            ], 409);

        } catch (NotNullConstraintViolationException $e) {
            return new JsonResponse([
                'status' => 'error',
                'msg' => 'Field cannot be null.',
                'dev_details' => $e->getMessage()
            ], 417);

        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'msg' => 'Unexpected error.',
                'dev_details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Find by ID
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function findById($id)
    {
        try {

            $data = $this->repository->findById($id);

            if ($data) {
                $data = $this->addHATEOAS($data);
            }

            return new JsonResponse([
                'status' => 'OK',
                'data' => $data
            ], 200);

        }catch(Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'msg' => 'Unexpected error.',
                'dev_details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function update($id)
    {
        try {

            if(!is_numeric($id)) {
                throw new InvalidDataIdException('Id must be numeric');
            }

            /** @var ModelInterface $object */
            $reflectionClass = new ReflectionClass($this->defaultModelClassName);
            $object = $reflectionClass->newInstance();

            // We preload the object first from the database, that allow partial update without error
            $data = $this->repository->findById($id);
            $object->loadFromArray($data);
            $object->loadFromJson($this->request->getContent());
            $object = $this->repository->update($object, $id);

            // Convert object to array
            $object = json_decode(json_encode($object), true);

            /**
             * @var array $object
             */
            $object = $this->addHATEOAS($object);

            return new JsonResponse([
                'status' => 'OK',
                'data' => $object
            ], 200);

        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse([
                'status' => 'error',
                'msg' => 'Unique constraint violation.',
                'dev_details' => $e->getMessage()
            ], 409);

        } catch (NotNullConstraintViolationException $e) {
            return new JsonResponse([
                'status' => 'error',
                'msg' => 'Field cannot be null.',
                'dev_details' => $e->getMessage()
            ], 417);

        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'msg' => 'Unexpected error.',
                'dev_details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws \Support3w\Api\Generic\Exception\DataModificationException
     */
    public function delete($id)
    {

        try {
            $success = $this->repository->delete($id);

            return new JsonResponse([
                'status' => 'OK',
                'success' => $success
            ], 200);
        }catch(Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'msg' => 'Unexpected error.',
                'dev_details' => $e->getMessage()
            ], 500);
        }

    }

    /**
     * Add hateoas
     *
     * @param array $data
     *
     * @return array
     */
    public function addHATEOAS(array $data)
    {
        return $data;
    }

    /**
     * Apply filters on Hateoas
     *
     * @return mixed
     */
    abstract public function applyFiltersOnHateoas();
}