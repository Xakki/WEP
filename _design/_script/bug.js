function bugSpoilers(obj) {
	var cl = obj.className;
	//var cl = obj.getAttribute('class');
	var i = cl.indexOf('unfolded');
	if(i >= 0) {
		obj.className = cl.replace('unfolded','');
		//obj.setAttribute('class',cl.replace('unfolded',''));
		obj.nextSibling.style.display = 'none';
		//obj.nextElementSibling.style.display = 'none';
	}
	else {
		obj.className = cl+' unfolded';
		//obj.setAttribute('class',cl+' unfolded');
		obj.nextSibling.style.display = 'block';
		//obj.nextElementSibling.style.display = 'block';
	}
}