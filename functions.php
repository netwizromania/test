function hello_elementor_child_scripts_styles() {

    wp_enqueue_style(
        'hello-elementor-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        [
            'hello-elementor-theme-style',
        ],
        HELLO_ELEMENTOR_CHILD_VERSION
    );

}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_scripts_styles', 20 );

/* Product page labels */
function display_feature_labels( $attr = '', $return = false ) {
    if (empty($attr)) {
        return;
    }

    $product = wc_get_product();
    if ( ! $product ) {
        return;
    }

    $attributes = array(
        'pa_wifi' => array(
            'icon' => 'fas fa-wifi',
            'text_yes' => 'Are Wi-Fi',
            'text_no' => 'Nu are Wi-Fi',
        ),
        'pa_kit-montaj' => array(
            'icon' => 'fas fa-tools',
            'text_yes' => 'Are Kit*',
            'text_no' => 'Nu are Kit',
        ),
        'pa_montaj' => array(
            'icon' => 'fas fa-hammer',
            'text_yes' => 'Are montaj',
            'text_no' => 'Fără montaj',
        ),
        'pa_transp-gratis' => array(
            'icon' => 'fas fa-truck',
            'text_yes' => 'Transport Gratuit*',
            'text_no' => 'Transport Plătit*',
        ),
    );

    $slug = array_key_exists($attr, $attributes) ? $attr : '';
    if (empty($slug)) return;
    
    $feature = $product->get_attribute($slug);

    if ($feature) {
        $class = (strtolower($feature) === 'yes' || strtolower($feature) === 'da') ? 'yes' : 'no';
        $text = $class === 'yes' ? $attributes[$slug]['text_yes'] : $attributes[$slug]['text_no'];
        $output = '<span class="feature-label ' . $class . '"><i class="' . $attributes[$slug]['icon'] . '"></i> ' . $text . '</span>';

        if ($return) {
            return $output;
        } else {
            echo $output;
        }
    }
}
add_action('woocommerce_single_product_summary', 'display_feature_labels', 6);

function display_feature_labels_shortcode( $atts ) {
    $attr = isset( $atts['id'] ) ? $atts['id'] : '';
    ob_start();
    display_feature_labels( $attr );
    return ob_get_clean();
}
add_shortcode('feature_labels', 'display_feature_labels_shortcode');

function display_transp_wifi_kit_montaj_montaj( $atts ) {
    ob_start();
    display_feature_labels( 'pa_transp-gratis' );
    display_feature_labels( 'pa_wifi' );
    display_feature_labels( 'pa_kit-montaj' );
    display_feature_labels( 'pa_montaj' );
    return ob_get_clean();
}
add_shortcode('transp_wifi_kit_montaj_montaj', 'display_transp_wifi_kit_montaj_montaj');

function display_optim_iarna( $atts ) {
    return display_feature_labels( 'pa_optim-iarna', true );
}
add_shortcode('optim_iarna', 'display_optim_iarna');

add_action('woocommerce_update_product', 'custom_save_attribute_meta');
add_action('woocommerce_new_product', 'custom_save_attribute_meta');
function custom_save_attribute_meta($product_id) {
    $attributes = ['pa_capgen', 'pa_seer', 'pa_scop'];

    $product = wc_get_product($product_id);
    foreach ($attributes as $attribute) {
        $attributeValue = $product->get_attribute($attribute);

        if (!add_post_meta($product_id, $attribute, $attributeValue, true)) {
            update_post_meta($product_id, $attribute, $attributeValue);
        }
    }
	
	$regularPrice = (float)$product->get_regular_price();
    $salePrice = (float)$product->get_sale_price();

    $discount = round($regularPrice - $salePrice);
    update_post_meta($product_id, '_discount_amount', $discount);
}

add_filter('woocommerce_catalog_orderby', 'custom_woocommerce_catalog_orderby');
function custom_woocommerce_catalog_orderby($options) {
    $options['pa_capgen'] = __( 'Sortare implicită', 'hello-elementor');
    $options['pa_seer'] = __( 'Sortare după SEER', 'hello-elementor');
    $options['pa_scop'] = __( 'Sort după SCOP', 'hello-elementor');

    return $options;
}

add_filter( 'woocommerce_default_catalog_orderby', 'custom_default_catalog_orderby');
function custom_default_catalog_orderby($sortby) {
    return 'pa_capgen';
}

