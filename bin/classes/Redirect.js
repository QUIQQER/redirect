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
         * @param {string} url - URL for the redirect
         * @param {string} targetProjectName - Project of the redirect's target
         * @param {string} targetProjectLanguage - Project language of the redirect's target
         * @param {number} targetSiteId - Site ID of the redirect's target
         * @param {boolean} skipChildren - Skip showing a dialog for each child
         *
         * @return {Promise} - Resolves with the result on success, rejects on error
         */
        addRedirect: function (url, targetProjectName, targetProjectLanguage, targetSiteId, skipChildren) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post('package_quiqqer_redirect_ajax_addRedirect', resolve, {
                    'package'            : pkg,
                    onError              : reject,
                    sourceUrl            : url,
                    targetProjectName    : targetProjectName,
                    targetProjectLanguage: targetProjectLanguage,
                    targetSiteId         : targetSiteId,
                    skipChildren         : skipChildren
                });
            });
        },


        /**
         * Processes the children of a site.
         * Adding redirects for each one or showing new dialogs to add custom redirects
         *
         * @param {string} url - URL for the redirect
         * @param {string} targetProjectName - Project of the redirect's target
         * @param {string} targetProjectLanguage - Project language of the redirect's target
         * @param {number} targetSiteId - Site ID of the redirect's target
         * @param {boolean} skipChildren - Skip showing a dialog for each child
         *
         * @return {Promise}
         */
        processChildren: function (url, targetProjectName, targetProjectLanguage, targetSiteId, skipChildren) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post('package_quiqqer_redirect_ajax_processChildren', resolve, {
                    'package'            : pkg,
                    onError              : reject,
                    sourceUrl            : url,
                    targetProjectName    : targetProjectName,
                    targetProjectLanguage: targetProjectLanguage,
                    targetSiteId         : targetSiteId,
                    skipChildren         : skipChildren ? 1 : 0
                });
            });
        }
    });
});
