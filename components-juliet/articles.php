<?php

//--------------------- standard way to declare block contents ---------------------
$block='mainRegionCenterContent';
ob_start();

if(!$articlesRegion)$articlesRegion='mainRegionCenterContent';
?>

<h3 style="line-height:40px;">Contact us</h3>

<form style="padding:0 20px;" name="form2" method="post" action="/index_01_exe.php" target="w2">
    <div style="margin-bottom:10px;">
        <label for="contact_form_name" style="display:inline-block;width:100px;vertical-align:top;">Name</label>
        <input name="Name" id="contact_form_name">
    </div>

    <div style="margin-bottom:10px;">
        <label for="contact_form_number" style="display:inline-block;width:100px;vertical-align:top;">Phone number</label>
        <input name="Phone" id="contact_form_number">
    </div>

    <div style="margin-bottom:10px;">
        <label for="contact_form_email" style="display:inline-block;width:100px;vertical-align:top;">Email</label>
        <input name="Email" id="contact_form_email">
    </div>

    <div style="margin-bottom:10px;">
        <label for="contact_form_message" style="display:inline-block;width:100px;vertical-align:top;">Message</label>
        <textarea name="Message" id="contact_form_message"></textarea>
    </div>

    <div style="margin-bottom:10px;">
        <input type="submit" content="Submit">
        <input name="mode" type="hidden" id="mode" value="message">
    </div>
</form>

<p>Chris@wingedrepublic.com</p>
<p>512-557-2945</p>

<?php
$articlesRegion=ob_get_contents();

$$block=ob_get_contents();
ob_end_clean();
?>