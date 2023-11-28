<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
  public function register(array $data): Merchant
  {
      $user = User::create([
          'name' => $data['name'],
          'email' => $data['email'],
          'password' => bcrypt($data['api_key']), 
          'type' => User::MERCHANT, 
      ]);

      $merchant = Merchant::create([
          'domain' => $data['domain'],
          'user_id' => $user->id,
      ]);

      return $merchant;
  }


    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
  public function updateMerchant(User $user, array $data)
  {
      $user->update([
          'name' => $data['name'],
          'email' => $data['email'],
      ]);

      $merchant = $user->merchant; // Assuming there's a relationship between User and Merchant
      if ($merchant) {
          $merchant->update([
              'domain' => $data['domain'],
          ]);
      }
  }


    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
  public function findMerchantByEmail(string $email): ?Merchant
  {
      $findUser = User::where('email', $email)->first(); // find user record by email 
      return $findUser ? $findUser->merchant : null; // check if merchant exists against that user
  }


    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
  public function payout(Affiliate $affiliate)
  {
      $unpaidOrders = $affiliate->orders()->where('paid', false)->get();

      foreach ($unpaidOrders as $key => $unpaid_order) {
          PayoutOrderJob::dispatch($unpaid_order); // dispatch PayoutOrderJob job
      }
  }

}
