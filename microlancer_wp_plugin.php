<?php

/*
Plugin Name: Microlancer WordPress Plugin
Plugin URI: http://dtbaker.net/
Description: Provides some basic Microlancer integration
Author: dtbaker
Version: 1.0.1
Author URI: http://dtbaker.net/
Copyright (C) 2013 dtbaker
*/

class microlancer_wordpress {
    public $url = '';
    public function __construct() {
        $this->url = "https://www.microlancer.com";

        // shortcode for any page of the blog:
        add_shortcode( 'microlancer_services', array($this, 'microlancer_wp_shortcode_print') );

        // start nasty hacks! yyyeeaaaaaaaaaaaahhhhhhh
        //add_action( 'wp_head', array( $this, 'microlancer_wp_shortcode_wp_head' ) );
        add_action( 'template_redirect', array($this, 'microlancer_page_template'));

        add_action( 'init', array($this,'microlancer_wp_endpoints') );
    }

    public function microlancer_wp_endpoints(){
        add_rewrite_endpoint( 'microlancerservice', EP_ALL);
        // above doesn't work well with our custom category, so we add a custom rewrite rule:
        add_rewrite_rule('^/(.*)/microlancerservice/(.*)/?','index.php?pagename=$matches[1]&microlancerservice=$matches[2]','top');
        //pagename=envato%2Fcustom-themeforest-or-codecanyon-modifications&microlancerservice=photography
    }
    public function microlancer_page_template(){
        global $wp,$wp_query,$post;
        if (is_singular() && $post){
            // what endpoints do we support?
            // todo: other endpoints?
            if($microlancer_service = $this->_current_microlancer_service()){
                $templatefilename = 'template-microlancer-service.php';
            }
            if(isset($templatefilename)){
                add_action( 'wp_title', array( $this, 'microlancer_wp_shortcode_page_title' ),100,3 );
                if( file_exists( get_template_directory() .'/'.$templatefilename)){
                    $return_template = get_template_directory() .'/'.$templatefilename;
                }else if (file_exists(dirname( __FILE__ ) . '/' . $templatefilename)) {
                    $return_template = dirname( __FILE__ ) . '/' . $templatefilename;
                }
                if (have_posts() && isset($return_template)) {
                    include($return_template);
                    die();
                } else {
                    $wp_query->is_404 = true;
                }
            }
        }
    }


    private function _current_microlancer_service(){
        global $wp_query;
        // look at the url, check if we're tring to load a faq article or not.
        $requested_microlancer_service = isset( $wp_query->query_vars['microlancerservice'] ) ? $wp_query->query_vars['microlancerservice'] : false;
        if($requested_microlancer_service){
            // pull our faq article in using wp_remote_get
            $services = $this->get_microlancer_services($requested_microlancer_service);
            return array(
                'current'=>$requested_microlancer_service,
                'services'=>$services
            );
        }
        return false;
    }
    /*private  function _sort_categories($a,$b){
        return strnatcasecmp($a['name'], $b['name']);
    }*/
    public function get_microlancer_services($category,$only_cached=false){
        $services = $this->_cache_get($category,($only_cached?9999999999:3600));
        if(!$services){
            $url = $this->url . '/explore/'.$category;
            $data = wp_remote_get($url);
            $page_data = is_array($data) && isset($data['body']) ? $data['body'] : '';
            //regex out the important bits
            if(preg_match_all('#<article class="l-list service">(.*)</article>#imsU',$page_data,$matches)){
                $services = array();
                //print_r($matches);
                foreach($matches[1] as $key=>$service){
                    if(preg_match('#<h1 class="service-title">\s*<a href="([^"]+)">([^<]+)</a>#',$service,$service_matches)){
                        preg_match('#>(\$\d+)<#',$service,$price);
                        preg_match('#src="//([^"]+)"#',$service,$thumb);
                        $this_service=array(
                            'html' => $service,
                            'price'=>$price[1],
                            'title' => $service_matches[2],
                            'url' => $this->url . $service_matches[1],
                            'thumb' => 'http://'.$thumb[1],
                        );
                        $services[]=$this_service;
                    }
                }
            }
            $this->_cache_add($category,$services);
        }
        return $services;
    }
    public function get_microlancer_categories(){
        // get a list of our faq articles by doing wp_remote_get
        $categories = $this->_cache_get('microlancer_services');
        if(!$categories){
            $url = $this->url.'/explore/';
            $data = wp_remote_get($url);
            $page_data = is_array($data) && isset($data['body']) ? $data['body'] : '';

            //echo htmlspecialchars($page_data);
            // regex out the available services.
            // hack, grab the list of categories from SerchFormProxyView
            // nah, don't want all categores, just parent ones.
            /*if(preg_match('#categories:\s*(\[[^\]]*\])#',$page_data,$matches)){
                //print_r($matches);
                $categories = json_decode($matches[1],true);
                if($categories){
                    usort($categories, array($this,'_sort_categories'));
                    $res = wp_cache_add( 'microlancer_services', $categories );
                }
            }*/
            if(preg_match_all('#<a href="/explore/([^"]+)" class="category-list-link">([^<]+)</a>#',$page_data,$matches)){
                //print_r($matches);
                $categories = array();
                foreach($matches[1] as $key=>$slug){
                    $categories[$slug] = trim(html_entity_decode($matches[2][$key]));
                }
                //print_r($categories);
                $this->_cache_add('microlancer_services',$categories);
            }
        }
        return $categories;
    }
    function microlancer_wp_shortcode_print($args) {
        $categories = $this->get_microlancer_categories();
        if($categories){
            ob_start();
            $templatefilename = 'template-microlancer-services.php';
            if( file_exists( get_template_directory() .'/'.$templatefilename)){
                $return_template = get_template_directory() .'/'.$templatefilename;
            }else if (file_exists(dirname( __FILE__ ) . '/' . $templatefilename)) {
                $return_template = dirname( __FILE__ ) . '/' . $templatefilename;
            }
            if (isset($return_template)) {
                include($return_template);
            }
            return ob_get_clean();
        }
    }
    function microlancer_wp_shortcode_page_title($title, $sep, $seplocation){
        $item = $this->_current_microlancer_service();
        if($item){
            $categories = $this->get_microlancer_categories();
            //remove_action('wp_head', 'rel_canonical');
            $title = "Service: ". (isset($categories[$item['current']]) ? htmlspecialchars($categories[$item['current']]) : '');
        }
        return $title;
    }
    function microlancer_wp_shortcode_wp_head(){
        /*$faq_item = $this->_current_microlancer_service();
        if($faq_item){
            ?>
            <!-- canonical url to stop duplicate content? -->
            <link rel="canonical" href="<?php echo htmlspecialchars($faq_item['url']);?>" />
            <?php
        }*/
    }

    // had issues with wp supercache, doing my own file based caching for this item.
    private function _cache_add($key,$data){
        $cache_file = dirname(__FILE__) . "/cache/".basename($key);
        file_put_contents($cache_file,serialize($data));
    }
    private function _cache_get($key,$time_limit=3600){
    $cache_file = dirname(__FILE__) . "/cache/".basename($key);
        if(is_file($cache_file) && filemtime($cache_file) > time()-$time_limit){
            return unserialize(file_get_contents($cache_file));
        }
        return false;
    }
}

new microlancer_wordpress();



