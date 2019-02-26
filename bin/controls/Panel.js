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
    'qui/controls/buttons/Separator',

    'package/quiqqer/redirect/bin/Handler',

    'controls/grid/Grid',
    'Locale'

], function (QUI, QUIPanel, QUIButtonSeparator, RedirectHandler, Grid, QUILocale) {
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
            '$onResize',
            'deleteRedirect',
            'openAddRedirectDialog',
            'editRedirect'
        ],

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

                onrefresh : this.loadData,
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
            this.loadData();
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
         * Load the grid data
         */
        loadData: function () {
            if (!this.$Grid) {
                return;
            }

            this.Loader.show();

            var self = this;

            RedirectHandler.getRedirects().then(function (result) {
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
                new AddRedirectPopup({
                    showSkip: false,
                    events  : {
                        onClose: self.loadData
                    }
                }).open();
            });
        },


        /**
         * Delete the (in the grid) selected redirects
         */
        deleteRedirect: function () {
            var sourceUrls = this.$Grid.getSelectedData().map(function (data) {
                return data.source_url;
            });

            RedirectHandler.deleteRedirects(sourceUrls).then(this.loadData);
        },


        editRedirect: function () {
            var self = this;
            require(['package/quiqqer/redirect/bin/controls/window/AddRedirect'], function (AddRedirectPopup) {
                new AddRedirectPopup({
                    showSkip         : false,
                    sourceUrlReadOnly: true,
                    sourceUrl        : self.$Grid.getSelectedData()[0].source_url,
                    targetUrl        : self.$Grid.getSelectedData()[0].target_url,
                    events           : {
                        onClose: self.loadData
                    }
                }).open();
            });
        }
    });
});
