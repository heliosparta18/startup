<?php

namespace Tests\Feature;

/**
 * Constraint diuji pada MariaDB / MySQL nyata:
 * UUID primary keys, composite unique stock (id_obat, id_client),
 * immutability snapshot harga pada item_pesanan (ADR-05),
 * dan idempotency ringkasan periode penjualan.
 *
 * Menggantikan constraint legacy kantin untuk sistem Apotek Multi-Klien.
 */
class DatabaseConstraintTest extends ApotekDatabaseConstraintTest
{
    // Mewarisi pengujian constraint Apotek lengkap
}
