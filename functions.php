add_action('woocommerce_update_product', 'custom_product_category_updater', 10, 2);

function custom_product_category_updater($product_id) {
    // Load product
    $product = wc_get_product($product_id);
    
    // Load product attributes
    $product_attributes = get_post_meta($product_id, '', true);
    
    // Load stock status
    $stock_status = $product->get_stock_status();
    
    // Load product categories
    $product_categories = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'slugs'));
    
    // Get the list of brands
    $brand_list = ['Gree', 'TCL', 'Yamato', 'Midea'];

    // Rule 1
    if ($stock_status === 'instock' 
        && isset($product_attributes['pa_multihidden'][0]) 
        && $product_attributes['pa_multihidden'][0] === 'No' 
        && isset($product_attributes['pa_optim-iarna'][0]) 
        && $product_attributes['pa_optim-iarna'][0] === 'Yes') {
        
        if (!in_array('aer-conditionat-optimizat-pentru-iarna', $product_categories)) {
            wp_set_post_terms($product_id, ['aer-conditionat-optimizat-pentru-iarna'], 'product_cat', true);
        }
    }

    // Rule 2
    if ($stock_status === 'outofstock' 
        || (isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'Yes') 
        || (isset($product_attributes['pa_optim-iarna'][0]) && $product_attributes['pa_optim-iarna'][0] === 'No')) {
        
        if (in_array('aer-conditionat-optimizat-pentru-iarna', $product_categories)) {
            wp_remove_object_terms($product_id, 'aer-conditionat-optimizat-pentru-iarna', 'product_cat');
        }
    }
	
    // Rule 3
    if ($stock_status === 'instock' && isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'No' && isset($product_attributes['pa_c-energ-mod-r'][0]) && $product_attributes['pa_c-energ-mod-r'][0] === 'A+++') {
        if (!in_array('aer-conditionat-recomandat-pentru-racire', $product_categories)) {
            wp_set_post_terms($product_id, ['aer-conditionat-recomandat-pentru-racire'], 'product_cat', true);
        }
    }

    // Rule 4
    if ($stock_status === 'outofstock' || (isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'Yes') || (isset($product_attributes['pa_c-energ-mod-r'][0]) && ($product_attributes['pa_c-energ-mod-r'][0] === 'A+' || $product_attributes['pa_c-energ-mod-r'][0] === 'A++'))) {
        if (in_array('aer-conditionat-recomandat-pentru-racire', $product_categories)) {
            wp_remove_object_terms($product_id, 'aer-conditionat-recomandat-pentru-racire', 'product_cat');
        }
    }

    // Rule 5
    if ($stock_status === 'instock' && isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'No' && isset($product_attributes['pa_c-energ-mod-i'][0]) && $product_attributes['pa_c-energ-mod-i'][0] === 'A+++') {
        if (!in_array('aer-conditionat-recomandat-pentru-incalzire', $product_categories)) {
            wp_set_post_terms($product_id, ['aer-conditionat-recomandat-pentru-incalzire'], 'product_cat', true);
        }
    }

    // Rule 6
    if ($stock_status === 'outofstock' || (isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'Yes') || (isset($product_attributes['pa_c-energ-mod-i'][0]) && ($product_attributes['pa_c-energ-mod-i'][0] === 'A+' || $product_attributes['pa_c-energ-mod-i'][0] === 'A++'))) {
        if (in_array('aer-conditionat-recomandat-pentru-incalzire', $product_categories)) {
            wp_remove_object_terms($product_id, 'aer-conditionat-recomandat-pentru-incalzire', 'product_cat');
        }
    }

    // Rule 7
    if ($stock_status === 'instock' && isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'No' && isset($product_attributes['pa_seer'][0]) && floatval($product_attributes['pa_seer'][0]) >= 7.3) {
        if (!in_array('cel-mai-bun-aer-conditionat-racire-dupa-seer', $product_categories)) {
            wp_set_post_terms($product_id, ['cel-mai-bun-aer-conditionat-racire-dupa-seer'], 'product_cat', true);
        }
    }

    // Rule 8
    if ($stock_status === 'outofstock' || (isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'Yes') || (isset($product_attributes['pa_seer'][0]) && floatval($product_attributes['pa_seer'][0]) < 7.3)) {
        if (in_array('cel-mai-bun-aer-conditionat-racire-dupa-seer', $product_categories)) {
            wp_remove_object_terms($product_id, 'cel-mai-bun-aer-conditionat-racire-dupa-seer', 'product_cat');
        }
    }

