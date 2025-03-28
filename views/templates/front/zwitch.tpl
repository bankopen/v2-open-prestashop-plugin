{* @author MY-Dev |Mohamed Youssef <mydev@my-dev.pro> *}
<section id="zwitch-external">
    {* check sandbox status *}
    {if $sandbox}
        <script id="context" type="text/javascript" src="https://sandbox-payments.open.money/layer"></script>
    {else}
        <script id="context" type="text/javascript" src="https://payments.open.money/layer"></script>
    {/if}

    {if isset($payment_token['id'])}
        <script type="text/javascript">
            var zwitch = document.querySelector('input[data-module-name="zwitch"]').parentElement.parentElement;

            zwitch.addEventListener("click", () => {

                if (! window.zwitchCheckoutInit) {
                    Layer.checkout({
                            token: "{$payment_token['id']}",
                            accesskey: "{$access_key}",
                            theme: {
                                logo : "{$store_logo}",
                                color: "#3d9080",
                                error_color : "#ff2b2b"
                            }
                        },
                        function(response) {

                            if (response.status === "captured") {
                                {if $sandbox}
                                    window.location.href = "{$validation_href}?payment_token=" + response.payment_token_id;
                                {else}
                                    window.location.href = "{$validation_href}?payment_token=" + response.payment_token;
                                {/if}
                            } else  {
                                window.zwitchCheckoutInit = false;
                            }
                        },
                        function(err) {
                            //integration errors
                        }
                    );
                    window.zwitchCheckoutInit = true;
                }
            });
        </script>
    {else}
        {if isset($payment_token) && ! is_null($payment_token)}
            <p>{$payment_token['error']}</p>
        {else}
            <p>{l s='We are currently experiencing difficulties processing your payment. Please contact our administration team for assistance. Thank you for your understanding!' d='Modules.Zwitch.Shop'}</p>
        {/if}
    {/if}
</section>