<?php

echo '<ul class="envato_studio_services">';
foreach($categories as $slug => $category){
    echo '<li class="envato_studio_service">';
    echo '<a href="';
    //echo add_query_arg('envato_studioservice',$slug);
    echo get_permalink().'studioservice/'.$slug.'/';
    echo '" class="envato_studio_link">';
    echo htmlspecialchars($category);
    echo '</a>';
    echo '</li>';
}
echo '</ul>';

$per_row = 3;
foreach($categories as $slug => $category){
    $services = $this->get_envato_studio_services($slug,true);
    $min_price = 99999;
    foreach($services as $service){
        $min_price = min(preg_replace('#[^\d]#','',$service['price']), $min_price);
    }
    ?>

    <div class="row">
        <h4><?php echo $category;?> (starting from $<?php echo $min_price;?>)</h4>

        <div class="row">
            <?php
	        $keys = array_rand($services,$per_row);
	        for($x=0;$x<$per_row;$x++){
                $service = $services[$keys[$x]];
                if($service){
                    ?>
                    <div class="col-sm-<?php echo 12/($per_row+1);?>">
                        <div class="thumbnail">
                          <img src="<?php echo $service['thumb'][0];?>">
                          <div class="caption text-center">
                            <p><?php echo ($service['title']);?></p>
                          </div>
                        </div>
                    </div>
                    <?php
                }
            } ?>
	        <div class="col-sm-<?php echo 12/($per_row+1);?>">
                <div class="thumbnail">
                  <div class="caption text-center">
                    <p><a href="<?php
		            //echo add_query_arg('envato_studioservice',$slug);
		            echo get_permalink().'studioservice/'.$slug.'/';
		            ?>" class="btn btn-success">View More &raquo;</a></p>
                  </div>
                </div>
            </div>

        </div>



    </div>

<?php } ?>