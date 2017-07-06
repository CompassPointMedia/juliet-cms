<?php
function h(){}
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>check entry form temp</title>
</head>

<body>
<form id="form1" name="form1" method="post" action="">
  <p>Account: 
    <select name="ParentAccounts_ID" id="ParentAccounts_ID">
	<option value="">&lt;Select..&gt;</option>
    </select>
</p>
  <p>Name: 
    <input name="Name" type="text" id="Name" value="<?php echo h($Name);?>" size="45" />
    <br />
    Amount: 
    <input name="Amount" type="text" id="Amount" size="8" />
  </p>
  <p>&nbsp; </p>
</form>
</body>
</html>