// Rule 9
    if ($stock_status === 'instock' && isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'No' && isset($product_attributes['pa_scop'][0]) && floatval($product_attributes['pa_scop'][0]) >= 5.1) {
        if (!in_array('cel-mai-bun-aer-conditionat-incalzire-dupa-scop', $product_categories)) {
            wp_set_post_terms($product_id, ['cel-mai-bun-aer-conditionat-incalzire-dupa-scop'], 'product_cat', true);
        }
    }

    // Rule 10
    if ($stock_status === 'outofstock' || (isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'Yes') || (isset($product_attributes['pa_scop'][0]) && floatval($product_attributes['pa_scop'][0]) < 5.1)) {
        if (in_array('cel-mai-bun-aer-conditionat-incalzire-dupa-scop', $product_categories)) {
            wp_remove_object_terms($product_id, 'cel-mai-bun-aer-conditionat-incalzire-dupa-scop', 'product_cat');
        }
    }
	
// Rule 11
if ($stock_status === 'instock' && isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'No' && in_array($product_attributes['pa_marca'][0], ['Gree', 'Midea', 'Yamato', 'TCL']) && isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) == 9000) {
    if (!in_array('aer-conditionat-9000-btu-stoc', $product_categories)) {
        wp_set_post_terms($product_id, ['aer-conditionat-9000-btu-stoc'], 'product_cat', true);
    }
}

// Rule 12
if ($stock_status === 'outofstock' || (isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'Yes') || !in_array($product_attributes['pa_marca'][0], ['Gree', 'Midea', 'Yamato', 'TCL']) || (isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) != 9000)) {
    if (in_array('aer-conditionat-9000-btu-stoc', $product_categories)) {
        wp_remove_object_terms($product_id, 'aer-conditionat-9000-btu-stoc', 'product_cat');
    }
}

// Rule 13
if ($stock_status === 'instock' && isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'No' && in_array($product_attributes['pa_marca'][0], ['Gree', 'Midea', 'Yamato', 'TCL']) && isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) == 12000) {
    if (!in_array('aer-conditionat-12000-btu-stoc', $product_categories)) {
        wp_set_post_terms($product_id, ['aer-conditionat-12000-btu-stoc'], 'product_cat', true);
    }
}

// Rule 14
if ($stock_status === 'outofstock' || (isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'Yes') || !in_array($product_attributes['pa_marca'][0], ['Gree', 'Midea', 'Yamato', 'TCL']) || (isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) != 12000)) {
    if (in_array('aer-conditionat-12000-btu-stoc', $product_categories)) {
        wp_remove_object_terms($product_id, 'aer-conditionat-12000-btu-stoc', 'product_cat');
    }
}

// Rule 15
if ($stock_status === 'instock' && isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'No' && in_array($product_attributes['pa_marca'][0], ['Gree', 'Midea', 'Yamato', 'TCL']) && isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) == 18000) {
    if (!in_array('aer-conditionat-18000-btu-stoc', $product_categories)) {
        wp_set_post_terms($product_id, ['aer-conditionat-18000-btu-stoc'], 'product_cat', true);
    }
}

// Rule 16
if ($stock_status === 'outofstock' || (isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'Yes') || !in_array($product_attributes['pa_marca'][0], ['Gree', 'Midea', 'Yamato', 'TCL']) || (isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) != 18000)) {
    if (in_array('aer-conditionat-18000-btu-stoc', $product_categories)) {
        wp_remove_object_terms($product_id, 'aer-conditionat-18000-btu-stoc', 'product_cat');
    }
}

