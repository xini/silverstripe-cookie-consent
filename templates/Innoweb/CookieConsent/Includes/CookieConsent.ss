<div role="dialog" aria-labelledby="cookieconsent-title" class="CookieConsent" id="CookieConsent">
	<div class="CookieConsent__hd">
		<h1 class="CookieConsent__title" id="cookieconsent-title">$SiteConfig.CookieConsentTitle</h1>
	</div>
	<div class="CookieConsent__bd">
		<div class="CookieConsent__content">
			$SiteConfig.CookieConsentContent
		</div>
	</div>
	<div class="CookieConsent__ft">
		<a class="CookieConsent__button CookieConsent__button--highlight js-cookie-consent-button" href="$AcceptCookiesLink" rel="nofollow">
			<%t Innoweb\\CookieConsent\\CookieConsent.Accept 'Accept' %>
		</a>
		<a class="CookieConsent__button js-cookie-info-button" href="$CookiePolicyPage.Link" rel="nofollow">
			<%t Innoweb\\CookieConsent\\CookieConsent.LearnMore 'Learn more' %>
		</a>
	</div>
</div>
