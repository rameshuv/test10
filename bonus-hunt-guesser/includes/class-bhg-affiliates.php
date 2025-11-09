<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
class BHG_Affiliates {
  const OPT='bhg_affiliate_sites';
  public static function init(){
    add_action('admin_menu',[__CLASS__,'menu']);
    register_setting('bhg_affiliates',self::OPT,[__CLASS__,'sanitize']);
    add_action('personal_options_update',[__CLASS__,'save_user']);
    add_action('edit_user_profile_update',[__CLASS__,'save_user']);
    add_action('show_user_profile',[__CLASS__,'user_fields']);
    add_action('edit_user_profile',[__CLASS__,'user_fields']);
  }
  public static function menu(){
    add_submenu_page('options-general.php',__('BHG Affiliates','bonus-hunt-guesser'),__('BHG Affiliates','bonus-hunt-guesser'),'manage_options','bhg_affiliates',[__CLASS__,'render']);
  }
  public static function sanitize($val){
    $out=[]; if(is_array($val)){ foreach($val as $slug=>$label){ $slug=sanitize_key($slug); $label=sanitize_text_field($label); if($slug&&$label){ $out[$slug]=$label; } } }
    return $out;
  }
  public static function render(){
    $sites=get_option(self::OPT,[]);
    echo '<div class="wrap"><h1>'.esc_html__('Affiliate Websites','bonus-hunt-guesser').'</h1>';
    echo '<form method="post" action="options.php">'; settings_fields('bhg_affiliates');
    echo '<table class="form-table"><thead><tr><th>Slug</th><th>Label</th></tr></thead><tbody>';
    echo '<tr><td><input type="text" name="'.esc_attr(self::OPT).'[site_a]" value="'.esc_attr(array_key_first($sites)??'').'"/></td><td><input type="text" name="'.esc_attr(self::OPT).'[label_a]" value="'.esc_attr(reset($sites)?:'').'"/></td></tr>';
    echo '</tbody></table>'; submit_button(); echo '</form></div>';
  }
  public static function user_fields($user){
    if(!current_user_can('edit_user',$user->ID))return;
    $sites=get_option(self::OPT,[]); if(empty($sites))return;
    echo '<h2>'.esc_html__('Affiliate Status','bonus-hunt-guesser').'</h2><table class="form-table">';
    foreach($sites as $slug=>$label){
      $val=get_user_meta($user->ID,'_bhg_aff_'.$slug,true);
      printf('<tr><th><label>%s</label></th><td><label><input type="checkbox" name="bhg_aff[%s]" value="1" %s/> %s</label></td></tr>',
        esc_html($label),esc_attr($slug),checked($val,'1',false),esc_html__('Affiliate for this website','bonus-hunt-guesser'));
    }
    echo '</table>';
  }
  public static function save_user($user_id){
    if(!current_user_can('edit_user',$user_id))return;
    $sites=get_option(self::OPT,[]); $in=isset($_POST['bhg_aff'])?(array)$_POST['bhg_aff']:[];
    foreach($sites as $slug=>$_){ update_user_meta($user_id,'_bhg_aff_'.$slug, isset($in[$slug])?'1':'0'); }
  }
}
BHG_Affiliates::init();
