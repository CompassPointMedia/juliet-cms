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

    <br/>
    <?php
    //jasperandwendy
    $res=rand(7,24);
    $_a=rand(2,$res-2);
    $_b=$res-$_a;
    $_q=sqrt($_a)/pow($_b, .3333);
    $_r=rand(1,1000000);
    ?>
    <span style="color:darkred;">Verify you are a human being to prevent spam!</span><br />
    <span style="font-size:larger;font-family:Georgia, 'Times New Roman', Times, serif;">
        <input type="hidden" name="_q" value="<?php echo $_q;?>" />
        <input type="hidden" name="_r" value="<?php echo $_r;?>" />
        <?php echo $_a . ' + '.$_b.' = ';?><input type="text" size="3" name="_res[<?php echo $_r;?>]" id="_res" value="" />
    </span>
    <br />
    <br />
    <div style="margin-bottom:10px;">
        <input type="submit" content="Submit">
        <input name="mode" type="hidden" id="mode" value="message">
    </div>

    <br />
</form>


<?php
$articlesRegion=ob_get_contents();

$$block=ob_get_contents();
ob_end_clean();
