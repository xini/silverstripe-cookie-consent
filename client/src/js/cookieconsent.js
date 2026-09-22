import Cookies from "js-cookie";

;(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const dataElement = document.getElementById('CookieConsentData');
        if (typeof(dataElement) != 'undefined' && dataElement != null) {
            const cookieName = dataElement.getAttribute('data-cookie');
            const cookieExpiry = dataElement.getAttribute('data-expiry');
            const additionalHostLinks = dataElement.getAttribute('data-additional-host-links');

            const popup = document.getElementById('CookieConsent');
            if (typeof (popup) != 'undefined' && popup != null) {
                if (document.cookie.match(new RegExp('(^| )' + cookieName + '=([^;]+)'))) {
                    popup.style.display = 'none';
                }
            }

            const buttons = document.querySelectorAll('.js-cookie-consent-button');
            if (buttons.length > 0) {
                Array.prototype.forEach.call(buttons, function (button) {
                    button.addEventListener('click', function (e) {
                        e.preventDefault();
                        // get cookie groups to be set
                        const newCookieGroups = this.getAttribute('data-cookie-groups');
                        // check for existing settings and merge them
                        let updatedCookieGroups = newCookieGroups;
                        const currentCookieGroups = Cookies.get(cookieName);
                        if (typeof currentCookieGroups === 'string' && currentCookieGroups.length > 0) {
                            const currentCookieGroupsArray = currentCookieGroups.split(",");
                            const newCookieGroupsArray = newCookieGroups.split(",");
                            const updatedCookieGroupsArray = [...new Set([...currentCookieGroupsArray, ...newCookieGroupsArray])];
                            updatedCookieGroups = updatedCookieGroupsArray.join(",");
                        }
                        // send cookie setting request to backend
                        const xhr = new XMLHttpRequest();
                        xhr.open('GET', this.href);
                        xhr.setRequestHeader('x-requested-with', 'XMLHttpRequest');
                        xhr.onload = function() {
                            if (xhr.status >= 200 && xhr.status < 300) {
                                // dispatch custom event once cookie loading request has returned
                                const event = new CustomEvent("updateCookieConsent", {
                                    detail: {
                                        groups: updatedCookieGroups.split(',')
                                    }
                                });
                                document.dispatchEvent(event);
                            }
                        };
                        xhr.send();
                        // set ccokies on additional hosts using helper image
                        if (typeof (additionalHostLinks) != 'undefined' && additionalHostLinks != null) {
                            additionalHostLinks.split(',').forEach(function (url) {
                                let img = document.createElement("img");
                                img.src = url + updatedCookieGroups;
                                img.width = 1;
                                img.height = 1;
                                img.alt = "";
                                img.className = "CookieConsent__host-image";
                                document.body.appendChild(img);
                            });
                        }
                        // hide popup
                        if (typeof (popup) != 'undefined' && popup != null) {
                            popup.style.display = 'none';
                        }
                    });
                });
            }
        }
    });

}());
