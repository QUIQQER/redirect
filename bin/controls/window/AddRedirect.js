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
    'Mustache',

    'https://unpkg.com/hyperlist@1.0.0',

    'text!package/quiqqer/redirect/bin/controls/window/AddRedirect.html',

    'css!package/quiqqer/redirect/bin/controls/window/AddRedirect.css'

], function (QUI, QUIConfirm, SiteInput, RedirectHandler, QUILocale, Mustache, HyperList, template) {
    "use strict";

    var lg = 'quiqqer/redirect';

    return new Class({

        Extends: QUIConfirm,
        Type: 'package/quiqqer/redirect/bin/controls/window/AddRedirect',

        Binds: [
            'initialize',
            '$onOpen',
            '$onSubmit',
            'generateHyperlistRowForChild',
            'getChildren',
            'getSourceUrl',
            'getTargetUrl',
            'setChildren'
        ],

        options: {
            maxWidth: 600,
            maxHeight: 700,
            title: QUILocale.get(lg, 'window.redirect.title'),
            autoclose: false,
            texticon: false,
            icon: 'fa fa-share',
            ok_button: {
                text: QUILocale.get(lg, 'site.delete.popup.button.ok.text'),
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

        initialize: function (options) {
            this.parent(options);

            this.addEvents({
                'onSubmit': this.$onSubmit,
                'onOpen': this.$onOpen
            });

            this.$TargetSiteInput = new SiteInput({
                external: true,
                name: 'add-redirect-target',
                project: this.getAttribute('projectName'),
                lang: this.getAttribute('projectLanguage')
            });

            let tmpChildren = [];
            for (let i = 0; i < 10000; i++) {
                tmpChildren.push({
                    source: '/test/hallo/' + i,
                    target: ''
                });
            }
            this.setChildren(tmpChildren);

            this.setChildren(this.getChildren().map(child => {
                child.enabled = true;
                return child;
            }));
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

                    self.close();
                }).catch(function (error) {
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


        $onOpen: function () {
            let Content = this.getContent(),
                children = this.getChildren();

            Content.innerHTML = Mustache.render(template, {
                sourceUrl: this.getAttribute('sourceUrl') || '',
                sourceUrlReadOnly: this.getAttribute('sourceUrlReadOnly'),
                note: QUILocale.get(lg, 'window.redirect.url.target.note'),
                showChildren: children.length
            });

            // Inject site input into the corresponding location (see template for exact location)
            this.$TargetSiteInput.inject(Content.getElementById('add-redirect-target-label'));
            this.$TargetSiteInput.$Input.value = this.getAttribute('targetUrl') || '';

            if (!children.length) {
                return;
            }

            const ChildrenContainer = Content.getElementById('add-redirect-children');
            // ChildrenContainer.classList.add("container");

            const config = {
                height: 350,
                itemHeight: 50,
                total: children.length,

                generate: this.generateHyperlistRowForChild
            };

            const List = HyperList.create(ChildrenContainer, config);

            // window.onresize = event => {
            //     config.height = window.innerHeight;
            //     List.refresh(ChildrenContainer, config);
            // };


        },

        generateHyperlistRowForChild: function (rowNumber) {
            const Wrapper = document.createElement("tr");

            let child = this.getChildren()[rowNumber];

            const EnabledInputRow = document.createElement('td');
            const EnabledInput = document.createElement('input');
            EnabledInput.type = 'checkbox';
            EnabledInput.checked = child.enabled;
            EnabledInput.oninput = (event) => {
                // Immediately update the information in data.children
                // This has to be done, since the input may unload from the Hyperlist when scrolling
                child.enabled = event.target.checked;
            };
            EnabledInputRow.appendChild(EnabledInput);
            Wrapper.appendChild(EnabledInputRow);

            // Source URL text input
            const SourceInputRow = document.createElement('td');
            const SourceInput = document.createElement('input');
            SourceInput.readOnly = false;
            SourceInput.value = child.source;
            SourceInput.oninput = (event) => {
                // Immediately update the information in data.children
                // This has to be done, since the input may unload from the Hyperlist when scrolling
                child.source = event.target.value;
            };
            SourceInputRow.appendChild(SourceInput);
            Wrapper.appendChild(SourceInputRow);

            // Target URL text input
            const TargetInputRow = document.createElement('td');
            const TargetInput = document.createElement('input');
            TargetInput.readOnly = false;
            TargetInput.value = child.target;
            TargetInput.oninput = (event) => {
                // Immediately update the information in data.children
                // This has to be done, since the input may unload from the Hyperlist when scrolling
                child.target = event.target.value;
            };
            TargetInputRow.appendChild(TargetInput);
            Wrapper.appendChild(TargetInputRow);

            return Wrapper;
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

        getChildren: function () {
            return this.getAttribute('children');
        },

        setChildren: function (children) {
            this.setAttribute('children', children);
        }
    });
});
