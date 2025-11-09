<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
class BHG_Dashboard {
  public static function init(){ add_action('wp_dashboard_setup',[__CLASS__,'register']); }
  public static function register(){
    wp_add_dashboard_widget('bhg_latest_hunts',__('Bonus Hunts — Latest Results','bonus-hunt-guesser'),[__CLASS__,'render']);
  }
  public static function render(){
    $rows = apply_filters('bhg_latest_hunts',[]);
    if(empty($rows) && post_type_exists('bhg_hunt')){
      $q=new WP_Query(['post_type'=>'bhg_hunt','posts_per_page'=>3,'meta_key'=>'_bhg_closed_at','orderby'=>'meta_value_num','order'=>'DESC']);
      foreach($q->posts as $p){ $rows[]=['title'=>get_the_title($p),'winners'=>[],'start_balance'=>get_post_meta($p->ID,'_bhg_start_balance',true),'final_balance'=>get_post_meta($p->ID,'_bhg_final_balance',true),'closed_at'=>get_post_meta($p->ID,'_bhg_closed_at',true)]; }
    }
    echo '<table class="widefat striped"><thead><tr><th>Title</th><th>All Winners</th><th>Start Balance</th><th>Final Balance</th><th>Closed At</th></tr></thead><tbody>';
    if(empty($rows)){ echo '<tr><td colspan="5">'.esc_html__('No closed hunts yet.','bonus-hunt-guesser').'</td></tr>'; }
    else{
      foreach(array_slice($rows,0,3) as $r){
        echo '<tr>';
        echo '<td>'.esc_html($r['title']??'').'</td>';
        echo '<td>';
        if(!empty($r['winners'])){
          echo '<table class="widefat"><tbody>';
          foreach($r['winners'] as $w){ printf('<tr><td><strong>%s</strong></td><td>%s</td><td>%s</td></tr>',esc_html($w['user']??''),esc_html($w['guess']??''),esc_html($w['diff']??'')); }
          echo '</tbody></table>';
        } else { echo '—'; }
        echo '</td>';
        echo '<td style="text-align:left">'.esc_html($r['start_balance']??'').'</td>';
        echo '<td style="text-align:left">'.esc_html($r['final_balance']??'').'</td>';
        echo '<td>'.esc_html($r['closed_at']??'').'</td>';
        echo '</tr>';
      }
    }
    echo '</tbody></table>';
  }
}
BHG_Dashboard::init();
