<?php

use eftec\PdoOne;

require_once dirname(__FILE__) .'/AbstractTestSuite.php';
/**
 * This test suite just demonstrates the baseline performance without any kind of ORM
 * or even any other kind of slightest abstraction.
 */
class PdoOneTestSuite extends AbstractTestSuite
{
    /** @var PdoOne */
    var $pdoOne=null;
    function initialize()
    {
        $this->pdoOne=new PdoOne('mysql','localhost','root', 'abc.123','sakila');
        $this->pdoOne->open();
        $this->pdoOne->logLevel=3;
        $this->con = $this->pdoOne->conn1;
        $this->initTables();
    }

    function clearCache()
    {
    }

    function beginTransaction()
    {
        $this->con->beginTransaction();
    }

    function commit()
    {
        $this->con->commit();
    }

    /**
     * @param $i
     *
     * @throws Exception
     */
    function runAuthorInsertion($i)
    {
        $this->pdoOne->set(['first_name'=>'John'.$i,'last_name'=>'Doe'.$i])
            ->from('author')->insert();
        $this->authors[]= $this->pdoOne->insert_id();
    }

    /**
     * @param $i
     *
     * @throws Exception
     */
    function runBookInsertion($i)
    {
        $this->pdoOne->set(['title'=>'Hello'.$i
            ,'isbn'=>'1234'
            ,'price'=>$i
            ,'author_id'=>$this->authors[array_rand($this->authors)]])
            ->from('book')->insert();

        $this->books[]= $this->pdoOne->insert_id();
    }

    /**
     * @param $i
     *
     * @throws Exception
     */
    function runPKSearch($i)
    {
        $author=$this->pdoOne->select('author.id, author.FIRST_NAME, author.LAST_NAME, author.EMAIL')
                    ->from('author')
                    ->where(['author.id'=>$this->authors[array_rand($this->authors)] ])
                    ->limit("1")
                    ->first();
    }

    /**
     * @param $i
     *
     * @throws Exception
     */
    function runHydrate($i)
    {
        $books=$this->pdoOne->select('book.ID, book.TITLE, book.ISBN, book.PRICE, book.AUTHOR_ID')
            ->from('book')
            ->where('book.PRICE > ?', [$i])
            ->limit("5")
            ->toList();
    }

    /**
     * @param $i
     *
     * @throws Exception
     */
    function runComplexQuery($i)
    {
        $name = 'John Doe';
        $books=$this->pdoOne->select('COUNT(*)')
            ->from('author')
            ->where('(author.ID>? OR (author.FIRST_NAME || author.LAST_NAME) = ?)'
                , ['s',$this->authors[array_rand($this->authors)],'s',$name])
            ->firstScalar();
    }

    /**
     * @param $i
     *
     * @throws Exception
     */
    function runJoinSearch($i)
    {
        $name = 'John Doe';
        $book=$this->pdoOne->select('book.ID, book.TITLE, book.ISBN, book.PRICE, book.AUTHOR_ID, author.ID, author.FIRST_NAME, author.LAST_NAME, author.EMAIL')
            ->from('book')
            ->left('author ON book.AUTHOR_ID = author.ID')
            ->where(['book.TITLE'=>'Hello' . $i])
            ->first();
    }
}