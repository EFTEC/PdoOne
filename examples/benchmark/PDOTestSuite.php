<?php

require_once dirname(__FILE__) .'/AbstractTestSuite.php';
/**
 * This test suite just demonstrates the baseline performance without any kind of ORM
 * or even any other kind of slightest abstraction.
 */
class PDOTestSuite extends AbstractTestSuite
{
    function initialize()
    {
        $this->con = new PDO('mysql:host=localhost;dbname=sakila', 'root', 'abc.123');
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

    function runAuthorInsertion($i)
    {
        $query = 'INSERT INTO author (first_name, last_name) VALUES (\'John?\',\'Doe?\')';
        $stmt = $this->con->prepare($query);
        $stmt->bindParam(1, $i, PDO::PARAM_INT);
        $stmt->bindParam(2, $i, PDO::PARAM_INT);
        $stmt->execute();
        $this->authors[]= $this->con->lastInsertId();
    }
    function runBookInsertion($i)
    {
        $query = 'INSERT INTO book (title, isbn, price, author_id) VALUES (\'Hello?\',\'1234\', ?, ?)';
        $stmt = $this->con->prepare($query);
        $stmt->bindParam(1, $i, PDO::PARAM_INT);
        $stmt->bindParam(2, $i, PDO::PARAM_INT);
        $stmt->bindParam(2, $this->authors[array_rand($this->authors)], PDO::PARAM_INT);
        $stmt->execute();
        $this->books[]= $this->con->lastInsertId();
    }

    function runPKSearch($i)
    {
        $query = 'SELECT author.ID, author.FIRST_NAME, author.LAST_NAME, author.EMAIL FROM author WHERE author.ID = ? LIMIT 1';
        $stmt = $this->con->prepare($query);
        $stmt->bindParam(1, $this->authors[array_rand($this->authors)], PDO::PARAM_INT);
        $stmt->execute();
        $author = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    function runHydrate($i)
    {
        $query = 'SELECT book.ID, book.TITLE, book.ISBN, book.PRICE, book.AUTHOR_ID FROM book WHERE book.PRICE > ? LIMIT 5';
        $stmt = $this->con->prepare($query);
        $stmt->bindParam(1, $i, PDO::PARAM_INT);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        }
    }
    function runComplexQuery($i)
    {
        $query = 'SELECT COUNT(*) FROM author WHERE (author.ID>? OR (author.FIRST_NAME || author.LAST_NAME) = ?)';
        $stmt = $this->con->prepare($query);
        $stmt->bindParam(1, $this->authors[array_rand($this->authors)], PDO::PARAM_INT);
        $name = 'John Doe';
        $stmt->bindParam(2, $name, PDO::PARAM_STR);
        $stmt->execute();
        $stmt->fetch(PDO::FETCH_NUM);
    }

    function runJoinSearch($i)
    {
        $query = 'SELECT book.ID, book.TITLE, book.ISBN, book.PRICE, book.AUTHOR_ID, author.ID, author.FIRST_NAME, author.LAST_NAME, author.EMAIL FROM book LEFT JOIN author ON book.AUTHOR_ID = author.ID WHERE book.TITLE = ? LIMIT 1';
        $stmt = $this->con->prepare($query);
        $str = 'Hello' . $i;
        $stmt->bindParam(1, $str, PDO::PARAM_STR);
        $stmt->execute();
        $book = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}