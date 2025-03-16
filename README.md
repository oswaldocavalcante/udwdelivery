# UDW Delivery - Uber Direct for WooCommerce

- Author: [Oswaldo Cavalcante](https://oswaldocavalcante.com/)
- License: GPL-2.0+
- Stable tag: 2.2.2
- Tested up to: 6.7
- Delivery service for WooCommerce integrating with Uber Direct API.

## Features

- Integrates Uber Direct delivery service for WooCommerce.
- Easy setup and configuration through the WooCommerce settings.
- Automatic handling of delivery requests and status updates.
- Supports multiple languages, including Portuguese (pt_BR).

## Installation

1. Upload `udwdelivery` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to WooCommerce settings/integrations and configure your Uber Direct API credentials.

## Setup

1. Make sure that your's store Address is already setted up in WooCommerce -> Settings -> General - this will be use by Uber Direct as the Pickup Location.
2. Go to WooCommerce -> Settings -> Integration -> Uber Direct, and set you Uber Direct credentials obtained in [https://developer.uber.com/docs/deliveries/get-started](https://developer.uber.com/docs/deliveries/get-started).
3. Go to WooCommerce -> Settings -> Shipping -> Zones and add the zone where Uber Direct will work with your deliveries. In this settings, you need to add Uber Direct as a Shipping Method for this zone. Remember that WooCommerce will only display Uber Direct as a shipping method in the client's cart, if his zipcode matches the same zone that you configured here.

## Reccomendations

In WooCommerce -> Settings -> Shipping -> Shipping settings, is recommended that your shop hides the shipping costs until the client inserts his address for delivery.

## Frequently Asked Questions

### What is Uber Direct?

Uber Direct is a delivery service from Uber that allows businesses to send packages directly to customers using Uber's network of drivers.

### Where do I get my API credentials?

You can set up your API credentials in the WooCommerce settings under the "Uber Direct" section. You will need your Customer ID, Client ID, and Client Secret from your Uber Direct account, as explained [here](https://developer.uber.com/docs/deliveries/get-started).