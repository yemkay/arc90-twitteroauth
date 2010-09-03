<?php

/**
 * LICENSE
 *
 * This source file is subject to the new BSD license bundled with this package
 * in the file, LICENSE. This license is also available through the web at:
 * {@link http://www.opensource.org/licenses/bsd-license.php}. If you did not
 * receive a copy of the license, and are unable to obtain it through the web,
 * please send an email to matt@mattwilliamsnyc.com, and I will send you a copy.
 *
 * @category   Arc90
 * @package    Arc90_Service
 * @subpackage Twitter
 * @author     Matt Williams <matt@mattwilliamsnyc.com>
 * @copyright  Copyright (c) 2008 {@link http://arc90.com Arc90 Inc.}, Matt Williams
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id: Twitter.php 45 2009-02-18 03:52:07Z mattwilliamsnyc $
 */

/**
 * @see Arc90_Service_Twitter_Response
 */
require_once 'Arc90/Service/Twitter/Response.php';

/**
 * Arc90_Service_Twitter provides methods for interacting with the {@link http://twitter.com Twitter} API.
 *
 * Based on Twitter's {@link http://apiwiki.twitter.com/REST+API+Documentation online documentation}.
 * 
 * NOTE: At the time of this writing, clients are allowed 100 requests per 60 sixty minute time period,
 * starting from their first request. For more information, please see the API documentation regarding
 * {@link http://apiwiki.twitter.com/REST+API+Documentation#RateLimiting rate limiting}.
 *
 * @category   Arc90
 * @package    Arc90_Service
 * @subpackage Twitter
 * @author     Matt Williams <matt@mattwilliamsnyc.com>
 * @copyright  Copyright (c) 2008 {@link http://arc90.com Arc90 Inc.}, Matt Williams
 */
class Arc90_Service_Twitter
{
    /** Entry point for the Twitter API */
    const API_URI = 'http://api.twitter.com/1';
    const API_URI_S = 'https://api.twitter.com/1';

    /**#@+ {@link http://apiwiki.twitter.com/REST+API+Documentation#StatusMethods} */
    const PATH_STATUS_PUBLIC  = '/statuses/public_timeline';
    const PATH_STATUS_FRIENDS = '/statuses/friends_timeline';
    const PATH_STATUS_USER    = '/statuses/user_timeline';
    const PATH_STATUS_SHOW    = '/statuses/show';
    const PATH_STATUS_UPDATE  = '/statuses/update';
    const PATH_STATUS_MENTIONS = '/statuses/mentions';
    const PATH_STATUS_DESTROY = '/statuses/destroy';
    /**#@-*/

    /**#@+ {@link http://apiwiki.twitter.com/REST+API+Documentation#UserMethods} */
    const PATH_USER_FRIENDS   = '/statuses/friends';
    const PATH_USER_FOLLOWERS = '/statuses/followers';
    const PATH_USER_SHOW      = '/users/show';
    const PATH_USER_LOOKUP    = '/users/lookup';
    /**#@-*/

    /**#@+ {@link http://apiwiki.twitter.com/REST+API+Documentation#DirectMessageMethods} */
    const PATH_DM_MESSAGES    = '/direct_messages';
    const PATH_DM_SENT        = '/direct_messages/sent';
    const PATH_DM_NEW         = '/direct_messages/new';
    const PATH_DM_DESTROY     = '/direct_messages/destroy';
    /**#@-*/

    /**#@+ {@link http://apiwiki.twitter.com/REST+API+Documentation#FriendshipMethods} */
    const PATH_FRIEND_CREATE  = '/friendships/create';
    const PATH_FRIEND_DESTROY = '/friendships/destroy';
    const PATH_FRIEND_EXISTS  = '/friendships/exists';
    /**#@-*/

    /**#@+ {@link http://apiwiki.twitter.com/REST-API-Documentation#SocialGraphMethods} */
    const PATH_SG_FRIENDS     = '/friends/ids';
    const PATH_SG_FOLLOWERS   = '/followers/ids';

    /**#@+ {@link http://apiwiki.twitter.com/REST+API+Documentation#AccountMethods} */
    const PATH_ACCT_VERIFY    = '/account/verify_credentials';
    const PATH_ACCT_END_SESS  = '/account/end_session';
    const PATH_ACCT_LOCATION  = '/account/update_location';
    const PATH_ACCT_DEVICE    = '/account/update_delivery_device';
    const PATH_ACCT_COLORS    = '/account/update_profile_colors';
    const PATH_ACCT_IMAGE     = '/account/update_profile_image';
    const PATH_ACCT_BGIMAGE   = '/account/update_profile_background_image';
    const PATH_ACCT_LIMIT     = '/account/rate_limit_status';
    const PATH_ACCT_PROFILE   = '/account/update_profile';
    /**#@-*/

    /**#@+ {@link http://apiwiki.twitter.com/REST+API+Documentation#FavoriteMethods} */
    const PATH_FAV_FAVORITES  = '/favorites';
    const PATH_FAV_CREATE     = '/favorites/create';
    const PATH_FAV_DESTROY    = '/favorites/destroy';
    /**#@-*/

    /**#@+ {@link http://apiwiki.twitter.com/REST+API+Documentation#NotificationMethods} */
    const PATH_NOTIF_FOLLOW   = '/notifications/follow';
    const PATH_NOTIF_LEAVE    = '/notifications/leave';
    /**#@-*/

    /**#@+ {@link http://apiwiki.twitter.com/REST+API+Documentation#BlockMethods} */
    const PATH_BLOCK_CREATE   = '/blocks/create';
    const PATH_BLOCK_DESTROY  = '/blocks/destroy';
    /**#@-*/

    /**#@+ {@link http://apiwiki.twitter.com/REST+API+Documentation#HelpMethods} */
    const PATH_HELP_TEST      = '/help/test';
    /**#@-*/

    /** Maximum length (in number of characters) of status updates */
    const STATUS_MAXLENGTH = 140;