// Rule 17
if ($stock_status === 'instock' && isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'No' && in_array($product_attributes['pa_marca'][0], ['Gree', 'Midea', 'Yamato', 'TCL']) && isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) == 24000) {
    if (!in_array('aer-conditionat-24000-btu-stoc', $product_categories)) {
        wp_set_post_terms($product_id, ['aer-conditionat-24000-btu-stoc'], 'product_cat', true);
    }
}

// Rule 18
if ($stock_status === 'outofstock' || (isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'Yes') || !in_array($product_attributes['pa_marca'][0], ['Gree', 'Midea', 'Yamato', 'TCL']) || (isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) != 24000)) {
    if (in_array('aer-conditionat-24000-btu-stoc', $product_categories)) {
        wp_remove_object_terms($product_id, 'aer-conditionat-24000-btu-stoc', 'product_cat');
    }
}

// Rule 19
if ($stock_status === 'instock' && isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'No' && $product_attributes['pa_marca'][0] == 'Gree' && isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) == 9000) {
    if (!in_array('aer-conditionat-gree-9000-btu-stoc', $product_categories)) {
        wp_set_post_terms($product_id, ['aer-conditionat-gree-9000-btu-stoc'], 'product_cat', true);
    }
}


// Rule 20
if ($stock_status === 'outofstock' || (isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'Yes') || $product_attributes['pa_marca'][0] != 'Gree' || (isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) != 9000)) {
    if (in_array('aer-conditionat-gree-9000-btu-stoc', $product_categories)) {
        wp_remove_object_terms($product_id, 'aer-conditionat-gree-9000-btu-stoc', 'product_cat');
    }
}

// Rule 21
if ($stock_status === 'instock' && isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'No' && $product_attributes['pa_marca'][0] == 'Gree' && isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) == 12000) {
    if (!in_array('aer-conditionat-gree-12000-btu-stoc', $product_categories)) {
        wp_set_post_terms($product_id, ['aer-conditionat-gree-12000-btu-stoc'], 'product_cat', true);
    }
}

// Rule 22
if ($stock_status === 'outofstock' || (isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'Yes') || $product_attributes['pa_marca'][0] != 'Gree' || (isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) != 12000)) {
    if (in_array('aer-conditionat-gree-12000-btu-stoc', $product_categories)) {
        wp_remove_object_terms($product_id, 'aer-conditionat-gree-12000-btu-stoc', 'product_cat');
    }
}

// Rule 23
if ($stock_status === 'instock' && isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'No' && $product_attributes['pa_marca'][0] == 'Gree' && isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) == 18000) {
    if (!in_array('aer-conditionat-gree-18000-btu-stoc', $product_categories)) {
        wp_set_post_terms($product_id, ['aer-conditionat-gree-18000-btu-stoc'], 'product_cat', true);
    }
}

// Rule 24
if ($stock_status === 'outofstock' || (isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'Yes') || $product_attributes['pa_marca'][0] != 'Gree' || (isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) != 18000)) {
    if (in_array('aer-conditionat-gree-18000-btu-stoc', $product_categories)) {
        wp_remove_object_terms($product_id, 'aer-conditionat-gree-18000-btu-stoc', 'product_cat');
    }
}

// Rule 25
if ($stock_status === 'instock' && isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'No' && $product_attributes['pa_marca'][0] == 'Gree' && isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) == 24000) {
    if (!in_array('aer-conditionat-gree-24000-btu-stoc', $product_categories)) {
        wp_set_post_terms($product_id, ['aer-conditionat-gree-24000-btu-stoc'], 'product_cat', true);
    }
}

