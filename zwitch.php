<?php
/**
 * @author MY-Dev |Mohamed Youssef <mydev@my-dev.pro>
 */
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Zwitch extends PaymentModule
{
    public function __construct()
    {
        $this->name = "zwitch";
        $this->tab = "payments_gateways";
        $this->version = "1.0.0.0";
        $this->author = "Zwitch";
        $this->author_uri = "https://www.zwitch.io";
        $this->need_instance = 0;
        $this->is_configurable = 1;
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => '8.99.99',
        ];

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans("Zwitch", [], "Modules.Zwitch.Admin");
        $this->description = $this->trans("Zwitch Payment Gateway", [], "Modules.Zwitch.Admin");

        $this->confirmUninstall = $this->trans("Are you sure you want to uninstall?", [], "Modules.Zwitch.Admin");

        if (!Configuration::get('ZWITCH_NAME')) {
            $this->warning = $this->trans('No name provided', [], 'Modules.Mymodule.Admin');
        }

    }

    public function getContent()
    {
        $route = $this->get('router')->generate('zwitch_configuration_form');
        Tools::redirectAdmin($route);
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        return parent::install()
        && $this->registerHook('PaymentOptions')
        && Configuration::updateValue('ZWITCH_NAME', 'zwitch')
        && $this->addOrderState("zwitch");
    }

    public function uninstall()
    {
        return parent::uninstall()
        && Configuration::deleteByName('ZWITCH_NAME');
    }

    public function hookPaymentOptions($params): array
    {
        $cart = $this->context->cart;

        if (false === Validate::isLoadedObject($cart) || false === $this->checkCurrency($cart)) {
            return [];
        }

        $paymentOptions = [];

        if (Configuration::get('ZWITCH_NAME')) {
            $paymentOptions[] = $this->getZwitchPaymentOptions();
        }

        return $paymentOptions;
    }

    private function checkCurrency(Cart $cart)
    {
        $currency_order = new Currency($cart->id_currency);
        /** @var array $currencies_module */
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (empty($currencies_module)) {
            return false;
        }

        foreach ($currencies_module as $currency_module) {
            if ($currency_order->id == $currency_module['id_currency']) {
                return true;
            }
        }

        return false;
    }

    private function getZwitchPaymentOptions()
    {
        $zwitch = new PaymentOption();
        $zwitch->setModuleName($this->name);
        $zwitch->setCallToActionText($this->l('ZWITCH'));
        $zwitch->setAction($this->context->link->getModuleLink($this->name, 'validation', ['option' => 'offline'], true));

        $token = $this->getZwitchPaymentToken();
        $this->context->smarty->assign($this->getSmartyVariables());

        $zwitch->setAdditionalInformation($this->context->smarty->fetch('module:zwitch/views/templates/front/zwitch.tpl'));
        $zwitch->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/logo.png'));

        return $zwitch;
    }

    private function getSmartyVariables(): array
    {
        return [
            'payment_token'     => $this->getZwitchPaymentToken(),
            'access_key'        => Configuration::get('ZWITCH_ACCESS_KEY'),
            'validation_href'   => Context::getContext()->link->getModuleLink('zwitch', 'validation'),
            'sandbox'           => $this->isSandboxEnabled(),
            'store_logo'        => Context::getContext()->link->getMediaLink(_PS_IMG_ . Configuration::get('PS_LOGO')),
        ];
    }

    private function getZwitchPaymentToken()
    {
        // Generate the timestamp in IST
        date_default_timezone_set('Asia/Kolkata');
        $timestamp = date('Y-m-d\TH:i:s'); // IST timestamp

        $endpoint = "https://api.zwitch.io/v1/pg/payment_token";

        if ($this->isSandboxEnabled()) {
            $endpoint = "https://api.zwitch.io/v1/pg/sandbox/payment_token";
        }

        $connection_options = [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($this->getOrderDetails()),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "X-O-Timestamp: {$timestamp}",
                "Authorization: Bearer " . $this->getBearerToken(),
            ],
        ];

        $connection = curl_init();
        curl_setopt_array($connection, $connection_options);
        $request = json_decode(curl_exec($connection), true);
        curl_close($connection);

        return $request;
    }

    public function getOrderDetails()
    {

        $cart = $this->context->cart;
        $currency = $this->context->currency;
        $customer = $this->context->customer;

        $address = new Address($cart->id_address_delivery);
        if ( ! $customer->is_guest && isset($customer->id_address_delivery) ) {
            $address = new Address($customer->id_address_delivery);

        }

        return [
            "amount" => number_format($cart->getOrderTotal(), 2, '.', ''),
            "currency" => $currency->iso_code,
            "mtx" => bin2hex(random_bytes(10)) . '_cart_' .  $cart->id,
            "contact_number" => $address->phone ?? $address->phone_mobile,
            "email_id" => $customer->email,
        ];
    }

    private function getBearerToken()
    {
        $access_key = Configuration::get('ZWITCH_ACCESS_KEY');
        $secret_key = Configuration::get('ZWITCH_SECRET_KEY');

        return "{$access_key}:{$secret_key}";
    }

    private function isSandboxEnabled(): bool
    {
        return Configuration::get('ZWITCH_SANDBOX_MODE');
    }

    private function addOrderState(string $moduleName)
    {
        // If the state does not exist, we create it.
        if (!Configuration::get("PS_OS_ZWITCH_PENDING")) {
            // create new order state
            $orderState = new OrderState();
            $orderState->color = "#52add7";
            $orderState->send_email = false;
            $orderState->module_name = $moduleName;
            $orderState->unremovable = true;
            $orderState->logable = false;
            $orderState->name = [];
            $languages = Language::getLanguages();

            foreach ($languages as $language) {
                $orderState->name[$language["id_lang"]] = $this->trans(
                    "ZWITCH Awaiting payment",
                    [],
                    "Modules.Zwitch.Admin"
                );
            }

            // save new order state
            $orderState->add();

            Configuration::updateValue(
                "PS_OS_ZWITCH_PENDING",
                (int) $orderState->id
            );
        }

        if (!Configuration::get("PS_OS_ZWITCH_ACCEPTED")) {
            // create new order state
            $orderState = new OrderState();
            $orderState->color = "#88D66C";
            $orderState->send_email = false;
            $orderState->module_name = $moduleName;
            $orderState->unremovable = true;
            $orderState->logable = true;
            $orderState->name = [];
            $languages = Language::getLanguages();

            foreach ($languages as $language) {
                $orderState->name[$language["id_lang"]] = $this->trans(
                    "ZWITCH Accepted Payment",
                    [],
                    "Modules.Zwitch.Admin"
                );
            }

            // save new order state
            $orderState->add();

            Configuration::updateValue(
                "PS_OS_ZWITCH_ACCEPTED",
                (int) $orderState->id
            );
        }

        if (Configuration::get("PS_OS_ZWITCH_PENDING") && Configuration::get("PS_OS_ZWITCH_ACCEPTED")) {
            return true;
        }

        return false;
    }
}