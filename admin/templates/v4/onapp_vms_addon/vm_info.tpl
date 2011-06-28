<div id="tab_content">
	<table width="100%" cellspacing="2" cellpadding="3" border="0" class="form">
		<tr>
			<td colspan="4" class="fieldlabel"><b>WHMCS {$LANG.User}</b></td>
		</tr>
		<tr>
			<td class="fieldlabel">{$LANG.FirstName}</td>
			<td class="fieldarea">{$whmcs_user.firstname}</td>
			<td width="15%" class="fieldlabel">Address</td>
			<td class="fieldarea">
				{$whmcs_user.address1}
				{$whmcs_user.address2}
			</td>
		</tr>
		<tr>
			<td width="15%" class="fieldlabel">{$LANG.LastName}</td>
			<td class="fieldarea">{$whmcs_user.lastname}</td>
			<td class="fieldlabel">City</td>
			<td class="fieldarea">{$whmcs_user.city}</td>
		</tr>
		<tr>
			<td valign="top" class="fieldlabel">Company Name</td>
			<td valign="top" class="fieldarea">{$whmcs_user.companyname}</td>
			<td class="fieldlabel">State /Region</td>
			<td class="fieldarea">{$whmcs_user.state}</td>
		</tr>
		<tr>
			<td class="fieldlabel">Phone Number</td>
			<td class="fieldarea">{$whmcs_user.phonenumber}</td>
			<td class="fieldlabel">Postcode</td>
			<td class="fieldarea">{$whmcs_user.postcode}</td>
		</tr>
		<tr>
			<td class="fieldlabel">Email Address</td>
			<td class="fieldarea">{$whmcs_user.email}</td>
			<td class="fieldlabel">Country</td>
			<td class="fieldarea">{$whmcs_user.country}</td>
		</tr>
	</table>
</div>

<br/>
{include file='vm_topnav.tpl'}

<table width="100%" cellspacing="1" cellpadding="3" border="0" class="datatable">
	<tr>
		<th>Product/Service</th>
		<th>Price</th>
		<th>Billing Cycle</th>
		<th>Next Due Date</th>
		<th>{$LANG.Actions}</th>
	</tr>
	{foreach from=$whmcs_user_products item=product}
		<tr>
			<td{$bg}>{$product.domain}</td>
			<td{$bg}>{$product.amount}</td>
			<td{$bg}>{$product.billingcycle}</td>
			<td{$bg}>
				{if $product.nextduedate eq '0000-00-00'}
					-
				{else}
					{$product.nextduedate}
				{/if}
			</td>
			<td{$bg}>
				<a href="{$BASE}&whmcs_user_id={$whmcs_user.id}&product_id={$product.id}&onapp_user_id={$smarty.get.onapp_user_id}&action=map">{$LANG.Map}</a>
			</td>
		</tr>
	{/foreach}
</table>

<p align="center">
	{if $prev}
		<a href="{$BASE}&whmcs_user_id={$whmcs_user.id}&server_id={$server_id}&action=info&page={$prev}">« {$LANG.Previous} {$LANG.Page}</a>
	{/if}
		&nbsp;
	{if $next}
		<a href="{$BASE}&whmcs_user_id={$whmcs_user.id}&server_id={$server_id}&action=info&page={$next}">{$LANG.Next} {$LANG.Page} »</a>
	{/if}
</p>