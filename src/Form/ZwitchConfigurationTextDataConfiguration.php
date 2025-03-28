<?php
/**
 * @author MY-Dev |Mohamed Youssef <mydev@my-dev.pro>
 */
namespace PrestaShop\Module\Zwitch\Form;

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;

final class ZwitchConfigurationTextDataConfiguration implements DataConfigurationInterface
{
    public const ZWITCH_ACCESS_KEY = 'ZWITCH_ACCESS_KEY';
    public const ZWITCH_SECRET_KEY = 'ZWITCH_SECRET_KEY';
    public const ZWITCH_SANDBOX_MODE = 'ZWITCH_SANDBOX_MODE';

    public function __construct(
        private ConfigurationInterface $configuration,
    )
    {}

    public function getConfiguration(): array
    {

        $data = [];
        $data['access_key'] = $this->configuration->get(static::ZWITCH_ACCESS_KEY);
        $data['secret_key'] = $this->configuration->get(static::ZWITCH_SECRET_KEY);
        $data['sandbox_mode'] = $this->configuration->get(static::ZWITCH_SANDBOX_MODE);

        return $data;
    }

    public function updateConfiguration(array $configuration): array
    {

        $error = [];

        if ($this->validateConfiguration($configuration)) {

            $this->configuration->set(static::ZWITCH_ACCESS_KEY, $configuration['access_key']);
            $this->configuration->set(static::ZWITCH_SECRET_KEY, $configuration['secret_key']);
            $this->configuration->set(static::ZWITCH_SANDBOX_MODE, $configuration['sandbox_mode']);
        } else {
            $error['warning'] = "Configuration fields should be filled!";
        }

        return $error;
    }

    public function validateConfiguration(array $configuration): bool
    {

        if (empty($configuration['access_key'])) {
            return false;
        }

        if (empty($configuration['secret_key'])) {
            return false;
        }

        return true;
    }
}