currentUrl = window.location.href;
collwd=false;
if (currentUrl.search(/collections.louvre.fr/)){
	if (currentUrl.search(/ark/)){
		collwd=true;
	}
};
if (collwd){
	browser.storage.sync.get('lang', function(data) {
		lg=data['lang'];
		
		var elemDiv = document.createElement('div');
		elemDiv.setAttribute('id','widgetwd');
		elemDiv.style.cssText = 'width:300px;opacity:0.9;z-index:100;background:#FFF;';
		document.getElementById('js-printNotice').insertAdjacentElement('afterend',elemDiv);
		arkid=currentUrl.substr(currentUrl.length - 9);
		fetch("https://zone47.com/lw/lwd.php?ark="+arkid+"&lang="+lg)
		.then(response => response.text())
		.then(text => {
			elemDiv.innerHTML = text;
		})
		.catch(error => alert("Erreur : " + error));
		
	}); 
}
