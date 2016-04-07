<?php

namespace Support3w\Api\Generic\Schema;

use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RequestSchemaValidator
 *
 */
class RequestSchemaValidator
{

    /**
     * @var string
     */
    private $schemaUrl;

    public function __construct($schemaUrl)
    {
        $this->schemaUrl = $schemaUrl;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function validate(Request $request)
    {

        $retriever = new UriRetriever;
        $schema = $retriever->retrieve('file://' . $this->schemaUrl);
        $validator = new Validator();
        $jsonData = json_decode($request->getContent());
        $validator->check($jsonData, $schema);

        if ($validator->isValid()) {
            return true;
        } else {
            foreach ($validator->getErrors() as $error) {
                // to be continued ...
                //throw new InvalidUserObjectException($error['property'] . $error['message']);
            }
        }

    }

}
 