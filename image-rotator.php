<?php
/*
Plugin Name: Image Rotator
Description: Shows a random image in the sidebar.
Version: 0.2.3
Author: Hassan Derakhshandeh

		* 	Copyright (C) 2011  Hassan Derakhshandeh
		*	http://tween.ir/
		*	hassan.derakhshandeh@gmail.com

		This program is free software; you can redistribute it and/or modify
		it under the terms of the GNU General Public License as published by
		the Free Software Foundation; either version 2 of the License, or
		(at your option) any later version.

		This program is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.

		You should have received a copy of the GNU General Public License
		along with this program; if not, write to the Free Software
		Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function boom_register_rotator_widget() {
	register_widget( 'Boom_Widget_Image_Rotator' );
}
add_action( 'widgets_init', 'boom_register_rotator_widget' );

class Boom_Widget_Image_Rotator extends WP_Widget {

	private $textdomain;
	private $width;
	private $height;

	/**
	 * Constructor
	 *
	 * @since 0.1
	 * @return void
	 */
	function Boom_Widget_Image_Rotator() {
		$this->textdomain = 'imagerotator';
		$widget_ops = array('description' => __( 'Displays a random image in the sidebar.', $this->textdomain ) );
		$this->WP_Widget( "boom-image-rotator", __( 'Image Rotator', $this->textdomain ), $widget_ops, null );
		$this->queue();
		add_action( 'admin_head-widgets.php', array( &$this, 'admin_scripts' ) );
	}

	/**
	 * Script & style queue for both front-page and admin area.
	 *
	 * Queues thickbox and media upload script for the widget options page
	 * and required scripts and styles for proper display.
	 * We make sure that we queue these only if we need them.
	 *
	 * @since 0.2
	 * @return void
	 */
	function queue() {
		global $pagenow;

		if( is_admin() && $pagenow == 'widgets.php' ) {
			add_thickbox();
			wp_enqueue_script( 'media-upload' );
		} else {
			/* queue script for the front-page only if slider has been chosen */
			$queue = false;
			$widget_instances = get_option( 'widget_boom-image-rotator' );
			if( is_active_widget( false, false, $this->id_base, true ) && $widget_instances ) {
				foreach( $widget_instances as $widget ) {
					if( $widget['method'] == 'easySlider' ) {
						/**
						 * Easy Slider 1.7
						 * @link http://cssglobe.com/post/5780/easy-slider-17-numeric-navigation-jquery-slider
						 */
						wp_enqueue_script( 'easySlider', plugins_url( 'js/jquery.easySlider1.7.js', __FILE__ ), array( 'jquery' ), '1.7' );
					}
					if( $widget['method'] == 'popeye' ) {
						/**
						 * jQuery.popeye 2.1
						 * @link http://dev.herr-schuessler.de/jquery/popeye/index.html
						 */
						wp_enqueue_script( 'popeye', plugins_url( 'popeye/jquery.popeye-2.1.min.js', __FILE__ ), array( 'jquery' ), '2.1' );
						wp_enqueue_style( 'popeye', plugins_url( 'popeye/jquery.popeye.css', __FILE__ ) );
						wp_enqueue_style( 'popeye.style', plugins_url( 'popeye/jquery.popeye.style.css', __FILE__ ) );
					}
				}
			}
		}
	}

	/**
	 * JavaScripts for admin page
	 *
	 * @since 0.1
	 * @return void
	 */
	function admin_scripts() { ?>
		<script>
		jQuery(function($){
			/* Media Upload control */
			var formfield = null;
			$('input.media_upload').live('click', function() {
				$('html').addClass('Image');
				formfield = $(this).prev();
				tb_show('', 'media-upload.php?type=image&TB_iframe=true');
				return false;
			});

			// user inserts file into post.
			//only run custom if user started process using the above process
			// window.send_to_editor(html) is how wp normally handle the received data
			window.original_send_to_editor = window.send_to_editor;
			window.send_to_editor = function(html){
				var fileurl;
				if (formfield != null) {
					fileurl = $('img',html).attr('src');
					formfield.val(fileurl);
					tb_remove();
					$('html').removeClass('Image');
					formfield = null;
				} else {
					window.original_send_to_editor(html);
				}
			};
			$('.remove').live('click', function(){
				$(this).parents('p').fadeOut(function(){
					$(this).remove();
				});
				return false;
			});
		});
		</script>
	<?php }

	/**
	 * Widget output for the front-end
	 *
	 * @since 0.1
	 * @return void
	 */
	function widget( $args, $instance ) {
		echo $args['before_widget'];
		if( $instance['title'] ) {
			$title = apply_filters('widget_title', $instance['title']);
			echo $args['before_title'] . $title . $args['after_title'];
		}
		if( $instance['images'] ) {
			if( $instance['resize'] ) {
				$this->width = $instance['width'];
				$this->height = $instance['height'];
				array_walk( $instance['images'], array( &$this, 'image_resize' ) );
			}
			#echo '<div class="image-rotator">';
			if( $instance['method'] == 'easySlider' ) {
				echo "<ul id='{$args['widget_id']}-slides'>";
				foreach( $instance['images'] as $image ) {
					echo "<li><img src='{$image}' alt='' /></li>";
				}
				echo "</ul>";
				echo "
					<script>
						jQuery('img:first', '#{$args['widget_id']}-slides').load(function(){
							var height = jQuery(this).height(),
								width = jQuery(this).width();
							jQuery('#{$args['widget_id']}-slides').parent().css({
								width: width,
								height: height
							}).easySlider({
								auto: true,
								continuous: true,
								controlsShow: false
							});
						});
					</script>
				";
			} elseif( $instance['method'] == 'popeye' ) {
				$this->_build_popeye( $instance );
			} elseif( $instance['method'] == 'random' ) {
				$image = array_rand( $instance['images'] );
				echo '<img src="' . $instance['images'][$image] . '" alt="" />';
			}
			#echo '</div>';
		}
		echo $args['after_widget'];
	}

	/**
	 * Creates the markups for jQuery Popeye
	 *
	 * @since 0.3
	 * @return void
	 */
	function _build_popeye( $options ) {
		extract( $options['popeye'] );

		$images = '';
		foreach( $options['images'] as $image ) {
			$images .= "<li style=''><a href='#'><img src='{$image}' alt='' /></a></li>";
		}
		$controls = '
            <div class="ppy-outer">
                <div class="ppy-stage">
                    <div class="ppy-counter">
                        <strong class="ppy-current"></strong> / <strong class="ppy-total"></strong> 
                    </div>
                </div>
                <div class="ppy-nav">
                    <div class="nav-wrap">
                        <a class="ppy-next" title="Next image">Next image</a>
                        <a class="ppy-prev" title="Previous image">Previous image</a>
                    </div>
                </div>
            </div>
		';
		echo "
			<div class='ppy-placeholder'>
				<div class='ppy {$theme}' style='width: {$options[width]}px; height: {$options[height]}px'>
					<ul class='ppy-imglist'>
						{$images}
					</ul>
					{$controls}
				</div><!-- .ppy -->
			</div><!-- .ppy-placeholder -->
			<script>
				jQuery(function($){
					$('#". $this->id ."').css({ width: ". $this->width .", height: ". $this->height ." }).find('.ppy-stage').css({ width: ". $this->width .", height: ". $this->height ." }).end().find('.ppy').popeye({ autoslide: true });
				});
			</script>
		";
	}

	function image_resize( &$value, $key ) {
		$width = $this->width;
		$height = $this->height;
		$value = plugins_url( 'timthumb.php', __FILE__ ) . "?src={$value}&amp;h={$height}&amp;w={$width}&amp;zc=1&amp;q=90";
	}

	/**
	 * Update widget options
	 *
	 * @since 0.1
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = stripslashes($new_instance['title']);
		$instance['method'] = stripslashes($new_instance['method']);
		$instance['resize'] = stripslashes($new_instance['resize']);
		$instance['width'] = stripslashes($new_instance['width']);
		$instance['height'] = stripslashes($new_instance['height']);
		$instance['popeye'] = $new_instance['popeye'];
		$instance['images'] = $new_instance['images'];

		return $instance;
	}

	/**
	 * Widget options form
	 *
	 * @since 0.1
	 * @return void
	 */
	function form( $instance ) { ?>
		<?php if( $instance ) : ?>
		<p>
			<label><?php _e('Title', $this->textdomain) ?></label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id('title') ?>" name="<?php echo $this->get_field_name('title') ?>" value="<?php echo $instance['title'] ?>" />
		</p>
		<p>
			<label><?php _e('Display Method', $this->textdomain) ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id('method') ?>" name="<?php echo $this->get_field_name('method') ?>">
				<option value="random" <?php selected( $instance['method'], 'random' ) ?>><?php _e('Random Image', $this->textdomain) ?></option>
				<option value="easySlider" <?php selected( $instance['method'], 'easySlider' ) ?>><?php _e('Easy Slider (Deprecated)', $this->textdomain) ?></option>
				<option value="popeye" <?php selected( $instance['method'], 'popeye' ) ?>><?php _e( 'Popeye', $this->textdomain ) ?></option>
			</select>
		</p>
		<div class="popeye-settings" style="display: none;">
			<!--<p>
				<label><?php _e( 'Theme', $this->textdomain ) ?></label>
				<select class="" name="<?php echo $this->get_field_name('popeye') ?>[theme]">
					<option value="ppy1" <?php selected( 'ppy1', $instance['popeye']['theme'] ) ?>>Style 1</option>
					<option value="ppy2" <?php selected( 'ppy2', $instance['popeye']['theme'] ) ?>>Style 2</option>
					<option value="ppy3" <?php selected( 'ppy3', $instance['popeye']['theme'] ) ?>>Style 3</option>
				</select>
			</p>-->
			<input type="hidden" name="<?php echo $this->get_field_name('popeye') ?>[theme]" value="ppy2" />
			<?php _e( 'Please make sure to specify width and height.', $this->textdomain ) ?>
		</div>
		<div class="images">
			<?php if( $instance['images'] ) : foreach( $instance['images'] as $image ) : ?>
			<p>
				<input type="text" name="<?php echo $this->get_field_name('images') ?>[]" value="<?php echo $image ?>" />
				<input type="button" class="button-secondary media_upload" value="<?php _e('Upload', $this->textdomain) ?>" />
				<a href="#" class="remove"><img src="<?php echo plugins_url( 'images/remove.gif', __FILE__ ) ?>" alt="<?php _e('Remove', $this->textdomain) ?>" width="16" height="16" style="vertical-align: middle" /></a>
			</p>
			<?php endforeach; endif; ?>
		</div>
		<p>
			<input type="button" class="button-secondary" id="<?php echo $this->get_field_id('add-button') ?>" value="<?php _e('Add Image', $this->textdomain) ?>" />
		</p>
		<script>
		jQuery(function($){
			$('#<?php echo $this->get_field_id('add-button') ?>').click(function(){
				$(this).parents('.widget-content').find('.images').append('<p><input type="text" name="<?php echo $this->get_field_name('images') ?>[]" /><input type="button" class="button-secondary media_upload" value="<?php _e('Upload', $this->textdomain) ?>" /><a href="#" class="remove"><img src="<?php echo plugins_url( 'images/remove.gif', __FILE__ ) ?>" alt="<?php _e('Remove', $this->textdomain) ?>" width="16" height="16" style="vertical-align: middle" /></a></p>');
			});
			$('#<?php echo $this->get_field_id( 'method' ) ?>').change(function(){
				if( $(this).val() == 'popeye' )
					$(this).closest('.widget-content').find('.popeye-settings').slideDown();
				else
					$(this).closest('.widget-content').find('.popeye-settings').slideUp();
			}).trigger('change');
		});
		</script>
		<p>
			<label>
			<input type="checkbox" name="<?php echo $this->get_field_name('resize') ?>" id="<?php echo $this->get_field_id('resize') ?>" value="1" <?php checked( $instance['resize'], 1 ) ?> />
			<?php _e( 'Resize images?', $this->textdomain ) ?>
			</label>
		</p>
		<p>
			<input type="text" size="4" name="<?php echo $this->get_field_name('width') ?>" id="<?php echo $this->get_field_id('width') ?>" value="<?php echo $instance['width'] ?>" />px &nbsp; X &nbsp; <input type="text" size="4" name="<?php echo $this->get_field_name('height') ?>" id="<?php echo $this->get_field_id('height') ?>" value="<?php echo $instance['height'] ?>" />px
		</p>
		<?php else :
			echo "<p>" . __( 'Click Save to continue!', $this->textdomain ) . "</p>";
		endif;
	}
}