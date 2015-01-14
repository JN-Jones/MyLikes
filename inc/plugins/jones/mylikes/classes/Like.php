<?php

class JB_MyClasses_Like
{
	private static function cache($pid=false)
	{
		global $cache, $db;

		$where = "";
		if($pid !== false)
			$where = "pid=".(int)$pid;

		$query = $db->simple_select("reputation", "pid, adduid", $where);
		while($ent = $db->fetch_array($query))
			$data[$ent['pid']][] = $ent['adduid'];

		if($pid === false)
			$cache->update("mylikes", $data);
		else
		{
			$odata = $cache->read("mylikes");
			$odata[$pid] = $data[$pid];
			$cache->update("mylikes", $odata);
		}
	}

	private static function get($pid)
	{
		global $cache;

		$likes = $cache->read("mylikes");
		if(empty($likes))
		{
			static::cache();
			$likes = $cache->read("mylikes", true);
		}

		if(!isset($likes[$pid]))
			return array();

		return $likes[$pid];
	}

	public static function getNumLikes($pid)
	{
		return count(static::get($pid));
	}

	public static function hasLiked($pid, $uid)
	{
		$likes = static::get($pid);
		return in_array($likes, $uid);
	}
}