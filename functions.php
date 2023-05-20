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
            'text' => 'Funcție Wi-Fi',
        ),
        'pa_kit-montaj' => array(
            'icon' => 'fas fa-tools',
            'text' => 'Kit montaj',
        ),
        'pa_montaj' => array(
            'icon' => 'fas fa-hammer',
            'text' => 'Montaj',
        ),
        'pa_transp-gratis' => array(
            'icon' => 'fas fa-truck',
            'text' => 'Transport Gratuit*',
        ),
        'pa_optim-iarna' => array(
            'icon' => '',
            'text' => '',
        )
    );

    $attributes = array_filter($attributes, function($key) use ($attr) {
        return $attr === '' || $key === $attr;
    }, ARRAY_FILTER_USE_KEY);

    foreach ($attributes as $slug => $data) {
        $feature = $product->get_attribute($slug);

        if ($feature) {
            $class = (strtolower($feature) === 'yes' || strtolower($feature) === 'da') ? 'yes' : 'no';
            $output = '<span class="feature-label ' . $class . '"><i class="' . $data['icon'] . '"></i> ' . $data['text'] . '</span>';

            if ($return) {
                return $output;
            } else {
                echo $output;
            }
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

function display_stock( $atts ) {
    $product = wc_get_product();
    if ( ! $product ) {
        return '';
    }
    
    $stock_quantity = $product->get_stock_quantity();
    $message = '';
    $icon = '';
    $color = '';
    
    if ($stock_quantity > 5) {
        $message = 'Stoc suficient';
        $icon = 'fas fa-thumbs-up';
        $color = 'green';
    } elseif ($stock_quantity > 0 && $stock_quantity <= 5) {
        $message = 'Stoc limitat';
        $icon = 'fas fa-exclamation-triangle';
        $color = 'orange';
    } elseif ($stock_quantity == 0) {
        $message = 'Produs indisponibil momentan';
        $color = 'grey';
    } 

    ob_start();
    ?>
    <div class="stock-status-container">
        <i class="<?php echo $icon; ?> stock-icon" style="color: <?php echo $color; ?>;"></i>
        <h4 class="stock-status-text"><?php echo $message; ?></h4>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('display_stock', 'display_stock');

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

add_shortcode('category_feature_labels', 'display_category_feature_labels_shortcode');
function display_category_feature_labels_shortcode($atts) {
    ob_start();
    display_other_feature_labels();
    return ob_get_clean();
}

add_action( 'elementor/query/pa_capgen', 'custom_elementor_loop_sorting_capgen', 10, 2 );
add_action( 'elementor/query/pa_seer', 'custom_elementor_loop_sorting_seer', 10, 2 );
add_action( 'elementor/query/pa_scop', 'custom_elementor_loop_sorting_scop', 10, 2 );

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

function youtube_embed_shortcode() {
    global $post;
    $youtube_url = get_post_meta($post->ID, 'youtube_video_url', true);

    if ($youtube_url) {
        return wp_oembed_get($youtube_url);
    }

    return '';
}
add_shortcode('youtube_embed', 'youtube_embed_shortcode');


function auto_update_sale_and_attribute_categories() {
    // Get all products
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => array('aer-conditionat-yamato', 'aer-conditionat-gree', 'aer-conditionat-midea'),
                'operator' => 'IN',
            ),
        ),
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
    }

    wp_reset_query();
}

function display_optim_iarna_table() {
    global $product;
    $feature = $product->get_attribute('pa_optim-iarna');

    if(strtolower($feature) === 'yes' || strtolower($feature) === 'da') {
        ob_start();
        ?>
        <table class="optim-iarna-table">
            <tr>
                <td class="image-cell"><img src="https://dev.climazone.ro/wp-content/uploads/2023/05/aer-conditionat-pentru-iarna-icon.webp" alt="feature_image"></td>
                <td class="text-cell">
                    <h4 class="h3-optim-iarna">Optimizat pentru răcire și încălzire*</h4>
                    <span class="p-optim-iarna">Conform specificațiilor producătorului</span>
                </td>
            </tr>
        </table>
        <?php
        return ob_get_clean();
    }
    return '';
}
add_shortcode('optim_iarna', 'display_optim_iarna_table');

