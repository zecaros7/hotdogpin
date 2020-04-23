# hotdogpin One-time Payment Checkout Experience

Getting Started
Follow these instructions to spin-up a copy of this demo project up on your local machine for development and testing purposes. This is meant to be a guide to show you how to go about building a Stripe Checkout payment flow for one time and recurring payments.

Prerequisites
PHP
Composer
Stripe CLI
Create a stripe account
Stripe API Keys
Step by step
Configure your keys
Copy the example .env file and update those values with your Stripe API keys.

cp .env.example .env
Then, be sure to update the publishable key (pk_xxx) in client/index.html

Start the server
cd server/php
composer install
composer start
Run the demo
For test cards see https://stripe.com/docs/testing#cards.

Step 1: Saving card details

Browse to http://localhost:4242 and click "Buy"

*Step 2: Complete Checkout and view payment status
