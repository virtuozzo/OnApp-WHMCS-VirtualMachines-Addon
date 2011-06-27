<form action="{$BASE}" method="get">
	<table width="100%" cellspacing="0" cellpadding="3" border="0">
		<tr>
			<td width="50%" align="left">
			{$total} {$LANG.Found}
			{if $pages}
				, {$LANG.Page} {$current} {$LANG.Of} {$pages}
			{/if}
			</td>
			<td width="50%" align="right">
				{if $pages}
					{$LANG.Jump} {$LANG.Page}:
					<select id="page" name="page">
					{section name=foo start=1 loop=$pages+1}
						<option{if $smarty.section.foo.index == $current} selected="selected" {/if}
																		  value="{$smarty.section.foo.index}">{$smarty.section.foo.index}</option>
					{/section}
					</select>
				{/if}
			</td>
		</tr>
	</table>
</form>