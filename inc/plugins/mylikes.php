<?php
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

// Test whether core is installed and if so get it up
defined("JB_CORE_INSTALLED") or require_once MYBB_ROOT."inc/plugins/jones/core/include.php";

$plugins->add_hook("postbit", "mylikes_postbit");
$plugins->add_hook("misc_start", "mylikes_popup");

function mylikes_info()
{
	global $pluginlist, $mybb, $lang, $db, $cache;

	if(!$pluginlist)
		$pluginlist = $cache->read("plugins");

	$lang->load("mylikes");

	$info = array(
		"name"			=> "MyLikes",
		"description"	=> "Adds a very simple like system using MyBB's reputation system",
		"website"		=> "http://jonesboard.de/",
		"author"		=> "Jones",
		"authorsite"	=> "http://jonesboard.de/",
		"version"		=> "1.0.1",
		"compatibility" => "18*",
		"codename"		=> "mylikes"
	);

	if(is_array($pluginlist['active']) && in_array("mylikes", $pluginlist['active']))
	{
		$query = $db->simple_select("settinggroups", "gid", "name='reputation'");
		$gid = $db->fetch_field($query, "gid");

		// Common errors - show messages for them
		if($mybb->settings['enablereputation'] != 1)
			$info['description'] .= "<br />".$lang->sprintf($lang->mylikes_error_enablereputation, $gid);
		elseif($mybb->settings['postrep'] != 1)
			$info['description'] .= "<br />".$lang->sprintf($lang->mylikes_error_postrep, $gid);
		elseif(!$mybb->settings['posrep'])
			$info['description'] .= "<br />".$lang->sprintf($lang->mylikes_error_posrep, $gid);
	}

	if(JB_CORE_INSTALLED === true)
		return JB_CORE::i()->getInfo($info);

	return $info;
}

function mylikes_install()
{
	jb_install_plugin("mylikes", array(), false, 1801);
}

function mylikes_is_installed()
{
	global $db;

	$query = $db->simple_select("templates", "*", "title='postbit_mylikes_button'");
	return ($db->num_rows($query) > 0);
}

function mylikes_uninstall()
{
	JB_Core::i()->uninstall("mylikes");
}

function mylikes_activate()
{
	JB_Core::i()->activate("mylikes");
}

function mylikes_deactivate()
{
	JB_Core::i()->deactivate("mylikes");
}

function mylikes_postbit(&$post)
{
	global $templates, $theme, $db, $mybb, $groupscache, $lang;

	// Permissions - I hate them... First: is the reputation system enabled?
	if($mybb->settings['enablereputation'] != 1 && $mybb->settings['postrep'] != 1)
		return; // Nothing to do...

	// Language - always needed
	$lang->load("mylikes");

	// Count the likes and build the numeric button
	$likes = JB_MyLikes_Like::getNumLikes($post['pid']);
	$likes_button = eval($templates->render("postbit_mylikes_button_num"));

	// We're viewing our own post -> nothing more todo *hurra*
	if($mybb->user['uid'] == $post['uid'])
	{
		$post['button_like'] = $likes_button;
		return;
	}

	// More fun: usergroup permissions - first: load it
	if($post['userusername'])
	{
		if(!$post['displaygroup'])
		{
			$post['displaygroup'] = $post['usergroup'];
		}
		$usergroup = $groupscache[$post['displaygroup']];
	}
	else
	{
		$usergroup = $groupscache[1];
	}

	// Either the logged in user can't give likes or the post author isn't allowed to receive them :(
	if($mybb->usergroup['cangivereputations'] != 1 && $usergroup['usereputationsystem'] != 1)
		return;

	// Did we liked that already?
	$liked = "";
	if(JB_MyLikes_Like::hasLiked($post['pid'], $mybb->user['uid']))
		$liked = "liked";

	// We need the success message to test whether an error occured
	$lang->load("reputation");
	$success = str_replace("'", "\'", $lang->vote_added_message);
	$delete = str_replace("'", "\'", $lang->vote_deleted_message);

	// Like or Unlike the thread?
	$mylikes = $lang->mylikes_like;
	if(!empty($liked))
		$mylikes = $lang->mylikes_unlike;

	// Get our buttons
	$post['button_like'] = eval($templates->render("postbit_mylikes_button"));
}

function mylikes_popup()
{
	global $db, $mybb, $lang, $groupscache, $templates;

	if($mybb->input['action'] == "likes_recount")
	{
		// Rebuild the cache for this post - the reputation/like counter may have changed
		if(!empty($mybb->input['pid']))
			JB_MyLikes_Like::cache($mybb->input['pid']);
		exit();
	}

	if($mybb->input['action'] != "likes")
		return;

	if(empty($mybb->input['pid']) || empty($mybb->input['uid']))
		error_no_permission();

	$lang->load("mylikes");

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