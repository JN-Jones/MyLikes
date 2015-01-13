<?php

$edits = array(
	"postbit" => array(
		'{$post[\'button_rep\']}' => '{$post[\'button_like\']}',
	),
	"postbit_classic" => array(
		'{$post[\'button_rep\']}' => '{$post[\'button_like\']}',
	),
	"headerinclude"	=> array(
		'<script type="text/javascript" src="{$mybb->asset_url}/jscripts/general.js?ver=1800"></script>'
		=>
		'<script type="text/javascript" src="{$mybb->asset_url}/jscripts/general.js?ver=1800"></script>
<script type="text/javascript" src="{$mybb->asset_url}/jscripts/mylikes.js?ver=100"></script>',
	),
);