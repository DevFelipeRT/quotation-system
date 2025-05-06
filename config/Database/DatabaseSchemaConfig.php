<?php

namespace Config\Database;

/**
 * Class DatabaseSchemaConfig
 *
 * Centralized reference for all table and column identifiers used across
 * the database layer. This class contains no logic and no sensitive data.
 *
 * @package Config\Database
 */
final class DatabaseSchemaConfig
{
    // ───────────── Tables ─────────────

    public const CLIENTS_TABLE_NAME       = 'cliente';
    public const QUOTATIONS_TABLE_NAME    = 'orcamento';
    public const CATEGORIES_TABLE_NAME    = 'categoria';
    public const ITEMS_TABLE_NAME         = 'item';
    public const QUOTATION_ITEMS_TABLE_NAME = 'item_orcamento';
    public const TYPES_TABLE_NAME         = 'tipos';

    // ─────── Columns: Clients ───────

    public const CLIENT_ID        = 'id';
    public const CLIENT_NAME      = 'nome';
    public const CLIENT_EMAIL     = 'email';
    public const CLIENT_PHONE     = 'telefone';
    public const CLIENT_CREATED   = 'data_criacao';
    public const CLIENT_UPDATED   = 'data_modificacao';

    // ─────── Columns: Quotations ───────

    public const QUOTATION_ID           = 'id';
    public const QUOTATION_NAME         = 'nome';
    public const QUOTATION_DESCRIPTION  = 'descricao';
    public const QUOTATION_DISCOUNT     = 'desconto';
    public const QUOTATION_FEE          = 'taxa';
    public const QUOTATION_CLIENT_ID    = 'cliente_id';
    public const QUOTATION_CREATED      = 'data_criacao';
    public const QUOTATION_UPDATED      = 'data_modificacao';

    // ─────── Columns: Categories ───────

    public const CATEGORY_ID           = 'id';
    public const CATEGORY_NAME         = 'nome';
    public const CATEGORY_DESCRIPTION  = 'descricao';
    public const CATEGORY_CREATED      = 'data_criacao';
    public const CATEGORY_UPDATED      = 'data_modificacao';

    // ─────── Columns: Items ───────

    public const ITEM_ID           = 'id';
    public const ITEM_NAME         = 'nome';
    public const ITEM_DESCRIPTION  = 'descricao';
    public const ITEM_PRICE        = 'preco';
    public const ITEM_CATEGORY_ID  = 'id_categoria';
    public const ITEM_CREATED      = 'data_criacao';
    public const ITEM_UPDATED      = 'data_modificacao';

    // ─────── Columns: Quotation Items ───────

    public const QITEM_ID             = 'id';
    public const QITEM_QUOTATION_ID   = 'id_orcamento';
    public const QITEM_ITEM_ID        = 'id_item';
    public const QITEM_TYPE_ID        = 'id_tipo';
    public const QITEM_QUANTITY       = 'quantidade';
    public const QITEM_DISCOUNT       = 'desconto';
    public const QITEM_FEE            = 'taxa';
    public const QITEM_CREATED        = 'data_criacao';
    public const QITEM_UPDATED        = 'data_modificacao';

    // ─────── Columns: Types ───────

    public const TYPE_ID           = 'id';
    public const TYPE_NAME         = 'nome';
    public const TYPE_DESCRIPTION  = 'descricao';
    public const TYPE_CREATED      = 'data_criacao';
    public const TYPE_UPDATED      = 'data_modificacao';
}
