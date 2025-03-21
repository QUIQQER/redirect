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
         * Adds the given redirects to the system.
         *
         * @param {Array<Object>} redirects - Array of objects with a "source" and "target" properties.
         * @param {string} projectName
         * @param {string} projectLanguage
         *
         * @returns {Promise}
         */
        addRedirects: function (redirects, projectName, projectLanguage) {
            return new Promise(function (resolve, reject) {
                QUIAjax.post('package_quiqqer_redirect_ajax_addRedirects', resolve, {
                    'package'      : pkg,
                    onError        : reject,
                    redirects      : JSON.stringify(redirects),
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
        },

        /**
         * Converts a given param URL to a SEO URL.
         * Returns a promise which resolves with the SEO url or false on error.
         *
         * (Calling this function makes an Ajax request to the server)
         *
         * @example Turns 'index.php?id=7&project=Mainproject&lang=de' into '/example'
         *
         * @param {string} paramUrl
         *
         * @returns {Promise}
         */
        getRewrittenUrl: function (paramUrl) {
            return new Promise(function (resolve, reject) {
                QUIAjax.get('package_quiqqer_redirect_ajax_getRewrittenUrl', resolve, {
                    'package': pkg,
                    onError  : reject,
                    paramUrl : paramUrl
                });
            });
        }
    });
});
