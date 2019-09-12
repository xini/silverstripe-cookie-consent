<div class="typography">
    <h1>$Title</h1>
	$Content
	<% if $SiteConfig.CookieGroups %>
		<% loop $SiteConfig.CookieGroups %>
			<h2>$Title</h2>
			$Content
		<% end_loop %>
	<% end_if %>
	$FooterContent
	<p>
		<% if $CookiesAccepted %>
			<a class="CookieConsent__button" href="$RevokeCookiesLink" rel="nofollow">
				<%t Innoweb\\CookieConsent\\CookieConsent.Revoke 'Revoke' %>
			</a>
		<% else %>
			<a class="CookieConsent__button CookieConsent__button--highlight js-cookie-consent-button" href="$AcceptCookiesLink" rel="nofollow">
				<%t Innoweb\\CookieConsent\\CookieConsent.Accept 'Accept' %>
			</a>
		<% end_if %>
	</p>
	
</div>