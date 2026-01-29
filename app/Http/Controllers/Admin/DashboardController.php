<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard
     */
    public function index()
    {
        // Sample data for orders table
        $orders = collect([
            (object) [
                'order_id' => '#ORD-2024',
                'customer_name' => 'Alice Johnson',
                'product_name' => 'Wireless Headphones',
                'amount' => '$129.99',
                'status' => 'Delivered',
                'status_variant' => 'success',
                'status_icon' => 'fas fa-check-circle',
                'created_at' => now()->subDay()
            ],
            (object) [
                'order_id' => '#ORD-2023',
                'customer_name' => 'Bob Smith',
                'product_name' => 'Smart Watch',
                'amount' => '$299.99',
                'status' => 'Pending',
                'status_variant' => 'warning',
                'status_icon' => 'fas fa-clock',
                'created_at' => now()->subDay()
            ],
            (object) [
                'order_id' => '#ORD-2022',
                'customer_name' => 'Carol White',
                'product_name' => 'Laptop Stand',
                'amount' => '$49.99',
                'status' => 'Shipped',
                'status_variant' => 'secondary',
                'status_icon' => 'fas fa-shipping-fast',
                'created_at' => now()->subDays(2)
            ],
            (object) [
                'order_id' => '#ORD-2021',
                'customer_name' => 'David Lee',
                'product_name' => 'USB-C Hub',
                'amount' => '$79.99',
                'status' => 'Delivered',
                'status_variant' => 'success',
                'status_icon' => 'fas fa-check-circle',
                'created_at' => now()->subDays(2)
            ],
        ]);

        return view('admin.dashboard', compact('orders'));
    }
}