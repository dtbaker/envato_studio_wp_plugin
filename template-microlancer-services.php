<?php

echo '<ul class="microlancer_services">';
foreach($categories as $slug => $category){
    echo '<li class="microlancer_service">';
    echo '<a href="';
    //echo add_query_arg('microlancerservice',$slug);
    echo get_permalink().'microlancerservice/'.$slug.'/';
    echo '" class="microlancer_link">';
    echo htmlspecialchars($category);
    echo '</a>';
    echo '</li>';
}
echo '</ul>';

$per_row = 3;
foreach($categories as $slug => $category){
    $services = $this->get_microlancer_services($slug,true);
    $min_price = 99999;
    foreach($services as $service){
        $min_price = min(preg_replace('#[^\d]#','',$service['price']), $min_price);
    }
    ?>
    <div class="row-fluid">
        <h4><?php echo $category;?> (<?php echo count($services);?> services from $<?php echo $min_price;?>)</h4>
        <ul class="thumbnails">
                    <?php
            $keys = array_rand($services,$per_row);
            for($x=0;$x<$per_row;$x++){
                        $service = $services[$keys[$x]];
                        if($service){
                            ?>
                            <li class="span<?php echo 12/($per_row+1);?>">
                                <div class="thumbnail">
                                  <img src="<?php echo $service['thumb'];?>">
                                  <div class="caption">
                                    <h4><?php echo htmlspecialchars($service['title']);?></h4>
                                  </div>
                                </div>
                              </li>
                            <?php
                        }
                    } ?>
            <li class="span<?php echo 12/($per_row+1);?>">
                <div class="thumbnail">
                  <div class="caption">
                    <p><a href="<?php
                        //echo add_query_arg('microlancerservice',$slug);
                        echo get_permalink().'microlancerservice/'.$slug.'/';
                        ?>" class="btn btn-primary">View <?php echo count($services);?> services &raquo;</a></p>
                  </div>
                </div>
              </li>
            </ul>
    </div>

<?php } ?>