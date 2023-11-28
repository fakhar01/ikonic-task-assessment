<?php

namespace App\Http\Controllers;

use App\Services\AffiliateService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
  protected $orderService;

  public function __construct(OrderService $orderService)
  {
      $this->orderService = $orderService;
  }
    /**
     * Pass the necessary data to the process order method
     * 
     * @param  Request $request
     * @return JsonResponse
     */
  public function __invoke(Request $request): JsonResponse
  {
      try { 
          $data = $request->all();
          $this->orderService->processOrder($data);

          return response()->json(['message' => 'Order started'], 200); //return data with 200 response code, and message
      } catch (\Exception $e) {

          return response()->json(['error' => 'Failed to start webhook'], 500); // return error with 500 code and message
      }
  }
}
