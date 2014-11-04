
var ww = document.body.clientWidth;
var www = $(window).height();
var hh = $(window).height()+60;

$('#container').attr('style', function(i,val){
	if (hh >= www){
	return 'min-height:'+hh+'px;'
	}
	else{
	return 'min-height:'+www+'px;'
	}
});

$(document).ready(function() {
	$(".nav li a").each(function() {
		if ($(this).next().length > 0) {
			$(this).addClass("parent");
		};
	})
	
	$(".toggleMenu").click(function(e) {
		e.preventDefault();
		$(this).toggleClass("active");
		$(".nav").toggle();
	});
	
	$("a").click(function(event){
		event.stopPropagation();
	});
	
	$("#content").click(function(e) {
		if ($(".toggleMenu").hasClass("active")){
		
		$(".toggleMenu").removeClass("active");
		$(".nav").hide();
		if ($(".nav li").hasClass("hover")){
			$(".nav li").removeClass("hover");
			}
		$("html, body").animate({ scrollTop: 0 }, "fast");
		return false;
		}
	});
	
	adjustMenu();
})

$(window).bind('resize orientationchange', function() {
	ww = document.body.clientWidth;
	adjustMenu();
});

var adjustMenu = function() {
	//if (ww <= 768) {
		$(".toggleMenu").css("display", "inline-block");
		if (!$(".toggleMenu").hasClass("active")) {
			$(".nav").hide();
			$(".nav li").removeClass("hover");
		} else {
			$(".nav").show();
		}
		$(".nav li").unbind('mouseenter mouseleave');
		$(".nav li a.parent").unbind('click').bind('click', function(e) {
			// must be attached to anchor element to prevent bubbling
			e.preventDefault();
			$(this).parent("li").toggleClass("hover");
		});
	//} 
	/*else if (ww > 768) {
		$(".toggleMenu").css("display", "none");
		$(".nav").show();
		$(".nav li").removeClass("hover");
		$(".nav li a").unbind('click');
		$(".nav li").unbind('mouseenter mouseleave').bind('mouseenter mouseleave', function() {
		 	// must be attached to li so that mouseleave is not triggered when hover over submenu
		 	$(this).toggleClass('hover');
		});
	}*/
}

function shadeRows(){
	$(document).ready(function() {
		$("table.timeoff tr:nth-child(2n)").addClass('shaded');
		$("table.coverage tr:nth-child(2n)").addClass('shaded');
		$("table.employees tr:nth-child(2n)").addClass('shaded');
		$("table.employees tr:nth-child(2n+1)").removeClass('shaded');
		$("table.sub_needs tr:nth-child(2n+3)").addClass('shaded');
		$("table.detail tr:nth-child(2n+3)").addClass('shaded');
		$("table.divisions tr:nth-child(2n+3)").addClass('shaded');
		$("table.confirming tr:nth-child(2n)").addClass('shaded');
		$("table.approve_timesheets tr:nth-child(4n+2)").addClass('shaded');
		$("table.approve_timesheets tr:nth-child(4n+3)").addClass('shaded');
	});
}
shadeRows();

function createTitle(){
	$(document).ready(function() {
		$('.td_inner').each(function(){
			h = $(this).height();
			content = $(this).html();
			$(this).css('white-space', 'normal');
			if ($(this).height() > h){
				$(this).attr('title', content);
			}
			$(this).css('white-space', 'nowrap');
		});
	});
}
createTitle();

function getScrollXY() {
	$(document).ready(function() {
		$('form#decline').append("<input type='hidden' name='scrollTop' value='"+$(document).scrollTop()+"'/>");
		$('form#decline').append("<input type='hidden' name='scrollLeft' value='"+$(document).scrollLeft()+"'>");
		$('form#undecline').append("<input type='hidden' name='scrollTop' value='"+$(document).scrollTop()+"'/>");
		$('form#undecline').append("<input type='hidden' name='scrollLeft' value='"+$(document).scrollLeft()+"'>");
		$('form#available').append("<input type='hidden' name='scrollTop' value='"+$(document).scrollTop()+"'/>");
		$('form#available').append("<input type='hidden' name='scrollLeft' value='"+$(document).scrollLeft()+"'>");
		$('form#unavailable').append("<input type='hidden' name='scrollTop' value='"+$(document).scrollTop()+"'/>");
		$('form#unavailable').append("<input type='hidden' name='scrollLeft' value='"+$(document).scrollLeft()+"'>");
		$('form.subassign').append("<input type='hidden' name='scrollTop' value='"+$(document).scrollTop()+"'/>");
		$('form.subassign').append("<input type='hidden' name='scrollLeft' value='"+$(document).scrollLeft()+"'>");
		$('form.declinecomment').append("<input type='hidden' name='scrollTop' value='"+$(document).scrollTop()+"'/>");
		$('form.declinecomment').append("<input type='hidden' name='scrollLeft' value='"+$(document).scrollLeft()+"'>");
	});
}