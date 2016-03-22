<?php
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of f-project.net
//
// © Copyright f-project.net 2014. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////
Yii::import('zii.widgets.CMenu');
/**
 * The Menu class extends zii.widgets.CMenu as a workaround for limitations about URL
 *
 * @author Bui Sy Nguyen <nguyenbs@gmail.com>
 */
class Menu extends CMenu{
    private $urlToPreg = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->extractPregs($this->items);
        parent::init();
    }

    private function extractPregs($items)
    {
        foreach($items as $key=>$item)
        {
            if(isset($item['url']) && is_array($item['url']))
            {
                if(isset($item['url']['@preg']))
                {
                    $this->urlToPreg[$item['url'][0]] = $item['url']['@preg'];
                    unset($this->items[$key]['url']['@preg']);
                }
            }
            if(isset($item['items']) && count($item['items']))
            {
                $this->extractPregs($item['items']);
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function isItemActive($item,$route)
    {
        if(isset($item['url']))
        {
            $url = $item['url'];
            if(is_array($url) && isset($this->urlToPreg[$url[0]]) && preg_match($this->urlToPreg[$url[0]], $route))
                return true;
        }
        return parent::isItemActive($item,$route);
    }
}