    /**
     * Twitter account username
     *
     * @var string
     */
    protected $_authUsername = '';

    /**
     * Twitter account password
     *
     * @var string 
     */
    protected $_authPassword = '';


    /**
     * {@link setCallback()}
     *
     * @var string
     */
    protected $_callback;

    /**
     * {@link setSource()}
     *
     * @var string
     */
    protected $_source = '';

    /**
     * {@link suppressResponseCodes()}
     *
     * @var boolean
     */
    protected $_suppressResponseCodes = FALSE;

    /**
     * {@link setTimeout()}
     *
     * @var integer
     */
    protected $_timeout = 60;

    /**
     * Constructs a new Twitter Web Service Client.
     *
     * User credentials may optionally be set as constructor parameters.
     *
     * @param string  $username Twitter account username
     * @param string  $password Twitter account password
     * @param string  $source   Source name to be sent with user status updates
     * @param integer $timeout  Length of time (in seconds) before timeout
     */
    public function __construct($username ='', $password ='', $source ='', $timeout =10)
    {
        $this->setAuth($username, $password)->setSource($source)->setTimeout($timeout);
    }

    /**
     * Set Twitter username and password.
     *
     * Provides a fluent interface.
     *
     * @param string $username Twitter account username
     * @param string $password Twitter account password
     *
     * @return Arc90_Service_Twitter
     */
    public function setAuth($username, $password)
    {
        $this->_authUsername = $username;
        $this->_authPassword = $password;

        return $this;
    }

    /**
     * Used when requesting JSON formatted responses to wrap the response in a callback method (e.g. myFunction(...)).
     *
     * Callbacks may only contain alphanumeric characters and underscores; any invalid characters will be stripped.
     *
     * @param string $callback Name of the desired callback function wrapper
     *
     * @return Arc90_Service_Twitter
     */
    public function setCallback($callback)
    {
        $this->_callback = $callback;

        return $this;
    }

    /**
     * Sets an application "source" included with status updates; must be an
     * {@link http://twitter.com/help/request_source approved application}.
     *
     * Provides a fluent interface.
     *
     * @param string $source Source name to be sent with user status updates
     *
     * @return Arc90_Service_Twitter
     */
    public function setSource($source)
    {
        $this->_source = $source;

        return $this;
    }

    /**
     * Sets the length of time (in seconds) to wait for a respnse from Twitter before timing out.
     *
     * Provides a fluent interface.
     *
     * @param integer $timeout Length of time (in seconds) before timeout
     *
     * @return Arc90_Service_Twitter
     */
    public function setTimeout($timeout)
    {
        // Verify that the timeout parameter is a positive integer
        self::_validatePositiveInteger('timeout', $timeout);

        $this->_timeout = $timeout;

        return $this;
    }

    /**
     * Causes all responses to be returned with a 200 OK status code - even errors.
     *
     * This parameter exists to accommodate Flash and JavaScript applications running in browsers that intercept
     * all non-200 responses. If used, it's then the job of the client to determine error states by parsing the
     * response body. Use with caution, as those error messages may change.
     *
     * @param boolean $suppress Suppress HTTP status codes
     *
     * @return Arc90_Service_Twitter
     */
    public function suppressResponseCodes($suppress =TRUE)
    {
        $this->_suppressResponseCodes = (boolean) $suppress;

        return $this;
    }

