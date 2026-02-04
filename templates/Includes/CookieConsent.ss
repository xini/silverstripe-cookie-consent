<div id="CookieConsentData" hidden
     data-cookie="$CookieConsentCookieName"
     data-expiry="$CookieConsentCookieExpiry"
     <% if $AdditionalDomainsCookiesEnabled %>data-additional-host-links="<% loop $AdditionalHosts %>$BaseLink<% if not $IsLast %>,<% end_if %><% end_loop %>"<% end_if %>
></div>
<% if $PromptCookieConsent %>
    <div role="dialog"
         aria-labelledby="cookieconsent-title"
         class="CookieConsent"
         id="CookieConsent"
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
                    <%t Innoweb\\CookieConsent\\CookieConsent.AcceptNecessaryCookies 'Accept necessary cookies' %>
                </a>
                <a class="CookieConsent__button js-cookie-info-button" href="$CookiePolicyPage.Link" rel="nofollow">
                    <%t Innoweb\\CookieConsent\\CookieConsent.ReviewCookiePolicy 'Review cookie policy' %>
                </a>
            <% else %>
                <a class="CookieConsent__button CookieConsent__button--highlight js-cookie-consent-button" href="$AcceptAllCookiesLink" rel="nofollow" data-cookie-groups="$AcceptAllCookiesGroups">
                    <%t Innoweb\\CookieConsent\\CookieConsent.AcceptAllCookies 'Accept all cookies' %>
                </a>
                <a class="CookieConsent__button js-cookie-consent-button" href="$AcceptNecessaryCookiesLink" rel="nofollow" data-cookie-groups="Necessary">
                    <%t Innoweb\\CookieConsent\\CookieConsent.AcceptOnlyNecessaryCookies 'Accept only necessary cookies' %>
                </a>
                <a class="CookieConsent__button js-cookie-info-button" href="$CookiePolicyPage.Link" rel="nofollow">
                    <%t Innoweb\\CookieConsent\\CookieConsent.ManageCookies 'Manage cookie settings' %>
                </a>
            <% end_if %>
        </div>
    </div>
<% end_if %>
<% if $SetAdditionalDomainsCookies %>
    <% loop $AdditionalHosts %>
        <img src="$FullLink" width="1" height="1" alt="" class="CookieConsent__host-image"/>
    <% end_loop %>
<% end_if %>
