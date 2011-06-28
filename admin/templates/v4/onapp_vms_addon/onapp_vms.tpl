<link href="{$BASE_CSS}/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{$BASE_JS}/handler.js"></script>

<script type="text/javascript">
	var LANG = {$LANG.JSMessages};
</script>

{if $msg}
	{if $msg_ok}
		{assign var='class' value='infobox'}
	{else}
		{assign var='class' value='errorbox'}
	{/if}
	<div style="font-size: 18px;" class="{$class}">{$msg_text}</div>
{/if}

{if $map}
	{include file='vm_map.tpl'}
{elseif $info}
	{include file='vm_info.tpl'}
{else}
	{include file='vm_main.tpl'}
{/if}