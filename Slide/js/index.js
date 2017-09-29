var x,y,z,w;

function setSlide()
{
	var sObj = $('div.slide');
	var sfade = new ShowUp("fade",400);
	var sslide = new ShowUp("slide",600);
	
	x = new Slider(sObj[0],3500,sfade);
	x.navigation();
	x.dots();
	
	y = new Slider(sObj[1],2500,sslide);
	y.navigation();
	
	z= new Slider(sObj[2],5000,sfade);
	z.dots();
	
	w= new Slider(sObj[3],1500,sslide);
	
}

jQuery(document).ready
(
	function()
	{
		setSlide();
	}
)