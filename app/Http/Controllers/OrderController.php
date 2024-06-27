<?php

namespace App\Http\Controllers;

use App\Models\Order\Order;
use App\Models\Order\Detail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'cashier_id'            => 'required|exists:users,id',
            'items'                 => 'required|array',
            'items.*.product_code'  => 'required|exists:products,code',
            'items.*.quantity'      => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $totalPrice = 0;
            $orderDetails = [];

            // Calculate the total price and prepare order details
            foreach ($validated['items'] as $item) {
                $product = Product::where('code', $item['product_code'])->first();
                
                if (!$product) {
                    throw new \Exception('Product not found for code: ' . $item['product_code']);
                }
                
                $unitPrice      =   $product->unit_price;
                $quantity       =   $item['quantity'];
                $totalPrice     +=  $unitPrice * $quantity;

                $orderDetails[] = [
                    'product_code'  => $item['product_code'],
                    'unit_price'    => $unitPrice,
                    'quantity'      => $quantity,
                ];
            }

            // Create the order
            $order = Order::create([
                'cashier_id' => $validated['cashier_id'],
                'total_price' => $totalPrice,
                'receipt_number' => $this->generateReceiptNumber(), // Optional: generate a receipt number
            ]);

            // Save the order details
            foreach ($orderDetails as $detail) {
                $detail['order_id'] = $order->id;
                Detail::create($detail);
            }

            DB::commit();

            // Return the order with its details
            $order->load('orderDetails');
            return response()->json([
                'order' => $order,
                // 'order_details' => $order->orderDetails,
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Order creation failed', 
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    // Method to calculate total sales
    public function totalSales(){
        try {
            $totalSales = Order::sum('total_price');
            return response()->json(['total_sales' => $totalSales], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to calculate total sales', 
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //Generate a unique receipt number
    private function generateReceiptNumber(){
        return rand(100000, 999999);
    }
}