// Rule 26
if ($stock_status === 'outofstock' || (isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'Yes') || $product_attributes['pa_marca'][0] != 'Gree' || (isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) != 24000)) {
    if (in_array('aer-conditionat-gree-24000-btu-stoc', $product_categories)) {
        wp_remove_object_terms($product_id, 'aer-conditionat-gree-24000-btu-stoc', 'product_cat');
    }
}

// Rule 27
if ($stock_status === 'instock' && isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'No' && $product_attributes['pa_marca'][0] == 'Midea' && isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) == 9000) {
    if (!in_array('aer-conditionat-midea-9000-btu-stoc', $product_categories)) {
        wp_set_post_terms($product_id, ['aer-conditionat-midea-9000-btu-stoc'], 'product_cat', true);
    }
}

// Rule 28
if ($stock_status === 'outofstock' || (isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'Yes') || $product_attributes['pa_marca'][0] != 'Midea' || (isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) != 9000)) {
    if (in_array('aer-conditionat-midea-9000-btu-stoc', $product_categories)) {
        wp_remove_object_terms($product_id, 'aer-conditionat-midea-9000-btu-stoc', 'product_cat');
    }
}

// Rule 29
if ($stock_status === 'instock' && isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'No' && $product_attributes['pa_marca'][0] == 'Midea' && isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) == 12000) {
    if (!in_array('aer-conditionat-midea-12000-btu-stoc', $product_categories)) {
        wp_set_post_terms($product_id, ['aer-conditionat-midea-12000-btu-stoc'], 'product_cat', true);
    }
}


// Rule 30
if ($stock_status === 'outofstock' || (isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'Yes') || !in_array($product_attributes['pa_marca'][0], ['Gree', 'Midea', 'Yamato', 'TCL']) || (isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) != 12000)) {
    if (in_array('aer-conditionat-midea-12000-btu-stoc', $product_categories)) {
        wp_remove_object_terms($product_id, 'aer-conditionat-midea-12000-btu-stoc', 'product_cat');
    }
}

// Rule 31
if ($stock_status === 'instock' && isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'No' && $product_attributes['pa_marca'][0] == 'Midea' && isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) == 18000) {
    if (!in_array('aer-conditionat-midea-18000-btu-stoc', $product_categories)) {
        wp_set_post_terms($product_id, ['aer-conditionat-midea-18000-btu-stoc'], 'product_cat', true);
    }
}

// Rule 32
if ($stock_status === 'outofstock' || (isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'Yes') || !in_array($product_attributes['pa_marca'][0], ['Gree', 'Midea', 'Yamato', 'TCL']) || (isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) != 18000)) {
    if (in_array('aer-conditionat-midea-18000-btu-stoc', $product_categories)) {
        wp_remove_object_terms($product_id, 'aer-conditionat-midea-18000-btu-stoc', 'product_cat');
    }
}

// Rule 33
if ($stock_status === 'instock' && isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'No' && $product_attributes['pa_marca'][0] == 'Midea' && isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) == 24000) {
    if (!in_array('aer-conditionat-midea-24000-btu-stoc', $product_categories)) {
        wp_set_post_terms($product_id, ['aer-conditionat-midea-24000-btu-stoc'], 'product_cat', true);
    }
}

// Rule 34
if ($stock_status === 'outofstock' || (isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'Yes') || !in_array($product_attributes['pa_marca'][0], ['Gree', 'Midea', 'Yamato', 'TCL']) || (isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) != 24000)) {
    if (in_array('aer-conditionat-midea-24000-btu-stoc', $product_categories)) {
        wp_remove_object_terms($product_id, 'aer-conditionat-midea-24000-btu-stoc', 'product_cat');
    }
}

// Rule 35
if ($stock_status === 'instock' && isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'No' && $product_attributes['pa_marca'][0] == 'Yamato' && isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) == 9000) {
    if (!in_array('aer-conditionat-yamato-9000-btu-stoc', $product_categories)) {
        wp_set_post_terms($product_id, ['aer-conditionat-yamato-9000-btu-stoc'], 'product_cat', true);
    }
}

