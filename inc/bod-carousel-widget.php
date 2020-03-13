<?php

 
// ************************************************************* 
// ****
// Create a widget which displays the latest posts in a carrosel 
// ****
// *************************************************************

function bod_carousel_register_image_sizes(){
	add_image_size('bod-carousel', 600, 400, true);
}
add_action('after_setup_theme', 'bod_carousel_register_image_sizes');

function bod_carousel_register_scripts() {	


    wp_register_style( 'bod-carousel-style',
        plugin_dir_url (__DIR__) .  'lib/slick/slick-mod.css',
		array(),
		'1.0'
    );
		
    wp_register_script( 'bod-carousel-script',
        plugin_dir_url (__DIR__) .  'lib/slick/slick.min.js',
		array('jquery'),
		'1.0',
		true
    );		
}

add_action( 'wp_enqueue_scripts', 'bod_carousel_register_scripts' );

function bod_carousel_register_admin_scripts() {	

    wp_register_style( 'bod-admin-style',
        plugin_dir_url (__DIR__) . 'css/bod-admin.css',
		array(),
		'1.0'		
    );
	
}

add_action( 'admin_enqueue_scripts', 'bod_carousel_register_admin_scripts' );
 
 
// Widget Class  
 
class bod_carousel_widget extends WP_Widget {
	
	public function __construct() {
		$widget_options = array( 
		  'classname' => 'bod_carousel_widget',
		  'description' => 'This is a Carousel Widget',
		);
		parent::__construct( 'bod_carousel_widget', 'Bod Post Carousel Widget', $widget_options );
	}
	  
	// execute widget code
	  
