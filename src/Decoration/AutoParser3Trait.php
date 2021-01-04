<?php

namespace Acsiomatic\DeviceDetectorBundle\Decoration;

/**
 * @internal
 */
trait AutoParser3Trait
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

    public function getOs($attr = '')
    {
        $this->parse();

        return parent::getOs($attr);
    }

    public function getClient($attr = '')
    {
        $this->parse();

        return parent::getClient($attr);
    }

    public function getDevice()
    {
        $this->parse();

        return parent::getDevice();
    }

    public function getBrand()
    {
        $this->parse();

        return parent::getBrand();
    }

    public function getBrandName()
    {
        $this->parse();

        return parent::getBrandName();
    }

    public function getModel()
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