// Rule 36
if ($stock_status === 'outofstock' || (isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'Yes') || !in_array($product_attributes['pa_marca'][0], ['Gree', 'Midea', 'Yamato', 'TCL']) || (isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) != 9000)) {
    if (in_array('aer-conditionat-yamato-9000-btu-stoc', $product_categories)) {
        wp_remove_object_terms($product_id, 'aer-conditionat-yamato-9000-btu-stoc', 'product_cat');
    }
}

// Rule 37
if ($stock_status === 'instock' && isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'No' && $product_attributes['pa_marca'][0] == 'Yamato' && isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) == 12000) {
    if (!in_array('aer-conditionat-yamato-12000-btu-stoc', $product_categories)) {
        wp_set_post_terms($product_id, ['aer-conditionat-yamato-12000-btu-stoc'], 'product_cat', true);
    }
}

// Rule 38
if ($stock_status === 'outofstock' || (isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'Yes') || !in_array($product_attributes['pa_marca'][0], ['Gree', 'Midea', 'Yamato', 'TCL']) || (isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) != 12000)) {
    if (in_array('aer-conditionat-yamato-12000-btu-stoc', $product_categories)) {
        wp_remove_object_terms($product_id, 'aer-conditionat-yamato-12000-btu-stoc', 'product_cat');
    }
}

// Rule 39
if ($stock_status === 'instock' && isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'No' && $product_attributes['pa_marca'][0] == 'Yamato' && isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) == 18000) {
    if (!in_array('aer-conditionat-yamato-18000-btu-stoc', $product_categories)) {
        wp_set_post_terms($product_id, ['aer-conditionat-yamato-18000-btu-stoc'], 'product_cat', true);
    }
}


// Rule 40
if ($stock_status === 'outofstock' || (isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'Yes') || !in_array($product_attributes['pa_marca'][0], ['Gree', 'Midea', 'Yamato', 'TCL']) || (isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) != 18000)) {
    if (in_array('aer-conditionat-yamato-18000-btu-stoc', $product_categories)) {
        wp_remove_object_terms($product_id, 'aer-conditionat-yamato-18000-btu-stoc', 'product_cat');
    }
}

// Rule 41
if ($stock_status === 'instock' && isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'No' && $product_attributes['pa_marca'][0] == 'Yamato' && isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) == 24000) {
    if (!in_array('aer-conditionat-yamato-24000-btu-stoc', $product_categories)) {
        wp_set_post_terms($product_id, ['aer-conditionat-yamato-24000-btu-stoc'], 'product_cat', true);
    }
}

// Rule 42
if ($stock_status === 'outofstock' || (isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'Yes') || !in_array($product_attributes['pa_marca'][0], ['Gree', 'Midea', 'Yamato', 'TCL']) || (isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) != 24000)) {
    if (in_array('aer-conditionat-yamato-24000-btu-stoc', $product_categories)) {
        wp_remove_object_terms($product_id, 'aer-conditionat-yamato-24000-btu-stoc', 'product_cat');
    }
}

// Rule 43
if ($stock_status === 'instock' && isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'No' && $product_attributes['pa_marca'][0] == 'TCL' && isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) == 9000) {
    if (!in_array('aer-conditionat-tcl-9000-btu-stoc', $product_categories)) {
        wp_set_post_terms($product_id, ['aer-conditionat-tcl-9000-btu-stoc'], 'product_cat', true);
    }
}

// Rule 44
if ($stock_status === 'outofstock' || (isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'Yes') || !in_array($product_attributes['pa_marca'][0], ['Gree', 'Midea', 'Yamato', 'TCL']) || (isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) != 9000)) {
    if (in_array('aer-conditionat-tcl-9000-btu-stoc', $product_categories)) {
        wp_remove_object_terms($product_id, 'aer-conditionat-tcl-9000-btu-stoc', 'product_cat');
    }
}

