<div id="$HolderID" class="field<% if extraClass %> $extraClass<% end_if %>">
    <div class="field_wrapper">
        $Field
        <label class="right" for="$ID">$Title<% if $RightTitle %> $RightTitle<% end_if %></label>
        <% if $Content %><span class="content">$Content</span><% end_if %>
    </div>
    <% if $Description %><span class="description">$Description</span><% end_if %>
    <% if $Message %><span class="message $MessageType">$Message</span><% end_if %>
</div>
