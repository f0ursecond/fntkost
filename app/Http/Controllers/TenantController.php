<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tenants = Tenant::with('transactions')->latest()->get();

        return view('tenants.index', compact('tenants'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone_number' => 'required|string|max:20',
            'room_number' => 'required|string|max:20|unique:tenants,room_number',
            'monthly_rent' => 'required|integer|min:0',
            'due_day' => 'required|integer|min:1|max:31',
            'move_in_date' => 'required|date',
            'months' => 'required|integer|in:1,3,6,12',
        ]);

        $moveInDate = \Carbon\Carbon::parse($validated['move_in_date']);
        $months = (int) $validated['months'];
        $moveOutDate = $moveInDate->copy()->addMonthsNoOverflow($months);

        $tenant = Tenant::create([
            'name' => $validated['name'],
            'phone_number' => $validated['phone_number'],
            'room_number' => $validated['room_number'],
            'monthly_rent' => $validated['monthly_rent'],
            'due_day' => $validated['due_day'],
            'move_in_date' => $validated['move_in_date'],
            'move_out_date' => $moveOutDate,
        ]);

        // Create initial paid transactions
        $startBillingMonth = $moveInDate->copy()->startOfMonth();
        for ($i = 0; $i < $months; $i++) {
            $billingMonth = $startBillingMonth->copy()->addMonths($i)->startOfMonth();
            $dueDate = $billingMonth->copy()->day(min((int) $tenant->due_day, $billingMonth->daysInMonth))->startOfDay();

            $tenant->transactions()->create([
                'billing_month' => $billingMonth,
                'amount' => $tenant->monthly_rent,
                'due_date' => $dueDate,
                'paid_at' => now(),
            ]);
        }
        
        return redirect()->route('tenants.index')->with('success', 'Tenant berhasil ditambahkan dan pembayaran awal dicatat.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $tenant = Tenant::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone_number' => 'required|string|max:20',
            'room_number' => 'required|string|max:20|unique:tenants,room_number,' . $tenant->id,
            'monthly_rent' => 'required|integer|min:0',
            'due_day' => 'required|integer|min:1|max:31',
            'move_in_date' => 'required|date',
            'move_out_date' => 'required|date|after:move_in_date',
        ]);

        $tenant->update($validated);

        return redirect()
            ->route('tenants.index')
            ->with('success', 'Tenant berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->delete();

        return redirect()
            ->route('tenants.index')
            ->with('success', 'Tenant berhasil dihapus.');
    }

    /**
     * Record payment for specified number of months and extend move_out_date.
     */
    public function pay(Request $request, string $id)
    {
        $validated = $request->validate([
            'months' => 'required|integer|in:1,3,6,12',
        ]);

        $tenant = Tenant::findOrFail($id);
        $months = (int) $validated['months'];

        // Determine starting billing month
        // We pay off any existing unpaid transactions first
        $unpaidTx = $tenant->transactions()
            ->whereNull('paid_at')
            ->orderBy('billing_month', 'asc')
            ->get();

        $monthsToPay = $months;

        foreach ($unpaidTx as $tx) {
            if ($monthsToPay <= 0) break;
            $tx->update([
                'paid_at' => now(),
                'amount' => $tenant->monthly_rent,
            ]);
            $monthsToPay--;
        }

        // If there are still months left to pay, create new transactions for subsequent months
        if ($monthsToPay > 0) {
            $latestTx = $tenant->transactions()->orderBy('billing_month', 'desc')->first();

            if ($latestTx) {
                $startBillingMonth = \Carbon\Carbon::parse($latestTx->billing_month)->addMonth()->startOfMonth();
            } else {
                $startBillingMonth = $tenant->move_in_date 
                    ? \Carbon\Carbon::parse($tenant->move_in_date)->startOfMonth() 
                    : now()->startOfMonth();
            }

            for ($i = 0; $i < $monthsToPay; $i++) {
                $billingMonth = $startBillingMonth->copy()->addMonths($i)->startOfMonth();
                $dueDate = $billingMonth->copy()->day(min((int) $tenant->due_day, $billingMonth->daysInMonth))->startOfDay();

                $tenant->transactions()->create([
                    'billing_month' => $billingMonth,
                    'amount' => $tenant->monthly_rent,
                    'due_date' => $dueDate,
                    'paid_at' => now(),
                ]);
            }
        }

        // Adjust move_out_date: add $months to the current move_out_date
        $currentEndDate = $tenant->move_out_date
            ? \Carbon\Carbon::parse($tenant->move_out_date)
            : ($tenant->move_in_date ? \Carbon\Carbon::parse($tenant->move_in_date) : now());

        $tenant->update([
            'move_out_date' => $currentEndDate->addMonthsNoOverflow($months),
        ]);

        return redirect()
            ->route('tenants.index')
            ->with('success', "Pembayaran {$months} bulan berhasil dicatat.");
    }
}
