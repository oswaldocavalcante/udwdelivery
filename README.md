# UDW Delivery - Uber Direct for WooCommerce

UDW Delivery is a Wordpress plugin that integrates Uber Direct as a shipping method for WooCommerce.

- Author: [Oswaldo Cavalcante](https://oswaldocavalcante.com/)
- Donate link: https://oswaldocavalcante.com/donation
- License: [GPL-2.0+](http://www.gnu.org/licenses/gpl-2.0.html)

## Features

- Integrates Uber Direct delivery service for WooCommerce.
- Easy setup and configuration through the WooCommerce settings.
- Automatic handling of delivery requests and status updates.
- Supports multiple languages.

## Setup

1. Make sure that your's store Address is already setted up in WooCommerce -> Settings -> General - this will be use by Uber Direct as the Pickup Location.
2. Go to WooCommerce -> Settings -> Integration -> Uber Direct, and set you Uber Direct credentials obtained in [https://developer.uber.com/docs/deliveries/get-started](https://developer.uber.com/docs/deliveries/get-started).
3. Go to WooCommerce -> Settings -> Shipping -> Zones and add the zone where Uber Direct will work with your deliveries. In this settings, you need to add Uber Direct as a Shipping Method for this zone. Remember that WooCommerce will only display Uber Direct as a shipping method in the client's cart, if his zipcode matches the same zone that you configured here.

## Reccomendations

In WooCommerce -> Settings -> Shipping -> Shipping settings, is recommended that your shop hides the shipping costs until the client inserts his address for delivery.

## External services

This plugin connects to Uber Direct API to obtain delivery information. It's needed to show the delivery information in the WooCommerce admin orders page, meta box in admin single order page, customer cart and customer checkout.

It sends your Uber API Credentials and the stores's address every time that a request is made. To get delivery quotes and create deliveries, this plugin also sends the customers address and order informations.

This service is provided by "Uber Technologies Inc.": [terms of use](https://www.uber.com/legal/terms), [privacy policy](https://privacy.uber.com/policy).


## Frequently Asked Questions

### What is Uber Direct?

Uber Direct is a delivery service from Uber that allows businesses to send packages directly to customers using Uber's network of drivers.

### Where do I get my API credentials?

You can set up your API credentials in the WooCommerce settings under the "Uber Direct" section. You will need your Customer ID, Client ID, and Client Secret from your Uber Direct account, as explained [here](https://developer.uber.com/docs/deliveries/get-started).