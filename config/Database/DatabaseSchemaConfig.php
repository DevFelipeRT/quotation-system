<?php

namespace Config\Database;

/**
 * DatabaseSchemaConfig
 *
 * Central registry of the database schema structure, including
 * table and column identifiers used throughout the domain and
 * infrastructure layers.
 *
 * This class provides a single source of truth for schema semantics.
 * It contains no logic for credentials or connections.
 */
class DatabaseSchemaConfig
{
    // ───────────────────────────────────────
    // Table names
    // ───────────────────────────────────────

    /** @var string Name of the clients table. */
    public const CLIENTS_TABLE = 'cliente';

    /** @var string Name of the quotations table. */
    public const QUOTATIONS_TABLE = 'orcamento';

    /** @var string Name of the categories table. */
    public const CATEGORIES_TABLE = 'categoria';

    /** @var string Name of the items table. */
    public const ITEMS_TABLE = 'item';

    /** @var string Name of the item_orcamento (quotation items) table. */
    public const QUOTATION_ITEMS_TABLE = 'item_orcamento';

    /** @var string Name of the types table. */
    public const TYPES_TABLE = 'tipos';

    // ───────────────────────────────────────
    // Clients table columns
    // ───────────────────────────────────────

    public const CLIENT_ID        = 'id';
    public const CLIENT_NAME      = 'nome';
    public const CLIENT_EMAIL     = 'email';
    public const CLIENT_PHONE     = 'telefone';
    public const CLIENT_CREATED   = 'data_criacao';
    public const CLIENT_UPDATED   = 'data_modificacao';

    // ───────────────────────────────────────
    // Quotations table columns
    // ───────────────────────────────────────

    public const QUOTATION_ID           = 'id';
    public const QUOTATION_NAME         = 'nome';
    public const QUOTATION_DESCRIPTION  = 'descricao';
    public const QUOTATION_DISCOUNT     = 'desconto';
    public const QUOTATION_FEE          = 'taxa';
    public const QUOTATION_CLIENT_ID    = 'cliente_id';
    public const QUOTATION_CREATED      = 'data_criacao';
    public const QUOTATION_UPDATED      = 'data_modificacao';

    // ───────────────────────────────────────
    // Categories table columns
    // ───────────────────────────────────────

    public const CATEGORY_ID           = 'id';
    public const CATEGORY_NAME         = 'nome';
    public const CATEGORY_DESCRIPTION  = 'descricao';
    public const CATEGORY_CREATED      = 'data_criacao';
    public const CATEGORY_UPDATED      = 'data_modificacao';

    // ───────────────────────────────────────
    // Items table columns
    // ───────────────────────────────────────

    public const ITEM_ID           = 'id';
    public const ITEM_NAME         = 'nome';
    public const ITEM_DESCRIPTION  = 'descricao';
    public const ITEM_PRICE        = 'preco';
    public const ITEM_CATEGORY_ID  = 'id_categoria';
    public const ITEM_CREATED      = 'data_criacao';
    public const ITEM_UPDATED      = 'data_modificacao';

    // ───────────────────────────────────────
    // Quotation Items table columns
    // ───────────────────────────────────────

    public const QITEM_ID             = 'id';
    public const QITEM_QUOTATION_ID   = 'id_orcamento';
    public const QITEM_ITEM_ID        = 'id_item';
    public const QITEM_TYPE_ID        = 'id_tipo';
    public const QITEM_QUANTITY       = 'quantidade';
    public const QITEM_DISCOUNT       = 'desconto';
    public const QITEM_FEE            = 'taxa';
    public const QITEM_CREATED        = 'data_criacao';
    public const QITEM_UPDATED        = 'data_modificacao';

    // ───────────────────────────────────────
    // Types table columns
    // ───────────────────────────────────────

    public const TYPE_ID           = 'id';
    public const TYPE_NAME         = 'nome';
    public const TYPE_DESCRIPTION  = 'descricao';
    public const TYPE_CREATED      = 'data_criacao';
    public const TYPE_UPDATED      = 'data_modificacao';
}
