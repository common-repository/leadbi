jQuery(document).ready(function () {
    var nonce = jQuery('#leadbi-iframe').attr("data-nonce");

    // Create IE + others compatible event handler
    var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
    var eventer = window[eventMethod];
    var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";

    // Listen to message from child window
    eventer(messageEvent, function (e) {

        // parse event data
        if (!e.data || e.data[0] !== '{') {
            return false;
        }

        var data = null;

        try {
            data = JSON.parse(e.data);
        } catch (error) {
            console.error('leadbi plugin', error);
        }

        if (!data) {
            return false;
        }

        // check the event
        if (data.event !== 'connected') {
            return false;
        }

        return jQuery.ajax({
            type: "post",
            dataType: "json",
            url: leadbiAjaxUpdate.ajaxurl,
            data: {
                action: "leadbi_update_options",
                connected: 1,
                websiteId: data.websiteId,
                websiteDomain: data.websiteDomain,
                wooCommerceEnabled: null,
                nonce: nonce
            },
            success: function (response) {
                if (response.type == "success") {
                    window.location.reload();
                } else {
                    alert("Failed to update options")
                }
            }
        })

    });

    // disconnect button
    jQuery(document).on('click', '#leadbi-disconnect', function () {
        return jQuery.ajax({
            type: "post",
            dataType: "json",
            url: leadbiAjaxUpdate.ajaxurl,
            data: {
                action: "leadbi_update_options",
                connected: 0,
                websiteId: null,
                websiteDomain: null,
                wooCommerceEnabled: null,
                nonce: nonce
            },
            success: function (response) {
                if (response.type == "success") {
                    window.location.reload();
                } else {
                    alert("Failed to disconnect website")
                }
            }
        })
    });

    /**
     * Handle enable woocommerce integration
     */
    jQuery(document).on('click', '#leadbi-disable-woocommerce', function () {
        return jQuery.ajax({
            type: "post",
            dataType: "json",
            url: leadbiAjaxUpdate.ajaxurl,
            data: {
                action: "leadbi_update_options",
                wooCommerceEnabled: 0,
                nonce: nonce
            },
            success: function (response) {
                if (response.type == "success") {
                    window.location.reload();
                } else {
                    alert("Failed to disable woocommerce")
                }
            },
            error: function () {
                alert("Failed to disable woocommerce")
            }
        });
    });

    /**
     * Handle disable woocommerce integration
     */
    jQuery(document).on('click', '#leadbi-enable-woocommerce', function () {
        return jQuery.ajax({
            type: "post",
            dataType: "json",
            url: leadbiAjaxUpdate.ajaxurl,
            data: {
                action: "leadbi_update_options",
                wooCommerceEnabled: 1,
                nonce: nonce
            },
            success: function (response) {
                if (response.type == "success") {
                    window.location.reload();
                } else {
                    alert("Failed to enable woocommerce")
                }
            },
            error: function () {
                alert("Failed to enable woocommerce")
            }
        });
    });

});