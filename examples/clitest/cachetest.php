<?php

use eftec\IPdoOneCache;

class CacheServicesmysql implements IPdoOneCache
{
    public $cacheData = [];
    public $cacheDataFamily = [];
    public $cacheCounter = 0; // for debug

    public function getCache($uid, $family = '')
    {

        if (isset($this->cacheData[$uid])) {
            $this->cacheCounter++;
            var_dump('<b>READING CACHE '.$uid.'</b>');
            return $this->cacheData[$uid];
        }
        var_dump('<b>READING CACHE NOT FOUND '.$uid.'</b>');
        return false;
    }

    /**
     * @param string $uid
     * @param string|array $family
     * @param null   $data
     * @param null   $ttl
     */
    public function setCache($uid, $family = '', $data = null, $ttl = null): void
    {
        if ($family === '') {
            $this->cacheData[$uid] = $data;
        } else {
            if (!is_array($family)) {
                $family = [$family];
            }
            foreach ($family as $fam) {
                if (!isset($this->cacheDataFamily[$fam])) {
                    $this->cacheDataFamily[$fam] = [];
                }
                var_dump('<b>SETTING CACHE '.$uid.'</b>');

                $this->cacheDataFamily[$fam][] = $uid;
                $this->cacheData[$uid] = $data;
                //var_dump($fam);
                //var_dump($this->cacheDataFamily[$fam]);
            }
        }
    }

    /**
     * @param string       $uid
     * @param string|array $family
     *
     * @return void
     */
    public function invalidateCache($uid = '', $family = ''): void
    {
        if ($family === '') {
            if ($uid === '') {
                $this->cacheData = []; // we delete all the cache
            } else {
                $this->cacheData[$uid] = [];
            }
        } else {
            if (!is_array($family)) {
                $family = [$family];
            }
            foreach ($family as $fam) {
                if(isset($this->cacheDataFamily[$fam])) {
                    foreach ($this->cacheDataFamily[$fam] as $id) {
                        unset($this->cacheData[$id]);
                        echo "deleting cache $id\n";
                    }
                }
                $this->cacheDataFamily[$fam] = [];
            }
        }
        //unset($this->cacheData[$uid]);
    }
}
