<?php
/**
 * The template for displaying all microlancer services
 *
 * @package dtbaker microlancer
 * @since dtbaker microlancer 1.0
 */

get_header(); ?>

    <div class="row-fluid">

        <div class="span3">

            <h4>All Services:</h4>
        <?php $categories = $this->get_microlancer_categories();
            echo '<ul class="microlancer_services">';
                foreach($categories as $slug => $category){
                    echo '<li class="microlancer_service">';
                    echo '<a href="';
                    //echo add_query_arg('microlancerservice',$slug);
                    echo get_permalink().'microlancerservice/'.$slug.'/';
                    echo '" class="microlancer_link'.(isset($microlancer_service['current']) && $microlancer_service['current'] == $slug ? ' current' : '').'">';
                    echo htmlspecialchars($category);
                    echo '</a>';
                    echo '</li>';
                }
                echo '</ul>'; ?>
            </div>
        <div class="span9">

                    <!-- <h2><a href="<?php echo add_query_arg('microlancerservice',false);?>">Custom Modifications</a> &raquo;  </h2> -->
            <h3><?php echo $categories[$microlancer_service['current']];?> Services &raquo;</h3>


                <?php
                $per_row = 3;
                while(count($microlancer_service['services'])){
                    echo '<div class="row-fluid"><ul class="thumbnails">';
                    for($x=0;$x<$per_row;$x++){
                        $service = array_shift($microlancer_service['services']);
                        if($service){
                            ?>
                            <li class="span<?php echo 12/$per_row;?>">
                                <div class="thumbnail">
                                  <img src="<?php echo $service['thumb'];?>">
                                  <div class="caption">
                                    <h4><?php echo htmlspecialchars($service['title']);?></h4>
                                    <p>Changes only <?php echo $service['price'];?>.</p>
                                    <p><a href="<?php echo $service['url'];?>" target="_blank" class="btn btn-primary">More Information</a></p>
                                  </div>
                                </div>
                              </li>
                            <?php
                        }
                    }
                    echo '</ul></div>';
                }
                ?>
                </div>

                </div>
		</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>