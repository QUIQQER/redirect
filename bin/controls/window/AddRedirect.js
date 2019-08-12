/**
 * @module package/quiqqer/redirect/bin/controls/window/SiteDelete
 * @author www.pcsg.de (Jan Wennrich)
 */
define('package/quiqqer/redirect/bin/controls/window/AddRedirect', [
    'qui/QUI',

    'qui/controls/windows/Confirm',
    'controls/projects/project/site/Input',
    'package/quiqqer/redirect/bin/Handler',

    'Locale',

    'css!package/quiqqer/redirect/bin/controls/window/AddRedirect.css'

], function (QUI, QUIConfirm, SiteInput, RedirectHandler, QUILocale) {
    "use strict";

    var lg = 'quiqqer/redirect';

    return new Class({

        Extends: QUIConfirm,
        Type   : 'package/quiqqer/redirect/bin/controls/window/AddRedirect',

        options: {
            maxWidth         : 600,
            maxHeight        : 350,
            title            : QUILocale.get(lg, 'window.redirect.title'),
            autoclose        : false,
            texticon         : false,
            icon             : 'fa fa-share',
            ok_button        : {
                text     : QUILocale.get(lg, 'site.delete.popup.button.ok.text'),
                textimage: 'fa fa-plus'
            },
            cancel_button    : {
                text     : QUILocale.get(lg, 'site.delete.popup.button.cancel.text'),
                textimage: 'icon-remove fa fa-remove'
            },
            sourceUrl        : false,
            sourceUrlReadOnly: false,
            projectName      : false,
            projectLanguage  : false
        },

        $SourceUrlInput: false,
        $SiteInput     : false,
        $skipChildren  : false,

        initialize: function (options) {
            this.parent(options);

            this.addEvents({
                'onSubmit': this.$onSubmit,
                'onCancel': this.$onCancel,
                'onOpen'  : this.$onOpen
            });

            this.$SiteInput = new SiteInput({
                external: true,
                name    : 'redirect-target'
            });

            if (this.getAttribute('showSkip')) {
                this.$skipChildren = true;
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
                var sourceUrl       = self.getSourceUrl(),
                    targetUrl       = self.getTargetUrl(),
                    projectName     = self.getAttribute('projectName'),
                    projectLanguage = self.getAttribute('projectLanguage'),
                    isSkipChecked   = self.isSkipChecked();

                if (!targetUrl) {
                    MessageHandler.addError(
                        QUILocale.get(lg, 'window.redirect.url.target.error')
                    );
                    return;
                }

                RedirectHandler.addRedirect(
                    sourceUrl,
                    targetUrl,
                    projectName,
                    projectLanguage
                ).then(function (result) {
                    if (!result) {
                        MessageHandler.addError(
                            QUILocale.get(lg, 'site.delete.popup.error.result')
                        );
                        return;
                    }

                    RedirectHandler.processFurtherUrls(
                        sourceUrl,
                        targetUrl,
                        isSkipChecked,
                        projectName,
                        projectLanguage
                    ).catch(console.error);

                    self.close();
                }).catch(function (error) {
                    console.error(error);
                    MessageHandler.addError(
                        QUILocale.get(lg, 'site.delete.popup.error.result')
                    );
                });
            });
        },


        /**
         * Called when the popup is canceled
         *
         * @param Win - The popup-window
         * @param value - Information about the selected site
         */
        $onCancel: function (Win, value) {
            var sourceUrl     = this.getSourceUrl(),
                isSkipChecked = this.isSkipChecked();

            RedirectHandler.processFurtherUrls(sourceUrl, null, isSkipChecked, null, null);
        },


        $onOpen: function () {
            var Content = this.getContent();

            Content.addClass('add-redirect-popup');

            new Element('label', {
                for : 'redirect-source',
                html: '<b>' + QUILocale.get(lg, 'window.redirect.url.source') + '</b>'
            }).inject(Content);

            this.$SourceUrlInput = new Element('input', {
                type : 'text',
                value: this.getAttribute('sourceUrl') ? this.getAttribute('sourceUrl') : "",
                name : 'redirect-source'
            });

            this.$SourceUrlInput.readOnly = this.getAttribute('sourceUrlReadOnly');

            this.$SourceUrlInput.inject(Content);

            new Element('label', {
                html: '<b>' + QUILocale.get(lg, 'window.redirect.url.target') + '</b>'
            }).inject(Content);

            this.$SiteInput.inject(Content);

            if (this.getAttribute('targetUrl')) {
                this.$SiteInput.$Input.value = this.getAttribute('targetUrl');
            }

            new Element('span', {
                'class': 'redirect-note',
                html   : QUILocale.get(lg, 'window.redirect.url.target.note')
            }).inject(Content);

            if (this.$skipChildren) {
                var SkipChildrenContainer = new Element('div', {
                    'class': 'redirect-children-container'
                });

                new Element('input', {
                    type   : 'checkbox',
                    checked: 1,
                    name   : 'skip-children',
                    id     : 'skip-children'
                }).inject(SkipChildrenContainer);

                new Element('label', {
                    for : "skip-children",
                    html: QUILocale.get(lg, 'window.redirect.children.skip')
                }).inject(SkipChildrenContainer);

                SkipChildrenContainer.inject(Content);
            }
        },


        /**
         * Returns if the skip checkbox is checked.
         *
         * @return {boolean}
         */
        isSkipChecked: function () {
            if (!this.$skipChildren) {
                return false;
            }

            return this.$Elm.getElement('[name="skip-children"]').checked;
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
