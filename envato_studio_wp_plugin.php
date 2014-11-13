<?php

/*
Plugin Name: Envato Studio WordPress Plugin
Plugin URI: http://dtbaker.net/
Description: Provides some basic envato studio integration for affiliates
Author: dtbaker
Version: 1.0.2
History:
 1.0.1 - initial release
 1.0.2 - 2014-11-13 - renamed to support envato studio
Author URI: http://dtbaker.net/
Copyright (C) 2013 dtbaker
*/

class envato_studio_wordpress {
    public $url = '';
    public function __construct() {
        $this->url = "https://studio.envato.com";

        // shortcode for any page of the blog:
        add_shortcode( 'envato_studio_services', array($this, 'envato_studio_wp_shortcode_print') );

        // start nasty hacks! yyyeeaaaaaaaaaaaahhhhhhh
        //add_action( 'wp_head', array( $this, 'envato_studio_wp_shortcode_wp_head' ) );
        add_action( 'template_redirect', array($this, 'envato_studio_page_template'));

        add_action( 'init', array($this,'envato_studio_wp_endpoints') );
    }

    public function envato_studio_wp_endpoints(){
        add_rewrite_endpoint( 'studioservice', EP_ALL);
        // above doesn't work well with our custom category, so we add a custom rewrite rule:
        add_rewrite_rule('^/(.*)/studioservice/(.*)/?','index.php?pagename=$matches[1]&studioservice=$matches[2]','top');
        //pagename=envato%2Fcustom-themeforest-or-codecanyon-modifications&studioservice=photography
    }
    public function envato_studio_page_template(){
        global $wp,$wp_query,$post;
        if (is_singular() && $post){
            // what endpoints do we support?
            // todo: other endpoints?
            if($envato_studio_service = $this->_current_envato_studio_service()){
                $templatefilename = 'template-envato-studio-service.php';
            }
            if(isset($templatefilename)){
                add_action( 'wp_title', array( $this, 'envato_studio_wp_shortcode_page_title' ),100,3 );
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


    private function _current_envato_studio_service(){
        global $wp_query;
        // look at the url, check if we're tring to load a faq article or not.
        $requested_envato_studio_service = isset( $wp_query->query_vars['studioservice'] ) ? $wp_query->query_vars['studioservice'] : false;
        if($requested_envato_studio_service){
            // pull our faq article in using wp_remote_get
            $services = $this->get_envato_studio_services($requested_envato_studio_service);
            return array(
                'current'=>$requested_envato_studio_service,
                'services'=>$services
            );
        }
        return false;
    }
    /*private  function _sort_categories($a,$b){
        return strnatcasecmp($a['name'], $b['name']);
    }*/
    public function get_envato_studio_services($category,$only_cached=false){
        $services = $this->_cache_get($category,($only_cached?9999999999:3600));
        if(!$services){
            $url = $this->url . '/explore/'.$category;
            $data = wp_remote_get($url);
            $page_data = is_array($data) && isset($data['body']) ? $data['body'] : '';
            //regex out the important bits
            if(preg_match_all('#<article class="service"[^>]*>(.*)</article>#imsU',$page_data,$matches)){
                $services = array();
                //print_r($matches);
                foreach($matches[1] as $key=>$service){
                    if(preg_match('#<h4 class="service__title">\s*<a href="([^"]+)">([^<]+)</a>#',$service,$service_matches)){
                        if(preg_match('#<div class="price--large">\s*(\$\d+)\s*<#',$service,$price)) {
	                        preg_match_all( '#class="service__gallery__image" height="\d+" src="([^"]+)"#', $service, $thumb );
	                        $this_service = array(
		                        'html'  => $service,
		                        'price' => $price[1],
		                        'title' => $service_matches[2],
		                        'url'   => $this->url . $service_matches[1],
		                        'thumb' => isset($thumb[1]) ? $thumb[1] : array(0 => "#"),
	                        );
	                        $services[]=$this_service;
                        }
                    }
                }
            }
            $this->_cache_add($category,$services);
        }
        return $services;
    }
    public function get_envato_studio_categories(){
        // get a list of our faq articles by doing wp_remote_get
        $categories = $this->_cache_get('envato_studio_services');
        if(!$categories){
            $url = $this->url.'/explore/';
            $data = wp_remote_get($url);
            $page_data = is_array($data) && isset($data['body']) ? $data['body'] : '';

            //echo htmlspecialchars($page_data);
            //  don't want all categores, just parent ones.
	        /*<a class="header__categories__dropdown__top-level__link" href="/explore/business-online-marketing">Business &amp; Online Marketing</a>
              <ul class="header__categories__dropdown__subcategories" id="business-online-marketing">
                  <li class="header__categories__dropdown__subcategories__category">
                      <a class="header__categories__dropdown__subcategories__category__link" href="/explore/social-media-design">Social Media Design</a>
                  </li>*/
            if(preg_match_all('#<a class="header__categories__dropdown__top-level__link" href="/explore/([^"]+)">([^<]+)</a>#',$page_data,$matches)){
                //print_r($matches);
                $categories = array();
                foreach($matches[1] as $key=>$slug){
                    $categories[$slug] = trim(html_entity_decode($matches[2][$key]));
                }
                //print_r($categories);
                $this->_cache_add('envato_studio_services',$categories);
            }
        }
        return $categories;
    }
    function envato_studio_wp_shortcode_print($args) {
        $categories = $this->get_envato_studio_categories();
        if($categories){
            ob_start();
            $templatefilename = 'template-envato-studio-services.php';
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
    function envato_studio_wp_shortcode_page_title($title, $sep, $seplocation){
        $item = $this->_current_envato_studio_service();
        if($item){
            $categories = $this->get_envato_studio_categories();
            //remove_action('wp_head', 'rel_canonical');
            $title = "Custom Modifications: ". (isset($categories[$item['current']]) ? htmlspecialchars($categories[$item['current']]) : '');
        }
        return $title;
    }
    function envato_studio_wp_shortcode_wp_head(){
        /*$faq_item = $this->_current_envato_studio_service();
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

new envato_studio_wordpress();



