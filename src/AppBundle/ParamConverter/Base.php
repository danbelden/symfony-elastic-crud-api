<?php

namespace AppBundle\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

abstract class Base implements ParamConverterInterface
{
    /**
     * Method to determine if this param converter supports this param
     * converter configuration
     *
     * @param ParamConverter $configuration
     * @return bool
     */
    public function supports(ParamConverter $configuration)
    {
        // Check, if option class was set in configuration
        $configClass = $configuration->getClass();
        if ($configClass === null) {
            return false;
        }

        // Return true as all tests passed
        return true;
    }

    /**
     * Method to convert the incoming request into the relevant object
     *
     * @param Request $request
     * @param ParamConverter $configuration
     * @return mixed
     */
    abstract public function apply(Request $request, ParamConverter $configuration);

    /**
     * Helper method to parse the request body and return a data array if valid
     *
     * @param Request $request
     * @return array
     * @throws BadRequestHttpException
     */
    protected function getDecodedJsonBody(Request $request)
    {
        $requestBody = $request->getContent();
        if (empty($requestBody)) {
            throw new BadRequestHttpException('Request body was empty');
        }

        $decodedRequestBody = json_decode($requestBody, true);
        if ($decodedRequestBody === false || $decodedRequestBody === null) {
            throw new BadRequestHttpException('Request body was not valid JSON');
        }

        return $decodedRequestBody;
    }

    /**
     * Helper method to convert the first form error into an invalid request
     * (400) HTTP exception to feedback to the client
     *
     * @param Form $form
     * @throws BadRequestHttpException
     */
    protected function throwFormError(Form $form)
    {
        foreach ($form->getErrors() as $error) {
            $errMsg = $error->getMessage();
            throw new BadRequestHttpException($errMsg);
        }

        foreach ($form->getIterator() as $formField) {
            foreach ($formField->getErrors() as $fieldError) {
                $fieldErrMsg = $fieldError->getMessage();
                throw new BadRequestHttpException($fieldErrMsg);
            }
        }

        $defaultErrMsg = 'Invalid submitted data!';
        throw new BadRequestHttpException($defaultErrMsg);
    }
}
