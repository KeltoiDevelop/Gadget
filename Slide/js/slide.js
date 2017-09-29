function ShowUp(name,interval)
{
	this.name = name;
	this.interval = interval;
	this.showEffect=function(show,hide)
	{
		$(show).show
		(
			this.name,
			{},
			this.interval,
			function()
			{
				$(hide).hide();
			}
		);
	}
}

function Slider(obj,interval,showUp)
{
	var me = this;
	this.obj = obj;
	this.interval = interval;
	this.showUp = showUp;
	this.index=0;
	this.length = $(this.obj).children('img').length-1;
	this.getImg=function()
	{
		return $(this.obj).children('img')[this.index];
	}
	this.next=function()
	{
		this.goto((this.index==(this.length))?0:this.index+1);	
	};
	this.previous=function()
	{
		this.goto((this.index==0)?this.length:this.index-1);
	};
	this.setFocus=function(index)
	{
		var li = $(obj).find("span.dot-content").find("ul.dot").find("li");
		
		$(li).attr('class','free');
		$(li[index]).attr('class','focus');
	};
	this.goto = function(index)
	{
		clearInterval(this.timer);
		var hide = this.getImg();
		$(hide).css("z-index",0);
		this.index=index;
		var show = this.getImg();
		$(show).css("z-index",1);
		this.setFocus(index);
		this.showUp.showEffect(show,hide);
		this.timer = setInterval
		(
			function()
			{
				me.next();
			},
			this.interval
		);
	};
	$(this.getImg()).show();
	this.timer = setInterval
	(
		function()
		{
			me.next();
		},
		interval
	);
	this.navigation=function()
	{
		var  html = '<span class="nav-content"><div class="nav-left nav"></div>';
	           html = html + '<div class="nav-right nav"></div></span>'; 
			   $(obj).append(html);
			   $(obj).children('span.nav-content').children('div.nav-left').click(function(){me.previous();});
			   $(obj).children('span.nav-content').children('div.nav-right').click(function(){me.next();});
	};
	this.dots=function()
	{
		var html = '<span class="dot-content"><ul class="dot"></ul></span>';
		$(obj).append(html);
		var ul = $(obj).find('span.dot-content').find('ul.dot');
		for(var i=0;i<=this.length;i++)
		{
			html = '<li alt=' + i + ' class=' + ((i==this.index)?"focus":"free") + '></li>';
			$(ul).append(html);
			var li = $(ul).children('li')[i];
			$(li).click(
				function()
				{
					if ($(this).attr('alt')!=me.index)
					{
						var clickIndex = parseInt($(this).attr("alt"));
						me.goto(clickIndex);
					}
				});
		}
	}
}
