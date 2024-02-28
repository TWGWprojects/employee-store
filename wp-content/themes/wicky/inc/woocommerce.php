<?php
add_action( 'init', 'wicky_button_product' );
add_action( 'init', 'wicky_woocommerce_single_product_summary' );
add_filter( 'wicky_custom_category', 'woocommerce_maybe_show_product_subcategories' );
function wicky_button_product(){
	$wicky_settings = wicky_global_settings();
	//Button List Product
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
	//Cart
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');
		add_action('woocommerce_after_shop_loop_item', 'wicky_woocommerce_template_loop_add_to_cart', 15 );
	//Whishlist
	if(isset($wicky_settings['product-wishlist']) && $wicky_settings['product-wishlist'] && class_exists( 'YITH_WCWL' ) ){
		add_action('woocommerce_after_shop_loop_item', 'wicky_add_loop_wishlist_link', 35 );	
	}
	//Compare
	if(isset($wicky_settings['product-compare']) && $wicky_settings['product-compare'] && class_exists( 'YITH_WOOCOMPARE' ) ){
		add_action('woocommerce_after_shop_loop_item', 'wicky_add_loop_compare_link', 15 );
	}	
	//Quickview
		add_action('woocommerce_after_shop_loop_item', 'wicky_quickview', 20 );
}
function wicky_woocommerce_single_product_summary(){
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
	remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash');	
	add_action( 'woocommerce_after_add_to_cart_button', 'wicky_add_loop_wishlist_link', 30 );
	add_action( 'woocommerce_after_add_to_cart_button', 'wicky_add_loop_compare_link', 35 );
	add_action( 'woocommerce_single_product_summary', 'wicky_add_social', 45 );
}
function wicky_update_total_price() {
	global $woocommerce;
	$data = array(
		'total_price' => $woocommerce->cart->get_cart_total(),
	);
	wp_send_json($data);
}	
add_action( 'wp_ajax_wicky_update_total_price', 'wicky_update_total_price' );
add_action( 'wp_ajax_nopriv_wicky_update_total_price', 'wicky_update_total_price' );	
/* Ajax Search */
add_action( 'wp_ajax_wicky_search_products_ajax', 'wicky_search_products_ajax' );
add_action( 'wp_ajax_nopriv_wicky_search_products_ajax', 'wicky_search_products_ajax' );
function wicky_search_products_ajax(){
	$character = (isset($_GET['character']) && $_GET['character'] ) ? $_GET['character'] : '';
	$limit = (isset($_GET['limit']) && $_GET['limit'] ) ? $_GET['limit'] : 5;
	$category = (isset($_GET['category']) && $_GET['category'] ) ? $_GET['category'] : "";
	$args = array(
		'post_type' 			=> 'product',
		'post_status'    		=> 'publish',
		'ignore_sticky_posts'   => 1,	  
		'posts_per_page'		=> $limit,
		'meta_query' => array(
			array(
				'key' => '_sku',
				'value' => $character,
				'compare' => 'LIKE'
			)
		)
	);
	
	if($category){
		$args['tax_query'] = array(
			array(
				'taxonomy'  => 'product_cat',
				'field'     => 'slug',
				'terms'     => $category ));
	}
	$list = new WP_Query( $args );
	
	//Check if SKU query count


	if($list->post_count == 0){
		$args = array(
			'post_type' 			=> 'product',
			'post_status'    		=> 'publish',
			'ignore_sticky_posts'   => 1,	
			's' 					=> $character,  
			'posts_per_page'		=> $limit,
			
		);
	}

	$list = new WP_Query( $args );


	//Query for SCC meta field

	if($list->post_count == 0){
		$args = array(
			'post_type' 			=> 'product',
			'post_status'    		=> 'publish',
			'ignore_sticky_posts'   => 1,	  
			'posts_per_page'		=> $limit,
			'meta_query' => array(
				array(
					'key' => 'sccdesc',
					'value' => $character,
					'compare' => 'LIKE'
				)
			)
			
		);
	}

	$list = new WP_Query( $args );

	$json = array();
	if ($list->have_posts()) {
		while($list->have_posts()): $list->the_post();
		global $product, $post;
		
		$image = wp_get_attachment_image_src( get_post_thumbnail_id( $product->id ), 'shop_catalog' );
		
		if(empty($image)){
			$image[0] = get_site_url().'/wp-content/uploads/2024/01/wine.png';
		}


		$json[] = array(
			'product_id' => $product->id,
			'name'       => $product->get_title(),		
			'image'		 =>  $image[0],
			'link'		 =>  get_permalink( $product->id ),
			'price'      =>  $product->get_price_html(),
			'custom_title' => get_custom_title($product),

		);			
		endwhile;
	}
	die (json_encode($json));
}
function wicky_woocommerce_template_loop_add_to_cart( $args = array() ) {
	global $product;
	if ( $product ) {
		$defaults = array(
			'quantity' => 1,
			'class'    => implode( ' ', array_filter( array(
					'button',
					'product_type_' . $product->get_type(),
					$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : 'read_more',
					$product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
			) ) ),
		);
		$args = apply_filters( 'woocommerce_loop_add_to_cart_args', wp_parse_args( $args, $defaults ), $product );
		wc_get_template( 'loop/add-to-cart.php', $args );
	}
}	

