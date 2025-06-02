<?php

namespace Mantasruigys3000\SimpleSwagger\Data;

use Mantasruigys3000\SimpleSwagger\Helpers\ReferenceHelper;

/**
 * This class is responsible for generating the 'content' of the request body along with the content types
 * This class does not generate the property or schema data
 */
class RequestContent
{
    private array $content;

    public function __construct(protected string $requestClass)
    {
        $this->constructContentTypes();
    }

    private function constructContentTypes()
    {
        $bodies = $this->requestClass::requestBodies();

        /**
         * @var RequestBody[] $bodies
         */
        foreach ($bodies as $body)
        {
            $ref = ReferenceHelper::getRequestBodyReference($body,$this->requestClass);
            $exampleRef = ReferenceHelper::getRequestBodyExampleReference($body,$this->requestClass);
            $this->content['content'][$body->schemaFactory->getContentType()]['schema']['oneOf'][] = ['$ref' => $ref];
            $this->content['content'][$body->schemaFactory->getContentType()]['examples'][] = ['$ref' => $exampleRef];
        }
    }

    public function toArray() : array
    {
        return $this->content;
    }


}