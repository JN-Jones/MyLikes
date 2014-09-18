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

function mylikes_activate() {}

function mylikes_deactivate() {}

function mylikes_postbit(&$post)
{
	global $templates, $theme, $db, $mybb;

	if($mybb->input['uid'] == $post['uid'])
	    return;

	$query = $db->simple_select("reputation", "SUM(reputation) AS likes", "uid={$post['uid']} AND pid={$post['pid']}");
	$likes = $db->fetch_field($query, "likes");

	if(empty($likes))
		$likes = 0;

	$post['button_like'] = eval($templates->render("mylikes_postbit_button"));
}
?>