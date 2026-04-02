<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        $customers = User::query()
            ->where('is_admin', false)
            ->withCount('orders')
            ->orderByDesc('id')
            ->paginate(30);

        return view('admin.customers.index', compact('customers'));
    }
}
