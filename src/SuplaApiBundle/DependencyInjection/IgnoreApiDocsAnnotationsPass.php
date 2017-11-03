<?php

namespace SuplaApiBundle\DependencyInjection;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class IgnoreApiDocsAnnotationsPass implements CompilerPassInterface {
    public function process(ContainerBuilder $container) {
        self::ignore();
    }

    public static function ignore() {
        AnnotationReader::addGlobalIgnoredName('api');
        AnnotationReader::addGlobalIgnoredName('apiDefine');
        AnnotationReader::addGlobalIgnoredName('apiDeprecated');
        AnnotationReader::addGlobalIgnoredName('apiDescription');
        AnnotationReader::addGlobalIgnoredName('apiError');
        AnnotationReader::addGlobalIgnoredName('apiErrorExample');
        AnnotationReader::addGlobalIgnoredName('apiExample');
        AnnotationReader::addGlobalIgnoredName('apiGroup');
        AnnotationReader::addGlobalIgnoredName('apiHeader');
        AnnotationReader::addGlobalIgnoredName('apiHeaderExample');
        AnnotationReader::addGlobalIgnoredName('apiIgnore');
        AnnotationReader::addGlobalIgnoredName('apiName');
        AnnotationReader::addGlobalIgnoredName('apiParam');
        AnnotationReader::addGlobalIgnoredName('apiParamExample');
        AnnotationReader::addGlobalIgnoredName('apiPermission');
        AnnotationReader::addGlobalIgnoredName('apiPrivate');
        AnnotationReader::addGlobalIgnoredName('apiSampleRequest');
        AnnotationReader::addGlobalIgnoredName('apiSuccess');
        AnnotationReader::addGlobalIgnoredName('apiSuccessExample');
        AnnotationReader::addGlobalIgnoredName('apiUse');
        AnnotationReader::addGlobalIgnoredName('apiVersion');
    }
}
