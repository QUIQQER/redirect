require([
    'qui/QUI',
    'Ajax'
], function (QUI, QUIAjax) {
    "use strict";

    /**
     * Registers a JavaScript callback which is called when a site is deleted
     */
    QUIAjax.registerGlobalJavaScriptCallback(
        'redirectShowAddRedirectDialog',
        function (Response, data) {
            require(['package/quiqqer/redirect/bin/controls/window/AddRedirect'], function (AddRedirectPopup) {
                new AddRedirectPopup({
                    sourceUrl: data.sourceUrl,
                    targetUrl: data.targetUrl,
                    showSkip : data.showSkip
                }).open();
            });
        }
    );
});