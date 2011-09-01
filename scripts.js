function ac_comments_hide() {
	document.getElementById('ac-client-comment').style.display = 'none';
	document.getElementById('ac-hide').style.display = 'none';
	document.getElementById('ac-show').style.display = 'block';
	document.getElementsByTagName('body')[0].style.paddingBottom = 0;
}

function ac_comments_show() {
	document.getElementById('ac-client-comment').style.display = 'block';
	document.getElementById('ac-show').style.display = 'none';
	document.getElementById('ac-hide').style.display = 'block';
	document.getElementsByTagName('body')[0].style.paddingBottom = '200px';
}