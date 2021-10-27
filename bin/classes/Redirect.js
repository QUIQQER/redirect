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
         * @param {string} projectName
         * @param {string} projectLanguage
         *
         * @return {Promise} - Resolves with the result on success, rejects on error
         */
        addRedirect: function (sourceUrl, targetUrl, projectName, projectLanguage) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post('package_quiqqer_redirect_ajax_addRedirect', resolve, {
                    'package'      : pkg,
                    onError        : reject,
                    sourceUrl      : sourceUrl,
                    targetUrl      : targetUrl,
                    projectName    : projectName,
                    projectLanguage: projectLanguage
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
         * @param {string} projectName
         * @param {string} projectLanguage
         *
         * @return {Promise}
         */
        processFurtherUrls: function (sourceUrl, targetUrl, skipChildren, projectName, projectLanguage) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post('package_quiqqer_redirect_ajax_processFurtherUrls', resolve, {
                    'package'      : pkg,
                    onError        : reject,
                    sourceUrl      : sourceUrl,
                    targetUrl      : targetUrl,
                    skipChildren   : skipChildren ? 1 : 0,
                    projectName    : projectName,
                    projectLanguage: projectLanguage
                });
            });
        },


        /**
         * Returns all redirects for a given project and language
         *
         * @param {string} projectName
         * @param {string} projectLanguage
         *
         * @return {Promise}
         */
        getRedirects: function (projectName, projectLanguage) {
            return new Promise(function (resolve, reject) {
                QUIAjax.get('package_quiqqer_redirect_ajax_getRedirects', resolve, {
                    'package'      : pkg,
                    onError        : reject,
                    projectName    : projectName,
                    projectLanguage: projectLanguage
                });
            });
        },


        /**
         * Returns a given amount of redirects, with a given sorting and offset, for a given project and language.
         * The searchString parameter can be used to filter the results by a given string.
         *
         * The result is formatted/intended to be used with the Grid-control.
         *
         * @param {string} projectName
         * @param {string} projectLanguage
         * @param {number} page
         * @param {number} perPage
         * @param {string} searchString
         *
         * @return {Promise}
         */
        getRedirectsForGrid: function (
            projectName,
            projectLanguage,
            page,
            perPage,
            searchString
        ) {
            return new Promise(function (resolve, reject) {
                QUIAjax.get('package_quiqqer_redirect_ajax_getRedirectsForGrid', resolve, {
                    'package'      : pkg,
                    onError        : reject,
                    projectName    : projectName,
                    projectLanguage: projectLanguage,
                    page           : page,
                    perPage        : perPage,
                    searchString   : searchString
                });
            });
        },


        /**
         * Deletes the redirect with the given source URL from the given project with the given language
         *
         * @param sourceUrl
         * @param {string} projectName
         * @param {string} projectLanguage
         *
         * @return {Promise}
         */
        deleteRedirect: function (sourceUrl, projectName, projectLanguage) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post('package_quiqqer_redirect_ajax_deleteRedirect', resolve, {
                    'package'      : pkg,
                    onError        : reject,
                    sourceUrl      : sourceUrl,
                    projectName    : projectName,
                    projectLanguage: projectLanguage
                });
            });
        },


        /**
         * Deletes the redirects with the given source-URLs from the given project with the given language
         *
         * @param {string[]} sourceUrls
         * @param {string} projectName
         * @param {string} projectLanguage
         *
         * @return {Promise}
         */
        deleteRedirects: function (sourceUrls, projectName, projectLanguage) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post('package_quiqqer_redirect_ajax_deleteRedirects', resolve, {
                    'package'      : pkg,
                    onError        : reject,
                    sourceUrls     : JSON.encode(sourceUrls),
                    projectName    : projectName,
                    projectLanguage: projectLanguage
                });
            });
        }
    });
});
