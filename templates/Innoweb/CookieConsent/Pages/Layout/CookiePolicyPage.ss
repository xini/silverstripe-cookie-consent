<div class="typography">
    <h1>$Title</h1>
    $Content
    <h2><%t Innoweb\\CookieConsent\\Pages\\CookiePolicyPage.ManageCookies 'Manage Cookies' %></h2>
    $Form
    <% if $SiteConfig.CookieGroups %>
        <h3><%t Innoweb\\CookieConsent\\Pages\\CookiePolicyPage.CookiesUsed 'Cookies used on this website' %></h3>
        <% loop $SiteConfig.CookieGroups %>
            <h4>$Title</h4>
            $Content
            <table>
                <thead>
                <tr>
                    <th><%t Innoweb\\CookieConsent\\Model\\CookieGroup.Title 'Cookie Name' %></th>
                    <th><%t Innoweb\\CookieConsent\\Model\\CookieGroup.Provider 'Placed by' %></th>
                    <th><%t Innoweb\\CookieConsent\\Model\\CookieGroup.Purpose 'Purpose' %></th>
                    <th><%t Innoweb\\CookieConsent\\Model\\CookieGroup.Expiry 'Expiry' %></th>
                </tr>
                </thead>
                <tbody>
                <% loop $Cookies %>
                    <tr>
                        <td>$Title</td>
                        <td>$ProviderLabel</td>
                        <td>$Purpose</td>
                        <td>$Expiry</td>
                    </tr>
                <% end_loop %>
                </tbody>
            </table>
        <% end_loop %>
    <% end_if %>
    $FooterContent
</div>
