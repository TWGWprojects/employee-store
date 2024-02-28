(function($) {
	$.fn.binFilterProduct = function(opts) {
		/* default configuration */	
		var config = $.extend({}, {
			widget_id : null,
			id_category:null,
			base_url: null,
			attribute:null,
			showcount:null,
			show_price:null,
			relation:null,
			show_only_sale:null,
			show_in_stock:null,
			show_brand:null,
			array_value_url : null	
		}, opts);
		$(document).ready(function(){
			
			_event_filter_product();
			_event_click_pagination();
			_eventFilter(1,false);
			_event_clear_all();
			
			$("li",".woocommerce-sort-count").click(function() {
				$("li",".woocommerce-sort-count").removeClass("active");
				$(this).addClass("active");
				_eventFilter();
				return false;
			});		

			$("li",".woocommerce-ordering").click(function() {
				_eventFilter();
				return false;
			});

			var view_products = $(".display",".bwp-top-bar");
			$("a",view_products).click(function(e) {
				e.preventDefault();
				if(!$(this).hasClass("active")){
					$("a",view_products).removeClass('active');
					var this_class	= $("ul.products").data("col");
					$(this).addClass('active');								
					_eventFilter();
				}
				return false;
			});			
			
		});		
		
		
		function _event_clear_all(){
			$(".filter_clear_all",config.widget_id).click(function(e) {
					$("input",config.widget_id).attr('checked', false);
					$("#price-filter-min-text",config.widget_id).val($("#bwp_slider_price",config.widget_id).data('min'));
					$("#price-filter-max-text",config.widget_id).val($("#bwp_slider_price",config.widget_id).data('max'));
					_eventFilter();
			});		
		}
		
		function _event_click_pagination(){
			$( "nav.woocommerce-pagination a.page-numbers").click(function(e) {
					e.preventDefault();
					$('ul.products','.main-archive-product').scrollTop(300);
					var status_id = $(this).attr('href').split('=');
					var paged = (status_id[1]) ? status_id[1] : 1;
					_eventFilter(paged);
				return false;
			});		
		}
		
		function _event_filter_product(){
			min_price = $("#price-filter-min-text",config.widget_id).val();
			max_price =  $("#price-filter-max-text",config.widget_id).val();
			$("#bwp_slider_price").slider({
			range:true,
			min: $("#bwp_slider_price",config.widget_id).data('min'),
			max: $("#bwp_slider_price",config.widget_id).data('max'),		
			values: [min_price,max_price],
			slide : function( event, ui ) {
					$("#text-price-filter-min-text",config.widget_id).html(ui.values[0]);
					$("#text-price-filter-max-text",config.widget_id).html(ui.values[1]);				
					$("#price-filter-min-text",config.widget_id).val(ui.values[0]);
					$("#price-filter-max-text",config.widget_id).val(ui.values[1]);		
				},
			change: function( event, ui ) {
					_eventFilter();	
					return false;	
				}		   			
			});	

			$( "#button-price-slider",config.widget_id ).click(function(e) {
				e.preventDefault();
				_eventFilter();
				return false;
			});	
			
			$("input:checkbox",config.widget_id ).on('click', function(){
				_eventFilter();	
				return false;
			});			
			
			$("span",config.widget_id ).on('click', function(){
				if($("input",$(this)).is(':checked'))
					$("input",$(this)).attr("checked", false);
				else
					$("input",$(this)).attr("checked", true);	
				_eventFilter();	
				return false;
			});			
		}	
		
		function _eventFilter(paged=1,load=true){
				if(load){
					$('html, body').animate({
						scrollTop: 300
					}, 300);		
					$('ul.products','.main-archive-product').addClass('active');
					$('ul.products','.main-archive-product').append( '<div class="loading"><div class="chasing-dots"><div></div><div></div><div></div><div></div></div></div>' );	
				}
				var $filter = new Object();				
				var $ajax_url = filter_ajax.ajaxurl;
				$filter.orderby 		=	$('.woocommerce-ordering').find('li.active').data("value");
				$filter.product_count 		=	$('.woocommerce-sort-count').find('li.active').data("value");	
				$filter.views				= ($('.view-grid.active').length > 0) ?  'grid' : 'list';	
				$filter.data 			= 	$("#bwp_form_filter_product",config.widget_id).serializeArray();
				$filter.default_min_price 	= $("#bwp_slider_price",config.widget_id).data("min");
				$filter.default_max_price 	= $("#bwp_slider_price",config.widget_id).data("max");	
				$filter.min_price 			= $("#price-filter-min-text",config.widget_id).val();	
				$filter.max_price 			= $("#price-filter-max-text",config.widget_id).val();
				$filter.paged				= paged;			
				jQuery.ajax({
					type: "POST", 
					url: $ajax_url,
					dataType: 'json',
					data: {
						filter 			: $filter,
						action 			: "bwp_filter_products_callback",
						id_category 	: config.id_category,			
						base_url 		: config.base_url,
						attribute 		: config.attribute,
						relation 		: config.relation,
						show_price 		: config.show_price,
						showcount 		: config.showcount,
						show_only_sale 	: config.show_only_sale,
						show_in_stock 	: config.show_in_stock,
						show_brand 		: config.show_brand,
						array_value_url : config.array_value_url
					},
					success: function (result) {	
						if (result.products){
							$('ul.products','.main-archive-product').html(result.products);
							_event_click_quickview_button($ajax_url);
						}else
							$('ul.products','.main-archive-product').html('');
						
						 _event_after_sucsess_ajax(result,config);
						
						if(load){
							setTimeout(function() {
								$('ul.products','.main-archive-product').removeClass('active');
								$( '.loading','.main-archive-product' ).remove();
							}, 400);
						}
						
					},
					error:function(jqXHR, textStatus, errorThrown) {
						console.log("error " + textStatus);
						console.log("incoming Text " + jqXHR.responseText);
					}
				});
			return false;	
		}
		
		function _event_click_quickview_button($ajax_url){
			$('.quickview-button').on( "click", function(e) {
				e.preventDefault();
				var product_id  = $(this).data('product_id');
				$(".quickview-"+product_id).addClass("loading");
				$.ajax({
					url: $ajax_url,
					data: {
						"action" : "wicky_quickviewproduct",
						'product_id' : product_id
					},
					success: function(results) {
						$('.bwp-quick-view').empty().html(results).addClass("active");
						$(".quickview-"+product_id).removeClass("loading");				
						$("#quickview-slick-carousel .slick-carousel").each(function(){
							_event_load_slick_carousel($(this));
							_event_check_nav_slick($(this));
						});
						if( typeof jQuery.fn.tawcvs_variation_swatches_form != 'undefined' ) {
							jQuery('.variations_form').tawcvs_variation_swatches_form();
							jQuery(document.body).trigger('tawcvs_initialized');
						}else{
							var form_variation = $(".bwp-quick-view").find('.variations_form');
							var form_variation_select = $(".bwp-quick-view").find('.variations_form .variations select');
							form_variation.wc_variation_form();
							form_variation_select.change();
						}					
						_event_close_quickview();
					},
					error: function(errorThrown) { console.log(errorThrown); },
				});
			});
		}
		function _event_check_nav_slick($element){
			if($(".slick-arrow",$element).length > 0){
				var $prev = $(".fa-angle-left",$element).clone();
				$(".fa-angle-left",$element).remove();
				if($element.parent().find(".fa-angle-left").length == 0){
					$prev.prependTo($element.parent());
				}
				$prev.on( "click", function() {
					$element.slick('slickPrev');
				});
				var $next =  $(".fa-angle-right",$element).clone();
				$(".fa-angle-right",$element).remove();
				if($element.parent().find(".fa-angle-right").length == 0){
					$next.appendTo($element.parent());
				}
				$next.on( "click", function() {
					$element.slick('slickNext');
				});
			}	
		}			
		function _event_close_quickview(){
			$('.quickview-close').on( "click", function(e) {
				e.preventDefault();
				$('.bwp-quick-view').empty().removeClass("active");
			});		
		}

		function _event_load_slick_carousel($element){
			$element.slick({
				arrows: $element.data("nav") ? true : false ,
				dots: $element.data("dots") ? true : false ,
				draggable : $element.data("draggable") ? false : true ,
				prevArrow: '<i class="slick-arrow fa fa-angle-left"></i>',
				nextArrow: '<i class="slick-arrow fa fa-angle-right"></i>',	
				slidesToShow: $element.data("columns"),
				asNavFor: $element.data("asnavfor") ? $element.data("asnavfor") : false ,
				vertical: $element.data("vertical") ? true : false ,
				verticalSwiping: $element.data("verticalswiping") ? $element.data("verticalswiping") : false ,
				rtl: ($("body").hasClass("rtl") && !$element.data("vertical")) ? true : false ,
				centerMode: $element.data("centermode") ? $element.data("centermode") : false ,
				focusOnSelect: $element.data("focusonselect") ? $element.data("focusonselect") : false ,
				responsive: [	
					{
					  breakpoint: 1200,
					  settings: {
						slidesToShow: $element.data("columns1"),
					  }
					},				
					{
					  breakpoint: 1024,
					  settings: {
						slidesToShow: $element.data("columns2"),
					  }
					},
					{
					  breakpoint: 768,
					  settings: {
						slidesToShow: $element.data("columns3"),
						vertical: false,
						verticalSwiping : false,
					  }
					},
					{
					  breakpoint: 480,
					  vertical: false,
					  verticalSwiping : false,				  
					  settings: {
						slidesToShow: $element.data("columns4"),
						vertical: false,
						verticalSwiping : false,					
					  }
					}
				]								
			});	
		}		
		
		function _event_after_sucsess_ajax(result,config){
			if (result.pagination) 
				$('nav.woocommerce-pagination').replaceWith(result.pagination);
			else
				$('nav.woocommerce-pagination').html('');
			
			if (result.result_count) 
				$('.woocommerce-result-count').replaceWith(result.result_count);
			else
				$('.woocommerce-result-count').html('');
			
			if (result.total_html) 
				$('.woocommerce-found-posts').replaceWith(result.total_html);
			else
				$('.woocommerce-found-posts').html('');						
			
			$('.bwp-filter-ajax',config.widget_id).replaceWith(result.left_nav);
			
			var checked = $("input:checked",config.widget_id).length;
			
			if(($("#price-filter-min-text",config.widget_id).val() != $("#bwp_slider_price",config.widget_id).data("min")) || ($("#price-filter-max-text",config.widget_id).val() != $("#bwp_slider_price",config.widget_id).data("max")))
				check_price = true;
			else
				check_price = false;
			
			if(checked > 0 || check_price)
				$(".filter_clear_all",config.widget_id).show();
			else
				$(".filter_clear_all",config.widget_id).hide();
			
			_event_filter_product();
			_addClassProductList();
			_event_clear_all();
			_event_click_pagination();
			_event_ajax_add_to_cart();
			if (result.base_url != '') 
				history.pushState({}, "", result.base_url.replace(/&amp;/g, '&').replace(/%2C/g, ','));		
		}
		
		function _event_ajax_add_to_cart(){
			$( "a.ajax_add_to_cart" ).on( "click", function() {
				setTimeout(function() {	
					if(!$(".top-cart",".wpbingoCartTop").hasClass("open"))
						$(".top-cart",".wpbingoCartTop").addClass("open");
				}, 2000);
			});
		}	

		function _addClassProductList(){
			var class_product_default = $("ul.products-list").data("col") ? $("ul.products-list").data("col") : "";
			var class_product_item = $('.view-grid.active').data('col') ? $('.view-grid.active').data('col') : class_product_default;
			if(class_product_item){
				var list_class 	= "col-lg-12 col-md-12 col-xs-12";	
				if($('.view-grid').hasClass('active')){
					$("ul.products-list").removeClass('list').addClass('grid');
					$("ul.products-list li").removeClass(list_class).addClass(class_product_item);
				}	
				if($('.view-list').hasClass('active')){
					$("ul.products-list").removeClass('grid').addClass('list');
					$("ul.products-list li").removeClass(class_product_item).addClass(list_class);
				}	
			}
		}
		
		return false;
	};
	
})(jQuery);