function display_discount_percentage( $atts ) {
    $product = wc_get_product();
    if ( ! $product ) {
        return '';
    }

    if ( $product->is_on_sale() ) {
        $regular_price = (float) $product->get_regular_price();
        $sale_price = (float) $product->get_sale_price();
        $percentage =  round(100 - ($sale_price / $regular_price * 100));

        ob_start();
        ?>
        <table class="discount-percentage-table">
            <tr>
                <td class="discount-icon-cell"><i class="fas fa-tag"></i></td>
                <td class="discount-text-cell">
                    <h3 class="h3-discount-percentage">Produs redus cu <?php echo $percentage; ?>%*</h3>
                    <span class="p-discount-percentage">*De la prețul recomandat de producător</span>
                </td>
            </tr>
        </table>
        <?php
        return ob_get_clean();
    }

    return '';
}
add_shortcode('discount_percentage', 'display_discount_percentage');

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

add_action('woocommerce_before_shop_loop_item', 'display_stock_and_transport_labels', 9);

function display_stock_and_transport_labels() {
    global $product;

    // Stock status
    $stock_quantity = $product->get_stock_quantity();
    $stock_status = '';
    $icon = '';
    if ($stock_quantity > 5) {
        $stock_status = 'Stoc suficient';
        $icon = 'fas fa-check-circle';
        $color = 'green';
    } elseif ($stock_quantity > 0 && $stock_quantity <= 5) {
        $stock_status = 'Stoc limitat';
        $icon = 'fas fa-exclamation-circle'; // Changed the icon
        $color = 'orange';
    } else {
        $stock_status = 'Indisponibil';
        $icon = 'fas fa-times-circle';
        $color = 'grey';
    }
    echo '<div class="stock-status-labels product-carousel-loop" style="color:' . $color . ';"><i class="' . $icon . '"></i> ' . $stock_status . '</div>';

    // Transport attribute
    $feature = $product->get_attribute('pa_transp-gratis');
    $text = (strtolower($feature) === 'yes' || strtolower($feature) === 'da') ? 'Transport gratuit' : 'Transport nu este gratuit';
    $icon = (strtolower($feature) === 'yes' || strtolower($feature) === 'da') ? 'fas fa-truck' : 'fas fa-truck-loading';
    $color = (strtolower($feature) === 'yes' || strtolower($feature) === 'da') ? 'green' : 'red';
    echo '<div class="transport-label product-carousel-loop" style="color:' . $color . ';"><i class="' . $icon . '"></i> ' . $text . '</div>';
}

function display_stock_and_transport_labels_shortcode($atts) {
    ob_start();
    display_stock_and_transport_labels();
    return ob_get_clean();
}
add_shortcode('stock_transport_labels', 'display_stock_and_transport_labels_shortcode');

add_action('woocommerce_before_shop_loop_item', 'display_other_feature_labels', 10);
function display_other_feature_labels() {
    global $product;

    // Product attributes
    $attributes = array(
        'pa_optim-iarna' => array(
            'icon_yes' => array('fa fa-snowflake-o' => 'blue', 'fa fa-sun' => 'orange'),
            'icon_no' => array('fa fa-snowflake-o' => 'blue')
        ),
        'pa_wifi' => array(
            'icon_yes' => array('fa fa-wifi' => 'green'),
            'icon_no' => array('fa fa-wifi' => 'red')
        ),
        'pa_kit-montaj' => array(
            'icon_yes' => array('fa fa-plug' => 'green'),
            'icon_no' => array('fa fa-plug' => 'red')
        ),
        'pa_montaj' => array(
            'icon_yes' => array('fa fa-wrench' => 'green'),
            'icon_no' => array('fa fa-wrench' => 'red')
        ),
    );

    echo '<div class="other-feature-labels">';
    foreach ($attributes as $slug => $data) {
        $feature = $product->get_attribute($slug);
        $icons = (strtolower($feature) === 'yes' || strtolower($feature) === 'da') ? $data['icon_yes'] : $data['icon_no'];

        $i = 0;
        foreach ($icons as $icon => $color) {
            echo '<div class="feature-label product-carousel-loop icon-size"><i class="' . $icon . '" style="color:' . $color . ';"></i>'.($slug === 'pa_optim-iarna' && $i < count($icons) - 1 ? '<span class="plus-sign">+</span>' : '').'</div>';
            $i++;
        }
    }
    echo '</div>';
}

function display_other_feature_labels_shortcode($atts) {
    ob_start();
    display_other_feature_labels();
    return ob_get_clean();
}
add_shortcode('other_feature_labels', 'display_other_feature_labels_shortcode');
