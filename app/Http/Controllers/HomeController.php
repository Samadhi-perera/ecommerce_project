<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;

use App\Models\User;

use App\Models\Cart;

use App\Models\Order;

use Session;

use stripe;

use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $user = User::where('usertype','user')->get()->count();

        $product = Product::all()->count();

        $order = Order::all()->count();

        $delivered = Order::where('status','Delivered')->get()->count();

        return view('admin.index',compact('user','product','order','delivered'));
    }
    public function home()
    {
        $product = Product::all();

    if (Auth::check()) {
        $user = Auth::user();
        $userid = $user->id;
        $count = Cart::where('user_id', $userid)->count();
    } else {
        $count = ''; 
    }

    return view('home.index', compact('product', 'count'));
    }

    public function login_home()
    {
        $product = Product::all();

        if(Auth::id())
        {
        $user = Auth::user();

        $userid = $user->id;

        $count = Cart::where('user_id',$userid)->count();
        }

        else
        {
            $sount ='';
        }


        return view('home.index', compact('product','count'));
    }

    public function product_details($id)
    {
        $data = Product::find($id);

         if(Auth::id())
        {
        $user = Auth::user();

        $userid = $user->id;

        $count = Cart::where('user_id',$userid)->count();
        }

        else
        {
            $sount ='';
        }


        return view('home.product_details', compact('data','count'));
    }

    public function add_cart($id)
    {
        $product_id = $id;

        $user = Auth::user();

        $user_id = $user->id;

        $data = new Cart;
        $data->user_id = $user_id;
        $data->product_id = $product_id;

        $data->save();  
        
        // toastr()->timeOut(10000)->closeButton()->addSuccess('Product Added to the Cart Successfully');

            session()->flash('success', 'Product Added to the Cart Successfully!');

        
        return redirect()->back();
    }

    public function mycart()
    {
        if(Auth::id())
        {
            $user = Auth::user();

            $userid = $user->id;

            $count = Cart::where('user_id',$userid)->count();

            $cart = Cart::where('user_id',$userid)->get();

        }    

        return view('home.mycart', compact('count','cart'));
    }

    public function delete_cart($id)
    {
        $data = Cart::find($id);

      $data->delete();

      
      return redirect()->back()->with('success', 'Cart deleted Successfully.');
    }

    public function confirm_order(Request $request)
    {
        $name = $request->name;
        $address = $request->address;
        $phone = $request->phone;
        $userid = Auth::User()->id;

        $cart = Cart::where('user_id',$userid)->get();

        foreach ($cart as $carts)
        {
            $order = new Order;

            $order->name = $name;

            $order->rec_address = $address;

            $order->phone = $phone;

            $order->user_id = $userid;

            $order->product_id = $carts->product_id;

            $order->save();


        }

        $cart_remove =Cart::where('user_id',$userid)->get();

        foreach ($cart_remove as $remove)
        {
            $data = Cart::find($remove->id);

            $data->delete();
        }
      return redirect()->back()->with('success', 'Product Ordered  Successfully.');

    }

    public function myorders()
    {
        $user = Auth::user()->id;

        $count = Cart::where('user_id', $user)->get()->count();

        $order = Order::where('user_id',$user)->get();

        return view('home.order',compact('count','order'));

        
    }

    public function stripe()

    {
        return view('home.stripe');
    }

     public function stripePost(Request $request)

    {

        Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

    

        Stripe\Charge::create ([

                "amount" => 100 * 100,

                "currency" => "usd",

                "source" => $request->stripeToken,

                "description" => "Test payment from itsolutionstuff.com." 

        ]);

      


              

        return back();

    }

  
    

}
