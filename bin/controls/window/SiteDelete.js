/**
 * @module package/quiqqer/redirect/bin/controls/window/SiteDelete
 * @author www.pcsg.de (Jan Wennrich)
 */
define('package/quiqqer/redirect/bin/controls/window/SiteDelete', [
    'qui/QUI',

    'controls/projects/Popup',
    'package/quiqqer/redirect/bin/Handler',

    'Locale'
], function (QUI, ProjectPopup, RedirectHandler, QUILocale) {
    "use strict";

    var lg = 'quiqqer/redirect';

    return new Class({

        Extends: ProjectPopup,
        Type   : 'package/quiqqer/redirect/bin/controls/window/SiteDelete',

        options: {
            title    : QUILocale.get(lg, 'site.delete.popup.title'),
            autoclose: false,
            icon     : 'fa fa-link',
            ok_button: {
                text     : QUILocale.get(lg, 'site.delete.popup.button.ok.text'),
                textimage: 'fa fa-link'
            },
            url      : false
        },

        initialize: function (options) {
            this.parent(options);

            this.addEvent('onSubmit', this.$onSubmit);
        },


        /**
         * Called when the popup is submitted
         *
         * @param Win - The popup-window
         * @param value - Information about the selected site
         */
        $onSubmit: function (Win, value) {
            var self = this;
            QUI.getMessageHandler().then(function (MessageHandler) {
                if (!value.project || !value.lang || !value.ids[0]) {
                    MessageHandler.addError(
                        QUILocale.get(lg, 'site.delete.popup.error.select.none')
                    );
                    return;
                }

                RedirectHandler.addRedirect(
                    self.getAttribute('url'),
                    value.project,
                    value.lang,
                    value.ids[0]
                ).then(function (result) {
                    if (!result) {
                        MessageHandler.addError(
                            QUILocale.get(lg, 'site.delete.popup.error.result')
                        );
                        return;
                    }

                    self.close();
                }).catch(console.error);
            });
        }
    });
});
