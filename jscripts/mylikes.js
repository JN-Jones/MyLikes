function addLike(pid, uid, likes, like_success, like_delete, lang_likes, lang_like, lang_unlike)
{
	// Initialize our internal cache of updated likes
	if(typeof addLike.like_cache == "undefined")
		addLike.like_cache = {};

	// If we changed that like count already: use the internal cache count
	if(typeof addLike.like_cache[pid] != "undefined")
		likes = addLike.like_cache[pid];

	// Are we deleting a like or adding one? Also recalculate the like count
	lid = "#like_"+pid;
	var deleting = "";
	var new_likes = likes+1;
	var message = like_success;
	if($(lid).hasClass("liked"))
	{
		deleting = "&delete=1";
		new_likes = likes-1;
		message = like_delete
	}

	// Now send the request
	$.ajax(
	{
		url: 'reputation.php?action=do_add&uid='+uid+'&pid='+pid+'&reputation=1'+deleting+'&my_post_key='+my_post_key,
		type: 'post',
		success: function (request)
		{
			// The success message is in the response - assume it was added correctly
			if(request.indexOf(message) > -1)
			{
				// And update the like counters
				id = "#liked_"+pid;
				$(id).text("("+(new_likes)+") "+lang_likes);
				$(lid).toggleClass("liked")
				if($(lid).hasClass("liked"))
					$(lid).text(lang_unlike);
				else
					$(lid).text(lang_like);
				addLike.like_cache[pid] = new_likes;
			}
			// We had an error - simply open the modal
			else
			{
				$(request).appendTo('body').modal( { fadeDuration: 250, zIndex: 5 } );
			}
		}
	});
}