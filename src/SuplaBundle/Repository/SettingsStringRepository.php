<?php
namespace SuplaBundle\Repository;

use Assert\Assertion;
use Doctrine\ORM\EntityRepository;
use SuplaBundle\Entity\Main\SettingsString;
use SuplaBundle\Enums\InstanceSettings;

class SettingsStringRepository extends EntityRepository {
    public function getValue(InstanceSettings $name, ?string $default = null): string {
        $setting = $this->findOneBy(['name' => $name->value]);
        if ($setting === null && $default !== null) {
            return $default;
        }
        Assertion::notNull($setting, 'No setting value for ' . $name->value);
        return $setting->getValue();
    }

    public function getValueBoolean(InstanceSettings $name, ?bool $default = null): bool {
        $defaultString = $default === null ? null : ($default ? 'true' : 'false');
        return filter_var($this->getValue($name, $defaultString), FILTER_VALIDATE_BOOLEAN);
    }

    public function hasValue(InstanceSettings $name): bool {
        $setting = $this->findOneBy(['name' => $name->value]);
        return $setting !== null;
    }

    public function clearValue(InstanceSettings $name): void {
        $setting = $this->findOneBy(['name' => $name->value]);
        if ($setting) {
            $this->_em->remove($setting);
            $this->_em->flush();
        }
    }

    public function setValue(InstanceSettings $name, string $value): void {
        if ($this->hasValue($name)) {
            $setting = $this->findOneBy(['name' => $name->value]);
        } else {
            $setting = new SettingsString($name->value, $value);
        }
        $setting->setValue($value);
        $this->_em->persist($setting);
        $this->_em->flush();
    }

    public function setValueBoolean(InstanceSettings $name, bool $value): void {
        $this->setValue($name, $value ? 'true' : 'false');
    }
}
