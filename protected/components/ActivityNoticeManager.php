<?php
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of f-project.net
//
// © Copyright f-project.net 2015. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////
/**
 * ActivityNoticeManager provides a set of methods for sending/receiving message using PhpAmqpLib
 *
 * @author Bui Sy Nguyen <nguyenbs@gmail.com>
 */
class ActivityNoticeManager extends \fproject\amqp\ActivityNoticeManager implements IApplicationComponent{
    /**
     * Initializes the application component.
     * This method is invoked after the application completes configuration.
     */
    public function init()
    {
        $this->_isInitialized = true;
    }

    private $_isInitialized = false;

    /**
     * @return boolean whether the {@link init()} method has been invoked.
     */
    public function getIsInitialized()
    {
        return $this->_isInitialized;
    }

    /**
     * @inheritdoc
     * */
    public function getDispatcher()
    {
        $user = Yii::app()->user;
        if(isset($user))
            $dispatcher = ['id'=>$user->sub,'name'=>$user->getState('username'), 'type'=>'user'];
        else
            $dispatcher = null;
        return $dispatcher;
    }
}