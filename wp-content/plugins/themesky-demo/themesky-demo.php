<?php 
/**
 * Plugin Name: ThemeSky Demo
 * Plugin URI: http://theme-sky.com
 * Description: Add Demo Options for the Drile theme
 * Version: 1.0.0
 * Author: ThemeSky Team
 * Author URI: http://theme-sky.com
 */
class ThemeSky_Demo{
	public $load_caroufredsel = false;
	function __construct(){
		add_action('init', array($this, 'change_cloudzoom_thumbnail_layout_options'), 15);
		add_action('template_redirect', array($this, 'template_redirect'), 1);
		add_action('init', array($this, 'update_portfolio_like_action'));
		
		add_filter('ts_metabox_options_page_options', array($this, 'metabox_page_options'));
		
		if( !is_admin() && !defined('DOING_AJAX') && isset($_GET['color']) ){
			add_filter('drile_custom_style_data', array($this, 'custom_style_data'));
			add_action('wp_enqueue_scripts', array($this, 'add_inline_custom_style'), 1000000);
		}
		
		/* Remove some scripts, styles from demo */
		add_action('wp_enqueue_scripts', array($this, 'remove_some_scripts_on_demo'), 10000);
		
		/* Remove jquery-selectBox of wishlist - used for Pro version */
		add_filter('yith_wcwl_main_script_deps', function( $array ){
			unset($array[array_search('jquery-selectBox', $array)]);
			return $array;
		});
		
		/* remove emoji */
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		
		/* Header currency/language demo html */
		add_action('drile_header_currency_switcher', array($this, 'header_currency_switcher'));
		add_action('drile_header_language_switcher', array($this, 'header_language_switcher'));
	}
	
	function get_theme_options( $key = '', $default = '' ){
		if( function_exists('drile_get_theme_options') ){
			return drile_get_theme_options($key, $default);
		}
		else{
			return $default;
		}
	}
	
	function change_theme_options( $key, $value ){
		if( function_exists('drile_change_theme_options') ){
			drile_change_theme_options( $key, $value );
		}
	}
	
	function change_cloudzoom_thumbnail_layout_options(){
		if( isset($_GET['options']) ){
			$options = explode('-', $_GET['options']);
			if( isset($options[5]) ){ /* Product Thumbnail Layout */
				switch( $options[5] ){
					case 1:
						$this->change_theme_options('ts_prod_thumbnail_layout', 'grid');
					break;
					case 2:
						$this->change_theme_options('ts_prod_thumbnail_layout', 'slider');
					break;
					case 3:
						$this->change_theme_options('ts_prod_thumbnail_layout', 'grid');
						$this->change_theme_options('ts_prod_summary_scrolling', 0);
					break;
					default:
						$this->change_theme_options('ts_prod_thumbnail_layout', 'default');
				}
			}
			
			if( isset($options[6]) ){
				$this->change_theme_options('ts_prod_cloudzoom', (int)$options[6]);
			}
		}
	}
	
