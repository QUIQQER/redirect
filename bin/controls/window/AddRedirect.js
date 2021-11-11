/**
 * Dialog that can be used to add redirects.
 *
 * @module package/quiqqer/redirect/bin/controls/window/AddRedirect
 * @author www.pcsg.de (Jan Wennrich)
 */
define('package/quiqqer/redirect/bin/controls/window/AddRedirect', [
    'qui/QUI',

    'qui/controls/windows/Confirm',
    'controls/projects/project/site/Input',
    'package/quiqqer/redirect/bin/Handler',

    'Locale',
    'Mustache',

    URL_OPT_DIR + 'bin/quiqqer-asset/hyperlist/hyperlist/dist/hyperlist.js',

    'text!package/quiqqer/redirect/bin/controls/window/AddRedirect.html',
    'text!package/quiqqer/redirect/bin/controls/window/AddRedirectChildRow.html',

    'css!package/quiqqer/redirect/bin/controls/window/AddRedirect.css'

], function (QUI, QUIConfirm, SiteInput, RedirectHandler, QUILocale, Mustache, HyperList, template, childRowTemplate) {
    "use strict";

    var lg = 'quiqqer/redirect';

    return new Class({

        Extends: QUIConfirm,
        Type: 'package/quiqqer/redirect/bin/controls/window/AddRedirect',

        Binds: [
            'initialize',
            '$onResize',
            '$onOpen',
            '$onSubmit',
            'generateHyperlistRowForChild',
            'getChildren',
            'getSourceUrl',
            'getTargetUrl',
            'setChildren',
            'getEnabledChildren'
        ],

        options: {
            maxWidth: 600,
            maxHeight: 300,
            title: QUILocale.get(lg, 'window.redirect.title'),
            autoclose: false,
            texticon: false,
            icon: 'fa fa-share',
            ok_button: {
                text: QUILocale.get(lg, 'window.redirect.children.add'),
                textimage: 'fa fa-plus'
            },
            cancel_button: {
                text: QUILocale.get(lg, 'site.delete.popup.button.cancel.text'),
                textimage: 'icon-remove fa fa-remove'
            },
            sourceUrl: false,
            sourceUrlReadOnly: false,
            projectName: false,
            projectLanguage: false,
            content: '',
            children: []
        },

        $SourceUrlInput: false,
        $TargetSiteInput: false,
        $ApplyToAllChildrenButton: false,
        $AddRedirectsForAllChildrenInput: false,

        Hyperlist: false,
        HyperlistConfig: {},
        $HyperlistContainer: false,


        /**
         * Initializes the popup.
         *
         * @param {Object} options
         */
        initialize: function (options) {
            this.parent(options);

            this.addEvents({
                'onSubmit': this.$onSubmit,
                'onOpen': this.$onOpen,
                'onResize': this.$onResize
            });

            this.$TargetSiteInput = new SiteInput({
                external: true,
                name: 'add-redirect-target',
                project: this.getAttribute('projectName'),
                lang: this.getAttribute('projectLanguage')
            });

            // Turn the parameterized URL from the select into it's SEO/rewritten URL
            this.$TargetSiteInput.addEvent('select', (paramUrl) => {
                RedirectHandler.getRewrittenUrl(paramUrl).then((seoUrl) => {
                    if (!seoUrl) {
                        return;
                    }

                    this.$TargetSiteInput.$Input.value = seoUrl;
                });
            });

            // Add the enabled property to all children.
            // Enabled means that a redirect for the child should be added on submit.
            this.setChildren(this.getChildren().map(child => {
                child.enabled = true;
                return child;
            }));

            if (this.getChildren().length) {
                this.setAttribute('maxHeight', 600);
            }
        },

        /**
         * Called after the popup was resized.
         */
        $onResize: function() {
            if (!this.Hyperlist || !this.HyperlistConfig || !this.$HyperlistContainer) {
                return;
            }

            this.HyperlistConfig.height = this.getContent().getSize().y - 225;
            this.refreshHyperlist();
        },

        /**
         * Called when the popup is submitted
         *
         * @param Win - The popup-window
         * @param value - Information about the selected site
         */
        $onSubmit: function (Win, value) {
            var self = this;

            QUI.getMessageHandler().then(MessageHandler => {
                var sourceUrl = self.getSourceUrl(),
                    targetUrl = self.getTargetUrl(),
                    projectName = self.getAttribute('projectName'),
                    projectLanguage = self.getAttribute('projectLanguage');

                if (!targetUrl) {
                    MessageHandler.addError(
                        QUILocale.get(lg, 'window.redirect.url.target.error')
                    );
                    return;
                }

                let redirectsToAdd = [{source: sourceUrl, target: targetUrl}].concat(this.getEnabledChildren());

                // Remove (now) unnecessary enabled attribute to save bandwidth
                redirectsToAdd.forEach(redirect => delete redirect.enabled);

                RedirectHandler.addRedirects(
                    redirectsToAdd,
                    projectName,
                    projectLanguage
                ).then(result => {
                    if (!result) {
                        MessageHandler.addError(
                            QUILocale.get(lg, 'site.delete.popup.error.result')
                        );
                        return;
                    }

                    self.close();
                }).catch(error => {
                    if (error.getCode() === QUIQQER_EXCEPTION_CODE_PACKAGE_NOT_LICENSED) {
                        return;
                    }

                    console.error(error);
                    MessageHandler.addError(
                        QUILocale.get(lg, 'site.delete.popup.error.result')
                    );
                });
            });
        },

        /**
         * Called automatically when the dialog/popup opens.
         */
        $onOpen: function () {
            let Content = this.getContent(),
                children = this.getChildren();

            Content.classList.add('add-redirect-dialog-content');

            Content.innerHTML = Mustache.render(template, {
                sourceUrl: this.getAttribute('sourceUrl') || '',
                sourceUrlReadOnly: this.getAttribute('sourceUrlReadOnly'),
                showChildren: children.length,
                labelSource: QUILocale.get(lg, 'window.redirect.url.source'),
                labelTarget: QUILocale.get(lg, 'window.redirect.url.target'),
                labelEnableAll: QUILocale.get(lg, 'window.redirect.children.enableAll'),
                labelApplyParent: QUILocale.get(lg, 'window.redirect.children.applyParent'),
                labelChildAdd: QUILocale.get(lg, 'window.redirect.children.add'),
                labelChildren: QUILocale.get(lg, 'window.redirect.children')
            });

            this.$SourceUrlInput = Content.getElementById('add-redirect-parent-source');

            // Inject site input into the corresponding location (see template for exact location)
            this.$TargetSiteInput.inject(Content.getElementById('add-redirect-target-label'));
            this.$TargetSiteInput.$Input.value = this.getAttribute('targetUrl') || '';

            if (!children.length) {
                return;
            }

            this.$HyperlistContainer = Content.getElementById('add-redirect-children');

            this.HyperlistConfig = {
                height: 265,
                itemHeight: 170,
                total: children.length,

                generate: this.generateHyperlistRowForChild
            };

            this.Hyperlist = HyperList.create(this.$HyperlistContainer, this.HyperlistConfig);

            this.$AddRedirectsForAllChildrenInput = Content.getElementById('add-redirect-enable-all-children');
            this.$AddRedirectsForAllChildrenInput.onchange = (event) => {
                let isEnabled = event.target.checked;

                // Enable or disable all children redirects based on the global checkbox
                this.setChildren(this.getChildren().map((child) => {
                    child.enabled = isEnabled;
                    return child;
                }));

                this.refreshHyperlist();
            };

            this.$ApplyToAllChildrenButton = Content.getElementById('add-redirect-apply-to-all-children');
            this.$ApplyToAllChildrenButton.onclick = (event) => {
                event.preventDefault();

                this.setChildren(this.getChildren().map((child) => {
                    // The child URL contains the parent URL (e.g. parent: '/FOO', child: '/FOO/bar')
                    // This replaces the parent URL part with the new parent target in the child URL.
                    // E.g.:
                    // Parent source: /FOO, parent target: /abc
                    // Child source: '/FOO/bar' becomes the new target '/abc/bar'
                    child.target = child.source.replace(this.getSourceUrl(), this.getTargetUrl());

                    return child;
                }));

                this.refreshHyperlist();
            };

        },

        /**
         * Generates a row for the Hyperlist.
         * The parameter specifies the row to generate.
         * This method is called by Hyperlist to refresh it's content.
         *
         * @param {number} rowNumber
         *
         * @returns {ChildNode}
         */
        generateHyperlistRowForChild: function (rowNumber) {
            const child = this.getChildren()[rowNumber];

            const Template = document.createElement("template");

            Template.innerHTML = Mustache.render(childRowTemplate, {
                sourceUrl: child.source,
                sourceUrlReadOnly: this.getAttribute('sourceUrlReadOnly'),
                labelSource: QUILocale.get(lg, 'window.redirect.url.source'),
                labelTarget: QUILocale.get(lg, 'window.redirect.url.target'),
                labelEnabled: QUILocale.get(lg, 'window.redirect.children.add')
            });

            // Our row is the first child of the template element's content
            const Row = Template.content.firstChild;

            const EnabledInput = Row.querySelector('.add-redirect-child-enabled');
            EnabledInput.checked = child.enabled;
            EnabledInput.oninput = (event) => {
                // Immediately update the information in data.children
                // This has to be done, since the input may unload from the Hyperlist when scrolling
                child.enabled = event.target.checked;
            };

            const SourceInput = Row.querySelector('.add-redirect-child-source');
            SourceInput.oninput = (event) => {
                // Immediately update the information in data.children
                // This has to be done, since the input may unload from the Hyperlist when scrolling
                child.source = event.target.value;
            };

            const TargetInput = new SiteInput({
                external: true,
                project: this.getAttribute('projectName'),
                lang: this.getAttribute('projectLanguage')
            });

            // Turn the parameterized URL from the select into it's SEO/rewritten URL
            TargetInput.addEvent('select', (paramUrl) => {
                RedirectHandler.getRewrittenUrl(paramUrl).then((seoUrl) => {
                    if (!seoUrl) {
                        return;
                    }

                    TargetInput.$Input.value = seoUrl;
                    child.target = seoUrl;
                });
            });

            TargetInput.inject(
                Row.querySelector('.add-redirect-child-target-label')
            );

            TargetInput.$Input.value = child.target;

            return Row;
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
            if (!this.$TargetSiteInput) {
                return false;
            }

            return this.$TargetSiteInput.$Input.value;
        },

        /**
         * Returns all children.
         *
         * @returns {Object[]}
         */
        getChildren: function () {
            return this.getAttribute('children');
        },

        /**
         * Returns only the children where the enabled checkbox is checked.
         *
         * @returns {Object[]}
         */
        getEnabledChildren: function () {
            return this.getChildren().filter(child => child.enabled);
        },

        /**
         * Sets the children redirects.
         * The parameter should be an array containing objects with the properties: source, target and enabled
         *
         * @param {Object[]} children
         */
        setChildren: function (children) {
            this.setAttribute('children', children);
        },

        /**
         * Refreshes/Redraws the Hyperlist.
         * Should be called when the settings, size or data of the Hyperlist changes.
         */
        refreshHyperlist: function () {
            this.Hyperlist.refresh(this.$HyperlistContainer, this.HyperlistConfig);
        },
    });
});
