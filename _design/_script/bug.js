function bugSpoilers(obj) {
	var cl = obj.getAttribute('class');
	var i = cl.indexOf('unfolded');
	if(i >= 0) {
		obj.setAttribute('class',cl.replace('unfolded',''));
		var cl = obj.getAttribute('style');
		obj.nextElementSibling.style.display = 'none';
	}
	else {
		obj.setAttribute('class',cl+' unfolded');
		obj.nextElementSibling.style.display = 'block';
	}
}