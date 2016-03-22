<?php
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of f-project.net
//
// © Copyright f-project.net 2014. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////
/**
 * Html Helper class.
 *
 * @author Bui Sy Nguyen <nguyenbs@gmail.com>
 */
class HtmlHelper {
    /**
     * @param ActiveRecord $model
     * @param string $field
     * @param string $prefix
     * @param string $suffix
     * @return mixed
     */
    public static function markSearch($model,$field,$prefix='<strong>',$suffix='</strong>') {
        $className = get_class($model);
        if (isset($_GET[$className][$field])&&$_GET[$className][$field])
            return str_replace($_GET[$className][$field],$prefix.$_GET[$className][$field].$suffix,$model->getAttribute($field));
        else
            return $model->getAttribute($field);
    }
} 