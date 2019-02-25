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

    'package/quiqqer/redirect/bin/Handler',

    'controls/grid/Grid',
    'Locale'

], function (QUI, QUIPanel, RedirectHandler, Grid, QUILocale) {
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
            '$onResize'
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
            // Buttons
            this.addButton({
                text     : QUILocale.get(lg, 'panel.button.redirect.add'),
                textimage: 'fa fa-plus',
                events   : {
                    onClick: this.openAddRedirectDialog
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
                onrefresh  : this.loadData,
                pagination : true
            });

            this.$Grid.addEvents({
                onDblClick: function () {
//                    this.openEntry(this.$Grid.getSelectedIndices()[0]);
                    // TODO: add onDblClick handler
                }.bind(this)
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
            require(['package/quiqqer/redirect/bin/controls/window/AddRedirect'], function (AddRedirectPopup) {
                new AddRedirectPopup({
                    showSkip: false
                }).open();
            });
        },


        deleteRedirect: function () {

        }
    });
});
