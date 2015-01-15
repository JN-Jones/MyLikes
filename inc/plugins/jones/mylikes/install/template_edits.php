<?php

$edits = array(
	"postbit" => array(
		'{$post[\'button_rep\']}' => '{$post[\'button_like\']}',
	),
	"postbit_classic" => array(
		'{$post[\'button_rep\']}' => '{$post[\'button_like\']}',
	),
	"headerinclude"	=> array(
		'// -->
</script>' => '// -->
</script>
<script type="text/javascript" src="{$mybb->asset_url}/jscripts/mylikes.js?ver=101"></script>',
	),
);