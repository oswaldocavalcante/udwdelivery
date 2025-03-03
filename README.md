# Uber Direct for WooCommerce

## Description

Uber Direct for WooCommerce allows you to manage deliveries directly from your WooCommerce store. This plugin provides a seamless experience for both store owners and customers, enabling efficient order fulfillment through Uber's delivery network.

## Features

- Integrates Uber Direct delivery service for WooCommerce.
- Easy setup and configuration through the WooCommerce settings.
- Automatic handling of delivery requests and status updates.
- Supports multiple languages, including Portuguese (pt_BR).

## Installation

1. Upload `uberdirect` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to WooCommerce settings/integrations and configure your Uber Direct API credentials.

## Setup

1. Make sure that your's store Address is already setted up in WooCommerce -> Settings -> General - this will be use by Uber Direct as the Pickup Location.
2. Go to WooCommerce -> Settings -> Integration tab -> Uber Direct, and set you Uber Direct credentials obtained in [https://developer.uber.com/docs/deliveries/get-started](https://developer.uber.com/docs/deliveries/get-started).
3. Go to WooCommerce -> Settings -> Shipping tab -> Zones and add the zone where Uber Direct will work with your deliveries. In this settings, you need to add Uber Direct as a Shipping Method for this zone. Remeber that WooCommerce will only display Uber Direct as a shipping method in the client's cart, if his zipcode matches the same zone that you configured here.

## Reccomendations

In WooCommerce -> Settings -> Shipping tab -> Shipping settings, is recommended that you shop hides the shipping costs until the client insert his address for delivery.

## Frequently Asked Questions

### What is Uber Direct?

Uber Direct is a delivery service that allows businesses to send packages directly to customers using Uber's network of drivers.

### How do I set up my API credentials?

You can set up your API credentials in the WooCommerce settings under the "Uber Direct" section. You will need your Customer ID, Client ID, and Client Secret from your Uber Direct account.

## License

This plugin is licensed under the GNU General Public License v2.0 or later. See the [LICENSE.txt](LICENSE.txt) file for more details.

## Author

Oswaldo Cavalcante  
[oswaldocavalcante.com](https://oswaldocavalcante.com/)