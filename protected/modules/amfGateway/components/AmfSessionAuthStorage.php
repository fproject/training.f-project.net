<?php
///////////////////////////////////////////////////////////////////////////////
// Licensed Source Code - Property of ProjectKit.net
//
// © Copyright ProjectKit.net 2014. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////


class AmfSessionAuthStorage implements \fproject\amf\auth\AuthStorageInterface{
    /**
     * Default session object member name
     */
    const MEMBER_DEFAULT = 'amf_storage';

    /**
     * Object to proxy $_SESSION storage
     *
     * @var CHttpSession
     */
    protected $_session;

    /**
     * Session object member
     *
     * @var mixed
     */
    protected $_member;

    /**
     * Init the session variable
     *
     * @param string $member
     * @return AmfSessionAuthStorage
     */
    public function __construct($member = self::MEMBER_DEFAULT)
    {
        $this->_member    = $member;
        $this->_session   = Yii::app()->session;
    }

    /**
     * Returns the name of the session object member
     *
     * @return string
     */
    public function getMember()
    {
        return $this->_member;
    }

    /**
     * Returns true if and only if storage is empty
     *
     * @throws \fproject\amf\session\SessionException If it is impossible to determine whether storage is empty
     * @return boolean
     */
    public function isEmpty()
    {
        return !isset($this->_session[$this->_member]);
    }

    /**
     * Returns the contents of storage
     *
     * Behavior is undefined when storage is empty.
     *
     * @throws \fproject\amf\session\SessionException If reading contents from storage is impossible
     * @return mixed
     */
    public function read()
    {
        return $this->_session[$this->_member];
    }

    /**
     * Writes $contents to storage
     *
     * @param  mixed $contents
     * @throws \fproject\amf\session\SessionException If writing $contents to storage is impossible
     * @return void
     */
    public function write($contents)
    {
        $this->_session[$this->_member] = $contents;
    }

    /**
     * Clears contents from storage
     *
     * @throws \fproject\amf\session\SessionException If clearing contents from storage is impossible
     * @return void
     */
    public function clear()
    {
        unset($this->_session[$this->_member]);
    }
}