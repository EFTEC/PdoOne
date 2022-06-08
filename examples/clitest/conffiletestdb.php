<?php http_response_code(404); die(1); // eftec/CliOne configuration file ?>
{
    "help": false,
    "first": "generate",
    "definition": "",
    "databasetype": "mysql",
    "server": "127.0.0.1",
    "user": "root",
    "password": "abc.123",
    "database": "testdb",
    "classdirectory": "repo2",
    "classnamespace": "eftec\\examples\\clitest\\repo2",
    "namespace": null,
    "savegen": null,
    "tables": null,
    "tablescolumns": null,
    "tablecommand": null,
    "convertionselected": null,
    "convertionnewvalue": null,
    "newclassname": null,
    "overridegenerate": "yes",
    "tablexclass": {
        "TableCategory": "TablecategoryRepo",
        "TableChild": "TablechildRepo",
        "TableGrandChild": "TablegrandchildRepo",
        "TableGrandChildTag": "TablegrandchildtagRepo",
        "TableParent": "TableParentRepo",
        "TableParentExt": "TableparentextRepo",
        "TableParentxCategory": "TableparentxcategoryRepo"
    },
    "conversion": [],
    "alias": [],
    "extracolumn": {
        "TableCategory": [],
        "TableChild": [],
        "TableGrandChild": [],
        "TableGrandChildTag": [],
        "TableParent": [],
        "TableParentExt": [],
        "TableParentxCategory": []
    },
    "removecolumn": [],
    "columnsTable": {
        "TableCategory": {
            "IdTableCategoryPK": null,
            "Name": null,
            "_TableParentxCategory": "ONETOMANY"
        },
        "TableChild": {
            "idgrandchildFK": null,
            "idtablachildPK": null,
            "NameChild": null,
            "_idgrandchildFK": "MANYTOONE",
            "_TableParent": "ONETOMANY"
        },
        "TableGrandChild": {
            "idgrandchildPK": null,
            "NameGrandChild": null,
            "_TableChild": "ONETOMANY"
        },
        "TableGrandChildTag": {
            "IdgrandchildFK": null,
            "IdTablaGrandChildTagPK": null,
            "Name": null
        },
        "TableParent": {
            "fieldDateTime": null,
            "fielDecimal": null,
            "fieldInt": null,
            "fieldKey": null,
            "fieldUnique": null,
            "fieldVarchar": null,
            "idchild2FK": null,
            "idchildFK": null,
            "idtablaparentPK": null,
            "_idchild2FK": "MANYTOONE",
            "_idchildFK": "MANYTOONE",
            "_TableParentExt": "ONETOONE",
            "_TableParentxCategory": "ONETOMANY"
        },
        "TableParentExt": {
            "fieldExt": null,
            "idtablaparentExtPK": null,
            "_idtablaparentExtPK": "ONETOONE"
        },
        "TableParentxCategory": {
            "idcategoryPKFK": null,
            "idtablaparentPKFK": null,
            "_idcategoryPKFK": "MANYTOONE",
            "_idtablaparentPKFK": "ONETOONE"
        }
    },
    "columnsAlias": {
        "TableCategory": {
            "IdTableCategoryPK": "IdTableCategoryPK",
            "Name": "Name",
            "_TableParentxCategory": "_TableParentxCategory"
        },
        "TableChild": {
            "idgrandchildFK": "idgrandchildFK",
            "idtablachildPK": "idtablachildPK",
            "NameChild": "NameChild",
            "_idgrandchildFK": "_idgrandchildFK",
            "_TableParent": "_TableParent"
        },
        "TableGrandChild": {
            "idgrandchildPK": "idgrandchildPK",
            "NameGrandChild": "NameGrandChild",
            "_TableChild": "_TableChild"
        },
        "TableGrandChildTag": {
            "IdgrandchildFK": "IdgrandchildFK",
            "IdTablaGrandChildTagPK": "IdTablaGrandChildTagPK",
            "Name": "Name"
        },
        "TableParent": {
            "fieldDateTime": "FieldDateTime",
            "fielDecimal": "FielDecimal",
            "fieldInt": "FieldInt",
            "fieldKey": "FieldKey",
            "fieldUnique": "FieldUnique",
            "fieldVarchar": "FieldVarchar",
            "idchild2FK": "Idchild2FK",
            "idchildFK": "IdchildFK",
            "idtablaparentPK": "IdtablaparentPK",
            "_idchild2FK": "_IdChild2FK",
            "_idchildFK": "_IdchildFK",
            "_TableParentExt": "_TableParentExt",
            "_TableParentxCategory": "_TableParentxCategory"
        },
        "TableParentExt": {
            "fieldExt": "fieldExt",
            "idtablaparentExtPK": "idtablaparentExtPK",
            "_idtablaparentExtPK": "_idtablaparentExtPK"
        },
        "TableParentxCategory": {
            "idcategoryPKFK": "idcategoryPKFK",
            "idtablaparentPKFK": "idtablaparentPKFK",
            "_idcategoryPKFK": "_idcategoryPKFK",
            "_idtablaparentPKFK": "_idtablaparentPKFK"
        }
    }
}