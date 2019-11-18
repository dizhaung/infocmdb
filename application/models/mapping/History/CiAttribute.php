<?php

/**
 * TABLE => 'CI_ATTRIBUTE'
 *
 */
class Db_History_CiAttribute extends Zend_Db_Table_Abstract
{

    protected $_name    = 'h_ci_attribute';
    protected $_primary = 'id';

    const TABLE_NAME = 'h_ci_attribute';

    // define db attributes
    const ID            = 'id';
    const CI_ID         = 'ci_id';
    const ATTRIBUTE_ID  = 'attribute_id';
    const VALUE_TEXT    = 'value_text';
    const VALUE_DATE    = 'value_date';
    const VALUE_DEFAULT = 'value_default';
    const VALUE_CI      = 'value_ci';
    const IS_INITIAL    = 'is_initial';
    const NOTE          = 'note';

    const HISTORY_ID        = 'history_id';
    const HISTORY_ID_DELETE = 'history_id_delete';

    const VALID_FROM = 'valid_from';
    const VALID_TO   = 'valid_to';
}