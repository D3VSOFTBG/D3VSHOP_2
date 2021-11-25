<?php

namespace App\Http\Controllers;

use App\ProductModel;
use App\RoleModel;
use App\SettingModel;
use App\StripeModel;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AdminController extends Controller
{
    function dashboard()
    {
        return view('admin.dashboard');
    }
    function users()
    {
        $users = User::orderBy('id', 'DESC')->get();
        $roles = RoleModel::all();

        $data = [
            'users' => $users,
            'roles' => $roles,
        ];

        return view('admin.users', $data);
    }
    function information()
    {
        return view('admin.information');
    }
    function shop_products()
    {
        $products = ProductModel::orderBy('id', 'DESC')->get();

        $data = [
            'products' => $products,
        ];

        return view('admin.shop.products', $data);
    }
    function payments_stripe()
    {
        $stripe = StripeModel::where('id', 1)->get();

        $data = [
            'stripe' => $stripe,
        ];

        return view('admin.payments.stripe', $data);
    }
    function product_create(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
            'name' => 'required',
            'price' => 'required|numeric',
            'quantity' => 'required|integer'
        ]);

        $products = new ProductModel();
        $products->slug = strtolower(trim(preg_replace('/\s+/', '-', $request->name))) . time();

        // image
        $new_image_name = time() . '.' . $request->image->extension();
        $request->image->move(public_path('/storage/img/products/'), $new_image_name);
        $products->image = $new_image_name;

        $products->name = $request->name;
        $products->price = $request->price;
        $products->quantity = $request->quantity;
        $products->save();

        return back();
    }
    function product_edit(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'quantity' => 'required|integer'
        ]);

        $products = ProductModel::find($request->id);

        if($products->name != $request->name)
        {
            $products->slug = strtolower(trim(preg_replace('/\s+/', '-', $request->name))) . time();
        }

        if(!empty($request->image))
        {
            $request->validate([
                'image' => 'required|image|max:2048',
            ]);

            // image
            $new_image_name = time() . '.' . $request->image->extension();
            $request->image->move(public_path('/storage/img/products/'), $new_image_name);
            $products->image = $new_image_name;
        }

        $products->name = $request->name;
        $products->price = $request->price;
        $products->quantity = $request->quantity;
        $products->save();

        return back();
    }
    function product_delete(Request $request)
    {
        ProductModel::find($request->id)->delete();
        return back();
    }
    function settings_get()
    {
        $settings = SettingModel::all();

        $data = [
            'settings' => $settings,
        ];

        return view('admin.settings', $data);
    }
    function settings_post(Request $request)
    {
        $request->validate([
            'shop_name' => 'required',
            'title_seperator' => 'required',
            'default_currency' => 'required',
            'theme_name' => 'required',
        ]);

        DB::update(
            "UPDATE settings SET value = CASE WHEN id = 1 THEN ? WHEN id = 2 THEN ? WHEN id = 3 THEN ? WHEN id = 4 THEN ? END WHERE ID IN (1, 2, 3, 4)",
            [$request->shop_name, $request->title_seperator, $request->default_currency, $request->theme_name]
        );

        // Delete all cache
        Cache::flush();

        return back();
    }
    function user_delete(Request $request)
    {
        User::find($request->id)->delete();
        return back();
    }
    function user_edit(Request $request)
    {
        $users = User::find($request->id);

        $request->validate([
            'name' => 'required',
            'role' => 'required',
        ]);

        $users->name = $request->name;

        if($request->email != $users->email)
        {
            $request->validate([
                'email' => 'required|email|unique:users',
            ]);
            $users->email = $request->email;
        }

        if($request->role == 'NULL')
        {
            $users->role = NULL;
        }
        else
        {
            $users->role = $request->role;
        }

        if(!empty($request->password) && !empty($request->password_confirmation))
        {
            if($request->password == $request->password_confirmation)
            {
                $users->password = Hash::make($request->password);
            }
            else
            {
                return back()->withErrors('The passwords do not match.');
            }
        }

        $users->save();

        return back();
    }
    function user_create(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'role' => 'required',
            'password' => 'required',
            'password_confirmation' => 'required',
        ]);

        $users = new User();

        $users->name = $request->name;
        $users->email = $request->email;

        if($request->role == 'NULL')
        {
            $users->role = NULL;
        }
        else
        {
            $users->role = $request->role;
        }

        if($request->password == $request->password_confirmation)
        {
            $users->password = Hash::make($request->password);
        }
        else
        {
            return back()->withErrors('The passwords do not match.');
        }

        $users->save();

        return back();
    }
}