	function template_redirect(){
		global $post;
		
		if( is_page() ){
			$page_intro = get_post_meta( $post->ID, 'ts_page_intro', true);
			if( $page_intro ){
				add_filter('body_class', function( $classes ){ $classes[]='ts-header-intro'; return $classes; });
				add_filter('theme_mod_background_image', '__return_empty_string');
				add_action('wp_footer', array($this, 'page_intro_script_handle'));
				add_action('wp_enqueue_scripts', array($this, 'page_intro_dequeue_scripts'), 9999);
				$this->change_theme_options('ts_responsive', 0);
				$this->change_theme_options('ts_enable_tiny_shopping_cart', 0);
				$this->change_theme_options('ts_enable_tiny_account', 0);
				$this->change_theme_options('ts_enable_quickshop', 0);
				$this->change_theme_options('ts_enable_sticky_header', 0);
				$this->change_theme_options('ts_loading_screen', 1);
				add_action('wp_enqueue_scripts', array($this, 'page_intro_dynamic_style_handle'), 9999);
				
				$loading_image = $this->get_theme_options('ts_prod_placeholder_img');
				$loading_image = $loading_image['url'];
				add_filter('wp_calculate_image_srcset_meta', '__return_empty_array');
				add_filter('wp_get_attachment_image_attributes', function($attr) use ($loading_image){
					$src = $attr['src'];
					$attr['src'] = $loading_image;
					$attr['data-src'] = $src;
					$attr['class'] .= ' ts-lazy-load';
					return $attr;
				});
			}
		}
		
		if( is_page_template('page-templates/blog-template.php') ){
			if( isset($_GET['style']) ){
				$this->change_theme_options('ts_blog_style', esc_attr($_GET['style']));
			}
			if( isset($_GET['readmore']) ){
				$this->change_theme_options('ts_blog_read_more', esc_attr($_GET['readmore']));
			}
			if( isset($_GET['excerpt']) ){
				$this->change_theme_options('ts_blog_excerpt', esc_attr($_GET['excerpt']));
			}
			if( isset($_GET['excerpt_words']) ){
				$this->change_theme_options('ts_blog_excerpt_max_words', (int)$_GET['excerpt_words']);
			}
		}
		
		if( is_singular('product') ){
			if( $this->get_theme_options('ts_quickshop_image_layout') == 'small-thumbnails' && $this->get_theme_options('ts_prod_thumbnails_style') == 'vertical' ){
				$this->load_caroufredsel = true;
			}
			
			if( isset($_GET['options']) ){
				$options = $_GET['options'];
				$options = explode('-', $options);
				if( is_array($options) && count($options) > 0 ){
					if( isset($options[0]) ){ /* Thumbnail style */
						$this->change_theme_options('ts_prod_thumbnails_style', $options[0]?'vertical':'horizontal');
						$this->change_theme_options('ts_prod_thumbnails_position', $options[0] == 2?'right':'left');
					}
					
					if( isset($options[1]) ){ /* Accordion tabs */
						$this->change_theme_options('ts_prod_accordion_tabs', (int)$options[1]);
					}
					
					if( isset($options[2]) ){ /* Tab inside summary */
						$this->change_theme_options('ts_prod_tabs_position', $options[2]?'inside_summary':'after_summary');
					}
					
					if( isset($options[3]) ){ /* Product Sidebar */
						switch( $options[3] ){
							case 1:
								$this->change_theme_options('ts_prod_layout', '1-1-0');
							break;
							case 2:
								$this->change_theme_options('ts_prod_layout', '0-1-1');
							break;
							case 3:
								$this->change_theme_options('ts_prod_layout', '1-1-1');
							break;
							default:
								$this->change_theme_options('ts_prod_layout', '0-1-0');
						}
					}
					
					if( isset($options[4]) ){ /* Product Attribute Dropdown */
						if( (int)$options[4] == 2 ){
							$this->change_theme_options('ts_prod_attr_dropdown', 0);
							$this->change_theme_options('ts_prod_attr_color_text', 1);
						}
						else{
							$this->change_theme_options('ts_prod_attr_dropdown', (int)$options[4]);
						}
					}
					
					if( isset($options[7]) ){ /* Categories */
						$this->change_theme_options('ts_prod_cat', (int)$options[7]);
					}
					
					if( isset($options[8]) ){ /* Meta content */
						$this->change_theme_options('ts_prod_sku', (int)$options[8]);
						$this->change_theme_options('ts_prod_availability', (int)$options[8]);
						$this->change_theme_options('ts_prod_brand', (int)$options[8]);
						$this->change_theme_options('ts_prod_cat', (int)$options[8]);
						$this->change_theme_options('ts_prod_tag', (int)$options[8]);
						$this->change_theme_options('ts_prod_sharing', (int)$options[8]);
					}
					
					if( isset($options[9]) ){ /* Next/Prev Navigation */
						$this->change_theme_options('ts_prod_next_prev_navigation', (int)$options[9]);
					}
				}
			}
			
			add_filter('woocommerce_add_to_cart_form_action', array($this, 'woocommerce_add_to_cart_form_action'));
		}
		
		if( is_tax('product_cat') || is_tax('product_tag') || is_post_type_archive('product') ){
			if( isset($_GET['filter_area']) ){
				$this->change_theme_options('ts_filter_widget_area', (int)$_GET['filter_area']);
			}
			
			if( isset($_GET['filter_style']) && in_array($_GET['filter_style'], array('default', 'sidebar', 'bottom', 'dropdown')) ){
				$this->change_theme_options('ts_filter_widget_area_style', $_GET['filter_style']);
			}
			
			if( isset($_GET['columns']) ){
				$this->change_theme_options('ts_prod_cat_columns', absint($_GET['columns']));
			}
			
			if( isset($_GET['shop_display']) ){
				$shop_display = esc_attr($_GET['shop_display']);
				if( in_array($shop_display, array('subcategories', 'both')) ){
					add_filter('option_woocommerce_shop_page_display', function() use ($shop_display){
						return $shop_display;
					});
					add_filter('option_woocommerce_category_archive_display', function() use ($shop_display){
						return $shop_display;
					});
				}
			}
			
			if( isset($_GET['meta']) && $_GET['meta'] == 'center' ){
				$this->change_theme_options('ts_prod_cat_meta_align', 'center');
			}
			
			if( isset($_GET['loading_type']) && in_array($_GET['loading_type'], array('infinity-scroll', 'load-more-button', 'ajax-pagination')) ){
				$this->change_theme_options('ts_prod_cat_loading_type', $_GET['loading_type']);
			}
		}
		
		if( is_singular('ts_portfolio') ){
			if( isset($_GET['thumbnail_style']) ){
				$this->change_theme_options('ts_portfolio_thumbnail_style', esc_attr($_GET['thumbnail_style']));
			}
			if( isset($_GET['slider_style']) ){
				$this->change_theme_options('ts_portfolio_slider_style', esc_attr($_GET['slider_style']));
			}
		}
		
		if( isset($_GET['rtl']) ){
			$this->change_theme_options('ts_enable_rtl', (int)$_GET['rtl']);
		}
		
		if( isset($_GET['breadcrumb']) ){
			$this->change_theme_options('ts_breadcrumb_layout', esc_attr($_GET['breadcrumb']));
		}
		
		if( isset($_GET['hover_style']) ){
			$this->change_theme_options('ts_product_hover_style', esc_attr($_GET['hover_style']));
		}
		
		if( isset($_GET['back_image']) ){
			$this->change_theme_options('ts_effect_product', (int)$_GET['back_image']);
		}
	}
	