// Added custom code to customize for product listing description 
function wicky_add_excerpt_in_product_archives() {
	global $post,$product;

	$product_attributes = $product->get_attributes();
	$product_sku = $product->get_sku();
	$pro_id = $product->get_id();	

	if(!empty($product_attributes)){

		$brands_name = array();
		$pa_varietal_name = array();
		$pa_tier_description_name = array();
		$pa_size_name = array();
		$pa_pack_description_name = array();
		$pa_mastercode_name = array();

		
		// Brand name list
		$brand_ids = $product_attributes['pa_brand']['options'];
		if(!empty($brand_ids)){
			foreach($brand_ids as $brand_id){
				$brands_name[] = get_term( $brand_id )->name;
			}
			$brand_name_str =  implode(', ', $brands_name);
		}

		// Varietal flavor
		$pa_varietal_ids = $product_attributes['pa_varietal-flavor']['options'];
		if(!empty($pa_varietal_ids)){
			foreach($pa_varietal_ids as $pa_varietal_id){
				$pa_varietal_name[] = get_term( $pa_varietal_id )->name;
			}
			$pa_varietal_name_str =  implode(', ', $pa_varietal_name);
		}

		// Tier Description
		$pa_tier_description_ids = $product_attributes['pa_tier-description']['options'];
		if(!empty($pa_tier_description_ids)){
			foreach($pa_tier_description_ids as $pa_tier_description_id){
				$pa_tier_description_name[] = get_term( $pa_tier_description_id )->name;
			}
			$pa_tier_description_name_str =  implode(', ', $pa_tier_description_name);
		}

		// Sizes
		$pa_size_ids = $product_attributes['pa_size']['options'];
		if(!empty($pa_size_ids)){
			foreach($pa_size_ids as $pa_size_id){
				$pa_size_name[] = get_term( $pa_size_id )->name;
			}
			$pa_size_name_str =  implode(', ', $pa_size_name);
		} 

		// Pack description
		$pa_pack_description_ids = $product_attributes['pa_pack-description']['options'];
		if(!empty($pa_pack_description_ids)){
			foreach($pa_pack_description_ids as $pa_pack_description_id){
				$pa_pack_description_name[] = get_term( $pa_pack_description_id )->name;
			}
			$pa_pack_description_name_str =  implode(', ', $pa_pack_description_name);
		}

		// Master code
		$pa_mastercode_ids = $product_attributes['pa_master-code']['options'];
		if(!empty($pa_mastercode_ids)){
			foreach($pa_mastercode_ids as $pa_mastercode_id){
				$pa_mastercode_name[] = get_term( $pa_mastercode_id )->name;
			}
			$pa_mastercode_name_str =  implode(', ', $pa_mastercode_name);
		}

		$product_details = $product->get_data();


		// SSC code
		$SCCDesc = '';
		$sccdesc = get_post_meta($pro_id,'sccdesc',true);

		
			
	echo '<div class="customized-desc item-description item-description2">'.esc_html($brand_name_str.' '.$pa_varietal_name_str.' '.$pa_tier_description_name_str.' '.$pa_size_name_str.' '.$pa_pack_description_name_str.' '.$product_sku.' '.$product_details['description'].' '.$sccdesc).'</div>';

	}
}	
//End -  Added custom code to customize for product listing description 

