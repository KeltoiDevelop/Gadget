

function setHamburguer(element)
{
	if (window.innerWidth<800)
	{
		var ul = $(element).children("ul");
		var invisible = $(ul).hasClass("hamburguer");
		
		if (invisible)
		{
			$(ul).removeClass("hamburguer");
		}
		else
		{
			$(ul).addClass("hamburguer");
		}
	}
}
