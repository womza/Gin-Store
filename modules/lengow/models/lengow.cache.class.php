<?php
/**
 * Copyright 2014 Lengow SAS.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 *  @author    Ludovic Drin <ludovic@lengow.com> Romain Le Polh <romain@lengow.com>
 *  @copyright 2014 Lengow SAS
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 */

$sep = DIRECTORY_SEPARATOR;
require_once dirname(__FILE__).$sep.'..'.$sep.'loader.php';
try
{
	loadFile('specificprice');
} catch(Exception $e)
{
	try
	{
		loadFile('core');
		LengowCore::log($e->getMessage(), null, 1);
	} catch (Exception $ex)
	{
		echo date('Y-m-d : H:i:s ').$e->getMessage().'<br />';
	}
}


if (_PS_CACHE_ENABLED_ == false)
{
	class LengowCache {
		public static function clear()
		{
			LengowProduct::clear();
			if (_PS_VERSION_ >= '1.5')
				LengowProduct::flushPriceCache();
			Link::$cache = array('page' => array());
			if (_PS_VERSION_ >= '1.5')
				LengowSpecificPrice::clear();
		}
	}
}
else
{
	switch (_PS_CACHING_SYSTEM_)
	{
		case 'CacheApc':
			class LengowCache extends CacheApc {
				public static function clear()
				{
					if (_PS_VERSION_ >= '1.5')
						self::$local = array();
					if (_PS_CACHE_ENABLED_)
						Cache::getInstance()->delete('*');
					LengowProduct::clear();
					if (_PS_VERSION_ >= '1.5')
						LengowProduct::flushPriceCache();
					Link::$cache = array('page' => array());
					if (_PS_VERSION_ >= '1.5')
						LengowSpecificPrice::clear();
				}
			}
			break;
		case 'CacheMemcache':
			class LengowCache extends CacheMemcache {
				public static function clear()
				{
					if (_PS_VERSION_ >= '1.5')
						self::$local = array();
					if (_PS_CACHE_ENABLED_)
						Cache::getInstance()->delete('*');
					LengowProduct::clear();
					if (_PS_VERSION_ >= '1.5')
						LengowProduct::flushPriceCache();
					Link::$cache = array('page' => array());
					if (_PS_VERSION_ >= '1.5')
						LengowSpecificPrice::clear();
				}
			}
			break;
		case 'CacheXcache':
			class LengowCache extends CacheXcache {
				public static function clear()
				{
					if (_PS_VERSION_ >= '1.5')
						self::$local = array();
					if (_PS_CACHE_ENABLED_)
						Cache::getInstance()->delete('*');
					LengowProduct::clear();
					if (_PS_VERSION_ >= '1.5')
						LengowProduct::flushPriceCache();
					Link::$cache = array('page' => array());
					if (_PS_VERSION_ >= '1.5')
						LengowSpecificPrice::clear();
				}
			}
			break;
		case 'CacheFs':
			class LengowCache extends CacheFs {
				public static function clear()
				{
					if (_PS_VERSION_ >= '1.5')
						self::$local = array();
					if (_PS_CACHE_ENABLED_)
						Cache::getInstance()->delete('*');
					LengowProduct::clear();
					if (_PS_VERSION_ >= '1.5')
						LengowProduct::flushPriceCache();
					Link::$cache = array('page' => array());
					if (_PS_VERSION_ >= '1.5')
						LengowSpecificPrice::clear();
				}
			}
			break;
		default:
			class LengowCache {
				public static function clear()
				{
					LengowProduct::clear();
					if (_PS_VERSION_ >= '1.5')
						LengowProduct::flushPriceCache();
					Link::$cache = array('page' => array());
					if (_PS_VERSION_ >= '1.5')
						LengowSpecificPrice::clear();
				}
			}
			break;
	};
}