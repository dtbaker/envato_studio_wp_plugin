<?php
/**
 * The template for displaying all wiki articles.
 *
 * @package dtbaker wiki
 * @since dtbaker wiki 1.0
 */

get_header(); ?>

    <div class="row-fluid">

        <div class="span4">

            <h4>Services</h4>
        <?php $categories = $this->get_microlancer_categories();
            echo '<ul class="microlancer_services">';
                foreach($categories as $slug => $category){
                    echo '<li class="microlancer_service">';
                    echo '<a href="'.add_query_arg('microlancerservice',$slug).'" class="microlancer_link'.(isset($microlancer_service['current']) && $microlancer_service['current'] == $slug ? ' current' : '').'">';
                    echo htmlspecialchars($category);
                    echo '</a>';
                    echo '</li>';
                }
                echo '</ul>'; ?>
            </div>
        <div class="span8">

                    <!-- <h2><a href="<?php echo add_query_arg('microlancerservice',false);?>">Custom Modifications</a> &raquo;  </h2> -->
                    <ul>
                    <?php
                    //print_r($microlancer_service);
                    foreach($microlancer_service['services'] as $service){
                        ?>
                        <li>
                            <a href="<?php echo $service['url'];?>"><?php echo htmlspecialchars($service['title']);?></a>
                            <a href="<?php echo $service['url'];?>"><img src="<?php echo $service['thumb'];?>"></a>
                            <?php echo $service['price'];?>
                        </li>
                        <?php
                    }
                    ?>
                    </ul>
                </div>
		</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>