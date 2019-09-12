<div class="typography">
    <h1>$Title</h1>
	$Content
	<% if $SiteConfig.CookieGroups %>
		<% loop $SiteConfig.CookieGroups %>
			<h2>$Title</h2>
			$Content
		<% end_loop %>
	<% end_if %>
	<p>
		<a class="CookieConsent__button CookieConsent__button--highlight js-cookie-consent-button" href="$AcceptCookiesLink" rel="nofollow">
			<%t Innoweb\\CookieConsent\\CookieConsent.Accept 'Accept' %>
		</a>
		<a class="CookieConsent__button" href="$RevokeCookiesLink" rel="nofollow">
			<%t Innoweb\\CookieConsent\\CookieConsent.Revoke 'Revoke' %>
		</a>
	</p>
	$FooterContent
</div>