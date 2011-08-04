<table cellspacing="2" cellpadding="3" border="0" width="100%" class="form">
	<tr>
		<td class="fieldlabel">
			{$LANG.Server}
			<select name="server" class="mapserver">
				{foreach from=$onapp_servers key=id item=server}
					<option value="{$id}">{$server.name} | {$server.ipaddress}</option>
				{/foreach}
			</select>
		</td>
	</tr>
</table>

<br/>
{include file='vm_topnav.tpl'}

<form action="" method="post" id="blockops">
	<div class="tablebg">
		<table width="100%" cellspacing="1" cellpadding="3" border="0" class="datatable">
			<tr>
				{*<th></th>*}
				<th>{$LANG.ID}</th>
				<th>{$LANG.FirstName}</th>
				<th>{$LANG.LastName}</th>
				<th>{$LANG.Email}</th>
				<th>{$LANG.Status}</th>
				<th>{$LANG.Actions}</th>
			</tr>
		{foreach from=$whmcs_users item=user}
			<tr>
				<td{$bg}>{$user.id}</td>
				<td{$bg}>{$user.firstname}</td>
				<td{$bg}>{$user.lastname}</td>
				<td{$bg}>{$user.email}</td>
				<td{$bg}>{$user.status}</td>
				<td{$bg}>
					<a href="{$BASE}&whmcs_user_id={$user.client_id}&onapp_user_id={$user.onapp_user_id}&action=info">{$LANG.View}</a>
				</td>
			</tr>
		{/foreach}
		</table>
	</div>
</form>

<p align="center">
	{if $prev}
		<a href="{$BASE}&server_id={$server_id}&page={$prev}">« {$LANG.Previous} {$LANG.Page}</a>
	{/if}
		&nbsp;
	{if $next}
		<a href="{$BASE}&server_id={$server_id}&page={$next}">{$LANG.Next} {$LANG.Page} »</a>
	{/if}
</p>