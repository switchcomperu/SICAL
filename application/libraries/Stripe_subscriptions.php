<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once APPPATH . 'libraries/Stripe_core.php';

class Stripe_subscriptions extends Stripe_core
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_upcoming_invoice($subscription_id)
    {
        return Stripe\Invoice::createPreview(['subscription' => $subscription_id]);
    }

    public function get_plans()
    {
        $hasMore       = true;
        $data          = null;
        $startingAfter = null;

        do {
            $plans = Stripe\Plan::all(
                array_merge(['limit' => 100, 'active' => true, 'expand' => ['data.product']], $startingAfter ? ['starting_after' => $startingAfter] : [])
            );

            if (is_null($data)) {
                $data = $plans;
            } else {
                $data->data = array_merge($data->data, $plans->data);
            }

            $startingAfter    = $data->data[count($data->data) - 1]->id ?? null;
            $hasMore          = $plans['has_more'];
            $data['has_more'] = $hasMore;
        } while ($hasMore);

        return $this->removeInactivePlansProduct($data);
    }

    protected function removeInactivePlansProduct($plans)
    {
        $active = [];

        foreach ($plans->data as $plan) {
            if ($plan->product->active === true) {
                $active[] = $plan;
            }
        }

        $plans->data = $active;

        return $plans;
    }

    public function get_plan($id)
    {
        return Stripe\Plan::retrieve($id);
    }

    public function get_product($id)
    {
        return Stripe\Product::retrieve($id);
    }

    public function get_subscription($data)
    {
        return Stripe\Subscription::retrieve($data);
    }

    public function cancel($id)
    {
        $sub = $this->get_subscription($id);
        $sub->cancel();
    }

    public function cancel_at_end_of_billing_period($id)
    {
        $sub = $this->get_subscription($id);

        Stripe\Subscription::update($id, [
            'cancel_at_period_end' => true,
        ]);

        return $sub->current_period_end;
    }

    public function resume($id, $plan_id)
    {
        $stripeSubscription = $this->get_subscription($id);

        Stripe\Subscription::update($id, [
            'cancel_at_period_end' => false,
            'items'                => [
                [
                    'id'   => $stripeSubscription->items->data[0]->id,
                    'plan' => $plan_id,
                ],
            ],
        ]);
    }

    public function update_subscription($subscription_id, $update_values, $db_subscription, $prorate = false)
    {
        $params = [];

        if (empty($subscription_id)) {
            return false;
        }

        if ($update_values['stripe_tax_id'] != $db_subscription->stripe_tax_id
                    || $update_values['stripe_tax_id_2'] != $db_subscription->stripe_tax_id_2
                    || $update_values['quantity'] != $db_subscription->quantity
                    || $update_values['stripe_plan_id'] != $db_subscription->stripe_plan_id
        ) {
            $stripeSubscription = $this->get_subscription($subscription_id);

            if (empty($update_values['stripe_tax_id']) && empty($update_values['stripe_tax_id_2'])) {
                $params['default_tax_rates'] = '';
            } else {
                $taxRates = '';

                foreach (['stripe_tax_id', 'stripe_tax_id_2'] as $key) {
                    if (! empty($update_values[$key])) {
                        if (! is_array($taxRates)) {
                            $taxRates = [];
                        }
                        $taxRates[] = $update_values[$key];
                    }
                }
                $params['default_tax_rates'] = $taxRates;
            }

            // Causing issue when changin both plan/items and quantity
            if ($update_values['quantity'] != $db_subscription->quantity && $update_values['stripe_plan_id'] == $db_subscription->stripe_plan_id) {
                $params['quantity'] = $update_values['quantity'];
            }

            if ($update_values['stripe_plan_id'] != $db_subscription->stripe_plan_id) {
                $items = [
                    [
                        'id'    => $stripeSubscription->items->data[0]->id,
                        'price' => $update_values['stripe_plan_id'],
                    ],
                ];

                // If quantity is changed, update quantity too
                if ($update_values['quantity'] != $db_subscription->quantity) {
                    $items[0]['quantity'] = $update_values['quantity'];
                }
                $params['items'] = $items;
            }

            $params['proration_behavior'] = $prorate ? 'create_prorations' : 'none';
            Stripe\Subscription::update($subscription_id, $params);
        }
    }

    /**
     * Create a new subscription
     *
     * @param string $customer_id
     * @param array  $options
     *
     * @return Stripe\Subscription
     */
    public function subscribe($customer_id, $options = [])
    {
        $defaultOptions = [
            'payment_behavior' => 'allow_incomplete',
        ];

        $options = array_merge($defaultOptions, $options);

        return Stripe\Subscription::create(array_merge([
            'customer' => $customer_id,
        ], $options));
    }
}
