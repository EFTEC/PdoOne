# Database Access Object wrapper for PHP and MySqli in a single class

PdoOne. It's a simple wrapper for Mysqli

This library is as fast as possible. Most of the operations are simple string/array managements.

[![Build Status](https://travis-ci.org/EFTEC/PdoOne.svg?branch=master)](https://travis-ci.org/EFTEC/PdoOne)
[![Packagist](https://img.shields.io/packagist/v/eftec/PdoOne.svg)](https://packagist.org/packages/eftec/PdoOne)
[![Total Downloads](https://poser.pugx.org/eftec/PdoOne/downloads)](https://packagist.org/packages/eftec/PdoOne)
[![Maintenance](https://img.shields.io/maintenance/yes/2019.svg)]()
[![composer](https://img.shields.io/badge/composer-%3E1.6-blue.svg)]()
[![php](https://img.shields.io/badge/php->5.6-green.svg)]()
[![php](https://img.shields.io/badge/php-7.x-green.svg)]()
[![CocoaPods](https://img.shields.io/badge/docs-70%25-yellow.svg)]()

Turn this 

```$stmt = $mysqli->prepare("SELECT * FROM myTable WHERE name = ?");
$stmt->bind_param("s", $_POST['name']);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows === 0) exit('No rows');
while($row = $result->fetch_assoc()) {
  $ids[] = $row['id'];
  $names[] = $row['name'];
  $ages[] = $row['age'];
}
var_export($ages);
$stmt->close();
```

into this

```
$products=$dao
    ->select("*")
    ->from("myTable")
    ->where("name = ?",[$_POST['name']])
    ->toList();
```



## Table of Content

- [PdoOne](#PdoOne)
  * [Install (using composer)](#install--using-composer-)
  * [Install (manually)](#install--manually-)
  * [Usage](#usage)
    + [Start a connection](#start-a-connection)
    + [Run an unprepared query](#run-an-unprepared-query)
    + [Run a prepared query](#run-a-prepared-query)
    + [Run a prepared query with parameters.](#run-a-prepared-query-with-parameters)
    + [Return data (first method)](#return-data--first-method-)
    + [Return data (second method)](#return-data--second-method-)
    + [Running a transaction](#running-a-transaction)
  * [Query Builder (DQL)](#query-builder--dql-)
    + [select($columns)](#select--columns-)
    + [distinct($distinct='distinct')](#distinct--distinct--distinct--)
    + [from($tables)](#from--tables-)
    + [where($where,[$arrayParameters=array()])](#where--where---arrayparameters-array----)
    + [order($order)](#order--order-)
    + [group($group)](#group--group-)
    + [having($having,[$arrayParameters])](#having--having---arrayparameters--)
    + [runGen($returnArray=true)](#rungen--returnarray-true-)
    + [toList()](#tolist--)
    + [toResult()](#toresult--)
    + [first()](#first--)
    + [last()](#last--)
    + [sqlGen()](#sqlgen--)
  * [Query Builder (DML), i.e. insert, update,delete](#query-builder--dml---ie-insert--update-delete)
    + [insert($table,$schema,[$values])](#insert--table--schema---values--)
    + [update($$table,$schema,$values,[$schemaWhere],[$valuesWhere])](#update---table--schema--values---schemawhere----valueswhere--)
    + [delete($table,$schemaWhere,[$valuesWhere])](#delete--table--schemawhere---valueswhere--)
  * [Changelist](#changelist)



## Install (using composer)

>

Add to composer.json the next requirement, then update composer.

```json
  {
      "require": {
        "eftec/PdoOne": "^3.15"
      }
  }
```
or install it via cli using

> composer require eftec/PdoOne

## Install (manually)

Just download the file lib/PdoOne.php and save it in a folder.

## Usage

### Start a connection

```php
$dao=new PdoOne("127.0.0.1","root","abc.123","sakila","");
$dao->connect();
```

where 
* 127.0.0.1 is the server where is the database.
* root is the user   
* abc.123 is the password of the user root.
* sakila is the database used.
* "" (optional) it could be a log file, such as c:\temp\log.txt

### Run an unprepared query

```php
$sql="CREATE TABLE `product` (
    `idproduct` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(45) NULL,
    PRIMARY KEY (`idproduct`));";
$dao->runRawQuery($sql);  
```

### Run a prepared query
```php
$sql="insert into `product`(name) values(?)";
$stmt=$dao->prepare($sql);
$productName="Cocacola";
$stmt->bind_param("s",$productName); // s stand for string. Also i =integer, d = double and b=blob
$dao->runQuery($stmt);
```

> note: you could also insert using a procedural chain [insert($table,$schema,[$values])](#insert--table--schema---values--)

### Run a prepared query with parameters.
```php
$dao->runRawQuery('insert into `product` (name) values(?)'
    ,array('s','cocacola'));
```



### Return data (first method)
It returns a mysqli_statement.

```php
    $sql="select * from `product` order by name";
    $stmt=$dao->prepare($sql);
    $dao->runQuery($stmt);
    $rows = $stmt->get_result();
    while ($row = $rows->fetch_assoc()) {
        var_dump($row);
    }
    
```    
> This statement must be processed manually.

### Return data (second method)
It returns an associative array.

```php
    $sql="select * from `product` order by name";
    $stmt=$dao->prepare($sql);
    $dao->runQuery($stmt);
    $rows = $stmt->get_result();
    $allRows=$rows->fetch_all(PDO::FETCH_ASSOC);
    var_dump($allRows);
```    

### Running a transaction
```php
try {
    $sql="insert into `product`(name) values(?)";
    $dao->startTransaction();
    $stmt=$dao->prepare($sql);
    $productName="Fanta";
    $stmt->bind_param("s",$productName); 
    $dao->runQuery($stmt);
    $dao->commit(); // transaction ok
} catch (Exception $e) {
    $dao->rollback(false); // error, transaction cancelled.
}
```   
#### startTransaction()
It starts a transaction

#### commit($throw=true)
It commits a transaction. 
* If $throw is true then it throws an exception if the transaction fails to commit.  Otherwise, it does not.

#### rollback($throw=true)
It rollbacks a transaction. 
* If $throw is true then it throws an exception if the transaction fails to rollback.  If false, then it ignores if the rollback fail or if the transaction is not open.

### Fields

### throwOnError=true
If true (default), then it throws an error if happens an error. If false, then the execution continues  

### isOpen=true
It is true if the database is connected otherwise,it's false.


## Query Builder (DQL)
You could also build a procedural query.

Example:
```php
$results = $dao->select("*")->from("producttype")
    ->where('name=?', ['s', 'Cocacola'])
    ->where('idproducttype=?', ['i', 1])
    ->toList();   
```

### select($columns)
Generates a select command.
```php
$results = $dao->select("col1,col2")->...
```
> Generates the query: **select col1,col2** ....

```php
$results = $dao->select("select * from table")->...
```

> Generates the query: **select * from table** ....



### distinct($distinct='distinct')
Generates a select command.
```php
$results = $dao->select("col1,col2")->distinct()...
```
> Generates the query: select **distinct** col1,col2 ....

>Note: ->distinct('unique') returns select **unique** ..

### from($tables)
Generates a from command.
```php
$results = $dao->select("*")->from('table')...
```
> Generates the query: select * **from table**

**$tables** could be a single table or a sql construction. For examp, the next command is valid:

```php
$results = $dao->select("*")->from('table t1 inner join t2 on t1.c1=t2.c2')...
```


### where($where,[$arrayParameters=array()])
Generates a where command.

* $where is an array or a string. If it's a string, then it's evaluated by using the parameters. if any

```php
$results = $dao->select("*")
->from('table')
->where('p1=1')...
```
> Generates the query: select * **from table** where p1=1

> Note: ArrayParameters is an array as follow: **type,value.**     
>   Where type is i=integer, d=double, s=string or b=blob. In case of doubt, use "s"   
> Example of arrayParameters:   
> ['i',1 ,'s','hello' ,'d',20.3 ,'s','world']

```php
$results = $dao->select("*")
->from('table')
->where('p1=?',['i',1])...
```
> Generates the query: select * from table **where p1=?(1)**

```php
$results = $dao->select("*")
->from('table')
->where('p1=? and p2=?',['i',1,'s','hello'])...
```

> Generates the query: select * from table **where p1=?(1) and p2=?('hello')**

> Note. where could be nested.
```php
$results = $dao->select("*")
->from('table')
->where('p1=?',['i',1])
->where('p2=?',['s','hello'])...
```
> Generates the query: select * from table **where p1=?(1) and p2=?('hello')**

You could also use:
```php
$results = $dao->select("*")->from("table")
    ->where(['p1'=>'Coca-Cola','p2'=>1])
    ->toList();
```
> Generates the query: select * from table **where p1=?(Coca-Cola) and p2=?(1)**        

### order($order)
Generates a order command.
```php
$results = $dao->select("*")
->from('table')
->order('p1 desc')...
```
> Generates the query: select * from table **order by p1 desc**

### group($group)
Generates a group command.
```php
$results = $dao->select("*")
->from('table')
->group('p1')...
```
> Generates the query: select * from table **group by p1**

### having($having,[$arrayParameters])
Generates a group command.
```php
$results = $dao->select("*")
->from('table')
->group('p1')
->having('p1>?',array('i',1))...
```
> Generates the query: select * from table group by p1 having p1>?(1)

> Note: Having could be nested having()->having()  
> Note: Having could be without parameters having('col>10') 

### runGen($returnArray=true)
Run the query generate.

>Note if returnArray is true then it returns an associative array.
> if returnArray is false then it returns a mysqli_result  
>Note: It resets the current parameters (such as current select, from, where,etc.)

### toList()
It's a macro of runGen. It returns an associative array or null.

```php
$results = $dao->select("*")
->from('table')
->toList()
```
### toResult()
It's a macro of runGen. It returns a mysqli_result or null.

```php
$results = $dao->select("*")
->from('table')
->toResult()
```

### first()
It's a macro of runGen. It returns the first row (if any, if not, it returns false) as an associative array.

```php
$results = $dao->select("*")
->from('table')
->first()
```

### last()
It's a macro of runGen. It returns the last row (if any, if not, it returns false) as an associative array.

```php
$results = $dao->select("*")
->from('table')
->last()
```
> Sometimes is more efficient to run order() and first() because last() reads all values.

### sqlGen()

It returns the sql command.
```php
$sql = $dao->select("*")
->from('table')
->sqlGen();
echo $sql; // returns select * from table
$results=$dao->toList(); // executes the query
```
> Note: it doesn't reset the query.

## Query Builder (DML), i.e. insert, update,delete

There are four ways to execute each command.

Let's say that we want to add an **integer** in the column **col1** with the value **20**

__Schema and values using a list of values__: Where the first value is the column, the second is the type of value (i=integer,d=double,s=string,b=blob) and second array contains the values.
```php
$dao->insert("table"
    ,['col1','i']
    ,[20]);
```
__Schema and values in the same list__: Where the first value is the column, the second is the type of value (i=integer,d=double,s=string,b=blob) and the third is the value.
```php
$dao->insert("table"
    ,['col1','i',20]);
```

__Schema and values using two associative arrays__:

```php
$dao->insert("table"
    ,['col1'=>'i']
    ,['col1'=>20]);
```
__Schema and values using a single associative array__: The type is calculated automatically.

```php
$dao->insert("table"
    ,['col1'=>20]);
```

### insert($table,$schema,[$values])
Generates a insert command.

```php
$dao->insert("producttype"
    ,['idproducttype','i','name','s','type','i']
    ,[1,'cocacola',1]);
```

Using nested chain (single array)
```php
    $dao->from("producttype")
        ->set(['idproducttype','i',0 ,'name','s','Pepsi' ,'type','i',1])
        ->insert();
```

Using nested chain multiple set
```php
    $dao->from("producttype")
        ->set("idproducttype=?",['i',101])
        ->set('name=?',['s','Pepsi'])
        ->set('type=?',['i',1])
        ->insert();
```
or (the type is defined, in the possible, automatically by MySql)     
```php
    $dao->from("producttype")
        ->set("idproducttype=?",['i',101])
        ->set('name=?','Pepsi')
        ->set('type=?',1)
        ->insert();
```




    
Using nested chain declarative set
```php
    $dao->from("producttype")
        ->set('(idproducttype,name,type) values (?,?,?)',['i',100,'s','Pepsi','i',1])
        ->insert();
```


> Generates the query: **insert into productype(idproducttype,name,type) values(?,?,?)** ....


### update($$table,$schema,$values,[$schemaWhere],[$valuesWhere])
Generates a insert command.

```php
$dao->update("producttype"
    ,['name','s','type','i'] //set
    ,[6,'Captain-Crunch',2] //set
    ,['idproducttype','i'] // where
    ,[6]); // where
```

```php
$dao->update("producttype"
    ,['name'=>'Captain-Crunch','type'=>2] // set
    ,['idproducttype'=>6]); // where
```

```php
$dao->from("producttype")
    ->set("name=?",['s','Captain-Crunch']) //set
    ->set("type=?",['i',6]) //set
    ->where('idproducttype=?',['i',6]) // where
    ->update(); // update
```

or

```php
$dao->from("producttype")
    ->set("name=?",'Captain-Crunch') //set
    ->set("type=?",6) //set
    ->where('idproducttype=?',['i',6]) // where
    ->update(); // update
```


> Generates the query: **update producttype set `name`=?,`type`=? where `idproducttype`=?** ....

### delete([$table],[$schemaWhere],[$valuesWhere])
Generates a delete command.

```php
$dao->delete("producttype"
    ,['idproducttype','i'] // where
    ,[7]); // where
```
```php
$dao->delete("producttype"
    ,['idproducttype'=>7]); // where
```
> Generates the query: **delete from producttype where `idproducttype`=?** ....

You could also delete via a DQL builder chain.
```php
$dao->from("producttype")
    ->where('idproducttype=?',['i',7]) // where
    ->delete(); 
```
```php
$dao->from("producttype")
    ->where(['idproducttype'=>7]) // where
    ->delete(); 
```
> Generates the query: **delete from producttype where `idproducttype`=?** ....

## Sequence

Sequence is an alternative to AUTO_NUMERIC field.  It uses a table to generate an unique ID.  
The sequence used is based on Twitter's Snowflake and it is generated based on 
time (with microseconds), Node Id and a sequence.   This generates a LONG (int 64) value that it's unique

### Creating a sequence

* **$dao->nodeId** set the node value (default is 1). If we want unique values amongst different clusters,
 then we could set the value
of the node as unique. The limit is up to 1024 nodes.
* **$dao->tableSequence** it sets the table (and function), the default value is snowflake.

```
$dao->nodeId=1; // optional
$dao->tableSequence='snowflake'; // optional
$dao->createSequence(); // it creates a table called snowflake and a function called next_snowflake()
```

### Using the sequence

* **$dao->getSequence([unpredictable=false])** returns the last sequence. If the sequence fails to generate, then it returns -1.
 The function could fails if the function is called more than 4096 times every 1/1000th second.

```
$dao->getSequence() // string(19) "3639032938181434317" 
```

```
$dao->getSequence(true) // returns a sequence by flipping some values.
```

### Creating a sequence without a table.

* **$dao->getSequencePHP([unpredictable=false])** Returns a sequence without using a table.
  This sequence is more efficient than $dao->getSequence but it uses a random value to deals
  with collisions.
  
* If upredictable is true then it returns an unpredictable number (it flips some digits)

```
$dao->getSequencePHP() // string(19) "3639032938181434317" 
```

```
$dao->getSequencePHP(true) // string(19) "1739032938181434311" 
```



## Changelist
* 3.28 2019-05-04 Added comments. Also ->select() allows an entire query.
* 3.27 2019-04-21 Added new methods of encryption SIMPLE (short encryption) and INTEGER (it converts and returns an integer)
* 3.26 2019-03-06 Now Encryption has it's own class.
* 3.25 2019-03-06 Added getSequencePHP(), getUnpredictable() and getUnpredictableInv()
* 3.24 2019-02-06 Added a new format of date
* 3.22 2018-12-30 Added sequence
* 3.21 2018-12-17 Fixed a bug with parameters, set() and insert(). There are several ways to do an insertar.  Now NULL is self:null
* 3.20 2018-12-15 Fixed bug with parameters and insert(). 
* 3.19 2018-12-09 Now null parameters are considered null.  We use instead PHP_INT_MAX to indicate when the value is not set. 
* 3.18 2018-12-07 Changed minimum stability.
* 3.17 2018-12-01 set() now allows a single value for the second argument.   
* 3.16 2018-11-03 Added test unit and travis CI.
* 3.15 2018-10-27
* * Now it allows multiple select()
* * function generateSqlFields()
* 3.14 2018-10-16 
* * Added field throwOnError. 
* * Added more control on the error. 
* * Now methods fails if the database is not open.
* * Added a container to messages (optional). It works with the function messages()
* * Added field isOpen
* * Added method storeInfo()
* 3.13 2018-10-05 Changed command eval to bind_param( ...)
* 3.12 2018-09-29 Fixed a bug with insert() it now returns the last identity.
* 3.11 2018-09-27 Cleaned the code. If it throws an exception, then the chain is reset.
* 3.9 2018-09-24 Some fixes
* 3.7 Added charset.
* 3.6 More fixes.
* 3.5 Small fixed.
* 3.4 DML new features. It allows nested operations 
    + ->from()->where()->delete()
    + ->from()->set()->where()->update()
    + ->from()->set()->insert()
* 3.3 DML modified. It allows a different kind of parameters.
* 3.2 Insert, Update,Delete
* 3.0 Major overhaul. It adds Query Builder features.
* 2.6.4 Better correction of error.
* 2.6.3 Fixed transaction. Now a nested transaction is not nested (and returns a false).
* 2.6 first public version


