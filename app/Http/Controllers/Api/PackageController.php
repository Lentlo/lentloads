<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\UserPackage;
use App\Models\Transaction;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::active()->ordered()->get();

        return $this->successResponse($packages);
    }

    public function purchase(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'listing_id' => 'required|exists:listings,id',
            'payment_method' => 'required|string',
        ]);

        $package = Package::active()->findOrFail($validated['package_id']);
        $listing = Listing::findOrFail($validated['listing_id']);

        if (!$listing->isOwner(auth()->user())) {
            return $this->errorResponse('Unauthorized', 403);
        }

        DB::beginTransaction();

        try {
            // Create transaction
            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'transactionable_type' => Package::class,
                'transactionable_id' => $package->id,
                'amount' => $package->price,
                'currency' => 'INR',
                'type' => 'debit',
                'status' => 'pending',
                'payment_method' => $validated['payment_method'],
                'description' => "Purchase of {$package->name} for listing #{$listing->id}",
            ]);

            // Here you would integrate with payment gateway
            // For now, we'll simulate successful payment
            $transaction->markAsCompleted();

            // Create user package
            $userPackage = UserPackage::create([
                'user_id' => auth()->id(),
                'listing_id' => $listing->id,
                'package_id' => $package->id,
                'starts_at' => now(),
                'expires_at' => now()->addDays($package->duration_days),
                'status' => 'active',
            ]);

            // Apply package benefits to listing
            $userPackage->applyToListing();

            DB::commit();

            return $this->successResponse([
                'transaction' => $transaction,
                'package' => $userPackage,
            ], 'Package purchased successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Payment failed: ' . $e->getMessage(), 500);
        }
    }

    public function myPackages()
    {
        $packages = UserPackage::with(['package', 'listing:id,title,slug'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return $this->paginatedResponse($packages);
    }

    public function activePackages()
    {
        $packages = UserPackage::with(['package', 'listing:id,title,slug'])
            ->where('user_id', auth()->id())
            ->active()
            ->get();

        return $this->successResponse($packages);
    }
}
