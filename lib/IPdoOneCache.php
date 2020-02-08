<?php

namespace eftec;

interface IPdoOneCache
{
    /**
     * It returns the value of the cache. If not found then it must returns null.
     * 
     * @param string $uid
     *
     * @return mixed|null
     */
    function getCache($uid);

    /**
     * @param string $uid
     * @param mixed $data
     * @param null|int $ttl
     *
     * @return mixed|null
     */
    function setCache($uid,$data,$ttl=null);    
}