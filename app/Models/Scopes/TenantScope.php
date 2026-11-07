<?php

namespace App\Models\Scopes;

use App\Support\Tenant\Contracts\TenantManagerInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global Scope untuk menginjeksi klausul WHERE id_client = ? secara atomik ke Abstract Syntax Tree (AST) Eloquent.
 * Mencegah cross-tenant data leakage dan eksploitasi IDOR di level database.
 *
 * @implements Scope<Model>
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        /** @var TenantManagerInterface $context */
        $context = app(TenantManagerInterface::class);

        if ($context->check()) {
            $column = $model->qualifyColumn('id_client');
            $builder->where($column, '=', $context->id());
        }
    }

    /**
     * Memperluas query builder dengan helper method multi-tenant.
     *
     * @param  Builder<Model>  $builder
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('withoutTenancy', function (Builder $builder): Builder {
            return $builder->withoutGlobalScope(TenantScope::class);
        });

        $builder->macro('whereTenant', function (Builder $builder, string $clientId): Builder {
            return $builder->withoutGlobalScope(TenantScope::class)->where($builder->getModel()->qualifyColumn('id_client'), '=', $clientId);
        });
    }
}
