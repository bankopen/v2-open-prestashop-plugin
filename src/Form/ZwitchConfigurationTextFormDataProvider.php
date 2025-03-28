<?php
/**
 * @author MY-Dev |Mohamed Youssef <mydev@my-dev.pro>
 */
namespace PrestaShop\Module\Zwitch\Form;

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;

class ZwitchConfigurationTextFormDataProvider implements FormDataProviderInterface
{

    public function __construct(
        private DataConfigurationInterface $zwitchConfigurationTextDataConfiguration,
    )
    {}

    public function getData()
    {
        return $this->zwitchConfigurationTextDataConfiguration->getConfiguration();
    }

    public function setData(array $data)
    {
        return $this->zwitchConfigurationTextDataConfiguration->updateConfiguration($data);
    }
}