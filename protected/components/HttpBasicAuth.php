<?php
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of f-project.net
//
// � Copyright f-project.net 2015. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////
class HttpBasicAuth extends \CFilter
{
    /**
     * @inheritdoc
     */
    protected function preFilter($filterChain)
    {
        $header = getallheaders();
        if(is_array($header) && array_key_exists('Authorization', $header)) {
            $authHeader = getallheaders()['Authorization'];
            if ($authHeader !== null && preg_match("/^Basic\\s+(.*?)$/", $authHeader, $matches)) {
                /** @var String $authString */
                $authString = base64_encode(Yii::app()->authClient->clientRSId . ":" . Yii::app()->authClient->clientRSSecret);
                if (strcmp($matches[1], $authString) == 0) {
                    return true;
                }
            }
        }
        return false;
    }
}