window.onload = function () {
	mimg = document.getElementById("mimg");
	if (mimg != null)
		mimg.ondblclick = function () {
			if (mimg.style.maxWidth == "none") {
				mimg.style.maxWidth = "800px";
				mimg.style.maxHeight = "800px";
			} else {
				mimg.style.maxWidth = "none";
				mimg.style.maxHeight = "none";
			}
		}
}

function toggle_favorite(element, id) {
	if (element.style.color == "gray") {
		if (set_favorite_internal(id, 1)) {
			element.style.color = "red";
		}
	} else {
		if (set_favorite_internal(id, 0)) {
			element.style.color = "gray";
		}
	}
	return false;
}

function set_favorite_internal(id, fav) {
	try {
		xmlhttp = new XMLHttpRequest();
		xmlhttp.open("POST", "favorite.php", false);
		xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
		xmlhttp.send("id=" + id + "&fav=" + fav);
		return xmlhttp.status == 200;
	} catch (ex) { return false; }
}
