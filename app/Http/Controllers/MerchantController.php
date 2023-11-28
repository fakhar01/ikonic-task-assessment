<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantController extends Controller
{
  protected $merchantService;

  public function __construct(MerchantService $merchantService)
  {
      $this->merchantService = $merchantService;
  }

    /**
     * Useful order statistics for the merchant API.
     * 
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
  public function orderStats(Request $request): JsonResponse
  {
      try {
          $fromDate = Carbon::createFromFormat('Y-m-d', $request->input('from'))->startOfDay();
          $toDate = Carbon::createFromFormat('Y-m-d', $request->input('to'))->endOfDay();

          $merchant = Merchant::findOrFail($request->user()->merchant_id); // Assume that  merchant_id is associated with the user

          $count = $this->merchantService->getOrderCount($merchant, $fromDate, $toDate);
          $commissionOwed = $this->merchantService->getUnpaidCommission($merchant, $fromDate, $toDate);
          $revenue = $this->merchantService->getOrderRevenue($merchant, $fromDate, $toDate);
          return response()->json([
              'count' => $count,
              'commission_owed' => $commissionOwed,
              'revenue' => $revenue,
          ], 200); // return response with 200 response code
      } catch (\Exception $e) { 
          return response()->json(['error' => 'Failed to fetch order data'], 500); // json response in case of failure
      }
  }
}
