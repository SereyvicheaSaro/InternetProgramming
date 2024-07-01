<?php

namespace App\Http\Controllers;

use App\Models\Order\Order;
use App\Models\Order\Detail;
use App\Models\Product;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

                if ($product->quantity < $item['quantity']) {
                    throw new \Exception('Insufficient quantity for product code: ' . $item['product_code']);
                }

                // Deduct the ordered quantity from the product's stock
                $product->quantity -= $item['quantity'];
                $product->save();

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
                'created_at' => Carbon::now('Asia/Phnom_Penh'),
                'updated_at' => Carbon::now('Asia/Phnom_Penh'),
            ]);

            // Save the order details
            foreach ($orderDetails as $detail) {
                $detail['order_id'] = $order->id;
                $detail['created_at'] = Carbon::now('Asia/Phnom_Penh');
                $detail['updated_at'] = Carbon::now('Asia/Phnom_Penh');
                Detail::create($detail);
            }

            DB::commit();

            // Return the order with its details
            $order->load('orderDetails');

            // Send notification to Telegram
            $this->sendTelegramNotification($order);

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

    // Method to calculate total items ordered
    public function totalItemsOrdered(){
        try {
            $totalOrdered = Order::count();
            return response()->json(['total_ordered' => $totalOrdered], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to calculate total items ordered', 
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //Generate a unique receipt number
    private function generateReceiptNumber(){
        return rand(100000, 999999);
    }

    protected function sendTelegramNotification($order)
    {
        try {
            $telegramToken = '7305996766:AAEYw9Nz8RXN1yEFGIPrw2aNze0dTpuDIy8';
            $chatId = '-4200088563';

            $client = new Client();

            // Compose message text
            // $message = "*New Order Received*\n\n";
            // $message = "=====================\n\n";
            $message = "🔢 Receipt Number: " . $order->receipt_number . "\n\n";
            $message .= "👩‍💻 Cashier: " . $order->cashier->name . "\n\n";
            $message .= "📅 Order at: " . $order->created_at->format('Y-m-d H:i:s') . "\n\n";
            $message .= "=====================\n\n";
            foreach ($order->orderDetails as $detail) {
                $product = Product::where('code', $detail->product_code)->first();
                if ($product) {
                    $message .= "🎋 Product: " . $product->name . "\n\n";
                    $message .= "💲 Unit Price: $" . $detail->unit_price . "\n\n";
                    $message .= "🎢 Quantity: " . $detail->quantity . "\n\n";
                }
            }
            $message .= "=====================\n\n";
            $message .= "💵 Total Price: $" . $order->total_price . "\n";

            // Send message via Telegram Bot API
            $response = $client->post("https://api.telegram.org/bot{$telegramToken}/sendMessage", [
                'json' => [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'Markdown',
                ]
            ]);

            Log::info('Telegram Notification Response', ['response' => $response->getBody()->getContents()]);
        } catch (\Exception $e) {
            Log::error('Error sending Telegram notification', ['error' => $e->getMessage()]);
        }
    }
}
