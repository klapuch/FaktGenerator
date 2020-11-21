document.onkeydown = function (e) {
	if (e.key === 'ArrowRight') {
		window.location.href = document.getElementById('next').href;
	}
}
