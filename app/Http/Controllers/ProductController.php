<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['show', 'index']]);
    }

    public function store(Request $request)
    {
        // return response()->json(auth()->user());
        if (!Tenant::getTenant()) {
            return response()->json(['error' => 'Not Found']);
        }
        $tenant_id = Tenant::getTenant()->id;
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $product = Product::create(array_merge(
            $validator->validate(),
            [
                'tenant_id' => $tenant_id,
                'user_id' => auth()->user()->id
            ]
        ));

        return response()->json([
            'message' => 'registered Successfully',
            'user' => $product
        ], 200);
    }

    public function update(Request $request, Product $product)
    {
        $tenant = Tenant::getTenant();
        if (!$tenant || $product->tenant_id != $tenant->id || $product->user_id != auth()->user()->id) {
            return response()->json(['error' => 'Not Found']);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $product->name = $request->name;
        $product->description = $request->description;
        $product->save();

        return response()->json(['product updated successfully.', $product]);
    }

    public function destroy(Product $product)
    {
        $tenant = Tenant::getTenant();
        if (!$tenant || $product->tenant_id != $tenant->id || $product->user_id != auth()->user()->id) {
            return response()->json(['error' => 'Not Found']);
        }
        $product->delete();
        return response()->json('product deleted successfully');
    }
}
