=== UDW Delivery - Uber Direct for WooCommerce ===
Contributors: oswaldocavalcante
Donate link: https://oswaldocavalcante.com/donation
Tags: woocommerce, delivery, shipping, uber, courier
Requires at least: 5.0
Tested up to: 6.7
Stable tag: 2.2.4
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 7.4

Delivery service for WooCommerce integrating with Uber Direct API.

== Description ==

Integrates Uber Direct delivery service for WooCommerce with automatic handling of delivery requests and status updates.

**Features**

* Easy setup and configuration through WooCommerce settings
* Automatic handling of delivery requests
* Real-time status updates
* Multiple language support

== Installation ==

1. Make sure that your's store Address is already setted up in WooCommerce -> Settings -> General - this will be use by Uber Direct as the Pickup Location.
2. Go to WooCommerce -> Settings -> Integration -> Uber Direct, and set you Uber Direct credentials obtained in [https://developer.uber.com/docs/deliveries/get-started](https://developer.uber.com/docs/deliveries/get-started).
3. Go to WooCommerce -> Settings -> Shipping -> Zones and add the zone where Uber Direct will work with your deliveries. In this settings, you need to add Uber Direct as a Shipping Method for this zone. Remember that WooCommerce will only display Uber Direct as a shipping method in the client's cart, if his zipcode matches the same zone that you configured here.
4. Reccomendation: in WooCommerce -> Settings -> Shipping -> Shipping settings, is recommended that your shop hides the shipping costs until the client inserts his address for delivery.

== External services ==

This plugin connects to Uber Direct API to obtain delivery information. It's needed to show the delivery information in the WooCommerce admin orders page, meta box in admin single order page, customer cart and customer checkout.

It sends your Uber API Credentials and the stores's address every time that a request is made. To get delivery quotes and create deliveries, this plugin also sends the customers address and order informations.

This service is provided by "Uber Technologies Inc.": [terms of use](https://www.uber.com/legal/terms), [privacy policy](https://privacy.uber.com/policy).

== Frequently Asked Questions ==

= What is Uber Direct? =

Uber Direct is a delivery service from Uber that allows businesses to send packages directly to customers using Uber's network of drivers.

= Where do I get my API credentials? =

You can obtain your credentials at developer.uber.com as explained [here](https://developer.uber.com/docs/deliveries/get-started).

== Screenshots ==

1. Customer checkout page displaying Uber Direct shipping option
2. Call a delivery directly from the list in Orders Page
3. Order details page showing delivery status and tracking information
4. WooCommerce Integration settings page where you can configure your Uber Direct API credentials
5. Shipping zone configuration with Uber Direct enabled as a shipping method