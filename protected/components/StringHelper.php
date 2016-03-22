<?php
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of f-project.net
//
// © Copyright f-project.net 2014. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////
/**
 * The helper class for string handle
 *
 * @author Bui Sy Nguyen <nguyenbs@gmail.com>
 */
class StringHelper {

    /**
     * Checks if a string exists in an array of strings
     * @param string $needle <p>
     * @param array $haystack <p>
     * The array.
     * </p>
     * @param bool $caseSensitive
     * @return bool true if needle is found in the array,
     * false otherwise.
     */
    public static function inArray($needle, $haystack, $caseSensitive=true)
    {
        foreach($haystack as $s)
        {
            if($caseSensitive)
                $i = strcmp($s, $needle);
            else
                $i = strcasecmp($s, $needle);
            if($i == 0)
                return true;
        }
        return false;
    }
} 