    /**
     * Returns the 20 most recent statuses from non-protected users who have set a custom user icon.
     *
     * Does not require authentication.
     *
     * Note that the public timeline is cached for 60 seconds so requesting it more often is a waste of resources.
     * 
     * Formats: xml, json, rss, atom
     *
     * API limit: Not applicable
     *
     * @param string $format Desired response format (defaults to JSON)
     * 
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function getPublicTimeline($format ='json')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml', 'rss', 'atom'));

        $uri = self::PATH_STATUS_PUBLIC . ".{$format}";

        return $this->_sendRequest($uri, array(), FALSE);
    }

    /**
     * Returns the 20 most recent statuses posted by the authenticating user and that user's friends.
     *
     * This is the equivalent of /home on the Web. 
     *
     * Optional Query Parameters:
     *
     * <ul>
     *  <li>
     *    <b>since</b>:
     *    Narrows results to statuses created after the specified date, up to 24 hours old.
     *  </li>
     *  <li>
     *    <b>since_id</b>:
     *    Returns only statuses with an ID greater than (that is, more recent than) the specified ID.
     *  </li>
     *  <li>
     *    <b>count</b>:
     *    Specifies the number of statuses to retrieve. May not be greater than 200.
     *  </li>
     *  <li>
     *    <b>page</b>:
     *    Returns a specified page of results (default 20 results per page).
     *  </li>
     * </ul>
     * 
     * Formats: xml, json, rss, atom
     *
     * API Limit: 1 per request 
     *
     * @param string $format Desired response format (defaults to JSON)
     * @param array  $params See method documentation for valid query parameters
     * 
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function getFriendsTimeline($format ='json', array $params =array())
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml', 'rss', 'atom'));

        $query = $this->_buildQueryString($params, array('count', 'page', 'since', 'since_id', 'max_id'));
        $uri   = self::PATH_STATUS_FRIENDS . ".{$format}" . $query;

        return $this->_sendRequest($uri);
    }

    /**
     * Returns the 20 most recent statuses posted from the authenticating user.
     *
     * It's also possible to request another user's timeline via the id parameter below.
     * This is the equivalent of the Web /archive page for your own user, or the profile page for a third party.
     *
     * Optional Query Parameters:
     *
     * <ul>
     *  <li>
     *    <b>id</b>:
     *    Specifies the ID or screen name of the user for whom to return the friends_timeline
     *  </li>
     *  <li>
     *    <b>count</b>:
     *    Specifies the number of statuses to retrieve. May not be greater than 200.
     *  </li>
     *  <li>
     *    <b>since</b>:
     *    Narrows results to statuses created after the specified date, up to 24 hours old.
     *  </li>
     *  <li>
     *    <b>since_id</b>:
     *    Returns only statuses with an ID greater (more recent) than the specified ID.
     *  </li>
     *  <li>
     *    <b>page</b>:
     *    Returns a specified page of results (default 20 results per page).
     *  </li>
     * </ul>
     * 
     * Formats: xml, json, rss, atom
     *
     * API Limit: 1 per request 
     *
     * @param string $format Desired response format (defaults to JSON)
     * @param array  $params See method documentation for valid query parameters
     * 
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function getUserTimeline($format ='json', array $params =array())
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml', 'rss', 'atom'));

        // Extract id parameter (if set); used for resource URI, NOT as a query parameter
        $id = isset($params['id']) ? $params['id'] : '';
        unset($params['id']);

        $query = $this->_buildQueryString($params, array('count', 'page', 'since', 'since_id'));
        $uri   = (('' == $id) ? self::PATH_STATUS_USER : self::PATH_STATUS_USER . "/{$id}") . ".{$format}" . $query;

        return $this->_sendRequest($uri);
    }

    /**
     * Returns a single status, specified by the id parameter below.
     *
     * The status's author will be returned inline.
     *
     * Formats: xml, json
     *
     * API Limit: 1 per request 
     *
     * @param string  $format Desired response format (defaults to JSON)
     * @param integer $id     The numerical ID of the status you're trying to retrieve
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function showStatus($id, $format ='json')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));
        self::_validatePositiveInteger('id', $id);

        $uri = self::PATH_STATUS_SHOW . "/{$id}.{$format}";

        return $this->_sendRequest($uri);
    }

    /**
     * Updates the authenticating user's status. Requires the status parameter specified below.
     *
     * Returns the posted status in requested format when successful.
     *
     * Formats: xml, json
     *
     * API Limit: Not applicable
     *
     * @param string  $status                Text of your status update; must not exceed 140 characters
     * @param integer $in_reply_to_status_id ID of the status being replied to (if this is a reply)
     * @param string  $format                Desired response format (defaults to JSON)
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function updateStatus($status, $in_reply_to_status_id =0, $format ='json')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));

        if(($max = self::STATUS_MAXLENGTH) < strlen(stripslashes($status)))
        {
            self::_throwException("Status updates may not exceed {$max} characters!");
        }

        // Using an associative array with CURLOPT_POSTFIELDS sends 'multipart/form-data'
        // This causes problems with "@replies" since they are interpreted as file uploads
		$params1 = array('status' => $status, 'in_reply_to_status_id' => $in_reply_to_status_id);
        $data   = http_build_query($params1);
		$params = array();

        // If an application source is defined, add to query parameters
        if(strlen($source = $this->_source))
        {
            $params['source'] = $source;
        }

        $query = $this->_buildQueryString($params);
        $uri   = self::PATH_STATUS_UPDATE . ".{$format}" . $query;

        return $this->_sendRequest($uri, $params1, TRUE, 'POST', $data);
    }

/**
     * Returns the 20 most recent @replies (status updates prefixed with @username) for the authenticating user.
     *
     * Optional Query Parameters:
     *
     * <ul>
     *  <li>
     *    <b>page</b>:
     *    Returns a specified page of results (20 results per page).
     *  </li>
     *  <li>
     *    <b>since</b>:
     *    Narrows results to statuses created after the specified date, up to 24 hours old.
     *  </li>
     *  <li>
     *    <b>since_id</b>:
     *    Returns only statuses with an ID greater (more recent) than the specified ID.
     *  </li>
     * </ul>
     *
     * Formats: xml, json, rss, atom
     *
     * API Limit: 1 per request
     *
     * @param string $format Desired response format (defaults to JSON)
     * @param array  $params See method documentation for valid query parameters
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function getMentions($format ='json', array $params =array())
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml', 'rss', 'atom'));

        $query = $this->_buildQueryString($params, array('page', 'since_id','count','max_id'));
        $uri   = self::PATH_STATUS_MENTIONS . ".{$format}" . $query;

        return $this->_sendRequest($uri);
    }

    /**
     * Destroys the status specified by the required ID parameter.
     *
     * The authenticating user must be the author of the specified status.
     *
     * Formats: xml, json
     *
     * API Limit: Not applicable
     *
     * @param integer $id     The ID of the status to destroy
     * @param string  $format Desired response format (defaults to JSON)
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function destroyStatus($id, $format ='json')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));
        self::_validatePositiveInteger('id', $id);

        $uri = self::PATH_STATUS_DESTROY . "/{$id}.{$format}";

        return $this->_sendRequest($uri, array(), TRUE, 'DELETE');
    }

    /**
     * Returns up to 100 of the authenticating user's friends who have most recently updated,
     * each with current status inline.
     *
     * It's also possible to request another user's recent friends list via the id parameter below. 
     *
     * Optional Query Parameters:
     *
     * <ul>
     *  <li>
     *    <b>id</b>:
     *    The ID or screen name of the user for whom to request a list of friends.
     *  </li>
     *  <li>
     *    <b>page</b>:
     *    Retrieves the next 100 friends.
     *  </li>
     *  <li>
     *    <b>lite</b>:
     *    Prevents the inline inclusion of current status. Must be set to a value of true.
     *  </li>
     *  <li>
     *    <b>since</b>:
     *    Narrows results to statuses created after the specified date, up to 24 hours old.
     *  </li>
     * </ul>
     * 
     * Formats: xml, json
     *
     * API Limit: 1 per request 
     *
     * @param string $format Desired response format (defaults to JSON)
     * @param array  $params See method documentation for valid query parameters
     * 
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function getFriends($format ='json', array $params =array())
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));

        // Extract id parameter (if set); used for resource URI, NOT as a query parameter
        $id = isset($params['id']) ? $params['id'] : '';
        unset($params['id']);

        $query = $this->_buildQueryString($params, array('cursor', 'lite', 'since'));
        $uri   = (('' == $id) ? self::PATH_USER_FRIENDS : self::PATH_USER_FRIENDS . "/{$id}") . ".{$format}" . $query;

        return $this->_sendRequest($uri);
    }

    /**
     * Returns the authenticating user's followers, each with current status inline.
     *
     * They are ordered by the order in which they joined Twitter (this is going to be changed).
     *
     * Optional Query Parameters:
     *
     * <ul>
     *  <li>
     *    <b>id</b>:
     *    The ID or screen name of the user for whom to request a list of followers.
     *  </li>
     *  <li>
     *    <b>page</b>:
     *    Retrieves the next 100 followers.
     *  </li>
     *  <li>
     *    <b>lite</b>:
     *    Prevents the inline inclusion of current status. Must be set to a value of true.
     *  </li>
     * </ul>
     * 
     * Formats: xml, json
     *
     * API Limit: 1 per request 
     *
     * @param string $format Desired response format (defaults to JSON)
     * @param array  $params See method documentation for valid query parameters
     * 
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function getFollowers($format ='json', array $params =array())
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));

        // Extract id parameter (if set); used for resource URI, NOT as a query parameter
        $id = isset($params['id']) ? $params['id'] : '';
        unset($params['id']);

        $query = $this->_buildQueryString($params, array('page', 'lite'));
        $uri   = (('' == $id) ? self::PATH_USER_FOLLOWERS : self::PATH_USER_FOLLOWERS . "/{$id}") . ".{$format}" . $query;

        return $this->_sendRequest($uri);
    }

    /**
     * Returns extended information for a given user, specified by ID/screen name or email.
     *
     * This information includes design settings, so third party developers can theme their widgets
     * according to a given user's preferences.
     * 
     * You must be properly authenticated to request the page of a protected user.
     *
     * NOTE: Twitter's documentation indicates that the id parameter is 'required', which is not accurate.
     * In fact, either the id parameter OR the email parameter is required, but not both.
     *
     * Examples: showUser(1, '', 'xml') OR showUser('', 'user@domain.com')
     *
     * If both id and email are supplied, the id parameter will take precedence.
     *
     * Formats: xml, json
     *
     * API Limit: 1 per request 
     *
     * @param mixed  $id     The ID or screen name of a user
     * @param string $email  The email address of a user
     * @param string $format Desired response format (defaults to JSON)
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function showUser($id ='', $email ='', $format ='json')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));

        if(strlen($id =trim($id)))
        {
            $uri = self::PATH_USER_SHOW . "/{$id}.{$format}";
        }
        else if(strlen($email =trim($email)))
        {
            $uri = self::PATH_USER_SHOW . ".{$format}?email={$email}";
        }
        else
        {
            self::_throwException('A user id, screen name, or email is required');
        }

        return $this->_sendRequest($uri);
    }

   /**
     * Returns extended information for the given users, specified by list of ID/screen name
     *
     * Examples: showUser(1, '', 'xml') OR showUser('', 'user@domain.com')
     *
     * If both id and email are supplied, the id parameter will take precedence.
     *
     * Formats: xml, json
     *
     * API Limit: 1 per request 
     *
     * @param mixed  $ids    Array of ids of users
     * @param mixed  $screen_names    Array of screen name of users
     * @param string $format Desired response format (defaults to JSON)
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function usersLookup($ids = array(), $screen_names = array(), $format ='json')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));

        $uri = self::PATH_USER_LOOKUP.".{$format}";
        $params1 = array();

        if(!empty($ids))
        {            
	    $params1 = array('user_id' => implode(',', $ids));
        }
	else if(!empty($screen_names))
	{
	    $params1 = array('screen_name' => implode(',', $screen_names));
	}
        else
        {
            self::_throwException('Atleast one user id or screen name is required');
        }

	$data   = http_build_query($params1);
	return $this->_sendRequest($uri, $params1, TRUE, 'POST', $data);
    }
    /**
     * Returns a list of the 20 most recent direct messages sent to the authenticating user.
     *
     * The XML and JSON versions include detailed information about the sending and recipient users.
     *
     * Optional Query Parameters:
     *
     * <ul>
     *  <li>
     *    <b>page</b>:
     *    Retrieves the 20 next most recent direct messages.
     *  </li>
     *  <li>
     *    <b>since</b>:
     *    Narrows resulting list of direct messages to those sent after the specified date, up to 24 hours old.
     *  </li>
     *  <li>
     *    <b>since_id</b>:
     *    Returns only direct messages with an ID greater (that is, more recent) than the specified ID.
     *  </li>
     * </ul>
     * 
     * Formats: xml, json, rss, atom
     *
     * API Limit: 1 per request 
     *
     * @param string $format Desired response format (defaults to JSON)
     * @param array  $params See method documentation for valid query parameters
     * 
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function getMessages($format ='json', array $params =array())
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml', 'rss', 'atom'));

        $query = $this->_buildQueryString($params, array('page', 'count', 'since', 'since_id'));
        $uri   = self::PATH_DM_MESSAGES . ".{$format}" . $query;

        return $this->_sendRequest($uri);
    }

    /**
     * Returns a list of the 20 most recent direct messages sent by the authenticating user.
     *
     * The XML and JSON versions include detailed information about the sending and recipient users.
     *
     * Optional Query Parameters:
     *
     * <ul>
     *  <li>
     *    <b>page</b>:
     *    Retrieves the 20 next most recent direct messages sent.
     *  </li>
     *  <li>
     *    <b>since</b>:
     *    Narrows resulting list of direct messages to those sent after the specified date, up to 24 hours old.
     *  </li>
     *  <li>
     *    <b>since_id</b>:
     *    Returns only sent direct messages with an ID greater (that is, more recent) than the specified ID.
     *  </li>
     * </ul>
     * 
     * Formats: xml, json
     *
     * API Limit: 1 per request 
     *
     * @param string $format Desired response format (defaults to JSON)
     * @param array  $params See method documentation for valid query parameters
     * 
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function getSentMessages($format ='json', array $params =array())
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));

        $query = $this->_buildQueryString($params, array('page', 'since', 'since_id'));
        $uri   = self::PATH_DM_SENT . ".{$format}" . $query;

        return $this->_sendRequest($uri);
    }

    /**
     * Sends a new direct message to the specified user from the authenticating user.
     *
     * Requires both the user and text parameters below.
     *
     * Returns the sent message in the requested format when successful.
     *
     * Formats: xml, json
     *
     * API Limit: Not applicable
     *
     * @param string $format Desired response format (defaults to JSON)
     * @param mixed  $user   The ID or screen name of the recipient user.
     * @param string $text   The text of your direct message. Must not exceed 140 characters
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function sendMessage($user, $text, $format ='json')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));

        $uri  = self::PATH_DM_NEW . ".{$format}";
		$params1 = array('user' => $user, 'text' => $text);
        $data   = http_build_query($params1);

        return $this->_sendRequest($uri, $params1, TRUE, 'POST', $data);
    }

    /**
     * Destroys the direct message specified in the required ID parameter.
     *
     * The authenticating user must be the recipient of the specified direct message.
     *
     * Formats: xml, json
     *
     * API Limit: Not applicable
     *
     * @param integer $id    The ID of the direct message to destroy
     * @param string $format Desired response format (defaults to JSON)
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function destroyMessage($id, $format ='json')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));
        self::_validatePositiveInteger('id', $id);

        $uri = self::PATH_DM_DESTROY . "/{$id}.{$format}";

        return $this->_sendRequest($uri, array(), TRUE, 'DELETE');
    }

    /**
     * Befriends the user specified in the ID parameter as the authenticating user.
     *
     * Returns the befriended user in the requested format when successful.
     *
     * Returns a string describing the failure condition when unsuccessful.
     *
     * Formats: xml, json
     *
     * API Limit: Not applicable
     *
     * @param mixed  $id     The ID or screen name of the user to befriend
     * @param string $format Desired response format (defaults to JSON)
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function createFriendship($id, $format ='json')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));

        $uri = sprintf('%s/%s.%s', self::PATH_FRIEND_CREATE, urlencode($id), $format);

        return $this->_sendRequest($uri, array(), TRUE, 'POST');
    }

    /**
     * Discontinues friendship with the user specified in the ID parameter as the authenticating user.
     *
     * Returns the un-friended user in the requested format when successful.
     *
     * Returns a string describing the failure condition when unsuccessful.
     *
     * Formats: xml, json
     *
     * API Limit: Not applicable
     *
     * @param mixed  $id     The ID or screen name of the user with whom to discontinue friendship
     * @param string $format Desired response format (defaults to JSON)
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function destroyFriendship($id, $format ='json')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));

        $uri = sprintf('%s/%s.%s', self::PATH_FRIEND_DESTROY, urlencode($id), $format);

        return $this->_sendRequest($uri, array(), TRUE, 'DELETE');
    }

    /**
     * Tests if a friendship exists between two users.
     *
     * Formats: xml, json, none
     *
     * API Limit: 1 per request
     *
     * @param mixed  $user_a The ID or screen name of the first user to test friendship for
     * @param mixed  $user_b The ID or screen name of the second user to test friendship for
     * @param string $format Desired response format (defaults to JSON)
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function friendshipExists($user_a, $user_b, $format ='json')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml', 'none'));

        $uri = self::PATH_FRIEND_EXISTS;

        if('none' != $format)
        {
            $uri .= ".{$format}";
        }

        $uri .= sprintf('?user_a=%s&user_b=%s', urlencode($user_a), urlencode($user_b));

        return $this->_sendRequest($uri);
    }

    /**
     * Returns an array of numeric IDs for every user the specified user is following.
     *
     * @param string $format Desired response format (defaults to JSON)
     * @param string $user   ID or screen_name of the user to retrieve the friends ID list for
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function graphFriends($format ='json', $user ='', $params = array())
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));

        $query = $this->_buildQueryString($params, array('cursor'));
        $uri   = self::PATH_SG_FRIENDS . ".{$format}" . $query;

        if('' != $user)
        {
            $uri = sprintf('%s/%s', $uri, urlencode($user));
        }

        $uri .= ".{$format}";

        return $this->_sendRequest($uri);
    }

    /**
     * Returns an array of numeric IDs for every user the specified user is followed by.
     *
     * @param string $format Desired response format (defaults to JSON)
     * @param string $user   ID or screen_name of the user to retrieve the followers ID list for
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function graphFollowers($format ='json', $user ='')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));

        $uri = self::PATH_SG_FOLLOWERS;

        if('' != $user)
        {
            $uri = sprintf('%s/%s', $uri, urlencode($user));
        }

        $uri .= ".{$format}";

        return $this->_sendRequest($uri);
    }

    /**
     * Returns a {@link Arc90_Service_Twitter_Search Twitter Search API client}.
     *
     * @param string $format   Desired response format; defaults to JSON
     * @param string $callback Callback function used to wrap JSON responses as JSONP
     *
     * @return Arc90_Service_Twitter_Search
     * @throws Arc90_Service_Twitter_Search_Exception
     */
    public function searchApi($format ='json', $callback ='')
    {
        /** @see Arc90_Service_Twitter_Search */
        require_once 'Arc90/Service/Twitter/Search.php';

        return new Arc90_Service_Twitter_Search($format, $callback);
    }

