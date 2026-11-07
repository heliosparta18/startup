<?php

namespace App\Support\Tenant\Contracts;

use App\Models\Client;
use App\Support\Tenant\Exceptions\TenantContextMissingException;

interface TenantManagerInterface
{
    /**
     * Dapatkan instance client tenant yang sedang aktif dalam request container.
     */
    public function get(): ?Client;

    /**
     * Tetapkan instance client tenant aktif ke dalam context.
     */
    public function set(Client $client): void;

    /**
     * Dapatkan ID UUID client tenant yang aktif.
     *
     * @throws TenantContextMissingException
     */
    public function id(): string;

    /**
     * Dapatkan nama resmi cabang apotek tenant yang aktif.
     */
    public function name(): ?string;

    /**
     * Dapatkan slug URL cabang apotek tenant yang aktif.
     */
    public function slug(): ?string;

    /**
     * Periksa apakah request saat ini terikat pada suatu tenant aktif.
     */
    public function check(): bool;

    /**
     * Alias dari method check().
     */
    public function has(): bool;

    /**
     * Bersihkan konteks tenant yang tersimpan (misal di akhir background task/job).
     */
    public function clear(): void;

    /**
     * Eksekusi closure / callback dalam konteks tenant tertentu dan pulihkan konteks semula.
     * Sangat krusial untuk operasi batch lintas cabang atau agregasi Super Admin.
     *
     * @template TReturn
     *
     * @param  \Closure(Client): TReturn  $callback
     * @return TReturn
     */
    public function runInContext(Client $client, \Closure $callback): mixed;
}