	public function widget( $args, $instance ) {
		
		// enqueue required assets
		
		wp_enqueue_style('bod-carousel-style');
		wp_enqueue_script('bod-carousel-script');
		
	  	
	  	echo $args['before_widget']; 
		
		// Widget Title
		
		if (!empty($instance[ 'title' ])) {
			$title = apply_filters( 'widget_title', $instance[ 'title' ] );
			echo $args['before_title'] . $title . $args['after_title']; 
		}
		
		// Heading with head tags
		
		if (!empty($instance[ 'heading' ])) {
			
			$center_head = ! empty( $instance['center_head'] ) ? $instance['center_head'] : false; 
			$output = '<';

			if (!empty($instance[ 'head_tag' ])) {
				$output .= esc_html($instance[ 'head_tag' ]); 
			} else {
				$output .= 'h2'; 
			}
			
			if ($center_head) $output .= ' style="text-align:center;"';
			$output .= ' class="bod_carousel_head">' .  esc_html($instance[ 'heading' ]) . '</';
			
			if (!empty($instance[ 'head_tag' ])) {
				$output .= esc_html($instance[ 'head_tag' ]) . '>'; 
			} else {
				$output .= 'h2>'; 
			}
			
			echo $output;			
		}
		
		// Description
		
		if (!empty($instance['desc'])) {
			$center_desc = ! empty( $instance['center_desc'] ) ? $instance['center_desc'] : false; 
			if ($center_desc) {
				echo '<p style="text-align:center;" class="bod_carousel_desc">' . esc_html($instance['desc']) . '</p>';
			} else {
				echo '<p class="bod_carousel_desc">' . esc_html($instance['desc']) . '</p>';
			}
		}       
		
		// kick of the query to get the posts
		$posts_per_page = ! empty ( intval($instance[ 'carousel_total_slides' ])) ? intval($instance[ 'carousel_total_slides' ]) : 10;
		
		$query_results = new WP_Query(array( 'post_type' => 'post', 'posts_per_page' => $posts_per_page ));
		
		// if we got some results from the query then process them
		
		if ($query_results->have_posts()) : ?>
		
			<div class='bod_carousel_thumb'>
				<?php 		
				// loop round the query results / posts
		
				while ($query_results->have_posts()): 
					$query_results->the_post(); // get the next post
					?> 
					<div class="bod_carousel_item"> 
						<article <?php post_class(); ?> >
						
							<?php if (has_post_thumbnail() ): // if post has a thumbnail ?>
                            
								<div class='bod_thumb_carousel_image'>
									<a ref='<?php the_permalink(); ?>'><?php the_post_thumbnail('bod-carousel'); ?></a>
                                 
                                    <div class='bod_image_info'>
                                        <div class='bod_image_text'> 
                                        
                                            <!-- Output the post title -->
                                            
                                            <?php
												the_title('<h3 class="bod_carousel_post_title"><a href="'.get_permalink().'" title="' . get_the_title() . '" >','</a></h3>'); 
											?>
                                            
                                            <!-- Output the categories associated with post -->
                                            
                                            <?php $categories = get_the_category();
                                            if ( !empty($categories) ) {
                                            	
                                                $output = '<div class="bod_carousel_post_cat">';
												$seperator = ', ';
												$firstCat = true;
                                                foreach ( $categories as $category) {
													if ($firstCat) {
														$firstCat = false;
													} else {
														$output .= $seperator;
													};
													
                                               		$output .= '<a href="' . esc_url(get_category_link($category->term_id)) . '" alt="' . esc_attr( sprintf(__('View all the posts in %s','bod'), $category->name )) . '" >' . esc_html($category->name) . '</a>';
                                                
                                                } // end foreach
                                                $output .= '</div>';
                                                echo $output;
                                            } ?>
                                            
                                        </div>
                                    </div>   
                                    
                                    <div class='bod_thumb_carousel_image_overlay'></div>                                    
								</div>
                                                            
							<?php endif; ?>
                            
                            <div class='bod_carousel_text_wrap'>
                            	<div class="bod_carousel_title">
                            		<?php the_title('<h3 class="bod_carousel_post_title"><a href="'.get_permalink().'" title="' . get_the_title() . '" >','</a></h3>'); ?>
                                </div>
                                
                                <div class="bod_carousel_excerpt">
									<?php the_excerpt(); ?>
                                </div>                              
                            </div>

						</article>
					</div> <!-- bod_carousel_item -->
					
				<?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
			</div>
		
        
        	<?php
			$carousel_slides = ! empty( $instance['carousel_slides'] ) ? $instance['carousel_slides'] : 3;
        	$carousel_scroll = ! empty( $instance['carousel_scroll'] ) ? $instance['carousel_scroll'] : 2; 
			?>
        
			<script>
                
                (function ($) {
                    $(document).ready(function () {
    
                        // initiate the carousel slideshow using slick
	
			
                        $('.bod_carousel_thumb').slick({
                            arrows: <?php echo isset($instance['carousel_arrow']) ? ( $instance['carousel_arrow'] ? 'true' : 'false') : 'true';?>,
                            dots: <?php echo isset($instance['carousel_dot']) ? ( $instance['carousel_dot'] ? 'true' : 'false') : 'true';?>,
                            infinite: true,
                            autoplay: true,
                            autoplaySpeed: 3000,
                            speed: 300,
                            fade: false,
                            pauseOnHover: true,
                            slidesToShow: <?php echo intval($carousel_slides); ?>,
                            slidesToScroll: <?php echo intval($carousel_scroll); ?>,
                            responsive: [
                                {
                                    breakpoint: 800,
                                    settings: {
                                        slidesToShow: 2,
                                        slidesToScroll: 1
                                    }
                                },
                                {
                                    breakpoint: 480,
                                    settings: {
                                        slidesToShow: 1,
                                        slidesToScroll: 1
                                    }
                                }
                            ]					
						});
                    
            
                    });
                })(jQuery);		
            
            </script>
		<?php endif;
		echo $args['after_widget'];
	}
	
	
	// *************************************
	// form to allow user defined parameters
	// ************************************* 
	
