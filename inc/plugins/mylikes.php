<?php
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("postbit", "mylikes_postbit");
$plugins->add_hook("misc_start", "mylikes_popup");

function mylikes_info()
{
	return array(
		"name"			=> "MyLikes",
		"description"	=> "Adds a very simple like system using MyBB's reputation system",
		"website"		=> "http://jonesboard.de/",
		"author"		=> "Jones",
		"authorsite"	=> "http://jonesboard.de/",
		"version"		=> "1.0",
		"compatibility" => "18*"
	);
}

function mylikes_install()
{
	global $db;

	$template = '<a href="javascript:addLike({$post[\'pid\']},{$post[\'uid\']}, {$likes});" title="Like" id="like_{$post[\'pid\']}"><span style="background-image: url(../../../images/valid.png);">Like</span></a>
<a href="javascript:MyBB.popupWindow(\'/misc.php?action=likes&pid={$post[\'pid\']}&uid={$post[\'uid\']}\');" id="liked_{$post[\'pid\']}"><span style="padding-left: 2px; background-image: url();">({$likes}) Like(s)</span></a>';
	$templatearray = array(
		"title" => "postbit_mylikes_button",
		"template" => $db->escape_string($template),
		"sid" => "-2",
	);
	$db->insert_query("templates", $templatearray);

	$template = '<div class="modal">
	<div style="overflow-y: auto; max-height: 400px;">
		<table cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
		<tr>
			<td class="thead" colspan="2">
				<div><strong>Likes</strong></div>
			</td>
		</tr>
		{$users}
		</table>
	</div>
</div>';
	$templatearray = array(
		"title" => "misc_mylikes",
		"template" => $db->escape_string($template),
		"sid" => "-2",
	);
	$db->insert_query("templates", $templatearray);

	$template = '					<tr>
						<td class="{$online_alt}" width="1%">
							<div class="buddy_avatar float_left"><img src="{$user[\'avatar\'][\'image\']}" alt="" {$user[\'avatar\'][\'width_height\']} style="margin-top: 3px;" /></div>
						</td>
						<td class="{$online_alt}">
							{$profile_link}
							<div class="buddy_action">
								<span class="smalltext">{$last_active}<br />{$send_pm}</span>
							</div>
						</td>
					</tr>';
	$templatearray = array(
		"title" => "misc_mylikes_like",
		"template" => $db->escape_string($template),
		"sid" => "-2",
	);
	$db->insert_query("templates", $templatearray);

	$template = '					<tr>
						<td class="trow1" colspan="2">This post wasn\'t liked by anyone :(</td>
					</tr>';
	$templatearray = array(
		"title" => "misc_mylikes_nolikes",
		"template" => $db->escape_string($template),
		"sid" => "-2",
	);
	$db->insert_query("templates", $templatearray);

}

function mylikes_is_installed()
{
	global $db;

	$query = $db->simple_select("templates", "*", "title='postbit_mylikes_button'");
	return ($db->num_rows($query) > 0);
}

function mylikes_uninstall()
{
	global $db;
	$db->delete_query("templates", "title='postbit_mylikes_button'");
	$db->delete_query("templates", "title='misc_mylikes'");
	$db->delete_query("templates", "title='misc_mylikes_like'");
	$db->delete_query("templates", "title='misc_mylikes_nolikes'");
}

function mylikes_activate()
{
	require_once MYBB_ROOT."inc/adminfunctions_templates.php";
	find_replace_templatesets("postbit", "#".preg_quote('{$post[\'button_rep\']}')."#i", '{$post[\'button_like\']}');
	find_replace_templatesets("headerinclude",
		"#".preg_quote('<script type="text/javascript" src="{$mybb->asset_url}/jscripts/general.js?ver=1800"></script>')."#i",
		'<script type="text/javascript" src="{$mybb->asset_url}/jscripts/general.js?ver=1800"></script>
<script type="text/javascript" src="{$mybb->asset_url}/jscripts/mylikes.js?ver=100"></script>');
}

function mylikes_deactivate()
{
	require_once MYBB_ROOT."inc/adminfunctions_templates.php";
	find_replace_templatesets("postbit", "#".preg_quote('{$post[\'button_like\']}')."#i", '{$post[\'button_rep\']}');
	find_replace_templatesets("headerinclude", "#".preg_quote('<script type="text/javascript" src="{$mybb->asset_url}/jscripts/mylikes.js?ver=100"></script>')."#i", '', 0);
}

function mylikes_postbit(&$post)
{
	global $templates, $theme, $db, $mybb;

	if($mybb->input['uid'] == $post['uid'])
	    return;

	$query = $db->simple_select("reputation", "SUM(reputation) AS likes", "uid={$post['uid']} AND pid={$post['pid']}");
	$likes = $db->fetch_field($query, "likes");

	if(empty($likes))
		$likes = 0;

	$post['button_like'] = eval($templates->render("postbit_mylikes_button"));
}

function mylikes_popup()
{
	global $db, $mybb, $lang, $groupscache, $templates;

	if($mybb->input['action'] != "likes")
	    return;

	if(empty($mybb->input['pid']) || empty($mybb->input['uid']))
	    error_no_permission();

	$pid = $mybb->get_input("pid");
	$uid = $mybb->get_input("uid");

	$query = $db->simple_select("reputation", "*", "uid={$uid} AND pid={$pid}");
	$users = "";
	while($like = $db->fetch_array($query))
	{
		$user = get_user($like['adduid']);

		$name = format_name($user['username'], $user['usergroup'], $user['displaygroup']);
		$profile_link = build_profile_link($name, $user['uid'], '_blank', 'if(window.opener) { window.opener.location = this.href; return false; }');

		$send_pm = '';
		if($mybb->user['receivepms'] != 0 && $user['receivepms'] != 0 && $groupscache[$user['usergroup']]['canusepms'] != 0)
		{
			eval("\$send_pm = \"".$templates->get("misc_buddypopup_user_sendpm")."\";");
		}

		if($user['lastactive'])
		{
			$last_active = $lang->sprintf($lang->last_active, my_date('relative', $user['lastactive']));
		}
		else
		{
			$last_active = $lang->sprintf($lang->last_active, $lang->never);
		}

		$user['avatar'] = format_avatar(htmlspecialchars_uni($user['avatar']), $user['avatardimensions'], '44x44');

		$online_alt = alt_trow();
		$users .= eval($templates->render("misc_mylikes_like"));
	}

	if(empty($users))
	    $users = eval($templates->render("misc_mylikes_nolikes"));

	echo eval($templates->render("misc_mylikes", 1, 0));
	exit();
}
?>