require([
    'qui/QUI',
    'Ajax'
], function (QUI, QUIAjax) {
    "use strict";

    /**
     * Registers a JavaScript callback which is called when a site is deleted
     */
    QUIAjax.registerGlobalJavaScriptCallback(
        'redirectOnSiteDelete',
        function (Response, data) {
            require(['package/quiqqer/redirect/bin/controls/window/SiteDelete'], function (SiteDeletePopup) {
                new SiteDeletePopup({url: data.url, showSkip: data.showSkip}).open();
            });
        }
    );
});