    /**
     * Returns an HTTP 200 OK response code and a format-specific response if authentication was successful.
     *
     * Use this method to test if supplied user credentials are valid with minimal overhead.
     *
     * Formats: xml, json, none
     *
     * API Limit: 1 per request
     *
     * @param string $format Desired response format (defaults to JSON)
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function verifyCredentials($format ='json')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml', 'none'));

        $uri = self::PATH_ACCT_VERIFY;

        if('none' != $format)
        {
            $uri .= ".{$format}";
        }

        return $this->_sendRequest($uri);
    }

    /**
     * Ends the session of the authenticating user, returning a null cookie.
     *
     * Use this method to sign users out of client-facing applications like widgets.
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function endSession()
    {
        return $this->_sendRequest(self::PATH_ACCT_END_SESS, array(), TRUE, 'POST');
    }

    /**
     * Updates the location attribute of the authenticating user, as displayed on the side of their profile
     * and returned in various API methods.
     *
     * Please note this is not normalized, geocoded, or translated to latitude/longitude at this time.
     *
     * Formats: xml, json
     *
     * API Limit: Not applicable
     *
     * @param string $location The location of the user
     * @param string $format   Desired response format (defaults to JSON)
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function updateLocation($location, $format ='json')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));

        $uri  = self::PATH_ACCT_LOCATION . ".{$format}";
		$params = array('location' => $location);
        $data = http_build_query($params);

        return $this->_sendRequest($uri, $params, TRUE, 'POST', $data);
    }

    /**
     * Sets which device Twitter delivers updates to for the authenticating user.
     *
     * Sending 'none' as the device parameter will disable IM or SMS updates.
     *
     * Formats: xml, json
     *
     * API Limit: Not applicable
     *
     * @param string $device Must be one of: sms, im, none
     * @param string $format Desired response format (defaults to JSON)
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function updateDeliveryDevice($device, $format ='json')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));
        self::_validateOption($device =strtolower($device), array('sms', 'im', 'none'));

        $uri  = self::PATH_ACCT_DEVICE . ".{$format}";

		$params = array('device' => $device);
        $data = http_build_query($params);

        return $this->_sendRequest($uri, $params, TRUE, 'POST', $data);
    }

    /**
     * Sets one or more hex values that control the color scheme of the authenticating user's
     * profile page on twitter.com. These values are also returned in the /users/show API method.
     *
     * At least one of the following parameters are required:
     *
     * <ul>
     *   <li>profile_background_color</li>
     *   <li>profile_text_color</li>
     *   <li>profile_link_color</li>
     *   <li>profile_sidebar_fill_color</li>
     *   <li>profile_sidebar_border_color</li>
     * </ul>
     *
     * Formats: xml, json
     *
     * API Limit: Not applicable
     *
     * @param array  $params One or more color parameters to be updated
     * @param string $format Desired response format (defaults to JSON)
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function updateProfileColors(array $params, $format ='json')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));

        if(!count($params))
        {
            self::_throwException('One or more parameters must be present to update user profile colors');
        }

        $valid = array(
            'profile_background_color',
            'profile_text_color',
            'profile_link_color',
            'profile_sidebar_fill_color',
            'profile_sidebar_border_color'
        );

        $uri  = self::PATH_ACCT_COLORS . ".{$format}";
        $data = substr($this->_buildQueryString($params, $valid), 1);

        return $this->_sendRequest($uri, $params, TRUE, 'POST', $data);
    }

    /**
     * Updates the authenticating user's profile image.
     *
     * Image must be a valid GIF, JPG, or PNG image of less than 700 kilobytes in size.
     * Images with width larger than 500 pixels will be scaled down. 
     *
     * @param string $image  Local file path of the image to be uploaded
     * @param string $format Desired response format (defaults to JSON)
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function updateProfileImage($image, $format ='json')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));

        if(!is_readable($image))
        {
            self::_throwException(sprintf('Image file "%s" is not readable', $image));
        }

        $uri  = self::PATH_ACCT_IMAGE . ".{$format}";
        
		$params = array('image' => file_get_contents($image));
		$data = $params;

        return $this->_sendRequest($uri, $params, TRUE, 'POST', $data);
    }

    /**
     * Updates the authenticating user's profile background image.
     *
     * Image must be a valid GIF, JPG, or PNG image of less than 800 kilobytes in size.
     * Images with width larger than 2048 pixels will be scaled down. 
     *
     * @param string $image  Local file path of the image to be uploaded
     * @param string $format Desired response format (defaults to JSON)
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function updateProfileBackgroundImage($image, $format ='json')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));

        if(!is_readable($image))
        {
            self::_throwException(sprintf('Image file "%s" is not readable', $image));
        }

        $uri  = self::PATH_ACCT_BGIMAGE . ".{$format}";
        $params = array('image' => file_get_contents($image));
		$data = $params;

        return $this->_sendRequest($uri, $params, TRUE, 'POST', $data);
    }

    /**
     * Returns the remaining number of API requests available to the authenticating user before the API limit
     * is reached for the current hour.
     *
     * Calls to rate_limit_status require authentication, but will not count against the rate limit.  
     *
     * Formats: xml, json
     *
     * API Limit: Not applicable
     *
     * @param string $format Desired response format (defaults to JSON)
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function getRateLimitStatus($format ='json')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));

        $uri = self::PATH_ACCT_LIMIT . ".{$format}";

        return $this->_sendRequest($uri);
    }

    /**
     * Sets values that users are able to set under the "Account" tab of their settings page.
     *
     * Only the parameters specified will be updated; to only update the "name" attribute, for example,
     * only include that parameter in your request.
     *
     * At least one of the following parameters are required:
     *
     * <ul>
     *   <li>name</li>
     *   <li>email</li>
     *   <li>url</li>
     *   <li>location</li>
     *   <li>description</li>
     * </ul>
     *
     * Formats: xml, json
     *
     * API Limit: Not applicable
     *
     * @param array  $params One or more parameters to be updated
     * @param string $format Desired response format (defaults to JSON)
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function updateProfile(array $params, $format ='json')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));

        if(!count($params))
        {
            self::_throwException('One or more parameters must be present to update user profile');
        }

        $valid = array('name', 'email', 'url', 'location', 'description');
        $uri   = self::PATH_ACCT_PROFILE . ".{$format}";
        $data  = substr($this->_buildQueryString($params, $valid), 1);

        return $this->_sendRequest($uri, $params, TRUE, 'POST', $data);
    }

    /**
     * Returns the 20 most recent favorite statuses for the authenticating user or user specified by the ID parameter.
     *
     * Optional Query Parameters:
     *
     * <ul>
     *  <li>
     *    <b>id</b>:
     *    The ID or screen name of the user for whom to request a list of favorite statuses.
     *  </li>
     *  <li>
     *    <b>page</b>:
     *    Returns a specified page of results (20 results per page).
     *  </li>
     * </ul>
     * 
     * Formats: xml, json, rss, atom
     *
     * API Limit: 1 per request 
     *
     * @param string $format Desired response format (defaults to JSON)
     * @param array  $params See method documentation for valid query parameters
     * 
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function getFavorites($format ='json', array $params =array())
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml', 'rss', 'atom'));

        // Extract id parameter (if set); used for resource URI, NOT as a query parameter
        $id = isset($params['id']) ? $params['id'] : '';
        unset($params['id']);

        $query = $this->_buildQueryString($params, array('page'));
        $uri   = (('' == $id) ? self::PATH_FAV_FAVORITES : self::PATH_FAV_FAVORITES . "/{$id}") . ".{$format}" . $query;

        return $this->_sendRequest($uri);
    }

    /**
     * Favorites the status specified in the ID parameter as the authenticating user.
     *
     * Returns the favorite status when successful.
     *
     * Formats: xml, json
     *
     * API Limit: Not applicable
     *
     * @param integer $id     The ID of the status to favorite
     * @param string  $format Desired response format (defaults to JSON)
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function createFavorite($id, $format ='json')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));
        self::_validatePositiveInteger('id', $id);

        $uri = self::PATH_FAV_CREATE . "/{$id}.{$format}";

        return $this->_sendRequest($uri, array(), TRUE, 'POST');
    }

    /**
     * Un-favorites the status specified in the ID parameter as the authenticating user.
     *
     * Returns the un-favorited status in the requested format when successful.
     *
     * Formats: xml, json
     *
     * API Limit: Not applicable
     *
     * @param integer $id     The ID of the status to un-favorite
     * @param string  $format Desired response format (defaults to JSON)
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function destroyFavorite($id, $format ='json')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));
        self::_validatePositiveInteger('id', $id);

        $uri = self::PATH_FAV_DESTROY . "/{$id}.{$format}";

        return $this->_sendRequest($uri, array(), TRUE, 'DELETE');
    }

    /**
     * Enables notifications for updates from the specified user to the authenticating user.
     *
     * Returns the specified user when successful.
     *
     * NOTE: The Notification Methods require the authenticated user to already be friends with the specified user.
     * Otherwise the error "there was a problem following the specified user" will be returned.
     *
     * Formats: xml, json
     *
     * API Limit: Not applicable
     *
     * @param integer $id     The ID or screen name of the user to follow
     * @param string  $format Desired response format (defaults to JSON)
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function follow($id, $format ='json')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));

        $uri = self::PATH_NOTIF_FOLLOW . "/{$id}.{$format}";

        return $this->_sendRequest($uri, array(), TRUE, 'POST');
    }

    /**
     * Disables notifications for updates from the specified user to the authenticating user.
     *
     * Returns the specified user when successful.
     *
     * NOTE: The Notification Methods require the authenticated user to already be friends with the specified user.
     * Otherwise the error "there was a problem following the specified user" will be returned.
     *
     * Formats: xml, json
     *
     * API Limit: Not applicable
     *
     * @param integer $user   The ID or screen name of the user to leave
     * @param string  $format Desired response format (defaults to JSON)
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function leave($user, $format ='json')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));

        $uri = self::PATH_NOTIF_LEAVE . "/{$id}.{$format}";

        return $this->_sendRequest($uri, array(), TRUE, 'POST');
    }

    /**
     * Blocks the user specified in the ID parameter as the authenticating user.
     *
     * Returns the blocked user in the requested format when successful.
     *
     * Formats: xml, json
     *
     * API Limit: Not applicable
     *
     * @link http://help.twitter.com/index.php?pg=kb.page&id=69 
     *
     * @param mixed  $id     The ID or screen name of the user to block
     * @param string $format Desired response format (defaults to JSON)
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function block($id, $format ='json')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));

        $uri = self::PATH_BLOCK_CREATE . "/{$id}.{$format}";

        return $this->_sendRequest($uri, array(), TRUE, 'POST');
    }

    /**
     * Un-blocks the user specified in the ID parameter as the authenticating user.
     *
     * Returns the un-blocked user in the requested format when successful.
     *
     * Formats: xml, json
     *
     * API Limit: Not applicable
     *
     * @link http://help.twitter.com/index.php?pg=kb.page&id=69 
     *
     * @param integer $id     The ID or screen name of the user to unblock
     * @param string  $format Desired response format (defaults to JSON)
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function unblock($user, $format ='json')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));

        $uri = self::PATH_BLOCK_DESTROY . "/{$id}.{$format}";

        return $this->_sendRequest($uri, array(), TRUE, 'DELETE');
    }

    /**
     * Returns the string "ok" in the requested format with a 200 OK HTTP status code.
     *
     * Formats: xml, json
     *
     * API Limit: Not applicable
     *
     * @param string $format Desired response format (defaults to JSON)
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    public function test($format ='json')
    {
        self::_validateOption($format =strtolower($format), array('json', 'xml'));

        $uri = self::PATH_HELP_TEST . ".{$format}";

        return $this->_sendRequest($uri);
    }

    /**
     * Sends an HTTP request to the Twitter API with optional Basic authentication.
     *
     * @param string  $uri    Target URI for this request (relative to the API root)
     * @param boolean $auth   Specifies whether to use HTTP Basic authentication
     * @param string  $method Specifies the HTTP method to be used for this request
     * @param mixed   $data   x-www-form-urlencoded data (or array) to be sent in a POST request body
     *
     * @return Arc90_Service_Twitter_Response
     * @throws Arc90_Service_Twitter_Exception
     */
    protected function _sendRequest($uri, $params = array(), $auth =TRUE, $method ='GET', $data ='')
    {
	if ($this->use_oauth)
	{
	    require_once('oauth/twitterOAuth.php');

	    /* Create TwitterOAuth with app key/secret and user access key/secret */
	    $to = new TwitterOAuth($this->oauth_consumer_key, $this->oauth_consumer_secret, $this->user_token, $this->user_secret);

	    if ($method=='GET')
	    {
		//break the query string to array elements
		$params = $this->parse_query($uri, PHP_URL_QUERY);
		$uri = parse_url($uri, PHP_URL_PATH);
		//trace(print_r($uri, true));
		//trace(print_r($params, true));
	    }
	    $content = $to->OAuthRequest( self::API_URI_S.$uri, $params, $method);

	    return new Arc90_Service_Twitter_Response($content, $to->meta);
	}
	else
	{
	    $ch = curl_init();

	    curl_setopt($ch, CURLOPT_URL, self::API_URI . $uri);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

	    if($auth)
	    {
		    curl_setopt($ch, CURLOPT_USERPWD, "{$this->_authUsername}:{$this->_authPassword}");
	    }

	    if('POST' == ($method = strtoupper($method)))
	    {
		    curl_setopt($ch, CURLOPT_POST, TRUE);
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	    }
	    else if('GET' != $method)
	    {
		    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
	    }

	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, TRUE);
	    curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);

