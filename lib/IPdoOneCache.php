<?php

namespace eftec;

interface IPdoOneCache
{
    /**
     * It returns the value of the cache. If not found then it must returns false
     *
     * @param string $uid    The unique id. It is generate by sha256 based in the query, parameters, type of query
     *                       and method.
     * @param string|string[] $family [optional] It is the family or group of the cache. It could be used to invalidate
     *                       the whole group. For example, to invalidate all the cache related with a table.
     *
     * @return mixed|bool If the cache is not found then it must returns false
     */
    function getCache($uid, $family = '');

    /**
     * It stores a cache. This method is used internally by PdoOne.<br>
     * 
     * @param string $uid    The unique id. It is generate by sha256 based in the query, parameters, type of query
     *                       and method.
     * @param string|string[] $family [optional] It is the family or group of the cache. It could be used to invalidate
     *                       the whole group. For example, to invalidate all the cache related with a table.
     * @param mixed|null $data The data to store
     * @param null|bool|int $ttl If null then the cache never expires.<br>
     *                           If false then we don't use cache.<br>
     *                           If int then it is the duration of the cache (in seconds)
     *
     * @return void.
     */
    function setCache($uid, $family = '', $data = null, $ttl = null);

    /**
     * Invalidate a single cache or a list of cache based in a single uid or in a family/group of cache.
     *
     * @param string|string[] $uid The unique id. It is generate by sha256 based in the query, parameters, type of
     *                             query and method.
     * @param string|string[] $family [optional] It is the family or group of the cache. It could be used to invalidate
     *                       the whole group. For example, to invalidate all the cache related with a table.
     *
     * @return mixed
     */
    function invalidateCache($uid='',$family='');
}