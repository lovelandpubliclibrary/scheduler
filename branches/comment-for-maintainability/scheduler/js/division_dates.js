var currentDate = new Date();
if (phpmon) {currentDate.setFullYear(phpyear,phpmon,phpdom);}

function loadnextFull() {
	currentDate.setDate(currentDate.getDate()+1);
	var tom = currentDate.getFullYear()+"-"+(currentDate.getMonth()+1)+"-"+currentDate.getDate();
	
	var monthNames = [ "January", "February", "March", "April", "May", "June",
		"July", "August", "September", "October", "November", "December" ];
	var d = currentDate.getDate();
	var m = monthNames[(currentDate.getMonth())];
	var y = currentDate.getFullYear();
	
var xmlhttp;
if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }

xmlhttp.onreadystatechange=function() {
  if (xmlhttp.readyState==4 && xmlhttp.status==200) {
    document.getElementById("schedDiv").innerHTML=xmlhttp.responseText;
	document.title = d+' '+m+' '+y+' | Loveland Public Library';
	createTitle();
    }
  }
  
xmlhttp.open("GET","block_daily_schedule.php?today=" + tom,true);
xmlhttp.send();
}

function loadprevFull() {
	currentDate.setDate(currentDate.getDate()-1);
	var tom = currentDate.getFullYear()+"-"+(currentDate.getMonth()+1)+"-"+currentDate.getDate();
	
	var monthNames = [ "January", "February", "March", "April", "May", "June",
		"July", "August", "September", "October", "November", "December" ];
	var d = currentDate.getDate();
	var m = monthNames[(currentDate.getMonth())];
	var y = currentDate.getFullYear();
	
var xmlhttp;
if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }

xmlhttp.onreadystatechange=function() {
  if (xmlhttp.readyState==4 && xmlhttp.status==200) {
    document.getElementById("schedDiv").innerHTML=xmlhttp.responseText;
	document.title = d+' '+m+' '+y+' | Loveland Public Library';
	createTitle();
    }
  }
  
xmlhttp.open("GET","block_daily_schedule.php?today=" + tom,true);
xmlhttp.send();
}

function loadnextDiv() {
	currentDate.setDate(currentDate.getDate()+1);
	var tom = currentDate.getFullYear()+"-"+(currentDate.getMonth()+1)+"-"+currentDate.getDate();
var xmlhttp;
if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }

xmlhttp.onreadystatechange=function() {
  if (xmlhttp.readyState==4 && xmlhttp.status==200) {
    document.getElementById("dayDiv").innerHTML=xmlhttp.responseText;
	createTitle();
    }
  }
  
xmlhttp.open("GET","../block_division_daily.php?today=" + tom + "&division=" + division,true);
xmlhttp.send();
}

function loadprevDiv() {
	currentDate.setDate(currentDate.getDate()-1);
	var tom = currentDate.getFullYear()+"-"+(currentDate.getMonth()+1)+"-"+currentDate.getDate();
var xmlhttp;
if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }

xmlhttp.onreadystatechange=function() {
  if (xmlhttp.readyState==4 && xmlhttp.status==200) {
    document.getElementById("dayDiv").innerHTML=xmlhttp.responseText;
	createTitle();
    }
  }
  
xmlhttp.open("GET","../block_division_daily.php?today=" + tom + "&division=" + division,true);
xmlhttp.send();
}

function loadnextweekDiv() {
	currentDate.setDate(currentDate.getDate()+7);
	var tom = currentDate.getFullYear()+"-"+(currentDate.getMonth()+1)+"-"+currentDate.getDate();
var xmlhttp;
if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }

xmlhttp.onreadystatechange=function() {
  if (xmlhttp.readyState==4 && xmlhttp.status==200) {
    document.getElementById("weekDiv").innerHTML=xmlhttp.responseText;
	shadeRows();
    }
  }
  
xmlhttp.open("GET","../block_division_weekly.php?today=" + tom + "&division=" + division,true);
xmlhttp.send();
}

function loadprevweekDiv() {
	currentDate.setDate(currentDate.getDate()-7);
	var tom = currentDate.getFullYear()+"-"+(currentDate.getMonth()+1)+"-"+currentDate.getDate();
var xmlhttp;
if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }

xmlhttp.onreadystatechange=function() {
  if (xmlhttp.readyState==4 && xmlhttp.status==200) {
    document.getElementById("weekDiv").innerHTML=xmlhttp.responseText;
	shadeRows();
    }
  }
  
xmlhttp.open("GET","../block_division_weekly.php?today=" + tom + "&division=" + division,true);
xmlhttp.send();
}

function loadnextSubs() {
	currentDate.setDate(currentDate.getDate()+1);
	var tom = currentDate.getFullYear()+"-"+(currentDate.getMonth()+1)+"-"+currentDate.getDate();
var xmlhttp;
if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }

xmlhttp.onreadystatechange=function() {
  if (xmlhttp.readyState==4 && xmlhttp.status==200) {
    document.getElementById("dayDiv").innerHTML=xmlhttp.responseText;
	createTitle();
    }
  }
  
xmlhttp.open("GET","../block_subs_specific.php?today=" + tom,true);
xmlhttp.send();
}

function loadprevSubs() {
	currentDate.setDate(currentDate.getDate()-1);
	var tom = currentDate.getFullYear()+"-"+(currentDate.getMonth()+1)+"-"+currentDate.getDate();
var xmlhttp;
if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }

xmlhttp.onreadystatechange=function() {
  if (xmlhttp.readyState==4 && xmlhttp.status==200) {
    document.getElementById("dayDiv").innerHTML=xmlhttp.responseText;
	createTitle();
    }
  }
  
xmlhttp.open("GET","../block_subs_specific.php?today=" + tom,true);
xmlhttp.send();
}

function loadnextweekSubs() {
	currentDate.setDate(currentDate.getDate()+7);
	var tom = currentDate.getFullYear()+"-"+(currentDate.getMonth()+1)+"-"+currentDate.getDate();
var xmlhttp;
if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }

xmlhttp.onreadystatechange=function() {
  if (xmlhttp.readyState==4 && xmlhttp.status==200) {
    document.getElementById("weekDiv").innerHTML=xmlhttp.responseText;
	shadeRows();
    }
  }
  
xmlhttp.open("GET","../block_subs_weekly.php?today=" + tom,true);
xmlhttp.send();
}

function loadprevweekSubs() {
	currentDate.setDate(currentDate.getDate()-7);
	var tom = currentDate.getFullYear()+"-"+(currentDate.getMonth()+1)+"-"+currentDate.getDate();
var xmlhttp;
if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }

xmlhttp.onreadystatechange=function() {
  if (xmlhttp.readyState==4 && xmlhttp.status==200) {
    document.getElementById("weekDiv").innerHTML=xmlhttp.responseText;
	shadeRows();
    }
  }
  
xmlhttp.open("GET","../block_subs_weekly.php?today=" + tom,true);
xmlhttp.send();
}
