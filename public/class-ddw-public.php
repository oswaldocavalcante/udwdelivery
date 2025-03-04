<?php

/**
 * The public-specific functionality of the plugin.
 *
 * @link       https://oswaldocavalcante.com
 * @since      1.0.0
 *
 * @package    Udw
 * @subpackage Udw/public
 */

/**
 * The public-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-specific stylesheet and JavaScript.
 *
 * @package    Udw
 * @subpackage Udw/public
 * @author     Oswaldo Cavalcante <contato@oswaldocavalcante.com>
 */

require_once DDW_ABSPATH . 'integrations/uberdirect/class-ddw-ud-api.php';

class Ddw_Public
{
	public function display_deadline_on_label($label, $method)
	{
		if(key_exists('dropoff_deadline', $method->meta_data))
		{
			$dropoff_deadline = $method->meta_data['dropoff_deadline'];
			$delivery_message = $this->get_deadline_message($dropoff_deadline);
			$label .= '<br><small class="ddw-deadline">' . esc_html($delivery_message) . '</small>';
		}

		return $label;
	}

	public function get_deadline_message(DateTime $deadline)
	{
		$deadline_day = '';

		if ($deadline->format('Y-m-d') == current_datetime()->format('Y-m-d'))
		{
			$deadline_day = __('today', 'directdelivery');
		}
		else if ($deadline->format('Y-m-d') == current_datetime()->modify('+1 day')->format('Y-m-d'))
		{
			$deadline_day = __('tomorrow', 'directdelivery');
		}
		else
		{
			$formatter = new IntlDateFormatter('pt_BR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, wp_timezone(), IntlDateFormatter::GREGORIAN, 'EEEE');
			$deadline_day = $formatter->format($deadline);
		}

		/* translators: 1: day of the week 2: time */
		return sprintf(__('(arrives %1$s, %2$s)', 'directdelivery'), $deadline_day, $deadline->format('H:i'));
	}
}
