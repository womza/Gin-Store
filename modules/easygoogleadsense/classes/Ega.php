<?php

class Ega extends ObjectModel
{
    public $id_shop;
    public $title;
    public $show_title;
    public $content;
    public $hook;
    public $active;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'easy_google_adsense',
        'primary' => 'id_easy_google_adsense',
        'fields' => array(
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => false),
            'title' => array('type' => self::TYPE_STRING, 'required' => true, 'size' => 128),
            'show_title' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),    
            'content' => array('type' => self::TYPE_HTML, 'required' => true),
            'hook' => array('type' => self::TYPE_STRING, 'required' => true, 'size' => 128),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),    
        )
    ); 
    
    public static function getAdvertisments($id_shop, $hook = '') {
        $query = 'SELECT * FROM `'._DB_PREFIX_.'easy_google_adsense`';
        $query .= 'WHERE id_shop = '.$id_shop;
        if ($hook != '')
            $query .= ' AND hook = "'.$hook.'" AND active = 1';
        $query .= ' ORDER BY id_easy_google_adsense DESC';
        return Db::getInstance()->ExecuteS($query);
    }
}