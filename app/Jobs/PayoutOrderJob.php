<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class PayoutOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

   protected $apiService;
   protected $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ApiService $apiService, Order $order)
    {
        $this->apiService = $apiService;
        $this->order = $order;
    }

    /**
     * Use the API service to send a payout of the correct amount.
     * Note: The order status must be paid if the payout is successful, or remain unpaid in the event of an exception.
     *
     * @return void
     */
  public function handle()
  {
      $order = $this->order;

      try {
          $payoutResult = $this->apiService->sendPayout($order->amount); 
          DB::transaction(function () use ($order) {
              $order->update(['status' => 'paid']);
          });
      } catch (\Exception $e) {
          // If an exception occurs during payout, log the error and keep order status as unpaid
          \Log::error("Payout failed for order_id: {$order->id}. Error: {$e->getMessage()}");
      }
  }
}
