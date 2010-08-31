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
 * @version    $Id: Search.php 39 2009-02-08 21:24:39Z mattwilliamsnyc $
 */

/**
 * @see Arc90_Service_Twitter_Response
 */
require_once 'Arc90/Service/Twitter/Response.php';

/**
 * Arc90_Service_Twitter_Search provides methods for interacting with the
 * {@link http://apiwiki.twitter.com/Search+API+Documentation Twitter Search API}.
 *
 * NOTE: Twitter has indicated that they plan to merge the stadard REST API and the search API
 * into a single, more RESTful API. In the meantime, this client provides an interface to the current
 * {@link http://search.twitter.com/ Search API}.
 *
 * @category   Arc90
 * @package    Arc90_Service
 * @subpackage Twitter_Search
 * @author     Matt Williams <matt@mattwilliamsnyc.com>
 * @copyright  Copyright (c) 2008 {@link http://arc90.com Arc90 Inc.}, Matt Williams
 */
class Arc90_Service_Twitter_Search
{
    /** Entry point for the Twitter Search API */
    const API_URI = 'http://search.twitter.com';

    /** {@link http://apiwiki.twitter.com/Search+API+Documentation#Search} */
    const PATH_SEARCH = '/search';

    /** {@link http://apiwiki.twitter.com/Search+API+Documentation#Trends} */
    const PATH_TRENDS = '/trends';

    /** Callback function used to wrap JSON responses */
    protected $_callback;

    /** Response format */
    protected $_format  = 'json';

    /** Supported response formats */
    protected $_formats = array('json', 'atom');

    /**
     * Creates a new Twitter Search API client.
     *
     * @param string $format   Desired response format; defaults to JSON
     * @param string $callback Callback function used to wrap JSON responses as JSONP
     *
     * @throws Arc90_Service_Twitter_Search_Exception
     */
    public function __construct($format ='json', $callback ='')
    {
        $this->setFormat($format)->setCallback($callback);
    }

    /**
     * Sets a callback function used to wrap JSON responses as JSONP
     *
     * @param string $callback Callback function used to wrap JSON responses as JSONP
     *
     * @return Arc90_Service_Twitter_Search
     */
    public function setCallback($callback)
    {
        $this->_callback = $callback;

        return $this;
    }

    /**
     * Sets the default response format (may be JSON or ATOM);
     *
     * @param string
     *
     * @return Arc90_Service_Twitter_Search
     * @throws Arc90_Service_Twitter_Search_Exception
     */
    public function setFormat($format)
    {
        if(!in_array(($format = strtolower($format)), $this->_formats))
        {
            self::_throwException(sprintf(
                '"%s" is not a valid response format. Valid formats include: %s',
                $format,
                join(', ', $this->_formats)
            ));
        }

        $this->_format = $format;

        return $this;
    }

