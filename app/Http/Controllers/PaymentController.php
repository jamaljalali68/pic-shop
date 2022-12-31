<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\SendOrderedImages;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cookie;
use App\Services\Payment\PaymentService;
use App\Http\Requests\Payment\PayRequest;
use App\Services\Payment\Requests\IDPayRequest;
use App\Services\Payment\Requests\IDPayVerifyRequest;

class PaymentController extends Controller
{
    public function pay(PayRequest $request)
    {

        $validatedData = $request->validated();

        $user = User::firstOrCreate([
            'email' => $validatedData['email'],
        ], [
            'name' => $validatedData['name'],
            'mobile' => $validatedData['mobile'],
        ]);

        try {
            $orderItems = json_decode(Cookie::get('basket'), true);

            if (count($orderItems) <=0){
                throw new \InvalidArgumentException('سبد خرید شما خالی است');
            }


            $products = Product::findMany(array_keys($orderItems));

            $productsPrice = $products->sum('price');

            $refCode = Str::random(30);

            $createdOrder = Order::create([
                'amount' => $productsPrice,
                'ref_code' => $refCode,
                'status' => 'unpaid',
                'user_id' => $user->id,
            ]);

            $orderItemsForCreatedOrder = $products->map(function ($product) {
                $currentProduct = $product->only(['price', 'id']);

                $currentProduct['product_id'] = $currentProduct['id'];

                unset($currentProduct['id']);

                return $currentProduct;
            });

            //here we use cookies for set orderitems this is not safe because cookies are accessible
            // $orderItemData = [];
            // foreach ($orderItems as $key => $value) {
            //     $orderItemData[] = [
            //         'price' => $value['price'],
            //         'order_id' => $createdOrder->id,
            //         'product_id' => $key
            //     ];
            // }

            $createdOrder->orderItems()->createMany($orderItemsForCreatedOrder->toArray());

            

            $createdPayment = Payment::create([
                'gateway' => 'idpay',
                'ref_code' => $refCode,
                'status' => 'unpaid',
                'order_id' => $createdOrder->id,
            ]);

            $idPayRequest = new IDPayRequest([
                'amount' => $productsPrice,
                'user' => $user,
                'orderId' => $refCode,
                'apiKey' => config('services.gateways.id_pay.api_key'),
            ]);

            $paymentService = new PaymentService(PaymentService::IDPAY, $idPayRequest);

            return $paymentService->pay();

        } catch (\Exception $e) {
            
            return back()->with('failed', $e->getMessage());
        }
    }

    public function callback(Request $request)
    {

        $paymentInfo = $request->all();


        $idPayVerifyRequest = new IDPayVerifyRequest([
            'orderId' => $paymentInfo['order_id'],
            'id' =>$paymentInfo['id'] ,
            'apiKey' => config('services.gateways.id_pay.api_key'),
        ]);

        
        $paymentService = new PaymentService(PaymentService::IDPAY, $idPayVerifyRequest);

        $result = $paymentService->verify();

        if (!$result['status']) {
            return redirect()->route('home.checkout')->with('failed', 'پرداخت شما انجام نشد');
        }
        
        if($request['status'] == 101){
            return redirect()->route('home.checkout')->with('failed', 'پرداخت شما قبلا انجام شده و تصاویر برای شما ایمیل گردیده');

        }

        $currentPayment = Payment::where('ref_code', $result['data']['order_id'] )->first();

        $currentPayment->update([
            'status' => 'paied',
            'res_id' => $result['data']['track_id'],
        ]);

        $currentPayment->order()->update([
            'status' => 'paid',
        ]);

        $currentUser = $currentPayment->order->user;
        $reservedProducts = $currentPayment->order->orderItems->map(function ($orderItem){

            return $orderItem->product;
        });

        $reservedProducts->toArray();

        Mail::to($currentUser)->send(new SendOrderedImages($reservedProducts->toArray() , $currentUser));

        Cookie::queue('basket',null);
            
       return redirect()->route('home.products.all')->with('success', 'خرید شما انجام شد و تصاویر ایمیل شدند');


    }
}