add_filter( 'woocommerce_get_catalog_ordering_args', 'custom_woocommerce_get_catalog_ordering_args', 10, 3);
function custom_woocommerce_get_catalog_ordering_args( $args, $orderby, $order ) {
    $attributes = ['pa_capgen', 'pa_seer', 'pa_scop'];
    if (in_array($orderby, $attributes)) {
        $args['meta_key'] = $orderby;
        add_filter('posts_clauses', 'custom_order_by_price_asc_post_clauses', 20);
    }

    return $args;
}

function custom_append_product_sorting_table_join($sql) {
    global $wpdb;

    if (!strstr($sql, 'wc_product_meta_lookup')) {
        $sql .= " LEFT JOIN {$wpdb->wc_product_meta_lookup} wc_product_meta_lookup ON $wpdb->posts.ID = wc_product_meta_lookup.product_id ";
    }

    return $sql;
}

function custom_order_by_price_asc_post_clauses($args) {
    $orderby = $_GET['orderby'] ?? null;
    $order = [
        'pa_capgen' => 'ASC',
        'pa_seer' => 'DESC',
        'pa_scop' => 'DESC',
    ];

    $args['join'] = custom_append_product_sorting_table_join($args['join']);
    $args['orderby'] = sprintf(' CAST(meta_value as unsigned) %s, wc_product_meta_lookup.min_price ASC ', $order[$orderby] ?? 'ASC');

    return $args;
}

add_filter('woocommerce_available_payment_gateways', 'custom_available_payment_gateways');
function custom_available_payment_gateways($available_gateways) {
    if (is_admin() || !is_checkout() || !isset($available_gateways['cod'])) {
        return $available_gateways;
    }

    foreach (WC()->cart->get_cart_contents() as $values) {
        $productDisableCod = get_post_meta($values['product_id'], '_custom_product_disable_cod_field', true);
        if ($productDisableCod === 'yes') {
            unset($available_gateways['cod']);

            break;
        }
    }

    return $available_gateways;
}

add_action( 'woocommerce_product_options_general_product_data', 'custom_woocommerce_product_fields' );
function custom_woocommerce_product_fields()
{
    global $woocommerce, $post;
    echo '<div class="product_custom_field">';
    woocommerce_wp_checkbox(
        array(
            'id' => '_custom_product_disable_cod_field',
            'label' => __('Fără plata cash', 'hello-elementor'),
        )
    );
    woocommerce_wp_text_input(
        array(
            'id' => '_custom_product_family_field',
            'label' => __('Gamă produs', 'hello-elementor'),
        )
    );
    echo '</div>';
}

add_action( 'woocommerce_process_product_meta', 'custom_product_custom_fields_save' );
function custom_product_custom_fields_save($post_id)
{
    update_post_meta($post_id, '_custom_product_disable_cod_field', esc_attr($_POST['_custom_product_disable_cod_field']));
    update_post_meta($post_id, '_custom_product_family_field', esc_attr($_POST['_custom_product_family_field']));
}

/* Stock and Quatity */
add_shortcode('custom_product_other_models', 'custom_product_other_models_callback');
function custom_product_other_models_callback(){
    $currentProduct = wc_get_product();
    $productFamily = $currentProduct->get_attribute('pa_gama');
    if (empty($productFamily)) {
        return null;
    }

    $result = [];
    $query = [
        'post_type' => 'product',
        'posts_per_page' => 3,
        'meta_key' => 'pa_capgen',
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
        'meta_query' => [
            [
                'key' => '_stock_status',
                'value' => 'instock'
            ],
        ],
        'tax_query' => [
            [
                'taxonomy' => 'pa_gama',
                'field' => 'name',
                'terms' => $productFamily,
            ]
        ],
    ];
    $wpQuery = new WP_Query($query);
    while ($wpQuery->have_posts()) {
        $wpQuery->the_post();
        $product = wc_get_product();
        if ($currentProduct->get_id() === $product->get_id()) {
            continue;
        }

        $productCapGen = $product->get_attribute('pa_capgen');
        $result[] = sprintf("<a href='%s'>%s BTU</a>", get_permalink($product->get_id()), $productCapGen);
    }

    wp_reset_postdata();

    return implode(', ', $result);
}

add_filter('yith_wcan_use_wp_the_query_object', '__return_true');
add_filter('elementor_pro/dynamic_tags/shortcode/should_escape', '__return_false');

/* Sorting */
add_shortcode('category_feature_labels', 'display_category_feature_labels_shortcode');
function display_category_feature_labels_shortcode($atts) {
    ob_start();
    display_other_feature_labels();
    return ob_get_clean();
}

