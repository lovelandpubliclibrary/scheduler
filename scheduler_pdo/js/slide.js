$(document).ready(function(){
	if (jQuery.browser.mobile===true){
		var element = document.getElementById('schedDiv');
		var hammertime = Hammer(element).on('swiperight',function(event){
			currentDate.setDate(currentDate.getDate()-1);
			var tom = currentDate.getFullYear()+"-"+(currentDate.getMonth()+1)+"-"+currentDate.getDate();
			
			var monthNames = [ "January", "February", "March", "April", "May", "June",
				"July", "August", "September", "October", "November", "December" ];
			var d = currentDate.getDate();
			var m = monthNames[(currentDate.getMonth())];
			var y = currentDate.getFullYear();
			
			document.title = d+' '+m+' '+y+' | Loveland Public Library';
			createTitle();
			
			$(this).closest('#schedDiv').hide('slide',{direction:'right'},500);
			$('#schedDiv').load('/scheduler/block2.php?today='+tom,function(){
				$(this).show('slide',{direction:'left'},500);
			});
			});
		}
	$('#schedDiv .prev').click(function(){
		currentDate.setDate(currentDate.getDate()-1);
		var tom = currentDate.getFullYear()+"-"+(currentDate.getMonth()+1)+"-"+currentDate.getDate();
		
		var monthNames = [ "January", "February", "March", "April", "May", "June",
			"July", "August", "September", "October", "November", "December" ];
		var d = currentDate.getDate();
		var m = monthNames[(currentDate.getMonth())];
		var y = currentDate.getFullYear();
		
		document.title = d+' '+m+' '+y+' | Loveland Public Library';
		createTitle();
		
		$(this).closest('#schedDiv').hide('slide',{direction:'right'},500);
		$('#schedDiv').load('/scheduler2/block2.php?today='+tom,function(){
			$(this).show('slide',{direction:'left'},500);
		});
	});
	
	$('#schedDiv .next').click(function(){
		currentDate.setDate(currentDate.getDate()+1);
		var tom = currentDate.getFullYear()+"-"+(currentDate.getMonth()+1)+"-"+currentDate.getDate();
		
		var monthNames = [ "January", "February", "March", "April", "May", "June",
			"July", "August", "September", "October", "November", "December" ];
		var d = currentDate.getDate();
		var m = monthNames[(currentDate.getMonth())];
		var y = currentDate.getFullYear();
		
		document.title = d+' '+m+' '+y+' | Loveland Public Library';
		createTitle();
		
		$(this).closest('#schedDiv').hide('slide',{direction:'left'},500);
		$('#schedDiv').load('/scheduler2/block2.php?today='+tom,function(){
			$(this).show('slide',{direction:'right'},500);
		});
	});

});