<?php
namespace SuplaBundle\ParamConverter;

use SuplaBundle\Entity\Scene;
use SuplaBundle\Model\CurrentUserAware;

class SceneParamConverter extends AbstractBodyParamConverter {
    use CurrentUserAware;

    public function getConvertedClass(): string {
        return Scene::class;
    }

    public function convert(array $data) {
        $user = $this->getCurrentUserOrThrow();
        $scene = new Scene($user);
        $scene->setCaption($data['caption'] ?? '');
        $scene->setEnabled($data['enabled'] ?? false);
        return $scene;
    }
}