add_action( 'elementor/query/pa_capgen', 'custom_elementor_loop_sorting_capgen', 10, 2 );
add_action( 'elementor/query/pa_seer', 'custom_elementor_loop_sorting_seer', 10, 2 );
add_action( 'elementor/query/pa_scop', 'custom_elementor_loop_sorting_scop', 10, 2 );
add_action( 'elementor/query/pa_reduceri', 'custom_elementor_loop_sorting_reduceri', 10, 2 );

function custom_elementor_loop_sorting_capgen( $query, $widget ) {
    $_GET['orderby'] = 'pa_capgen';
    $query->set('meta_key', 'pa_capgen');
    add_filter('posts_clauses', 'custom_order_by_price_asc_post_clauses', 20);
}

function custom_elementor_loop_sorting_seer( $query, $widget ) {
    $_GET['orderby'] = 'pa_seer';
    $query->set('meta_key', 'pa_seer');
    add_filter('posts_clauses', 'custom_order_by_price_asc_post_clauses', 20);
}

function custom_elementor_loop_sorting_scop( $query, $widget ) {
    $_GET['orderby'] = 'pa_scop';
    $query->set('meta_key', 'pa_scop');
    add_filter('posts_clauses', 'custom_order_by_price_asc_post_clauses', 20);
}

function custom_elementor_loop_sorting_reduceri( $query, $widget ) {
    $query->set('meta_query', [
        'relation' => 'AND',
        [
            'key' => '_discount_amount',
            'compare' => 'EXISTS',
            'type' => 'numeric',
        ],
        [
            'key' => '_stock',
            'compare' => 'EXISTS',
            'type' => 'numeric',
        ],
        [
            'key' => '_sale_price',
            'compare' => 'EXISTS',
            'type' => 'numeric',
        ],
    ]);

    $query->set('orderby', [
        '_discount_amount' => 'DESC',
        '_stock' => 'DESC',
        '_sale_price' => 'DESC',
    ]);
}

/* Product Page Attribute Youtube */
function youtube_embed_shortcode() {
    global $post;
    $youtube_url = get_post_meta($post->ID, 'youtube_video_url', true);

    if ($youtube_url) {
        return wp_oembed_get($youtube_url);
    }

    return '';
}
add_shortcode('youtube_embed', 'youtube_embed_shortcode');

/* Product Page Attribute For Text */
function get_product_attribute_shortcode( $atts ) {
    global $product;
    
    if( ! is_object( $product ) ) {
        return '';
    }
    
    $atts = shortcode_atts( array(
        'attr' => null,
    ), $atts, 'product_attr' );

    if( ! $atts['attr'] ) {
        return '';
    }
    
    return $product->get_attribute( wc_sanitize_taxonomy_name( $atts['attr'] ) );
}
add_shortcode( 'product_attr', 'get_product_attribute_shortcode' );

/* AutoUpdate Special Categories */
function auto_update_sale_and_attribute_categories() {
    // Get all products
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
    );

    $loop = new WP_Query( $args );

    while ( $loop->have_posts() ) {
        $loop->the_post();
        $product = wc_get_product( get_the_ID() );

        // Check if product is on sale and in stock
        if( $product->is_on_sale() && $product->get_stock_quantity() > 0 ) {
            // If product is on sale, add it to 'reduceri-aer-conditionat' category
            wp_set_object_terms( get_the_ID(), 'reduceri-aer-conditionat', 'product_cat', true );
        } else {
            // If product is not on sale, remove it from 'reduceri-aer-conditionat' category
            wp_remove_object_terms( get_the_ID(), 'reduceri-aer-conditionat', 'product_cat' );
        }

        // Check product attributes and add to respective categories
        $attributes = $product->get_attributes();
        if ( isset($attributes['pa_scop']) && floatval($product->get_attribute('pa_scop')) >= 7.3 && $product->get_stock_quantity() > 0 ) {
            wp_set_object_terms( get_the_ID(), 'cel-mai-bun-aer-conditionat-dupa-scop', 'product_cat', true );
        }
        if ( isset($attributes['pa_seer']) && floatval($product->get_attribute('pa_seer')) >= 4.4 && $product->get_stock_quantity() > 0 ) {
            wp_set_object_terms( get_the_ID(), 'cel-mai-bun-aer-conditionat-dupa-seer', 'product_cat', true );
        }
        if ( isset($attributes['pa_c-energ-mod-r']) && $product->get_attribute('pa_c-energ-mod-r') == 'A+++' && $product->get_stock_quantity() > 0 ) {
            wp_set_object_terms( get_the_ID(), 'recomandari-dupa-clasa-energetica-racire', 'product_cat', true );
        }

        // Check if product is in stock and add to the respective BTU and brand category
        if ($product->get_stock_quantity() > 0) {
            $pa_capgen = intval($product->get_attribute('pa_capgen'));
            $pa_marca = strtolower($product->get_attribute('pa_marca'));
            $btu_brands = array('gree', 'yamato', 'midea');
            if (in_array($pa_marca, $btu_brands) && in_array($pa_capgen, array(9000, 12000, 18000, 24000))) {
                wp_set_object_terms( get_the_ID(), "aer-conditionat-{$pa_marca}-{$pa_capgen}-btu-stoc", 'product_cat', true );
            }
        }
    }

    wp_reset_query();
}

