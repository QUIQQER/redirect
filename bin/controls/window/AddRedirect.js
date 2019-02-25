/**
 * @module package/quiqqer/redirect/bin/controls/window/SiteDelete
 * @author www.pcsg.de (Jan Wennrich)
 */
define('package/quiqqer/redirect/bin/controls/window/AddRedirect', [
    'qui/QUI',

    'qui/controls/windows/Confirm',
    'qui/controls/buttons/Switch',
    'controls/projects/project/site/Input',
    'package/quiqqer/redirect/bin/Handler',

    'Locale',

    'css!package/quiqqer/redirect/bin/controls/window/AddRedirect.css'

], function (QUI, QUIConfirm, QUISwitch, SiteInput, RedirectHandler, QUILocale) {
    "use strict";

    var lg = 'quiqqer/redirect';

    return new Class({

        Extends: QUIConfirm,
        Type   : 'package/quiqqer/redirect/bin/controls/window/AddRedirect',

        options: {
            title            : QUILocale.get(lg, 'site.delete.popup.title'),
            autoclose        : false,
            texticon         : false,
            icon             : 'fa fa-link',
            ok_button        : {
                text     : QUILocale.get(lg, 'site.delete.popup.button.ok.text'),
                textimage: 'fa fa-link'
            },
            sourceUrl        : false,
            sourceUrlReadOnly: false
        },

        $SourceUrlInput: false,
        $SiteInput     : false,
        $SkipChildren  : false,

        initialize: function (options) {
            this.parent(options);

            this.addEvents({
                'onSubmit': this.$onSubmit,
                'onOpen'  : this.$onOpen
            });

            this.$SiteInput = new SiteInput({
                external: true,
                name    : 'redirect-target'
            });

            if (this.getAttribute('showSkip')) {
                this.$SkipChildren = new QUISwitch({name: 'skip-children'});
            }
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
                var sourceUrl     = self.getSourceUrl(),
                    targetUrl     = self.getTargetUrl(),
                    isSkipChecked = self.isSkipChecked();

                if (!targetUrl) {
                    MessageHandler.addError(
                        QUILocale.get(lg, 'window.redirect.url.target.error')
                    );
                    return;
                }

                if (self.getAttribute('showSkip')) {
                    RedirectHandler.processChildren(sourceUrl, targetUrl, isSkipChecked).then(function () {
                        RedirectHandler.addRedirect(sourceUrl, targetUrl).then(function (result) {
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

                RedirectHandler.addRedirect(sourceUrl, targetUrl).then(function (result) {
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


        $onOpen: function () {
            var Content = this.getContent();

            Content.setStyles({
                'display'        : 'flex',
                'flex-direction' : 'column',
                'align-items'    : 'center',
                'justify-content': 'space-around'
            });

            new Element('label', {
                for : 'redirect-source',
                html: QUILocale.get(lg, 'window.redirect.url.source')
            }).inject(Content);

            this.$SourceUrlInput = new Element('input', {
                value: this.getAttribute('sourceUrl') ? this.getAttribute('sourceUrl') : "",
                name : 'redirect-source'
            });

            this.$SourceUrlInput.readOnly = this.getAttribute('sourceUrlReadOnly');

            this.$SourceUrlInput.inject(Content);

            new Element('span', {
                html: QUILocale.get(lg, 'window.redirect.url.target') + ':'
            }).inject(Content);

            this.$SiteInput.inject(Content);

            if (this.getAttribute('targetUrl')) {
                this.$SiteInput.$Input.value = this.getAttribute('targetUrl');
            }

            if (this.$SkipChildren) {
                var SkipChildrenContainer = new Element('div');

                new Element('label', {
                    for : "skip-children",
                    html: QUILocale.get(lg, 'window.redirect.children.skip')
                }).inject(SkipChildrenContainer);

                this.$SkipChildren.inject(SkipChildrenContainer);

                SkipChildrenContainer.inject(Content);
            }
        },


        /**
         * Returns if the skip checkbox is checked.
         *
         * @return {boolean}
         */
        isSkipChecked: function () {
            if (!this.$SkipChildren) {
                return false;
            }

            return this.$SkipChildren.getStatus();
        },


        /**
         * Returns the entered redirect's source URL
         *
         * @return {string}
         */
        getSourceUrl: function () {
            return this.$SourceUrlInput.value;
        },


        /**
         * Returns the URL currently in the text-input.
         * This URL is used for the redirect's target.
         * If the input is not yet present, false is returned.
         *
         * @return {string|boolean}
         */
        getTargetUrl: function () {
            if (!this.$SiteInput) {
                return false;
            }

            return this.$SiteInput.$Input.value;
        }
    });
});
