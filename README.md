# hotdogpin One-time Payment Checkout Experience

# Getting Started

This readme is meant to be a guide to show you how to go about building a Stripe Checkout payment flow for one time payment for a website (ex. hotdog pin in this case)


### Prerequisites
* PHP
* [Composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos)
* [Stripe CLI](https://github.com/stripe/stripe-cli/)
* [Create a stripe account](https://dashboard.stripe.com/register)
* [Stripe API Keys](https://stripe.com/docs/keys)

## Step by step

1. **Configure your keys**

Copy the example `.env` file and update those values with your Stripe API keys.

```
cp .env.example .env
```

Then, be sure to update the publishable key (pk_xxx) in client/index.html

2. **Start the server**

```sh
cd server/php
composer install
composer start
```

3. **Run the test**

For test cards see [https://stripe.com/docs/testing#cards](https://stripe.com/docs/testing#cards).

*Step 1: Saving card details*

Browse to [http://localhost:4242](http://localhost:4242) and click "Buy"

*Step 2: Complete Checkout and view payment status
