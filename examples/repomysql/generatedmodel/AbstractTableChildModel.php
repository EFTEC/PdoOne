error Uncaught Exception: <b>[PDOException]</b> code:42S02<br>mysql:Failed to run query"show columns from TableChild"<br><u>Message:</u>  SQLSTATE<b>[42S02]</b>: Base table or view not found: 1146 Table 'testdb.tablechild' doesn't exist<br><u>Last query:</u> <b>[show columns from TableChild]</b><br><u>Database:</u> 127.0.0.1 - testdb<br><u>param:</u> <b>[]</b><br><u>error_last:</u> {"type":2,"message":"Undefined array key \"tablechild\"","file":"D:\\www\\currentproject\\PdoOne\\lib\\PdoOne.php","line":2997}<br><u>Trace:</u><br><span style="background-color:blue; color:white">D:\www\currentproject\PdoOne\lib\PdoOne.php:1905</span>&nbsp;&nbsp;&nbsp;&nbsp;PDOStatement->execute((null))<br><span style="background-color:blue; color:white">D:\www\currentproject\PdoOne\lib\PdoOne.php:1692</span>&nbsp;&nbsp;&nbsp;&nbsp;eftec\PdoOne->runQuery(PDOStatement)<br><span style="background-color:blue; color:white">D:\www\currentproject\PdoOne\lib\ext\PdoOne_Mysql.php:147</span>&nbsp;&nbsp;&nbsp;&nbsp;eftec\PdoOne->runRawQuery('show columns from TableChild' , <b>[]</b>)<br><span style="background-color:blue; color:white">D:\www\currentproject\PdoOne\lib\PdoOne.php:905</span>&nbsp;&nbsp;&nbsp;&nbsp;eftec\ext\PdoOne_Mysql->getDefTable('TableChild')<br><span style="background-color:blue; color:white">D:\www\currentproject\PdoOne\lib\PdoOne.php:3755</span>&nbsp;&nbsp;&nbsp;&nbsp;eftec\PdoOne->getDefTable('TableChild' , <b>[]</b>)<br><span style="background-color:blue; color:white">D:\www\currentproject\PdoOne\lib\PdoOne.php:3396</span>&nbsp;&nbsp;&nbsp;&nbsp;eftec\PdoOne->generateAbstractModelClass('TableChild' , 'mysql\\repomodel' , <b>[]</b> , {"TableParent":"TableParentModel","TableChild":"TableChildModel","TableGrandChild":"TableGrandChildModel","TableGrandChildTag":"TableGrandChildTagModel","TableParentxCategory":"TableParentxCategoryModel","TableCategory":"TableCategoryModel","TableParentExt":"TableParentExtModel"} , <b>[]</b> , (null) , (null) , 'TestDb' , <b>[]</b> , <b>[]</b> , <b>[]</b>)<br><span style="background-color:blue; color:white">D:\www\currentproject\PdoOne\examples\repomysql\generate_tabla_repo.php:62</span>&nbsp;&nbsp;&nbsp;&nbsp;eftec\PdoOne->generateAllClasses({"TableParent":<b>["TableParentRepo","TableParentModel"]</b>,"TableChild":<b>["TableChildRepo","TableChildModel"]</b>,"TableGrandChild":<b>["TableGrandChildRepo","TableGrandChildModel"]</b>,"TableGrandChildTag":<b>["TableGrandChildTagRepo","TableGrandChildTagModel"]</b>,"TableParentxCategory":<b>["TableParentxCategoryRepo","TableParentxCategoryModel"]</b>,"TableCategory":<b>["TableCategoryRepo","TableCategoryModel"]</b>,"TableParentExt":<b>["TableParentExtRepo","TableParentExtModel"]</b>} , 'TestDb' , <b>["repomysql","mysql\\repomodel"]</b> , <b>["D:\\www\\currentproject\\PdoOne\\examples\\repomysql\/generated","D:\\www\\currentproject\\PdoOne\\examples\\repomysql\/generatedmodel"]</b> , '1' , {"TableParent":{"_idchild2FK":"PARENT","_TableParentxCategory":"MANYTOMANY","fieldKey":<b>["encrypt",null]</b>,"extracol":"datetime3"},"TableParentExt":{"_idtablaparentExtPK":"PARENT"</u> , {"TableParent":{"extracol":"CURRENT_TIMESTAMP","extracol2":"20"</u> , <b>[]</b>)<br>