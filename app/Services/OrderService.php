<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use App\Sevices\AffiliateService;

class OrderService
{
  protected $affiliateService;

  public function __construct(AffiliateService $affiliateService)
  {
      $this->affiliateService = $affiliateService;
  }

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
  public function processOrder(array $data)
  {
      $order = Order::firstOrNew(['order_id' => $data['order_id']]);

      if ($order->exists) {
          return;
      }

      $merchant = Merchant::where('domain', $data['merchant_domain'])->firstOrFail();

      $customerEmail = $data['customer_email'];
      $existingAffiliate = Affiliate::where('email', $customerEmail)->first();

      // create a new affiliate
      if (!$existingAffiliate) {
          $this->affiliateService->register(
              $merchant,
              $customerEmail,
              $data['customer_name'],
              0.1 // For example, a default commission rate of 10%
          );
      }

      $order->fill($data);
      $order->save(); // add order data
  }

}
