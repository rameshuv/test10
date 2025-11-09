<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
class BHG_Winner_Limits {
  const OPT='bhg_winner_limits_v1';
  public static function init(){
    add_action('admin_menu',[__CLASS__,'menu']);
    register_setting('bhg_limits',self::OPT,[__CLASS__,'sanitize']);
    add_filter('bhg_can_award_winner',[__CLASS__,'enforce'],10,3);
  }
  public static function defaults(){ return ['hunt'=>['max'=>1,'days'=>30],'tournament'=>['max'=>1,'days'=>365]]; }
  public static function sanitize($v){
    $d=self::defaults(); $out=$d;
    if(is_array($v)){ foreach($d as $k=>$_){ $out[$k]['max']=absint($v[$k]['max']??$d[$k]['max']); $out[$k]['days']=absint($v[$k]['days']??$d[$k]['days']); } }
    return $out;
  }
  public static function menu(){
    add_submenu_page('options-general.php',__('Bonus Hunt Limits','bonus-hunt-guesser'),__('Bonus Hunt Limits','bonus-hunt-guesser'),'manage_options','bhg_limits',[__CLASS__,'render']);
  }
  public static function render(){
    $v=get_option(self::OPT,self::defaults());
    echo '<div class="wrap"><h1>'.esc_html__('Winner Limits','bonus-hunt-guesser').'</h1><form method="post" action="options.php">'; settings_fields('bhg_limits');
    echo '<h2>'.esc_html__('Bonushunt','bonus-hunt-guesser').'</h2>';
    printf('<p><label>%s <input type="number" name="%s[hunt][max]" value="%d"/></label> <label>%s <input type="number" name="%s[hunt][days]" value="%d"/></label></p>',
      esc_html__('Max wins per user','bonus-hunt-guesser'), esc_attr(self::OPT), (int)$v['hunt']['max'],
      esc_html__('Rolling period (days)','bonus-hunt-guesser'), esc_attr(self::OPT), (int)$v['hunt']['days']);
    echo '<h2>'.esc_html__('Tournament','bonus-hunt-guesser').'</h2>';
    printf('<p><label>%s <input type="number" name="%s[tournament][max]" value="%d"/></label> <label>%s <input type="number" name="%s[tournament][days]" value="%d"/></label></p>',
      esc_html__('Max wins per user','bonus-hunt-guesser'), esc_attr(self::OPT), (int)$v['tournament']['max'],
      esc_html__('Rolling period (days)','bonus-hunt-guesser'), esc_attr(self::OPT), (int)$v['tournament']['days']);
    submit_button(); echo '</form></div>';
  }
  public static function enforce($allowed,$user_id,$context){
    $cfg=get_option(self::OPT,self::defaults()); $type=$context['type']??'hunt';
    $max=absint($cfg[$type]['max']??0); $days=absint($cfg[$type]['days']??0);
    if(0===$max) return true;
    $since=time()-DAY_IN_SECONDS*max(1,$days);
    $count=(int)apply_filters('bhg_wins_in_window',0,(int)$user_id,(string)$type,(int)$since);
    return $count<$max;
  }
}
BHG_Winner_Limits::init();
