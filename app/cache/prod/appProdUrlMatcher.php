<?php

use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;

/**
 * appProdUrlMatcher.
 *
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class appProdUrlMatcher extends Symfony\Bundle\FrameworkBundle\Routing\RedirectableUrlMatcher
{
    /**
     * Constructor.
     */
    public function __construct(RequestContext $context)
    {
        $this->context = $context;
    }

    public function match($pathinfo)
    {
        $allow = array();
        $pathinfo = rawurldecode($pathinfo);
        $context = $this->context;
        $request = $this->request;

        // _homepage
        if (rtrim($pathinfo, '/') === '') {
            if (substr($pathinfo, -1) !== '/') {
                return $this->redirect($pathinfo.'/', '_homepage');
            }

            return array (  '_controller' => 'AppBundle\\Controller\\DefaultController::indexAction',  '_route' => '_homepage',);
        }

        if (0 === strpos($pathinfo, '/a')) {
            if (0 === strpos($pathinfo, '/account')) {
                // _account_register
                if ($pathinfo === '/account/register') {
                    return array (  '_controller' => 'AppBundle\\Controller\\AccountController::registerAction',  '_route' => '_account_register',);
                }

                if (0 === strpos($pathinfo, '/account/c')) {
                    // _account_checkemail
                    if ($pathinfo === '/account/checkemail') {
                        return array (  '_controller' => 'AppBundle\\Controller\\AccountController::checkEmailAction',  '_route' => '_account_checkemail',);
                    }

                    // _account_confirmemail
                    if (0 === strpos($pathinfo, '/account/confirmemail') && preg_match('#^/account/confirmemail/(?P<token>[^/]++)$#s', $pathinfo, $matches)) {
                        return $this->mergeDefaults(array_replace($matches, array('_route' => '_account_confirmemail')), array (  '_controller' => 'AppBundle\\Controller\\AccountController::confirmEmailAction',));
                    }

                    // _account_create
                    if ($pathinfo === '/account/create') {
                        return array (  '_controller' => 'AppBundle\\Controller\\AccountController::createAction',  '_route' => '_account_create',);
                    }

                }

                // _account_view
                if ($pathinfo === '/account/view') {
                    return array (  '_controller' => 'AppBundle\\Controller\\AccountController::viewAction',  '_route' => '_account_view',);
                }

                // _account_pwd
                if ($pathinfo === '/account/pwd') {
                    return array (  '_controller' => 'AppBundle\\Controller\\AccountController::changePasswordAction',  '_route' => '_account_pwd',);
                }

                // _account_forgot_passwd
                if ($pathinfo === '/account/forgot_passwd') {
                    return array (  '_controller' => 'AppBundle\\Controller\\AccountController::forgotPasswordAction',  '_route' => '_account_forgot_passwd',);
                }

                // _account_reset_passwd
                if (0 === strpos($pathinfo, '/account/reset_passwd') && preg_match('#^/account/reset_passwd/(?P<token>[^/]++)$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_account_reset_passwd')), array (  '_controller' => 'AppBundle\\Controller\\AccountController::resetPasswordAction',));
                }

            }

            if (0 === strpos($pathinfo, '/auth/log')) {
                if (0 === strpos($pathinfo, '/auth/login')) {
                    // _auth_login
                    if ($pathinfo === '/auth/login') {
                        return array (  '_controller' => 'AppBundle\\Controller\\AuthController::loginAction',  '_route' => '_auth_login',);
                    }

                    // _auth_login_check
                    if ($pathinfo === '/auth/login_check') {
                        return array (  '_controller' => 'AppBundle\\Controller\\AuthController::securityCheckAction',  '_route' => '_auth_login_check',);
                    }

                }

                // _auth_logout
                if ($pathinfo === '/auth/logout') {
                    return array (  '_controller' => 'AppBundle\\Controller\\AuthController::logoutAction',  '_route' => '_auth_logout',);
                }

            }

            if (0 === strpos($pathinfo, '/aid')) {
                // _aid_list
                if (rtrim($pathinfo, '/') === '/aid') {
                    if (substr($pathinfo, -1) !== '/') {
                        return $this->redirect($pathinfo.'/', '_aid_list');
                    }

                    return array (  '_controller' => 'AppBundle\\Controller\\AccessIDController::listAction',  '_route' => '_aid_list',);
                }

                // _aid_new
                if ($pathinfo === '/aid/new') {
                    return array (  '_controller' => 'AppBundle\\Controller\\AccessIDController::newAction',  '_route' => '_aid_new',);
                }

                // _aid_item
                if (preg_match('#^/aid/(?P<id>[^/]++)/view$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_aid_item')), array (  '_controller' => 'AppBundle\\Controller\\AccessIDController::itemViewAction',));
                }

                // _aid_item_delete
                if (preg_match('#^/aid/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_aid_item_delete')), array (  '_controller' => 'AppBundle\\Controller\\AccessIDController::itemDeleteAction',));
                }

                // _loc_assignloc
                if (preg_match('#^/aid/(?P<id>[^/]++)/assignloc$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_loc_assignloc')), array (  '_controller' => 'AppBundle\\Controller\\AccessIDController::assignLocAction',));
                }

                // _aid_ajax_assign_list
                if (preg_match('#^/aid/(?P<id>[^/]++)/ajax/assign_list$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_aid_ajax_assign_list')), array (  '_controller' => 'AppBundle\\Controller\\AccessIDController::ajaxAssignLocList',));
                }

                // _aid_ajax_getdetails
                if (preg_match('#^/aid/(?P<id>[^/]++)/ajax/getdetails$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_aid_ajax_getdetails')), array (  '_controller' => 'AppBundle\\Controller\\AccessIDController::ajaxGetDetails',));
                }

                // _aid_ajax_setenabled
                if (preg_match('#^/aid/(?P<id>[^/]++)/ajax/setenabled/(?P<enabled>[^/]++)$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_aid_ajax_setenabled')), array (  '_controller' => 'AppBundle\\Controller\\AccessIDController::ajaxSetEnabled',));
                }

                // _aid_ajax_setcaption
                if (preg_match('#^/aid/(?P<id>[^/]++)/ajax/setcaption$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_aid_ajax_setcaption')), array (  '_controller' => 'AppBundle\\Controller\\AccessIDController::ajaxSetCaption',));
                }

                // _aid_ajax_setpwd
                if (preg_match('#^/aid/(?P<id>[^/]++)/ajax/setpwd$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_aid_ajax_setpwd')), array (  '_controller' => 'AppBundle\\Controller\\AccessIDController::ajaxSetPwd',));
                }

            }

        }

        if (0 === strpos($pathinfo, '/loc')) {
            // _loc_list
            if (rtrim($pathinfo, '/') === '/loc') {
                if (substr($pathinfo, -1) !== '/') {
                    return $this->redirect($pathinfo.'/', '_loc_list');
                }

                return array (  '_controller' => 'AppBundle\\Controller\\LocationController::listAction',  '_route' => '_loc_list',);
            }

            // _loc_new
            if ($pathinfo === '/loc/new') {
                return array (  '_controller' => 'AppBundle\\Controller\\LocationController::newAction',  '_route' => '_loc_new',);
            }

            // _loc_item
            if (preg_match('#^/loc/(?P<id>[^/]++)/view$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => '_loc_item')), array (  '_controller' => 'AppBundle\\Controller\\LocationController::itemViewAction',));
            }

            // _loc_item_delete
            if (preg_match('#^/loc/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => '_loc_item_delete')), array (  '_controller' => 'AppBundle\\Controller\\LocationController::itemDeleteAction',));
            }

            // _loc_assignaid
            if (preg_match('#^/loc/(?P<id>[^/]++)/assignaid$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => '_loc_assignaid')), array (  '_controller' => 'AppBundle\\Controller\\LocationController::assignAidAction',));
            }

            // _loc_ajax_assign_list
            if (preg_match('#^/loc/(?P<id>[^/]++)/ajax/assign_list$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => '_loc_ajax_assign_list')), array (  '_controller' => 'AppBundle\\Controller\\LocationController::ajaxAssignAidList',));
            }

            // _loc_ajax_getdetails
            if (preg_match('#^/loc/(?P<id>[^/]++)/ajax/getdetails$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => '_loc_ajax_getdetails')), array (  '_controller' => 'AppBundle\\Controller\\LocationController::ajaxGetDetails',));
            }

            // _loc_ajax_setenabled
            if (preg_match('#^/loc/(?P<id>[^/]++)/ajax/setenabled/(?P<enabled>[^/]++)$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => '_loc_ajax_setenabled')), array (  '_controller' => 'AppBundle\\Controller\\LocationController::ajaxSetEnabled',));
            }

            // _loc_ajax_setcaption
            if (preg_match('#^/loc/(?P<id>[^/]++)/ajax/setcaption$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => '_loc_ajax_setcaption')), array (  '_controller' => 'AppBundle\\Controller\\LocationController::ajaxSetCaption',));
            }

            // _loc_ajax_setpwd
            if (preg_match('#^/loc/(?P<id>[^/]++)/ajax/setpwd$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => '_loc_ajax_setpwd')), array (  '_controller' => 'AppBundle\\Controller\\LocationController::ajaxSetPwd',));
            }

        }

        if (0 === strpos($pathinfo, '/iodev')) {
            // _iodev_list
            if (rtrim($pathinfo, '/') === '/iodev') {
                if (substr($pathinfo, -1) !== '/') {
                    return $this->redirect($pathinfo.'/', '_iodev_list');
                }

                return array (  '_controller' => 'AppBundle\\Controller\\IODeviceController::listAction',  '_route' => '_iodev_list',);
            }

            // _iodev_item
            if (preg_match('#^/iodev/(?P<id>[^/]++)/view$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => '_iodev_item')), array (  '_controller' => 'AppBundle\\Controller\\IODeviceController::itemAction',));
            }

            // _iodev_item_edit
            if (preg_match('#^/iodev/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => '_iodev_item_edit')), array (  '_controller' => 'AppBundle\\Controller\\IODeviceController::itemEditAction',));
            }

            // _iodev_item_delete
            if (preg_match('#^/iodev/(?P<id>[^/]++)/delete$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => '_iodev_item_delete')), array (  '_controller' => 'AppBundle\\Controller\\IODeviceController::itemDeleteAction',));
            }

            // _iodev_channel_item_edit
            if (preg_match('#^/iodev/(?P<devid>[^/]++)/(?P<id>[^/]++)/edit$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => '_iodev_channel_item_edit')), array (  '_controller' => 'AppBundle\\Controller\\IODeviceController::channelItemEditAction',));
            }

            // _iodev_ajax_setenabled
            if (preg_match('#^/iodev/(?P<id>[^/]++)/ajax/setenabled/(?P<enabled>[^/]++)$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => '_iodev_ajax_setenabled')), array (  '_controller' => 'AppBundle\\Controller\\IODeviceController::ajaxSetEnabled',));
            }

            // _iodev_ajax_setcomment
            if (preg_match('#^/iodev/(?P<id>[^/]++)/ajax/setcomment$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => '_iodev_ajax_setcomment')), array (  '_controller' => 'AppBundle\\Controller\\IODeviceController::ajaxSetCaption',));
            }

            // _iodev_ajax_getfuncparams
            if (0 === strpos($pathinfo, '/iodev/ajax/getfuncparams') && preg_match('#^/iodev/ajax/getfuncparams/(?P<channel_id>[^/]++)/(?P<function>[^/]++)$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => '_iodev_ajax_getfuncparams')), array (  '_controller' => 'AppBundle\\Controller\\IODeviceController::ajaxGetfuncparamsAction',));
            }

        }

        if (0 === strpos($pathinfo, '/ajax')) {
            // _ajaxlngset
            if (0 === strpos($pathinfo, '/ajax/lngset') && preg_match('#^/ajax/lngset/(?P<_loc>[^/]++)$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => '_ajaxlngset')), array (  '_controller' => 'AppBundle\\Controller\\AjaxController::lngsetAction',));
            }

            // _ajax_pwdgen
            if (0 === strpos($pathinfo, '/ajax/pwdgen') && preg_match('#^/ajax/pwdgen/(?P<len>[^/]++)$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => '_ajax_pwdgen')), array (  '_controller' => 'AppBundle\\Controller\\AjaxController::pwdgenAction',));
            }

            if (0 === strpos($pathinfo, '/ajax/serverctrl-')) {
                // _ajax_serverctrl-sensorstate
                if (0 === strpos($pathinfo, '/ajax/serverctrl-sensorstate') && preg_match('#^/ajax/serverctrl\\-sensorstate/(?P<channel_id>[^/]++)$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_ajax_serverctrl-sensorstate')), array (  '_controller' => 'AppBundle\\Controller\\AjaxController::serverctrlSensorStateAction',));
                }

                // _ajax_serverctrl-tempval
                if (0 === strpos($pathinfo, '/ajax/serverctrl-tempval') && preg_match('#^/ajax/serverctrl\\-tempval/(?P<channel_id>[^/]++)$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_ajax_serverctrl-tempval')), array (  '_controller' => 'AppBundle\\Controller\\AjaxController::serverctrlTempValAction',));
                }

                // _ajax_serverctrl-connstate
                if ($pathinfo === '/ajax/serverctrl-connstate') {
                    return array (  '_controller' => 'AppBundle\\Controller\\AjaxController::serverctrlConnStateAction',  '_route' => '_ajax_serverctrl-connstate',);
                }

            }

        }

        throw 0 < count($allow) ? new MethodNotAllowedException(array_unique($allow)) : new ResourceNotFoundException();
    }
}
