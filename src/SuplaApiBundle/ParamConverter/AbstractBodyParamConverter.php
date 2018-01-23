<?php
namespace SuplaApiBundle\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use SuplaBundle\Entity\IODeviceChannel;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractBodyParamConverter implements ParamConverterInterface {
    final public function apply(Request $request, ParamConverter $configuration) {
        $paramName = $configuration->getName();
        if ($request->get($paramName)) {
            // skip the param conversion if it is defined in the URL
            return false;
        }
        $data = $request->request->all();
        $entity = $this->convert($data);
        $request->attributes->set($paramName, $entity);
        return true;
    }

    final public function supports(ParamConverter $configuration) {
        return $configuration->getClass() == IODeviceChannel::class;
    }

    abstract public function getConvertedClass(): string;

    abstract public function convert(array $requestData);
}
