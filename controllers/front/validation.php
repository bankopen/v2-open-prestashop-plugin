<?php
/**
 * @author MY-Dev |Mohamed Youssef <mydev@my-dev.pro>
 */
class ZwitchValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        // In the template, we need the vars paymentId & paymentStatus to be defined
        $cart = $this->context->cart;

        if (
            $cart->id_customer == 0 ||
            $cart->id_address_delivery == 0 ||
            $cart->id_address_invoice == 0 ||
            !$this->module->active
        ) {
            $this->redirectToCart();
        }

        $customer = new Customer($this->context->cart->id_customer);

        if (false === Validate::isLoadedObject($customer)) {
            $this->redirectToCart();
        }

        if ( ! Tools::getIsset('payment_token') ) {
            $this->redirectToCart();
        }

        // use check payment status API to validate payment status
        $payment_status = $this->confirmPaymentStatus(Tools::getValue('payment_token'));

        if (!isset($payment_status['status']) || $payment_status['status'] != 'captured') {
            Tools::redirect($this->context->link->getPageLink(
                'order',
                true,
                (int) $this->context->language->id,
                [
                    'step' => 1,
                ]
            ));
        }

        $this->module->validateOrder(
            (int) $this->context->cart->id,
            (int) Configuration::get("PS_OS_ZWITCH_ACCEPTED"),
            (float) $payment_status['amount'],
            $this->module->displayName,
            null,
            [
                'transaction_id' => $payment_status['id'], // Should be retrieved from your Payment response
            ],
            (int) $this->context->currency->id,
            false,
            $customer->secure_key
        );

        Tools::redirect($this->context->link->getPageLink(
            'order-confirmation',
            true,
            (int) $this->context->language->id,
            [
                'id_cart' => (int) $this->context->cart->id,
                'id_module' => (int) $this->module->id,
                'id_order' => (int) $this->module->currentOrder,
                'key' => $customer->secure_key,
            ]
        ));
    }

    private function confirmPaymentStatus(string $payment_token_id)
    {
        // Generate the timestamp in IST
        date_default_timezone_set('Asia/Kolkata');
        $timestamp = date('Y-m-d\TH:i:s'); // IST timestamp

        $endpoint = "https://api.zwitch.io/v1/pg/payment_token/{$payment_token_id}/payment";

        if ($this->isSandboxEnabled()) {
            $endpoint = "https://api.zwitch.io/v1/pg/sandbox/payment_token/{$payment_token_id}/payment";
        }

        $connection_options = [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "GET",
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

    private function getBearerToken()
    {
        $access_key = Configuration::get('ZWITCH_ACCESS_KEY');
        $secret_key = Configuration::get('ZWITCH_SECRET_KEY');

        return "{$access_key}:{$secret_key}";
    }

    private function redirectToCart()
    {
        Tools::redirect($this->context->link->getPageLink(
            'order',
            true,
            (int) $this->context->language->id,
            [
                'step' => 1,
            ]
        ));
    }

    private function isSandboxEnabled(): bool
    {
        return Configuration::get('ZWITCH_SANDBOX_MODE');
    }
}