function seeMenuHamburguer(element)
{
	var ul = $(element).children("ul");
	
	if ($(ul).hasClass('hamburguer'))
	{	
		$(ul).removeClass('hamburguer');	
	}
	else
	{
		$(ul).addClass('hamburguer');
	}
}