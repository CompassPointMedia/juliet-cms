<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Untitled Document</title>
<script language="javascript" type="text/javascript" src="/Library/js/jquery.js"></script>
<script language="javascript" type="text/javascript">
$(document).ready(function(){
	if(typeof(window.opener)!=='undefined'){
		for(var i in window.opener)alert(i + ':'+  window.opener[i]);
	}
});
</script>
</head>

<body>
hello
</body>
</html>
