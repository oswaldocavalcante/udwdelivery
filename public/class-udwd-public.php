<?php

/**
 * The public-specific functionality of the plugin.
 *
 * @link       https://oswaldocavalcante.com
 * @since      1.0.0
 *
 * @package    UDWDelivery
 * @subpackage UDWDelivery/public
 */

/**
 * The public-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-specific stylesheet and JavaScript.
 *
 * @package    UDWDelivery
 * @subpackage UDWDelivery/public
 * @author     Oswaldo Cavalcante <contato@oswaldocavalcante.com>
 */

if(!defined('ABSPATH')) exit; // Exit if accessed directly

require_once UDWD_ABSPATH . 'integrations/uberdirect/class-udwd-ud-api.php';

class UDWD_Public
{
	public function display_deadline_on_label($label, $method)
	{
		if(key_exists('dropoff_deadline', $method->meta_data))
		{
			$dropoff_deadline = $method->meta_data['dropoff_deadline'];
			$delivery_message = $this->get_deadline_message($dropoff_deadline);
			$label .= '<br><small class="udwd-deadline">' . esc_html($delivery_message) . '</small>';
		}

		return $label;
	}

	public function get_deadline_message(DateTime $deadline)
	{
		$deadline_day = '';

		if ($deadline->format('Y-m-d') == current_datetime()->format('Y-m-d'))
		{
			$deadline_day = __('today', 'udwdelivery');
		}
		else if ($deadline->format('Y-m-d') == current_datetime()->modify('+1 day')->format('Y-m-d'))
		{
			$deadline_day = __('tomorrow', 'udwdelivery');
		}
		else
		{
			$formatter = new IntlDateFormatter('pt_BR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, wp_timezone(), IntlDateFormatter::GREGORIAN, 'EEEE');
			$deadline_day = $formatter->format($deadline);
		}

		/* translators: 1: day of the week 2: time */
		return sprintf(__('(arrives %1$s, %2$s)', 'udwdelivery'), $deadline_day, $deadline->format('H:i'));
	}
}
