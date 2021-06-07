<?php
namespace DMCK_WP_MEDIA_PLUGIN;
class DMCK {
	public $DMCK_MENU;
	function __construct() {
		$this->DMCK_MENU = (object) array(
			"title"=>"&#64;DMCK",
			"slug" => "dmck-menu-collection"
		);		
	}
	function menu_create(){
		global $wp_admin_bar;
        if( ! $this->menu_exists($this->DMCK_MENU->title, false)){
            $wp_admin_bar->add_menu(
                array(
                    'id' => $this->DMCK_MENU->slug,
                    'title' => $this->DMCK_MENU->title,
                    'href' => "#",
                    'meta'  => array(
                            'title' => $this->DMCK_MENU->title,
                            'class' => $this->DMCK_MENU->slug
                    )
                )
            );          
        }
		return $this->DMCK_MENU->slug;		
	}
	function menu_exists( $handle, $sub = false ){
		if( !is_admin() || (defined('DOING_AJAX') && DOING_AJAX) ){return false;}		  	
		global $menu, $submenu;
		$check_menu = $sub ? $submenu : $menu;
		if( empty( $check_menu ) ){return false;}		  	
		foreach( $check_menu as $k => $item ){
			if( $sub ){
				foreach( $item as $sm ){
					if($handle == $sm[2]){return true;}
				}
			} else {
				if( $handle == $item[2] ){return true;}					
			}
		}
		return false;
	}		
}