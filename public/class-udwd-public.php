<?php

if(!defined('ABSPATH')) exit; // Exit if accessed directly

require_once UDWD_ABSPATH . 'integrations/uberdirect/class-udwd-ud-api.php';

/**
 * The public-specific functionality of the plugin.
 *
 * @author     Oswaldo Cavalcante <contato@oswaldocavalcante.com>
 * @link       https://oswaldocavalcante.com
 * 
 * @package    UDWDelivery
 * @subpackage UDWDelivery/public
 */
class UDWD_Public
{
	/**
	 * Modifies the shipping label adding the shipping deadline.
	 *
	 * @param string $label The shipping label.
	 * @param WC_Shipping_Rate $method The shipping method.
	 * @return string The modified shipping label with the added deadline.
	 */
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

	/**
	 * Returns a message with the deadline day and time.
	 *
	 * @param DateTime $deadline
	 * @return string
	 */
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