// Rule 45
if ($stock_status === 'instock' && isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'No' && $product_attributes['pa_marca'][0] == 'TCL' && isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) == 12000) {
    if (!in_array('aer-conditionat-tcl-12000-btu-stoc', $product_categories)) {
        wp_set_post_terms($product_id, ['aer-conditionat-tcl-12000-btu-stoc'], 'product_cat', true);
    }
}

// Rule 46
if ($stock_status === 'outofstock' || (isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'Yes') || !in_array($product_attributes['pa_marca'][0], ['Gree', 'Midea', 'Yamato', 'TCL']) || (isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) != 12000)) {
    if (in_array('aer-conditionat-tcl-12000-btu-stoc', $product_categories)) {
        wp_remove_object_terms($product_id, 'aer-conditionat-tcl-12000-btu-stoc', 'product_cat');
    }
}

// Rule 47
if ($stock_status === 'instock' && isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'No' && $product_attributes['pa_marca'][0] == 'TCL' && isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) == 18000) {
    if (!in_array('aer-conditionat-tcl-18000-btu-stoc', $product_categories)) {
        wp_set_post_terms($product_id, ['aer-conditionat-tcl-18000-btu-stoc'], 'product_cat', true);
    }
}

// Rule 48
if ($stock_status === 'outofstock' || (isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'Yes') || !in_array($product_attributes['pa_marca'][0], ['Gree', 'Midea', 'Yamato', 'TCL']) || (isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) != 18000)) {
    if (in_array('aer-conditionat-tcl-18000-btu-stoc', $product_categories)) {
        wp_remove_object_terms($product_id, 'aer-conditionat-tcl-18000-btu-stoc', 'product_cat');
    }
}

// Rule 49
if ($stock_status === 'instock' && isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'No' && $product_attributes['pa_marca'][0] == 'TCL' && isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) == 24000) {
    if (!in_array('aer-conditionat-tcl-24000-btu-stoc', $product_categories)) {
        wp_set_post_terms($product_id, ['aer-conditionat-tcl-24000-btu-stoc'], 'product_cat', true);
    }
}

// Rule 50
if ($stock_status === 'outofstock' || (isset($product_attributes['pa_multihidden'][0]) && $product_attributes['pa_multihidden'][0] === 'Yes') || !in_array($product_attributes['pa_marca'][0], ['Gree', 'Midea', 'Yamato', 'TCL']) || (isset($product_attributes['pa_capgen'][0]) && intval($product_attributes['pa_capgen'][0]) != 24000)) {
    if (in_array('aer-conditionat-tcl-24000-btu-stoc', $product_categories)) {
        wp_remove_object_terms($product_id, 'aer-conditionat-tcl-24000-btu-stoc', 'product_cat');
    }
}

// Rule 51
if ($stock_status === 'instock' && $product->is_on_sale()) {
    if (!in_array('reduceri-aer-conditionat', $product_categories)) {
        wp_set_post_terms($product_id, ['reduceri-aer-conditionat'], 'product_cat', true);
    }
}

// Rule 52
if ($stock_status === 'outofstock' || !$product->is_on_sale()) {
    if (in_array('reduceri-aer-conditionat', $product_categories)) {
        wp_remove_object_terms($product_id, 'reduceri-aer-conditionat', 'product_cat');
    }
}

}

// Manual update function
function manual_update_special_categories() {
    // Here you should put your manual update logic
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1
    );
    $products = new WP_Query($args);

    if ($products->have_posts()): while ($products->have_posts()): $products->the_post();
        custom_product_category_updater(get_the_ID());
    endwhile; endif; 

    wp_reset_query();   
}

// Admin page setup
function update_categories_page() {
    // Check if the button is clicked
    if (isset($_POST['update_categories'])) {
        manual_update_special_categories();
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

add_action('admin_menu', 'register_my_custom_menu_page');

function register_my_custom_menu_page() {
    add_menu_page(
        'Update Categories', // page title
        'Update Categories', // menu title
        'manage_options', // capability
        'update_categories', // menu slug
        'update_categories_page' // callable function
    );
}
