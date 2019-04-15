const headerClick = document.getElementsByClassName('clickable');
function click(e) {
	if (e.currentTarget.hasChildNodes()) {
		if (e.currentTarget.children[0].style.visibility == 'hidden' || e.currentTarget.children[0].style.visibility == '') {
			for (let i = 0; i < headerClick.length; i++){
				if (headerClick[i].children[0].style.visibility == 'visible') {
					headerClick[i].children[0].style.visibility = 'hidden'
				} 
			}
			e.currentTarget.children[0].style.visibility = 'visible'
		} else {
			e.currentTarget.children[0].style.visibility = 'hidden'
		}
	}
}