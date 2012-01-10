function bugSpoilers(obj) {
	var obj = obj.parentNode;
	if(obj.className.indexOf('unfolded') >= 0) obj.className = obj.className.replace('unfolded',''); else obj.className = obj.className+' unfolded';
}
