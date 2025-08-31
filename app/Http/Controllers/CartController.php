<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CartController extends Controller
{
    // Display cart contents
    public function index()
    {
        $cart = $this->getCart();
        $total = $this->calculateTotal($cart);
        
        return view('cart.index', compact('cart', 'total'));
    }
    
    // Add item to cart
    public function add(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:file,package',
            'id' => 'required|integer'
        ]);
        
        $cart = $this->getCart();
        $key = $validated['type'] . '_' . $validated['id'];
        
        // Check if item already in cart
        if (!isset($cart[$key])) {
            if ($validated['type'] === 'file') {
                $item = File::findOrFail($validated['id']);
                $cart[$key] = [
                    'type' => 'file',
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'thumbnail' => $item->thumbnail_url
                ];
            } else {
                $item = Package::findOrFail($validated['id']);
                $cart[$key] = [
                    'type' => 'package',
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'duration' => $item->duration_days
                ];
            }
            
            $this->saveCart($cart);
        }
        
        return response()->json([
            'success' => true,
            'count' => count($cart),
            'message' => 'Item added to cart'
        ]);
    }
    
    // Remove item from cart
    public function remove(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string'
        ]);
        
        $cart = $this->getCart();
        
        if (isset($cart[$validated['key']])) {
            unset($cart[$validated['key']]);
            $this->saveCart($cart);
        }
        
        return response()->json([
            'success' => true,
            'count' => count($cart),
            'total' => $this->calculateTotal($cart)
        ]);
    }
    
    // Clear entire cart
    public function clear()
    {
        $this->saveCart([]);
        
        return redirect()->route('cart.index')
                       ->with('success', 'Cart cleared successfully');
    }
    
    // Get cart from session (file-based storage)
    private function getCart()
    {
        $sessionId = session()->getId();
        $cacheKey = 'cart_' . $sessionId;
        
        // Use file-based cache instead of Redis
        $cacheFile = storage_path('framework/cache/carts/' . $cacheKey . '.json');
        
        if (file_exists($cacheFile)) {
            $content = file_get_contents($cacheFile);
            return json_decode($content, true) ?: [];
        }
        
        return [];
    }
    
    // Save cart to session storage
    private function saveCart($cart)
    {
        $sessionId = session()->getId();
        $cacheKey = 'cart_' . $sessionId;
        
        // Create directory if doesn't exist
        $cacheDir = storage_path('framework/cache/carts');
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        // Save to file
        $cacheFile = $cacheDir . '/' . $cacheKey . '.json';
        file_put_contents($cacheFile, json_encode($cart));
        
        // Clean old cart files (older than 24 hours)
        $this->cleanOldCarts($cacheDir);
    }
    
    // Calculate cart total
    private function calculateTotal($cart)
    {
        return array_reduce($cart, function ($total, $item) {
            return $total + $item['price'];
        }, 0);
    }
    
    // Clean old cart files
    private function cleanOldCarts($dir)
    {
        $files = glob($dir . '/*.json');
        $now = time();
        
        foreach ($files as $file) {
            if ($now - filemtime($file) > 86400) { // 24 hours
                unlink($file);
            }
        }
    }
}