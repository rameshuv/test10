<?php
/**
 * Prize section view.
 *
 * @package Bonus_Hunt_Guesser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$layout        = isset( $view_layout ) ? $view_layout : ( isset( $layout ) ? $layout : 'grid' );
$size          = isset( $view_size ) ? $view_size : ( isset( $size ) ? $size : 'medium' );
$prizes        = isset( $view_prizes ) && is_array( $view_prizes ) ? $view_prizes : ( isset( $prizes ) && is_array( $prizes ) ? $prizes : array() );
$display       = isset( $view_display ) && is_array( $view_display ) ? $view_display : ( isset( $display ) && is_array( $display ) ? $display : array() );
$context       = isset( $view_context ) && is_array( $view_context ) ? $view_context : ( isset( $context ) && is_array( $context ) ? $context : array() );
$title_text    = isset( $view_title ) ? $view_title : ( isset( $title_text ) ? $title_text : '' );
$count         = isset( $view_count ) ? (int) $view_count : count( $prizes );
$pages         = isset( $view_pages ) ? max( 1, (int) $view_pages ) : max( 1, (int) ceil( $count / max( 1, isset( $context['carousel_visible'] ) ? (int) $context['carousel_visible'] : 1 ) ) );
$card_renderer = ( isset( $view_card_renderer ) && is_callable( $view_card_renderer ) ) ? $view_card_renderer : ( ( isset( $card_renderer ) && is_callable( $card_renderer ) ) ? $card_renderer : null );
$visible       = isset( $context['carousel_visible'] ) ? max( 1, (int) $context['carousel_visible'] ) : 1;
$autoplay      = ! empty( $context['carousel_autoplay'] );
$interval      = isset( $context['carousel_interval'] ) ? max( 1000, (int) $context['carousel_interval'] ) : 5000;
?>
<div class="bhg-prizes-block bhg-prizes-layout-<?php echo esc_attr( $layout ); ?> size-<?php echo esc_attr( $size ); ?>">
        <?php if ( '' !== $title_text ) : ?>
                <h4 class="bhg-prizes-title"><?php echo esc_html( $title_text ); ?></h4>
        <?php endif; ?>

        <?php if ( 'carousel' === $layout && $card_renderer ) : ?>
                <?php $show_nav = $pages > 1; ?>
                <div class="bhg-prize-carousel" data-count="<?php echo esc_attr( $count ); ?>" data-visible="<?php echo esc_attr( $visible ); ?>" data-pages="<?php echo esc_attr( $pages ); ?>" data-autoplay="<?php echo $autoplay ? '1' : '0'; ?>" data-interval="<?php echo esc_attr( $interval ); ?>">
                        <?php if ( $show_nav ) : ?>
                                <button type="button" class="bhg-prize-nav bhg-prize-prev" aria-label="<?php echo esc_attr( bhg_t( 'previous', 'Previous' ) ); ?>">
                                        <span aria-hidden="true">&lsaquo;</span>
                                </button>
                        <?php endif; ?>

                        <div class="bhg-prize-track-wrapper">
                                <div class="bhg-prize-track">
                                        <?php foreach ( $prizes as $prize ) : // phpcs:ignore Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace ?>
                                                <?php echo $card_renderer ? call_user_func( $card_renderer, $prize, $size, $display, $context ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                        <?php endforeach; ?>
                                </div>
                        </div>

                        <?php if ( $show_nav ) : ?>
				<button type="button" class="bhg-prize-nav bhg-prize-next" aria-label="<?php echo esc_attr( bhg_t( 'next', 'Next' ) ); ?>">
					<span aria-hidden="true">&rsaquo;</span>
				</button>

                                <div class="bhg-prize-dots" role="tablist">
                                        <?php for ( $i = 0; $i < $pages; $i++ ) : // phpcs:ignore Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace ?>
                                                <button type="button" class="bhg-prize-dot<?php echo 0 === $i ? ' active' : ''; ?>" data-index="<?php echo esc_attr( $i ); ?>" aria-label="<?php echo esc_attr( sprintf( bhg_t( 'prize_slide_label', 'Go to prize %d' ), $i + 1 ) ); ?>"></button>
                                        <?php endfor; ?>
                                </div>
                        <?php endif; ?>
                </div>
        <?php elseif ( $card_renderer ) : ?>
                <div class="bhg-prizes-grid">
                        <?php foreach ( $prizes as $prize ) : // phpcs:ignore Squiz.ControlStructures.ControlSignature.NewlineAfterOpenBrace ?>
                                <?php echo call_user_func( $card_renderer, $prize, $size, $display, $context ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php endforeach; ?>
                </div>
        <?php endif; ?>
</div>
