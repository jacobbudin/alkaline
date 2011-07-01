$(document).ready(function(){
	$('a[onclick*="FOTOMOTO"]').addClass('buy');
	$('.comment_submit').click(function(event){
		parent = $(this).parent();
		text = parent.find('.comment_text').val();
		author_name = parent.find('.comment_author_name').val();
		author_email = parent.find('.comment_author_email').val();
		image_id = parent.find('input[name="image_id"]').val();
		post_id = parent.find('input[name="post_id"]').val();
		if(!empty(text) && !empty(author_name) && !empty(author_email)){
			$.post(BASE + 'themes/onyxpro/tasks/add-comment.php', { text: text, author_name: author_name, author_email: author_email, image_id: image_id, post_id: post_id }, function(comment){
				if(!empty(comment)){
					$('table.comments').append('<tr><td></td><td>' + comment + '<p class="quiet">&#8212; ' + author_name + ', just now (may await moderation)</p></td></tr>');
					$('.comments.box').slideUp();
				}
				else{
					alert('Your comment could not be submitted at this time. Please try again later.');
				}
			});
		}
		else{
			alert('You must complete all fields to submit a comment.');
		}
		event.preventDefault();
	});
});