<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Calibrate Labels</title>
<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<style type="text/css">
#calibrate{
	width:598px;
	border:1px solid darkred;
	margin:30px auto;
	height:350px;
	}
.subcal{
	padding:10px 25px;
	}
</style>
</head>

<body>
<div id="calibrate"><div class="subcal">
<h1>Determine Your Printer's PPI</h1>
<p>Follow this simple test to calibrate how many pixels per inch (PPI) your printer prints at:</p>
<ol>
  <li>Print this page.</li>
  <li>The red outline this box is in is exactly 600 pixels wide</li>
  <li>Use a ruler and measure as closely as possible how wide the red outline is. Use decimals instead of fractions, e.g. 6 1/8&quot; is 6.125, 7 1/2 inch is 7.5</li>
  <li>Divide 600/{your_measurement}</li>
  <li>Enter that number in the 1 inch calibration field.</li>
  </ol>
<p>Normally the value will be around 100 - 96 is a typical value. </p>
<p>
<input type="button" name="Print" value="Print" onclick="window.print();" />
&nbsp;&nbsp;
<input type="button" name="Close" value="Close" onclick="window.close();" />
</p>
</div></div>
</body>
</html>
