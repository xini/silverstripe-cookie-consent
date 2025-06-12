<% if $PromptCookieConsent %>
    <div role="dialog"
         aria-labelledby="cookieconsent-title"
         class="CookieConsent"
         id="CookieConsent"
         data-cookie="$CookieConsentCookieName"
         data-expiry="$CookieConsentCookieExpiry"
         <% if $AdditionalDomainsCookiesEnabled %>data-additional-host-links="<% loop $AdditionalHosts %>$BaseLink<% if not $IsLast %>,<% end_if %><% end_loop %>"<% end_if %>
     >
        <div class="CookieConsent__hd">
            <h2 class="CookieConsent__title" id="cookieconsent-title">$SiteConfig.CookieConsentTitle</h2>
        </div>
        <div class="CookieConsent__bd">
            <div class="CookieConsent__content">
                $SiteConfig.CookieConsentContent
            </div>
        </div>
        <div class="CookieConsent__ft">
            <% if $SiteUsesNecessaryCookiesOnly %>
                <a class="CookieConsent__button CookieConsent__button--highlight js-cookie-consent-button" href="$AcceptNecessaryCookiesLink" rel="nofollow" data-cookie-groups="Necessary">
                    <%t Innoweb\\CookieConsent\\CookieConsent.AcceptNecessaryCookies 'Necessary cookies only' %>
                </a>
                <a class="CookieConsent__button js-cookie-info-button" href="$CookiePolicyPage.Link" rel="nofollow">
                    <%t Innoweb\\CookieConsent\\CookieConsent.ReviewCookiePolicy 'Review cookie policy' %>
                </a>
            <% else %>
                <a class="CookieConsent__button CookieConsent__button--highlight js-cookie-consent-button" href="$AcceptAllCookiesLink" rel="nofollow" data-cookie-groups="$AcceptAllCookiesGroups">
                    <%t Innoweb\\CookieConsent\\CookieConsent.AcceptAllCookies 'Accept all cookies' %>
                </a>
                <a class="CookieConsent__button js-cookie-consent-button" href="$AcceptNecessaryCookiesLink" rel="nofollow" data-cookie-groups="Necessary">
                    <%t Innoweb\\CookieConsent\\CookieConsent.AcceptNecessaryCookies 'Necessary cookies only' %>
                </a>
                <a class="CookieConsent__button js-cookie-info-button" href="$CookiePolicyPage.Link" rel="nofollow">
                    <%t Innoweb\\CookieConsent\\CookieConsent.ManageCookies 'Manage cookie settings' %>
                </a>
            <% end_if %>
        </div>
    </div>
    <script>
        const popup = document.getElementById('CookieConsent');
        if (typeof(popup) != 'undefined' && popup != null) {
            const cookieName = popup.getAttribute('data-cookie');
            const cookieExpiry = popup.getAttribute('data-expiry');
            const additionalHostLinks = popup.getAttribute('data-additional-host-links');
            if (document.cookie.match(new RegExp('(^| )' + cookieName + '=([^;]+)'))) {
                popup.style.display = 'none';
            }
            document.addEventListener('DOMContentLoaded', function() {
                const buttons = document.querySelectorAll('.js-cookie-consent-button');
                if (buttons.length > 0) {
                    Array.prototype.forEach.call(buttons, function (button) {
                        button.addEventListener('click', function (e) {
                            e.preventDefault();
                            const xhr = new XMLHttpRequest();
                            xhr.open('GET', this.href);
                            xhr.setRequestHeader('x-requested-with', 'XMLHttpRequest');
                            xhr.send();
                            const d = new Date;
                            d.setTime(d.getTime() + 24*60*60*1000*cookieExpiry);
                            const cookieGroups = this.getAttribute('data-cookie-groups');
                            document.cookie = cookieName + "=" + cookieGroups + ";path=/;expires=" + d.toGMTString();
                            if (typeof(additionalHostLinks) != 'undefined' && additionalHostLinks != null) {
                                additionalHostLinks.split(',').forEach(function (url) {
                                    let img = document.createElement("img");
                                    img.src = url + cookieGroups;
                                    img.width = 1;
                                    img.height = 1;
                                    img.alt = "";
                                    img.className = "CookieConsent__host-image";
                                    document.body.appendChild(img);
                                });
                            }
                            let event = new CustomEvent("updateCookieConsent");
                            document.dispatchEvent(event);
                            popup.style.display = 'none';
                        });
                    });
                }
            });
        }
    </script>
<% end_if %>
<% if $SetAdditionalDomainsCookies %>
    <% loop $AdditionalHosts %>
        <img src="$FullLink" width="1" height="1" alt="" class="CookieConsent__host-image"/>
    <% end_loop %>
<% end_if %>
