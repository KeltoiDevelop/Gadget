/*Changes the opacity of settled element and change text, giving a highlight effect to applied text*/
function ClickFadeChange(obj, text)
{
	this.obj = obj;
	this.text = text;
	this.setTextAnimotion=function(opacityFrom,interval)
	{
		var fade = this.obj;
		var newT = this.text;
		var txt = $(this.obj).children('span.text');
		var originalT = $(txt).html();
		$(fade).click
		(
			function()
			{	
				var active = ($(txt).html()==originalT);
				var text = active?newT:originalT;
				var opacityTo = $(txt).css("opacity");
				var opacity = active?opacityFrom:opacityTo;
				
				$(fade).children('img').fadeTo
				(
					interval,
					opacity,
					function()
					{
						$(fade).children('span.text').html(text);
					}
				);
			}
		);
	};
	
	this.setTextTimer = function(interval,textSub)
	{
		var fade = this.obj;
		$(fade).children('span.text').html(this.text);
		
		return setInterval
			(
				function(t1, t2)
				{
					var val = 	($(fade).children('span.text').html()==t1)?t2:t1;
					$(fade).children('span.text').html(val);
				},
				interval,
				this.text,
				textSub
			);
	};
	
	this.setTextFade = function(interval,textSub)
	{
		var fade = this.obj;
		$(fade).children('span.text').html(this.text);
		
		return setInterval
			(
				function(t1, t2)
				{
					var val = 	($(fade).children('span.text').html()==t1)?t2:t1;
					
					$(fade).children('span.text').fadeOut
						(
							interval/3,
							function()
							{
								$(fade).children('span.text').html(val);
								$(fade).children('span.text').fadeIn(interval/3);
							}
						);
				},
				interval,
				this.text,
				textSub
			);		
	}
}
