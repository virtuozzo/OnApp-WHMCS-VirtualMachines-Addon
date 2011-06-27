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
	<br/>

	<table width="100%" cellspacing="2" cellpadding="3" border="0" class="form">
		<tr>
			<td colspan="4" class="fieldlabel"><b>Product/Service</b></td>
		</tr>
		<tr>
			<td class="fieldlabel">Name</td>
			<td class="fieldarea">{$product.domain}</td>
			<td width="15%" class="fieldlabel">Price</td>
			<td class="fieldarea">{$product.amount}</td>
		</tr>
		<tr>
			<td width="15%" class="fieldlabel">Billing Cycle</td>
			<td class="fieldarea">{$product.billingcycle}</td>
			<td class="fieldlabel">Next Due Date</td>
			<td class="fieldarea">{$product.nextduedate}</td>
		</tr>
	</table>
</div>

<br/>
{include file='topnav.tpl'}

<table width="100%" cellspacing="1" cellpadding="3" border="0" class="datatable">
	<tr>
		<th>Label</th>
		<th>IP Addresses</th>
		<th>Disk Size</th>
		<th>RAM</th>
		<th>Status</th>
		<th>{$LANG.Actions}</th>
	</tr>
	{foreach from=$onapp_vms item=vm}
		<tr>
			<td{$bg}>{$vm._label}</td>
			<td{$bg}>{$vm._ip_addresses}</td>
			<td{$bg}>{$vm._total_disk_size}</td>
			<td{$bg}>{$vm._memory}</td>
			<td{$bg}>
				{if $vm._locked eq "true" || $vm._built eq "false" }
    				Pending
				{elseif $vm._booted eq "true"}
       				ON
				{elseif $vm._booted eq "false"}
					OFF
				{/if}
			</td>
			<td{$bg}>
				{if $vm.resource_errors eq false}
					<a href="{$BASE}&service_id={$product.id}&vm_id={$vm._id}&whmcs_user_id={$whmcs_user.id}&action=domap">{$LANG.Map}</a>
				{else}
					Resources are not identical:
					<table>
						<tr>
							<td width="50%">OnApp</td>
							<td>WHMCS</td>
						</tr>
					{foreach from=$vm.resource_errors key=K item=i}
						<tr>
							<td colspan="2">{$K}</td>
						</tr>
					{foreach from=$i key=k item=v}
						<tr>
							<td>{$k}</td>
							<td>{$v}</td>
						</tr>
					{/foreach}
					{/foreach}
					</table>
				{/if}
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