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

            var information = "URL:<br>" + this.getAttribute('url');

            if (this.getAttribute('showSkip')) {
                // TODO: add locale variable
                information += '<br><input type="checkbox" name="showSkip"/><label for="showSkip">Skip?</label>';
            }

            this.setAttribute('information', information);
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

                var url           = self.getAttribute('url'),
                    project       = value.project,
                    lang          = value.lang,
                    siteId        = value.ids[0],
                    isSkipChecked = self.isSkipChecked();

                if (self.getAttribute('showSkip')) {
                    RedirectHandler.processChildren(url, project, lang, siteId, isSkipChecked).then(function () {
                        RedirectHandler.addRedirect(url, project, lang, siteId).then(function (result) {
                            if (!result) {
                                MessageHandler.addError(
                                    QUILocale.get(lg, 'site.delete.popup.error.result')
                                );
                                return;
                            }

                            self.close();
                        }).catch(console.error);
                    }).catch(console.error);
                    return;
                }

                RedirectHandler.addRedirect(url, project, lang, siteId).then(function (result) {
                    if (!result) {
                        MessageHandler.addError(
                            QUILocale.get(lg, 'site.delete.popup.error.result')
                        );
                        return;
                    }

                    self.close();
                }).catch(console.error);
            });
        },


        /**
         * Returns if the skip checkbox is checked.
         *
         * @return {boolean}
         */
        isSkipChecked: function () {
            if (!this.getAttribute('showSkip')) {
                return false;
            }

            return this.getContent().querySelector('[name=showSkip]').checked
        }
    });
});
