<?php
namespace SuplaBundle\Repository;

use Assert\Assertion;
use Doctrine\ORM\EntityRepository;
use SuplaBundle\Entity\Main\SettingsString;

class SettingsStringRepository extends EntityRepository {
    public function getValue(string $name): string {
        $setting = $this->findOneBy(['name' => $name]);
        Assertion::notNull($setting, 'No setting value for ' . $name);
        return $setting->getValue();
    }

    public function hasValue(string $name): bool {
        $setting = $this->findOneBy(['name' => $name]);
        return $setting !== null;
    }

    public function clearValue(string $name): void {
        $setting = $this->findOneBy(['name' => $name]);
        if ($setting) {
            $this->_em->remove($setting);
            $this->_em->flush();
        }
    }

    public function setValue(string $name, string $value): void {
        if ($this->hasValue($name)) {
            $setting = $this->findOneBy(['name' => $name]);
        } else {
            $setting = new SettingsString($name, $value);
        }
        $setting->setValue($value);
        $this->_em->persist($setting);
        $this->_em->flush();
    }
}
