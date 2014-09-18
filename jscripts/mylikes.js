function addLike(pid, uid, likes)
{
	$.ajax(
	{
		url: 'reputation.php?action=do_add&uid='+uid+'&pid='+pid+'&reputation=1&my_post_key='+my_post_key,
		type: 'post',
		success: function (request)
		{
			id = "#liked_"+pid;
			$(id).text("("+(likes+1)+") Like(s)");
		}
	});
}