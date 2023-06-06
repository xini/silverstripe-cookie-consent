<% if $PromptCookieConsent %>
    <div role="dialog" aria-labelledby="cookieconsent-title" class="CookieConsent" id="CookieConsent" data-cookie="$CookieConsentCookieName">
        <div class="CookieConsent__hd">
            <h1 class="CookieConsent__title" id="cookieconsent-title">$SiteConfig.CookieConsentTitle</h1>
        </div>
        <div class="CookieConsent__bd">
            <div class="CookieConsent__content">
                $SiteConfig.CookieConsentContent
            </div>
        </div>
        <div class="CookieConsent__ft">
            <a class="CookieConsent__button CookieConsent__button--highlight js-cookie-consent-button" href="$AcceptAllCookiesLink" rel="nofollow">
                <%t Innoweb\\CookieConsent\\CookieConsent.AcceptAllCookies 'Accept all cookies' %>
            </a>
            <a class="CookieConsent__button js-cookie-consent-button" href="$AcceptNecessaryCookiesLink" rel="nofollow">
                <%t Innoweb\\CookieConsent\\CookieConsent.AcceptNecessaryCookies 'Accept necessary cookies' %>
            </a>
            <a class="CookieConsent__button js-cookie-info-button" href="$CookiePolicyPage.Link" rel="nofollow">
                <%t Innoweb\\CookieConsent\\CookieConsent.ManageCookies 'Manage cookie settings' %>
            </a>
        </div>
    </div>
    <script>
        const popup = document.getElementById('CookieConsent');
        if (typeof(popup) != 'undefined' && popup != null) {
            const cookieName = popup.getAttribute('data-cookie');
            if (document.cookie.match(new RegExp('(^| )' + cookieName + '=([^;]+)'))) {
                popup.style.display = 'none';
            }
            const buttons = document.querySelectorAll('.js-cookie-consent-button');
            if (buttons.length > 0) {
                Array.prototype.forEach.call(buttons, function (button) {
                    button.addEventListener('click', function (e) {
                        e.preventDefault();
                        const xhr = new XMLHttpRequest();
                        xhr.open('GET', this.href);
                        xhr.setRequestHeader('x-requested-with', 'XMLHttpRequest');
                        xhr.send();
                        popup.style.display = 'none';
                    });
                });
            }
        }
    </script>
<% end_if %>
