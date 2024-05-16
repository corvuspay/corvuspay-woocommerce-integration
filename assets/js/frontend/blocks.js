(() => {
    "use strict";
    const react = window.React, t = window.wc.wcBlocksRegistry, n = window.wp.i18n, o = window.wc.wcSettings,
        c = window.wp.htmlEntities;
    var i;
    const l = (0, o.getPaymentMethodData)("corvuspay", {}),
        a = (0, n.__)("Corvuspay", "woocommerce"),
        ariaLabel = (0, c.decodeEntities)((null == l ? void 0 : l.title) || "") || a,
        d = () => (0, c.decodeEntities)(l.description || ""),
        CorvusPay_Gateway = {
            name: "corvuspay",
            label: (0, react.createElement)((t => {
                const {PaymentMethodLabel: n} = t.components;
                return (0, react.createElement)(n, {text: ariaLabel})
            }), null),
            content: (0, react.createElement)("span", {
                dangerouslySetInnerHTML: {
                    __html: d(),
                },
            }),
            edit: (0, react.createElement)("span", {
                dangerouslySetInnerHTML: {
                    __html: d(),
                },
            }),
            canMakePayment: () => !0,
            ariaLabel: ariaLabel,
            supports: {
                features: null !== (i = null == l ? void 0 : l.supports) && void 0 !== i ? i : [],
                showSaveOption: l.showSaveOption === "yes",
                showSavedCards: l.showSavedCards === "yes"
            }
        };
    (0, t.registerPaymentMethod)(CorvusPay_Gateway)
})();