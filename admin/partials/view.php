<!-- View used when the WordPress installation is connected -->
<div class="wrap">

    <div> 
        <h1 class="wp-heading-inline">LeadBI for WordPress (<?php echo $settings['websiteDomain'] ?>)</h1>
        <button class="button button-primary" style="float: right;margin-top: 10px; margin-left: 10px;" id="leadbi-disconnect" type="button">Disconnect</button>

        <?php if($this->isWooCommerceActive()) : ?>
            <?php if(isset($settings['wooCommerceEnabled']) && $settings['wooCommerceEnabled']) : ?>
                <button class="button button-primary" style="float: right;margin-top: 10px" id="leadbi-disable-woocommerce" type="button">Disable WooCommerce</button>
            <?php else: ?>
                <button class="button button-primary" style="float: right;margin-top: 10px" id="leadbi-enable-woocommerce" type="button">Enable WooCommerce</button>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <div>
        <small>Website ID: <?php echo $settings['websiteId'] ?> </small> 
    </div>

    <!-- use iframe to show the status of the connected website -->
    <iframe src="<?php echo $endpoint ?>?domain=<?php echo $domain; ?>" 
        frameborder="0" width="100%" height="900" data-nonce="<?php echo $nonce ?>" id="leadbi-iframe"></iframe>
    <!-- end iframe -->

</div>


