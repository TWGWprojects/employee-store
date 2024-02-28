(function($) {
	"use strict";
	class Elementor_Js_Wpbingo {
		static getInstance() {
			if (!Elementor_Js_Wpbingo.instance) {
				Elementor_Js_Wpbingo.instance = new Elementor_Js_Wpbingo();
			}
			return Elementor_Js_Wpbingo.instance;
		}
		constructor() {
			$(window).on('elementor/frontend/init', () => {
				this.init();
			});
		}
		init() {
			elementorFrontend.hooks.addAction('frontend/element_ready/bwp_instagram.default', ($scope) => {
				let bwpInstagramsElem     = $scope.find('.bwp-instagram');
				bwpInstagramsElem.each(function() {
					var $element = $(this);
					var value_user_id = $element.data("user_id");
					var value_access_token = $element.data("access_token");
					var value_limit = $element.data("limit");
					var $item_row = $element.data("rows") ? $element.data("rows") : 1;
					if (value_user_id && value_access_token) {
						var media_users_recent = "https://api.instagram.com/v1/users/" + value_user_id + "/media/recent?access_token=" + value_access_token + "&count=" + value_limit;
						$.ajax({
							method: "GET",
							dataType: "jsonp",
							cache: false,
							url: media_users_recent,
							success: function (response) {
								var data_image = response.data;
								if (typeof (data_image) == 'undefined') {
									$(".content_instagram",$element).append("<li>"+$element.data("text_check_user")+"</li>");
									return;
								}
								
								if (data_image.length > 0) {
									var html = '';
									for (var i = 0; i < data_image.length; i++) {
										if(i < data_image.length){
											var $j = i + 1;
											if( ($j == 1) ||  ( $j % $item_row  == 1 ) || ( $item_row == 1 )) {
												html += '<div class="item-instagram">';
											}
											html += '<div class="image-instagram '+($(".content_instagram",$element).data("attributes") ? $(".content_instagram",$element).data("attributes") : '' )+'">'
													+ '<a class="instagram" target="_blank"  href="' + data_image[i].link + ' ">'
													+	'<img src="' + data_image[i].images.standard_resolution.url + '" alt="" title="" width="'+$element.data("width")+'" height="'+$element.data("height")+'">' 
													+ '</a>'
												+'</div>';
											if( ($j == data_image.length) || ($j % $item_row == 0) || ($item_row == 1)){
												html += '</div>';
											}
										}	
									}
									$(".content_instagram",$element).html(html);
									if($(".content_instagram",$element).hasClass("slick-carousel")){
										_wpbingo_slick_carousel($(".content_instagram",$element));
									}
								}else {
									$(".content_instagram",$element).append("<li>"+$(this).data("text_image_show")+"</li>");
									return;
								}
							},
							error: function () {
								$(".content_instagram",$element).append("<li>"+$(this).data("text_image_show")+"</li>");
							}
						})
					}	
				});
			});
			elementorFrontend.hooks.addAction('frontend/element_ready/bwp_filter_homepage.default', ($scope) => {
				let bwpFilterHomepageElem     = $scope.find('.bwp-filter-homepage');
				bwpFilterHomepageElem.each(function() {
					var $element = $(this);
					$(".bwp-filter-toggle",$element).click(function(){
						if($(this).hasClass('active')){
							$(this).removeClass('active');
							$(".bwp-filter-attribute",$element).slideUp();
						}else{
							$(this).addClass('active');	
							$(".bwp-filter-attribute",$element).slideDown();
						}	
					});	
					
					$("li",$element).click(function(){
						var $parent = $(this).parent();
						if($parent.hasClass('filter-orderby')){
							var order_text = $(this).text();
							$('.text-orderby').html(order_text);
						}

						if($parent.hasClass('filter-category') || $parent.hasClass('filter-orderby'))
							$("li",$parent).removeClass('active');
						else
							$(this).removeClass('active');
						
						if($(this).hasClass('active')){
							$(this).removeClass('active');
						}else{
							$(this).addClass('active');
						}
						
						var count_loadmore = $(".count_loadmore",$element).data("default");
						$(".count_loadmore",$element).val(parseInt(count_loadmore));
						_eventFilterHomePage($element);
					});	
					
					$(".loadmore",$element).click(function(){
						_eventFilterHomePage($element,true);
					});	
					
					
					$('.clear_all',$element).click(function(e){
						var $content_filter 	= $(".bwp-filter-attribute",$element);
						var bwp_slider_price 	= $(".bwp_slider_price",$element);
						$("li",$content_filter).removeClass('active'); 
						$(".price-filter-min-text",$element).val(bwp_slider_price.data("min"));
						$(".price-filter-max-text",$element).val(bwp_slider_price.data("max"));
						$(".text-price-filter-min-text",$element).html(bwp_slider_price.data("min"));
						$(".text-price-filter-max-text",$element).html(bwp_slider_price.data("max"));
						$(".ui-slider-range",bwp_slider_price).css({"left": "0px", "width": "100%"});
						$("span",bwp_slider_price).first().css("left","0px");
						$("span",bwp_slider_price).last().css("left","100%");
						_eventFilterHomePage($element); 
					});		
					
					var min_price = $(".price-filter-min-text",$element).val();
					var max_price =  $(".price-filter-max-text",$element).val();
					$(".bwp_slider_price",$element).slider({
					range:true,
					min: $(".bwp_slider_price",$element).data('min'),
					max: $(".bwp_slider_price",$element).data('max'),		
					values: [min_price,max_price],
					slide : function( event, ui ) {
							$(".text-price-filter-min-text",$element).html(ui.values[0]);
							$(".text-price-filter-max-text",$element).html(ui.values[1]);
							$(".price-filter-min-text",$element).val(ui.values[0]);		
							$(".price-filter-max-text",$element).val(ui.values[1]);		
						},
					change: function( event, ui ) {
						_eventFilterHomePage($element);		
					}
					
					});	
				});				
			});			
		}
	}
	Elementor_Js_Wpbingo.getInstance();

	function _eventFilterHomePage($element,loadmore = false){
			if(loadmore){
				$('.loadmore',$element).addClass('loading');
			}else{
				$('.bwp-filter-content',$element).addClass('active');
				$('.bwp-filter-content',$element).append('<div class="loading"><div class="chasing-dots"><div></div><div></div><div></div><div></div></div></div>');
			}
			var $filter = new Object();				
			var $ajax_url = filter_ajax.ajaxurl;
			$filter.category 		=	$(".filter-category li.active",$element).data("value");		
			$filter.orderby 		=	$(".filter-orderby li.active",$element).data("value");	
			$filter.min_price 		= 	$(".price-filter-min-text",$element).val();	
			$filter.max_price 		= 	$(".price-filter-max-text",$element).val();
			$filter.class_col 		= 	(!$element.hasClass("slider")) ? $element.data("class_col") : "";
			$filter.loadmore 		= 	(loadmore) ? 1 : 0;
			$filter.item_row 		= 	$(".products-list",$element).data("item_row") ? $(".products-list",$element).data("item_row") : 1;
			if(loadmore){
				$filter.paged 			= 	$(".count_loadmore",$element).val();			
			}else{
				$filter.paged			=	1;				
			}
			$filter.product_count 	= 	$element.data("showmore");
			
			var atributes			=	$element.data("atributes");
			if(atributes){
				var atributes		=	atributes.split(',');	
				for(var i=0;i<atributes.length;i++){
					var atr = [];
					$("."+atributes[i]+" li.active",$element).each(function(index){
						atr[index] = $(this).data("value");
					});					
					$filter[atributes[i]] = atr;	
				}						
			}	
		
			var brands  = [];
			$(".filter-brand li.active",$element).each(function(index){
				brands[index] = $(this).data("value");
			});
			
			$filter.brand = brands; 
			
			jQuery.ajax({
				type: "POST", 
				url: $ajax_url,
				dataType: 'json',
				data: {
					filter 			: $filter,
					action 			: "bwp_filter_homepage_callback",
				},
				success: function (result) {	
					if(loadmore){
						if (result.products)
							$('.products-list',$element).append(result.products);
						var count_loadmore = $(".count_loadmore",$element).val();
							$(".count_loadmore",$element).val(parseInt(count_loadmore) + 1);
					}else{
						if (result.products){
							$('.products-list',$element).html(result.products);
							if($element.hasClass("slider")){
								$('.products-list',$element).removeClass("slick-slider slick-initialized");
								_wpbingo_slick_carousel($('.products-list',$element));
								_check_nav_slick($('.products-list',$element));
							}
						}else{
							$('.products-list',$element).html('');
						}						
					}

					_wpbingo_click_quickview_button();
					
					if (result.loadmore && result.loadmore == 1)
						$(".products_loadmore",$element).show();
					else
						$(".products_loadmore",$element).hide();
					
					if(loadmore){
						$('.loadmore',$element).removeClass('loading');	
					}else{
						$('.bwp-filter-content',$element).removeClass('active');
						$('.loading',$element).remove();
					}
					var $content_filter = $(".bwp-filter-attribute",$element);

					if($("li.active",$content_filter).length > 0 || ($(".price-filter-min-text",$element).val() != $(".bwp_slider_price",$element).data("min"))  || ($(".price-filter-max-text",$element).val() != $(".bwp_slider_price",$element).data("max")))
						$(".clear_all",$element).show();
					else
						$(".clear_all",$element).hide();	
				},
				error:function(jqXHR, textStatus, errorThrown) {
					console.log("error " + textStatus);
					console.log("incoming Text " + jqXHR.responseText);
				}
			});
			
		return false;	
	}

	function _wpbingo_slick_carousel($element){
		var _body    = $( 'body' );
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
			rtl: (_body.hasClass("rtl") && !$element.data("vertical")) ? true : false ,
			centerMode: $element.data("centermode") ? $element.data("centermode") : false ,
			focusOnSelect: $element.data("focusonselect") ? $element.data("focusonselect") : false ,
			responsive: [
				{
				  breakpoint: 1500,
				  settings: {
					slidesToShow: $element.data("columns1500") ? $element.data("columns1500") : $element.data("columns"),
				  }
				},			
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

	function _check_nav_slick($element){
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
	
	function _wpbingo_click_quickview_button(){
		$('.quickview-button').on( "click", function(e) {
			e.preventDefault();
			var product_id  = $(this).data('product_id');
			$(".quickview-"+product_id).addClass("loading");
			$.ajax({
				url: filter_ajax.ajaxurl,
				data: {
					"action" : "wicky_quickviewproduct",
					'product_id' : product_id
				},
				success: function(results) {
					$('.bwp-quick-view').empty().html(results).addClass("active");
					$(".quickview-"+product_id).removeClass("loading");				
					$("#quickview-slick-carousel .slick-carousel").each(function(){
						_wpbingo_slick_carousel($(this));
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
					_wpbingo_click_close_quickview();
				},
				error: function(errorThrown) { console.log(errorThrown); },
			});
		});
	}
	
	function _wpbingo_click_close_quickview(){
		$('.quickview-close').on( "click", function(e) {
			e.preventDefault();
			$('.bwp-quick-view').empty().removeClass("active");
		});		
	}
	
	//Load more Product
	
	$( ".bwp-products-loadmore" ).each(function() {
		var $element = $(this);
		 $(".loadmore",$element).click(function(e) {
			e.preventDefault(); 
			var paged = $(".count_loadmore", $element).val();
			$.ajax({
				type: "POST", 
				url: $element.data("url"),
				dataType: 'json',
				data: {
					action 		: "bwp_load_more_callback",
					category 	:  $element.data("category"),			
					orderby 	:  $element.data("orderby"),
					order 		:  $element.data("order"),
					numberposts :  $element.data("numberposts"),
					source 		:  $element.data("source"),
					attributes 	:  $element.data("attributes"),
					total 		:  $element.data("total"),
					paged 		: 	paged,
				},
				beforeSend: function() {
	                $('.loadmore',$element).addClass('loading');
	            },				
				success: function (result) {	
					if (result.products){
						$('.products-list',$element).append(result.products);
						paged = parseInt(paged) + 1;
						$('.count_loadmore',$element).val(paged);
						_wpbingo_click_quickview_button();
					}
					if(result.check_loadmore == 1)
						$('.products_loadmore',$element).hide();
					 	$('.loadmore',$element).removeClass('loading');
				}
			});
		});	
	});
})(jQuery);
