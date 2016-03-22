<?php
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of f-project.net
//
// © Copyright f-project.net 2014. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////

/**
 * Define the constants use for comment target and attachment target
 * @author Bui Sy Nguyen <nguyenbs@gmail.com>
 */
class TargetType {
    const PROJECT = 0;
    const TASK = 1;
    const ISSUE = 2;
    const RESOURCE = 3;
    const CALENDAR_ITEM = 4;
    const USER = 5;
    const POST = 6;
    const TASK_RESOURCE_ASSIGNMENT = 7;

    /**
     * Get a target type's name
     * @param int $type the target type
     * @return string
     */
    public static function getTargetTypeName($type)
    {
        switch($type)
        {
            case self::PROJECT:
                return 'Project';
            case self::TASK:
                return 'Task';
            case self::ISSUE:
                return 'Issue';
            case self::RESOURCE:
                return 'Resource';
            case self::CALENDAR_ITEM:
                return 'CalendarItem';
            case self::USER:
                return 'User';
            case self::POST:
                return 'Post';
            case self::TASK_RESOURCE_ASSIGNMENT:
                return 'TaskResourceAssignment';
            default:
                return null;
        }
    }
} 