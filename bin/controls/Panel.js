/**
 * Redirect panel
 *
 * @module package/quiqqer/redirect/bin/controls/Panel
 * @author www.pcsg.de (Jan Wennrich)
 *
 */
define('package/quiqqer/redirect/bin/controls/Panel', [

    'qui/QUI',
    'qui/controls/desktop/Panel',
    'qui/controls/buttons/Select',
    'qui/controls/buttons/Separator',
    'qui/controls/windows/Confirm',

    'package/quiqqer/redirect/bin/Handler',

    'controls/grid/Grid',
    'Locale'

], function (QUI, QUIPanel, QUISelect, QUIButtonSeparator, QUIConfirm, RedirectHandler, Grid, QUILocale) {
    "use strict";

    var lg = 'quiqqer/redirect';

    return new Class({

        Extends: QUIPanel,
        Type   : 'package/quiqqer/redirect/bin/controls/Panel',

        Binds: [
            'loadData',
            'openSearch',
            'openClear',
            '$onCreate',
            '$onInject',
            '$onResize',
            'deleteRedirect',
            'openAddRedirectDialog',
            'editRedirect',
            'getSelectedProjectData'
        ],

        $ProjectSelect: null,

        initialize: function (options) {
            this.setAttributes({
                icon : 'fa fa-share',
                title: QUILocale.get('quiqqer/redirect', 'panel.title')
            });

            this.parent(options);

            this.$Grid = null;

            this.addEvents({
                onCreate: this.$onCreate,
                onResize: this.$onResize,
                onInject: this.$onInject
            });
        },

        /**
         * event : on create
         */
        $onCreate: function () {
            var self = this;

            // Buttons
            this.addButton({
                name     : 'redirect-add',
                text     : QUILocale.get(lg, 'panel.button.redirect.add'),
                textimage: 'fa fa-plus',
                events   : {
                    onClick: this.openAddRedirectDialog
                }
            });

            this.addButton(new QUIButtonSeparator());

            this.addButton({
                name     : 'redirect-edit',
                text     : QUILocale.get(lg, 'panel.button.redirect.edit'),
                textimage: 'fa fa-pencil',
                disabled : true,
                events   : {
                    onClick: this.editRedirect
                }
            });

            this.addButton({
                name     : 'redirect-delete',
                text     : QUILocale.get(lg, 'panel.button.redirect.delete'),
                textimage: 'fa fa-trash',
                disabled : true,
                events   : {
                    onClick: this.deleteRedirect
                },
                styles   : {
                    float: 'right'
                }
            });

            // Grid
            var Container = new Element('div').inject(
                this.getContent()
            );

            this.$Grid = new Grid(Container, {
                columnModel: [{
                    header   : QUILocale.get(lg, 'window.redirect.url.source'),
                    dataIndex: 'source_url',
                    dataType : 'string',
                    width    : 500
                }, {
                    header   : QUILocale.get(lg, 'window.redirect.url.target'),
                    dataIndex: 'target_url',
                    dataType : 'string',
                    width    : 500
                }],

                onrefresh : self.loadData,
                pagination: true,

                multipleSelection: true
            });

            this.$Grid.addEvents({
                onClick   : function () {
                    self.getButtons('redirect-edit').disable();

                    if (self.$Grid.getSelectedIndices().length === 1) {
                        self.getButtons('redirect-edit').enable();
                    }

                    self.getButtons('redirect-delete').enable();
                },
                onDblClick: self.editRedirect
            });
        },


        /**
         * event : on inject
         */
        $onInject: function () {
            var self = this;
            require(['controls/projects/Select'], function (ProjectSelect) {
                self.$ProjectSelect = new ProjectSelect({
                    emptyselect: false,
                    events     : {
                        onChange: self.loadData
                    }
                });

                self.addButton(self.$ProjectSelect);
            });
        },


        /**
         * event : on resize
         */
        $onResize: function () {
            if (!this.$Grid) {
                return;
            }

            var Content = this.getContent();

            if (!Content) {
                return;
            }

            var size = Content.getSize();

            this.$Grid.setHeight(size.y - 40);

            this.$Grid.setWidth(size.x - 40);
        },

        /**
         * Load the grid data for the currently selected project-name and -language
         */
        loadData: function () {
            if (!this.$Grid) {
                return;
            }

            this.Loader.show();

            var self = this;

            var selectedProjectData = self.getSelectedProjectData(),
                projectName         = selectedProjectData[0],
                projectLanguage     = selectedProjectData[1];

            RedirectHandler.getRedirects(projectName, projectLanguage).then(function (result) {
                self.$Grid.setData({data: result});
                self.Loader.hide();
            });
        },

        /**
         * Opens the add-redirect-dialog-popup
         */
        openAddRedirectDialog: function () {
            var self = this;
            require(['package/quiqqer/redirect/bin/controls/window/AddRedirect'], function (AddRedirectPopup) {
                var selectedProjectData = self.getSelectedProjectData();

                if (!selectedProjectData) {
                    QUI.getMessageHandler().then(function (MessageHandler) {
                        MessageHandler.addAttention(
                            QUILocale.get(lg, 'panel.error.projectData.missing.message'),
                            self.$ProjectSelect.getElm()
                        );
                    });
                    return;
                }

                new AddRedirectPopup({
                    showSkip       : false,
                    projectName    : selectedProjectData[0],
                    projectLanguage: selectedProjectData[1],
                    events         : {
                        onClose: self.loadData
                    }
                }).open();
            });
        },


        /**
         * Delete the (in the grid) selected redirects
         */
        deleteRedirect: function () {
            var self = this;

            var sourceUrls = this.$Grid.getSelectedData().map(function (data) {
                return data.source_url;
            });


            new QUIConfirm({
                icon     : 'fa fa-trash',
                title    : QUILocale.get(lg, 'window.redirect.delete.title'),
                maxHeight: 300,
                maxWidth : 450,
                ok_button: {
                    text     : QUILocale.get(lg, 'window.redirect.delete.button.ok.text'),
                    textimage: 'fa fa-trash'
                },
                events   : {
                    onOpen: function (Win) {
                        Win.getContent().set('html', QUILocale.get(
                            lg,
                            'window.redirect.delete.text', {urls: sourceUrls.toString()})
                        );
                    },

                    onSubmit: function () {
                        var selectedProjectData = self.getSelectedProjectData();
                        RedirectHandler.deleteRedirects(
                            sourceUrls,
                            selectedProjectData[0],
                            selectedProjectData[1]
                        ).then(self.loadData);
                    }
                }
            }).open();
        },


        editRedirect: function () {
            var self = this;
            require(['package/quiqqer/redirect/bin/controls/window/AddRedirect'], function (AddRedirectPopup) {
                var selectedProjectData = self.getSelectedProjectData();

                new AddRedirectPopup({
                    showSkip         : false,
                    sourceUrlReadOnly: true,
                    sourceUrl        : self.$Grid.getSelectedData()[0].source_url,
                    targetUrl        : self.$Grid.getSelectedData()[0].target_url,
                    projectName      : selectedProjectData[0],
                    projectLanguage  : selectedProjectData[1],
                    events           : {
                        onClose: self.loadData
                    }
                }).open();
            });
        },


        /**
         * Returns an array ofdata about the selected project.
         * First entry in the array is the project's name, the second entry is the project's language
         *
         * @return {string[]}
         */
        getSelectedProjectData: function () {
            var value = this.$ProjectSelect.getValue();

            if (!value) {
                return null;
            }

            return value.split(',');
        }
    });
});