    /**
     * Returns tweets that match a specified query.
     *
     * You can use a variety of {@link http://search.twitter.com/operators search operators} in your query.
     *
     * Here are a few examples:
     * <ul>
     *   <li>
     *     Find tweets <b>containing a word</b>:
     *     {@link http://search.twitter.com/search.atom?q=twitter}
     *   </li>
     *   <li>
     *     Find tweets <b>from a user</b>:
     *     {@link http://search.twitter.com/search.atom?q=from%3Aalexiskold}
     *   </li>
     *   <li>
     *     Find tweets <b>to a user</b>:
     *     {@link http://search.twitter.com/search.atom?q=to%3Atechcrunch}
     *   </li>
     *   <li>
     *     Find tweets <b>referencing a user</b>:
     *     {@link http://search.twitter.com/search.atom?q=%40mashable}
     *   </li>
     *   <li>
     *     Find tweets <b>containing a hashtag</b>:
     *     {@link http://search.twitter.com/search.atom?q=%23haiku}
     *   </li>
     *   <li>
     *     Combine any of the operators together:
     *     {@link http://search.twitter.com/search.atom?q=movie+%3A%29}
     *   </li>
     * </ul>
     *
     * The search method also supports the following optional URL parameters:
     *
     * <ul>
     *   <li>
     *     <b>lang</b>:
     *     restricts tweets to the given language, given by an ISO 639-1 code.
     *     Ex: {@link http://search.twitter.com/search.atom?lang=en&q=devo}
     *   </li>
     *   <li>
     *     <b>rpp</b>:
     *     the number of tweets to return per page, up to a max of 100.
     *     Ex: {@link http://search.twitter.com/search.atom?lang=en&q=devo&rpp=15}
     *   </li>
     *   <li>
     *     <b>page</b>:
     *     the page number (starting at 1) to return, up to a max of roughly 1500 results (based on rpp * page)
     *   </li>
     *   <li>
     *     <b>since_id</b>: returns tweets with status ids greater than the given id.
     *   </li>
     *   <li>
     *     <b>geocode</b>:
     *     returns tweets by users located within a given radius of the given latitude/longitude,
     *     where the user's location is taken from their Twitter profile.
     *     The parameter value is specified by "latitide,longitude,radius", where radius units must be specified as
     *     either "mi" (miles) or "km" (kilometers).
     *     Ex: {@link http://search.twitter.com/search.atom?geocode=40.757929%2C-73.985506%2C25km}.
     *     Note that you cannot use the near operator via the API to geocode arbitrary locations; however you can use
     *     this geocode parameter to search near geocodes directly.
     *   </li>
     *   <li>
     *     <b>show_user</b>:
     *     when "true", adds "<user>:" to the beginning of the tweet.
     *     This is useful for readers that do not display Atom's author field. The default is "false".
     *   </li>
     * </ul>
     *
     * There are a few restrictions to the search method:
     *
     * <ul>
     *   <li>We only provide access to data for about four months, older data may not be searchable.</li>
     *   <li>
     *     As noted above, the near: operator does not work via the API
     *     and instead must be replaced by the geocode parameter.
     *   </li>
     *   <li>Query length is limited to 140 characters.</li>
     * </ul>
     *
     * @param string $query  Search query
     * @param array  $params Optional query parameters
     *
     * @return Arc90_Service_Twitter_Response
     */
    public function search($query, array $params =array())
    {
        $query = array('q' => $query);

        if('json' == $this->_format && '' != $this->_callback)
        {
            $query['callback'] = $this->_callback;
        }

        foreach($params as $key => $value)
        {
            switch($key)
            {
                case 'lang':
                {
                    if(2 == strlen($value))
                    {
                        $query[$key] = $value;
                    }

                    break;
                }
                case 'geocode':
                {
                    // latitude,longitude,radius
                    if(preg_match('/^[+-]?\d+(\.\d+)?,[+-]?\d+(\.\d+)?,\d+(mi|km)$/', $value))
                    {
                        $query[$key] = $value;
                    }
                    break;
                }
                case 'page':
                case 'since_id':
				case 'max_id':
                {
                    $value = intval($value);

                    if(1 <= $value)
                    {
                        $query[$key] = $value;
                    }
                    break;
                }
                case 'rpp':
                {
                    $value = intval($value);
                    
                    if(100 < $value)
                    {
                        $value = 100;
                    }
                    else if(1 > $value)
                    {
                        $value = 1;
                    }

                    $query[$key] = $value;
                    break;
                }
                case 'show_user':
                {
                    $query[$key] = 'true';
					break;
                }
				//Yemkay: Params used in advanced search
				case 'ands':
				case 'ors':
				case 'nots':
				case 'from':
				case 'q':
				{
					$query[$key] = $value;
				}
            }
        }
        
        $uri = sprintf('%s.%s?%s', self::PATH_SEARCH, $this->_format, http_build_query($query));
		//echo $uri."<br/>";
        return $this->_sendRequest($uri);
    }

    /**
     * Returns the top ten queries that are currently trending on Twitter.
     *
     * The response includes the time of the request, the name of each trending topic,
     * and the url to the Twitter Search results page for that topic.
     * Currently, the only supported format for this method is JSON.
     * The callback parameter is supported, however.
     *
     * @return Arc90_Service_Twitter_Response
     */
    public function trends()
    {
        return $this->_sendRequest(self::PATH_TRENDS . '.json');
    }

    /**
     * Sends a request to the Twitter Search API and returns a response object.
     *
     * @param string $uri Target URI (including query string) for this request
     *
     * @return Arc90_Service_Twitter_Response
     */
    protected static function _sendRequest($uri)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::API_URI . $uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, TRUE);
	curl_setopt($ch, CURLOPT_USERAGENT, SEARCH_API_USER_AGENT);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:', 'Accept-Charset: ISO-8859-1,utf-8'));

        $data = curl_exec($ch);
        $meta = curl_getinfo($ch);

        curl_close($ch);

        return new Arc90_Service_Twitter_Response($data, $meta);
    }

    /**
     * Throws an exception with a user-supplied message.
     *
     * @param string $message Message to be provided with the exception
     *
     * @throws Arc90_Service_Twitter_Search_Exception
     */
    protected static function _throwException($message)
    {
        /** @see Arc90_Service_Twitter_Search_Exception */
        require_once 'Arc90/Service/Twitter/Search/Exception.php';

        throw new Arc90_Service_Twitter_Search_Exception($message);
    }
}
