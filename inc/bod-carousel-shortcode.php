<?php

 
// ************************************************************* 
// ****
// Create a shortcode which displays the latest posts in a carrosel 
// ****
// *************************************************************
// Shortcode bod-carousel 
// Param load_posts - int - number of posts to load
// Param disp_posts - int - number of posts to display
// Param scroll_posts - int - number of posts to scroll 

 //defines the functionality for the location shortcode
 class bod_carousel_shortcode{
 	
	//on initialize
	public function __construct(){
		add_action('init', array($this,'register_bod_carousel_shortcode')); //shortcode
		add_action( 'wp_enqueue_scripts', array($this,'bod_carousel_register_shortcode_scripts') );
	}

	//location shortcode
	public function register_bod_carousel_shortcode(){
		add_shortcode('bod-carousel', array($this,'bod_carousel_output'));
	}
	
	// register scripts 
	public function bod_carousel_register_shortcode_scripts() {	

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
	
	//shortcode display
	public function bod_carousel_output($atts, $content = '', $tag){
		
		wp_enqueue_style('bod-carousel-style');
		wp_enqueue_script('bod-carousel-script');
				
		// shortcode_atts acts like a merge takes values from $atts and merges with first array but only for keys in 1st array. extract then takes the array reszult and creates individual variables  
		
		extract ( shortcode_atts(array(
			'load_posts' => 10,
			'disp_posts' => 3,
			'scroll_posts' => 1)
		,$atts,$tag));
		
		// kick of the query to get the posts
		$posts_per_page = intval($load_posts);
		
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
			$carousel_slides = intval($disp_posts);
        	$carousel_scroll = intval($scroll_posts); 
			?>
        
			<script>
                
                (function ($) {
                    $(document).ready(function () {
    
                        // initiate the carousel slideshow using slick
	
			
                        $('.bod_carousel_thumb').slick({
                            arrows: true,
                            dots: true,
                            infinite: true,
                            autoplay: true,
                            autoplaySpeed: 3000,
                            speed: 300,
                            fade: false,
                            pauseOnHover: true,
                            slidesToShow: <?php echo $carousel_slides; ?>,
                            slidesToScroll: <?php echo $carousel_scroll; ?>,
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

	}

 }
 $bod_carousel_shortcode = new bod_carousel_shortcode;

?>