	function woocommerce_add_to_cart_form_action( $product_url ){
		if( !empty($_GET) ){
			$product_url = add_query_arg( $_GET, $product_url );
		}
		return $product_url;
	}
	
	function page_intro_script_handle(){
		wp_dequeue_script('vc_tta_autoplay_script');
	?>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				"use strict";
				
				$('.ts-feature-wrapper a').on('click', function(e){
					var href = $(this).attr('href');
					if( href.indexOf('#') == 0 ){
						e.preventDefault();
						var section = $(href);
						if( section.length != 0 ){
							var extra_space = $('#wpadminbar').length ? $('#wpadminbar').height() : 0;
							var offset_top = section.offset().top;
							offset_top -= extra_space;
							var scroll_top = $(window).scrollTop();
							var speed_mul = Math.ceil( Math.abs(offset_top - scroll_top) / 6000 );
							$('body,html').animate({
								scrollTop: offset_top
							}, 1500 * speed_mul).promise().done(function(){
								var real_top = section.offset().top - extra_space;
								if( real_top != offset_top ){
									$('body,html').animate({
										scrollTop: real_top
									}, Math.abs(real_top - offset_top));
								}
							});
						}
					}
					return false;
				});
				
				$('.vc_tta-tabs .vc_tta-tabs-list .vc_tta-tab a').on('click', function(){
					if( history.pushState ){
						setTimeout(function(){
							history.pushState(null, null, ' ');
						}, 0);
					}
				});
				
				$('.vc_tta-tabs .vc_tta-tab a').on('click lazy_load', function(e){
					e.preventDefault();
					var href = $(this).attr('href');
					if( href.indexOf('#') == 0 ){
						var panel = $(href);
						if( panel.length > 0 ){
							panel.find('img.ts-tab-lazy-load').each(function(index, element){
								if( $(element).data('src') ){
									$(element).attr('src', $(element).data('src')).removeAttr('data-src').removeClass('ts-tab-lazy-load');
								}
							});
						}
					}
					ts_update_tab_min_height();
					
					if( !$(this).parents('.vc_tta-tabs-list').hasClass('clicked') ){
						$(this).parents('.vc_tta-tabs-list').addClass('clicked');
						$(this).parents('.vc_tta-tabs-list').find('a').trigger('lazy_load');
					}
				});
				
				$(window).on('load', function(){
					$('.vc_tta-tabs img').on('load', function(){
						ts_update_tab_min_height(500);
					});
					
					$('.ts_fadeInLeft').addClass('fadeInLeft animated');
					$('.ts_fadeInRight').addClass('fadeInRight animated');
				});
				var tab_timeout = 0;
				function ts_update_tab_min_height( interval ){
					if( typeof interval == 'undefined' ){
						interval = 900;
					}
					clearTimeout(tab_timeout);
					tab_timeout = setTimeout(function(){
						$('.vc_tta-tabs .vc_tta-panels').each(function(){
							$(this).find('.vc_tta-panel').css('min-height', 0);
							var min_height = $(this).find('.vc_tta-panel.vc_active').height();
							$(this).find('.vc_tta-panel').css('min-height', min_height);
						});
					}, interval);
				}
			});
		</script>
	<?php
	}
	
	function page_intro_dequeue_scripts(){
		wp_dequeue_style('woocommerce-layout');
		wp_dequeue_style('woocommerce-smallscreen');
		wp_dequeue_style('woocommerce-general');
		wp_dequeue_style('woocommerce_prettyPhoto_css');
		wp_dequeue_style('prettyphoto');
		wp_dequeue_style('jquery-colorbox');
		wp_dequeue_style('jquery-selectBox');
		wp_dequeue_style('yith-wcwl-main');
		wp_dequeue_style('yith-wcwl-font-awesome');
		
		wp_dequeue_style('select2');
		wp_dequeue_style('owl-carousel');
		wp_dequeue_style('drile-dynamic-css');
		
		wp_dequeue_style('rs-plugin-settings');
		wp_dequeue_style('revslider-material-icons');
		wp_dequeue_style('revslider-basics-css');
		wp_dequeue_style('rs-color-picker-css');
		wp_dequeue_style('revbuilder-select2RS');
		
		wp_dequeue_script('contact-form-7');
		
		wp_dequeue_script('woocommerce');
		wp_dequeue_script('wc-cart-fragments');
		wp_dequeue_script('wc-add-to-cart');
		wp_dequeue_script('prettyphoto');
		wp_dequeue_script('prettyPhoto-init');
		
		wp_dequeue_script('vc_woocommerce-add-to-cart-js');
		
		wp_dequeue_script('jquery-selectBox');
		wp_dequeue_script('jquery-yith-wcwl');
		
		wp_dequeue_script('yith-woocompare-main');
		wp_dequeue_script('jquery-colorbox');
		
		wp_dequeue_script('select2');
		wp_dequeue_script('wc-add-to-cart-variation');
		wp_dequeue_script('owl-carousel');
		
		// Revolution Slider
		wp_dequeue_script('revmin');
		wp_dequeue_script('tp-tools');
		
	}
	
	function page_intro_custom_style(){
		return '.ts_fadeInLeft, .ts_fadeInRight{
					opacity: 0;
				}

				@-webkit-keyframes fadeInLeft{
					0%{opacity:0;-webkit-transform:translate3d(-100%,0,0);transform:translate3d(-100%,0,0)}
					100%{opacity:1;-webkit-transform:none;transform:none}
				}
				@keyframes fadeInLeft{
					0%{opacity:0;-webkit-transform:translate3d(-100%,0,0);transform:translate3d(-100%,0,0)}
					100%{opacity:1;-webkit-transform:none;transform:none}
				}
				.fadeInLeft{
					-webkit-animation-name:fadeInLeft;
					animation-name:fadeInLeft
				}

				@-webkit-keyframes fadeInRight{
					0%{opacity:0;-webkit-transform:translate3d(100%,0,0);transform:translate3d(100%,0,0)}
					100%{opacity:1;-webkit-transform:none;transform:none}
				}
				@keyframes fadeInRight{
					0%{opacity:0;-webkit-transform:translate3d(100%,0,0);transform:translate3d(100%,0,0)}
					100%{opacity:1;-webkit-transform:none;transform:none}
				}
				.fadeInRight{
					-webkit-animation-name:fadeInRight;
					animation-name:fadeInRight
				}

				.animated{
					-webkit-animation-duration: 1s;
					animation-duration: 1s;
					-webkit-animation-fill-mode: both;
					animation-fill-mode: both;
				}';
	}
	
	function page_intro_dynamic_style_handle(){
		$file_name = 'drile_intro';
		$upload_dir = wp_upload_dir();
		$filename_dir = trailingslashit($upload_dir['basedir']) . $file_name . '.css';
		$filename = trailingslashit($upload_dir['baseurl']) . $file_name . '.css';
		if( !file_exists($filename_dir) ){ /* Create File */
			global $wp_filesystem;
			if( empty( $wp_filesystem ) ) {
				require_once( ABSPATH .'/wp-admin/includes/file.php' );
				WP_Filesystem();
			}
			
			$creds = request_filesystem_credentials($filename_dir, '', false, false, array());
			if( ! WP_Filesystem($creds) ){
				return false;
			}
			
			if( $wp_filesystem ) {
				ob_start();
				include get_template_directory() . '/framework/dynamic_style.php';
				$dynamic_css = ob_get_contents();
				ob_end_clean();
				
				$dynamic_css .= $this->page_intro_custom_style();
		
				$wp_filesystem->put_contents(
					$filename_dir,
					$dynamic_css,
					FS_CHMOD_FILE
				);
			}
		}
		
		wp_enqueue_style('intro-dynamic-css', $filename);
	}
	
	function update_portfolio_like_action(){
		global $ts_portfolios;
		if( is_a($ts_portfolios, 'TS_Portfolios') && !is_user_logged_in() ){
			remove_action('wp_ajax_ts_portfolio_update_like', array($ts_portfolios, 'update_like'));
			remove_action('wp_ajax_nopriv_ts_portfolio_update_like', array($ts_portfolios, 'update_like'));
			add_action('wp_ajax_ts_portfolio_update_like', array($this, 'update_portfolio_like'));
			add_action('wp_ajax_nopriv_ts_portfolio_update_like', array($this, 'update_portfolio_like'));
			
			add_filter('ts_portfolio_like_num', array($this, 'portfolio_get_like'), 10, 2);
			add_filter('ts_portfolio_already_like', array($this, 'portfolio_already_like'), 10, 2);
		}
	}
	
	function update_portfolio_like(){
		if( isset($_POST['post_id']) ){
			global $ts_portfolios;
			$post_id = $_POST['post_id'];
			$like_num = $ts_portfolios->get_like($post_id);
			if( isset($_COOKIE['ts_portfolio_like_'.$post_id]) ){ /* Liked => Unlike */
				setcookie('ts_portfolio_like_'.$post_id, '', time()-3600, '/');
			}
			else{
				$like_num++;
				setcookie('ts_portfolio_like_'.$post_id, '1', time()+3600, '/');
			}
			die((string)$like_num);
		}
		die('');
	}
	
	function portfolio_get_like( $val, $post_id ){
		if( isset($_COOKIE['ts_portfolio_like_'.$post_id]) && !wp_doing_ajax() ){
			$val++;
		}
		return $val;
	}
	
	function portfolio_already_like( $val, $post_id ){
		if( isset($_COOKIE['ts_portfolio_like_'.$post_id]) ){
			return true;
		}
		return $val;
	}
	
	/* Metabox Page Options */
	function metabox_page_options( $options ){
		$options[] = array(
				'id'		=> 'page_options_demo_heading'
				,'label'	=> 'Page Options - Demo'
				,'desc'		=> ''
				,'type'		=> 'heading'
			);
		
		$options[] = array(
				'id'		=> 'page_intro'
				,'label'	=> 'Page Intro'
				,'desc'		=> ''
				,'type'		=> 'select'
				,'options'	=> array(
								'1'		=> 'Yes'
								,'0'	=> 'No'
								)
				,'default'	=> '0'
			);
	
		return $options;
	}
	
	/* Custom Style */
	function custom_style_data( $data = array() ){
		if( isset($_GET['color']) ){
			$color_name = $_GET['color'];
			$color_folder = get_template_directory() . '/admin/preset-colors/';
			$file_path = $color_folder . $color_name . '.php';
			if( file_exists($file_path) ){
				$preset_colors = array();
				include $file_path;
				foreach($preset_colors as $option_name => $value ){
					if( isset($data[$option_name]) ){
						$data[$option_name] = $value;
					}
				}
			}
		}
		return $data;
	}
	
	function add_inline_custom_style(){
		$custom_file = get_template_directory() .'/framework/dynamic_style.php';
		if( file_exists( $custom_file ) ){
			wp_dequeue_style('drile-dynamic-css');
			
			ob_start();
			include $custom_file;
			$custom_css = ob_get_clean();
			
			$after_file = wp_style_is('drile-rtl', 'enqueued')?'drile-rtl':'drile-style';
			wp_add_inline_style( $after_file, $custom_css );
		}
	}
	
	function remove_some_scripts_on_demo(){
		global $post;
		
		$site_url = get_option( 'siteurl' );
		if( strpos($site_url, 'theme-sky.com') !== false ){
			wp_dequeue_style('wp-block-library');
			wp_dequeue_style('wc-block-style');
			
			if( !( is_singular('product') || is_page('Wishlist') ) ){
				wp_dequeue_style('jquery-selectBox');
				wp_dequeue_style('yith-wcwl-font-awesome');
				wp_dequeue_style('yith-wcwl-main');
			}
			wp_dequeue_script('jquery-selectBox');
			wp_dequeue_script('prettyPhoto');
			wp_dequeue_script('vc_woocommerce-add-to-cart-js');
		}
		
		if( isset($post->post_content) && !has_shortcode($post->post_content, 'contact-form-7') && !is_page('Home 06') ){
			wp_dequeue_style('contact-form-7');
			wp_dequeue_script('jquery-form');
			wp_dequeue_script('contact-form-7');
		}
		
		if( $this->load_caroufredsel ){
			wp_enqueue_script('jquery-caroufredsel');
		}
	}
	
	function header_currency_switcher(){
		if( !apply_filters('themesky_show_currency_switcher_demo_html', true) ){
			return;
		}
		?>
		<div class="wcml_currency_switcher">
			<a href="javascript: void(0)" class="wcml-cs-active-currency">USD</a>
			<ul>
				<li><a href="#">USD</a></li>
				<li><a href="#">EUR</a></li>
			</ul>
		</div>
		<?php
	}
	
	function header_language_switcher(){
		if( !apply_filters('themesky_show_language_switcher_demo_html', true) ){
			return;
		}
		?>
		<div class="wpml-ls-statics-shortcode_actions wpml-ls wpml-ls-legacy-dropdown js-wpml-ls-legacy-dropdown">
			<ul>
				<li tabindex="0" class="wpml-ls-slot-shortcode_actions wpml-ls-item wpml-ls-item-en wpml-ls-current-language wpml-ls-first-item wpml-ls-item-legacy-dropdown">
					<a href="#" class="js-wpml-ls-item-toggle wpml-ls-item-toggle">
						<span class="wpml-ls-native">English</span>
					</a>
					<ul class="wpml-ls-sub-menu">
						<li class="wpml-ls-slot-shortcode_actions wpml-ls-item wpml-ls-item-fr">
							<a href="#" class="wpml-ls-link">
								<span class="wpml-ls-native">Français</span>
							</a>
						</li>
						<li class="wpml-ls-slot-shortcode_actions wpml-ls-item wpml-ls-item-de wpml-ls-last-item">
							<a href="#" class="wpml-ls-link">
								<span class="wpml-ls-native">Deutsch</span>
							</a>
						</li>
					</ul>
				</li>
			</ul>
		</div>
		<?php
	}
} 
new ThemeSky_Demo();
?>