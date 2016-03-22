<?php
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of ProjectKit.net
//
// © Copyright ProjectKit.net 2014. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////

/**
 *
 * The AmfAuth class is used for AMF Service authentication.
 *
 * @author Bui Sy Nguyen <nguyenbs@gmail.com>
 */
class AmfAcl extends \fproject\amf\acl\Acl{

    const DEFAULT_LOGIN_ROLE = 'defaultLoginRole';

    /**
     * Singleton instance
     *
     * @var AmfAcl
     */
    protected static $_instance = null;

    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return AmfAcl
     */
    protected function __construct()
    {
        $this->initAcl();
    }

    /**
     * Returns an singleton instance of AmfAcl
     *
     * Singleton pattern implementation
     *
     * @return AmfAcl Provides a fluent interface
     */
    public static function staticInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function initAcl()
    {
        $this->addRole(self::DEFAULT_LOGIN_ROLE);
        $this->allow(self::DEFAULT_LOGIN_ROLE);
    }
} 