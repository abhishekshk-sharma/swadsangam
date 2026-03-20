<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\BelongsToBranch;

class CashHandover extends Model
{
    use BelongsToBranch;
    protected $fillable = [
        'tenant_id', 'branch_id', 'cashier_id', 'handover_date',
        'denom_1', 'denom_2', 'denom_5', 'denom_10', 'denom_20',
        'denom_50', 'denom_100', 'denom_200', 'denom_500',
        'total_cash', 'notes', 'status', 'approved_by', 'approved_at',
    ];

    protected $casts = ['approved_at' => 'datetime', 'handover_date' => 'date'];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $q) {
            if (app()->bound('current_tenant_id')) {
                $q->where('cash_handovers.tenant_id', (int) app('current_tenant_id'));
            }
        });
    }

    public function cashier()
    {
        return $this->belongsTo(Employee::class, 'cashier_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    /** Recalculate total from denomination counts. */
    public function recalcTotal(): void
    {
        $this->total_cash =
            $this->denom_1   * 1   +
            $this->denom_2   * 2   +
            $this->denom_5   * 5   +
            $this->denom_10  * 10  +
            $this->denom_20  * 20  +
            $this->denom_50  * 50  +
            $this->denom_100 * 100 +
            $this->denom_200 * 200 +
            $this->denom_500 * 500;
    }

    public static function denominations(): array
    {
        return [1, 2, 5, 10, 20, 50, 100, 200, 500];
    }
}
