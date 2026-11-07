<?php

namespace App\Models\Traits;

use App\Models\Client;
use App\Models\Scopes\TenantScope;
use App\Support\Tenant\Contracts\TenantManagerInterface;
use App\Support\Tenant\Exceptions\TenantAccessDeniedException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait untuk seluruh entitas domain yang terikat pada cabang apotek (Single Database Multi-Tenant).
 *
 * @mixin Model
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model): void {
            /** @var TenantManagerInterface $context */
            $context = app(TenantManagerInterface::class);

            if ($context->check()) {
                $tenantId = $context->id();

                if (empty($model->getAttribute('id_client'))) {
                    $model->setAttribute('id_client', $tenantId);
                } elseif ($model->getAttribute('id_client') !== $tenantId) {
                    throw new TenantAccessDeniedException(
                        "Manipulasi payload terdeteksi: id_client '{$model->getAttribute('id_client')}' tidak sesuai dengan cabang aktif '{$tenantId}'."
                    );
                }
            }
        });
    }

    /**
     * Relasi balik ke profil apotek cabang.
     *
     * @return BelongsTo<Client, static>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'id_client', 'id_client');
    }

    /**
     * Melepas filter TenantScope secara eksplisit (hanya untuk proses Super Admin atau background reconciliation).
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWithoutTenancy(Builder $query): Builder
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }

    /**
     * Mengarahkan kueri ke cabang apotek spesifik tanpa bergantung pada TenantContext aktif.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForTenant(Builder $query, string $clientId): Builder
    {
        return $query->withoutGlobalScope(TenantScope::class)->where($this->qualifyColumn('id_client'), $clientId);
    }
}
