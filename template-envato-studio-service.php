<?php
/**
 * The template for displaying all envato_studio services
 *
 * @package Envato Studio WordPress Plugin
 * @since Envato Studio WordPress Plugin 1.0.2
 */

get_header();
$categories = $this->get_envato_studio_categories();
?>

    <div class="row">

        <?php /* <div class="col-sm-3">

            <h4>All Services:</h4>
        <?php
            echo '<ul class="envato_studio_services">';
                foreach($categories as $slug => $category){
                    echo '<li class="envato_studio_service">';
                    echo '<a href="';
                    //echo add_query_arg('studioservice',$slug);
                    echo get_permalink().'studioservice/'.$slug.'/';
                    echo '" class="envato_studio_link'.(isset($envato_studio_service['current']) && $envato_studio_service['current'] == $slug ? ' current' : '').'">';
                    echo htmlspecialchars($category);
                    echo '</a>';
                    echo '</li>';
                }
                echo '</ul>'; ?>
            </div>
        <div class="col-sm-9">
		*/ ?>
            <h3><a href="<?php echo get_permalink();?>">Custom Modifications</a> &raquo; <?php echo $categories[$envato_studio_service['current']];?> Services &raquo;</h3>


                <?php
                $per_row = 3;
                while(count($envato_studio_service['services'])){
                    echo '<div class="row">';
                    for($x=0;$x<$per_row;$x++){
                        $service = array_shift($envato_studio_service['services']);
                        if($service){
                            ?>
	                        <div class="col-sm-<?php echo 12/($per_row+1);?>">
		                        <div class="thumbnail">
		                          <img src="<?php echo $service['thumb'][0];?>">
		                          <div class="caption">
		                            <p><?php echo ($service['title']);?></p>
		                          <p>Price: <?php echo $service['price'];?></p>
                                    <p class="text-center"><a href="<?php echo $service['url'];?>" rel="nofollow" target="_blank" class="btn btn-success">Details &raquo;</a></p>
		                          </div>
		                        </div>
		                    </div>
                            <?php
                        }
                    }
                    echo '</div>';
                }
                ?>
                </div>

          <?php /*   </div> */ ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>