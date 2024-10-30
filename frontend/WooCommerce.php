<?php

namespace LeadBI;

/**
 * The code used on the frontend.
 */
 class WooCommerce extends \WC_Integration { 

    protected static $product_type = array(
        'simple',
        'variable',
        'grouped'
    );

    /*
    * Construct
    */
    public function __construct() {
        $this->id = 'leadbi';
        $this->method_title = "LeadBI Integration";
        $this->method_description = __('eadBI is a powerful Marketing Automation software that allows you to automatically track, identify and communicate with any visitor coming to your website');

        $this->init_settings();

        $this->domain_api_key = $this->get_option('domain_api_key');
        $this->token = $this->get_option('token');
        $this->add_to_cart_button_id = $this->get_option('add_to_cart_button_id');
        $this->price_label_id = $this->get_option('price_label_id');
        $this->help_pages = $this->get_option('help_pages');

        add_action('woocommerce_before_single_product', array($this, 'viewProduct'), 20, 0);

        add_action('woocommerce_after_add_to_cart_button', array($this, 'addToCart'));
        
        add_action( 'woocommerce_after_cart', array($this, 'removeFromCart' ));
        add_action( 'woocommerce_after_mini_cart', array($this, 'removeFromCart' ));

        add_action('woocommerce_before_single_product', array($this, 'clickImage'), 30, 0);

        add_action('woocommerce_thankyou', array($this, 'createOrder'));
    }

    /**
     * Fetch product info
     */
    private function getProduct($product){
        if (!($product instanceof \WC_Product && $product->is_type(self::$product_type))) {
            return null;
        }

        // find the product price
        switch ($product->get_type()) {
            case 'variable':
                list($price, $specialPrice) = $this->getPricesForVariableProducts($product);
                break;
            case 'grouped':
                list($price, $specialPrice) = $this->getPricesForGroupedProducts($product);
                break;
            default:
                $price = wc_get_price_including_tax( $product, array('price' => $product->get_regular_price() ) );
                $salePrice = wc_get_price_including_tax( $product, array('price' => $product->get_price() ) );
                $salePrice = $price == $salePrice ? 0 : $salePrice;
                $specialPrice = (!empty($salePrice) ? $salePrice : 0);
                break;
        }

        // get product image
        $image_url = wp_get_attachment_url(get_post_thumbnail_id());
        if (empty($image_url)) {
            $image_url = site_url() . '/wp-content/plugins/woocommerce/assets/images/placeholder.png';
        }

        // get product category
        $categories = get_the_terms($product->get_id(), 'product_cat');
        $cat = array();
        if ($categories) {
            foreach ($categories as $category) {
                $cat['catid'] = $category->term_id;
                $cat['cat'] = $category->name;
                $cat['catparent'] = $category->parent;
            }
        } else {
            $cat['catid'] = 1;
            $cat['cat'] = "Root";
            $cat['catparent'] = "false";
        }

        // check if the product is in stock
        $stock = $product->is_in_stock() ? 1 : 0;

        return array(
            'id' => $product->get_id(),
            'name' => $product->get_title(),
            'img' => $image_url,
            'price' => $price,
            'promo_price' => $specialPrice,
            'available_inventory' => $stock,
            'category_id' => $cat['catid'],
            'category_name' => $cat['cat']
        );
    }

    /**
     * Returns the current product if product page
     */
    private function getCurrentProduct(){
        // check if we are on a product page
        if (!is_product()) {
            return false;
        }

        global $product; // get the current product

        // get product info
        $productInfo = $this->getProduct($product);
        return $productInfo;
    }
    /**
     * Add view product api call
     */
    public function viewProduct(){
        $productInfo = $this->getCurrentProduct();

        if(!$productInfo){
            return false;
        }

        $currency = get_woocommerce_currency(); 

        // inject javascript
        echo '<script>
            jQuery(document).ready(function () {
                // Shop.viewProduct(product, callback)
                window.$leadbi_website.getCurrentUser(function (err, user) {
                    return user.getShop(function (err, shop) { 
                        return shop.viewProduct({
                            id: "'. $productInfo['id'] . '",
                            name: "' . htmlspecialchars($productInfo['name']) . '",
                            url: "' . $productInfo['url'] . '",
                            img: "' . $productInfo['img'] . '",
                            price: "' . $productInfo['price'] . ' ' . $currency . '" ,
                            promo_price: "' . $productInfo['promo_price'] . ' ' . $currency . '",
                            available_inventory: ' . $productInfo['available_inventory']  . ',
                            category_id: ' . $productInfo['category_id'] . ',
                            category_name: "' . $productInfo['category_name'] . '"
                        }, function (err) {
                            console.error(err);
                        });
                    });
                });
            });
        </script>';
    }

    /**
     * Add add to product api call
     */
    public function addToCart() {
        $productInfo = $this->getCurrentProduct();

        if(!$productInfo){
            return false;
        }

        $currency = get_woocommerce_currency(); 

        // inject javascript
        echo '<script>
            jQuery(document).ready(function () {
                jQuery(".single_add_to_cart_button").click(function(){ 
                    // Shop.Cart.addProduct(product, callback)
                    window.$leadbi_website.getCurrentUser(function (err, user) {
                        return user.getShop(function (err, shop) {
                            return shop.getCart("default", function (err, cart) { 
                                return cart.addProduct({
                                    id: "'. $productInfo['id'] . '",
                                    name: "' . htmlspecialchars($productInfo['name']) . '",
                                    url: "' . $productInfo['url'] . '",
                                    img: "' . $productInfo['img'] . '",
                                    price: "' . $productInfo['price'] . ' ' . $currency . '" ,
                                    promo_price: "' . $productInfo['promo_price'] . ' ' . $currency . '",
                                    available_inventory: ' . $productInfo['available_inventory']  . ',
                                    category_id: ' . $productInfo['category_id'] . ',
                                    category_name: "' . $productInfo['category_name'] . '",
                                    quantity: 1
                                }, function (err) {
                                    console.error(err);
                                });
                            });
                        });
                    });
                });
            });
        </script>';
    }

    /**
     * Add remove from cart api call
     */
    public function removeFromCart() {

        // inject javascript
        echo '<script>
            jQuery(document).ready(function () {
                jQuery(".product-remove a.remove").click(function() {
                    // Shop.Cart.removeProduct(product, callback)
                    var product_id = jQuery(this).data(\'product_id\');
                    window.$leadbi_website.getCurrentUser(function (err, user) {
                        return user.getShop(function (err, shop) {
                            return shop.getCart("default", function (err, cart) { 
                                return cart.removeProduct(product_id, function (err) {
                                    console.error(err);
                                });
                            });
                        });
                    });

                });
            });
        </script>';
    }

    /**
     * Add click image api call
     */
    public function clickImage() {
        $productInfo = $this->getCurrentProduct();

        if(!$productInfo){
            return false;
        }

        $currency = get_woocommerce_currency(); 

        // inject javascript
        echo '<script>
            jQuery(document).ready(function () {

                if (document.getElementsByClassName(".woocommerce-main-image") > 0 ) {
                    jQuery(".woocommerce-main-image").click(function() { 
                        // Shop.Cart.addProduct(product, callback)
                        window.$leadbi_website.getCurrentUser(function (err, user) {
                            return user.getShop(function (err, shop) {
                                return shop.clickImage({
                                    id: "'. $productInfo['id'] . '",
                                    name: "' . htmlspecialchars($productInfo['name']) . '",
                                    url: "' . $productInfo['url'] . '",
                                    img: "' . $productInfo['img'] . '",
                                    price: "' . $productInfo['price'] . ' ' . $currency . '" ,
                                    promo_price: "' . $productInfo['promo_price'] . ' ' . $currency . '",
                                    available_inventory: ' . $productInfo['available_inventory']  . ',
                                    category_id: ' . $productInfo['category_id'] . ',
                                    category_name: "' . $productInfo['category_name'] . '",
                                    quantity: 1
                                }, function (err) {
                                    console.error(err);
                                });
                            });
                        });
                    });
                }

                jQuery(".woocommerce-product-gallery__image").click(function() {
                    // Shop.Cart.addProduct(product, callback)
                    window.$leadbi_website.getCurrentUser(function (err, user) {
                        return user.getShop(function (err, shop) {
                            return shop.clickImage({
                                id: "'. $productInfo['id'] . '",
                                name: "' . htmlspecialchars($productInfo['name']) . '",
                                url: "' . $productInfo['url'] . '",
                                img: "' . $productInfo['img'] . '",
                                price: "' . $productInfo['price'] . ' ' . $currency . '" ,
                                promo_price: "' . $productInfo['promo_price'] . ' ' . $currency . '",
                                available_inventory: ' . $productInfo['available_inventory']  . ',
                                category_id: ' . $productInfo['category_id'] . ',
                                category_name: "' . $productInfo['category_name'] . '",
                                quantity: 1
                            }, function (err) {
                                console.error(err);
                            });
                        });
                    });
                });
            });
        </script>';

    }

    /**
     * Add create order api call
     */
    public function createOrder($order_id){
        if (!is_numeric($order_id)) {
            return false;
        }

        $order = new \WC_Order($order_id);

        $coupons_list = '';
        if ($order->get_used_coupons()) {
            $coupons_count = count($order->get_used_coupons());
            $i = 1;
            foreach ($order->get_used_coupons() as $coupon) {
                $coupons_list .= $coupon;
                if ($i < $coupons_count) {
                    $coupons_list .= ', ';
                    $i++;
                }
            }
        }

        $currency = get_woocommerce_currency(); 

        // inject javascript
        echo '<script>
            jQuery(document).ready(function () {
                window.$leadbi_website.getCurrentUser(function (err, user) {
                    return user.getShop(function (err, shop) {
                        return shop.getCart("default", function (err, cart) { 
                            return cart.createOrder({
                                "order_no": ' . $order->get_id() . ',
                                "first_name": "' . $order->get_billing_last_name() . '",
                                "last_name": "' . $order->get_billing_first_name() . '",
                                "email": "' . $order->get_billing_email() . '",
                                "phone": "' . $order->get_billing_phone() . '",
                                "state": "' . $order->get_billing_state() . '",
                                "city": "' . $order->get_billing_city() . '",
                                "address": "' . $order->get_billing_address_1() . " " . $order->get_billing_address_2() . '",
                                "discount_code": "' . $coupons_list . '",
                                "discount": ' . (empty($order->get_discount) ? 0 : $order->get_discount) . ',
                                "shipping": ' . (empty($order->get_total_shipping) ? 0 : $order->get_total_shipping) . ',
                                "rebates": 0,
                                "fees": 0,
                                "total": "' . $order->get_total() . ' ' . $currency . '"
                            }, function (err) {
                                console.error(err);
                            });
                        });
                    });
                });
            });
        </script>';

    }

 }