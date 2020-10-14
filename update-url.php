<?php
	include_once('wp-load.php');
	global $wpdb;
	$wp_prefix = $wpdb->base_prefix;
	
	$message = '';
	
	if( isset($_POST) && isset($_POST['site_url']) && strlen(trim($_POST['site_url'])) > 0 ){
		$current_url = get_option( 'siteurl', '' );
		$new_url = $_POST['site_url'];
		
		$result_1 = $wpdb->query("update `{$wp_prefix}options` set `option_value`='{$new_url}' where `option_name` in('siteurl','home');");
		
		$result_2 = $wpdb->query("update `{$wp_prefix}links` set `link_url` = replace(`link_url`, '{$current_url}', '{$new_url}');");
		$result_3 = $wpdb->query("update `{$wp_prefix}posts` set `guid` = replace(`guid`, '{$current_url}', '{$new_url}');");
		$result_4 = $wpdb->query("update `{$wp_prefix}posts` set `post_content` = replace(`post_content`, '{$current_url}', '{$new_url}');");
		$result_5 = $wpdb->query("update `{$wp_prefix}posts` set `post_title` = replace(`post_title`, '{$current_url}', '{$new_url}') where post_type='nav_menu_item';");
		$result_6 = $wpdb->query("update `{$wp_prefix}postmeta` set `meta_value` = replace(`meta_value`, '{$current_url}', '{$new_url}');");
		
		/* Update Redux URL */
		$option_name = 'drile_theme_options';
		$option_ids = array(
					'ts_logo'
					,'ts_logo_mobile'
					,'ts_logo_sticky'
					,'ts_favicon'
					,'ts_image_not_found'
					,'ts_custom_loading_image'
					,'ts_header_contact_information'
					,'ts_bg_breadcrumbs'
					,'ts_prod_placeholder_img'
					);
		$theme_options = get_option($option_name);
		if( is_array($theme_options) ){
			foreach( $option_ids as $option_id ){
				if( isset($theme_options[$option_id]) ){
					$theme_options[$option_id] = str_replace($current_url, $new_url, $theme_options[$option_id]);
				}
			}
			update_option($option_name, $theme_options);
		}
		
		/* Update Widgets */
		$widgets = array(
			'media_image' => array('url')
			,'ts_single_image' => array('img_url')
			,'ts_mailchimp_subscription' => array('bg_image')
		);
		foreach( $widgets as $base => $fields ){
			$widget_instances = get_option( 'widget_' . $base, array() );
			if( is_array($widget_instances) ){
				foreach( $widget_instances as $number => $instance ){
					if( $number == '_multiwidget' ){
						continue;
					}
					foreach( $fields as $field ){
						if( isset($widget_instances[$number][$field]) ){
							$widget_instances[$number][$field] = str_replace($current_url, $new_url, $widget_instances[$number][$field]);
						}
					}
				}
				update_option( 'widget_' . $base, $widget_instances );
			}
		}
		
		/* Update revolution slider images */
		$slides = $wpdb->get_results('select * from '.$wp_prefix.'revslider_slides');
		if( is_array($slides) ){
			foreach( $slides as $slide ){
				$params = json_decode($slide->params);
				$layers = json_decode($slide->layers);
				
				if( isset($params->bg->image) ){
					$params->bg->image = str_replace($current_url, $new_url, $params->bg->image);
				}
				if( isset($params->thumb->customThumbSrc) ){
					$params->thumb->customThumbSrc = str_replace($current_url, $new_url, $params->thumb->customThumbSrc);
				}
				if( isset($params->thumb->customAdminThumbSrc) ){
					$params->thumb->customAdminThumbSrc = str_replace($current_url, $new_url, $params->thumb->customAdminThumbSrc);
				}
				
				if( isset($params->bg->mpeg) ){
					$params->bg->mpeg = str_replace($current_url, $new_url, $params->bg->mpeg);
				}
				if( isset($params->bg->webm) ){
					$params->bg->webm = str_replace($current_url, $new_url, $params->bg->webm);
				}
				if( isset($params->bg->ogv) ){
					$params->bg->ogv = str_replace($current_url, $new_url, $params->bg->ogv);
				}
				
				if( is_object($layers) ){
					foreach( $layers as $key => $layer ){
						if( isset($layers->$key->media->imageUrl) ){
							$layers->$key->media->imageUrl = str_replace($current_url, $new_url, $layers->$key->media->imageUrl);
						}
						
						if( isset($layers->$key->actions->action) && is_array($layers->$key->actions->action) ){
							foreach( $layers->$key->actions->action as $k => $a ){
								if( isset($layers->$key->actions->action[$k]->image_link) ){
									$layers->$key->actions->action[$k]->image_link = str_replace($current_url, $new_url, $layers->$key->actions->action[$k]->image_link);
								}
							}
						}
					}
				}
				
				$params = addslashes(json_encode($params));
				$layers = addslashes(json_encode($layers));
				
				$wpdb->query( "update `{$wp_prefix}revslider_slides` set `params`='{$params}', `layers`='{$layers}' where `id`={$slide->id}" );
			}
		}
		
		$static_slides = $wpdb->get_results('select * from '.$wp_prefix.'revslider_static_slides');
		if( is_array($static_slides) ){
			foreach( $static_slides as $slide ){
				$layers = json_decode($slide->layers);
				
				if( is_object($layers) ){
					foreach( $layers as $key => $layer ){
						if( isset($layers->$key->media->imageUrl) ){
							$layers->$key->media->imageUrl = str_replace($current_url, $new_url, $layers->$key->media->imageUrl);
						}
					}
				}
				
				$layers = addslashes(json_encode($layers));
				$wpdb->query( "update `{$wp_prefix}revslider_static_slides` set `layers`='{$layers}' where `id`={$slide->id}" );
			}
		}
		/* Update revolution slider images */
		
		if( $result_1 === false || $result_2 === false || $result_3 === false || $result_4 === false || $result_5 === false || $result_6 === false ){
			$message = 'Update failed';
		}else{
			$message = 'Update successfully! You need to save Permalinks(Settings > Permalinks) again';
		}
	}
?>
<div class="form-wrapper">
	<form name="input" action="" method="post">
		<h2>Input your site url</h2>
		<input type="text" name="site_url" autofocus autocomplete="off" />
		<p class="description">Without '/' at the end of url</p>
		<input type="submit" value="Change URL" />
	</form> 
	<?php if( $message != '' ): ?>
	<div class="message">
		<?php echo $message; ?>
	</div>
	<?php endif; ?>
</div>

<style type="text/css">
	.form-wrapper{
		width: 600px;
		text-align: center;
		margin: 0 auto;
		padding: 20px 10px;
		font-size: 17px;
	}
	.form-wrapper h2{
		text-transform: uppercase;
	}
	.form-wrapper input[type="text"]{
		width: 90%;
		height: 30px;
		line-height: 30px;
		font-size: 16px;
		padding: 2px 5px;
	}
	.form-wrapper .description{
		diplay: block;
		padding: 5px 5px;
		font-style: italic;
	}
	.form-wrapper input[type="submit"]{
		background-color: #2E9AFE;
		color: #fff;
		border: none;
		padding: 5px 10px;
		font-size: 16px;
	}
	.form-wrapper input[type="submit"]:hover{
		cursor: pointer;
		background-color: #58ACFA;
	}
	.form-wrapper .message{
		color: #FF00FF;
	}
</style>