	public function form( $instance ) {
		
		wp_enqueue_style('bod-carousel-admin-style');
		
		// get the fields if they exist
		
		$title = ! empty( $instance['title'] ) ? $instance['title'] : ''; 
		$heading = ! empty( $instance['heading'] ) ? $instance['heading'] : ''; 
		$head_tag = ! empty( $instance['head_tag'] ) ? $instance['head_tag'] : 'H3';
        $instance['center_head'] = isset( $instance['center_head'] ) ? $instance['center_head'] : false; 
        $desc = ! empty( $instance['desc'] ) ? $instance['desc'] : ''; 
        $instance['center_desc'] = isset( $instance['center_desc'] ) ? $instance['center_desc'] : false; 
		$carousel_total_slides = ! empty( $instance['carousel_total_slides'] ) ? $instance['carousel_total_slides'] + 1 : 10;
        $carousel_slides = ! empty( $instance['carousel_slides'] ) ? $instance['carousel_slides'] : 3;
        $carousel_scroll = ! empty( $instance['carousel_scroll'] ) ? $instance['carousel_scroll'] : 2; 
		$instance['carousel_arrow'] = isset( $instance['carousel_arrow'] ) ? $instance['carousel_arrow'] : true; 
        $instance['carousel_dot'] = isset( $instance['carousel_dot'] ) ? $instance['carousel_dot'] : true;
	
		?>

        
        <div id="bod-carousel-admin">
        	
            <!-- Title -->
        
            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title','bod_carousel') ?></label>
                <input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" />
            </p>
            
            <!-- Heading -->
        
            <p>
                <label for="<?php echo $this->get_field_id( 'heading' ); ?>"><?php _e('Heading','bod_carousel') ?></label>
                <input type="text" id="<?php echo $this->get_field_id( 'heading' ); ?>" name="<?php echo $this->get_field_name( 'heading' ); ?>" value="<?php echo esc_attr( $heading ); ?>" />
            </p>            
            
            <!-- Heading Tag -->
            
            <p>
                <label for="<?php echo $this->get_field_id( 'head_tag' ); ?>"><?php _e('Heading Tag','bod_carousel') ?></label>
                <select id="<?php echo $this->get_field_id( 'head_tag' ); ?>" name="<?php echo $this->get_field_name( 'head_tag' ); ?>" >
                    <option value='H1' <?php if ($head_tag == 'H1' ) echo 'selected="selected"'; ?>>H1</option>
                    <option value='H2' <?php if ($head_tag == 'H2' ) echo 'selected="selected"'; ?>>H2</option>
                    <option value='H3' <?php if ($head_tag == 'H3' ) echo 'selected="selected"'; ?>>H3</option>
                    <option value='H4' <?php if ($head_tag == 'H4' ) echo 'selected="selected"'; ?>>H4</option>
                </select>
                
            </p>
            
            <!-- Center header -->
            


            <p>
                <input type="checkbox" id="<?php echo $this->get_field_id( 'center_head' ); ?>" name="<?php echo $this->get_field_name( 'center_head' ); ?>" <?php checked ( $instance['center_head'] ); ?> />
                <label class='checkboxLabel' for="<?php echo $this->get_field_id( 'center_head' ); ?>"><?php _e('Center Heading','bod_carousel') ?></label>
            </p>
            
                                
            <!-- Description -->
            
            <p>
                <label for="<?php echo $this->get_field_id( 'desc' ); ?>"><?php _e('Description','bod_carousel') ?></label>
                <textarea  id="<?php echo $this->get_field_id( 'desc' ); ?>" name="<?php echo $this->get_field_name( 'desc' ); ?>" rows='3'><?php echo esc_attr( $desc ); ?></textarea>
            </p>
            
            
            <!-- Center Description -->
            
            <p>                
                <input type="checkbox" id="<?php echo $this->get_field_id( 'center_desc' ); ?>" name="<?php echo $this->get_field_name( 'center_desc' ); ?>" <?php checked ( $instance['center_desc'] ); ?> />
                <label class='checkboxLabel' for="<?php echo $this->get_field_id( 'center_desc' ); ?>"><?php _e('Center Description','bod_carousel') ?></label>
            </p>
            
            <!-- Carousel Options -->
            
            <div class='bod-option-heading'>Carousel Options</div>
            
            <!-- Carousel total number of slides (posts) to load -->
        
            <p>
                <label for="<?php echo $this->get_field_id( 'carousel_total_slides' ); ?>"><?php _e('Total Number of Slides in Carousel','bod_carousel') ?></label>
                <input type="number" min='1' max='10' id="<?php echo $this->get_field_id( 'carousel_total_slides' ); ?>" name="<?php echo $this->get_field_name( 'carousel_total_slides' ); ?>" value="<?php echo intval( $carousel_total_slides ); ?>" />
            </p>         
                      
            <!-- Carousel number of slides to display -->
        
            <p>
                <label for="<?php echo $this->get_field_id( 'carousel_slides' ); ?>"><?php _e('Display Number of Slides','bod_carousel') ?></label>
                <input type="number" min='1' max='5' id="<?php echo $this->get_field_id( 'carousel_slides' ); ?>" name="<?php echo $this->get_field_name( 'carousel_slides' ); ?>" value="<?php echo intval( $carousel_slides ); ?>" />
            </p>                
            
            <!-- Carousel number of slides to scroll -->
        
            <p>
                <label for="<?php echo $this->get_field_id( 'carousel_scroll' ); ?>"><?php _e('Scroll Number of Slides','bod_carousel') ?></label>
                <input type="number" min='1' max='5' id="<?php echo $this->get_field_id( 'carousel_scroll' ); ?>" name="<?php echo $this->get_field_name( 'carousel_scroll' ); ?>" value="<?php echo intval( $carousel_scroll ); ?>" />
            </p>    
            
            <!-- Carousel display arrow navigation -->
            
            <p>
                <input type="checkbox" id="<?php echo $this->get_field_id( 'carousel_arrow' ); ?>" name="<?php echo $this->get_field_name( 'carousel_arrow' ); ?>" <?php checked ( $instance['carousel_arrow'] ); ?> />
                <label class='checkboxLabel' for="<?php echo $this->get_field_id( 'carousel_arrow' ); ?>"><?php _e('Arrow Navigation','bod_carousel') ?></label>                
            </p>
            
            <!-- Carousel display dot navigation -->
            
            <p>                
                <input type="checkbox" id="<?php echo $this->get_field_id( 'carousel_dot' ); ?>" name="<?php echo $this->get_field_name( 'carousel_dot' ); ?>" <?php checked ( $instance['carousel_dot'] ); ?>  />
                <label class='checkboxLabel' for="<?php echo $this->get_field_id( 'carousel_arrow' ); ?>"><?php _e('Dot Navigation','bod_carousel') ?></label>
            </p>
                                                
		</div> <!-- end bod-carousel-admin -->
		<?php 
	}
	
	
	// *************************
	// update widget paramneters
	// *************************
	
