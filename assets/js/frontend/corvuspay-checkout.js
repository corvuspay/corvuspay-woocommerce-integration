jQuery(function ($) {
    "use strict";
    const o = window.wc.wcSettings;
    const l = (0, o.getPaymentMethodData)("corvuspay", {});

    function waitForElm(selector) {
        return new Promise(resolve => {
            if (document.querySelector(selector)) {
                return resolve(document.querySelector(selector));
            }

            const observer = new MutationObserver(mutations => {
                if (document.querySelector(selector)) {
                    observer.disconnect();
                    resolve(document.querySelector(selector));
                }
            });

            // If you get "parameter 1 is not of type 'Node'" error, see https://stackoverflow.com/a/77855838/492336
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        });
    }

    wp.hooks.addAction(
        'experimental__woocommerce_blocks-checkout-set-active-payment-method',
        "corvuspay", function (payment_method) {
            if (payment_method.value === "corvuspay" && l.disableAndCheckSaveOption) {
                waitForElm('.wc-block-components-payment-methods__save-card-info input').then((elm) => {
                    let saveOptionCheckbox = document.querySelector(".wc-block-components-payment-methods__save-card-info input");
                    saveOptionCheckbox.checked = true;
                    saveOptionCheckbox.disabled = true;
                });
            }
        }
    );
});

