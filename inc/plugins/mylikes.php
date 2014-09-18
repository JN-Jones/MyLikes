<?php
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("postbit", "mylikes_postbit");

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
<a href="javascript:" id="liked_{$post[\'pid\']}"><span style="padding-left: 2px; background-image: url();">({$likes}) Like(s)</span></a>';
	$templatearray = array(
		"title" => "postbit_mylikes_button",
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
?>