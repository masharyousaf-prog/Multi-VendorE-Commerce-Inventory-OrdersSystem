@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <div class="flex items-center justify-between">
        <div>
            <span class="text-xs font-bold uppercase tracking-widest text-orange-500">Promotions Engine</span>
            <h1 class="text-2xl font-extrabold">Discount Vouchers & Promo Coupons</h1>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 rounded-xl theme-card text-xs font-bold">Back to Dashboard</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <form method="POST" action="{{ route('admin.coupons.store') }}" class="lg:col-span-4 theme-card rounded-2xl p-6 space-y-4">
            @csrf
            <h3 class="font-extrabold text-base">Create New Promo Code</h3>
            <div>
                <label class="text-xs font-bold block mb-1">Coupon Code</label>
                <input type="text" name="code" required placeholder="e.g. SAVE30" class="w-full px-3.5 py-2.5 rounded-xl text-xs font-mono uppercase">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-bold block mb-1">Discount Type</label>
                    <select name="type" class="w-full px-3 py-2.5 rounded-xl text-xs font-bold">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed ($)</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold block mb-1">Value</label>
                    <input type="number" step="0.01" name="value" required placeholder="20" class="w-full px-3.5 py-2.5 rounded-xl text-xs">
                </div>
            </div>
            <div>
                <label class="text-xs font-bold block mb-1">Minimum Order Subtotal ($)</label>
                <input type="number" step="0.01" name="min_order_amount" value="50" required class="w-full px-3.5 py-2.5 rounded-xl text-xs">
            </div>
            <button type="submit" class="w-full py-3 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-extrabold">
                Create Voucher
            </button>
        </form>

        <div class="lg:col-span-8 theme-card rounded-2xl overflow-hidden">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="theme-elevated uppercase text-[10px]">
                        <th class="p-4">Code</th>
                        <th class="p-4">Discount</th>
                        <th class="p-4">Min Order</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--border-color);">
                    @foreach($coupons as $coupon)
                        <tr>
                            <td class="p-4 font-mono font-extrabold text-orange-500">{{ $coupon->code }}</td>
                            <td class="p-4 font-bold">{{ $coupon->type === 'percentage' ? $coupon->value . '% OFF' : '$' . number_format($coupon->value, 2) . ' OFF' }}</td>
                            <td class="p-4">${{ number_format($coupon->min_order_amount, 2) }}</td>
                            <td class="p-4">
                                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold {{ $coupon->is_active ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                                    {{ $coupon->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </td>
                            <td class="p-4 text-right space-x-2">
                                <form method="POST" action="{{ route('admin.coupons.toggle', $coupon) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-3 py-1 rounded-lg theme-elevated font-bold">{{ $coupon->is_active ? 'Disable' : 'Enable' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.coupons.delete', $coupon) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 rounded-lg bg-rose-600 text-white font-bold">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
