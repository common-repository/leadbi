<!-- 
View used to show the customer a list of websites from his LeadBI 
account to connect to the current WordPress installation
-->
<div class="wrap">

    <div> 
        <h1 class="wp-heading-inline">LeadBI for WordPress </h1>
    </div>

    <!-- iframe for to the current account list of websites -->
    <iframe src="<?php echo $endpoint ?>?domain=<?php echo $domain; ?>" 
        frameborder="0" width="100%" height="700" data-nonce="<?php echo $nonce ?>" id="leadbi-iframe"></iframe>
    <!-- end -->

    <!-- Reset settings button -->
    <div style="padding: 20px; text-align:center;"> 
        <button class="button button-primary" id="leadbi-disconnect" type="button">Reset Settings</button>
    </div>
</div>