/* Special Categories Special Page */
function update_categories_page() {
    // Check if the button is clicked
    if ( isset($_POST['update_categories']) ) {
        auto_update_sale_and_attribute_categories();
        echo '<div class="updated"><p>Categoriile au fost actualizate cu succes, Silviu!</p></div>';
    }

    // Display the form and button
    echo '<div class="wrap">';
    echo '<h1>Actualizare categorii</h1>';
    echo '<form method="post">';
    echo '<input type="submit" name="update_categories" class="button button-primary" value="Actualizează acum" />';
    echo '</form>';
    echo '</div>';
}

add_action( 'admin_menu', 'register_my_custom_menu_page' );

function register_my_custom_menu_page() {
    add_menu_page(
        'Update Categories', // page title
        'Update Categories', // menu title
        'manage_options', // capability
        'update_categories', // menu slug
        'update_categories_page', // callable function
        '', // icon url
        90 // position
    );
}

/* Product Page Discount Percentage */
function display_discount_percentage( $atts ) {
    $product = wc_get_product();
    if ( ! $product ) {
        return '';
    }

    if ( $product->is_on_sale() ) {
        $regular_price = (float) $product->get_regular_price();
        $sale_price = (float) $product->get_sale_price();
        $percentage = round( 100 - ( $sale_price / $regular_price * 100 ) );

        ob_start();
        ?>
        <div class="discount-percentage-container">
            <div class="discount-icon-cell"><i class="fas fa-tag"></i></div>
            <div class="discount-text-cell">
                <h3 class="h3-discount-percentage">Produs redus cu <?php echo $percentage; ?>%*</h3>
                <span class="p-discount-percentage">*De la prețul recomandat de producător</span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    return '';
}
add_shortcode( 'discount_percentage', 'display_discount_percentage' );

/* Category page labels */
/* 
 * This function is used to display the stock status of a product.
 */
function display_stock_status() {  
  global $product;  

  // Checking if the product is set
  if ( ! $product ) {  
    global $post;  
    $product = wc_get_product($post->ID);  
  }  

  // If the product is still not set, we return
  if(!$product) {
    return;
  }

  // Stock status  
  $stock_quantity = $product->get_stock_quantity();  
  $stock_status = '';  
  $stock_icon = '';  
  $stock_text_color = '';  

  // Assigning values based on stock quantity
  if ($stock_quantity > 5) {  
    $stock_status = 'Stoc suficient';  
    $stock_icon = 'fas fa-check-circle';  
    $stock_text_color = 'stock-green';  
  } elseif ($stock_quantity > 0 && $stock_quantity <= 5) {  
    $stock_status = 'Stoc limitat';  
    $stock_icon = 'fas fa-exclamation-circle';  
    $stock_text_color = 'stock-orange';  
  } else {  
    $stock_status = 'Indisponibil';  
    $stock_icon = 'fas fa-dot-circle-o';  
    $stock_text_color = 'stock-grey';  
  }  

  // Echoing out the stock status with assigned values
  echo '<div class="stock-product-loop ' . $stock_text_color . '">';  
  echo '<i class="' . $stock_icon . '"></i> ' . $stock_status;  
  echo '</div>';  
}

function display_transport_status() {
    global $product;

    if ( ! $product ) {
        global $post;
        $product = wc_get_product($post->ID);
    }

    // Transport attribute
    $feature = $product->get_attribute('pa_transp-gratis');
    $transport_text = '';
    $transport_icon = '';
    $transport_text_color = '';

    if (strtolower($feature) === 'yes' || strtolower($feature) === 'da') {
        $transport_text = 'Transport gratuit';
        $transport_icon = 'fas fa-truck-loading';
        $transport_text_color = 'green !important'; // Green color
    } else {
        $transport_text = 'Transport nu este gratuit';
        $transport_icon = 'fas fa-truck-loading';
        $transport_text_color = '#FF6347 !important'; // Red color
    }

    echo '<div class="transport-product-loop" style="color:' . $transport_text_color . ';">';
    echo '<i class="' . $transport_icon . '"></i> ' . $transport_text;
    echo '</div>';
}


function display_stock_status_shortcode() {
    ob_start();
    display_stock_status();
    return ob_get_clean();
}

function display_transport_status_shortcode() {
    ob_start();
    display_transport_status();
    return ob_get_clean();
}

add_shortcode('stock_status', 'display_stock_status_shortcode');
add_shortcode('transport_status', 'display_transport_status_shortcode');

// Display stock and transport status on WooCommerce default loop
add_action('woocommerce_before_shop_loop_item', 'display_stock_status', 9);
add_action('woocommerce_before_shop_loop_item', 'display_transport_status', 9);

function display_other_feature_labels() {
    global $product;

    if (! $product) {
        global $post;
        $product = wc_get_product($post->ID);
    }
	
	if (!$product) {
		return;
	}

    // Product attributes
    $attributes = array(
        'pa_optim-iarna' => array(
            'label_yes' => '<i class="fa fa-snowflake-o blue"></i><i class="fa fa-sun orange"></i>',
            'label_no' => '<i class="fa fa-snowflake-o blue"></i>',
        ),
        'pa_wifi' => array(
            'label_yes' => '<i class="fa fa-wifi green"></i>',
            'label_no' => '<i class="fa fa-wifi red"></i>',
        ),
        'pa_kit-montaj' => array(
            'label_yes' => '<i class="fa fa-plug green"></i>',
            'label_no' => '<i class="fa fa-plug red"></i>',
        ),
        'pa_montaj' => array(
            'label_yes' => '<i class="fa fa-wrench green"></i>',
            'label_no' => '<i class="fa fa-wrench red"></i>',
        ),
    );

    ob_start();

    echo '<div class="feature-labels-product-loop-main">';
    foreach ($attributes as $slug => $data) {
        $feature = $product->get_attribute($slug);
        $label = (strtolower($feature) === 'yes' || strtolower($feature) === 'da') ? $data['label_yes'] : $data['label_no'];

        echo '<div class="feature-label-product-loop-main">' . $label . '</div>';
    }
    echo '</div>';

    return ob_get_clean();
}

function display_other_feature_labels_shortcode($atts) {
    return display_other_feature_labels();
}

add_shortcode('other_feature_labels', 'display_other_feature_labels_shortcode');

function display_stock( $atts ) {
    $product = wc_get_product();
    if ( ! $product ) {
        return '';
    }
    
    $stock_quantity = $product->get_stock_quantity();
    $message = '';
    $icon = '';
    $status_class = '';
    
    if ($stock_quantity > 5) {
        $message = 'Stoc suficient';
        $icon = 'fa fa-battery-full stock-suficient';
        $status_class = 'stock-suficient';
    } elseif ($stock_quantity > 0 && $stock_quantity <= 5) {
        $message = 'Stoc limitat';
        $icon = 'fa fa-battery-quarter stock-limitat';
        $status_class = 'stock-limitat';
    } elseif ($stock_quantity == 0) {
        $message = 'Produs indisponibil momentan';
        $icon = 'fa fa-battery-empty stock-limitat';
        $status_class = 'produs-indisponibil';
    } 

    ob_start();
    ?>
    <div class="stock-status-container">
        <i class="<?php echo $icon; ?> stock-icon"></i>
        <h4 class="stock-status-text <?php echo $status_class; ?>"><?php echo $message; ?></h4>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('display_stock', 'display_stock');

function display_transp_gratis( $atts ) {
    ob_start();
    display_feature_labels( 'pa_transp-gratis' );
    return ob_get_clean();
}
add_shortcode( 'transp_gratis', 'display_transp_gratis' );

function display_wifi( $atts ) {
    ob_start();
    display_feature_labels( 'pa_wifi' );
    return ob_get_clean();
}
add_shortcode( 'wifi', 'display_wifi' );

function display_kit_montaj( $atts ) {
    ob_start();
    display_feature_labels( 'pa_kit-montaj' );
    return ob_get_clean();
}
add_shortcode( 'kit_montaj', 'display_kit_montaj' );

function display_montaj( $atts ) {
    ob_start();
    display_feature_labels( 'pa_montaj' );
    return ob_get_clean();
}
add_shortcode( 'montaj', 'display_montaj' );
