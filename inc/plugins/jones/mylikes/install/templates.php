<?php

$templates[] = array(
	"title"		=> "postbit_mylikes_button",
	"template"	=> '<a href="javascript:addLike({$post[\'pid\']}, {$post[\'uid\']}, {$likes}, \'{$success}\', \'{$delete}\', \'{$lang->mylikes_likes}\', \'{$lang->mylikes_like}\', \'{$lang->mylikes_unlike}\');"><span id="like_{$post[\'pid\']}" class="mylikes_like {$liked}">{$mylikes}</span></a>
<a href="javascript:MyBB.popupWindow(\'/misc.php?action=likes&pid={$post[\'pid\']}&uid={$post[\'uid\']}\');" id="liked_{$post[\'pid\']}"><span class="mylikes_likes">({$likes}) {$lang->mylikes_likes}</span></a>'
);

$templates[] = array(
	"title"		=> "misc_mylikes",
	"template"	=> '<div class="modal">
	<div style="overflow-y: auto; max-height: 400px;">
		<table cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
		<tr>
			<td class="thead" colspan="2">
				<div><strong>{$lang->mylikes_likes}</strong></div>
			</td>
		</tr>
		{$users}
		</table>
	</div>
</div>'
);

$templates[] = array(
	"title"		=> "misc_mylikes_like",
	"template"	=> '					<tr>
						<td class="{$online_alt}" width="1%">
							<div class="buddy_avatar float_left"><img src="{$user[\'avatar\'][\'image\']}" alt="" {$user[\'avatar\'][\'width_height\']} style="margin-top: 3px;" /></div>
						</td>
						<td class="{$online_alt}">
							{$profile_link}
							<div class="buddy_action">
								<span class="smalltext">{$last_active}<br />{$send_pm}</span>
							</div>
						</td>
					</tr>'
);

$templates[] = array(
	"title"		=> "misc_mylikes_nolikes",
	"template"	=> '					<tr>
						<td class="trow1" colspan="2">{$lang->mylikes_no_likes}</td>
					</tr>'
);