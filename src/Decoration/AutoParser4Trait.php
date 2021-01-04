<?php

namespace Acsiomatic\DeviceDetectorBundle\Decoration;

/**
 * @internal
 */
trait AutoParser4Trait
{
    public function isBot(): bool
    {
        $this->parse();

        return parent::isBot();
    }

    public function isMobile(): bool
    {
        $this->parse();

        return parent::isMobile();
    }

    public function isDesktop(): bool
    {
        $this->parse();

        return parent::isDesktop();
    }

    public function getOs(string $attr = '')
    {
        $this->parse();

        return parent::getOs($attr);
    }

    public function getClient(string $attr = '')
    {
        $this->parse();

        return parent::getClient($attr);
    }

    public function getDevice(): ?int
    {
        $this->parse();

        return parent::getDevice();
    }

    public function getBrand(): string
    {
        $this->parse();

        return parent::getBrand();
    }

    public function getBrandName(): string
    {
        $this->parse();

        return parent::getBrandName();
    }

    public function getModel(): string
    {
        $this->parse();

        return parent::getModel();
    }

    public function getBot()
    {
        $this->parse();

        return parent::getBot();
    }
}
