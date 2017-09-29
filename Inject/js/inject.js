function call(screen)
{
	$('article').html("");
	var url = 'article/' + screen + '.htm';

	$.ajax
	(
		{
			context:document.body,
			url:url,
		}
	).done(function(obj)
	{
		$('article').html(obj);
	});
}

function inputHeader(header)
{
	var url = 'header/' + header + '.htm';
	$.ajax
	(
		{
			context:document.body,
			url:url,
		}
	).done(function(obj)
	{
		$('header').html(obj);
	});
}	

function inputFooter(footer)
{
	var url = 'footer/' + footer + '.htm';
	$.ajax
	(
		{
			context:document.body,
			url:url,
		}
	).done(function(obj)
	{
		$('footer').html(obj);
	});
}

function Menu(name,value)
{
	this.name=name;
	this.value=value;
	this.subMenu=new Array();
	this.addSubMenu=function(name,value)
	{
		subMenu.push(new Menu(name,value));
	};
}

function insertMenu(ul,menu)
{
	$.each(menu,function(i,e)
	{
		var html = '<li onclick="call(' + e.value + ')"><span>';
		html+=e.name;
		if (e.subMenu!=undefined)insertMenu(e.subMenu);
		html+='</span></li>';
	});
	
	$(ul).html(html);
}
