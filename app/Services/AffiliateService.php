<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
  public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
  {
      try {
          $affiliate = Affiliate::create([
              'merchant_id' => $merchant->id,
              'email' => $email,
              'name' => $name,
              'commission_rate' => $commissionRate,
          ]);

          // Send email if created successfully
          Mail::to($email)->send(new AffiliateCreated($affiliate));

          return $affiliate;
      } catch (\Exception $e) {
          throw new AffiliateCreateException("Failed to create affiliation: {$e->getMessage()}"); // handel the error
      }
  }

}