	    $data = curl_exec($ch);
	    $meta = curl_getinfo($ch);

	    curl_close($ch);
	}

        return new Arc90_Service_Twitter_Response($data, $meta);
    }

    // Originally written by xellisx
function parse_query($var)
 {
  /**
   *  Use this function to parse out the query array element from
   *  the output of parse_url().
   */
  $var  = parse_url($var, PHP_URL_QUERY);
  $var  = html_entity_decode($var);
  $var  = explode('&', $var);
  $arr  = array();

  foreach($var as $val)
   {
    if($val) {
        $x          = explode('=', $val);
        $arr[$x[0]] = $x[1];
    }
   }
  unset($val, $x, $var);
  return $arr;
 }

    /**
     * Builds a query string from an array of parameters and values.
     *
     * @param array $args    Parameter/value pairs to be evaluated for this query string
     * @param array $allowed Optional array of allowed parameter keys
     *
     * @return string
     * @throws Arc90_Service_Twitter_Exception
     */
    protected function _buildQueryString(array $args, array $allowed =array())
    {
        // If disallowed parameters are present, throw an exception with an error message
        if(count($allowed) && count($disallowed = array_diff(array_keys($args), $allowed)))
        {
            self::_throwException('Invalid parameter(s): ' . join(', ', $disallowed));
        }

        $params = array();

        foreach($args as $key => $value)
        {
            if(NULL != $value)
            {
                if('lite' == $key)
                {
                    $value = 'true';
                }
                else if('since' == $key)
                {
                    $value = date(DATE_RFC822, strtotime($value));
                }

                $params[] = sprintf('%s=%s', $key, urlencode($value));
            }
        }

        if('' != $this->_callback)
        {
            $params[] = "callback={$this->_callback}";
        }

        if($this->_suppressResponseCodes)
        {
            $params[] = 'suppress_response_codes=1';
        }

        return count($params) ? '?' . implode('&', $params) : '';
    }

    /**
     * Validates an option against a set of allowed options.
     *
     * @param mixed $option  Option to validate
     * @param array $options Array of allowed option values
     *
     * @throws Arc90_Service_Twitter_Exception
     */
    protected static function _validateOption($option, array $options)
    {
        if(!in_array($option, $options))
        {
            self::_throwException("Invalid option '{$option}'. Valid options include: " . implode(', ', $options));
        }
    }

    /**
     * Verifies that a given value is either a positive integer or a string representing a positive integer.
     *
     * @param string $name  Name of the field being verified
     * @param mixed  $value Value of the field being verified
     *
     * @throws Arc90_Service_Twitter_Exception
     */
    protected static function _validatePositiveInteger($name, $value)
    {
        if(0 >= $value || !(is_integer($value) || (is_numeric($value) && floatval($value) == intval(floatval($value)))))
        {
            self::_throwException("Field '{$name}' must be a positive integer value, '{$value}' given");
        }
    }

    /**
     * Throws an Arc90_Service_Twitter_Exception.
     *
     * @param string $message Message to be provided with the exception
     *
     * @throws Arc90_Service_Twitter_Exception
     */
    protected static function _throwException($message)
    {
        /** @see Arc90_Service_Twitter_Exception */
        require_once 'Arc90/Service/Twitter/Exception.php';

        throw new Arc90_Service_Twitter_Exception($message);
    }
	
	var $use_oauth = false;

	function useOAuth($oauth_consumer_key, $oauth_consumer_secret, $user_token, $user_secret)
	{
		$this->use_oauth = true;
		$this->oauth_consumer_key = $oauth_consumer_key;
		$this->oauth_consumer_secret = $oauth_consumer_secret;
		$this->user_token = $user_token;
		$this->user_secret = $user_secret;
	}
	
	//refer: http://us2.php.net/manual/en/function.parse-str.php#45493
	function parse_str_ext($toparse) {
		$returnarray = array();
		$keyvaluepairs = split("&", $toparse);
		foreach($keyvaluepairs as $pairval) {
			$splitpair = split("=", $pairval);
			$returnarray[$splitpair[0]] = $splitpair[1];
		}
		return $returnarray;    
	}
}
