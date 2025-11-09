<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
class BHG_Hunt_Prizes {
  public static function init(){ add_action('add_meta_boxes',[__CLASS__,'box']); add_action('save_post_bhg_hunt',[__CLASS__,'save']); }
  public static function box(){ if(!post_type_exists('bhg_hunt')) return; add_meta_box('bhg_hunt_prizes',__('Prizes','bonus-hunt-guesser'),[__CLASS__,'render'],'bhg_hunt','normal'); }
  public static function render($post){
    wp_nonce_field('bhg_hunt_prizes','bhg_hunt_prizes_nonce');
    $regular=(array)get_post_meta($post->ID,'_bhg_prizes_regular',true);
    $premium=(array)get_post_meta($post->ID,'_bhg_prizes_premium',true);
    $prizes=get_posts(['post_type'=>'bhg_prize','numberposts'=>-1,'orderby'=>'title','order'=>'ASC']);
    echo '<p><strong>'.esc_html__('Regular Prize Set','bonus-hunt-guesser').'</strong></p><select name="bhg_prizes_regular[]" multiple size="6" style="width:100%">';
    foreach($prizes as $p){ printf('<option value="%d" %s>%s</option>',(int)$p->ID, selected(in_array($p->ID,$regular,true),true,false), esc_html($p->post_title)); }
    echo '</select><p><strong>'.esc_html__('Premium Prize Set (affiliate winners)','bonus-hunt-guesser').'</strong></p><select name="bhg_prizes_premium[]" multiple size="6" style="width:100%">';
    foreach($prizes as $p){ printf('<option value="%d" %s>%s</option>',(int)$p->ID, selected(in_array($p->ID,$premium,true),true,false), esc_html($p->post_title)); }
    echo '</select>';
  }
  public static function save($post_id){
    if(!isset($_POST['bhg_hunt_prizes_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['bhg_hunt_prizes_nonce'])),'bhg_hunt_prizes')) return;
    if(defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE) return;
    if(!current_user_can('edit_post',$post_id)) return;
    $reg=isset($_POST['bhg_prizes_regular'])?array_map('absint',(array)$_POST['bhg_prizes_regular']):[];
    $pre=isset($_POST['bhg_prizes_premium'])?array_map('absint',(array)$_POST['bhg_prizes_premium']):[];
    update_post_meta($post_id,'_bhg_prizes_regular',$reg);
    update_post_meta($post_id,'_bhg_prizes_premium',$pre);
  }
}
BHG_Hunt_Prizes::init();