/*add second thumbnail loop product*/
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
add_action( 'woocommerce_before_shop_loop_item_title', 'wicky_woocommerce_template_loop_product_thumbnail', 10 );
function wicky_product_thumbnail( $size = 'woocommerce_thumbnail', $placeholder_width = 0, $placeholder_height = 0  ) {
	global $wicky_settings,$product;
	$html = '';
	$id = get_the_ID();
	$gallery = get_post_meta($id, '_product_image_gallery', true);
	$attachment_image = '';
	if(!empty($gallery)) {
		$gallery = explode(',', $gallery);
		$first_image_id = $gallery[0];
		$attachment_image = wp_get_attachment_image($first_image_id , $size, false, array('class' => 'hover-image back'));
	}
		$image = wp_get_attachment_image_src( get_post_thumbnail_id( $product->get_id() ), '' );
		if ( has_post_thumbnail() ){
			if( $attachment_image && $wicky_settings['category-image-hover']){
				$html .= '<div class="product-thumb-hover">';
				$html .= '<a href="' . get_the_permalink() . '" class="woocommerce-LoopProduct-link">';
				$html .= (get_the_post_thumbnail( $product->get_id(), $size )) ? get_the_post_thumbnail( $product->get_id(), $size ): '<img src="'.get_template_directory_uri().'/images/placeholder.jpg" alt="'. esc_attr__('No thumb', 'wicky').'">';
				if($wicky_settings['category-image-hover']){
					$html .= $attachment_image;
				}
				$html .= '</a>';
				$html .= '</div>';				
			}else{
				$html .= '<a href="' . get_the_permalink() . '" class="woocommerce-LoopProduct-link">';		
				$html .= '<img src="'.get_site_url().'/wp-content/uploads/2024/01/wine.png" alt="'. esc_attr__('No thumb', 'wicky').'">';
				$html .= '</a>';
			}		
		}else{
			$html .= '<a href="' . get_the_permalink() . '" class="woocommerce-LoopProduct-link">';		
			$html .= '<img src="'.get_site_url().'/wp-content/uploads/2024/01/wine.png" alt="'. esc_attr__('No thumb', 'wicky').'">';
			$html .= '</a>';	
		}
	/* quickview */
	return $html;
}
function wicky_woocommerce_template_loop_product_thumbnail(){
	echo wicky_product_thumbnail();
}
function wicky_countdown_woocommerce_template_loop_product_thumbnail(){
	echo wicky_product_thumbnail("shop_single");
}
//Button List Product
/*********QUICK VIEW PRODUCT**********/
function wicky_product_quick_view_scripts() {	
	wp_enqueue_script('wc-add-to-cart-variation');
}
add_action( 'wp_enqueue_scripts', 'wicky_product_quick_view_scripts' );	
function wicky_quickview(){
	global $product;
	$quickview = wicky_get_config('product_quickview'); 
	if( $quickview ) : 
		echo '<span class="product-quickview"><a href="#" data-product_id="'.esc_attr($product->get_id()).'" class="quickview quickview-button quickview-'.esc_attr($product->get_id()).'" >'.apply_filters( 'out_of_stock_add_to_cart_text', 'Quick View' ).' <i class="icon-search"></i>'.'</a></span>';
	endif;
}
add_action("wp_ajax_wicky_quickviewproduct", "wicky_quickviewproduct");
add_action("wp_ajax_nopriv_wicky_quickviewproduct", "wicky_quickviewproduct");
function wicky_quickviewproduct(){
	echo wicky_content_product();exit();
}
function wicky_content_product(){
	$productid = (isset($_REQUEST["product_id"]) && $_REQUEST["product_id"]>0) ? $_REQUEST["product_id"] : 0;
	$query_args = array(
		'post_type'	=> 'product',
		'p'			=> $productid
	);
	$outputraw = $output = '';
	$r = new WP_Query($query_args);
	if($r->have_posts()){ 
		while ($r->have_posts()){ $r->the_post(); setup_postdata($r->post);
			ob_start();
			wc_get_template_part( 'content', 'quickview-product' );
			$outputraw = ob_get_contents();
			ob_end_clean();
		}
	}
	$output = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $outputraw);
	return $output;	
}
//Wish list
function wicky_add_loop_wishlist_link(){	
	if ( in_array( 'yith-woocommerce-wishlist/init.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ){
		echo do_shortcode( "[yith_wcwl_add_to_wishlist]" );
	}
}
//Compare
function wicky_add_loop_compare_link(){
	global $post;
	$product_id = $post->ID;	
	if( class_exists( 'YITH_WOOCOMPARE' ) ){
		echo '<div class="woocommerce product compare-button"><a href="javascript:void(0)" class="compare button" data-product_id="'. esc_attr($product_id) .'" rel="nofollow">'.esc_html__("Compare","wicky").'</a></div>';	
	}	
}
function wicky_add_social() {
	if ( shortcode_exists( 'social_share' ) ) :
		echo '<div class="social-icon">';
			echo '<div class="social-title">' . esc_html__( 'Share:', 'wicky' ) . '</div>';
			echo do_action( 'woocommerce_share' );
			echo do_shortcode( "[social_share]" );
		echo '</div>';
	endif;	
}
function wicky_add_thumb_single_product() {
	echo '<div class="image-thumbnail-list">';
	do_action( 'woocommerce_product_thumbnails' );
	echo '</div>';
}
function wicky_get_class_item_product(){
	$wicky_settings = wicky_global_settings();
	$product_col_large = 12 /(wicky_get_config('product_col_large',4));	
	$product_col_medium = 12 /(wicky_get_config('product_col_medium',3));
	$product_col_sm 	= 12 /(wicky_get_config('product_col_sm',1));
	$class_item_product = 'col-lg-'.$product_col_large.' col-md-'.$product_col_medium.' col-sm-'.$product_col_sm;
	return $class_item_product;
}
function wicky_catalog_perpage(){
	$wicky_settings = wicky_global_settings();
	$query_string = wicky_get_query_string();
	parse_str($query_string, $params);
	$query_string 	= '?'.$query_string;
	$per_page 	=   (isset($wicky_settings['product_count']) && $wicky_settings['product_count'])  ? (int)$wicky_settings['product_count'] : 12;
	$product_count = (isset($params['product_count']) && $params['product_count']) ? ($params['product_count']) : $per_page;
	?>
	<div class="wicky-woocommerce-sort-count">
		<div class="woocommerce-sort-count">
			<span><?php echo esc_html__('Show','wicky'); ?></span>
			<ul class="pwb-dropdown-menu">
				<li data-value="<?php echo esc_attr($per_page); 	?>"<?php if ($product_count == $per_page){?>class="active"<?php } ?>><a href="<?php echo wicky_add_url_parameter($query_string, 'product_count', $per_page); ?>"><?php echo esc_attr($per_page); ?></a></li>
				<li data-value="<?php echo esc_attr($per_page*2); 	?>"<?php if ($product_count == $per_page*2){?>class="active"<?php } ?>><a href="<?php echo wicky_add_url_parameter($query_string, 'product_count', $per_page*2); ?>"><?php echo esc_attr($per_page*2); ?></a></li>
				<li data-value="<?php echo esc_attr($per_page*3); 	?>"<?php if ($product_count == $per_page*3){?>class="active"<?php } ?>><a href="<?php echo wicky_add_url_parameter($query_string, 'product_count', $per_page*3); ?>"><?php echo esc_attr($per_page*3); ?></a></li>
			</ul>
		</div>
	</div>
<?php }	
add_filter('loop_shop_per_page', 'wicky_loop_shop_per_page');
function wicky_loop_shop_per_page() {
	$wicky_settings = wicky_global_settings();
	$query_string = wicky_get_query_string();
	parse_str($query_string, $params);
	$per_page 	=   (isset($wicky_settings['product_count']) && $wicky_settings['product_count'])  ? (int)$wicky_settings['product_count'] : 12;
	$product_count = (isset($params['product_count']) && $params['product_count']) ? ($params['product_count']) : $per_page;
	return $product_count;
}	
function wicky_found_posts(){
	wc_get_template( 'loop/woocommerce-found-posts.php' );
}	
remove_action('woocommerce_before_main_content', 'wicky_woocommerce_breadcrumb', 20);	
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
function wicky_search_form_product(){
	$query_string = wicky_get_query_string();
	parse_str($query_string, $params);
	$category_slug = isset( $params['product_cat'] ) ? $params['product_cat'] : '';
	$terms =	get_terms( 'product_cat', 
	array(  
		'hide_empty' => true,	
		'parent' => 0	
	));
	$class_ajax_search 	= "";	 
	$ajax_search 		= wicky_get_config('show-ajax-search',false);
	$limit_ajax_search 		= wicky_get_config('limit-ajax-search',5);
	if($ajax_search){
		$class_ajax_search = "ajax-search";
	}
	?>
	<form role="search" method="get" class="search-from <?php echo esc_attr($class_ajax_search); ?>" action="<?php echo esc_url(home_url( '/' )); ?>" data-admin="<?php echo admin_url( 'admin-ajax.php', 'wicky' ); ?>" data-noresult="<?php echo esc_html__("No Result","wicky") ; ?>" data-limit="<?php echo esc_attr($limit_ajax_search); ?>">
		<?php if($terms){ /*?>
		<div class="select_category pwb-dropdown dropdown">
			<span class="pwb-dropdown-toggle dropdown-toggle" data-toggle="dropdown"><?php echo esc_html__("Category","wicky"); ?></span>
			<span class="caret"></span>
			<ul class="pwb-dropdown-menu dropdown-menu category-search">
			<li data-value="" class="<?php  echo (empty($category_slug) ?  esc_attr("active") : ""); ?>"><?php echo esc_html__("Browse Category","wicky"); ?></li>
				<?php foreach($terms as $term){?>
					<li data-value="<?php echo esc_attr($term->slug); ?>" class="<?php  echo (($term->slug == $category_slug) ?  esc_attr("active") : ""); ?>"><?php echo esc_html($term->name); ?></li>
					<?php
						$terms_vl1 =	get_terms( 'product_cat', 
						array( 
								'parent' => '', 
								'hide_empty' => false,
								'parent' 		=> $term->term_id, 
						));						
					?>	
					<?php foreach ($terms_vl1 as $term_vl1) { ?>
						<li data-value="<?php echo esc_attr($term_vl1->slug); ?>" class="<?php  echo (($term_vl1->slug == $category_slug) ?  esc_attr("active") : ""); ?>"><?php echo esc_html($term_vl1->name); ?></li>
						<?php
							$terms_vl2 =	get_terms( 'product_cat', 
							array( 
									'parent' => '', 
									'hide_empty' => false,
									'parent' 		=> $term_vl1->term_id, 
						));	?>					
						<?php foreach ($terms_vl2 as $term_vl2) { ?>
						<li data-value="<?php echo esc_attr($term_vl2->slug); ?>" class="<?php  echo (($term_vl2->slug == $category_slug) ?  esc_attr("active") : ""); ?>"><?php echo esc_html($term_vl2->name); ?></li>
						<?php } ?>
					<?php } ?>
				<?php } ?>
			</ul>	
			<input type="hidden" name="product_cat" class="product-cat" value="<?php echo esc_attr($category_slug); ?>"/>
		</div>	
		<?php */} ?>	
		<div class="search-box">
			<button id="searchsubmit" class="btn" type="submit">
				<i class="icon_search"></i>
				<span><?php echo esc_html__('search','wicky'); ?></span>
			</button>
			<input type="text" value="<?php echo get_search_query(); ?>" name="s" id="s" class="input-search s" placeholder="<?php echo esc_attr__( 'Search', 'wicky' ); ?>" />
			<ul class="result-search-products">
			</ul>
		</div>
		<input type="hidden" name="post_type" value="product" />
	</form>
<?php }
function wicky_top_cart(){
	global $woocommerce; ?>
	<div id="cart" class="top-cart">
		<a class="cart-icon" href="<?php echo get_permalink( wc_get_page_id( 'cart' ) ); ?>" title="<?php esc_attr_e('View your shopping cart', 'wicky'); ?>">
			<i class="flaticon-bag"></i>
		</a>
	</div>
<?php }
function wicky_button_filter(){
	$html = '<a class="button-filter-toggle hidden-xs hidden-sm">'.esc_html__( 'Filter', 'wicky' ).'</a>';
	echo wp_kses_post($html);
}	
function wicky_image_single_product(){
	$wicky_settings = wicky_global_settings();
	$class = new stdClass;
	$class->show_thumb = wicky_get_config('product-thumbs',false);
	$position = (isset($wicky_settings['position-thumbs']) && $wicky_settings['position-thumbs']) ? $wicky_settings['position-thumbs'] : "bottom";
	$position = get_post_meta( get_the_ID(), 'product_position_thumb', true ) ? get_post_meta( get_the_ID(), 'product_position_thumb', true ) : $position;
	$class->position = $position;
	if($class->show_thumb && $position == "outsite"){
		add_action( 'woocommerce_single_product_summary', 'wicky_add_thumb_single_product', 40 );
	}	
	if($position == 'left' || $position == "right"){
		$class->class_thumb = "col-sm-2";
		$class->class_data_image = 'data-vertical="true" data-verticalswiping="true"';
		$class->class_image = "col-sm-10";
	}else{
		$class->class_thumb = $class->class_image = "col-sm-12";
		$class->class_data_image = "";
	}
	if(isset($wicky_settings['product-thumbs-count']) && $wicky_settings['product-thumbs-count'])
		$product_count_thumb = 	$wicky_settings['product-thumbs-count'];
	else
		$product_count_thumb = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
	$product_count_thumb = get_post_meta( get_the_ID(), 'product_count_thumb', true ) ? get_post_meta( get_the_ID(), 'product_count_thumb', true ) : $product_count_thumb;
	$class->product_count_thumb =	$product_count_thumb;
	$product_layout_thumb = (isset($wicky_settings['layout-thumbs']) && $wicky_settings['layout-thumbs']) ? $wicky_settings['layout-thumbs'] : "zoom";
	$product_layout_thumb = get_post_meta( get_the_ID(), 'product_layout_thumb', true ) ? get_post_meta( get_the_ID(), 'product_layout_thumb', true ) : $product_layout_thumb;
	$class->product_layout_thumb =	$product_layout_thumb;	
	return $class;
}
function wicky_category_top_bar(){
	$sidebar_product = wicky_category_sidebar();
	add_action('woocommerce_before_shop_loop','woocommerce_result_count',20); 
	add_action('woocommerce_before_shop_loop','wicky_display_view', 40);
	remove_action('woocommerce_before_shop_loop','wicky_found_posts', 20);
	add_action('woocommerce_before_shop_loop','woocommerce_catalog_ordering', 30);
	add_action('woocommerce_before_shop_loop','wicky_catalog_perpage', 35);
	if($sidebar_product == 'full'){
		add_action('woocommerce_before_shop_loop','wicky_button_filter', 25);
	}	
	do_action( 'woocommerce_before_shop_loop' );
}
function wicky_get_product_discount(){
	global $product;
	$discount = 0;
	if ($product->is_on_sale() && $product->is_type( 'variable' )){
		$available_variations = $product->get_available_variations();
		for ($i = 0; $i < count($available_variations); ++$i) {
			$variation_id=$available_variations[$i]['variation_id'];
			$variable_product1= new WC_Product_Variation( $variation_id );
			$regular_price = $variable_product1->get_regular_price();
			$sales_price = $variable_product1->get_sale_price();
			if(is_numeric($regular_price) && is_numeric($sales_price)){
				$percentage = round( (( $regular_price - $sales_price ) / $regular_price ) * 100 ) ;
				if ($percentage > $discount) {
					$discount = $percentage;
				}
			}
		}
	}elseif($product->is_on_sale() && $product->is_type( 'simple' )){
		$regular_price	= $product->get_regular_price();
		$sales_price	= $product->get_sale_price();
		if(is_numeric($regular_price) && is_numeric($sales_price)){
			$discount = round( ( ( $regular_price - $sales_price ) / $regular_price ) * 100 );
		}
	}
	if( $discount > 0 ){
		$text_discount = "-".$discount.'%';
	}else{
		$text_discount = '';
	}
	return 	$text_discount;
}	
function wicky_category_bottom(){
	remove_action('woocommerce_after_shop_loop','woocommerce_result_count', 20);
	do_action( 'woocommerce_after_shop_loop' );
}
?>