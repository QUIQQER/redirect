/**
 * Redirect Handler
 *
 * @module package/quiqqer/redirect/bin/classes/Redirect
 * @author www.pcsg.de (Jan Wennrich)
 */
define('package/quiqqer/redirect/bin/classes/Redirect', [
    'qui/classes/DOM',

    'Ajax'
], function (QUIDOM, QUIAjax) {
    "use strict";

    var pkg = "quiqqer/redirect";

    return new Class({
        Extends: QUIDOM,
        Type   : 'package/quiqqer/redirect/bin/classes/Redirect',

        /**
         * Adds a new redirect to the system.
         *
         * @param {string} sourceUrl - Source URL for the redirect
         * @param {string} targetUrl - Target of the redirect
         *
         * @return {Promise} - Resolves with the result on success, rejects on error
         */
        addRedirect: function (sourceUrl, targetUrl) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post('package_quiqqer_redirect_ajax_addRedirect', resolve, {
                    'package': pkg,
                    onError  : reject,
                    sourceUrl: sourceUrl,
                    targetUrl: targetUrl
                });
            });
        },


        /**
         * Processes the children of a site.
         * Adding redirects for each one or showing new dialogs to add custom redirects
         *
         * @param {string} sourceUrl - URL for the redirect
         * @param {string} targetUrl - Target of the redirect
         * @param {boolean} skipChildren - Skip showing a dialog for each child
         *
         * @return {Promise}
         */
        processChildren: function (sourceUrl, targetUrl, skipChildren) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post('package_quiqqer_redirect_ajax_processChildren', resolve, {
                    'package'   : pkg,
                    onError     : reject,
                    sourceUrl   : sourceUrl,
                    targetUrl   : targetUrl,
                    skipChildren: skipChildren ? 1 : 0
                });
            });
        },


        /**
         * Returns all redirects from the database
         *
         * @return {Promise}
         */
        getRedirects: function () {
            return new Promise(function (resolve, reject) {
                QUIAjax.get('package_quiqqer_redirect_ajax_getRedirects', resolve, {
                    'package': pkg,
                    onError  : reject
                });
            });
        }
    });
});