	public function update( $new_instance, $old_instance ) {
  		$instance = $old_instance;
  		$instance[ 'title' ] = sanitize_text_field( $new_instance[ 'title' ] );
  		$instance[ 'heading' ] = sanitize_text_field( $new_instance[ 'heading' ] );		
		$instance[ 'head_tag' ] = sanitize_text_field( $new_instance[ 'head_tag' ] );
		$instance[ 'center_head' ] = isset ($new_instance[ 'center_head' ]) ? 1 : 0;		
		$instance[ 'desc' ] = sanitize_textarea_field( $new_instance[ 'desc' ] );
		$instance[ 'center_desc' ] = isset($new_instance[ 'center_desc' ]) ? 1 : 0;		
		
		// Make sure the values for carousel_total_slides (number of posts to get from database), carousel_slides 
		// (number of slides to display in carousel), and carousel_scroll (number of slides to scroll at one time) are valid. 		// total slides should  not exceed 10, display slides should be <= total slidea and number to scroll <= display
		// slides
		
		$bod_max_slides = apply_filters( 'link_title', 10 );
		
		$instance[ 'carousel_total_slides' ] = intval( $new_instance[ 'carousel_total_slides' ] ) <= $bod_max_slides ? intval( $new_instance[ 'carousel_total_slides' ] ) : $bod_max_slides ;		
		$instance[ 'carousel_slides' ] = intval( $new_instance[ 'carousel_slides' ] ) <= $instance[ 'carousel_total_slides' ]? intval( $new_instance[ 'carousel_slides' ] ) : $instance[ 'carousel_total_slides' ];
		$instance[ 'carousel_scroll' ] = intval( $new_instance[ 'carousel_scroll' ] ) <= $instance[ 'carousel_slides' ] ? intval( $new_instance[ 'carousel_scroll' ] ) :$instance[ 'carousel_slides' ] ;
		$instance[ 'carousel_total_slides' ] --; // database row count starts at 0 so subtract 1 from total
		
		$instance[ 'carousel_arrow' ] = isset($new_instance[ 'carousel_arrow' ]) ? 1 : 0;		
		$instance[ 'carousel_dot' ] = isset($new_instance[ 'carousel_dot' ]) ? 1 : 0;		
		
  		return $instance;
	}
	
}
 
function bod_register_carousel_widget() { 
  register_widget( 'bod_carousel_Widget' );
}
add_action( 'widgets_init', 'bod_register_carousel_widget' );



?>